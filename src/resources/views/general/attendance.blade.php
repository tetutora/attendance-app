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
    @if(!$attendance)
        <div class="status-tag">勤務外</div>
        <form action="{{ route('general.attendance') }}" method="post">
            @csrf
            <button type="submit" class="attendance-button">出勤</button>
        </form>
    @elseif($attendance && is_null($attendance->clock_out))
        <div class="status-tag">
            @if(is_null($attendance->break_start))
                出勤中
            @elseif(!is_null($attendance->break_start) && is_null($attendance->break_end))
                休憩中
            @else
                出勤中
            @endif
        </div>
        @if(is_null($attendance->break_start) || !is_null($attendance->break_end))
            <form action="{{ route('general.attendance') }}" method="post">
                @csrf
                <button type="submit" class="attendance-button" name="action" value="clock_out">退勤</button>
            </form>
        @endif
        @if(is_null($attendance->break_start) || !is_null($attendance->break_end))
            <form action="{{ route('general.attendance') }}" method="post">
                @csrf
                <button type="submit" class="attendance-button" name="action" value="break_start">休憩入</button>
            </form>
        @elseif(!is_null($attendance->break_start) && is_null($attendance->break_end))
            <form action="{{ route('general.attendance') }}" method="post">
                @csrf
                <button type="submit" class="attendance-button" name="action" value="break_end">休憩戻</button>
            </form>
        @endif
    @elseif($attendance && $attendance->clock_out)
        <div class="status-tag">退勤済</div>
        <p>お疲れ様でした。</p>
    @endif
    <div class="date">{{ $date }} {{ $dayOfWeek }}</div>
    <div class="time" id="current-time">{{ now()->format('H:i:s') }}</div>
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
