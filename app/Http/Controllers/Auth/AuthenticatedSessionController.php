<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect based on role — all non-customer roles go to admin panel
        $user = Auth::user();

        if ($user->Role === 'KitchenOwner' && $user->kitchenOwner) {
            if ($user->kitchenOwner->VerifyStatus === 'Rejected') {
                Auth::guard('web')->logout();
                return redirect()->route('login')->withErrors(['email' => 'Your account has been rejected by the administrator.']);
            }
            if ($user->kitchenOwner->Status === 'Suspended') {
                Auth::guard('web')->logout();
                return redirect()->route('login')->withErrors(['email' => 'Your account is currently suspended. You do not have access to the dashboard.']);
            }
        }

        if ($user->Role === 'Caterer' && $user->caterer) {
            if (!$user->caterer->IsActive) {
                Auth::guard('web')->logout();
                return redirect()->route('login')->withErrors(['email' => 'Your account is currently deactivated. You do not have access to the dashboard.']);
            }
        }

        if ($user->Role === 'DeliveryAgent' && $user->deliveryAgent) {
            // Priority 1: Hasn't uploaded files at all
            if (!$user->deliveryAgent->IsVerified) {
                return redirect()->route('agent.verify.page');
            }
            // Priority 2: Uploaded files, but Admin hasn't approved them yet
            if (!$user->deliveryAgent->AdminVerified) {
                Auth::guard('web')->logout();
                return redirect()->route('login')->withErrors(['email' => 'Your documents have been submitted and are pending Admin review. You will receive an email once approved.']);
            }
        }

        $dashRoutes = [
            'Admin'         => '/admin/dashboard',
            'Owner'         => '/admin/dashboard',
            'KitchenOwner'  => '/admin/kitchen/dashboard',
            'Caterer'       => '/admin/caterer/dashboard',
            'DeliveryAgent' => '/admin/agent/dashboard',
            'Customer'      => '/',
        ];

        return redirect($dashRoutes[$user->Role] ?? '/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Check role BEFORE destroying session
        $wasCustomer = Auth::user()?->Role === 'Customer';

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Customers get a special goodbye page with animation
        if ($wasCustomer) {
            return redirect()->route('frontend.goodbye');
        }

        return redirect()->route('frontend.home');
    }
}
