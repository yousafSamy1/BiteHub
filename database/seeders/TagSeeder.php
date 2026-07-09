<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // Health & Diet
            ['name' => 'Diabetic-friendly', 'icon' => 'fa-tint', 'category' => 'Health & Diet'],
            ['name' => 'Keto', 'icon' => 'fa-avocado', 'category' => 'Health & Diet'],
            ['name' => 'Vegan', 'icon' => 'fa-leaf', 'category' => 'Health & Diet'],
            ['name' => 'Gluten-Free', 'icon' => 'fa-seedling', 'category' => 'Health & Diet'],
            ['name' => 'High Protein', 'icon' => 'fa-dumbbell', 'category' => 'Health & Diet'],
            ['name' => 'Low Carb', 'icon' => 'fa-bread-slice', 'category' => 'Health & Diet'],
            // Meal Time
            ['name' => 'Breakfast', 'icon' => 'fa-sun', 'category' => 'Meal Time'],
            ['name' => 'Lunch', 'icon' => 'fa-utensils', 'category' => 'Meal Time'],
            ['name' => 'Dinner', 'icon' => 'fa-moon', 'category' => 'Meal Time'],
            ['name' => 'Snack', 'icon' => 'fa-cookie', 'category' => 'Meal Time'],
            // Mood & Activity
            ['name' => 'Post-workout', 'icon' => 'fa-fire', 'category' => 'Mood & Activity'],
            ['name' => 'Comfort Food', 'icon' => 'fa-heart', 'category' => 'Mood & Activity'],
            ['name' => 'Energizing', 'icon' => 'fa-bolt', 'category' => 'Mood & Activity'],
            // Allergies
            ['name' => 'Nut-Free', 'icon' => 'fa-ban', 'category' => 'Allergies'],
            ['name' => 'Dairy-Free', 'icon' => 'fa-cow', 'category' => 'Allergies'],
        ];

        foreach ($tags as $tag) {
            \App\Models\Tag::updateOrCreate(['name' => $tag['name']], $tag);
        }
    }
}
