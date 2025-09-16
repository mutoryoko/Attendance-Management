@extends('layouts.default')

@section('title', '勤怠詳細画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" />
@endsection

@section('content')
<div class="content">
    <div class="attendances__wrapper">
        <h1 class="title">勤怠詳細</h1>
        <form class="attendance__edit-form" action="{{ route('attendance.send', ['id' => $attendance->id]) }}" method="POST">
            @csrf
            <table class="detail__table">
                <tr class="table-row">
                    <th class="table-header">名前</th>
                    <td class="table-data">
                        <p class="user-name">{{ Auth::user()->name }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">日付</th>
                    <td class="table-data work-date">
                        <p class="work-year">{{ $workDate->format('Y年') }}</p>
                        <p class="work-date">{{ $workDate->format('n月j日') }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">出勤・退勤</th>
                    <td class="table-data time-data">
                        <input class="time__input" type="time" name="requested_work_start" value="{{ old('requested_work_start', $attendance->clock_in_time->format('H:i')) ?? '' }}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="requested_work_end" value="{{ old('requested_work_end', $attendance->clock_out_time->format('H:i')) ?? '' }}" />
                        @error('requested_work_start')
                        <p class="error">{{ $message }}</p>
                        @enderror
                        @error('requested_work_end')
                        <p class="error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">休憩</th>
                    <td class="table-data time-data">
                        <input class="time__input" type="time" name="requested_break_start" value="{{ old('requested_break_start') ?? ''}}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="requested_break_end" value="{{ old('requested_break_end') ?? '' }}" />
                        @error('requested_break_start')
                        <p class="error">{{ $message }}</p>
                        @enderror
                        @error('requested_break_end')
                        <p class="error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">備考</th>
                    <td class="table-data note-data">
                        <textarea class="note-text" name="note">{{ old('note') }}</textarea>
                        @error('note')
                        <p class="error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>
            <div class="edit__button">
                <button class="edit__btn--submit" type="submit">修正</button>
            </div>
        </form>
    </div>
</div>
@endsection