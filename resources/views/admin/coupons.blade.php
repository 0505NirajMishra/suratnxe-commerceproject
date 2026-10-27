@extends('layouts.admin') @section('title', 'Coupons – Admin') @section('content')<div class="ph-row">
    <h1>Coupons</h1>
</div>
<div class="panel panel-b">
    <h2>Create coupon</h2>
    <form method="post" action="{{ route('admin.coupons.store') }}" class="grid2">@csrf<div class="field">
            <label>Code</label><input name="code" required></div>
        <div class="field"><label>Type</label><select name="type">
                <option value="percent">Percentage</option>
                <option value="flat">Flat amount</option>
            </select></div>
        <div class="field"><label>Value</label><input type="number" min="0" step="0.01" name="value"
                required></div>
        <div class="field"><label>Minimum order</label><input type="number" min="0" name="min_order"
                value="0"></div>
        <div class="field"><label>Maximum discount</label><input type="number" min="0" name="max_discount"
                value="0"></div>
        <div class="field"><label>Description</label><input name="description"></div>
        <div><button class="btn btn-dark">Create Coupon</button></div>
    </form>
    <hr>
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min order</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($coupons as $c)
                    <tr>
                        <td>{{ $c->code }}</td>
                        <td>{{ $c->type }}</td>
                        <td>{{ $c->type === 'percent' ? $c->value . '%' : '₹' . $c->value }}</td>
                        <td>₹{{ $c->min_order }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.coupons.delete', $c) }}"
                                data-confirm="Delete this coupon?">@csrf @method('DELETE')<button
                                    class="btn btn-line btn-sm">Delete</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>@endsection
