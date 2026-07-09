<?php

namespace App\Http\Controllers;

use App\Models\LiveChat;
use App\Models\Order;
use App\Models\KitchenOwner;
use App\Models\Caterer;
use App\Models\MenuItem;
use App\Models\MenuOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    // ─── Ownership checks ─────────────────────────────────────────────────────
    private function kitchenOwnsOrder(Order $order): bool
    {
        $kitchen = KitchenOwner::where('UserID', Auth::user()->UserID)->first();
        if (!$kitchen)
            return false;
        $kitchenItemIds = MenuItem::where('KitchenOwnerID', $kitchen->KitchenOwnerID)->pluck('MenuItemID');
        return MenuOrderItem::where('OrderID', $order->OrderID)
            ->whereIn('MenuItemID', $kitchenItemIds)->exists();
    }

    private function catererOwnsOrder(Order $order): bool
    {
        $caterer = Caterer::where('UserID', Auth::user()->UserID)->first();
        if (!$caterer)
            return false;
        $catererItemIds = MenuItem::where('CatererID', $caterer->CatererID)->pluck('MenuItemID');
        return MenuOrderItem::where('OrderID', $order->OrderID)
            ->whereIn('MenuItemID', $catererItemIds)->exists();
    }

    // ─── Kitchen: View Chat ───────────────────────────────────────────────────
    public function kitchenOrderChat($orderId)
    {
        $order = Order::findOrFail($orderId);
        abort_unless($this->kitchenOwnsOrder($order), 403);
        $messages = LiveChat::where('OrderID', $orderId)->with('sender')->orderBy('LiveChatID')->get();
        return view('admin.kitchen.chat.order', compact('order', 'messages'));
    }

    public function kitchenSendMessage(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        abort_unless($this->kitchenOwnsOrder($order), 403);
        $request->validate(['message' => 'required|string|max:1000']);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return back()->with(['message' => 'Message contains prohibited language.', 'alert-type' => 'error']);
        }

        $receiverId = $order->customer->user->UserID ?? null;

        LiveChat::create([
            'OrderID' => $orderId,
            'SenderID' => Auth::user()->UserID,
            'ReceiverID' => $receiverId,
            'Message' => $request->message,
            'Type' => $request->type ?? 'message',
            'Timestamp' => now(),
        ]);
        return back()->with(['message' => 'Message sent.', 'alert-type' => 'success']);
    }

    public function kitchenApproveRequest(Request $request, $chatId)
    {
        $msg = LiveChat::findOrFail($chatId);
        $extraCharge = (float) ($request->extra_charge ?? 0);
        $approvalMsg = $request->approval_message;

        if ($msg->OrderID) {
            $order = Order::findOrFail($msg->OrderID);
            abort_unless($this->kitchenOwnsOrder($order), 403);
            if ($extraCharge > 0) {
                $order->increment('TotalPrice', $extraCharge);
                $extraPoints = (int) floor($extraCharge / 10);
                if ($extraPoints > 0) {
                    $order->increment('LoyaltyPoints', $extraPoints);
                }
            }
        } else {
            // Pre-order request: check item ownership or Admin role
            $item = $msg->MenuItemID > 0 ? MenuItem::find($msg->MenuItemID) : null;
            if (Auth::user()->Role !== 'Admin') {
                $kitchen = KitchenOwner::where('UserID', Auth::user()->UserID)->firstOrFail();
                if ($item) {
                    abort_unless($item->KitchenOwnerID == $kitchen->KitchenOwnerID, 403);
                } else {
                    abort_unless($msg->ReceiverID == Auth::user()->UserID, 403);
                }
            }
        }

        $msg->update(['Type' => 'approved', 'ExtraCharge' => $extraCharge]);

        if (!empty($approvalMsg)) {
            LiveChat::create([
                'MenuItemID' => $msg->MenuItemID,
                'SessionID' => $msg->SessionID,
                'SenderID' => Auth::id(),
                'ReceiverID' => $msg->SenderID,
                'Message' => $approvalMsg,
                'Type' => 'message',
                'ExtraCharge' => $extraCharge,
                'Timestamp' => now(),
            ]);
        }

        $note = $extraCharge > 0 ? " Extra charge of {$extraCharge} EGP added." : '';
        return back()->with(['message' => 'Request approved.' . $note, 'alert-type' => 'success']);
    }

    public function kitchenRejectRequest($chatId)
    {
        $msg = LiveChat::findOrFail($chatId);
        if ($msg->OrderID) {
            $order = Order::findOrFail($msg->OrderID);
            abort_unless($this->kitchenOwnsOrder($order), 403);
        } else {
            $item = $msg->MenuItemID > 0 ? MenuItem::find($msg->MenuItemID) : null;
            if (Auth::user()->Role !== 'Admin') {
                $kitchen = KitchenOwner::where('UserID', Auth::user()->UserID)->firstOrFail();
                if ($item) {
                    abort_unless($item->KitchenOwnerID == $kitchen->KitchenOwnerID, 403);
                } else {
                    abort_unless($msg->ReceiverID == Auth::user()->UserID, 403);
                }
            }
        }
        $msg->update(['Type' => 'rejected']);
        return back()->with(['message' => 'Request rejected.', 'alert-type' => 'warning']);
    }

    // ─── Pre-order Customization ──────────────────────────────────────────────
    public function sendPreOrderRequest(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|integer', // Can be 0 for direct kitchen request
            'kitchen_id' => 'nullable|integer',
            'caterer_id' => 'nullable|integer',
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:36',
        ]);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return response()->json(['status' => 'error', 'message' => 'Message contains prohibited language.'], 403);
        }

        $userId = Auth::id();
        $menuItemId = intval($request->menu_item_id);
        $receiverId = null;

        if ($menuItemId > 0) {
            $item = MenuItem::findOrFail($menuItemId);
            $receiverId = $item->KitchenOwnerID ? KitchenOwner::find($item->KitchenOwnerID)->UserID :
                ($item->CatererID ? Caterer::find($item->CatererID)->UserID : null);
        } else {
            // Direct request to kitchen/caterer
            if ($request->kitchen_id) {
                $receiverId = KitchenOwner::find($request->kitchen_id)->UserID ?? null;
            } elseif ($request->caterer_id) {
                $receiverId = Caterer::find($request->caterer_id)->UserID ?? null;
            }
        }

        // If session_id is provided, add message to that thread
        // Otherwise, create a brand new session
        $sessionId = $request->session_id;

        if ($sessionId) {
            // Verify this session belongs to this user
            $existsInSession = LiveChat::where('SessionID', $sessionId)
                ->where(function ($q) use ($userId) {
                    $q->where('SenderID', $userId)->orWhere('ReceiverID', $userId);
                })
                ->exists();

            if ($existsInSession) {
                // Add message to existing session thread
                $chat = LiveChat::create([
                    'MenuItemID' => ($menuItemId > 0) ? $menuItemId : null,
                    'SessionID' => $sessionId,
                    'SenderID' => $userId,
                    'ReceiverID' => $receiverId,
                    'Message' => $request->message,
                    'Type' => 'message',
                    'Timestamp' => now(),
                ]);
            } else {
                // Invalid session, create new
                $sessionId = null;
            }
        }

        if (!$sessionId) {
            // Create a brand new session with a unique ID
            $sessionId = (string) Str::uuid();

            $chat = LiveChat::create([
                'MenuItemID' => ($menuItemId > 0) ? $menuItemId : null,
                'SessionID' => $sessionId,
                'SenderID' => $userId,
                'ReceiverID' => $receiverId,
                'Message' => $request->message,
                'Type' => 'request',
                'Timestamp' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent.',
            'chat' => $chat,
            'session_id' => $sessionId,
        ]);
    }

    public function getPreOrderMessages($menuItemId, Request $request)
    {
        $sessionId = $request->get('session_id');

        if ($menuItemId == 0) {
            $query = LiveChat::whereNull('MenuItemID');
        } else {
            $query = LiveChat::where('MenuItemID', $menuItemId);
        }

        $query->whereNull('OrderID')
            ->where(function ($q) {
                $q->where('SenderID', Auth::user()->UserID)
                    ->orWhere('ReceiverID', Auth::user()->UserID);
            });

        // For direct requests (ID 0), also filter by kitchen/caterer if provided (safety)
        if ($menuItemId == 0) {
            if ($request->kitchen_id) {
                $kUser = KitchenOwner::find($request->kitchen_id)->UserID ?? 0;
                $query->where(function($q) use ($kUser){
                    $q->where('SenderID', $kUser)->orWhere('ReceiverID', $kUser);
                });
            } elseif ($request->caterer_id) {
                $cUser = Caterer::find($request->caterer_id)->UserID ?? 0;
                $query->where(function($q) use ($cUser){
                    $q->where('SenderID', $cUser)->orWhere('ReceiverID', $cUser);
                });
            }
        }

        if ($sessionId) {
            $query->where('SessionID', $sessionId);
        } else {
            // If no session_id, return empty (new chat)
            return response()->json([]);
        }

        $messages = $query->with(['sender', 'receiver'])
            ->orderBy('LiveChatID')
            ->get();

        return response()->json($messages);
    }

    public function kitchenPreOrderRequests()
    {
        $user = Auth::user();

        if ($user->Role === 'Admin') {
            $rawRequests = LiveChat::whereNull('OrderID')
                ->where('Type', 'request')
                ->whereNotNull('SessionID')
                ->with(['sender', 'menuItem'])
                ->orderByDesc('Timestamp')
                ->get();

            $rawApproved = LiveChat::whereNull('OrderID')
                ->where('Type', 'approved')
                ->whereNotNull('SessionID')
                ->with(['sender', 'menuItem'])
                ->orderByDesc('Timestamp')
                ->get();
        } else {
            $kitchen = KitchenOwner::where('UserID', $user->UserID)->firstOrFail();
            $rawRequests = LiveChat::whereNull('OrderID')
                ->where('ReceiverID', $user->UserID)
                ->where('Type', 'request')
                ->whereNotNull('SessionID')
                ->with(['sender', 'menuItem'])
                ->orderByDesc('Timestamp')
                ->get();

            $rawApproved = LiveChat::whereNull('OrderID')
                ->where('ReceiverID', $user->UserID)
                ->where('Type', 'approved')
                ->whereNotNull('SessionID')
                ->with(['sender', 'menuItem'])
                ->orderByDesc('Timestamp')
                ->get();
        }

        // Filter: only show sessions that are NOT closed (no rejected/added_to_cart in same session)
        $requests = $rawRequests->filter(function ($req) {
            return !LiveChat::where('SessionID', $req->SessionID)
                ->whereIn('Type', ['rejected', 'added_to_cart'])
                ->exists();
        })->values();

        $approvedPending = $rawApproved->filter(function ($req) {
            return !LiveChat::where('SessionID', $req->SessionID)
                ->whereIn('Type', ['added_to_cart'])
                ->exists();
        })->values();

        return view('admin.kitchen.customizations', compact('requests', 'approvedPending'));
    }

    public function kitchenPreOrderChat($menuItemId, $customerId)
    {
        $item = $menuItemId > 0 ? MenuItem::find($menuItemId) : null;
        $customerUser = \App\Models\User::findOrFail($customerId);

        if (Auth::user()->Role !== 'Admin') {
            $kitchen = KitchenOwner::where('UserID', Auth::user()->UserID)->first();
            if ($item) {
                abort_unless($kitchen && $item->KitchenOwnerID == $kitchen->KitchenOwnerID, 403);
            } else {
                abort_unless($kitchen, 403);
            }
        }

        $sessionId = request()->get('session');
        $query = LiveChat::whereNull('OrderID');

        if ($sessionId) {
            $query->where('SessionID', $sessionId);
        } else {
            if ($menuItemId == 0) {
                $query->whereNull('MenuItemID');
            } else {
                $query->where('MenuItemID', $menuItemId);
            }
            $query->where(function ($q) use ($customerId) {
                $q->where('SenderID', $customerId)->orWhere('ReceiverID', $customerId);
            });
        }

        $messages = $query->with(['sender', 'receiver'])
            ->orderBy('LiveChatID', 'asc')
            ->get();

        return view('admin.kitchen.chat.preorder', compact('item', 'customerUser', 'messages', 'sessionId', 'menuItemId'));
    }

    public function kitchenSendPreOrderReply(Request $request, $menuItemId, $customerId)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return back()->with(['message' => 'Message contains prohibited language.', 'alert-type' => 'error']);
        }

        $sessionId = $request->session_id;

        LiveChat::create([
            'MenuItemID' => ($menuItemId > 0) ? $menuItemId : null,
            'SessionID' => $sessionId,
            'SenderID' => Auth::user()->UserID,
            'ReceiverID' => $customerId,
            'Message' => $request->message,
            'Type' => 'message',
            'Timestamp' => now(),
        ]);

        return back()->with(['message' => 'Reply sent.', 'alert-type' => 'success']);
    }

    // ─── Subscription Customization ───────────────────────────────────────────
    public function kitchenSubscriptionChat($id)
    {
        $sub = \App\Models\Subscription::findOrFail($id);
        $customerUser = $sub->customer->user;

        $messages = LiveChat::where('SubscriptionID', $id)
            ->with(['sender', 'receiver'])
            ->orderBy('LiveChatID')
            ->get();

        return view('admin.kitchen.chat.subscription', compact('sub', 'customerUser', 'messages'));
    }

    public function kitchenSendSubscriptionReply(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $sub = \App\Models\Subscription::findOrFail($id);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return back()->with(['message' => 'Message contains prohibited language.', 'alert-type' => 'error']);
        }

        LiveChat::create([
            'SubscriptionID' => $id,
            'SenderID' => Auth::user()->UserID,
            'ReceiverID' => $sub->customer->user->UserID,
            'Message' => $request->message,
            'Type' => 'message',
            'Timestamp' => now(),
        ]);
        return back()->with(['message' => 'Reply sent.', 'alert-type' => 'success']);
    }

    public function customerSubscriptionChat($id)
    {
        $sub = \App\Models\Subscription::findOrFail($id);
        abort_unless($sub->customer->UserID == Auth::id(), 403);

        $messages = LiveChat::where('SubscriptionID', $id)
            ->with(['sender', 'receiver'])
            ->orderBy('LiveChatID')
            ->get();

        return view('frontend.chat.subscription', compact('sub', 'messages'));
    }

    public function customerSendSubscriptionMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $sub = \App\Models\Subscription::findOrFail($id);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return back()->with(['message' => 'Message contains prohibited language.', 'alert-type' => 'error']);
        }
        abort_unless($sub->customer->UserID == Auth::id(), 403);

        $receiverId = \App\Models\KitchenOwner::find($sub->KitchenOwnerID)->UserID ?? null;

        LiveChat::create([
            'SubscriptionID' => $id,
            'SenderID' => Auth::user()->UserID,
            'ReceiverID' => $receiverId,
            'Message' => $request->message,
            'Type' => 'message',
            'Timestamp' => now(),
        ]);
        return back()->with(['message' => 'Message sent.', 'alert-type' => 'success']);
    }

    // ─── Caterer: Pre-Order Requests ─────────────────────────────────────────

    public function catererPreOrderRequests()
    {
        $user = Auth::user();

        $rawRequests = LiveChat::whereNull('OrderID')
            ->where('ReceiverID', $user->UserID)
            ->where('Type', 'request')
            ->whereNotNull('SessionID')
            ->with(['sender', 'menuItem'])
            ->orderByDesc('Timestamp')
            ->get();

        $rawApproved = LiveChat::whereNull('OrderID')
            ->where('ReceiverID', $user->UserID)
            ->where('Type', 'approved')
            ->whereNotNull('SessionID')
            ->with(['sender', 'menuItem'])
            ->orderByDesc('Timestamp')
            ->get();

        $requests = $rawRequests->filter(function ($req) {
            return !LiveChat::where('SessionID', $req->SessionID)
                ->whereIn('Type', ['rejected', 'added_to_cart'])
                ->exists();
        })->values();

        $approvedPending = $rawApproved->filter(function ($req) {
            return !LiveChat::where('SessionID', $req->SessionID)
                ->whereIn('Type', ['added_to_cart'])
                ->exists();
        })->values();

        return view('admin.caterer.customizations', compact('requests', 'approvedPending'));
    }

    public function catererPreOrderChat($menuItemId, $customerId)
    {
        $item = $menuItemId > 0 ? MenuItem::find($menuItemId) : null;
        $customerUser = \App\Models\User::findOrFail($customerId);

        if (Auth::user()->Role !== 'Admin') {
            $caterer = Caterer::where('UserID', Auth::user()->UserID)->first();
            if ($item) {
                abort_unless($caterer && $item->CatererID == $caterer->CatererID, 403);
            } else {
                abort_unless($caterer, 403);
            }
        }

        $sessionId = request()->get('session');
        $query = LiveChat::whereNull('OrderID');

        if ($sessionId) {
            $query->where('SessionID', $sessionId);
        } else {
            if ($menuItemId == 0) {
                $query->whereNull('MenuItemID');
            } else {
                $query->where('MenuItemID', $menuItemId);
            }
            $query->where(function ($q) use ($customerId) {
                $q->where('SenderID', $customerId)->orWhere('ReceiverID', $customerId);
            });
        }

        $messages = $query->with(['sender', 'receiver'])
            ->orderBy('LiveChatID', 'asc')
            ->get();

        return view('admin.caterer.chat.preorder', compact('item', 'customerUser', 'messages', 'sessionId', 'menuItemId'));
    }

    public function catererSendPreOrderReply(Request $request, $menuItemId, $customerId)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return back()->with(['message' => 'Message contains prohibited language.', 'alert-type' => 'error']);
        }

        $sessionId = $request->session_id;

        LiveChat::create([
            'MenuItemID' => ($menuItemId > 0) ? $menuItemId : null,
            'SessionID' => $sessionId,
            'SenderID' => Auth::user()->UserID,
            'ReceiverID' => $customerId,
            'Message' => $request->message,
            'Type' => 'message',
            'Timestamp' => now(),
        ]);

        return back()->with(['message' => 'Reply sent.', 'alert-type' => 'success']);
    }

    // ─── Admin/Caterer: View Any Chat ─────────────────────────────────────────────────
    public function catererOrderChat($orderId)
    {
        $order = Order::findOrFail($orderId);
        abort_unless($this->catererOwnsOrder($order), 403);
        $messages = LiveChat::where('OrderID', $orderId)->with('sender')->orderBy('LiveChatID')->get();
        return view('admin.caterer.chat.order', compact('order', 'messages'));
    }

    public function catererSendMessage(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        abort_unless($this->catererOwnsOrder($order), 403);
        $request->validate(['message' => 'required|string|max:1000']);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return back()->with(['message' => 'Message contains prohibited language.', 'alert-type' => 'error']);
        }

        $receiverId = $order->customer->user->UserID ?? null;

        LiveChat::create([
            'OrderID' => $orderId,
            'SenderID' => Auth::user()->UserID,
            'ReceiverID' => $receiverId,
            'Message' => $request->message,
            'Type' => $request->type ?? 'message',
            'Timestamp' => now(),
        ]);
        return back()->with(['message' => 'Message sent.', 'alert-type' => 'success']);
    }

    public function catererApproveRequest(Request $request, $chatId)
    {
        $msg = LiveChat::findOrFail($chatId);
        $extraCharge = (float) ($request->extra_charge ?? 0);
        $approvalMsg = $request->approval_message;

        if ($msg->OrderID) {
            $order = Order::findOrFail($msg->OrderID);
            abort_unless($this->catererOwnsOrder($order), 403);
            if ($extraCharge > 0) {
                $order->increment('TotalPrice', $extraCharge);
                $extraPoints = (int) floor($extraCharge / 10);
                if ($extraPoints > 0) {
                    $order->increment('LoyaltyPoints', $extraPoints);
                }
            }
        } else {
            $item = $msg->MenuItemID > 0 ? MenuItem::find($msg->MenuItemID) : null;
            if (Auth::user()->Role !== 'Admin') {
                $caterer = Caterer::where('UserID', Auth::user()->UserID)->firstOrFail();
                if ($item) {
                    abort_unless($item->CatererID == $caterer->CatererID, 403);
                } else {
                    abort_unless($msg->ReceiverID == Auth::user()->UserID, 403);
                }
            }
        }

        $msg->update(['Type' => 'approved', 'ExtraCharge' => $extraCharge]);

        if (!empty($approvalMsg)) {
            LiveChat::create([
                'MenuItemID' => $msg->MenuItemID,
                'SessionID' => $msg->SessionID,
                'SenderID' => Auth::id(),
                'ReceiverID' => $msg->SenderID,
                'Message' => $approvalMsg,
                'Type' => 'message',
                'ExtraCharge' => $extraCharge,
                'Timestamp' => now(),
            ]);
        }

        $note = $extraCharge > 0 ? " Extra charge of {$extraCharge} EGP added." : '';
        return back()->with(['message' => 'Request approved.' . $note, 'alert-type' => 'success']);
    }

    public function catererRejectRequest($chatId)
    {
        $msg = LiveChat::findOrFail($chatId);
        if ($msg->OrderID) {
            $order = Order::findOrFail($msg->OrderID);
            abort_unless($this->catererOwnsOrder($order), 403);
        } else {
            $item = $msg->MenuItemID > 0 ? MenuItem::find($msg->MenuItemID) : null;
            if (Auth::user()->Role !== 'Admin') {
                $caterer = Caterer::where('UserID', Auth::user()->UserID)->firstOrFail();
                if ($item) {
                    abort_unless($item->CatererID == $caterer->CatererID, 403);
                } else {
                    abort_unless($msg->ReceiverID == Auth::user()->UserID, 403);
                }
            }
        }
        $msg->update(['Type' => 'rejected']);
        return back()->with(['message' => 'Request rejected.', 'alert-type' => 'warning']);
    }

    // ─── Admin: View Any Chat ─────────────────────────────────────────────────
    public function adminOrderChat($orderId)
    {
        $order = Order::findOrFail($orderId);
        $messages = LiveChat::where('OrderID', $orderId)->with('sender')->orderBy('LiveChatID')->get();
        return view('admin.chat.order', compact('order', 'messages'));
    }

    public function adminSendMessage(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $request->validate(['message' => 'required|string|max:1000']);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->message)) {
            return back()->with(['message' => 'Message contains prohibited language.', 'alert-type' => 'error']);
        }

        $receiverId = $order->customer->user->UserID ?? null;

        LiveChat::create([
            'OrderID' => $orderId,
            'SenderID' => Auth::user()->UserID,
            'ReceiverID' => $receiverId,
            'Message' => $request->message,
            'Type' => 'message',
            'Timestamp' => now(),
        ]);

        return back()->with(['message' => 'Admin message sent.', 'alert-type' => 'success']);
    }

    public function getActiveSessions()
    {
        $userId = Auth::id();
        if (!$userId) return response()->json([]);

        // 1. Pre-order customization sessions
        $preOrderSessions = LiveChat::whereNull('OrderID')
            ->whereNotNull('SessionID')
            ->where(function ($q) use ($userId) {
                $q->where('SenderID', $userId)->orWhere('ReceiverID', $userId);
            })
            ->with(['menuItem', 'sender'])
            ->get()
            ->groupBy('SessionID')
            ->map(function ($msgs) use ($userId) {
                $lastMsg = $msgs->sortByDesc('LiveChatID')->first();
                $firstMsg = $msgs->sortBy('LiveChatID')->first();
                $hasUnread = ($lastMsg->SenderID != $userId);

                $kitchenId = 0; $catererId = 0;
                if ($firstMsg->menuItem) {
                    $kitchenId = $firstMsg->menuItem->KitchenOwnerID;
                    $catererId = $firstMsg->menuItem->CatererID;
                } else {
                    $receiverId = ($firstMsg->SenderID == $userId) ? $firstMsg->ReceiverID : $firstMsg->SenderID;
                    $k = \App\Models\KitchenOwner::where('UserID', $receiverId)->first();
                    if ($k) $kitchenId = $k->KitchenOwnerID;
                    else {
                        $c = \App\Models\Caterer::where('UserID', $receiverId)->first();
                        if ($c) $catererId = $c->CatererID;
                    }
                }

                return [
                    'session_id' => $firstMsg->SessionID,
                    'order_id' => null,
                    'menu_item_id' => $firstMsg->MenuItemID,
                    'item_name' => $firstMsg->menuItem->ItemName ?? 'Custom Request',
                    'item_price' => $firstMsg->menuItem->ItemPrice ?? 0,
                    'kitchen_id' => $kitchenId,
                    'caterer_id' => $catererId,
                    'last_message' => $lastMsg->Message,
                    'last_time' => $lastMsg->Timestamp->diffForHumans(),
                    'unread' => $hasUnread,
                    'type' => $lastMsg->Type,
                    'owner_type' => $kitchenId ? 'kitchen' : ($catererId ? 'caterer' : 'kitchen')
                ];
            });

        // 2. Active Order sessions (only orders not yet Delivered/Cancelled)
        $orderSessions = LiveChat::whereNotNull('OrderID')
            ->whereHas('order', function($q) {
                $q->whereNotIn('OrderStatus', ['Delivered', 'Cancelled']);
            })
            ->where(function ($q) use ($userId) {
                $q->where('SenderID', $userId)->orWhere('ReceiverID', $userId);
            })
            ->with(['order.kitchenOwner', 'order.caterer', 'sender'])
            ->get()
            ->groupBy('OrderID')
            ->map(function ($msgs) use ($userId) {
                $lastMsg = $msgs->sortByDesc('LiveChatID')->first();
                $firstMsg = $msgs->sortBy('LiveChatID')->first();
                $order = $lastMsg->order;
                $hasUnread = ($lastMsg->SenderID != $userId);

                $vendorName = $order->kitchenOwner->KitchenName ?? ($order->caterer->FullName ?? 'Vendor');

                return [
                    'session_id' => null,
                    'order_id' => $order->OrderID,
                    'menu_item_id' => 0,
                    'item_name' => "Order #{$order->OrderID} ({$vendorName})",
                    'item_price' => $order->TotalPrice,
                    'kitchen_id' => $order->KitchenOwnerID,
                    'caterer_id' => $order->CatererID,
                    'last_message' => $lastMsg->Message,
                    'last_time' => $lastMsg->Timestamp->diffForHumans(),
                    'unread' => $hasUnread,
                    'type' => 'order_chat',
                    'owner_type' => $order->KitchenOwnerID ? 'kitchen' : ($order->CatererID ? 'caterer' : 'kitchen')
                ];
            });

        $sessions = $preOrderSessions->merge($orderSessions)->values();

        return response()->json($sessions);
    }
}
