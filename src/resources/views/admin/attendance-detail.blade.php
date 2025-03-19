@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance-detail.css') }}">
@endsection

@section('content')
@php
    $date = \Carbon\Carbon::parse($attendance->clock_in);
    $currentApproval = $attendanceApproval ?? $attendanceApproved;
    $isApproved = isset($currentApproval) && $currentApproval->approval_status_id == 2;
@endphp

<h2>勤怠詳細</h2>

<div class="attendance-detail">
    <form method="POST" action="{{ route('admin.attendance.approve', ['attendance_correct_request' => $currentApproval->id]) }}">
        @csrf
        <input type="hidden" name="attendance_correct_request" value="{{ $currentApproval->id }}">
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
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                    〜
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
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
                    @if ($breaks->isEmpty())
                        <div class="break-entry">
                            <input type="time" name="break_in[]" value="{{ old('break_in.0', '--:--') }}">
                            〜
                            <input type="time" name="break_out[]" value="{{ old('break_out.0', '--:--') }}">
                        </div>
                    @else
                        @foreach ($breaks as $index => $break)
                            <div class="break-entry">
                                <input type="time" name="break_in[]" value="{{ old("break_in.{$index}", $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '--:--') }}">
                                〜
                                <input type="time" name="break_out[]" value="{{ old("break_out.{$index}", $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '--:--') }}">
                            </div>
                            @error("break_in.{$index}")
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            @error("break_out.{$index}")
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endforeach
                    @endif
                </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="remarks">{{ old('remarks', $currentApproval->remarks) }}</textarea>
                        @error('remarks')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>
        </div>

        <div class="button-container">
            @if ($isApproved)
                <p class="approved-message">承認済み</p>
            @else
                <button type="submit" class="approval-button">承認</button>
            @endif
        </div>
    </form>
</div>

@endsection
