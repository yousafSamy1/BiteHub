<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';
    protected $primaryKey = 'MenuItemID';
    public $timestamps = false;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryID', 'CategoryID');
    }

    public function kitchenOwner()
    {
        return $this->belongsTo(KitchenOwner::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function caterer()
    {
        return $this->belongsTo(Caterer::class, 'CatererID', 'CatererID');
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class, 'MenuItemID', 'MenuItemID');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'menu_order_items', 'MenuItemID', 'OrderID')
                     ->withPivot('Quantity');
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'menu_subscribes', 'MenuItemID', 'SubscriptionID');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'menu_item_tag', 'menu_item_id', 'tag_id');
    }
}
