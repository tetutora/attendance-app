@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance-detail.css') }}">
@endsection

@section('content')

@php
    $date = \Carbon\Carbon::parse($attendance->clock_in);
    $isApproved = session('attendance_updated');
@endphp

<h2>勤怠詳細</h2>

<div class="attendance-detail">
    <form action="{{ route('general.attendance.update', $attendance->id) }}" method="post">
    @csrf
        <div class="attendance-detail-container">
            <table class="attendance-table">
                <tr>
                    <th>名前</th>
                    <td class="name">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td class="date-row">
                        <span class="year-box">{{ $date->year }}年</span>
                        <span class="month-day-box">{{ $date->month }}月{{ $date->day }}日</span>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td class="time-row">
                        <span class="time-box editable" contenteditable="true" data-type="clock_in">{{ $attendance->clock_in ? $date->format('H:i') : '--:--' }}</span>
                        <input type="hidden" name="clock_in" class="hidden-input" value="{{ $attendance->clock_in ? $date->format('H:i') : '' }}">
                        〜
                        <span class="time-box editable" contenteditable="true" data-type="clock_out">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '--:--' }}</span>
                        <input type="hidden" name="clock_out" class="hidden-input" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                    </td>
                </tr>
                <tr>
                    <th>休憩時間</th>
                    <td class="time-row">
                        <span class="time-box editable" contenteditable="true" data-type="break_start">{{ $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '--:--' }}</span>
                        <input type="hidden" name="break_start" class="hidden-input" value="{{ $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '' }}">
                        〜
                        <span class="time-box editable" contenteditable="true" data-type="break_end">{{ $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '--:--' }}</span>
                        <input type="hidden" name="break_end" class="hidden-input" value="{{ $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '' }}">
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td><textarea name="remarks">{{ $attendance->remarks }}</textarea></td>
                </tr>
            </table>
        </div>
        <div class="button-container">
            @if (!$isApproved)
                <button type="submit" class="save-button">修正</button>
            @else
                <p class="approval-message">承認待ちのため、修正できません</p>
            @endif
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".time-box.editable").forEach(function (box) {
        box.addEventListener("blur", function () {
            let input = this.nextElementSibling;
            let value = this.textContent.trim();

            let regex = /^([01]?[0-9]|2[0-3]):([0-5]?[0-9])$/;
            if (regex.test(value)) {
                input.value = value;
            } else {
                this.textContent = input.value;
            }
        });
    });
});
</script>
@endsection
