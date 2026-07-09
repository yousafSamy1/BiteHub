<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'UserID';
    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['Password'];

    // Tell Laravel which column stores the password
    public function getAuthPassword()
    {
        return $this->Password;
    }

    // Tell Laravel which column is the email for password resets
    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    // Tell Laravel where to route notifications
    public function routeNotificationForMail()
    {
        return $this->Email;
    }

    // Accessor so Breeze views can use $user->name
    public function getNameAttribute()
    {
        return $this->FullName;
    }

    // Accessor so Breeze views can use $user->email
    public function getEmailAttribute()
    {
        return $this->attributes['Email'] ?? null;
    }

    // Accessor so we handle both 'photo' and 'Image' terminology
    public function getPhotoAttribute()
    {
        return $this->attributes['Image'] ?? null;
    }

    // Accessors for easier Eager Loading with existing hasMany
    public function phone()
    {
        return $this->hasOne(UserPhone::class, 'UserID', 'UserID');
    }

    public function address()
    {
        return $this->hasOne(UserAddress::class, 'UserID', 'UserID');
    }

    // Relationships
    public function phones()
    {
        return $this->hasMany(UserPhone::class, 'UserID', 'UserID');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'UserID', 'UserID');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'UserID', 'UserID');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'UserID', 'UserID');
    }

    public function kitchenOwner()
    {
        return $this->hasOne(KitchenOwner::class, 'UserID', 'UserID');
    }

    public function caterer()
    {
        return $this->hasOne(Caterer::class, 'UserID', 'UserID');
    }

    public function deliveryAgent()
    {
        return $this->hasOne(DeliveryAgent::class, 'UserID', 'UserID');
    }

    public function sentChats()
    {
        return $this->hasMany(LiveChat::class, 'SenderID', 'UserID');
    }

    public function receivedChats()
    {
        return $this->hasMany(LiveChat::class, 'ReceiverID', 'UserID');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'UserID', 'UserID');
    }

    /**
     * Override the default password reset notification
     * to use BiteHub's custom branded email template.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // --- Withdrawal Logic ---
    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'UserID', 'UserID');
    }

    public function getPendingBalanceAttribute()
    {
        return $this->withdrawalRequests()->where('Status', 'Pending')->sum('Amount');
    }
}
