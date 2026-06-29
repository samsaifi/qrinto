<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'type', 'value',
        'min_order_amount', 'max_discount', 'usage_limit',
        'usage_per_user', 'used_count', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isValid(float $orderAmount = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        
        $minAmount = \App\Services\CurrencyService::convert((float) $this->min_order_amount);
        if ($this->min_order_amount && $orderAmount < $minAmount) return false;
        
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        $discount = match($this->type) {
            'percentage' => $amount * ($this->value / 100),
            'fixed' => \App\Services\CurrencyService::convert((float) $this->value),
            default => 0,
        };

        if ($this->max_discount) {
            $maxDiscount = \App\Services\CurrencyService::convert((float) $this->max_discount);
            $discount = min($discount, $maxDiscount);
        }

        return round(min($discount, $amount), 2);
    }
}
