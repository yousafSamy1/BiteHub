<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KitchenOwner;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Caterer;
use App\Models\Advertising;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Subscription;
use App\Models\KitchenPlan;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('Email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->Password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->UserID,
                'name' => $user->FullName,
                'email' => $user->Email,
                'role' => $user->Role,
                'image' => $user->Image ? asset('storage/' . $user->Image) : null
            ]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'FullName' => $request->name,
                'Email' => $request->email,
                'Password' => Hash::make($request->password),
                'Role' => 'Customer',
                'Status' => 'Active',
            ]);

            Customer::create([
                'UserID' => $user->UserID,
                'WalletBalance' => 0.00,
            ]);

            DB::commit();

            $token = $user->createToken('mobile_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->UserID,
                    'name' => $user->FullName,
                    'email' => $user->Email,
                    'role' => $user->Role,
                    'image' => null
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Registration failed'], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getHomeData()
    {
        $activeAds = Advertising::with(['kitchenOwner', 'caterer'])
            ->whereIn('Status', ['Active', 'Approved'])
            ->whereDate('StartDate', '<=', now())
            ->whereDate('EndDate', '>=', now())
            ->orderByDesc('AdvertisingID')
            ->get()
            ->map(function($ad) {
                return [
                    'id' => $ad->AdvertisingID,
                    'image' => $ad->BackgroundImage ? asset('storage/' . $ad->BackgroundImage) : null,
                    'title' => $ad->Title,
                    'kitchen_id' => $ad->KitchenOwnerID,
                    'caterer_id' => $ad->CatererID,
                ];
            });

        $categories = Category::where('Status', 'Active')->get()->map(function($cat) {
            return [
                'id' => $cat->CategoryID,
                'name' => $cat->Name,
                'image' => $cat->Image ? asset('storage/' . $cat->Image) : null
            ];
        });

        $kitchens = KitchenOwner::join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
            ->where('kitchen_owners.VerifyStatus', 'Verified')
            ->where('kitchen_owners.Status', 'Active')
            ->select('kitchen_owners.*', 'users.FullName', 'users.Image')
            ->limit(10)
            ->get()
            ->map(function($k) {
                return [
                    'id' => $k->KitchenOwnerID,
                    'name' => $k->KitchenName,
                    'rating' => $k->average_rating,
                    'reviews_count' => $k->review_count,
                    'image' => $k->Image ? asset('storage/' . $k->Image) : null,
                    'status' => $k->current_status,
                ];
            });

        // Popular
        $popular = MenuItem::where('Status', 'Active')
            ->with('images', 'category')
            ->inRandomOrder()->limit(8)->get()
            ->map(function($i) {
                return [
                    'id' => $i->MenuItemID,
                    'name' => $i->ItemName,
                    'price' => $i->Price,
                    'discount_price' => $i->DiscountPrice,
                    'image' => $i->images->first() ? asset('storage/' . $i->images->first()->ImagePath) : null,
                    'category' => $i->category?->Name,
                    'kitchen_id' => $i->KitchenOwnerID,
                ];
            });

        return response()->json([
            'ads' => $activeAds,
            'categories' => $categories,
            'top_kitchens' => $kitchens,
            'popular' => $popular
        ]);
    }

    public function getBrowseData(Request $request)
    {
        $search = $request->query('search');

        $kitchensQuery = KitchenOwner::where('VerifyStatus', 'Verified')
            ->where('Status', 'Active')
            ->with('user');
        
        $caterersQuery = Caterer::where('VerifyStatus', 'Verified')
            ->where('Status', 'Active')
            ->with('user');

        if ($search) {
            $kitchensQuery->where('KitchenName', 'LIKE', '%' . $search . '%');
            $caterersQuery->where('BusinessName', 'LIKE', '%' . $search . '%');
        }

        $kitchens = $kitchensQuery->get()->map(function($k) {
            return [
                'id' => $k->KitchenOwnerID,
                'name' => $k->KitchenName,
                'rating' => $k->average_rating,
                'reviews_count' => $k->review_count,
                'image' => $k->user->Image ? asset('storage/' . $k->user->Image) : null,
                'status' => $k->current_status,
                'is_verified' => true,
            ];
        });

        $caterers = $caterersQuery->get()->map(function($c) {
            return [
                'id' => $c->CatererID,
                'name' => $c->BusinessName,
                'rating' => $c->average_rating,
                'reviews_count' => $c->review_count,
                'image' => $c->user->Image ? asset('storage/' . $c->user->Image) : null,
            ];
        });

        return response()->json([
            'kitchens' => $kitchens,
            'caterers' => $caterers
        ]);
    }

    public function getKitchenDetails($id)
    {
        $kitchen = KitchenOwner::with(['user', 'menuItems.images', 'menuItems.category', 'plans'])
            ->where('KitchenOwnerID', $id)
            ->first();

        if (!$kitchen) {
            return response()->json(['message' => 'Kitchen not found'], 404);
        }

        return response()->json([
            'id' => $kitchen->KitchenOwnerID,
            'name' => $kitchen->KitchenName,
            'description' => $kitchen->Description,
            'image' => $kitchen->user->Image ? asset('storage/' . $kitchen->user->Image) : null,
            'rating' => $kitchen->average_rating,
            'reviews_count' => $kitchen->review_count,
            'status' => $kitchen->current_status,
            'location' => $kitchen->Location,
            'opening_time' => $kitchen->OpeningTime ? Carbon::parse($kitchen->OpeningTime)->format('h:i A') : null,
            'closing_time' => $kitchen->ClosingTime ? Carbon::parse($kitchen->ClosingTime)->format('h:i A') : null,
            'menu' => $kitchen->menuItems->map(function($item) {
                return [
                    'id' => $item->MenuItemID,
                    'name' => $item->ItemName,
                    'price' => $item->Price,
                    'discount_price' => $item->DiscountPrice,
                    'image' => $item->images->first() ? asset('storage/' . $item->images->first()->ImagePath) : null,
                    'category' => $item->category?->Name,
                    'kitchen_id' => $item->KitchenOwnerID,
                ];
            }),
            'plans' => $kitchen->plans->where('Status', 'Active')->map(function($plan) {
                return [
                    'id' => $plan->KitchenPlanID,
                    'name' => $plan->Name,
                    'description' => $plan->Description,
                    'price' => $plan->Price,
                    'duration' => $plan->DurationDays
                ];
            })
        ]);
    }

    public function getCatererDetails($id)
    {
        $caterer = Caterer::with(['user', 'menuItems.images', 'menuItems.category'])
            ->where('CatererID', $id)
            ->first();

        if (!$caterer) {
            return response()->json(['message' => 'Caterer not found'], 404);
        }

        return response()->json([
            'id' => $caterer->CatererID,
            'name' => $caterer->BusinessName,
            'description' => $caterer->Description,
            'image' => $caterer->user->Image ? asset('storage/' . $caterer->user->Image) : null,
            'rating' => $caterer->average_rating,
            'reviews_count' => $caterer->review_count,
            'menu' => $caterer->menuItems->map(function($item) {
                return [
                    'id' => $item->MenuItemID,
                    'name' => $item->ItemName,
                    'price' => $item->Price,
                    'discount_price' => $item->DiscountPrice,
                    'image' => $item->images->first() ? asset('storage/' . $item->images->first()->ImagePath) : null,
                    'category' => $item->category?->Name,
                    'caterer_id' => $item->CatererID,
                ];
            })
        ]);
    }

    // --- Auth Routes ---

    public function getProfile(Request $request)
    {
        $user = $request->user();
        $customer = clone current($user->customer()->getModels());
        
        if(!$customer && $user->Role == 'Customer'){
            $customer = Customer::create(['UserID' => $user->UserID]);
        }

        return response()->json([
            'id' => $user->UserID,
            'name' => $user->FullName,
            'email' => $user->Email,
            'wallet_balance' => $customer?->WalletBalance ?? 0,
            'loyalty_points' => $customer?->LoyaltyPoints ?? 0,
            'total_orders' => Order::where('CustomerID', $customer?->CustomerID)->count(),
            'image' => $user->Image ? asset('storage/' . $user->Image) : null
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->FullName = $request->full_name ?? $user->FullName;
        if($request->has('phone')) {
            $user->PhoneNumber = $request->phone;
        }
        $user->save();

        return response()->json(['message' => 'Profile updated']);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->Password)) {
            return response()->json(['message' => 'Current password incorrect'], 422);
        }

        $user->Password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated']);
    }

    public function getMyOrders(Request $request)
    {
        $customer = $request->user()->customer;
        if (!$customer) return response()->json([]);

        $orders = Order::where('CustomerID', $customer->CustomerID)
            ->with(['kitchen', 'caterer'])
            ->orderBy('CreatedAt', 'desc')
            ->get()->map(function($o) {
                return [
                    'id' => $o->OrderID,
                    'order_number' => '#' . str_pad($o->OrderID, 5, '0', STR_PAD_LEFT),
                    'status' => $o->OrderStatus,
                    'total' => $o->TotalPrice,
                    'createdAt' => Carbon::parse($o->CreatedAt)->format('Y-m-d h:i A'),
                    'items' => [], // summary
                ];
            });

        return response()->json($orders);
    }

    public function getOrderDetail(Request $request, $id)
    {
        $customer = $request->user()->customer;
        $order = Order::where('OrderID', $id)->where('CustomerID', $customer->CustomerID)
            ->with(['orderItems.menuItem.images'])
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'id' => $order->OrderID,
            'order_number' => '#' . str_pad($order->OrderID, 5, '0', STR_PAD_LEFT),
            'status' => $order->OrderStatus,
            'total' => $order->TotalPrice,
            'address' => $order->DeliveryAddress,
            'createdAt' => Carbon::parse($order->CreatedAt)->format('Y-m-d h:i A'),
            'items' => $order->orderItems->map(function($oi) {
                $img = $oi->menuItem && $oi->menuItem->images->first() ? asset('storage/' . $oi->menuItem->images->first()->ImagePath) : null;
                return [
                    'name' => $oi->menuItem ? $oi->menuItem->ItemName : 'Unknown Item',
                    'quantity' => $oi->Quantity,
                    'price' => $oi->PriceAtOrder,
                    'image' => $img,
                ];
            })
        ]);
    }

    public function placeOrder(Request $request)
    {
        $customer = $request->user()->customer;
        $items = $request->items;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'CustomerID' => $customer->CustomerID,
                'KitchenOwnerID' => $request->kitchen_id,
                'CatererID' => $request->caterer_id,
                'OrderStatus' => 'Pending',
                'TotalPrice' => $request->total,
                'DeliveryAddress' => $request->address,
                'PaymentMethod' => $request->payment_method ?? 'COD',
                'PaymentStatus' => 'Pending',
                'OrderNotes' => $request->note,
            ]);

            foreach ($items as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                if ($menuItem) {
                    OrderItem::create([
                        'OrderID' => $order->OrderID,
                        'MenuItemID' => $menuItem->MenuItemID,
                        'Quantity' => $item['quantity'],
                        'PriceAtOrder' => $menuItem->DiscountPrice ?? $menuItem->Price ?? 0,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Order placed', 'order_id' => $order->OrderID]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function getSubscriptionPlans(Request $request)
    {
        // ... (Not fully implemented yet, sending empty for now)
        return response()->json([]);
    }

    public function getMySubscriptions(Request $request)
    {
        $customer = $request->user()->customer;
        if (!$customer) return response()->json([]);

        $subs = Subscription::where('CustomerID', $customer->CustomerID)
            ->with(['kitchenPlan.kitchen', 'kitchen'])
            ->get()->map(function($s) {
                return [
                    'id' => $s->SubscriptionID,
                    'planTime' => $s->PlanTime,
                    'status' => $s->Status,
                    'price' => $s->Price,
                    'startDate' => $s->StartDate ? Carbon::parse($s->StartDate)->format('Y-m-d') : null,
                    'endDate' => $s->EndDate ? Carbon::parse($s->EndDate)->format('Y-m-d') : null,
                    'kitchenName' => $s->kitchenPlan ? ($s->kitchenPlan->kitchen->KitchenName ?? null) : ($s->kitchen->KitchenName ?? null),
                    'planTitle' => $s->kitchenPlan->Name ?? null,
                ];
            });

        return response()->json($subs);
    }

    public function cancelSubscription(Request $request, $id)
    {
        $sub = Subscription::find($id);
        if ($sub) {
            $sub->Status = 'Cancelled';
            $sub->save();
        }
        return response()->json(['message' => 'Cancelled']);
    }

    public function getTickets(Request $request)
    {
        $tickets = SupportTicket::where('UserID', $request->user()->UserID)
            ->orderBy('CreatedAt', 'desc')
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->TicketID,
                    'subject' => $t->Subject,
                    'status' => $t->Status,
                    'lastMessage' => null, 
                    'createdAt' => Carbon::parse($t->CreatedAt)->format('Y-m-d'),
                ];
            });
        return response()->json($tickets);
    }

    public function createTicket(Request $request)
    {
        SupportTicket::create([
            'UserID' => $request->user()->UserID,
            'Subject' => $request->subject,
            'Message' => $request->message ?? '',
            'Status' => 'Open',
        ]);
        return response()->json(['message' => 'Created']);
    }
}
