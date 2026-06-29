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
        'store_name', 'store_code', 'owner_name', 'email', 'phone',
        'address', 'city', 'state', 'zip_code', 'country', 'lat', 'lon',
        'printer_ip_address', 'printer_port', 'printer_name',
        'printer_type', 'paper_size',
        'ftp_host', 'ftp_port', 'ftp_username', 'ftp_password',
        'ftp_remote_path', 'ftp_passive_mode',
        'is_active', 'opening_time', 'closing_time',
        'gst_number', 'logo', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'printer_port'     => 'integer',
            'ftp_port'         => 'integer',
            'ftp_passive_mode' => 'boolean',
            'opening_time'     => 'datetime:H:i',
            'closing_time'     => 'datetime:H:i',
        ];
    }

    /* ── Scopes ─────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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

