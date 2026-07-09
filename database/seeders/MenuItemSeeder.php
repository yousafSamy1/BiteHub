<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // Kitchen Owner menu items
        DB::table('menu_items')->insert([
            ['ItemName'=>'Koshary','ItemPrice'=>30.00,'CategoryID'=>1,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Traditional Egyptian koshary with crispy onions, spicy tomato sauce, garlic vinegar, and chickpeas.','Status'=>'Available'],
            ['ItemName'=>'Molokhia with Rice','ItemPrice'=>45.00,'CategoryID'=>1,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Fresh molokhia with garlic served over fluffy Egyptian rice with grilled chicken pieces.','Status'=>'Available'],
            ['ItemName'=>'Mahshi','ItemPrice'=>55.00,'CategoryID'=>1,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Stuffed grape leaves, peppers, and zucchini with seasoned rice, herbs, and tangy tomato broth.','Status'=>'Available'],
            ['ItemName'=>'Om Ali','ItemPrice'=>25.00,'CategoryID'=>2,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Classic Egyptian bread pudding with crushed nuts, raisins, coconut, and warm sweetened milk.','Status'=>'Available'],
            ['ItemName'=>'Fattah','ItemPrice'=>60.00,'CategoryID'=>1,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Layers of crispy bread, seasoned rice, and tender lamb with garlic vinegar sauce.','Status'=>'Available'],
            ['ItemName'=>'Fresh Mango Juice','ItemPrice'=>15.00,'CategoryID'=>4,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Freshly squeezed Egyptian mango juice, no sugar added, served ice cold.','Status'=>'Available'],
            ['ItemName'=>'Foul Medames','ItemPrice'=>20.00,'CategoryID'=>5,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Traditional Egyptian fava beans with olive oil, cumin, lemon juice, and fresh herbs.','Status'=>'Available'],
            ['ItemName'=>'Basbousa','ItemPrice'=>18.00,'CategoryID'=>2,'KitchenOwnerID'=>1,'CatererID'=>null,'Description'=>'Semolina cake soaked in sweet rose water syrup topped with almonds and hazelnuts.','Status'=>'Available'],
            ['ItemName'=>'Mediterranean Bowl','ItemPrice'=>40.00,'CategoryID'=>1,'KitchenOwnerID'=>2,'CatererID'=>null,'Description'=>'Fresh bowl with crispy falafel, creamy hummus, tabbouleh, grilled halloumi, and tahini.','Status'=>'Available'],
            ['ItemName'=>'Quinoa Salad','ItemPrice'=>35.00,'CategoryID'=>3,'KitchenOwnerID'=>2,'CatererID'=>null,'Description'=>'Healthy quinoa with roasted vegetables, crumbled feta cheese, pomegranate seeds.','Status'=>'Available'],
            ['ItemName'=>'Kunafa','ItemPrice'=>30.00,'CategoryID'=>2,'KitchenOwnerID'=>2,'CatererID'=>null,'Description'=>'Crispy golden kunafa with melted Akawi cheese filling, drizzled with sweet sugar syrup.','Status'=>'Available'],
            ['ItemName'=>'Shakshuka','ItemPrice'=>28.00,'CategoryID'=>5,'KitchenOwnerID'=>2,'CatererID'=>null,'Description'=>'Poached eggs in rich spiced tomato-pepper sauce with fresh herbs.','Status'=>'Available'],
            ['ItemName'=>'Avocado Toast','ItemPrice'=>32.00,'CategoryID'=>5,'KitchenOwnerID'=>2,'CatererID'=>null,'Description'=>'Smashed avocado on multigrain toast with cherry tomatoes, feta, zaatar, and poached egg.','Status'=>'Available'],
            ['ItemName'=>'Green Smoothie','ItemPrice'=>22.00,'CategoryID'=>4,'KitchenOwnerID'=>2,'CatererID'=>null,'Description'=>'Spinach, banana, mango, chia seeds, and almond milk blended to perfection.','Status'=>'Available'],
            ['ItemName'=>'Mulukhiyah Rabbit','ItemPrice'=>75.00,'CategoryID'=>1,'KitchenOwnerID'=>3,'CatererID'=>null,'Description'=>'Authentic Upper Egyptian mulukhiyah cooked with whole rabbit, garlic, and coriander.','Status'=>'Available'],
            ['ItemName'=>'Feteer Meshaltet','ItemPrice'=>40.00,'CategoryID'=>5,'KitchenOwnerID'=>3,'CatererID'=>null,'Description'=>'Flaky Egyptian layered pastry, baked golden and crispy.','Status'=>'Available'],
            ['ItemName'=>'Hawawshi','ItemPrice'=>35.00,'CategoryID'=>1,'KitchenOwnerID'=>3,'CatererID'=>null,'Description'=>'Spiced minced meat baked inside Egyptian baladi bread until crispy.','Status'=>'Available'],
            ['ItemName'=>'Roz Bel Laban','ItemPrice'=>20.00,'CategoryID'=>2,'KitchenOwnerID'=>3,'CatererID'=>null,'Description'=>'Creamy Egyptian rice pudding with vanilla, cinnamon, and crushed pistachios.','Status'=>'Available'],
            ['ItemName'=>'Kebda Iskandarani','ItemPrice'=>30.00,'CategoryID'=>3,'KitchenOwnerID'=>3,'CatererID'=>null,'Description'=>'Alexandrian-style liver sauteed with peppers, garlic, and spices.','Status'=>'Available'],
            ['ItemName'=>'Chicken Shawarma Plate','ItemPrice'=>45.00,'CategoryID'=>7,'KitchenOwnerID'=>4,'CatererID'=>null,'Description'=>'Marinated chicken shawarma with garlic sauce, pickled turnips, and seasoned fries.','Status'=>'Available'],
            ['ItemName'=>'Hummus Trio','ItemPrice'=>35.00,'CategoryID'=>3,'KitchenOwnerID'=>4,'CatererID'=>null,'Description'=>'Classic, roasted red pepper, and basil hummus served with warm pita and olive oil.','Status'=>'Available'],
            ['ItemName'=>'Mixed Grill Platter','ItemPrice'=>95.00,'CategoryID'=>7,'KitchenOwnerID'=>4,'CatererID'=>null,'Description'=>'Assorted grilled meats: lamb chops, chicken wings, kofta, and kabab.','Status'=>'Available'],
            ['ItemName'=>'Lamb Mansaf','ItemPrice'=>85.00,'CategoryID'=>1,'KitchenOwnerID'=>4,'CatererID'=>null,'Description'=>'Traditional Jordanian lamb cooked in fermented yogurt sauce, served over saffron rice.','Status'=>'Available'],
            ['ItemName'=>'Baklava Assorted','ItemPrice'=>28.00,'CategoryID'=>2,'KitchenOwnerID'=>4,'CatererID'=>null,'Description'=>'Assorted Syrian baklava with walnuts, pistachios, and cashews.','Status'=>'Available'],
            ['ItemName'=>'Turkish Coffee','ItemPrice'=>12.00,'CategoryID'=>4,'KitchenOwnerID'=>4,'CatererID'=>null,'Description'=>'Authentic Turkish coffee brewed in a traditional copper cezve.','Status'=>'Available'],
            ['ItemName'=>'Kunafa Nabulsia','ItemPrice'=>35.00,'CategoryID'=>2,'KitchenOwnerID'=>5,'CatererID'=>null,'Description'=>'Premium Nabulsi kunafa with stretchy cheese, golden vermicelli, and rose water syrup.','Status'=>'Available'],
            ['ItemName'=>'Qatayef','ItemPrice'=>25.00,'CategoryID'=>2,'KitchenOwnerID'=>5,'CatererID'=>null,'Description'=>'Traditional Ramadan pancakes filled with cream or nuts, fried and drizzled with syrup.','Status'=>'Available'],
            ['ItemName'=>'Luqaimat','ItemPrice'=>20.00,'CategoryID'=>2,'KitchenOwnerID'=>5,'CatererID'=>null,'Description'=>'Golden fried dough balls drizzled with date syrup and sprinkled with sesame seeds.','Status'=>'Available'],
            ['ItemName'=>'Halawet El Jibn','ItemPrice'=>30.00,'CategoryID'=>2,'KitchenOwnerID'=>5,'CatererID'=>null,'Description'=>'Sweet cheese rolls filled with ashta cream, topped with pistachios and rose syrup.','Status'=>'Available'],
            ['ItemName'=>'Sahlab','ItemPrice'=>18.00,'CategoryID'=>4,'KitchenOwnerID'=>5,'CatererID'=>null,'Description'=>'Traditional hot milk drink thickened with orchid flour, topped with cinnamon and nuts.','Status'=>'Available'],
            ['ItemName'=>'Mango Kunafa','ItemPrice'=>38.00,'CategoryID'=>2,'KitchenOwnerID'=>5,'CatererID'=>null,'Description'=>'Signature creation! Crispy kunafa layered with fresh mango cream and pistachios.','Status'=>'Available'],
            ['ItemName'=>'Grilled Chicken Salad','ItemPrice'=>42.00,'CategoryID'=>8,'KitchenOwnerID'=>6,'CatererID'=>null,'Description'=>'Herb-grilled chicken breast over mixed greens with cherry tomatoes and balsamic dressing.','Status'=>'Available'],
            ['ItemName'=>'Protein Power Bowl','ItemPrice'=>48.00,'CategoryID'=>8,'KitchenOwnerID'=>6,'CatererID'=>null,'Description'=>'Grilled salmon, quinoa, edamame, sweet potato, and kale with tahini-lemon dressing.','Status'=>'Available'],
            ['ItemName'=>'Keto Plate','ItemPrice'=>55.00,'CategoryID'=>8,'KitchenOwnerID'=>6,'CatererID'=>null,'Description'=>'Grilled steak with cauliflower mash, sauteed mushrooms, and buttered asparagus.','Status'=>'Available'],
            ['ItemName'=>'Acai Bowl','ItemPrice'=>38.00,'CategoryID'=>8,'KitchenOwnerID'=>6,'CatererID'=>null,'Description'=>'Frozen acai blended smooth, topped with granola, fresh berries, coconut flakes.','Status'=>'Available'],
            ['ItemName'=>'Detox Green Juice','ItemPrice'=>18.00,'CategoryID'=>4,'KitchenOwnerID'=>6,'CatererID'=>null,'Description'=>'Cucumber, celery, ginger, lemon, and green apple. Fresh cold-pressed daily.','Status'=>'Available'],
            ['ItemName'=>'Egyptian Sushi Roll','ItemPrice'=>50.00,'CategoryID'=>1,'KitchenOwnerID'=>7,'CatererID'=>null,'Description'=>'Creative fusion: sushi rice with koshary toppings wrapped in nori.','Status'=>'Available'],
            ['ItemName'=>'Tacos El Masry','ItemPrice'=>38.00,'CategoryID'=>1,'KitchenOwnerID'=>7,'CatererID'=>null,'Description'=>'Soft corn tortillas filled with slow-cooked shawarma, tahini slaw, and pickled onions.','Status'=>'Available'],
            ['ItemName'=>'Pharaoh Burger','ItemPrice'=>55.00,'CategoryID'=>1,'KitchenOwnerID'=>7,'CatererID'=>null,'Description'=>'Wagyu beef patty with halloumi, caramelized onions, rocket, and special dukkah sauce.','Status'=>'Available'],
            ['ItemName'=>'Lotus Cheesecake','ItemPrice'=>35.00,'CategoryID'=>2,'KitchenOwnerID'=>7,'CatererID'=>null,'Description'=>'Creamy no-bake cheesecake with Lotus Biscoff crust and caramel drizzle.','Status'=>'Available'],
            ['ItemName'=>'Passion Fruit Mojito','ItemPrice'=>22.00,'CategoryID'=>4,'KitchenOwnerID'=>7,'CatererID'=>null,'Description'=>'Non-alcoholic passion fruit mojito with fresh mint, lime, and sparkling water.','Status'=>'Available'],
            ['ItemName'=>'Grilled Sea Bass','ItemPrice'=>80.00,'CategoryID'=>6,'KitchenOwnerID'=>8,'CatererID'=>null,'Description'=>'Whole sea bass marinated with herbs and grilled to perfection.','Status'=>'Available'],
            ['ItemName'=>'Shrimp Tagine','ItemPrice'=>70.00,'CategoryID'=>6,'KitchenOwnerID'=>8,'CatererID'=>null,'Description'=>'Jumbo shrimp simmered in a rich tomato-pepper tagine with onions and fresh herbs.','Status'=>'Available'],
            ['ItemName'=>'Calamari Rings','ItemPrice'=>40.00,'CategoryID'=>6,'KitchenOwnerID'=>8,'CatererID'=>null,'Description'=>'Crispy golden calamari rings served with garlic aioli and lemon wedges.','Status'=>'Available'],
            ['ItemName'=>'Seafood Platter','ItemPrice'=>120.00,'CategoryID'=>6,'KitchenOwnerID'=>8,'CatererID'=>null,'Description'=>'Premium platter: grilled fish, shrimp, calamari, crab, and mussels. Feeds 2-3.','Status'=>'Available'],
            ['ItemName'=>'Sayadeya Rice','ItemPrice'=>65.00,'CategoryID'=>6,'KitchenOwnerID'=>8,'CatererID'=>null,'Description'=>'Traditional Egyptian fish and rice dish with caramelized onion sauce.','Status'=>'Available'],
            ['ItemName'=>'Fish & Chips','ItemPrice'=>45.00,'CategoryID'=>6,'KitchenOwnerID'=>8,'CatererID'=>null,'Description'=>'Beer-battered fish fillet with crispy fries, coleslaw, and tartar sauce.','Status'=>'Available'],
        ]);

        // Caterer menu items
        DB::table('menu_items')->insert([
            ['ItemName'=>'Wedding Package Silver','ItemPrice'=>5000.00,'CategoryID'=>1,'KitchenOwnerID'=>null,'CatererID'=>1,'Description'=>'Serves 100 guests. Includes 3 main courses, 2 desserts, beverages, and basic table setup.','Status'=>'Available'],
            ['ItemName'=>'Wedding Package Gold','ItemPrice'=>8500.00,'CategoryID'=>1,'KitchenOwnerID'=>null,'CatererID'=>1,'Description'=>'Serves 150 guests. Premium menu with 5 main courses, 3 desserts, live cooking stations.','Status'=>'Available'],
            ['ItemName'=>'Corporate Lunch Box','ItemPrice'=>45.00,'CategoryID'=>1,'KitchenOwnerID'=>null,'CatererID'=>3,'Description'=>'Individual boxed lunch: sandwich, salad, fruit, dessert, and juice. Min order: 20.','Status'=>'Available'],
            ['ItemName'=>'Birthday Party Package','ItemPrice'=>2000.00,'CategoryID'=>1,'KitchenOwnerID'=>null,'CatererID'=>4,'Description'=>'Serves 50 guests. Includes main course, sides, birthday cake, decorations.','Status'=>'Available'],
        ]);
    }
}
