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
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
