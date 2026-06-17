<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The templates table already exists with a different schema — restructure it.
        Schema::table('templates', function (Blueprint $table) {
            // Drop old columns that are no longer needed
            $table->dropColumn(['category', 'type', 'structure', 'preview_image_url', 'print_specs', 'price']);
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('icon_type')->default('lucide')->after('slug');
            $table->string('icon_value')->nullable()->after('icon_type');
            $table->json('canvas_config')->after('icon_value');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn(['slug', 'icon_type', 'icon_value', 'canvas_config', 'sort_order']);
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->string('type')->default('folded_card');
            $table->json('structure')->nullable();
            $table->string('preview_image_url')->nullable();
            $table->json('print_specs')->nullable();
            $table->decimal('price', 10, 2)->default(0);
        });
    }
};
