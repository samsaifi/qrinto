<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IP-based geolocation for local print logs (city / country). Resolved from the
 * request IP via an external geo API in LocalPrintLogService; left null for
 * private/localhost IPs or when the lookup fails.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('local_print_logs', function (Blueprint $table) {
            $table->string('geo_city', 120)->nullable()->after('device_name');
            $table->string('geo_region', 120)->nullable()->after('geo_city');
            $table->string('geo_country', 120)->nullable()->after('geo_region');
            $table->string('geo_country_code', 8)->nullable()->after('geo_country');
        });
    }

    public function down(): void
    {
        Schema::table('local_print_logs', function (Blueprint $table) {
            $table->dropColumn(['geo_city', 'geo_region', 'geo_country', 'geo_country_code']);
        });
    }
};
