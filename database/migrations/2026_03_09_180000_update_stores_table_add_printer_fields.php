<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns (all nullable initially)
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'store_name')) {
                $table->string('store_name', 255)->nullable()->after('id');
            }
            if (!Schema::hasColumn('stores', 'store_code')) {
                $table->string('store_code', 50)->nullable()->after(
                    Schema::hasColumn('stores', 'store_name') ? 'store_name' : 'id'
                );
            }
            if (!Schema::hasColumn('stores', 'owner_name')) {
                $table->string('owner_name', 255)->nullable()->after(
                    Schema::hasColumn('stores', 'store_code') ? 'store_code' : 'id'
                );
            }
            if (!Schema::hasColumn('stores', 'zip_code')) {
                $table->string('zip_code', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('stores', 'country')) {
                $table->string('country', 100)->default('USA')->nullable();
            }
            if (!Schema::hasColumn('stores', 'printer_ip_address')) {
                $table->string('printer_ip_address', 45)->nullable();
            }
            if (!Schema::hasColumn('stores', 'printer_port')) {
                $table->integer('printer_port')->default(9100)->nullable();
            }
            if (!Schema::hasColumn('stores', 'printer_name')) {
                $table->string('printer_name', 255)->nullable();
            }
            if (!Schema::hasColumn('stores', 'printer_type')) {
                $table->string('printer_type', 20)->default('laser')->nullable();
            }
            if (!Schema::hasColumn('stores', 'paper_size')) {
                $table->string('paper_size', 20)->default('A4')->nullable();
            }
            if (!Schema::hasColumn('stores', 'opening_time')) {
                $table->time('opening_time')->nullable();
            }
            if (!Schema::hasColumn('stores', 'closing_time')) {
                $table->time('closing_time')->nullable();
            }
            if (!Schema::hasColumn('stores', 'gst_number')) {
                $table->string('gst_number', 50)->nullable();
            }
            if (!Schema::hasColumn('stores', 'logo')) {
                $table->string('logo', 255)->nullable();
            }
            if (!Schema::hasColumn('stores', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('stores', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Step 2: Copy data from old columns to new ones
        if (Schema::hasColumn('stores', 'name')) {
            DB::statement("UPDATE `stores` SET `store_name` = `name` WHERE `store_name` IS NULL OR `store_name` = ''");
        }
        if (Schema::hasColumn('stores', 'zip')) {
            DB::statement("UPDATE `stores` SET `zip_code` = `zip` WHERE `zip_code` IS NULL OR `zip_code` = ''");
        }

        // Step 3: Generate UNIQUE store_code for ALL rows that don't have one
        $stores = DB::table('stores')->where(function ($q) {
            $q->whereNull('store_code')->orWhere('store_code', '');
        })->get();

        foreach ($stores as $store) {
            $code = 'STORE-' . str_pad($store->id, 3, '0', STR_PAD_LEFT);
            DB::table('stores')->where('id', $store->id)->update(['store_code' => $code]);
        }

        // Step 4: Set defaults for owner_name
        DB::statement("UPDATE `stores` SET `owner_name` = 'Owner' WHERE `owner_name` IS NULL OR `owner_name` = ''");

        if (config('database.default') === 'sqlite') {
            DB::statement("UPDATE `stores` SET `store_name` = 'Store #' || id WHERE `store_name` IS NULL OR `store_name` = ''");
        } else {
            DB::statement("UPDATE `stores` SET `store_name` = CONCAT('Store #', id) WHERE `store_name` IS NULL OR `store_name` = ''");
        }

        // Step 6: Now make store_code unique (all rows have unique values now)
        try {
            Schema::table('stores', function (Blueprint $table) {
                $table->unique('store_code');
            });
        } catch (\Exception $e) {
            // unique index might already exist
        }

        // Step 7: Add printer_ip_address index
        try {
            Schema::table('stores', function (Blueprint $table) {
                $table->index('printer_ip_address');
            });
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    public function down(): void
    {
        $dropColumns = [
            'store_name', 'store_code', 'owner_name', 'country',
            'printer_ip_address', 'printer_port', 'printer_name',
            'printer_type', 'paper_size', 'zip_code',
            'opening_time', 'closing_time', 'gst_number', 'logo', 'notes',
        ];

        Schema::table('stores', function (Blueprint $table) use ($dropColumns) {
            foreach ($dropColumns as $col) {
                if (Schema::hasColumn('stores', $col)) {
                    try { $table->dropColumn($col); } catch (\Exception $e) {}
                }
            }
            if (Schema::hasColumn('stores', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
