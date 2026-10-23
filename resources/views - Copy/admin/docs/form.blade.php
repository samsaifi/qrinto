@extends('layouts.admin')
@section('title', $guide->exists ? 'Edit Guide: ' . $guide->title : 'Add Documentation Guide')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-surface-900">{{ $guide->exists ? 'Edit Documentation Guide' : 'Add New Documentation Guide' }}</h1>
            <p class="text-xs text-surface-500">Create simple, step-by-step guides for non-technical employees.</p>
        </div>
        <a href="{{ route('admin.docs.manage') }}" class="px-4 py-2 bg-surface-100 hover:bg-surface-200 text-surface-800 text-xs font-semibold rounded-xl border border-surface-200 transition">
            Back to Management
        </a>
    </div>

    <form method="POST" action="{{ $guide->exists ? route('admin.docs.update', $guide->slug) : route('admin.docs.store') }}" class="bg-white rounded-2xl border border-surface-200/70 p-6 sm:p-8 shadow-xs space-y-6">
        @csrf
        @if($guide->exists)
        @method('PUT')
        @endif

        <div class="grid sm:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Guide Title *</label>
                <input type="text" name="title" value="{{ old('title', $guide->title) }}" required placeholder="e.g. How to Process an Order" class="w-full text-sm px-4 py-2.5 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Slug -->
            <div>
                <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">URL Slug (Unique)</label>
                <input type="text" name="slug" value="{{ old('slug', $guide->slug) }}" placeholder="e.g. how-to-process-an-order" class="w-full text-sm px-4 py-2.5 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <span class="text-[11px] text-surface-400 mt-1 block">Leave blank to generate automatically from title.</span>
            </div>

            <!-- Icon -->
            <div>
                <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Lucide Icon Name</label>
                <input type="text" name="icon" value="{{ old('icon', $guide->icon ?: 'book-open') }}" placeholder="e.g. package, box, store, users" class="w-full text-sm px-4 py-2.5 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Category *</label>
                <select name="category" required class="w-full text-sm px-4 py-2.5 rounded-xl border border-surface-200 bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="getting_started" {{ old('category', $guide->category) === 'getting_started' ? 'selected' : '' }}>Getting Started</option>
                    <option value="orders" {{ old('category', $guide->category) === 'orders' ? 'selected' : '' }}>Orders</option>
                    <option value="catalog" {{ old('category', $guide->category) === 'catalog' ? 'selected' : '' }}>Catalog</option>
                    <option value="stores" {{ old('category', $guide->category) === 'stores' ? 'selected' : '' }}>Stores</option>
                    <option value="users" {{ old('category', $guide->category) === 'users' ? 'selected' : '' }}>Users</option>
                </select>
            </div>

            <!-- Subcategory -->
            <div>
                <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Subcategory (Catalog items)</label>
                <select name="subcategory" class="w-full text-sm px-4 py-2.5 rounded-xl border border-surface-200 bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">None / General</option>
                    <option value="products" {{ old('subcategory', $guide->subcategory) === 'products' ? 'selected' : '' }}>Products</option>
                    <option value="categories" {{ old('subcategory', $guide->subcategory) === 'categories' ? 'selected' : '' }}>Categories</option>
                    <option value="card_types" {{ old('subcategory', $guide->subcategory) === 'card_types' ? 'selected' : '' }}>Card Types/Sizes</option>
                    <option value="templates" {{ old('subcategory', $guide->subcategory) === 'templates' ? 'selected' : '' }}>Templates</option>
                    <option value="coupons" {{ old('subcategory', $guide->subcategory) === 'coupons' ? 'selected' : '' }}>Coupons</option>
                    <option value="events" {{ old('subcategory', $guide->subcategory) === 'events' ? 'selected' : '' }}>Events</option>
                    <option value="paper_types" {{ old('subcategory', $guide->subcategory) === 'paper_types' ? 'selected' : '' }}>Paper Types</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div>
                <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Display Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $guide->sort_order ?: 0) }}" class="w-full text-sm px-4 py-2.5 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Visible To Roles -->
            <div>
                <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Visible To User Roles</label>
                <div class="flex flex-wrap items-center gap-4 pt-1">
                    @php $assignedRoles = old('visible_roles', $guide->visible_roles ?: ['admin', 'store_admin', 'staff']); @endphp
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-surface-700 cursor-pointer">
                        <input type="checkbox" name="visible_roles[]" value="admin" {{ in_array('admin', $assignedRoles) ? 'checked' : '' }} class="rounded border-surface-300 text-brand-600 focus:ring-brand-500">
                        Admin
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-surface-700 cursor-pointer">
                        <input type="checkbox" name="visible_roles[]" value="store_admin" {{ in_array('store_admin', $assignedRoles) ? 'checked' : '' }} class="rounded border-surface-300 text-brand-600 focus:ring-brand-500">
                        Store Admin
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-surface-700 cursor-pointer">
                        <input type="checkbox" name="visible_roles[]" value="staff" {{ in_array('staff', $assignedRoles) ? 'checked' : '' }} class="rounded border-surface-300 text-brand-600 focus:ring-brand-500">
                        Store Staff
                    </label>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div>
            <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Short Summary</label>
            <textarea name="summary" rows="2" placeholder="Brief 1-2 sentence description for search results and cards..." class="w-full text-sm p-4 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('summary', $guide->summary) }}</textarea>
        </div>

        <!-- Content -->
        <div>
            <label class="block text-xs font-bold text-surface-700 uppercase tracking-wider mb-2">Guide Content (HTML Supported) *</label>
            <textarea name="content" rows="12" required placeholder="Write clear, step-by-step instructions for non-technical employees using HTML tags like <h3>, <p>, <ol>, <li>..." class="w-full text-sm p-4 rounded-xl border border-surface-200 font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('content', $guide->content) }}</textarea>
        </div>

        <!-- Published Toggle -->
        <div class="flex items-center justify-between p-4 bg-surface-50 rounded-xl border border-surface-200">
            <div>
                <span class="text-sm font-bold text-surface-800 block">Publish Guide</span>
                <span class="text-xs text-surface-500">When published, authorized users can view this guide in the help center.</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $guide->is_published ?? true) ? 'checked' : '' }} class="sr-only peer">
                <div class="w-11 h-6 bg-surface-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-surface-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500"></div>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-100">
            <a href="{{ route('admin.docs.manage') }}" class="px-5 py-2.5 bg-surface-100 hover:bg-surface-200 text-surface-700 text-xs font-semibold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                {{ $guide->exists ? 'Update Guide' : 'Save Guide' }}
            </button>
        </div>
    </form>
</div>
@endsection
