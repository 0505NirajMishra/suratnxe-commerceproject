@extends('layouts.admin')
@section('title', 'Categories – Admin')
@section('content')
<div class="ph-row"><h1>Categories</h1></div>
<section class="panel">
    <div class="panel-h"><h3>Add Category</h3></div>
    <div class="panel-b">
        <form method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid2">
                <div class="field"><label for="category-name">Name *</label><input id="category-name" name="name" required maxlength="120" value="{{ old('name') }}"></div>
                <div class="field"><label for="category-code">Unique code *</label><input id="category-code" name="code" required maxlength="10" value="{{ old('code') }}"></div>
                <div class="field"><label for="category-image">Category image *</label><input id="category-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" required><small>JPG, PNG or WebP.</small></div>
                <div class="field"><label for="category-description">Description</label><input id="category-description" name="description" maxlength="1000" value="{{ old('description') }}"></div>
                <label class="field category-active"><span>Visible on storefront</span><input type="checkbox" name="is_active" value="1" checked></label>
            </div>
            <button class="btn btn-dark" type="submit">Add Category</button>
        </form>
    </div>
</section>

<section class="panel panel-b" style="margin-top:20px">
    <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr><th>Category</th><th>Code</th><th>Products</th><th>Visibility</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td colspan="5">
                            <form id="category-edit-{{ $category->id }}" method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="category-edit">
                                @csrf @method('PATCH')
                                <div class="category-edit-current">
                                    @if($category->image)
                                        <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}" class="category-admin-image">
                                    @else
                                        <span class="category-admin-image category-admin-placeholder">No image</span>
                                    @endif
                                    <div><strong>{{ $category->name }}</strong><span class="sub">{{ $category->products_count }} fabrics · {{ $category->is_active ? 'Active' : 'Inactive' }}</span></div>
                                </div>
                                <div class="grid2 category-edit-fields">
                                    <div class="field"><label for="category-name-{{ $category->id }}">Name *</label><input id="category-name-{{ $category->id }}" name="name" required maxlength="120" value="{{ $category->name }}"></div>
                                    <div class="field"><label for="category-code-{{ $category->id }}">Code *</label><input id="category-code-{{ $category->id }}" name="code" required maxlength="10" value="{{ $category->code }}"></div>
                                    <div class="field"><label for="category-image-{{ $category->id }}">Replace image</label><input id="category-image-{{ $category->id }}" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
                                    <div class="field"><label for="category-description-{{ $category->id }}">Description</label><input id="category-description-{{ $category->id }}" name="description" maxlength="1000" value="{{ $category->description }}"></div>
                                    <div class="field"><label for="category-order-{{ $category->id }}">Sort order</label><input id="category-order-{{ $category->id }}" type="number" name="sort_order" min="0" max="65535" required value="{{ $category->sort_order }}"></div>
                                    <label class="field category-active"><span>Visible on storefront</span><input type="checkbox" name="is_active" value="1" @checked($category->is_active)></label>
                                </div>
                            </form>
                            <div class="ph-act category-row-actions">
                                <button class="btn btn-dark btn-sm" type="submit" form="category-edit-{{ $category->id }}">Save Category</button>
                                <form method="post" action="{{ route('admin.categories.delete', $category) }}" data-confirm="Delete this category and its products?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-line btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $categories->links() }}
</section>
@endsection
