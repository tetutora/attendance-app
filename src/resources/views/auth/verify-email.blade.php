@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('メールアドレスの確認') }}</div>

                    <div class="card-body">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('新しい確認リンクがあなたのメールアドレスに送信されました。') }}
                            </div>
                        @endif

                        {{ __('続行する前に、確認リンクが含まれたメールを確認してください。') }}
                        {{ __('メールが届いていない場合') }},
                        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('もう一度確認メールを送信する') }}</button>.
                        </form>

                        <div class="mt-3">
                            <a href="https://mailtrap.io/inboxes" class="btn btn-primary">{{ __('Mailtrapに移動') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
