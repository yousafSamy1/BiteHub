<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\KitchenOwner;
use App\Models\Caterer;
use App\Models\DeliveryAgent;
use App\Models\CateringRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailySummaryService
{
    public function getSummaryData()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();

        return [
            'date' => Carbon::now()->format('l, j F Y'),
            
            // --- A. FINANCIAL KPIs ---
            'financial' => [
                'revenue' => $this->compareQuery(function($start, $end) {
                    return Order::whereBetween('CreatedAt', [$start, $end])->whereNotIn('OrderStatus', ['Cancelled'])->sum('TotalPrice');
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),
                
                'lost_revenue' => $this->compareQuery(function($start, $end) {
                    return Order::whereBetween('CreatedAt', [$start, $end])->where('OrderStatus', 'Cancelled')->sum('TotalPrice');
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),

                'aov' => $this->compareQuery(function($start, $end) {
                    $revenue = Order::whereBetween('CreatedAt', [$start, $end])->whereNotIn('OrderStatus', ['Cancelled'])->sum('TotalPrice');
                    $count = Order::whereBetween('CreatedAt', [$start, $end])->whereNotIn('OrderStatus', ['Cancelled'])->count();
                    return $count > 0 ? $revenue / $count : 0;
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),
            ],

            // --- B. OPERATIONAL KPIs ---
            'operational' => [
                'total_orders' => $this->compareQuery(function($start, $end) {
                    return Order::whereBetween('CreatedAt', [$start, $end])->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),

                'cancelled_orders' => $this->compareQuery(function($start, $end) {
                    return Order::whereBetween('CreatedAt', [$start, $end])->where('OrderStatus', 'Cancelled')->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),
                
                'catering_requests' => $this->compareQuery(function($start, $end) {
                    return CateringRequest::whereBetween('CreatedAt', [$start, $end])->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),

                'new_subscriptions' => $this->compareQuery(function($start, $end) {
                    return Subscription::whereBetween('StartDate', [$start, $end])->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),
            ],

            // --- C. PROVIDER ONBOARDING & GROWTH ---
            'onboarding' => [
                'pending_kitchens' => KitchenOwner::where('VerifyStatus', 'Pending')->count(), // Exact snapshot right now
                
                'new_customers' => $this->compareQuery(function($start, $end) {
                    return User::where('Role', 'Customer')->whereBetween('CreatedAt', [$start, $end])->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),

                'new_kitchens' => $this->compareQuery(function($start, $end) {
                    return KitchenOwner::join('users', 'kitchen_owners.UserID', '=', 'users.UserID')
                        ->whereBetween('users.CreatedAt', [$start, $end])->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),

                'new_caterers' => $this->compareQuery(function($start, $end) {
                    return Caterer::join('users', 'caterers.UserID', '=', 'users.UserID')
                        ->whereBetween('users.CreatedAt', [$start, $end])->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),
                
                'new_agents' => $this->compareQuery(function($start, $end) {
                    return DeliveryAgent::join('users', 'delivery_agents.UserID', '=', 'users.UserID')
                        ->whereBetween('users.CreatedAt', [$start, $end])->count();
                }, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd),
            ]
        ];
    }

    /**
     * Helper to compute today, yesterday, absolute diff, and percentage trend.
     */
    private function compareQuery(callable $closure, $todayStart, $todayEnd, $yesterdayStart, $yesterdayEnd)
    {
        $today = $closure($todayStart, $todayEnd);
        $yesterday = $closure($yesterdayStart, $yesterdayEnd);
        
        // Handling AOV (decimals) or normal Counts
        $todayVal = is_float($today) ? $today : (int)$today;
        $yesterdayVal = is_float($yesterday) ? $yesterday : (int)$yesterday;

        $diff = $todayVal - $yesterdayVal;
        
        if ($yesterdayVal == 0) {
            $trend = $todayVal > 0 ? 100 : 0;
        } else {
            $trend = round((($todayVal - $yesterdayVal) / $yesterdayVal) * 100, 1);
        }

        return [
            'today' => $todayVal,
            'yesterday' => $yesterdayVal,
            'diff' => $diff,
            'trend' => $trend,
        ];
    }
}
