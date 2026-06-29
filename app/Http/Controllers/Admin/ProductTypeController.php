<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    public function index()
    {
        $productTypes = ProductType::with('children')
            ->parents()
            ->orderBy('sort_order')
            ->get();

        return view('admin.product-types.index', compact('productTypes'));
    }

    public function create()
    {
        $parentTypes = ProductType::parents()->orderBy('name')->get();
        return view('admin.product-types.form', compact('parentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:product_types,id',
            'icon_svg' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');

        ProductType::create($data);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product Type/Size created!');
    }

    public function edit(ProductType $productType)
    {
        $parentTypes = ProductType::parents()->where('id', '!=', $productType->id)->orderBy('name')->get();
        return view('admin.product-types.form', compact('productType', 'parentTypes'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:product_types,id',
            'icon_svg' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');

        $productType->update($data);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product Type/Size updated!');
    }

    public function destroy(ProductType $productType)
    {
        // For now, allow deletion even if it has children, as they will be cascaded or we can check
        if ($productType->children()->exists()) {
            return redirect()->route('admin.product-types.index')
                ->with('error', 'Cannot delete type that has sizes. Delete sizes first.');
        }

        $productType->delete();

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product Type/Size deleted.');
    }
}
