<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPrintLog extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'store_id',
        'user_id',
        'order_number',
        'printed_at',
        'printer_name',
        'tray_key',
        'tray_label',
        'is_default_printer',
        'copies',
        'size',
        'media',
        'gsm',
        'user_type',
        'pdf_path',
        'pdf_bytes',
        'pdf_sha256',
        'status',
        'error_message',
        'duration_ms',
        'client_ip',
        'user_agent',
        'qz_tray_version',
    ];

    protected $casts = [
        'printed_at'         => 'datetime',
        'is_default_printer' => 'bool',
        'copies'             => 'int',
        'user_type'          => 'int',
        'pdf_bytes'          => 'int',
        'duration_ms'        => 'int',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
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
