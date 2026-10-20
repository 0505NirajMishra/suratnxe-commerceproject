@extends('layouts.app')
@section('title', 'Contact Surat NX')
@section('content')
<main class="sec">
    <div class="wrap">
        <div class="page-head">
            <h1>Contact Us</h1>
            <p>Tell us about your fabric requirement, bulk order or custom enquiry.</p>
        </div>
        <div class="two-col">
            <div class="form-card">
                <h2>Send an enquiry</h2>
                <form method="post" action="{{ route('contact.send') }}">
                    @csrf
                    <div class="field"><label for="contact-name">Name</label><input id="contact-name" name="name" required value="{{ old('name') }}"></div>
                    <div class="field"><label for="contact-email">Email</label><input id="contact-email" type="email" name="email" required value="{{ old('email') }}"></div>
                    <div class="field"><label for="contact-phone">Phone</label><input id="contact-phone" name="phone" value="{{ old('phone') }}"></div>
                    <div class="field"><label for="contact-subject">Subject</label><input id="contact-subject" name="subject" required value="{{ old('subject') }}"></div>
                    <div class="field"><label for="contact-message">Message</label><textarea id="contact-message" name="message" rows="6" required>{{ old('message') }}</textarea></div>
                    <button class="btn btn-dark" type="submit">Send Enquiry</button>
                </form>
            </div>
            <div class="form-card">
                <h2>Get in touch</h2>
                <p><a href="{{ \App\Models\Setting::get('map_url') }}" target="_blank" rel="noopener noreferrer">{{ \App\Models\Setting::get('address') }}</a></p>
                <p><a href="tel:{{ preg_replace('/\s+/', '', (string) \App\Models\Setting::get('phone')) }}">{{ \App\Models\Setting::get('phone') }}</a></p>
                <p><a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) \App\Models\Setting::get('whatsapp')) }}" target="_blank" rel="noopener noreferrer">WhatsApp: {{ \App\Models\Setting::get('phone') }}</a></p>
                <p><a href="mailto:{{ \App\Models\Setting::get('email') }}">{{ \App\Models\Setting::get('email') }}</a></p>
                <div class="contact-hours">
                    <h3>Business hours</h3>
                    <p>{!! nl2br(e(\App\Models\Setting::get('business_hours'))) !!}</p>
                    <a class="link" href="{{ \App\Models\Setting::get('map_url') }}" target="_blank" rel="noopener noreferrer">View address on Google Maps</a>
                </div>
                @if(\App\Models\Setting::get('youtube_video'))
                    <p class="contact-video"><a href="{{ \App\Models\Setting::get('youtube_video') }}" target="_blank" rel="noopener noreferrer">Watch our latest video</a></p>
                @endif
                <div class="contact-social" aria-label="Follow Surat NX">
                    <h3>Follow Surat NX</h3>
                    <div>
                        <a href="{{ \App\Models\Setting::get('social_instagram') }}" target="_blank" rel="noopener noreferrer">Instagram</a>
                        <a href="{{ \App\Models\Setting::get('social_facebook') }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a href="{{ \App\Models\Setting::get('social_x') }}" target="_blank" rel="noopener noreferrer">X</a>
                        <a href="{{ \App\Models\Setting::get('social_linkedin') }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                        <a href="{{ \App\Models\Setting::get('social_youtube') }}" target="_blank" rel="noopener noreferrer">YouTube</a>
                    </div>
                </div>
                <div style="margin-top:24px">
                    <h3>Bulk or boutique order?</h3>
                    <p>Share the fabric type, colour, quantity and delivery city. Our team will get back to you.</p>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
