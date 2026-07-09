<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatAdReviewSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('live_chats')->insert([
            ['SenderID'=>3,'ReceiverID'=>13,'Message'=>'Hi! Can I customize my Koshary order? I want extra sauce.','Timestamp'=>'2026-02-11 10:00:00'],
            ['SenderID'=>13,'ReceiverID'=>3,'Message'=>'Of course! We can add extra spicy sauce no problem.','Timestamp'=>'2026-02-11 10:02:00'],
            ['SenderID'=>3,'ReceiverID'=>13,'Message'=>'Perfect, thank you so much!','Timestamp'=>'2026-02-11 10:03:00'],
            ['SenderID'=>4,'ReceiverID'=>14,'Message'=>'Do you offer meal prep packages for the week?','Timestamp'=>'2026-02-11 11:30:00'],
            ['SenderID'=>14,'ReceiverID'=>4,'Message'=>'Yes! Check our subscription plans. We have weekly and monthly options.','Timestamp'=>'2026-02-11 11:32:00'],
            ['SenderID'=>5,'ReceiverID'=>16,'Message'=>'Is the Mixed Grill Platter enough for 2 people?','Timestamp'=>'2026-02-10 14:00:00'],
            ['SenderID'=>16,'ReceiverID'=>5,'Message'=>'Absolutely! It serves 2-3 people generously. Want me to add extra bread?','Timestamp'=>'2026-02-10 14:05:00'],
            ['SenderID'=>5,'ReceiverID'=>16,'Message'=>'Yes please! And extra hummus too.','Timestamp'=>'2026-02-10 14:06:00'],
            ['SenderID'=>7,'ReceiverID'=>18,'Message'=>'Are your keto meals truly zero carb?','Timestamp'=>'2026-02-11 09:15:00'],
            ['SenderID'=>18,'ReceiverID'=>7,'Message'=>'Our keto meals are under 5g net carbs per serving. All nutritional info is on the menu!','Timestamp'=>'2026-02-11 09:20:00'],
            ['SenderID'=>6,'ReceiverID'=>20,'Message'=>"What's the catch of the day?",'Timestamp'=>'2026-02-11 15:00:00'],
            ['SenderID'=>20,'ReceiverID'=>6,'Message'=>'Today we have fresh Red Sea grouper and Mediterranean sea bass! Both are excellent grilled.','Timestamp'=>'2026-02-11 15:03:00'],
        ]);

        DB::table('advertisings')->insert([
            ['PaymentID'=>2,'KitchenOwnerID'=>1,'CatererID'=>null,'Title'=>'Mama Kitchen Grand Opening Sale!','Description'=>'20% off all items for the first week!','StartDate'=>'2026-02-01','EndDate'=>'2026-02-28','Status'=>'Active'],
            ['PaymentID'=>4,'KitchenOwnerID'=>4,'CatererID'=>null,'Title'=>"Amira's Shawarma Festival",'Description'=>'Buy 2 shawarma plates, get 1 free!','StartDate'=>'2026-02-10','EndDate'=>'2026-02-20','Status'=>'Active'],
            ['PaymentID'=>2,'KitchenOwnerID'=>null,'CatererID'=>1,'Title'=>'Golden Plate Wedding Season Special','Description'=>'Book your wedding catering before March and get 15% discount!','StartDate'=>'2026-02-01','EndDate'=>'2026-03-31','Status'=>'Active'],
            ['PaymentID'=>3,'KitchenOwnerID'=>6,'CatererID'=>null,'Title'=>'Healthy New Year Challenge','Description'=>'Start your fitness journey with our meal prep packages. First week 30% off!','StartDate'=>'2026-02-01','EndDate'=>'2026-02-15','Status'=>'Active'],
            ['PaymentID'=>1,'KitchenOwnerID'=>5,'CatererID'=>null,'Title'=>'Ramadan Kunafa Pre-Orders','Description'=>'Pre-order your Ramadan kunafa now and enjoy free delivery!','StartDate'=>'2026-02-15','EndDate'=>'2026-03-15','Status'=>'Inactive'],
        ]);

        DB::table('reviews')->insert([
            ['CustomerID'=>1,'KitchenOwnerID'=>1,'CatererID'=>null,'OrderID'=>1,'Rating'=>5,'Comment'=>'Absolutely amazing koshary! Tastes just like my grandmother used to make.','CreatedAt'=>'2026-02-10 12:00:00'],
            ['CustomerID'=>1,'KitchenOwnerID'=>1,'CatererID'=>null,'OrderID'=>2,'Rating'=>4,'Comment'=>'Molokhia was great but the basbousa was a little too sweet for my taste.','CreatedAt'=>'2026-02-09 16:00:00'],
            ['CustomerID'=>2,'KitchenOwnerID'=>2,'CatererID'=>null,'OrderID'=>3,'Rating'=>5,'Comment'=>'The Mediterranean Bowl is so fresh and delicious! Best healthy food in Cairo.','CreatedAt'=>'2026-02-09 20:00:00'],
            ['CustomerID'=>3,'KitchenOwnerID'=>2,'CatererID'=>null,'OrderID'=>4,'Rating'=>4,'Comment'=>'Shakshuka was perfectly cooked. Quick delivery too.','CreatedAt'=>'2026-02-08 13:30:00'],
            ['CustomerID'=>4,'KitchenOwnerID'=>4,'CatererID'=>null,'OrderID'=>5,'Rating'=>5,'Comment'=>'Mixed Grill Platter was INCREDIBLE. Huge portions!','CreatedAt'=>'2026-02-08 21:00:00'],
            ['CustomerID'=>5,'KitchenOwnerID'=>6,'CatererID'=>null,'OrderID'=>6,'Rating'=>5,'Comment'=>'Best healthy food service! The protein bowl is my new addiction.','CreatedAt'=>'2026-02-07 15:00:00'],
            ['CustomerID'=>1,'KitchenOwnerID'=>1,'CatererID'=>null,'OrderID'=>13,'Rating'=>4,'Comment'=>'Always consistent quality. Mama Kitchen never disappoints!','CreatedAt'=>'2026-02-06 13:00:00'],
            ['CustomerID'=>3,'KitchenOwnerID'=>3,'CatererID'=>null,'OrderID'=>14,'Rating'=>5,'Comment'=>'Mulukhiyah rabbit was outstanding! Authentic Upper Egyptian flavors.','CreatedAt'=>'2026-02-05 18:00:00'],
            ['CustomerID'=>9,'KitchenOwnerID'=>7,'CatererID'=>null,'OrderID'=>16,'Rating'=>5,'Comment'=>'Egyptian Sushi? GENIUS! It actually tastes amazing.','CreatedAt'=>'2026-02-04 14:00:00'],
            ['CustomerID'=>10,'KitchenOwnerID'=>7,'CatererID'=>null,'OrderID'=>17,'Rating'=>4,'Comment'=>'Pharaoh Burger is next level. The dukkah sauce is incredible.','CreatedAt'=>'2026-02-03 16:00:00'],
            ['CustomerID'=>2,'KitchenOwnerID'=>8,'CatererID'=>null,'OrderID'=>19,'Rating'=>5,'Comment'=>'Seafood platter was super fresh and beautifully presented.','CreatedAt'=>'2026-02-02 20:00:00'],
            ['CustomerID'=>8,'KitchenOwnerID'=>1,'CatererID'=>null,'OrderID'=>24,'Rating'=>4,'Comment'=>'Good food as always. Delivery was a bit late but the food made up for it.','CreatedAt'=>'2026-02-01 12:00:00'],
            ['CustomerID'=>4,'KitchenOwnerID'=>4,'CatererID'=>null,'OrderID'=>25,'Rating'=>5,'Comment'=>"Fattah was divine! Best I've had outside of a wedding. 10/10",'CreatedAt'=>'2026-01-30 19:00:00'],
            ['CustomerID'=>6,'KitchenOwnerID'=>5,'CatererID'=>null,'OrderID'=>null,'Rating'=>5,'Comment'=>"Rania's kunafa is the best in Cairo, period.",'CreatedAt'=>'2026-02-11 16:00:00'],
            ['CustomerID'=>7,'KitchenOwnerID'=>6,'CatererID'=>null,'OrderID'=>null,'Rating'=>4,'Comment'=>'Love the keto options! Finally a kitchen that understands clean eating.','CreatedAt'=>'2026-02-11 10:00:00'],
        ]);
    }
}
