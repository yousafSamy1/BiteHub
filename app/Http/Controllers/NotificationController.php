<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('UserID', Auth::id())
            ->orderBy('CreatedAt', 'desc')
            ->paginate(20);
        
        $role = Auth::user()->Role;
        if (in_array($role, ['Customer', 'DeliveryAgent'])) {
            return view('frontend.notifications_index', compact('notifications'));
        }
        
        return view('admin.notifications.index', compact('notifications'));
    }

    public function clearAll()
    {
        Notification::where('UserID', Auth::id())->update(['IsRead' => true]);
        return redirect()->back()->with(['message' => 'All notifications marked as read.', 'alert-type' => 'success']);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('UserID', Auth::id())->where('NotificationID', $id)->first();
        if ($notification) {
            $notification->update(['IsRead' => true]);
            
            if ($notification->Type === 'Chat') {
                return redirect()->to(route('frontend.profile') . '#chats');
            }
        }
        return redirect()->back()->with(['message' => 'Notification marked as read.', 'alert-type' => 'success']);
    }

    public function getLatestNotifications()
    {
        $notifications = Notification::where('UserID', Auth::id())
            ->where('IsRead', false)
            ->orderBy('CreatedAt', 'desc')
            ->limit(5)
            ->get()
            ->map(function($n) {
                return [
                    'id' => $n->NotificationID,
                    'title' => $n->Title,
                    'message' => $n->Message,
                    'type' => strtolower($n->Type),
                    'time' => \Carbon\Carbon::parse($n->CreatedAt)->diffForHumans(),
                    'url' => route('notifications.read', $n->NotificationID),
                    'icon' => match($n->Type) {
                        'Order' => 'shopping-cart',
                        'Promotion' => 'gift',
                        'Chat' => 'comment-dots',
                        default => 'bell',
                    }
                ];
            });

        return response()->json([
            'unread_count' => Notification::where('UserID', Auth::id())->where('IsRead', false)->count(),
            'notifications' => $notifications
        ]);
    }
}
