@extends('layouts.admin')
@section('title', isset($size) ? 'Edit Size: ' . $size->label : 'Add Custom Print Size')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('admin.custom-print-sizes.index') }}" class="p-2 rounded-xl hover:bg-surface-100 text-surface-400 hover:text-surface-600 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h1 class="font-display font-bold text-2xl text-surface-900">
            {{ isset($size) ? 'Edit Size: ' . $size->label : 'Add Custom Print Size' }}
        </h1>
    </div>
    <p class="text-sm text-surface-500 ml-12">{{ isset($size) ? 'Update the size details and pricing' : 'Add a new size option for custom prints' }}</p>
</div>

<div class="max-w-2xl">
    <form action="{{ isset($size) ? route('admin.custom-print-sizes.update', $size) : route('admin.custom-print-sizes.store') }}"
          method="POST"
          class="bg-white rounded-2xl border border-surface-100 shadow-card p-8 space-y-6">
        @csrf
        @if(isset($size))
            @method('PUT')
        @endif

        <!-- Key & Label Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="key" class="block text-sm font-semibold text-surface-700 mb-2">Size Key <span class="text-red-500">*</span></label>
                <input type="text" id="key" name="key"
                       value="{{ old('key', $size->key ?? '') }}"
                       placeholder="e.g. S, M, L, XL, XXL"
                       class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 font-mono uppercase"
                       required maxlength="10">
                <p class="text-xs text-surface-400 mt-1">Unique identifier (auto-uppercased)</p>
                @error('key')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="label" class="block text-sm font-semibold text-surface-700 mb-2">Display Label <span class="text-red-500">*</span></label>
                <input type="text" id="label" name="label"
                       value="{{ old('label', $size->label ?? '') }}"
                       placeholder="e.g. Small, Medium, Large"
                       class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                       required>
                @error('label')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Dimensions & Price Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="dimensions" class="block text-sm font-semibold text-surface-700 mb-2">Dimensions <span class="text-red-500">*</span></label>
                <input type="text" id="dimensions" name="dimensions"
                       value="{{ old('dimensions', $size->dimensions ?? '') }}"
                       placeholder='e.g. 8" × 10"'
                       class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                       required>
                <p class="text-xs text-surface-400 mt-1">Displayed to the customer</p>
                @error('dimensions')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-semibold text-surface-700 mb-2">Price ($) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-surface-400 font-semibold">$</span>
                    <input type="number" id="price" name="price"
                           value="{{ old('price', $size->price ?? '') }}"
                           placeholder="0.00"
                           step="0.01" min="0.01"
                           class="w-full pl-8 rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500"
                           required>
                </div>
                @error('price')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Sort Order -->
        <div class="max-w-xs">
            <label for="sort_order" class="block text-sm font-semibold text-surface-700 mb-2">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order"
                   value="{{ old('sort_order', $size->sort_order ?? 0) }}"
                   min="0"
                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
            <p class="text-xs text-surface-400 mt-1">Lower numbers appear first</p>
        </div>

        <!-- Toggles -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
            <label class="flex items-center gap-3 p-4 bg-surface-50 rounded-xl cursor-pointer hover:bg-surface-100 transition">
                <input type="hidden" name="is_popular" value="0">
                <input type="checkbox" name="is_popular" value="1"
                       {{ old('is_popular', $size->is_popular ?? false) ? 'checked' : '' }}
                       class="rounded text-brand-600 focus:ring-brand-500 w-5 h-5">
                <div>
                    <span class="text-sm font-semibold text-surface-800">Mark as Popular</span>
                    <p class="text-xs text-surface-400">Shows a "Popular" badge on this size</p>
                </div>
            </label>

            <label class="flex items-center gap-3 p-4 bg-surface-50 rounded-xl cursor-pointer hover:bg-surface-100 transition">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $size->is_active ?? true) ? 'checked' : '' }}
                       class="rounded text-accent-600 focus:ring-accent-500 w-5 h-5">
                <div>
                    <span class="text-sm font-semibold text-surface-800">Active</span>
                    <p class="text-xs text-surface-400">Only active sizes are shown to customers</p>
                </div>
            </label>
        </div>

        <!-- Preview Card -->
        <div class="pt-4 border-t border-surface-100">
            <h3 class="text-sm font-semibold text-surface-500 uppercase tracking-wider mb-3">Preview</h3>
            <div class="inline-block border-2 border-surface-200 rounded-2xl p-6 text-center min-w-[140px]"
                 x-data="{ key: '{{ old('key', $size->key ?? '') }}', label: '{{ old('label', $size->label ?? '') }}', dims: '{{ old('dimensions', $size->dimensions ?? '') }}', price: '{{ old('price', $size->price ?? '') }}' }"
                 x-init="
                    document.getElementById('key').addEventListener('input', (e) => key = e.target.value.toUpperCase());
                    document.getElementById('label').addEventListener('input', (e) => label = e.target.value);
                    document.getElementById('dimensions').addEventListener('input', (e) => dims = e.target.value);
                    document.getElementById('price').addEventListener('input', (e) => price = e.target.value);
                 ">
                <div class="w-14 h-14 rounded-xl bg-surface-100 text-surface-400 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="maximize-2" class="w-7 h-7"></i>
                </div>
                <p class="font-display font-bold text-xl text-surface-900" x-text="label || 'Label'"></p>
                <p class="text-xs text-surface-400 mb-2" x-text="dims || 'Dimensions'"></p>
                <p class="font-bold text-2xl text-surface-900">$<span x-text="price ? parseFloat(price).toFixed(2) : '0.00'"></span></p>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-between pt-4 border-t border-surface-100">
            <a href="{{ route('admin.custom-print-sizes.index') }}" class="px-6 py-2.5 text-surface-500 hover:text-surface-700 font-medium transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                <i data-lucide="save" class="w-4 h-4"></i>
                {{ isset($size) ? 'Update Size' : 'Add Size' }}
            </button>
        </div>
    </form>
</div>
@endsection
