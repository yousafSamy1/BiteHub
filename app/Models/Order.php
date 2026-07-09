<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'OrderID';
    public $timestamps = false;
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'CustomerID');
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(DeliveryAgent::class, 'DeliveryAgentID', 'DeliveryAgentID');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'PaymentID', 'PaymentID');
    }

    public function liveChat()
    {
        return $this->belongsTo(LiveChat::class, 'LiveChatID', 'LiveChatID');
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_order_items', 'OrderID', 'MenuItemID')
                     ->withPivot('Quantity');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'OrderID', 'OrderID');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'SubscriptionID', 'SubscriptionID');
    }

    public function kitchenOwner()
    {
        return $this->belongsTo(KitchenOwner::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function caterer()
    {
        return $this->belongsTo(Caterer::class, 'CatererID', 'CatererID');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'OrderID', 'OrderID');
    }

    protected static function booted()
    {
        static::updated(function ($order) {
            if ($order->wasChanged('OrderStatus')) {
                $status = $order->OrderStatus;
                $customerUser = $order->customer->user ?? null;
                if ($customerUser) {
                    Notification::notify($customerUser->UserID, 'Order Update', "Your order #{$order->OrderID} is now {$status}.", 'Order');
                }

                if ($status === 'Ready' && $order->DeliveryAgentID) {
                    $agentUser = $order->deliveryAgent->user ?? null;
                    if ($agentUser) {
                        Notification::notify($agentUser->UserID, 'Order Ready for Pickup', "Order #{$order->OrderID} is ready. You can start delivery.", 'Order');
                    }
                }
            }
        });
    }
}
