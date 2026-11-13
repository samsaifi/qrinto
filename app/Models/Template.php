<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    public const KIND_PHOTO = 'photo';
    public const KIND_CARD  = 'card';
    public const KIND_LAYOUT = 'layout';
    public const KIND_TEXT    = 'text_style';  // Text panel styles (one or more lines)
    public const KIND_STICKER = 'sticker_set'; // Stickers panel: one emoji category
    public const KIND_BG      = 'bg_palette';  // Background panel: one colour theme
    public const KIND_SVG     = 'svg_art';     // Stickers panel artwork: one SVG with default size/position

    /** Kinds that belong to the greeting-card editor (/editor) and use the card admin screens. */
    public const CARD_EDITOR_KINDS = [self::KIND_CARD, self::KIND_LAYOUT, self::KIND_TEXT, self::KIND_STICKER, self::KIND_BG, self::KIND_SVG];

    protected $fillable = [
        'category_id', 'name', 'slug', 'kind', 'icon_type', 'icon_value',
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

    /** Customizer photo-layout templates (everything that is not a card editor template). */
    public function scopePhoto($query)
    {
        return $query->where('kind', self::KIND_PHOTO);
    }

    /** Greeting-card editor (/editor) templates. */
    public function scopeCard($query)
    {
        return $query->where('kind', self::KIND_CARD);
    }

    /** Card editor Layouts panel entries (canvas_config: view + pages). */
    public function scopeLayout($query)
    {
        return $query->where('kind', self::KIND_LAYOUT);
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
