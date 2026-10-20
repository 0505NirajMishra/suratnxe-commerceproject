<?php

namespace App\Http\Controllers;

use App\Models\CartSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::guard('web')->user()?->isAdmin()) {
            Auth::guard('web')->logout();
        }

        if (Auth::check()) {
            return redirect()->route('account');
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $oldSessionId = $request->session()->getId();
        $csrfToken = $request->session()->token();

        if (User::where('email', $data['email'])->where('role', 'admin')->exists()) {
            return back()
                ->withErrors(['email' => 'Administrators must sign in through the admin panel.'])
                ->onlyInput('email');
        }

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->put('_token', $csrfToken);
        $newSessionId = $request->session()->getId();

        \App\Models\CartItem::where('session_id', $oldSessionId)
            ->update(['session_id' => $newSessionId, 'user_id' => Auth::id()]);
        $this->moveCartSession($oldSessionId, $newSessionId);

        return redirect()->intended(route('home'))->with('success', 'You are now logged in.');
    }

    public function register()
    {
        if (Auth::guard('web')->user()?->isAdmin()) {
            Auth::guard('web')->logout();
        }

        if (Auth::check()) {
            return redirect()->route('account');
        }

        return view('auth.register');
    }

    public function store(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAdmin() ? 'admin.dashboard' : 'account');
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => 'accepted',
        ]);
        unset($data['terms']);

        $user = User::create($data + ['role' => 'customer']);
        $oldSessionId = $request->session()->getId();
        $csrfToken = $request->session()->token();
        Auth::login($user);
        $request->session()->put('_token', $csrfToken);
        $newSessionId = $request->session()->getId();

        \App\Models\CartItem::where('session_id', $oldSessionId)
            ->update(['session_id' => $newSessionId, 'user_id' => $user->id]);
        $this->moveCartSession($oldSessionId, $newSessionId);

        return redirect()->route('account')->with('success', 'Welcome to Surat NX.');
    }

    public function logout(Request $request)
    {
        $isAdminLogout = $request->routeIs('admin.logout');
        $csrfToken = $request->session()->token();
        $guard = Auth::guard($isAdminLogout ? 'admin' : 'web');
        $guard->logout();
        if ($isAdminLogout) {
            $request->session()->forget('admin_last_activity');
        }
        $request->session()->regenerate();
        $request->session()->put('_token', $csrfToken);

        if ($isAdminLogout) {
            $message = $request->boolean('idle_timeout')
                ? 'Your admin session expired after 15 minutes of inactivity. Please sign in again.'
                : 'You have been signed out of the admin panel.';

            return redirect()->route('admin.login')->with('success', $message);
        }

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }

    private function moveCartSession(string $oldSessionId, string $newSessionId): void
    {
        CartSession::where('session_id', $oldSessionId)
            ->update(['session_id' => $newSessionId]);
    }
}
