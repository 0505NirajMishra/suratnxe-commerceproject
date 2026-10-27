@extends('layouts.admin')
@section('title', 'Admin Profile')
@section('content')
<div class="ph-row"><h1>Admin Profile</h1></div>
<section class="panel">
    <div class="panel-h"><h3>Account details</h3></div>
    <div class="panel-b">
        <form method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="admin-profile-photo">
                <span class="avatar">
                    @if(auth()->user()->profile_photo_path)
                        <img src="{{ asset('storage/'.auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
                <div class="field"><label for="admin-profile-photo">Profile picture</label><input id="admin-profile-photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP.</small></div>
            </div>
            <div class="grid2">
                <div class="field"><label for="admin-profile-name">Name *</label><input id="admin-profile-name" name="name" required maxlength="120" value="{{ old('name', auth()->user()->name) }}"></div>
                <div class="field"><label for="admin-profile-email">Email *</label><input id="admin-profile-email" type="email" name="email" required maxlength="190" value="{{ old('email', auth()->user()->email) }}"></div>
                <div class="field"><label for="admin-profile-phone">Phone</label><input id="admin-profile-phone" type="tel" name="phone" maxlength="20" value="{{ old('phone', auth()->user()->phone) }}"></div>
            </div>
            <hr>
            <br/>
            <h3>Change password (optional)</h3>
            <br/>
            {{-- <div class="grid2">
                <div class="field"><label for="admin-current-password">Current password</label><input id="admin-current-password" type="password" name="current_password" autocomplete="current-password"></div>
                <div class="field"><label for="admin-new-password">New password</label><input id="admin-new-password" type="password" name="password" autocomplete="new-password" minlength="8"></div>
                <div class="field"><label for="admin-confirm-password">Confirm new password</label><input id="admin-confirm-password" type="password" name="password_confirmation" autocomplete="new-password"></div>
            </div> --}}
            
            <div class="grid2" id="adminPasswordSettings">

                <!-- Current Password -->
                <div class="field">
                    <label for="admin-current-password">Current password</label>

                    <div class="admin-pw-wrapper">
                        <input id="admin-current-password"
                            type="password"
                            name="current_password"
                            autocomplete="current-password">

                        <button type="button"
                                class="admin-pw-toggle"
                                aria-label="Show current password"
                                aria-pressed="false">

                            <svg class="admin-eye-open"
                                viewBox="0 0 24 24"
                                width="20" height="20"
                                fill="none" stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>

                            <svg class="admin-eye-closed"
                                viewBox="0 0 24 24"
                                width="20" height="20"
                                fill="none" stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                style="display:none">
                                <path d="M3 3l18 18"/>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.9 5.2A11 11 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3.1 3.9"/>
                                <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1 0 1.9-.2 2.8-.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- New Password -->
                <div class="field">
                    <label for="admin-new-password">New password</label>

                    <div class="admin-pw-wrapper">
                        <input id="admin-new-password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            minlength="8">

                        <button type="button"
                                class="admin-pw-toggle"
                                aria-label="Show new password"
                                aria-pressed="false">

                            <svg class="admin-eye-open"
                                viewBox="0 0 24 24"
                                width="20" height="20"
                                fill="none" stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>

                            <svg class="admin-eye-closed"
                                viewBox="0 0 24 24"
                                width="20" height="20"
                                fill="none" stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                style="display:none">
                                <path d="M3 3l18 18"/>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.9 5.2A11 11 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3.1 3.9"/>
                                <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1 0 1.9-.2 2.8-.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="field">
                    <label for="admin-confirm-password">Confirm new password</label>

                    <div class="admin-pw-wrapper">
                        <input id="admin-confirm-password"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password">

                        <button type="button"
                                class="admin-pw-toggle"
                                aria-label="Show confirm password"
                                aria-pressed="false">

                            <svg class="admin-eye-open"
                                viewBox="0 0 24 24"
                                width="20" height="20"
                                fill="none" stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>

                            <svg class="admin-eye-closed"
                                viewBox="0 0 24 24"
                                width="20" height="20"
                                fill="none" stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                style="display:none">
                                <path d="M3 3l18 18"/>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.9 5.2A11 11 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3.1 3.9"/>
                                <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1 0 1.9-.2 2.8-.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

            <button class="btn btn-dark" type="submit">Save Profile</button>
        </form>
    </div>
</section>
@endsection
