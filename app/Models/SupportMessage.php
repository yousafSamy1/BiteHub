<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $table = 'support_messages';
    protected $primaryKey = 'MessageID';
    protected $guarded = [];

    public function inquiry()
    {
        return $this->belongsTo(SupportInquiry::class, 'InquiryID', 'InquiryID');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'SenderID', 'UserID');
    }
}
