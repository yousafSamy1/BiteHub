<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\KitchenOwner;
use App\Models\Caterer;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\LoyaltyTransaction;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class SupportController extends Controller
{
    // ─── Problem Categories per Role ────────────────────────────────────────────

    private static array $customerCategories = [
        'Order Not Delivered',
        'Wrong Items Received',
        'Food Quality Issue',
        'Payment / Refund Issue',
        'Delivery Was Late',
        'Driver / Delivery Agent Behavior',
        'Subscription Problem',
        'Caterer Issue',
        'App / Technical Bug',
        'Other',
    ];

    private static array $kitchenCategories = [
        'Payment / Billing Issue',
        'Order Problem',
        'Customer Misconduct',
        'Subscription Dispute',
        'Technical Issue on Platform',
        'Platform Policy Concern',
        'Account / Profile Issue',
        'Other',
    ];

    private static array $catererCategories = [
        'Payment / Billing Issue',
        'Catering Request Problem',
        'Customer Misconduct',
        'Contract / Agreement Dispute',
        'Technical Issue on Platform',
        'Platform Policy Concern',
        'Account / Profile Issue',
        'Other',
    ];

    // ─── CUSTOMER ────────────────────────────────────────────────────────────────

    public function customerIndex()
    {
        $user     = Auth::user();
        $customer = Customer::where('UserID', $user->UserID)->first();

        $tickets = SupportTicket::where('UserID', $user->UserID)
            ->where('SenderType', 'Customer')
            ->orderByDesc('TicketID')
            ->paginate(2);

        $orders = $customer
            ? Order::where('CustomerID', $customer->CustomerID)
                ->orderByDesc('CreatedAt')
                ->limit(30)
                ->get()
            : collect();

        $categories = self::$customerCategories;

        if (request()->ajax()) {
            return view('frontend.support._tickets_list', compact('tickets'))->render();
        }

        return view('frontend.support.index', compact('tickets', 'orders', 'categories'));
    }

    public function customerStore(Request $request)
    {
        $request->validate([
            'category'    => 'required|string|max:100',
            'subject'     => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'order_id'    => 'nullable|integer',
        ]);

        $user = Auth::user();

        // Check for profanity in subject and description
        $filter = app(\App\Services\ProfanityFilterService::class);
        if (!$filter->checkAndProcess($user, $request->subject) || !$filter->checkAndProcess($user, $request->description)) {
            return back()->with(['message' => 'Your ticket contains prohibited language and was not submitted.', 'alert-type' => 'error']);
        }

        // Verify the order belongs to the customer if provided
        $orderId = null;
        if ($request->order_id) {
            $customer = Customer::where('UserID', $user->UserID)->first();
            if ($customer) {
                $order = Order::where('OrderID', $request->order_id)
                    ->where('CustomerID', $customer->CustomerID)
                    ->first();
                $orderId = $order?->OrderID;
            }
        }

        $ticket = SupportTicket::create([
            'UserID'      => $user->UserID,
            'SenderType'  => 'Customer',
            'Category'    => $request->category,
            'Subject'     => $request->subject,
            'Description' => $request->description,
            'OrderID'     => $orderId,
            'Status'      => 'Open',
        ]);

        // Notify Admins
        $admins = User::whereIn('Role', ['Admin', 'Owner'])->get();
        foreach ($admins as $admin) {
            Notification::notify($admin->UserID, 'New Support Ticket', "New ticket #{$ticket->TicketID} from customer {$user->FullName}.", 'System');
        }

        return back()->with(['message' => 'Your support ticket has been submitted. We will get back to you soon!', 'alert-type' => 'success']);
    }

    public function customerShow($id)
    {
        $user   = Auth::user();
        $ticket = SupportTicket::where('TicketID', $id)
            ->where('UserID', $user->UserID)
            ->firstOrFail();

        return view('frontend.support.show', compact('ticket'));
    }

    // ─── KITCHEN OWNER ───────────────────────────────────────────────────────────

    public function kitchenIndex()
    {
        $user    = Auth::user();
        $tickets = SupportTicket::where('UserID', $user->UserID)
            ->where('SenderType', 'KitchenOwner')
            ->orderByDesc('TicketID')
            ->paginate(10);

        $categories = self::$kitchenCategories;

        return view('admin.kitchen.support.index', compact('tickets', 'categories'));
    }

    public function kitchenStore(Request $request)
    {
        $request->validate([
            'category'    => 'required|string|max:100',
            'subject'     => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'order_id'    => 'nullable|integer',
        ]);

        $user = Auth::user();

        $filter = app(\App\Services\ProfanityFilterService::class);
        if (!$filter->checkAndProcess($user, $request->subject) || !$filter->checkAndProcess($user, $request->description)) {
            return back()->with(['message' => 'Your request contains prohibited language.', 'alert-type' => 'error']);
        }

        $ticket = SupportTicket::create([
            'UserID'      => $user->UserID,
            'SenderType'  => 'KitchenOwner',
            'Category'    => $request->category,
            'Subject'     => $request->subject,
            'Description' => $request->description,
            'OrderID'     => $request->order_id,
            'Status'      => 'Open',
        ]);

        // Notify Admins
        $admins = User::whereIn('Role', ['Admin', 'Owner'])->get();
        foreach ($admins as $admin) {
            Notification::notify($admin->UserID, 'New Kitchen Support Ticket', "New ticket #{$ticket->TicketID} from kitchen owner {$user->FullName}.", 'System');
        }

        return back()->with(['message' => 'Your support ticket has been submitted. We will get back to you soon!', 'alert-type' => 'success']);
    }

    // ─── CATERER ─────────────────────────────────────────────────────────────────

    public function catererIndex()
    {
        $user    = Auth::user();
        $tickets = SupportTicket::where('UserID', $user->UserID)
            ->where('SenderType', 'Caterer')
            ->orderByDesc('TicketID')
            ->paginate(10);

        $categories = self::$catererCategories;

        return view('admin.caterer.support.index', compact('tickets', 'categories'));
    }

    public function catererStore(Request $request)
    {
        $request->validate([
            'category'    => 'required|string|max:100',
            'subject'     => 'required|string|max:255',
            'description' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        $filter = app(\App\Services\ProfanityFilterService::class);
        if (!$filter->checkAndProcess($user, $request->subject) || !$filter->checkAndProcess($user, $request->description)) {
            return back()->with(['message' => 'Your request contains prohibited language.', 'alert-type' => 'error']);
        }

        $ticket = SupportTicket::create([
            'UserID'      => $user->UserID,
            'SenderType'  => 'Caterer',
            'Category'    => $request->category,
            'Subject'     => $request->subject,
            'Description' => $request->description,
            'Status'      => 'Open',
        ]);

        // Notify Admins
        $admins = User::whereIn('Role', ['Admin', 'Owner'])->get();
        foreach ($admins as $admin) {
            Notification::notify($admin->UserID, 'New Caterer Support Ticket', "New ticket #{$ticket->TicketID} from caterer {$user->FullName}.", 'System');
        }

        return back()->with(['message' => 'Support request submitted successfully. Admin will review it shortly.', 'alert-type' => 'success']);
    }

    // ─── ADMIN ───────────────────────────────────────────────────────────────────

    public function adminIndex(Request $request)
    {
        $query = SupportTicket::join('users', 'support_tickets.UserID', '=', 'users.UserID')
            ->select('support_tickets.*', 'users.FullName as UserName', 'users.Email as UserEmail');

        if ($request->status) {
            $query->where('support_tickets.Status', $request->status);
        }
        if ($request->sender_type) {
            $query->where('support_tickets.SenderType', $request->sender_type);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('support_tickets.Subject', 'like', '%' . $request->search . '%')
                  ->orWhere('users.FullName', 'like', '%' . $request->search . '%')
                  ->orWhere('support_tickets.Category', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->from_date) {
            $query->whereDate('support_tickets.created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('support_tickets.created_at', '<=', $request->to_date);
        }

        $tickets = $query->orderByDesc('support_tickets.TicketID')->paginate(15);

        // Stats
        $stats = [
            'total'      => SupportTicket::count(),
            'open'       => SupportTicket::where('Status', 'Open')->count(),
            'inprogress' => SupportTicket::where('Status', 'InProgress')->count(),
            'resolved'   => SupportTicket::whereIn('Status', ['Resolved', 'Closed'])->count(),
        ];

        return view('admin.reports.index', compact('tickets', 'stats'));
    }

    public function adminShow($id)
    {
        $ticket = SupportTicket::join('users', 'support_tickets.UserID', '=', 'users.UserID')
            ->select('support_tickets.*', 'users.FullName as UserName', 'users.Email as UserEmail', 'users.Image as UserImage')
            ->where('support_tickets.TicketID', $id)
            ->firstOrFail();

        $order = null;
        if ($ticket->OrderID) {
            $order = Order::leftJoin('customers', 'orders.CustomerID', '=', 'customers.CustomerID')
                ->leftJoin('users as cu', 'customers.UserID', '=', 'cu.UserID')
                ->select('orders.*', 'cu.FullName as CustomerName')
                ->where('orders.OrderID', $ticket->OrderID)
                ->first();
        }

        return view('admin.reports.show', compact('ticket', 'order'));
    }

    public function adminReply(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string|max:5000',
            'status'      => 'required|in:Open,InProgress,Resolved,Closed',
        ]);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess(Auth::user(), $request->admin_reply)) {
            return back()->with(['message' => 'Reply contains prohibited language.', 'alert-type' => 'error']);
        }

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update([
            'AdminReply' => $request->admin_reply,
            'Status'     => $request->status,
        ]);

        Notification::notify($ticket->UserID, 'Support Ticket Update', "Administrator replied to your ticket #{$ticket->TicketID}.", 'System');

        return back()->with(['message' => 'Reply sent and ticket updated.', 'alert-type' => 'success']);
    }

    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:Open,InProgress,Resolved,Closed']);
        SupportTicket::findOrFail($id)->update(['Status' => $request->status]);
        return back()->with(['message' => 'Ticket status updated.', 'alert-type' => 'success']);
    }

    public function adminProcessRefund(Request $request, $id)
    {
        $request->validate([
            'refund_amount'  => 'required|numeric|min:0',
            'loyalty_points' => 'required|integer|min:0',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        if (!$ticket->OrderID) {
            return back()->with(['message' => 'This ticket is not linked to an order.', 'alert-type' => 'error']);
        }

        $order = Order::with(['kitchenOwner', 'caterer', 'customer.user'])->findOrFail($ticket->OrderID);

        DB::beginTransaction();
        try {
            $refundAmount  = $request->refund_amount;
            $loyaltyPoints = $request->loyalty_points;

            // 1. Logic: Transfer from Kitchen/Caterer to Customer
            $provider = $order->kitchenOwner ?? $order->caterer;
            $customer = $order->customer;

            if ($refundAmount > 0) {
                if (!$provider || !$provider->UserID) {
                    throw new \Exception("Service provider (Kitchen/Caterer) not found for this order.");
                }

                $providerUser = User::find($provider->UserID);
                $customerUser = $customer->user;

                if ($providerUser) {
                    $providerUser->decrement('Wallet_balance', $refundAmount);
                }
                if ($customer) {
                    $customer->increment('WalletBalance', $refundAmount);
                }

                // Log in RefundRequests for history (optional but good)
                RefundRequest::create([
                    'RefundableID'   => $order->OrderID,
                    'RefundableType' => 'Order',
                    'CustomerID'     => $customer->CustomerID,
                    'Reason'         => "Manual Refund via Ticket #$id: " . $ticket->Subject,
                    'AdminNotes'     => "Resolution for Ticket #$id: Manual deduction processed by Admin.",
                    'OriginalAmount' => $order->TotalPrice,
                    'Amount'         => $refundAmount,
                    'Status'         => 'Approved'
                ]);
            }

            // 2. Add Loyalty Points
            if ($loyaltyPoints > 0) {
                $customer->increment('WalletBalance', 0); // No effect on cash, just ensuring customer exists
                LoyaltyTransaction::create([
                    'CustomerID' => $customer->CustomerID,
                    'Points'     => $loyaltyPoints,
                    'Type'       => 'Bonus',
                    'Description'=> "Compensation for Ticket #$id"
                ]);
            }

            // 3. Update Statuses
            $order->update(['OrderStatus' => 'Refunded']);
            $ticket->update([
                'Status'     => 'Resolved',
                'AdminReply' => ($ticket->AdminReply ? $ticket->AdminReply . "\n\n" : "") . 
                                "Resolution: Processed a refund of " . number_format($refundAmount, 2) . " EGP and granted " . $loyaltyPoints . " loyalty points."
            ]);

            Notification::notify($ticket->UserID, 'Ticket Resolved', "Your ticket #{$ticket->TicketID} has been resolved with compensation.", 'System');

            DB::commit();
            return back()->with(['message' => 'Refund processed successfully and ticket resolved.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Refund Error: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }
}
