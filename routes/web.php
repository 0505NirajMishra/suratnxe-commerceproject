<?php

use App\Http\Controllers\{
    AccountController,
    AdminController,
    AuthController,
    CartController,
    CheckoutController,
    ContactController,
    StoreController,
    WishlistController
};
use Illuminate\Support\Facades\Route;

Route::get('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'requestForm'])
    ->middleware('guest')
    ->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendLink'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('password.email');
Route::get('/verify-password-otp', [\App\Http\Controllers\PasswordResetController::class, 'verifyForm'])
    ->middleware('guest')
    ->name('password.otp.form');
Route::post('/verify-password-otp', [\App\Http\Controllers\PasswordResetController::class, 'verifyOtp'])
    ->middleware(['guest', 'throttle:8,1'])
    ->name('password.otp.verify');
Route::get('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'resetForm'])
    ->middleware('guest')
    ->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset'])
    ->middleware(['guest', 'throttle:8,1'])
    ->name('password.update');

Route::post('/checkout/razorpay/orders', [\App\Http\Controllers\RazorpayController::class, 'create'])
    ->middleware(['auth', 'customer', 'throttle:10,1'])
    ->name('checkout.razorpay.create');
Route::post('/checkout/razorpay/verify', [\App\Http\Controllers\RazorpayController::class, 'verify'])
    ->middleware(['auth', 'customer', 'throttle:10,1'])
    ->name('checkout.razorpay.verify');
Route::post('/webhooks/razorpay', [\App\Http\Controllers\RazorpayController::class, 'webhook'])
    ->name('webhooks.razorpay');
Route::post('/products/{product:slug}/reviews', [\App\Http\Controllers\ProductReviewController::class, 'store'])
    ->middleware(['auth', 'customer'])
    ->name('reviews.store');

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/shop', [StoreController::class, 'shop'])->name('shop');
Route::get('/products/{product:slug}', [StoreController::class, 'product'])->name('product');
Route::get('/about', [StoreController::class, 'page'])->defaults('page', 'about')->name('about');
Route::get('/policy', [StoreController::class, 'page'])->defaults('page', 'policy')->name('policy');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:8,1')->name('contact.send');
Route::post('/newsletter', [ContactController::class, 'subscribe'])->middleware('throttle:10,1')->name('newsletter');
Route::get('/track-order', [StoreController::class, 'track'])->name('track');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:8,1')->name('login.post');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:8,1')->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon', [CartController::class, 'coupon'])->name('cart.coupon');
Route::get('/checkout', [CheckoutController::class, 'index'])->middleware(['auth', 'customer'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'place'])->middleware(['auth', 'customer', 'throttle:10,1'])->name('checkout.place');
Route::get('/order-success/{order:order_no}', [CheckoutController::class, 'success'])->name('order.success');

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'password'])->name('account.password');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::patch('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('account.addresses.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'deleteAddress'])->name('account.addresses.delete');
    Route::post('/account/orders/{order:order_no}/reorder', [AccountController::class, 'reorder'])->name('account.orders.reorder');
    Route::post('/account/orders/{order:order_no}/cancel', [AccountController::class, 'cancelOrder'])->name('account.orders.cancel');
});

Route::get('/returns', [\App\Http\Controllers\ReturnRequestController::class, 'index'])
    ->middleware(['auth', 'customer'])
    ->name('returns.index');
Route::post('/returns/{order:order_no}', [\App\Http\Controllers\ReturnRequestController::class, 'store'])
    ->middleware(['auth', 'customer', 'throttle:5,1'])
    ->name('returns.store');
Route::get('/returns/{returnRequest}/photos/{photo}', [\App\Http\Controllers\ReturnRequestController::class, 'customerPhoto'])
    ->middleware(['auth', 'customer'])
    ->whereNumber('photo')
    ->name('returns.photo');
Route::get('/admin/returns', [\App\Http\Controllers\ReturnRequestController::class, 'adminIndex'])
    ->middleware('admin')
    ->name('admin.returns');
Route::patch('/admin/returns/{returnRequest}', [\App\Http\Controllers\ReturnRequestController::class, 'adminUpdate'])
    ->middleware('admin')
    ->name('admin.returns.update');
Route::patch('/admin/settings/return-window', [\App\Http\Controllers\ReturnRequestController::class, 'saveReturnWindow'])
    ->middleware('admin')
    ->name('admin.settings.return-window');
Route::patch('/admin/settings/delivery', [AdminController::class, 'deliverySettingsSave'])
    ->middleware('admin')
    ->name('admin.settings.delivery');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('login');
    Route::post('/login', [AdminController::class, 'authenticate'])->name('login.post');

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/activity', [AdminController::class, 'activity'])->name('activity');
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/products', [AdminController::class, 'products'])->name('products');
        Route::get('/products/create', [AdminController::class, 'productCreate'])->name('products.create');
        Route::post('/products', [AdminController::class, 'productSave'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminController::class, 'productEdit'])->name('products.edit');
        Route::put('/products/{product}', [AdminController::class, 'productSave'])->name('products.update');
        Route::delete('/products/{product}', [AdminController::class, 'productDelete'])->name('products.delete');
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'categorySave'])->name('categories.store');
        Route::patch('/categories/{category}', [AdminController::class, 'categoryUpdate'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'categoryDelete'])->name('categories.delete');
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::get('/orders/export', [AdminController::class, 'ordersExport'])->name('orders.export');
        Route::patch('/orders/{order:order_no}', [AdminController::class, 'orderUpdate'])->name('orders.update');
        Route::get('/returns/{returnRequest}/photos/{photo}', [\App\Http\Controllers\ReturnRequestController::class, 'adminPhoto'])->whereNumber('photo')->name('returns.photo');
        Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
        Route::get('/customers/{customer}', [AdminController::class, 'customer'])->name('customers.show');
        Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons');
        Route::post('/coupons', [AdminController::class, 'couponSave'])->name('coupons.store');
        Route::delete('/coupons/{coupon}', [AdminController::class, 'couponDelete'])->name('coupons.delete');
        Route::get('/enquiries', [AdminController::class, 'enquiries'])->name('enquiries');
        Route::patch('/enquiries/{enquiry}', [AdminController::class, 'enquiryUpdate'])->name('enquiries.update');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'settingsSave'])->name('settings.update');
        Route::patch('/settings/contact', [AdminController::class, 'contactSettingsSave'])->name('settings.contact');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('/profile', [AdminController::class, 'profileSave'])->name('profile.update');
    });
});
