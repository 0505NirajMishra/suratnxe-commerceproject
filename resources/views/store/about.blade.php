@extends('layouts.app')
@section('title', 'About '.\App\Models\Setting::get('name'))
@section('content')
<main class="sec">
	<div class="wrap">
		<div class="page-head">
			<p class="hero-k">{{ \App\Models\Setting::get('tagline') }}</p>
			<h1>{{ $settings['about_title'] }}</h1>
			<p>{{ $settings['about_intro'] }}</p>
		</div>
		<div class="why">
			<div class="why-i"><h3>{{ $settings['about_quality_title'] }}</h3><p>{{ $settings['about_quality_copy'] }}</p></div>
			<div class="why-i"><h3>{{ $settings['about_collection_title'] }}</h3><p>{{ $settings['about_collection_copy'] }}</p></div>
			<div class="why-i"><h3>{{ $settings['about_service_title'] }}</h3><p>{{ $settings['about_service_copy'] }}</p></div>
		</div>
		<section class="about-contact">
			<div>
				<p class="hero-k">Visit or get in touch</p>
				<h2>We’re here to help</h2>
				<p><a href="{{ \App\Models\Setting::get('map_url') }}" target="_blank" rel="noopener noreferrer">{{ \App\Models\Setting::get('address') }}</a></p>
				<p><a href="tel:{{ preg_replace('/\s+/', '', (string) \App\Models\Setting::get('phone')) }}">{{ \App\Models\Setting::get('phone') }}</a>
					<span aria-hidden="true"> · </span>
					<a href="https://wa.me/{{ preg_replace('/\D+/', '', (string) \App\Models\Setting::get('whatsapp')) }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
				</p>
				<p><a href="mailto:{{ \App\Models\Setting::get('email') }}">{{ \App\Models\Setting::get('email') }}</a></p>
				<div class="about-social contact-social" aria-label="Follow Surat NX">
					<h3>Follow Surat NX</h3>
					<div>
						<a href="{{ \App\Models\Setting::get('social_instagram') }}" target="_blank" rel="noopener noreferrer">Instagram</a>
						<a href="{{ \App\Models\Setting::get('social_facebook') }}" target="_blank" rel="noopener noreferrer">Facebook</a>
						<a href="{{ \App\Models\Setting::get('social_x') }}" target="_blank" rel="noopener noreferrer">X</a>
						<a href="{{ \App\Models\Setting::get('social_linkedin') }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>
						<a href="{{ \App\Models\Setting::get('social_youtube') }}" target="_blank" rel="noopener noreferrer">YouTube</a>
					</div>
				</div>
			</div>
			<div>
				<h3>Business hours</h3>
				<p>{!! nl2br(e(\App\Models\Setting::get('business_hours'))) !!}</p>
				@if(\App\Models\Setting::get('youtube_video'))
					<a class="link" href="{{ \App\Models\Setting::get('youtube_video') }}" target="_blank" rel="noopener noreferrer">Watch Surat NX</a>
				@endif
			</div>
		</section>
		<div class="promo">
			<div class="promo-img"></div>
			<div class="promo-t"><h2>{{ $settings['about_cta_title'] }}</h2><p>{{ $settings['about_cta_copy'] }}</p><a class="btn btn-gold" href="{{ route('shop') }}">Explore Fabrics</a> <a class="btn btn-dark" href="{{ route('contact') }}">Contact Us</a></div>
		</div>
	</div>
</main>
@endsection