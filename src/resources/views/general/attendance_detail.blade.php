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
                    <span class="year-box">{{ old('attendance_date', $date->year) }}年</span>
                    <span class="month-day-box">{{ old('attendance_date', $date->month) }}月{{ old('attendance_date', $date->day) }}日</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td class="time-row">
                    <!-- 時刻を通常のinputタグに変更 -->
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
                            <input type="time" name="break_in[]" value="{{ old('break_in', '--:--') }}">
                            〜
                            <input type="time" name="break_out[]" value="{{ old('break_out', '--:--') }}">
                        </div>
                    @else
                        @foreach ($breaks as $index => $break)
                            <div class="break-entry">
                                <input type="time" name="break_in[]" value="{{ old("break_in_{$index}", $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '--:--') }}">
                                〜
                                <input type="time" name="break_out[]" value="{{ old("break_out_{$index}", $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '--:--') }}">
                            </div>
                            @error("break_in_{$index}")
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                            @error("break_out_{$index}")
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        @endforeach
                    @endif
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