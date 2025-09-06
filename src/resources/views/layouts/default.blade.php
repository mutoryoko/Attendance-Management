<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>@yield('title')</title>
        <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700&display=swap" rel="stylesheet">
        @yield('css')
    </head>
    <body>
        <header class="header">
            <div class="header__inner">
                <div class="header__logo">
                    <a href="{{ route('login') }}">
                        <img src="{{ asset('img/logo.svg') }}" alt="ロゴ" />
                    </a>
                </div>
                <nav class="header__nav">
                    <ul class="header__nav--items">
                        <li class="header__nav--item">
                            <a class="attendance__btn nav__link" href="">勤怠</a>
                        </li>
                        <li class="header__nav--item">
                            <a class="attendance-index__btn nav__link" href="">勤怠一覧</a>
                        </li>
                        <li class="header__nav--item">
                            <a class="request__btn nav__link" href="">申請</a>
                        </li>
                        <li class="header__nav--item">
                            <form action="{{ route('logout') }}" method="POST">
                            @csrf
                                <button class="logout__btn" type="submit">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
        <main>
            @yield('content')
        </main>
    </body>
</html>