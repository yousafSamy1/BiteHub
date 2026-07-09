<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subscriptions')->insert([
            ['CustomerID'=>1,'PlanTime'=>'Monthly','Price'=>200.00,'StartDate'=>'2026-02-01','EndDate'=>'2026-03-01','Status'=>'Active'],
            ['CustomerID'=>1,'PlanTime'=>'Weekly','Price'=>60.00,'StartDate'=>'2026-02-01','EndDate'=>'2026-02-08','Status'=>'Active'],
            ['CustomerID'=>2,'PlanTime'=>'Monthly','Price'=>200.00,'StartDate'=>'2026-01-15','EndDate'=>'2026-02-15','Status'=>'Active'],
            ['CustomerID'=>3,'PlanTime'=>'Weekly','Price'=>60.00,'StartDate'=>'2026-02-05','EndDate'=>'2026-02-12','Status'=>'Active'],
            ['CustomerID'=>5,'PlanTime'=>'Monthly','Price'=>200.00,'StartDate'=>'2026-01-01','EndDate'=>'2026-02-01','Status'=>'Expired'],
            ['CustomerID'=>7,'PlanTime'=>'Daily','Price'=>25.00,'StartDate'=>'2026-02-11','EndDate'=>'2026-02-12','Status'=>'Active'],
            ['CustomerID'=>4,'PlanTime'=>'Weekly','Price'=>60.00,'StartDate'=>'2026-01-20','EndDate'=>'2026-01-27','Status'=>'Expired'],
            ['CustomerID'=>9,'PlanTime'=>'Monthly','Price'=>200.00,'StartDate'=>'2026-02-10','EndDate'=>'2026-03-10','Status'=>'Active'],
        ]);

        DB::table('subscription_payments')->insert([
            ['PaymentID'=>2,'SubscriptionID'=>1],['PaymentID'=>2,'SubscriptionID'=>2],
            ['PaymentID'=>4,'SubscriptionID'=>3],['PaymentID'=>1,'SubscriptionID'=>4],
            ['PaymentID'=>3,'SubscriptionID'=>5],['PaymentID'=>1,'SubscriptionID'=>6],
            ['PaymentID'=>2,'SubscriptionID'=>7],['PaymentID'=>4,'SubscriptionID'=>8],
        ]);

        DB::table('menu_subscribes')->insert([
            ['SubscriptionID'=>1,'MenuItemID'=>1],['SubscriptionID'=>1,'MenuItemID'=>2],['SubscriptionID'=>1,'MenuItemID'=>3],['SubscriptionID'=>1,'MenuItemID'=>9],
            ['SubscriptionID'=>2,'MenuItemID'=>7],['SubscriptionID'=>2,'MenuItemID'=>12],['SubscriptionID'=>2,'MenuItemID'=>13],
            ['SubscriptionID'=>3,'MenuItemID'=>9],['SubscriptionID'=>3,'MenuItemID'=>10],['SubscriptionID'=>3,'MenuItemID'=>14],
            ['SubscriptionID'=>4,'MenuItemID'=>32],['SubscriptionID'=>4,'MenuItemID'=>33],['SubscriptionID'=>4,'MenuItemID'=>36],
            ['SubscriptionID'=>6,'MenuItemID'=>1],['SubscriptionID'=>6,'MenuItemID'=>9],['SubscriptionID'=>6,'MenuItemID'=>32],
            ['SubscriptionID'=>8,'MenuItemID'=>33],['SubscriptionID'=>8,'MenuItemID'=>34],['SubscriptionID'=>8,'MenuItemID'=>35],['SubscriptionID'=>8,'MenuItemID'=>36],
        ]);
    }
}
