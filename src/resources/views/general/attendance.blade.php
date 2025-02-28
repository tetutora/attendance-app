@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance.css') }}">
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
    $date = now()->format('Y年m月d日');
    $dayOfWeek = $weekdays[now()->format('l')];
@endphp

<div class="attendance-container">
    <div class="status-tag">
        @if($attendance && $status)
            {{ $status->description }}
        @elseif(!$attendance)
            勤務外
        @endif
    </div>
    <div class="date">{{ $date }} {{ $dayOfWeek }}</div>
    <div class="time" id="current-time">{{ now()->format('H:i:s') }}</div>
    <div class="button-container">
        @if($attendance)
            @if($attendance->attendance_date == now()->toDateString())
                @if(is_null($attendance->clock_out)) <!-- 退勤前の状態 -->
                    @if(is_null($attendance->break_in)) <!-- 休憩開始ボタン -->
                        <form action="{{ route('general.attendance') }}" method="post">
                            @csrf
                            <input type="hidden" name="attendance_date" value="{{ now()->toDateString() }}">
                            <button type="submit" class="attendance-button break-start" name="action" value="break_in">休憩入</button>
                        </form>
                    @elseif(!is_null($attendance->break_in) && is_null($attendance->break_out)) <!-- 休憩中の状態 -->
                        <form action="{{ route('general.attendance') }}" method="post">
                            @csrf
                            <input type="hidden" name="attendance_date" value="{{ now()->toDateString() }}">
                            <button type="submit" class="attendance-button break-end" name="action" value="break_out">休憩戻</button>
                        </form>
                    @endif

                    <!-- 退勤ボタン -->
                    @if(is_null($attendance->break_in) || !is_null($attendance->break_out))
                        <form action="{{ route('general.attendance') }}" method="post">
                            @csrf
                            <input type="hidden" name="attendance_date" value="{{ now()->toDateString() }}">
                            <button type="submit" class="attendance-button clock-out" name="action" value="clock_out">退勤</button>
                        </form>
                    @endif
                @else
                    <p>お疲れ様でした。</p>
                @endif
            @endif
        @else
            <form action="{{ route('general.attendance') }}" method="post">
                @csrf
                <input type="hidden" name="attendance_date" value="{{ now()->toDateString() }}">
                <button type="submit" class="attendance-button clock-in">出勤</button>
            </form>
        @endif
    </div>
</div>

@endsection

@section('script')
<script>
    function updateTime() {
        const now = new Date();
        document.getElementById('current-time').textContent = now.toLocaleTimeString('ja-JP', {hour12: false});
    }
    setInterval(updateTime, 1000);
</script>
@endsection
