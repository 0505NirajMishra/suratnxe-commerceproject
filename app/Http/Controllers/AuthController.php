<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationOtpMail;
use App\Models\CartSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Throwable;

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

        $request->merge(['email' => Str::lower(trim((string) $request->input('email', '')))]);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => 'accepted',
        ]);
        unset($data['terms']);

        DB::table('registration_otps')->where('expires_at', '<=', now())->delete();
        $otp = (string) random_int(100000, 999999);
        DB::table('registration_otps')->updateOrInsert(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password_hash' => Hash::make($data['password']),
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $request->session()->put('registration_email', $data['email']);

        if (!$this->queueRegistrationOtp($data['email'], $otp)) {
            return redirect()->route('register.otp.form')->withErrors([
                'otp' => 'We could not queue the verification email. Please try resending it.',
            ]);
        }

        return redirect()->route('register.otp.form')->with('status', 'Your verification code is being sent to your email address.');
    }

    public function registrationOtpForm(Request $request)
    {
        $email = $request->session()->get('registration_email');
        if (!$email || !DB::table('registration_otps')->where('email', $email)->exists()) {
            $request->session()->forget('registration_email');

            return redirect()->route('register');
        }

        return view('auth.verify-registration-otp', compact('email'));
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $data = $request->validate(['otp' => ['required', 'digits:6']]);
        $email = $request->session()->get('registration_email');
        if (!$email) {
            return redirect()->route('register')->withErrors([
                'email' => 'Your registration session expired. Please register again.',
            ]);
        }

        $user = DB::transaction(function () use ($email, $data) {
            $challenge = DB::table('registration_otps')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (!$challenge || $challenge->attempts >= 5 || now()->greaterThan($challenge->expires_at)) {
                return null;
            }

            if (!Hash::check($data['otp'], $challenge->otp_hash)) {
                DB::table('registration_otps')->where('id', $challenge->id)->increment('attempts');
                return null;
            }

            if (User::where('email', $email)->exists()) {
                DB::table('registration_otps')->where('id', $challenge->id)->delete();
                return null;
            }

            $user = new User;
            $user->forceFill([
                'name' => $challenge->name,
                'email' => $challenge->email,
                'phone' => $challenge->phone,
                'password' => $challenge->password_hash,
                'role' => 'customer',
                'email_verified_at' => now(),
            ])->save();

            DB::table('registration_otps')->where('id', $challenge->id)->delete();

            return $user;
        });

        if (!$user) {
            return back()->withErrors([
                'otp' => 'This code is invalid or expired. You can request a new code below.',
            ]);
        }

        $oldSessionId = $request->session()->getId();
        $request->session()->forget('registration_email');
        Auth::login($user);
        $request->session()->regenerate();
        $newSessionId = $request->session()->getId();

        \App\Models\CartItem::where('session_id', $oldSessionId)
            ->update(['session_id' => $newSessionId, 'user_id' => $user->id]);
        $this->moveCartSession($oldSessionId, $newSessionId);

        return redirect()->route('account')->with('success', 'Your email is verified. Welcome to Surat NX.');
    }

    public function resendRegistrationOtp(Request $request)
    {
        $email = $request->session()->get('registration_email');
        if (!$email) {
            return redirect()->route('register')->withErrors([
                'email' => 'Your registration session expired. Please register again.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        $updated = DB::table('registration_otps')
            ->where('email', $email)
            ->update([
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
                'attempts' => 0,
                'updated_at' => now(),
            ]);

        if (!$updated) {
            $request->session()->forget('registration_email');

            return redirect()->route('register')->withErrors([
                'email' => 'Your registration request expired. Please register again.',
            ]);
        }

        if (!$this->queueRegistrationOtp($email, $otp)) {
            return back()->withErrors([
                'otp' => 'We could not queue the verification email. Please try again later.',
            ]);
        }

        return back()->with('status', 'Your new verification code is being sent.');
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

    private function queueRegistrationOtp(string $email, string $otp): bool
    {
        try {
            Mail::to($email)->queue(new RegistrationOtpMail($otp));

            return true;
        } catch (Throwable $exception) {
            Log::error('Registration verification email delivery failed.', [
                'recipient' => $email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
