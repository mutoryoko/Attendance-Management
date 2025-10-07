@extends('layouts.default')

@section('title', '出勤登録画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
<div class="content">
    @if (session('status'))
    <p class="session">{{ session('status') }}</p>
    @endif

    <div class="attendance-form">
        <div class="working-status">
            @switch($status)
                @case('not_clocked_in')
                    <p class="status">勤務外</p>
                    @break
                @case('working')
                    <p class="status">出勤中</p>
                    @break
                @case('on_break')
                    <p class="status">休憩中</p>
                    @break
                @case('clocked_out')
                    <p class="status">退勤済</p>
                    @break
            @endswitch
        </div>

        <livewire:clock />

        <div class="attendance-buttons">
            @switch($status)
                @case('not_clocked_in')
                {{-- 出勤前 --}}
                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <button class="clock__btn" type="submit" name="action" value="clock_in">出勤</button>
                    </form>
                    @break
                @case('working')
                {{-- 出勤中 --}}
                    <div class="while-working__buttons">
                        <form action="{{ route('attendance.store') }}" method="POST">
                            @csrf
                            <button class="clock__btn" type="submit" name="action" value="clock_out">退勤</button>
                        </form>
                        <form action="{{ route('attendance.store') }}" method="POST">
                            @csrf
                            <button class="break__btn" type="submit" name="action" value="break_start">休憩入</button>
                        </form>
                    </div>
                    @break
                @case('on_break')
                {{-- 休憩中 --}}
                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <button class="break__btn" type="submit" name="action" value="break_end">休憩戻</button>
                    </form>
                    @break
                @case('clocked_out')
                {{-- 退勤後 --}}
                    <p class="message">お疲れ様でした。</p>
                    @break
            @endswitch
        </div>
    </div>
</div>
@endsection