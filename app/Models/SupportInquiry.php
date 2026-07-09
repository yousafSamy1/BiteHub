<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportInquiry extends Model
{
    protected $table = 'support_inquiries';
    protected $primaryKey = 'InquiryID';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'InquiryID', 'InquiryID');
    }
}
