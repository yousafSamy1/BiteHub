<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailOTP;
use Carbon\Carbon;

class VerifyOtpController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function show(Request $request)
    {
        // Require the session to have verify_email set
        if (!session()->has('verify_email')) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify the entered OTP.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|array|min:6|max:6',
            'otp.*' => 'required|numeric|digits:1',
        ]);

        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login to verify.']);
        }

        $user = User::where('Email', $email)->first();
        if (!$user) {
            return redirect()->route('register')->withErrors(['email' => 'User not found.']);
        }

        // Combine the OTP array back to a string
        $enteredOtp = implode('', $request->otp);

        if ($user->otp_code !== $enteredOtp) {
            return back()->withErrors(['otp' => 'Invalid verification code.']);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'The verification code has expired. Please request a new one.']);
        }

        // Verify user
        $user->email_verified_at = Carbon::now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Clear session and log them in
        session()->forget('verify_email');
        Auth::login($user);

        // Send Welcome Mail
        try {
            Mail::to($user->Email)->send(new \App\Mail\WelcomeMail($user));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Welcome email failed: ' . $e->getMessage());
        }

        // Redirect based on role
        if ($user->Role === 'Admin') {
            return redirect('/admin/dashboard');
        }

        return redirect()->route('frontend.home');
    }

    /**
     * Resend a new OTP.
     */
    public function resend(Request $request)
    {
        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('login')->withErrors(['email' => 'Session expired. Please login to verify.']);
        }

        $user = User::where('Email', $email)->first();
        if (!$user) {
            return redirect()->route('register')->withErrors(['email' => 'User not found.']);
        }

        // Generate new OTP
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();

        // Send email
        try {
            Mail::to($user->Email)->send(new VerifyEmailOTP($otpCode));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('OTP email failed: ' . $e->getMessage());
            
            if (config('app.debug')) {
                 return back()->with('status', 'Email sending failed. DEBUG MODE: Your new OTP code is ' . $otpCode);
            }
            return back()->withErrors(['email' => 'Failed to send OTP email. Please try again.']);
        }

        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
