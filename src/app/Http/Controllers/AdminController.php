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

        $attendances = Attendance::whereDate('clock_in', $selectedDate)->get();

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
        $attendances = Attendance::whereMonth('clock_in', Carbon::parse($selectedMonth)->month)
            ->whereYear('clock_in', Carbon::parse($selectedMonth)->year)
            ->where('user_id',$id)
            ->get();

            return view('admin.staff-attendance', compact('attendances', 'staff', 'selectedMonth'));
    }

    // 勤怠修正申請一覧
    public function showCorrectionRequests(Request $request)
    {
        $status = $request->get('status', '承認待ち');

        if (auth()->user()->role === 'admin') {
            // 承認済みの場合は、AttendanceApproved を取得し、そこから AttendanceApproval のユーザーを取得
            $requests = ($status === '承認済み')
                ? AttendanceApproved::with(['attendance', 'attendance.user', 'attendance.attendanceApproval.user'])->get()
                : AttendanceApproval::with('user')->whereNotNull('requested_at')->get();
        } else {
            $requests = ($status === '承認済み')
                ? AttendanceApproved::with(['attendance', 'attendance.user', 'attendance.attendanceApproval.user'])->get()
                : AttendanceApproval::with('user')->whereNotNull('requested_at')->get();
        }

        return view('admin.correction-requests', compact('requests', 'status'));
    }

    // 勤怠修正申請詳細表示
    public function showAttendanceDetail($attendanceCorrectRequestId)
    {
        $attendanceApproval = AttendanceApproval::findOrFail($attendanceCorrectRequestId);
        $attendance = $attendanceApproval->attendance;

        return view('admin.attendance-detail', compact('attendance', 'attendanceApproval'));
    }

    // 申請承認処理
    public function approve(Request $request, $attendance_correct_request)
    {
        $attendanceApproval = AttendanceApproval::findOrFail($attendance_correct_request);
        $attendance = $attendanceApproval->attendance;

        if ($request->status === '承認') {
            $userId = $attendanceApproval->user_id;

            // 承認された内容を AttendanceApproved テーブルに保存
            AttendanceApproved::create([
                'attendance_id' => $attendance->id,
                'user_id' => $userId,
                'status' => '承認済み',
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out,
                'break_start' => $attendance->break_start,
                'break_end' => $attendance->break_end,
                'remarks' => $attendance->remarks,
            ]);

            // 承認申請の削除
            $attendanceApproval->delete();

            // 承認済みメッセージをセッションに保存
            session()->flash('status', '承認済み');
        }

        // 承認後に同じページにリダイレクト
        return back();
    }

}
