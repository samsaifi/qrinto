<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * photo = customizer photo-layout templates (existing rows).
     * card  = greeting-card editor (/editor) templates; canvas_config holds
     *         {category, style, tags, featured, popular, pages}.
     */
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->string('kind', 20)->default('photo')->after('slug')->index();
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('kind');
        });
    }
};
