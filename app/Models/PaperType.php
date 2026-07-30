<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PaperType extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
