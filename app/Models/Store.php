<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_name', 'name', 'store_code', 'owner_name', 'email', 'phone',
        'address', 'city', 'state', 'zip_code', 'country', 'lat', 'lon',
        'printer_ip_address', 'printer_port', 'printer_name',
        'printer_type', 'paper_size',
        'ftp_host', 'ftp_port', 'ftp_username', 'ftp_password',
        'ftp_remote_path', 'ftp_passive_mode',
        'is_active', 'is_test', 'opening_time', 'closing_time',
        'gst_number', 'logo', 'notes', 'tray_config',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'is_test'          => 'boolean',
            'printer_port'     => 'integer',
            'ftp_port'         => 'integer',
            'ftp_passive_mode' => 'boolean',
            'opening_time'     => 'datetime:H:i',
            'closing_time'     => 'datetime:H:i',
            'tray_config'      => 'array',
        ];
    }

    /* ── Tray setup (store panel Trays screen) ──────────
     |  Option lists and the automatic printer "User Type" derivation
     |  (UT1–UT7) described in the dev spec §6. Staff pick size / media / gsm
     |  per tray; the User Type is worked out from those, never chosen by hand.
     */

    public const TRAY_KEYS = ['mp', 'tray1', 'tray2', 'tray3', 'tray4', 'tray5'];

    public static function trayLabels(): array
    {
        return [
            'mp'    => 'MP tray',
            'tray1' => 'Tray 1',
            'tray2' => 'Tray 2',
            'tray3' => 'Tray 3',
            'tray4' => 'Tray 4',
            'tray5' => 'Tray 5',
        ];
    }

    public static function traySizeOptions(): array
    {
        return ['4x6' => '4 × 6 in', '5x7' => '5 × 7 in', '7x10' => '7 × 10 in', '8.5x11' => '8.5 × 11 in'];
    }

    /** Media options grouped by how they drive the User Type. */
    public static function trayMediaOptions(): array
    {
        return [
            'plain'            => 'Plain paper',
            'cardstock'        => 'Cardstock',
            'cardstock_scored' => 'Cardstock, scored',
            'photo_glossy'     => 'Photo paper, glossy',
            'photo_lustre'     => 'Photo paper, lustre',
            'photo_matte'      => 'Photo paper, matte',
            'film'             => 'Film',
            'envelopes'        => 'Envelopes',
            'labels'           => 'Labels',
            'magnets'          => 'Magnets',
        ];
    }

    public static function trayGsmOptions(): array
    {
        return ['120' => '120 gsm', '120-150' => '120 to 150 gsm', '150-270' => '150 to 270 gsm', '270-324' => '270 to 324 gsm'];
    }

    /**
     * Derive the printer User Type (1–7) from a tray's size / media / gsm,
     * per the spec:
     *   film = UT7; envelopes & labels = UT3; magnets = UT6;
     *   paper by gsm — 120 = UT1, 120-150 = UT2, 150-270 = UT5, 270-324 = UT6;
     *   scored 150-324 = UT6, scored 324 (270-324) at 5×7 = UT5;
     *   a glossy or silk (lustre) surface shifts one step down: 1→2, 2→5, 5→6.
     */
    public static function deriveUserType(?string $size, ?string $media, ?string $gsm): ?int
    {
        if (!$media) {
            return null;
        }

        if ($media === 'film')                        return 7;
        if (in_array($media, ['envelopes', 'labels'])) return 3;
        if ($media === 'magnets')                      return 6;

        // Paper: start from the gsm band.
        $ut = match ($gsm) {
            '120'     => 1,
            '120-150' => 2,
            '150-270' => 5,
            '270-324' => 6,
            default   => 1,
        };

        $scored = $media === 'cardstock_scored';
        $glossy = in_array($media, ['photo_glossy', 'photo_lustre']);

        if ($scored) {
            // Scored stock is UT6 across 150–324, except scored 324 at 5×7 → UT5.
            $ut = ($gsm === '270-324' && $size === '5x7') ? 5 : 6;
        }

        if ($glossy) {
            $ut = match ($ut) {
                1 => 2,
                2 => 5,
                5 => 6,
                default => $ut,
            };
        }

        return $ut;
    }

    /**
     * Stable slug for a printer name — used as the tray "key" so lookups
     * survive across visits without depending on fragile array indexes.
     */
    public static function trayKeyFromPrinter(string $printer): string
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', trim($printer)));
        return trim($slug, '_') ?: 'printer';
    }

    /**
     * Normalised tray rows for the panel — one entry per configured printer
     * (whatever the store saved on /store/trays). Each row carries the
     * derived User Type. Trays are dynamic: their identity is the PC printer
     * name, not a fixed mp/tray1..5 slot.
     */
    public function trayRows(): array
    {
        $rows = [];
        foreach (($this->tray_config ?? []) as $entry) {
            $printer = trim((string) ($entry['printer'] ?? ''));
            if ($printer === '') continue;

            $row = [
                'key'       => (string) ($entry['key'] ?? self::trayKeyFromPrinter($printer)),
                'label'     => (string) ($entry['label'] ?? $printer),
                'printer'   => $printer,
                'size'      => $entry['size']  ?? null,
                'media'     => $entry['media'] ?? null,
                'gsm'       => $entry['gsm']   ?? null,
                'enabled'   => (bool) ($entry['enabled'] ?? false),
            ];
            $row['user_type'] = self::deriveUserType($row['size'], $row['media'], $row['gsm']);
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Best-match tray for an order given its media dimensions.
     * Prefers an enabled tray whose loaded size exactly matches the order,
     * then falls back to the MP tray.
     */
    public function matchTrayForSize(?string $sizeCode): ?array
    {
        $rows = collect($this->trayRows())->where('enabled', true);
        if ($sizeCode) {
            $needle = str_replace([' ', '×', 'x'], ['', 'x', 'x'], strtolower($sizeCode));
            $exact  = $rows->first(fn($r) => strtolower($r['size'] ?? '') === $needle);
            if ($exact) return $exact;
        }
        // Fallback: any tray labelled "MP" if present, else the first enabled row.
        return $rows->first(fn($r) => stripos($r['label'] ?? '', 'MP') !== false) ?? $rows->first();
    }

    /* ── Scopes ─────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRealStores($query)
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('stores', 'is_test')) {
            return $query->where('is_test', false);
        }
        return $query;
    }

    /* ── Accessors ──────────────────────────────────── */

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country,
        ])->filter()->implode(', ');
    }

    /* ── Relationships ──────────────────────────────── */

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function printLogs(): HasMany
    {
        return $this->hasMany(PrintLog::class);
    }

    public function orderPrintLogs(): HasMany
    {
        return $this->hasMany(OrderPrintLog::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /* ── Currency ───────────────────────────────────── */

    /**
     * Check if this store is located in Canada.
     */
    public function isCanadian(): bool
    {
        return in_array($this->country, ['Canada', 'CA', 'canada', 'ca', 'CAN']);
    }

    /**
     * Get the currency code for this store.
     */
    public function getCurrencyAttribute(): string
    {
        return $this->isCanadian() ? 'CAD' : 'USD';
    }

    /**
     * Get the currency symbol for this store.
     */
    public function getCurrencySymbolAttribute(): string
    {
        return $this->isCanadian() ? 'C$' : '$';
    }

    /* ── Methods ────────────────────────────────────── */

    /**
     * Ping the printer IP to check if it is reachable.
     */
    public function isOnline(): bool
    {
        if (empty($this->printer_ip_address)) {
            return false;
        }

        try {
            $connection = @fsockopen(
                $this->printer_ip_address,
                $this->printer_port ?? 9100,
                $errno,
                $errstr,
                3 // 3 second timeout
            );

            if ($connection) {
                fclose($connection);
                return true;
            }
        } catch (\Exception $e) {
            // Printer not reachable
        }

        return false;
    }

    /**
     * Check if FTP configuration is set.
     */
    public function hasFtpConfig(): bool
    {
        return !empty($this->ftp_host) && !empty($this->ftp_username);
    }

    /**
     * Test FTP connection to the printer.
     */
    public function testFtpConnection(): array
    {
        if (!$this->hasFtpConfig()) {
            return ['success' => false, 'message' => 'FTP not configured.'];
        }

        try {
            $connection = @ftp_connect($this->ftp_host, $this->ftp_port ?? 21, 10);
            if (!$connection) {
                return ['success' => false, 'message' => "Cannot connect to FTP host: {$this->ftp_host}:{$this->ftp_port}"];
            }

            $login = @ftp_login($connection, $this->ftp_username, $this->ftp_password ?? '');
            if (!$login) {
                ftp_close($connection);
                return ['success' => false, 'message' => 'FTP login failed. Check username/password.'];
            }

            if ($this->ftp_passive_mode) {
                ftp_pasv($connection, true);
            }

            ftp_close($connection);
            return ['success' => true, 'message' => 'FTP connection successful.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'FTP error: ' . $e->getMessage()];
        }
    }
}

