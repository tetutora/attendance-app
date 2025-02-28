@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance-detail.css') }}">
@endsection

@section('content')
@php
    $date = \Carbon\Carbon::parse($attendance->clock_in);
    $isApproved = isset($attendanceApproved);
    // もしattendanceApprovalがnullならattendanceApprovedを使用する
    $currentApproval = $attendanceApproval ?? $attendanceApproved;
@endphp

<h2>勤怠詳細</h2>

<div class="attendance-detail">
    <form method="POST" action="{{ route('admin.attendance.approve', ['attendance_correct_request' => $currentApproval->id]) }}">
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
                        <span class="year-box editable">{{ $date->year }}年</span>
                        <input type="hidden" name="year" value="{{ $date->year }}">
                        <span class="month-day-box editable">{{ $date->month }}月{{ $date->day }}日</span>
                        <input type="hidden" name="month_day" value="{{ $date->month }}-{{ $date->day }}">
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td class="time-row">
                        <span class="time-box editable">{{ $attendance->clock_in ? $date->format('H:i') : '--:--' }}</span>
                        〜
                        <span class="time-box editable">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '--:--' }}</span>
                    </td>
                </tr>
                <tr>
                    <th>休憩時間</th>
                    <td class="time-row">
                        <span class="time-box editable">{{ $attendance->break_in ? \Carbon\Carbon::parse($attendance->break_in)->format('H:i') : '--:--' }}</span>
                        〜
                        <span class="time-box editable">{{ $attendance->break_out ? \Carbon\Carbon::parse($attendance->break_out)->format('H:i') : '--:--' }}</span>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks">{{ old('remarks', $currentApproval->remarks) }}</textarea>
                    </td>
                </tr>
            </table>
        </div>

        <div class="button-container">
            @if ($isApproved)
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
