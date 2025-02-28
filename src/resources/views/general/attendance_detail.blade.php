@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance-detail.css') }}">
@endsection

@section('content')

@php
    $date = \Carbon\Carbon::parse($attendance->clock_in);
    $approval = $attendance->approval;  // 承認データを取得
@endphp

<h2>勤怠詳細</h2>

<div class="attendance-detail">
    <form action="{{ route('general.attendance.update', $attendance->id) }}" method="post">
        @csrf
        <input type="hidden" name="status" value="承認待ち">
        <div class="attendance-detail-container">
            <table class="attendance-table">
                <tr>
                    <th>名前</th>
                    <td class="name">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td class="date-row">
                        <span class="year-box editable" contenteditable="true" data-type="year">{{ $date->year }}年</span>
                        <input type="hidden" name="attendance_date" class="hidden-work-date" value="{{ $date->format('Y-m-d') }}">
                        <span class="month-day-box editable" contenteditable="true" data-type="month_day">{{ $date->month }}月{{ $date->day }}日</span>
                        @error('attendance_date')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
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
                        @error('clock_in')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('clock_out')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <th>休憩時間</th>
                    <td class="time-row">
                        <span class="time-box editable" contenteditable="true" data-type="break_in">{{ $attendance->break_in ? \Carbon\Carbon::parse($attendance->break_in)->format('H:i') : '--:--' }}</span>
                        <input type="hidden" name="break_in" class="hidden-input" value="{{ $attendance->break_in ? \Carbon\Carbon::parse($attendance->break_in)->format('H:i') : '' }}">
                        〜
                        <span class="time-box editable" contenteditable="true" data-type="break_out">{{ $attendance->break_out ? \Carbon\Carbon::parse($attendance->break_out)->format('H:i') : '--:--' }}</span>
                        <input type="hidden" name="break_out" class="hidden-input" value="{{ $attendance->break_out ? \Carbon\Carbon::parse($attendance->break_out)->format('H:i') : '' }}">
                        @error('break_in')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error('break_out')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks">{{ old('remarks', $attendance->remarks) }}</textarea>
                        @error('remarks')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>
        </div>

        <div class="button-container">
            @if ($approval && $approval->status === '承認待ち')
                <p class="approval-message">・承認待ちのため修正できません</p>
            @else
                <button type="submit" class="save-button">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".editable").forEach(function (box) {
        box.addEventListener("blur", function () {
            let input = this.nextElementSibling;
            let value = this.textContent.trim();

            if (value) {
                if (box.classList.contains('year-box')) {
                    input.value = value.replace('年', '');
                } else if (box.classList.contains('month-day-box')) {
                    input.value = value.replace('日', '').replace('月', '-');
                } else if (box.classList.contains('time-box')) {
                    input.value = value;
                }
            }

            // 更新された日付をwork_dateとして隠しフィールドにセット
            const yearBox = document.querySelector('.year-box').textContent.replace('年', '');
            const monthDayBox = document.querySelector('.month-day-box').textContent.replace('月', '-').replace('日', '');
            document.querySelector('.hidden-work-date').value = `${yearBox}-${monthDayBox}`;
        });
    });
});
</script>
@endsection
