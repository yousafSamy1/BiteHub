<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'SubscriptionID';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'PreferredTimes' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'CustomerID');
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'subscription_payments', 'SubscriptionID', 'PaymentID');
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_subscribes', 'SubscriptionID', 'MenuItemID')
            ->withPivot('Status', 'ModifiedStatus', 'KitchenNotes');
    }

    public function kitchen()
    {
        return $this->belongsTo(KitchenOwner::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function kitchenPlan()
    {
        return $this->belongsTo(KitchenPlan::class, 'KitchenPlanID', 'KitchenPlanID');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'SubscriptionID', 'SubscriptionID');
    }

    public function getTotalPriceAttribute()
    {
        return ($this->Price ?? 0) + ($this->DeliveryCharge ?? 0);
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total_price - ($this->PaidAmount ?? 0);
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->remaining_balance <= 0;
    }

    public function getDeadlineAttribute()
    {
        return \Carbon\Carbon::parse($this->EndDate)->subDay();
    }

    public function getIsOverdueAttribute()
    {
        return !$this->is_fully_paid && now()->gt($this->deadline);
    }

    /**
     * Cancel the subscription and refund the remaining balance to the customer's wallet.
     * Calculation is based on actual money paid vs cost of delivered meals.
     */
    public function cancelAndRefund($reason = null)
    {
        if ($this->Status === 'Cancelled' || $this->Status === 'Refunded') {
            return false;
        }

        $paidAmount = (float) ($this->PaidAmount ?? 0);
        $totalPlanPrice = (float) (($this->Price ?? 0) + ($this->DeliveryCharge ?? 0));
        
        $plan = $this->kitchenPlan;
        $totalMealsInPlan = max(1, (int)($this->DurationDays ?? 1) * (int)($this->MealsPerDay ?? 1));
        $refundAmount = 0;

        if ($totalMealsInPlan > 0) {
            $pricePerMeal = $totalPlanPrice / $totalMealsInPlan;
            $deliveredMealsCount = $this->orders()->where('OrderStatus', 'Delivered')->count();
            $consumedCost = $deliveredMealsCount * $pricePerMeal;
            
            $refundAmount = $paidAmount - $consumedCost;
            
            if ($refundAmount > 0) {
                $customer = $this->customer;
                $kitchenUser = $this->kitchen->user ?? null;

                // 1. Deduct from Kitchen Owner's wallet
                if ($kitchenUser) {
                    $kitchenUser->decrement('Wallet_balance', $refundAmount);
                }

                // 2. Credit to Customer's wallet
                $customer->increment('WalletBalance', $refundAmount);
                
                // Log transaction
                LoyaltyTransaction::create([
                    'CustomerID' => $this->CustomerID,
                    'Points' => $refundAmount,
                    'Type' => 'Refund',
                    'Description' => "Refund for cancelled plan: " . ($plan->Title ?? 'Plan') . " (Deducted from Kitchen)",
                    'CreatedAt' => now(),
                ]);
            }
        }

        // Cancel future pending orders for this subscription
        $this->orders()->whereIn('OrderStatus', ['Pending', 'Scheduled'])->update(['OrderStatus' => 'Cancelled']);

        $this->Status = 'Cancelled';
        $this->cancel_reason = $reason;
        $this->save();

        return $refundAmount;
    }
}
