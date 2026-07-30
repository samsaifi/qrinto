<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaperType;
use App\Models\Store;
use Illuminate\Http\Request;

class PaperTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = PaperType::with('stores', 'user');

        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $query->whereHas('stores', fn($q) => $q->where('stores.id', auth()->user()->store_id));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $paperTypes = $query->latest()->paginate(15)->withQueryString();

        return view('admin.paper-types.index', compact('paperTypes'));
    }

    public function create()
    {
        $stores = auth()->user()->isAdmin()
            ? Store::orderBy('store_name')->get()
            : collect();

        return view('admin.paper-types.form', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:stores,id',
        ]);

        $paperType = PaperType::create([
            'title' => $request->title,
            'is_active' => $request->boolean('is_active'),
            'user_id' => auth()->id(),
        ]);

        $this->syncStores($paperType, $request);

        return redirect()->route('admin.paper-types.index')
            ->with('success', 'Paper Type created!');
    }

    public function edit(PaperType $paperType)
    {
        $paperType->load('stores');
        $stores = auth()->user()->isAdmin()
            ? Store::orderBy('store_name')->get()
            : collect();

        return view('admin.paper-types.form', compact('paperType', 'stores'));
    }

    public function update(Request $request, PaperType $paperType)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:stores,id',
        ]);

        $paperType->update([
            'title' => $request->title,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncStores($paperType, $request);

        return redirect()->route('admin.paper-types.index')
            ->with('success', 'Paper Type updated!');
    }

    public function destroy(PaperType $paperType)
    {
        $paperType->delete();

        return redirect()->route('admin.paper-types.index')
            ->with('success', 'Paper Type deleted.');
    }

    private function syncStores(PaperType $paperType, Request $request): void
    {
        if (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
            $paperType->stores()->sync([auth()->user()->store_id]);
        } else {
            $paperType->stores()->sync($request->input('stores', []));
        }
    }
}
