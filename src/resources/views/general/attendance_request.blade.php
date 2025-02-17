@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance_list.css') }}">
@endsection

@section('content')
<h2 class="title">勤怠修正申請一覧</h2>

<div class="attendance-container">
    <table class="attendance-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
                <tr>
                    <td>{{ $request->status }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->clock_in)->format('Y/m/d') }}</td>
                    <td>{{ $request->remarks }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td><a class="attendance-detail" href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}">詳細</a></td>
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
