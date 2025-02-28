@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance-detail.css') }}">
@endsection

@section('content')

@php
    $date = \Carbon\Carbon::parse($attendance->clock_in);
    $approval = $attendance->approval;
@endphp

<h2>勤怠詳細</h2>

<div class="attendance-detail">
    <form method="POST" action="{{ route('admin.attendance.approve', ['attendance_correct_request' => $attendanceApproval->id]) }}">
        @csrf
        <input type="hidden" name="status" value="承認">
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
                        <input type="hidden" name="year" class="hidden-input" value="{{ $date->year }}">

                        <span class="month-day-box editable" contenteditable="true" data-type="month_day">{{ $date->month }}月{{ $date->day }}日</span>
                        <input type="hidden" name="month_day" class="hidden-input" value="{{ $date->month }}-{{ $date->day }}">
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
                    <td>
                        <textarea name="remarks">{{ old('remarks', $attendance->remarks) }}</textarea>
                    </td>
                </tr>
            </table>
        </div>

        <div class="button-container">
            @if ($approval && $approval->status === '承認済み')
                <p class="approval-message">・承認済み</p>
            @else
                <button type="submit" class="approval-button">承認</button>
            @endif
        </div>
    </form>

    @if(session('status') === '承認済み')
        <p class="approval-message">・承認済み</p>
    @endif
</div>
@endsection
