<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documentation_guides')) {
            Schema::create('documentation_guides', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('category'); // getting_started, orders, catalog, stores, users
                $table->string('subcategory')->nullable(); // products, categories, card_types, templates, coupons, events, paper_types
                $table->string('icon')->default('book-open');
                $table->text('summary')->nullable();
                $table->longText('content');
                $table->json('visible_roles')->nullable(); // ["admin", "store_admin", "staff"]
                $table->boolean('is_published')->default(true);
                $table->integer('sort_order')->default(0);
                $table->integer('views_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_guides');
    }
};
