<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'saved_payment_methods';

    protected $fillable = [
        'UserID',
        'Type',
        'Details',
        'IsPrimary',
    ];

    protected $casts = [
        'Details' => 'array',
        'IsPrimary' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}
