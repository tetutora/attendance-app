<!-- resources/views/auth/verify.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>{{ __('Thanks for signing up!') }}</h2>
        <p>{{ __('Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}</p>
        
        <!-- メール再送信のリンク -->
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">{{ __('Resend Verification Email') }}</button>
        </form>
    </div>
@endsection
