<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <title>Attendance</title>
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" class="header__logo">
        </div>
        <ul class="header-nav">
        @if (Auth::check())
            @if (Auth::user()->role === 'admin')
                <li class="header-nav__item">
                    <a class="header-nav__button" href="/admin/attendance/list">勤怠一覧</a>
                    <a class="header-nav__button" href="/admin/dashboard">ダッシュボード</a>
                    <form class="logout__form" action="/admin/logout" method="post">
                        @csrf
                        <button class="logout__button">ログアウト</button>
                    </form>
                </li>
            @else <!-- 一般ユーザー用メニュー -->
                <li class="header-nav__item">
                    <a class="header-nav__button" href="/attendance">勤怠</a>
                    <a class="header-nav__button" href="/attendance/list">勤怠一覧</a>
                    <a class="header-nav__button" href="/stamp_correction_request/list">申請</a>
                    <form class="logout__form" action="/logout" method="post">
                        @csrf
                        <button class="logout__button">ログアウト</button>
                    </form>
                </li>
            @endif
        @endif
        </ul>
    </header>
    <main>
        @yield('content')
        @yield('script')
    </main>
</body>
</html>
