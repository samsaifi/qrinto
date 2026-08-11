<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * List products with search, pagination, and category filters
     */
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)
            ->with(['category', 'productType', 'store']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type_slug')) {
            $query->whereHas('productType', function ($q) use ($request) {
                $q->where('slug', $request->type_slug);
            });
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $products = $query->paginate($perPage);

        $transformed = $products->getCollection()->map(function ($product) {
            return [
                'id'                 => $product->id,
                'name'               => $product->name,
                'slug'               => $product->slug,
                'short_description'  => $product->short_description,
                'sku'                => $product->sku,
                'base_price'         => (float) $product->base_price,
                'compare_price'      => $product->compare_price ? (float) $product->compare_price : null,
                'formatted_price'    => CurrencyService::format($product->base_price),
                'featured_image_url' => $product->featured_image_url ?? $product->sample_image_url ?? $product->frame_image_url,
                'no_of_pages'        => $product->no_of_pages ?? 1,
                'pdf_orientation'    => $product->pdf_orientation ?? 'portrait',
                'category_name'      => $product->category?->name,
                'product_type_name'  => $product->productType?->name,
                'store_name'         => $product->store?->store_name,
                'is_featured'        => (bool) $product->is_featured,
            ];
        });

        return response()->json([
            'success'   => true,
            'meta'      => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
            'data'      => $transformed,
        ]);
    }

    /**
     * Show detailed product specs, mask_data, canvas setup & gallery
     */
    public function show($slugOrId)
    {
        $product = Product::where('slug', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->with(['category', 'productType', 'paperType', 'store', 'images'])
            ->first();

        if (!$product || !$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Increment views count
        $product->increment('views_count');

        $galleryImages = $product->images->map(function ($img) {
            return Product::formatStorageUrl($img->image_path);
        })->toArray();

        $maskData = $product->mask_data ?? [];
        if (is_string($maskData)) {
            $maskData = json_decode($maskData, true) ?? [];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                   => $product->id,
                'name'                 => $product->name,
                'slug'                 => $product->slug,
                'sku'                  => $product->sku,
                'short_description'    => $product->short_description,
                'description'          => $product->description,
                'base_price'           => (float) $product->base_price,
                'compare_price'        => $product->compare_price ? (float) $product->compare_price : null,
                'formatted_price'      => CurrencyService::format($product->base_price),
                'no_of_pages'          => $product->no_of_pages ?? 1,
                'pdf_orientation'      => $product->pdf_orientation ?? 'portrait',
                'min_images'           => $product->min_images ?? 1,
                'max_images'           => $product->max_images ?? 10,
                'allowed_print_types'  => $product->allowed_print_types ?? [],
                'frame_image_url'      => $product->frame_image ? Product::formatStorageUrl($product->frame_image) : null,
                'sample_image_url'     => $product->sample_image ? Product::formatStorageUrl($product->sample_image) : null,
                'background_image_url' => $product->background_image ? Product::formatStorageUrl($product->background_image) : null,
                'overlay_image_url'    => $product->overlay_image ? Product::formatStorageUrl($product->overlay_image) : null,
                'gallery_images'       => $galleryImages,
                'mask_data'            => $maskData,
                'category'             => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                'product_type'         => $product->productType ? ['id' => $product->productType->id, 'name' => $product->productType->name, 'slug' => $product->productType->slug] : null,
                'paper_type'           => $product->paperType ? ['id' => $product->paperType->id, 'name' => $product->paperType->name] : null,
                'store'                => $product->store ? ['id' => $product->store->id, 'name' => $product->store->store_name, 'code' => $product->store->store_code] : null,
            ],
        ]);
    }

    /**
     * Calculate Price based on customization parameters
     */
    public function calculatePrice(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $quantity = (int) $request->input('quantity', 1);
        $basePrice = (float) $product->base_price;
        $unitPrice = $basePrice;

        // Custom size modifier
        if ($request->filled('size_price')) {
            $unitPrice = (float) $request->size_price;
        }

        $subtotal = $unitPrice * $quantity;

        return response()->json([
            'success'           => true,
            'unit_price'        => $unitPrice,
            'quantity'          => $quantity,
            'subtotal'          => $subtotal,
            'formatted_subtotal'=> CurrencyService::format($subtotal),
        ]);
    }
}
