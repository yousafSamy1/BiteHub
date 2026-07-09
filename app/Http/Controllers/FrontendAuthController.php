<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\KitchenOwner;
use App\Models\Caterer;
use App\Models\DeliveryAgent;
use App\Models\UserPhone;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FrontendAuthController extends Controller
{
    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Try with the User table (Bite-style: plain-text password)
        $user = User::where('Email', $request->email)->first();

        if ($user && $user->Password === $request->password) {
            Auth::login($user);
            $dashMap = [
                'Admin' => 'admin',
                'Customer' => 'customer',
                'KitchenOwner' => 'kitchen-owner',
                'Caterer' => 'caterer',
                'DeliveryAgent' => 'delivery',
            ];
            $dash = $dashMap[$user->Role] ?? 'customer';
            return redirect()->route('frontend.home');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }

    public function showRegister()
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,Email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-zA-Z]/', // Must contain at least one letter
            ],
            'role' => 'required|in:Customer,KitchenOwner,Caterer,DeliveryAgent',
        ], [
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.regex' => 'The password must contain at least one letter (cannot be only numbers).',
        ]);

        $img = 'https://ui-avatars.com/api/?name=' . urlencode($request->name) . '&background=ff6b35&color=fff';

        try {
            DB::beginTransaction();

            $user = User::create([
                'FullName' => $request->name,
                'Email' => $request->email,
                'Password' => $request->password,
                'Role' => $request->role,
                'Image' => $img,
            ]);

            switch ($request->role) {
                case 'Customer':
                    Customer::create(['UserID' => $user->UserID]);
                    break;
                case 'KitchenOwner':
                    $kname = $request->kitchen_name ?: $request->name . "'s Kitchen";
                    KitchenOwner::create(['UserID' => $user->UserID, 'KitchenName' => $kname]);
                    break;
                case 'Caterer':
                    Caterer::create(['UserID' => $user->UserID]);
                    break;
                case 'DeliveryAgent':
                    $vtype = $request->vehicle_type ?: 'Bike';
                    DeliveryAgent::create(['UserID' => $user->UserID, 'VehicleType' => $vtype]);
                    break;
            }

            if ($request->phone) {
                UserPhone::create(['UserID' => $user->UserID, 'PhoneNumber' => $request->phone]);
            }
            if ($request->address) {
                UserAddress::create(['UserID' => $user->UserID, 'Address' => $request->address]);
            }

            DB::commit();

            return back()->with('success', true);
        } catch (\Exception $e) {
            DB::rollBack();
            $error = str_contains($e->getMessage(), 'Duplicate')
                ? 'Email already registered.'
                : 'Registration failed. Please try again.';
            return back()->withErrors(['register' => $error])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('frontend.home');
    }
}
