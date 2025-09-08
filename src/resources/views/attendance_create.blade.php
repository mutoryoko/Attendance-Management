@extends('layouts.default')

@section('title', '出勤登録画面')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
    <div class="content">
        <form class="attendance-form" action="" method="POST">
            @csrf
            <p>勤務外</p>
            <livewire:clock />
            <div class="attendance-buttons">
                <button class="" type="submit">出勤</button>
                <button class="" type="submit">退勤</button>
                <button class="" type="submit">休憩入</button>
                <button class="" type="submit">休憩戻</button>
            </div>
            <p>お疲れ様でした。</p>
        </form>
    </div>
@endsection