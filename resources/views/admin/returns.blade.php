@extends('layouts.admin')
@section('title', 'Return requests - Admin')
@section('content')
<div class="ph-row"><h1>Return requests</h1><a class="btn btn-light" href="{{ route('admin.dashboard') }}">Dashboard</a></div>
<div class="panel panel-b">
    @forelse ($returnRequests as $returnRequest)
        <article class="panel panel-b" style="margin-bottom:18px">
            <h2>{{ $returnRequest->order->order_no }} · {{ $returnRequest->status }}</h2>
            <div class="return-admin-grid">
                <section class="return-admin-details">
                    <h3>Customer details</h3>
                    <p><strong>{{ $returnRequest->order->name }}</strong></p>
                    <p>Email: <a href="mailto:{{ $returnRequest->order->email }}">{{ $returnRequest->order->email }}</a></p>
                    <p>Phone: <a href="tel:{{ $returnRequest->order->phone }}">{{ $returnRequest->order->phone }}</a></p>
                    @php($address = $returnRequest->order->address ?? [])
                    <p>
                        Address:
                        {{ collect([$address['line1'] ?? null, $address['line2'] ?? null, $address['landmark'] ?? null, $address['city'] ?? null, $address['state'] ?? null, $address['pin'] ?? null])->filter()->join(', ') ?: 'No address on order' }}
                    </p>
                </section>
                <section class="return-admin-details">
                    <h3>Return request</h3>
                    <p><strong>Reason:</strong> {{ $returnRequest->reason }}</p>
                    <p><strong>Customer description:</strong> {{ $returnRequest->details }}</p>
                    <p><strong>Requested:</strong> {{ $returnRequest->created_at->format('d M Y, h:i A') }}</p>
                    <p><strong>Payment:</strong> {{ $returnRequest->order->payment_method }} · {{ $returnRequest->order->payment_status }}</p>
                </section>
            </div>
            <section class="return-admin-products">
                <h3>Products in order</h3>
                @foreach($returnRequest->order->items as $item)
                    <div class="return-admin-product">
                        @if($item->image)
                            <img src="{{ asset('storage/'.$item->image) }}" alt="{{ $item->name }}">
                        @endif
                        <div>
                            <strong>{{ $item->name }} @if((int) $returnRequest->order_item_id === (int) $item->id)<span class="return-product-badge">Requested return</span>@endif</strong>
                            <span>SKU: {{ $item->sku }} · {{ $item->qty }} m × ₹{{ number_format((float) $item->price, 2) }}</span>
                        </div>
                        <b>₹{{ number_format((float) $item->qty * $item->price, 2) }}</b>
                    </div>
                @endforeach
            </section>
            @if($returnRequest->photo_paths)
                <section class="return-admin-photos">
                    <h3>Customer product photos</h3>
                    <div>
                        @foreach($returnRequest->photo_paths as $photoIndex => $photoPath)
                            <a href="{{ route('admin.returns.photo', [$returnRequest, $photoIndex]) }}" target="_blank" rel="noopener">
                                <img src="{{ route('admin.returns.photo', [$returnRequest, $photoIndex]) }}" alt="Return evidence photo {{ $photoIndex + 1 }}">
                                <span>View photo {{ $photoIndex + 1 }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
            <form method="post" action="{{ route('admin.returns.update', $returnRequest) }}">
                @csrf @method('PATCH')
                <div class="field"><label for="return-status-{{ $returnRequest->id }}">Status</label><select id="return-status-{{ $returnRequest->id }}" name="status">@foreach (['Requested', 'Approved', 'Rejected', 'Received', 'Resolved'] as $status)<option value="{{ $status }}" @selected($returnRequest->status === $status)>{{ $status }}</option>@endforeach</select></div>
                <div class="field">
                    <label for="return-note-{{ $returnRequest->id }}">Response to customer</label>
                    <textarea id="return-note-{{ $returnRequest->id }}" name="admin_note" rows="3">{{ $returnRequest->admin_note }}</textarea>
                    <small>Approving a return automatically adds packing instructions and an estimated 3-4 day pickup note.</small>
                </div>
                <button class="btn btn-dark btn-sm" type="submit">Save update</button>
            </form>
        </article>
    @empty
        <p>No return requests yet.</p>
    @endforelse
    {{ $returnRequests->links() }}
</div>
@endsection