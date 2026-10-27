<?php
namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PayUService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
	public function index(CartService $cart, PayUService $payu)
	{
		$totals = $cart->totals();
		if ($totals['items']->isEmpty()) {
			return redirect()->route('cart')->withErrors(['cart' => 'Your cart is empty.']);
		}

		$customer = auth()->user()?->isAdmin() ? null : auth()->user();
		$addresses = $customer?->addresses()
			->orderByDesc('is_default')
			->latest()
			->get()
			->unique(fn (Address $address) => json_encode(array_map(
				fn ($value) => strtolower(preg_replace('/\s+/', ' ', trim((string) $value))),
				$address->only(['name', 'phone', 'line1', 'line2', 'landmark', 'city', 'state', 'pin', 'type'])
			)))
			->values() ?? collect();
		$expressTotals = $cart->totals(true);
		$onlineEnabled = $payu->isConfigured();

		return view('store.checkout-page', compact('totals', 'expressTotals', 'addresses', 'onlineEnabled'));
	}

	public function place(Request $request, OrderService $orders, CartService $cart, PayUService $payu)
	{
		$data = $request->validate([
			'name' => 'required|string|max:120',
			'email' => 'required|email|max:190',
			'phone' => 'required|string|max:20',
			'line1' => 'required|string|max:190',
			'line2' => 'nullable|string|max:190',
			'landmark' => 'nullable|string|max:190',
			'city' => 'required|string|max:100',
			'state' => 'required|string|max:100',
			'pin' => 'required|string|max:10',
			'payment_method' => 'required|in:COD,PayU',
			'express' => 'nullable|boolean',
			'notes' => 'nullable|string|max:1000',
		]);

		$address = collect($data)->only(['name', 'phone', 'line1', 'line2', 'landmark', 'city', 'state', 'pin'])->all();
		$customer = auth()->user()?->isAdmin() ? null : auth()->user();
		$user = ['id' => $customer?->id, 'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']];

		if ($data['payment_method'] === 'PayU') {
			abort_unless($payu->isConfigured(), 503, 'Online payments are not configured.');

			$totals = $cart->totals((bool) ($data['express'] ?? false));
			if ($totals['items']->isEmpty()) {
				return redirect()->route('cart')->withErrors(['cart' => 'Your cart is empty.']);
			}

			$amountPaise = (int) round($totals['total'] * 100);
			if ($amountPaise < 100) {
				return back()->withInput()->withErrors(['payment_method' => 'Online payments require a minimum order total of INR 1.']);
			}

			$txnid = 'SNX'.Str::upper(Str::random(20));
			$order = $orders->place($user, $address, 'PayU', (bool) ($data['express'] ?? false), $data['notes'] ?? null, $txnid, $amountPaise);

			return view('payments.payu-redirect', [
				'paymentUrl' => $payu->paymentUrl(),
				'paymentData' => $payu->paymentData($order),
			]);
		}

		$order = $orders->place($user, $address, 'COD', (bool) ($data['express'] ?? false), $data['notes'] ?? null);

		if ($customer) {
			$customer->addresses()->firstOrCreate(
				$address + ['type' => 'Home'],
				['is_default' => !$customer->addresses()->exists()]
			);
		}

		$request->session()->put('completed_order_no', $order->order_no);

		return redirect()->route('order.success', $order)->with('success', 'Order placed successfully.');
	}

	public function success(Request $request, Order $order)
	{
		$customer = auth()->user()?->isAdmin() ? null : auth()->user();
		if ($customer) {
			abort_if($order->user_id !== $customer->id, 403);
		} else {
			abort_unless($request->session()->get('completed_order_no') === $order->order_no, 403);
		}

		return view('store.order-success', compact('order'));
	}
}
