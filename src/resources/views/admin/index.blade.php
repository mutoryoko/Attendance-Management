@extends('layouts.default')

@section('title', '勤怠一覧画面（管理者）')

@section('css')
    <link rel="stylesheet" href="" />
@endsection

@section('content')
    <div class="content">
        @if (session('status'))
        <p class="session">{{ session('status') }}</p>
        @endif
        勤怠一覧画面（管理者）
    </div>
@endsection