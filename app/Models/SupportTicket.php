<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';
    protected $primaryKey = 'TicketID';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }

    public function kitchenOwner()
    {
        return $this->hasOneThrough(KitchenOwner::class, User::class, 'UserID', 'UserID', 'UserID', 'UserID');
    }

    public function caterer()
    {
        return $this->hasOneThrough(Caterer::class, User::class, 'UserID', 'UserID', 'UserID', 'UserID');
    }

    public function getSenderTypeLabelAttribute(): string
    {
        return match($this->SenderType) {
            'Customer'     => '🛒 Customer',
            'KitchenOwner' => '🍳 Kitchen Owner',
            'Caterer'      => '🎉 Caterer',
            default        => $this->SenderType,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->Status) {
            'Open'       => 'warning',
            'InProgress' => 'info',
            'Resolved'   => 'success',
            'Closed'     => 'secondary',
            default      => 'secondary',
        };
    }
}
