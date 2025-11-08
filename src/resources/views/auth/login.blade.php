@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-container__inner">
        <h1 class="auth-container__title">ログイン</h1>
        <form class="auth-form" action="{{ route('login') }}" method="POST" novalidate>
            @csrf
            <label class="auth-form__label">メールアドレス</label>
            <input class="auth-form__input" type="email" name="email" value="{{ old('email') }}">
            <span class="error-message">
                @error('email') {{ $message }} @else &nbsp; @enderror
            </span>
            <label class="auth-form__label">パスワード</label>
            <input class="auth-form__input" type="password" name="password">
            <span class="error-message">
                @error('password') {{ $message }} @else &nbsp; @enderror
            </span>
            <button class="auth-button" type="submit">ログインする</button>
        </form>
        <a class="auth-link" href="/register">会員登録はこちら</a>
    </div>
</div>
@endsection