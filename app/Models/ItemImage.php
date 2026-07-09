<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    protected $table = 'item_images';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'MenuItemID', 'MenuItemID');
    }
}
