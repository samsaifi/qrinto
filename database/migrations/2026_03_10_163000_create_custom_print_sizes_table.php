<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_print_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('key', 10)->unique();        // e.g. S, M, L, XL
            $table->string('label');                      // e.g. Small, Medium
            $table->string('dimensions');                  // e.g. 8" × 10"
            $table->decimal('price', 10, 2);              // e.g. 19.99
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default sizes
        DB::table('custom_print_sizes')->insert([
            [
                'key' => 'GC_S',
                'label' => 'Greeting Card: Small',
                'dimensions' => '3.5 × 5 (folded 5×7)',
                'price' => 5.99,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'GC_M',
                'label' => 'Greeting Card: Medium',
                'dimensions' => '5×7"',
                'price' => 7.99,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'GC_L',
                'label' => 'Greeting Card: Large',
                'dimensions' => '5×7 (folded 7×10)',
                'price' => 9.99,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'MAG_S',
                'label' => 'Magnet: Small',
                'dimensions' => '4×6',
                'price' => 4.99,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'MAG_M',
                'label' => 'Magnet: Medium',
                'dimensions' => '5×7',
                'price' => 6.99,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_print_sizes');
    }
};
