<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_type')->nullable()->after('status'); // 'pickup', 'shipment'
            $table->foreignId('store_id')->nullable()->after('fulfillment_type')->constrained('stores')->nullOnDelete();
            $table->date('estimated_delivery_date')->nullable()->after('store_id');
            $table->string('print_job_id')->nullable()->after('estimated_delivery_date');
        });

        // Modify status enum. Using raw statement for MySQL.
        // If this fails (e.g. SQLite), we might need a different approach.
        // Assuming MySQL/MariaDB for XAMPP.
        try {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending', 'confirmed', 'processing', 'printing', 'shipped', 'delivered_store', 'delivered', 'cancelled', 'refunded'
            ) DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Fallback: just leave it or handle error if not MySQL
        }

        // Update order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('product_id')->constrained('templates')->nullOnDelete();
            // customization_data exists as json in original migration, so no need to add unless checking first.
            // original: $table->json('customization_data')->nullable(); -> OK.
            
            $table->string('print_ready_url')->nullable()->after('customization_data');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn(['fulfillment_type', 'store_id', 'estimated_delivery_date', 'print_job_id']);
        });

        // Revert status enum (partial revert)
        try {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'
            ) DEFAULT 'pending'");
        } catch (\Exception $e) {}

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'print_ready_url']);
        });
    }
};
