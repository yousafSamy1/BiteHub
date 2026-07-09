<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\KitchenOwner;
use App\Models\Caterer;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\Advertising;
use App\Models\Subscription;
use App\Models\LoyaltyTransaction;
use App\Models\CateringRequest;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\RefundRequest;
use App\Models\ErrorReport;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\PromoCodeAnnouncement;

class AdminController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────
    public function AdminDashboard(Request $request)
    {
        $range = $request->query('range', 'all');
        $startDate = null;
        $endDate   = now();

        switch ($range) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                break;
            default:
                $range = 'all';
                break;
        }

        $applyFilter = function($query, $column = 'CreatedAt') use ($startDate, $endDate) {
            if ($startDate) {
                return $query->whereBetween($column, [$startDate, $endDate]);
            }
            return $query;
        };

        $totalUsers     = User::count();
        $totalCustomers = User::where('Role', 'Customer')->count();
        $totalKitchens  = KitchenOwner::count();
        $totalCaterers  = Caterer::count();
        
        // Filtered KPIs
        $totalOrders    = $applyFilter(Order::query())->count();
        $orderRevenue   = $applyFilter(Order::whereNotIn('OrderStatus', ['Cancelled']))->sum('TotalPrice');
        $subRevenue     = $applyFilter(\App\Models\Subscription::whereNotIn('Status', ['Cancelled', 'Pending']), 'StartDate')->sum('PaidAmount');
        $totalRevenue   = $orderRevenue + $subRevenue;
        $pendingOrders  = Order::where('OrderStatus', 'Pending')->count(); // Standard current count
        $todayOrders    = Order::whereDate('CreatedAt', today())->count();

        $recentOrders = Order::join('customers', 'orders.CustomerID', '=', 'customers.CustomerID')
            ->join('users', 'customers.UserID', '=', 'users.UserID')
            ->select('orders.*', 'users.FullName as CustomerName')
            ->orderByDesc('orders.CreatedAt')
            ->limit(8)
            ->get();

        $recentUsers = User::orderByDesc('CreatedAt')->limit(5)->get();

        $ordersByStatus = $applyFilter(Order::query())
            ->select('OrderStatus', DB::raw('count(*) as count'))
            ->groupBy('OrderStatus')
            ->pluck('count', 'OrderStatus');

        // NEW: Support & Refund KPIs
        $openTicketsCount = \App\Models\SupportTicket::whereIn('Status', ['Open', 'InProgress'])->count();
        $pendingRefundsCount = RefundRequest::where('Status', 'Pending')->count();
        $totalWalletsSum = User::sum('Wallet_balance') + Customer::sum('WalletBalance');
        
        // Calculate Platform Commission (15% of Items)
        $orderItemsSum = $applyFilter(Order::whereNotIn('OrderStatus', ['Cancelled']))
            ->select(DB::raw('SUM(CASE WHEN TotalPrice > 15 THEN TotalPrice - 15 ELSE 0 END) as items_sum'))
            ->value('items_sum') ?? 0;
        $orderCommission = $orderItemsSum * 0.15;
        
        $totalPointsDiscount = $applyFilter(Order::whereNotIn('OrderStatus', ['Cancelled']))->sum('PointsDiscount');
        $siteCommission = $orderCommission + $subRevenue - $totalPointsDiscount;

        // Revenue Chart Logic
        // If range is today/week/month, we show daily. If year/all, we show monthly.
        $chartData = [];
        $chartLabels = [];

        if (in_array($range, ['today', 'week', 'month'])) {
            $daysToFetch = ($range == 'today') ? 1 : (($range == 'week') ? 7 : 30);
            $revenueByDay = $applyFilter(Order::whereNotIn('OrderStatus', ['Cancelled']))
                ->select(DB::raw('DATE(CreatedAt) as date'), DB::raw('SUM(TotalPrice) as total'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            foreach ($revenueByDay as $row) {
                $chartLabels[] = date('d M', strtotime($row->date));
                $chartData[]   = (float) $row->total;
            }
        } else {
            // Default: Monthly for the current year
            $monthlySales = Order::select(DB::raw('MONTH(CreatedAt) as month'), DB::raw('SUM(TotalPrice) as total'))
                ->whereYear('CreatedAt', date('Y'))
                ->whereNotIn('OrderStatus', ['Cancelled'])
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')->toArray();

            for ($i = 1; $i <= 12; $i++) {
                $chartLabels[] = date('M', mktime(0, 0, 0, $i, 1));
                $chartData[]   = (float) ($monthlySales[$i] ?? 0);
            }
        }

        $totalDeliveryAgents = DeliveryAgent::count();
        $totalSubscriptions  = Subscription::count();
        $totalAdvertisements = Advertising::count();
        $totalCateringReqs   = CateringRequest::count();
        $totalWalletsSum     = User::sum('Wallet_balance') + Customer::sum('WalletBalance');

        // Removed redundant all-time commission calculation that was overwriting the filtered KPI.

        // Top Performers in period
        $topKitchens = $applyFilter(DB::table('orders'), 'orders.CreatedAt')
            ->join('menu_order_items', 'orders.OrderID', '=', 'menu_order_items.OrderID')
            ->join('menu_items', 'menu_order_items.MenuItemID', '=', 'menu_items.MenuItemID')
            ->join('kitchen_owners', 'menu_items.KitchenOwnerID', '=', 'kitchen_owners.KitchenOwnerID')
            ->join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
            ->select('users.FullName', 'kitchen_owners.KitchenName', DB::raw('count(DISTINCT orders.OrderID) as order_count'))
            ->groupBy('users.FullName', 'kitchen_owners.KitchenName', 'kitchen_owners.KitchenOwnerID')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();

        $topCaterers = $applyFilter(CateringRequest::query(), 'catering_requests.CreatedAt')
            ->join('caterers', 'catering_requests.CatererID', '=', 'caterers.CatererID')
            ->join('users', 'caterers.UserID', '=', 'users.UserID')
            ->select('users.FullName', 'caterers.BusinessName as CatererName', DB::raw('count(catering_requests.RequestID) as request_count'))
            ->groupBy('users.FullName', 'caterers.BusinessName', 'caterers.CatererID')
            ->orderByDesc('request_count')
            ->limit(5)
            ->get();

        $recentTickets = \App\Models\SupportTicket::with('user')->orderByDesc('created_at')->limit(5)->get();
        $recentRefunds = RefundRequest::with('customer.user')->orderByDesc('updated_at')->limit(5)->get();

        return view('admin.index', compact(
            'totalUsers', 'totalCustomers', 'totalKitchens', 'totalCaterers', 'totalDeliveryAgents',
            'totalOrders', 'totalRevenue', 'pendingOrders', 'todayOrders',
            'totalSubscriptions', 'totalAdvertisements', 'totalCateringReqs', 'totalWalletsSum',
            'siteCommission', 'range', 'chartData', 'chartLabels',
            'recentOrders', 'ordersByStatus', 'recentUsers',
            'topKitchens', 'topCaterers', 'openTicketsCount', 'pendingRefundsCount',
            'recentTickets', 'recentRefunds'
        ));
    }

    public function AdminKPI(Request $request)
    {
        $range = $request->query('range', 'all');
        $startDate = null;
        $endDate   = now();

        switch ($range) {
            case 'today': $startDate = now()->startOfDay(); break;
            case 'week':  $startDate = now()->startOfWeek(); break;
            case 'month': $startDate = now()->startOfMonth(); break;
            case 'year':  $startDate = now()->startOfYear(); break;
            default:      $range = 'all'; break;
        }

        $applyFilter = function($query, $column = 'CreatedAt') use ($startDate, $endDate) {
            if ($startDate) return $query->whereBetween($column, [$startDate, $endDate]);
            return $query;
        };

        // 1. Platform Revenue
        $orderRevenue = $applyFilter(Order::whereNotIn('OrderStatus', ['Cancelled']))->sum('TotalPrice');
        $subRevenue   = $applyFilter(Subscription::whereNotIn('Status', ['Cancelled', 'Pending']), 'StartDate')->sum('PaidAmount');
        $totalPlatformRevenue = $orderRevenue + $subRevenue;
        
        $todayPlatformRevenue = Order::whereDate('CreatedAt', today())->whereNotIn('OrderStatus', ['Cancelled'])->sum('TotalPrice');
        $monthlyPlatformRevenueTotal = Order::whereMonth('CreatedAt', date('m'))->whereYear('CreatedAt', date('Y'))->whereNotIn('OrderStatus', ['Cancelled'])->sum('TotalPrice');

        // 2. Commission
        $orderItemsSum = $applyFilter(Order::whereNotIn('OrderStatus', ['Cancelled']))
            ->select(DB::raw('SUM(CASE WHEN TotalPrice > 15 THEN TotalPrice - 15 ELSE 0 END) as items_sum'))
            ->value('items_sum') ?? 0;
        $orderCommission = $orderItemsSum * 0.15;
        $totalPointsDiscount = $applyFilter(Order::whereNotIn('OrderStatus', ['Cancelled']))->sum('PointsDiscount');
        $siteCommission = $orderCommission + $subRevenue - $totalPointsDiscount;

        // 3. Platform Stats
        $totalOrdersCount = $applyFilter(Order::query())->count();
        $pendingOrdersCount = Order::where('OrderStatus', 'Pending')->count();
        $totalUsersCount = User::count();
        $totalKitchensCount = KitchenOwner::count();
        $totalCaterersCount = Caterer::count();
        $totalAgentsCount = DeliveryAgent::count();

        // 4. Activity
        $activeSubscriptionsCount = Subscription::where('Status', 'Active')->count();
        $activeAdsCount = Advertising::where('Status', 'Active')->count();
        $pendingRefundsCount = RefundRequest::where('Status', 'Pending')->count();
        $openTicketsCount = \App\Models\SupportTicket::whereIn('Status', ['Open', 'InProgress'])->count();

        // Chart Data (System Revenue Trend)
        $chartData = [];
        $chartLabels = [];
        $monthlyRevenue = Order::select(DB::raw('MONTH(CreatedAt) as month'), DB::raw('SUM(TotalPrice) as total'))
            ->whereYear('CreatedAt', date('Y'))
            ->whereNotIn('OrderStatus', ['Cancelled'])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')->toArray();

        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = date('M', mktime(0, 0, 0, $i, 1));
            $chartData[] = (float)($monthlyRevenue[$i] ?? 0);
        }

        return view('admin.kpi_page', compact(
            'range', 'totalPlatformRevenue', 'todayPlatformRevenue', 'monthlyPlatformRevenueTotal',
            'siteCommission', 'totalOrdersCount', 'pendingOrdersCount', 'totalUsersCount',
            'totalKitchensCount', 'totalCaterersCount', 'totalAgentsCount',
            'activeSubscriptionsCount', 'activeAdsCount', 'pendingRefundsCount', 'openTicketsCount',
            'chartData', 'chartLabels'
        ));
    }


    public function downloadDailySummary(\App\Services\DailySummaryService $summaryService)
    {
        $data = $summaryService->getSummaryData();
        $dateString = \Carbon\Carbon::now()->format('Y-m-d');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily_summary_pdf', compact('data'));
        
        return $pdf->download('Daily_Summary_' . $dateString . '.pdf');
    }


    // ─── Owner Management ────────────────────────────────────────────────────
    public function adminList()
    {
        $admins = User::whereIn('Role', ['Admin', 'Owner'])->orderByDesc('CreatedAt')->get();
        return view('admin.admins.index', compact('admins'));
    }

    public function toggleRole($id)
    {
        $user = User::findOrFail($id);
        $privilegedEmails = ['wezo8123@gmail.com', 'matf4866@gmail.com', 'yousafsamy50@gmail.com'];

        // Only specified owners can demote an owner or promote to owner
        if ($user->Role === 'Owner' || request()->new_role === 'Owner') {
            if (!in_array(Auth::user()->Email, $privilegedEmails)) {
                return back()->with(['message' => 'Unauthorized action for this role level.', 'alert-type' => 'error']);
            }
        }

        $newRole = $user->Role === 'Admin' ? 'Owner' : 'Admin';
        $user->update(['Role' => $newRole]);

        return back()->with(['message' => 'User role updated to ' . $newRole, 'alert-type' => 'success']);
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')->orderByDesc('CreatedAt')->paginate(50);
        return view('admin.audit_logs.index', compact('logs'));
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $passwordStr = \Illuminate\Support\Str::random(10);
        $role = 'Admin'; // Default role for new admins

        $user = User::create([
            'FullName' => $request->name,
            'Email'    => $request->email,
            'Password' => Hash::make($passwordStr),
            'Role'     => $role,
            'Status'   => 'Active',
            'Image'    => 'https://ui-avatars.com/api/?name=' . urlencode($request->name) . '&background=10b981&color=fff',
        ]);

        // Send Onboarding Email
        $details = [
            'name'     => $user->FullName,
            'email'    => $user->Email,
            'password' => $passwordStr,
            'role'     => $role
        ];

        \Illuminate\Support\Facades\Mail::to($user->Email)->send(new \App\Mail\AdminAccountCreated($details));

        return back()->with(['message' => 'Administrator created successfully and credentials emailed.', 'alert-type' => 'success']);
    }


    // ─── Users ────────────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $query = User::query();
        if ($request->role) $query->where('Role', $request->role);
        if ($request->search) $query->where(function($q) use ($request) {
            $q->where('FullName', 'like', '%'.$request->search.'%')
              ->orWhere('Email', 'like', '%'.$request->search.'%');
        });
        $users = $query->orderByDesc('CreatedAt')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $privilegedEmails = ['wezo8123@gmail.com', 'matf4866@gmail.com', 'yousafsamy50@gmail.com'];

        // Protect Owners
        if ($user->Role === 'Owner') {
            if (!in_array(Auth::user()->Email, $privilegedEmails)) {
                return back()->with(['message' => 'Unauthorized: Only specific owners can delete other owners.', 'alert-type' => 'error']);
            }
        }

        $user->delete();
        return back()->with(['message' => 'User deleted.', 'alert-type' => 'success']);
    }

    public function suspendUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['Status' => 'Suspended']);
        return back()->with(['message' => 'User account has been suspended.', 'alert-type' => 'warning']);
    }

    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['Status' => 'Active', 'ProfanityStrikes' => 0]); // Reset strikes on manual activation
        return back()->with(['message' => 'User account has been activated and strikes reset.', 'alert-type' => 'success']);
    }

    public function wallets(Request $request)
    {
        $query = User::query();
        if ($request->role) $query->where('Role', $request->role);
        if ($request->search) $query->where(function($q) use ($request) {
            $q->where('FullName', 'like', '%'.$request->search.'%')
              ->orWhere('Email', 'like', '%'.$request->search.'%');
        });
        $users = $query->orderByDesc('Wallet_balance')->paginate(20);
        $totalWallets = User::sum('Wallet_balance');
        return view('admin.wallets.index', compact('users', 'totalWallets'));
    }


    // ─── Kitchens ─────────────────────────────────────────────────────────────
    public function kitchens(Request $request)
    {
        $query = KitchenOwner::join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
            ->select('kitchen_owners.*', 'users.FullName', 'users.Email', 'users.Image', 'users.CreatedAt as JoinedAt');
        if ($request->status) $query->where('kitchen_owners.Status', $request->status);
        if ($request->verify) $query->where('kitchen_owners.VerifyStatus', $request->verify);
        $kitchens = $query->orderByDesc('kitchen_owners.KitchenOwnerID')->paginate(15);
        return view('admin.kitchens.index', compact('kitchens'));
    }

    public function verifyKitchen($id)
    {
        $kitchen = KitchenOwner::findOrFail($id);
        $kitchen->update(['VerifyStatus' => 'Verified', 'Status' => 'Active']);
        Notification::notify($kitchen->UserID, 'Account Verified', 'Your kitchen account has been verified and is now active.', 'System');
        return back()->with(['message' => 'Kitchen verified and activated.', 'alert-type' => 'success']);
    }

    public function rejectKitchen($id)
    {
        $kitchen = KitchenOwner::findOrFail($id);
        $kitchen->update(['VerifyStatus' => 'Rejected']);
        Notification::notify($kitchen->UserID, 'Verification Rejected', 'Your kitchen verification request was rejected. Please contact support.', 'System');
        return back()->with(['message' => 'Kitchen rejected.', 'alert-type' => 'warning']);
    }

    public function suspendKitchen($id)
    {
        KitchenOwner::findOrFail($id)->update(['Status' => 'Suspended']);
        return back()->with(['message' => 'Kitchen suspended.', 'alert-type' => 'warning']);
    }

    public function activateKitchen($id)
    {
        KitchenOwner::findOrFail($id)->update(['Status' => 'Active']);
        return back()->with(['message' => 'Kitchen activated.', 'alert-type' => 'success']);
    }

    // ─── Caterers ─────────────────────────────────────────────────────────────
    public function caterers(Request $request)
    {
        $query = Caterer::join('users', 'caterers.UserID', '=', 'users.UserID')
            ->select('caterers.*', 'users.FullName', 'users.Email', 'users.Image');
        if ($request->has('active')) $query->where('caterers.IsActive', $request->active);
        $caterers = $query->orderByDesc('caterers.CatererID')->paginate(15);
        return view('admin.caterers.index', compact('caterers'));
    }

    public function toggleCaterer($id)
    {
        $c = Caterer::findOrFail($id);
        $c->update(['IsActive' => !$c->IsActive]);
        return back()->with(['message' => 'Caterer updated.', 'alert-type' => 'success']);
    }

    // ─── Agents ───────────────────────────────────────────────────────────────
    public function agents(Request $request)
    {
        $query = DeliveryAgent::join('users', 'delivery_agents.UserID', '=', 'users.UserID')
            ->select('delivery_agents.*', 'users.FullName', 'users.Email', 'users.Image');
        if ($request->status) $query->where('delivery_agents.Status', $request->status);
        $agents = $query->orderByDesc('delivery_agents.DeliveryAgentID')->paginate(15);
        return view('admin.agents.index', compact('agents'));
    }

    public function updateAgentStatus(Request $request, $id)
    {
        DeliveryAgent::findOrFail($id)->update(['Status' => $request->status]);
        return back()->with(['message' => 'Agent status updated.', 'alert-type' => 'success']);
    }

    public function approveAgent(Request $request, $id)
    {
        $agent = DeliveryAgent::findOrFail($id);
        
        if ($agent->IsVerified && !$agent->AdminVerified) {
            $agent->update(['AdminVerified' => true]);

            // Notify the agent
            Notification::notify($agent->UserID, 'Account Approved', 'Your delivery agent account has been approved by administration.', 'System');

            $details = [
                'name' => $agent->user->FullName,
            ];

            \Illuminate\Support\Facades\Mail::to($agent->user->Email)->send(new \App\Mail\AgentApproved($details));

            return back()->with(['message' => 'Agent approved and notified via email.', 'alert-type' => 'success']);
        }

        return back()->with(['message' => 'Agent is not eligible for approval.', 'alert-type' => 'error']);
    }

    public function storeAgent(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'phone'        => 'required|string|max:20',
            'vehicle_type' => 'required|in:Bike,Car,Motorcycle',
        ]);

        try {
            DB::beginTransaction();

            $passwordStr = \Illuminate\Support\Str::random(10);
            $img = 'https://ui-avatars.com/api/?name=' . urlencode($request->name) . '&background=ff6b35&color=fff';

            $user = User::create([
                'FullName' => $request->name,
                'Email'    => $request->email,
                'Password' => Hash::make($passwordStr),
                'Role'     => 'DeliveryAgent',
                'Image'    => $img,
                'Wallet_balance' => 0.00,
            ]);

            DeliveryAgent::create([
                'UserID'      => $user->UserID,
                'VehicleType' => $request->vehicle_type,
                'Status'      => 'Available',
            ]);

            \App\Models\UserPhone::create([
                'UserID'      => $user->UserID,
                'PhoneNumber' => $request->phone,
            ]);

            DB::commit();

            $details = [
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $passwordStr,
            ];

            \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\AgentAccountCreated($details));

            return back()->with(['message' => 'Delivery Agent created successfully and credentials emailed.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Error creating agent: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── Orders ───────────────────────────────────────────────────────────────
    public function orders(Request $request)
    {
        $query = Order::leftJoin('customers', 'orders.CustomerID', '=', 'customers.CustomerID')
            ->leftJoin('users as cu', 'customers.UserID', '=', 'cu.UserID')
            ->leftJoin('delivery_agents', 'orders.DeliveryAgentID', '=', 'delivery_agents.DeliveryAgentID')
            ->leftJoin('users as au', 'delivery_agents.UserID', '=', 'au.UserID')
            ->leftJoin('payments', 'orders.PaymentID', '=', 'payments.PaymentID')
            ->leftJoin('user_addresses', function($join) {
                $join->on('cu.UserID', '=', 'user_addresses.UserID')
                     ->where('user_addresses.IsPrimary', 1);
            })
            ->select(
                'orders.*',
                'cu.FullName as CustomerName',
                'au.FullName as AgentName',
                'payments.Method as PaymentMethod',
                'user_addresses.Address as CustomerAddress'
            );
        if ($request->status) $query->where('orders.OrderStatus', $request->status);
        $orders = $query->orderByDesc('orders.OrderID')->paginate(15);
        $agents = DeliveryAgent::join('users', 'delivery_agents.UserID', '=', 'users.UserID')
            ->where('delivery_agents.Status', 'Available')
            ->select('delivery_agents.DeliveryAgentID', 'users.FullName', 'delivery_agents.ServiceArea')
            ->get();
        return view('admin.orders.index', compact('orders', 'agents'));
    }

    public function updateOrderStatus(Request $request, \App\Services\OrderSettlementService $settler, $id)
    {
        $order = Order::findOrFail($id);

        if ($request->status === 'Delivered') {
            // Admins bypass OTP check (true), but we track if it was a real match for wallet settlement
            $isRealMatch = ($request->delivery_code === $order->DeliveryCode);
            $result = $settler->settle($order, true, $request->all(), $isRealMatch);

            if ($result['status'] === 'success') {
                return back()->with(['message' => $result['message'], 'alert-type' => 'success']);
            } else {
                return back()->with(['message' => $result['message'], 'alert-type' => 'error']);
            }
        }

        $order->update(['OrderStatus' => $request->status]);

        if ($request->status === 'Delivered' && !$order->PointsAwarded && $order->LoyaltyPoints > 0) {
            $customer = $order->customer;
            if ($customer) {
                LoyaltyTransaction::create([
                    'CustomerID'  => $customer->CustomerID,
                    'Points'      => $order->LoyaltyPoints,
                    'Type'        => 'Earned',
                    'Description' => 'Order #' . $order->OrderID . ' delivered — earned ' . $order->LoyaltyPoints . ' BitePoints 🎉',
                ]);
                $order->update(['PointsAwarded' => true]);
            }
        }

        return back()->with(['message' => 'Order status updated.', 'alert-type' => 'success']);
    }

    public function assignAgent(Request $request, $id)
    {
        Order::findOrFail($id)->update(['DeliveryAgentID' => $request->agent_id, 'OrderStatus' => 'Delivering']);
        return back()->with(['message' => 'Agent assigned.', 'alert-type' => 'success']);
    }

    // ─── Advertisements ───────────────────────────────────────────────────────
    public function ads()
    {
        $ads = Advertising::leftJoin('kitchen_owners', 'advertisings.KitchenOwnerID', '=', 'kitchen_owners.KitchenOwnerID')
            ->leftJoin('caterers', 'advertisings.CatererID', '=', 'caterers.CatererID')
            ->select('advertisings.*', 'kitchen_owners.KitchenName', 'caterers.BusinessName')
            ->orderByDesc('advertisings.AdvertisingID')
            ->paginate(15);
        return view('admin.ads.index', compact('ads'));
    }

    public function storeAd(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $bgImage = null;
        if ($request->file('image')) {
            $file = $request->file('image');
            $filename = date('YmdHi') . '_' . $file->getClientOriginalName();
            $file->move(public_path('upload/ad_images'), $filename);
            $bgImage = $filename;
        }

        Advertising::create([
            'Title'             => $request->title,
            'Description'       => $request->description,
            'StartDate'         => $request->start_date,
            'EndDate'           => $request->end_date,
            'BackgroundImage'   => $bgImage,
            'Status'            => 'Active',
        ]);
        return back()->with(['message' => 'Advertisement created.', 'alert-type' => 'success']);
    }

    public function toggleAd($id)
    {
        $ad = Advertising::findOrFail($id);
        $ad->update(['Status' => $ad->Status === 'Active' ? 'Inactive' : 'Active']);
        return back()->with(['message' => 'Ad status toggled.', 'alert-type' => 'success']);
    }

    public function approveAd($id)
    {
        $ad = Advertising::findOrFail($id);
        $ad->update(['Status' => 'Approved']);
        
        if ($ad->KitchenOwnerID) {
            $ko = KitchenOwner::find($ad->KitchenOwnerID);
            if ($ko) Notification::notify($ko->UserID, 'Ad Approved', "Your advertisement '{$ad->Title}' has been approved and is now active.", 'Promotion');
        }
        
        return back()->with(['message' => 'Advertisement approved.', 'alert-type' => 'success']);
    }

    public function rejectAd($id)
    {
        $ad = Advertising::findOrFail($id);
        $ad->update(['Status' => 'Rejected']);

        if ($ad->KitchenOwnerID) {
            $ko = KitchenOwner::find($ad->KitchenOwnerID);
            if ($ko) Notification::notify($ko->UserID, 'Ad Rejected', "Your advertisement '{$ad->Title}' was rejected.", 'Promotion');
        }

        return back()->with(['message' => 'Advertisement rejected.', 'alert-type' => 'warning']);
    }

    public function deleteAd($id)
    {
        Advertising::findOrFail($id)->delete();
        return back()->with(['message' => 'Ad deleted.', 'alert-type' => 'success']);
    }

    // ─── Subscriptions ────────────────────────────────────────────────────────
    public function subscriptions(Request $request)
    {
        $query = Subscription::leftJoin('customers', 'subscriptions.CustomerID', '=', 'customers.CustomerID')
            ->leftJoin('users', 'customers.UserID', '=', 'users.UserID')
            ->select('subscriptions.*', 'users.FullName as CustomerName', 'users.Email');
        if ($request->status) $query->where('subscriptions.Status', $request->status);
        $subscriptions = $query->orderByDesc('subscriptions.SubscriptionID')->paginate(15);
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function cancelSubscription($id)
    {
        $sub = Subscription::findOrFail($id);
        $refund = $sub->cancelAndRefund('Cancelled by Admin');
        
        $msg = 'Subscription cancelled.';
        if ($refund > 0) {
            $msg .= " Refunded " . number_format($refund, 2) . " to user wallet.";
        }
        
        return back()->with(['message' => $msg, 'alert-type' => 'warning']);
    }

    // ─── Loyalty ──────────────────────────────────────────────────────────────
    public function loyalty(Request $request)
    {
        $query = LoyaltyTransaction::leftJoin('customers', 'loyalty_transactions.CustomerID', '=', 'customers.CustomerID')
            ->leftJoin('users', 'customers.UserID', '=', 'users.UserID')
            ->select('loyalty_transactions.*', 'users.FullName as CustomerName');
        $transactions = $query->orderByDesc('loyalty_transactions.TransactionID')->paginate(15);
        $customers = Customer::join('users', 'customers.UserID', '=', 'users.UserID')
            ->select('customers.CustomerID', 'users.FullName')->get();
        return view('admin.loyalty.index', compact('transactions', 'customers'));
    }

    public function addLoyaltyPoints(Request $request)
    {
        $request->validate(['customer_id' => 'required', 'points' => 'required|integer|min:1', 'type' => 'required']);
        LoyaltyTransaction::create([
            'CustomerID'  => $request->customer_id,
            'Points'      => $request->points,
            'Type'        => $request->type,
            'Description' => $request->description ?? 'Admin manual adjustment',
        ]);
        return back()->with(['message' => 'Points added.', 'alert-type' => 'success']);
    }

    // ─── Catering Requests ────────────────────────────────────────────────────
    public function catering(Request $request)
    {
        $query = CateringRequest::leftJoin('customers', 'catering_requests.CustomerID', '=', 'customers.CustomerID')
            ->leftJoin('users as cu', 'customers.UserID', '=', 'cu.UserID')
            ->leftJoin('caterers', 'catering_requests.CatererID', '=', 'caterers.CatererID')
            ->leftJoin('users as cat', 'caterers.UserID', '=', 'cat.UserID')
            ->select(
                'catering_requests.*',
                'cu.FullName as CustomerName',
                'cat.FullName as CatererName'
            );
        if ($request->status) $query->where('catering_requests.Status', $request->status);
        $requests = $query->orderByDesc('catering_requests.RequestID')->paginate(15);
        return view('admin.catering.index', compact('requests'));
    }

    public function updateCateringStatus(Request $request, $id)
    {
        CateringRequest::findOrFail($id)->update(['Status' => $request->status]);
        return back()->with(['message' => 'Catering request updated.', 'alert-type' => 'success']);
    }

    // ─── Categories ───────────────────────────────────────────────────────────
    public function categories(Request $request)
    {
        $query = \App\Models\Category::query();
        if ($request->search) {
            $query->where('CategoryName', 'like', '%' . $request->search . '%');
        }
        $categories = $query->orderBy('CategoryID')->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['CategoryName' => 'required|string|max:255|unique:categories,Name']);
        \App\Models\Category::create([
            'Name'        => $request->CategoryName,
            'Description' => $request->Description,
        ]);
        return back()->with(['message' => 'Category created successfully.', 'alert-type' => 'success']);
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate(['CategoryName' => 'required|string|max:255|unique:categories,Name,' . $id . ',CategoryID']);
        \App\Models\Category::findOrFail($id)->update([
            'Name'        => $request->CategoryName,
            'Description' => $request->Description,
        ]);
        return back()->with(['message' => 'Category updated.', 'alert-type' => 'success']);
    }

    public function deleteCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        if ($category->menuItems()->exists()) {
            return back()->with([
                'message' => 'Cannot delete category because it is associated with items.',
                'alert-type' => 'error'
            ]);
        }
        $category->delete();
        return back()->with(['message' => 'Category deleted.', 'alert-type' => 'success']);
    }

    // ─── Profile / Password / Logout ─────────────────────────────────────────
    public function AdminProfile()
    {
        $profileData = User::find(Auth::user()->UserID ?? Auth::id());
        return view('admin.admin_profile_view', compact('profileData'));
    }

    public function AdminchangePassword()
    {
        $profileData = User::find(Auth::user()->UserID ?? Auth::id());
        return view('admin.admin_change_password', compact('profileData'));
    }

    public function AdminUpdatePassword(Request $request)
    {
        $request->validate([
            'old_password'              => 'required',
            'new_password'              => 'required|confirmed',
            'new_password_confirmation' => 'required',
        ]);
        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return back()->with(['message' => 'Current password does not match.', 'alert-type' => 'error']);
        }
        User::where('UserID', Auth::user()->UserID)->update(['Password' => Hash::make($request->new_password)]);
        return back()->with(['message' => 'Password changed successfully.', 'alert-type' => 'success']);
    }

    public function store(Request $request)
    {
        $id   = Auth::user()->UserID ?? Auth::id();
        $data = User::find($id);
        $data->FullName = $request->FullName ?? $data->FullName;
        if ($request->hasFile('photo') || $request->hasFile('Image')) {
            $file = $request->file('photo') ?? $request->file('Image');
            $filename = rand().'.'.$file->getClientOriginalExtension();
            
            // Delete old image if it exists to avoid piling up junk
            if (!empty($data->Image) && !str_contains($data->Image, 'no_image') && file_exists(public_path('upload/admin_images/'.$data->Image))) {
                @unlink(public_path('upload/admin_images/'.$data->Image));
            }

            $file->move(public_path('upload/admin_images'), $filename);
            $data->Image = $filename;
        }
        $data->save();
        return back()->with(['message' => 'Profile updated.', 'alert-type' => 'success']);
    }

    public function AdminLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function AdminLogin()
    {
        return view('auth.login');
    }

    // ─── Refund Management ───────────────────────────────────────────────────
    public function refundRequests()
    {
        $requests = RefundRequest::with(['customer.user'])->orderByDesc('created_at')->get();
        return view('admin.refunds.index', compact('requests'));
    }

    public function approveRefund($id)
    {
        $refund = RefundRequest::findOrFail($id);
        if ($refund->Status !== 'Pending') {
            return back()->with(['message' => 'This request has already been processed.', 'alert-type' => 'warning']);
        }

        try {
            DB::beginTransaction();

            $customer = $refund->customer;
            $owner = User::where('Role', 'Owner')->first();
            $kitchen = null;

            if ($refund->RefundableType === 'Order') {
                $order = Order::findOrFail($refund->RefundableID);
                $kitchen = $order->kitchenOwner;
                $order->update(['OrderStatus' => 'Refunded']);
            } else {
                $sub = Subscription::findOrFail($refund->RefundableID);
                $kitchen = $sub->kitchen;
                $sub->update(['Status' => 'Refunded']);
            }

            if (!$kitchen || !$kitchen->user) {
                throw new \Exception("Kitchen owner not found for this refund.");
            }

            $kitchenUser = $kitchen->user;
            $customerUser = $customer->user;

            // 1. Deduct the full refund amount from the Kitchen Owner's wallet
            $kitchenUser->decrement('Wallet_balance', $refund->Amount);

            // 2. Add the refund amount back to the Customer's wallet
            // Note: Checking if the column name is WalletBalance or Wallet_balance based on previous models
            if (Schema::hasColumn('users', 'Wallet_balance')) {
                $customerUser->increment('Wallet_balance', $refund->Amount);
            } else {
                $customer->increment('WalletBalance', $refund->Amount);
            }
            
            // 3. Mark request as approved
            $refund->update([
                'Status' => 'Approved',
                'AdminNotes' => $refund->AdminNotes . "\nApproved by " . Auth::user()->FullName . " on " . now()->format('Y-m-d H:i'),
            ]);

            Notification::notify($customerUser->UserID, 'Refund Approved', "Your refund request for {$refund->Amount} EGP has been approved.", 'System');
            Notification::notify($kitchenUser->UserID, 'Refund Processed', "A refund of {$refund->Amount} EGP has been deducted from your wallet.", 'System');

            DB::commit();
            return back()->with(['message' => 'Refund approved! Funds transferred from Kitchen to Customer wallet.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Refund error: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function rejectRefund(Request $request, $id)
    {
        $refund = RefundRequest::findOrFail($id);
        $refund->update([
            'Status' => 'Rejected',
            'AdminNotes' => $request->admin_notes ?? 'Rejected by ' . Auth::user()->FullName,
        ]);
        if ($refund->customer && $refund->customer->user) {
            Notification::notify($refund->customer->user->UserID, 'Refund Rejected', "Your refund request for {$refund->Amount} EGP was rejected.", 'System');
        }
        return back()->with(['message' => 'Refund request rejected.', 'alert-type' => 'info']);
    }

    // ─── Error Reports ───────────────────────────────────────────────────────
    public function errorReports()
    {
        $reports = ErrorReport::with('user')->orderByDesc('created_at')->get();
        return view('admin.error_reports.index', compact('reports'));
    }

    public function updateErrorReport(Request $request, $id)
    {
        $report = ErrorReport::findOrFail($id);
        $report->update(['Status' => $request->status]);
        return back()->with(['message' => 'Error report status updated.', 'alert-type' => 'success']);
    }

    /**
     * Get real-time stats for the dashboard and notifications.
     */
    public function getRealtimeStats(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $data = [];

        // 1. Notifications
        $notifications = \App\Models\Notification::where('UserID', $user->UserID)
            ->where('IsRead', false)
            ->orderByDesc('CreatedAt')
            ->limit(5)
            ->get();
        
        $data['unreadNotificationsCount'] = $notifications->count();
        $data['notifications'] = $notifications->map(function($n) {
            return [
                'id' => $n->NotificationID,
                'title' => $n->Title,
                'msg' => $n->Message,
                'time' => \Carbon\Carbon::parse($n->CreatedAt)->diffForHumans(),
                'type' => strtolower($n->Type),
                'icon' => match($n->Type) {
                    'Order' => 'shopping-cart',
                    'Promotion' => 'gift',
                    'Chat' => 'message-square',
                    default => 'bell',
                }
            ];
        });

        // 2. Dashboard KPIs (If Admin/Owner)
        if (in_array($user->Role, ['Admin', 'Owner'])) {
            $range = $request->input('range', 'today');
            
            $applyFilter = function($query, $dateCol = 'CreatedAt') use ($range) {
                if ($range == 'today') return $query->whereDate($dateCol, today());
                if ($range == 'week') return $query->where($dateCol, '>=', now()->startOfWeek());
                if ($range == 'month') return $query->where($dateCol, '>=', now()->startOfMonth());
                if ($range == 'year') return $query->where($dateCol, '>=', now()->startOfYear());
                return $query;
            };

            $orderItemsSum = $applyFilter(\App\Models\Order::whereNotIn('OrderStatus', ['Cancelled']))
                ->select(DB::raw('SUM(CASE WHEN TotalPrice > 15 THEN TotalPrice - 15 ELSE 0 END) as items_sum'))
                ->value('items_sum') ?? 0;
            $orderCommission = $orderItemsSum * 0.15;
            $subRevenue = $applyFilter(\App\Models\Subscription::whereNotIn('Status', ['Cancelled', 'Pending']), 'StartDate')->sum('PaidAmount');
            $totalPointsDiscount = $applyFilter(\App\Models\Order::whereNotIn('OrderStatus', ['Cancelled']))->sum('PointsDiscount');

            $data['kpis'] = [
                'totalOrders' => $applyFilter(\App\Models\Order::query())->count(),
                'totalRevenue' => (float) $applyFilter(\App\Models\Order::whereNotIn('OrderStatus', ['Cancelled']))->sum('TotalPrice'),
                'pendingOrders' => \App\Models\Order::where('OrderStatus', 'Pending')->count(),
                'totalCustomers' => \App\Models\User::where('Role', 'Customer')->count(),
                'siteCommission' => (float) ($orderCommission + $subRevenue - $totalPointsDiscount),
                'totalWalletsSum' => (float) (\App\Models\User::whereNotIn('Role', ['Admin', 'Owner'])->sum('Wallet_balance') + \App\Models\Customer::sum('WalletBalance')),
            ];
        }

        return response()->json($data);
    }

    /**
     * Returns ONLY the HTML fragment of the orders table.
     */
    public function ordersTableFragment(Request $request)
    {
        $query = Order::leftJoin('customers', 'orders.CustomerID', '=', 'customers.CustomerID')
            ->leftJoin('users as cu', 'customers.UserID', '=', 'cu.UserID')
            ->leftJoin('delivery_agents', 'orders.DeliveryAgentID', '=', 'delivery_agents.DeliveryAgentID')
            ->leftJoin('users as au', 'delivery_agents.UserID', '=', 'au.UserID')
            ->leftJoin('payments', 'orders.PaymentID', '=', 'payments.PaymentID')
            ->leftJoin('user_addresses', function($join) {
                $join->on('cu.UserID', '=', 'user_addresses.UserID')
                     ->where('user_addresses.IsPrimary', 1);
            })
            ->select('orders.*', 'cu.FullName as CustomerName', 'au.FullName as AgentName', 'payments.Method as PaymentMethod', 'user_addresses.Address as CustomerAddress');
        
        if ($request->status) $query->where('orders.OrderStatus', $request->status);
        
        $orders = $query->orderByDesc('orders.OrderID')->paginate(15);
        $agents = DeliveryAgent::join('users', 'delivery_agents.UserID', '=', 'users.UserID')
            ->where('delivery_agents.Status', 'Available')
            ->select('delivery_agents.DeliveryAgentID', 'users.FullName', 'delivery_agents.ServiceArea')
            ->get();

        return view('admin.orders.table_body', compact('orders', 'agents'))->render();
    }

    // ─── Promo Codes ──────────────────────────────────────────────────────────
    public function promoCodes(Request $request)
    {
        $query = PromoCode::query();
        if ($request->filled('creator_role')) {
            $query->where('CreatorRole', $request->creator_role);
        }
        $promoCodes = $query->latest()->paginate(20);
        return view('admin.promo_codes.index', compact('promoCodes'));
    }

    public function storePromoCode(Request $request)
    {
        $request->validate([
            'Code'           => 'required|string|max:50|unique:promo_codes,Code',
            'Type'           => 'required|in:Percentage,Fixed',
            'Value'          => 'required|numeric|min:0' . ($request->Type === 'Percentage' ? '|max:100' : ''),
            'MinOrderAmount' => 'required|numeric|min:0',
            'MaxUses'        => 'nullable|integer|min:1',
            'ExpiryDate'     => 'nullable|date|after:today',
        ]);

        PromoCode::create([
            'Code'           => strtoupper(trim($request->Code)),
            'Type'           => $request->Type,
            'Value'          => $request->Value,
            'MinOrderAmount' => $request->MinOrderAmount,
            'MaxUses'        => $request->MaxUses,
            'ExpiryDate'     => $request->ExpiryDate,
            'IsActive'       => true,
            'UsedCount'      => 0,
        ]);

        return back()->with(['message' => 'Promo code created successfully!', 'alert-type' => 'success']);
    }

    public function updatePromoCode(Request $request, $id)
    {
        $promo = PromoCode::findOrFail($id);
        $request->validate([
            'Code'           => 'required|string|max:50|unique:promo_codes,Code,' . $id . ',PromoCodeID',
            'Type'           => 'required|in:Percentage,Fixed',
            'Value'          => 'required|numeric|min:0' . ($request->Type === 'Percentage' ? '|max:100' : ''),
            'MinOrderAmount' => 'required|numeric|min:0',
            'MaxUses'        => 'nullable|integer|min:1',
            'ExpiryDate'     => 'nullable|date',
        ]);

        $promo->update([
            'Code'           => strtoupper(trim($request->Code)),
            'Type'           => $request->Type,
            'Value'          => $request->Value,
            'MinOrderAmount' => $request->MinOrderAmount,
            'MaxUses'        => $request->MaxUses,
            'ExpiryDate'     => $request->ExpiryDate,
        ]);

        return back()->with(['message' => 'Promo code updated successfully!', 'alert-type' => 'success']);
    }

    public function togglePromoCode($id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->update(['IsActive' => !$promo->IsActive]);
        $status = $promo->IsActive ? 'activated' : 'deactivated';
        return back()->with(['message' => "Promo code {$status}!", 'alert-type' => 'success']);
    }

    public function deletePromoCode($id)
    {
        PromoCode::findOrFail($id)->delete();
        return back()->with(['message' => 'Promo code deleted.', 'alert-type' => 'success']);
    }

    public function announcePromoCode($id)
    {
        $promo = PromoCode::findOrFail($id);

        if ($promo->email_sent_at) {
            return response()->json([
                'success' => false,
                'message' => 'This promo code announcement was already sent on ' . $promo->email_sent_at->format('d M Y, h:i A') . '.'
            ]);
        }

        $customers = User::where('Role', 'Customer')->whereNotNull('email')->get();
        $sentCount = 0;

        try {
            foreach ($customers as $customer) {
                Mail::to($customer->email)->send(new PromoCodeAnnouncement($promo));
                $sentCount++;
            }
            $promo->update(['email_sent_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Announcement sent to ' . $sentCount . ' customers successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send emails. Error: ' . $e->getMessage()
            ]);
        }
    }
}
