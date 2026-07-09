<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOwner extends Model
{
    protected $table = 'kitchen_owners';
    protected $primaryKey = 'KitchenOwnerID';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'Attachment' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function plans()
    {
        return $this->hasMany(KitchenPlan::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function advertisings()
    {
        return $this->hasMany(Advertising::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function getAverageRatingAttribute()
    {
        $avg = $this->reviews()->avg('Rating');
        return $avg ? round($avg, 1) : 4.5;
    }

    public function getReviewCountAttribute()
    {
        $count = $this->reviews()->count();
        return $count > 0 ? $count : 1; 
    }

    public function getCurrentStatusAttribute()
    {
        $openingTime = $this->OpeningTime ?? '09:00:00';
        $closingTime = $this->ClosingTime ?? '22:00:00';

        $now = now();
        $opening = \Carbon\Carbon::parse($openingTime);
        $closing = \Carbon\Carbon::parse($closingTime);

        // Handle overnight shifts
        if ($closing->lessThan($opening)) {
            $isOpen = $now->greaterThanOrEqualTo($opening) || $now->lessThanOrEqualTo($closing);
        } else {
            $isOpen = $now->between($opening, $closing);
        }

        if (!$isOpen) {
            return 'Closed';
        }

        // Check if busy (3+ preparing orders)
        // If preparing_orders_count was already computed via withCount (optimization)
        $preparingOrdersCount = $this->preparing_orders_count ?? Order::where('OrderStatus', 'Preparing')
            ->whereHas('menuItems', function($q) {
                $q->where('KitchenOwnerID', $this->KitchenOwnerID);
            })->count();

        if ($preparingOrdersCount >= 3) {
            return 'Busy';
        }

        return 'Open';
    }
}
