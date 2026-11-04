<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per product type/size visibility: show_in_templates controls the /cards
 * template flow, show_in_local_print controls /print/own-design/sizes.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $table->boolean('show_in_templates')->default(true)->after('is_active');
            $table->boolean('show_in_local_print')->default(true)->after('show_in_templates');
        });
    }

    public function down(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $table->dropColumn(['show_in_templates', 'show_in_local_print']);
        });
    }
};
