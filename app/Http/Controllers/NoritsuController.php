<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class NoritsuController extends Controller
{
    /**
     * Display a listing of the Noritsu available products.
     */
    public function index()
    {
        $products = Product::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($product) {
                $product->display_image = $product->sample_image_url ?? $product->frame_image_url ?? 'https://placehold.co/300x400/eee/999?text=No+Image';
                return $product;
            });

        return view('noritsu.index', compact('products'));
    }

    /**
     * Show the editor for the given product.
     */
    public function editor(Product $product)
    {
        if (!$product->is_active) {
            abort(404, 'Product not found or not available for Noritsu printing.');
        }

        return view('noritsu.editor', compact('product'));
    }
}
