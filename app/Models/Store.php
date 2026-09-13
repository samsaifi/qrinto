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
        return [
            '8.5x11'      => 'Letter',
            'Tabloid'     => 'Tabloid',
            '8.5x14'      => 'Legal',
            'Statement'   => 'Statement',
            'Executive'   => 'Executive',
            'A3'          => 'A3',
            'A4'          => 'A4',
            'A5'          => 'A5',
            'A6'          => 'A6',
            'B4'          => 'B4',
            'B5'          => 'B5',
            'B6'          => 'B6',
            'B6Half'      => 'B6 Half',
            'B7'          => 'B7',
            'B8'          => 'B8',
            '8.5x13.5'    => 'Legal13.5',
            '8.5x13'      => 'Legal13',
            '8.5x8.5'     => '8.5"SQ',
            'Folio'       => 'Folio',
            '3x5'         => 'Index Card 3 x 5in',
            '4x6'         => '4 x 6in',
            '5x7'         => '5 x 7in',
            '5x7.25'      => '5 x 7.25in E2E',
            '8K-260x368'  => '8K 260 x 368mm',
            '8K-270x390'  => '8K 270 x 390mm',
            '8K-273x394'  => '8K 273 x 394mm',
            '16K-184x260' => '16K 184 x 260mm',
            '16K-195x270' => '16K 195 x 270mm',
            '16K-197x273' => '16K 197 x 273mm',
            'Legal-E2E'   => 'Legal E2E',
            'Letter-E2E'  => 'Letter E2E',
            '4x6-E2E'     => '4 x 6in E2E',
            '5x7-E2E'     => '5 x 7in E2E',
            '7x10-E2E'    => '7 x 10in E2E',
            'custom'      => 'PostScript Custom Page Size',
        ];
    }

    /**
     * Dimensions (width × height in inches) for every known paper size.
     * Used by the front-end to derive hidden size_width / size_height fields,
     * and by matchTrayForSize for dimension-based matching.
     */
    public static function traySizeDimensions(): array
    {
        return [
            // Dimensions verified against the Noritsu 931-BL PS driver's own
            // PrintCapabilities (GetPrintCapabilities XML), in inches.
            '8.5x11'      => [8.5, 11],
            'Tabloid'     => [11, 17],
            '8.5x14'      => [8.5, 14],
            'Statement'   => [5.5, 8.5],
            'Executive'   => [7.25, 10.5],
            'A3'          => [11.69, 16.54],
            'A4'          => [8.27, 11.69],
            'A5'          => [5.83, 8.27],
            'A6'          => [4.13, 5.83],
            'B4'          => [10.12, 14.33],
            'B5'          => [7.17, 10.12],
            'B6'          => [5.04, 7.17],
            'B6Half'      => [2.53, 7.17],
            'B7'          => [3.58, 5.04],
            'B8'          => [2.51, 3.58],
            '8.5x13.5'    => [8.5, 13.5],
            '8.5x13'      => [8.5, 13],
            '8.5x8.5'     => [8.5, 8.5],
            'Folio'       => [8.26, 13],
            '3x5'         => [3, 5],
            '4x6'         => [4, 6],
            '5x7'         => [5, 7],
            '5x7.25'      => [5.57, 7.74],
            '8K-260x368'  => [10.24, 14.49],
            '8K-270x390'  => [10.62, 15.36],
            '8K-273x394'  => [10.75, 15.5],
            '16K-184x260' => [7.25, 10.24],
            '16K-195x270' => [7.68, 10.62],
            '16K-197x273' => [7.75, 10.75],
            'Legal-E2E'   => [9.07, 14.49],
            'Letter-E2E'  => [9.07, 11.49],
            '4x6-E2E'     => [4.57, 6.49],
            '5x7-E2E'     => [5.57, 7.49],
            '7x10-E2E'    => [7.57, 10.49],
            // 'custom' (PostScript Custom Page Size) has no fixed dimensions —
            // intentionally omitted so buildPrintConfig() sends no cfg.size for it.
        ];
    }

    /**
     * Try to map arbitrary paper dimensions (inches) to a standard size code
     * for deriveUserType compatibility. Returns null if no match.
     */
    public static function normalizeToSizeCode(?float $width, ?float $height): ?string
    {
        if (!$width || !$height) return null;
        foreach (self::traySizeDimensions() as $code => [$w, $h]) {
            if ((abs($width - $w) < 0.15 && abs($height - $h) < 0.15) ||
                (abs($width - $h) < 0.15 && abs($height - $w) < 0.15)) {
                return $code;
            }
        }
        return null;
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
     *   paper by gsm - 120 = UT1, 120-150 = UT2, 150-270 = UT5, 270-324 = UT6;
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
     * Stable slug for a printer name - used as the tray "key" so lookups
     * survive across visits without depending on fragile array indexes.
     */
    public static function trayKeyFromPrinter(string $printer): string
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', trim($printer)));
        return trim($slug, '_') ?: 'printer';
    }

    /**
     * Normalised tray rows for the panel - one entry per configured printer
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

            $sizeKey = $entry['size'] ?? null;
            $sw = isset($entry['size_width'])  ? (float) $entry['size_width']  : null;
            $sh = isset($entry['size_height']) ? (float) $entry['size_height'] : null;
            if (!$sw && $sizeKey && isset(self::traySizeDimensions()[$sizeKey])) {
                [$sw, $sh] = self::traySizeDimensions()[$sizeKey];
            }

            $row = [
                'key'         => (string) ($entry['key'] ?? self::trayKeyFromPrinter($printer)),
                'label'       => (string) ($entry['label'] ?? $printer),
                'printer'     => $printer,
                'size'        => $sizeKey,
                'size_width'  => $sw,
                'size_height' => $sh,
                'media'       => $entry['media'] ?? null,
                'gsm'         => $entry['gsm']   ?? null,
                'density'     => $entry['density'] ?? null,
                'landscape'       => (bool) ($entry['landscape'] ?? false),
                'duplex'          => $entry['duplex'] ?? 'simplex',
                'color'           => (bool) ($entry['color'] ?? true),
                'input_bin'       => $entry['input_bin'] ?? null,
                'quality'         => $entry['quality'] ?? null,
                'media_type_live' => $entry['media_type_live'] ?? null,
                'enabled'     => (bool) ($entry['enabled'] ?? false),
            ];
            $sizeCode = self::normalizeToSizeCode($row['size_width'], $row['size_height']) ?? $row['size'];
            $row['user_type'] = self::deriveUserType($sizeCode, $row['media'], $row['gsm']);
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

            // Try exact string match on the size field.
            $exact = $rows->first(fn($r) => strtolower($r['size'] ?? '') === $needle);
            if ($exact) return $exact;

            // Try matching by stored dimensions against the size code.
            $knownDims = ['4x6' => [4, 6], '5x7' => [5, 7], '7x10' => [7, 10], '8.5x11' => [8.5, 11]];
            if (isset($knownDims[$needle])) {
                [$tw, $th] = $knownDims[$needle];
                $byDims = $rows->first(fn($r) =>
                    $r['size_width'] && $r['size_height'] &&
                    ((abs($r['size_width'] - $tw) < 0.15 && abs($r['size_height'] - $th) < 0.15) ||
                     (abs($r['size_width'] - $th) < 0.15 && abs($r['size_height'] - $tw) < 0.15))
                );
                if ($byDims) return $byDims;
            }
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

