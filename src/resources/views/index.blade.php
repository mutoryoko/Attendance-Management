@extends('layouts.default')

@section('title', '勤怠一覧画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endsection

@section('content')
<div class="content">
    @if (session('status'))
    <p class="session">{{ session('status') }}</p>
    @endif
    <div class="attendances__wrapper">
        <h1 class="title">勤怠一覧</h1>
        <div class="pagination">
        </div>
        <div class="attendances__table">
            <table class="attendances__table-inner">
                <tr class="table-row">
                    <th class="table-header">日付</th>
                    <th class="table-header">出勤</th>
                    <th class="table-header">退勤</th>
                    <th class="table-header">休憩</th>
                    <th class="table-header">合計</th>
                    <th class="table-header">詳細</th>
                </tr>

                @foreach ($period as $date)
                @php
                $attendance = $attendances->get($date->format('Y-m-d'));
                @endphp
                <tr class="table-row">
                    <td class="table-data">{{ $date->format('m/d') }}</td>
                    <td class="table-data">{{ $attendance->formatted_clock_in_time ?? '' }}</td>
                    <td class="table-data">{{ $attendance->formatted_clock_out_time ?? '' }}</td>
                    <td class="table-data">{{ $attendance->total_break_minutes ?? '' }}</td>
                    <td class="table-data">{{ $attendance->total_work_minutes ?? '' }}</td>
                    @if ($attendance)
                    <td class="table-data"><a class="detail__link" href="">詳細</a></td>
                    @endif
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection