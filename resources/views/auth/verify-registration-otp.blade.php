@extends('layouts.app')
@section('title', 'Verify your email - Surat NX')
@section('content')
<main class="auth auth-page auth-recovery-page">
    <section class="auth-art">
        <div>
            <h2>One more step</h2>
            <p>Verify your email to finish creating your Surat NX account.</p>
        </div>
    </section>
    <section class="auth-form">
        <div class="auth-card">
            <h1>Verify your email</h1>
            <p>We sent a one-time code to <strong>{{ $email }}</strong>. It expires in 10 minutes.</p>
            @if(session('status'))<div class="alert ok" role="status">{{ session('status') }}</div>@endif
            @error('otp')<div class="alert" role="alert">{{ $message }}</div>@enderror
            <form method="post" action="{{ route('register.otp.verify') }}">
                @csrf
                <div class="field">
                    <label for="registration-otp">Verification code</label>
                    <input id="registration-otp" type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" placeholder="Enter 6-digit code" required autofocus>
                </div>
                <button class="btn btn-dark btn-block auth-submit" type="submit">Verify and create account</button>
            </form>
            <form class="auth-resend-form" method="post" action="{{ route('register.otp.resend') }}">
                @csrf
                <button class="link" type="submit">Resend code</button>
            </form>
            <p class="alt"><a href="{{ route('register') }}">Use a different email</a></p>
        </div>
    </section>
</main>
@endsection
