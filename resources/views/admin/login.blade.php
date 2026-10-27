<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/png" href="{{ asset('images/Surat_nx_logo_texts.png') }}">
    <title>Admin Login – Surat NX</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="adm-body">
    <main class="login-w">
        <section class="login-c">
            <a class="logo" href="{{ route('home') }}">
                <span class="mono">SN</span>
                <span class="logo-t"><b>SURAT NX</b><small>ADMIN PANEL</small></span>
            </a>
            <h1>Admin sign in</h1>
            <p>Manage fabrics, orders and customers.</p>
            @if(session('success'))
                <div class="alert ok" role="status">{{ session('success') }}</div>
            @endif
            <form method="post" action="{{ route('admin.login.post') }}" novalidate>
                @csrf
                <div class="field">
                    <label for="admin-email">Email</label>
                    <input id="admin-email" type="email" name="email" value="{{ old('email', 'admin@suratnx.com') }}" autocomplete="username" required>
                </div>
                <div class="field">
                    <label for="admin-password">Password</label>
                    <input id="admin-password" type="password" name="password" autocomplete="current-password" required>
                </div>
                <button class="btn btn-dark btn-block">Sign In</button>
            </form>
            <p><a class="link" href="{{ route('home') }}">← Back to storefront</a></p>
        </section>
    </main>
    <script>window.laravelValidationErrors = @json($errors->messages());</script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
</body>
</html>
