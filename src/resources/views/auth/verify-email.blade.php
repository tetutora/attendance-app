@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        @if (session('resent'))
                            <div class="alert alert-success bold-text" role="alert">
                                {{ __('新しい確認リンクがあなたのメールアドレスに送信されました。') }}
                            </div>
                        @endif

                        <p class="bold-text">{{ __('登録していただいたメールアドレスに認証メールを送付しました。') }}</p>
                        <p class="bold-text">{{ __('メール認証を完了してください。') }}</p>

                        <div class="button-container">
                            <a href="https://mailtrap.io" class="custom-button">{{ __('認証はこちらから') }}</a>
                        </div>

                        <div class="resend-link-container">
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="resend-link">
                                    {{ __('認証メールを再送する') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
