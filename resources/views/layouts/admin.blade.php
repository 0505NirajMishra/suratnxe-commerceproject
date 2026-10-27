<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/png" href="{{ asset('images/Surat_nx_logo_texts.png') }}">
    <title>@yield('title', 'Surat NX Admin')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body class="adm-body">
    <div class="adm" id="adminShell">
        <aside class="side" aria-label="Admin menu">
            <div class="brand">
                <span class="mono">SN</span>
                <div><b>SURAT NX</b><small>ADMIN PANEL</small></div>
            </div>
            <nav>
                <a class="{{ request()->routeIs('admin.dashboard') ? 'on' : '' }}" href="{{ route('admin.dashboard') }}">▦ Dashboard</a>
                <a class="{{ request()->routeIs('admin.products*') ? 'on' : '' }}" href="{{ route('admin.products') }}">▤ Fabrics</a>
                <a class="{{ request()->routeIs('admin.categories') ? 'on' : '' }}" href="{{ route('admin.categories') }}">▧ Categories</a>
                <a class="{{ request()->routeIs('admin.orders*') ? 'on' : '' }}" href="{{ route('admin.orders') }}">▣ Orders</a>
                <a class="{{ request()->routeIs('admin.customers') ? 'on' : '' }}" href="{{ route('admin.customers') }}">♙ Customers</a>
                <a class="{{ request()->routeIs('admin.coupons') ? 'on' : '' }}" href="{{ route('admin.coupons') }}">◇ Coupons</a>
                <a class="{{ request()->routeIs('admin.enquiries') ? 'on' : '' }}" href="{{ route('admin.enquiries') }}">✉ Enquiries</a>
                <a class="{{ request()->routeIs('admin.returns*') ? 'on' : '' }}" href="{{ route('admin.returns') }}">↩ Returns</a>
                <a class="{{ request()->routeIs('admin.reports') ? 'on' : '' }}" href="{{ route('admin.reports') }}">▥ Reports</a>
                <a class="{{ request()->routeIs('admin.settings*') ? 'on' : '' }}" href="{{ route('admin.settings') }}">⚙ Settings</a>
                <a class="{{ request()->routeIs('admin.profile*') ? 'on' : '' }}" href="{{ route('admin.profile') }}">♙ Profile</a>
            </nav>
        </aside>
        <div class="scrim" data-admin-close></div>
        <div class="amain">
            <header class="top">
                <button class="hicon menu-btn" type="button" data-admin-toggle aria-label="Open admin menu" aria-expanded="false">☰</button>
                <form class="gsearch" method="get" action="{{ route('admin.products') }}">
                    <span aria-hidden="true">⌕</span>
                    <input name="q" value="{{ request('q') }}" placeholder="Search fabrics…" aria-label="Search fabrics">
                </form>
                <span class="grow"></span>
                <details class="admin-notification-menu">
                    <summary class="hicon" aria-label="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                        @php($notificationCount = collect($adminNotifications)->sum('count'))
                        @if($notificationCount > 0)<span class="cnt">{{ $notificationCount > 99 ? '99+' : $notificationCount }}</span>@endif
                    </summary>
                    <div class="admin-notification-dropdown">
                        <strong class="admin-notification-heading">Notifications</strong>
                        @forelse($adminNotifications as $notification)
                            <a href="{{ $notification['url'] }}" class="admin-notification-item">
                                <span>{{ $notification['label'] }}</span>
                                <b>{{ $notification['count'] }}</b>
                            </a>
                        @empty
                            <p class="admin-notification-empty">No notifications available.</p>
                        @endforelse
                        @if(collect($adminNotifications)->sum('count') === 0)
                            <p class="admin-notification-empty">You're all caught up.</p>
                        @endif
                    </div>
                </details>
                <details class="admin-profile-menu">
                    <summary class="who-b">
                        <span class="avatar">@if(auth()->user()->profile_photo_path)<img src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}" alt="">@else{{ mb_substr(auth()->user()->name, 0, 1) }}@endif</span>
                        <span class="admin-profile-label"><b>{{ auth()->user()->name }}</b><small>Administrator</small></span>
                    </summary>
                    <div class="admin-profile-dropdown">
                        <a href="{{ route('admin.profile') }}">My profile</a>
                        <form method="post" action="{{ route('admin.logout') }}">@csrf<button type="submit">Sign out</button></form>
                    </div>
                </details>
            </header>
            <main class="content">
                @if (session('success'))
                    <div class="alert ok" role="status">{{ session('success') }}</div>
                @endif
                @yield('content')
            </main>
            <footer style="border-top:1px solid var(--line);padding:18px 28px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:13px;color:var(--muted)">
                <span>© {{ date('Y') }} Surat NX. All rights reserved.</span>
                <span>Crafted with care in Surat, India.</span>
            </footer>
        </div>
    </div>
    <form id="adminIdleLogout" method="post" action="{{ route('admin.logout') }}" hidden>
        @csrf
        <input type="hidden" name="idle_timeout" value="1">
    </form>
    <script>window.laravelValidationErrors = @json($errors->messages());</script>
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    
    <script>
    (() => {
        const idleLimit = 15 * 60 * 1000;
        const adminId = @json(auth()->id());
        const activityUrl = @json(route('admin.activity'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const logoutForm = document.getElementById('adminIdleLogout');
        const storageKey = `suratnx-admin-last-activity-${adminId}`;
        const serverActivity = {{ (int) session('admin_last_activity', now()->timestamp) }} * 1000;
        let lastActivity = serverActivity;
        let lastSharedActivity = serverActivity;
        let lastPing = serverActivity;
        let logoutStarted = false;
        let idleTimer;

        try {
            const sharedActivity = Number(localStorage.getItem(storageKey)) || 0;
            lastActivity = Math.max(lastActivity, sharedActivity);
            lastSharedActivity = lastActivity;
        } catch (error) {
            console.warn('Admin tabs could not share activity state.', error);
        }

        const expireSession = () => {
            if (logoutStarted) return;
            logoutStarted = true;
            logoutForm?.requestSubmit();
        };

        const checkIdle = () => {
            window.clearTimeout(idleTimer);
            const remaining = idleLimit - (Date.now() - lastActivity);
            if (remaining <= 0) {
                expireSession();
                return;
            }
            idleTimer = window.setTimeout(checkIdle, remaining + 25);
        };

        const recordActivity = () => {
            if (logoutStarted) return;
            lastActivity = Date.now();
            if (lastActivity - lastSharedActivity >= 1000) {
                try {
                    localStorage.setItem(storageKey, String(lastActivity));
                    lastSharedActivity = lastActivity;
                } catch (error) {
                    console.warn('Admin activity could not be shared between tabs.', error);
                }
            }
            checkIdle();

            if (lastActivity - lastPing >= 20000 && csrfToken) {
                lastPing = lastActivity;
                fetch(activityUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    keepalive: true,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                }).catch((error) => console.error('Could not refresh the admin session.', error));
            }
        };

        ['pointerdown', 'pointermove', 'keydown', 'scroll', 'touchstart', 'wheel'].forEach((eventName) => {
            document.addEventListener(eventName, recordActivity, { passive: true });
        });

        window.addEventListener('storage', (event) => {
            if (event.key !== storageKey) return;
            const sharedActivity = Number(event.newValue) || 0;
            if (sharedActivity > lastActivity) {
                lastActivity = sharedActivity;
                lastSharedActivity = sharedActivity;
                checkIdle();
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && Date.now() - lastActivity >= idleLimit) {
                expireSession();
            }
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) window.location.reload();
        });

        checkIdle();
    })();
    </script>
</body>
</html>
