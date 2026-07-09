<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhoneAddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_phones')->insert([
            ['UserID'=>3,'PhoneNumber'=>'+20-100-123-4567'],['UserID'=>3,'PhoneNumber'=>'+20-112-987-6543'],
            ['UserID'=>4,'PhoneNumber'=>'+20-101-234-5678'],
            ['UserID'=>5,'PhoneNumber'=>'+20-100-345-6789'],
            ['UserID'=>6,'PhoneNumber'=>'+20-111-456-7890'],
            ['UserID'=>7,'PhoneNumber'=>'+20-102-567-8901'],
            ['UserID'=>8,'PhoneNumber'=>'+20-100-678-9012'],
            ['UserID'=>9,'PhoneNumber'=>'+20-112-789-0123'],
            ['UserID'=>10,'PhoneNumber'=>'+20-101-890-1234'],
            ['UserID'=>11,'PhoneNumber'=>'+20-100-901-2345'],
            ['UserID'=>12,'PhoneNumber'=>'+20-111-012-3456'],
            ['UserID'=>13,'PhoneNumber'=>'+20-111-987-6543'],
            ['UserID'=>14,'PhoneNumber'=>'+20-100-876-5432'],
            ['UserID'=>15,'PhoneNumber'=>'+20-112-765-4321'],
            ['UserID'=>16,'PhoneNumber'=>'+20-101-654-3210'],
            ['UserID'=>17,'PhoneNumber'=>'+20-100-543-2109'],
            ['UserID'=>18,'PhoneNumber'=>'+20-111-432-1098'],
            ['UserID'=>21,'PhoneNumber'=>'+20-102-321-0987'],
            ['UserID'=>22,'PhoneNumber'=>'+20-100-210-9876'],
            ['UserID'=>25,'PhoneNumber'=>'+20-112-109-8765'],
            ['UserID'=>26,'PhoneNumber'=>'+20-101-098-7654'],
        ]);

        DB::table('user_addresses')->insert([
            ['UserID'=>3,'Address'=>'Nasr City, Cairo'],['UserID'=>3,'Address'=>'Maadi, Cairo'],
            ['UserID'=>4,'Address'=>'Heliopolis, Cairo'],
            ['UserID'=>5,'Address'=>'Dokki, Giza'],
            ['UserID'=>6,'Address'=>'Zamalek, Cairo'],
            ['UserID'=>7,'Address'=>'6th October City, Giza'],
            ['UserID'=>8,'Address'=>'New Cairo, Cairo'],
            ['UserID'=>9,'Address'=>'Mohandessin, Giza'],
            ['UserID'=>10,'Address'=>'Rehab City, New Cairo'],
            ['UserID'=>11,'Address'=>'Madinaty, New Cairo'],
            ['UserID'=>12,'Address'=>'Shubra, Cairo'],
            ['UserID'=>13,'Address'=>'Maadi, Cairo'],
            ['UserID'=>14,'Address'=>'Heliopolis, Cairo'],
            ['UserID'=>15,'Address'=>'Mansoura, Dakahlia'],
            ['UserID'=>16,'Address'=>'Zamalek, Cairo'],
            ['UserID'=>17,'Address'=>'Nasr City, Cairo'],
            ['UserID'=>18,'Address'=>'Sheikh Zayed, Giza'],
            ['UserID'=>19,'Address'=>'New Cairo, Cairo'],
            ['UserID'=>20,'Address'=>'Alexandria'],
            ['UserID'=>21,'Address'=>'Nasr City, Cairo'],
            ['UserID'=>22,'Address'=>'Garden City, Cairo'],
            ['UserID'=>23,'Address'=>'Mohandessin, Giza'],
            ['UserID'=>25,'Address'=>'Maadi, Cairo'],
            ['UserID'=>26,'Address'=>'Dokki, Giza'],
        ]);
    }
}
