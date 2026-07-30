<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model
{
    use HasSlug;

    protected $fillable = [
        'title',
        'slug',
        'is_active',
        'icon_svg',
        'color',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getSvgsForCategory($categoryId, $eventIds)
    {
        return self::whereIn('id', $eventIds)
            ->whereHas('categories', fn($q) => $q->where('categories.id', $categoryId))
            // ->whereNotNull('icon_svg')
            ->get(['icon_svg', 'color','title'])
            ->toArray();
    }


    public static function getIcon($string)
    {
         if ($string !== strip_tags($string)) {
            return $string;
        } 
        return '<i data-lucide="' . e($string) . '" class="w-5 h-5"></i>';
    }
    public function icon($string)
    {
        if ($this->isHtml($string)) {
            return $string;
        } 
        return '<i data-lucide="' . e($string) . '" class="w-5 h-5"></i>';
    }

    public function isHtml($string)
    {
        return $string !== strip_tags($string);
    }
}
