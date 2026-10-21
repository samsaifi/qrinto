<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'template_id', 'product_name', 'quantity',
        'unit_price', 'total_price', 'customization_data',
        'selected_options', 'uploaded_images', 'print_ready_url', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'customization_data' => 'array',
            'selected_options' => 'array',
            'uploaded_images' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Design image URLs in PAGE order (page 1 = front … page 4 = back), for the
     * store's design preview. Resolves each slot the same way OrderPdfService
     * does: customer upload → item's uploaded_images → product default image.
     */
    public function designImageUrls(): array
    {
        $slots = ['frame_image', 'sample_image', 'background_image', 'overlay_image'];
        $pages = (int) ($this->product->no_of_pages ?? 1);
        $pages = in_array($pages, [2, 4], true) ? $pages : 1;

        $customization = (array) ($this->customization_data ?? []);
        $uploadIds = (array) ($customization['canvas_mapped_ids'] ?? ($customization['upload_ids'] ?? []));
        $uploadedImages = (array) ($this->uploaded_images ?? []);
        $uploads = CustomerUpload::whereIn('id', array_filter(array_values($uploadIds)))->get()->keyBy('id');

        $urls = [];
        foreach (array_slice($slots, 0, $pages) as $slot) {
            $path = optional($uploads->get($uploadIds[$slot] ?? null))->file_path
                ?? ($uploadedImages[$slot] ?? null)
                ?? ($this->product ? $this->product->getRawOriginal($slot) : null);

            if ($path) {
                $urls[] = asset('storage/' . ltrim($path, '/'));
            }
        }

        if (!$urls && !empty($customization['preview_url'])) {
            $urls[] = $customization['preview_url'];
        }

        return $urls;
    }
}
