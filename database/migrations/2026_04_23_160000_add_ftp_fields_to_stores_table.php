<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'ftp_host')) {
                $table->string('ftp_host', 255)->nullable()->after('paper_size');
            }
            if (!Schema::hasColumn('stores', 'ftp_port')) {
                $table->integer('ftp_port')->default(21)->nullable()->after('ftp_host');
            }
            if (!Schema::hasColumn('stores', 'ftp_username')) {
                $table->string('ftp_username', 255)->nullable()->after('ftp_port');
            }
            if (!Schema::hasColumn('stores', 'ftp_password')) {
                $table->string('ftp_password', 255)->nullable()->after('ftp_username');
            }
            if (!Schema::hasColumn('stores', 'ftp_remote_path')) {
                $table->string('ftp_remote_path', 500)->default('/')->nullable()->after('ftp_password');
            }
            if (!Schema::hasColumn('stores', 'ftp_passive_mode')) {
                $table->boolean('ftp_passive_mode')->default(true)->after('ftp_remote_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $columns = ['ftp_host', 'ftp_port', 'ftp_username', 'ftp_password', 'ftp_remote_path', 'ftp_passive_mode'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('stores', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
