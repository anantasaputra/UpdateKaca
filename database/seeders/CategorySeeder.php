<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cute',
                'slug' => 'cute',
                'description' => 'Adorable and playful frames perfect for fun moments',
                'is_active' => true,
            ],
            [
                'name' => 'Minimalis',
                'slug' => 'minimalis',
                'description' => 'Simple and elegant frames with clean design',
                'is_active' => true,
            ],
            [
                'name' => 'Formal',
                'slug' => 'formal',
                'description' => 'Professional and sophisticated frames for formal occasions',
                'is_active' => true,
            ],
            [
                'name' => 'Couple',
                'slug' => 'couple',
                'description' => 'Romantic frames designed for couples',
                'is_active' => true,
            ],
            [
                'name' => 'Birthday',
                'slug' => 'birthday',
                'description' => 'Festive frames for birthday celebrations',
                'is_active' => true,
            ],
            [
                'name' => 'Holiday',
                'slug' => 'holiday',
                'description' => 'Seasonal and holiday-themed frames',
                'is_active' => true,
            ],
            [
                'name' => 'Vintage',
                'slug' => 'vintage',
                'description' => 'Classic retro-style frames',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}