@extends('layouts.default')

@section('title', '勤怠詳細画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="attendances__wrapper">
        <h1 class="title">勤怠詳細</h1>
        <form class="attendance__edit-form" action="" method="POST">
            @csrf
            @method('PUT')
            <table class="detail__table">
                <tr class="table-row">
                    <th class="table-header">名前</th>
                    <td class="table-data">
                        <p>{{ Auth::user()->name }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">日付</th>
                    <td class="table-data work-date">
                        <p>{{ $workDate->format('Y年') }}</p>
                        <p>{{ $workDate->format('n月j日') }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">出勤・退勤</th>
                    <td class="table-data time-data">
                        <input class="time__input" type="time" name="requested_work_start" value="{{ old('requested_work_start', $attendance->clock_in_time->format('H:i')) ?? '' }}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="requested_work_end" value="{{ old('requested_work_end', $attendance->clock_out_time->format('H:i')) ?? '' }}" />
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">休憩</th>
                    <td class="table-data time-data">
                        <input class="time__input" type="time" name="requested_break_start" value="{{ old('requested_break_start') ?? ''}}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="requested_break_end" value="{{ old('requested_break_end') ?? '' }}" />
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">備考</th>
                    <td class="table-data">
                        <textarea name="note">{{ old('note') }}</textarea>
                    </td>
                </tr>
            </table>
            <div class="edit__button">
                <input type="hidden" value="{{ $attendance->id }}">
                <button class="edit__btn--submit" type="submit">修正</button>
            </div>
        </form>
    </div>
</div>
@endsection