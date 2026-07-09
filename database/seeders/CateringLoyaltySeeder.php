<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CateringLoyaltySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('catering_requests')->insert([
            ['CustomerID'=>2,'CatererID'=>1,'EventType'=>'Wedding','EventDate'=>'2026-03-15','GuestCount'=>200,'Budget'=>15000.00,'Details'=>'Egyptian-themed wedding reception. Need full service including setup, waitstaff, and traditional dishes.','Status'=>'Pending'],
            ['CustomerID'=>5,'CatererID'=>3,'EventType'=>'Corporate','EventDate'=>'2026-03-20','GuestCount'=>80,'Budget'=>4000.00,'Details'=>'Quarterly company celebration. Need boxed lunches and a dessert table.','Status'=>'Accepted'],
            ['CustomerID'=>3,'CatererID'=>2,'EventType'=>'Birthday Party','EventDate'=>'2026-02-28','GuestCount'=>50,'Budget'=>3000.00,'Details'=>'30th birthday party. Want a premium menu with live cooking station.','Status'=>'Pending'],
            ['CustomerID'=>7,'CatererID'=>4,'EventType'=>'Family Gathering','EventDate'=>'2026-03-01','GuestCount'=>30,'Budget'=>2000.00,'Details'=>'Family reunion dinner. Traditional Egyptian buffet style.','Status'=>'Accepted'],
            ['CustomerID'=>4,'CatererID'=>1,'EventType'=>'Engagement','EventDate'=>'2026-04-10','GuestCount'=>150,'Budget'=>12000.00,'Details'=>'Engagement party with mixed Egyptian and Lebanese menu.','Status'=>'Pending'],
            ['CustomerID'=>10,'CatererID'=>2,'EventType'=>'Graduation','EventDate'=>'2026-06-15','GuestCount'=>100,'Budget'=>8000.00,'Details'=>'University graduation celebration. Modern fusion menu preferred.','Status'=>'Pending'],
        ]);

        DB::table('loyalty_transactions')->insert([
            ['CustomerID'=>1,'Points'=>15,'Type'=>'Earned','Description'=>'Points from Order #1','CreatedAt'=>'2026-02-10 12:00:00'],
            ['CustomerID'=>1,'Points'=>17,'Type'=>'Earned','Description'=>'Points from Order #2','CreatedAt'=>'2026-02-09 16:00:00'],
            ['CustomerID'=>1,'Points'=>30,'Type'=>'Earned','Description'=>'Points from Order #7','CreatedAt'=>'2026-02-11 20:30:00'],
            ['CustomerID'=>1,'Points'=>36,'Type'=>'Earned','Description'=>'Points from Order #13','CreatedAt'=>'2026-02-06 13:00:00'],
            ['CustomerID'=>1,'Points'=>-50,'Type'=>'Redeemed','Description'=>'Redeemed for 25 EGP discount on Order #7','CreatedAt'=>'2026-02-11 19:55:00'],
            ['CustomerID'=>2,'Points'=>24,'Type'=>'Earned','Description'=>'Points from Order #3','CreatedAt'=>'2026-02-09 20:00:00'],
            ['CustomerID'=>2,'Points'=>64,'Type'=>'Earned','Description'=>'Points from Order #19','CreatedAt'=>'2026-02-02 20:00:00'],
            ['CustomerID'=>2,'Points'=>50,'Type'=>'Bonus','Description'=>'Welcome bonus for new subscription!','CreatedAt'=>'2026-01-15 08:00:00'],
            ['CustomerID'=>3,'Points'=>9,'Type'=>'Earned','Description'=>'Points from Order #4','CreatedAt'=>'2026-02-08 13:30:00'],
            ['CustomerID'=>3,'Points'=>46,'Type'=>'Earned','Description'=>'Points from Order #9','CreatedAt'=>'2026-02-11 23:45:00'],
            ['CustomerID'=>3,'Points'=>13,'Type'=>'Earned','Description'=>'Points from Order #14','CreatedAt'=>'2026-02-05 18:00:00'],
            ['CustomerID'=>4,'Points'=>40,'Type'=>'Earned','Description'=>'Points from Order #5','CreatedAt'=>'2026-02-08 21:00:00'],
            ['CustomerID'=>4,'Points'=>38,'Type'=>'Earned','Description'=>'Points from Order #25','CreatedAt'=>'2026-01-30 19:00:00'],
            ['CustomerID'=>5,'Points'=>19,'Type'=>'Earned','Description'=>'Points from Order #6','CreatedAt'=>'2026-02-07 15:00:00'],
            ['CustomerID'=>5,'Points'=>25,'Type'=>'Referral','Description'=>'Referral bonus: invited Khaled!','CreatedAt'=>'2026-01-25 10:00:00'],
            ['CustomerID'=>7,'Points'=>14,'Type'=>'Earned','Description'=>'Points from Order #11','CreatedAt'=>'2026-02-12 01:00:00'],
            ['CustomerID'=>8,'Points'=>18,'Type'=>'Earned','Description'=>'Points from Order #12','CreatedAt'=>'2026-02-12 01:10:00'],
            ['CustomerID'=>8,'Points'=>15,'Type'=>'Earned','Description'=>'Points from Order #24','CreatedAt'=>'2026-02-01 12:00:00'],
            ['CustomerID'=>9,'Points'=>16,'Type'=>'Earned','Description'=>'Points from Order #16','CreatedAt'=>'2026-02-04 14:00:00'],
            ['CustomerID'=>9,'Points'=>27,'Type'=>'Earned','Description'=>'Points from Order #22','CreatedAt'=>'2026-02-11 21:15:00'],
            ['CustomerID'=>10,'Points'=>29,'Type'=>'Earned','Description'=>'Points from Order #17','CreatedAt'=>'2026-02-03 16:00:00'],
            ['CustomerID'=>10,'Points'=>50,'Type'=>'Earned','Description'=>'Points from Order #23','CreatedAt'=>'2026-02-12 01:20:00'],
        ]);
    }
}
