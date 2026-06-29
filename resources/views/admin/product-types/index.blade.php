@extends('layouts.admin')
@section('title', 'Product Types & Sizes')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Product Types & Sizes</h1>
        <p class="text-sm text-surface-500">Manage product categories and their available sizes</p>
    </div>
    <a href="{{ route('admin.product-types.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Type/Size
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($productTypes as $type)
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden group">
        
        <div class="p-5">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-brand-50 text-brand-600 rounded-lg">
                        @if($type->icon_svg)
                            {!! $type->icon_svg !!}
                        @else
                            <i data-lucide="tag" class="w-5 h-5"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-display font-semibold text-surface-900">{{ $type->name }}</h3>
                        @if($type->title)
                            <p class="text-[10px] text-surface-500 italic">{{ $type->title }}</p>
                        @endif
                        @if($type->price)
                            <p class="text-xs font-bold text-brand-600">{{ \App\Services\CurrencyService::format($type->price) }}</p>
                        @else
                            <p class="text-xs text-surface-400">Main Type</p>
                        @endif
                    </div>
                </div>
                <span class="px-2 py-1 {{ $type->is_active ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} text-[10px] font-bold rounded uppercase">
                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="flex items-center gap-2 mt-4 text-xs">
                <a href="{{ route('admin.product-types.edit', $type) }}" class="px-3 py-1.5 bg-surface-100 text-surface-600 font-semibold rounded-lg hover:bg-surface-200 transition">Edit</a>
                <form action="{{ route('admin.product-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Delete this type?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 font-semibold rounded-lg hover:bg-red-100 transition">Delete</button>
                </form>
            </div>

            {{-- Sizes (Subcategories) --}}
            @if($type->children->count() > 0)
            <div class="mt-4 pt-4 border-t border-surface-100">
                <p class="text-xs font-semibold text-surface-400 uppercase tracking-wider mb-3">Available Sizes</p>
                <div class="space-y-2">
                    @foreach($type->children as $size)
                    <div class="flex items-center justify-between p-2 bg-surface-50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 flex items-center justify-center text-surface-400">
                                @if($size->icon_svg)
                                    <div class="w-4 h-4">{!! $size->icon_svg !!}</div>
                                @else
                                    <i data-lucide="maximize" class="w-3.5 h-3.5"></i>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-surface-700">{{ $size->name }}</span>
                                    @if($size->title)
                                        <span class="text-[10px] text-surface-400 font-normal">({{ $size->title }})</span>
                                    @endif
                                </div>
                                @if($size->price)
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-brand-600">{{ \App\Services\CurrencyService::format($size->price) }}</span>
                                    @if($size->old_price)
                                    <span class="text-[10px] text-surface-400 line-through">{{ \App\Services\CurrencyService::format($size->old_price) }}</span>
                                    @endif
                                </div>
                                @endif
                                @if($size->width && $size->height)
                                <p class="text-[10px] text-surface-400 mt-0.5">Dim: {{ $size->width }}x{{ $size->height }} {{ $size->unit }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.product-types.edit', $size) }}" class="p-1 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </a>
                            <form action="{{ route('admin.product-types.destroy', $size) }}" method="POST" onsubmit="return confirm('Delete size?')">
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
    <div class="col-span-full text-center py-12 text-surface-400 bg-white rounded-2xl border border-dashed border-surface-200">
        <i data-lucide="layout-grid" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
        <p>No product types or sizes defined yet.</p>
        <a href="{{ route('admin.product-types.create') }}" class="text-brand-600 font-semibold mt-2 inline-block">Create your first one</a>
    </div>
    @endforelse
</div>
@endsection
