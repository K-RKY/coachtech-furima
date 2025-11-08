@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-container__inner">
        <h1 class="auth-container__title">会員登録</h1>
        <form class="auth-form" action="{{ route('register') }}" method="POST" novalidate>
            @csrf
            <label class="auth-form__label">ユーザー名</label>
            <input class="auth-form__input" type="text" name="name" value="{{ old('name') }}">
            <span class="error-message">
                @error('name') {{ $message }} @else &nbsp; @enderror
            </span>
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
            <label class="auth-form__label">確認用パスワード</label>
            <input class="auth-form__input" type="password" name="password_confirmation">
            <span class="error-message"></span>
            <button class="auth-button" type="submit">登録する</button>
        </form>
        <a class="auth-link" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection