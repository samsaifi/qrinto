<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add is_test column if not exists
        if (!Schema::hasColumn('stores', 'is_test')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->boolean('is_test')->default(false);
            });
        }

        // 2. Clean up store names and flag test stores
        $testStoreNames = [
            'Walsh 931 Test' => 'Noritsu Test - Walsh 931',
            'Noritsu Test Dunst' => 'Noritsu Test - Dunst',
            'Docherty-931 Test' => 'Noritsu Test - Docherty 931',
            'Noritsu Test - Alberto' => 'Noritsu Test - Alberto',
            'Bp - NAC store' => 'Noritsu Test - Bp NAC',
        ];

        $nameCol = Schema::hasColumn('stores', 'store_name') ? 'store_name' : 'name';

        foreach ($testStoreNames as $oldName => $newName) {
            DB::table('stores')
                ->where($nameCol, $oldName)
                ->update([
                    $nameCol => $newName,
                    'is_test' => true,
                ]);
        }

        // Flag any remaining stores with 'Test' or 'NAC' in name as test stores
        DB::table('stores')
            ->where(function ($query) use ($nameCol) {
                $query->where($nameCol, 'like', '%Test%')
                      ->orWhere($nameCol, 'like', '%NAC%');
            })
            ->update(['is_test' => true]);

        // 3. Update Flat card price to $1.00
        if (Schema::hasColumn('products', 'base_price')) {
            DB::table('products')
                ->where('name', 'like', '%Flat%')
                ->orWhere('slug', 'like', '%flat%')
                ->update(['base_price' => 1.00]);
        }

        if (Schema::hasColumn('product_types', 'price')) {
            DB::table('product_types')
                ->where('name', 'like', '%Flat%')
                ->orWhere('slug', 'like', '%flat%')
                ->update(['price' => 1.00]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stores', 'is_test')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropColumn('is_test');
            });
        }
    }
};
