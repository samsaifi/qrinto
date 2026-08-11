<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductType;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    /**
     * List all Product Types / Categories
     */
    public function index()
    {
        $productTypes = ProductType::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->get()
            ->map(function ($pt) {
                return [
                    'id'             => $pt->id,
                    'name'           => $pt->name,
                    'slug'           => $pt->slug,
                    'description'    => $pt->description ?? null,
                    'icon'           => $pt->icon ?? null,
                    'image_url'      => $pt->image_path ? asset('storage/' . $pt->image_path) : null,
                    'products_count' => $pt->products_count,
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $productTypes->count(),
            'data'    => $productTypes,
        ]);
    }

    /**
     * Show category details with products
     */
    public function show($slugOrId)
    {
        $pt = ProductType::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->with(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->first();

        if (!$pt) {
            return response()->json([
                'success' => false,
                'message' => 'Product Type / Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $pt->id,
                'name'        => $pt->name,
                'slug'        => $pt->slug,
                'description' => $pt->description ?? null,
                'image_url'   => $pt->image_path ? asset('storage/' . $pt->image_path) : null,
                'products'    => $pt->products->map(function ($product) {
                    return [
                        'id'                 => $product->id,
                        'name'               => $product->name,
                        'slug'               => $product->slug,
                        'short_description'  => $product->short_description,
                        'base_price'         => (float) $product->base_price,
                        'compare_price'      => $product->compare_price ? (float) $product->compare_price : null,
                        'formatted_price'    => \App\Services\CurrencyService::format($product->base_price),
                        'featured_image_url' => $product->featured_image_url ?? $product->sample_image_url ?? $product->frame_image_url,
                        'no_of_pages'        => $product->no_of_pages ?? 1,
                        'is_featured'        => (bool) $product->is_featured,
                    ];
                }),
            ],
        ]);
    }
}
