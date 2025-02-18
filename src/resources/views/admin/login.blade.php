@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="login">
    <div class="login-title">
        <h1>管理者ログイン</h1>
    </div>
    <form action="{{ route('admin.authenticate') }}" method="post">
        @csrf
        <div class="login-form">
            <div class="form__item">
                <span class="form__title">メールアドレス</span>
                <div class="form__input">
                    <input type="email" name="email" value="{{ old('email') }}" class="form__input-content">
                </div>
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__item">
                <span class="form__title">パスワード</span>
                <div class="form__input">
                    <input type="password" name="password" value="{{ old('password') }}" class="form__input-content">
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="login__button">
            <button class="login__button-submit" type="submit">ログイン</button>
        </div>
    </form>
</div>
@endsection