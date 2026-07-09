<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'CustomerID';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'CustomerID', 'CustomerID');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'CustomerID', 'CustomerID');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'CustomerID', 'CustomerID');
    }

    public function cateringRequests()
    {
        return $this->hasMany(CateringRequest::class, 'CustomerID', 'CustomerID');
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'CustomerID', 'CustomerID');
    }
}
