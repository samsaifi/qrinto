<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrintoSize;
use Illuminate\Http\Request;

class QrintoSizeController extends Controller
{
    public function index()
    {
        $sizes = QrintoSize::orderBy('sort_order')->get();
        return view('admin.qrinto-sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('admin.qrinto-sizes.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key'        => 'required|string|max:10|unique:qrinto_sizes,key',
            'label'      => 'required|string|max:255',
            'dimensions' => 'required|string|max:255',
            'price'      => 'required|numeric|min:0.01',
            'is_popular' => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        QrintoSize::create([
            'key'        => strtoupper($request->key),
            'label'      => $request->label,
            'dimensions' => $request->dimensions,
            'price'      => $request->price,
            'is_popular' => $request->boolean('is_popular'),
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.qrinto-sizes.index')
            ->with('success', 'Qrinto size added!');
    }

    public function edit(QrintoSize $qrinto_size)
    {
        return view('admin.qrinto-sizes.form', ['size' => $qrinto_size]);
    }

    public function update(Request $request, QrintoSize $qrinto_size)
    {
        $request->validate([
            'key'        => 'required|string|max:10|unique:qrinto_sizes,key,' . $qrinto_size->id,
            'label'      => 'required|string|max:255',
            'dimensions' => 'required|string|max:255',
            'price'      => 'required|numeric|min:0.01',
            'is_popular' => 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $qrinto_size->update([
            'key'        => strtoupper($request->key),
            'label'      => $request->label,
            'dimensions' => $request->dimensions,
            'price'      => $request->price,
            'is_popular' => $request->boolean('is_popular'),
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('admin.qrinto-sizes.index')
            ->with('success', 'Qrinto size updated!');
    }

    public function destroy(QrintoSize $qrinto_size)
    {
        $qrinto_size->delete();

        return redirect()->route('admin.qrinto-sizes.index')
            ->with('success', 'Qrinto size deleted.');
    }
}
