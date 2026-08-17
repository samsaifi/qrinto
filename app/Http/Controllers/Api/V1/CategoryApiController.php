<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    /**
     * Helper to format product data consistently
     */
    private function formatProduct($product)
    {
        return [
            'id'                 => $product->id,
            'name'               => $product->name,
            'slug'               => $product->slug,
            'short_description'  => $product->short_description,
            'base_price'         => (float) $product->base_price,
            'compare_price'      => $product->compare_price ? (float) $product->compare_price : null,
            'formatted_price'    => \App\Services\CurrencyService::format($product->base_price),
            'thumbnail_image_url' => $product->frame_image_thumbnail ??  $product->featured_image,
            'featured_image_url' => $product->featured_image_url ?? null,
            'second_image' => $product->sample_image_url ?? null,
            'third_image' => $product->background_image_url ?? null,
            'fourth_image' => $product->overlay_image_url ?? null,
            'no_of_pages'        => $product->no_of_pages ?? 1,
            'is_featured'        => (bool) $product->is_featured,
            'category_id'        => $product->category?->id,
            'category_name'      => $product->category?->name, 
        ];
    }
    // 
 
    private function formatCategory($category)
    {
        return [
            'id'                 => $category->id,
            'name'               => $category->name,
            'slug'               => $category->slug,
            'title'              => $category->title ?? null,
            'description'        => $category->description ?? null,
            'icon'               => $category->icon_svg ?? $category->icon ?? null,
            'price'              => $category->price,
            'old_price'          => $category->old_price,
            'width'              => $category->width,
            'height'             => $category->height,
            'unit'               => $category->unit,
            'sort_order'         => $category->sort_order,
        ];
    }

    /**
     * Helper to fetch active products for a ProductType (main entity),
     * matching product_type_id (including child sizes) and category_id.
     */
    private function getActiveProductsForType(ProductType $pt)
    {
        $childIds = $pt->children ? $pt->children->pluck('id')->toArray() : [];
        $allTypeIds = array_merge([$pt->id], $childIds);

        // Also check matching Category by slug, name or ID
        $matchingCatId = Category::where('slug', $pt->slug)
            ->orWhere('name', $pt->name)
            ->orWhere('id', $pt->id)
            ->value('id');

        return Product::where('is_active', true)
            ->where(function ($q) use ($pt, $allTypeIds, $matchingCatId) {
                $q->whereIn('product_type_id', $allTypeIds)
                  ->orWhere('category_id', $pt->id);
                if ($matchingCatId) {
                    $q->orWhere('category_id', $matchingCatId);
                }
            })
            ->get();
    }

    /**
     * List all ProductTypes (main categories) with all their active products
     */
    public function index()
    {
        $productTypes = ProductType::where('is_active', true)->where('parent_id', null)
            ->with(['children' => fn ($q) => $q->where('is_active', true) ])
            ->orderBy('sort_order')
            ->get();

        $data = $productTypes->map(function ($pt) {
            $products = $this->getActiveProductsForType($pt);

            return [
                'id'             => $pt->id,
                'name'           => $pt->name,
                'slug'           => $pt->slug,
                'title'          => $pt->title ?? null,
                'description'    => $pt->description ?? null,
                'icon'           => $pt->icon_svg ?? $pt->icon ?? null,
                'image_url'      => $pt->image_path ? asset('storage/' . $pt->image_path) : null,
                'products_count' => $products->count(),
                'sizes'          => $pt->children->map(fn ($c) => $c->is_active == true ? $this->formatCategory($c) : null)->values(),
                'products'       => $products->map(fn ($p) => $this->formatProduct($p))->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $data->count(),
            'data'    => $data,
        ]);
    }

    /**
     * Show category (ProductType) details with all active products
     */
    public function show($slugOrId)
    {
        $pt = ProductType::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->with(['children' => fn ($q) => $q->where('is_active', true)])
            ->first();

        if (!$pt) {
            // Fallback lookup in Category table if slugOrId is Category
            $cat = Category::where('slug', $slugOrId)->orWhere('id', $slugOrId)->first();
            if ($cat) {
                $pt = ProductType::where('slug', $cat->slug)->orWhere('name', $cat->name)->first();
            }
        }

        if (!$pt) {
            return response()->json([
                'success' => false,
                'message' => 'Product Type / Category not found.',
            ], 404);
        }

        $products = $this->getActiveProductsForType($pt);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $pt->id,
                'name'           => $pt->name,
                'slug'           => $pt->slug,
                'title'          => $pt->title ?? null,
                'description'    => $pt->description ?? null,
                'icon'           => $pt->icon_svg ?? $pt->icon ?? null,
                'image_url'      => $pt->image_path ? asset('storage/' . $pt->image_path) : null,
                'products_count' => $products->count(),
                'products'       => $products->map(fn ($p) => $this->formatProduct($p))->values(),
            ],
        ]);
    }
}
