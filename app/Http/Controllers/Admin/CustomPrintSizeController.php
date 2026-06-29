<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPrintSize;
use Illuminate\Http\Request;

class CustomPrintSizeController extends Controller
{
    public function index()
    {
        $sizes = CustomPrintSize::orderBy('sort_order')->get();
        return view('admin.custom-print-sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('admin.custom-print-sizes.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key'        => 'required|string|max:10|unique:custom_print_sizes,key',
            'label'      => 'required|string|max:255',
            'dimensions' => 'required|string|max:255',
            'price'      => 'required|numeric|min:0.01',
            'is_popular' => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        CustomPrintSize::create([
            'key'        => strtoupper($request->key),
            'label'      => $request->label,
            'dimensions' => $request->dimensions,
            'price'      => $request->price,
            'is_popular' => $request->boolean('is_popular'),
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.custom-print-sizes.index')
            ->with('success', 'Custom print size added!');
    }

    public function edit(CustomPrintSize $custom_print_size)
    {
        return view('admin.custom-print-sizes.form', ['size' => $custom_print_size]);
    }

    public function update(Request $request, CustomPrintSize $custom_print_size)
    {
        $request->validate([
            'key'        => 'required|string|max:10|unique:custom_print_sizes,key,' . $custom_print_size->id,
            'label'      => 'required|string|max:255',
            'dimensions' => 'required|string|max:255',
            'price'      => 'required|numeric|min:0.01',
            'is_popular' => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $custom_print_size->update([
            'key'        => strtoupper($request->key),
            'label'      => $request->label,
            'dimensions' => $request->dimensions,
            'price'      => $request->price,
            'is_popular' => $request->boolean('is_popular'),
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.custom-print-sizes.index')
            ->with('success', 'Custom print size updated!');
    }

    public function destroy(CustomPrintSize $custom_print_size)
    {
        $custom_print_size->delete();

        return redirect()->route('admin.custom-print-sizes.index')
            ->with('success', 'Custom print size deleted.');
    }
}
