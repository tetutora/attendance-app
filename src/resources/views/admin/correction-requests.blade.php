@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/correction-request.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧</h2>

<div class="status-buttons">
    <a href="{{ route('admin.correction-requests', ['approval_status_id' => 1]) }}" class="btn {{ (int)$approvalStatusId === 1 ? 'active' : '' }}">承認待ち</a>
    <a href="{{ route('admin.correction-requests', ['approval_status_id' => 2]) }}" class="btn {{ (int)$approvalStatusId === 2 ? 'active' : '' }}">承認済み</a>
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
                    <td>
                        @if($request instanceof \App\Models\AttendanceApproval)
                            {{-- approval_status_id が 1 の場合は「承認待ち」を表示 --}}
                            {{ $request->approval_status_id === 1 ? '承認待ち' : '' }}
                        @elseif($request instanceof \App\Models\AttendanceApproved)
                            {{-- approval_status_id が 2 の場合は「承認済み」を表示 --}}
                            {{ $request->approval_status_id === 2 ? '承認済み' : '' }}
                        @endif
                    </td>
                    <td>{{ $request->user->name ?? '名前不明' }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance_date)->format('Y/m/d') ?? '' }}</td>
                    <td>{{ $request->remarks ?? $request->attendance->remarks }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->correction_requested_at ?? $request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a class="attendance-detail" 
                        href="{{ route('admin.attendance-detail', ['attendance_correct_request' => $request->id]) }}">
                        詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
