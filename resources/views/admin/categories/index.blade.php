@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', 'Categories')

@section('content')
@php
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Categories</h1>
        <p class="text-sm text-surface-500">Organize your product catalog</p>
    </div>
    <a href="{{ route($rPrefix . 'categories.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Category
    </a>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-6">
    <form action="{{ route($rPrefix . 'categories.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-surface-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Category name..."
                   class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-surface-500 mb-1">Status</label>
            <select name="status" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Filter</button>
        <a href="{{ route($rPrefix . 'categories.index') }}" class="text-sm text-surface-500 hover:text-brand-600">Clear</a>
    </form>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
            <thead>
                <tr class="bg-surface-50 border-b border-surface-100">
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-16">Image</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Name</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-20">Status</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">Parent</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider hidden sm:table-cell w-24">Products</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">Created By</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden sm:table-cell w-32">Created</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-28">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50">
                @forelse($categories as $category)
                <tr class="hover:bg-surface-50/60 transition-colors">
                    <td class="px-3 py-3 text-center">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-8 h-8 rounded-lg object-cover mx-auto">
                        @else
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-100 text-surface-400">
                                <i data-lucide="image" class="w-4 h-4"></i>
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-semibold text-sm text-surface-800">{{ $category->name }}</span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md {{ $category->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        @if($category->parent)
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md bg-brand-50 text-brand-700 border border-brand-200/60">{{ $category->parent->name }}</span>
                        @else
                            <span class="text-xs text-surface-400">&mdash;</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-center hidden sm:table-cell">
                        <span class="text-sm text-surface-600">{{ $category->products->count() }}</span>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <span class="text-sm text-surface-600">{{ $category->user->name ?? '—' }}</span>
                    </td>
                    <td class="px-3 py-3 hidden sm:table-cell">
                        <span class="text-sm text-surface-500">{{ $category->created_at->format('M d, Y') }}</span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route($rPrefix . 'categories.edit', $category) }}" class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route($rPrefix . 'categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex p-1.5 rounded-lg hover:bg-red-50 text-surface-400 hover:text-red-600 transition" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-surface-400">No categories yet. <a href="{{ route($rPrefix . 'categories.create') }}" class="text-brand-600 font-medium">Add your first category</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="px-6 py-4 border-t border-surface-100">{{ $categories->links() }}</div>
    @endif
</div>
@endsection
