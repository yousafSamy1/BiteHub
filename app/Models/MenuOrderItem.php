<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MenuOrderItem extends Pivot
{
    protected $table = 'menu_order_items';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'MenuItemID', 'MenuItemID');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }
}
