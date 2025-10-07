@extends('layouts.default')

@section('title', 'スタッフ別勤怠一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="attendances__wrapper">
        <h1 class="title">{{ $user->name }}さんの勤怠</h1>
        <div class="pagination">
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}">
                <img class="arrow" src="{{ asset('img/arrow-left.svg') }}" alt="左矢印">
                <span class="prev-month">前月</span>
            </a>
            <div class="month">
                <h2 class="month-text">{{ $currentMonth->format('Y/m') }}</h2>
            </div>
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}">
                <span class="next-month">翌月</span>
                <img class="arrow" src="{{ asset('img/arrow-right.svg') }}" alt="右矢印">
            </a>
        </div>
        <div class="attendances__table">
            <table class="attendances__table-inner">
                <thead>
                    <tr class="table-row__header">
                        <th class="table-header th__date">日付</th>
                        <th class="table-header">出勤</th>
                        <th class="table-header">退勤</th>
                        <th class="table-header">休憩</th>
                        <th class="table-header">合計</th>
                        <th class="table-header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($period as $date)
                    @php
                    $attendance = $attendances->get($date->format('Y-m-d'));
                    @endphp
                    <tr class="table-row">
                        <td class="table-data">{{ $date->isoFormat('MM/DD(ddd)') }}</td>
                        <td class="table-data">{{ $attendance->formatted_work_start ?? '' }}</td>
                        <td class="table-data">{{ $attendance->formatted_work_end ?? '' }}</td>
                        <td class="table-data">{{ $attendance->formatted_break_time ?? '' }}</td>
                        <td class="table-data">{{ $attendance->formatted_work_time ?? '' }}</td>
                        <td class="table-data">
                        @if ($date->lte(today()))
                            @if ($attendance)
                            <a class="detail__link" href="{{ route('admin.detail', ['id' => $attendance->id]) }}">詳細</a>
                            @else
                            {{-- 欠勤日の場合、日付をパラメータとする --}}
                            <a class="detail__link" href="{{ route('admin.detail', ['id' => $date->format('Y-m-d'), 'user' => $user->id]) }}">詳細</a>
                            @endif
                        @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form action="{{ route('admin.attendance.export', ['id' => $user->id]) }}?month={{ $currentMonth->format('Y-m') }}" method="POST">
            @csrf
            <div class="button__wrapper">
                <button class="export__btn">CSV出力</button>
            </div>
        </form>
    </div>
</div>
@endsection