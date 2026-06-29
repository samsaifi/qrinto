<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use App\Services\CurrencyService;

class ProductType extends Model
{
    use HasSlug;

    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'slug',
        'icon_svg',
        'price',
        'old_price',
        'price_cad',
        'old_price_cad',
        'width',
        'height',
        'unit',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'price_cad' => 'decimal:2',
            'old_price_cad' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductType::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the currency-aware price.
     */
    public function getActivePriceAttribute(): float
    {
        return CurrencyService::getPrice($this, 'price');
    }

    /**
     * Get the currency-aware old/compare price.
     */
    public function getActiveOldPriceAttribute(): ?float
    {
        if (CurrencyService::isCanadian()) {
            $cad = $this->old_price_cad;
            if ($cad !== null) return (float) $cad;
        }
        return $this->old_price ? (float) $this->old_price : null;
    }
}
