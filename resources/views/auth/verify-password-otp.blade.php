@extends('layouts.app')
@section('title', 'Verify your email - Surat NX')
@section('content')
<main class="auth auth-page auth-recovery-page">
    <section class="auth-art">
        <div>
            <h2>One more step</h2>
            <p>Use the six-digit code in your inbox to securely continue.</p>
        </div>
    </section>
    <section class="auth-form">
        <div class="auth-card">
            <h1>Verify your email</h1>
            <p>We sent a one-time code to <strong>{{ $email }}</strong>. It expires in 10 minutes.</p>
            @if(session('status'))<div class="alert ok" role="status">{{ session('status') }}</div>@endif
            <form method="post" action="{{ route('password.otp.verify') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="field">
                    <label for="reset-otp">Verification code</label>
                    <input id="reset-otp" type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" placeholder="Enter 6-digit code" required autofocus>
                </div>
                <button class="btn btn-dark btn-block auth-submit" type="submit">Verify code</button>
            </form>
            <form class="auth-resend-form" method="post" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button class="link" type="submit">Resend code</button>
            </form>
            <p class="alt"><a href="{{ route('password.request') }}">Use a different email</a></p>
        </div>
    </section>
</main>
@endsection
