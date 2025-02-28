<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\AttendanceStatus;
use App\Models\ApprovalStatus;
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

        if (!$attendance) {
            $status = AttendanceStatus::where('status', 'off_duty')->first(); // 勤務外のステータス
        } else {
            // 勤務情報がある場合、そのステータスを設定
            if ($attendance->clock_out) {
                $status = AttendanceStatus::where('status', 'clocked_out')->first();
            } elseif ($attendance->break_in && !$attendance->break_out) {
                $status = AttendanceStatus::where('status', 'on_break')->first();
            } elseif ($attendance->clock_in && (!$attendance->break_in || $attendance->break_out)) {
                $status = AttendanceStatus::where('status', 'in_office')->first();
            }
        }

        $statuses = AttendanceStatus::all();

        return view('general.attendance', compact('attendance', 'status', 'statuses'));
    }

    // 勤怠登録処理
    public function update(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', now()->toDateString())
            ->latest()
            ->first();

        if (!$attendance) {
            // 初回出勤処理
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'attendance_date' => now()->toDateString(),
                'clock_in' => now()->format('H:i'),
                'status_id' => AttendanceStatus::where('status', 'in_office')->value('id'), // ステータス設定
            ]);
        } elseif (is_null($attendance->clock_out)) {
            switch ($request->action) {
                case 'break_in':
                    if (is_null($attendance->break_in) && is_null($attendance->break_out)) {
                        $attendance->break_in = now()->format('H:i');
                        $attendance->status_id = AttendanceStatus::where('status', 'on_break')->value('id'); // 休憩中
                    }
                    break;
                case 'break_out':
                    if (!is_null($attendance->break_in) && is_null($attendance->break_out)) {
                        $attendance->break_out = now()->format('H:i');
                        $attendance->status_id = AttendanceStatus::where('status', 'in_office')->value('id'); // 出勤中に戻る
                    }
                    break;
                case 'clock_out':
                    $attendance->clock_out = now()->format('H:i');
                    $attendance->status_id = AttendanceStatus::where('status', 'clocked_out')->value('id'); // 退勤済み
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
            ->whereYear('clock_in', substr($month, 0, 4))
            ->whereMonth('clock_in', substr($month, 5, 2))
            ->orderBy('clock_in', 'asc')
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

    // 勤務時間の計算
    private function calculateWorkDuration($clock_in, $clock_out, $break_time)
    {
        if (!$clock_in || !$clock_out) {
            return '00:00';
        }

        $clock_in_time = Carbon::parse($clock_in);
        $clock_out_time = Carbon::parse($clock_out);

        // 勤務時間の計算
        $work_duration = $clock_in_time->diffInMinutes($clock_out_time);

        // 休憩時間を差し引く
        if ($break_time) {
            $work_duration -= $break_time;
        }

        return $this->formatMinutesToTimeString($work_duration);
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

    // 勤怠詳細画面
    public function showDetail($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->formatted_work_time = $this->formatMinutesToTimeString($attendance->work_time);
        $attendance->formatted_break_time = $this->formatMinutesToTimeString($attendance->break_time);

        return view('general.attendance_detail', compact('attendance'));
    }

    // 申請一覧
    public function showRequest(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status', '承認待ち');

        $requests = DB::table('attendance_approvals')  // テーブル名を修正
            ->join('users', 'attendance_approvals.user_id', '=', 'users.id')  // テーブル名を修正
            ->where('approval_status_id', '=', $status)  // ステータスでフィルタリング
            ->where('user_id', '=', $user->id)
            ->orderByDesc('attendance_approvals.created_at')  // テーブル名を修正
            ->get();

        return view('general.attendance_request', compact('requests'));
    }

    // 勤怠修正処理
    public function updateAttendance(UpdateAttendanceRequest $request, $id)
    {
        $validated = $request->validated();
        $attendance = Attendance::findOrFail($id);

        // 承認待ちのステータス確認
        $approval = $attendance->approval;
        if ($approval && $approval->status === '承認待ち') {
            return redirect()->route('general.attendance_detail', ['id' => $attendance->id])
                ->with('attendance_updated', '承認待ちのため修正できません');
        }

        // 勤怠情報を更新
        if ($request->has('attendance_date')) {
            $attendance->attendance_date = Carbon::parse($request->input('attendance_date'))->format('Y-m-d');
        }

        $attendance->clock_in = $validated['clock_in'] ?? $attendance->clock_in;
        $attendance->clock_out = $validated['clock_out'] ?? $attendance->clock_out;
        $attendance->break_in = $validated['break_in'] ?? $attendance->break_in;
        $attendance->break_out = $validated['break_out'] ?? $attendance->break_out;

        if (!is_null($attendance->break_in) && !is_null($attendance->break_out)) {
            $breakIn = Carbon::parse($attendance->break_in);
            $breakOut = Carbon::parse($attendance->break_out);
            $attendance->break_time = max($breakIn->diffInMinutes($breakOut), 0);
        } else {
            $attendance->break_time = 0;
        }

        if (!is_null($attendance->clock_in) && !is_null($attendance->clock_out)) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            $attendance->work_time = max($clockIn->diffInMinutes($clockOut) - ($attendance->break_time ?? 0), 0);
        }

        $attendance->save();

        $approvalStatus = ApprovalStatus::where('status', '承認待ち')->first();
        if ($approvalStatus) {
            $approvalStatusId = $approvalStatus->id;
        } else {
            return redirect()->route('general.attendance_detail', ['id' => $attendance->id])
                ->with('error', '承認待ちステータスが見つかりません');
        }

        AttendanceApproval::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'approval_status_id' => $approvalStatusId,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_in' => $attendance->break_in,
            'break_out' => $attendance->break_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => $attendance->remarks,
        ]);

        return redirect()->route('general.attendance_detail', ['id'=> $attendance->id]);
    }
}
