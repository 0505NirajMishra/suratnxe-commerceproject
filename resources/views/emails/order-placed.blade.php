<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $orderDetails['number'] }}</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f5f1e9;color:#201c16;font-family:Arial,sans-serif">
    <main style="max-width:680px;margin:0 auto;padding:28px;background:#fffdfa;border:1px solid #e5ded2">
        <p style="margin:0 0 8px;color:#90734d;font-size:12px;font-weight:bold;letter-spacing:2px">SURAT NX</p>
        <h1 style="margin:0 0 12px;font-family:Georgia,serif;font-size:26px">{{ $storeCopy ? 'New order received' : 'Thank you for your order' }}</h1>
        <p style="margin:0 0 20px;line-height:1.6">{{ $storeCopy ? 'A new order has been placed on your store.' : 'Your fabric order has been received. Here is a brief summary of your order.' }}</p>

        <table role="presentation" cellspacing="0" cellpadding="7" border="0" width="100%" style="border-collapse:collapse;margin-bottom:22px">
            <tr><th align="left" style="border-bottom:1px solid #e5ded2">Order</th><td style="border-bottom:1px solid #e5ded2">{{ $orderDetails['number'] }}</td></tr>
            <tr><th align="left" style="border-bottom:1px solid #e5ded2">Order date</th><td style="border-bottom:1px solid #e5ded2">{{ $orderDetails['date'] }}</td></tr>
            <tr><th align="left" style="border-bottom:1px solid #e5ded2">Order status</th><td style="border-bottom:1px solid #e5ded2">{{ $orderDetails['status'] }}</td></tr>
            <tr><th align="left" style="border-bottom:1px solid #e5ded2">Payment</th><td style="border-bottom:1px solid #e5ded2">{{ $orderDetails['payment'] }}</td></tr>
        </table>

        <h2 style="margin:0 0 10px;font-family:Georgia,serif;font-size:20px">Customer and delivery details</h2>
        <table role="presentation" cellspacing="0" cellpadding="7" border="0" width="100%" style="border-collapse:collapse;margin-bottom:22px">
            <tr><th align="left">Name</th><td>{{ $orderDetails['customer'] }}</td></tr>
            <tr><th align="left">Email</th><td>{{ $orderDetails['email'] }}</td></tr>
            <tr><th align="left">Phone</th><td>{{ $orderDetails['phone'] }}</td></tr>
            <tr><th align="left" valign="top">Address</th><td>{{ $orderDetails['address'] }}</td></tr>
        </table>

        <h2 style="margin:0 0 10px;font-family:Georgia,serif;font-size:20px">Items ordered</h2>
        <table role="presentation" cellspacing="0" cellpadding="8" border="0" width="100%" style="border-collapse:collapse;margin-bottom:18px">
            <thead>
                <tr style="background:#f5f1e9">
                    <th align="left">Product</th>
                    <th align="right">Qty</th>
                    <th align="right">Price</th>
                    <th align="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td style="border-bottom:1px solid #e5ded2">
                            @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" width="64" height="64" style="display:block;width:64px;height:64px;object-fit:cover;margin-bottom:6px">
                            @endif
                            <strong>{{ $item['name'] }}</strong><br>
                            <span style="color:#6b6259;font-size:12px">{{ $item['sku'] }}</span>
                        </td>
                        <td align="right" style="border-bottom:1px solid #e5ded2;white-space:nowrap">{{ $item['qty'] }} m</td>
                        <td align="right" style="border-bottom:1px solid #e5ded2;white-space:nowrap">₹{{ number_format($item['price'], 2) }}/m</td>
                        <td align="right" style="border-bottom:1px solid #e5ded2;white-space:nowrap">₹{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table role="presentation" cellspacing="0" cellpadding="7" border="0" width="100%" style="border-collapse:collapse">
            <tr><th align="right">Subtotal</th><td align="right">₹{{ number_format($orderDetails['subtotal'], 2) }}</td></tr>
            @if($orderDetails['discount'] > 0)
                <tr><th align="right">Discount{{ $orderDetails['coupon'] ? ' ('.$orderDetails['coupon'].')' : '' }}</th><td align="right">− ₹{{ number_format($orderDetails['discount'], 2) }}</td></tr>
            @endif
            <tr><th align="right">Shipping</th><td align="right">{{ $orderDetails['shipping'] > 0 ? '₹'.number_format($orderDetails['shipping'], 2) : 'Free' }}</td></tr>
            <tr><th align="right">GST</th><td align="right">₹{{ number_format($orderDetails['tax'], 2) }}</td></tr>
            <tr><th align="right" style="border-top:1px solid #e5ded2;font-size:18px">Total</th><td align="right" style="border-top:1px solid #e5ded2;font-size:18px;font-weight:bold">₹{{ number_format($orderDetails['total'], 2) }}</td></tr>
        </table>

        <p style="margin:22px 0 0;color:#6b6259;font-size:13px;line-height:1.6">
            {{ $storeCopy ? 'Please sign in to the admin panel to process this order.' : 'We will email you when your order status changes.' }}
        </p>
        <p style="margin:18px 0 0;color:#6b6259;font-size:12px">Surat NX · {{ \App\Models\Setting::get('email') }}</p>
    </main>
</body>
</html>
