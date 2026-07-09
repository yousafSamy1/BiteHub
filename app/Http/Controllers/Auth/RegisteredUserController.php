<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use App\Models\KitchenOwner;
use App\Models\Caterer;
use App\Models\DeliveryAgent;
use App\Models\UserPhone;
use App\Models\UserAddress;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Mail\WelcomeMail;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,Email'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'regex:/[a-zA-Z]/',      // Must contain at least one letter
                'regex:/[0-9]/',          // Must contain at least one number
                'regex:/[^a-zA-Z0-9]/',   // Must contain at least one special character
            ],
            'role' => ['required', 'in:Customer,KitchenOwner,Caterer,DeliveryAgent'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf,doc,docx', 'max:5120'], // 5MB max per file
        ], [
            'email.email' => 'Please enter a valid email address (e.g. name@example.com).',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain at least one letter, one number, and one special character (!@#$...).',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $img = 'https://ui-avatars.com/api/?name=' . urlencode($request->name) . '&background=ff6b35&color=fff';

        try {
            DB::beginTransaction();

            $user = User::create([
                'FullName' => $request->name,
                'Email' => $request->email,
                'Password' => Hash::make($request->password),
                'Role' => $request->role,
                'Image' => $img,
                'Wallet_balance' => 0.00,
            ]);

            // Create role-specific record
            switch ($request->role) {
                case 'Customer':
                    Customer::create([
                        'UserID' => $user->UserID,
                        'WalletBalance' => 0.00,
                    ]);
                    break;
                case 'KitchenOwner':
                    $kname = $request->kitchen_name ?: $request->name . "'s Kitchen";
                    $attachments = [];
                    if ($request->hasFile('attachments')) {
                        foreach ($request->file('attachments') as $file) {
                            $filename = rand() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->move(public_path('upload/kitchen_attachments'), $filename);
                            $attachments[] = $filename;
                        }
                    }

                    KitchenOwner::create([
                        'UserID' => $user->UserID,
                        'KitchenName' => $kname,
                        'Attachment' => empty($attachments) ? null : $attachments,
                    ]);
                    break;
                case 'Caterer':
                    $bname = $request->business_name ?: $request->name . "'s Catering";
                    $attachments = [];
                    if ($request->hasFile('attachments')) {
                        foreach ($request->file('attachments') as $file) {
                            $filename = rand() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->move(public_path('upload/caterer_attachments'), $filename);
                            $attachments[] = $filename;
                        }
                    }

                    Caterer::create([
                        'UserID' => $user->UserID,
                        'BusinessName' => $bname,
                        'Attachment' => empty($attachments) ? null : $attachments,
                    ]);
                    break;
                case 'DeliveryAgent':
                    $vtype = $request->vehicle_type ?: 'Bike';
                    DeliveryAgent::create([
                        'UserID' => $user->UserID,
                        'VehicleType' => $vtype,
                    ]);
                    break;
            }

            // Save optional phone & address
            if ($request->phone) {
                UserPhone::create(['UserID' => $user->UserID, 'PhoneNumber' => $request->phone]);
            }
            if ($request->address) {
                UserAddress::create(['UserID' => $user->UserID, 'Address' => $request->address]);
            }

            DB::commit();

            event(new Registered($user));

            // Generate OTP
            $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp_code = $otpCode;
            $user->otp_expires_at = \Carbon\Carbon::now()->addMinutes(15);
            $user->save();

            // Store email in session to verify 
            session(['verify_email' => $user->Email]);

            // Send OTP email
            try {
                Mail::to($user->Email)->send(new \App\Mail\VerifyEmailOTP($otpCode));
            } catch (\Exception $mailEx) {
                \Illuminate\Support\Facades\Log::warning('OTP email failed: ' . $mailEx->getMessage());
                if (config('app.debug')) {
                    session()->flash('status', 'Email sending failed (SMTP issue). DEBUG MODE: Your OTP code is ' . $otpCode);
                }
            }

            return redirect()->route('verify.otp');

        } catch (\Exception $e) {
            DB::rollBack();
            $error = str_contains($e->getMessage(), 'Duplicate')
                ? 'Email already registered.'
                : 'Registration failed: ' . $e->getMessage();
            return back()->withErrors(['email' => $error])->withInput();
        }
    }
}
