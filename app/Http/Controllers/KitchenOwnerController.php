<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\MenuOrderItem;
use App\Models\KitchenOwner;
use App\Models\Category;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Subscription;
use App\Models\KitchenPlan;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\Advertising;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountDeletedMail;
use App\Mail\PromoCodeAnnouncement;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use App\Services\PaymobService;
use App\Models\Notification;

class KitchenOwnerController extends Controller
{
    private function myKitchen()
    {
        return KitchenOwner::where('UserID', Auth::user()->UserID)->first();
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────
    public function KitchenDashboard()
    {
        $user = Auth::user();
        $kitchen = $this->myKitchen();
        $totalMenuItems = $pendingOrders = $totalOrders = $todayOrders = 0;
        $activeSubscribers = 0;
        $todaySubscribedMeals = 0;

        $monthlySalesData = array_fill(0, 12, 0);

        if ($kitchen) {
            $totalMenuItems = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->count();
            $menuItemIds = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->pluck('MenuItemID');
            if ($menuItemIds->isNotEmpty()) {
                $orderIds = MenuOrderItem::whereIn('MenuItemID', $menuItemIds)->pluck('OrderID')->unique();
                $totalOrders = Order::whereIn('OrderID', $orderIds)->count();
                $pendingOrders = Order::whereIn('OrderID', $orderIds)->where('OrderStatus', 'Pending')->count();
                $todayOrders = Order::whereIn('OrderID', $orderIds)->whereDate('CreatedAt', today())->count();

                // Get sales count for each month in the current year
                $monthlySales = Order::select(\Illuminate\Support\Facades\DB::raw('MONTH(CreatedAt) as month'), \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                    ->whereIn('OrderID', $orderIds)
                    ->whereYear('CreatedAt', date('Y'))
                    ->groupBy('month')
                    ->pluck('count', 'month')->toArray();

                // Get monthly revenue for the current month
                $monthlyRevenue = Order::whereIn('OrderID', $orderIds)
                    ->whereMonth('CreatedAt', date('m'))
                    ->whereYear('CreatedAt', date('Y'))
                    ->where('OrderStatus', 'Delivered') // Only count delivered for revenue
                    ->sum('TotalPrice');

                // Get open support tickets count for THIS kitchen
                $openSupportTickets = \App\Models\SupportTicket::where('UserID', Auth::id())
                    ->whereIn('Status', ['Open', 'InProgress'])
                    ->count();

                // Get last 5 orders for quick view
                $recentOrders = Order::whereIn('OrderID', $orderIds)
                    ->orderBy('CreatedAt', 'desc')
                    ->limit(5)
                    ->get();

                for ($i = 1; $i <= 12; $i++) {
                    $monthlySalesData[$i - 1] = $monthlySales[$i] ?? 0;
                }
            }

            // ─── Subscriptions Logic ─────────────────────────────────────────
            // Get unique customers who have active subscriptions with items from THIS kitchen
            $activeSubscribers = \App\Models\Subscription::where('Status', 'Active')
                ->whereDate('StartDate', '<=', now())
                ->whereDate('EndDate', '>=', now())
                ->whereHas('menuItems', function ($q) use ($kitchen) {
                    $q->where('KitchenOwnerID', $kitchen->KitchenOwnerID)
                        ->where('menu_subscribes.Status', 'Approved');
                })->count();

            // Count how many meal units the kitchen needs to prepare TODAY
            // (Assuming 1 meal per subscriber per day for simplicity in this version)
            $todaySubscribedMeals = $activeSubscribers; // Each active sub = 1 meal today
        }
        return view('admin.kitchen.index', compact(
            'user',
            'kitchen',
            'totalMenuItems',
            'totalOrders',
            'pendingOrders',
            'todayOrders',
            'monthlySalesData',
            'activeSubscribers',
            'todaySubscribedMeals',
            'monthlyRevenue',
            'openSupportTickets',
            'recentOrders'
        ));
    }

    public function KitchenKPI(Request $request)
    {
        $user = Auth::user();
        $kitchen = $this->myKitchen();
        if (!$kitchen) return redirect()->route('kitchen.dashboard');

        $range = $request->query('range', 'all');
        $startDate = null;
        $endDate = now();

        switch ($range) {
            case 'today': $startDate = now()->startOfDay(); break;
            case 'week': $startDate = now()->startOfWeek(); break;
            case 'month': $startDate = now()->startOfMonth(); break;
            case 'year': $startDate = now()->startOfYear(); break;
            default: $range = 'all'; break;
        }

        $applyFilter = function($query, $column = 'orders.CreatedAt') use ($startDate, $endDate) {
            if ($startDate) return $query->whereBetween($column, [$startDate, $endDate]);
            return $query;
        };

        // Base order query for THIS kitchen
        $orderQuery = Order::join('menu_order_items', 'orders.OrderID', '=', 'menu_order_items.OrderID')
            ->join('menu_items', 'menu_order_items.MenuItemID', '=', 'menu_items.MenuItemID')
            ->where('menu_items.KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->select('orders.*')
            ->distinct();

        // 1. Total Revenue (All time)
        $totalRevenue = (clone $orderQuery)->where('orders.OrderStatus', 'Delivered')->sum('orders.TotalPrice');
        
        // 2. Today's Revenue
        $todayRevenue = (clone $orderQuery)->whereDate('orders.CreatedAt', today())->where('orders.OrderStatus', 'Delivered')->sum('orders.TotalPrice');
        
        // 3. This Month's Revenue
        $monthlyRevenueTotal = (clone $orderQuery)->whereMonth('orders.CreatedAt', date('m'))->whereYear('orders.CreatedAt', date('Y'))->where('orders.OrderStatus', 'Delivered')->sum('orders.TotalPrice');

        // 4. Total Orders (Filtered by range)
        $totalOrdersCount = $applyFilter(clone $orderQuery)->count();

        // 5. Pending Orders (Snapshot)
        $pendingOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Pending')->count();

        // 6. Completed Orders (Snapshot)
        $completedOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Delivered')->count();

        // 7. Average Order Value (AOV)
        $deliveredOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Delivered')->count();
        $aov = $deliveredOrdersCount > 0 ? $totalRevenue / $deliveredOrdersCount : 0;

        // 8. Total Menu Items
        $totalMenuItemsCount = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->count();

        // 9. Active Subscriptions
        $activeSubscriptionsCount = Subscription::where('Status', 'Active')
            ->whereHas('menuItems', function($q) use ($kitchen) {
                $q->where('KitchenOwnerID', $kitchen->KitchenOwnerID);
            })->count();

        // 10. Subscriber Revenue (Monthly MRR approximation)
        $subscriberRevenue = Subscription::where('Status', 'Active')
            ->whereHas('menuItems', function($q) use ($kitchen) {
                $q->where('KitchenOwnerID', $kitchen->KitchenOwnerID);
            })->sum('PaidAmount');

        // 11. Cancellation Rate
        $cancelledOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Cancelled')->count();
        $totalOrdersEver = (clone $orderQuery)->count();
        $cancellationRate = $totalOrdersEver > 0 ? ($cancelledOrdersCount / $totalOrdersEver) * 100 : 0;

        // 12. Customer Satisfaction (Avg Rating)
        $avgRating = Review::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->avg('Rating') ?? 0;

        // 13. Total Reviews
        $totalReviewsCount = Review::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->count();

        // 14. Active Advertisements
        $activeAdsCount = Advertising::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->where('Status', 'Active')->count();

        // Chart Data (Revenue Trends)
        $chartData = [];
        $chartLabels = [];
        $revenueByMonth = (clone $orderQuery)->where('orders.OrderStatus', 'Delivered')
            ->select(DB::raw('MONTH(orders.CreatedAt) as month'), DB::raw('SUM(orders.TotalPrice) as total'))
            ->whereYear('orders.CreatedAt', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')->toArray();

        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = date('M', mktime(0, 0, 0, $i, 1));
            $chartData[] = (float)($revenueByMonth[$i] ?? 0);
        }

        // Top Selling Items
        $topItems = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->withCount(['orders as sales_count'])
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get();

        // Recent Reviews
        $recentReviews = Review::where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->with('customer.user')
            ->orderByDesc('ReviewID')
            ->limit(3)
            ->get();

        return view('admin.kitchen.kpi', compact(
            'kitchen', 'range', 'totalRevenue', 'todayRevenue', 'monthlyRevenueTotal',
            'totalOrdersCount', 'pendingOrdersCount', 'completedOrdersCount', 'aov',
            'totalMenuItemsCount', 'activeSubscriptionsCount', 'subscriberRevenue',
            'cancellationRate', 'avgRating', 'totalReviewsCount', 'activeAdsCount',
            'chartData', 'chartLabels', 'topItems', 'recentReviews'
        ));
    }

    public function togglePlanRequests(Request $request)
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        $kitchen->update([
            'AcceptsPlanRequests' => $request->has('accepts_plan_requests') ? 1 : 0
        ]);

        return back()->with(['message' => 'Plan requests preference updated.', 'alert-type' => 'success']);
    }

    public function updateHours(Request $request)
    {
        $request->validate([
            'opening_time' => 'required',
            'closing_time' => 'required',
        ]);

        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        $kitchen->update([
            'OpeningTime' => $request->opening_time,
            'ClosingTime' => $request->closing_time,
        ]);

        return back()->with(['message' => 'Working hours updated successfully.', 'alert-type' => 'success']);
    }

    // ─── Menu Items ───────────────────────────────────────────────────────────
    public function menuItems()
    {
        $kitchen = $this->myKitchen();
        $items = $kitchen
            ? MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->orderByDesc('MenuItemID')->paginate(15)
            : collect();
        $categories = Category::all();
        $tags = \App\Models\Tag::all()->groupBy('category');
        return view('admin.kitchen.menu.index', compact('items', 'categories', 'kitchen', 'tags'));
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'ItemName' => 'required|string|max:255',
            'ItemPrice' => 'required|numeric|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        // Resolve category: create new if name typed, else use selected ID
        $categoryID = $request->CategoryID ?: null;
        if (!empty(trim($request->NewCategoryName ?? ''))) {
            $newCat = Category::firstOrCreate(['Name' => trim($request->NewCategoryName)]);
            $categoryID = $newCat->CategoryID;
        }

        $item = MenuItem::create([
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'CategoryID' => $categoryID,
            'ItemName' => $request->ItemName,
            'Description' => $request->Description,
            'Ingredients' => $request->Ingredients,
            'PortionSize' => $request->PortionSize,
            'Calories' => $request->Calories,
            'Protein' => $request->Protein,
            'Carbs' => $request->Carbs,
            'Fats' => $request->Fats,
            'PrepTime' => $request->PrepTime,
            'ItemPrice' => $request->ItemPrice,
            'DiscountPrice' => rtrim(trim($request->DiscountPrice), '.') === '' ? null : $request->DiscountPrice,
            'Status' => 'Available',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . rand() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/item_images'), $filename);
                \App\Models\ItemImage::create([
                    'MenuItemID' => $item->MenuItemID,
                    'Image' => $filename
                ]);
            }
        }

        if ($request->has('tags')) {
            $item->tags()->sync($request->tags);
        }

        return back()->with(['message' => 'Item added successfully.', 'alert-type' => 'success']);
    }

    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'ItemName' => 'required',
            'ItemPrice' => 'required|numeric',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);
        $kitchen = $this->myKitchen();
        $item = MenuItem::where('MenuItemID', $id)->where('KitchenOwnerID', $kitchen->KitchenOwnerID)->firstOrFail();

        // Resolve category: create new if name typed, else use selected ID
        $categoryID = $request->CategoryID ?: null;
        if (!empty(trim($request->NewCategoryName ?? ''))) {
            $newCat = Category::firstOrCreate(['Name' => trim($request->NewCategoryName)]);
            $categoryID = $newCat->CategoryID;
        }

        $item->update([
            'ItemName' => $request->ItemName,
            'Description' => $request->Description,
            'Ingredients' => $request->Ingredients,
            'PortionSize' => $request->PortionSize,
            'Calories' => $request->Calories,
            'Protein' => $request->Protein,
            'Carbs' => $request->Carbs,
            'Fats' => $request->Fats,
            'PrepTime' => $request->PrepTime,
            'ItemPrice' => $request->ItemPrice,
            'DiscountPrice' => rtrim(trim($request->DiscountPrice), '.') === '' ? null : $request->DiscountPrice,
            'CategoryID' => $categoryID,
        ]);

        if ($request->hasFile('images')) {
            // Delete old images from database and storage to replace them
            $oldImages = \App\Models\ItemImage::where('MenuItemID', $item->MenuItemID)->get();
            foreach ($oldImages as $oldImg) {
                if (file_exists(public_path('upload/item_images/' . $oldImg->Image))) {
                    @unlink(public_path('upload/item_images/' . $oldImg->Image));
                }
            }
            \App\Models\ItemImage::where('MenuItemID', $item->MenuItemID)->delete();

            // Save new images
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . rand() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/item_images'), $filename);
                \App\Models\ItemImage::create([
                    'MenuItemID' => $item->MenuItemID,
                    'Image' => $filename
                ]);
            }
        }

        if ($request->has('tags')) {
            $item->tags()->sync($request->tags);
        } else {
            $item->tags()->detach();
        }

        return back()->with(['message' => 'Item updated successfully.', 'alert-type' => 'success']);
    }

    public function toggleItem($id)
    {
        $kitchen = $this->myKitchen();
        $item = MenuItem::where('MenuItemID', $id)->where('KitchenOwnerID', $kitchen->KitchenOwnerID)->firstOrFail();
        $item->update(['Status' => $item->Status === 'Available' ? 'Unavailable' : 'Available']);
        return back()->with(['message' => 'Item status updated.', 'alert-type' => 'success']);
    }

    public function deleteItem($id)
    {
        $kitchen = $this->myKitchen();
        MenuItem::where('MenuItemID', $id)->where('KitchenOwnerID', $kitchen->KitchenOwnerID)->firstOrFail()->delete();
        return back()->with(['message' => 'Item deleted.', 'alert-type' => 'success']);
    }

    // ─── Orders ───────────────────────────────────────────────────────────────
    public function kitchenOrders(Request $request)
    {
        $kitchen = $this->myKitchen();
        $orders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        if ($kitchen) {
            $menuItemIds = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->pluck('MenuItemID');
            
            // Collect order IDs from traditional menu items
            $orderIdsFromItems = MenuOrderItem::whereIn('MenuItemID', $menuItemIds)->pluck('OrderID')->unique();
            
            // Collect order IDs from direct custom requests (where ReceiverID is the kitchen owner's UserID)
            $orderIdsFromChats = \App\Models\LiveChat::where('ReceiverID', Auth::user()->UserID)
                ->whereNotNull('OrderID')
                ->pluck('OrderID')
                ->unique();

            $allOrderIds = $orderIdsFromItems->merge($orderIdsFromChats)->unique();

            if ($allOrderIds->isNotEmpty()) {
                $query = Order::whereIn('orders.OrderID', $allOrderIds)
                    ->leftJoin('customers', 'orders.CustomerID', '=', 'customers.CustomerID')
                    ->leftJoin('users', 'customers.UserID', '=', 'users.UserID')
                    ->select('orders.*', 'users.FullName as CustomerName');

                // NEW: Handle separation of Standard and Plan orders
                if ($request->type === 'plan') {
                    $query->whereNotNull('orders.SubscriptionID');
                } else if ($request->type === 'standard') {
                    $query->whereNull('orders.SubscriptionID');
                }
                
                if ($request->status) {
                    $query->where('orders.OrderStatus', $request->status);
                }

                // Date filter: for Meal Plan orders use ScheduledDate, for others use CreatedAt
                if ($request->date_from) {
                    $query->where(function($q) use ($request) {
                        $q->whereDate('orders.ScheduledDate', '>=', $request->date_from)
                          ->orWhere(function($q2) use ($request) {
                              $q2->whereNull('orders.ScheduledDate')
                                 ->whereDate('orders.CreatedAt', '>=', $request->date_from);
                          });
                    });
                }
                if ($request->date_to) {
                    $query->where(function($q) use ($request) {
                        $q->whereDate('orders.ScheduledDate', '<=', $request->date_to)
                          ->orWhere(function($q2) use ($request) {
                              $q2->whereNull('orders.ScheduledDate')
                                 ->whereDate('orders.CreatedAt', '<=', $request->date_to);
                          });
                    });
                }

                $regularOrders = $query
                    ->orderByDesc('orders.OrderID')
                    ->paginate(15, ['*'], 'orders_page')
                    ->withQueryString();
            } else {
                $regularOrders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            }
            
            // Second partition: Actual Meal Plan Contracts (Subscriptions)
            $subscriptionsQuery = \App\Models\Subscription::where('KitchenOwnerID', $kitchen->KitchenOwnerID)
                ->with(['customer.user', 'kitchenPlan']);
                
            $mealPlanOrders = $subscriptionsQuery
                ->orderByDesc('SubscriptionID')
                ->paginate(15, ['*'], 'meals_page');

            return view('admin.kitchen.orders.index', compact('regularOrders', 'mealPlanOrders'));
        }
        
        $regularOrders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        $mealPlanOrders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        
        return view('admin.kitchen.orders.index', compact('regularOrders', 'mealPlanOrders'));
    }

    public function updateKitchenOrderStatus(Request $request, $id)
    {
        $kitchen = $this->myKitchen();
        $menuItemIds = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->pluck('MenuItemID');
        $orderIds = MenuOrderItem::whereIn('MenuItemID', $menuItemIds)->pluck('OrderID')->unique();
        abort_unless($orderIds->contains((int) $id), 403);

        $order = Order::findOrFail($id);

        // Restrict Kitchen Owners to preparation-phase statuses
        $allowedStatuses = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Cancelled'];
        if (!in_array($request->status, $allowedStatuses)) {
            return back()->with(['message' => 'Status upgrade restricted. Only agents or admins can mark as delivering/delivered.', 'alert-type' => 'error']);
        }

        $order->update(['OrderStatus' => $request->status]);

        return back()->with(['message' => 'Order status updated.', 'alert-type' => 'success']);
    }



    // ─────────────────────────────────────────────────────────────────────
    // Kitchen Subscription Plans (CRUD)
    // ─────────────────────────────────────────────────────────────────────

    public function plans()
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen not found.', 'alert-type' => 'error']);

        $plans = KitchenPlan::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->orderByDesc('KitchenPlanID')->get();
        return view('admin.kitchen.plans.index', compact('plans'));
    }

    public function createPlan()
    {
        $kitchen = $this->myKitchen();
        $menuItems = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->where('Status', 'Available')->get();
        return view('admin.kitchen.plans.create', compact('menuItems'));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'plan_time' => 'required|in:Daily,Weekly,Monthly',
            'meals_per_day' => 'required|integer|in:1,2,3',
            'description' => 'nullable|string',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'exists:menu_items,MenuItemID',
        ]);

        $kitchen = $this->myKitchen();

        $plan = KitchenPlan::create([
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'Title' => $request->title,
            'Description' => $request->description,
            'Price' => $request->price,
            'PlanTime' => $request->plan_time,
            'MealsPerDay' => $request->meals_per_day,
            'Status' => 'Active',
        ]);

        $plan->menuItems()->sync($request->menu_items);

        return redirect()->route('kitchen.plans')->with(['message' => 'Plan created successfully!', 'alert-type' => 'success']);
    }

    public function editPlan($id)
    {
        $kitchen = $this->myKitchen();
        $plan = KitchenPlan::with('menuItems')->where('KitchenPlanID', $id)->where('KitchenOwnerID', $kitchen->KitchenOwnerID)->firstOrFail();
        $menuItems = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->where('Status', 'Available')->get();
        return view('admin.kitchen.plans.edit', compact('plan', 'menuItems'));
    }

    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'plan_time' => 'required|in:Daily,Weekly,Monthly',
            'meals_per_day' => 'required|integer|in:1,2,3',
            'status' => 'required|in:Active,Inactive',
            'menu_items' => 'required|array|min:1',
            'menu_items.*' => 'exists:menu_items,MenuItemID',
        ]);

        $kitchen = $this->myKitchen();
        $plan = KitchenPlan::where('KitchenPlanID', $id)->where('KitchenOwnerID', $kitchen->KitchenOwnerID)->firstOrFail();

        $plan->update([
            'Title' => $request->title,
            'Description' => $request->description,
            'Price' => $request->price,
            'PlanTime' => $request->plan_time,
            'MealsPerDay' => $request->meals_per_day,
            'Status' => $request->status,
        ]);

        $plan->menuItems()->sync($request->menu_items);

        return redirect()->route('kitchen.plans')->with(['message' => 'Plan updated successfully!', 'alert-type' => 'success']);
    }

    public function deletePlan($id)
    {
        $kitchen = $this->myKitchen();
        KitchenPlan::where('KitchenPlanID', $id)->where('KitchenOwnerID', $kitchen->KitchenOwnerID)->firstOrFail()->delete();
        return back()->with(['message' => 'Plan deleted.', 'alert-type' => 'success']);
    }

    // ─── Subscriptions ────────────────────────────────────────────────────────
    public function subscriptions()
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        $kitchenId = $kitchen->KitchenOwnerID;

        // Final Subscription Fetch:
        // 1. Where KitchenPlanID belongs to this kitchen's plans
        // 2. OR Where it contains menu items belonging to this kitchen
        $subscriptions = \App\Models\Subscription::where('KitchenOwnerID', $kitchenId)
            ->with(['customer.user', 'kitchenPlan', 'menuItems'])
            ->orderByDesc('SubscriptionID')
            ->paginate(10);

        return view('admin.kitchen.subscriptions.index', compact('subscriptions', 'kitchenId'));
    }

    public function subscriptionRequests()
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen not found.', 'alert-type' => 'error']);

        $requests = \App\Models\Subscription::where('Status', 'PendingApproval')
            ->where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->with(['customer.user', 'menuItems'])
            ->orderByDesc('SubscriptionID')
            ->get();

        return view('admin.kitchen.subscriptions.requests', compact('requests'));
    }

    public function approveSubscriptionRequest(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0'
        ]);

        $kitchen = $this->myKitchen();
        $subscription = \App\Models\Subscription::where('SubscriptionID', $id)
            ->where('Status', 'PendingApproval')
            ->where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->firstOrFail();

        $subscription->update([
            'Status' => 'AwaitingPayment',
            'Price' => $request->price,
            'DeliveryCharge' => (($subscription->DurationDays ?? 0) * ($subscription->MealsPerDay ?? 1)) * 15.00,
            'DepositAmount' => $request->deposit_amount ?? 0
        ]);

        // Also update pivot items status
        \Illuminate\Support\Facades\DB::table('menu_subscribes')
            ->where('SubscriptionID', $id)
            ->whereIn('MenuItemID', MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->pluck('MenuItemID'))
            ->update(['Status' => 'Approved']);

        return back()->with(['message' => 'Subscription approved and price set.', 'alert-type' => 'success']);
    }

    public function rejectSubscriptionRequest(Request $request, $id)
    {
        $kitchen = $this->myKitchen();
        $subscription = \App\Models\Subscription::where('SubscriptionID', $id)
            ->where('Status', 'PendingApproval')
            ->where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->firstOrFail();

        $subscription->update(['Status' => 'Rejected']);

        \Illuminate\Support\Facades\DB::table('menu_subscribes')
            ->where('SubscriptionID', $id)
            ->whereIn('MenuItemID', MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->pluck('MenuItemID'))
            ->update(['Status' => 'Rejected']);

        return back()->with(['message' => 'Subscription request rejected.', 'alert-type' => 'success']);
    }

    public function updateSubscriptionItem(Request $request, $subscription_id, $item_id)
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        $subscription = \App\Models\Subscription::where('SubscriptionID', $subscription_id)
            ->whereHas('menuItems', function ($q) use ($kitchen, $item_id) {
                $q->where('menu_items.KitchenOwnerID', $kitchen->KitchenOwnerID)
                    ->where('menu_items.MenuItemID', $item_id);
            })->firstOrFail();

        $action = $request->input('action');
        $notes = $request->input('kitchen_notes');

        // Update the pivot record specifically for this item
        $pivotQuery = \Illuminate\Support\Facades\DB::table('menu_subscribes')
            ->where('SubscriptionID', $subscription_id)
            ->where('MenuItemID', $item_id);

        if ($action === 'approve') {
            $pivotQuery->update(['Status' => 'Approved', 'KitchenNotes' => $notes]);
            $msg = 'Subscription item approved.';
        } elseif ($action === 'reject') {
            $pivotQuery->update(['Status' => 'Rejected', 'KitchenNotes' => $notes]);
            $msg = 'Subscription item rejected.';
        } elseif ($action === 'modify_approve') {
            $pivotQuery->update(['ModifiedStatus' => 'None', 'KitchenNotes' => $notes]);
            $msg = 'Modification approved.';
        } elseif ($action === 'modify_reject') {
            // Depending on business logic, rejecting a modification might revert it or drop it.
            // For now, we just clear the ModifiedStatus.
            $pivotQuery->update(['ModifiedStatus' => 'None', 'KitchenNotes' => $notes]);
            $msg = 'Modification rejected.';
        } else {
            return back()->with(['message' => 'Invalid action.', 'alert-type' => 'error']);
        }

        return back()->with(['message' => $msg, 'alert-type' => 'success']);
    }

    // ─── Advertisements ───────────────────────────────────────────────────────
    public function ads()
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        $ads = \App\Models\Advertising::where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->orderByDesc('AdvertisingID')
            ->paginate(15);
        return view('admin.kitchen.ads.index', compact('ads', 'kitchen'));
    }

    public function storeAd(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        $pricePerDay = 50.00; // Default price per day in currency units
        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);
        $days = $start->diffInDays($end);
        $total = $pricePerDay * $days;

        if (\Illuminate\Support\Facades\Auth::user()->Wallet_balance < $total) {
            return back()->with(['message' => "Insufficient wallet balance. You need {$total} EGP for {$days} days.", 'alert-type' => 'error']);
        }

        \Illuminate\Support\Facades\Auth::user()->decrement('Wallet_balance', $total);

        $bgImage = null;
        if ($request->file('image')) {
            $file = $request->file('image');
            $filename = date('YmdHi') . '_' . $file->getClientOriginalName();
            $file->move(public_path('upload/ad_images'), $filename);
            $bgImage = $filename;
        }

        $ad = \App\Models\Advertising::create([
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'Title' => $request->title,
            'Description' => $request->description,
            'StartDate' => $request->start_date,
            'EndDate' => $request->end_date,
            'PricePerDay' => $pricePerDay,
            'TotalAmount' => $total,
            'PaidAt' => now(),
            'Status' => 'Pending',
            'BackgroundImage' => $bgImage,
        ]);

        // Notify Admins
        $admins = User::whereIn('Role', ['Admin', 'Owner'])->get();
        foreach ($admins as $admin) {
            Notification::notify($admin->UserID, 'New Ad Submission', "New advertisement '{$ad->Title}' submitted by {$kitchen->KitchenName}.", 'Promotion');
        }
        return back()->with(['message' => "Advertisement submitted for approval. {$total} EGP deducted from your wallet.", 'alert-type' => 'success']);
    }

    // ─── Categories (CRUD) ──────────────────────────────────────────────────
    public function categories()
    {
        $query = Category::query();
        if (request('search')) {
            $query->where('Name', 'like', '%' . request('search') . '%');
        }
        $categories = $query->orderBy('Name')->paginate(15);
        return view('admin.kitchen.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['CategoryName' => 'required|string|max:255|unique:categories,Name']);
        Category::create([
            'Name' => trim($request->CategoryName),
            'Description' => $request->Description,
        ]);
        return back()->with(['message' => 'Category added successfully.', 'alert-type' => 'success']);
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate(['CategoryName' => 'required|string|max:255|unique:categories,Name,' . $id . ',CategoryID']);
        Category::findOrFail($id)->update([
            'Name' => trim($request->CategoryName),
            'Description' => $request->Description,
        ]);
        return back()->with(['message' => 'Category updated.', 'alert-type' => 'success']);
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
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
    public function KitchenProfile()
    {
        return view('admin.kitchen.profile', ['profileData' => Auth::user()]);
    }

    public function store(Request $request)
    {
        $data = User::find(Auth::user()->UserID);
        $data->FullName = $request->name ?? $data->FullName;
        if ($request->hasFile('photo')) {
            $f = $request->file('photo');
            $filename = rand() . '.' . $f->getClientOriginalExtension();

            // Delete old image if it exists to avoid piling up junk
            if (!empty($data->Image) && !str_contains($data->Image, 'no_image') && file_exists(public_path('upload/admin_images/' . $data->Image))) {
                @unlink(public_path('upload/admin_images/' . $data->Image));
            }

            $f->move(public_path('upload/admin_images'), $filename);
            $data->Image = $filename;
        }
        $data->save();
        
        if ($request->has('location')) {
            $kitchen = \App\Models\KitchenOwner::where('UserID', \Illuminate\Support\Facades\Auth::user()->UserID)->first();
            if ($kitchen) {
                $kitchen->Location = $request->location;
                if ($request->has('latitude') && $request->has('longitude')) {
                    $kitchen->Latitude = $request->latitude;
                    $kitchen->Longitude = $request->longitude;
                }
                $kitchen->OpeningTime = $request->opening_time;
                $kitchen->ClosingTime = $request->closing_time;
                $kitchen->save();
            }
        }
        
        return back()->with(['message' => 'Profile Updated.', 'alert-type' => 'success']);
    }

    public function KitchenChangePassword()
    {
        return view('admin.kitchen.change_password', ['profileData' => Auth::user()]);
    }

    public function KitchenUpdatePassword(Request $request)
    {
        $request->validate(['old_password' => 'required', 'new_password' => 'required|confirmed']);
        if (!Hash::check($request->old_password, Auth::user()->Password))
            return back()->with(['message' => 'Current password does not match.', 'alert-type' => 'error']);
        User::where('UserID', Auth::user()->UserID)->update(['Password' => Hash::make($request->new_password)]);
        return back()->with(['message' => 'Password changed.', 'alert-type' => 'success']);
    }

    public function planSubscribers($id)
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen not found.', 'alert-type' => 'error']);

        $plan = KitchenPlan::where('KitchenPlanID', $id)
            ->where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->firstOrFail();

        $subscribers = Subscription::where('KitchenPlanID', $id)
            ->with(['customer.user', 'menuItems'])
            ->get();

        return view('admin.kitchen.plans.subscribers', compact('plan', 'subscribers'));
    }

    public function cancelSubscriptionByOwner($id)
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen not found.', 'alert-type' => 'error']);

        $subscription = Subscription::where('SubscriptionID', $id)
            ->whereHas('kitchenPlan', function ($q) use ($kitchen) {
                $q->where('KitchenOwnerID', $kitchen->KitchenOwnerID);
            })
            ->firstOrFail();

        $refund = $subscription->cancelAndRefund('Cancelled by Kitchen Owner');
        $subscription->update(['EndDate' => now()]);

        $msg = 'Subscription cancelled successfully.';
        if ($refund > 0) {
            $msg .= " Refunded " . number_format($refund, 2) . " to user wallet.";
        }

        return back()->with(['message' => $msg, 'alert-type' => 'success']);
    }

    public function KitchenLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // ─── PayMob Wallet Top Up ─────────────────────────────────────────────
    public function paymobWalletTopupWait(Request $request, PaymobService $paymob)
    {
        $amount = $request->amount;
        if (!$amount || $amount < 50) {
            return back()->with(['message' => 'Minimum top-up amount is 50 EGP.', 'alert-type' => 'error']);
        }

        $request->session()->put('paymob_kitchen_topup', [
            'amount' => $amount
        ]);
        $request->session()->put('paymob_order_type', 'topup');

        $token = $paymob->authenticate();
        if (!$token) {
            return back()->with(['message' => 'PayMob authentication failed.', 'alert-type' => 'error']);
        }

        $userId = Auth::user()->UserID;
        $orderId = $paymob->createOrder($token, (float) $amount, [], 'TOPUP_' . $userId . '_' . time());
        if (!$orderId) {
            return back()->with(['message' => 'PayMob order registration failed.', 'alert-type' => 'error']);
        }

        $user = Auth::user();
        $phoneRecord = $user->phone ?? ($user->phones ? $user->phones()->first() : null);
        $billingData = [
            'first_name'   => $user->FullName ?: 'Guest',
            'last_name'    => 'User',
            'email'        => $user->Email ?: 'guest@bitehub.com',
            'phone_number' => $phoneRecord ? $phoneRecord->PhoneNumber : '01000000000',
        ];

        $paymentKey = $paymob->getPaymentKey($token, $orderId, (float) $amount, $billingData);
        if (!$paymentKey) {
            return back()->with(['message' => 'PayMob payment key generation failed.', 'alert-type' => 'error']);
        }

        return redirect($paymob->getIframeUrl($paymentKey));
    }

    public function paymobTopupSuccess(Request $request)
    {
        $data = $request->session()->pull('paymob_kitchen_topup');
        if (!$data) {
            return redirect()->route('kitchen.dashboard')->with(['message' => 'Session expired. Please try again.', 'alert-type' => 'error']);
        }

        $user = Auth::user();
        if (!$user) {
             return redirect()->route('login');
        }

        try {
            $user->increment('Wallet_balance', $data['amount']);
            return redirect()->route('kitchen.dashboard')->with(['message' => 'PayMob Top-Up successful! 🎉 Wallet credited.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            return redirect()->route('kitchen.dashboard')->with(['message' => 'Payment failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        $userEmail = $user->Email;
        $userName  = $user->FullName;

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        try {
            Mail::to($userEmail)->send(new AccountDeletedMail($userName));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Kitchen account deletion email failed: ' . $e->getMessage());
        }

        return redirect('/')->with(['message' => 'Your kitchen account has been permanently deleted.', 'alert-type' => 'success']);
    }

    // ─── Refund Tracking ─────────────────────────────────────────────────────
    public function refunds()
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen)
            return back()->with(['message' => 'Kitchen profile not found.', 'alert-type' => 'error']);

        // Find approved refunds for this kitchen's orders
        $orderRefunds = RefundRequest::where('RefundableType', 'Order')
            ->where('Status', 'Approved')
            ->whereHas('order', function($q) use ($kitchen) {
                $q->where('KitchenOwnerID', $kitchen->KitchenOwnerID);
            })->with('customer.user')->get();

        // Find approved refunds for this kitchen's subscriptions
        $subRefunds = RefundRequest::where('RefundableType', 'Subscription')
            ->where('Status', 'Approved')
            ->whereHas('subscription', function($q) use ($kitchen) {
                $q->where('KitchenOwnerID', $kitchen->KitchenOwnerID);
            })->with('customer.user')->get();

        $refunds = $orderRefunds->concat($subRefunds)->sortByDesc('updated_at');

        return view('admin.kitchen.refunds', compact('refunds'));
    }

    // ─── Promo Codes ─────────────────────────────────────────────────────────
    public function promoCodes()
    {
        $kitchen = $this->myKitchen();
        if (!$kitchen) return back()->with(['message' => 'Profile not found.', 'alert-type' => 'error']);

        $promoCodes = \App\Models\PromoCode::where('KitchenOwnerID', $kitchen->KitchenOwnerID)
            ->latest()->paginate(20);
        return view('admin.kitchen.promo_codes.index', compact('promoCodes'));
    }

    public function storePromoCode(Request $request)
    {
        $kitchen = $this->myKitchen();
        $request->validate([
            'Code'           => 'required|string|max:50|unique:promo_codes,Code',
            'Type'           => 'required|in:Percentage,Fixed',
            'Value'          => 'required|numeric|min:0' . ($request->Type === 'Percentage' ? '|max:100' : ''),
            'MinOrderAmount' => 'required|numeric|min:0',
            'MaxUses'        => 'nullable|integer|min:1',
            'ExpiryDate'     => 'nullable|date|after:today',
        ]);

        \App\Models\PromoCode::create([
            'Code'           => strtoupper(trim($request->Code)),
            'Type'           => $request->Type,
            'Value'          => $request->Value,
            'MinOrderAmount' => $request->MinOrderAmount,
            'MaxUses'        => $request->MaxUses,
            'ExpiryDate'     => $request->ExpiryDate,
            'IsActive'       => true,
            'UsedCount'      => 0,
            'KitchenOwnerID' => $kitchen->KitchenOwnerID,
            'CreatorRole'    => 'KitchenOwner',
        ]);

        return back()->with(['message' => 'Promo code created successfully!', 'alert-type' => 'success']);
    }

    public function updatePromoCode(Request $request, $id)
    {
        $kitchen = $this->myKitchen();
        $promo = \App\Models\PromoCode::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->findOrFail($id);
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
        $kitchen = $this->myKitchen();
        $promo = \App\Models\PromoCode::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->findOrFail($id);
        $promo->update(['IsActive' => !$promo->IsActive]);
        $status = $promo->IsActive ? 'activated' : 'deactivated';
        return back()->with(['message' => "Promo code {$status}!", 'alert-type' => 'success']);
    }

    public function deletePromoCode($id)
    {
        $kitchen = $this->myKitchen();
        \App\Models\PromoCode::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->findOrFail($id)->delete();
        return back()->with(['message' => 'Promo code deleted.', 'alert-type' => 'success']);
    }

    public function announcePromoCode($id)
    {
        $kitchen = $this->myKitchen();
        $promo = \App\Models\PromoCode::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->findOrFail($id);

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
