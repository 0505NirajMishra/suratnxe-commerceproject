
@extends('layouts.admin')

@section('title', 'Fabrics – Admin')

@section('content')
<div class="ph-row">
    <h1>Fabrics</h1>
    <a class="btn btn-dark" href="{{ route('admin.products.create') }}">+ Add Fabric</a>
</div>

<div class="panel panel-b">
    <form method="get" class="toolbar">
        <input
            name="q"
            value="{{ request('q') }}"
            placeholder="Search name or SKU"
        >
        <button class="btn btn-light btn-sm" type="submit">Search</button>
    </form>

    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Fabric</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price/metre</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($products as $p)
                    @php
                        $coverImage = $p->images->sortBy('sort_order')->first();
                        $imageUrl = null;

                        if ($coverImage && $coverImage->path) {
                            $imagePath = ltrim($coverImage->path, '/');

                            if (str_starts_with($imagePath, 'public/')) {
                                $imagePath = substr($imagePath, 7);
                            }

                            $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
                        }
                    @endphp

                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;min-width:180px">
                                @if ($imageUrl)
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $p->name }}"
                                        loading="lazy"
                                        style="width:62px;height:62px;flex:0 0 62px;object-fit:contain;background:#f7f7f7;border:1px solid #e5e5e5;border-radius:6px;padding:3px"
                                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                    >
                                    <div style="display:none;width:62px;height:62px;flex:0 0 62px;align-items:center;justify-content:center;background:#f7f7f7;border:1px solid #e5e5e5;border-radius:6px;font-size:10px;text-align:center;color:#b42318">
                                        Image unavailable
                                    </div>
                                @else
                                    <div style="width:62px;height:62px;flex:0 0 62px;display:flex;align-items:center;justify-content:center;background:#f7f7f7;border:1px solid #e5e5e5;border-radius:6px;font-size:10px;text-align:center;color:#777">
                                        No image
                                    </div>
                                @endif

                                <span>{{ $p->name }}</span>
                            </div>
                        </td>

                        <td>{{ $p->sku }}</td>
                        <td>{{ $p->category->name ?? '—' }}</td>
                        <td>₹{{ number_format($p->price, 2) }}</td>
                        <td>{{ $p->stock }}</td>
                        <td>{{ $p->status === 'active' ? 'Active' : 'Inactive' }}</td>

                        <td>
                            <div class="ph-act">
                                <a class="btn btn-light btn-sm"
                                   href="{{ route('admin.products.edit', $p) }}">
                                    Edit
                                </a>

                                <form
                                    method="post"
                                    action="{{ route('admin.products.delete', $p) }}"
                                    data-confirm="Delete this fabric?"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-line btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No fabrics found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
</div>
@endsection
