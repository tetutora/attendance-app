@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance_list.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧</h2>

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
                    <td>{{ $request->remarks }}</td> {{-- 申請理由のカラムを変更 --}}
                    <td>{{ \Carbon\Carbon::parse($request->correction_requested_at)->format('Y/m/d') }}</td>
                    <td><a class="attendance-detail" href="{{ route('admin.attendance.detail', ['id' => $request->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
