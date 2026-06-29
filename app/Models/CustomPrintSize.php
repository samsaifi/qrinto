<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\CurrencyService;

class CustomPrintSize extends Model
{
    protected $fillable = [
        'key', 'label', 'dimensions', 'price', 'price_cad',
        'is_popular', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'price_cad'  => 'decimal:2',
            'is_popular' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    /**
     * Only active sizes, ordered by sort_order.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the currency-aware active price.
     */
    public function getActivePriceAttribute(): float
    {
        return CurrencyService::getPrice($this, 'price');
    }

    /**
     * Convert to the array format expected by the frontend JS.
     */
    public function toFrontendArray(): array
    {
        return [
            'id'         => $this->key,
            'label'      => $this->label,
            'dimensions' => $this->dimensions,
            'price'      => (float) CurrencyService::getPrice($this, 'price'),
            'popular'    => $this->is_popular,
        ];
    }
}
