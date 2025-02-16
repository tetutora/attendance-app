<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠登録画面
    public function showAttendancePage()
    {
        $user = Auth::user();
        $attendance = $user->attendance()->whereDate('clock_in', now()->toDateString())->first();

        return view('general.attendance', compact('attendance'));
    }

    // 勤怠登録処理
    public function update(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('clock_in', now()->toDateString())
            ->latest()
            ->first();

        if (!$attendance) {
            Attendance::create([
                'user_id' => Auth::id(),
                'clock_in' => now(),
                'clock_out' => null,
                'break_start' => null,
                'break_end' => null,
                'break_time' => 0,
                'work_time' => 0,
            ]);
        } elseif (is_null($attendance->clock_out)) {
            switch ($request->action) {
                case 'break_start':
                    if (is_null($attendance->break_start) && is_null($attendance->break_end)) {
                        $attendance->break_start = now();
                        $attendance->save();
                    } elseif (!is_null($attendance->break_end)) {
                        $attendance->break_start = now();
                        $attendance->break_end = null;
                        $attendance->save();
                    }
                    break;
                case 'break_end':
                    if (!is_null($attendance->break_start) && is_null($attendance->break_end)) {
                        $attendance->break_end = now();

                        $breakStart = Carbon::parse($attendance->break_start);
                        $breakEnd = Carbon::parse($attendance->break_end);
                        $breakTime = $breakStart->diffInMinutes($breakEnd);

                        $attendance->break_time = $breakTime;
                        $attendance->save();
                    }
                    break;
                case 'clock_out':
                    $attendance->clock_out = now();

                    $clockIn = Carbon::parse($attendance->clock_in);
                    $clockOut = Carbon::parse($attendance->clock_out);
                    $workTime = $clockIn->diffInMinutes($clockOut);

                    $workTime -= $attendance->break_time;

                    $attendance->work_time = $workTime;

                    $attendance->save();
                    break;
                default:
                    break;
            }
        }

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

        return view('general.attendance_list',compact('attendances'));
    }

    // 勤怠詳細画面
    public function showDetail($id)
    {
        $attendance = Attendance::findOrFail($id);
        return view('general.attendance_detail', compact('attendance'));
    }

    // 勤怠修正処理
    public function updateAttendance(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->clock_in = $validated['clock_in'] ? Carbon::parse($validated['clock_in'])->format('Y-m-d H:i:s') : null;
        $attendance->clock_out = $validated['clock_out'] ? Carbon::parse($validated['clock_out'])->format('Y-m-d H:i:s') : null;
        $attendance->break_start = $validated['break_start'] ? Carbon::parse($validated['break_start'])->format('Y-m-d H:i:s') : null;
        $attendance->break_end = $validated['break_end'] ? Carbon::parse($validated['break_end'])->format('Y-m-d H:i:s') : null;
        $attendance->remarks = $validated['remarks'] ?? '';

        $attendance->save();

        return redirect()->back()->with('success', '勤怠情報を更新しました');

    }
}
