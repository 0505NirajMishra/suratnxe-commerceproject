@extends('layouts.app')
@section('title', 'Create Account - Surat NX')
@section('content')
<main class="auth auth-page auth-register">
    <section class="auth-art" aria-label="Join Surat NX">
        <div>
            <h2>Join Surat NX</h2>
            <p>Save your addresses, track every order and keep a wishlist of fabrics you love.</p>
        </div>
    </section>
    <section class="auth-form">
        <div class="auth-card">
            <h1>Create account</h1>
            <p>It takes less than a minute.</p>
            <form method="post" action="{{ route('register.post') }}">
                @csrf
                <div class="field">
                    <label for="register-name">Full name</label>
                    <input id="register-name" name="name" autocomplete="name" placeholder="Your full name" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="row2 auth-row">
                    <div class="field">
                        <label for="register-email">Email address</label>
                        <input id="register-email" type="email" name="email" autocomplete="email" placeholder="you@example.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="field">
                        <label for="register-phone">Mobile number</label>
                        <input id="register-phone" type="tel" name="phone" autocomplete="tel" inputmode="numeric" maxlength="10" placeholder="10-digit mobile" value="{{ old('phone') }}" required>
                    </div>
                </div>
                <div class="row2 auth-row">
                    <div class="field">
                        <label for="register-password">Password</label>
                        <div class="pw">
                            <input id="register-password" type="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
                            <button type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="register-password-confirmation">Confirm password</label>
                        <div class="pw">
                            <input id="register-password-confirmation" type="password" name="password_confirmation" placeholder="Re-enter your password" autocomplete="new-password" minlength="8" required>
                            <button type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <label class="check auth-terms">
                    <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                    <span>I agree to the <a class="link" href="{{ route('policy') }}#terms">Terms &amp; Conditions</a> and <a class="link" href="{{ route('policy') }}">Privacy Policy</a></span>
                </label>
                <button class="btn btn-dark btn-block auth-submit" type="submit">Create account</button>
            </form>
            <p class="alt">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
        </div>
    </section>
</main>
@endsection
