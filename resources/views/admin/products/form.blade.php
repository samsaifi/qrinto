@extends('layouts.admin')
@section('title', isset($product) ? 'Edit Product' : 'Create Product')

@php
    $isNotAdmin = !auth()->user()->isAdmin();
    $isAdmin = auth()->user()->isAdmin();
@endphp
@section('content')
    <div class="mb-8">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.index') }}"
                class="p-2 rounded-xl hover:bg-surface-100 text-surface-500 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>

            <div>
                <h1 class="font-display font-bold text-2xl text-surface-900">
                    {{ isset($product) ? 'Edit Product' : 'Create Product' }}</h1>
                <p class="text-sm text-surface-500">
                    {{ isset($product) ? 'View and manage product configuration' : 'Add a new product' }}</p>
            </div>
        </div>
    </div>


    <form action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}"
        method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if (isset($product))
            @method('PUT')
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Basic Information</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Product Name *</label>
                            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            @error('name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Short Description</label>
                            <textarea name="short_description" rows="2"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Full Description</label>
                            <textarea name="description" rows="6"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $product->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Pricing</h2>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Base Price ($) *</label>
                            <input type="number" name="base_price" step="0.01"
                                value="{{ old('base_price', $product->base_price ?? '') }}" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Compare Price ($)</label>
                            <input type="number" name="compare_price" step="0.01"
                                value="{{ old('compare_price', $product->compare_price ?? '') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Product Images</h2>
                    <div>
                        <ul class="grid grid-cols-1   list-disc list-inside gap-x-6 gap-y-1">
                            <li class="text-[10px] text-red-500 font-medium">
                                The first image is required.
                            </li>

                            <li class="text-[10px] text-red-500 font-medium">
                                If you upload only the first image, leave the remaining three image fields empty.
                            </li>

                            <li class="text-[10px] text-red-500 font-medium">
                                The system will automatically fill the last three canvas images based on the
                                first image.
                            </li>

                            <li class="text-[10px] text-red-500 font-medium">
                                To use your own designs, upload all four images.
                            </li>

                            <li class="text-[10px] text-red-500 font-medium">
                                Landscape images must be <strong>1400 × 1000 pixels</strong> (Width × Height).
                            </li>

                            <li class="text-[10px] text-red-500 font-medium">
                                Portrait images must be <strong>1000 × 1400 pixels</strong> (Width × Height).
                            </li>
                        </ul>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <!-- Frame Image -->
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Frame Image <span
                                    class="text-brand-500">*</span></label>
                            <p class="text-xs text-surface-400 mb-3">Upload the empty frame only (no photo inside)</p>
                            <div class="relative group" id="frameDropZone">
                                <div class="border-2 border-dashed border-surface-200 rounded-xl p-4 text-center hover:border-brand-400 transition cursor-pointer {{ isset($product) && $product->frame_image ? 'border-brand-300 bg-brand-50/30' : '' }}"
                                    onclick="document.getElementById('frame_image_input').click()">
                                    @if (isset($product) && $product->frame_image)
                                        <div class="mb-3">
                                            <img src="{{ $product->frame_image_url }}" alt="Frame"
                                                class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                        </div>
                                        <p class="text-xs text-surface-500">Click or drag to replace</p>
                                    @else
                                        <div class="py-4">
                                            <div
                                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-surface-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-surface-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-surface-600">Click to upload frame</p>
                                            <p class="text-xs text-surface-400 mt-1">PNG, JPG up to 5MB</p>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" name="frame_image" id="frame_image_input" accept="image/*"
                                    class="hidden" onchange="previewImage(this, 'framePreview')">
                                <div id="framePreview" class="hidden mt-3">
                                    <img src="" alt="Frame preview"
                                        class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                    <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New frame selected</p>
                                </div>
                            </div>
                            @error('frame_image')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sample Image -->
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Sample Image</label>
                            <p class="text-xs text-surface-400 mb-3">Upload frame with a sample photo inside (preview)</p>
                            <div class="relative group" id="sampleDropZone">
                                <div class="border-2 border-dashed border-surface-200 rounded-xl p-4 text-center hover:border-brand-400 transition cursor-pointer {{ isset($product) && $product->sample_image ? 'border-brand-300 bg-brand-50/30' : '' }}"
                                    onclick="document.getElementById('sample_image_input').click()">
                                    @if (isset($product) && $product->sample_image)
                                        <div class="mb-3">
                                            <img src="{{ $product->sample_image_url }}" alt="Sample"
                                                class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                        </div>
                                        <p class="text-xs text-surface-500">Click or drag to replace</p>
                                    @else
                                        <div class="py-4">
                                            <div
                                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-surface-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-surface-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-surface-600">Click to upload sample</p>
                                            <p class="text-xs text-surface-400 mt-1">Frame + photo preview</p>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" name="sample_image" id="sample_image_input" accept="image/*"
                                    class="hidden" onchange="previewImage(this, 'samplePreview')">
                                <div id="samplePreview" class="hidden mt-3">
                                    <img src="" alt="Sample preview"
                                        class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                    <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New sample selected
                                    </p>
                                </div>
                            </div>
                            @error('sample_image')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Background Image -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-surface-700 mb-1">Background Image</label>
                            <p class="text-xs text-surface-400 mb-3">Upload a background for the canvas (e.g. bg.jpg)</p>
                            <div class="relative group" id="backgroundDropZone">
                                <div class="border-2 border-dashed border-surface-200 rounded-xl p-4 text-center hover:border-brand-400 transition cursor-pointer {{ isset($product) && $product->background_image ? 'border-brand-300 bg-brand-50/30' : '' }}"
                                    onclick="document.getElementById('background_image_input').click()">
                                    @if (isset($product) && $product->background_image)
                                        <div class="mb-3">
                                            <img src="{{ $product->background_image_url }}" alt="Background"
                                                class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                        </div>
                                        <p class="text-xs text-surface-500">Click or drag to replace</p>
                                    @else
                                        <div class="py-4">
                                            <div
                                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-surface-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-surface-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-surface-600">Click to upload background</p>
                                            <p class="text-xs text-surface-400 mt-1">PNG, JPG up to 5MB</p>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" name="background_image" id="background_image_input"
                                    accept="image/*" class="hidden" onchange="previewImage(this, 'backgroundPreview')">
                                <div id="backgroundPreview" class="hidden mt-3">
                                    <img src="" alt="Background preview"
                                        class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                    <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New background
                                        selected</p>
                                </div>
                            </div>
                            @error('background_image')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Overlay Image -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-surface-700 mb-1">Overlay Image</label>
                            <p class="text-xs text-surface-400 mb-3">Upload an optional overlay layer</p>
                            <div class="relative group" id="overlayDropZone">
                                <div class="border-2 border-dashed border-surface-200 rounded-xl p-4 text-center hover:border-brand-400 transition cursor-pointer {{ isset($product) && $product->overlay_image ? 'border-brand-300 bg-brand-50/30' : '' }}"
                                    onclick="document.getElementById('overlay_image_input').click()">
                                    @if (isset($product) && $product->overlay_image)
                                        <div class="mb-3">
                                            <img src="{{ $product->overlay_image_url }}" alt="Overlay"
                                                class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                        </div>
                                        <p class="text-xs text-surface-500">Click or drag to replace</p>
                                    @else
                                        <div class="py-4">
                                            <div
                                                class="w-12 h-12 mx-auto mb-2 rounded-xl bg-surface-100 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-surface-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-surface-600">Click to upload overlay</p>
                                            <p class="text-xs text-surface-400 mt-1">PNG (transparent), JPG</p>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" name="overlay_image" id="overlay_image_input" accept="image/*"
                                    class="hidden" onchange="previewImage(this, 'overlayPreview')">
                                <div id="overlayPreview" class="hidden mt-3">
                                    <img src="" alt="Overlay preview"
                                        class="mx-auto max-h-40 rounded-lg object-contain shadow-sm">
                                    <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New overlay selected
                                    </p>
                                </div>
                            </div>
                            @error('overlay_image')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Customization Settings -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Customization Settings</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Min Photos Required</label>
                            <input type="number" name="min_images" min="0"
                                value="{{ old('min_images', $product->min_images ?? 1) }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Max Photos Allowed</label>
                            <input type="number" name="max_images" min="1"
                                value="{{ old('max_images', $product->max_images ?? 10) }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">SEO</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Meta Title</label>
                            <input type="text" name="meta_title"
                                value="{{ old('meta_title', $product->meta_title ?? '') }}"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Meta Description</label>
                            <textarea name="meta_description" rows="2"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Publish</h2>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                                class="rounded text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-surface-700">Active (visible to customers)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1"
                                {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}
                                class="rounded text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-surface-700">Featured Product</span>
                        </label>
                    </div>
                    <button type="submit"
                        class="w-full mt-6 px-5 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                        {{ isset($product) ? 'Update Product' : 'Create Product' }}
                    </button>
                </div>

                <!-- Category -->
                <!-- Category -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Number of Pages</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Number of
                                Pages <span class="text-red-500">*</span></label>
                            <select name="no_of_pages" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select a category...</option>
                                @foreach ($no_of_pages_array as $key => $val)
                                    @if ($isNotAdmin && $key == 1)
                                        @continue
                                    @else
                                        <option value="{{ $key }}"
                                            {{ old('no_of_pages', $product->no_of_pages ?? '') == $key ? 'selected' : '' }}>
                                            {{ $val }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('no_of_pages')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Store Product Visibility </h2>
                    <div class="space-y-4">
                        <div>

                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Store
                                Selection <span class="text-red-500">*</span></label>
                            <select name="store_id"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">

                                <option value=""
                                    {{ old('store_id', $product->store_id ?? '') === '' ? 'selected' : '' }}>
                                    All Store
                                </option>
                                @if ($isNotAdmin)
                                    <option value="{{ auth()->user()->store->id ?? '' }}"
                                        {{ old('store_id', $product->store_id ?? '') == (auth()->user()->store->id ?? '') ? 'selected' : '' }}>
                                        My Store only
                                    </option>
                                @endif
                                @if (!$isNotAdmin && isset($product) && $product->product_store)
                                    @php
                                        $store = \App\Models\Store::find($product->product_store);
                                    @endphp
                                    <option value="{{ $product->product_store ?? '' }}"
                                        {{ old('store_id', $product->product_store ?? '') == ($product->store_id ?? '') ? 'selected' : '' }}>
                                        {{ $store->store_name ?? '' }} Only
                                    </option>
                                @endif
                            </select>
                            @error('store_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Category</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Category
                                Selection <span class="text-red-500">*</span></label>
                            <select name="category_id" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select a category...</option>
                                @foreach ($categories->whereNull('parent_id') as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}
                                        class="font-bold">
                                        {{ $cat->name }}
                                    </option>
                                    @foreach ($categories->where('parent_id', $cat->id) as $sub)
                                        <option value="{{ $sub->id }}"
                                            {{ old('category_id', $product->category_id ?? '') == $sub->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;&nbsp;— {{ $sub->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- Product Type (Size) -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Product Size</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Size
                                Selection</label>
                            <select name="product_type_id"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select a size...</option>
                                @foreach ($productTypes->whereNull('parent_id') as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('product_type_id', $product->product_type_id ?? '') == $type->id ? 'selected' : '' }}
                                        class="font-bold">
                                        {{ $type->name }}
                                    </option>
                                    @foreach ($productTypes->where('parent_id', $type->id) as $sub)
                                        <option value="{{ $sub->id }}"
                                            {{ old('product_type_id', $product->product_type_id ?? '') == $sub->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;&nbsp;— {{ $sub->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('product_type_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6" x-data="tagInput(@js(old('tags', $product->tags ?? [])), @js($allTags ?? []))">
                    <h2 class="font-display font-semibold text-lg mb-5">Tags</h2>
                    <div>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <template x-for="(tag, index) in tags" :key="index">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-brand-100 text-brand-800 rounded-lg text-sm font-medium shadow-sm">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeTag(index)"
                                        class="text-brand-500 hover:text-brand-700 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <input type="text" list="existingTags"
                            @keydown.enter.prevent="addTag($event.target.value); $event.target.value = ''"
                            placeholder="Type a tag and press Enter"
                            class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <p class="text-xs text-surface-400 mt-2">Used for filtering products on the category page.</p>

                        <datalist id="existingTags">
                            <template x-for="sugg in availableSuggestions" :key="sugg">
                                <option :value="sugg"></option>
                            </template>
                        </datalist>

                        <!-- <div x-show="availableSuggestions.length > 0" class="mt-4" x-cloak>
                                                                                                                                                                                                                                                                                                                                                                                                    <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2">Suggestions</p>
                                                                                                                                                                                                                                                                                                                                                                                                    <div class="flex flex-wrap gap-2">
                                                                                                                                                                                                                                                                                                                                                                                                        <template x-for="sugg in availableSuggestions" :key="sugg">
                                                                                                                                                                                                                                                                                                                                                                                                            <button type="button" @click="addTag(sugg)" class="px-2.5 py-1 bg-surface-50 text-surface-600 rounded-lg text-xs font-medium border border-surface-200 hover:border-brand-300 hover:text-brand-600 hover:bg-brand-50 transition shadow-sm">
                                                                                                                                                                                                                                                                                                                                                                                                                + <span x-text="sugg"></span>
                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                        </template>
                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                </div> -->

                        <template x-for="(tag, index) in tags" :key="'input-' + index">
                            <input type="hidden" name="tags[]" :value="tag">
                        </template>
                    </div>
                </div>

                <!-- PDF Orientation -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">PDF Type</h2>
                    <div class="space-y-4">
                        <select name="pdf_orientation"
                            class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                            <option value="portrait"
                                {{ old('pdf_orientation', $product->pdf_orientation ?? 'portrait') == 'portrait' ? 'selected' : '' }}>
                                Portrait (Tall)</option>
                            <option value="landscape"
                                {{ old('pdf_orientation', $product->pdf_orientation ?? 'portrait') == 'landscape' ? 'selected' : '' }}>
                                Landscape (Wide)</option>
                        </select>
                        <p class="text-xs text-surface-400">Controls the layout of the generated high-res PDF.</p>
                    </div>
                </div>

                <!-- Sort Order -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h2 class="font-display font-semibold text-lg mb-5">Display Order</h2>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}"
                        class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                    <p class="text-xs text-surface-400 mt-1">Lower number = higher priority</p>
                </div>
            </div>
        </div>
    </form>

    @if (isset($product))
        <!-- Option Groups Management -->
        <div class="mt-8 bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-display font-semibold text-lg">Option Groups</h2>
            </div>

            <!-- Existing option groups -->
            @foreach ($product->optionGroups as $group)
                <div class="mb-6 p-4 bg-surface-50 rounded-xl">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-surface-800">{{ $group->name }} <span
                                class="text-xs text-surface-400">({{ $group->display_type }})</span></h3>
                        <form action="{{ route('admin.options.destroy', $group) }}" method="POST"
                            onsubmit="return confirm('Delete this group?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete Group</button>
                        </form>
                    </div>
                    <!-- Values -->
                    <div class="space-y-2 mb-3">
                        @foreach ($group->values as $value)
                            <div class="flex items-center justify-between px-3 py-2 bg-white rounded-lg text-sm">
                                <span>{{ $value->label }} — <span
                                        class="text-brand-600">{{ $value->formatted_price_modifier }}</span></span>
                                <form action="{{ route('admin.optionValues.destroy', $value) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    <!-- Add Value -->
                    <form action="{{ route('admin.options.values.store', $group) }}" method="POST"
                        class="flex gap-2 mt-2">
                        @csrf
                        <input type="text" name="label" placeholder="Value label" required
                            class="flex-1 rounded-lg border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <input type="number" name="price_modifier" step="0.01" placeholder="Price ±"
                            class="w-24 rounded-lg border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <button type="submit"
                            class="px-3 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">Add</button>
                    </form>
                </div>
            @endforeach

            <!-- Add New Option Group -->
            <form action="{{ route('admin.products.options.store', $product) }}" method="POST"
                class="pt-4 border-t border-surface-200">
                @csrf
                <h3 class="font-semibold text-surface-700 mb-3">Add Option Group</h3>
                <div class="grid sm:grid-cols-3 gap-3">
                    <input type="text" name="name" placeholder="Group name (e.g. Size)" required
                        class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <select name="display_type"
                        class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="buttons">Buttons</option>
                        <option value="cards">Cards</option>
                        <option value="dropdown">Dropdown</option>
                    </select>
                    <div class="flex gap-2">
                        <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="is_required"
                                value="1" checked class="rounded text-brand-600"> Required</label>
                        <button type="submit"
                            class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Add
                            Group</button>
                    </div>
                </div>
            </form>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.classList.remove('hidden');
                    preview.querySelector('img').src = e.target.result;
                    // Hide the existing image in the drop zone
                    const dropZone = input.previousElementSibling || input.closest('.relative').querySelector(
                        '.border-dashed');
                    if (dropZone) {
                        dropZone.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function tagInput(initialTags, allAvailableTags) {
            return {
                tags: Array.isArray(initialTags) ? initialTags : (initialTags ? JSON.parse(initialTags) : []),
                allAvailableTags: allAvailableTags || [],
                get availableSuggestions() {
                    return this.allAvailableTags.filter(tag => !this.tags.includes(tag)).sort();
                },
                addTag(tag) {
                    tag = tag.trim();
                    if (tag !== '' && !this.tags.includes(tag)) {
                        this.tags.push(tag);
                    }
                },
                removeTag(index) {
                    this.tags.splice(index, 1);
                }
            }
        }
    </script>
@endpush
