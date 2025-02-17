<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceApproval;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\UpdateAttendanceRequest;

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
    public function updateAttendance(UpdateAttendanceRequest $request, $id)
    {
        $validated = $request->validated();

        $attendance = Attendance::findOrFail($id);

        $approval = $attendance->approval;
        if ($approval && $approval->status === '承認待ち') {
            return redirect()->route('general.attendance_detail', ['id' => $attendance->id])
                ->with('attendance_updated', '承認待ちのため修正できません');
        }

        if ($request->has('year') && $request->has('month_day')) {
            $year = $request->input('year');
            list($month, $day) = explode('-', $request->input('month_day'));

            if (!empty($attendance->clock_in)) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $attendance->clock_in = Carbon::create($year, $month, $day, $clockIn->hour, $clockIn->minute)->format('Y-m-d H:i:s');
            }

            if (!empty($attendance->clock_out)) {
                $clockOut = Carbon::parse($attendance->clock_out);
                $attendance->clock_out = Carbon::create($year, $month, $day, $clockOut->hour, $clockOut->minute)->format('Y-m-d H:i:s');
            }

            if (!empty($attendance->break_start)) {
                $breakStart = Carbon::parse($attendance->break_start);
                $attendance->break_start = Carbon::create($year, $month, $day, $breakStart->hour, $breakStart->minute)->format('Y-m-d H:i:s');
            }

            if (!empty($attendance->break_end)) {
                $breakEnd = Carbon::parse($attendance->break_end);
                $attendance->break_end = Carbon::create($year, $month, $day, $breakEnd->hour, $breakEnd->minute)->format('Y-m-d H:i:s');
            }
        }

        if (!empty($validated['clock_in'])) {
            $clockInDate = Carbon::parse($attendance->clock_in ?? now())->format('Y-m-d');
            $attendance->clock_in = Carbon::parse("$clockInDate {$validated['clock_in']}")->format('Y-m-d H:i:s');
        }

        if (!empty($validated['clock_out'])) {
            $clockOutDate = Carbon::parse($attendance->clock_out ?? now())->format('Y-m-d');
            $attendance->clock_out = Carbon::parse("$clockOutDate {$validated['clock_out']}")->format('Y-m-d H:i:s');
        }

        if (!empty($validated['break_start'])) {
            $breakStartDate = Carbon::parse($attendance->break_start ?? now())->format('Y-m-d');
            $attendance->break_start = Carbon::parse("$breakStartDate {$validated['break_start']}")->format('Y-m-d H:i:s');
        }

        if (!empty($validated['break_end'])) {
            $breakEndDate = Carbon::parse($attendance->break_end ?? now())->format('Y-m-d');
            $attendance->break_end = Carbon::parse("$breakEndDate {$validated['break_end']}")->format('Y-m-d H:i:s');
        }

        $attendance->remarks = $validated['remarks'] ?? $attendance->remarks;
        $attendance->save();

        AttendanceApproval::create([
            'attendance_id' => $attendance->id,
            'status' => '承認待ち',
            'user_id' => Auth::id(),
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'break_start' => $attendance->break_start,
            'break_end' => $attendance->break_end,
            'break_time' => $attendance->break_time,
            'work_time' => $attendance->work_time,
            'remarks' => $attendance->remarks,
        ]);

        return redirect()->route('general.attendance_detail', ['id' => $attendance->id]);
    }
}
