@extends('layouts.default')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css')}}" />
@endsection

@section('content')
    <div class="content">
        <h1 class="title">ログイン</h1>
        <form class="user-form" action="" method="POST">
            @csrf
            <div class="user-form__item">
                <label class="user-form__label"><div>メールアドレス</div></label>
                <input class="user-form__input" type="text" name="email" value="{{ old('email') }}" />
                {{-- @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror --}}
            </div>
            <div class="user-form__item">
                <label class="user-form__label"><div>パスワード</div></label>
                <input class="user-form__input" type="password" name="password" />
                {{-- @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror --}}
            </div>

            <button class="login__btn" type="submit">ログインする</button>
            <a class="user-form__link" href="{{ route('registerForm') }}">会員登録はこちら</a>
        </form>
    </div>
@endsection