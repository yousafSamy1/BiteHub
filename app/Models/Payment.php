<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'PaymentID';
    public $timestamps = false;
    protected $guarded = [];

    public function orders()
    {
        return $this->hasMany(Order::class, 'PaymentID', 'PaymentID');
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'subscription_payments', 'PaymentID', 'SubscriptionID');
    }

    public function advertisings()
    {
        return $this->hasMany(Advertising::class, 'PaymentID', 'PaymentID');
    }
}
