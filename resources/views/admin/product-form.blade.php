@extends('layouts.admin')
@section('title', ($product->exists ? 'Edit' : 'Add').' Fabric – Admin')
@section('content')
<div class="ph-row">
    <div><h1>{{ $product->exists ? 'Edit Fabric' : 'Add Fabric' }}</h1></div>
    <div class="ph-act"><a class="btn btn-light btn-sm" href="{{ route('admin.products') }}">← All fabrics</a></div>
</div>
<section class="panel">
    <div class="panel-b">
        <form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
            @csrf
            @if ($product->exists) @method('PUT') @endif
            <div class="grid2">
                <div class="field"><label for="fabric-name">Fabric name *</label><input id="fabric-name" name="name" required value="{{ old('name', $product->name) }}"></div>
                <div class="field"><label for="fabric-sku">SKU *</label><input id="fabric-sku" name="sku" required value="{{ old('sku', $product->sku) }}"></div>
                <div class="field"><label for="fabric-category">Category *</label><select id="fabric-category" name="category_id" required>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="field"><label for="fabric-coupon">Product coupon</label><select id="fabric-coupon" name="coupon_id"><option value="">No product coupon</option>@foreach ($coupons as $coupon)<option value="{{ $coupon->id }}" @selected((string) old('coupon_id', $product->coupon_id) === (string) $coupon->id)>{{ $coupon->code }}{{ $coupon->isUsable() ? '' : ' (inactive/expired)' }}</option>@endforeach</select><small>Only this fabric will receive the selected coupon automatically.</small></div>
                <div class="field"><label for="fabric-status">Storefront status *</label><select id="fabric-status" name="status" required><option value="active" @selected(old('status', $product->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $product->status) !== 'active')>Inactive</option></select></div>
                <div class="field"><label for="fabric-price">Price per metre (₹) *</label><input id="fabric-price" type="number" min="0" step="0.01" name="price" required value="{{ old('price', $product->price) }}"></div>
                <div class="field"><label for="fabric-mrp">MRP (₹)</label><input id="fabric-mrp" type="number" min="0" step="0.01" name="mrp" value="{{ old('mrp', $product->mrp) }}"></div>
                <div class="field"><label for="fabric-stock">Stock (metres) *</label><input id="fabric-stock" type="number" min="0" name="stock" required value="{{ old('stock', $product->stock ?? 0) }}"></div>
                <div class="field"><label for="fabric-width">Width *</label><input id="fabric-width" name="width" required maxlength="30" value="{{ old('width', $product->width) }}" placeholder="58 inch"></div>
                <div class="field"><label for="fabric-composition">Fabric composition *</label><input id="fabric-composition" name="fabric" required maxlength="120" value="{{ old('fabric', $product->fabric) }}"></div>
                <div class="field"><label for="fabric-usage">Usage *</label><input id="fabric-usage" name="usage" required maxlength="190" value="{{ old('usage', $product->usage) }}"></div>
                <div class="field"><label for="fabric-care">Care instructions *</label><input id="fabric-care" name="care" required maxlength="190" value="{{ old('care', $product->care) }}"></div>
                <div class="field"><label for="fabric-colors">Colours *</label><input id="fabric-colors" name="colors" required maxlength="190" value="{{ old('colors', $product->colors) }}"></div>
                <div class="field"><label for="fabric-tags">Tags (comma separated)</label><input id="fabric-tags" name="tags" value="{{ old('tags', implode(', ', $product->tags ?? [])) }}" placeholder="new, trending, best"></div>
                <div class="field" style="grid-column:1/-1"><label for="fabric-description">Description *</label><textarea id="fabric-description" name="description" rows="5" required maxlength="5000">{{ old('description', $product->description) }}</textarea></div>
                <div class="field" style="grid-column:1/-1">
                    <label for="fabric-images">Upload product images {{ $product->exists ? '(up to 10, max 5MB each)' : '(at least one required, up to 10, max 5MB each)' }}</label>
                    <input id="fabric-images" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" @required(!$product->exists)>
                    @if ($product->exists && $product->images->count())
                        <p>Current images:</p>
                        <div class="grid g4">@foreach ($product->images as $image)<img src="{{ Storage::url($image->path) }}" alt="Fabric image" style="width:100%;aspect-ratio:1;object-fit:cover">@endforeach</div>
                    @endif
                </div>
            </div>
            <button class="btn btn-dark" type="submit">Save Fabric</button>
        </form>
    </div>
</section>
@endsection
