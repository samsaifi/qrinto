<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persist the exact print-ready PDF that was sent to PrintTrays alongside its
 * audit log row, so an admin can re-download / reprint precisely what printed.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('local_print_logs', function (Blueprint $table) {
            $table->string('generated_pdf_path', 512)->nullable()->after('page_dimensions');
            $table->string('generated_pdf_url', 512)->nullable()->after('generated_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('local_print_logs', function (Blueprint $table) {
            $table->dropColumn(['generated_pdf_path', 'generated_pdf_url']);
        });
    }
};
