@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Categories</h1>
        <p class="text-sm text-surface-500">Organize your product catalog</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Category
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($categories as $category)
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden group">

        @if($category->image)
        <div class="aspect-[16/9] bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center">
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
        </div>
        @endif

        <div class="p-5">
            <h3 class="font-display font-semibold text-surface-900">{{ $category->name }}</h3>
            <p class="text-sm text-surface-500 mt-1">{{ $category->products->count() }} products</p>
            @if($category->parent)
            <p class="text-xs text-surface-400 mt-1">Parent: {{ $category->parent->name }}</p>
            @endif
            <div class="flex items-center gap-2 mt-4">
                <a href="{{ route('admin.categories.edit', $category) }}" class="px-3 py-1.5 bg-surface-100 text-surface-600 text-xs font-semibold rounded-lg hover:bg-surface-200 transition">Edit</a>
                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-100 transition">Delete</button>
                </form>
            </div>

            {{-- Child Categories --}}
            @if($category->children->count() > 0)
            <div class="mt-4 pt-4 border-t border-surface-100">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Subcategories</p>
                <div class="space-y-2">
                    @foreach($category->children as $child)
                    <div class="flex items-center justify-between p-2 bg-surface-50 rounded-lg">
                        <div>
                            <span class="text-sm font-medium text-surface-700">{{ $child->name }}</span>
                            <span class="text-xs text-surface-400 ml-1">({{ $child->products->count() }} products)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.categories.edit', $child) }}" class="p-1 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $child) }}" method="POST" onsubmit="return confirm('Delete subcategory?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1 text-surface-400 hover:text-red-600 transition" title="Delete">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-surface-400">No categories yet.</div>
    @endforelse
</div>
@endsection