<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderInvoiceMail;

class AgentController extends Controller
{
    // ─── Verification Gateway ────────────────────────────────────────────────
    public function showVerificationForm()
    {
        $user = Auth::user();
        $agent = $user->deliveryAgent;
        
        // If already verified, kick them to the dashboard
        if ($agent && $agent->IsVerified) {
            return redirect()->route('agent.dashboard');
        }

        return view('admin.agent.verify');
    }

    public function processVerification(Request $request)
    {
        $request->validate([
            'attachments'   => 'required|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();
        $agent = $user->deliveryAgent;

        if ($agent && !$agent->IsVerified) {
            $uploadedFiles = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = rand() . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('upload/agent_attachments'), $filename);
                    $uploadedFiles[] = 'upload/agent_attachments/' . $filename;
                }
            }

            $agent->update([
                'Attachment' => empty($uploadedFiles) ? null : $uploadedFiles,
                'IsVerified' => true,
                // AdminVerified remains false by default
            ]);

            Auth::guard('web')->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Documents submitted successfully. You will be notified via email once an Administrator approves your account.',
            ]);
        }

        return redirect()->route('agent.dashboard');
    }

    public function updateStatus(Request $request)
    {
        $agent = Auth::user()->deliveryAgent;
        if ($agent) {
            $newStatus = ($agent->Status === 'Available') ? 'Offline' : 'Available';
            $agent->update(['Status' => $newStatus]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'new_status' => $newStatus,
                    'message' => 'Status changed successfully.'
                ]);
            }

            return back()->with([
                'message' => 'Status changed to ' . $newStatus,
                'alert-type' => 'success'
            ]);
        }
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'error', 'message' => 'Agent record not found.'], 404);
        }
        return back();
    }

    public function updateServiceLocation(Request $request)
    {
        $agent = Auth::user()->deliveryAgent;
        
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'service_area' => 'nullable|string|max:255',
        ]);

        if ($agent) {
            $agent->update([
                'Latitude'  => $request->lat,
                'Longitude' => $request->lng,
                'ServiceArea' => $request->service_area,
            ]);

            return back()->with(['message' => 'Service location updated successfully.', 'alert-type' => 'success']);
        }
        
        return back()->with(['message' => 'Failed to update location.', 'alert-type' => 'error']);
    }

    /**
     * Settle cash collections using digital wallet balance
     */
    public function settleDebtWithWallet()
    {
        $user = \App\Models\User::find(Auth::id());
        $debt = $user->cash_to_settle;

        if ($debt <= 0) {
            return back()->with(['message' => 'You have no outstanding debt to settle.', 'alert-type' => 'info']);
        }

        if ($user->Wallet_balance < $debt) {
            return back()->with(['message' => 'Insufficient wallet balance to settle debt. Please use the Paymob option.', 'alert-type' => 'error']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($user, $debt) {
                $user->decrement('Wallet_balance', $debt);
                $user->update(['cash_to_settle' => 0]);
            });

            return back()->with(['message' => 'Debt settled successfully using wallet balance!', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            return back()->with(['message' => 'Error settling debt: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Initiate Paymob payment for debt settlement
     */
    public function paymobDebtSettleWait(Request $request, \App\Services\PaymobService $paymob)
    {
        $user = Auth::user();
        $debt = (float)$user->cash_to_settle;

        if ($debt <= 0) {
            return back()->with(['message' => 'You have no debt to settle.', 'alert-type' => 'info']);
        }

        $request->session()->put('paymob_agent_debt_settle', ['amount' => $debt]);
        $request->session()->put('paymob_order_type', 'agent_debt');

        $token = $paymob->authenticate();
        if (!$token) return back()->with(['message' => 'PayMob authentication failed.', 'alert-type' => 'error']);

        $orderId = $paymob->createOrder($token, $debt, [], 'DEBT_SETTLE_' . $user->UserID . '_' . time());
        if (!$orderId) return back()->with(['message' => 'PayMob order registration failed.', 'alert-type' => 'error']);

        $phoneRecord = $user->phone ?? ($user->phones ? $user->phones()->first() : null);
        $billingData = [
            'first_name'   => $user->FullName ?: 'Agent',
            'last_name'    => 'Rider',
            'email'        => $user->Email ?: 'agent@bitehub.com',
            'phone_number' => $phoneRecord ? $phoneRecord->PhoneNumber : '01000000000',
        ];

        $paymentKey = $paymob->getPaymentKey($token, $orderId, $debt, $billingData);
        if (!$paymentKey) return back()->with(['message' => 'PayMob payment key failed.', 'alert-type' => 'error']);

        return redirect($paymob->getIframeUrl($paymentKey));
    }

    /**
     * Handle Paymob success callback for debt settlement
     */
    public function paymobDebtSettleSuccess(Request $request)
    {
        $data = $request->session()->pull('paymob_agent_debt_settle');
        if (!$data) {
            return redirect()->route('agent.dashboard')->with(['message' => 'Session expired.', 'alert-type' => 'error']);
        }

        $user = \App\Models\User::find(Auth::id());
        if (!$user) return redirect()->route('login');

        try {
            $user->update(['cash_to_settle' => 0]);
            return redirect()->route('agent.dashboard')->with(['message' => 'Debt settled successfully via Paymob! 🎉', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->route('agent.dashboard')->with(['message' => 'Payment failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────
    public function AgentDashboard()
    {
        $user = Auth::user();
        $profileData = $user;

        $agent = $user->deliveryAgent;
        
        // Safety: Auto-create agent record if missing for a user with the correct role
        if (!$agent && $user->Role === 'DeliveryAgent') {
            $agent = \App\Models\DeliveryAgent::create([
                'UserID' => $user->UserID,
                'Status' => 'Available',
                'IsVerified' => true,
                'AdminVerified' => true
            ]);
        }

        $assignedDeliveries  = 0;
        $completedDeliveries = 0;
        $pendingDeliveries   = 0;
        $todayCompleted      = 0;
        $todayEarnings       = 0;
        $monthlySalesData    = array_fill(0, 12, 0);
        $recentOrders        = collect();

        if ($agent) {
            $agentOrdersQuery = Order::where('DeliveryAgentID', $agent->DeliveryAgentID);
            
            $assignedDeliveries  = (clone $agentOrdersQuery)->count();
            $completedDeliveries = (clone $agentOrdersQuery)->where('OrderStatus', 'Delivered')->count();
            $pendingDeliveries   = (clone $agentOrdersQuery)
                                        ->whereIn('OrderStatus', ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Delivering'])->count();
            
            $todayCompleted = (clone $agentOrdersQuery)
                                    ->where('OrderStatus', 'Delivered')
                                    ->whereDate('CreatedAt', \Carbon\Carbon::today())
                                    ->count();

            // Calculate Today's Earnings (Approx: 15 EGP per delivery + Bonus if >= 11)
            $todayEarnings = $todayCompleted * 15.00;
            if ($todayCompleted >= 11) {
                $todayEarnings += 50.00;
            }
                                        
            // Get delivery count for each month in the current year
            $monthlySales = Order::select(\Illuminate\Support\Facades\DB::raw('MONTH(CreatedAt) as month'), \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->where('DeliveryAgentID', $agent->DeliveryAgentID)
                ->whereYear('CreatedAt', date('Y'))
                ->groupBy('month')
                ->pluck('count', 'month')->toArray();
                
            for ($i = 1; $i <= 12; $i++) {
                $monthlySalesData[$i-1] = (int) ($monthlySales[$i] ?? 0);
            }

            // Fetch 5 recent orders with details
            $recentOrders = (clone $agentOrdersQuery)
                ->with(['customer.user', 'payment'])
                ->orderByDesc('OrderID')
                ->limit(5)
                ->get();
        }

        return view('admin.agent.index', compact(
            'profileData',
            'assignedDeliveries',
            'completedDeliveries',
            'pendingDeliveries',
            'todayCompleted',
            'todayEarnings',
            'agent',
            'monthlySalesData',
            'recentOrders'
        ));
    }

    // ─── Deliveries ───────────────────────────────────────────────────────────
    public function myDeliveries(Request $request)
    {
        $user = Auth::user();
        $agent = $user->deliveryAgent;
        
        $todayOrders = collect();
        $scheduledOrders = collect();

        if ($agent) {
            $baseQuery = Order::where('DeliveryAgentID', $agent->DeliveryAgentID)
                ->with(['customer.user.phone', 'customer.user.address', 'payment'])
                ->orderByDesc('OrderID');

            if ($request->status) {
                $baseQuery->where('OrderStatus', $request->status);
            }

            $allOrders = $baseQuery->get();

            // Today's + Past (Overdue) Active Orders: Scheduled for Today/Earlier AND not terminal
            $todayOrders = $allOrders->filter(function($o) {
                $isTodayOrPast = $o->ScheduledDate <= \Carbon\Carbon::today()->toDateString();
                $isTerminalStatus = in_array($o->OrderStatus, ['Delivered', 'Cancelled']);
                $isInProgress = in_array($o->OrderStatus, ['Preparing', 'Ready', 'Delivering']);

                return ($isTodayOrPast && !$isTerminalStatus) || $isInProgress;
            });

            // Future Scheduled Orders: Scheduled for Tomorrow or later AND not yet completed/cancelled
            $scheduledOrders = $allOrders->filter(function($o) {
                return $o->ScheduledDate > \Carbon\Carbon::today()->toDateString() 
                       && !in_array($o->OrderStatus, ['Delivered', 'Cancelled']);
            });
        }

        return view('admin.agent.deliveries.index', compact('todayOrders', 'scheduledOrders'));
    }

    public function showDeliveryDetails($id)
    {
        $agent = Auth::user()->deliveryAgent;
        $order = Order::where('OrderID', $id)->where('DeliveryAgentID', $agent->DeliveryAgentID)
            ->with(['customer.user.phone', 'customer.user.address', 'payment', 'menuItems', 'kitchenOwner', 'caterer'])
            ->firstOrFail();

        // Get customer's exact coordinates based on the address
        $userAddress = null;
        if ($order->customer && $order->customer->user) {
            $userAddress = \App\Models\UserAddress::where('UserID', $order->customer->user->UserID)
                ->orderByDesc('IsPrimary')->first();
        }

        return view('admin.agent.deliveries.show', compact('order', 'userAddress'));
    }

    public function updateDeliveryStatus(Request $request, $id)
    {
        $agent = Auth::user()->deliveryAgent;
        $order = Order::where('OrderID', $id)->where('DeliveryAgentID', $agent->DeliveryAgentID)->firstOrFail();
        
        $request->validate(['status' => 'required|in:Pending,Confirmed,Preparing,Ready,Delivering,Delivered']);

        // RESTRICTION: Agents can't change status until kitchen sets it to Ready
        $allowedToChangeFrom = ['Ready', 'Delivering'];
        if (!in_array($order->OrderStatus, $allowedToChangeFrom)) {
            return back()->with(['message' => 'The kitchen has not marked this order as ready yet. You can only start delivery once it is Ready.', 'alert-type' => 'error']);
        }

        if ($request->status === 'Delivered') {
            $request->validate([
                'delivery_code' => 'required|string',
                'wallet_change' => 'nullable|numeric|min:0',
                'plan_cash_paid' => 'nullable|numeric|min:0',
            ], [
                'delivery_code.required' => 'The customer delivery OTP code is required to close this order.',
            ]);

            if ($request->delivery_code !== $order->DeliveryCode) {
                return back()->with(['message' => 'Invalid delivery code provided.', 'alert-type' => 'error']);
            }
            
            if ($order->OrderStatus !== 'Delivered') {
                $settler = new \App\Services\OrderSettlementService();
                $result = $settler->settle($order, true, [
                    'wallet_change' => $request->wallet_change,
                    'plan_cash_paid' => $request->plan_cash_paid
                ], true); // true because we already verified OTP match at line 333

                if ($result['status'] === 'success') {
                    return back()->with(['message' => $result['message'], 'alert-type' => 'success']);
                } else {
                    return back()->with(['message' => $result['message'], 'alert-type' => 'error']);
                }
            }


        }
        
        $order->update(['OrderStatus' => $request->status]);
        return back()->with(['message' => 'Delivery status updated.', 'alert-type' => 'success']);
    }

    public function updateLocation(Request $request, $id)
    {
        $agent = Auth::user()->deliveryAgent;
        $order = Order::where('OrderID', $id)->where('DeliveryAgentID', $agent->DeliveryAgentID)->firstOrFail();

        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $order->update([
            'DriverLatitude'  => $request->lat,
            'DriverLongitude' => $request->lng,
        ]);

        return response()->json(['success' => true]);
    }

    public function AgentProfile()
    {
        $profileData = Auth::user();
        return view('admin.agent.profile', compact('profileData'));
    }

    public function store(Request $request)
    {
        $id = Auth::user()->UserID;
        $data = User::find($id);
        $data->FullName = $request->name;
        
        if ($request->file('photo')) {
            $file = $request->file('photo');
            
            // Standardize path using DIRECTORY_SEPARATOR for Windows/Linux compatibility
            $uploadPath = public_path('upload' . DIRECTORY_SEPARATOR . 'admin_images');
            
            // Defensive: ensure directory exists and try to create it if it doesn't
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            // Delete old image if exists
            if ($data->Image && file_exists($uploadPath . DIRECTORY_SEPARATOR . $data->Image)) {
                @unlink($uploadPath . DIRECTORY_SEPARATOR . $data->Image);
            }
            
            $filename = rand() . time() . '.' . $file->getClientOriginalExtension();
            
            // Move file using sanitized path
            try {
                $file->move($uploadPath, $filename);
                $data->Image = $filename;
            } catch (\Exception $e) {
                // If it still fails, fallback to a more primitive move or log the error
                \Illuminate\Support\Facades\Log::error("Manual upload failure in AgentController: " . $e->getMessage());
                return back()->with(['message' => 'Upload failed: ' . $e->getMessage(), 'alert-type' => 'error']);
            }
        }

        $data->save();

        $notification = ['message' => 'Profile Updated Successfully', 'alert-type' => 'success'];
        return redirect()->back()->with($notification);
    }

    public function AgentChangePassword()
    {
        $profileData = Auth::user();
        return view('admin.agent.change_password', compact('profileData'));
    }

    public function AgentUpdatePassword(Request $request)
    {
        $request->validate([
            'old_password'              => 'required',
            'new_password'              => 'required|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        if (!Hash::check($request->old_password, Auth::user()->Password)) {
            return back()->with(['message' => 'Current Password Does Not Match!', 'alert-type' => 'error']);
        }

        User::where('UserID', Auth::id())->update([
            'Password' => Hash::make($request->new_password),
        ]);

        return back()->with(['message' => 'Password Changed Successfully', 'alert-type' => 'success']);
    }

    public function AgentLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
