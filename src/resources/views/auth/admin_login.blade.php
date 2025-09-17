@extends('layouts.default')

@section('title', 'ログイン画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
@endsection

@section('content')
<div class="content">
    @if (session('status'))
        <p class="session">{{ session('status') }}</p>
    @endif
    <h1 class="auth-title">管理者ログイン</h1>
    <form class="user-form" action="{{ route('admin.login') }}" method="POST">
        @csrf
        <div class="user-form__item">
            <label class="user-form__label"><div>メールアドレス</div></label>
            <input class="user-form__input" type="text" name="email" value="{{ old('email') }}" />
            @error('email')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="user-form__item">
            <label class="user-form__label"><div>パスワード</div></label>
            <input class="user-form__input" type="password" name="password" />
            @error('password')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <button class="login__btn" type="submit">管理者ログインする</button>
    </form>
</div>
@endsection