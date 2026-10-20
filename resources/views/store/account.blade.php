@extends('layouts.app')
@section('title', 'My Account - Surat NX')
@section('content')
<main class="sec account-page">
    <div class="wrap">
        <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span>My Account</span></nav>
        <div class="page-head"><h1>My Account</h1><p>Manage your profile and view your orders.</p></div>
        @php
            $completedOrders = $user->orders->where('status', '!=', 'Cancelled');
            $activeOrders = $user->orders->whereNotIn('status', ['Delivered', 'Cancelled']);
            $totalSpent = $completedOrders->sum('total');
        @endphp
        <div class="acc">
            <aside class="acc-side">
                <div class="acc-user">
                    <span class="avatar">@if($user->profile_photo_path)<img src="{{ Storage::disk('public')->url($user->profile_photo_path) }}" alt="{{ $user->name }}">@else{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}@endif</span>
                    <div><b>{{ $user->name }}</b><span>{{ $user->email }}</span></div>
                </div>
                <nav class="acc-nav" aria-label="Account navigation">
                    <a class="on" href="#overview"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 21v-2a7.5 7.5 0 0 1 15 0v2"/></svg></span>Overview</a>
                    <a href="#orders"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 7 9 5 9-5M3 7v10l9 5 9-5V7M12 12v10"/></svg></span>My Orders</a>
                    <a href="#addresses"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>Saved Addresses</a>
                    <a href="#profile"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 5 5 5M4 20l4.5-1 11-11a2.1 2.1 0 0 0-3-3l-11 11L4 20Z"/></svg></span>Update Profile</a>
                    <a href="#password"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg></span>Change Password</a>
                    <a href="{{ route('track') }}"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h12v12H3zM15 9h4l3 4v4h-7z"/><circle cx="7.5" cy="19" r="2"/><circle cx="18.5" cy="19" r="2"/></svg></span>Track Orders</a>
                    <a href="{{ route('returns.index') }}"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h9a7 7 0 0 1 0 14h-2"/></svg></span>Returns</a>
                    <a href="{{ route('wishlist') }}"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8Z"/></svg></span>My Wishlist</a>
                    <form method="post" action="{{ route('logout') }}">@csrf<button type="submit"><span class="acc-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg></span>Sign Out</button></form>
                </nav>
            </aside>
            <div class="acc-main">
                <section id="overview" class="account-section">
                    <h2>Welcome back, {{ Str::before($user->name, ' ') }}</h2>
                    <div class="stats">
                        <div class="stat"><span>Total orders</span><b>{{ $user->orders->count() }}</b></div>
                        <div class="stat"><span>Active orders</span><b>{{ $activeOrders->count() }}</b></div>
                        <div class="stat"><span>Wishlist</span><b>{{ $user->wishlist->count() }}</b></div>
                        <div class="stat"><span>Total spent</span><b>₹{{ number_format((float) $totalSpent, 0) }}</b></div>
                    </div>
                    <div class="account-section-head"><h3>Recent orders</h3><a class="link" href="#orders">View all <span aria-hidden="true">→</span></a></div>
                    @forelse($user->orders->take(2) as $order)
                        <article class="account-order">
                            <div class="account-order-head">
                                <div><span>Order placed</span><b>{{ $order->created_at->format('d M Y') }}</b></div>
                                <div><span>Total</span><b>₹{{ number_format((float) $order->total, 2) }}</b></div>
                                <div><span>Order # {{ $order->order_no }}</span><b class="pill">{{ $order->status }}</b></div>
                            </div>
                            <div class="account-order-items">
                                @foreach($order->items->take(2) as $item)
                                    <div class="mini">
                                        @if($item->image)<img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">@else<div class="fabric-art account-fabric-art">SN</div>@endif
                                        <div class="grow">
                                            <b>{{ $item->name }}</b>
                                            <span>{{ $item->qty }} m × ₹{{ number_format((float) $item->price, 2) }}</span>
                                            @if($order->status === 'Delivered' && $item->product)
                                                @if(in_array((int) $item->product_id, $reviewedProductIds, true))
                                                    <span class="account-review-done">✓ Review submitted</span>
                                                @else
                                                    <a class="account-review-link" href="{{ route('product', $item->product) }}#reviews">Rate this product</a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="account-order-actions">
                                <button class="btn btn-dark btn-sm" type="button" data-order-details="{{ $order->order_no }}">View details</button>
                                <a class="btn btn-line btn-sm" href="{{ route('track', ['order_no' => $order->order_no, 'email' => $order->email]) }}">Track order</a>
                                <form method="post" action="{{ route('account.orders.reorder', $order) }}">@csrf<button class="btn btn-light btn-sm" type="submit">↶ Reorder</button></form>
                                @if(in_array($order->status, ['Pending', 'Confirmed', 'Processing'], true) && $order->payment_status !== 'Paid')
                                    <form method="post" action="{{ route('account.orders.cancel', $order) }}" data-confirm="Cancel order {{ $order->order_no }}?">@csrf<button class="btn btn-light btn-sm account-cancel-button" type="submit">Cancel order</button></form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="account-empty"><p>Your orders will appear here once you place one.</p><a class="btn btn-dark btn-sm" href="{{ route('shop') }}">Browse fabrics</a></div>
                    @endforelse
                </section>

                <section id="orders" class="account-section">
                    <h2>My Orders</h2>
                    @forelse($user->orders as $order)
                        <article class="account-order">
                            <div class="account-order-head">
                                <div><span>Order placed</span><b>{{ $order->created_at->format('d M Y') }}</b></div>
                                <div><span>Total</span><b>₹{{ number_format((float) $order->total, 2) }}</b></div>
                                <div><span>Order # {{ $order->order_no }}</span><b class="pill">{{ $order->status }}</b></div>
                            </div>
                            <div class="account-order-items">
                                @foreach($order->items as $item)
                                    <div class="mini">
                                        @if($item->image)<img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">@else<div class="fabric-art account-fabric-art">SN</div>@endif
                                        <div class="grow">
                                            <b>{{ $item->name }}</b>
                                            <span>{{ $item->qty }} m × ₹{{ number_format((float) $item->price, 2) }}</span>
                                            @if($order->status === 'Delivered' && $item->product)
                                                @if(in_array((int) $item->product_id, $reviewedProductIds, true))
                                                    <span class="account-review-done">✓ Review submitted</span>
                                                @else
                                                    <a class="account-review-link" href="{{ route('product', $item->product) }}#reviews">Rate this product</a>
                                                @endif
                                            @endif
                                        </div>
                                        <strong>₹{{ number_format((float) $item->price * $item->qty, 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                            <div class="account-order-actions">
                                <button class="btn btn-dark btn-sm" type="button" data-order-details="{{ $order->order_no }}">View details</button>
                                <a class="btn btn-line btn-sm" href="{{ route('track', ['order_no' => $order->order_no, 'email' => $order->email]) }}">Track order</a>
                                <form method="post" action="{{ route('account.orders.reorder', $order) }}">@csrf<button class="btn btn-light btn-sm" type="submit">↶ Reorder</button></form>
                                @if(in_array($order->status, ['Pending', 'Confirmed', 'Processing'], true) && $order->payment_status !== 'Paid')
                                    <form method="post" action="{{ route('account.orders.cancel', $order) }}" data-confirm="Cancel order {{ $order->order_no }}?">@csrf<button class="btn btn-light btn-sm account-cancel-button" type="submit">Cancel order</button></form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="account-empty"><p>You have not placed any orders yet.</p><a class="btn btn-dark btn-sm" href="{{ route('shop') }}">Start shopping</a></div>
                    @endforelse
                </section>

                <section id="addresses" class="account-section">
                    <h2>Saved Addresses</h2>
                    <div class="saved-address-grid">
                        @foreach($user->addresses as $address)
                            <article class="saved-address">
                                <span class="pill">{{ $address->is_default ? 'DEFAULT · ' : '' }}{{ $address->type }}</span>
                                <b>{{ $address->name }}</b>
                                <p>{{ $address->line1 }}@if($address->line2), {{ $address->line2 }}@endif</p>
                                @if($address->landmark)<p>{{ $address->landmark }}</p>@endif
                                <p>{{ $address->city }}, {{ $address->state }} – {{ $address->pin }}</p>
                                <p>Phone: {{ $address->phone }}</p>
                                <div class="address-actions">
                                    <details><summary>Edit</summary>
                                        <form method="post" action="{{ route('account.addresses.update', $address) }}" class="address-form">
                                            @csrf @method('PATCH')
                                            <div class="row2">
                                                <div class="field"><label for="address-name-{{ $address->id }}">Full name *</label><input id="address-name-{{ $address->id }}" name="name" required value="{{ $address->name }}"></div>
                                                <div class="field"><label for="address-phone-{{ $address->id }}">Mobile number *</label><input id="address-phone-{{ $address->id }}" name="phone" required value="{{ $address->phone }}"></div>
                                            </div>
                                            <div class="field"><label for="address-line1-{{ $address->id }}">Address line 1 *</label><input id="address-line1-{{ $address->id }}" name="line1" required value="{{ $address->line1 }}"></div>
                                            <div class="field"><label for="address-line2-{{ $address->id }}">Address line 2</label><input id="address-line2-{{ $address->id }}" name="line2" value="{{ $address->line2 }}"></div>
                                            <div class="row2">
                                                <div class="field"><label for="address-city-{{ $address->id }}">City *</label><input id="address-city-{{ $address->id }}" name="city" required value="{{ $address->city }}"></div>
                                                <div class="field"><label for="address-state-{{ $address->id }}">State *</label><input id="address-state-{{ $address->id }}" name="state" required value="{{ $address->state }}"></div>
                                            </div>
                                            <div class="row2">
                                                <div class="field"><label for="address-pin-{{ $address->id }}">PIN code *</label><input id="address-pin-{{ $address->id }}" name="pin" required value="{{ $address->pin }}"></div>
                                                <div class="field"><label for="address-type-{{ $address->id }}">Address type</label><select id="address-type-{{ $address->id }}" name="type">@foreach(['Home', 'Work', 'Other'] as $type)<option value="{{ $type }}" @selected($address->type === $type)>{{ $type }}</option>@endforeach</select></div>
                                            </div>
                                            <div class="field"><label for="address-landmark-{{ $address->id }}">Landmark</label><input id="address-landmark-{{ $address->id }}" name="landmark" value="{{ $address->landmark }}"></div>
                                            <label class="check"><input type="checkbox" name="is_default" value="1" @checked($address->is_default)> Make this my default address</label>
                                            <button class="btn btn-dark btn-sm" type="submit">Save address</button>
                                        </form>
                                    </details>
                                    <form method="post" action="{{ route('account.addresses.delete', $address) }}" data-confirm="Delete this saved address?">@csrf @method('DELETE')<button class="link address-delete" type="submit">Delete</button></form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <details class="add-address">
                        <summary><span aria-hidden="true">＋</span> Add new address</summary>
                        <form method="post" action="{{ route('account.addresses.store') }}" class="address-form">
                            @csrf
                            <div class="row2">
                                <div class="field"><label for="new-address-name">Full name *</label><input id="new-address-name" name="name" required value="{{ $user->name }}"></div>
                                <div class="field"><label for="new-address-phone">Mobile number *</label><input id="new-address-phone" name="phone" required value="{{ $user->phone }}"></div>
                            </div>
                            <div class="field"><label for="new-address-line1">Address line 1 *</label><input id="new-address-line1" name="line1" required></div>
                            <div class="field"><label for="new-address-line2">Address line 2</label><input id="new-address-line2" name="line2"></div>
                            <div class="row2">
                                <div class="field"><label for="new-address-city">City *</label><input id="new-address-city" name="city" required></div>
                                <div class="field"><label for="new-address-state">State *</label><input id="new-address-state" name="state" required></div>
                            </div>
                            <div class="row2">
                                <div class="field"><label for="new-address-pin">PIN code *</label><input id="new-address-pin" name="pin" required></div>
                                <div class="field"><label for="new-address-type">Address type</label><select id="new-address-type" name="type"><option>Home</option><option>Work</option><option>Other</option></select></div>
                            </div>
                            <div class="field"><label for="new-address-landmark">Landmark</label><input id="new-address-landmark" name="landmark"></div>
                            <label class="check"><input type="checkbox" name="is_default" value="1"> Make this my default address</label>
                            <button class="btn btn-dark" type="submit">Save address</button>
                        </form>
                    </details>
                    @if($user->addresses->isEmpty())<p class="muted">No saved addresses yet. Add one here or it will be saved when you place an order.</p>@endif
                </section>

                <section id="profile" class="account-section">
                    <h2>Update Profile</h2>
                    <form method="post" action="{{ route('account.update') }}" class="account-form" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        <div class="field profile-photo-field">
                            <label for="profile-photo">Profile picture</label>
                            <div class="profile-photo-control">
                                <span class="avatar profile-photo-preview" data-profile-photo-preview>
                                    @if($user->profile_photo_path)
                                        <img src="{{ Storage::disk('public')->url($user->profile_photo_path) }}" alt="{{ $user->name }}">
                                    @else
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    @endif
                                </span>
                                <div><input id="profile-photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-profile-photo-upload><small>JPG, PNG or WebP. Maximum 5 MB.</small>@error('profile_photo')<small class="field-error">{{ $message }}</small>@enderror</div>
                            </div>
                        </div>
                        <div class="row2">
                            <div class="field"><label for="profile-name">Full name *</label><input id="profile-name" name="name" required value="{{ old('name', $user->name) }}"></div>
                            <div class="field"><label for="profile-phone">Mobile number *</label><input id="profile-phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}"></div>
                        </div>
                        <div class="field"><label for="profile-email">Email address</label><input id="profile-email" type="email" name="email" required value="{{ old('email', $user->email) }}"></div>
                        <div class="row2">
                            <div class="field"><label for="profile-gender">Gender</label><select id="profile-gender" name="gender"><option value="">Prefer not to say</option>@foreach(['Female', 'Male', 'Other'] as $gender)<option value="{{ $gender }}" @selected($user->gender === $gender)>{{ $gender }}</option>@endforeach</select></div>
                            <div class="field"><label for="profile-dob">Date of birth</label><input id="profile-dob" type="date" name="dob" value="{{ old('dob', $user->dob?->format('Y-m-d')) }}"></div>
                        </div>
                        <button class="btn btn-dark" type="submit">Save changes</button>
                    </form>
                </section>

                <section id="password" class="account-section">
                    <h2>Change Password</h2>
                    <form method="post" action="{{ route('account.password') }}" class="account-form password-form">
                        @csrf @method('PUT')
                        <div class="field"><label for="current-password">Current password</label><input id="current-password" type="password" name="current_password" autocomplete="current-password" required></div>
                        <div class="field"><label for="new-password">New password</label><input id="new-password" type="password" name="password" autocomplete="new-password" minlength="8" placeholder="At least 8 characters" required></div>
                        <div class="field"><label for="confirm-password">Confirm new password</label><input id="confirm-password" type="password" name="password_confirmation" autocomplete="new-password" required></div>
                        <button class="btn btn-dark" type="submit">Update password</button>
                    </form>
                </section>
            </div>
        </div>
        @foreach($user->orders as $order)
            @php
                $orderSteps = \App\Models\Order::STEPS;
                $orderStepIndex = $order->stepIndex();
                $timelineByStatus = $order->timeline->keyBy('status');
                $deliveryAddress = $order->address ?? [];
            @endphp
            <template id="order-details-{{ $order->order_no }}">
                <div class="order-detail-content">
                    <h2>Order {{ $order->order_no }}</h2>
                    <p class="order-detail-subtitle">Placed on {{ $order->created_at->format('d M Y, h:i A') }} <span class="pill">{{ $order->status }}</span></p>
                    @if($order->status === 'Cancelled')
                        <div class="cancel-note"><b>This order was cancelled.</b></div>
                    @else
                        <div class="tl track-timeline order-detail-timeline">
                            @foreach($orderSteps as $index => $step)
                                @php
                                    $stepClass = $index < $orderStepIndex || ($index === $orderStepIndex && $orderStepIndex === count($orderSteps) - 1) ? 'done' : ($index === $orderStepIndex ? 'cur' : 'todo');
                                    $event = $timelineByStatus->get($step);
                                @endphp
                                <div class="tl-s {{ $stepClass }}"><div class="tl-d" aria-hidden="true">{{ $stepClass === 'done' ? '✓' : '' }}</div><div class="tx"><b>{{ $step }}</b><span>{{ $event?->created_at?->format('d M') ?? '—' }}</span></div></div>
                            @endforeach
                        </div>
                    @endif
                    <h3>Items</h3>
                    <div class="order-detail-items">
                        @foreach($order->items as $item)
                            <div class="mini">
                                @if($item->image)<img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">@else<div class="fabric-art account-fabric-art">SN</div>@endif
                                <div class="grow"><b>{{ $item->name }}</b><span>{{ $item->qty }} m × ₹{{ number_format((float) $item->price, 2) }}</span></div>
                                <strong>₹{{ number_format((float) $item->price * $item->qty, 2) }}</strong>
                            </div>
                        @endforeach
                    </div>
                    <div class="order-detail-totals">
                        <div class="sum-r"><span>Subtotal</span><span>₹{{ number_format((float) $order->subtotal, 2) }}</span></div>
                        @if($order->discount > 0)<div class="sum-r"><span>Discount</span><span>− ₹{{ number_format((float) $order->discount, 2) }}</span></div>@endif
                        <div class="sum-r"><span>Shipping{{ $order->express ? ' (Express)' : '' }}</span><span>{{ $order->shipping > 0 ? '₹' . number_format((float) $order->shipping, 2) : 'FREE' }}</span></div>
                        <div class="sum-r"><span>GST</span><span>₹{{ number_format((float) $order->tax, 2) }}</span></div>
                        <div class="sum-r t"><span>Total</span><b>₹{{ number_format((float) $order->total, 2) }}</b></div>
                    </div>
                    <div class="row2 order-detail-meta">
                        <div><h4>Delivery address</h4>
                            <p>{{ $deliveryAddress['name'] ?? $order->name }}</p>
                            <p>{{ $deliveryAddress['line1'] ?? '' }}@if(!empty($deliveryAddress['line2'])), {{ $deliveryAddress['line2'] }}@endif</p>
                            @if(!empty($deliveryAddress['landmark']))<p>{{ $deliveryAddress['landmark'] }}</p>@endif
                            <p>{{ $deliveryAddress['city'] ?? '' }}, {{ $deliveryAddress['state'] ?? '' }} – {{ $deliveryAddress['pin'] ?? '' }}</p>
                            <p>Phone: {{ $deliveryAddress['phone'] ?? $order->phone }}</p>
                        </div>
                        <div><h4>Payment</h4><p>{{ $order->payment_method }} <span class="pill">{{ $order->payment_status }}</span></p>
                            @if($order->txn_id)<p>Txn: {{ $order->txn_id }}</p>@endif
                            @if($order->notes)<p>Note: {{ $order->notes }}</p>@endif
                        </div>
                    </div>
                    <div class="order-detail-actions">
                        <a class="btn btn-gold btn-sm" href="{{ route('track', ['order_no' => $order->order_no, 'email' => $order->email]) }}">Track order</a>
                        <button class="btn btn-light btn-sm" type="button" data-order-invoice>▤ Invoice</button>
                    </div>
                </div>
            </template>
        @endforeach
    </div>
</main>
<script>
    (() => {
        const closeModal = (wrapper) => {
            wrapper.remove();
            document.body.classList.remove('no-scroll');
        };
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-order-details]');
            if (trigger) {
                const template = document.getElementById(`order-details-${trigger.dataset.orderDetails}`);
                if (!template) return;
                const wrapper = document.createElement('div');
                wrapper.className = 'modal-wrap order-detail-modal-wrap';
                wrapper.setAttribute('role', 'presentation');
                const dialog = document.createElement('div');
                dialog.className = 'modal wide order-detail-modal';
                dialog.setAttribute('role', 'dialog');
                dialog.setAttribute('aria-modal', 'true');
                dialog.setAttribute('aria-label', `Order ${trigger.dataset.orderDetails} details`);
                const close = document.createElement('button');
                close.className = 'modal-x';
                close.type = 'button';
                close.setAttribute('aria-label', 'Close order details');
                close.textContent = '×';
                dialog.append(close, template.content.cloneNode(true));
                wrapper.append(dialog);
                wrapper.addEventListener('click', (modalEvent) => {
                    if (modalEvent.target === wrapper || modalEvent.target.closest('.modal-x')) closeModal(wrapper);
                });
                document.body.append(wrapper);
                document.body.classList.add('no-scroll');
                close.focus();
                return;
            }
            const invoice = event.target.closest('[data-order-invoice]');
            if (invoice) window.print();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                const wrapper = document.querySelector('.order-detail-modal-wrap');
                if (wrapper) closeModal(wrapper);
            }
        });
    })();
</script>
@endsection
