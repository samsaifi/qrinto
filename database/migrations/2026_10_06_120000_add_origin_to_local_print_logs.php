<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distinguish prints initiated from the web (browser) vs the standalone kiosk
 * software. Existing rows are treated as 'web' because every log recorded so
 * far was created by the browser-side LPLog wrapper on /print/pdf/check and
 * the customizer preview page.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('local_print_logs', function (Blueprint $table) {
            $table->string('origin', 20)->default('web')->after('source');
            $table->index('origin');
        });
    }

    public function down(): void
    {
        Schema::table('local_print_logs', function (Blueprint $table) {
            $table->dropIndex(['origin']);
            $table->dropColumn('origin');
        });
    }
};
