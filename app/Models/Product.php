<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use App\Services\CurrencyService;

class Product extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = [
        'category_id', 'product_type_id', 'paper_type_id', 'store_id', 'product_store',
        'created_by', 'event_id',
        'name', 'slug', 'short_description', 'description',
        'base_price', 'compare_price', 'base_price_cad', 'compare_price_cad',
        'sku', 'frame_image', 'sample_image',
        'background_image', 'overlay_image',
        'min_images', 'max_images', 'allowed_print_types', 'customization_config',
        'mask_data',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_featured', 'is_active', 'sort_order', 'views_count', 'tags',
        'pdf_orientation',
        'no_of_pages'
    ];
    public $no_of_pages_array = [
        // 1 => 'Megnet category without marking area', 
        1 => 'Flat Single ', 
        2 => 'Flat Double ', 
        4 => 'Folded ', 
    ];
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'base_price_cad' => 'decimal:2',
            'compare_price_cad' => 'decimal:2',
            'allowed_print_types' => 'array',
            'customization_config' => 'array',
            'mask_data' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function paperType(): BelongsTo
    {
        return $this->belongsTo(PaperType::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function optionGroups(): HasMany
    {
        return $this->hasMany(ProductOptionGroup::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFrameImageUrlAttribute(): ?string
    {
        if ($this->frame_image) {
            return asset('storage/' . $this->frame_image);
        }
        return null;
    }

    public function getFrameImageThumbnailAttribute(): ?string
    {
        if (!$this->frame_image) return null;
        
        $thumbPath = 'thumbnails/products/' . $this->frame_image;
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($thumbPath)) {
            return asset('storage/' . $thumbPath);
        }
        
        return $this->frame_image_url;
    }

    public function getSampleImageUrlAttribute(): ?string
    {
        if ($this->sample_image) {
            return asset('storage/' . $this->sample_image);
        }
        return null;
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        if ($this->background_image) {
            return asset('storage/' . $this->background_image);
        }
        return null;
    }

    public function getOverlayImageUrlAttribute(): ?string
    {
        if ($this->overlay_image) {
            return asset('storage/' . $this->overlay_image);
        }
        return null;
    }

    /**
     * Backward-compatible alias: returns frame image or first gallery image.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if ($this->frame_image) {
            return asset('storage/' . $this->frame_image);
        }
        $primary = $this->images()->where('is_primary', true)->first();
        return $primary ? asset('storage/' . $primary->image_path) : null;
    }

    /**
     * Always return a thumbnail URL for the given storage-relative image path.
     * If the thumbnail does not exist yet it is created first, then returned.
     */
    public function getFeaturedImageUrl($img_url): ?string
    {
        if (!$img_url) {
            return null;
        }

        // Accept either a full asset URL (e.g. $product->featured_image_url)
        // or a plain storage-relative path — normalise to the relative path.
        $img_url = str_replace(asset('storage') . '/', '', $img_url);
        $img_url = ltrim(preg_replace('#^/?storage/#', '', $img_url), '/');

        // Original file on the "public" storage disk.
        $source = storage_path('app/public/' . $img_url);

        // Thumbnail mirrors the original path under a "thumbnails/" prefix.
        $thumbRelative = 'thumbnails/' . $img_url;
        $thumbFull = storage_path('app/public/' . $thumbRelative);

        // Create the thumbnail if it is missing or older than the source.
        if (!is_file($thumbFull) || (is_file($source) && filemtime($thumbFull) < filemtime($source))) {
            if (!is_file($source)) {
                // No source to build from — fall back to the original URL.
                return asset('storage/' . $img_url);
            }

            if (!is_dir(dirname($thumbFull))) {
                @mkdir(dirname($thumbFull), 0755, true);
            }

            try {
                $manager = new \Intervention\Image\ImageManager(
                    new \Intervention\Image\Drivers\Gd\Driver()
                );
                $manager->read($source)
                    ->scaleDown(width: 400) // keep aspect ratio, max 400px wide
                    ->save($thumbFull, quality: 80);
            } catch (\Throwable $e) {
                // If thumbnailing fails, degrade gracefully to the full image.
                return asset('storage/' . $img_url);
            }
        }

        // Always return the thumbnail.
        return asset('storage/' . $thumbRelative);
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if ($this->compare_price && $this->compare_price > $this->base_price) {
            return round((($this->compare_price - $this->base_price) / $this->compare_price) * 100);
        }
        return null;
    }

    /**
     * Get the currency-aware base price.
     */
    public function getActivePriceAttribute(): float
    {
        return CurrencyService::getPrice($this, 'base_price');
    }

    /**
     * Get the currency-aware compare price.
     */
    public function getActiveComparePriceAttribute(): ?float
    {
        if ($this->compare_price === null) return null;
        return CurrencyService::convert((float) $this->compare_price);
    }

    /**
     * Calculate the final price based on selected options.
     */
    public function calculatePrice(array $selectedOptionIds = []): float
    {
        $price = (float) $this->active_price;

        if (empty($selectedOptionIds)) {
            return $price;
        }

        $optionValues = ProductOptionValue::whereIn('id', $selectedOptionIds)->get();

        foreach ($optionValues as $option) {
            $modifier = CurrencyService::convert((float) $option->price_modifier);

            switch ($option->price_type) {
                case 'fixed':
                    $price += $modifier;
                    break;
                case 'percentage':
                    $price += ($price * $modifier / 100);
                    break;
                case 'absolute':
                    $price = $modifier;
                    break;
            }
        }

        return max(0, round($price, 2));
    }

    /**
     * Parse dimensions from category or product name to return an aspect ratio.
     */
    public function getAspectRatioAttribute(): string
    {
        if ($this->productType && $this->productType->width && $this->productType->height) {
            return "{$this->productType->width} / {$this->productType->height}";
        }

        $name = $this->category ? $this->category->name : $this->name;
        
        // E.g., "3.5 x 5", "5x7", "4x6", "5×7"
        if (preg_match('/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/iu', $name, $matches)) {
            $w = (float) $matches[1];
            $h = (float) $matches[2];
            return "{$w} / {$h}";
        }
        
        // Default fallback aspect ratio
        return "4 / 3";
    }

    /**
     * Get physical print dimensions [width, height] in inches for high-res output.
     */
    public function getPrintDimensionsAttribute(): array
    {
        if ($this->productType && $this->productType->width && $this->productType->height) {
            return [(float)$this->productType->width, (float)$this->productType->height];
        }

        $name = $this->category ? $this->category->name : $this->name;
        
        if (preg_match('/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/iu', $name, $matches)) {
            $w = (float) $matches[1];
            $h = (float) $matches[2];
            return [$w, $h];
        }
        
        // Default physical dimensions in inches
        return [4, 3];
    }
}
