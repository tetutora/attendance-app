@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance-detail.css') }}">
@endsection

@section('content')

@php
    $date = \Carbon\Carbon::parse($attendance->attendance_date);
@endphp

<h2>勤怠詳細</h2>

<div class="attendance-detail">
    <form method="POST" action="{{ route('general.attendance.update', ['id' => $attendance->id]) }}">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <div class="attendance-detail-container">
            <table class="attendance-table">
                <tr>
                    <th>名前</th>
                    <td class="name">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td class="date-row">
                        <!-- 年と月日の入力ボックス -->
                        <span class="year-box editable" contenteditable="true" data-type="year">{{ old('attendance_date', $date->year) }}年</span>
                        <input type="hidden" name="attendance_date" class="hidden-attendance-date" value="{{ $date->format('Y-m-d') }}">
                        <span class="month-day-box editable" contenteditable="true" data-type="month_day">{{ old('attendance_date', $date->month) }}月{{ old('attendance_date', $date->day) }}日</span>
                        @error('attendance_date')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td class="time-row">
                        <span class="time-box editable" contenteditable="true" data-type="clock_in">
                            {{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '--:--') }}
                        </span>
                        <input type="hidden" name="clock_in" value="{{ $attendance->clock_in }}">
                        〜
                        <span class="time-box editable" contenteditable="true" data-type="clock_out">
                            {{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '--:--') }}
                        </span>
                        <input type="hidden" name="clock_out" class="hidden-input"
                            value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
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
                    <td class="break-time-row">
                        @foreach ($breaks as $break)
                            <div class="break-entry">
                                <span class="time-box editable" contenteditable="true" data-type="break_in">
                                    {{ old("break_in[{$break->id}]", \Carbon\Carbon::parse($break->break_in)->format('H:i') ?? '--:--') }}
                                </span>
                                <input type="hidden" name="break_in[{{ $break->id }}]" class="hidden-input"
                                    value="{{ old("break_in[{$break->id}]", \Carbon\Carbon::parse($break->break_in)->format('H:i') ?? '') }}">
                                〜
                                <span class="time-box editable" contenteditable="true" data-type="break_out">
                                    {{ old("break_out[{$break->id}]", \Carbon\Carbon::parse($break->break_out)->format('H:i') ?? '--:--') }}
                                </span>
                                <input type="hidden" name="break_out[{{ $break->id }}]" class="hidden-input"
                                    value="{{ old("break_out[{$break->id}]", \Carbon\Carbon::parse($break->break_out)->format('H:i') ?? '') }}">
                            </div>
                        @endforeach
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
                        <textarea name="remarks">{{ old('remarks', $approval->remarks ?? '') }}</textarea>
                        @error('remarks')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>
        </div>

        <div class="button-container">
            @if ($isApprovalPending)
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
    const hiddenDateInput = document.querySelector('.hidden-attendance-date');
    const yearBox = document.querySelector('.year-box');
    const monthDayBox = document.querySelector('.month-day-box');

    const hiddenDate = hiddenDateInput.value;
    if (hiddenDate) {
        const [year, month, day] = hiddenDate.split('-');
        yearBox.textContent = `${year}年`;
        monthDayBox.textContent = `${month}月${day}日`;
    }

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

            const year = yearBox.textContent.replace('年', '');
            const monthDay = monthDayBox.textContent.replace('月', '-').replace('日', '');
            hiddenDateInput.value = `${year}-${monthDay}`;

            console.log('Updated Date:', hiddenDateInput.value);
        });
    });
});
</script>
@endsection
