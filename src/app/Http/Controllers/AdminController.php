<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\AttendanceApproved;
use App\Models\User;
use Carbon\Carbon;
use Auth;
use League\Csv\Writer;
use SplTempFileObject;
use App\Http\Requests\ApprovalDetailRequest;


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
        $approvalStatusId = $request->query('approval_status_id', 1);

        if (auth()->user()->role === 'admin') {
            $requests = AttendanceApproval::with(['user', 'approvalStatus'])
                ->where('approval_status_id', $approvalStatusId)
                ->orderBy('attendance_date', 'asc')
                ->get();
        }
        \Log::debug($requests);
        return view('admin.correction-requests', compact('requests', 'approvalStatusId'));
    }

    // 勤怠詳細表示
    public function showAttendanceDetail($attendance)
    {
        $attendance = Attendance::find($attendance);

        if (!$attendance) {
            abort(404, "Attendance not found");
        }

        $attendanceApproval = $attendance->approval;

        $isApprovalPending = isset($attendanceApproval) && $attendanceApproval->approval_status_id == 1;

        $breaks = $attendance->breaks;

        return view('general.attendance_detail', compact('attendance', 'attendanceApproval', 'isApprovalPending', 'breaks'));
    }

    // 勤怠詳細更新処理
    public function updateAttendanceDetail(ApprovalDetailRequest $request, $attendance)
    {
        $attendance = Attendance::find($attendance);

        if (!$attendance) {
            abort(404, "Attendance not found");
        }

        $attendance->clock_in = $request->input('clock_in', $attendance->clock_in);
        $attendance->clock_out = $request->input('clock_out', $attendance->clock_out);
        $attendance->break_in = $request->input('break_in', $attendance->break_in);
        $attendance->break_out = $request->input('break_out', $attendance->break_out);

        $attendance->save();

        return redirect()->route('admin.attendance-detail', ['attendance' => $attendance->id]);
    }

    // 勤怠修正申請詳細表示
    public function showRequestAttendanceDetail($attendance_correct_request)
    {
        $attendanceApproval = AttendanceApproval::find($attendance_correct_request);

        if (!$attendanceApproval) {
            abort(404, "AttendanceApproval not found");
        }

        $attendance = $attendanceApproval->attendance;
        if (!$attendance) {
            abort(404, "Attendance not found");
        }

        $breaks = $attendance->breaks;
        $isApprovalPending = $attendanceApproval->approval_status_id === 1;

        return view('admin.attendance-detail', compact('attendance', 'attendanceApproval', 'breaks', 'isApprovalPending'));
    }

    // 申請承認処理
    public function approve(ApprovalDetailRequest $request, $attendance_correct_request)
    {
        $attendanceApproval = AttendanceApproval::find($attendance_correct_request);

        if (!$attendanceApproval) {
            abort(404, "AttendanceApproval or not found");
        }

        $attendanceApproval->update([
            'approval_status_id' => 2
        ]);

        return redirect()->route('admin.stamp_correction_request.approve', ['attendance_correct_request' => $attendanceApproval->id]);

    }

    // CSV出力
    public function exportCSV(Request $request, $userId)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('attendance_date', Carbon::parse($month)->year)
            ->whereMonth('attendance_date', Carbon::parse($month)->month)
            ->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject(), 'w');

        $csv->insertOne(['日付', '出勤', '退勤', '休憩', '合計', '詳細']);

        foreach ($attendances as $attendance) {
            $csv->insertOne([
                $attendance->attendance_date ? Carbon::parse($attendance->attendance_date)->format('Y-m-d (D)') : '',
                $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                $this->convertToHoursMinutes($attendance->break_time),
                $this->convertToHoursMinutes($attendance->work_time),
                route('general.attendance-detail', ['id' => $attendance->id]),
            ]);
        }

        $filename = 'attendance_' . Carbon::parse($month)->format('Y-m') . '_user_' . $userId . '.csv';
        return response((string) $csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    protected function convertToHoursMinutes($minutes)
    {
        if (!$minutes) return '';
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        return sprintf('%d:%02d', $hours, $minutes);
    }

}
