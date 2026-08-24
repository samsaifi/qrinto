<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'order_number', 'invoice_number', 'status',
        'subtotal', 'discount_amount', 'tax_amount', 'shipping_amount', 'total',
        'currency',
        'coupon_code', 'shipping_address', 'billing_address',
        'payment_gateway', 'payment_id', 'payment_status', 'paid_at',
        'notes', 'special_instructions', 'tracking_number', 'tracking_url',
        'fulfillment_type', 'store_id', 'estimated_delivery_date', 'print_job_id',
        'guest_email', 'guest_phone', 'admin_notes', 'flow_data', 
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'paid_at' => 'datetime',
            'estimated_delivery_date' => 'date',
            'flow_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function printLogs(): HasMany
    {
        return $this->hasMany(PrintLog::class)->latest();
    }

    /**
     * Detailed QZ Tray print events (order_print_logs). Distinct from the
     * lighter PrintLog audit trail — this one snapshots the tray/media/PDF
     * used, timing, and success/failure per QZ Tray dispatch.
     */
    public function orderPrintLogs(): HasMany
    {
        return $this->hasMany(OrderPrintLog::class)->latest('printed_at');
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -5));
        return "{$prefix}-{$date}-{$random}";
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return "{$prefix}-{$year}-" . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => '#f59e0b',
            'confirmed' => '#3b82f6',
            'processing', 'printing' => '#8b5cf6', // Indigo
            'shipped' => '#8b5cf6',
            'ready', 'ready_for_pickup', 'delivered_store' => '#10b981', // Emerald
            'done', 'completed', 'delivered' => '#0ea5e9',
            'cancelled' => '#ef4444',
            'refunded' => '#6b7280',
            default => '#6b7280',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Shared order state machine (store panel / track page / emails)
    |--------------------------------------------------------------------------
    | The database `status` column holds a variety of legacy strings. These
    | helpers collapse them into the four canonical pickup stages used by the
    | consumer flow — New, Printing, Ready for pickup, Picked up — so the store
    | queue, the tracker, and emails all read from one source of truth.
    */

    public const STAGE_NEW       = 'new';
    public const STAGE_PRINTING  = 'printing';
    public const STAGE_READY     = 'ready';
    public const STAGE_DONE      = 'done';
    public const STAGE_CANCELLED = 'cancelled';

    /**
     * Ordered pickup stages with their customer-facing labels and icons.
     */
    public static function pickupStages(): array
    {
        return [
            self::STAGE_NEW      => ['label' => 'New',              'icon' => 'inbox'],
            self::STAGE_PRINTING => ['label' => 'Printing',         'icon' => 'printer'],
            self::STAGE_READY    => ['label' => 'Ready for pickup', 'icon' => 'package-check'],
        ];
    }

    /**
     * Map any raw status string onto its canonical pickup stage.
     */
    public function getQueueStageAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'done', 'completed', 'delivered', 'picked_up'            => self::STAGE_DONE,
            'ready', 'ready_for_pickup', 'delivered_store'           => self::STAGE_READY,
            'processing', 'printing', 'in_production', 'shipped'     => self::STAGE_PRINTING,
            'cancelled', 'refunded'                                  => self::STAGE_CANCELLED,
            default                                                  => self::STAGE_NEW, // pending, confirmed, ...
        };
    }

    /**
     * The forward transition (single action button) for the current stage, or
     * null when the order is finished/cancelled and has no next action.
     *
     * `email` marks a transition that notifies the customer (ready-for-pickup).
     */
    public function getForwardActionAttribute(): ?array
    {
        // `to` holds the actual DB enum value to write (the status column is an
        // ENUM: pending, confirmed, processing, printing, shipped,
        // delivered_store, delivered, cancelled, refunded). queue_stage() maps
        // these back onto the canonical pickup stages.
        return match ($this->queue_stage) {
            self::STAGE_NEW      => ['to' => 'printing',         'label' => 'Print on 931BL', 'icon' => 'printer'],
            self::STAGE_PRINTING => ['to' => 'delivered_store', 'label' => 'Mark ready',     'icon' => 'package-check', 'email' => true],
            self::STAGE_READY    => ['to' => 'delivered',       'label' => 'Picked up',      'icon' => 'check-circle-2'],
            default              => null,
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'refunded' => 'gray',
            default => 'gray',
        };
    }
}
