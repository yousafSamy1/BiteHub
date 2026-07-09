<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class LiveChat extends Model
{
    protected $table = 'live_chats';
    protected $primaryKey = 'LiveChatID';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'Timestamp' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'SenderID', 'UserID');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'ReceiverID', 'UserID');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'OrderID', 'OrderID');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'MenuItemID', 'MenuItemID');
    }

    protected static function booted()
    {
        static::created(function ($chat) {
            if ($chat->ReceiverID) {
                Notification::notify(
                    $chat->ReceiverID, 
                    'New Message', 
                    ($chat->sender->FullName ?? 'Someone') . ': ' . \Illuminate\Support\Str::limit($chat->Message, 50), 
                    'Chat'
                );
            }
        });
    }
}
