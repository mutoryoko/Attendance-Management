@extends('layouts.default')

@section('title', '申請一覧画面（一般ユーザー）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}" />
@endsection

@section('content')
<div class="content">
    @if (session('status'))
    <p class="session">{{ session('status') }}</p>
    @endif
    <div class="request__wrapper">
        <h1 class="title">申請一覧</h1>
    </div>
</div>
@endsection