@extends('layouts.admin')
@section('title', 'Orders – Admin')
@section('content')
<div class="ph-row">
    <h1>Orders</h1>
    <a class="btn btn-light btn-sm" href="{{ route('admin.orders.export', request()->query()) }}">Export CSV</a>
</div>
<div class="panel panel-b">
    <form method="get" class="toolbar">
        <input name="q" value="{{ request('q') }}" placeholder="Order, customer or email">
        <select name="status">
            <option value="">All statuses</option>
            @foreach(\App\Models\Order::STATUSES as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button class="btn btn-light btn-sm">Filter</button>
    </form>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Order / Customer</th><th>Items</th><th>Total</th><th>Actions / update</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->order_no }}</strong>
                            <small class="sub">{{ $order->name }} · {{ $order->email }} · {{ $order->phone }}</small>
                            <small class="sub">{{ $order->created_at->format('d M Y, h:i A') }}</small>
                            <details class="order-details">
                                <summary>View order details</summary>
                                <div class="order-details-panel">
                                    <strong>Delivery address</strong>
                                    <p>{{ collect($order->address ?? [])->only(['line1', 'line2', 'landmark', 'city', 'state', 'pin'])->filter()->implode(', ') ?: 'No address recorded' }}</p>
                                    <strong>Items</strong>
                                    @foreach($order->items as $item)
                                        <p>{{ $item->name }} ({{ $item->sku }}) × {{ $item->qty }} — ₹{{ number_format($item->price, 2) }}</p>
                                    @endforeach
                                    <strong>Status timeline</strong>
                                    @forelse($order->timeline as $event)
                                        <p>{{ $event->status }} · {{ $event->created_at->format('d M Y, h:i A') }}</p>
                                    @empty
                                        <p>No status history.</p>
                                    @endforelse
                                </div>
                            </details>
                        </td>
                        <td>@foreach($order->items as $item)<div>{{ $item->name }} × {{ $item->qty }}</div>@endforeach</td>
                        <td>₹{{ number_format($order->total, 2) }}<small class="sub">{{ $order->payment_method }} / {{ $order->payment_status }}</small></td>
                        <td>
                            <form method="post" action="{{ route('admin.orders.update', $order) }}">
                                @csrf @method('PATCH')
                                <div class="field"><label for="status-{{ $order->id }}">Status</label><select id="status-{{ $order->id }}" name="status" required>@foreach(\App\Models\Order::STATUSES as $status)<option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>@endforeach</select></div>
                                <div class="field"><label for="payment-{{ $order->id }}">Payment status</label><select id="payment-{{ $order->id }}" name="payment_status" required>@foreach(['Pending', 'Paid', 'COD', 'Failed', 'Refunded'] as $paymentStatus)<option value="{{ $paymentStatus }}" @selected($order->payment_status === $paymentStatus)>{{ $paymentStatus }}</option>@endforeach</select></div>
                                <button class="btn btn-dark btn-sm">Save Update</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection