<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryPaymentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['Name'=>'Main Meals','Description'=>'Hearty main courses and traditional Egyptian dishes'],
            ['Name'=>'Desserts','Description'=>'Sweet treats, Oriental pastries, and traditional desserts'],
            ['Name'=>'Appetizers','Description'=>'Starters, dips, and side dishes to kick off your meal'],
            ['Name'=>'Beverages','Description'=>'Fresh juices, smoothies, hot drinks, and traditional drinks'],
            ['Name'=>'Breakfast','Description'=>'Morning meals, brunch items, and Egyptian breakfast classics'],
            ['Name'=>'Seafood','Description'=>'Fresh fish, shrimp, calamari, and seafood platters'],
            ['Name'=>'Grills & BBQ','Description'=>'Premium grilled meats, kebabs, and BBQ specialties'],
            ['Name'=>'Healthy','Description'=>'Low-calorie, keto, vegan, and gluten-free options'],
        ]);

        DB::table('payments')->insert([
            ['Method'=>'Cash'],['Method'=>'Card'],['Method'=>'Wallet'],['Method'=>'Online'],
        ]);
    }
}
