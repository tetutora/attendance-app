@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance_detail.css') }}">
@endsection

@section('content')

@php
    $weekdays = [
        "Sunday" => "(日)",
        "Monday" => "(月)",
        "Tuesday" => "(火)",
        "Wednesday" => "(水)",
        "Thursday" => "(木)",
        "Friday" => "(金)",
        "Saturday" => "(土)"
    ];
    $date = \Carbon\Carbon::parse($attendance->clock_in)->format('Y年m月d日');
    $dayOfWeek = $weekdays[\Carbon\Carbon::parse($attendance->clock_in)->format('l')];
@endphp

<div class="attendance-detail-container">
    <h2>勤怠詳細</h2>

    @if(session('success'))
        <p class="success-message">{{ session('success') }}</p>
    @endif

    <form action="{{ route('general.attendance.update', $attendance->id) }}" method="post">
        @csrf
        <table class="attendance-table">
            <tr>
                <th>ユーザー名</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ $date }} {{ $dayOfWeek }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="editable-time">
                    <span class="display-time" id="work-time-display">
                        <span id="clock-in-display">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '--:--' }}</span>
                        〜
                        <span id="clock-out-display">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '--:--' }}</span>
                    </span>
                    <input type="time" name="clock_in" id="clock-in" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}" class="hidden-input">
                    <input type="time" name="clock_out" id="clock-out" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}" class="hidden-input">
                </td>
            </tr>
            <tr>
                <th>休憩時間</th>
                <td class="editable-time">
                    <span class="display-time" id="break-time-display">
                        <span id="break-start-display">{{ $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '--:--' }}</span>
                        〜
                        <span id="break-end-display">{{ $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '--:--' }}</span>
                    </span>
                    <input type="time" name="break_start" id="break-start" value="{{ $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '' }}" class="hidden-input">
                    <input type="time" name="break_end" id="break-end" value="{{ $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '' }}" class="hidden-input">
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td><textarea name="remarks">{{ $attendance->remarks }}</textarea></td>
            </tr>
        </table>

        <div class="button-container">
            <button type="submit" class="save-button">修正</button>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function setupEditableTime(displayId, inputStartId, inputEndId) {
        let timeDisplay = document.getElementById(displayId);
        let inputStart = document.getElementById(inputStartId);
        let inputEnd = document.getElementById(inputEndId);
        let startDisplay = document.getElementById(inputStartId + "-display");
        let endDisplay = document.getElementById(inputEndId + "-display");

        timeDisplay.addEventListener("click", function() {
            timeDisplay.style.display = "none";
            inputStart.classList.remove("hidden-input");
            inputEnd.classList.remove("hidden-input");
        });

        inputStart.addEventListener("change", function() {
            startDisplay.textContent = this.value || "--:--";
        });

        inputEnd.addEventListener("change", function() {
            endDisplay.textContent = this.value || "--:--";
        });
    }

    // 出勤・退勤時間
    setupEditableTime("work-time-display", "clock-in", "clock-out");
    // 休憩時間
    setupEditableTime("break-time-display", "break-start", "break-end");
});
</script>

<style>
.hidden-input {
    display: none;
}

.display-time {
    cursor: pointer;
    font-weight: bold;
}
</style>

@endsection
