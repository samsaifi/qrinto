@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', isset($productType) ? 'Edit Product Type/Size' : 'Create Product Type/Size')

@section('content')
@php
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
@endphp
<div class="max-w-2xl">
    <div class="mb-8">
        <a href="{{ route($rPrefix . 'product-types.index') }}" class="inline-flex items-center gap-2 text-sm text-surface-500 hover:text-brand-600 transition mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to list
        </a>
        <h1 class="font-display font-bold text-2xl text-surface-900">{{ isset($productType) ? 'Edit Product Type/Size' : 'Create Product Type/Size' }}</h1>
    </div>

    <form action="{{ isset($productType) ? route($rPrefix . 'product-types.update', $productType) : route($rPrefix . 'product-types.store') }}"
          method="POST" class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
        @csrf
        @if(isset($productType)) @method('PUT') @endif

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $productType->name ?? '') }}" required
                       placeholder="e.g. Photo Prints or 4x6 inch"
                       class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $productType->title ?? '') }}"
                       placeholder="e.g. Premium High Glossy Photo Prints"
                       class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Parent Type</label>
                <select name="parent_id" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">None (Top Level Type)</option>
                    @foreach($parentTypes as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id', $productType->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-surface-400 mt-1">If this is a size, select its product type here.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Icon SVG Code</label>
                <textarea name="icon_svg" rows="5" placeholder='<svg ...> ... </svg>'
                          class="w-full rounded-xl border-surface-200 font-mono text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('icon_svg', $productType->icon_svg ?? '') }}</textarea>
                <p class="text-xs text-surface-400 mt-1">Paste raw SVG code here. It will be used as the icon.</p>
                @if(isset($productType) && $productType->icon_svg)
                <div class="mt-2 p-3 bg-surface-50 rounded-lg inline-block">
                    <p class="text-[10px] text-surface-400 uppercase font-bold mb-2">Preview:</p>
                    <div class="w-8 h-8 text-brand-600">
                        {!! $productType->icon_svg !!}
                    </div>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Price</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-surface-400">$</span>
                        <input type="number" name="price" step="0.01" value="{{ old('price', $productType->price ?? '') }}"
                               class="w-full pl-8 rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Old Price (Optional)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-surface-400">$</span>
                        <input type="number" name="old_price" step="0.01" value="{{ old('old_price', $productType->old_price ?? '') }}"
                               class="w-full pl-8 rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Width</label>
                    <input type="number" name="width" step="0.01" value="{{ old('width', $productType->width ?? '') }}"
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Height</label>
                    <input type="number" name="height" step="0.01" value="{{ old('height', $productType->height ?? '') }}"
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Unit</label>
                    <select name="unit" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="mm" {{ (old('unit', $productType->unit ?? 'mm') == 'mm') ? 'selected' : '' }}>mm</option>
                        <option value="cm" {{ (old('unit', $productType->unit ?? 'mm') == 'cm') ? 'selected' : '' }}>cm</option>
                        <option value="inch" {{ (old('unit', $productType->unit ?? 'mm') == 'inch') ? 'selected' : '' }}>inch</option>
                        <option value="px" {{ (old('unit', $productType->unit ?? 'mm') == 'px') ? 'selected' : '' }}>px</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $productType->sort_order ?? 0) }}"
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $productType->is_active ?? true) ? 'checked' : '' }}
                               class="rounded text-brand-600 focus:ring-brand-500"> Active
                    </label>
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-8">
            <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                {{ isset($productType) ? 'Update' : 'Create' }} Type/Size
            </button>
            <a href="{{ route($rPrefix . 'product-types.index') }}" class="px-6 py-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
