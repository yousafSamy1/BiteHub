<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenPlan extends Model
{
    use HasFactory;

    protected $table = 'kitchen_plans';
    protected $primaryKey = 'KitchenPlanID';

    protected $fillable = [
        'KitchenOwnerID',
        'Title',
        'Description',
        'Price',
        'PlanTime',
        'MealsPerDay',
        'Status',
    ];

    public function kitchen()
    {
        return $this->belongsTo(KitchenOwner::class, 'KitchenOwnerID', 'KitchenOwnerID');
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'plan_menu_items', 'KitchenPlanID', 'MenuItemID');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'KitchenPlanID', 'KitchenPlanID');
    }
}
