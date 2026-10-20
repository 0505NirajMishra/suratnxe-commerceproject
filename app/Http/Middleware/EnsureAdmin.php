<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        Auth::shouldUse('admin');
        $guard = Auth::guard('admin');
        $user = $guard->user();
        if (!$user) {
            return $this->preventCaching(redirect()->route('admin.login'));
        }
        abort_unless($user->isAdmin(), 403);

        $lastActivity = (int) $request->session()->get('admin_last_activity', 0);
        if ($lastActivity > 0 && $lastActivity <= now()->subMinutes(15)->timestamp) {
            $guard->logout();
            $request->session()->forget('admin_last_activity');
            $request->session()->regenerateToken();

            return $this->preventCaching(
                redirect()->route('admin.login')->with('success', 'Your admin session expired after 15 minutes of inactivity. Please sign in again.')
            );
        }

        $request->session()->put('admin_last_activity', now()->timestamp);

        return $this->preventCaching($next($request));
    }

    private function preventCaching($response)
    {
        return $response->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
