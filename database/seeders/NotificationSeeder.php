<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('notifications')->insert([
            ['UserID'=>3,'Title'=>'Order Delivered!','Message'=>'Your order #1 has been delivered. Enjoy your meal!','IsRead'=>true,'Type'=>'Order','CreatedAt'=>'2026-02-10 11:00:00'],
            ['UserID'=>3,'Title'=>'New Promotion!','Message'=>'Mama Kitchen: 20% off all items this week!','IsRead'=>false,'Type'=>'Promotion','CreatedAt'=>'2026-02-11 08:00:00'],
            ['UserID'=>3,'Title'=>'Order on the way!','Message'=>'Your order #7 is being delivered by Mahmoud.','IsRead'=>false,'Type'=>'Order','CreatedAt'=>'2026-02-11 20:30:00'],
            ['UserID'=>4,'Title'=>'Order Delivered!','Message'=>'Your order #3 has been delivered. Rate your experience!','IsRead'=>true,'Type'=>'Order','CreatedAt'=>'2026-02-09 19:30:00'],
            ['UserID'=>4,'Title'=>'Subscription Alert','Message'=>'Your weekly subscription is expiring tomorrow.','IsRead'=>false,'Type'=>'System','CreatedAt'=>'2026-02-11 09:00:00'],
            ['UserID'=>5,'Title'=>'Points Earned!','Message'=>'You earned 46 loyalty points from order #9!','IsRead'=>false,'Type'=>'Order','CreatedAt'=>'2026-02-11 23:45:00'],
            ['UserID'=>13,'Title'=>'New Order!','Message'=>'You received a new order from Ahmed Hassan.','IsRead'=>false,'Type'=>'Order','CreatedAt'=>'2026-02-12 00:15:00'],
            ['UserID'=>13,'Title'=>'Great Review!','Message'=>'Ahmed gave you 5 stars! "Absolutely amazing koshary!"','IsRead'=>true,'Type'=>'System','CreatedAt'=>'2026-02-10 12:05:00'],
            ['UserID'=>25,'Title'=>'New Delivery!','Message'=>'You have a new delivery assignment for order #7.','IsRead'=>false,'Type'=>'Order','CreatedAt'=>'2026-02-11 20:10:00'],
            ['UserID'=>1,'Title'=>'System Alert','Message'=>'New kitchen registration pending verification: Yasmine Kitchen','IsRead'=>false,'Type'=>'System','CreatedAt'=>'2026-02-11 14:00:00'],
            ['UserID'=>21,'Title'=>'Catering Request','Message'=>'New wedding catering request from Layla for March 15.','IsRead'=>false,'Type'=>'Order','CreatedAt'=>'2026-02-11 12:00:00'],
            ['UserID'=>7,'Title'=>'Welcome to BiteHub!','Message'=>'Thanks for subscribing! Your daily plan starts today.','IsRead'=>true,'Type'=>'System','CreatedAt'=>'2026-02-11 08:00:00'],
        ]);
    }
}
