
@extends('layouts.admin')

@section('title', ($product->exists ? 'Edit' : 'Add').' Fabric – Admin')

@section('content')
<div class="ph-row">
    <div>
        <h1>{{ $product->exists ? 'Edit Fabric' : 'Add Fabric' }}</h1>
    </div>
    <div class="ph-act">
        <a class="btn btn-light btn-sm" href="{{ route('admin.products') }}">
            ← All fabrics
        </a>
    </div>
</div>

<section class="panel">
    <div class="panel-b">
        <form method="post"
              enctype="multipart/form-data"
              action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">

            @csrf
            @if ($product->exists)
                @method('PUT')
            @endif

            <div class="grid2">
                <div class="field">
                    <label for="fabric-name">Fabric name *</label>
                    <input id="fabric-name" name="name" required
                           value="{{ old('name', $product->name) }}">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="field">
                    <label for="fabric-sku">SKU *</label>
                    <input id="fabric-sku" name="sku" required
                           value="{{ old('sku', $product->sku) }}">
                    @error('sku') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="field">
                    <label for="fabric-category">Category *</label>
                    <select id="fabric-category" name="category_id" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected(old('category_id', $product->category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="fabric-coupon">Product coupon</label>
                    <select id="fabric-coupon" name="coupon_id">
                        <option value="">No product coupon</option>
                        @foreach ($coupons as $coupon)
                            <option value="{{ $coupon->id }}"
                                @selected((string) old('coupon_id', $product->coupon_id) === (string) $coupon->id)>
                                {{ $coupon->code }}{{ $coupon->isUsable() ? '' : ' (inactive/expired)' }}
                            </option>
                        @endforeach
                    </select>
                    <small>Only this fabric will receive the selected coupon automatically.</small>
                </div>

                <div class="field">
                    <label for="fabric-status">Storefront status *</label>
                    <select id="fabric-status" name="status" required>
                        <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $product->status) !== 'active')>Inactive</option>
                    </select>
                </div>

                <div class="field">
                    <label for="fabric-price">Price per metre (₹) *</label>
                    <input id="fabric-price" type="number" min="0" step="0.01"
                           name="price" required value="{{ old('price', $product->price) }}">
                </div>

                <div class="field">
                    <label for="fabric-mrp">MRP (₹)</label>
                    <input id="fabric-mrp" type="number" min="0" step="0.01"
                           name="mrp" value="{{ old('mrp', $product->mrp) }}">
                </div>

                <div class="field">
                    <label for="fabric-stock">Stock (metres) *</label>
                    <input id="fabric-stock" type="number" min="0"
                           name="stock" required value="{{ old('stock', $product->stock ?? 0) }}">
                </div>

                <div class="field">
                    <label for="fabric-width">Width *</label>
                    <input id="fabric-width" name="width" required maxlength="30"
                           value="{{ old('width', $product->width) }}" placeholder="58 inch">
                </div>

                <div class="field">
                    <label for="fabric-composition">Fabric composition *</label>
                    <input id="fabric-composition" name="fabric" required maxlength="120"
                           value="{{ old('fabric', $product->fabric) }}">
                </div>

                <div class="field">
                    <label for="fabric-usage">Usage *</label>
                    <input id="fabric-usage" name="usage" required maxlength="190"
                           value="{{ old('usage', $product->usage) }}">
                </div>

                <div class="field">
                    <label for="fabric-care">Care instructions *</label>
                    <input id="fabric-care" name="care" required maxlength="190"
                           value="{{ old('care', $product->care) }}">
                </div>

                <div class="field">
                    <label for="fabric-colors">Colours *</label>
                    <input id="fabric-colors" name="colors" required maxlength="190"
                           value="{{ old('colors', $product->colors) }}">
                </div>

                <div class="field">
                    <label for="fabric-tags">Tags (comma separated)</label>
                    <input id="fabric-tags" name="tags"
                           value="{{ old('tags', implode(', ', $product->tags ?? [])) }}"
                           placeholder="new, trending, best">
                </div>

                <div class="field" style="grid-column:1/-1">
                    <label for="fabric-description">Description *</label>
                    <textarea id="fabric-description" name="description"
                              rows="5" required maxlength="5000">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="field" style="grid-column:1/-1">
                    <label for="fabric-images">
                        Upload product images
                        {{ $product->exists ? '(up to 10; select new images to add)' : '(at least one required, up to 10)' }}
                    </label>

                    <input
                        id="fabric-images"
                        type="file"
                        name="images[]"
                        multiple
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        @required(!$product->exists)
                    >

                    @error('images')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    @error('images.*')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                    @if ($product->exists)
                        @php
                            $currentImages = $product->images
                                ->sortBy('sort_order')
                                ->values();
                        @endphp

                        @if ($currentImages->isNotEmpty())
                            <p style="margin-top:18px;font-weight:600">Current images:</p>

                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:14px;margin-top:10px">
                                @foreach ($currentImages as $image)
                                    @php
                                        // Normalize paths saved in the database, including Windows paths.
                                        $imagePath = str_replace('\\', '/', trim((string) $image->path));
                                        $imagePath = ltrim($imagePath, '/');

                                        // If an absolute storage path was saved, keep only the relative file path.
                                        $storageMarker = 'storage/app/public/';
                                        $storagePosition = strpos($imagePath, $storageMarker);

                                        if ($storagePosition !== false) {
                                            $imagePath = substr($imagePath, $storagePosition + strlen($storageMarker));
                                        } elseif (str_starts_with($imagePath, 'public/storage/')) {
                                            $imagePath = substr($imagePath, strlen('public/storage/'));
                                        } elseif (str_starts_with($imagePath, 'storage/')) {
                                            $imagePath = substr($imagePath, strlen('storage/'));
                                        } elseif (str_starts_with($imagePath, 'public/')) {
                                            $imagePath = substr($imagePath, strlen('public/'));
                                        }

                                        // Use the current host, e.g. 127.0.0.1:8000.
                                        $imageUrl = url('/storage/' . $imagePath);
                                    @endphp

                                    <div style="border:1px solid #ddd;border-radius:8px;padding:7px;min-width:0">
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $product->name }} fabric image"
                                            loading="lazy"
                                            style="display:block;width:100%;aspect-ratio:1/1;object-fit:contain;background:#f7f7f7;border-radius:5px"
                                            onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
                                        >

                                        <div style="display:none;overflow-wrap:anywhere;font-size:12px;color:#b42318;padding:8px 0">
                                            Image could not load.
                                            <br>
                                            {{ $imagePath }}
                                        </div>

                                        <small style="display:block;margin-top:6px;overflow-wrap:anywhere;color:#777">
                                            {{ basename($imagePath) }}
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p style="margin-top:12px;color:#a16207">
                                No image records found for this fabric. Select the images again and save.
                            </p>
                        @endif
                    @endif
                </div>
            </div>

            <button class="btn btn-dark" type="submit">Save Fabric</button>
        </form>
    </div>
</section>
@endsection
