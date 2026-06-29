<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('featured_image', 'frame_image');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('sample_image')->nullable()->after('frame_image');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sample_image');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('frame_image', 'featured_image');
        });
    }
};
