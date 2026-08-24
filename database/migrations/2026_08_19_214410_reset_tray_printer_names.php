<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Any stores that saved tray_config with the old printer-name shape
 * ("Noritsu 931BL Tray 1", no parens) get reset so the current defaults —
 * "Noritsu 931BL (Tray N)" matching the driver's installed queue names —
 * take effect. Stores that had already customised their printer names are
 * left alone (their names don't begin with the old default).
 */
return new class extends Migration
{
    public function up(): void
    {
        $stores = DB::table('stores')->whereNotNull('tray_config')->get(['id', 'tray_config']);

        foreach ($stores as $store) {
            $cfg = json_decode($store->tray_config, true);
            if (!is_array($cfg)) continue;

            $usesOldDefault = false;
            foreach ($cfg as $row) {
                $p = $row['printer'] ?? '';
                if (in_array($p, ['Noritsu 931BL', 'Noritsu 931BL Tray 1', 'Noritsu 931BL Tray 2', 'Noritsu 931BL Tray 3', 'Noritsu 931BL Tray 4'], true)) {
                    $usesOldDefault = true;
                    break;
                }
            }
            if ($usesOldDefault) {
                DB::table('stores')->where('id', $store->id)->update(['tray_config' => null]);
            }
        }
    }

    public function down(): void
    {
        // No reverse — the old defaults are the wrong values by definition.
    }
};
