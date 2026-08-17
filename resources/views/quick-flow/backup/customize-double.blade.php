{{-- ═══════════════════════════════════════════════════════════════════════
     KEY RATIO FIX:
     Admin saves canvasWidth=560, canvasHeight=400 (7:5), aspectRatioW=7, aspectRatioH=5.
     Frontend reads those values → computes scaleFactor = displayWidth / adminCanvasW
     → displayHeight = adminCanvasH * scaleFactor   (preserves 7:5 exactly)
     Mask coords: displayX = mask.left * scaleFactor,  displayW = mask.width * mask.scaleX * scaleFactor

     READY-MADE TEMPLATES:
     A template = a set of layers (text, raster images, and/or SVGs) positioned as
     canvas fractions so they scale across screens.
     Layer flags: _isTemplateText | _isTemplateImage | _isTemplateSvg (template content)
                  _isUserText (customer text)  |  _isUserImage (customer uploaded photo)
     Z-order (bottom→top): _isUserImage → _isTemplateImage → _isTemplateSvg
                            → _isTemplateText → _isUserText → maskGuide
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.quick-flow')
@section('title', 'Customize Your ' . $product->name)
@section('header_title', 'Customize')

@section('breadcrumbs')
    @if ($product->category?->parent)
        <i data-lucide="chevron-right" class="w-4 h-4 text-surface-300 shrink-0"></i>
        <a href="{{ route('flow.category', $product->category->parent->slug) }}"
            class="text-surface-600 hover:text-mobile-500 font-medium truncate max-w-[100px] transition-colors">{{ $product->category->parent->name }}</a>
    @endif
    @if ($product->category)
        <i data-lucide="chevron-right" class="w-4 h-4 text-surface-300 shrink-0"></i>
        <a href="{{ route('flow.category', $product->category->slug) }}"
            class="text-surface-600 hover:text-mobile-500 font-medium truncate max-w-[100px] transition-colors">{{ $product->category->name }}</a>
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
            border-color: #38bdf8;
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
            border-color: #38bdf8;
            background: #e0f2fe;
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
            border-color: #38bdf8;
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
                max-width: 400px;
                margin: auto;
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
                padding-bottom: 110px !important;
            }

            .text-toolbar {
                padding: 8px;
                gap: 4px;
            }

            .text-toolbar select {
                max-width: 80px;
            }
        }

        /* Spring-loaded slide-up bottom drawer */
        #text-drawer {
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        #drawer-backdrop {
            transition: opacity 0.3s ease-in-out;
        }

        /* pb-safe handles modern iOS bezel-less bottom safe area */
        .pb-safe {
            padding-bottom: env(safe-area-inset-bottom, 16px);
        }

        .canvas-wrapper {
            background: #f1f5f9;
            border-radius: 1rem;
            touch-action: none;
        }

        /* ── Floating Text Layer Action Icons ── */
        .text-layer-actions {
            position: absolute;
            z-index: 150;
            pointer-events: none;
            display: none;
        }

        .text-layer-actions.visible {
            display: flex;
            gap: 6px;
            pointer-events: auto;
        }

        .text-layer-action-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2), 0 0 0 1.5px rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            padding: 0;
            outline: none;
        }

        .text-layer-action-btn svg {
            width: 14px;
            height: 14px;
            pointer-events: none;
        }

        .text-layer-action-btn.edit-btn {
            background: #3b82f6;
            color: white;
        }

        .text-layer-action-btn.edit-btn:hover {
            background: #2563eb;
            transform: scale(1.15);
        }

        .text-layer-action-btn.edit-btn:active {
            transform: scale(0.95);
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

        /* ── Ready-made Template Strip ── */
        .template-strip {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 6px 4px 2px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            flex-wrap: wrap;
        }

        .template-strip::-webkit-scrollbar {
            display: none;
        }

        .template-chip {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 9999px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap;
        }

        .template-chip:hover {
            background: #e2e8f0;
        }

        .template-chip:active {
            transform: scale(.95);
        }

        .template-chip i {
            width: 14px;
            height: 14px;
            color: #38bdf8;
        }

        .template-chip-label {
            flex: 0 0 auto;
            align-self: center;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #000000;
            padding-right: 4px;
            white-space: nowrap;
            width: 100%;
            display: block;
            padding: 13px 5px;
            border-bottom: 1px solid;
        }

        /* ── Template Category Filter ── */
        .template-cat-filter {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 4px 4px 2px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .template-cat-filter::-webkit-scrollbar {
            display: none;
        }

        .template-cat-chip {
            flex: 0 0 auto;
            padding: 5px 13px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #0ea5e9;
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap;
        }

        .template-cat-chip:hover {
            background: #e2e8f0;
        }

        .template-cat-chip.active {
            background: #38bdf8;
            border-color: #38bdf8;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    @php
        $imageTypes = [];
        $slots = [
            'frame_image' => 'Page 1',
            'sample_image' => 'Page 2',
        ];

        $galleryImages = $product->images->values();
        $galleryIndex = 0;
        $fallbackUrl =
            $product->featured_image_url ??
            ($product->sample_image_url ??
                ($product->frame_image_url ??
                    ($product->background_image_url ??
                        'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="%2394a3b8" stroke-width="1.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/><circle cx="9" cy="9" r="2"/></svg>')));

        foreach ($slots as $field => $label) {
            $url = $product->{$field . '_url'};

            // If field is empty, try to take from gallery
            if (!$url && isset($galleryImages[$galleryIndex])) {
                $url = \App\Models\Product::formatStorageUrl($galleryImages[$galleryIndex]->image_path);
                $galleryIndex++;
            }

            // If still empty, use fallback product URL
            if (!$url) {
                $url = $fallbackUrl;
            }

            $imageTypes[$field] = ['label' => $label, 'url' => $url];
        }

        $maskData = $product->mask_data ?? [];
        if (is_string($maskData)) {
            $maskData = json_decode($maskData, true) ?? [];
        }
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
            <p class="text-slate-500 font-medium text-xs  ">Customize each image by uploading photos and adding text.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 items-start">

            <!-- ═══ SECTION 1: Design & Preview Area ═══ -->
            <div class="space-y-1">
                <!-- Thumbnail Navigation -->
                <div class="thumb-nav" id="thumb-nav">
                    @foreach ($imageTypes as $key => $img)
                        @php
                            $config = $maskData[$key] ?? [];
                            $enabled = ($config['enabled'] ?? true) !== false;
                        @endphp
                        <div class="thumb-nav-item {{ !$enabled ? 'disabled-tab' : '' }}" data-key="{{ $key }}"
                            onclick="customizer.switchCanvas('{{ $key }}')">
                            <img src="{{ $img['url'] }}">
                            <span class="thumb-label">Page {{ $loop->iteration }}</span>
                            @if (!$enabled)
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
                    @foreach ($imageTypes as $key => $img)
                        @php
                            $config = $maskData[$key] ?? [];
                            $enabled = ($config['enabled'] ?? true) !== false;
                        @endphp
                        <div id="canvas-wrapper-{{ $key }}" class="canvas-layer"
                            style="position:absolute;top:0;left:0;width:100%;visibility:hidden;pointer-events:none;z-index:-1;">
                            <canvas id="canvas-{{ $key }}"></canvas>
                            @if (!$enabled)
                                <div class="canvas-disabled-overlay"></div>
                            @endif
                        </div>
                    @endforeach

                    <!-- Floating Action Icon for Active Text Layer -->
                    <div id="text-layer-actions" class="text-layer-actions">
                        <button class="text-layer-action-btn edit-btn" id="text-action-edit" title="Edit Text">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                <path d="m15 5 4 4" />
                            </svg>
                        </button>
                    </div>
                </div>
                @if ($product->store_id)
                    @php
                        /** @var \App\Models\Store|null $reservedStore */
                        $reservedStore = $product->relationLoaded('store')
                            ? $product->store
                            : \App\Models\Store::find($product->store_id);

                        $isCanadianStore = $reservedStore?->isCanadian() ?? false;
                        $cadRate = \App\Services\CurrencyService::getRate(); // e.g. 1.38
                        $basePriceUsd = (float) $product->base_price;
                        $comparePriceUsd = (float) $product->compare_price;
                        $basePriceCad = round($basePriceUsd * $cadRate, 2);
                        $comparePriceCad = round($comparePriceUsd * $cadRate, 2);
                    @endphp

                    {{-- ── Store-Reserved Product Notice ─────────────────────── --}}
                    <div
                        class="flex items-start gap-2.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2.5 my-2 text-xs leading-relaxed shadow-sm">

                        {{-- Warning icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-3.5 w-3.5 shrink-0 fill-amber-500"
                            viewBox="0 0 512 512">
                            <path
                                d="M256 0c14.7 0 28.2 8.1 35.2 21l216 400c6.7 12.4 6.4 27.4-.8 39.5S486.1 480 472 480L40 480c-14.1 0-27.2-7.4-34.4-19.5s-7.5-27.1-.8-39.5l216-400c7-12.9 20.5-21 35.2-21zm0 352a32 32 0 1 0 0 64 32 32 0 1 0 0-64zm0-192c-18.2 0-32.7 15.5-31.4 33.7l7.4 104c.9 12.5 11.4 22.3 23.9 22.3 12.6 0 23-9.7 23.9-22.3l7.4-104c1.3-18.2-13.1-33.7-31.4-33.7z" />
                        </svg>

                        {{-- Notice body --}}
                        <div class="text-amber-800">
                            <p class="font-semibold">
                                Store-exclusive card
                                @if ($reservedStore)
                                    reserved for
                                    <span class="font-bold text-amber-900">{{ $reservedStore->store_name }}</span>
                                @endif
                            </p>
                            <p class="mt-0.5">
                                Pricing applied on
                                @if ($isCanadianStore)
                                    <span class="mx-1 text-amber-400">&bull;</span>
                                    @if ($comparePriceCad > 0 && $comparePriceCad > $basePriceCad)
                                        <s class="text-amber-400">C${{ number_format($comparePriceCad, 2) }}</s>
                                    @endif
                                    <strong class="text-amber-900">C${{ number_format($basePriceCad, 2) }} CAD</strong>
                                @else
                                    ${{ number_format($product->base_price, 2) }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endif

                <!-- ═══ Ready-made Template Strip ═══ -->
                <div class="pt-1">
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 px-1 mb-1">Templates
                    </div>
                    <!-- Category Filter -->
                    <div id="template-cat-filter" class="template-cat-filter mb-1"></div>
                    <!-- Template Chips -->
                    <div class="template-strip" id="template-strip"></div>
                </div>

            </div>

            <!-- ═══ SECTION 2: Premium Fixed Bottom Customizer Dock (Single Row Layout) ═══ -->
            <div data-tour="editor-toolbar"
                class="fixed max-w-md mx-auto bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-100 shadow-[0_-10px_35px_rgba(0,0,0,0.08)] px-4 py-3 pb-safe">
                <div class="flex items-center justify-between gap-3">

                    <!-- Left Half: Edit Zone Tools (Icons) -->
                    <div class="flex items-center gap-2">
                        <!-- Photo Upload Button -->
                        <div id="upload-area" class="relative">
                            <label class="block cursor-pointer">
                                <div id="upload-zone"
                                    class="w-11 h-11 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 active:scale-95 transition flex items-center justify-center shadow-sm">
                                    <div id="upload-icon-bg" class="text-slate-600 flex items-center justify-center">
                                        <i id="upload-icon" data-lucide="camera" class="w-5 h-5"></i>
                                    </div>
                                    <span id="upload-text" class="hidden">Upload</span>
                                </div>
                                <input type="file" onchange="customizer.handleFileUpload(this)"
                                    class="absolute opacity-0 w-0 h-0 pointer-events-none" id="photo-upload-input"
                                    accept="image/*" multiple>
                            </label>
                        </div>

                        <!-- Add / Edit Text Trigger -->
                        <button id="trigger-text-drawer" onclick="customizer.openTextDrawer()"
                            class="w-11 h-11 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 active:scale-95 transition flex items-center justify-center shadow-sm text-slate-600 relative">
                            <i data-lucide="type" class="w-5 h-5"></i>
                            <span id="text-edit-indicator"
                                class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-mobile-500 rounded-full border-2 border-white hidden"></span>
                        </button>

                        <!-- Quick Color Picker Button -->
                        <div class="relative w-11 h-11 shrink-0">
                            <input type="color" id="main-color-input"
                                oninput="customizer._updateSelectedStyle('fill', this.value)"
                                class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                            <div id="main-color-preview"
                                class="w-full h-full rounded-xl border border-slate-200 bg-slate-50 shadow-sm flex items-center justify-center transition active:scale-95 overflow-hidden">
                                <i data-lucide="palette" class="w-5 h-5 text-slate-600"></i>
                            </div>
                        </div>

                        <!-- Trash/Delete Action (Only shown when active layer is selected) -->
                        <button id="remove-btn" onclick="customizer.handleRemove()"
                            class="hidden w-11 h-11 rounded-xl border border-red-100 bg-red-50 hover:bg-red-100 text-red-500 transition active:scale-95 flex items-center justify-center shadow-sm">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Right Half: Continue & Save Button -->
                    <div class="flex-1">
                        <form action="{{ route('flow.cart.add') }}" method="POST" id="checkout-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="upload_ids" id="upload_ids_field">
                            <button type="button" id="submit-btn" onclick="customizer.submitAllCanvases()"
                                class="w-full bg-slate-900 hover:bg-black text-white font-display font-black py-3 px-4 rounded-xl flex items-center justify-center gap-1.5 transition-all active:scale-[0.97] shadow-md shadow-slate-100 text-xs">
                                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                <span>Add to Cart</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            <!-- ═══ TEXT EDITING DRAWER / MODAL ═══ -->
            <div id="drawer-backdrop"
                class="fixed max-w-md mx-auto inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-300 opacity-0"
                onclick="customizer.closeTextDrawer()"></div>

            <div id="text-drawer"
                class="fixed max-w-md mx-auto inset-x-0 bottom-0 z-50 bg-white rounded-t-[32px] shadow-[0_-15px_40px_rgba(0,0,0,0.12)] transform translate-y-full transition-transform duration-300 ease-out border-t border-slate-100 pb-safe">
                <!-- Pull Handle bar -->
                <div class="flex justify-center py-3" onclick="customizer.closeTextDrawer()">
                    <div class="w-12 h-1.5 bg-slate-200 rounded-full cursor-pointer hover:bg-slate-300 transition-colors">
                    </div>
                </div>

                <!-- Header -->
                <div class="px-6 pb-3 flex items-center justify-between border-b border-slate-50">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i data-lucide="type" class="w-4 h-4 text-mobile-500"></i>
                        <span id="drawer-title-label">Add Text Layer</span>
                    </h3>
                    <button onclick="customizer.closeTextDrawer()"
                        class="p-1 rounded-full hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Body Contents -->
                <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                    <!-- Text Area Input -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Type Your Text</label>
                        <div class="relative">
                            <textarea id="text-input" placeholder="Type here..."
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-mobile-500 focus:bg-white transition-all resize-none font-medium text-slate-700"
                                oninput="customizer.onTextInputChange(this.value)" rows="2"></textarea>
                            <button id="clear-text-btn" onclick="customizer.clearSelection()"
                                class="hidden absolute right-3 top-3 text-slate-300 hover:text-slate-500 transition-colors">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Font Styles Dropdowns -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Font Style</label>
                            <select id="font-family-select"
                                onchange="customizer._updateSelectedStyle('fontFamily', this.value)"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:outline-none focus:border-mobile-500 focus:bg-white transition-all">
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
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Font Size</label>
                            <select id="font-size-select"
                                onchange="customizer._updateSelectedStyle('fontSize', parseInt(this.value))"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:outline-none focus:border-mobile-500 focus:bg-white transition-all">
                                @for ($i = 8; $i <= 96; $i += 2)
                                    <option value="{{ $i }}" {{ $i == 16 ? 'selected' : '' }}>
                                        {{ $i }} px</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Alignment & Color Options -->
                    <div class="grid grid-cols-2 gap-3 items-center">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Color
                                Palette</label>
                            <div class="relative w-full h-11">
                                <input type="color" id="text-color-input"
                                    oninput="customizer._updateSelectedStyle('fill', this.value)"
                                    class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                                <div id="text-color-preview"
                                    class="w-full h-full rounded-xl border border-slate-200 shadow-sm flex items-center justify-between px-3 bg-slate-50"
                                    style="background: #000000">
                                    <span class="text-xs font-bold text-white mix-blend-difference">Choose</span>
                                    <i data-lucide="palette"
                                        class="w-4 h-4 text-white mix-blend-difference opacity-75"></i>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alignment</label>
                            <select id="text-align-select"
                                onchange="customizer._updateSelectedStyle('textAlign', this.value)"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold focus:outline-none focus:border-mobile-500 focus:bg-white transition-all">
                                <option value="left">Left</option>
                                <option value="center" selected>Center</option>
                                <option value="right">Right</option>
                                <option value="justify">Justify</option>
                            </select>
                        </div>
                    </div>

                    <!-- Button Actions inside Drawer -->
                    <div class="pt-3">
                        <button id="add-text-btn" onclick="customizer.addTextAndClose()"
                            class="w-full bg-mobile-500 hover:bg-mobile-600 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg shadow-mobile-100 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add to Card
                        </button>
                        <div id="editing-badge"
                            class="hidden w-full flex items-center justify-center gap-2 py-3.5 bg-mobile-50 text-mobile-600 rounded-xl border border-mobile-100">
                            <i data-lucide="type" class="w-4 h-4 shrink-0"></i>
                            <span class="text-xs font-black uppercase tracking-wider">Active Layer Editing</span>
                        </div>
                    </div>
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
            _actionIconsRAF: null,
            _pendingActionObj: null,

            // Default Text Styles
            textFontSize: '24',
            textFontFamily: 'Arial',
            textColor: '#000000',
            textAlign: 'center',

            // ── Ready-made Design Templates (loaded from DB) ──────────────────────
            templates: @json($activeTemplates ?? []),
            templateCategories: @json($templateCategories ?? []),
            activeTplCategory: null,

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
                window.addEventListener('resize', this._debounce(() => {
                    this._resizeAllCanvases();
                    this._positionTextActionIcons();
                }, 150));

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

                // 6. Floating Action Icon Listeners (fire BEFORE Fabric clears selection)
                this._initActionIconListeners();

                // 7. Build the ready-made template category filter + chips
                this.renderCategoryFilter();
                this.renderTemplateChips();

                // Final UI Sync
                this.updateUI();
            },

            _initActionIconListeners() {
                const editBtn = document.getElementById('text-action-edit');
                const actionsDiv = document.getElementById('text-layer-actions');

                // Stop all events on the container from reaching the canvas
                if (actionsDiv) {
                    ['mousedown', 'touchstart', 'pointerdown'].forEach(evt => {
                        actionsDiv.addEventListener(evt, (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                        }, {
                            passive: false
                        });
                    });
                }

                if (editBtn) {
                    editBtn.addEventListener('pointerdown', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        // Capture the object NOW before any selection:cleared fires
                        this._pendingActionObj = this.selectedObject;
                    }, {
                        passive: false
                    });
                    editBtn.addEventListener('pointerup', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        const obj = this._pendingActionObj || this.selectedObject;
                        if (obj && obj._isUserText) {
                            this.selectedObject = obj; // restore in case it was cleared
                            this.openTextDrawer();
                        }
                        this._pendingActionObj = null;
                    });
                    // Fallback for touch devices
                    editBtn.addEventListener('touchend', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        const obj = this._pendingActionObj || this.selectedObject;
                        if (obj && obj._isUserText) {
                            this.selectedObject = obj;
                            this.openTextDrawer();
                        }
                        this._pendingActionObj = null;
                    }, {
                        passive: false
                    });
                }


            },

            // ── Build the category filter tabs ─────────────────────────────────────
            renderCategoryFilter() {
                const filter = document.getElementById('template-cat-filter');
                if (!filter) return;
                filter.innerHTML = '';

                const allBtn = document.createElement('button');
                allBtn.type = 'button';
                allBtn.className = 'template-cat-chip' + (this.activeTplCategory === null ? ' active' : '');
                allBtn.dataset.cat = '';
                allBtn.textContent = 'All';
                allBtn.onclick = () => this.filterTemplates(null);
                filter.appendChild(allBtn);

                (this.templateCategories || []).forEach(cat => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'template-cat-chip' + (this.activeTplCategory === cat.id ? ' active' : '');
                    btn.dataset.cat = String(cat.id);
                    btn.textContent = cat.name;
                    btn.onclick = () => this.filterTemplates(cat.id);
                    filter.appendChild(btn);
                });
            },

            // ── Filter templates by category ───────────────────────────────────────
            filterTemplates(catId) {
                this.activeTplCategory = catId;
                document.querySelectorAll('.template-cat-chip').forEach(el => {
                    el.classList.toggle('active', el.dataset.cat === (catId === null ? '' : String(catId)));
                });
                this.renderTemplateChips();
            },

            // ── Build the template chips from the templates object ──────────────────
            renderTemplateChips() {
                const strip = document.getElementById('template-strip');
                if (!strip) return;

                strip.querySelectorAll('.template-chip').forEach(el => el.remove());

                Object.keys(this.templates).forEach(id => {
                    const tpl = this.templates[id];
                    // Filter by active category
                    if (this.activeTplCategory !== null && tpl.categoryId !== this.activeTplCategory) return;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'template-chip';
                    btn.setAttribute('data-template', id);
                    btn.onclick = () => this.applyTemplate(id);
                    const iconHtml = tpl.iconUrl ?
                        `<img src="${tpl.iconUrl}" alt="" style="width:1em;height:1em;object-fit:contain;">` :
                        `<i data-lucide="${tpl.icon || 'layout-template'}"></i>`;
                    btn.innerHTML = `${iconHtml}<span>${tpl.label || id}</span>`;
                    strip.appendChild(btn);
                });

                if (window.lucide) window.lucide.createIcons();
            },

            // ── Apply a ready-made template to the canvas(es) ───────────────────────
            // Async because images and SVGs load asynchronously — we await them so the
            // final z-ordering happens after every layer is on the canvas.
            async applyTemplate(id) {
                const tpl = this.templates[id];
                if (!tpl) return;

                const keys = tpl.applyTo === 'all' ?
                    Object.keys(this.canvases).filter(k => this.canvasEnabled[k]) : [this.activeCanvas];

                const firstKey = Object.keys(this.imageTypes)[0];

                for (const key of keys) {
                    const cv = this.canvases[key];
                    if (!cv || this.canvasEnabled[key] === false) continue;
                    const fc = cv.fabricCanvas;
                    const W = fc.width,
                        H = fc.height,
                        sf = cv.scaleFactor;

                    // Clear previous template layers only — keep the customer's own photo and text
                    if (tpl.replace !== false) {
                        fc.getObjects().filter(o => o._isTemplateText || o._isTemplateImage || o._isTemplateSvg)
                            .forEach(o => fc.remove(o));
                    }

                    // 1) Raster images (await each so they all land before z-ordering)
                    for (const spec of (tpl.images || [])) {
                        await this._addTemplateImage(fc, {
                            ...spec,
                            ignoreMask: tpl.ignoreMask
                        }, sf, key);
                    }

                    // 2) SVGs (url or inline string)
                    for (const spec of (tpl.svgs || [])) {
                        await this._addTemplateSvg(fc, {
                            ...spec,
                            ignoreMask: tpl.ignoreMask
                        }, sf, key);
                    }

                    // 3) Text layers (synchronous)
                    (tpl.texts || []).forEach(spec => {
                        const align = spec.textAlign || 'center';
                        const t = new fabric.Textbox(spec.text || '', {
                            left: W * (spec.xFrac ?? 0.5),
                            top: H * (spec.yFrac ?? 0.4),
                            originX: align === 'right' ? 'right' : (align === 'left' ? 'left' :
                                'center'),
                            originY: 'top',
                            width: W * (spec.widthFrac || 0.8),
                            fontSize: (spec.fontSize || 24) * sf, // scale like masks
                            fontFamily: spec.fontFamily || 'Inter',
                            fill: spec.fill || '#000000',
                            textAlign: align,
                            _isTemplateText: true,
                            objectCaching: false,
                            cornerSize: 12,
                            transparentCorners: false,
                            borderColor: '#378ADD',
                            cornerColor: '#378ADD',
                            cornerStyle: 'circle',
                            lockScalingFlip: true,
                            hasRotatingPoint: true
                        });

                        // Apply the photo mask clip to Page 1 unless the template opts out
                        this._maybeClip(t, {
                            ignoreMask: tpl.ignoreMask
                        }, sf, key);

                        fc.add(t);

                        // Preload the font, then refresh once it is ready
                        document.fonts.load(`${t.fontSize}px "${t.fontFamily}"`).then(() => {
                            if (t.canvas) {
                                t.setCoords();
                                t.canvas.requestRenderAll();
                            }
                        }).catch(() => {});
                    });

                    this._enforceZOrder(key);

                    fc.renderAll();
                    this._saveCanvasState(key);
                }

                // Templates were applied across pages — clear any active selection
                const activeCv = this.canvases[this.activeCanvas];
                if (activeCv && activeCv.fabricCanvas) {
                    activeCv.fabricCanvas.discardActiveObject().renderAll();
                }
                this.selectedObject = null;
                this._hideTextActionIcons();
                this.updateUI();
            },

            // ── Add a raster image from a template ──────────────────────────────────
            _addTemplateImage(fc, spec, sf, key) {
                return new Promise((resolve) => {
                    fabric.Image.fromURL(spec.url, img => {
                        if (!img) return resolve(null);
                        const W = fc.width,
                            H = fc.height;
                        const scale = (W * (spec.widthFrac || 0.3)) / img.width;
                        img.set({
                            originX: 'center',
                            originY: 'center',
                            left: W * (spec.xFrac ?? 0.5),
                            top: H * (spec.yFrac ?? 0.5),
                            scaleX: scale,
                            scaleY: scale,
                            angle: spec.angle || 0,
                            _isTemplateImage: true,
                            selectable: !spec.locked,
                            evented: !spec.locked,
                            hasControls: !spec.locked,
                            cornerStyle: 'circle',
                            cornerSize: 12,
                            transparentCorners: false,
                            borderColor: '#378ADD',
                            cornerColor: '#378ADD',
                            lockScalingFlip: true,
                            uniformScaling: true,
                            objectCaching: true
                        });
                        this._maybeClip(img, spec, sf, key);
                        fc.add(img);
                        resolve(img);
                    }, {
                        crossOrigin: 'anonymous'
                    });
                });
            },

            // ── Add an SVG (from url OR inline string) from a template ───────────────
            _addTemplateSvg(fc, spec, sf, key) {
                return new Promise((resolve) => {
                    const onLoaded = (objects, options) => {
                        if (!objects || !objects.length) return resolve(null);
                        const obj = fabric.util.groupSVGElements(objects, options);
                        const W = fc.width;
                        const baseW = obj.width || 100;
                        const scale = (W * (spec.widthFrac || 0.15)) / baseW;

                        // Optional recolor: paint every path one solid fill (single-color icons)
                        if (spec.fill) {
                            if (obj._objects) obj._objects.forEach(o => o.set('fill', spec.fill));
                            else obj.set('fill', spec.fill);
                        }

                        obj.set({
                            originX: 'center',
                            originY: 'center',
                            left: W * (spec.xFrac ?? 0.5),
                            top: fc.height * (spec.yFrac ?? 0.5),
                            scaleX: scale,
                            scaleY: scale,
                            angle: spec.angle || 0,
                            _isTemplateSvg: true,
                            selectable: !spec.locked,
                            evented: !spec.locked,
                            hasControls: !spec.locked,
                            cornerStyle: 'circle',
                            cornerSize: 12,
                            transparentCorners: false,
                            borderColor: '#378ADD',
                            cornerColor: '#378ADD',
                            lockScalingFlip: true,
                            uniformScaling: true
                        });
                        this._maybeClip(obj, spec, sf, key);
                        fc.add(obj);
                        resolve(obj);
                    };

                    if (spec.url) fabric.loadSVGFromURL(spec.url, onLoaded);
                    else if (spec.svg) fabric.loadSVGFromString(spec.svg, onLoaded);
                    else resolve(null);
                });
            },

            // ── Shared mask-clip helper (Page 1 only, unless the spec opts out) ──────
            _maybeClip(obj, spec, sf, key) {
                if (spec.ignoreMask) return;
                const clipGroup = this._createCombinedClipPath(key, sf);
                if (clipGroup) obj.set('clipPath', clipGroup);
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

                if (statusCard && statusIconBg && statusBadge && statusLabel) {
                    if (enabled) {
                        statusCard.className =
                            'border-2 rounded-2xl p-4 flex items-center h-full bg-gradient-to-r from-mobile-50 to-sky-50 border-mobile-100';
                        statusIconBg.className =
                            'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-mobile-500';
                        statusBadge.className = 'text-[9px] font-bold uppercase tracking-wider text-mobile-700';
                        statusBadge.textContent = 'Editing';
                        statusLabel.className = 'text-xs font-black truncate text-mobile-900';
                    } else {
                        statusCard.className =
                            'border-2 rounded-2xl p-4 flex items-center h-full bg-slate-50 border-slate-200';
                        statusIconBg.className =
                            'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-slate-300';
                        statusBadge.className = 'text-[9px] font-bold uppercase tracking-wider text-slate-500';
                        statusBadge.textContent = 'Locked';
                        statusLabel.className = 'text-xs font-black truncate text-slate-600';
                    }
                    statusLabel.textContent = this.imageTypes[key]?.label || 'Layer';
                }

                // 4. Upload Area
                const uploadArea = document.getElementById('upload-area');
                const uploadZone = document.getElementById('upload-zone');
                const uploadIconBg = document.getElementById('upload-icon-bg');
                const uploadText = document.getElementById('upload-text');

                uploadArea.classList.toggle('hidden', !enabled);
                if (enabled) {
                    uploadZone.classList.toggle('has-image', hasImg);
                    if (hasImg) {
                        uploadIconBg.className =
                            'w-9 h-9 rounded-xl flex items-center justify-center shadow-sm shrink-0  bg-gray-100  text-gray-600';
                        uploadText.textContent = 'Uploaded';
                    } else {
                        uploadIconBg.className =
                            'w-9 h-9 rounded-xl flex items-center justify-center shadow-sm shrink-0 bg-white text-slate-400';
                        uploadText.textContent = 'Upload';
                    }
                }

                // 5. Toolbars
                const zoomCtrl = document.getElementById('zoom-control');
                if (zoomCtrl) zoomCtrl.classList.toggle('hidden', !enabled || !hasImg);

                const textToolbar = document.getElementById('text-toolbar');
                if (textToolbar) textToolbar.classList.toggle('hidden', !enabled);

                // 6. Selection Sync
                this._syncToolbarToSelection(this.selectedObject);

                // 7. Remove Button
                const removeBtn = document.getElementById('remove-btn');
                const removeBtnText = document.getElementById('remove-btn-text');
                const showRemove = enabled && (hasImg || this.selectedObject);
                removeBtn.classList.toggle('hidden', !showRemove);
                if (showRemove && removeBtnText) {
                    removeBtnText.textContent = this.selectedObject ? 'Remove Selected Text' : 'Remove Photo';
                }

                // 8. Refresh Icons
                if (window.lucide) window.lucide.createIcons();
            },

            _initAllCanvases() {
                const containerEl = document.getElementById('canvas-container');
                if (!containerEl) return;
                const displayWidth = containerEl.offsetWidth;

                const isPortrait = <?php echo ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false'; ?>;

                // All 4 product images share the same dimensions, so every page must use a
                // single canvas aspect ratio. The admin mask editor only records canvasWidth/
                // canvasHeight for tabs that were actually visited — unvisited tabs keep the
                // default 600×400 (landscape). Trusting each page's own saved dims therefore
                // squishes portrait pages that were never masked. Derive one shared ratio from
                // the first page whose config has real dimensions (the masked page), so every
                // canvas matches the actual product image instead of the stale default.
                const sharedConfig = Object.keys(this.imageTypes)
                    .map(k => this.allMaskData[k] || {})
                    .find(c => c.canvasWidth && c.canvasHeight) || {};
                const adminW = sharedConfig.canvasWidth || (isPortrait ? 400 : 560);
                const adminH = sharedConfig.canvasHeight || (isPortrait ? 560 : 400);

                Object.keys(this.imageTypes).forEach(key => {
                    const canvasEl = document.getElementById('canvas-' + key);
                    if (!canvasEl) return;

                    const config = this.allMaskData[key] || {};
                    const isEditable = this.canvasEnabled[key];

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

                    // Dynamic touch action scroll optimizer:
                    // Allows modern mobile browsers to scroll the page up/down if not touching an editable object.
                    const upperCanvasEl = fc.upperCanvasEl;
                    if (upperCanvasEl) {
                        upperCanvasEl.style.touchAction = 'pan-y';

                        upperCanvasEl.addEventListener('touchstart', (e) => {
                            const target = fc.findTarget(e);
                            if (target && (target._isUserImage || target._isUserText ||
                                    target._isTemplateText || target._isTemplateImage || target
                                    ._isTemplateSvg)) {
                                upperCanvasEl.style.touchAction = 'none';
                            } else {
                                upperCanvasEl.style.touchAction = 'pan-y';
                            }
                        }, {
                            passive: true
                        });

                        upperCanvasEl.addEventListener('touchend', () => {
                            upperCanvasEl.style.touchAction = 'pan-y';
                        });
                    }

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

                    // --- Add Mask Guides (Visual Only) ---
                    const firstKey = Object.keys(this.imageTypes)[0];
                    const mData = this.allMaskData[key] || {};
                    const masks = mData.masks || this.allMaskData.masks;
                    if (key === firstKey && Array.isArray(masks) && masks.length > 0) {
                        this.canvases[key].maskGuides = [];
                        masks.forEach((m, idx) => {
                            const guide = this._createMaskObject(m, scaleFactor, {
                                fill: 'transparent',
                                stroke: 'rgba(0, 80, 220, 0.5)',
                                strokeWidth: 1,
                                selectable: false,
                                evented: false,
                                name: 'mask_guide_' + idx
                            });
                            if (guide) {
                                fc.add(guide);
                                this.canvases[key].maskGuides.push(guide);
                            }
                        });
                    }

                    fc.on('mouse:down', () => {
                        fc.calcOffset();
                    });
                    fc.on('selection:created', (e) => {
                        this.selectedObject = e.selected[0];
                        setTimeout(() => {
                            if (fc.getActiveObject() === e.selected[0]) {
                                this.updateUI();
                                this._positionTextActionIcons();
                            }
                        }, 0);
                    });
                    fc.on('selection:updated', (e) => {
                        this.selectedObject = e.selected[0];
                        setTimeout(() => {
                            if (fc.getActiveObject() === e.selected[0]) {
                                this.updateUI();
                                this._positionTextActionIcons();
                            }
                        }, 0);
                    });
                    fc.on('selection:cleared', () => {
                        if (this.activeCanvas !== key) return;
                        this.selectedObject = null;
                        setTimeout(() => {
                            if (!fc.getActiveObject()) {
                                this.updateUI();
                                this._hideTextActionIcons();
                                this.closeTextDrawer();
                            }
                        }, 0);
                    });

                    fc.on('object:modified', () => {
                        this._saveCanvasState(key);
                        this._positionTextActionIcons();
                    });
                    fc.on('object:added', () => this._saveCanvasState(key));
                    fc.on('object:removed', () => {
                        this._saveCanvasState(key);
                        this._hideTextActionIcons();
                    });


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
                        this._positionTextActionIcons();
                    });

                    // object:scaled event bilkul hata do ya empty rakho
                    fc.on('object:scaled', (e) => {
                        this._saveCanvasState(key);
                        this._positionTextActionIcons();
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



                                fc.add(obj);
                            });
                            fc.renderAll();
                            fc.getObjects().forEach(o => o.setCoords());

                            this._enforceZOrder(key);

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
                    if (textInput) textInput.placeholder = 'Add text...';
                    if (clearBtn) clearBtn.classList.add('hidden');
                    if (addBtn) addBtn.classList.remove('hidden');
                    if (editBadge) editBadge.classList.add('hidden');

                    const textEditIndicator = document.getElementById('text-edit-indicator');
                    if (textEditIndicator) textEditIndicator.classList.add('hidden');
                    return;
                }

                if (textInput && textInput.value !== obj.text) {
                    textInput.value = obj.text;
                }
                const fontFam = document.getElementById('font-family-select');
                if (fontFam) fontFam.value = obj.fontFamily;

                const fontSizeSel = document.getElementById('font-size-select');
                if (fontSizeSel) fontSizeSel.value = obj.fontSize.toString();

                const colorInp = document.getElementById('text-color-input');
                if (colorInp) colorInp.value = obj.fill;

                const colorPreview = document.getElementById('text-color-preview');
                if (colorPreview) colorPreview.style.background = obj.fill;

                const mainColorInp = document.getElementById('main-color-input');
                if (mainColorInp) mainColorInp.value = obj.fill;

                const mainColorPreview = document.getElementById('main-color-preview');
                if (mainColorPreview) mainColorPreview.style.background = obj.fill;

                const alignSel = document.getElementById('text-align-select');
                if (alignSel) alignSel.value = obj.textAlign || 'center';

                if (clearBtn) clearBtn.classList.remove('hidden');
                if (addBtn) addBtn.classList.add('hidden');
                if (editBadge) editBadge.classList.remove('hidden');

                const textEditIndicator = document.getElementById('text-edit-indicator');
                if (textEditIndicator) textEditIndicator.classList.remove('hidden');

                // Text drawer now opens only via the edit icon on canvas
            },

            async _updateSelectedStyle(property, value) {
                if (property === 'fill') {
                    const preview = document.getElementById('text-color-preview');
                    if (preview) preview.style.background = value;
                    const mainPreview = document.getElementById('main-color-preview');
                    if (mainPreview) mainPreview.style.background = value;
                }
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

                if (obj.type === 'textbox' && (property === 'fontSize' || property === 'fontFamily' || property ===
                        'fontWeight')) {
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
                if (this.selectedObject && (this.selectedObject.type === 'i-text' || this.selectedObject.type ===
                        'text' || this.selectedObject.type === 'textbox')) {
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
                this._hideTextActionIcons();
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
                if (this.canvasEnabled[key] === false) return Promise.reject(new Error('Canvas disabled'));
                const cv = this.canvases[key];
                if (!cv) return Promise.reject(new Error('Canvas not found'));

                return new Promise((resolve) => {
                    fabric.Image.fromURL(url, img => {
                        const canvasW = cv.fabricCanvas.width;
                        const canvasH = cv.fabricCanvas.height;

                        // Initial size must be 1/3 width of canvas
                        const s = canvasW / (3 * img.width);

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

                        cv.fabricCanvas.add(img);

                        this._enforceZOrder(key);

                        cv.fabricCanvas.setActiveObject(img);
                        cv.fabricCanvas.renderAll();
                        img.setCoords();
                        this.imgScales[key] = s;
                        this.updateUI();
                        resolve(img);
                    }, {
                        crossOrigin: 'anonymous'
                    });
                });
            },

            async handleFileUpload(input) {
                if (!input.files || input.files.length === 0 || !this.activeCanvas) return;
                const key = this.activeCanvas;
                if (this.canvasEnabled[key] === false) return;

                this.isUploading = true;
                this.updateUI();

                try {
                    // Process files sequentially
                    for (let i = 0; i < input.files.length; i++) {
                        const file = input.files[i];

                        // 1. Process & Optimize Image (Resize & Convert to WebP)
                        const optimized = await this._processImage(file);

                        // 2. Display on Fabric Canvas
                        this.canvasImages[key] = optimized.dataUrl;
                        const imgObj = await this._addImageToCanvas(key, optimized.dataUrl);

                        // 3. Upload to Server
                        const fd = new FormData();
                        fd.append('image', optimized.blob, 'upload.webp');
                        fd.append('_token', '<?php echo csrf_token(); ?>');

                        const res = await fetch('<?php echo route('flow.upload'); ?>', {
                            method: 'POST',
                            body: fd
                        });
                        const dat = await res.json();
                        if (dat.success) {
                            this.uploadIds[key] = dat.upload_id;
                            imgObj._uploadId = dat.upload_id;
                            this._saveCanvasState(key);
                        }
                    }
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
                const obj = this.selectedObject;
                if (!cv || !obj || !obj._isUserImage) return;
                this.imgScales[this.activeCanvas] = val;
                obj.set({
                    scaleX: parseFloat(val),
                    scaleY: parseFloat(val)
                });
                obj.setCoords();
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

                cv.fabricCanvas.add(t);

                this._enforceZOrder(this.activeCanvas);

                t.setCoords();
                cv.fabricCanvas.setActiveObject(t);
                cv.fabricCanvas.renderAll();

                // 3. Background Font Load - once loaded, refresh the canvas
                document.fonts.load(`${fontSize}px "${fontFamily}"`).then(() => {
                    if (t.canvas) {
                        t.set('fontFamily', fontFamily);
                        t.setCoords();
                        t.canvas.requestRenderAll();
                        this._positionTextActionIcons();
                    }
                }).catch(e => console.warn("Font load error:", e));

                this.updateUI();
                this._positionTextActionIcons();
            },

            handleRemove() {
                const cv = this.canvases[this.activeCanvas];
                if (!cv) return;
                const fc = cv.fabricCanvas;

                if (this.selectedObject) {
                    const objToRemove = this.selectedObject;
                    const wasImage = objToRemove._isUserImage;
                    this.selectedObject = null;

                    // Discard first so Fabric doesn't try to render a removed active object
                    fc.discardActiveObject();
                    fc.remove(objToRemove);

                    // Clear the top canvas (selection handles / ghost overlay)
                    fc.clearContext(fc.contextTop);
                    fc.renderAll();

                    if (objToRemove._isUserText) {
                        document.getElementById('text-input').value = '';
                    }
                    this._hideTextActionIcons();

                    if (wasImage) {
                        const hasRemainingImages = fc.getObjects().some(o => o._isUserImage);
                        if (!hasRemainingImages) {
                            this.canvasImages[this.activeCanvas] = null;
                            this.uploadIds[this.activeCanvas] = null;
                        }
                    }
                }

                this._saveCanvasState(this.activeCanvas);
                this.updateUI();
            },

            submitAllCanvases() {
                if (this.isSavingComposite) return;
                const hasContent = Object.values(this.uploadIds).some(id => id !== null) ||
                    Object.keys(this.canvases).some(k => this.canvases[k].fabricCanvas.backgroundImage ||
                        this.canvases[k].fabricCanvas.getObjects().some(o =>
                            o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg));

                if (!hasContent) {
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
                    const hasCanvasContent = this.canvasImages[key] !== null || cv.fabricCanvas
                        .backgroundImage ||
                        cv.fabricCanvas.getObjects().some(
                            o => o._isUserText || o._isTemplateText || o._isTemplateImage || o
                            ._isTemplateSvg);
                    if (!hasCanvasContent) return;

                    cv.fabricCanvas.discardActiveObject();

                    // Hide all guides
                    if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', false));
                    cv.fabricCanvas.renderAll();

                    const b64 = cv.fabricCanvas.toDataURL({
                        format: 'jpeg',
                        quality: 0.9,
                        multiplier: 2
                    });

                    // Restore Guide Visibility
                    if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', true));
                    cv.fabricCanvas.renderAll();

                    const res = await fetch('<?php echo route('flow.upload_composite'); ?>', {
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

            _createCombinedClipPath(key, sf) {
                const mData = this.allMaskData[key] || {};
                const masks = mData.masks || this.allMaskData.masks;
                if (!Array.isArray(masks) || masks.length === 0) return null;

                const clipObjects = masks.map(m => this._createMaskObject(m, sf, {
                    absolutePositioned: true,
                    strokeWidth: 0
                })).filter(Boolean);

                if (clipObjects.length === 0) return null;
                if (clipObjects.length === 1) {
                    clipObjects[0].set({
                        absolutePositioned: true
                    });
                    return clipObjects[0];
                }

                const group = new fabric.Group(clipObjects, {
                    absolutePositioned: true
                });
                return group;
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
                    const newSf = cv.fabricCanvas.width / cv.adminW;
                    cv.fabricCanvas.getObjects().forEach(o => {
                        o.left *= ratio;
                        o.top *= ratio;
                        o.scaleX *= ratio;
                        o.scaleY *= ratio;

                        // Update Mask on Resize
                        if (o.clipPath) {
                            const newClip = this._createCombinedClipPath(key, newSf);
                            if (newClip) {
                                newClip.canvas = cv.fabricCanvas;
                                o.set('clipPath', newClip);
                            }
                        }

                        o.setCoords();
                    });

                    // Update Mask Guides on Resize
                    if (cv.maskGuides && cv.maskGuides.length > 0) {
                        cv.maskGuides.forEach(g => cv.fabricCanvas.remove(g));
                        cv.maskGuides = [];
                        const mData = this.allMaskData[key] || {};
                        const masks = mData.masks || this.allMaskData.masks;
                        if (Array.isArray(masks)) {
                            masks.forEach((m, idx) => {
                                const guide = this._createMaskObject(m, newSf, {
                                    fill: 'transparent',
                                    stroke: 'rgba(0, 80, 220, 0.5)',
                                    strokeWidth: 1,
                                    selectable: false,
                                    evented: false,
                                    name: 'mask_guide_' + idx
                                });
                                if (guide) {
                                    cv.fabricCanvas.add(guide);
                                    cv.maskGuides.push(guide);
                                    guide.bringToFront();
                                }
                            });
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
                    // Remove all user and template content, keep background and mask guide
                    objects.forEach(o => {
                        if (o._isUserImage || o._isUserText ||
                            o._isTemplateText || o._isTemplateImage || o._isTemplateSvg) {
                            cv.fabricCanvas.remove(o);
                        }
                    });
                    cv.fabricCanvas.renderAll();
                    this._saveCanvasState(key);
                });
                this.updateUI();
                alert('All designs cleared!');
            },

            openTextDrawer() {
                const drawer = document.getElementById('text-drawer');
                const backdrop = document.getElementById('drawer-backdrop');
                if (!drawer || !backdrop) return;

                const label = document.getElementById('drawer-title-label');
                if (label) {
                    label.textContent = this.selectedObject ? 'Edit Text Layer' : 'Add Text Layer';
                }

                backdrop.classList.remove('hidden');
                drawer.classList.remove('translate-y-full');

                setTimeout(() => {
                    backdrop.classList.add('opacity-100');
                }, 10);
            },

            closeTextDrawer() {
                const drawer = document.getElementById('text-drawer');
                const backdrop = document.getElementById('drawer-backdrop');
                if (!drawer || !backdrop) return;

                backdrop.classList.remove('opacity-100');
                drawer.classList.add('translate-y-full');

                setTimeout(() => {
                    backdrop.classList.add('hidden');
                }, 300);
            },

            addTextAndClose() {
                this.addText();
                this.closeTextDrawer();
            },

            // ── Canvas Floating Action Icons for Text Layers ─────────────────────
            _positionTextActionIcons() {
                const actionsEl = document.getElementById('text-layer-actions');
                if (!actionsEl) return;

                const obj = this.selectedObject;
                // Only show for text layers
                if (!obj || !obj._isUserText) {
                    this._hideTextActionIcons();
                    return;
                }

                const cv = this.canvases[this.activeCanvas];
                if (!cv || !cv.fabricCanvas) return;

                const canvasContainer = document.getElementById('canvas-container');
                if (!canvasContainer) return;

                // Get the bounding rect of the object on the fabric canvas
                obj.setCoords();
                const bound = obj.getBoundingRect();

                // Position icons at top-right corner of the text bounding box
                const iconGroupWidth = 66; // 30 + 6 + 30 (two buttons + gap)
                let iconLeft = bound.left + bound.width - iconGroupWidth / 2;
                let iconTop = bound.top - 38; // above the bounding box

                // Keep within canvas bounds
                const cW = cv.fabricCanvas.width;
                if (iconLeft + iconGroupWidth > cW) iconLeft = cW - iconGroupWidth - 4;
                if (iconLeft < 0) iconLeft = 4;
                if (iconTop < 0) iconTop = bound.top + bound.height + 6; // below if no room above

                actionsEl.style.left = iconLeft + 'px';
                actionsEl.style.top = iconTop + 'px';
                actionsEl.classList.add('visible');
            },

            _hideTextActionIcons() {
                const actionsEl = document.getElementById('text-layer-actions');
                if (actionsEl) actionsEl.classList.remove('visible');
            },

            onCanvasDeleteIconClick() {
                if (this.selectedObject && this.selectedObject._isUserText) {
                    this.handleRemove();
                }
            },

            onCanvasEditIconClick() {
                if (this.selectedObject && this.selectedObject._isUserText) {
                    this.openTextDrawer();
                }
            },

            // Layer order (bottom → top):
            //   customer photo → template images → template SVGs → template text → user text → guide
            _enforceZOrder(key) {
                const cv = this.canvases[key];
                if (!cv || !cv.fabricCanvas) return;
                const fc = cv.fabricCanvas;
                // Call bringToFront bottom-up: each group lands above the previous
                [
                    o => o._isUserImage, // customer uploaded photo — lowest
                    o => o._isTemplateImage, // template raster images
                    o => o._isTemplateSvg, // template SVG decorations
                    o => o._isTemplateText, // template text
                    o => o._isUserText, // user-typed text — topmost content
                ].forEach(pred => fc.getObjects().filter(pred).forEach(o => o.bringToFront()));
                if (cv.maskGuides) cv.maskGuides.forEach(g => g.bringToFront());
                fc.renderAll();
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            customizer.init();
            // Global mouseup to release Fabric drag state if user releases mouse outside canvas
            window.addEventListener('mouseup', (e) => {
                Object.values(customizer.canvases).forEach(cv => {
                    if (cv.fabricCanvas && cv.fabricCanvas._currentTransform) {
                        if (e.target !== cv.fabricCanvas.upperCanvasEl) {
                            cv.fabricCanvas.__onMouseUp(e);
                        }
                    }
                });
            });
        });
    </script>
@endpush
