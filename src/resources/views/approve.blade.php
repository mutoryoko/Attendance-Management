@extends('layouts.default')

@section('title', '申請詳細画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="attendances__wrapper">
        <h1 class="title">勤怠詳細</h1>
        <form class="attendance__edit-form" action="{{ route('admin.approve', ['attendance_correct_request' => $requestAttendance->id]) }}" method="POST">
            @csrf
            <table class="detail__table">
                <tr class="table-row">
                    <th class="table-header">名前</th>
                    <td class="table-data">
                        <p class="applier-name">{{ $requestAttendance->applier->name ?? '' }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">日付</th>
                    <td class="table-data work-date">
                        <p class="year">
                            {{ $requestAttendance->attendance->work_date->format('Y年') ?? '' }}
                        </p>
                        <p class="date">
                            {{ $requestAttendance->attendance->work_date->format('n月j日') ?? '' }}
                        </p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">出勤・退勤</th>
                    <td class="table-data time-data">
                        {{ $requestAttendance->formatted_requested_work_start ?? '' }}
                        <span class="range">〜</span>
                        {{ $requestAttendance->formatted_requested_work_end ?? '' }}
                    </td>
                </tr>
                @php
                $breakLoopCount = count($requestBreakTimes);
                @endphp
                @for ($i = 0; $i < $breakLoopCount; $i++)
                    @php
                    $requestBreakTime = $requestBreakTimes[$i] ?? null;
                    @endphp
                <tr class="table-row">
                    <th class="table-header">休憩{{ $i + 1 }}</th>
                    <td class="table-data time-data">
                        {{ $requestBreakTime->formatted_requested_break_start ?? '' }}
                        <span class="range">〜</span>
                        {{ $requestBreakTime->formatted_requested_break_end ?? '' }}
                    </td>
                </tr>
                @endfor
                <tr class="table-row">
                    <th class="table-header">備考</th>
                    <td class="table-data">
                        {{ $requestAttendance->note }}
                    </td>
                </tr>
            </table>
            <div class="approve__button">
                {{-- 一般ユーザー用ボタン --}}
                @if ($attendance->latestRequest->is_approved)
                <button disabled>承認済み</button>
                @else
                <p class="alert">※承認待ちのため修正はできません</p>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection