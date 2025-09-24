@extends('layouts.default')

@section('title', '勤怠詳細画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}" />
@endsection

{{-- 内容は管理者・一般ユーザー共通 --}}
@section('content')
<div class="content">
    <div class="attendances__wrapper">
        <h1 class="title">勤怠詳細</h1>
        @if(Auth::guard('web')->check())
        <form class="attendance__edit-form" action="{{ route('attendance.send', ['id' => $attendance->exists ? $attendance->id : $attendance->work_date->format('Y-m-d')]) }}" method="POST">
        @elseif(Auth::guard('admin')->check())
        <form class="attendance__edit-form" action="{{ route('admin.update', ['id' => $attendance->id]) }}" method="POST">
        @endif
            @csrf
            <table class="detail__table">
                <tr class="table-row">
                    <th class="table-header">名前</th>
                    <td class="table-data">
                        <p class="user-name">{{ $attendance->user->name }}</p>
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">日付</th>
                    <td class="table-data work-date">
                        <p class="work-year">{{ $attendance->work_date->format('Y年') ?? '' }}</p>
                        {{ $attendance->work_date->format('n月j日') ?? '' }}
                    </td>
                </tr>
                <tr class="table-row">
                    <th class="table-header">出勤・退勤</th>
                    <td class="table-data">
                        <input class="time__input" type="time" name="requested_work_start" value="{{ old('requested_work_start', $attendance->formatted_work_start) ?? '' }}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="requested_work_end" value="{{ old('requested_work_end', $attendance->formatted_work_end) ?? '' }}" />

                        @if ($errors->hasAny(['requested_work_start', 'requested_work_end']))
                        <p class="error">
                            {{ $errors->first('requested_work_start') ?: $errors->first('requested_work_end') }}
                        </p>
                        @endif
                    </td>
                </tr>
                @php
                $breakLoopCount = max(count($attendance->breakTimes) + 1, 2);
                @endphp
                @for ($i = 0; $i < $breakLoopCount; $i++)
                    @php
                    $breakTime = $attendance->breakTimes[$i] ?? null;
                    @endphp
                <tr class="table-row">
                    <th class="table-header">休憩{{ $i + 1 }}</th>
                    <td class="table-data">
                        <input class="time__input" type="time" name="breaks[{{ $i }}][start]" value="{{ old('breaks.' . $i . '.start', $breakTime ? $breakTime->formatted_break_start : '') }}" />
                        <span class="range">〜</span>
                        <input class="time__input" type="time" name="breaks[{{ $i }}][end]" value="{{ old('breaks.' . $i . '.end', $breakTime ? $breakTime->formatted_break_end : '') }}" />

                        @error('breaks.' . $i . '.end')
                            <p class="error">{{ $message }}</p>
                        @else
                            @error('breaks.' . $i . '.start')
                                <p class="error">{{ $message }}</p>
                            @enderror
                        @enderror
                    </td>
                </tr>
                @endfor
                <tr class="table-row">
                    <th class="table-header">備考</th>
                    <td class="table-data note-data">
                        <textarea class="note-text" name="note" placeholder="電車遅延のため" rows="3">{{ old('note', $attendance->note ?? '') }}</textarea>

                        @error('note')
                        <p class="error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>
            <div class="button__wrapper">
                @if ($attendance->pendingRequest)
                <p class="alert">※承認待ちのため修正はできません</p>
                @else
                <button class="edit__btn" type="submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection