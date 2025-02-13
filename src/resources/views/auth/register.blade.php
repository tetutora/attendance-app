@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register">
    <div class="register-title">
        <h1>会員登録</h1>
    </div>
    <form action="/register" method="post">
        @csrf
        <div class="register-form">
            <div class="form__item">
                <span class="form__title">名前</span>
                <div class="form__input">
                    <input class="form__input-content" type="text" name="name" value="{{ old('name') }}">
                </div>
            </div>
            <div class="form__item">
                <span class="form__title">メールアドレス</span>
                <div class="form__input">
                    <input class="form__input-content" type="text" name="email" value="{{ old('email') }}">
                </div>
            </div>
            <div class="form__item">
                <span class="form__title">パスワード</span>
                <div class="form__input">
                    <input class="form__input-content" type="password" name="password">
                </div>
            </div>
            <div class="form__item">
                <span class="form__title">確認用パスワード</span>
                <div class="form__input">
                    <input class="form__input-content" type="password" name="password_confirmation">
                </div>
            </div>
        </div>
        <div class="register__button">
            <button class="register__button-submit" type="submit">登録する</button>
        </div>
    </form>
    <div class="login__link">
        <a href="/login" class="login__link-button">ログインはこちら</a>
    </div>
</div>
@endsection
