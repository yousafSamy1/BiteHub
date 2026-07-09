<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'icon', 'category'];

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_tag', 'tag_id', 'menu_item_id');
    }
}
