<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kiosk;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KioskController extends Controller
{
    public function index(Request $request)
    {
        $query = Kiosk::with(['store', 'product']);

        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $query->where('store_id', auth()->user()->store_id);
        } elseif ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('kiosk')) {
            $query->where('kiosk', $request->kiosk === '1');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('file_path', 'like', "%{$search}%")
                  ->orWhere('printer_tray', 'like', "%{$search}%")
                  ->orWhere('print_size', 'like', "%{$search}%")
                  ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        $kiosks = $query->latest()->paginate(15)->withQueryString();
        
        $stores = auth()->user()->isAdmin() 
            ? Store::orderBy('store_name')->get() 
            : collect();
        $products = Product::orderBy('name')->get();

        return view('admin.kiosks.index', compact('kiosks', 'stores', 'products'));
    }

    public function create()
    {
        $stores = auth()->user()->isAdmin()
            ? Store::orderBy('store_name')->get()
            : (auth()->user()->store_id ? Store::where('id', auth()->user()->store_id)->get() : collect());

        $products = Product::orderBy('name')->get();

        return view('admin.kiosks.form', compact('stores', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'nullable|exists:products,id',
            'kiosk' => 'required|boolean',
            'file_path' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpeg,jpg,png,zip|max:20480',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'printer_tray' => 'nullable|string|max:255',
            'print_size' => 'nullable|string|max:255',
        ]);

        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $validated['store_id'] = auth()->user()->store_id;
        }

        if ($request->hasFile('file')) {
            $uploadedPath = $request->file('file')->store('kiosks', 'public');
            $validated['file_path'] = $uploadedPath;
        }

        if (empty($validated['total_amount']) && isset($validated['quantity'], $validated['price'])) {
            $validated['total_amount'] = $validated['quantity'] * $validated['price'];
        }

        unset($validated['file']);

        Kiosk::create($validated);

        return redirect()->route('admin.kiosks.index')
            ->with('success', 'Kiosk record created successfully.');
    }

    public function show(Kiosk $kiosk)
    {
        $kiosk->load(['store', 'product']);

        return view('admin.kiosks.show', compact('kiosk'));
    }

    public function edit(Kiosk $kiosk)
    {
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id && $kiosk->store_id !== auth()->user()->store_id) {
            abort(403, 'Unauthorized action.');
        }

        $stores = auth()->user()->isAdmin()
            ? Store::orderBy('store_name')->get()
            : Store::where('id', auth()->user()->store_id)->get();

        $products = Product::orderBy('name')->get();

        return view('admin.kiosks.form', compact('kiosk', 'stores', 'products'));
    }

    public function update(Request $request, Kiosk $kiosk)
    {
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id && $kiosk->store_id !== auth()->user()->store_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'nullable|exists:products,id',
            'kiosk' => 'required|boolean',
            'file_path' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpeg,jpg,png,zip|max:20480',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'printer_tray' => 'nullable|string|max:255',
            'print_size' => 'nullable|string|max:255',
        ]);

        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $validated['store_id'] = auth()->user()->store_id;
        }

        if ($request->hasFile('file')) {
            if ($kiosk->file_path && Storage::disk('public')->exists($kiosk->file_path)) {
                Storage::disk('public')->delete($kiosk->file_path);
            }
            $uploadedPath = $request->file('file')->store('kiosks', 'public');
            $validated['file_path'] = $uploadedPath;
        }

        if (empty($validated['total_amount']) && isset($validated['quantity'], $validated['price'])) {
            $validated['total_amount'] = $validated['quantity'] * $validated['price'];
        }

        unset($validated['file']);

        $kiosk->update($validated);

        return redirect()->route('admin.kiosks.index')
            ->with('success', 'Kiosk record updated successfully.');
    }

    public function destroy(Kiosk $kiosk)
    {
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id && $kiosk->store_id !== auth()->user()->store_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($kiosk->file_path && Storage::disk('public')->exists($kiosk->file_path)) {
            Storage::disk('public')->delete($kiosk->file_path);
        }

        $kiosk->delete();

        return redirect()->route('admin.kiosks.index')
            ->with('success', 'Kiosk record deleted successfully.');
    }
}
