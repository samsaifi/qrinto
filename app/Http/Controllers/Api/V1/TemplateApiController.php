<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class TemplateApiController extends Controller
{
    /**
     * List active preset templates for customizer
     */
    public function index(Request $request)
    {
        $productId = $request->input('product_id');
        $query = Product::where('is_active', true);

        if ($productId) {
            $query->where('id', $productId);
        }

        $products = $query->get();
        $allTemplates = [];

        foreach ($products as $product) {
            $customConfig = $product->customization_config ?? [];
            if (!empty($customConfig['templates'])) {
                foreach ($customConfig['templates'] as $id => $tpl) {
                    $allTemplates[] = array_merge(['id' => $id, 'product_id' => $product->id, 'product_name' => $product->name], $tpl);
                }
            }
        }

        return response()->json([
            'success' => true,
            'count'   => count($allTemplates),
            'data'    => $allTemplates,
        ]);
    }

    /**
     * Show template specification detail
     */
    public function show($id)
    {
        $products = Product::where('is_active', true)->get();
        foreach ($products as $product) {
            $customConfig = $product->customization_config ?? [];
            if (!empty($customConfig['templates'][$id])) {
                return response()->json([
                    'success' => true,
                    'data'    => array_merge(['id' => $id, 'product_id' => $product->id], $customConfig['templates'][$id]),
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Template not found.',
        ], 404);
    }
}
