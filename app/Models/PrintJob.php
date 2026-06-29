<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    protected $fillable = [
        'job_id', 'order_id', 'store_id', 'asset_url', 'media_size',
        'product_name', 'quantity', 'customer_name', 'settings',
        'status', 'error_message', 'printed_at',
    ];

    protected $casts = [
        'settings'   => 'array',
        'printed_at' => 'datetime',
    ];

    /**
     * Relationship to the Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship to the Store.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Map this job to the structure expected by the C# Print Agent.
     */
    public function toAgentArray(): array
    {
        return [
            'JobId'        => (string) $this->job_id,
            'OrderId'      => (string) $this->order->order_number,
            'AssetUrl'     => $this->asset_url, 
            'MediaSize'    => $this->media_size,
            'Product'      => $this->product_name,
            'Quantity'     => (int) $this->quantity,
            'CustomerName' => $this->customer_name ?? 'Guest',
            'Settings'     => $this->settings ?? [
                'ColorMode'   => 'CMYK',
                'Resolution'  => 1200,
                'DuplexMode'  => 'None',
                'MediaType'   => 'Glossy',
            ],
            'ReceivedAt'   => $this->created_at->toIso8601String(),
            'State'        => 0, // 0 = Queued
        ];
    }
}
