@extends('layouts.app')
@section('title', 'Policies - '.\App\Models\Setting::get('name'))
@section('content')
<main class="sec">
	<div class="wrap">
		<div class="page-head"><h1>Customer Policies</h1><p>Clear information for a confident shopping experience.</p></div>
		<div class="form-card">
			<h2>Shipping</h2><p>{{ $settings['policy_shipping'] }}</p>
			<h2>Returns and refunds</h2><p>{{ $settings['policy_returns'] }}</p>
			<h2>Privacy</h2><p>{{ $settings['policy_privacy'] }}</p>
			<h2>Terms</h2><p>{{ $settings['policy_terms'] }}</p>
		</div>
	</div>
</main>
@endsection