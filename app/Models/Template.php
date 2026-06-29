<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'icon_type', 'icon_value',
        'canvas_config', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'canvas_config' => 'array',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Return templates that match the given category_id OR are global (category_id IS NULL).
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->whereNull('category_id');
            if ($categoryId) {
                $q->orWhere('category_id', $categoryId);
            }
        });
    }

    public function getIconUrlAttribute(): ?string
    {
        if ($this->icon_type === 'upload' && $this->icon_value) {
            return asset('storage/' . $this->icon_value);
        }
        return null;
    }
}
