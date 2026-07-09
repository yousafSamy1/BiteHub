<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'NotificationID';
    public $timestamps = false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public static function notify($userId, $title, $message, $type = 'System')
    {
        return self::create([
            'UserID' => $userId,
            'Title' => $title,
            'Message' => $message,
            'Type' => $type,
            'CreatedAt' => now(),
            'IsRead' => false
        ]);
    }
}
