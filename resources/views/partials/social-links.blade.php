@php
    // $variant: 'footer' (dark background) or 'light' (contact/about pages)
    $variant = $variant ?? 'light';
    $socialIcons = [
        'Instagram' => ['key' => 'social_instagram', 'svg' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.6" fill="currentColor"/>'],
        'Facebook'  => ['key' => 'social_facebook',  'svg' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>'],
        'X'         => ['key' => 'social_x',         'svg' => '<path d="M4 4l11.733 16h4.267l-11.733-16z"/><path d="M4 20l6.768-6.768m2.46-2.46l6.772-6.772"/>'],
        'LinkedIn'  => ['key' => 'social_linkedin',  'svg' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>'],
        'YouTube'   => ['key' => 'social_youtube',   'svg' => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>'],
    ];
@endphp
@foreach($socialIcons as $network => $icon)
    @php $socialUrl = \App\Models\Setting::get($icon['key']); @endphp
    @if($socialUrl)
        <a class="social-icon social-icon--{{ \Illuminate\Support\Str::lower($network) }}" href="{{ $socialUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $network }}" title="{{ $network }}">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $icon['svg'] !!}</svg>
        </a>
    @endif
@endforeach
