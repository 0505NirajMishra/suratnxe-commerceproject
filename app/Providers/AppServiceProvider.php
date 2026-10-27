<?php
namespace App\Providers;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
class AppServiceProvider extends ServiceProvider
{
 public function register(): void {}
 public function boot(): void
 {
  RateLimiter::for('mail', fn () => Limit::perSecond(1));

  View::composer('layouts.admin', function ($view) {
   if (!auth()->check() || !auth()->user()->isAdmin()) {
    $view->with('adminNotifications', []);
    return;
   }

   $view->with('adminNotifications', [
    [
     'label' => 'Orders to process',
     'count' => Order::whereIn('status', ['Pending', 'Confirmed', 'Processing', 'Packed'])->count(),
     'url' => route('admin.orders'),
    ],
    [
     'label' => 'New enquiries',
     'count' => Enquiry::where('status', 'New')->count(),
     'url' => route('admin.enquiries'),
    ],
    [
     'label' => 'Return requests',
     'count' => ReturnRequest::where('status', 'Requested')->count(),
     'url' => route('admin.returns'),
    ],
   ]);
  });
 }
}
