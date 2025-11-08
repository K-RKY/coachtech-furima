<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH フリマ</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>


<body>
    <header>
        <a class="header-logo" href="{{ route('items.index') }}">
            <img class="header-logo__image" src="/storage/images/logo.svg" alt="COACHTECH">
        </a>

        @if (!request()->is('login') && !request()->is('register') && !request()->is('verify-notice'))
        <form class="search-form" action="{{ route('items.index') }}" method="GET">
            @csrf
            <input class="search-form__input" type="text" name="keyword" placeholder="なにをお探しですか？">
        </form>

        <nav class="header-nav">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                @auth
                <button class="logout-button" type="submit">ログアウト</button>
                @else
                <a class="header-nav__link" href="/login">ログイン</a>
                @endauth
            </form>
            <a class="header-nav__link" href="{{ route('mypage.index') }}">マイページ</a>
            <form action="{{ route('sell.create') }}" method="GET">
                @csrf
                <button class="header-nav__button" type="submit">出品</button>
            </form>
        </nav>
        @endif
    </header>
    <main>
        @yield('content')
    </main>

    @if (session('status'))
    <script>
        alert("{{ session('status') }}");
    </script>
    @endif

    @if (session('error'))
    <script>
        alert("エラー: {{ session('error') }}");
    </script>
    @endif

</body>

</html>