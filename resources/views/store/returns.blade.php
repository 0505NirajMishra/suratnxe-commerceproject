@extends('layouts.app')
@section('title', 'Return requests - '.\App\Models\Setting::get('name'))
@section('content')
<main class="sec"><div class="wrap">
    <div class="page-head"><h1>Return requests</h1><p>Request a review within {{ $returnWindowDays }} days of delivery.</p></div>
    @if($errors->any())
        <div class="validation-summary" role="alert">{{ $errors->first() }}</div>
    @endif
    @forelse ($orders as $order)
        <section class="form-card" style="margin-bottom:18px">
            <h2>{{ $order->order_no }}</h2>
            <p>Delivered order · {{ $order->created_at->format('d M Y') }} · INR {{ number_format($order->total, 2) }}</p>
            @if ($order->returnRequest)
                <p class="return-status-line">Your request is currently: <strong class="return-status-badge">{{ $order->returnRequest->status }}</strong></p>
                <div class="return-status-update" role="status">
                    <strong>Current return update</strong>
                    <p>{{ $order->returnRequest->customerStatusMessage() }}</p>
                </div>
                @if($order->returnRequest->orderItem)
                    <p>Product: <strong>{{ $order->returnRequest->orderItem->name }}</strong> · SKU {{ $order->returnRequest->orderItem->sku }} · {{ $order->returnRequest->orderItem->qty }} m</p>
                @endif
                <p>Reason: {{ $order->returnRequest->reason }}</p>
                <p>{{ $order->returnRequest->details }}</p>
                @if($order->returnRequest->photo_paths)
                    <section class="customer-return-photos">
                        <h3>Your uploaded product photos</h3>
                        <div>
                            @foreach($order->returnRequest->photo_paths as $photoIndex => $photoPath)
                                <a href="{{ route('returns.photo', [$order->returnRequest, $photoIndex]) }}" target="_blank" rel="noopener">
                                    <img src="{{ route('returns.photo', [$order->returnRequest, $photoIndex]) }}" alt="Your return evidence photo {{ $photoIndex + 1 }}">
                                    <span>View photo {{ $photoIndex + 1 }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
                @if ($order->returnRequest->admin_note)
                    <p class="return-store-response"><strong>Store response</strong><br>{!! nl2br(e($order->returnRequest->admin_note)) !!}</p>
                @endif
            @elseif ($order->timeline->firstWhere('status', 'Delivered')?->created_at?->gte(now()->subDays($returnWindowDays)))
                <form method="post" action="{{ route('returns.store', $order) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <label for="return-item-{{ $order->id }}">Product to return</label>
                        <select id="return-item-{{ $order->id }}" name="order_item_id" required>
                            <option value="">Choose a product</option>
                            @foreach($order->items as $item)
                                <option value="{{ $item->id }}" @selected(old('order_item_id') == $item->id)>{{ $item->name }} · SKU {{ $item->sku }} · {{ $item->qty }} m</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label for="reason-{{ $order->id }}">Reason</label><select id="reason-{{ $order->id }}" name="reason" required><option value="">Choose a reason</option>@foreach (['Damaged', 'Wrong item', 'Quality issue', 'Other'] as $reason)<option value="{{ $reason }}">{{ $reason }}</option>@endforeach</select></div>
                    <div class="field"><label for="details-{{ $order->id }}">Details</label><textarea id="details-{{ $order->id }}" name="details" rows="3" maxlength="2000" required>{{ old('details') }}</textarea></div>
                    <div class="field">
                        <label for="return-photos-{{ $order->id }}">Product photos (required, up to 5)</label>
                        <input id="return-photos-{{ $order->id }}" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required>
                        <small>Upload clear photos showing the product or issue. JPG, PNG or WEBP, up to 5 MB each.</small>
                    </div>
                    <button class="btn btn-dark" type="submit">Submit request</button>
                </form>
            @else
                <p class="muted">The {{ $returnWindowDays }}-day return request window has closed.</p>
            @endif
        </section>
    @empty
        <div class="form-card"><p>No delivered orders are eligible for a return request.</p><a class="btn btn-light" href="{{ route('account') }}">View account</a></div>
    @endforelse
</div></main>
@endsection