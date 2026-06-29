<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Option groups: Size, Material, Frame, Print Type, etc.
        Schema::create('product_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Size", "Material", "Frame"
            $table->string('slug');
            $table->string('display_type')->default('buttons'); // buttons, cards, dropdown
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Individual option values with price modifiers
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')->constrained('product_option_groups')->cascadeOnDelete();
            $table->string('label'); // e.g. "12x18 inches", "Acrylic 3mm"
            $table->string('value'); // machine-readable value
            $table->decimal('price_modifier', 10, 2)->default(0); // +/- from base price
            $table->enum('price_type', ['fixed', 'percentage', 'absolute'])->default('fixed');
            // fixed = add to base, percentage = % of base, absolute = replace base
            $table->string('image')->nullable(); // preview image for this option
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_option_groups');
    }
};
