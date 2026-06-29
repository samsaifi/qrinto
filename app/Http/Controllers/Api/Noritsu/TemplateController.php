<?php

namespace App\Http\Controllers\Api\Noritsu;

use App\Http\Controllers\Controller;
use App\Models\Product; // Changed from Template
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * List available products as templates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::active()->with('category'); // Eager load category

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Just get all active products for now
        $products = $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category ? $product->category->name : 'General',
                'price' => $product->base_price,
                'image' => $product->frame_image_url ?? $product->sample_image_url ?? 'https://placehold.co/300x400/eee/999?text=No+Image',
                'frame_image' => $product->frame_image_url,
                'mask_data' => $product->mask_data,
            ];
        });

        return response()->json($products);
    }

    /**
     * Get details of a specific product/template.
     */
    public function show($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->base_price,
            'image' => $product->frame_image_url ?? $product->sample_image_url,
            'frame_image' => $product->frame_image_url,
            'mask_data' => $product->mask_data,
            // Add other customization config if needed
        ]);
    }
}
