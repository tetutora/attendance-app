@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance_list.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧</h2>

<div class="status-buttons">
    <a href="{{ route('general.correction-requests', ['status' => '承認待ち']) }}" class="btn {{ $status === '承認待ち' ? 'active' : '' }}">承認待ち</a>
    <a href="{{ route('general.correction-requests', ['status' => '承認済み']) }}" class="btn {{ $status === '承認済み' ? 'active' : '' }}">承認済み</a>
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
                    <td>{{ $request->status }}</td>
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
