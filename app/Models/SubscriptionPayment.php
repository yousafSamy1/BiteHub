<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SubscriptionPayment extends Pivot
{
    protected $table = 'subscription_payments';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'PaymentID', 'PaymentID');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'SubscriptionID', 'SubscriptionID');
    }
}
