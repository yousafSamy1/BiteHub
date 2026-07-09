<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('item_images')->insert([
            // Mama Kitchen (KO 1) - Items 1-8
            ['MenuItemID'=>1,'Image'=>'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400'],
            ['MenuItemID'=>2,'Image'=>'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400'],
            ['MenuItemID'=>3,'Image'=>'https://images.unsplash.com/photo-1627308595229-7830a5c91f9f?w=400'],
            ['MenuItemID'=>4,'Image'=>'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?w=400'],
            ['MenuItemID'=>5,'Image'=>'https://images.unsplash.com/photo-1547592180-85f173990554?w=400'],
            ['MenuItemID'=>6,'Image'=>'https://images.unsplash.com/photo-1546173159-315724a31696?w=400'],
            ['MenuItemID'=>7,'Image'=>'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400'],
            ['MenuItemID'=>8,'Image'=>'https://images.unsplash.com/photo-1508737027454-e6454ef45adb?w=400'],
            // Nour's Delights (KO 2) - Items 9-14
            ['MenuItemID'=>9,'Image'=>'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400'],
            ['MenuItemID'=>10,'Image'=>'https://images.unsplash.com/photo-1505253716362-afaea1d3d1af?w=400'],
            ['MenuItemID'=>11,'Image'=>'https://images.unsplash.com/photo-1519915028121-7d3463d20b13?w=400'],
            ['MenuItemID'=>12,'Image'=>'https://images.unsplash.com/photo-1590412200988-a436970781fa?w=400'],
            ['MenuItemID'=>13,'Image'=>'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=400'],
            ['MenuItemID'=>14,'Image'=>'https://images.unsplash.com/photo-1610970881699-44a5587cabec?w=400'],
            // Fatma's Table (KO 3) - Items 15-19
            ['MenuItemID'=>15,'Image'=>'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400'],
            ['MenuItemID'=>16,'Image'=>'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400'],
            ['MenuItemID'=>17,'Image'=>'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400'],
            ['MenuItemID'=>18,'Image'=>'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400'],
            ['MenuItemID'=>19,'Image'=>'https://images.unsplash.com/photo-1544025162-d76694265947?w=400'],
            // Amira's Palace (KO 4) - Items 20-25
            ['MenuItemID'=>20,'Image'=>'https://images.unsplash.com/photo-1529006557810-274b9b2fc783?w=400'],
            ['MenuItemID'=>21,'Image'=>'https://images.unsplash.com/photo-1577805947697-89e18249d767?w=400'],
            ['MenuItemID'=>22,'Image'=>'https://images.unsplash.com/photo-1544025162-d76694265947?w=400'],
            ['MenuItemID'=>23,'Image'=>'https://images.unsplash.com/photo-1547592180-85f173990554?w=400'],
            ['MenuItemID'=>24,'Image'=>'https://images.unsplash.com/photo-1519915028121-7d3463d20b13?w=400'],
            ['MenuItemID'=>25,'Image'=>'https://images.unsplash.com/photo-1514066558159-fc8c737ef259?w=400'],
            // Rania's Sweets (KO 5) - Items 26-31
            ['MenuItemID'=>26,'Image'=>'https://images.unsplash.com/photo-1519915028121-7d3463d20b13?w=400'],
            ['MenuItemID'=>27,'Image'=>'https://images.unsplash.com/photo-1508737027454-e6454ef45adb?w=400'],
            ['MenuItemID'=>28,'Image'=>'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?w=400'],
            ['MenuItemID'=>29,'Image'=>'https://images.unsplash.com/photo-1519915028121-7d3463d20b13?w=400'],
            ['MenuItemID'=>30,'Image'=>'https://images.unsplash.com/photo-1514066558159-fc8c737ef259?w=400'],
            ['MenuItemID'=>31,'Image'=>'https://images.unsplash.com/photo-1519915028121-7d3463d20b13?w=400'],
            // Heba's Healthy Bites (KO 6) - Items 32-36
            ['MenuItemID'=>32,'Image'=>'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400'],
            ['MenuItemID'=>33,'Image'=>'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400'],
            ['MenuItemID'=>34,'Image'=>'https://images.unsplash.com/photo-1544025162-d76694265947?w=400'],
            ['MenuItemID'=>35,'Image'=>'https://images.unsplash.com/photo-1590301157890-4810ed352733?w=400'],
            ['MenuItemID'=>36,'Image'=>'https://images.unsplash.com/photo-1610970881699-44a5587cabec?w=400'],
            // Yasmine's Kitchen (KO 7) - Items 37-41
            ['MenuItemID'=>37,'Image'=>'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400'],
            ['MenuItemID'=>38,'Image'=>'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400'],
            ['MenuItemID'=>39,'Image'=>'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400'],
            ['MenuItemID'=>40,'Image'=>'https://images.unsplash.com/photo-1508737027454-e6454ef45adb?w=400'],
            ['MenuItemID'=>41,'Image'=>'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=400'],
            // Samira's Seafood (KO 8) - Items 42-47
            ['MenuItemID'=>42,'Image'=>'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400'],
            ['MenuItemID'=>43,'Image'=>'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=400'],
            ['MenuItemID'=>44,'Image'=>'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=400'],
            ['MenuItemID'=>45,'Image'=>'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400'],
            ['MenuItemID'=>46,'Image'=>'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400'],
            ['MenuItemID'=>47,'Image'=>'https://images.unsplash.com/photo-1580217593608-61931cefc821?w=400'],
            // Caterer items 48-51
            ['MenuItemID'=>48,'Image'=>'https://images.unsplash.com/photo-1555244162-803834f70033?w=400'],
            ['MenuItemID'=>49,'Image'=>'https://images.unsplash.com/photo-1555244162-803834f70033?w=400'],
            ['MenuItemID'=>50,'Image'=>'https://images.unsplash.com/photo-1547592180-85f173990554?w=400'],
            ['MenuItemID'=>51,'Image'=>'https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=400'],
        ]);
    }
}
