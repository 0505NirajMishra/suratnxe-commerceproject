@extends('layouts.admin')
@section('title', 'Settings - Admin')
@section('content')
<div class="ph-row"><h1>Store Settings</h1></div>
<div class="panel panel-b">
	<form method="post" action="{{ route('admin.settings.update') }}">
		@csrf
		@method('PUT')
		<div class="grid2">
			@foreach (['name' => 'Store name', 'tagline' => 'Tagline', 'email' => 'Store email', 'phone' => 'Phone', 'location' => 'Store location', 'address' => 'Address'] as $key => $label)
				<div class="field" @if ($key === 'address') style="grid-column:1/-1" @endif><label for="setting-{{ $key }}">{{ $label }}</label><input id="setting-{{ $key }}" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" required></div>
			@endforeach
			@foreach (['free_ship' => 'Free shipping threshold (INR)', 'ship_fee' => 'Standard shipping fee (INR)', 'express_fee' => 'Express shipping fee (INR)', 'gst' => 'GST on discounted goods (%)'] as $key => $label)
				<div class="field"><label for="setting-{{ $key }}">{{ $label }}</label><input id="setting-{{ $key }}" type="number" step="0.01" min="0" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" required></div>
			@endforeach
			@foreach (['hero_kicker' => 'Hero eyebrow', 'hero_title' => 'Homepage hero title', 'hero_subtitle' => 'Homepage hero subtitle', 'feature_quality_title' => 'Quality feature title', 'feature_quality_copy' => 'Quality feature copy', 'feature_custom_title' => 'Custom orders feature title', 'feature_custom_copy' => 'Custom orders feature copy', 'feature_shipping_title' => 'Shipping feature title', 'feature_shipping_copy' => 'Shipping feature copy', 'home_categories_title' => 'Category section title', 'home_categories_link' => 'Category section link', 'home_featured_title' => 'Featured section title', 'home_featured_copy' => 'Featured section copy', 'home_custom_title' => 'Custom orders heading', 'home_custom_copy' => 'Custom orders copy', 'home_custom_button' => 'Custom orders button', 'home_why_title' => 'Why choose us heading', 'home_why_copy' => 'Why choose us copy', 'why_source_title' => 'Sourcing benefit title', 'why_source_copy' => 'Sourcing benefit copy', 'why_quality_title' => 'Quality benefit title', 'why_quality_copy' => 'Quality benefit copy', 'why_shipping_title' => 'Dispatch benefit title', 'why_shipping_copy' => 'Dispatch benefit copy', 'why_payment_title' => 'Payment benefit title', 'why_payment_copy' => 'Payment benefit copy', 'home_bulk_title' => 'Bulk orders heading', 'home_bulk_copy' => 'Bulk orders copy', 'home_bulk_button' => 'Bulk orders button', 'about_title' => 'About page heading', 'about_intro' => 'About introduction', 'about_quality_title' => 'About quality title', 'about_quality_copy' => 'About quality copy', 'about_collection_title' => 'About collection title', 'about_collection_copy' => 'About collection copy', 'about_service_title' => 'About service title', 'about_service_copy' => 'About service copy', 'about_cta_title' => 'About call-to-action title', 'about_cta_copy' => 'About call-to-action copy', 'policy_shipping' => 'Shipping policy', 'policy_returns' => 'Returns policy', 'policy_privacy' => 'Privacy policy', 'policy_terms' => 'Terms', 'footer_description' => 'Footer description'] as $key => $label)
				<div class="field" style="grid-column:1/-1"><label for="setting-{{ $key }}">{{ $label }}</label><textarea id="setting-{{ $key }}" name="{{ $key }}" rows="3" required>{{ old($key, $settings[$key] ?? '') }}</textarea></div>
			@endforeach
			@foreach (['social_facebook' => 'Facebook URL', 'social_instagram' => 'Instagram URL', 'social_youtube' => 'YouTube URL'] as $key => $label)
				<div class="field"><label for="setting-{{ $key }}">{{ $label }}</label><input id="setting-{{ $key }}" type="url" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}"></div>
			@endforeach
		</div>
		<button class="btn btn-dark" type="submit">Save Settings</button>
	</form>
</div>
<div class="panel panel-b" style="margin-top:18px">
	<h2>Delivery timelines</h2>
	<form method="post" action="{{ route('admin.settings.delivery') }}">
		@csrf
		@method('PATCH')
		<div class="grid2">
			@foreach (['standard_delivery_min_days' => 'Standard minimum working days', 'standard_delivery_max_days' => 'Standard maximum working days', 'express_delivery_min_days' => 'Express minimum working days', 'express_delivery_max_days' => 'Express maximum working days'] as $key => $label)
				<div class="field"><label for="setting-{{ $key }}">{{ $label }}</label><input id="setting-{{ $key }}" type="number" min="1" max="90" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" required></div>
			@endforeach
		</div>
		<button class="btn btn-dark" type="submit">Save delivery timelines</button>
	</form>
</div>
<div class="panel panel-b" style="margin-top:18px">
	<h2>Contact details, hours and social links</h2>
	<form method="post" action="{{ route('admin.settings.contact') }}">
		@csrf
		@method('PATCH')
		<div class="grid2">
			<div class="field"><label for="contact-phone">Phone number</label><input id="contact-phone" name="phone" value="{{ old('phone', $settings['phone'] ?? '') }}" required></div>
			<div class="field"><label for="contact-whatsapp">WhatsApp number (international format)</label><input id="contact-whatsapp" name="whatsapp" value="{{ old('whatsapp', $settings['whatsapp'] ?? '') }}" required></div>
			<div class="field"><label for="contact-location">Location label</label><input id="contact-location" name="location" value="{{ old('location', $settings['location'] ?? '') }}" required></div>
			<div class="field"><label for="contact-map">Google Maps URL</label><input id="contact-map" type="url" name="map_url" value="{{ old('map_url', $settings['map_url'] ?? '') }}" required></div>
			<div class="field" style="grid-column:1/-1"><label for="contact-address">Full address</label><input id="contact-address" name="address" value="{{ old('address', $settings['address'] ?? '') }}" required></div>
			<div class="field" style="grid-column:1/-1"><label for="contact-hours">Business hours</label><textarea id="contact-hours" name="business_hours" rows="4" required>{{ old('business_hours', $settings['business_hours'] ?? '') }}</textarea></div>
			<div class="field" style="grid-column:1/-1"><label for="contact-video">YouTube video URL</label><input id="contact-video" type="url" name="youtube_video" value="{{ old('youtube_video', $settings['youtube_video'] ?? '') }}" required></div>
			@foreach (['social_instagram' => 'Instagram URL', 'social_facebook' => 'Facebook URL', 'social_x' => 'X URL', 'social_linkedin' => 'LinkedIn URL', 'social_youtube' => 'YouTube channel URL'] as $key => $label)
				<div class="field"><label for="setting-{{ $key }}">{{ $label }}</label><input id="setting-{{ $key }}" type="url" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}"></div>
			@endforeach
		</div>
		<button class="btn btn-dark" type="submit">Save contact details</button>
	</form>
</div>
<div class="panel panel-b" style="margin-top:18px">
	<h2>Returns</h2>
	<form method="post" action="{{ route('admin.settings.return-window') }}" class="ph-act">
		@csrf @method('PATCH')
		<div class="field"><label for="return-window-days">Return request window (days)</label><input id="return-window-days" type="number" min="1" max="60" name="return_window_days" value="{{ old('return_window_days', $settings['return_window_days'] ?? 7) }}" required></div>
		<button class="btn btn-dark" type="submit">Save return window</button>
	</form>
</div>
@endsection