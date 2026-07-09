<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Caterer;
use App\Models\Category;
use App\Models\CateringRequest;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\MenuOrderItem;
use App\Models\Review;
use App\Models\Advertising;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountDeletedMail;
use App\Mail\PromoCodeAnnouncement;

class CatererController extends Controller
{
    private function myCaterer()
    {
        return Caterer::where('UserID', Auth::user()->UserID)->first();
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────
    public function CatererDashboard()
    {
        $caterer = $this->myCaterer();
        $totalRequests = $openRequests = $completedRequests = $cancelledRequests = 0;
        $openSupportTickets = $pendingRefunds = 0;
        $monthlySalesData = array_fill(0, 12, 0);
        $recentTickets = collect();
        $recentRefunds = collect();

        if ($caterer) {
            $totalRequests     = CateringRequest::where('CatererID', $caterer->CatererID)->count();
            $openRequests      = CateringRequest::where('CatererID', $caterer->CatererID)->where('Status', 'Pending')->count();
            $completedRequests = CateringRequest::where('CatererID', $caterer->CatererID)->where('Status', 'Completed')->count();
            $cancelledRequests = CateringRequest::where('CatererID', $caterer->CatererID)->whereIn('Status', ['Cancelled','Rejected'])->count();
            
            // Get request count for each month in the current year
            $monthlySales = CateringRequest::select(\Illuminate\Support\Facades\DB::raw('MONTH(CreatedAt) as month'), \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->where('CatererID', $caterer->CatererID)
                ->whereYear('CreatedAt', date('Y'))
                ->groupBy('month')
                ->pluck('count', 'month')->toArray();
                
            for ($i = 1; $i <= 12; $i++) {
                $monthlySalesData[$i-1] = $monthlySales[$i] ?? 0;
            }

            // Support Tickets for this caterer
            $openSupportTickets = \App\Models\SupportTicket::where('UserID', Auth::id())
                ->whereIn('Status', ['Open', 'InProgress'])
                ->count();
            $recentTickets = \App\Models\SupportTicket::where('UserID', Auth::id())
                ->orderByDesc('created_at')->limit(5)->get();

            // Pending Refunds for this caterer
            $pendingRefunds = \App\Models\RefundRequest::where('Status', 'Pending')
                ->where('RefundableType', 'Order')
                ->whereHas('order', function($oq) use ($caterer) { $oq->where('CatererID', $caterer->CatererID); })
                ->count();
            $recentRefunds = \App\Models\RefundRequest::where('RefundableType', 'Order')
                ->whereHas('order', function($oq) use ($caterer) { $oq->where('CatererID', $caterer->CatererID); })
                ->orderByDesc('updated_at')->limit(5)->get();
        }
        return view('admin.caterer.index', compact('caterer', 'totalRequests', 'openRequests', 'completedRequests', 'cancelledRequests', 'monthlySalesData', 'openSupportTickets', 'pendingRefunds', 'recentTickets', 'recentRefunds'));
    }

    public function CatererKPI(Request $request)
    {
        $user = Auth::user();
        $caterer = $this->myCaterer();
        if (!$caterer) return redirect()->route('caterer.dashboard');

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

        $applyFilter = function($query, $column = 'CreatedAt') use ($startDate, $endDate) {
            if ($startDate) return $query->whereBetween($column, [$startDate, $endDate]);
            return $query;
        };

        // 1. Core Revenue (Orders)
        $orderQuery = Order::join('menu_order_items', 'orders.OrderID', '=', 'menu_order_items.OrderID')
            ->join('menu_items', 'menu_order_items.MenuItemID', '=', 'menu_items.MenuItemID')
            ->where('menu_items.CatererID', $caterer->CatererID)
            ->select('orders.*')
            ->distinct();

        $totalRevenue = (clone $orderQuery)->where('orders.OrderStatus', 'Delivered')->sum('orders.TotalPrice');
        $todayRevenue = (clone $orderQuery)->whereDate('orders.CreatedAt', today())->where('orders.OrderStatus', 'Delivered')->sum('orders.TotalPrice');
        $monthlyRevenueTotal = (clone $orderQuery)->whereMonth('orders.CreatedAt', date('m'))->whereYear('orders.CreatedAt', date('Y'))->where('orders.OrderStatus', 'Delivered')->sum('orders.TotalPrice');

        // 2. Orders Logic
        $totalOrdersCount = $applyFilter(clone $orderQuery, 'orders.CreatedAt')->count();
        $pendingOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Pending')->count();
        $completedOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Delivered')->count();
        $deliveredOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Delivered')->count();
        $aov = $deliveredOrdersCount > 0 ? $totalRevenue / $deliveredOrdersCount : 0;

        // 3. Catering Specific
        $cateringQuery = CateringRequest::where('CatererID', $caterer->CatererID);
        $totalCateringRequests = (clone $cateringQuery)->count();
        $approvedCateringRequests = (clone $cateringQuery)->where('Status', 'Approved')->count();

        // 4. Products & Ads
        $totalMenuItemsCount = MenuItem::where('CatererID', $caterer->CatererID)->count();
        $activeAdsCount = Advertising::where('CatererID', $caterer->CatererID)->where('Status', 'Active')->count();

        // 5. Satisfaction
        $avgRating = Review::where('CatererID', $caterer->CatererID)->avg('Rating') ?? 0;
        $totalReviewsCount = Review::where('CatererID', $caterer->CatererID)->count();
        $recentReviews = Review::where('CatererID', $caterer->CatererID)->with('customer.user')->orderByDesc('ReviewID')->limit(3)->get();

        // 6. Cancellation Rate
        $cancelledOrdersCount = (clone $orderQuery)->where('orders.OrderStatus', 'Cancelled')->count();
        $totalOrdersEver = (clone $orderQuery)->count();
        $cancellationRate = $totalOrdersEver > 0 ? ($cancelledOrdersCount / $totalOrdersEver) * 100 : 0;

        // Chart Data
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

        $topItems = MenuItem::where('CatererID', $caterer->CatererID)
            ->withCount(['orders as sales_count'])
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get();

        return view('admin.caterer.kpi', compact(
            'caterer', 'range', 'totalRevenue', 'todayRevenue', 'monthlyRevenueTotal',
            'totalOrdersCount', 'pendingOrdersCount', 'completedOrdersCount', 'aov',
            'totalCateringRequests', 'approvedCateringRequests', 'totalMenuItemsCount',
            'activeAdsCount', 'avgRating', 'totalReviewsCount', 'recentReviews',
            'cancellationRate', 'chartData', 'chartLabels', 'topItems'
        ));
    }

    public function updateHours(Request $request)
    {
        $request->validate([
            'opening_time' => 'required',
            'closing_time' => 'required',
        ]);

        $caterer = $this->myCaterer();
        if (!$caterer) return back()->with(['message' => 'Caterer profile not found.', 'alert-type' => 'error']);

        $caterer->update([
            'OpeningTime' => $request->opening_time,
            'ClosingTime' => $request->closing_time,
        ]);

        return back()->with(['message' => 'Working hours updated successfully.', 'alert-type' => 'success']);
    }

    // ─── Catering Requests ────────────────────────────────────────────────────
    public function myRequests(Request $request)
    {
        $caterer  = $this->myCaterer();
        $requests = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        if ($caterer) {
            $query = CateringRequest::where('catering_requests.CatererID', $caterer->CatererID)
                ->leftJoin('customers', 'catering_requests.CustomerID', '=', 'customers.CustomerID')
                ->leftJoin('users', 'customers.UserID', '=', 'users.UserID')
                ->select('catering_requests.*', 'users.FullName as CustomerName');
            if ($request->status) $query->where('catering_requests.Status', $request->status);
            $requests = $query->orderByDesc('catering_requests.CreatedAt')->paginate(15);
        }
        return view('admin.caterer.requests.index', compact('requests'));
    }

    public function updateRequest(Request $request, $id)
    {
        $caterer = $this->myCaterer();
        $r = CateringRequest::where('RequestID', $id)->where('CatererID', $caterer->CatererID)->firstOrFail();
        $r->update(['Status' => $request->status]);
        return back()->with(['message' => 'Request updated.', 'alert-type' => 'success']);
    }

    // ─── Menu Items (CRUD) ────────────────────────────────────────────────────
    public function menuItems()
    {
        $caterer    = $this->myCaterer();
        $items      = $caterer
            ? \App\Models\MenuItem::where('CatererID', $caterer->CatererID)
                ->orderByDesc('MenuItemID')->paginate(15)
            : collect();
        $categories = \App\Models\Category::orderBy('Name')->get();
        $tags = \App\Models\Tag::all()->groupBy('category');
        return view('admin.caterer.menu.index', compact('items', 'categories', 'caterer', 'tags'));
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'ItemName'  => 'required|string|max:255',
            'ItemPrice' => 'required|numeric|min:0',
            'images.*'  => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $caterer = $this->myCaterer();
        if (!$caterer) return back()->with(['message' => 'Caterer profile not found.', 'alert-type' => 'error']);

        // Resolve category
        $categoryID = $request->CategoryID ?: null;
        if (!empty(trim($request->NewCategoryName ?? ''))) {
            $newCat = \App\Models\Category::firstOrCreate(['Name' => trim($request->NewCategoryName)]);
            $categoryID = $newCat->CategoryID;
        }

        $item = \App\Models\MenuItem::create([
            'CatererID'     => $caterer->CatererID,
            'CategoryID'    => $categoryID,
            'ItemName'      => $request->ItemName,
            'Description'   => $request->Description,
            'Ingredients'   => $request->Ingredients,
            'PortionSize'   => $request->PortionSize,
            'Calories'      => $request->Calories,
            'Protein'       => $request->Protein,
            'Carbs'         => $request->Carbs,
            'Fats'          => $request->Fats,
            'PrepTime'      => $request->PrepTime,
            'ItemPrice'     => $request->ItemPrice,
            'DiscountPrice' => rtrim(trim($request->DiscountPrice), '.') === '' ? null : $request->DiscountPrice,
            'Status'        => 'Available',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $filename = time() . '_' . rand() . '.' . $img->getClientOriginalExtension();
                $img->move(public_path('upload/item_images'), $filename);
                \App\Models\ItemImage::create(['MenuItemID' => $item->MenuItemID, 'Image' => $filename]);
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
            'ItemName'  => 'required',
            'ItemPrice' => 'required|numeric',
            'images.*'  => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $caterer = $this->myCaterer();
        $item = \App\Models\MenuItem::where('MenuItemID', $id)->where('CatererID', $caterer->CatererID)->firstOrFail();

        $categoryID = $request->CategoryID ?: null;
        if (!empty(trim($request->NewCategoryName ?? ''))) {
            $newCat = \App\Models\Category::firstOrCreate(['Name' => trim($request->NewCategoryName)]);
            $categoryID = $newCat->CategoryID;
        }

        $item->update([
            'ItemName'      => $request->ItemName,
            'Description'   => $request->Description,
            'Ingredients'   => $request->Ingredients,
            'PortionSize'   => $request->PortionSize,
            'Calories'      => $request->Calories,
            'Protein'       => $request->Protein,
            'Carbs'         => $request->Carbs,
            'Fats'          => $request->Fats,
            'PrepTime'      => $request->PrepTime,
            'ItemPrice'     => $request->ItemPrice,
            'DiscountPrice' => rtrim(trim($request->DiscountPrice), '.') === '' ? null : $request->DiscountPrice,
            'CategoryID'    => $categoryID,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $filename = time() . '_' . rand() . '.' . $img->getClientOriginalExtension();
                $img->move(public_path('upload/item_images'), $filename);
                \App\Models\ItemImage::create(['MenuItemID' => $item->MenuItemID, 'Image' => $filename]);
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
        $caterer = $this->myCaterer();
        $item = \App\Models\MenuItem::where('MenuItemID', $id)->where('CatererID', $caterer->CatererID)->firstOrFail();
        $item->update(['Status' => $item->Status === 'Available' ? 'Unavailable' : 'Available']);
        return back()->with(['message' => 'Item status updated.', 'alert-type' => 'success']);
    }

    public function deleteItem($id)
    {
        $caterer = $this->myCaterer();
        \App\Models\MenuItem::where('MenuItemID', $id)->where('CatererID', $caterer->CatererID)->firstOrFail()->delete();
        return back()->with(['message' => 'Item deleted.', 'alert-type' => 'success']);
    }

    // ─── Orders ───────────────────────────────────────────────────────────────
    public function catererOrders(Request $request)
    {
        $caterer = $this->myCaterer();
        $orders  = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        if ($caterer) {
            $menuItemIds = \App\Models\MenuItem::where('CatererID', $caterer->CatererID)->pluck('MenuItemID');
            
            // Traditional items
            $orderIdsFromItems = \App\Models\MenuOrderItem::whereIn('MenuItemID', $menuItemIds)->pluck('OrderID')->unique();
            
            // Direct chat-negotiated orders
            $orderIdsFromChats = \App\Models\LiveChat::where('ReceiverID', Auth::user()->UserID)
                ->whereNotNull('OrderID')
                ->pluck('OrderID')
                ->unique();

            $allOrderIds = $orderIdsFromItems->merge($orderIdsFromChats)->unique();

            if ($allOrderIds->isNotEmpty()) {
                $query = \App\Models\Order::whereIn('orders.OrderID', $allOrderIds)
                    ->leftJoin('customers', 'orders.CustomerID', '=', 'customers.CustomerID')
                    ->leftJoin('users', 'customers.UserID', '=', 'users.UserID')
                    ->select('orders.*', 'users.FullName as CustomerName');
                if ($request->status) $query->where('orders.OrderStatus', $request->status);
                $orders = $query->orderByDesc('orders.OrderID')->paginate(15);
            }
        }
        return view('admin.caterer.orders.index', compact('orders'));
    }

    public function updateCatererOrderStatus(Request $request, $id)
    {
        $caterer     = $this->myCaterer();
        $menuItemIds = \App\Models\MenuItem::where('CatererID', $caterer->CatererID)->pluck('MenuItemID');
        $orderIds    = \App\Models\MenuOrderItem::whereIn('MenuItemID', $menuItemIds)->pluck('OrderID')->unique();
        abort_unless($orderIds->contains((int)$id), 403);

        $order = \App\Models\Order::findOrFail($id);

        // Restrict Caterers to preparation-phase statuses
        $allowedStatuses = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Cancelled'];
        if (!in_array($request->status, $allowedStatuses)) {
            return back()->with(['message' => 'Status upgrade restricted. Only agents or admins can mark as delivering/delivered.', 'alert-type' => 'error']);
        }

        $order->update(['OrderStatus' => $request->status]);

        return back()->with(['message' => 'Order status updated.', 'alert-type' => 'success']);
    }



    // ─── Advertisements ───────────────────────────────────────────────────────
    public function ads()
    {
        $caterer = $this->myCaterer();
        if (!$caterer) return back()->with(['message' => 'Caterer profile not found.', 'alert-type' => 'error']);

        $ads = \App\Models\Advertising::where('CatererID', $caterer->CatererID)
            ->orderByDesc('AdvertisingID')
            ->paginate(15);
        return view('admin.caterer.ads.index', compact('ads', 'caterer'));
    }

    public function storeAd(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $caterer = $this->myCaterer();
        if (!$caterer) return back()->with(['message' => 'Caterer profile not found.', 'alert-type' => 'error']);

        $pricePerDay = 50.00;
        $start = \Carbon\Carbon::parse($request->start_date);
        $end   = \Carbon\Carbon::parse($request->end_date);
        $days  = $start->diffInDays($end);
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

        \App\Models\Advertising::create([
            'CatererID'       => $caterer->CatererID,
            'Title'           => $request->title,
            'Description'     => $request->description,
            'StartDate'       => $request->start_date,
            'EndDate'         => $request->end_date,
            'PricePerDay'     => $pricePerDay,
            'TotalAmount'     => $total,
            'PaidAt'          => now(),
            'Status'          => 'Pending',
            'BackgroundImage' => $bgImage,
        ]);
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
        return view('admin.caterer.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['CategoryName' => 'required|string|max:255|unique:categories,Name']);
        Category::create([
            'Name'        => trim($request->CategoryName),
            'Description' => $request->Description,
        ]);
        return back()->with(['message' => 'Category added successfully.', 'alert-type' => 'success']);
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate(['CategoryName' => 'required|string|max:255|unique:categories,Name,' . $id . ',CategoryID']);
        Category::findOrFail($id)->update([
            'Name'        => trim($request->CategoryName),
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
    public function CatererProfile()
    {
        return view('admin.caterer.profile', ['profileData' => Auth::user()]);
    }

    public function store(Request $request)
    {
        $data = User::find(Auth::user()->UserID);
        $data->FullName = $request->name ?? $data->FullName;
        if ($request->hasFile('photo')) {
            $f = $request->file('photo');
            $filename = rand().'.'.$f->getClientOriginalExtension();
            $f->move(public_path('upload/admin_images'), $filename);
            $data->Image = $filename;
        }
        $data->save();
        
        if ($request->has('location')) {
            $caterer = \App\Models\Caterer::where('UserID', \Illuminate\Support\Facades\Auth::user()->UserID)->first();
            if ($caterer) {
                $caterer->Location = $request->location;
                if ($request->has('latitude') && $request->has('longitude')) {
                    $caterer->Latitude = $request->latitude;
                    $caterer->Longitude = $request->longitude;
                }
                $caterer->OpeningTime = $request->opening_time;
                $caterer->ClosingTime = $request->closing_time;
                $caterer->save();
            }
        }
        
        return back()->with(['message' => 'Profile Updated.', 'alert-type' => 'success']);
    }

    public function CatererChangePassword()
    {
        return view('admin.caterer.change_password', ['profileData' => Auth::user()]);
    }

    public function CatererUpdatePassword(Request $request)
    {
        $request->validate(['old_password' => 'required', 'new_password' => 'required|confirmed']);
        if (!Hash::check($request->old_password, Auth::user()->Password))
            return back()->with(['message' => 'Current password does not match.', 'alert-type' => 'error']);
        User::where('UserID', Auth::user()->UserID)->update(['Password' => Hash::make($request->new_password)]);
        return back()->with(['message' => 'Password changed.', 'alert-type' => 'success']);
    }

    public function CatererLogout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
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
            \Illuminate\Support\Facades\Log::warning('Caterer account deletion email failed: ' . $e->getMessage());
        }

        return redirect('/')->with(['message' => 'Your caterer account has been permanently deleted.', 'alert-type' => 'success']);
    }

    // ─── Refund Tracking ─────────────────────────────────────────────────────
    public function refunds()
    {
        $caterer = $this->myCaterer();
        if (!$caterer)
            return back()->with(['message' => 'Caterer profile not found.', 'alert-type' => 'error']);

        // Find approved refunds for this caterer's orders
        $orderRefunds = \App\Models\RefundRequest::where('RefundableType', 'Order')
            ->where('Status', 'Approved')
            ->whereHas('order', function($q) use ($caterer) {
                $q->where('CatererID', $caterer->CatererID);
            })->with('customer.user')->get();

        $refunds = $orderRefunds->sortByDesc('updated_at');

        return view('admin.caterer.refunds', compact('refunds'));
    }

    // ─── Promo Codes ─────────────────────────────────────────────────────────
    public function promoCodes()
    {
        $caterer = $this->myCaterer();
        if (!$caterer) return back()->with(['message' => 'Profile not found.', 'alert-type' => 'error']);

        $promoCodes = \App\Models\PromoCode::where('CatererID', $caterer->CatererID)
            ->latest()->paginate(20);
        return view('admin.caterer.promo_codes.index', compact('promoCodes'));
    }

    public function storePromoCode(Request $request)
    {
        $caterer = $this->myCaterer();
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
            'CatererID'      => $caterer->CatererID,
            'CreatorRole'    => 'Caterer',
        ]);

        return back()->with(['message' => 'Promo code created successfully!', 'alert-type' => 'success']);
    }

    public function updatePromoCode(Request $request, $id)
    {
        $caterer = $this->myCaterer();
        $promo = \App\Models\PromoCode::where('CatererID', $caterer->CatererID)->findOrFail($id);
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
        $caterer = $this->myCaterer();
        $promo = \App\Models\PromoCode::where('CatererID', $caterer->CatererID)->findOrFail($id);
        $promo->update(['IsActive' => !$promo->IsActive]);
        $status = $promo->IsActive ? 'activated' : 'deactivated';
        return back()->with(['message' => "Promo code {$status}!", 'alert-type' => 'success']);
    }

    public function deletePromoCode($id)
    {
        $caterer = $this->myCaterer();
        \App\Models\PromoCode::where('CatererID', $caterer->CatererID)->findOrFail($id)->delete();
        return back()->with(['message' => 'Promo code deleted.', 'alert-type' => 'success']);
    }

    public function announcePromoCode($id)
    {
        $caterer = $this->myCaterer();
        $promo = \App\Models\PromoCode::where('CatererID', $caterer->CatererID)->findOrFail($id);

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
