<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:190']);
        $email = Str::lower(trim($data['email']));

        DB::table('password_reset_otps')->where('expires_at', '<=', now())->delete();
        $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_until']);
        $request->session()->put('password_reset_email', $email);

        $user = User::where('email', $email)->first();
        if ($user) {
            $otp = (string) random_int(100000, 999999);
            DB::table('password_reset_otps')->updateOrInsert(
                ['email' => $email],
                [
                    'otp_hash' => Hash::make($otp),
                    'expires_at' => now()->addMinutes(10),
                    'attempts' => 0,
                    'verified_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            Mail::to($user->email)->queue(new PasswordResetOtpMail($otp));
        }

        return redirect()->route('password.otp.form')->with(
            'status',
            'If an account exists for that email, we have sent a verification code.'
        );
    }

    public function verifyForm(Request $request)
    {
        $email = $request->session()->get('password_reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-password-otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:190',
            'otp' => ['required', 'digits:6'],
        ]);
        $email = Str::lower(trim($data['email']));

        if ($request->session()->get('password_reset_email') !== $email) {
            return back()->withErrors(['otp' => 'This verification code is invalid or has expired.']);
        }

        $verified = DB::transaction(function () use ($email, $data) {
            $challenge = DB::table('password_reset_otps')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (!$challenge || $challenge->attempts >= 5 || now()->greaterThan($challenge->expires_at)) {
                return false;
            }

            if (!Hash::check($data['otp'], $challenge->otp_hash)) {
                DB::table('password_reset_otps')->where('id', $challenge->id)->increment('attempts');
                return false;
            }

            DB::table('password_reset_otps')->where('id', $challenge->id)->update([
                'verified_at' => now(),
                'expires_at' => now()->addMinutes(10),
                'updated_at' => now(),
            ]);

            return true;
        });

        if (!$verified) {
            return back()->withErrors(['otp' => 'This verification code is invalid or has expired.']);
        }

        $request->session()->put('password_reset_verified_email', $email);
        $request->session()->put('password_reset_verified_until', now()->addMinutes(10)->timestamp);

        return redirect()->route('password.reset');
    }

    public function resetForm(Request $request)
    {
        $email = $request->session()->get('password_reset_verified_email');
        $verifiedUntil = $request->session()->get('password_reset_verified_until', 0);
        $challenge = $email
            ? DB::table('password_reset_otps')->where('email', $email)->first()
            : null;

        if (!$challenge || !$challenge->verified_at || now()->timestamp > $verifiedUntil || now()->greaterThan($challenge->expires_at)) {
            $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_until']);

            return $request->session()->has('password_reset_email')
                ? redirect()->route('password.otp.form')->withErrors(['otp' => 'Please verify a current code before resetting your password.'])
                : redirect()->route('password.request');
        }

        return view('auth.reset-password', compact('email'));
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:190',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);
        $email = Str::lower(trim($data['email']));
        $verifiedEmail = $request->session()->get('password_reset_verified_email');
        $verifiedUntil = $request->session()->get('password_reset_verified_until', 0);

        if ($verifiedEmail !== $email || now()->timestamp > $verifiedUntil) {
            $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_until']);

            return redirect()->route('password.request')->withErrors([
                'email' => 'Your verification has expired. Request a new code to continue.',
            ]);
        }

        $user = DB::transaction(function () use ($data, $email) {
            $challenge = DB::table('password_reset_otps')
                ->where('email', $email)
                ->whereNotNull('verified_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();
            $user = User::where('email', $email)->lockForUpdate()->first();

            if (!$challenge || !$user) {
                return null;
            }

            $user->forceFill([
                'password' => $data['password'],
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('password_reset_otps')->where('id', $challenge->id)->delete();

            return $user;
        });

        if (!$user) {
            $request->session()->forget(['password_reset_verified_email', 'password_reset_verified_until']);

            return redirect()->route('password.request')->withErrors([
                'email' => 'Your verification has expired. Request a new code to continue.',
            ]);
        }

        event(new PasswordReset($user));
        $request->session()->forget([
            'password_reset_email',
            'password_reset_verified_email',
            'password_reset_verified_until',
        ]);
        $request->session()->regenerate();

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now log in.');
    }
}
