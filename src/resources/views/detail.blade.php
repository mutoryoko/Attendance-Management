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
                        <p class="work-year">{{ $attendance->work_date->format('Y年') }}</p>
                        <p class="work-date">{{ $attendance->work_date->format('n月j日') }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">出勤・退勤</th>
                    <td class="table-data time-data">
                        <input class="time__input" type="time" name="requested_work_start" value="{{ old('requested_work_start', $attendance->formatted_work_start) ?? '' }}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="requested_work_end" value="{{ old('requested_work_end', $attendance->formatted_work_end) ?? '' }}" />

                        @if ($errors->hasAny(['requested_work_start', 'requested_work_end']))
                        <p class="error">{{ $errors->first('requested_work_start') ?: $errors->first('requested_work_end') }}</p>
                        @endif
                    </td>
                </tr>
                @php
                $breakLoopCount = max(count($breakTimes) + 1, 2);
                @endphp
                @for ($i = 0; $i < $breakLoopCount; $i++)
                    @php
                    $breakTime = $breakTimes[$i] ?? null;
                    @endphp
                <tr class="table-row">
                    <th class="table-header">休憩{{ $i + 1 }}</th>
                    <td class="table-data time-data">
                        <input class="time__input" type="time" name="breaks[{{ $i }}][start]" value="{{ old('breaks.' . $i . '.start', $breakTime ? $breakTime->formatted_break_start : '') }}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="breaks[{{ $i }}][end]" value="{{ old('breaks.' . $i . '.end', $breakTime ? $breakTime->formatted_break_end : '') }}" />

                        @if ($errors->hasAny(['breaks.*.start', 'breaks.*.end']))
                        <p class="error">{{ $errors->first('breaks.*.start') ?: $errors->first('breaks.*.end') }}</p>
                        @endif
                    </td>
                </tr>
                @endfor
                <tr class="table-row">
                    <th class="table-header">備考</th>
                    <td class="table-data note-data">
                        <textarea class="note-text" name="note" placeholder="電車遅延のため" rows="3">{{ old('note') }}</textarea>

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