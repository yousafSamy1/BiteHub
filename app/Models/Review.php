<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'ReviewID';
    public $timestamps = false;
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'CustomerID');
    }

    public function kitchenOwner()
    {
        return $this->belongsTo(KitchenOwner::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function caterer()
    {
        return $this->belongsTo(Caterer::class, 'CatererID', 'CatererID');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }
}
