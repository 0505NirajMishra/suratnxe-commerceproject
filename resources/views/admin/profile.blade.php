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
                <div class="field"><label for="admin-profile-photo">Profile picture</label><input id="admin-profile-photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, maximum 5 MB.</small></div>
            </div>
            <div class="grid2">
                <div class="field"><label for="admin-profile-name">Name *</label><input id="admin-profile-name" name="name" required maxlength="120" value="{{ old('name', auth()->user()->name) }}"></div>
                <div class="field"><label for="admin-profile-email">Email *</label><input id="admin-profile-email" type="email" name="email" required maxlength="190" value="{{ old('email', auth()->user()->email) }}"></div>
                <div class="field"><label for="admin-profile-phone">Phone</label><input id="admin-profile-phone" type="tel" name="phone" maxlength="20" value="{{ old('phone', auth()->user()->phone) }}"></div>
            </div>
            <hr>
            <h3>Change password (optional)</h3>
            <div class="grid2">
                <div class="field"><label for="admin-current-password">Current password</label><input id="admin-current-password" type="password" name="current_password" autocomplete="current-password"></div>
                <div class="field"><label for="admin-new-password">New password</label><input id="admin-new-password" type="password" name="password" autocomplete="new-password" minlength="8"></div>
                <div class="field"><label for="admin-confirm-password">Confirm new password</label><input id="admin-confirm-password" type="password" name="password_confirmation" autocomplete="new-password"></div>
            </div>
            <button class="btn btn-dark" type="submit">Save Profile</button>
        </form>
    </div>
</section>
@endsection
