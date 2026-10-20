<?php
use App\Http\Middleware\EnsureStorefrontAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
 ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
 ->withMiddleware(function (Middleware $middleware) { $middleware->alias(['admin' => App\Http\Middleware\EnsureAdmin::class, 'customer' => App\Http\Middleware\EnsureCustomer::class]); $middleware->appendToGroup('web', EnsureStorefrontAccess::class); $middleware->validateCsrfTokens(except: ['webhooks/razorpay']); })
 ->withExceptions(function (Exceptions $exceptions) {}) ->create();
