<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon_type', 'icon_value',
        'canvas_config', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'canvas_config' => 'array',
        'is_active'     => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getIconUrlAttribute(): ?string
    {
        if ($this->icon_type === 'upload' && $this->icon_value) {
            return asset('storage/' . $this->icon_value);
        }
        return null;
    }
}
