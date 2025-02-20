@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance_list.css') }}">
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
<h2 class="title">{{ $staff->name }}さんの勤怠</h2>

<div class="attendance-header">
    <div class="month-selector">
        <button class="month-button" onclick="changeMonth(-1)">◀ 前月</button>
        <div class="calendar-container">
            <input class="this-month" type="month" id="monthPicker" value="{{ $selectedMonth }}" onchange="changeMonthFromPicker()" style="display:block; width: auto;" />
        </div>
        <button class="month-button" onclick="changeMonth(1)">翌月 ▶</button>
    </div>
</div>

<div class="attendance-container">
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
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
                <td>
                    @if($attendance->clock_in)
                        {{ \Carbon\Carbon::parse($attendance->clock_in)->locale('ja')->format('n月j日(D)') }}
                    @endif
                </td>
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
document.getElementById('monthPicker').addEventListener('change', function() {
    window.location.href = "?month=" + this.value;
});

function changeMonth(offset) {
    let picker = document.getElementById('monthPicker');
    let date = new Date(picker.value + "-01");
    date.setMonth(date.getMonth() + offset);
    picker.value = date.toISOString().slice(0, 7);
    window.location.href = "?month=" + picker.value;
}
</script>

@endsection
