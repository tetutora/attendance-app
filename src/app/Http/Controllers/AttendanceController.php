<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\AttendanceStatus;
use App\Models\ApprovalStatus;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateAttendanceRequest;

class AttendanceController extends Controller
{
    // 勤怠登録画面
    public function showAttendancePage()
    {
        $user = Auth::user();
        $attendance = $user->attendance()
            ->whereDate('attendance_date', now()->toDateString())
            ->latest()
            ->first();

        $breaks = $attendance ? $attendance->breaks : collect();

        if (!$attendance) {
            $status = AttendanceStatus::where('status', 'off_duty')->first();
        } else {
            if ($attendance->clock_out) {
                $status = AttendanceStatus::where('status', 'clocked_out')->first();
            } elseif ($attendance->break_in && !$attendance->break_out) {
                $status = AttendanceStatus::where('status', 'on_break')->first();
            } elseif ($attendance->clock_in && (!$attendance->break_in || $attendance->break_out)) {
                $status = AttendanceStatus::where('status', 'in_office')->first();
            }
        }

        $statuses = AttendanceStatus::all();

        return view('general.attendance', compact('attendance', 'status', 'statuses', 'breaks'));
    }

    // 勤怠登録処理
    public function update(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', now()->toDateString())
            ->latest()
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'attendance_date' => now()->toDateString(),
                'clock_in' => now()->format('H:i'),
                'status_id' => AttendanceStatus::where('status', 'in_office')->value('id'),
            ]);
        }

        // 休憩時間の処理
        if ($attendance && is_null($attendance->clock_out)) {
            switch ($request->action) {
                case 'break_in':
                    if ($attendance->breaks->isEmpty() || !is_null($attendance->breaks->last()->break_out)) {
                        // 新しい休憩を開始
                        $attendance->breaks()->create([
                            'break_in' => now()->format('H:i'),
                        ]);
                    }
                    break;
                case 'break_out':
                    // 休憩終了の処理
                    if (!$attendance->breaks->isEmpty() && is_null($attendance->breaks->last()->break_out)) {
                        $attendance->breaks->last()->update([
                            'break_out' => now()->format('H:i'),
                        ]);
                    }
                    break;
                case 'clock_out': // 退勤処理
                    // 退勤処理を行う場合
                    $attendance->clock_out = now()->format('H:i');
                    $attendance->save();
                    break;
            }
        }

        // 勤務時間を再計算
        $attendance->save();

        return redirect()->route('general.attendance');
    }

    // 勤怠一覧画面
    public function showAttendanceList(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('attendance_date', substr($month, 0, 4))
            ->whereMonth('attendance_date', substr($month, 5, 2))
            ->orderBy('attendance_date', 'asc')
            ->get();

        foreach ($attendances as $attendance) {
            // 勤務時間を計算
            $attendance->formatted_work_time = $this->calculateWorkDuration(
                $attendance->clock_in,
                $attendance->clock_out,
                $attendance->break_time
            );
        }

        return view('general.attendance_list', compact('attendances'));
    }

    // 申請一覧
    public function showRequest(Request $request)
    {
        $user = Auth::user();

        $statusText = $request->query('status', '承認待ち');

        $statusMapping = [
            '承認待ち' => 1,
            '承認済み' => 2
        ];
        $statusId = $statusMapping[$statusText] ?? 1;

        $requests = DB::table('attendance_approvals')
            ->join('users', 'attendance_approvals.user_id', '=', 'users.id')
            ->select(
                'attendance_approvals.approval_status_id as status',
                'users.name as name',
                'attendance_approvals.attendance_date',
                'attendance_approvals.remarks',
                'attendance_approvals.created_at',
                'attendance_approvals.attendance_id'
            )
            ->where('attendance_approvals.approval_status_id', '=', $statusId)
            ->where('attendance_approvals.user_id', '=', $user->id)
            ->orderBy('attendance_approvals.attendance_date', 'asc')
            ->get();

        $statusLabels = [
            1 => '承認待ち',
            2 => '承認済み',
        ];

        foreach ($requests as $request) {
            $request->status = $statusLabels[$request->status];
        }

        return view('general.attendance_request', compact('requests'))->with('status', $statusText);
    }

    // 勤怠詳細画面
    public function showDetail($id)
    {
        $attendance = Attendance::with('breaks')->find($id);
        $attendance->formatted_work_time = $this->formatMinutesToTimeString($attendance->work_time);
        $attendance->formatted_break_time = $this->formatMinutesToTimeString($attendance->break_time);

        $breaks = $attendance->breaks;

        $approval = \App\Models\AttendanceApproval::where('attendance_id', $attendance->id)->first();

        $isApprovalPending = $approval && $approval->approval_status_id === 1;

        return view('general.attendance_detail', compact('attendance', 'approval', 'isApprovalPending', 'breaks'));
    }

    public function updateAttendance(Request $request, $attendance_id)
    {
        // dd($request->all());

        $attendance = Attendance::find($attendance_id);
        $attendance->attendance_date = $request->attendance_date;
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->save();

        $totalBreakTime = 0;

        if ($request->has('break_in') && $request->has('break_out')) {
            $break_in = $request->input('break_in');
            $break_out = $request->input('break_out');

            foreach ($break_in as $break_id => $break_start) {
                $break_end = $break_out[$break_id];

                $existingBreak = BreakTime::where('attendance_id', $attendance_id)
                                            ->where('id', $break_id)
                                            ->first();

                if ($existingBreak) {
                    $existingBreak->update([
                        'break_in' => $break_start,
                        'break_out' => $break_end,
                    ]);

                    $break_in_time = \Carbon\Carbon::parse($break_start);
                    $break_out_time = \Carbon\Carbon::parse($break_end);

                    if ($break_out_time->lessThan($break_in_time)) {
                        $break_out_time->addDay();
                    }

                    $totalBreakTime += $break_in_time->diffInMinutes($break_out_time);
                }
            }
            $attendance->break_time = $totalBreakTime;
            $attendance->save();
        }
        return redirect()->route('general.attendance_detail', ['id' => $attendance->id]);
    }

    // 勤怠修正処理
    // public function updateAttendance(Request $request,$attendance_id)
    // {
    //     // $attendance = Attendance::where('id', $request->input('attendance_id'))
        //                         ->where('user_id', Auth::id())
        //                         ->first();

        // if (!$attendance) {
        //     return redirect()->back()->with('error', '該当する勤怠データが見つかりません。');
        // }

        // $attendance->fill([
        //     'clock_in' => $request->input('clock_in'),
        //     'clock_out' => $request->input('clock_out'),
        //     'attendance_date' => $request->input('attendance_date'),
        // ])->save();


        // $existingBreaks = $attendance->breaks()->orderBy('break_in')->get();
        // $breakIns = $request->input('break_in', []);
        // $breakOuts = $request->input('break_out', []);

        // foreach ($breakIns as $index => $breakIn) {
        //     $breakOut = $breakOuts[$index] ?? null;

        //     if (isset($existingBreaks[$index])) {
        //         $existingBreaks[$index]->update([
        //             'break_in' => $breakIn,
        //             'break_out' => $breakOut,
        //         ]);
        //     } else {
        //         $attendance->breaks()->create([
        //             'break_in' => $breakIn->format('H:i'),
        //             'break_out' => $breakOut->format('H:i'),
        //         ]);
        //     }
        // }

        // // 勤務時間を再計算
        // $attendance->work_time = $this->calculateWorkDuration(
        //     $attendance->clock_in,
        //     $attendance->clock_out,
        //     $attendance->breaks->sum('break_time')
        // );

        // $attendance->save();

        // // 承認待ちの申請を作成
        // $approvalStatus = ApprovalStatus::where('status', '承認待ち')->first();
        // if (!$approvalStatus) {
        //     return redirect()->route('general.attendance_detail', ['id' => $attendance->id])
        //         ->with('error', '承認待ちステータスが見つかりません');
        // }

        // $approvalData = AttendanceApproval::create([
        //     'attendance_id' => $attendance->id,
        //     'user_id' => $attendance->user_id,
        //     'approval_status_id' => $approvalStatus->id,
        //     'attendance_date' => $attendance->attendance_date,
        //     'clock_in' => $attendance->clock_in,
        //     'clock_out' => $attendance->clock_out,
        //     'break_time' => $attendance->breaks->sum('break_time'),
        //     'work_time' => $attendance->work_time,
        //     'remarks' => $request->input('remarks'),
        // ]);
        // // dd($approvalData);

    // }

    private function formatMinutesToTimeString($minutes)
    {
        if ($minutes === null || $minutes <= 0) {
            return '00:00';
        }
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }

    private function calculateWorkDuration($clock_in, $clock_out, $break_time)
    {
        if (!$clock_in || !$clock_out) {
            return '00:00';
        }

        $clock_in_time = Carbon::parse($clock_in);
        $clock_out_time = Carbon::parse($clock_out);

        if ($clock_out_time->lessThan($clock_in_time)) {
            $clock_out_time->addDay();
        }

        $work_duration = $clock_in_time->diffInMinutes($clock_out_time);

        if ($break_time && $break_time > 0) {
            $work_duration -= $break_time;
        }

        return $this->formatMinutesToTimeString($work_duration);
    }

    private function calculateBreakDuration($break_in, $break_out)
    {
        if (!$break_in || !$break_out) {
            return '00:00';
        }

        $break_in_time = Carbon::parse($break_in);
        $break_out_time = Carbon::parse($break_out);

        if ($break_out_time->lessThan($break_in_time)) {
            $break_out_time->addDay();
        }

        $break_duration = $break_in_time->diffInMinutes($break_out_time);

        return $this->formatMinutesToTimeString($break_duration);
    }
}
