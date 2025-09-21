@extends('layouts.default')

@section('title', '申請一覧画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}" />
@endsection

@section('content')
<div class="content">
    @if (session('status'))
    <p class="session">{{ session('status') }}</p>
    @endif
    <div class="request__wrapper">
        <h1 class="title">申請一覧</h1>
        <div class="tabs">
            <a class="tab-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('request', ['status' => 'pending']) }}">
                承認待ち
            </a>
            <a class="tab-link {{ $status === 'approved' ? 'active' : '' }}" href="{{ route('request', ['status' => 'approved']) }}">
                承認済み
            </a>
        </div>
        <div class="requests__table">
            <table class="requests__table-inner">
                <thead>
                    <tr class="table-row__header">
                        <th class="table-header">状態</th>
                        <th class="table-header">名前</th>
                        <th class="table-header">対象日時</th>
                        <th class="table-header">申請理由</th>
                        <th class="table-header">申請日時</th>
                        <th class="table-header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requestAttendances as $requestAttendance)
                    <tr class="table-row">
                        @if($status === 'approved')
                        <td class="table-data">承認済み</td>
                        @else
                        <td class="table-data">承認待ち</td>
                        @endif
                        <td class="table-data">
                            {{ $requestAttendance->applier->name ?? ''}}
                        </td>
                        <td class="table-data">
                            {{ $requestAttendance->attendance->work_date->format('Y/m/j') ?? '' }}
                        </td>
                        <td class="table-data">
                            {{ $requestAttendance->note ?? '' }}
                        </td>
                        <td class="table-data">
                            {{ $requestAttendance->created_at->format('Y/m/j') ?? '' }}
                        </td>
                        <td class="table-data">
                            @if(Auth::guard('web')->check())
                            <a class="detail__link" href="{{ route('attendance.detail', ['id' => $requestAttendance->attendance->id]) }}">詳細</a>
                            @elseif(Auth::guard('admin')->check())
                            <a class="detail__link" href="{{ route('admin.request.detail', ['attendance_correct_request' => $requestAttendance->id]) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection