<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class UpdateCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Delete all old categories
        Category::query()->delete();

        // Greeting Cards (parent)
        $greetingCards = Category::create([
            'name' => 'Greeting Cards',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $greetingCards->id,
            'name' => 'Small - 3.5 x 5 (folded 5x7)',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Category::create([
            'parent_id' => $greetingCards->id,
            'name' => 'Medium - 5×7"',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        Category::create([
            'parent_id' => $greetingCards->id,
            'name' => 'Large - 5x7 (folded 7x10)',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Magnets (parent)
        $magnets = Category::create([
            'name' => 'Magnets',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $magnets->id,
            'name' => 'Small - 4x6',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Category::create([
            'parent_id' => $magnets->id,
            'name' => 'Medium - 5x7',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        Category::create([
            'parent_id' => $magnets->id,
            'name' => 'Large - N/A',
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }
}
