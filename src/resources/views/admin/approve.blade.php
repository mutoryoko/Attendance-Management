@extends('layouts.default')

@section('title', '修正申請承認画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="attendances__wrapper">
        <h1 class="title">勤怠詳細</h1>
        <form class="attendance__edit-form" action="{{ route('admin.approve', ['attendance_correct_request' => $request->id]) }}" method="POST">
            @csrf
            <table class="detail__table">
                <tr class="table-row">
                    <th class="table-header">名前</th>
                    <td class="table-data">
                        <p class="user-name">{{ $request->applier->name ?? '' }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">日付</th>
                    <td class="table-data work-date">
                        <p class="work-year">
                            {{ $request->attendance->work_date->format('Y年') ?? '' }}
                        </p>
                        <p class="work-date">
                            {{ $request->attendance->work_date->format('n月j日') ?? '' }}
                        </p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">出勤・退勤</th>
                    <td class="table-data time-data">
                        {{ $request->formatted_requested_work_start ?? '' }}
                        <span class="range">〜</span>
                        {{ $request->formatted_requested_work_end ?? '' }}
                    </td>
                </tr>
                @php
                $breakLoopCount = max(count($requestedBreakTimes) + 1, 2);
                @endphp
                @for ($i = 0; $i < $breakLoopCount; $i++)
                    @php
                    $requestedBreakTime = $requestedBreakTimes[$i] ?? null;
                    @endphp
                <tr class="table-row">
                    <th class="table-header">休憩{{ $i + 1 }}</th>
                    <td class="table-data time-data">
                        {{ $requestedBreakTime->formatted_requested_break_start ?? '' }}
                        <span class="range">〜</span>
                        {{ $requestedBreakTime->formatted_requested_break_end ?? '' }}
                    </td>
                </tr>
                @endfor
                <tr class="table-row">
                    <th class="table-header">備考</th>
                    <td class="table-data note-data">
                        {{ $request->note }}
                    </td>
                </tr>
            </table>
            <div class="approve__button">
                <button class="approve__btn--submit" type="submit">承認</button>
            </div>
        </form>
    </div>
</div>
@endsection