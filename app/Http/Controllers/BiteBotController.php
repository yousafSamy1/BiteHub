<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportInquiry;
use App\Models\SupportMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BiteBotController extends Controller
{
    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $messageText = trim($request->message);

        if (!app(\App\Services\ProfanityFilterService::class)->checkAndProcess($user, $messageText)) {
            return response()->json(['error' => 'Your message contains prohibited language and was not sent.'], 403);
        }

        // 1. Get or Create Inquiry
        $inquiry = SupportInquiry::firstOrCreate(
            ['UserID' => $user->UserID, 'Status' => 'Bot'],
            ['Status' => 'Bot'] // Default if creating
        );

        // 2. Save User Message
        SupportMessage::create([
            'InquiryID' => $inquiry->InquiryID,
            'SenderType' => 'User',
            'SenderID' => $user->UserID,
            'Message' => $messageText,
        ]);

        // 3. Bot Logic
        $response = $this->getBotResponse($messageText);

        if ($response['type'] === 'answer') {
            SupportMessage::create([
                'InquiryID' => $inquiry->InquiryID,
                'SenderType' => 'Bot',
                'Message' => $response['text'],
            ]);
        } else {
            // Unknown question - Escalate to Admin
            $inquiry->Status = 'Escalated';
            $inquiry->save();

            SupportMessage::create([
                'InquiryID' => $inquiry->InquiryID,
                'SenderType' => 'Bot',
                'Message' => "I don't know the answer to that yet, but I've sent your question to the Admin team. They will get back to you shortly! 🚀",
            ]);
        }

        return response()->json([
            'history' => $this->getChatHistory($inquiry->InquiryID)
        ]);
    }

    public function fetchHistory()
    {
        $user = Auth::user();
        if (!$user) return response()->json(['messages' => []]);

        $inquiry = SupportInquiry::where('UserID', $user->UserID)->latest()->first();
        if (!$inquiry) return response()->json(['messages' => []]);

        // Mark admin messages as read when user fetches history
        SupportMessage::where('InquiryID', $inquiry->InquiryID)
            ->where('SenderType', 'Admin')
            ->update(['IsRead' => true]);

        return response()->json([
            'messages' => $this->getChatHistory($inquiry->InquiryID)
        ]);
    }

    private function getBotResponse($msg)
    {
        $msg = strtolower($msg);

        // Best Kitchen
        if (str_contains($msg, 'best kitchen') || str_contains($msg, 'top kitchen') || str_contains($msg, 'highest rated')) {
            $best = DB::table('kitchen_owners as ko')
                ->join('users as u', 'ko.UserID', '=', 'u.UserID')
                ->leftJoin('menu_items as mi', 'mi.KitchenOwnerID', '=', 'ko.KitchenOwnerID')
                ->leftJoin('menu_order_items as moi', 'moi.MenuItemID', '=', 'mi.MenuItemID')
                ->leftJoin('orders as o', 'o.OrderID', '=', 'moi.OrderID')
                ->where('ko.Status', 'Active')
                ->select('ko.KitchenName', DB::raw('COUNT(DISTINCT o.OrderID) as totalOrders'))
                ->groupBy('ko.KitchenOwnerID', 'ko.KitchenName')
                ->orderByDesc('totalOrders')
                ->first();

            $name = $best ? $best->KitchenName : "our rising stars";
            return ['type' => 'answer', 'text' => "Currently, **$name** is our top-performing kitchen based on orders and user reviews! 🏆"];
        }

        // Highest Sold Item
        if (str_contains($msg, 'highest item') || str_contains($msg, 'popular dish') || str_contains($msg, 'best seller')) {
            $item = DB::table('menu_items as mi')
                ->join('menu_order_items as moi', 'moi.MenuItemID', '=', 'mi.MenuItemID')
                ->select('mi.ItemName', DB::raw('SUM(moi.Quantity) as total_sold'))
                ->groupBy('mi.MenuItemID', 'mi.ItemName')
                ->orderByDesc('total_sold')
                ->first();

            $name = $item ? $item->ItemName : "our daily specials";
            return ['type' => 'answer', 'text' => "The most popular dish right now is **$name**! Everyone seems to be loving it today. 😋"];
        }

        // Payment Methods
        if (str_contains($msg, 'payment') || str_contains($msg, 'pay') || str_contains($msg, 'card') || str_contains($msg, 'cash')) {
            return ['type' => 'answer', 'text' => "We accept **Wallet Balance**, **Credit/Debit Cards (Stripe)**, and **Cash on Delivery**. You can top up your wallet anytime from your dashboard! 💳"];
        }

        // How to login/register
        if (str_contains($msg, 'how to') && (str_contains($msg, 'register') || str_contains($msg, 'join'))) {
            return ['type' => 'answer', 'text' => "You can register as a Customer, Kitchen Owner, or Caterer by clicking the 'Sign Up' button in the top right corner! 📝"];
        }

        // Greeting
        if (preg_match('/^(hi|hello|hey|good morning|good evening)/i', $msg)) {
            return ['type' => 'answer', 'text' => "Hi there! I'm BiteBot. I can tell you about our top kitchens, best-selling dishes, or help you with payment info. What's on your mind? 😊"];
        }

        return ['type' => 'escalate'];
    }

    private function getChatHistory($inquiryID)
    {
        return SupportMessage::where('InquiryID', $inquiryID)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($m) {
                return [
                    'sender' => $m->SenderType,
                    'message' => $m->Message,
                    'time' => $m->created_at->diffForHumans(),
                ];
            });
    }

    // --- Admin Dashboard Methods ---

    public function adminInquiries()
    {
        $inquiries = SupportInquiry::with(['user', 'messages'])
            ->orderByDesc('updated_at')
            ->get();
        return view('admin.support.inquiries.index', compact('inquiries'));
    }

    public function adminChat($id)
    {
        $inquiry = SupportInquiry::with(['user', 'messages.sender'])->findOrFail($id);
        
        // Mark user messages as read
        SupportMessage::where('InquiryID', $id)->where('SenderType', 'User')->update(['IsRead' => true]);

        return view('admin.support.inquiries.chat', compact('inquiry'));
    }

    public function adminReply(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);
        
        $inquiry = SupportInquiry::findOrFail($id);
        $inquiry->Status = 'Resolved'; // Or keep as Escalated if waiting for more
        $inquiry->touch();
        $inquiry->save();

        SupportMessage::create([
            'InquiryID' => $id,
            'SenderType' => 'Admin',
            'SenderID' => Auth::id(),
            'Message' => $request->message,
        ]);

        return back()->with(['message' => 'Reply sent!', 'alert-type' => 'success']);
    }

    public function checkUnread()
    {
        $user = Auth::user();
        if (!$user) return response()->json(['unread' => 0]);

        $unread = DB::table('support_messages as sm')
            ->join('support_inquiries as si', 'sm.InquiryID', '=', 'si.InquiryID')
            ->where('si.UserID', $user->UserID)
            ->where('sm.SenderType', 'Admin')
            ->where('sm.IsRead', false)
            ->count();

        return response()->json(['unread' => $unread]);
    }
}
