<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $table = 'withdrawal_requests';
    protected $primaryKey = 'RequestID';

    protected $fillable = [
        'UserID',
        'Amount',
        'Commission',
        'NetAmount',
        'Method',
        'MethodDetails',
        'Status',
        'AdminNotes',
    ];

    protected $casts = [
        'MethodDetails' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}
