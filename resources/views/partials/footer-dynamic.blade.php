@php
    $socialLinks = [
        'Instagram' => \App\Models\Setting::get('social_instagram'),
        'Facebook' => \App\Models\Setting::get('social_facebook'),
        'X' => \App\Models\Setting::get('social_x'),
        'LinkedIn' => \App\Models\Setting::get('social_linkedin'),
        'YouTube' => \App\Models\Setting::get('social_youtube'),
    ];
@endphp
<footer class="ftr">
    <div class="wrap ftr-grid">
        <div class="ftr-brand">
            <a class="logo light" href="{{ route('home') }}">
                <span class="mono">SN</span>
                <span class="logo-t"><b>{{ \App\Models\Setting::get('name') }}</b><small>{{ \App\Models\Setting::get('tagline') }}</small></span>
            </a>
            <p>{{ \App\Models\Setting::get('footer_description') }}</p>
            <div class="social" aria-label="Social media">
                @foreach($socialLinks as $network => $url)
                    @if($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $network }}</a>
                    @endif
                @endforeach
            </div>
            @if(\App\Models\Setting::get('youtube_video'))
                <a class="footer-video-link" href="{{ \App\Models\Setting::get('youtube_video') }}" target="_blank" rel="noopener noreferrer">Watch our latest video</a>
            @endif
        </div>

        <div>
            <h4>Quick Links</h4>
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('shop') }}">All Fabrics</a>
            <a href="{{ route('about') }}">About Us</a>
            <a href="{{ route('contact') }}">Contact Us</a>
            <a href="{{ route('track') }}">Track Order</a>
        </div>

        <div>
            <h4>Shop by Category</h4>
            @foreach(\App\Models\Category::active()->orderBy('sort_order')->take(6)->get() as $navCategory)
                <a href="{{ route('shop', ['cat' => $navCategory->slug]) }}">{{ $navCategory->name }}</a>
            @endforeach
        </div>

        <div>
            <h4>Customer Care</h4>
            <a href="{{ route('account') }}">My Account</a>
            <a href="{{ route('policy') }}">Shipping &amp; Returns</a>
            <a href="{{ route('policy') }}">Privacy Policy</a>
        </div>

        <div class="ftr-contact">
            <h4>Get in Touch</h4>
            <p><a href="{{ \App\Models\Setting::get('map_url') }}" target="_blank" rel="noopener noreferrer">{{ \App\Models\Setting::get('address') }}</a></p>
            <p><a href="tel:{{ preg_replace('/\s+/', '', (string) \App\Models\Setting::get('phone')) }}">{{ \App\Models\Setting::get('phone') }}</a></p>
            <p><a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) \App\Models\Setting::get('whatsapp')) }}" target="_blank" rel="noopener noreferrer">WhatsApp us</a></p>
            <p><a href="mailto:{{ \App\Models\Setting::get('email') }}">{{ \App\Models\Setting::get('email') }}</a></p>
            <details class="footer-hours">
                <summary>Business hours</summary>
                <p>{!! nl2br(e(\App\Models\Setting::get('business_hours'))) !!}</p>
            </details>
            <form class="nl" method="post" action="{{ route('newsletter') }}">
                @csrf
                <input type="email" name="email" required placeholder="Your email" aria-label="Your email">
                <button class="btn btn-gold btn-sm" type="submit">Subscribe</button>
            </form>
        </div>
    </div>
    <div class="ftr-bot">
        <div class="wrap">
            <span>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('name') }}. All rights reserved.</span>
            <span>{{ \App\Models\Setting::get('location') }}. <a href="{{ route('admin.login') }}" class="adm-link">Admin</a></span>
        </div>
    </div>
</footer>
