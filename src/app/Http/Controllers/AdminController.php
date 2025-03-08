<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\AttendanceApproved;
use App\Models\User;
use Carbon\Carbon;
use Auth;


class AdminController extends Controller
{
    // 勤怠一覧表示
    public function index(Request $request)
    {
        $selectedDate = Carbon::parse($request->get('date', now()->format('Y-m-d')));

        $attendances = Attendance::whereDate('attendance_date', $selectedDate)->get();

        return view('admin.attendance-list', compact('attendances', 'selectedDate'));
    }

    // スタッフ一覧表示
    public function showStaffList()
    {
        $staffs = User::where('role', 'user')->get();

        return view('admin.staff-list', compact('staffs'));
    }

    // スタッフ別勤怠一覧表示
    public function showStaffAttendanceList(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $selectedMonth = $request->get('month', now()->format('Y-m'));
        $attendances = Attendance::whereMonth('attendance_date', Carbon::parse($selectedMonth)->month)
            ->whereYear('clock_in', Carbon::parse($selectedMonth)->year)
            ->where('user_id',$id)
            ->orderBy('attendance_date', 'asc')
            ->get();

            return view('admin.staff-attendance', compact('attendances', 'staff', 'selectedMonth'));
    }

    // 勤怠修正申請一覧
    public function showCorrectionRequests(Request $request)
    {
        $approvalStatusId = $request->query('approval_status_id', 1); // '承認待ち'がデフォルト

        if (auth()->user()->role === 'admin') {
            if ($approvalStatusId == 2) {
                $requests = AttendanceApproved::with(['attendance', 'attendance.user', 'attendance.attendanceApprovals.user', 'approvalStatus'])
                    ->orderBy('attendance_date', 'asc')
                    ->get();
            } else {
                $requests = AttendanceApproval::with(['user', 'approvalStatus'])
                    ->whereNotNull('created_at')
                    ->orderBy('attendance_date', 'asc')
                    ->get();
            }
        }

        return view('admin.correction-requests', compact('requests', 'approvalStatusId'));
    }

    // 勤怠修正申請詳細表示
    public function showAttendanceDetail($attendance_correct_request)
{
    $attendanceApproval = AttendanceApproval::find($attendance_correct_request);
    $attendanceApproved = AttendanceApproved::find($attendance_correct_request);

    if ($attendanceApproval) {
        $attendance = $attendanceApproval->attendance;
        if (!$attendance) {
            abort(404, "Attendance not found");
        }
        $breaks = $attendance->breaks;
        $currentApproval = $attendanceApproval;
    } elseif ($attendanceApproved) {
        $attendance = $attendanceApproved->attendance;
        if (!$attendance) {
            abort(404, "Attendance not found");
        }
        $breaks = $attendance->breaks;
        $currentApproval = $attendanceApproved;
    } else {
        abort(404, "AttendanceApproval or AttendanceApproved not found");
    }

    return view('admin.attendance-detail', compact('attendance', 'attendanceApproval', 'attendanceApproved', 'breaks'));
}

    // 申請承認処理
    public function approve(Request $request, $attendance_correct_request)
    {
        \Log::info('Received attendance_correct_request: ' . $attendance_correct_request);

        $attendance_correct_request = (int) $attendance_correct_request;

        $attendanceApproval = AttendanceApproval::find($attendance_correct_request);
        $attendanceApproved = AttendanceApproved::find($attendance_correct_request);

        if (!$attendanceApproval && !$attendanceApproved) {
            abort(404, "AttendanceApproval or AttendanceApproved not found");
        }

        if ($attendanceApproval) {
            $attendance = $attendanceApproval->attendance;
            $currentApproval = $attendanceApproval;
        } elseif ($attendanceApproved) {
            $attendance = $attendanceApproved->attendance;
            $currentApproval = $attendanceApproved;
        }

        if (!$attendance) {
            abort(404, "Attendance not found");
        }

        // 承認処理
        $newAttendanceApproved = AttendanceApproved::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'approval_status_id' => 2,
            'attendance_date' => $attendance->attendance_date,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => $attendanceApproval ? $attendanceApproval->remarks : null,
        ]);

        // 既存の申請データを削除
        if ($attendanceApproval) {
            $attendanceApproval->delete();
        }

        // 新しい承認データのIDを使用してリダイレクト
        return redirect()->route('admin.attendance-detail', ['attendance_correct_request' => $newAttendanceApproved->id]);
    }

}
