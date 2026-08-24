<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Per-tray media configuration for the store panel Trays screen.
            // Shape: [ ['key'=>'mp','size'=>'5x7','media'=>'cardstock_scored','gsm'=>'270-324','enabled'=>true], ... ]
            $table->json('tray_config')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('tray_config');
        });
    }
};
