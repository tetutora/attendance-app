@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance_list.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧</h2>

<div class="status-buttons">
    <a href="{{ route('general.correction-requests', ['approval_status_id' => 1]) }}" class="btn {{ $approvalStatusId == 1 ? 'active' : '' }}">承認待ち</a>
    <a href="{{ route('general.correction-requests', ['approval_status_id' => 2]) }}" class="btn {{ $approvalStatusId == 2 ? 'active' : '' }}">承認済み</a>
</div>

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
                    <td>{{ $request->approval_status_id == 1 ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $request->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->remarks }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td><a class="attendance-detail" href="{{ route('general.attendance-detail', ['id' => $request->attendance_id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
