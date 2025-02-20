<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;


class AdminController extends Controller
{
    // 勤怠一覧表示
    public function index(Request $request)
    {
        $selectedDate = Carbon::parse($request->get('date', now()->format('Y-m-d')));

        $attendances = Attendance::whereDate('clock_in', $selectedDate)->get();

        return view('admin.attendance-list', compact('attendances', 'selectedDate'));
    }

    public function showStaffList()
    {
        $staffs = User::where('role', 'user')->get();

        return view('admin.staff-list', compact('staffs'));
    }

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

}
