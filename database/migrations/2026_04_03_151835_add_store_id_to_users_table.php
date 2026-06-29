<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $col) {
            $col->foreignId('store_id')->nullable()->constrained('stores')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $col) {
            $col->dropForeign(['store_id']);
            $col->dropColumn('store_id');
        });
    }
};
