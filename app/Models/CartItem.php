<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'quantity', 'unit_price',
        'customization_data', 'selected_options',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'customization_data' => 'array',
            'selected_options' => 'array',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalAttribute(): float
    {
        return round($this->unit_price * $this->quantity, 2);
    }
}
