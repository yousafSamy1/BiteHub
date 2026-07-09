<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pass = Hash::make('123');

        DB::table('users')->insert([
            ['FullName'=>'Admin User','Email'=>'admin@mail.com','Password'=>$pass,'Role'=>'Admin','Image'=>'https://ui-avatars.com/api/?name=Admin+User&background=FF6B35&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'System Admin','Email'=>'sysadmin@mail.com','Password'=>$pass,'Role'=>'Admin','Image'=>'https://ui-avatars.com/api/?name=System+Admin&background=E55A2B&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Ahmed Hassan','Email'=>'ahmed@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Ahmed+Hassan&background=4FC3F7&color=fff&size=128','Wallet_balance'=>250.00],
            ['FullName'=>'Layla Mostafa','Email'=>'layla@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Layla+Mostafa&background=EC407A&color=fff&size=128','Wallet_balance'=>175.50],
            ['FullName'=>'Omar Youssef','Email'=>'omar.y@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Omar+Youssef&background=7E57C2&color=fff&size=128','Wallet_balance'=>320.00],
            ['FullName'=>'Nada Ibrahim','Email'=>'nada@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Nada+Ibrahim&background=26A69A&color=fff&size=128','Wallet_balance'=>80.00],
            ['FullName'=>'Khaled Farouk','Email'=>'khaled@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Khaled+Farouk&background=42A5F5&color=fff&size=128','Wallet_balance'=>450.00],
            ['FullName'=>'Mona Saleh','Email'=>'mona@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Mona+Saleh&background=AB47BC&color=fff&size=128','Wallet_balance'=>95.00],
            ['FullName'=>'Yasser Mahmoud','Email'=>'yasser@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Yasser+Mahmoud&background=66BB6A&color=fff&size=128','Wallet_balance'=>200.00],
            ['FullName'=>'Dina Kamal','Email'=>'dina@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Dina+Kamal&background=FF7043&color=fff&size=128','Wallet_balance'=>150.00],
            ['FullName'=>'Tarek Nabil','Email'=>'tarek@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Tarek+Nabil&background=5C6BC0&color=fff&size=128','Wallet_balance'=>60.00],
            ['FullName'=>'Hana Adel','Email'=>'hana@mail.com','Password'=>$pass,'Role'=>'Customer','Image'=>'https://ui-avatars.com/api/?name=Hana+Adel&background=EF5350&color=fff&size=128','Wallet_balance'=>310.00],
            ['FullName'=>'Sara El-Masry','Email'=>'kitchen@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Sara+ElMasry&background=66BB6A&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Nour Hassan','Email'=>'nour@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Nour+Hassan&background=26A69A&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Fatma Ali','Email'=>'fatma@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Fatma+Ali&background=FF8A65&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Amira Kamel','Email'=>'amira@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Amira+Kamel&background=BA68C8&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Rania Saeed','Email'=>'rania@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Rania+Saeed&background=4DB6AC&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Heba Magdy','Email'=>'heba@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Heba+Magdy&background=FFB74D&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Yasmine Taha','Email'=>'yasmine@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Yasmine+Taha&background=E57373&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Samira Fathi','Email'=>'samira@mail.com','Password'=>$pass,'Role'=>'KitchenOwner','Image'=>'https://ui-avatars.com/api/?name=Samira+Fathi&background=81C784&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Caterer One','Email'=>'cat@mail.com','Password'=>$pass,'Role'=>'Caterer','Image'=>'https://ui-avatars.com/api/?name=Caterer+One&background=AB47BC&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Royal Catering Co','Email'=>'royal@mail.com','Password'=>$pass,'Role'=>'Caterer','Image'=>'https://ui-avatars.com/api/?name=Royal+Catering&background=7E57C2&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Elite Events','Email'=>'elite@mail.com','Password'=>$pass,'Role'=>'Caterer','Image'=>'https://ui-avatars.com/api/?name=Elite+Events&background=5C6BC0&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Nile Feasts','Email'=>'nile@mail.com','Password'=>$pass,'Role'=>'Caterer','Image'=>'https://ui-avatars.com/api/?name=Nile+Feasts&background=4FC3F7&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Mahmoud Rider','Email'=>'del@mail.com','Password'=>$pass,'Role'=>'DeliveryAgent','Image'=>'https://ui-avatars.com/api/?name=Mahmoud+Rider&background=EF5350&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Ali Express','Email'=>'ali.del@mail.com','Password'=>$pass,'Role'=>'DeliveryAgent','Image'=>'https://ui-avatars.com/api/?name=Ali+Express&background=FF7043&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Hassan Speed','Email'=>'hassan.del@mail.com','Password'=>$pass,'Role'=>'DeliveryAgent','Image'=>'https://ui-avatars.com/api/?name=Hassan+Speed&background=FFA726&color=fff&size=128','Wallet_balance'=>0.00],
            ['FullName'=>'Karim Flash','Email'=>'karim.del@mail.com','Password'=>$pass,'Role'=>'DeliveryAgent','Image'=>'https://ui-avatars.com/api/?name=Karim+Flash&background=42A5F5&color=fff&size=128','Wallet_balance'=>0.00],
        ]);
    }
}
