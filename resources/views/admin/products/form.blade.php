@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', isset($product) ? 'Edit Product' : 'Create Product')

@php
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
    $isNotAdmin = !auth()->user()->isAdmin();
    $isAdmin = auth()->user()->isAdmin();
@endphp
@section('content')
    <div class="mb-8">
        <div class="flex items-center gap-3">
            <a href="{{ route($rPrefix . 'products.index') }}"
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


    <form action="{{ isset($product) ? route($rPrefix . 'products.update', $product) : route($rPrefix . 'products.store') }}"
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
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="font-display font-semibold text-lg">Basic Information</h2>
                        <button type="button" id="ai-generate-btn" onclick="aiGenerate()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition shadow-md shadow-brand-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="ai-btn-icon">&#10024;</span>
                            <span id="ai-btn-text">AI Generate</span>
                        </button>
                    </div>
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
                        <!--<div>-->
                        <!--    <label class="block text-sm font-medium text-surface-700 mb-1">Compare Price ($)</label>-->
                        <!--    <input type="number" name="compare_price" step="0.01"-->
                        <!--        value="{{ old('compare_price', $product->compare_price ?? '') }}"-->
                        <!--        class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">-->
                        <!--</div>-->
                        <!--<div>-->
                        <!--    <label class="block text-sm font-medium text-surface-700 mb-1">SKU</label>-->
                        <!--    <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"-->
                        <!--        class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">-->
                        <!--</div>-->
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
                            <label class="block text-sm font-medium text-surface-700 mb-1">Front Image <span
                                    class="text-brand-500">*</span></label>
                            <p class="text-xs text-surface-400 mb-3">Upload the empty Front only (no photo inside)</p>
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
                                    <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New frame selected
                                    </p>
                                </div>
                            </div>
                            @error('frame_image')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @if (isset($product) && ($product->no_of_pages == 2 || $product->no_of_pages == 4))
                            <!-- Sample Image -->
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Inside Left Image</label>
                                <p class="text-xs text-surface-400 mb-3">Upload frame with a Inside Left Image (preview)
                                </p>
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
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
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
                                        <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New sample
                                            selected
                                        </p>
                                    </div>
                                </div>
                                @error('sample_image')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        @if (isset($product) && $product->no_of_pages == 4)
                            <!-- Background Image -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-surface-700 mb-1">Inside Right Image</label>
                                <p class="text-xs text-surface-400 mb-3">Upload a inside right image for the canvas (e.g.
                                    bg.jpg)</p>
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
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-medium text-surface-600">Click to upload background
                                                </p>
                                                <p class="text-xs text-surface-400 mt-1">PNG, JPG up to 5MB</p>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" name="background_image" id="background_image_input"
                                        accept="image/*" class="hidden"
                                        onchange="previewImage(this, 'backgroundPreview')">
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
                                <label class="block text-sm font-medium text-surface-700 mb-1">Back Cover Image</label>
                                <p class="text-xs text-surface-400 mb-3">Upload an optional Back Cover layer</p>
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
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
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
                                        <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New overlay
                                            selected
                                        </p>
                                    </div>
                                </div>
                                @error('overlay_image')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        @if (!isset($product))
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1">Inside Left Image</label>
                                <p class="text-xs text-surface-400 mb-3">Upload frame with a Inside Left Image (preview)
                                </p>
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
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
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
                                        <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New sample
                                            selected
                                        </p>
                                    </div>
                                </div>
                                @error('sample_image')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-surface-700 mb-1">Inside Right Image</label>
                                <p class="text-xs text-surface-400 mb-3">Upload a inside right image for the canvas (e.g.
                                    bg.jpg)</p>
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
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-medium text-surface-600">Click to upload background
                                                </p>
                                                <p class="text-xs text-surface-400 mt-1">PNG, JPG up to 5MB</p>
                                            </div>
                                        @endif
                                    </div>
                                    <input type="file" name="background_image" id="background_image_input"
                                        accept="image/*" class="hidden"
                                        onchange="previewImage(this, 'backgroundPreview')">
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
                                <label class="block text-sm font-medium text-surface-700 mb-1">Back Cover Image</label>
                                <p class="text-xs text-surface-400 mb-3">Upload an optional Back Cover layer</p>
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
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
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
                                        <p class="text-xs text-center text-accent-600 mt-1 font-medium">✓ New overlay
                                            selected
                                        </p>
                                    </div>
                                </div>
                                @error('overlay_image')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Customization Settings -->
                <!--<div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">-->
                <!--    <h2 class="font-display font-semibold text-lg mb-5">Customization Settings</h2>-->
                <!--    <div class="grid sm:grid-cols-2 gap-4">-->
                <!--        <div>-->
                <!--            <label class="block text-sm font-medium text-surface-700 mb-1">Min Photos Required</label>-->
                <!--            <input type="number" name="min_images" min="0"-->
                <!--                value="{{ old('min_images', $product->min_images ?? 1) }}"-->
                <!--                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">-->
                <!--        </div>-->
                <!--        <div>-->
                <!--            <label class="block text-sm font-medium text-surface-700 mb-1">Max Photos Allowed</label>-->
                <!--            <input type="number" name="max_images" min="1"-->
                <!--                value="{{ old('max_images', $product->max_images ?? 10) }}"-->
                <!--                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->

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
                        <!--<label class="flex items-center gap-3 cursor-pointer">-->
                        <!--    <input type="checkbox" name="is_featured" value="1"-->
                        <!--        {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}-->
                        <!--        class="rounded text-brand-600 focus:ring-brand-500">-->
                        <!--    <span class="text-sm text-surface-700">Featured Product</span>-->
                        <!--</label>-->
                    </div>
                    <button type="submit"
                        class="w-full mt-6 px-5 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                        {{ isset($product) ? 'Update Product' : 'Create Product' }}
                    </button>
                </div>

                <!-- Category -->
                <!-- Category -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Number of
                                Pages <span class="text-red-500">*</span></label>
                            <select name="no_of_pages" required
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select a category...</option>
                                @foreach ($no_of_pages_array as $key => $val)

                                    <option value="{{ $key }}"
                                        {{ old('no_of_pages', $product->no_of_pages ?? '') == $key ? 'selected' : '' }}>
                                        {{ $val }}
                                    </option>

                                @endforeach
                            </select>
                            @error('no_of_pages')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="space-y-4 mt-5">
                        <div x-data="searchSelect({
                            items: [
                                { value: '', label: 'No event' },
                                @foreach ($events as $event)
                                    { value: '{{ $event->id }}', label: '{{ addslashes($event->title) }}' }, @endforeach
                            ],
                            selected: '{{ old('event_id', $product->event_id ?? '') }}'
                        })">
                            <label
                                class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Event</label>
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                    @keydown.escape="open = false" @keydown.arrow-down.prevent="highlightNext()"
                                    @keydown.arrow-up.prevent="highlightPrev()"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    :placeholder="selectedLabel || 'Search events...'"
                                    class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <div x-show="open && filteredItems.length > 0" @click.outside="open = false" x-cloak
                                    class="absolute z-50 mt-1 w-full bg-white border border-surface-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="(item, idx) in filteredItems" :key="item.value">
                                        <div @click="selectItem(item)"
                                            :class="idx === highlighted ? 'bg-brand-50 text-brand-700' : 'hover:bg-surface-50'"
                                            class="px-3 py-2 cursor-pointer text-sm" x-text="item.label"></div>
                                    </template>
                                </div>
                            </div>
                            <input type="hidden" name="event_id" :value="selectedValue">
                            @error('event_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="space-y-4 mt-5">
                        <div>

                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Store
                                Product Visibility <span class="text-red-500">*</span></label>
                            <select name="store_id"
                                class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                @if ($isNotAdmin)
                                    <option value="{{ auth()->user()->store->id ?? '' }}"
                                        {{ old('store_id', $product->store_id ?? '') == (auth()->user()->store->id ?? '') ? 'selected' : '' }}>
                                        My Store only
                                    </option>
                                @endif
                                <option value="">
                                    All Store
                                </option>

                                @if (!$isNotAdmin && isset($product) && $product->product_store)
                                    @php
                                        $store = \App\Models\Store::find($product->product_store);
                                    @endphp
                                    <option value="{{ $product->product_store ?? '' }}"
                                        {{ old('store_id', $product->product_store ?? '') == ($product->store_id ?? '') ? 'selected' : '' }}>
                                        {{ $store->store_name ?? '' }} Only (My Store only)
                                    </option>
                                @endif
                            </select>
                            @error('store_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="space-y-4 mt-5">
                        <div x-data="searchSelect({
                            items: [
                                { value: '', label: 'Select a category...' },
                                @foreach ($categories->whereNull('parent_id') as $cat)
                                    { value: '{{ $cat->id }}', label: '{{ addslashes($cat->name) }}', bold: true },
                                    @foreach ($categories->where('parent_id', $cat->id) as $sub)
                                        { value: '{{ $sub->id }}', label: '   - {{ addslashes($sub->name) }}' }, @endforeach
                                @endforeach
                            ],
                            selected: '{{ old('category_id', $product->category_id ?? '') }}',
                            required: true
                        })">
                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Category
                                Selection <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                    @keydown.escape="open = false" @keydown.arrow-down.prevent="highlightNext()"
                                    @keydown.arrow-up.prevent="highlightPrev()"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    :placeholder="selectedLabel || 'Search categories...'"
                                    class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <div x-show="open && filteredItems.length > 0" @click.outside="open = false" x-cloak
                                    class="absolute z-50 mt-1 w-full bg-white border border-surface-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="(item, idx) in filteredItems" :key="item.value">
                                        <div @click="selectItem(item)"
                                            :class="[idx === highlighted ? 'bg-brand-50 text-brand-700' : 'hover:bg-surface-50',
                                                item.bold ? 'font-bold' : ''
                                            ]"
                                            class="px-3 py-2 cursor-pointer text-sm" x-text="item.label"></div>
                                    </template>
                                </div>
                            </div>
                            <input type="hidden" name="category_id" :value="selectedValue" x-bind:required="required">
                            @error('category_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="space-y-4 mt-5">
                        <div x-data="searchSelect({
                            items: [
                                { value: '', label: 'Select a size...' },
                                @foreach ($productTypes->whereNull('parent_id') as $type)
                                    { value: '{{ $type->id }}', label: '{{ addslashes($type->name) }}', bold: true },
                                    @foreach ($productTypes->where('parent_id', $type->id) as $sub)
                                        { value: '{{ $sub->id }}', label: '   - {{ addslashes($sub->name) }} {{ addslashes($sub->title) }} - {{ $sub->width }} x {{ $sub->height }} {{ $sub->unit }}' }, @endforeach
                                @endforeach
                            ],
                            selected: '{{ old('product_type_id', $product->product_type_id ?? '') }}'
                        })">
                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Size
                                Selection</label>
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                    @keydown.escape="open = false" @keydown.arrow-down.prevent="highlightNext()"
                                    @keydown.arrow-up.prevent="highlightPrev()"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    :placeholder="selectedLabel || 'Search sizes...'"
                                    class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <div x-show="open && filteredItems.length > 0" @click.outside="open = false" x-cloak
                                    class="absolute z-50 mt-1 w-full bg-white border border-surface-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="(item, idx) in filteredItems" :key="item.value">
                                        <div @click="selectItem(item)"
                                            :class="[idx === highlighted ? 'bg-brand-50 text-brand-700' : 'hover:bg-surface-50',
                                                item.bold ? 'font-bold' : ''
                                            ]"
                                            class="px-3 py-2 cursor-pointer text-sm" x-text="item.label"></div>
                                    </template>
                                </div>
                            </div>
                            <input type="hidden" name="product_type_id" :value="selectedValue">
                            @error('product_type_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="space-y-4 mt-5">
                        <div x-data="searchSelect({
                            items: [
                                { value: '', label: 'Select a paper type...' },
                                { value: 'none', label: 'None' },
                                @foreach ($paperTypes as $paperType)
                                    { value: '{{ $paperType->id }}', label: '{{ addslashes($paperType->title) }}' }, @endforeach
                            ],
                            selected: '{{ old('paper_type_id', $product->paper_type_id ?? '') }}'
                        })">
                            <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Paper
                                Type Selection</label>
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                    @keydown.escape="open = false" @keydown.arrow-down.prevent="highlightNext()"
                                    @keydown.arrow-up.prevent="highlightPrev()"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    :placeholder="selectedLabel || 'Search paper types...'"
                                    class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                                <div x-show="open && filteredItems.length > 0" @click.outside="open = false" x-cloak
                                    class="absolute z-50 mt-1 w-full bg-white border border-surface-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="(item, idx) in filteredItems" :key="item.value">
                                        <div @click="selectItem(item)"
                                            :class="idx === highlighted ? 'bg-brand-50 text-brand-700' : 'hover:bg-surface-50'"
                                            class="px-3 py-2 cursor-pointer text-sm" x-text="item.label"></div>
                                    </template>
                                </div>
                            </div>
                            <input type="hidden" name="paper_type_id" :value="selectedValue">
                            @error('paper_type_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div x-data="tagInput({{ json_encode(old('tags', $product->tags ?? [])) }}, {{ json_encode($allTags) }})">
                        <div class="flex flex-wrap gap-2 mb-3 mt-5">
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
                        <label class="block text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Add
                            Tags</label>
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


            </div>
        </div>
    </form>

    <!--@if (isset($product))-->
    <!-- Option Groups Management -->
    <!--    <div class="mt-8 bg-white rounded-2xl border border-surface-100 shadow-card p-6">-->
    <!--        <div class="flex items-center justify-between mb-5">-->
    <!--            <h2 class="font-display font-semibold text-lg">Option Groups</h2>-->
    <!--        </div>-->

    <!-- Existing option groups -->
    <!--        @foreach ($product->optionGroups as $group)
    -->
    <!--            <div class="mb-6 p-4 bg-surface-50 rounded-xl">-->
    <!--                <div class="flex items-center justify-between mb-3">-->
    <!--                    <h3 class="font-semibold text-surface-800">{{ $group->name }} <span-->
    <!--                            class="text-xs text-surface-400">({{ $group->display_type }})</span></h3>-->
    <!--                    <form action="{{ route('admin.options.destroy', $group) }}" method="POST"-->
    <!--                        onsubmit="return confirm('Delete this group?')">-->
    <!--                        @csrf @method('DELETE')-->
    <!--                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete Group</button>-->
    <!--                    </form>-->
    <!--                </div>-->
    <!-- Values -->
    <!--                <div class="space-y-2 mb-3">-->
    <!--                    @foreach ($group->values as $value)
    -->
    <!--                        <div class="flex items-center justify-between px-3 py-2 bg-white rounded-lg text-sm">-->
    <!--                            <span>{{ $value->label }} - <span-->
    <!--                                    class="text-brand-600">{{ $value->formatted_price_modifier }}</span></span>-->
    <!--                            <form action="{{ route('admin.optionValues.destroy', $value) }}" method="POST">-->
    <!--                                @csrf @method('DELETE')-->
    <!--                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>-->
    <!--                            </form>-->
    <!--                        </div>-->
    <!--
    @endforeach-->
    <!--                </div>-->
    <!-- Add Value -->
    <!--                <form action="{{ route('admin.options.values.store', $group) }}" method="POST"-->
    <!--                    class="flex gap-2 mt-2">-->
    <!--                    @csrf-->
    <!--                    <input type="text" name="label" placeholder="Value label" required-->
    <!--                        class="flex-1 rounded-lg border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">-->
    <!--                    <input type="number" name="price_modifier" step="0.01" placeholder="Price ±"-->
    <!--                        class="w-24 rounded-lg border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">-->
    <!--                    <button type="submit"-->
    <!--                        class="px-3 py-2 bg-brand-600 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">Add</button>-->
    <!--                </form>-->
    <!--            </div>-->
    <!--
    @endforeach-->

    <!-- Add New Option Group -->
    <!--        <form action="{{ route('admin.products.options.store', $product) }}" method="POST"-->
    <!--            class="pt-4 border-t border-surface-200">-->
    <!--            @csrf-->
    <!--            <h3 class="font-semibold text-surface-700 mb-3">Add Option Group</h3>-->
    <!--            <div class="grid sm:grid-cols-3 gap-3">-->
    <!--                <input type="text" name="name" placeholder="Group name (e.g. Size)" required-->
    <!--                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">-->
    <!--                <select name="display_type"-->
    <!--                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">-->
    <!--                    <option value="buttons">Buttons</option>-->
    <!--                    <option value="cards">Cards</option>-->
    <!--                    <option value="dropdown">Dropdown</option>-->
    <!--                </select>-->
    <!--                <div class="flex gap-2">-->
    <!--                    <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="is_required"-->
    <!--                            value="1" checked class="rounded text-brand-600"> Required</label>-->
    <!--                    <button type="submit"-->
    <!--                        class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Add-->
    <!--                        Group</button>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </form>-->
    <!--    </div>-->
    <!--@endif-->
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

        function searchSelect({
            items = [],
            selected = '',
            required = false
        } = {}) {
            return {
                items,
                search: '',
                open: false,
                highlighted: -1,
                selectedValue: selected,
                required,
                get selectedLabel() {
                    const found = this.items.find(i => String(i.value) === String(this.selectedValue));
                    return found && found.value !== '' ? found.label.trim() : '';
                },
                get filteredItems() {
                    if (!this.search) return this.items;
                    const q = this.search.toLowerCase();
                    return this.items.filter(i => i.label.toLowerCase().includes(q));
                },
                selectItem(item) {
                    this.selectedValue = item.value;
                    this.search = '';
                    this.open = false;
                    this.highlighted = -1;
                },
                highlightNext() {
                    if (this.highlighted < this.filteredItems.length - 1) this.highlighted++;
                },
                highlightPrev() {
                    if (this.highlighted > 0) this.highlighted--;
                },
                selectHighlighted() {
                    if (this.highlighted >= 0 && this.filteredItems[this.highlighted]) {
                        this.selectItem(this.filteredItems[this.highlighted]);
                    }
                }
            };
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

        function aiGenerate() {
            const nameInput = document.querySelector('input[name="name"]');
            const name = nameInput ? nameInput.value.trim() : '';

            if (!name) {
                showAiNotification('Please enter a Product Name first.', 'error');
                nameInput && nameInput.focus();
                return;
            }

            const btn = document.getElementById('ai-generate-btn');
            const btnIcon = document.getElementById('ai-btn-icon');
            const btnText = document.getElementById('ai-btn-text');

            btn.disabled = true;
            btnIcon.innerHTML =
                '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
            btnText.textContent = 'Generating...';

            const context = {};
            const shortDesc = document.querySelector('textarea[name="short_description"]');
            if (shortDesc && shortDesc.value.trim()) context.short_description = shortDesc.value.trim();
            const desc = document.querySelector('textarea[name="description"]');
            if (desc && desc.value.trim()) context.description = desc.value.trim();
            const basePrice = document.querySelector('input[name="base_price"]');
            if (basePrice && basePrice.value.trim()) context.base_price = basePrice.value.trim();

            const dropdownOptions = {};

            const pagesSelect = document.querySelector('select[name="no_of_pages"]');
            if (pagesSelect) {
                dropdownOptions.number_of_pages = Array.from(pagesSelect.options)
                    .filter(o => o.value).map(o => o.text.trim());
            }

            const storeSelect = document.querySelector('select[name="store_id"]');
            if (storeSelect) {
                dropdownOptions.store_visibility = Array.from(storeSelect.options)
                    .map(o => o.text.trim());
            }

            const pdfSelect = document.querySelector('select[name="pdf_orientation"]');
            if (pdfSelect) {
                dropdownOptions.pdf_type = Array.from(pdfSelect.options)
                    .map(o => o.text.trim());
            }

            // Alpine-powered searchSelect dropdowns - extract from x-data items
            document.querySelectorAll('[x-data]').forEach(el => {
                const hidden = el.querySelector('input[type="hidden"]');
                if (!hidden) return;
                const fieldName = hidden.getAttribute('name');
                const textInput = el.querySelector('input[type="text"]');
                if (!textInput) return;

                const xData = el.getAttribute('x-data');
                if (!xData || !xData.includes('searchSelect')) return;

                const labelMatches = xData.match(/label:\s*'([^']*?)'/g);
                if (labelMatches) {
                    const labels = labelMatches.map(m => m.replace(/label:\s*'/, '').replace(/'$/, '').trim())
                        .filter(l => l && !l.startsWith('Select') && !l.startsWith('Search') && l !== 'No event' &&
                            l !== 'None');

                    const keyMap = {
                        'event_id': 'event',
                        'category_id': 'category',
                        'product_type_id': 'size',
                        'paper_type_id': 'paper_type'
                    };
                    const key = keyMap[fieldName] || fieldName;
                    if (labels.length > 0) dropdownOptions[key] = labels;
                }
            });

            fetch("{{ route('admin.products.ai-generate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                            '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name,
                        context,
                        dropdown_options: dropdownOptions
                    }),
                })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        showAiNotification(res.message || 'AI generation failed.', 'error');
                        return;
                    }
                    applyAiData(res.data);
                    showAiNotification('Product content generated successfully!', 'success');
                })
                .catch(err => {
                    console.error('AI Generate Error:', err);
                    showAiNotification('Failed to generate content. Please try again.', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    btnIcon.innerHTML = '&#10024;';
                    btnText.textContent = 'AI Generate';
                });
        }

        function applyAiData(data) {
            if (data.short_description) {
                const el = document.querySelector('textarea[name="short_description"]');
                if (el) el.value = data.short_description;
            }
            if (data.description) {
                const el = document.querySelector('textarea[name="description"]');
                if (el) el.value = data.description;
            }
            if (data.meta_title) {
                const el = document.querySelector('input[name="meta_title"]');
                if (el) el.value = data.meta_title;
            }
            if (data.meta_description) {
                const el = document.querySelector('textarea[name="meta_description"]');
                if (el) el.value = data.meta_description;
            }
            if (data.base_price !== undefined && data.base_price !== null) {
                const el = document.querySelector('input[name="base_price"]');
                if (el && !el.value.trim()) el.value = data.base_price;
            }

            if (data.number_of_pages) {
                matchSelect('no_of_pages', data.number_of_pages);
            }
            if (data.pdf_type) {
                matchSelect('pdf_orientation', data.pdf_type);
            }
            if (data.store_visibility) {
                matchSelect('store_id', data.store_visibility);
            }

            if (data.event) matchAlpineSelect('event_id', data.event);
            if (data.category) matchAlpineSelect('category_id', data.category);
            if (data.size) matchAlpineSelect('product_type_id', data.size);
            if (data.paper_type) matchAlpineSelect('paper_type_id', data.paper_type);

            if (data.tags && Array.isArray(data.tags)) {
                const tagContainer = document.querySelector('[x-data*="tagInput"]');
                if (tagContainer && tagContainer.__x) {
                    const alpineData = tagContainer.__x.$data;
                    data.tags.forEach(tag => {
                        if (!alpineData.tags.includes(tag)) {
                            alpineData.tags.push(tag);
                        }
                    });
                } else if (tagContainer) {
                    const xData = Alpine.$data(tagContainer);
                    if (xData) {
                        data.tags.forEach(tag => {
                            if (!xData.tags.includes(tag)) {
                                xData.tags.push(tag);
                            }
                        });
                    }
                }
            }
        }

        function matchSelect(name, value) {
            const select = document.querySelector(`select[name="${name}"]`);
            if (!select) return;
            const val = String(value).toLowerCase().trim();
            let bestMatch = null;
            Array.from(select.options).forEach(opt => {
                const optText = opt.text.toLowerCase().trim();
                const optVal = opt.value.toLowerCase().trim();
                if (optVal === val || optText === val || optText.includes(val) || val.includes(optText)) {
                    bestMatch = opt.value;
                }
            });
            if (bestMatch !== null) select.value = bestMatch;
        }

        function matchAlpineSelect(hiddenName, value) {
            const hidden = document.querySelector(`input[type="hidden"][name="${hiddenName}"]`);
            if (!hidden) return;
            const container = hidden.closest('[x-data]');
            if (!container) return;

            const val = String(value).toLowerCase().trim();
            const xDataStr = container.getAttribute('x-data');
            const itemMatches = xDataStr.match(/\{[^}]*value:\s*'([^']*)'[^}]*label:\s*'([^']*)'[^}]*\}/g);
            if (!itemMatches) return;

            let bestValue = '';
            itemMatches.forEach(m => {
                const vMatch = m.match(/value:\s*'([^']*)'/);
                const lMatch = m.match(/label:\s*'([^']*)'/);
                if (vMatch && lMatch) {
                    const label = lMatch[1].replace(/^\s*-\s*/, '').trim().toLowerCase();
                    if (label === val || label.includes(val) || val.includes(label)) {
                        bestValue = vMatch[1];
                    }
                }
            });

            if (bestValue) {
                const alpineData = Alpine.$data(container);
                if (alpineData) {
                    alpineData.selectedValue = bestValue;
                    alpineData.search = '';
                    alpineData.open = false;
                }
            }
        }

        function showAiNotification(message, type) {
            const existing = document.getElementById('ai-notification');
            if (existing) existing.remove();

            const colors = type === 'success' ?
                'bg-green-50 border-green-200 text-green-800' :
                'bg-red-50 border-red-200 text-red-800';

            const div = document.createElement('div');
            div.id = 'ai-notification';
            div.className =
                `fixed top-4 right-4 z-50 px-5 py-3 rounded-xl border shadow-lg ${colors} transition-all duration-300`;
            div.textContent = message;
            document.body.appendChild(div);

            setTimeout(() => {
                div.style.opacity = '0';
                setTimeout(() => div.remove(), 300);
            }, 4000);
        }
    </script>
@endpush
