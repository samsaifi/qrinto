<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Make user_id nullable for guest checkout
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add guest contact info
            $table->string('guest_email')->nullable()->after('user_id');
            $table->string('guest_phone')->nullable()->after('guest_email');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert changes - note: this might fail if there are records with null user_id
            // In a real production scenario, we'd need to handle that.
            // keeping it simple for dev
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn(['guest_email', 'guest_phone']);
        });
    }
};
