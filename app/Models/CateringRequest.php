<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringRequest extends Model
{
    protected $table = 'catering_requests';
    protected $primaryKey = 'RequestID';
    public $timestamps = false;
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'CustomerID', 'CustomerID');
    }

    public function caterer()
    {
        return $this->belongsTo(Caterer::class, 'CatererID', 'CatererID');
    }
}
