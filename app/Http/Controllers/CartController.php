<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\MenuOrderItem;
use App\Models\LoyaltyTransaction;
use App\Models\LiveChat;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Services\PaymobService;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CartController extends Controller
{
    public function show()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with(['message' => 'Please sign up or login to access your cart.', 'alert-type' => 'warning']);
        }

        $walletBalance = 0;
        $loyaltyPoints = 0;
        $pendingRequests = [];
        $approvedRequests = [];
        $addresses = [];

        if (Auth::check()) {
            $customer = Customer::where('UserID', Auth::user()->UserID)->first();
            if ($customer) {
                $walletBalance = $customer->WalletBalance;
                $loyaltyPoints = LoyaltyTransaction::where('CustomerID', $customer->CustomerID)
                    ->selectRaw('SUM(CASE WHEN Type IN ("Earned","Bonus","Referral") THEN Points ELSE -Points END) as total')
                    ->value('total') ?? 0;
            }

            $pendingRequests = LiveChat::where('SenderID', Auth::id())->whereNull('OrderID')->where('Type', 'request')->with('menuItem')->get();
            $approvedRequests = LiveChat::where('SenderID', Auth::id())->whereNull('OrderID')->where('Type', 'approved')->with('menuItem')->get();
            $addresses = \App\Models\UserAddress::where('UserID', Auth::id())->orderByDesc('IsPrimary')->get();
        }
        return view('frontend.cart', compact('walletBalance', 'loyaltyPoints', 'pendingRequests', 'approvedRequests', 'addresses'));
    }

    public function getCustomizationCount()
    {
        if (!Auth::check()) return response()->json(['count' => 0]);
        $pending = LiveChat::where('SenderID', Auth::id())->whereNull('OrderID')->where('Type', 'request')->count();
        $approved = LiveChat::where('SenderID', Auth::id())->whereNull('OrderID')->where('Type', 'approved')->count();
        return response()->json(['count' => $pending + $approved]);
    }

    public function markCustomizationUsed($id)
    {
        $chat = LiveChat::where('LiveChatID', $id)->where('SenderID', Auth::id())->whereNull('OrderID')->where('Type', 'approved')->firstOrFail();
        $chat->update(['Type' => 'added_to_cart']);
        return response()->json(['status' => 'ok']);
    }

    public function deleteCustomizationRequest($id)
    {
        $chat = LiveChat::where('LiveChatID', $id)->where('SenderID', Auth::id())->whereNull('OrderID')->whereIn('Type', ['request', 'approved'])->firstOrFail();
        if ($chat->SessionID) LiveChat::where('SessionID', $chat->SessionID)->delete();
        else $chat->delete();
        return response()->json(['status' => 'ok']);
    }

    // ─── Cash / Wallet checkout ───────────────────────────────────────────────
    public function placeOrder(Request $request)
    {
        $request->validate([
            'total'      => 'required|numeric',
            'payment'    => 'required|string',
            'address'    => 'required|string',
            'cart_items' => 'required|string',
        ]);

        $cartItems = json_decode($request->cart_items, true) ?? [];
        $vendorError = $this->_checkVendorsOpen($cartItems);
        if ($vendorError) return back()->with(['message' => $vendorError, 'alert-type' => 'error']);

        $user = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();
        if (!$customer) return back()->with(['message' => 'Customer profile not found.', 'alert-type' => 'error']);

        $total      = (float) $request->total;
        $pointsUsed = (int) max(0, $request->input('points_used', 0));

        if ($pointsUsed > 0) {
            $currentBalance = LoyaltyTransaction::where('CustomerID', $customer->CustomerID)->selectRaw('SUM(CASE WHEN Type IN ("Earned","Bonus","Referral") THEN Points ELSE -Points END) as total')->value('total') ?? 0;
            if ($pointsUsed > $currentBalance) return back()->with(['message' => 'Insufficient BitePoints.', 'alert-type' => 'error']);
        }

        if ($request->payment === 'Wallet') {
            if ($customer->WalletBalance < $total) return back()->with(['message' => 'Insufficient wallet balance.', 'alert-type' => 'error']);
            $customer->decrement('WalletBalance', $total);
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create(['Method' => $request->payment, 'Status' => ($request->payment === 'Wallet' ? 'Completed' : 'Pending')]);
            $orderIds = $this->_createSplitOrders($customer, $cartItems, $payment->PaymentID, [
                'address'          => $request->address,
                'special_requests' => $request->special_requests ?? '',
                'is_deposit'       => $request->is_deposit ?? 0,
                'points_used'      => $pointsUsed,
                'promo_code'       => $request->session()->get('applied_promo_code'),
            ]);

            // Clear promo code from session after successful order
            $request->session()->forget('applied_promo_code');

            DB::commit();
            $idString = implode(',', $orderIds);
            return redirect()->route('frontend.tracking', ['id' => $idString])->with(['message' => 'Order(s) placed successfully! 🎉', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Checkout Error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return back()->with(['message' => 'Checkout failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── PayMob session creation ───────────────────────────────────────────────
    public function paymobCheckout(Request $request, PaymobService $paymob)
    {
        $request->validate(['total' => 'required|numeric', 'address' => 'required|string', 'cart_items' => 'required|string']);
        $cartItemsArr = json_decode($request->cart_items, true) ?? [];
        $vendorError = $this->_checkVendorsOpen($cartItemsArr);
        if ($vendorError) return back()->with(['message' => $vendorError, 'alert-type' => 'error']);

        $request->session()->put('paymob_order', [
            'total' => $request->total, 'address' => $request->address, 'cart_items' => $request->cart_items,
            'special_requests' => $request->special_requests ?? '', 'is_deposit' => $request->is_deposit ?? 0,
            'points_used' => $request->points_used ?? 0
        ]);
        $request->session()->put('paymob_order_type', 'order');

        $token = $paymob->authenticate();
        if (!$token) return back()->with(['message' => 'PayMob auth failed.', 'alert-type' => 'error']);

        $user = Auth::user();
        $merchantOrderId = 'ORD_' . time() . '_' . $user->UserID;

        $orderId = $paymob->createOrder($token, (float) $request->total, [], $merchantOrderId);
        if (!$orderId) return back()->with(['message' => 'PayMob order creation failed.', 'alert-type' => 'error']);

        $user = Auth::user();
        $billingData = ['first_name' => $user->FullName ?: 'Guest', 'last_name' => 'User', 'email' => $user->Email ?: 'guest@bitehub.com', 'phone_number' => ($user->phone->PhoneNumber ?? '01000000000')];
        $paymentKey = $paymob->getPaymentKey($token, $orderId, (float) $request->total, $billingData);
        if (!$paymentKey) return back()->with(['message' => 'PayMob payment key failed.', 'alert-type' => 'error']);

        return redirect($paymob->getIframeUrl($paymentKey));
    }

    public function paymobSuccess(Request $request)
    {
        $data = $request->session()->pull('paymob_order');
        if (!$data) return redirect()->route('frontend.home')->with(['message' => 'Session expired.']);

        $user = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();
        if (!$customer) return redirect()->route('frontend.home');

        DB::beginTransaction();
        try {
            $payment = Payment::create(['Method' => 'Card', 'Status' => 'Completed']);
            $data['promo_code'] = $request->session()->get('applied_promo_code');
            $orderIds = $this->_createSplitOrders($customer, json_decode($data['cart_items'], true), $payment->PaymentID, $data);
            $request->session()->forget('applied_promo_code');
            DB::commit();
            return redirect()->route('frontend.tracking', ['id' => implode(',', $orderIds)])->with(['message' => 'Payment successful! 🎉', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('frontend.cart')->with(['message' => 'Checkout failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    // ─── Stripe session creation ───────────────────────────────────────────────
    public function stripeCheckout(Request $request)
    {
        $request->validate(['total' => 'required|numeric', 'address' => 'required|string', 'cart_items' => 'required|string']);
        $cartItemsArr = json_decode($request->cart_items, true) ?? [];
        $vendorError = $this->_checkVendorsOpen($cartItemsArr);
        if ($vendorError) return back()->with(['message' => $vendorError, 'alert-type' => 'error']);

        $request->session()->put('stripe_order', [
            'total' => $request->total, 'address' => $request->address, 'cart_items' => $request->cart_items,
            'special_requests' => $request->special_requests ?? '', 'is_deposit' => $request->is_deposit ?? 0,
            'points_used' => $request->points_used ?? 0
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [['price_data' => ['currency' => 'egp', 'product_data' => ['name' => 'BiteHub Order'], 'unit_amount' => (int)round($request->total * 100)], 'quantity' => 1]],
            'mode' => 'payment', 'success_url' => route('frontend.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}', 'cancel_url' => route('frontend.stripe.cancel')
        ]);
        return redirect($session->url);
    }

    public function stripeSuccess(Request $request)
    {
        $data = $request->session()->pull('stripe_order');
        if (!$data) return redirect()->route('frontend.home')->with(['message' => 'Session expired.']);

        $user = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();
        if (!$customer) return redirect()->route('frontend.home');

        DB::beginTransaction();
        try {
            $payment = Payment::create(['Method' => 'Card', 'Status' => 'Completed']);
            $data['promo_code'] = $request->session()->get('applied_promo_code');
            $orderIds = $this->_createSplitOrders($customer, json_decode($data['cart_items'], true), $payment->PaymentID, $data);
            $request->session()->forget('applied_promo_code');
            DB::commit();
            return redirect()->route('frontend.tracking', ['id' => implode(',', $orderIds)])->with(['message' => 'Payment successful! 🎉', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('frontend.cart')->with(['message' => 'Checkout failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function stripeCancel()
    {
        return redirect()->route('frontend.cart')->with(['message' => 'Payment cancelled.', 'alert-type' => 'warning']);
    }

    public function reorder(Request $request, $id)
    {
        $user = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();
        if (!$customer) return redirect()->route('login');

        $order = Order::with('menuItems.images')->where('OrderID', $id)->where('CustomerID', $customer->CustomerID)->firstOrFail();
        $cartItems = []; $total = 0;
        foreach ($order->menuItems as $item) {
            $price = $item->DiscountPrice ?? $item->ItemPrice;
            $qty = $item->pivot->Quantity ?? 1;
            $cartItems[] = ['id' => $item->MenuItemID, 'name' => $item->ItemName, 'price' => round((float)$price, 2), 'qty' => (int)$qty];
            $total += round((float)$price, 2) * (int)$qty;
        }
        if (empty($cartItems)) return redirect()->route('frontend.home')->with(['message' => 'No items to reorder.', 'alert-type' => 'error']);

        $request->session()->put('stripe_order', [
            'total' => round($total + 15, 2), 'address' => \App\Models\UserAddress::where('UserID', $user->UserID)->orderByDesc('IsPrimary')->value('Address') ?? 'N/A',
            'cart_items' => json_encode($cartItems), 'special_requests' => 'Reorder of #' . $order->OrderID, 'is_deposit' => 0, 'points_used' => 0
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [['price_data' => ['currency' => 'egp', 'product_data' => ['name' => 'BiteHub Reorder #' . $order->OrderID], 'unit_amount' => (int)round(($total + 15) * 100)], 'quantity' => 1]],
            'mode' => 'payment', 'success_url' => route('frontend.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}', 'cancel_url' => route('frontend.stripe.cancel')
        ]);
        return redirect($session->url);
    }

    public function applyPromoCode(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $promo = PromoCode::where('Code', strtoupper(trim($request->code)))->first();
        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Invalid promo code.']);
        }

        // Calculate applicable subtotal
        $cart = $request->input('cart_items', []);
        $applicableSubtotal = 0;
        
        if ($promo->KitchenOwnerID) {
            foreach ($cart as $details) {
                if (!empty($details['is_subscription'])) {
                    $sub = \App\Models\Subscription::find($details['subscription_id']);
                    if ($sub && $sub->KitchenOwnerID == $promo->KitchenOwnerID) {
                        $applicableSubtotal += $details['price'] * $details['qty'];
                    }
                } else {
                    $mi = \App\Models\MenuItem::find((int)$details['id']);
                    if ($mi && $mi->KitchenOwnerID == $promo->KitchenOwnerID) {
                        $applicableSubtotal += $details['price'] * $details['qty'];
                    }
                }
            }
        } elseif ($promo->CatererID) {
            foreach ($cart as $details) {
                if (empty($details['is_subscription'])) {
                    $mi = \App\Models\MenuItem::find((int)$details['id']);
                    if ($mi && $mi->CatererID == $promo->CatererID) {
                        $applicableSubtotal += $details['price'] * $details['qty'];
                    }
                }
            }
        } else {
            foreach ($cart as $details) {
                $applicableSubtotal += $details['price'] * $details['qty'];
            }
        }

        if ($applicableSubtotal <= 0) {
            return response()->json(['success' => false, 'message' => 'This promo code does not apply to any items in your cart.']);
        }

        if (!$promo->isValid($applicableSubtotal)) {
            $msg = 'This promo code is not valid.';
            if ($promo->ExpiryDate && \Carbon\Carbon::parse($promo->ExpiryDate)->isPast()) $msg = 'This promo code has expired.';
            elseif ($promo->MaxUses !== null && $promo->UsedCount >= $promo->MaxUses) $msg = 'This promo code has reached its usage limit.';
            elseif ($applicableSubtotal < $promo->MinOrderAmount) $msg = 'Minimum order amount of ' . number_format($promo->MinOrderAmount, 2) . ' EGP is required for the applicable items.';
            elseif (!$promo->IsActive) $msg = 'This promo code is inactive.';
            return response()->json(['success' => false, 'message' => $msg]);
        }

        $discount = $promo->getDiscountAmount($applicableSubtotal);
        $request->session()->put('applied_promo_code', $promo->Code);

        return response()->json([
            'success'  => true,
            'message'  => 'Promo code applied! You save ' . number_format($discount, 2) . ' EGP.',
            'discount' => round($discount, 2),
            'code'     => $promo->Code,
            'type'     => $promo->Type,
            'value'    => $promo->Value,
            'min_order_amount' => (float)$promo->MinOrderAmount,
        ]);
    }

    public function removePromoCode(Request $request)
    {
        $request->session()->forget('applied_promo_code');
        return response()->json(['success' => true]);
    }

    private function _createSplitOrders($customer, $cartItems, $paymentId, $data)
    {
        $sessionToken = Str::uuid()->toString();
        $isDeposit = ($data['is_deposit'] ?? 0) == 1;
        $pointsUsed = (int)($data['points_used'] ?? 0);
        $pointsDisc = round($pointsUsed / 100, 2); // 100 points = 1 EGP
        $specialReqBase = trim(($data['special_requests'] ?? '') . "\nDelivery: " . ($data['address'] ?? 'N/A'));

        // Resolve promo code if present
        $promoCode = null;
        if (!empty($data['promo_code'])) {
            $promoCode = PromoCode::where('Code', $data['promo_code'])->first();
        }

        $groups = [];
        foreach ($cartItems as $ci) {
            $vKey = 'unknown';
            if (!empty($ci['is_subscription'])) {
                $sub = \App\Models\Subscription::find($ci['subscription_id']);
                if ($sub) $vKey = 'k_' . $sub->KitchenOwnerID;
            } else {
                $mi = MenuItem::find(intval($ci['id']));
                if ($mi) $vKey = $mi->KitchenOwnerID ? 'k_' . $mi->KitchenOwnerID : ($mi->CatererID ? 'c_' . $mi->CatererID : 'unknown');
            }
            $groups[$vKey][] = $ci;
        }

        $orderIds = [];
        $totalSubtotal = array_sum(array_map(fn($g) => array_reduce($g, fn($c, $i) => $c + ($i['price'] * ($i['qty'] ?? 1)), 0), $groups));

        foreach ($groups as $vKey => $items) {
            $parts = explode('_', $vKey);
            $kitchenId = ($parts[0] === 'k') ? $parts[1] : null;
            $catererId = ($parts[0] === 'c') ? $parts[1] : null;

            $subtotal = array_reduce($items, fn($c, $i) => $c + ($i['price'] * ($i['qty'] ?? 1)), 0);
            $deliveryFee = 15.00;
            $thisOrderPointsDisc = $totalSubtotal > 0 ? round($pointsDisc * ($subtotal / $totalSubtotal), 2) : 0;
            
            // Proportionally split platform promo discount, or apply fully if vendor promo matches
            $thisOrderPromoDisc = 0;
            if ($promoCode) {
                if ($promoCode->KitchenOwnerID) {
                    if ($promoCode->KitchenOwnerID == $kitchenId) {
                        $thisOrderPromoDisc = $promoCode->getDiscountAmount($subtotal);
                    }
                } elseif ($promoCode->CatererID) {
                    if ($promoCode->CatererID == $catererId) {
                        $thisOrderPromoDisc = $promoCode->getDiscountAmount($subtotal);
                    }
                } else {
                    $thisOrderPromoDisc = $totalSubtotal > 0 ? round($promoCode->getDiscountAmount($totalSubtotal) * ($subtotal / $totalSubtotal), 2) : 0;
                }
            }
            
            $undiscountedTotal = $subtotal + $deliveryFee;

            $agent = null;
            $userAddr = \App\Models\UserAddress::where('UserID', $customer->UserID)->where('Address', $data['address'] ?? '')->first();
            
            $agent = \App\Models\DeliveryAgent::findBestForAddress(
                $data['address'] ?? '', 
                $userAddr ? $userAddr->Latitude : null, 
                $userAddr ? $userAddr->Longitude : null
            );

            $order = Order::create([
                'CustomerID' => $customer->CustomerID, 'DeliveryAgentID' => $agent ? $agent->DeliveryAgentID : null,
                'KitchenOwnerID' => $kitchenId, 'CatererID' => $catererId,
                'KitchenOrderNumber' => $kitchenId ? Order::where('KitchenOwnerID', $kitchenId)->count() + 1 : (Order::where('CatererID', $catererId)->count() + 1),
                'TotalPrice' => $undiscountedTotal, 'PointsDiscount' => $thisOrderPointsDisc, 'PaymentID' => $paymentId,
                'PromoCode'     => $promoCode ? $promoCode->Code : null,
                'PromoDiscount' => $thisOrderPromoDisc,
                'OrderType' => !empty($items[0]['is_subscription']) ? 'Meal Plan' : ($catererId ? 'Catering' : 'Order'),
                'OrderStatus' => 'Pending', 'SpecialRequests' => $specialReqBase . "\n[Session: $sessionToken]",
                'LoyaltyPoints' => (int)floor($undiscountedTotal), 'DeliveryCode' => str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
            ]);
            $orderIds[] = $order->OrderID;

            // Send Notifications
            Notification::notify($customer->UserID, 'Order Placed', "Your order #{$order->OrderID} has been placed successfully.", 'Order');

            if ($kitchenId) {
                $ko = \App\Models\KitchenOwner::find($kitchenId);
                if ($ko) Notification::notify($ko->UserID, 'New Order Received', "You have a new order #{$order->KitchenOrderNumber} from {$customer->user->FullName}.", 'Order');
            } elseif ($catererId) {
                $ct = \App\Models\Caterer::find($catererId);
                if ($ct) Notification::notify($ct->UserID, 'New Catering Order', "You have a new catering order #{$order->KitchenOrderNumber} from {$customer->user->FullName}.", 'Order');
            }

            if ($agent) {
                Notification::notify($agent->UserID, 'New Delivery Assigned', "You have been assigned to delivery #{$order->OrderID}.", 'Order');
            }

            // Notify Admins
            $admins = User::whereIn('Role', ['Admin', 'Owner'])->get();
            foreach ($admins as $admin) {
                Notification::notify($admin->UserID, 'New Platform Order', "A new order #{$order->OrderID} was placed by {$customer->user->FullName}.", 'Order');
            }

            $payment = \App\Models\Payment::find($paymentId);
            // Immediate wallet credit for online/wallet payments (Subtotal only, delivery fee stays in platform until delivery)
            if ($payment && $payment->Status === 'Completed') {
                $vendorUser = null;
                if ($kitchenId) {
                    $ko = \App\Models\KitchenOwner::find($kitchenId);
                    $vendorUser = $ko ? $ko->user : null;
                } elseif ($catererId) {
                    $ct = \App\Models\Caterer::find($catererId);
                    $vendorUser = $ct ? $ct->user : null;
                }

                if ($vendorUser) {
                    $commissionRate = 0.15; // 15% site commission
                    $vendorRate = 0.85; // 85% vendor share
                    $itemPrice = (float)$subtotal * $vendorRate;
                    $vendorUser->increment('Wallet_balance', $itemPrice);

                    // Transit delivery fee + Site Share (15%) to Platform Owner
                    $owner = User::where('Role', 'Admin')->first();
                    if ($owner) {
                        $siteShare = (float)$subtotal * $commissionRate;
                        $owner->increment('Wallet_balance', 15.00 + $siteShare);
                        
                        // SYSTEM PAYS FOR POINTS: Deduct discount from Platform Profit
                        if ($thisOrderPointsDisc > 0) {
                            $owner->decrement('Wallet_balance', $thisOrderPointsDisc);
                        }
                        // Deduct PROMO discount from appropriate party
                        if ($thisOrderPromoDisc > 0) {
                            if ($promoCode && ($promoCode->KitchenOwnerID || $promoCode->CatererID)) {
                                $vendorUser->decrement('Wallet_balance', $thisOrderPromoDisc);
                            } else {
                                $owner->decrement('Wallet_balance', $thisOrderPromoDisc);
                            }
                        }
                    }
                }
            }


            foreach ($items as $ci) {
                if (!empty($ci['is_subscription'])) {
                    $sub = \App\Models\Subscription::find($ci['subscription_id']);
                    if ($sub) {
                        $paid = $isDeposit ? ($ci['price'] * 0.2) : $ci['price'];
                        $sub->update(['Status' => 'Active', 'DepositAmount' => $paid]);
                        DB::table('subscription_payments')->insert(['SubscriptionID' => $sub->SubscriptionID, 'PaymentID' => $paymentId]);
                    }
                    continue;
                }
                if (!empty($ci['session_id'])) {
                    LiveChat::where('SessionID', $ci['session_id'])->whereNull('OrderID')->update(['OrderID' => $order->OrderID]);
                }
                MenuOrderItem::create(['MenuItemID' => intval($ci['id']), 'OrderID' => $order->OrderID, 'Quantity' => intval($ci['qty'] ?? 1)]);
            }
        }
        if ($pointsUsed > 0) {
            LoyaltyTransaction::create(['CustomerID' => $customer->CustomerID, 'Type' => 'Redeemed', 'Points' => $pointsUsed, 'Description' => 'Multi-order split']);
        }
        // Mark promo code as used
        if ($promoCode) {
            $promoCode->increment('UsedCount');
        }
        return $orderIds;
    }

    private function _checkVendorsOpen($cartItems)
    {
        foreach ($cartItems as $ci) {
            if (!empty($ci['is_subscription'])) {
                $sub = \App\Models\Subscription::with('kitchen')->find($ci['subscription_id']);
                if ($sub && $sub->kitchen && $sub->kitchen->current_status === 'Closed') return "Sorry, {$sub->kitchen->KitchenName} is closed.";
                continue;
            }
            $mi = MenuItem::with(['kitchenOwner', 'caterer'])->find(intval($ci['id']));
            if ($mi) {
                if ($mi->KitchenOwnerID && $mi->kitchenOwner && $mi->kitchenOwner->current_status === 'Closed') return "Sorry, {$mi->kitchenOwner->KitchenName} is closed.";
                if ($mi->CatererID && $mi->caterer && $mi->caterer->current_status === 'Closed') return "Sorry, {$mi->caterer->FullName} is closed.";
            }
        }
        return null;
    }
}
