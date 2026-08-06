{{-- ═══════════════════════════════════════════════════════════════════════
     KEY RATIO FIX:
     Admin saves canvasWidth=560, canvasHeight=400 (7:5), aspectRatioW=7, aspectRatioH=5.
     Frontend reads those values → computes scaleFactor = displayWidth / adminCanvasW
     → displayHeight = adminCanvasH * scaleFactor   (preserves 7:5 exactly)
     Mask coords: displayX = mask.left * scaleFactor,  displayW = mask.width * mask.scaleX * scaleFactor
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.quick-flow')
@section('title', 'Customize Your ' . $product->name)
@section('header_title', 'Customize')

@section('breadcrumbs')
@if($product->category?->parent)
<i data-lucide="chevron-right" class="w-4 h-4 text-surface-300 shrink-0"></i>
<a href="{{ route('flow.category', $product->category->parent->slug) }}" class="text-surface-600 hover:text-brand-500 font-medium truncate max-w-[100px] transition-colors">{{ $product->category->parent->name }}</a>
@endif
@if($product->category)
<i data-lucide="chevron-right" class="w-4 h-4 text-surface-300 shrink-0"></i>
<a href="{{ route('flow.category', $product->category->slug) }}" class="text-surface-600 hover:text-brand-500 font-medium truncate max-w-[100px] transition-colors">{{ $product->category->name }}</a>
@endif
<i data-lucide="chevron-right" class="w-4 h-4 text-surface-300 shrink-0"></i>
<span class="text-surface-900 font-bold max-w-[100px] truncate">Customize</span>
@endsection

@push('styles')
<style>
    .thumb-nav {
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .thumb-nav-item {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        border: 3px solid #e2e8f0;
        transition: all .2s;
        position: relative;
        background: #f8fafc;
    }

    .thumb-nav-item.active {
        border-color: #6FBA3B;
        box-shadow: 0 0 0 3px rgba(111, 186, 59, .2);
    }

    .thumb-nav-item.disabled-tab {
        opacity: .5;
        cursor: default;
    }

    .thumb-nav-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumb-nav-item .thumb-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, .6);
        color: #fff;
        font-size: 8px;
        font-weight: 700;
        text-align: center;
        padding: 2px 0;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .thumb-nav-item .thumb-lock {
        position: absolute;
        top: 3px;
        right: 3px;
        background: rgba(0, 0, 0, .5);
        border-radius: 50%;
        padding: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .canvas-wrapper {
        position: relative;
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, .1);
    }

    .canvas-hidden {
        display: none !important;
    }

    .upload-zone {
        border: 2px dashed #e2e8f0;
        border-radius: 1.25rem;
        padding: 1.25rem;
        background: #f8fafc;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .upload-zone:hover {
        border-color: #6FBA3B;
        background: #F5FAF1;
        transform: translateY(-2px);
    }

    .upload-zone.has-image {
        border-style: solid;
        border-color: #10b981;
        background: #f0fdf4;
    }

    .text-toolbar {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1.25rem;
        padding: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    .text-toolbar input[type="text"],
    .text-toolbar textarea {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 10px 14px;
        font-size: 14px;
        background: #fff;
        transition: all 0.2s;
        width: 100%;
        resize: none;
        min-height: 44px;
    }

    .text-toolbar input[type="text"]:focus,
    .text-toolbar textarea:focus {
        border-color: #6FBA3B;
        box-shadow: 0 0 0 3px rgba(111, 186, 59, 0.1);
        outline: none;
    }

    .text-toolbar select {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        cursor: pointer;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 32px;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .text-toolbar button {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all .2s;
    }

    .canvas-disabled-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.05);
        z-index: 200;
        pointer-events: none;
        border-radius: 1.5rem;
    }

    .canvas-wrapper:has(.canvas-disabled-overlay) {
        cursor: not-allowed;
    }

    .canvas-container {
        z-index: 100;
        touch-action: none;
    }

    .hidden {
        display: none !important;
    }

    /* ── Curated Fonts ── */
    @font-face {
        font-family: 'ABeeZee';
        src: url("{{ asset('fonts/ABeeZeeRegular.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Alex Brush';
        src: url("{{ asset('fonts/AlexBrush.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Alfa Slab One';
        src: url("{{ asset('fonts/AlfaSlabOne.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Amatic SC';
        src: url("{{ asset('fonts/AmaticSC.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Anton';
        src: url("{{ asset('fonts/Anton.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Bangers';
        src: url("{{ asset('fonts/Bangers.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Bebas Neue';
        src: url("{{ asset('fonts/BebasNeue.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Bungee';
        src: url("{{ asset('fonts/Bungee.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Caveat';
        src: url("{{ asset('fonts/Caveat.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Cinzel';
        src: url("{{ asset('fonts/Cinzel.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Courgette';
        src: url("{{ asset('fonts/Courgette.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Crimson Pro';
        src: url("{{ asset('fonts/CrimsonPro.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Dancing Script';
        src: url("{{ asset('fonts/DancingScript.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Great Vibes';
        src: url("{{ asset('fonts/GreatVibes.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Indie Flower';
        src: url("{{ asset('fonts/IndieFlower.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Inter';
        src: url("{{ asset('fonts/Inter.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Lato';
        src: url("{{ asset('fonts/Lato.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Lobster';
        src: url("{{ asset('fonts/Lobster.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Lora';
        src: url("{{ asset('fonts/Lora.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Open Sans';
        src: url("{{ asset('fonts/OpenSans.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Oswald';
        src: url("{{ asset('fonts/Oswald.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Pacifico';
        src: url("{{ asset('fonts/Pacifico.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Permanent Marker';
        src: url("{{ asset('fonts/PermanentMarker.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Playfair Display';
        src: url("{{ asset('fonts/PlayfairDisplay.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Poppins';
        src: url("{{ asset('fonts/Poppins.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Righteous';
        src: url("{{ asset('fonts/Righteous.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Roboto';
        src: url("{{ asset('fonts/Roboto.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Sacramento';
        src: url("{{ asset('fonts/Sacramento.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Satisfy';
        src: url("{{ asset('fonts/Satisfy.ttf') }}");
        font-display: swap;
    }

    @font-face {
        font-family: 'Shadows Into Light';
        src: url("{{ asset('fonts/ShadowsIntoLightTwo.ttf') }}");
        font-display: swap;
    }
</style>
<style>
    /* Horizontal scroll for thumbnails on small screens */
    .thumb-nav {
        display: flex;
        gap: 10px;
        justify-content: flex-start;
        overflow-x: auto;
        padding: 4px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        flex-wrap: nowrap;
    }

    .thumb-nav::-webkit-scrollbar {
        display: none;
    }

    .thumb-nav-item {
        flex: 0 0 64px;
        height: 64px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
    }

    /* Sticky Bottom Action for Mobile */
    @media (max-width: 768px) {
        .mobile-sticky-action {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem;
            border-top: 1px solid #e2e8f0;
            z-index: 50;
            box-shadow: 0 -10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .container {
            padding-bottom: 120px;
        }

        .text-toolbar {
            padding: 8px;
            gap: 4px;
        }

        .text-toolbar select {
            max-width: 80px;
        }
    }

    .canvas-wrapper {
        background: #f1f5f9;
        border-radius: 1rem;
        touch-action: none;
    }

    .thumb-nav-clear {
        flex: 0 0 64px;
        height: 64px;
        border-radius: 12px;
        border: 2px dashed #fca5a5;
        background: #fef2f2;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .2s;
        margin-left: 10px;
    }

    .thumb-nav-clear:hover {
        background: #fee2e2;
        border-color: #ef4444;
    }

    .thumb-nav-clear i {
        color: #ef4444;
    }

    .thumb-nav-clear span {
        font-size: 10px;
        font-weight: 700;
        color: #ef4444;
        margin-top: 2px;
    }
</style>
@endpush

@section('content')
@php
$imageTypes = [];
$slots = [
'frame_image' => 'Page 1',
'sample_image' => 'Page 2',
'background_image' => 'Page 3',
'overlay_image' => 'Page 4'
];

$galleryImages = $product->images->values();
$galleryIndex = 0;

foreach($slots as $field => $label) {
$url = $product->{$field . '_url'};

// If field is empty, try to take from gallery
if (!$url && isset($galleryImages[$galleryIndex])) {
$url = asset('storage/' . $galleryImages[$galleryIndex]->image_path);
$galleryIndex++;
}

$imageTypes[$field] = ['label' => $label, 'url' => $url];
}

$maskData = $product->mask_data ?? [];
if (is_string($maskData)) $maskData = json_decode($maskData, true) ?? [];
@endphp

<div id="customizer-app" class="container mx-auto mb-12">

    <div class="space-y-2 mb-2 text-left">
        <h1 class="text-lg lg:text-2xl font-display font-black tracking-tight flex items-start justify-start gap-3">
            <a href="javascript:history.back()"
                class="w-6 h-6 bg-white border border-slate-200 shadow-sm rounded-full hover:bg-slate-50 transition-colors text-slate-500 hover:text-slate-900 inline-flex items-center justify-center">
                <i data-lucide="arrow-left" class="w-3 h-3"></i>
            </a>
            Design Your Order
        </h1>
        <p class="text-slate-500 font-medium text-xs md:ml-14">Customize each image by uploading photos and adding text.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 items-start">

        <!-- ═══ SECTION 1: Design & Preview Area ═══ -->
        <div class="space-y-1">
            <!-- Thumbnail Navigation -->
            <div class="thumb-nav" id="thumb-nav">
                @foreach($imageTypes as $key => $img)
                @php $config = $maskData[$key] ?? []; $enabled = ($config['enabled'] ?? true) !== false; @endphp
                <div class="thumb-nav-item {{ !$enabled ? 'disabled-tab' : '' }}"
                    data-key="{{ $key }}"
                    onclick="customizer.switchCanvas('{{ $key }}')">
                    <img src="{{ $img['url'] }}">
                    <span class="thumb-label">Page {{ $loop->iteration }}</span>
                    @if(!$enabled)
                    <div class="thumb-lock">
                        <i data-lucide="lock" class="w-3 h-3 text-white"></i>
                    </div>
                    @endif
                </div>
                @endforeach

                <!-- Clear All Button -->
                <div class="thumb-nav-clear" onclick="customizer.clearAll()">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Clear All</span>
                </div>
            </div>

            <!-- Main Workspace Canvas -->
            <div class="canvas-wrapper" id="canvas-container">
                @foreach($imageTypes as $key => $img)
                @php $config = $maskData[$key] ?? []; $enabled = ($config['enabled'] ?? true) !== false; @endphp
                <div id="canvas-wrapper-{{ $key }}" class="canvas-layer" style="position:absolute;top:0;left:0;width:100%;visibility:hidden;pointer-events:none;z-index:-1;">
                    <canvas id="canvas-{{ $key }}"></canvas>
                    @if(!$enabled)
                    <div class="canvas-disabled-overlay"></div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Zoom Control -->
            <div id="zoom-control" class="hidden flex items-center gap-4 px-5 bg-white border-2 border-slate-100 rounded-2xl py-3 shadow-sm">
                <div class="p-2 bg-slate-50 rounded-lg"><i data-lucide="image" class="w-4 h-4 text-slate-400"></i></div>
                <input type="range" id="zoom-slider" oninput="customizer.updateImageScale(this.value)"
                    min="0.1" max="3" step="0.01" value="1"
                    class="flex-1 h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-brand-500">
                <div class="p-2 bg-slate-50 rounded-lg"><i data-lucide="zoom-in" class="w-5 h-5 text-slate-400"></i></div>
            </div>
        </div>

        <!-- ═══ SECTION 2: Editing Tools ═══ -->
        <div class="space-y-1">
            <div class="grid grid-cols-2 gap-3">
                <!-- Current Status -->
                <div id="status-card" class="border-2 rounded-2xl p-4 flex items-center h-full bg-slate-50 border-slate-200">
                    <div class="flex items-center gap-3">
                        <div id="status-icon-bg" class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-slate-300">
                            <i id="status-icon" data-lucide="lock" class="w-4 h-4 text-white"></i>
                        </div>
                        <div class="min-w-0 overflow-hidden">
                            <p id="status-badge" class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Locked</p>
                            <p id="status-label" class="text-xs font-black truncate text-slate-600">Layer</p>
                        </div>
                    </div>
                </div>

                <!-- Photo Upload Area -->
                <div id="upload-area" class="relative hidden">
                    <label class="block cursor-pointer h-full">
                        <div id="upload-zone" class="upload-zone !p-3 h-full flex items-center">
                            <div class="flex items-center gap-3 w-full">
                                <div id="upload-icon-bg" class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm shrink-0 bg-white text-slate-400">
                                    <i id="upload-icon" data-lucide="camera" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <h4 id="upload-text" class="font-bold text-xs text-slate-800 leading-tight truncate">Upload</h4>
                                    <p class="text-[9px] font-medium text-slate-400 mt-0.5 truncate">Tap to pick</p>
                                </div>
                            </div>
                        </div>
                        <input type="file" onchange="customizer.handleFileUpload(this)"
                            class="absolute opacity-0 w-0 h-0 pointer-events-none"
                            id="photo-upload-input"
                            accept="image/*">
                    </label>
                </div>
            </div>

            <!-- Advanced Text Toolbar -->
            <div id="text-toolbar" class="hidden text-toolbar flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[140px]">
                    <textarea id="text-input" placeholder="Add text..."
                        class="w-full" oninput="customizer.onTextInputChange(this.value)" rows="1"></textarea>
                    <button id="clear-text-btn" onclick="customizer.clearSelection()"
                        class="hidden absolute right-3 top-3 text-slate-300 hover:text-slate-500 transition-colors">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="flex flex-col gap-2 w-full md:flex-1 relative">
                    <!-- First Row: 2 Buttons -->
                    <div class="flex items-center gap-2 w-full">
                        <select id="font-family-select" onchange="customizer._updateSelectedStyle('fontFamily', this.value)" class="flex-1 bg-transparent min-w-0">
                            <option style="font-family: 'Inter'">Inter</option>
                            <option style="font-family: 'Roboto'">Roboto</option>
                            <option style="font-family: 'Open Sans'">Open Sans</option>
                            <option style="font-family: 'Poppins'">Poppins</option>
                            <option style="font-family: 'Lato'">Lato</option>
                            <option style="font-family: 'ABeeZee'">ABeeZee</option>
                            <option style="font-family: 'Oswald'">Oswald</option>
                            <option style="font-family: 'Bebas Neue'">Bebas Neue</option>
                            <option style="font-family: 'Anton'">Anton</option>
                            <option style="font-family: 'Alfa Slab One'">Alfa Slab One</option>
                            <option style="font-family: 'Playfair Display'">Playfair Display</option>
                            <option style="font-family: 'Lora'">Lora</option>
                            <option style="font-family: 'Crimson Pro'">Crimson Pro</option>
                            <option style="font-family: 'Cinzel'">Cinzel</option>
                            <option style="font-family: 'Dancing Script'">Dancing Script</option>
                            <option style="font-family: 'Great Vibes'">Great Vibes</option>
                            <option style="font-family: 'Pacifico'">Pacifico</option>
                            <option style="font-family: 'Alex Brush'">Alex Brush</option>
                            <option style="font-family: 'Satisfy'">Satisfy</option>
                            <option style="font-family: 'Courgette'">Courgette</option>
                            <option style="font-family: 'Sacramento'">Sacramento</option>
                            <option style="font-family: 'Caveat'">Caveat</option>
                            <option style="font-family: 'Indie Flower'">Indie Flower</option>
                            <option style="font-family: 'Amatic SC'">Amatic SC</option>
                            <option style="font-family: 'Shadows Into Light'">Shadows Into Light</option>
                            <option style="font-family: 'Lobster'">Lobster</option>
                            <option style="font-family: 'Righteous'">Righteous</option>
                            <option style="font-family: 'Bangers'">Bangers</option>
                            <option style="font-family: 'Bungee'">Bungee</option>
                            <option style="font-family: 'Permanent Marker'">Permanent Marker</option>
                        </select>
                        <select id="text-align-select" onchange="customizer._updateSelectedStyle('textAlign', this.value)" class="flex-1 bg-transparent min-w-0">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                            <option value="justify">Justify</option>
                        </select>
                    </div>

                    <!-- Second Row: 3 Buttons -->
                    <div class="flex items-center gap-2 w-full">
                        <select id="font-size-select" onchange="customizer._updateSelectedStyle('fontSize', parseInt(this.value))" class="flex-1 bg-transparent min-w-0">
                            @for($i=8; $i<=96; $i+=2)
                                <option value="{{ $i }}" {{ $i == 16 ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                        </select>

                        <div class="relative w-10 h-10 shrink-0">
                            <input type="color" id="text-color-input" oninput="customizer._updateSelectedStyle('fill', this.value)"
                                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                            <div id="text-color-preview" class="w-full h-full rounded-xl border-2 border-white shadow-sm flex items-center justify-center overflow-hidden" style="background: #000000">
                                <i data-lucide="palette" class="w-4 h-4 text-white mix-blend-difference opacity-50"></i>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <button id="add-text-btn" onclick="customizer.addText()" class="w-full bg-brand-500 hover:bg-brand-600 text-white h-10 px-2 rounded-xl text-sm font-bold shadow-lg shadow-brand-100 transition-all active:scale-95">
                                + Add
                            </button>
                            <div id="editing-badge" class="hidden w-full flex items-center justify-center gap-1 h-10 px-2 bg-brand-50 text-brand-600 rounded-xl border border-brand-100">
                                <i data-lucide="type" class="w-4 h-4 shrink-0"></i>
                                <span class="text-[10px] md:text-xs font-black uppercase tracking-widest truncate">Editing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Eraser / Remove Action -->
            <button id="remove-btn" onclick="customizer.handleRemove()"
                class="hidden w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 border-2 border-red-100 rounded-xl text-red-600 font-bold text-sm hover:bg-red-100 transition">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span id="remove-btn-text">Remove Photo</span>
            </button>

            <!-- Final Checkout Action -->
            <div class="pt-1 border-t border-slate-100">
                <form action="{{ route('flow.checkout') }}" method="POST" id="checkout-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="upload_ids" id="upload_ids_field">
                    <button type="button" id="submit-btn" onclick="customizer.submitAllCanvases()"
                        class="w-full bg-slate-900 text-white font-display font-black py-4 rounded-2xl flex items-center justify-center gap-3 hover:bg-black transition-all active:scale-[0.98] shadow-xl shadow-slate-200">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                        Confirm Design & Continue
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
    const customizer = {
        // ── State ─────────────────────────────────────────────────────────────
        activeCanvas: '',
        canvases: {},
        canvasEnabled: {},
        canvasImages: {},
        uploadIds: {},
        imgScales: {},
        isUploading: false,
        isSavingComposite: false,
        selectedObject: null,

        // Default Text Styles
        textFontSize: '24',
        textFontFamily: 'Arial',
        textColor: '#000000',
        textAlign: 'center',

        // Injected from PHP
        imageTypes: <?php echo json_encode($imageTypes); ?>,
        allMaskData: <?php echo json_encode($maskData); ?>,
        productId: <?php echo $product->id; ?>,

        // ── Boot ──────────────────────────────────────────────────────────────
        init() {
            // 1. Parse mask data
            if (typeof this.allMaskData === 'string') {
                try {
                    this.allMaskData = JSON.parse(this.allMaskData);
                } catch (e) {
                    this.allMaskData = {};
                }
            }

            // 2. Map State
            Object.keys(this.imageTypes).forEach(key => {
                const config = this.allMaskData[key];
                this.canvasEnabled[key] = (config && config.enabled === false) ? false : true;
                this.canvasImages[key] = null;
                this.uploadIds[key] = null;
                this.imgScales[key] = 1;
            });

            // 3. Set Active Tab
            const firstEnabled = Object.keys(this.canvasEnabled).find(k => this.canvasEnabled[k] === true);
            this.activeCanvas = firstEnabled || Object.keys(this.imageTypes)[0];

            // 4. Initialization
            setTimeout(() => {
                this._initAllCanvases();
                this.updateUI(); // Ensure UI reflects initialized canvases
            }, 100);
            window.addEventListener('resize', this._debounce(() => this._resizeAllCanvases(), 150));

            // 5. Keyboard Shortcuts
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Delete' || e.key === 'Backspace') {
                    if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
                    if (this.selectedObject) {
                        e.preventDefault();
                        this.handleRemove();
                    }
                }
            });

            // Final UI Sync
            this.updateUI();
        },

        updateUI() {
            const key = this.activeCanvas;
            const enabled = this.canvasEnabled[key];
            const hasImg = this.canvasImages[key] !== null;

            // 1. Tabs
            document.querySelectorAll('.thumb-nav-item').forEach(el => {
                el.classList.toggle('active', el.dataset.key === key);
            });

            // 2. Canvas Visibility
            Object.keys(this.imageTypes).forEach(k => {
                const el = document.getElementById('canvas-wrapper-' + k);
                if (k === key) {
                    el.style.position = 'relative';
                    el.style.visibility = 'visible';
                    el.style.pointerEvents = 'auto';
                    el.style.zIndex = '1';
                } else {
                    el.style.position = 'absolute';
                    el.style.visibility = 'hidden';
                    el.style.pointerEvents = 'none';
                    el.style.zIndex = '-1';
                }
            });

            // 3. Status Card
            const statusCard = document.getElementById('status-card');
            const statusIconBg = document.getElementById('status-icon-bg');
            const statusBadge = document.getElementById('status-badge');
            const statusLabel = document.getElementById('status-label');

            if (enabled) {
                statusCard.className = 'border-2 rounded-2xl p-4 flex items-center h-full bg-gradient-to-r from-brand-50 to-sky-50 border-brand-100';
                statusIconBg.className = 'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-brand-500';
                statusBadge.className = 'text-[9px] font-bold uppercase tracking-wider text-brand-700';
                statusBadge.textContent = 'Editing';
                statusLabel.className = 'text-xs font-black truncate text-brand-900';
            } else {
                statusCard.className = 'border-2 rounded-2xl p-4 flex items-center h-full bg-slate-50 border-slate-200';
                statusIconBg.className = 'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-slate-300';
                statusBadge.className = 'text-[9px] font-bold uppercase tracking-wider text-slate-500';
                statusBadge.textContent = 'Locked';
                statusLabel.className = 'text-xs font-black truncate text-slate-600';
            }
            statusLabel.textContent = this.imageTypes[key]?.label || 'Layer';

            // 4. Upload Area
            const uploadArea = document.getElementById('upload-area');
            const uploadZone = document.getElementById('upload-zone');
            const uploadIconBg = document.getElementById('upload-icon-bg');
            const uploadText = document.getElementById('upload-text');

            uploadArea.classList.toggle('hidden', !enabled);
            if (enabled) {
                uploadZone.classList.toggle('has-image', hasImg);
                if (hasImg) {
                    uploadIconBg.className = 'w-9 h-9 rounded-xl flex items-center justify-center shadow-sm shrink-0 bg-emerald-100 text-emerald-600';
                    uploadText.textContent = 'Uploaded';
                } else {
                    uploadIconBg.className = 'w-9 h-9 rounded-xl flex items-center justify-center shadow-sm shrink-0 bg-white text-slate-400';
                    uploadText.textContent = 'Upload';
                }
            }

            // 5. Toolbars
            document.getElementById('zoom-control').classList.toggle('hidden', !enabled || !hasImg);
            document.getElementById('text-toolbar').classList.toggle('hidden', !enabled);

            // 6. Selection Sync
            this._syncToolbarToSelection(this.selectedObject);

            // 7. Remove Button
            const removeBtn = document.getElementById('remove-btn');
            const removeBtnText = document.getElementById('remove-btn-text');
            const showRemove = enabled && (hasImg || this.selectedObject);
            removeBtn.classList.toggle('hidden', !showRemove);
            if (showRemove) {
                removeBtnText.textContent = this.selectedObject ? 'Remove Selected Text' : 'Remove Photo';
            }

            // 8. Refresh Icons
            if (window.lucide) window.lucide.createIcons();
        },

        _initAllCanvases() {
            const containerEl = document.getElementById('canvas-container');
            if (!containerEl) return;
            const displayWidth = containerEl.offsetWidth;

            Object.keys(this.imageTypes).forEach(key => {
                const canvasEl = document.getElementById('canvas-' + key);
                if (!canvasEl) return;

                const config = this.allMaskData[key] || {};
                const isEditable = this.canvasEnabled[key];

                const isPortrait = <?php echo ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false'; ?>;
                const adminW = config.canvasWidth || (isPortrait ? 400 : 560);
                const adminH = config.canvasHeight || (isPortrait ? 560 : 400);

                const scaleFactor = displayWidth / adminW;
                const displayHeight = Math.round(adminH * scaleFactor);

                if (key === this.activeCanvas || !containerEl.style.height) {
                    containerEl.style.height = displayHeight + 'px';
                }

                const fc = new fabric.Canvas('canvas-' + key, {
                    width: displayWidth,
                    height: displayHeight,
                    backgroundColor: null,
                    selection: false,
                    preserveObjectStacking: true,
                    enableRetinaScaling: false,
                    imageSmoothingEnabled: false, // Sharper edges
                    allowTouchScrolling: true // Allow browser touch scrolling when not dragging objects
                });

                this.canvases[key] = {
                    fabricCanvas: fc,
                    imgObj: null,
                    scaleFactor: scaleFactor,
                    adminW: adminW,
                    adminH: adminH
                };

                const url = this.imageTypes[key]?.url;
                if (url) {
                    fabric.Image.fromURL(url, img => {
                        img.set({
                            left: -1, // 1px bleed
                            top: -1,
                            scaleX: (displayWidth + 2) / img.width,
                            scaleY: (displayHeight + 2) / img.height,
                            selectable: false,
                            evented: false,
                        });
                        fc.setBackgroundImage(img, fc.requestRenderAll.bind(fc));
                    }, {
                        crossOrigin: 'anonymous'
                    });
                }

                // --- Add Mask Guide (Visual Only) ---
                const firstKey = Object.keys(this.imageTypes)[0];
                const mData = this.allMaskData[key] || {};
                const masks = mData.masks || this.allMaskData.masks;
                if (key === firstKey && Array.isArray(masks) && masks.length > 0) {
                    const m = masks[0];
                    const guide = this._createMaskObject(m, scaleFactor, {
                        fill: 'transparent',
                        stroke: 'rgba(0, 80, 220, 0.5)', // Solid border color
                        strokeWidth: 1,
                        selectable: false,
                        evented: false,
                        name: 'mask_guide'
                    });
                    if (guide) {
                        fc.add(guide);
                        this.canvases[key].maskGuide = guide;
                    }
                }

                fc.on('mouse:down', () => {
                    fc.calcOffset();
                });
                fc.on('mouse:up', () => {
                    fc.isDragging = false;
                    fc.calcOffset();
                });
                fc.on('mouse:out', () => {
                    fc.isDragging = false;
                    fc.calcOffset();
                });

                fc.on('selection:created', (e) => {
                    this.selectedObject = e.selected[0];
                    this.updateUI();
                });
                fc.on('selection:updated', (e) => {
                    this.selectedObject = e.selected[0];
                    this.updateUI();
                });
                fc.on('selection:cleared', () => {
                    if (this.activeCanvas === key) {
                        this.selectedObject = null;
                        this.updateUI();
                    }
                });

                fc.on('object:modified', () => this._saveCanvasState(key));
                fc.on('object:added', () => this._saveCanvasState(key));
                fc.on('object:removed', () => this._saveCanvasState(key));


                fc.on('object:scaling', (e) => {
                    if (e.target._isUserImage) {
                        this.imgScales[key] = e.target.scaleX;
                        const slider = document.getElementById('zoom-slider');
                        if (slider) slider.value = e.target.scaleX;
                    }
                    this._saveCanvasState(key);
                });

                fc.on('object:moving', (e) => {
                    this.containObject(e.target);
                    this._saveCanvasState(key);
                });

                // object:scaled event bilkul hata do ya empty rakho
                fc.on('object:scaled', (e) => {
                    this._saveCanvasState(key);
                });

                // --- Mobile Pinch to Zoom & Rotate ---
                let initialPinchDistance = null;
                let initialPinchAngle = null;
                let initialObjScale = null;
                let initialObjAngle = null;

                fc.upperCanvasEl.addEventListener('touchstart', (e) => {
                    if (e.touches.length === 2) {
                        const obj = fc.getActiveObject();
                        if (obj && (obj._isUserImage || obj._isUserText)) {
                            e.preventDefault();
                            const dx = e.touches[0].clientX - e.touches[1].clientX;
                            const dy = e.touches[0].clientY - e.touches[1].clientY;
                            initialPinchDistance = Math.hypot(dx, dy);
                            initialPinchAngle = Math.atan2(dy, dx) * 180 / Math.PI;
                            initialObjScale = obj.scaleX;
                            initialObjAngle = obj.angle;
                        }
                    }
                }, {
                    passive: false
                });

                fc.upperCanvasEl.addEventListener('touchmove', (e) => {
                    if (e.touches.length === 2 && initialPinchDistance !== null) {
                        const obj = fc.getActiveObject();
                        if (obj && (obj._isUserImage || obj._isUserText)) {
                            e.preventDefault();
                            const dx = e.touches[0].clientX - e.touches[1].clientX;
                            const dy = e.touches[0].clientY - e.touches[1].clientY;
                            const currentDistance = Math.hypot(dx, dy);
                            const currentPinchAngle = Math.atan2(dy, dx) * 180 / Math.PI;

                            const scaleFactor = currentDistance / initialPinchDistance;
                            let angleDelta = currentPinchAngle - initialPinchAngle;

                            // Normalize angle delta to prevent jumping
                            if (angleDelta > 180) angleDelta -= 360;
                            if (angleDelta < -180) angleDelta += 360;

                            const newScale = Math.max(0.05, initialObjScale * scaleFactor);

                            obj.set({
                                scaleX: newScale,
                                scaleY: newScale,
                                angle: initialObjAngle + angleDelta
                            });
                            obj.setCoords();
                            fc.requestRenderAll();

                            if (obj._isUserImage) {
                                this.imgScales[key] = newScale;
                                const slider = document.getElementById('zoom-slider');
                                if (slider) slider.value = newScale;
                            }
                        }
                    }
                }, {
                    passive: false
                });

                fc.upperCanvasEl.addEventListener('touchend', (e) => {
                    if (e.touches.length < 2 && initialPinchDistance !== null) {
                        initialPinchDistance = null;
                        initialPinchAngle = null;
                        this._saveCanvasState(key);
                    }
                });

                this._loadCanvasState(key);
                fc.renderAll();
            });
        },

        _saveCanvasState(key) {
            const cv = this.canvases[key];
            if (!cv) return;
            const fc = cv.fabricCanvas;
            const objects = fc.getObjects().filter(obj => obj._isUserImage || obj._isUserText);
            const data = {
                objects: objects.map(obj => obj.toObject(['_isUserImage', '_isUserText', '_uploadId'])),
                imgScale: this.imgScales[key],
                uploadId: this.uploadIds[key]
            };
            localStorage.setItem(`qrinto_design_v1_${this.productId}_${key}`, JSON.stringify(data));
        },

        _loadCanvasState(key) {
            const saved = localStorage.getItem(`qrinto_design_v1_${this.productId}_${key}`);
            if (!saved) return;
            try {
                const data = JSON.parse(saved);
                const cv = this.canvases[key];
                const fc = cv.fabricCanvas;
                this.imgScales[key] = data.imgScale || 1;
                this.uploadIds[key] = data.uploadId;

                if (data.objects && data.objects.length > 0) {
                    fabric.util.enlivenObjects(data.objects, (enlivenedObjects) => {
                        enlivenedObjects.forEach(obj => {
                            if (obj._isUserImage) {
                                obj.set({
                                    selectable: true,
                                    evented: true,
                                    hasControls: true,
                                    lockScalingFlip: true,
                                    uniformScaling: true,
                                    cornerSize: 12,
                                    transparentCorners: false,
                                    borderColor: '#378ADD',
                                    cornerColor: '#378ADD',
                                    cornerStyle: 'circle'
                                });
                                cv.imgObj = obj;
                                this.canvasImages[key] = true;
                            }
                            if (obj._isUserText) {
                                obj.set({
                                    selectable: true,
                                    evented: true,
                                    hasControls: true,
                                    lockScalingFlip: true,
                                    uniformScaling: true,
                                    cornerSize: 10,
                                    transparentCorners: false,
                                    borderColor: '#378ADD',
                                    cornerColor: '#378ADD',
                                    cornerStyle: 'circle'
                                });
                            }

                            // --- Apply Mask to First Canvas Only ---
                            const firstKey = Object.keys(this.imageTypes)[0];
                            const mData = this.allMaskData[key] || {};
                            const masks = mData.masks || this.allMaskData.masks;
                            if (key === firstKey && Array.isArray(masks) && masks.length > 0) {
                                const m = masks[0];
                                const sf = cv.scaleFactor;
                                const clipPath = this._createMaskObject(m, sf, {
                                    absolutePositioned: true,
                                    strokeWidth: 1, // Include border in clipping area
                                    stroke: 'black'
                                });
                                if (clipPath) obj.set('clipPath', clipPath);
                            }

                            fc.add(obj);
                        });
                        fc.renderAll();
                        fc.getObjects().forEach(o => o.setCoords());
                        fc.renderAll();
                        this.updateUI();
                    });
                }
            } catch (e) {
                console.error('Restore design error:', e);
            }
        },

        _syncToolbarToSelection(obj) {
            const textInput = document.getElementById('text-input');
            const clearBtn = document.getElementById('clear-text-btn');
            const addBtn = document.getElementById('add-text-btn');
            const editBadge = document.getElementById('editing-badge');

            if (!obj || !(obj.type === 'i-text' || obj.type === 'text' || obj.type === 'textbox')) {
                textInput.placeholder = 'Add text...';
                clearBtn.classList.add('hidden');
                addBtn.classList.remove('hidden');
                editBadge.classList.add('hidden');
                return;
            }

            if (textInput.value !== obj.text) {
                textInput.value = obj.text;
            }
            document.getElementById('font-family-select').value = obj.fontFamily;
            document.getElementById('font-size-select').value = obj.fontSize.toString();
            document.getElementById('text-color-input').value = obj.fill;
            document.getElementById('text-color-preview').style.background = obj.fill;
            document.getElementById('text-align-select').value = obj.textAlign || 'center';

            clearBtn.classList.remove('hidden');
            addBtn.classList.add('hidden');
            editBadge.classList.remove('hidden');
        },

        async _updateSelectedStyle(property, value) {
            if (property === 'fill') document.getElementById('text-color-preview').style.background = value;
            if (!this.selectedObject) return;
            const obj = this.selectedObject;

            if (property === 'fontFamily') {
                try {
                    await document.fonts.load(`1em "${value}"`);
                } catch (e) {
                    console.warn("Font load failed:", value);
                }
            }

            obj.set(property, value);

            if (obj.type === 'textbox' && (property === 'fontSize' || property === 'fontFamily' || property === 'fontWeight')) {
                const cv = this.canvases[this.activeCanvas];
                const maxScreenW = cv.fabricCanvas.width * 0.9;
                const maxUnscaledW = maxScreenW / obj.scaleX;

                const temp = new fabric.Text(obj.text, {
                    fontSize: obj.fontSize,
                    fontFamily: obj.fontFamily,
                    fontWeight: obj.fontWeight,
                    fontStyle: obj.fontStyle
                });
                const desiredWidth = temp.width + (obj.fontSize * 0.2);
                obj.set('width', Math.min(desiredWidth, maxUnscaledW));
            }

            const cv = this.canvases[this.activeCanvas];
            if (cv) {
                cv.fabricCanvas.requestRenderAll();
                this._saveCanvasState(this.activeCanvas);
            }
        },

        onTextInputChange(value) {
            if (this.selectedObject && (this.selectedObject.type === 'i-text' || this.selectedObject.type === 'text' || this.selectedObject.type === 'textbox')) {
                const obj = this.selectedObject;
                obj.set('text', value);

                if (obj.type === 'textbox') {
                    const cv = this.canvases[this.activeCanvas];
                    const maxScreenW = cv.fabricCanvas.width * 0.9;
                    const maxUnscaledW = maxScreenW / obj.scaleX;

                    const temp = new fabric.Text(value, {
                        fontSize: obj.fontSize,
                        fontFamily: obj.fontFamily,
                        fontWeight: obj.fontWeight,
                        fontStyle: obj.fontStyle
                    });
                    const desiredWidth = temp.width + (obj.fontSize * 0.2);
                    obj.set('width', Math.min(desiredWidth, maxUnscaledW));
                }

                this.canvases[this.activeCanvas]?.fabricCanvas?.requestRenderAll();
                this._saveCanvasState(this.activeCanvas);

                if (value.trim().length === 0) {
                    this.handleRemove();
                }
            } else if (value.trim().length > 0) {
                // To prevent race conditions while typing fast, 
                // we check if we already have a text object we just added.
                this.addText(true);
            }
        },

        clearSelection() {
            const cv = this.canvases[this.activeCanvas];
            if (cv) {
                cv.fabricCanvas.discardActiveObject().renderAll();
                this.selectedObject = null;
                document.getElementById('text-input').value = '';
                this.updateUI();
            }
        },

        switchCanvas(key) {
            const currentCv = this.canvases[this.activeCanvas];
            if (currentCv && currentCv.fabricCanvas) {
                currentCv.fabricCanvas.discardActiveObject();
                currentCv.fabricCanvas.renderAll();
            }
            this.selectedObject = null;
            document.getElementById('text-input').value = '';
            this.activeCanvas = key;

            this.updateUI();
            setTimeout(() => {
                const newCv = this.canvases[key];
                if (newCv && newCv.fabricCanvas) {
                    newCv.fabricCanvas.calcOffset();
                    newCv.fabricCanvas.discardActiveObject().renderAll();
                }
            }, 50);
        },

        _addImageToCanvas(key, url) {
            if (this.canvasEnabled[key] === false) return;
            const cv = this.canvases[key];
            if (!cv) return;
            if (cv.imgObj) cv.fabricCanvas.remove(cv.imgObj);

            fabric.Image.fromURL(url, img => {
                const canvasW = cv.fabricCanvas.width;
                const canvasH = cv.fabricCanvas.height;

                // Use "Cover" scaling to ensure no gaps
                const sX = canvasW / img.width;
                const sY = canvasH / img.height;
                const s = Math.max(sX, sY);

                img.set({
                    left: (canvasW - img.width * s) / 2,
                    top: (canvasH - img.height * s) / 2,
                    scaleX: s,
                    scaleY: s,
                    cornerStyle: 'circle',
                    cornerSize: 12,
                    transparentCorners: false,
                    borderColor: '#378ADD',
                    cornerColor: '#378ADD',
                    hasControls: true,
                    hasBorders: true,
                    selectable: true,
                    _isUserImage: true,
                    objectCaching: true,
                    lockScalingFlip: true,
                    uniformScaling: true,
                    centeredScaling: false
                });

                // --- Apply Mask to First Canvas Only ---
                const firstKey = Object.keys(this.imageTypes)[0];
                const mData = this.allMaskData[key] || {};
                const masks = mData.masks || this.allMaskData.masks;
                if (key === firstKey && Array.isArray(masks) && masks.length > 0) {
                    const m = masks[0];
                    const sf = cv.scaleFactor;
                    const clipPath = this._createMaskObject(m, sf, {
                        absolutePositioned: true,
                        strokeWidth: 1, // Include border in clipping area
                        stroke: 'black'
                    });
                    if (clipPath) img.set('clipPath', clipPath);
                }

                cv.fabricCanvas.add(img);
                if (cv.maskGuide) cv.maskGuide.bringToFront();
                cv.fabricCanvas.setActiveObject(img);
                cv.fabricCanvas.renderAll();
                img.setCoords();
                cv.imgObj = img;
                this.imgScales[key] = s;
                this.updateUI();
            }, {
                crossOrigin: 'anonymous'
            });
        },

        async handleFileUpload(input) {
            const file = input.files[0];
            if (!file || !this.activeCanvas) return;
            const key = this.activeCanvas;
            if (this.canvasEnabled[key] === false) return;

            this.isUploading = true;
            this.updateUI();

            try {
                // 1. Process & Optimize Image (Resize & Convert to WebP)
                const optimized = await this._processImage(file);

                // 2. Display on Fabric Canvas
                this.canvasImages[key] = optimized.dataUrl;
                this._addImageToCanvas(key, optimized.dataUrl);

                // 3. Upload to Server
                const fd = new FormData();
                fd.append('image', optimized.blob, 'upload.webp');
                fd.append('_token', '<?php echo csrf_token(); ?>');

                const res = await fetch('<?php echo route("flow.upload"); ?>', {
                    method: 'POST',
                    body: fd
                });
                const dat = await res.json();
                if (dat.success) this.uploadIds[key] = dat.upload_id;

            } catch (err) {
                console.error('Image processing/upload error:', err);
            } finally {
                this.isUploading = false;
                input.value = '';
                this.updateUI();
            }
        },

        _processImage(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDim = 1200; // High quality but manageable

                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height *= maxDim / width;
                            width = maxDim;
                        } else {
                            width *= maxDim / height;
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const dataUrl = canvas.toDataURL('image/webp', 0.85);
                    canvas.toBlob((blob) => {
                        resolve({
                            blob,
                            dataUrl
                        });
                    }, 'image/webp', 0.85);
                };
                img.onerror = reject;
                img.src = URL.createObjectURL(file);
            });
        },

        updateImageScale(val) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv?.imgObj) return;
            this.imgScales[this.activeCanvas] = val;
            cv.imgObj.set({
                scaleX: parseFloat(val),
                scaleY: parseFloat(val)
            });
            cv.imgObj.setCoords();
            cv.fabricCanvas.requestRenderAll();
        },

        addText(isLiveType = false) {
            const key = this.activeCanvas;
            if (this.canvasEnabled[key] === false) return;
            const cv = this.canvases[key];
            const textVal = document.getElementById('text-input').value;
            if (!cv || !textVal.trim()) return;

            const fontSize = parseInt(document.getElementById('font-size-select').value);
            const fontFamily = document.getElementById('font-family-select').value;
            const color = document.getElementById('text-color-input').value;
            const align = document.getElementById('text-align-select').value;

            // 1. Create the Textbox immediately (synchronously) to prevent duplication
            const t = new fabric.Textbox(textVal, {
                left: cv.fabricCanvas.width * 0.1,
                top: cv.fabricCanvas.height / 3,
                width: cv.fabricCanvas.width * 0.8,
                fontSize: fontSize,
                fontFamily: fontFamily,
                fill: color,
                textAlign: align,
                _isUserText: true,
                objectCaching: false,
                cornerSize: 12,
                transparentCorners: false,
                borderColor: '#378ADD',
                cornerColor: '#378ADD',
                cornerStyle: 'circle',
                lockScalingFlip: true,
                hasRotatingPoint: true
            });

            // 2. Set as selected immediately! This prevents the "new layer on every character" bug.
            this.selectedObject = t;

            // --- Apply Mask to First Canvas Only ---
            const firstKey = Object.keys(this.imageTypes)[0];
            const mData = this.allMaskData[key] || {};
            const masks = mData.masks || this.allMaskData.masks;
            if (key === firstKey && Array.isArray(masks) && masks.length > 0) {
                const m = masks[0];
                const sf = cv.scaleFactor;
                const clipPath = this._createMaskObject(m, sf, {
                    absolutePositioned: true,
                    strokeWidth: 1, // Include border in clipping area
                    stroke: 'black'
                });
                if (clipPath) t.set('clipPath', clipPath);
            }

            cv.fabricCanvas.add(t);
            if (cv.maskGuide) cv.maskGuide.bringToFront();
            t.setCoords();
            cv.fabricCanvas.setActiveObject(t);
            cv.fabricCanvas.renderAll();

            // 3. Background Font Load - once loaded, refresh the canvas
            document.fonts.load(`${fontSize}px "${fontFamily}"`).then(() => {
                if (t.canvas) {
                    t.set('fontFamily', fontFamily);
                    t.setCoords();
                    t.canvas.requestRenderAll();
                }
            }).catch(e => console.warn("Font load error:", e));

            this.updateUI();
        },

        handleRemove() {
            const cv = this.canvases[this.activeCanvas];
            if (!cv) return;
            if (this.selectedObject) {
                cv.fabricCanvas.remove(this.selectedObject);
                cv.fabricCanvas.discardActiveObject();
                this.selectedObject = null;
                document.getElementById('text-input').value = '';
            } else if (cv.imgObj) {
                cv.fabricCanvas.remove(cv.imgObj);
                cv.imgObj = null;
                this.canvasImages[this.activeCanvas] = null;
                this.uploadIds[this.activeCanvas] = null;
            }
            cv.fabricCanvas.renderAll();
            this.updateUI();
        },

        submitAllCanvases() {
            if (this.isSavingComposite) return;
            const hasUpload = Object.values(this.uploadIds).some(id => id !== null) ||
                Object.keys(this.canvases).some(k => this.canvases[k].fabricCanvas.getObjects().some(o => o._isUserText));

            if (!hasUpload) {
                document.getElementById('upload_ids_field').value = JSON.stringify({});
                document.getElementById('checkout-form').submit();
                return;
            }

            this.isSavingComposite = true;
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="animate-spin" data-lucide="loader-2"></i> Saving...';
            lucide.createIcons();

            const ids = {};
            const uploadPromises = Object.keys(this.canvases).map(async key => {
                const cv = this.canvases[key];
                if (!cv || !this.canvasEnabled[key]) return;
                const hasEdit = this.canvasImages[key] !== null || cv.fabricCanvas.getObjects().some(o => o._isUserText);
                if (!hasEdit) return;

                cv.fabricCanvas.discardActiveObject();

                // Hide guide
                const guide = cv.maskGuide;
                if (guide) {
                    guide.set('visible', false);
                    cv.fabricCanvas.renderAll();
                }

                const b64 = cv.fabricCanvas.toDataURL({
                    format: 'jpeg',
                    quality: 0.9,
                    multiplier: 2
                });

                // Restore Guide Visibility
                if (guide) {
                    guide.set('visible', true);
                    cv.fabricCanvas.renderAll();
                }

                const res = await fetch('<?php echo route("flow.upload_composite"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
                    },
                    body: JSON.stringify({
                        image_data: b64,
                        canvas_key: key
                    }),
                });
                const dat = await res.json();
                if (dat.success) ids[key] = dat.upload_id;
            });

            Promise.all(uploadPromises).then(() => {
                document.getElementById('upload_ids_field').value = JSON.stringify(ids);
                document.getElementById('checkout-form').submit();
            }).catch(err => {
                console.error(err);
                this.isSavingComposite = false;
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="shopping-cart"></i> Confirm Design & Continue';
                lucide.createIcons();
            });
        },

        containObject(obj) {
            if (!obj || !obj.canvas || !obj._isUserImage) return;
            const fc = obj.canvas;

            // ✅ Sirf movement constrain — koi size check nahi
            obj.setCoords();
            const br = obj.getBoundingRect();

            // Canvas boundary se bahar na jaye (optional — yeh bhi hatana ho toh hata do)
            if (br.left > fc.width) obj.left = fc.width - 10;
            if (br.top > fc.height) obj.top = fc.height - 10;
            if (br.left + br.width < 0) obj.left = -(br.width - 10);
            if (br.top + br.height < 0) obj.top = -(br.height - 10);

            obj.setCoords();
        },

        _debounce(fn, delay) {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), delay);
            };
        },

        // ── Mask Helper ───────────────────────────────────────────────────────
        _createMaskObject(m, sf, extraProps = {}) {
            if (!m) return null;
            const type = m.type || 'rectangle';
            const base = {
                left: m.left * sf,
                top: m.top * sf,
                scaleX: (m.scaleX || 1) * sf,
                scaleY: (m.scaleY || 1) * sf,
                angle: m.angle || 0,
                originX: 'left',
                originY: 'top',
                ...extraProps
            };

            switch (type) {
                case 'square':
                case 'rectangle':
                case 'diamond':
                    return new fabric.Rect({
                        ...base,
                        width: m.width,
                        height: m.height
                    });
                case 'circle':
                    return new fabric.Circle({
                        ...base,
                        radius: m.radius
                    });
                case 'ellipse':
                case 'oval':
                    return new fabric.Ellipse({
                        ...base,
                        rx: m.rx,
                        ry: m.ry
                    });
                case 'triangle':
                    return new fabric.Triangle({
                        ...base,
                        width: m.width,
                        height: m.height
                    });
                case 'pentagon':
                    return new fabric.Polygon(this._getPolygonPoints(5, 55), base);
                case 'hexagon':
                    return new fabric.Polygon(this._getPolygonPoints(6, 55), base);
                case 'star':
                    return new fabric.Polygon(this._getStarPoints(5, 55, 25), base);
                case 'heart':
                    return new fabric.Path(this._getHeartPath(), base);
                case 'arch':
                    return new fabric.Path(this._getArchPath(), base);
                case 'custom_polygon':
                    return new fabric.Polygon(m.points || [], base);
                case 'text':
                    return new fabric.IText(m.text || 'Text', {
                        ...base,
                        fontSize: (m.fontSize || 24) * sf,
                        fontFamily: m.fontFamily || 'Arial',
                        fontWeight: m.fontWeight || 'normal',
                        fontStyle: m.fontStyle || 'normal',
                    });
                default:
                    return new fabric.Rect({
                        ...base,
                        width: m.width || 100,
                        height: m.height || 100
                    });
            }
        },

        _getPolygonPoints(sides, r) {
            return Array.from({
                length: sides
            }, (_, i) => {
                const a = (Math.PI * 2 * i / sides) - Math.PI / 2;
                return {
                    x: r + r * Math.cos(a),
                    y: r + r * Math.sin(a)
                };
            });
        },
        _getStarPoints(pts, outer, inner) {
            return Array.from({
                length: pts * 2
            }, (_, i) => {
                const r = i % 2 === 0 ? outer : inner;
                const a = (Math.PI * i / pts) - Math.PI / 2;
                return {
                    x: outer + r * Math.cos(a),
                    y: outer + r * Math.sin(a)
                };
            });
        },
        _getHeartPath() {
            return 'M 50 90 C 25 70 0 50 0 30 C 0 12 12 0 25 0 C 35 0 45 7 50 18 C 55 7 65 0 75 0 C 88 0 100 12 100 30 C 100 50 75 70 50 90 Z';
        },
        _getArchPath() {
            return 'M 10 120 L 10 50 C 10 15 30 0 60 0 C 90 0 110 15 110 50 L 110 120 Z';
        },

        _resizeAllCanvases() {
            const cont = document.getElementById('canvas-container');
            if (!cont) return;
            const newW = cont.offsetWidth;
            Object.keys(this.canvases).forEach(key => {
                const cv = this.canvases[key];
                if (!cv?.fabricCanvas) return;
                const oldW = cv.fabricCanvas.width;
                const ratio = newW / oldW;
                const newH = Math.round(cv.adminH * (newW / cv.adminW));
                cv.fabricCanvas.setDimensions({
                    width: newW,
                    height: newH
                });
                const bg = cv.fabricCanvas.backgroundImage;
                if (bg) {
                    bg.left = -1;
                    bg.top = -1;
                    bg.scaleX = (newW + 2) / bg.width;
                    bg.scaleY = (newH + 2) / bg.height;
                }
                cv.fabricCanvas.getObjects().forEach(o => {
                    o.left *= ratio;
                    o.top *= ratio;
                    o.scaleX *= ratio;
                    o.scaleY *= ratio;

                    // Update Mask on Resize
                    const firstKey = Object.keys(this.imageTypes)[0];
                    const mData = this.allMaskData[key] || {};
                    const masks = mData.masks || this.allMaskData.masks;
                    if (key === firstKey && Array.isArray(masks) && masks.length > 0 && o.clipPath) {
                        const m = masks[0];
                        const newSf = cv.fabricCanvas.width / cv.adminW;

                        // Recreate clipPath using new scale factor to perfectly scale custom polygons and other shapes!
                        const newClip = this._createMaskObject(m, newSf, {
                            absolutePositioned: true,
                            strokeWidth: 0
                        });
                        if (newClip) {
                            newClip.canvas = cv.fabricCanvas;
                            o.set('clipPath', newClip);
                        }
                    }

                    o.setCoords();
                });

                // Update Mask Guide on Resize
                if (cv.maskGuide) {
                    const mData = this.allMaskData[key] || {};
                    const masks = mData.masks || this.allMaskData.masks;
                    const m = masks[0];
                    const newSf = cv.fabricCanvas.width / cv.adminW;

                    // Recreate mask guide to perfectly scale custom polygons and other shapes!
                    cv.fabricCanvas.remove(cv.maskGuide);
                    const guide = this._createMaskObject(m, newSf, {
                        fill: 'transparent',
                        stroke: 'rgba(0, 80, 220, 0.5)',
                        strokeWidth: 1,
                        selectable: false,
                        evented: false,
                        name: 'mask_guide'
                    });
                    if (guide) {
                        cv.fabricCanvas.add(guide);
                        cv.maskGuide = guide;
                        guide.bringToFront();
                    }
                }

                cv.fabricCanvas.renderAll();
            });
        },

        clearAll() {
            if (!confirm('Are you sure you want to clear all designs?')) return;
            Object.keys(this.canvases).forEach(key => {
                const cv = this.canvases[key];
                if (!cv?.fabricCanvas) return;
                const objects = cv.fabricCanvas.getObjects();
                // Filter out background image and mask guide by identifying user objects
                objects.forEach(o => {
                    if (o._isUserImage || o._isUserText) {
                        cv.fabricCanvas.remove(o);
                    }
                });
                cv.fabricCanvas.renderAll();
                this._saveCanvasState(key);
            });
            this.updateUI();
            alert('All designs cleared!');
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        customizer.init();
        // Global mouseup to release Fabric drag state if user releases mouse outside canvas
        window.addEventListener('mouseup', () => {
            Object.values(customizer.canvases).forEach(cv => {
                if (cv.fabricCanvas) cv.fabricCanvas.__onMouseUp(new Event('mouseup'));
            });
        });
    });
</script>
@endpush