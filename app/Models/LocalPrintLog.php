<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per local-print attempt. See the create_local_print_logs migration
 * and LocalPrintLogService for how these are written across the print lifecycle.
 */
class LocalPrintLog extends Model
{
    protected $fillable = [
        'store_id', 'user_id',
        'source', 'origin', 'source_url', 'print_type', 'status',
        'order_id', 'design_id', 'template_id', 'product_id',
        'printer_name', 'printer_id', 'printer_model', 'tray_key', 'tray_label',
        'paper_source', 'paper_size', 'media_size', 'orientation', 'color_mode',
        'duplex', 'scale_mode', 'scale_percentage', 'copies',
        'file_url', 'file_name', 'file_type', 'file_size', 'page_count', 'page_dimensions',
        'generated_pdf_path', 'generated_pdf_url',
        'request_payload', 'response_payload', 'printer_settings', 'design_settings', 'system_information',
        'printtrays_job_id', 'error_message',
        'ip_address', 'user_agent', 'browser', 'operating_system', 'device_name', 'session_id',
        'geo_city', 'geo_region', 'geo_country', 'geo_country_code',
        'requested_at', 'started_at', 'completed_at', 'failed_at', 'duration_ms',
    ];

    protected $casts = [
        'request_payload'    => 'array',
        'response_payload'   => 'array',
        'printer_settings'   => 'array',
        'design_settings'    => 'array',
        'system_information' => 'array',
        'copies'             => 'int',
        'scale_percentage'   => 'int',
        'file_size'          => 'int',
        'page_count'         => 'int',
        'duration_ms'        => 'int',
        'requested_at'       => 'datetime',
        'started_at'         => 'datetime',
        'completed_at'       => 'datetime',
        'failed_at'          => 'datetime',
    ];

    /** Human labels for the known source pages. */
    public const SOURCES = [
        'pdf_check'                      => 'PDF Check',
        'own_design_customizer_preview'  => 'Customizer',
        'local'                          => 'Local',
    ];

    /**
     * Where the print was triggered from. `web` = browser (LPLog wrapper on the
     * public print pages). `standalone` = the kiosk/desktop app posting via the
     * /api/v1/local-print-logs endpoints.
     */
    public const ORIGIN_WEB        = 'web';
    public const ORIGIN_STANDALONE = 'standalone';
    public const ORIGINS = [
        self::ORIGIN_WEB        => 'Web',
        self::ORIGIN_STANDALONE => 'Standalone',
    ];

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ($this->source ?: '—');
    }

    public function originLabel(): string
    {
        return self::ORIGINS[$this->origin] ?? ($this->origin ?: '—');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
