@extends('layouts.app')
@section('title', 'Log in - Surat NX')
@section('content')
<main class="auth auth-page auth-login">
    <section class="auth-art" aria-label="Welcome to Surat NX">
        <div>
            <h2>Welcome back to Surat NX</h2>
            <p>Log in to track orders, manage saved addresses and check out faster.</p>
        </div>
    </section>
    <section class="auth-form">
        <div class="auth-card">
            <h1>Log in</h1>
            <p>Enter your email and password to continue.</p>
            @if(request()->filled('next'))
                <div class="alert ok">Please log in to continue.</div>
            @endif
            <div class="demo">
                <span>Demo account: <b>demo@suratnx.com</b> / <b>demo123</b></span>
                <button class="btn btn-light btn-sm" type="button" data-demo-fill>Fill demo login</button>
            </div>
            <form method="post" action="{{ route('login.post') }}" novalidate>
                @csrf
                <div class="field">
                    <label for="login-email">Email address</label>
                    <input id="login-email" type="email" name="email" autocomplete="email" placeholder="you@example.com" value="{{ old('email') }}" required autofocus @if($errors->has('email')) class="field-invalid" aria-invalid="true" aria-describedby="login-email-error" @endif>
                    @error('email')<small class="field-error-message" id="login-email-error" role="alert" data-validation-error>{{ $message }}</small>@enderror
                </div>
                <div class="field">
                    <label for="login-password">Password</label>
                    <div class="pw">
                        <input id="login-password" type="password" name="password" placeholder="Your password" autocomplete="current-password" required @if($errors->has('password')) class="field-invalid" aria-invalid="true" aria-describedby="login-password-error" @endif>
                        @error('password')<small class="field-error-message" id="login-password-error" role="alert" data-validation-error>{{ $message }}</small>@enderror
                        <button type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="auth-login-options">
                    <label class="check"><input type="checkbox" name="remember" value="1" checked> Keep me signed in</label>
                    <a class="link" href="{{ route('password.request') }}">Forgot password?</a>
                </div>
                <button class="btn btn-dark btn-block auth-submit" type="submit">Log in</button>
            </form>
            <p class="alt">New to Surat NX? <a href="{{ route('register') }}">Create an account</a></p>
        </div>
    </section>
</main>
@endsection
