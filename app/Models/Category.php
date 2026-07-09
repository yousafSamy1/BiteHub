<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'CategoryID';
    public $timestamps = false;
    protected $guarded = [];

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'CategoryID', 'CategoryID');
    }
}
