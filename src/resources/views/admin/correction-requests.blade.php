@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/attendance_list.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧</h2>

<div class="status-buttons">
    <a href="{{ route('admin.correction-requests', ['status' => '承認待ち']) }}" class="btn {{ isset($status) && $status === '承認待ち' ? 'active' : '' }}">承認待ち</a>
    <a href="{{ route('admin.correction-requests', ['status' => '承認済み']) }}" class="btn {{ isset($status) && $status === '承認済み' ? 'active' : '' }}">承認済み</a>
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
                    <td>
                        {{-- 承認待ちの場合 --}}
                        @if($request instanceof \App\Models\AttendanceApproval)
                            {{ $request->user->name ?? '名前不明' }}
                        {{-- 承認済みの場合 --}}
                        @elseif($request instanceof \App\Models\AttendanceApproved)
                            {{ $request->attendance->attendanceApproval->user->name ?? '名前不明' }}
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($request->clock_in)->format('Y/m/d') }}</td>
                    <td>{{ $request->remarks ?? $request->attendance->remarks }}</td> {{-- 申請理由のカラムを変更 --}}
                    <td>{{ \Carbon\Carbon::parse($request->correction_requested_at ?? $request->created_at)->format('Y/m/d') }}</td>
                    <td><a class="attendance-detail" href="{{ route('admin.attendance-detail', ['attendance_correct_request' => $request->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
