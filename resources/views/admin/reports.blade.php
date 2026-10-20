@extends('layouts.admin')
@section('title', 'Reports – Admin')
@section('content')
<div class="ph-row"><h1>Reports</h1></div>
<section class="panel">
    <div class="panel-h"><h3>Order status summary</h3></div>
    <div class="panel-b tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Status</th><th>Orders</th></tr></thead>
            <tbody>
                @forelse($statuses as $status)
                    <tr><td>{{ $status->status }}</td><td>{{ number_format($status->total) }}</td></tr>
                @empty
                    <tr><td colspan="2">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
<section class="panel" style="margin-top:20px">
    <div class="panel-h"><h3>Monthly orders — last 12 months</h3></div>
    <div class="panel-b tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Month</th><th>Orders</th><th>Sales</th></tr></thead>
            <tbody>
                @forelse($monthly as $month)
                    <tr><td>{{ \Carbon\Carbon::createFromDate($month->yr, $month->mon, 1)->format('F Y') }}</td><td>{{ number_format($month->orders) }}</td><td>₹{{ number_format($month->sales, 2) }}</td></tr>
                @empty
                    <tr><td colspan="3">No orders recorded in the last 12 months.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
