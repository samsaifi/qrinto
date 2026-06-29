<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\CurrencyService;

class ProductOptionValue extends Model
{
    protected $fillable = [
        'option_group_id', 'label', 'value', 'price_modifier', 'price_modifier_cad', 'price_type',
        'image', 'description', 'is_default', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'decimal:2',
            'price_modifier_cad' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class, 'option_group_id');
    }

    public function getFormattedPriceModifierAttribute(): string
    {
        $modifier = (float) $this->price_modifier;
        $converted = CurrencyService::convert($modifier);
        $symbol = CurrencyService::getSymbol();

        if ($modifier == 0) return 'Included';
        $sign = $modifier > 0 ? '+' : '';
        return match($this->price_type) {
            'percentage' => $sign . $modifier . '%',
            'absolute' => $symbol . number_format($converted, 2),
            default => $sign . $symbol . number_format($converted, 2),
        };
    }

    /**
     * Get the currency-aware price modifier.
     */
    public function getActivePriceModifierAttribute(): float
    {
        return (float) CurrencyService::convert((float) $this->price_modifier);
    }
}
