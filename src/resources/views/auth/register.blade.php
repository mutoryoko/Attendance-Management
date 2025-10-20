@extends('layouts.default')

@section('title', '会員登録画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}" />
@endsection

@section('content')
<div class="content">
    @if (session('status'))
    <p class="session">{{ session('status') }}</p>
    @elseif(session('error'))
    <p class="session-error">{{ session('error') }}</p>
    @endif

    <h1 class="auth-title">会員登録</h1>
    <form class="user-form" action="{{ route('register') }}" method="POST">
        @csrf
        <div class="user-form__item">
            <label for="user_name" class="user-form__label"><div>名前</div></label>
            <input id="user_name" class="user-form__input" type="text" name="name" value="{{ old('name') }}" />
            @error('name')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="user-form__item">
            <label for="email" class="user-form__label"><div>メールアドレス</div></label>
            <input id="email" class="user-form__input" type="text" name="email" value="{{ old('email') }}" />
            @error('email')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <div class="user-form__item">
            <label for="password" class="user-form__label"><div>パスワード</div></label>
            <input id="password" class="user-form__input" type="password" name="password" />
            {{-- ここでは「一致しません」エラーは表示しない --}}
            @error('password')
            @if (!str_contains($message, '一致しません'))
            <p class="error">{{ $message }}</p>
            @endif
            @enderror
        </div>
        <div class="user-form__item">
            <label for="pw-confirm" class="user-form__label"><div>パスワード確認</div></label>
            <input id="pw-confirm" class="user-form__input" type="password" name="password_confirmation" />
            @error('password')
            @if (str_contains($message, '一致しません'))
            <p class="error">{{ $message }}</p>
            @endif
            @enderror
        </div>

        <button class="register__btn" type="submit">登録する</button>
        <a class="user-form__link" href="{{ route('login') }}">ログインはこちら</a>
    </form>
</div>
@endsection