<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('orders')->insert([
            ['CustomerID'=>1,'DeliveryAgentID'=>1,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>75.00,'LoyaltyPoints'=>15,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Extra spicy sauce please','CreatedAt'=>'2026-02-10 10:30:00'],
            ['CustomerID'=>1,'DeliveryAgentID'=>1,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>85.00,'LoyaltyPoints'=>17,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'CreatedAt'=>'2026-02-09 14:00:00'],
            ['CustomerID'=>2,'DeliveryAgentID'=>2,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>120.00,'LoyaltyPoints'=>24,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'No onions on the shawarma','CreatedAt'=>'2026-02-09 18:45:00'],
            ['CustomerID'=>3,'DeliveryAgentID'=>1,'PaymentID'=>3,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>45.00,'LoyaltyPoints'=>9,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'CreatedAt'=>'2026-02-08 12:15:00'],
            ['CustomerID'=>4,'DeliveryAgentID'=>3,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>200.00,'LoyaltyPoints'=>40,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Birthday dinner - please add a candle!','CreatedAt'=>'2026-02-08 19:00:00'],
            ['CustomerID'=>5,'DeliveryAgentID'=>2,'PaymentID'=>4,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>95.00,'LoyaltyPoints'=>19,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'CreatedAt'=>'2026-02-07 13:30:00'],
            ['CustomerID'=>1,'DeliveryAgentID'=>1,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>150.00,'LoyaltyPoints'=>30,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivering','DriverLatitude'=>30.0444,'DriverLongitude'=>31.2357,'SpecialRequests'=>null,'CreatedAt'=>'2026-02-11 20:00:00'],
            ['CustomerID'=>2,'DeliveryAgentID'=>null,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>55.00,'LoyaltyPoints'=>11,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Preparing','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Gluten-free if possible','CreatedAt'=>'2026-02-12 00:15:00'],
            ['CustomerID'=>1,'DeliveryAgentID'=>1,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>75.00,'LoyaltyPoints'=>15,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Extra spicy sauce please','DeliveryCode'=>'1234','CreatedAt'=>'2026-02-10 10:30:00'],
            ['CustomerID'=>1,'DeliveryAgentID'=>1,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>85.00,'LoyaltyPoints'=>17,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'5678','CreatedAt'=>'2026-02-09 14:00:00'],
            ['CustomerID'=>2,'DeliveryAgentID'=>2,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>120.00,'LoyaltyPoints'=>24,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'No onions on the shawarma','DeliveryCode'=>'9012','CreatedAt'=>'2026-02-09 18:45:00'],
            ['CustomerID'=>3,'DeliveryAgentID'=>1,'PaymentID'=>3,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>45.00,'LoyaltyPoints'=>9,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'3456','CreatedAt'=>'2026-02-08 12:15:00'],
            ['CustomerID'=>4,'DeliveryAgentID'=>3,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>200.00,'LoyaltyPoints'=>40,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Birthday dinner - please add a candle!','DeliveryCode'=>'7890','CreatedAt'=>'2026-02-08 19:00:00'],
            ['CustomerID'=>5,'DeliveryAgentID'=>2,'PaymentID'=>4,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>95.00,'LoyaltyPoints'=>19,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'2345','CreatedAt'=>'2026-02-07 13:30:00'],
            ['CustomerID'=>1,'DeliveryAgentID'=>1,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>150.00,'LoyaltyPoints'=>30,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivering','DriverLatitude'=>30.0444,'DriverLongitude'=>31.2357,'SpecialRequests'=>null,'DeliveryCode'=>'6789','CreatedAt'=>'2026-02-11 20:00:00'],
            ['CustomerID'=>2,'DeliveryAgentID'=>null,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>55.00,'LoyaltyPoints'=>11,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Preparing','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Gluten-free if possible','DeliveryCode'=>'0123','CreatedAt'=>'2026-02-12 00:15:00'],
            ['CustomerID'=>3,'DeliveryAgentID'=>null,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>230.00,'LoyaltyPoints'=>46,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Confirmed','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Family gathering - need extra plates','DeliveryCode'=>'4567','CreatedAt'=>'2026-02-11 23:30:00'],
            ['CustomerID'=>6,'DeliveryAgentID'=>null,'PaymentID'=>3,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>40.00,'LoyaltyPoints'=>8,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Pending','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'8901','CreatedAt'=>'2026-02-12 00:45:00'],
            ['CustomerID'=>7,'DeliveryAgentID'=>null,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>68.00,'LoyaltyPoints'=>14,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Pending','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Extra tahini sauce','DeliveryCode'=>'1111','CreatedAt'=>'2026-02-12 01:00:00'],
            ['CustomerID'=>8,'DeliveryAgentID'=>null,'PaymentID'=>4,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>92.00,'LoyaltyPoints'=>18,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Pending','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'2222','CreatedAt'=>'2026-02-12 01:10:00'],
            ['CustomerID'=>1,'DeliveryAgentID'=>1,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>180.00,'LoyaltyPoints'=>36,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'3333','CreatedAt'=>'2026-02-06 11:00:00'],
            ['CustomerID'=>3,'DeliveryAgentID'=>2,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>65.00,'LoyaltyPoints'=>13,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Less sugar in the juice please','DeliveryCode'=>'4444','CreatedAt'=>'2026-02-05 16:20:00'],
            ['CustomerID'=>4,'DeliveryAgentID'=>null,'PaymentID'=>3,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>110.00,'LoyaltyPoints'=>22,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Cancelled','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Changed plans, sorry','DeliveryCode'=>'5555','CreatedAt'=>'2026-02-07 09:45:00'],
            ['CustomerID'=>9,'DeliveryAgentID'=>3,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>78.00,'LoyaltyPoints'=>16,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'6666','CreatedAt'=>'2026-02-04 12:00:00'],
            ['CustomerID'=>10,'DeliveryAgentID'=>4,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>145.00,'LoyaltyPoints'=>29,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'7777','CreatedAt'=>'2026-02-03 14:30:00'],
            ['CustomerID'=>5,'DeliveryAgentID'=>null,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>55.00,'LoyaltyPoints'=>11,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Ready','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Self pickup','DeliveryCode'=>'8888','CreatedAt'=>'2026-02-11 22:00:00'],
            ['CustomerID'=>2,'DeliveryAgentID'=>1,'PaymentID'=>4,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>320.00,'LoyaltyPoints'=>64,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Large family order','DeliveryCode'=>'9999','CreatedAt'=>'2026-02-02 18:00:00'],
            ['CustomerID'=>7,'DeliveryAgentID'=>null,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>42.00,'LoyaltyPoints'=>8,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Preparing','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'0000','CreatedAt'=>'2026-02-11 23:00:00'],
            ['CustomerID'=>6,'DeliveryAgentID'=>null,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>88.00,'LoyaltyPoints'=>18,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Confirmed','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Pack each item separately please','DeliveryCode'=>'1010','CreatedAt'=>'2026-02-11 22:30:00'],
            ['CustomerID'=>9,'DeliveryAgentID'=>2,'PaymentID'=>3,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>135.00,'LoyaltyPoints'=>27,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivering','DriverLatitude'=>30.0450,'DriverLongitude'=>31.2360,'SpecialRequests'=>null,'DeliveryCode'=>'1212','CreatedAt'=>'2026-02-11 21:15:00'],
            ['CustomerID'=>10,'DeliveryAgentID'=>null,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>250.00,'LoyaltyPoints'=>50,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Pending','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>'Event order - delivering at 6 PM','DeliveryCode'=>'1313','CreatedAt'=>'2026-02-12 01:20:00'],
            ['CustomerID'=>8,'DeliveryAgentID'=>3,'PaymentID'=>2,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>75.00,'LoyaltyPoints'=>15,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivered','DriverLatitude'=>null,'DriverLongitude'=>null,'SpecialRequests'=>null,'DeliveryCode'=>'1414','CreatedAt'=>'2026-02-01 10:00:00'],
            ['CustomerID'=>4,'DeliveryAgentID'=>4,'PaymentID'=>1,'LiveChatID'=>null,'Deposit'=>0,'TotalPrice'=>190.00,'LoyaltyPoints'=>38,'Amount'=>null,'UnitPrice'=>null,'OrderStatus'=>'Delivering','DriverLatitude'=>30.0444,'DriverLongitude'=>31.2357,'SpecialRequests'=>'Include extra bread','DeliveryCode'=>'1515','CreatedAt'=>'2026-01-30 17:00:00'],
        ]);

        DB::table('menu_order_items')->insert([
            ['MenuItemID'=>1,'OrderID'=>1,'Quantity'=>2],['MenuItemID'=>6,'OrderID'=>1,'Quantity'=>1],
            ['MenuItemID'=>2,'OrderID'=>2,'Quantity'=>1],['MenuItemID'=>8,'OrderID'=>2,'Quantity'=>2],
            ['MenuItemID'=>9,'OrderID'=>3,'Quantity'=>1],['MenuItemID'=>11,'OrderID'=>3,'Quantity'=>1],['MenuItemID'=>14,'OrderID'=>3,'Quantity'=>2],
            ['MenuItemID'=>12,'OrderID'=>4,'Quantity'=>1],
            ['MenuItemID'=>22,'OrderID'=>5,'Quantity'=>2],['MenuItemID'=>24,'OrderID'=>5,'Quantity'=>1],['MenuItemID'=>25,'OrderID'=>5,'Quantity'=>1],
            ['MenuItemID'=>32,'OrderID'=>6,'Quantity'=>1],['MenuItemID'=>36,'OrderID'=>6,'Quantity'=>1],
            ['MenuItemID'=>3,'OrderID'=>7,'Quantity'=>2],['MenuItemID'=>5,'OrderID'=>7,'Quantity'=>1],['MenuItemID'=>7,'OrderID'=>7,'Quantity'=>1],
            ['MenuItemID'=>9,'OrderID'=>8,'Quantity'=>1],['MenuItemID'=>10,'OrderID'=>8,'Quantity'=>1],
            ['MenuItemID'=>42,'OrderID'=>9,'Quantity'=>1],['MenuItemID'=>43,'OrderID'=>9,'Quantity'=>2],['MenuItemID'=>44,'OrderID'=>9,'Quantity'=>1],
            ['MenuItemID'=>12,'OrderID'=>10,'Quantity'=>1],['MenuItemID'=>13,'OrderID'=>10,'Quantity'=>1],
            ['MenuItemID'=>21,'OrderID'=>11,'Quantity'=>1],['MenuItemID'=>24,'OrderID'=>11,'Quantity'=>1],
            ['MenuItemID'=>33,'OrderID'=>12,'Quantity'=>1],['MenuItemID'=>34,'OrderID'=>12,'Quantity'=>1],
            ['MenuItemID'=>22,'OrderID'=>13,'Quantity'=>2],['MenuItemID'=>23,'OrderID'=>13,'Quantity'=>1],
            ['MenuItemID'=>15,'OrderID'=>14,'Quantity'=>1],
            ['MenuItemID'=>3,'OrderID'=>15,'Quantity'=>1],['MenuItemID'=>4,'OrderID'=>15,'Quantity'=>2],
            ['MenuItemID'=>37,'OrderID'=>16,'Quantity'=>1],['MenuItemID'=>38,'OrderID'=>16,'Quantity'=>1],
            ['MenuItemID'=>39,'OrderID'=>17,'Quantity'=>1],['MenuItemID'=>41,'OrderID'=>17,'Quantity'=>1],
            ['MenuItemID'=>32,'OrderID'=>18,'Quantity'=>1],
            ['MenuItemID'=>45,'OrderID'=>19,'Quantity'=>1],['MenuItemID'=>46,'OrderID'=>19,'Quantity'=>1],['MenuItemID'=>44,'OrderID'=>19,'Quantity'=>2],
            ['MenuItemID'=>16,'OrderID'=>20,'Quantity'=>1],['MenuItemID'=>17,'OrderID'=>20,'Quantity'=>1],
            ['MenuItemID'=>20,'OrderID'=>21,'Quantity'=>1],['MenuItemID'=>21,'OrderID'=>21,'Quantity'=>1],
            ['MenuItemID'=>26,'OrderID'=>22,'Quantity'=>2],['MenuItemID'=>27,'OrderID'=>22,'Quantity'=>1],['MenuItemID'=>30,'OrderID'=>22,'Quantity'=>1],
            ['MenuItemID'=>42,'OrderID'=>23,'Quantity'=>2],['MenuItemID'=>43,'OrderID'=>23,'Quantity'=>1],['MenuItemID'=>46,'OrderID'=>23,'Quantity'=>1],
            ['MenuItemID'=>1,'OrderID'=>24,'Quantity'=>1],['MenuItemID'=>2,'OrderID'=>24,'Quantity'=>1],
            ['MenuItemID'=>5,'OrderID'=>25,'Quantity'=>2],['MenuItemID'=>4,'OrderID'=>25,'Quantity'=>3],
        ]);
    }
}
