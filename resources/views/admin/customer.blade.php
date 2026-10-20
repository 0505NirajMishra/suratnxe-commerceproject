@extends('layouts.admin')
@section('title', 'Customer profile – Admin')
@section('content')
<div class="ph-row">
    <div><h1>Customer profile</h1><p>{{ $customer->name }}</p></div>
    <a class="btn btn-light btn-sm" href="{{ route('admin.customers') }}">← All customers</a>
</div>
<div class="admin-grid admin-customer-grid">
    <section class="stat-card"><span>Orders</span><strong>{{ $customer->orders->count() }}</strong></section>
    <section class="stat-card"><span>Total spent</span><strong>₹{{ number_format($customer->orders->where('status', '!=', 'Cancelled')->sum('total'), 2) }}</strong></section>
    <section class="stat-card"><span>Customer since</span><strong>{{ $customer->created_at->format('d M Y') }}</strong></section>
</div>
<section class="panel">
    <div class="panel-h"><h3>Contact details</h3></div>
    <div class="panel-b">
        <div class="customer-profile-heading">
            <span class="customer-profile-avatar">
                @if($customer->profile_photo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($customer->profile_photo_path) }}" alt="{{ $customer->name }}">
                @else
                    {{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}
                @endif
            </span>
            <strong>{{ $customer->name }}</strong>
        </div>
        <p><strong>Name:</strong> {{ $customer->name }}</p>
        <p><strong>Email:</strong> <a class="link" href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></p>
        <p><strong>Phone:</strong> {{ $customer->phone ?: '—' }}</p>
        <p><strong>Gender:</strong> {{ $customer->gender ?: '—' }}</p>
        <p><strong>Date of birth:</strong> {{ $customer->dob?->format('d M Y') ?? '—' }}</p>
    </div>
</section>
<section class="panel" style="margin-top:20px">
    <div class="panel-h"><h3>Saved addresses</h3></div>
    <div class="panel-b">
        @forelse($customer->addresses as $address)
            <div class="customer-address">
                <strong>{{ $address->name }} · {{ $address->type }}</strong>
                <p>{{ collect($address->only(['line1', 'line2', 'landmark', 'city', 'state', 'pin']))->filter()->implode(', ') }}</p>
                <p>{{ $address->phone }}</p>
            </div>
        @empty
            <p>No saved addresses.</p>
        @endforelse
    </div>
</section>
<section class="panel" style="margin-top:20px">
    <div class="panel-h"><h3>Order history</h3></div>
    <div class="panel-b tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th>Payment</th></tr></thead>
            <tbody>
                @forelse($customer->orders as $order)
                    <tr>
                        <td>{{ $order->order_no }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>@foreach($order->items as $item)<div>{{ $item->name }} × {{ $item->qty }}</div>@endforeach</td>
                        <td>₹{{ number_format($order->total, 2) }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->payment_status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
