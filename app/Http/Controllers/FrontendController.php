<?php

namespace App\Http\Controllers;

use App\Models\KitchenOwner;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Caterer;
use App\Models\Order;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\CateringRequest;
use App\Models\Subscription;
use App\Models\KitchenPlan;
use App\Models\Advertising;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use App\Models\Payment;
use App\Services\PaymobService;
use App\Models\RefundRequest;
use Carbon\Carbon;

class FrontendController extends Controller
{
    public function home()
    {
        $activeAds = Advertising::with(['kitchenOwner', 'caterer'])
            ->whereIn('Status', ['Active', 'Approved'])
            ->whereDate('StartDate', '<=', now())
            ->whereDate('EndDate', '>=', now())
            ->orderByDesc('AdvertisingID')
            ->get();

        $kitchenAds = $activeAds->whereNotNull('KitchenOwnerID');
        $catererAds = $activeAds->whereNotNull('CatererID');

        $sponsoredKitchenIds = $kitchenAds->pluck('KitchenOwnerID')->unique();
        $sponsoredCatererIds = $catererAds->pluck('CatererID')->unique();

        $kitchenInClause = $sponsoredKitchenIds->isNotEmpty() ? $sponsoredKitchenIds->implode(',') : '0';
        $kitchens = KitchenOwner::join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
            ->where('kitchen_owners.VerifyStatus', 'Verified')
            ->where('kitchen_owners.Status', 'Active')
            ->select(
                'kitchen_owners.*',
                'users.FullName',
                'users.Image',
                DB::raw("CASE WHEN kitchen_owners.KitchenOwnerID IN ($kitchenInClause) THEN 0 ELSE 1 END as sponsorOrder")
            )
            ->orderBy('sponsorOrder')
            ->get();

        $catererInClause = $sponsoredCatererIds->isNotEmpty() ? $sponsoredCatererIds->implode(',') : '0';
        $caterers = Caterer::join('users', 'caterers.UserID', '=', 'users.UserID')
            ->where('caterers.IsActive', 1)
            ->select(
                'caterers.*',
                'users.FullName',
                'users.Image',
                DB::raw("CASE WHEN caterers.CatererID IN ($catererInClause) THEN 0 ELSE 1 END as sponsorOrder")
            )
            ->orderBy('sponsorOrder')
            ->get();

        $categories = Category::where('Status', 'Active')->get();

        $popular = MenuItem::with('images')
            ->leftJoin('categories', 'menu_items.CategoryID', '=', 'categories.CategoryID')
            ->leftJoin('kitchen_owners', 'menu_items.KitchenOwnerID', '=', 'kitchen_owners.KitchenOwnerID')
            ->where('menu_items.Status', 'Available')
            ->whereNotNull('menu_items.KitchenOwnerID')
            ->select('menu_items.*', 'categories.Name as CatName', 'kitchen_owners.KitchenName')
            ->orderBy('menu_items.MenuItemID')
            ->limit(8)
            ->get();

        // Recent orders for logged-in customers (for the "Order Again" slider)
        $recentCustomerOrders = collect();
        $user = Auth::user();
        if ($user && $user->Role === 'Customer' && $user->customer) {
            $recentCustomerOrders = Order::where('CustomerID', $user->customer->CustomerID)
                ->with(['menuItems.images'])
                ->orderByDesc('OrderID')
                ->limit(5)
                ->get();
        }

        return view('frontend.home', compact('kitchens', 'caterers', 'categories', 'popular', 'kitchenAds', 'catererAds', 'sponsoredKitchenIds', 'sponsoredCatererIds', 'recentCustomerOrders'));
    }

    public function customerDashboard()
    {
        $user = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();
        $walletBalance = $customer?->WalletBalance ?? 0;
        $loyaltyPoints = 0;
        $recentOrders = collect();
        $mySubscriptions = collect();
        $pendingSubscriptions = collect();

        if ($customer) {
            $loyaltyPoints = LoyaltyTransaction::where('CustomerID', $customer->CustomerID)
                ->selectRaw('SUM(CASE WHEN Type="Earned" OR Type="Bonus" OR Type="Referral" THEN Points ELSE -Points END) as total')
                ->value('total') ?? 0;
            $recentOrders = Order::where('CustomerID', $customer->CustomerID)
                ->with(['payment', 'supportTickets'])
                ->orderByDesc('CreatedAt')->limit(10)->get();
            $pendingSubscriptions = Subscription::where('CustomerID', $customer->CustomerID)
                ->whereIn('Status', ['PendingApproval', 'AwaitingPayment'])
                ->with(['menuItems', 'payments'])->get();
            $mySubscriptions = Subscription::where('CustomerID', $customer->CustomerID)
                ->whereIn('Status', ['Active', 'Expired', 'Cancelled'])
                ->with(['menuItems', 'payments'])
                ->orderByDesc('SubscriptionID')->get();
        }

        return view('frontend.customer_dashboard', compact(
            'walletBalance',
            'loyaltyPoints',
            'recentOrders',
            'mySubscriptions',
            'pendingSubscriptions'
        ));
    }


    public function myAddresses()
    {
        return redirect()->to(route('frontend.profile') . '#addresses');
    }

    public function storeAddress(Request $request)
    {
        $request->validate(['address' => 'required|string|max:255', 'latitude' => 'nullable|numeric', 'longitude' => 'nullable|numeric']);
        $user = Auth::user();
        $isFirst = \App\Models\UserAddress::where('UserID', $user->UserID)->count() === 0;
        \App\Models\UserAddress::create(['UserID' => $user->UserID, 'Address' => $request->address, 'Latitude' => $request->latitude, 'Longitude' => $request->longitude, 'IsPrimary' => $isFirst]);
        return back()->with(['message' => 'Address added successfully!', 'alert-type' => 'success']);
    }

    public function setPrimaryAddress($id)
    {
        $user = Auth::user();
        $address = \App\Models\UserAddress::where('AddressID', $id)->where('UserID', $user->UserID)->firstOrFail();
        \App\Models\UserAddress::where('UserID', $user->UserID)->update(['IsPrimary' => false]);
        $address->update(['IsPrimary' => true]);
        return back()->with(['message' => 'Primary address updated.', 'alert-type' => 'success']);
    }

    public function deleteAddress($id)
    {
        $user = Auth::user();
        $address = \App\Models\UserAddress::where('AddressID', $id)->where('UserID', $user->UserID)->firstOrFail();

        $address->delete();

        return back()->with(['message' => 'Address deleted.', 'alert-type' => 'success']);
    }

    public function browse(Request $request)
    {
        $search = $request->get('search');
        $area = $request->get('area', 'nearby');
        $rating = $request->get('rating');
        $user = Auth::user();
        $nearby = $request->get('nearby');

        // If area selection is 'nearby', treat it as a proximity filter
        if ($area === 'nearby') {
            $nearby = '1';
        }

        // Active kitchen ads
        $kitchenAds = Advertising::with('kitchenOwner')
            ->whereIn('Status', ['Active', 'Approved'])
            ->whereNotNull('KitchenOwnerID')
            ->whereDate('StartDate', '<=', now())
            ->whereDate('EndDate', '>=', now())
            ->orderByDesc('AdvertisingID')
            ->get();

        // Kitchens Query
        $kitchensQuery = KitchenOwner::join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
            ->where('kitchen_owners.Status', 'Active')
            ->withCount([
                'menuItems as preparing_orders_count' => function ($q) {
                    $q->whereHas('orders', function ($o) {
                        $o->where('OrderStatus', 'Preparing');
                    });
                }
            ])
            ->select('kitchen_owners.*', 'users.FullName', 'users.Image');

        if ($search) {
            $kitchensQuery->where('kitchen_owners.KitchenName', 'LIKE', "%{$search}%");
        }

        // 1. Area filter
        if ($area && $area !== 'nearby') {
            $kitchensQuery->where('kitchen_owners.Location', 'LIKE', "%{$area}%");
        }

        $kitchensQuery->withAvg('reviews as avg_rating', 'Rating');

        if ($rating) {
            $kitchensQuery->having('avg_rating', '>=', $rating);
        }

        $kitchens = $kitchensQuery->get();

        $primaryAddress = null;
        $noAddress = false;
        if ($user) {
            $primaryAddress = \App\Models\UserAddress::where('UserID', $user->UserID)->where('IsPrimary', true)->first();
            if ($primaryAddress && $primaryAddress->Latitude && $primaryAddress->Longitude) {
                $lat1 = deg2rad($primaryAddress->Latitude);
                $lon1 = deg2rad($primaryAddress->Longitude);

                foreach ($kitchens as $k) {
                    if ($k->Latitude && $k->Longitude) {
                        $lat2 = deg2rad($k->Latitude);
                        $lon2 = deg2rad($k->Longitude);
                        $a = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2;
                        $k->distance = round(6371 * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
                    }
                }

                if ($nearby == '1') {
                    $kitchens = $kitchens->filter(function ($k) {
                        return ($k->distance ?? 999) <= 20;
                    });
                }
            } else {
                if ($nearby == '1') $noAddress = true;
            }
        } else {
            if ($nearby == '1') $noAddress = true;
        }

        return view('frontend.browse', compact('kitchens', 'kitchenAds', 'nearby', 'area', 'noAddress'));
    }

    public function kitchen($id)
    {
        $kitchen = KitchenOwner::join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
            ->where('kitchen_owners.KitchenOwnerID', $id)
            ->select('kitchen_owners.*', 'users.FullName', 'users.Image', 'users.Email')
            ->first();

        if (!$kitchen)
            return redirect()->route('frontend.browse');

        // Calculate live rating and review count
        $ratingData = \App\Models\Review::where('KitchenOwnerID', $id)
            ->selectRaw('AVG(Rating) as avg_rating, COUNT(*) as rev_count')
            ->first();

        $kitchen->average_rating = round($ratingData->avg_rating ?? 4.5, 1);
        $kitchen->review_count = $ratingData->rev_count ?? 0;

        $menuItems = MenuItem::with('images')->leftJoin('categories', 'menu_items.CategoryID', '=', 'categories.CategoryID')
            ->where('menu_items.KitchenOwnerID', $id)
            ->where('menu_items.Status', 'Available')
            ->select('menu_items.*', 'categories.Name as CatName')
            ->get();

        // Fetch active plans for this kitchen
        $plans = KitchenPlan::where('KitchenOwnerID', $id)->where('Status', 'Active')->get();

        return view('frontend.kitchen', compact('kitchen', 'menuItems', 'plans'));
    }

    public function caterer($id)
    {
        $caterer = Caterer::join('users', 'caterers.UserID', '=', 'users.UserID')
            ->where('caterers.CatererID', $id)
            ->select('caterers.*', 'users.FullName', 'users.Image', 'users.Email')
            ->first();

        if (!$caterer)
            return redirect()->route('frontend.browse');

        // Calculate live rating and review count
        $ratingData = \App\Models\Review::where('CatererID', $id)
            ->selectRaw('AVG(Rating) as avg_rating, COUNT(*) as rev_count')
            ->first();

        $caterer->average_rating = round($ratingData->avg_rating ?? 4.5, 1);
        $caterer->review_count = $ratingData->rev_count ?? 0;

        $menuItems = MenuItem::with('images')->leftJoin('categories', 'menu_items.CategoryID', '=', 'categories.CategoryID')
            ->where('menu_items.CatererID', $id)
            ->where('menu_items.Status', 'Available')
            ->select('menu_items.*', 'categories.Name as CatName')
            ->get();

        return view('frontend.caterer', compact('caterer', 'menuItems'));
    }

    public function caterers(Request $request)
    {
        $search = $request->get('search');

        $caterersQuery = Caterer::join('users', 'caterers.UserID', '=', 'users.UserID')
            ->where('caterers.IsActive', 1)
            ->select('caterers.*', 'users.FullName', 'users.Image');

        if ($search) {
            $caterersQuery->where('users.FullName', 'LIKE', "%{$search}%");
        }

        $user = Auth::user();
        $nearbyParam = $request->get('nearby');
        $area = $request->get('area', 'nearby');
        $rating = $request->get('rating');

        // Support 'nearby' as an area selection
        if ($area === 'nearby') {
            $nearbyParam = '1';
        }

        // Apply area filter if not nearby
        if ($area && $area !== 'nearby') {
            $caterersQuery->where('caterers.Location', 'LIKE', "%{$area}%");
        }

        $caterersQuery->withAvg('reviews as avg_rating', 'Rating');

        if ($rating) {
            $caterersQuery->having('avg_rating', '>=', $rating);
        }

        $caterers = $caterersQuery->get();

        $isProximityFiltered = false;
        $noAddress = false;

        // Proximity handling & Distance Attachment
        if ($user) {
            $primaryAddress = \App\Models\UserAddress::where('UserID', $user->UserID)->where('IsPrimary', true)->first();
            if ($primaryAddress && $primaryAddress->Latitude && $primaryAddress->Longitude) {
                $lat1 = deg2rad($primaryAddress->Latitude);
                $lon1 = deg2rad($primaryAddress->Longitude);

                foreach ($caterers as $c) {
                    if ($c->Latitude && $c->Longitude) {
                        $lat2 = deg2rad($c->Latitude);
                        $lon2 = deg2rad($c->Longitude);
                        $a = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2;
                        $c->distance = round(6371 * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
                    }
                }

                // If 'Near Me' is ON, filter by 20km
                if ($nearbyParam == '1') {
                    $caterers = $caterers->filter(function ($c) {
                        return ($c->distance ?? 999) <= 20;
                    });
                    $isProximityFiltered = true;
                }
            } else {
                if ($nearbyParam == '1') $noAddress = true;
            }
        } else {
            if ($nearbyParam == '1') $noAddress = true;
        }

        // Active caterer ads
        $catererAds = Advertising::with(['caterer.user'])
            ->whereIn('Status', ['Active', 'Approved'])
            ->whereNotNull('CatererID')
            ->whereDate('StartDate', '<=', now())
            ->whereDate('EndDate', '>=', now())
            ->orderByDesc('AdvertisingID')
            ->get();

        return view('frontend.caterers', compact('caterers', 'catererAds', 'nearbyParam', 'area', 'isProximityFiltered', 'noAddress'));
    }

    public function menu(Request $request)
    {
        $catFilter = intval($request->get('cat', 0));
        $tagsFilter = $request->get('tags', []);
        if (!is_array($tagsFilter)) {
            $tagsFilter = explode(',', $tagsFilter);
        }

        // ─── Proximity filter & Distance Attachment ──────────────────────
        $nearbyKitchenIds = null;
        $kitchenDistances = [];
        $search = $request->get('search');

        // ─── Filter Master List Generation (Area + Proximity) ────────────
        $kitchenIds = null; // null means no location-based filter
        $kitchenDistances = [];
        $user = Auth::user();
        $nearbyParam = $request->get('nearby');
        $area = $request->get('area', 'nearby');

        // Support 'nearby' as an area selection
        if ($area === 'nearby') { $nearbyParam = '1'; }

        // 1. Filter by Region (Area)
        if ($area && $area !== 'nearby') {
            $matchedKitchens = KitchenOwner::where('Status', 'Active');
            $matchedKitchens = $matchedKitchens->get()->filter(function($k) use ($area) {
                $loc = strtolower($k->Location ?? '');
                $target = strtolower($area);
                if (str_contains($loc, $target)) return true;
                $kn = strtolower($k->KitchenName ?? '');
                if ($target == 'alexandria' && (str_contains($kn, 'alex') || str_contains($kn, 'sea'))) return true;
                return ($target == 'cairo' && (str_contains($loc, 'cairo') || str_contains($kn, 'mama') || str_contains($kn, 'rania')));
            });
            $kitchenIds = $matchedKitchens->pluck('KitchenOwnerID')->toArray();
        }

        // 2. Filter by Proximity & Calculate Distances
        $isProximityFiltered = false;
        $noAddress = false;
        if ($user) {
            $primaryAddress = \App\Models\UserAddress::where('UserID', $user->UserID)->where('IsPrimary', true)->first();
            if ($primaryAddress && $primaryAddress->Latitude && $primaryAddress->Longitude) {
                $lat1 = deg2rad($primaryAddress->Latitude);
                $lon1 = deg2rad($primaryAddress->Longitude);

                $allKitchens = KitchenOwner::select('KitchenOwnerID', 'Latitude', 'Longitude')->where('Status', 'Active')->get();

                foreach ($allKitchens as $k) {
                    if ($k->Latitude && $k->Longitude) {
                        $lat2 = deg2rad($k->Latitude);
                        $lon2 = deg2rad($k->Longitude);
                        $dist = 6371 * 2 * atan2(sqrt(sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2), sqrt(1 - (sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2)));
                        $kitchenDistances[$k->KitchenOwnerID] = round($dist, 1);
                    }
                }

                if ($nearbyParam == '1') {
                    $nearbyIds = collect($kitchenDistances)->filter(fn($d) => $d <= 20)->keys()->toArray();
                    $kitchenIds = ($kitchenIds === null) ? $nearbyIds : array_intersect($kitchenIds, $nearbyIds);
                    $isProximityFiltered = true;
                }
            } else {
                if ($nearbyParam == '1') $noAddress = true;
            }
        } else {
            if ($nearbyParam == '1') $noAddress = true;
        }

        // ─── Main items query ─────────────────────────────────────────────
        $query = MenuItem::with(['images', 'kitchenOwner', 'caterer', 'category', 'tags'])
            ->where('menu_items.Status', 'Available')
            ->whereNotNull('menu_items.KitchenOwnerID');

        if ($catFilter > 0) {
            $query->where('menu_items.CategoryID', $catFilter);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('menu_items.ItemName', 'LIKE', "%{$search}%")
                  ->orWhere('menu_items.Description', 'LIKE', "%{$search}%");
            });
        }

        if ($kitchenIds !== null) {
            $query->whereIn('menu_items.KitchenOwnerID', $kitchenIds);
        }

        if (!empty($tagsFilter)) {
            foreach ($tagsFilter as $tagId) {
                $query->whereHas('tags', function($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
            }
        }

        $items = $query->orderBy('menu_items.CategoryID')->orderBy('menu_items.ItemName')->get();

        // Compatibility mapping & Distance attachment
        foreach ($items as $item) {
            $item->CatName = $item->category?->Name;
            $item->KitchenName = $item->kitchenOwner?->KitchenName;
            if (isset($kitchenDistances[$item->KitchenOwnerID])) {
                $item->distance = $kitchenDistances[$item->KitchenOwnerID];
            }
        }

        $categories = Category::where('Status', 'Active')->get();

        // ─── Most-ordered items (scoped to nearby kitchens + category + tags) ───
        $mostOrderedItemIds = \Illuminate\Support\Facades\DB::table('menu_order_items')
            ->select('MenuItemID', \Illuminate\Support\Facades\DB::raw('SUM(Quantity) as total_quantity'))
            ->groupBy('MenuItemID')
            ->orderByDesc('total_quantity')
            ->pluck('MenuItemID');

        $mostOrderedItems = collect([]);
        if ($mostOrderedItemIds->isNotEmpty()) {
            $mostOrderedQuery = MenuItem::with(['images', 'kitchenOwner', 'caterer', 'category', 'tags'])
                ->whereIn('menu_items.MenuItemID', $mostOrderedItemIds)
                ->where('menu_items.Status', 'Available')
                ->whereNotNull('menu_items.KitchenOwnerID');

            if ($search) {
                $mostOrderedQuery->where(function($q) use ($search) {
                    $q->where('menu_items.ItemName', 'LIKE', "%{$search}%")
                      ->orWhere('menu_items.Description', 'LIKE', "%{$search}%");
                });
            }

            if ($kitchenIds !== null) {
                $mostOrderedQuery->whereIn('menu_items.KitchenOwnerID', $kitchenIds);
            }
            if ($catFilter > 0) {
                $mostOrderedQuery->where('menu_items.CategoryID', $catFilter);
            }
            if (!empty($tagsFilter)) {
                foreach ($tagsFilter as $tagId) {
                    $mostOrderedQuery->whereHas('tags', function($q) use ($tagId) {
                        $q->where('tags.id', $tagId);
                    });
                }
            }

            $unsortedMostOrdered = $mostOrderedQuery->get();
            $mostOrderedItems = $mostOrderedItemIds->map(function ($id) use ($unsortedMostOrdered) {
                $item = $unsortedMostOrdered->where('MenuItemID', $id)->first();
                if ($item) {
                    $item->CatName = $item->category?->Name;
                    $item->KitchenName = $item->kitchenOwner?->KitchenName;
                }
                return $item;
            })->filter()->take(4);
        }

        // ─── Personalized Recommendations ────────────────────────────────
        $recommendedItems = collect([]);
        if (Auth::check()) {
            $customer = \App\Models\Customer::where('UserID', Auth::id())->first();
            if ($customer) {
                // If a category is selected, we prioritize recommendations from THAT category
                // otherwise we use their top categories from history.
                $targetCatIds = collect([]);

                if ($catFilter > 0) {
                    $targetCatIds = collect([$catFilter]);
                } else {
                    $targetCatIds = \Illuminate\Support\Facades\DB::table('menu_order_items')
                        ->join('orders', 'menu_order_items.OrderID', '=', 'orders.OrderID')
                        ->join('menu_items', 'menu_order_items.MenuItemID', '=', 'menu_items.MenuItemID')
                        ->where('orders.CustomerID', $customer->CustomerID)
                        ->whereNotNull('menu_items.CategoryID')
                        ->select('menu_items.CategoryID', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
                        ->groupBy('menu_items.CategoryID')
                        ->orderByDesc('cnt')
                        ->limit(2)
                        ->pluck('CategoryID');
                }

                if ($targetCatIds->isNotEmpty()) {
                    $excludeIds = $mostOrderedItems->pluck('MenuItemID')->toArray();

                    $recQuery = MenuItem::with(['images', 'kitchenOwner', 'caterer', 'category', 'tags'])
                        ->whereIn('menu_items.CategoryID', $targetCatIds)
                        ->where('menu_items.Status', 'Available')
                        ->whereNotNull('menu_items.KitchenOwnerID')
                        ->whereNotIn('menu_items.MenuItemID', $excludeIds);

                    if ($search) {
                        $recQuery->where(function($q) use ($search) {
                            $q->where('menu_items.ItemName', 'LIKE', "%{$search}%")
                              ->orWhere('menu_items.Description', 'LIKE', "%{$search}%");
                        });
                    }

                    if ($kitchenIds !== null) {
                        $recQuery->whereIn('menu_items.KitchenOwnerID', $kitchenIds);
                    }
                    if (!empty($tagsFilter)) {
                        foreach ($tagsFilter as $tagId) {
                            $recQuery->whereHas('tags', function($q) use ($tagId) {
                                $q->where('tags.id', $tagId);
                            });
                        }
                    }

                    $recommendedItems = $recQuery->inRandomOrder()->limit(4)->get();
                    foreach ($recommendedItems as $item) {
                        $item->CatName = $item->category?->Name;
                        $item->KitchenName = $item->kitchenOwner?->KitchenName;
                    }
                }
            }
        }

        // $isProximityFiltered already set above based on valid address existence
        $allTags = \App\Models\Tag::all()->groupBy('category');

        return view('frontend.menu', compact('items', 'categories', 'catFilter', 'mostOrderedItems', 'recommendedItems', 'isProximityFiltered', 'nearbyParam', 'area', 'search', 'noAddress', 'allTags', 'tagsFilter'));
    }


    public function item($id)
    {
        $item = MenuItem::with(['kitchenOwner', 'caterer', 'category', 'images'])
            ->where('MenuItemID', $id)
            ->first();

        if (!$item)
            return redirect()->route('frontend.menu');

        // Map some legacy attributes for compatibility with the view if needed
        $item->CatName = $item->category?->Name;
        $item->KitchenName = $item->kitchenOwner?->KitchenName;

        $relatedItems = MenuItem::where('CategoryID', $item->CategoryID)
            ->where('MenuItemID', '!=', $id)
            ->where('Status', 'Available')
            ->whereNotNull('KitchenOwnerID')
            ->limit(4)
            ->get();

        return view('frontend.item', compact('item', 'relatedItems'));
    }

    public function topKitchens()
    {
        $user = Auth::user();
        $nearbyKitchenIds = null;
        $noAddress = false;

        if ($user) {
            $primaryAddress = \App\Models\UserAddress::where('UserID', $user->UserID)
                ->where('IsPrimary', true)->first();
            if ($primaryAddress && $primaryAddress->Latitude && $primaryAddress->Longitude) {
                $nearbyKitchenIds = KitchenOwner::select('KitchenOwnerID', 'Latitude', 'Longitude')
                    ->where('Status', 'Active')
                    ->get()
                    ->filter(function ($k) use ($primaryAddress) {
                        if (!$k->Latitude || !$k->Longitude)
                            return false;
                        $lat1 = deg2rad($primaryAddress->Latitude);
                        $lon1 = deg2rad($primaryAddress->Longitude);
                        $lat2 = deg2rad($k->Latitude);
                        $lon2 = deg2rad($k->Longitude);
                        $a = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2;
                        $dist = 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
                        return $dist <= 20;
                    })
                    ->pluck('KitchenOwnerID')
                    ->toArray();
            } else {
                $noAddress = true;
            }
        } else {
            $noAddress = true;
        }

        $whereClause = "WHERE ko.Status='Active'";
        if ($nearbyKitchenIds !== null) {
            if (empty($nearbyKitchenIds)) {
                $topKitchens = [];
                return view('frontend.top-kitchens', compact('topKitchens', 'noAddress'));
            }
            $ids = implode(',', $nearbyKitchenIds);
            $whereClause .= " AND ko.KitchenOwnerID IN ($ids)";
        } else {
            // Guest or no address: we only show something if we have a default or we follow "ONLY near you"
            // For now, let's show nothing and let the view handle the noAddress prompt.
            $topKitchens = [];
            return view('frontend.top-kitchens', compact('topKitchens', 'noAddress'));
        }

        $topKitchens = DB::select("
            SELECT ko.KitchenOwnerID, ko.KitchenName, ko.Description, ko.VerifyStatus, u.Image, u.FullName,
            COUNT(DISTINCT o.OrderID) as totalOrders,
            COALESCE(SUM(o.TotalPrice),0) as totalRevenue,
            COALESCE((SELECT ROUND(AVG(Rating), 1) FROM reviews r WHERE r.KitchenOwnerID = ko.KitchenOwnerID), 4.5) as average_rating
            FROM kitchen_owners ko
            JOIN users u ON ko.UserID=u.UserID
            LEFT JOIN menu_items mi ON mi.KitchenOwnerID=ko.KitchenOwnerID
            LEFT JOIN menu_order_items moi ON moi.MenuItemID=mi.MenuItemID
            LEFT JOIN orders o ON o.OrderID=moi.OrderID
            $whereClause
            GROUP BY ko.KitchenOwnerID, ko.KitchenName, ko.Description, ko.VerifyStatus, u.Image, u.FullName
            ORDER BY totalOrders DESC, totalRevenue DESC
            LIMIT 10
        ");

        return view('frontend.top-kitchens', compact('topKitchens', 'noAddress'));
    }

    public function subscriptions()
    {
        $user = Auth::user();
        $mySubscriptions = collect();
        $pendingSubscriptions = collect();

        if ($user && $user->Role === 'Customer' && $user->customer) {
            $customerID = $user->customer->CustomerID;

            $today = \Carbon\Carbon::today()->toDateString();

            $pendingSubscriptions = Subscription::where('CustomerID', $customerID)
                ->whereIn('Status', ['PendingApproval', 'AwaitingPayment', 'Requested'])
                ->with(['menuItems', 'payments', 'kitchen.user'])
                ->get();

            $mySubscriptions = Subscription::where('CustomerID', $customerID)
                ->whereIn('Status', ['Active', 'Expired', 'Cancelled', 'Paused'])
                ->with([
                    'menuItems',
                    'payments',
                    'kitchen.user',
                    'orders' => function ($q) use ($today) {
                        $q->whereDate('ScheduledDate', '>=', $today)
                            ->orderBy('ScheduledDate', 'asc');
                    }
                ])
                ->orderByDesc('SubscriptionID')
                ->get();
        }

        // Fetch all active available plans for browsing
        $plans = KitchenPlan::where('Status', 'Active')->with(['kitchen.user', 'menuItems.images'])->orderByDesc('KitchenPlanID')->get();

        return view('frontend.subscriptions', compact('plans', 'mySubscriptions', 'pendingSubscriptions'));
    }

    public function orderTracking($id)
    {
        $user = Auth::user();
        if (!$user->customer)
            return redirect()->route('login');

        $ids = explode(',', $id);
        $orders = Order::whereIn('OrderID', $ids)->where('CustomerID', $user->customer->CustomerID)->with(['kitchenOwner', 'caterer', 'payment', 'menuItems'])->get();
        if ($orders->isEmpty())
            abort(404);

        $reviews = \App\Models\Review::whereIn('OrderID', $ids)->get()->keyBy('OrderID');
        return view('frontend.order-tracking', ['orders' => $orders, 'reviews' => $reviews, 'id_string' => $id]);
    }

    public function orderTrackingData($id)
    {
        $user = Auth::user();
        if (!$user || !$user->customer)
            return response()->json(['error' => 'Unauthorized'], 401);

        $ids = explode(',', $id);
        $orders = Order::whereIn('OrderID', $ids)->where('CustomerID', $user->customer->CustomerID)
            ->with(['kitchenOwner', 'caterer', 'subscription.kitchen'])
            ->get();

        $primaryAddress = \App\Models\UserAddress::where('UserID', $user->UserID)->where('IsPrimary', true)->first();
        $deliveryLat = $primaryAddress ? $primaryAddress->Latitude : null;
        $deliveryLng = $primaryAddress ? $primaryAddress->Longitude : null;

        $data = [];
        foreach ($orders as $o) {
            $kitchenLat = $o->kitchenOwner ? $o->kitchenOwner->Latitude : ($o->caterer ? $o->caterer->Latitude : null);
            $kitchenLng = $o->kitchenOwner ? $o->kitchenOwner->Longitude : ($o->caterer ? $o->caterer->Longitude : null);

            if (!$kitchenLat && $o->subscription && $o->subscription->kitchen) {
                $kitchenLat = $o->subscription->kitchen->Latitude;
                $kitchenLng = $o->subscription->kitchen->Longitude;
            }

            $data[$o->OrderID] = [
                'status' => $o->OrderStatus,
                'driver_lat' => $o->DriverLatitude ?? $kitchenLat,
                'driver_lng' => $o->DriverLongitude ?? $kitchenLng,
                'kitchen_lat' => $kitchenLat,
                'kitchen_lng' => $kitchenLng,
                'delivery_lat' => $deliveryLat,
                'delivery_lng' => $deliveryLng,
            ];
        }
        return response()->json($data);
    }

    public function cancelOrder($id)
    {
        $user = Auth::user();
        if (!$user->customer)
            return redirect()->route('login');

        $order = Order::where('OrderID', $id)
            ->where('CustomerID', $user->customer->CustomerID)
            ->firstOrFail();

        // Ony allow cancelling if Pending
        if ($order->OrderStatus === 'Pending') {
            $order->update(['OrderStatus' => 'Cancelled']);
            return back()->with(['message' => 'Order cancelled automatically.', 'alert-type' => 'success']);
        }

        return back()->with(['message' => 'Order cannot be cancelled at this stage.', 'alert-type' => 'error']);
    }

    public function rateOrder(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->customer)
            return redirect()->route('login');

        $order = Order::with('menuItems')->where('OrderID', $id)
            ->where('CustomerID', $user->customer->CustomerID)
            ->firstOrFail();

        if ($order->OrderStatus !== 'Delivered') {
            return back()->with(['message' => 'You can only rate delivered orders.', 'alert-type' => 'error']);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
        ]);

        if ($request->comment && !app(\App\Services\ProfanityFilterService::class)->checkAndProcess($user, $request->comment)) {
            return back()->with(['message' => 'Your comment contains prohibited language and was not saved.', 'alert-type' => 'error']);
        }

        if (\App\Models\Review::where('OrderID', $id)->exists()) {
            return back()->with(['message' => 'You have already rated this order.', 'alert-type' => 'warning']);
        }

        $firstItem = $order->menuItems->first();
        if (!$firstItem) {
            return back()->with(['message' => 'Order has no items.', 'alert-type' => 'error']);
        }

        \App\Models\Review::create([
            'CustomerID' => $user->customer->CustomerID,
            'KitchenOwnerID' => $firstItem->KitchenOwnerID ?: null,
            'CatererID' => $firstItem->CatererID ?: null,
            'OrderID' => $order->OrderID,
            'Rating' => $request->rating,
            'Comment' => $request->comment,
            'CreatedAt' => now(),
        ]);

        return back()->with(['message' => 'Thank you for your feedback!', 'alert-type' => 'success']);
    }

    public function cateringForm()
    {
        // View catering form
        $caterers = Caterer::join('users', 'caterers.UserID', '=', 'users.UserID')
            ->select('caterers.*', 'users.FullName')
            ->where('caterers.IsActive', 1)->get();
        return view('frontend.catering-booking', compact('caterers'));
    }

    public function storeCatering(Request $request)
    {
        $request->validate([
            'caterer_id' => 'required',
            'event_type' => 'required|string|max:100',
            'event_date' => 'required|date|after:today',
            'guest_count' => 'required|integer|min:10',
        ]);

        $user = Auth::user();
        if (!$user)
            return redirect()->route('login')->with('message', 'Please login to book a caterer');

        $customer = $user->customer;
        if (!$customer) {
            $customer = Customer::create(['UserID' => $user->UserID, 'WalletBalance' => 0.00]);
        }

        CateringRequest::create([
            'CustomerID' => $customer->CustomerID,
            'CatererID' => $request->caterer_id,
            'EventType' => $request->event_type,
            'EventDate' => $request->event_date,
            'GuestCount' => $request->guest_count,
            'Budget' => $request->budget,
            'Details' => $request->details,
            'Status' => 'Pending',
        ]);

        return redirect()->route('frontend.home')->with(['message' => 'Catering request submitted successfully! The caterer will review your request.', 'alert-type' => 'success']);
    }

    public function subscribe(Request $request)
    {
        return redirect()->route('frontend.meal_plan_builder');
    }

    public function storeSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:kitchen_plans,KitchenPlanID',
            'start_date' => 'required|date|after_or_equal:today',
        ]);

        $user = Auth::user();
        if (!$user)
            return redirect()->route('login');

        // Auto-create customer if missing
        $customer = $user->customer;
        if (!$customer) {
            $customer = Customer::create(['UserID' => $user->UserID, 'WalletBalance' => 0.00]);
        }

        $plan = KitchenPlan::with('kitchen')->findOrFail($request->plan_id);

        if ($plan->kitchen && $plan->kitchen->current_status === 'Closed') {
            return back()->with(['message' => "Sorry, {$plan->kitchen->KitchenName} is currently closed. You can't subscribe now.", 'alert-type' => 'error']);
        }

        $price = $plan->Price;

        DB::beginTransaction();
        try {
            // ── Payment Processing ──────────────────────────────────────
            if ($request->payment_method === 'Wallet') {
                if ($customer->WalletBalance < $price) {
                    throw new \Exception('Insufficient wallet balance.');
                }
                $customer->decrement('WalletBalance', $price);
                $payment = Payment::create(['Method' => 'Wallet']);
            } else {
                // This shouldn't happen as Stripe has its own path, but fallback
                throw new \Exception('Invalid payment method for this route.');
            }

            $days = ['Daily' => 1, 'Weekly' => 7, 'Monthly' => 30];
            $endDate = \Carbon\Carbon::parse($request->start_date)->addDays($days[$plan->PlanTime] ?? 30);

            // ── Create Subscription ──────────────────────────────────────
            $sub = Subscription::create([
                'CustomerID' => $customer->CustomerID,
                'KitchenPlanID' => $plan->KitchenPlanID,
                'PlanTime' => $plan->PlanTime,
                'Price' => $price,
                'StartDate' => $request->start_date,
                'EndDate' => $endDate,
                'Status' => 'Active',
            ]);

            // ── Link Payment ─────────────────────────────────────────────
            DB::table('subscription_payments')->insert([
                'SubscriptionID' => $sub->SubscriptionID,
                'PaymentID' => $payment->PaymentID,
            ]);

            // ── Award Loyalty Points ─────────────────────────────────────
            $points = (int) floor($price / 10);
            if ($points > 0) {
                LoyaltyTransaction::create([
                    'CustomerID' => $customer->CustomerID,
                    'Points' => $points,
                    'Type' => 'Earned',
                    'Description' => 'Subscribed to "' . $plan->Title . '" — earned ' . $points . ' BitePoints 🎉',
                ]);
            }

            // ── Attach Menu Items ────────────────────────────────────────
            $menuItemIds = $request->input('menu_items', []);
            if (!empty($menuItemIds)) {
                $sub->menuItems()->attach($menuItemIds, ['Status' => 'Pending']);
            }

            DB::commit();
            return redirect()->route('dashboard.customer')->with(['message' => 'Subscription successful! 🎉', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Subscription failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── Chat with Kitchen (Customer side - Order specific) ────────────────
    public function chatWithOrder($orderId)
    {
        $user = Auth::user();
        $order = Order::where('OrderID', $orderId)
            ->where('CustomerID', $user->customer->CustomerID ?? 0)
            ->first();

        if (!$order) {
            return redirect()->route('dashboard.customer')->with(['message' => 'Order not found.', 'alert-type' => 'error']);
        }

        // Determine if kitchen or caterer
        $kitchenUser = null;
        $chatTitle = 'Chef';
        $chatImage = null;

        if ($order->kitchenOwner) {
            $ko = $order->kitchenOwner;
            $kitchenUser = $ko->user;
            $chatTitle = $ko->KitchenName;
            $chatImage = $kitchenUser->Image;
        } elseif ($order->caterer) {
            $cat = $order->caterer;
            $kitchenUser = $cat->user;
            $chatTitle = $cat->user->FullName . ' (Caterer)';
            $chatImage = $kitchenUser->Image;
        } elseif ($order->subscription && $order->subscription->kitchen) {
            $ko = $order->subscription->kitchen;
            $kitchenUser = $ko->user;
            $chatTitle = $ko->KitchenName;
            $chatImage = $kitchenUser->Image;
        } elseif ($order->menuItems->first() && $order->menuItems->first()->kitchenOwner) {
            $ko = $order->menuItems->first()->kitchenOwner;
            $kitchenUser = $ko->user;
            $chatTitle = $ko->KitchenName;
            $chatImage = $kitchenUser->Image;
        } elseif ($order->menuItems->first() && $order->menuItems->first()->caterer) {
            $cat = $order->menuItems->first()->caterer;
            $kitchenUser = $cat->user;
            $chatTitle = $cat->user->FullName . ' (Caterer)';
            $chatImage = $kitchenUser->Image;
        }

        if (!$kitchenUser) {
            return back()->with(['message' => 'No kitchen assigned to this order yet.', 'alert-type' => 'warning']);
        }

        $messages = \App\Models\LiveChat::where('OrderID', $orderId)
            ->orderBy('LiveChatID')
            ->get();

        // Pass simple vars to view
        $chatData = [
            'orderId' => $order->OrderID,
            'title' => $chatTitle,
            'image' => $chatImage,
            'receiverId' => $kitchenUser->UserID,
        ];

        return view('frontend.chat', compact('chatData', 'messages'));
    }

    public function sendOrderChatMessage(Request $request, $orderId)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $user = Auth::user();
        $order = Order::where('OrderID', $orderId)
            ->where('CustomerID', $user->customer->CustomerID ?? 0)
            ->first();

        if (!$order)
            return response()->json(['error' => 'Not found'], 404);

        $receiverId = $request->receiver_id; // From frontend

        $msg = \App\Models\LiveChat::create([
            'OrderID' => $order->OrderID,
            'SenderID' => $user->UserID,
            'ReceiverID' => $receiverId,
            'Message' => $request->message,
            'Type' => 'message',
            'Timestamp' => now(),
        ]);

        return response()->json([
            'id' => $msg->LiveChatID,
            'message' => $msg->Message,
            'sender' => $user->FullName,
            'senderImg' => $user->Image,
            'time' => $msg->Timestamp->format('H:i'),
            'isMine' => true,
            'type' => $msg->Type
        ]);
    }

    public function getOrderChatMessages(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = Order::where('OrderID', $orderId)
            ->where('CustomerID', $user->customer->CustomerID ?? 0)
            ->first();

        if (!$order)
            return response()->json([]);

        $lastId = intval($request->get('after', 0));
        $messages = \App\Models\LiveChat::where('OrderID', $orderId)
            ->where('LiveChatID', '>', $lastId)
            ->orderBy('LiveChatID')
            ->get();

        return response()->json($messages->map(fn($m) => [
            'id' => $m->LiveChatID,
            'message' => $m->Message,
            'isMine' => $m->SenderID == $user->UserID,
            'time' => \Carbon\Carbon::parse($m->Timestamp)->format('H:i'),
            'type' => $m->Type,
            'charge' => $m->ExtraCharge
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Customer Profile
    // ─────────────────────────────────────────────────────────────────────

    public function myProfile()
    {
        $user = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();

        $walletBalance = $customer?->WalletBalance ?? 0;
        $loyaltyPoints = 0;
        $totalOrders = 0;
        $mySubscriptions = collect();
        $addresses = [];

        if ($customer) {
            $loyaltyPoints = LoyaltyTransaction::where('CustomerID', $customer->CustomerID)
                ->selectRaw('SUM(CASE WHEN Type IN ("Earned","Bonus","Referral") THEN Points ELSE -Points END) as total')
                ->value('total') ?? 0;

            $totalOrders = Order::where('CustomerID', $customer->CustomerID)
                ->where('OrderStatus', '!=', 'Cancelled')
                ->count();

            $mySubscriptions = Subscription::where('CustomerID', $customer->CustomerID)
                ->with(['menuItems'])
                ->orderByDesc('SubscriptionID')
                ->get();

            $activePlansCount = $mySubscriptions->where('Status', 'Active')
                ->filter(function ($sub) {
                    return \Carbon\Carbon::parse($sub->EndDate)->endOfDay()->isFuture();
                })->count();
            $addresses = \App\Models\UserAddress::where('UserID', $user->UserID)->get();
        } else {
            $activePlansCount = 0;
            $addresses = \App\Models\UserAddress::where('UserID', $user->UserID)->get();
        }

        $activeSessions = [];
        if ($user) {
            // 1. Pre-order customization sessions
            $preOrderSessions = \App\Models\LiveChat::whereNull('OrderID')
                ->whereNotNull('SessionID')
                ->where(function ($q) use ($user) {
                    $q->where('SenderID', $user->UserID)->orWhere('ReceiverID', $user->UserID);
                })
                ->with(['menuItem', 'sender'])
                ->get()
                ->groupBy('SessionID')
                ->map(function ($msgs) use ($user) {
                    $first = $msgs->sortBy('LiveChatID')->first();
                    $last = $msgs->sortByDesc('LiveChatID')->first();
                    
                    $kitchenId = 0; $catererId = 0;
                    if ($first->menuItem) {
                        $kitchenId = $first->menuItem->KitchenOwnerID;
                        $catererId = $first->menuItem->CatererID;
                    } else {
                        $receiverId = ($first->SenderID == $user->UserID) ? $first->ReceiverID : $first->SenderID;
                        $k = \App\Models\KitchenOwner::where('UserID', $receiverId)->first();
                        if ($k) $kitchenId = $k->KitchenOwnerID;
                        else {
                            $c = \App\Models\Caterer::where('UserID', $receiverId)->first();
                            if ($c) $catererId = $c->CatererID;
                        }
                    }

                    return [
                        'session_id' => $first->SessionID,
                        'order_id' => null,
                        'menu_item_id' => $first->MenuItemID,
                        'item_name' => $first->menuItem->ItemName ?? 'Custom Request',
                        'item_price' => $first->menuItem->ItemPrice ?? 0,
                        'kitchen_id' => $kitchenId,
                        'caterer_id' => $catererId,
                        'last_message' => $last->Message,
                        'last_time' => $last->Timestamp->diffForHumans(),
                        'unread' => ($last->SenderID != $user->UserID),
                        'owner_type' => $kitchenId ? 'kitchen' : ($catererId ? 'caterer' : 'kitchen')
                    ];
                });

            // 2. Active Order sessions
            $orderSessions = \App\Models\LiveChat::whereNotNull('OrderID')
                ->whereHas('order', function($q) {
                    $q->whereNotIn('OrderStatus', ['Delivered', 'Cancelled']);
                })
                ->where(function ($q) use ($user) {
                    $q->where('SenderID', $user->UserID)->orWhere('ReceiverID', $user->UserID);
                })
                ->with(['order.kitchenOwner', 'order.caterer', 'sender'])
                ->get()
                ->groupBy('OrderID')
                ->map(function ($msgs) use ($user) {
                    $last = $msgs->sortByDesc('LiveChatID')->first();
                    $first = $msgs->sortBy('LiveChatID')->first();
                    $order = $last->order;
                    
                    $vendorName = $order->kitchenOwner->KitchenName ?? ($order->caterer->FullName ?? 'Vendor');

                    return [
                        'session_id' => null,
                        'order_id' => $order->OrderID,
                        'menu_item_id' => 0,
                        'item_name' => "Order #{$order->OrderID} ({$vendorName})",
                        'item_price' => $order->TotalPrice,
                        'kitchen_id' => $order->KitchenOwnerID,
                        'caterer_id' => $order->CatererID,
                        'last_message' => $last->Message,
                        'last_time' => $last->Timestamp->diffForHumans(),
                        'unread' => ($last->SenderID != $user->UserID),
                        'owner_type' => $order->KitchenOwnerID ? 'kitchen' : ($order->CatererID ? 'caterer' : 'kitchen')
                    ];
                });

            $activeSessions = $preOrderSessions->merge($orderSessions)->values();
        }

        return view('frontend.my_profile', compact(
            'user',
            'customer',
            'walletBalance',
            'loyaltyPoints',
            'totalOrders',
            'mySubscriptions',
            'activePlansCount',
            'addresses',
            'activeSessions'
        ));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Photo Upload
        if ($request->has('update_photo')) {
            $request->validate(['photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . rand() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/admin_images'), $filename);
                // Delete old image if exists
                if ($user->Image && file_exists(public_path('upload/admin_images/' . $user->Image))) {
                    @unlink(public_path('upload/admin_images/' . $user->Image));
                }
                $user->Image = $filename;
                $user->save();
            }
            return back()->with(['message' => 'Profile photo updated!', 'alert-type' => 'success']);
        }

        // Info Update
        $request->validate([
            'full_name' => 'required|string|max:100',
        ]);

        $user->FullName = $request->full_name;
        if ($request->filled('phone')) {
            $user->PhoneNumber = $request->phone;
        }
        $user->save();

        return back()->with(['message' => 'Profile updated successfully!', 'alert-type' => 'success']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password ?? $user->Password ?? '')) {
            return back()->with(['message' => 'Current password is incorrect.', 'alert-type' => 'error']);
        }

        $user->Password = Hash::make($request->password);
        $user->password = Hash::make($request->password); // for Breeze compatibility
        $user->save();

        return back()->with(['message' => 'Password updated successfully!', 'alert-type' => 'success']);
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        // Capture details before deletion
        $userEmail = $user->Email;
        $userName = $user->FullName;

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Send goodbye email after logout/delete
        try {
            Mail::to($userEmail)->send(new \App\Mail\AccountDeletedMail($userName));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Account deletion email failed: ' . $e->getMessage());
        }

        return redirect('/')->with(['message' => 'Your account has been permanently deleted.', 'alert-type' => 'success']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Subscription Management (Customer)
    // ─────────────────────────────────────────────────────────────────────

    public function cancelSubscription(Request $request, $id)
    {
        $user = auth()->user();
        $sub = Subscription::findOrFail($id);

        if ($sub->CustomerID !== $user->customer->CustomerID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $refund = $sub->cancelAndRefund($request->reason);

        if ($refund === false) {
            return response()->json(['success' => false, 'message' => 'Subscription already cancelled.']);
        }

        return response()->json(['success' => true, 'refund' => $refund]);
    }

    public function pauseSubscription(Request $request, $id)
    {
        $user = auth()->user();
        $sub = Subscription::findOrFail($id);

        if ($sub->CustomerID !== $user->customer->CustomerID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $sub->Status = 'Paused';
        $sub->is_paused = true;
        $sub->pause_reason = $request->reason;
        $sub->paused_at = now();
        $sub->save();

        return response()->json(['success' => true]);
    }

    public function resumeSubscription(Request $request, $id)
    {
        $user = auth()->user();
        $sub = Subscription::findOrFail($id);

        if ($sub->CustomerID !== $user->customer->CustomerID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $sub->Status = 'Active';
        $sub->is_paused = false;
        $sub->save();

        return response()->json(['success' => true]);
    }

    public function deletePendingSubscription($id)
    {
        $user = auth()->user();
        $sub = Subscription::findOrFail($id);

        if ($sub->CustomerID !== $user->customer->CustomerID) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        if (!in_array($sub->Status, ['PendingApproval', 'AwaitingPayment'])) {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be cancelled.']);
        }

        $sub->menuItems()->detach();
        $sub->delete();

        return response()->json(['success' => true]);
    }

    public function renewSubscription($id)
    {
        $user = Auth::user();
        $customer = \App\Models\Customer::where('UserID', $user->UserID)->firstOrFail();

        $oldSub = Subscription::where('SubscriptionID', $id)
            ->where('CustomerID', $customer->CustomerID)
            ->firstOrFail();

        $baseDate = \Carbon\Carbon::parse($oldSub->EndDate);
        if ($baseDate->isPast()) {
            $baseDate = \Carbon\Carbon::now();
        }

        $daysMap = ['Daily' => 1, 'Weekly' => 7, 'Monthly' => 30];
        $durationDays = $oldSub->DurationDays ?: (!empty($oldSub->PlanTime) ? ($daysMap[$oldSub->PlanTime] ?? 30) : 30);
        $newEnd = $baseDate->copy()->addDays($durationDays);

        // Option B: Clone the subscription to send a new request to the kitchen
        $newSub = Subscription::create([
            'CustomerID' => $oldSub->CustomerID,
            'KitchenOwnerID' => $oldSub->KitchenOwnerID,
            'KitchenPlanID' => $oldSub->KitchenPlanID,
            'PlanTime' => $oldSub->PlanTime,
            'MealsPerDay' => $oldSub->MealsPerDay,
            'DurationDays' => $durationDays,
            'PreferredTimes' => $oldSub->PreferredTimes,
            'Status' => 'PendingApproval',
            'StartDate' => $baseDate,
            'EndDate' => $newEnd,
            'Price' => 0,
            'DeliveryCharge' => (($oldSub->DurationDays ?? 0) * ($oldSub->MealsPerDay ?? 1)) * 15.00,
            'PaidAmount' => 0,
            'DepositAmount' => 0,
            'is_paused' => false,
        ]);

        $menuItemIds = $oldSub->menuItems->pluck('MenuItemID')->toArray();
        if (!empty($menuItemIds)) {
            $newSub->menuItems()->attach($menuItemIds, ['Status' => 'Pending']);
        }

        return back()->with(['message' => 'Renewal request sent to kitchen for approval! 🎉', 'alert-type' => 'success']);
    }
    public function stripeSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:kitchen_plans,KitchenPlanID',
            'start_date' => 'required|date|after_or_equal:today',
        ]);

        $plan = KitchenPlan::findOrFail($request->plan_id);
        $user = Auth::user();

        // Store subscription data in session
        $request->session()->put('stripe_subscription', [
            'plan_id' => $request->plan_id,
            'start_date' => $request->start_date,
            'menu_items' => $request->menu_items ?? [],
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'egp',
                        'product_data' => ['name' => 'BiteHub Subscription: ' . $plan->Title],
                        'unit_amount' => (int) ($plan->Price * 100),
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('frontend.stripe.subscribe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('frontend.subscribe', ['plan' => $request->plan_id]),
        ]);

        return redirect($session->url);
    }

    public function stripeSubscriptionSuccess(Request $request)
    {
        $data = $request->session()->pull('stripe_subscription');
        if (!$data)
            return redirect()->route('frontend.home')->with(['message' => 'Session expired.', 'alert-type' => 'error']);

        $user = Auth::user();

        // Auto-create customer if missing
        $customer = $user->customer;
        if (!$customer) {
            $customer = Customer::create(['UserID' => $user->UserID, 'WalletBalance' => 0.00]);
        }

        $plan = KitchenPlan::findOrFail($data['plan_id']);

        DB::beginTransaction();
        try {
            $payment = Payment::create(['Method' => 'Card']);

            $days = ['Daily' => 1, 'Weekly' => 7, 'Monthly' => 30];
            $endDate = \Carbon\Carbon::parse($data['start_date'])->addDays($days[$plan->PlanTime] ?? 30);

            $sub = Subscription::create([
                'CustomerID' => $customer->CustomerID,
                'KitchenOwnerID' => $plan->KitchenOwnerID,
                'KitchenPlanID' => $plan->KitchenPlanID,
                'PlanTime' => $plan->PlanTime,
                'Price' => $plan->Price,
                'MealsPerDay' => $plan->MealsPerDay, // Use frequency from plan
                'DurationDays' => $days[$plan->PlanTime] ?? 30, // Map duration
                'StartDate' => $data['start_date'],
                'EndDate' => $endDate,
                'Status' => 'Active',
            ]);

            // ── Link Payment ─────────────────────────────────────────────
            DB::table('subscription_payments')->insert([
                'SubscriptionID' => $sub->SubscriptionID,
                'PaymentID' => $payment->PaymentID,
            ]);

            // Attach pre-defined menu items from the plan
            $planItems = $plan->menuItems->pluck('MenuItemID')->toArray();
            if (!empty($planItems)) {
                $sub->menuItems()->attach($planItems, ['Status' => 'Approved']);
            }

            // Also attach any manually selected items if provided
            if (!empty($data['menu_items'])) {
                $manualItems = array_diff($data['menu_items'], $planItems);
                if (!empty($manualItems)) {
                    $sub->menuItems()->attach($manualItems, ['Status' => 'Pending']);
                }
            }

            // Award Points (10% of total)
            $points = (int) floor($plan->Price / 10);
            if ($points > 0) {
                LoyaltyTransaction::create([
                    'CustomerID' => $customer->CustomerID,
                    'Points' => $points,
                    'Type' => 'Earned',
                    'Description' => 'Subscribed to "' . $plan->Title . '" via Card — earned ' . $points . ' BitePoints 🎉',
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard.customer')->with(['message' => 'Subscribed successfully via Card! 🎉', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('frontend.home')->with(['message' => 'Subscription failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function storeSubscriptionRequest(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,MenuItemID',
            'duration' => 'required|integer|in:7,14,30',
            'meals_per_day' => 'required|integer|in:1,2,3',
            'start_date' => 'required|date|after:today',
        ]);

        $user = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found.']);
        }

        $item = MenuItem::findOrFail($request->menu_item_id);

        // Ensure it's a home kitchen item
        if (!$item->KitchenOwnerID) {
            return response()->json(['success' => false, 'message' => 'This item does not belong to a home kitchen.']);
        }

        $endDate = \Carbon\Carbon::parse($request->start_date)->addDays((int) $request->duration);

        try {
            DB::beginTransaction();

            $sub = Subscription::create([
                'CustomerID' => $customer->CustomerID,
                'KitchenOwnerID' => $item->KitchenOwnerID,
                'Status' => 'PendingApproval',
                'StartDate' => $request->start_date,
                'EndDate' => $endDate,
                'MealsPerDay' => $request->meals_per_day,
                'DurationDays' => $request->duration,
                'Price' => 0, // Will be set by kitchen owner during approval
            ]);

            // Link the specific item
            $sub->menuItems()->attach($item->MenuItemID, ['Status' => 'PendingApproval']);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function mealPlanBuilder()
    {
        $user = Auth::user();
        $primaryAddress = \App\Models\UserAddress::where('UserID', $user->UserID)->where('IsPrimary', true)->first();

        $kitchens = KitchenOwner::join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
            ->where('kitchen_owners.Status', 'Active')
            ->where('kitchen_owners.AcceptsPlanRequests', true)
            ->with(['reviews'])
            ->get();

        if ($primaryAddress && $primaryAddress->Latitude && $primaryAddress->Longitude) {
            $kitchens = $kitchens->filter(function ($k) use ($primaryAddress) {
                if (!$k->Latitude || !$k->Longitude) return false;
                $lat1 = deg2rad($primaryAddress->Latitude);
                $lon1 = deg2rad($primaryAddress->Longitude);
                $lat2 = deg2rad($k->Latitude);
                $lon2 = deg2rad($k->Longitude);
                $a = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lon2 - $lon1) / 2) ** 2;
                $dist = 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
                return $dist <= 20;
            });
        }

        $kitchens = $kitchens->map(function ($k) {
                // Exact Heuristic Logic from Browse/Home
                $kImg = 'default_k.png';
                $kn = strtolower($k->KitchenName);
                if (str_contains($kn, 'mama'))
                    $kImg = 'mama.png';
                elseif (str_contains($kn, 'rania'))
                    $kImg = 'rania.png';
                elseif (str_contains($kn, 'amira'))
                    $kImg = 'hero.png';
                elseif (str_contains($kn, 'fatma'))
                    $kImg = 'upper_egypt.png';
                elseif (str_contains($kn, 'nour') || str_contains($kn, 'delights'))
                    $kImg = 'mediterranean.png';
                elseif (str_contains($kn, 'heba') || str_contains($kn, 'healthy'))
                    $kImg = 'healthy.png';
                elseif (str_contains($kn, 'samira') || str_contains($kn, 'seafood') || str_contains($kn, 'alex'))
                    $kImg = 'seafood.png';

                $kRawImg = $k->UserImage;
                if (!empty($kRawImg) && !str_contains($kRawImg, 'no_image') && file_exists(public_path('upload/admin_images/' . $kRawImg))) {
                    $k->ProfileImg = asset('upload/admin_images/' . $kRawImg);
                } else {
                    $k->ProfileImg = asset('upload/website_assets/' . $kImg);
                }

                $k->AverageRating = number_format($k->reviews->avg('Rating') ?: 4.5, 1);
                return $k;
            });

        return view('frontend.meal_plan_builder', compact('kitchens'));
    }

    public function getKitchenMenu($id)
    {
        $items = MenuItem::with(['category', 'images'])->where('KitchenOwnerID', $id)->where('Status', 'Available')->get();
        return response()->json($items->map(function ($i) {
            $itemImg = null;
            if ($i->images->count() > 0) {
                $dbImg = $i->images->first()->Image;
                $itemImg = str_starts_with($dbImg, 'http') ? $dbImg : asset('upload/item_images/' . $dbImg);
            } else {
                // Exact Heuristic Logic from Home/Menu
                $mappedImg = 'grills.png';
                $in = strtolower($i->ItemName);
                if (str_contains($in, 'koshari'))
                    $mappedImg = 'koshari.png';
                elseif (str_contains($in, 'mahshi') || str_contains($in, 'waraq') || str_contains($in, 'stuffed'))
                    $mappedImg = 'mahshi.png';
                elseif (str_contains($in, 'foul') || str_contains($in, 'falafel') || str_contains($in, 'breakfast'))
                    $mappedImg = 'foul_falafel.png';
                elseif (str_contains($in, 'pasta') || str_contains($in, 'macarona') || str_contains($in, 'bechamel') || str_contains($in, 'lasagna'))
                    $mappedImg = 'pasta.png';
                elseif (str_contains($in, 'molokhia') || str_contains($in, 'green') || str_contains($in, 'salad') || str_contains($in, 'keto') || str_contains($in, 'healthy') || str_contains($in, 'acai'))
                    $mappedImg = 'healthy.png';
                elseif (str_contains($in, 'fish') || str_contains($in, 'shrimp') || str_contains($in, 'seafood') || str_contains($in, 'sayadeya'))
                    $mappedImg = 'seafood.png';
                elseif (str_contains($in, 'dessert') || str_contains($in, 'sweet') || str_contains($in, 'baklava') || str_contains($in, 'kunafa') || str_contains($in, 'qatayef') || str_contains($in, 'basbousa') || str_contains($in, 'ali') || str_contains($in, 'cake'))
                    $mappedImg = 'sweets.png';
                elseif (str_contains($in, 'soup') || str_contains($in, 'lentil') || str_contains($in, 'orzo'))
                    $mappedImg = 'soup.png';
                elseif (str_contains($in, 'fattah') || str_contains($in, 'mansaf') || str_contains($in, 'kabsa') || str_contains($in, 'roz'))
                    $mappedImg = 'traditional_rice.png';
                elseif (str_contains($in, 'juice') || str_contains($in, 'mango') || str_contains($in, 'sahlab') || str_contains($in, 'coffee') || str_contains($in, 'drink'))
                    $mappedImg = 'drinks.png';
                elseif (str_contains($in, 'wedding') || str_contains($in, 'corporate') || str_contains($in, 'package'))
                    $mappedImg = 'packages.png';

                $itemImg = asset('upload/website_assets/' . $mappedImg);
            }

            return [
                'id' => $i->MenuItemID,
                'name' => $i->ItemName,
                'price' => $i->ItemPrice,
                'image' => $itemImg,
                'category' => $i->category->Name ?? 'Food',
                'description' => $i->Description ?? 'No description available.'
            ];
        })->values());
    }

    public function storeMealPlanBuilder(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->Role !== 'Customer') {
            return response()->json(['success' => false, 'message' => 'Please login as a customer to build a plan.']);
        }

        $customer = $user->customer;
        if (!$customer) {
            $customer = Customer::create(['UserID' => $user->UserID, 'WalletBalance' => 0.00]);
        }

        $request->validate([
            'kitchen_id' => 'required',
            'menu_items' => 'required|array|min:1',
            'duration' => 'required|integer|min:1',
            'meals_per_day' => 'required|integer|min:1|max:5',
            'start_date' => 'required|date|after_or_equal:today',
            'times' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            $endDate = \Carbon\Carbon::parse($request->start_date)->addDays((int) $request->duration);

            $duration = (int) $request->duration;
            $mealsPerDay = (int) ($request->meals_per_day ?? 1);
            $deliveryFee = ($duration * $mealsPerDay) * 15.00; // 15 EGP per individual meal delivery
            $totalPrice = ($request->price ?? 0);

            $sub = Subscription::create([
                'CustomerID' => $customer->CustomerID,
                'KitchenOwnerID' => $request->kitchen_id,
                'Status' => 'PendingApproval',
                'StartDate' => $request->start_date,
                'EndDate' => $endDate,
                'MealsPerDay' => $request->meals_per_day,
                'DurationDays' => $duration,
                'PreferredTimes' => $request->times, // Store as JSON array
                'Price' => $totalPrice,
                'DeliveryCharge' => $deliveryFee,
                'PaidAmount' => 0.00,
                'DepositAmount' => 0.00, // Will be set during payment
            ]);

            $sub->menuItems()->attach($request->menu_items, ['Status' => 'Pending']);

            if ($request->filled('custom_message')) {
                $kitchenOwner = \App\Models\KitchenOwner::find($request->kitchen_id);
                if ($kitchenOwner) {
                    \App\Models\LiveChat::create([
                        'SubscriptionID' => $sub->SubscriptionID,
                        'SenderID' => $user->UserID,
                        'ReceiverID' => $kitchenOwner->UserID,
                        'Message' => $request->custom_message,
                        'Type' => 'message'
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'subscription_id' => $sub->SubscriptionID]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function paySubscriptionInstallment(Request $request)
    {
        $user = Auth::user();
        if (!$user)
            return response()->json(['success' => false, 'message' => 'Please login.']);

        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,SubscriptionID',
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string'
        ]);

        $sub = Subscription::find($request->subscription_id);

        try {
            DB::beginTransaction();

            $method = $request->input('method');

            if ($method === 'Wallet') {
                $customer = $user->customer;
                if (!$customer || $customer->WalletBalance < $request->amount) {
                    throw new \Exception('Insufficient wallet balance (Current Balance: ' . ($customer->WalletBalance ?? 0) . ' EGP).');
                }
                $customer->decrement('WalletBalance', $request->amount);
            }

            // Create Payment Record
            $payment = \App\Models\Payment::create([
                'Method' => $method,
                'Amount' => $request->amount,
                'Status' => 'Completed',
            ]);

            // Link Payment to Subscription
            DB::table('subscription_payments')->insert([
                'PaymentID' => $payment->PaymentID,
                'SubscriptionID' => $sub->SubscriptionID
            ]);

            // Update Subscription Paid Amount (cumulative)
            $sub->PaidAmount += $request->input('amount');

            // If first payment, set DepositAmount
            if ($sub->DepositAmount == 0) {
                $sub->DepositAmount = $request->input('amount');
            }

            // Calculate total and potential status update
            $totalPrice = ($sub->Price ?? 0) + ($sub->DeliveryCharge ?? 0);
            if ($sub->PaidAmount >= $totalPrice) {
                $sub->Status = 'Active';
            } else if ($sub->Status === 'AwaitingPayment' && $sub->PaidAmount > 0) {
                $sub->Status = 'Active'; // Can be active even with deposit
            }

            $sub->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function subscriptionPaymentPage(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->customer) {
            return redirect()->route('login');
        }

        $subscription = Subscription::where('SubscriptionID', $id)
            ->where('CustomerID', $user->customer->CustomerID)
            ->with(['kitchen.user', 'menuItems'])
            ->firstOrFail();

        $amount = $request->query('amount', $subscription->remaining_balance);
        if ($amount <= 0) {
            return redirect()->route('frontend.subscriptions')->with(['message' => 'No payment due.', 'alert-type' => 'info']);
        }

        $walletBalance = $user->customer->WalletBalance ?? 0;

        return view('frontend.subscription_pay', compact('subscription', 'amount', 'walletBalance'));
    }

    // ─── PayMob Subscription Payment ──────────────────────────────────────────
    public function paymobSubscriptionInstallmentWait(Request $request, $id, PaymobService $paymob)
    {
        $user = Auth::user();
        $sub = Subscription::where('SubscriptionID', $id)
            ->where('CustomerID', $user->customer->CustomerID)
            ->with(['kitchen.user'])
            ->firstOrFail();

        $amount = $request->amount;
        if (!$amount || $amount <= 0) {
            return back()->with(['message' => 'Invalid payment amount.', 'alert-type' => 'error']);
        }

        $request->session()->put('paymob_sub_installment', [
            'subscription_id' => $id,
            'amount' => $amount
        ]);
        $request->session()->put('paymob_order_type', 'subscription');

        $token = $paymob->authenticate();
        if (!$token) {
            return back()->with(['message' => 'PayMob authentication failed.', 'alert-type' => 'error']);
        }

        $orderId = $paymob->createOrder($token, (float) $amount, [], $id . time());
        if (!$orderId) {
            return back()->with(['message' => 'PayMob order registration failed.', 'alert-type' => 'error']);
        }

        $phoneRecord = $user->phone ?? ($user->phones ? $user->phones()->first() : null);
        $billingData = [
            'first_name' => $user->FullName ?: 'Guest',
            'last_name' => 'User',
            'email' => $user->Email ?: 'guest@bitehub.com',
            'phone_number' => $phoneRecord ? $phoneRecord->PhoneNumber : '01000000000',
        ];

        $paymentKey = $paymob->getPaymentKey($token, $orderId, (float) $amount, $billingData);
        if (!$paymentKey) {
            return back()->with(['message' => 'PayMob payment key generation failed.', 'alert-type' => 'error']);
        }

        return redirect($paymob->getIframeUrl($paymentKey));
    }

    public function paymobSubscriptionSuccess(Request $request)
    {
        $data = $request->session()->pull('paymob_sub_installment');
        if (!$data) {
            return redirect()->route('frontend.subscriptions')->with(['message' => 'Session expired. Please try again.', 'alert-type' => 'error']);
        }

        $user = Auth::user();
        $id = $data['subscription_id'];
        $amount = $data['amount'];

        $sub = Subscription::where('SubscriptionID', $id)
            ->where('CustomerID', $user->customer->CustomerID)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Create Payment Record
            $payment = \App\Models\Payment::create([
                'Method' => 'Card',
                'Amount' => $amount,
                'Status' => 'Completed',
            ]);

            // Link Payment to Subscription
            DB::table('subscription_payments')->insert([
                'PaymentID' => $payment->PaymentID,
                'SubscriptionID' => $sub->SubscriptionID
            ]);

            // Update Subscription Paid Amount
            $sub->PaidAmount += $amount;

            // If first payment, set DepositAmount
            if ($sub->DepositAmount == 0) {
                $sub->DepositAmount = $amount;
            }

            // Status Update
            $totalPrice = ($sub->Price ?? 0) + ($sub->DeliveryCharge ?? 0);
            if ($sub->PaidAmount >= $totalPrice) {
                $sub->Status = 'Active';
            } else if ($sub->Status === 'AwaitingPayment' && $sub->PaidAmount > 0) {
                $sub->Status = 'Active';
            }
            $sub->save();

            // ── Revenue Split (15 EGP per delivery, 85/15 for vendor/owner) ──
            $totalBase = (float) ($sub->Price ?? 0);
            $totalDel = (float) ($sub->DeliveryCharge ?? 0);
            $totalFullPrice = $totalBase + $totalDel;

            if ($totalFullPrice > 0) {
                $deliveryPortion = $amount * ($totalDel / $totalFullPrice);
                $mealsPortion = $amount - $deliveryPortion;

                $vendorShare = round($mealsPortion * 0.85, 2);
                $ownerShare = round($mealsPortion * 0.15, 2) + round($deliveryPortion, 2);
            } else {
                $vendorShare = round($amount * 0.85, 2);
                $ownerShare = round($amount * 0.15, 2);
            }

            // Credit Vendor (Kitchen)
            $kitchen = \App\Models\KitchenOwner::find($sub->KitchenOwnerID);
            if ($kitchen && $kitchen->user) {
                $kitchen->user->increment('Wallet_balance', $vendorShare);
            }

            // Credit Platform Owner
            $owner = \App\Models\User::where('Role', 'Owner')->first();
            if ($owner) {
                $owner->increment('Wallet_balance', $ownerShare);
            }

            DB::commit();

            // Dispatch all lifetime orders immediately
            $sub->refresh();
            $this->_dispatchSubscriptionOrders($sub);

            return redirect()->route('frontend.subscriptions')->with(['message' => 'PayMob Payment successful! 🎉', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('frontend.subscriptions')->with(['message' => 'Payment processing failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function processSubscriptionPayment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->customer) {
            return redirect()->route('login');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|string|in:Wallet,Card,Cash'
        ]);

        $sub = Subscription::where('SubscriptionID', $id)
            ->where('CustomerID', $user->customer->CustomerID)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $method = $request->input('method');

            if ($method === 'Wallet') {
                $customer = $user->customer;
                if ($customer->WalletBalance < $request->amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }
                $customer->decrement('WalletBalance', $request->amount);
            }

            // Create Payment Record
            $payment = \App\Models\Payment::create([
                'Method' => $method,
                'Amount' => $request->amount,
                'Status' => 'Completed',
            ]);

            // Link Payment to Subscription
            DB::table('subscription_payments')->insert([
                'PaymentID' => $payment->PaymentID,
                'SubscriptionID' => $sub->SubscriptionID
            ]);

            // Update Subscription Paid Amount
            $sub->PaidAmount += $request->amount;

            // If first payment, set DepositAmount
            if ($sub->DepositAmount == 0) {
                $sub->DepositAmount = $request->amount;
            }

            // Status Update
            $totalPrice = ($sub->Price ?? 0) + ($sub->DeliveryCharge ?? 0);
            if ($sub->PaidAmount >= $totalPrice) {
                $sub->Status = 'Active';
            } else if ($sub->Status === 'AwaitingPayment' && $sub->PaidAmount > 0) {
                $sub->Status = 'Active';
            }
            $sub->save();

            // ── Revenue Split (Only for pre-paid digital forms Wallet/Card) ───────────
            if ($method !== 'Cash') {
                $amtPaid = (float) $request->amount;
                $tBase = (float) ($sub->Price ?? 0);
                $tDel = (float) ($sub->DeliveryCharge ?? 0);
                $tFull = $tBase + $tDel;

                if ($tFull > 0) {
                    $delPortion = $amtPaid * ($tDel / $tFull);
                    $mealsPortion = $amtPaid - $delPortion;

                    $vendorShare = round($mealsPortion * 0.85, 2);
                    $ownerShare = round($mealsPortion * 0.15, 2) + round($delPortion, 2);
                } else {
                    $vendorShare = round($amtPaid * 0.85, 2);
                    $ownerShare = round($amtPaid * 0.15, 2);
                }

                // Credit Vendor
                $kitchen = \App\Models\KitchenOwner::find($sub->KitchenOwnerID);
                if ($kitchen && $kitchen->user) {
                    $kitchen->user->increment('Wallet_balance', $vendorShare);
                }

                // Credit Owner
                $owner = \App\Models\User::where('Role', 'Owner')->first();
                if ($owner) {
                    $owner->increment('Wallet_balance', $ownerShare);
                }
            }

            DB::commit();

            // Dispatch all lifetime orders immediately upon activation (no more waiting for midnight)
            if ($sub->Status === 'Active') {
                $sub->refresh();
                $this->_dispatchSubscriptionOrders($sub);
            }

            return redirect()->route('frontend.subscriptions')->with(['message' => 'Payment successful! 🎉', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Payment failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function stripeSubscriptionInstallmentWait(Request $request, $id)
    {
        $user = Auth::user();
        $sub = Subscription::where('SubscriptionID', $id)
            ->where('CustomerID', $user->customer->CustomerID)
            ->with(['kitchen.user'])
            ->firstOrFail();

        $amount = $request->amount;
        if (!$amount || $amount <= 0) {
            return back()->with(['message' => 'Invalid payment amount.', 'alert-type' => 'error']);
        }

        $request->session()->put('stripe_sub_installment', [
            'subscription_id' => $id,
            'amount' => $amount
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $shortTitle = 'Installment for Plan #' . $id . ' (' . ($sub->kitchen->KitchenName ?? 'Kitchen') . ')';

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'egp',
                        'product_data' => ['name' => 'BiteHub ' . $shortTitle],
                        'unit_amount' => (int) ($amount * 100),
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('frontend.stripe.subscription.process.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('frontend.subscription.payment', $id),
        ]);

        return redirect($session->url);
    }

    public function stripeSubscriptionInstallmentSuccess(Request $request)
    {
        $data = $request->session()->pull('stripe_sub_installment');
        if (!$data) {
            return redirect()->route('frontend.subscriptions')->with(['message' => 'Session expired. Please try again.', 'alert-type' => 'error']);
        }

        $user = Auth::user();
        if (!$user || !$user->customer) {
            return redirect()->route('login');
        }

        $sub = Subscription::where('SubscriptionID', $data['subscription_id'])
            ->where('CustomerID', $user->customer->CustomerID)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $payment = \App\Models\Payment::create([
                'Method' => 'Card',
                'Amount' => $data['amount'],
                'Status' => 'Completed',
            ]);

            DB::table('subscription_payments')->insert([
                'PaymentID' => $payment->PaymentID,
                'SubscriptionID' => $sub->SubscriptionID
            ]);

            $sub->PaidAmount += $data['amount'];

            if ($sub->DepositAmount == 0) {
                $sub->DepositAmount = $data['amount'];
            }

            $totalPrice = ($sub->Price ?? 0) + ($sub->DeliveryCharge ?? 0);
            if ($sub->PaidAmount >= $totalPrice) {
                $sub->Status = 'Active';
            } else if ($sub->Status === 'AwaitingPayment' && $sub->PaidAmount > 0) {
                $sub->Status = 'Active';
            }

            $sub->save();

            // ── Revenue Split (Matching payment logic) ───────────────────────
            $pAmt = (float) $data['amount'];
            $tBase = (float) ($sub->Price ?? 0);
            $tDel = (float) ($sub->DeliveryCharge ?? 0);
            $tFull = $tBase + $tDel;

            if ($tFull > 0) {
                $delPortion = $pAmt * ($tDel / $tFull);
                $mealsPortion = $pAmt - $delPortion;

                $vendorShare = round($mealsPortion * 0.85, 2);
                $ownerShare = round($mealsPortion * 0.15, 2) + round($delPortion, 2);
            } else {
                $vendorShare = round($pAmt * 0.85, 2);
                $ownerShare = round($pAmt * 0.15, 2);
            }

            // Credit Vendor
            $kitchen = \App\Models\KitchenOwner::find($sub->KitchenOwnerID);
            if ($kitchen && $kitchen->user) {
                $kitchen->user->increment('Wallet_balance', $vendorShare);
            }

            // Credit Owner
            $owner = \App\Models\User::where('Role', 'Owner')->first();
            if ($owner) {
                $owner->increment('Wallet_balance', $ownerShare);
            }

            DB::commit();

            // Dispatch all lifetime orders immediately
            $sub->refresh();
            $this->_dispatchSubscriptionOrders($sub);

            return redirect()->route('frontend.subscriptions')->with(['message' => 'Stripe Payment successful! 🎉', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('frontend.subscriptions')->with(['message' => 'Payment failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── Dispatch all lifetime delivery orders for a subscription ────────────
    private function _dispatchSubscriptionOrders($sub): void
    {
        try {
            $start = \Carbon\Carbon::parse($sub->StartDate);
            $end = \Carbon\Carbon::parse($sub->EndDate);
            $daysCount = min($start->diffInDays($end), 180);

            $timeSlots = $sub->PreferredTimes ?? [];
            if (empty($timeSlots))
                $timeSlots = ['09:00'];

            $approvedItems = $sub->menuItems()->wherePivot('Status', 'Approved')->get();
            if ($approvedItems->isEmpty())
                return;

            for ($i = 0; $i <= $daysCount; $i++) {
                $date = $start->copy()->addDays($i);
                foreach ($timeSlots as $timeSlot) {
                    $slot = substr(trim($timeSlot), 0, 5);

                    $alreadyDispatched = \App\Models\Order::where('SubscriptionID', $sub->SubscriptionID)
                        ->whereDate('ScheduledDate', $date)
                        ->where('DeliveryTime', $slot)
                        ->exists();

                    if ($alreadyDispatched)
                        continue;

                    $payment = \App\Models\Payment::create(['Method' => 'Online', 'Status' => 'Completed', 'Amount' => 0]);
                    $custAddr = \App\Models\UserAddress::where('UserID', $sub->customer->UserID)->where('IsPrimary', true)->first();
                    $agent = \App\Models\DeliveryAgent::findBestForAddress(
                        $custAddr ? $custAddr->Address : '',
                        $custAddr ? $custAddr->Latitude : null,
                        $custAddr ? $custAddr->Longitude : null
                    );

                    $order = \App\Models\Order::create([
                        'CustomerID' => $sub->CustomerID,
                        'DeliveryAgentID' => $agent ? $agent->DeliveryAgentID : null,
                        'KitchenOwnerID' => $sub->KitchenOwnerID,
                        'PaymentID' => $payment->PaymentID,
                        'SubscriptionID' => $sub->SubscriptionID,
                        'DeliveryTime' => $slot,
                        'ScheduledDate' => $date,
                        'TotalPrice' => 0,
                        'OrderType' => 'Meal Plan',
                        'OrderStatus' => 'Pending',
                        'SpecialRequests' => "📦 Meal Plan #{$sub->SubscriptionID} · Delivery for {$date->toDateString()} · Slot: {$slot}",
                        'DeliveryCode' => str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                    ]);

                    foreach ($approvedItems as $item) {
                        $order->menuItems()->attach($item->MenuItemID, ['Quantity' => 1]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error dispatching subscription orders: ' . $e->getMessage());
        }
    }

    public function requestRefund(Request $request)
    {
        $request->validate([
            'refundable_id' => 'required|integer',
            'refundable_type' => 'required|in:Order,Subscription',
            'reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        if (!$user->customer) {
            return back()->with(['message' => 'Unauthorized action.', 'alert-type' => 'error']);
        }

        $id = $request->refundable_id;
        $type = $request->refundable_type;
        $originalAmount = 0;
        $consumedAmount = 0;
        $refundableAmount = 0;

        if ($type === 'Order') {
            $order = Order::with('payment')->where('OrderID', $id)
                ->where('CustomerID', $user->customer->CustomerID)
                ->firstOrFail();

            // Constraint: Online Payment Only (Commented out for testing)
            /*
            if (!$order->payment || in_array($order->payment->Method, ['Cash'])) {
                return back()->with(['message' => 'Only orders paid online (Visa/Wallet) can be refunded through the system. For cash orders, contact support.', 'alert-type' => 'error']);
            }
            */

            // Rule: Within 3 days
            if (Carbon::parse($order->CreatedAt)->addDays(3)->isPast()) {
                return back()->with(['message' => 'Refund period for this order has expired (3 days limit).', 'alert-type' => 'error']);
            }

            if ($order->OrderStatus === 'Cancelled' || $order->OrderStatus === 'Refunded') {
                return back()->with(['message' => 'Order is already cancelled or refunded.', 'alert-type' => 'warning']);
            }

            $originalAmount = $order->TotalPrice;
            $refundableAmount = $order->TotalPrice;
        } else {
            $sub = Subscription::with('payments')->where('SubscriptionID', $id)
                ->where('CustomerID', $user->customer->CustomerID)
                ->firstOrFail();

            // Constraint: Online Payment Only
            $lastPayment = $sub->payments->last();
            if (!$lastPayment || in_array($lastPayment->Method, ['Cash'])) {
                return back()->with(['message' => 'Only plans paid online can be refunded through the system.', 'alert-type' => 'error']);
            }

            // Pro-rating Logic: Calculate consumed orders
            $totalDays = Carbon::parse($sub->StartDate)->diffInDays(Carbon::parse($sub->EndDate)) ?: 1;
            $dailyRate = $sub->Price / $totalDays;
            
            // Count "used" orders (any order that reached 'Confirmed' or above)
            $consumedOrdersCount = $sub->orders()
                ->whereNotIn('OrderStatus', ['Pending', 'Cancelled'])
                ->count();
            
            $originalAmount = $sub->Price;
            $consumedAmount = $consumedOrdersCount * $dailyRate;
            $refundableAmount = max(0, $originalAmount - $consumedAmount);

            if ($refundableAmount <= 0) {
                return back()->with(['message' => 'This subscription has been fully consumed and is no longer refundable.', 'alert-type' => 'error']);
            }

            if ($sub->Status === 'Cancelled' || $sub->Status === 'Refunded') {
                return back()->with(['message' => 'Subscription is already cancelled or refunded.', 'alert-type' => 'warning']);
            }
        }

        // Check if a request already exists
        $exists = RefundRequest::where('RefundableID', $id)
            ->where('RefundableType', $type)
            ->where('Status', 'Pending')
            ->exists();

        if ($exists) {
            return back()->with(['message' => 'You already have a pending refund request for this item.', 'alert-type' => 'warning']);
        }

        RefundRequest::create([
            'CustomerID' => $user->customer->CustomerID,
            'RefundableID' => $id,
            'RefundableType' => $type,
            'OriginalAmount' => $originalAmount,
            'ConsumedAmount' => $consumedAmount,
            'Amount' => $refundableAmount,
            'Reason' => $request->reason,
            'Status' => 'Pending',
        ]);

        return back()->with(['message' => 'Refund request submitted successfully! Admin will review the pro-rated amount (Refund: ' . number_format($refundableAmount, 2) . ' EGP).', 'alert-type' => 'success']);
    }
}
