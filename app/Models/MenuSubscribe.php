<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MenuSubscribe extends Pivot
{
    protected $table = 'menu_subscribes';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'SubscriptionID', 'SubscriptionID');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'MenuItemID', 'MenuItemID');
    }
}
