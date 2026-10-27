<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to secure payment - Surat NX</title>
</head>
<body>
    <main>
        <h1>Redirecting to PayU</h1>
        <p>Your order is saved securely. Do not refresh or close this page while the payment window opens.</p>
        <form id="payu-payment-form" method="post" action="{{ $paymentUrl }}">
            @foreach ($paymentData as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <button type="submit">Continue to PayU</button>
        </form>
    </main>
    <script>document.getElementById('payu-payment-form').submit();</script>
</body>
</html>
