@extends('layouts.default')

@section('title', '勤怠一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}" />
@endsection

@section('content')
<div class="content">
    @if (session('status'))
    <p class="session">{{ session('status') }}</p>
    @elseif(session('error'))
    <p class="session-error">{{ session('error') }}</p>
    @endif

    <div class="attendances__wrapper admin-attendances__wrapper">
        <h1 class="title">
            <span class="title-date">{{ $currentDay->format('Y年n月j日') }}</span>の勤怠
        </h1>
        {{-- ページネーション --}}
        <div class="pagination">
            <a href="{{ route('admin.index', ['date' => $prevDay]) }}">
                <img class="arrow" src="{{ asset('img/arrow-left.svg') }}" alt="左矢印">
                <span class="prev-day">前日</span>
            </a>
            <div class="date">
                <h2 class="date-text">{{ $currentDay->format('Y/m/d') }}</h2>
            </div>
            <a href="{{ route('admin.index', ['date' => $nextDay]) }}">
                <span class="next-day">翌日</span>
                <img class="arrow" src="{{ asset('img/arrow-right.svg') }}" alt="右矢印">
            </a>
        </div>
        {{-- 勤怠テーブル --}}
        <div class="attendances__table">
            <table class="attendances__table-inner">
                <thead>
                    <tr class="table-row__header">
                        <th class="table-header th__name">名前</th>
                        <th class="table-header">出勤</th>
                        <th class="table-header">退勤</th>
                        <th class="table-header">休憩</th>
                        <th class="table-header">合計</th>
                        <th class="table-header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    @php
                    $attendance = $user->attendances->first();
                    @endphp
                    <tr class="table-row">
                        <td class="table-data">{{ $user->name }}</td>
                        <td class="table-data">{{ $attendance->formatted_work_start ?? '' }}</td>
                        <td class="table-data">{{ $attendance->formatted_work_end ?? '' }}</td>
                        <td class="table-data">{{ $attendance->formatted_break_time ?? '' }}</td>
                        <td class="table-data">{{ $attendance->formatted_work_time ?? '' }}</td>
                        <td class="table-data">
                            @if ($currentDay->lte(today()))
                                @if($attendance)
                                <a class="detail__link" href="{{ route('admin.detail', ['id' => $attendance->id, 'redirect_to' => url()->full()]) }}">
                                    詳細
                                </a>
                                @else
                                <a class="detail__link" href="{{ route('admin.detail', ['id' => $currentDay->format('Y-m-d'), 'user' => $user->id, 'redirect_to' => url()->full()] ) }}">
                                    詳細
                                </a>
                                @endif
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