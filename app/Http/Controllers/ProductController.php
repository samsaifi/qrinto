<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->with('category', 'images');

        // Filter by category
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $categoryIds = [$category->id];
                $childIds = $category->children->pluck('id')->toArray();
                $categoryIds = array_merge($categoryIds, $childIds);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by print type
        if ($request->filled('print_type')) {
            $query->whereJsonContains('allowed_print_types', $request->print_type);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // Sort
        $sortBy = $request->get('sort', 'latest');
        $query = match($sortBy) {
            'price_low' => $query->orderBy('base_price', 'asc'),
            'price_high' => $query->orderBy('base_price', 'desc'),
            'popular' => $query->orderBy('views_count', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::active()->parents()->with('children')->orderBy('sort_order')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->increment('views_count');
        $product->load('category', 'images', 'optionGroups.values');

        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('images')
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function customize(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load('images', 'optionGroups.values');

        return view('products.customize', compact('product'));
    }

    /**
     * API: Calculate price based on selected options.
     */
    public function calculatePrice(Request $request, Product $product)
    {
        $selectedOptions = $request->input('options', []);
        $quantity = max(1, (int) $request->input('quantity', 1));

        $unitPrice = $product->calculatePrice($selectedOptions);
        $totalPrice = $unitPrice * $quantity;

        return response()->json([
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'formatted_unit_price' => '$' . number_format($unitPrice, 2),
            'formatted_total_price' => '$' . number_format($totalPrice, 2),
        ]);
    }
}
