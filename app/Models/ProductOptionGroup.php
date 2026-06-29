<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionGroup extends Model
{
    protected $fillable = [
        'product_id', 'name', 'slug', 'display_type', 'is_required', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class, 'option_group_id')->orderBy('sort_order');
    }

    public function activeValues(): HasMany
    {
        return $this->values()->where('is_active', true);
    }
}
