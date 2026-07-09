<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertising extends Model
{
    protected $table = 'advertisings';
    protected $primaryKey = 'AdvertisingID';
    public $timestamps = false;
    protected $guarded = [];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'PaymentID', 'PaymentID');
    }

    public function kitchenOwner()
    {
        return $this->belongsTo(KitchenOwner::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function caterer()
    {
        return $this->belongsTo(Caterer::class, 'CatererID', 'CatererID');
    }
}
