@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css') }}">
@endsection

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
@endphp

@section('content')
<h2 class="title">{{ $selectedDate->format('Y年n月j日') }}の勤怠</h2>

<div class="attendance-header">
    <div class="date-selector">
        <button class="date-button" onclick="changeDate(-1)">◀ 前日</button>
        <div class="calendar-container">
            <input class="this-date" type="date" id="datePicker" value="{{ $selectedDate->format('Y-m-d') }}" onchange="changeDateFromPicker()" style="display:block; width: auto;" />
        </div>
        <button class="date-button" onclick="changeDate(1)">翌日 ▶</button>
    </div>
</div>

<div class="attendance-container">
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name}}</td>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td>{{ $attendance->break_time ? $attendance->break_time . '分' : '' }}</td>
                <td>{{ $attendance->work_time ? $attendance->work_time . '分' : '' }}</td>
                <td><a class="attendance-detail" href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('datePicker').addEventListener('change', function() {
        window.location.href = "?date=" + this.value;
    });
});

function changeDate(offset) {
    let picker = document.getElementById('datePicker');
    let date = new Date(picker.value);
    date.setDate(date.getDate() + offset);
    picker.value = date.toISOString().slice(0, 10);
    window.location.href = "?date=" + picker.value;
}
</script>

@endsection
