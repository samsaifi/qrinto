<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('store_id')->constrained('users')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->after('created_by')->constrained('events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_id');
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
