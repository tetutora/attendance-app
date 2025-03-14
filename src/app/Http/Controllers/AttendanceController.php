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
            } elseif ($attendance->breaks->whereNull('break_out')->isNotEmpty()) { // 休憩中の判定
                $status = AttendanceStatus::where('status', 'on_break')->first();
            } elseif ($attendance->clock_in) {
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
            $status = AttendanceStatus::where('status', 'in_office')->first();
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'attendance_date' => now()->toDateString(),
                'clock_in' => now()->format('H:i'),
                'status_id' => $status ? $status->id : null,
            ]);
        }

        if ($request->filled('status')) {
            $status = AttendanceStatus::where('status', $request->input('status'))->first();
            if ($status) {
                $attendance->status_id = $status->id;
                $attendance->save();
            } else {
                \Log::error('Invalid status received: ' . $request->input('status'));
            }
        }

        if ($attendance && is_null($attendance->clock_out)) {
            switch ($request->action) {
                case 'break_in':
                    if ($attendance->breaks->isEmpty() || !is_null($attendance->breaks->last()->break_out)) {
                        $attendance->breaks()->create([
                            'break_in' => now()->format('H:i'),
                        ]);

                        $status = AttendanceStatus::where('status', 'on_break')->first();
                        if ($status) {
                            $attendance->status_id = $status->id;
                            $attendance->save();
                        }
                    }
                    break;

                case 'break_out':
                    if (!$attendance->breaks->isEmpty() && is_null($attendance->breaks->last()->break_out)) {
                        $attendance->breaks->last()->update([
                            'break_out' => now()->format('H:i'),
                        ]);

                        // ステータスを「出勤中」に戻す
                        $status = AttendanceStatus::where('status', 'in_office')->first();
                        if ($status) {
                            $attendance->status_id = $status->id;
                            $attendance->save();
                        }
                    }
                    break;

                case 'clock_out':
                    $attendance->clock_out = now()->format('H:i');
                    $attendance->save();

                    $status = AttendanceStatus::where('status', 'clocked_out')->first();
                    if ($status) {
                        $attendance->status_id = $status->id;
                        $attendance->save();
                    }
                    break;
            }
        }
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

        if ($statusId == 2) {
            // 承認済みの勤怠データを取得
            $requests = DB::table('attendance_approved')
                ->join('users', 'attendance_approved.user_id', '=', 'users.id')
                ->select(
                    'attendance_approved.approval_status_id as status',
                    'users.name as name',
                    'attendance_approved.attendance_date',
                    'attendance_approved.remarks',
                    'attendance_approved.created_at',
                    'attendance_approved.attendance_id'
                )
                ->where('attendance_approved.user_id', '=', $user->id)
                ->orderBy('attendance_approved.attendance_date', 'asc')
                ->get();
        } else {
            // 承認待ちの勤怠データを取得
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
                ->where('attendance_approvals.approval_status_id', '=', 1) // 承認待ち
                ->where('attendance_approvals.user_id', '=', $user->id)
                ->orderBy('attendance_approvals.attendance_date', 'asc')
                ->get();
        }

        $statusLabels = [
            1 => '承認待ち',
            2 => '承認済み',
        ];

        // ステータスのラベルを変換
        foreach ($requests as $request) {
            $request->status = $statusLabels[$request->status];
        }

        return view('general.attendance_request', compact('requests'))->with('status', $statusText);
    }

    // 勤怠詳細画面
    public function showDetail($id)
    {
        $attendance = Attendance::with('breaks')->find($id);

        $date = Carbon::parse($attendance->attendance_date);

        $attendance->formatted_work_time = $this->formatMinutesToTimeString($attendance->work_time);
        $attendance->formatted_break_time = $this->formatMinutesToTimeString($attendance->break_time);

        $breaks = $attendance->breaks;

        $approval = \App\Models\AttendanceApproval::where('attendance_id', $attendance->id)->first();

        $isApprovalPending = $approval && $approval->approval_status_id === 1;

        return view('general.attendance_detail', compact('attendance', 'approval', 'isApprovalPending', 'breaks', 'date'));
    }

    public function updateAttendance(UpdateAttendanceRequest $request, $attendance_id)
    {
        $attendance = Attendance::find($attendance_id);

        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->save();

        if ($request->has('break_in') && $request->has('break_out')) {
            $totalBreakTime = 0;

            BreakTime::where('attendance_id', $attendance_id)->delete();

            $breakInTimes = $request->input('break_in');
            $breakOutTimes = $request->input('break_out');

            foreach ($breakInTimes as $index => $breakStart) {
                $breakEnd = $breakOutTimes[$index];

                $break_in_time = Carbon::parse($breakStart);
                $break_out_time = Carbon::parse($breakEnd);

                if ($break_out_time->lessThan($break_in_time)) {
                    $break_out_time->addDay();
                }

                BreakTime::create([
                    'attendance_id' => $attendance_id,
                    'break_in' => $breakStart,
                    'break_out' => $breakEnd,
                ]);

                $totalBreakTime += $break_in_time->diffInMinutes($break_out_time);
            }
            $attendance->break_time = $totalBreakTime;
        }

        $attendance->save();

        $attendanceApproval = new AttendanceApproval([
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'approval_status_id' => 1,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => $request->remarks,
        ]);
        $attendanceApproval->save();

        return redirect()->route('general.attendance_detail', ['id' => $attendance->id]);
    }

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
