@extends('layouts.app')
@section('title', 'Forgot password - Surat NX')
@section('content')
<main class="auth auth-page auth-recovery-page">
    <section class="auth-art">
        <div>
            <h2>Reset your password</h2>
            <p>Enter your account email and we’ll send a secure verification code.</p>
        </div>
    </section>
    <section class="auth-form">
        <div class="auth-card">
            <h1>Forgot password</h1>
            <p>Enter your account email and we’ll email you a one-time code to reset your password.</p>
            @if(session('status'))<div class="alert ok" role="status">{{ session('status') }}</div>@endif
            <form method="post" action="{{ route('password.email') }}">
                @csrf
                <div class="field">
                    <label for="reset-email">Email address</label>
                    <input id="reset-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required autofocus>
                </div>
                <button class="btn btn-dark btn-block auth-submit" type="submit">Send verification code</button>
            </form>
            <p class="alt"><a href="{{ route('login') }}">Back to log in</a></p>
        </div>
    </section>
</main>
@endsection
