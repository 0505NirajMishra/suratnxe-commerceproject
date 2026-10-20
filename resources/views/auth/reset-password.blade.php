@extends('layouts.app')
@section('title', 'Choose a new password - Surat NX')
@section('content')
<main class="auth auth-page auth-recovery-page">
    <section class="auth-art">
        <div>
            <h2>Reset your password</h2>
            <p>Choose a new password to protect your Surat NX account.</p>
        </div>
    </section>
    <section class="auth-form">
        <div class="auth-card">
            <h1>Create new password</h1>
            <p>Choose a strong password you haven’t used before.</p>
            <form method="post" action="{{ route('password.update') }}">
                @csrf
                <div class="field">
                    <label for="reset-email">Email address</label>
                    <input id="reset-email" type="email" name="email" value="{{ $email }}" autocomplete="email" readonly>
                </div>
                <div class="field">
                    <label for="reset-password">New password</label>
                    <div class="pw">
                        <input id="reset-password" type="password" name="password" autocomplete="new-password" placeholder="At least 8 characters" required minlength="8">
                        <button type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="field">
                    <label for="reset-confirmation">Confirm new password</label>
                    <div class="pw">
                        <input id="reset-confirmation" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Enter your new password again" required minlength="8">
                        <button type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <button class="btn btn-dark btn-block auth-submit" type="submit">Reset password</button>
            </form>
            <p class="alt"><a href="{{ route('login') }}">Back to log in</a></p>
        </div>
    </section>
</main>
@endsection
