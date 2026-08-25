<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    private function isStoreAdmin(): bool
    {
        return !Auth::user()->isAdmin();
    }

    private function categoryQuery()
    {
        $query = Category::query();
        if ($this->isStoreAdmin()) {
            $query->where('user_id', Auth::id());
        }
        return $query;
    }

    private function authorizeCategory(Category $category): void
    {
        if ($this->isStoreAdmin() && $category->user_id !== Auth::id()) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $query = $this->categoryQuery()->with('parent', 'products', 'user');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('sort_order')->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = $this->categoryQuery()->parents()->orderBy('name')->get();
        return view('admin.categories.form', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->boolean('is_active');

        if ($this->isStoreAdmin()) {
            $data['user_id'] = Auth::id();
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        $route = request()->is('store*') ? 'storepanel_cat.categories.index' : 'admin.categories.index';
        return redirect()->route($route)
            ->with('success', 'Category created!');
    }

    public function edit(Category $category)
    {
        $this->authorizeCategory($category);

        $parentCategories = $this->categoryQuery()
            ->parents()
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.form', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($category);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        $route = request()->is('store*') ? 'storepanel_cat.categories.index' : 'admin.categories.index';
        return redirect()->route($route)
            ->with('success', 'Category updated!');
    }

    public function destroy(Category $category)
    {
        $this->authorizeCategory($category);

        $route = request()->is('store*') ? 'storepanel_cat.categories.index' : 'admin.categories.index';
        if ($category->products()->exists()) {
            return redirect()->route($route)
                ->with('error', 'Cannot delete category with products.');
        }

        if ($category->image) Storage::disk('public')->delete($category->image);
        $category->delete();

        return redirect()->route($route)
            ->with('success', 'Category deleted.');
    }
}
