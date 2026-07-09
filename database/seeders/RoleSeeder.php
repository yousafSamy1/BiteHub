<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insert([['UserID'=>1],['UserID'=>2]]);

        DB::table('customers')->insert([
            ['UserID'=>3,'WalletBalance'=>250.00],['UserID'=>4,'WalletBalance'=>175.50],
            ['UserID'=>5,'WalletBalance'=>320.00],['UserID'=>6,'WalletBalance'=>80.00],
            ['UserID'=>7,'WalletBalance'=>450.00],['UserID'=>8,'WalletBalance'=>95.00],
            ['UserID'=>9,'WalletBalance'=>200.00],['UserID'=>10,'WalletBalance'=>150.00],
            ['UserID'=>11,'WalletBalance'=>60.00],['UserID'=>12,'WalletBalance'=>310.00],
        ]);

        DB::table('kitchen_owners')->insert([
            ['UserID'=>13,'KitchenName'=>'Mama Kitchen','Description'=>'Authentic homemade Egyptian food made with love. Specializing in traditional recipes passed down through generations.','Status'=>'Active','VerifyStatus'=>'Verified','Attachment'=>null],
            ['UserID'=>14,'KitchenName'=>"Nour's Delights",'Description'=>'Fresh and healthy Mediterranean cuisine with a modern twist. Farm-to-table ingredients sourced from local Egyptian farms.','Status'=>'Active','VerifyStatus'=>'Verified','Attachment'=>null],
            ['UserID'=>15,'KitchenName'=>"Fatma's Table",'Description'=>"Grandmother's secret recipes brought to your doorstep. Authentic Upper Egyptian cuisine with rich flavors and generous portions.",'Status'=>'Active','VerifyStatus'=>'Verified','Attachment'=>null],
            ['UserID'=>16,'KitchenName'=>"Amira's Palace",'Description'=>'Premium Syrian and Lebanese cuisine prepared with authentic spices. From creamy hummus to perfectly grilled meats.','Status'=>'Active','VerifyStatus'=>'Verified','Attachment'=>null],
            ['UserID'=>17,'KitchenName'=>"Rania's Sweets",'Description'=>'Handcrafted Oriental desserts and pastries. From kunafa to baklava, each piece is a work of art.','Status'=>'Active','VerifyStatus'=>'Verified','Attachment'=>null],
            ['UserID'=>18,'KitchenName'=>"Heba's Healthy Bites",'Description'=>'Clean eating made delicious! Keto, vegan, and gluten-free options available.','Status'=>'Active','VerifyStatus'=>'Verified','Attachment'=>null],
            ['UserID'=>19,'KitchenName'=>"Yasmine's Kitchen",'Description'=>'Fusion cuisine blending Egyptian tradition with international flavors. Creative dishes that surprise and delight.','Status'=>'Active','VerifyStatus'=>'Pending','Attachment'=>null],
            ['UserID'=>20,'KitchenName'=>"Samira's Seafood",'Description'=>"Fresh seafood dishes from Alexandria's finest recipes. Grilled, fried, or steamed.",'Status'=>'Active','VerifyStatus'=>'Pending','Attachment'=>null],
        ]);

        DB::table('caterers')->insert([
            ['UserID'=>21,'BusinessName'=>'Golden Plate Catering','Description'=>'Professional catering for events of all sizes.','Attachment'=>null,'IsActive'=>true],
            ['UserID'=>22,'BusinessName'=>'Royal Catering Co','Description'=>'Luxury catering for weddings, galas, and VIP events.','Attachment'=>null,'IsActive'=>true],
            ['UserID'=>23,'BusinessName'=>'Elite Events Catering','Description'=>'Corporate catering specialists.','Attachment'=>null,'IsActive'=>true],
            ['UserID'=>24,'BusinessName'=>'Nile Feasts','Description'=>'Traditional Egyptian banquet catering.','Attachment'=>null,'IsActive'=>true],
        ]);

        DB::table('delivery_agents')->insert([
            ['UserID'=>25,'VehicleType'=>'Motorcycle','PlateNumber'=>'CAI-1234','Status'=>'Available','Attachment'=>null],
            ['UserID'=>26,'VehicleType'=>'Bicycle','PlateNumber'=>'N/A','Status'=>'Available','Attachment'=>null],
            ['UserID'=>27,'VehicleType'=>'Motorcycle','PlateNumber'=>'GIZ-5678','Status'=>'Available','Attachment'=>null],
            ['UserID'=>28,'VehicleType'=>'Car','PlateNumber'=>'ALX-9012','Status'=>'Offline','Attachment'=>null],
        ]);
    }
}
