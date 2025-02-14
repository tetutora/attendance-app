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
    $dayOfWeek = $weekdays[now()->format('l')]; // 日本語の曜日に変換
@endphp

<div class="attendance-container">
    <div class="status-tag">勤務外</div>
    <div class="date">{{ $date }} {{ $dayOfWeek}}</div>
    <div class="time" id="current-time">{{ now()->format('H:i:s') }}</div>
    <form action="/attendance/list" method="post">
        @csrf
        <button type="submit" class="attendance-button">出勤</button>
    </form>
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