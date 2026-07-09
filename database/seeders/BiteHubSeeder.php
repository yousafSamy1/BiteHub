<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BiteHubSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoleSeeder::class,
            CategoryPaymentSeeder::class,
            MenuItemSeeder::class,
            ItemImageSeeder::class,
            OrderSeeder::class,
            SubscriptionSeeder::class,
            ChatAdReviewSeeder::class,
            NotificationSeeder::class,
            CateringLoyaltySeeder::class,
            PhoneAddressSeeder::class,
        ]);
    }
}
