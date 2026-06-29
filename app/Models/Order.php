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
        'notes', 'tracking_number', 'tracking_url',
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
            'processing' => '#6366f1',
            'printing' => '#8b5cf6', // Indigo
            'shipped' => '#8b5cf6',
            'delivered_store' => '#10b981', // Emerald
            'delivered' => '#22c55e',
            'cancelled' => '#ef4444',
            'refunded' => '#6b7280',
            default => '#6b7280',
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
