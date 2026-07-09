<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    protected $table = 'refund_requests';
    protected $primaryKey = 'RequestID';

    protected $fillable = [
        'CustomerID',
        'RefundableID',
        'RefundableType',
        'OriginalAmount',
        'ConsumedAmount',
        'Amount',
        'Reason',
        'Status',
        'AdminNotes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'CustomerID');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'RefundableID', 'OrderID');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'RefundableID', 'SubscriptionID');
    }

    public function getIsOrderAttribute()
    {
        return $this->RefundableType === 'Order';
    }

    public function getIsSubscriptionAttribute()
    {
        return $this->RefundableType === 'Subscription';
    }
}
