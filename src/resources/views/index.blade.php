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
    <h1 class="title">勤怠一覧</h1>
</div>
@endsection