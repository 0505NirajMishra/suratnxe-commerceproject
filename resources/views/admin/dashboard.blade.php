@extends('layouts.admin')
@section('title', 'Dashboard - Surat NX Admin')
@section('content')
<div class="ph-row">
    <div>
        <h1>Dashboard</h1>
        <p>Overview of your store</p>
    </div>
    <div class="ph-act">
        <a class="btn btn-light btn-sm" href="{{ route('admin.returns') }}">Return requests</a>
    </div>
</div>
<div class="cards">
    <div class="scard">
        <span class="ic-c" aria-hidden="true">▤</span>
        <div><b>{{ number_format($stats['products']) }}</b><span>Total Fabrics</span></div>
    </div>
    <div class="scard">
        <span class="ic-c" aria-hidden="true">▣</span>
        <div><b>{{ number_format($stats['orders']) }}</b><span>Total Orders</span></div>
    </div>
    <div class="scard">
        <span class="ic-c" aria-hidden="true">♙</span>
        <div><b>{{ number_format($stats['customers']) }}</b><span>Customers</span></div>
    </div>
    <div class="scard">
        <span class="ic-c" aria-hidden="true">₹</span>
        <div><b>₹{{ number_format($stats['revenue'], 0) }}</b><span>Paid Revenue</span></div>
    </div>
</div>
<section class="panel">
    <div class="panel-h">
        <h3>Recent Orders</h3>
        <a class="link" href="{{ route('admin.orders') }}">View all orders</a>
    </div>
    <div class="panel-b">
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Payment</th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_no }}</td>
                            <td>{{ $order->name }}<span class="sub">{{ $order->email }}</span></td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
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
    </div>
</section>
@endsection
