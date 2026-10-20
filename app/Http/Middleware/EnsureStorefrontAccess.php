<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureStorefrontAccess
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin', 'admin/*', 'webhooks/razorpay')) {
            return $next($request);
        }

        Auth::shouldUse('web');

        if (Auth::guard('web')->user()?->isAdmin()) {
            Auth::guard('web')->logout();
        }

        return $next($request);
    }
}
