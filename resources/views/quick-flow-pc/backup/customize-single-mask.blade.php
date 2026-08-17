{{-- Deprecated: All customizer views are unified in customize.blade.php --}}
@include('quick-flow-pc.customize')
@section('title', 'Customize Your ' . $product->name)

@push('styles')
    <style>
        /* ── Hero Header & Workspace ── */
        .hero-cust-gradient {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #faf0ff 60%, #f0f4ff 100%);
        }

        .hero-cust-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(236, 72, 153, 0.04) 1px, transparent 0);
            background-size: 32px 32px;
        }

        .hero-blob-1 {
            position: absolute;
            top: -60px;
            right: 15%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(40px);
            pointer-events: none;
        }

        .hero-blob-2 {
            position: absolute;
            bottom: -40px;
            right: 5%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(249, 168, 212, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(30px);
            pointer-events: none;
        }

        .hero-blob-3 {
            position: absolute;
            top: 20%;
            right: 35%;
            width: 80px;
            height: 80px;
            background: rgba(236, 72, 153, 0.15);
            border-radius: 50%;
            filter: blur(10px);
            pointer-events: none;
        }

        .hero-dots {
            position: absolute;
            top: 10%;
            right: 3%;
            width: 80px;
            height: 80px;
            background-image: radial-gradient(circle, rgba(236, 72, 153, 0.2) 2px, transparent 2px);
            background-size: 10px 10px;
            border-radius: 50%;
            pointer-events: none;
        }

        .cust-page {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 32px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .cust-page {
                grid-template-columns: 1fr;
            }
        }

        .cust-breadcrumb a {
            transition: color 0.2s ease;
        }

        /* Canvas */
        .canvas-wrapper {
            position: relative;
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            touch-action: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 8px 24px -4px rgba(0, 0, 0, 0.06);
        }

        .canvas-hidden {
            display: none !important;
        }

        .canvas-disabled-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.05);
            z-index: 200;
            pointer-events: none;
            border-radius: 1rem;
        }

        .canvas-container {
            z-index: 100;
            touch-action: none;
        }

        .hidden {
            display: none !important;
        }

        /* Upload Zone */
        .upload-zone {
            border: 2px dashed #e2e8f0;
            border-radius: 1rem;
            padding: 1rem;
            background: #f8fafc;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .upload-zone:hover {
            border-color: var(--color-brand-500, #ec4899);
            background: #fdf2f8;
            transform: translateY(-1px);
        }

        .upload-zone.has-image {
            border-style: solid;
            border-color: #10b981;
            background: #f0fdf4;
        }

        /* Ready-made Template Strip & Category Filter */
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
            color: #D65F32;
        }

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
            background: #D65F32;
            border-color: #D65F32;
            color: #fff;
        }

        /* Mockup Preview */
        .preview-toggle-btn {
            color: #0ea5e9;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            background: transparent;
        }

        .preview-toggle-btn.active {
            background: #fff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }

        .mockup-stage {
            border-radius: 16px;
            overflow: hidden;
            transition: background 0.35s ease, padding 0.35s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .mockup-stage.flat {
            background: #f8fafc;
            padding: 24px;
        }

        .mockup-stage.room {
            background: linear-gradient(180deg, #eef2f7 0%, #e6ebf2 62%, #dfe5ee 62%, #d3dae4 100%);
            padding: 24px 24px 40px;
            position: relative;
        }

        .mockup-stage.room::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 26px;
            height: 2px;
            background: rgba(15, 23, 42, 0.08);
        }

        .mockup-scene {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .mockup-frame {
            position: relative;
            display: inline-block;
            background: #fff;
            transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1), border 0.35s ease, box-shadow 0.35s ease, padding 0.35s ease;
        }

        .mockup-stage.flat .mockup-frame {
            max-width: 100%;
            border-radius: 6px;
            padding: 0;
            box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.28);
        }

        .mockup-stage.room .mockup-frame {
            max-width: 74%;
            border: 10px solid #fff;
            border-radius: 2px;
            padding: 4px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1), 0 22px 44px -14px rgba(15, 23, 42, 0.48);
        }

        .mockup-frame img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 2px;
        }

        .mockup-empty {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 160px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
        }

        /* Mask info badge */
        .mask-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #3b82f6;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Fonts */
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
@endpush

@section('content')
    @php
        $imageTypes = [];
        $slots = ['frame_image' => 'Page 1'];
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
            if (!$url && isset($galleryImages[$galleryIndex])) {
                $url = \App\Models\Product::formatStorageUrl($galleryImages[$galleryIndex]->image_path);
                $galleryIndex++;
            }
            if (!$url) {
                $url = $fallbackUrl;
            }
            $imageTypes[$field] = ['label' => $label, 'url' => $url];
        }
        $maskData = $product->mask_data ?? [];
        if (is_string($maskData)) {
            $maskData = json_decode($maskData, true) ?? [];
        }
        $flowData = session('quick_flow_data', []);

        $bcStoreName =
            session('active_store_name') ??
            ((session('active_store_id') ? \App\Models\Store::find(session('active_store_id'))?->name : null) ??
                ($product->store->name ?? (null ?? ($flowData['store_name'] ?? 'Store'))));

        $bcProductType =
            $flowData['type_name'] ?? ($product->productType->name ?? ($flowData['category_name'] ?? 'Product Type'));

        $bcTypeSlug = $flowData['type_slug'] ?? ($product->productType->slug ?? null);

        $bcPageSizeSide =
            $flowData['size_name'] ??
            ($flowData['size_title'] ??
                (null ??
                    ((isset($flowData['size_width'], $flowData['size_height'])
                        ? $flowData['size_width'] . '×' . $flowData['size_height'] . ($flowData['size_unit'] ?? '')
                        : null) ??
                        ($product->no_of_pages
                            ? ($product->no_of_pages == 1
                                ? 'Single Side'
                                : ($product->no_of_pages == 2
                                    ? 'Double Side'
                                    : $product->no_of_pages . ' Pages'))
                            : 'Shape Mask Canvas'))));

        $bcTemplateName = $product->name ?? 'Custom Template';
    @endphp

    <div id="customizer-app" class="pb-16">

        {{-- ── Breadcrumb Navigation (Home >> Store >> Product Type >> Page Size/Side >> Template Name) ── --}}
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-2.5">
            <nav class="cust-breadcrumb flex items-center flex-wrap gap-2 text-xs font-semibold">
                {{-- 1. Home --}}
                <a href="{{ route('flow-pc.index') }}" class="text-slate-500 hover:text-brand-600 transition-colors">Home</a>

                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>

                {{-- 2. Selected Store --}}
                <span class="text-slate-500 font-medium">{{ $bcStoreName }}</span>

                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>

                {{-- 3. Product Type --}}
                @if ($bcTypeSlug)
                    <a href="{{ route('flow-pc.category', $bcTypeSlug) }}"
                        class="text-slate-500 hover:text-brand-600 transition-colors">{{ $bcProductType }}</a>
                @else
                    <span class="text-slate-500 font-medium">{{ $bcProductType }}</span>
                @endif

                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>

                {{-- 4. Page Size / Side --}}
                <span class="text-slate-500 font-medium">
                    {{ $bcPageSizeSide }}
                </span>

                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>

                {{-- 5. Template Name --}}
                <span class="text-slate-900 font-extrabold max-w-[280px] sm:max-w-xs truncate"
                    title="{{ $bcTemplateName }}">
                    {{ $bcTemplateName }}
                </span>
            </nav>
        </div>

        {{-- ── 1. STUDIO EDITOR WORKSPACE (TOP FULL-SCREEN FOCUS WITH DOTTED BACKGROUND) ── --}}
        <section class="w-full relative py-8 px-4 sm:px-6 lg:px-10 border-b border-slate-200/80"
            style="background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;">

            <div class="max-w-[1400px] mx-auto">

                {{-- Studio Independent Floating Layout (Centered Canvas + Absolute Floating Tools Docks) --}}
                <div class="relative w-full min-h-[80vh] flex items-center justify-center">

                    {{-- ═══ LEFT: ABSOLUTE FLOATING VERTICAL TOOL DOCK ═══ --}}
                    <div
                        class="absolute left-1 lg:left-4 top-1/2 -translate-y-1/2 grid grid-cols-2 gap-x-2 gap-y-3 justify-items-center items-start shrink-0 z-30 py-3 px-2 bg-white/50 backdrop-blur-sm rounded-3xl border border-slate-200/60 shadow-sm">

                        {{-- 1. Ready-Made Templates Button --}}
                        <button type="button" onclick="toggleTemplatesDrawer()"
                            class="group flex flex-col items-center gap-1 cursor-pointer" title="Ready-Made Templates">
                            <div id="templates-dock-btn"
                                class="w-12 h-12 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center text-brand-500 group-hover:bg-brand-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                                <i data-lucide="layout-template" class="w-5 h-5"></i>
                            </div>
                            <span
                                class="text-[10px] font-black text-slate-600 group-hover:text-brand-600 transition-colors">Templates</span>
                        </button>

                        {{-- 2. Layers Button --}}
                        <button type="button" onclick="toggleLayersDrawer()"
                            class="group flex flex-col items-center gap-1 cursor-pointer" title="Layers Panel">
                            <div id="layers-dock-btn"
                                class="w-12 h-12 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center  text-gray-500 group-hover: bg-gray-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                                <i data-lucide="layers" class="w-5 h-5"></i>
                            </div>
                            <span
                                class="text-[10px] font-black text-slate-600 group-hover: text-gray-600 transition-colors">Layers</span>
                        </button>

                        {{-- 3. Clear All Button --}}
                        <button type="button" onclick="customizer.clearAll()"
                            class="group flex flex-col items-center gap-1 cursor-pointer" title="Clear All Designs">
                            <div id="clear-dock-btn"
                                class="w-12 h-12 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center text-red-500 group-hover:bg-red-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </div>
                            <span class="text-[10px] font-black text-red-600">Clear All</span>
                        </button>
                    </div>

                    {{-- ═══ FLYOUT READY-MADE TEMPLATES STUDIO DRAWER ═══ --}}
                    <div id="templates-studio-drawer"
                        class="hidden absolute left-24 top-1/2 -translate-y-1/2 w-84 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                                    <i data-lucide="layout-template" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Ready-Made
                                    Templates</span>
                            </div>
                            <button type="button" onclick="toggleTemplatesDrawer()"
                                class="text-slate-400 hover:text-slate-600 p-1">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <p class="text-[11px] font-medium text-slate-400">Click any template to load designs onto your
                            canvas.</p>

                        {{-- Category Filter --}}
                        <div id="template-cat-filter" class="template-cat-filter flex flex-wrap gap-1.5 pb-1"></div>

                        {{-- Template Chips / Grid Container --}}
                        <div id="template-strip"
                            class="template-strip flex flex-wrap gap-2 max-h-[360px] overflow-y-auto pr-1"></div>
                    </div>

                    {{-- ═══ FLYOUT LAYERS STUDIO DRAWER (Left Side Floating Drawer) ═══ --}}
                    <div id="layers-studio-drawer"
                        class="hidden absolute left-24 top-1/2 -translate-y-1/2 w-84 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg  bg-gray-100  text-gray-600 flex items-center justify-center">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Layers Panel</span>
                            </div>
                            <button type="button" onclick="toggleLayersDrawer()"
                                class="text-slate-400 hover:text-slate-600 p-1">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <p class="text-[11px] font-medium text-slate-400">Drag items to reorder stacking order. Top layer
                            sits on front.</p>

                        <div id="layers-list" class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                            {{-- Populated dynamically via JS --}}
                        </div>
                    </div>

                    {{-- ═══ CENTER: ROCK-SOLID CENTERED CANVAS WORKSPACE ═══ --}}
                    <div class="w-full flex flex-col items-center justify-center min-w-0 space-y-6">

                        {{-- Centered Canvas Stage (80% Viewport Height Editor Stage) --}}
                        <div class="w-full flex items-center justify-center min-h-[80vh] py-6 relative" id="canvas-stage">
                            {{-- Canvas Wrapper (Centered 80vh Canvas) --}}
                            <div class="canvas-wrapper bg-white shadow-2xl rounded-2xl overflow-hidden relative mx-auto flex items-center justify-center transition-all duration-200"
                                id="canvas-container">
                                <div id="canvas-loading-overlay"
                                    class="hidden absolute inset-0 bg-white/80 backdrop-blur-xs z-50 flex flex-col items-center justify-center space-y-3 rounded-2xl transition-all duration-300">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center shadow-lg shadow-brand-500/10">
                                        <i data-lucide="loader-2" class="w-5 h-5 text-brand-600 animate-spin"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-800 tracking-wider uppercase">Loading
                                        Template...</span>
                                </div>
                                @foreach ($imageTypes as $key => $img)
                                    <div id="canvas-wrapper-{{ $key }}" class="canvas-layer"
                                        style="position:absolute;top:0;left:0;width:100%;height:100%;visibility:hidden;pointer-events:none;z-index:-1;">
                                        <canvas id="canvas-{{ $key }}"></canvas>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Hidden JS Utility Elements --}}
                        <div style="display: none !important;">
                            <div id="zoom-control">
                                <input type="range" id="zoom-slider" min="0.1" max="3" step="0.01"
                                    value="1">
                            </div>
                            <div id="mockup-preview-card">
                                <div id="mockup-stage" class="mockup-stage flat">
                                    <div class="mockup-scene">
                                        <div id="mockup-frame" class="mockup-frame">
                                            <img id="mockup-image" alt="Live preview">
                                            <div id="mockup-empty" class="mockup-empty"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ RIGHT: ABSOLUTE FLOATING VERTICAL STUDIO DOCK (Fixed Position - Never Shifts Canvas) ═══ --}}
                    <div
                        class="absolute right-1 lg:right-4 top-1/2 -translate-y-1/2 grid grid-cols-2 gap-x-2 gap-y-3 justify-items-center items-start shrink-0 z-30 py-3 px-2 bg-white/50 backdrop-blur-sm rounded-3xl border border-slate-200/60 shadow-sm">

                        {{-- 1. Photo Tool --}}
                        <label for="photo-upload-input" class="group flex flex-col items-center gap-1.5 cursor-pointer"
                            title="Upload Photo">
                            <div id="upload-icon-bg"
                                class="w-12 h-12 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-pink-500 group-hover:bg-brand-hover group-hover:text-white group-hover:scale-110 transition-all duration-200">
                                <i id="upload-icon" data-lucide="image-plus" class="w-5 h-5"></i>
                            </div>
                            <span id="upload-text"
                                class="text-xs font-bold text-slate-600 group-hover:text-brand-hover transition-colors">Photo</span>
                        </label>
                        <input type="file" onchange="customizer.handleFileUpload(this)" class="hidden"
                            id="photo-upload-input" accept="image/*" multiple>

                        {{-- 2. + Text Tool --}}
                        <button type="button" onclick="toggleTextDrawer()"
                            class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Add Custom Text">
                            <div id="text-dock-btn"
                                class="w-12 h-12 rounded-full bg-purple-100/90 border border-purple-200/90 shadow-xl shadow-purple-500/10 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
                                <i data-lucide="type" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-bold text-purple-600 group-hover:text-purple-700 transition-colors">+
                                Text</span>
                        </button>

                        {{-- 3. Color Tool --}}
                        <div class="group flex flex-col items-center gap-1.5 cursor-pointer relative" title="Text Color">
                            <div
                                class="w-12 h-12 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-brand-500 group-hover:bg-brand-600 group-hover:text-white group-hover:scale-110 transition-all duration-200 relative overflow-hidden">
                                <i data-lucide="palette" class="w-5 h-5"></i>
                                <input type="color" id="text-color-input"
                                    oninput="customizer._updateSelectedStyle('fill', this.value)"
                                    class="absolute inset-0 opacity-0 w-full h-full cursor-pointer">
                            </div>
                            <span
                                class="text-xs font-bold text-slate-600 group-hover:text-brand-600 transition-colors">Color</span>
                        </div>

                        {{-- 4. Fonts Tool --}}
                        <button type="button" onclick="toggleTextDrawer('font')"
                            class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Select Font">
                            <div
                                class="w-12 h-12 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-brand-500 group-hover:bg-brand-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
                                <i data-lucide="whole-word" class="w-5 h-5"></i>
                            </div>
                            <span
                                class="text-xs font-bold text-slate-600 group-hover:text-brand-600 transition-colors">Fonts</span>
                        </button>

                        {{-- 5. Delete Tool (Hidden when no selection) --}}
                        <button type="button" id="remove-btn" onclick="customizer.handleRemove()"
                            class="hidden group flex flex-col items-center gap-1.5 cursor-pointer" title="Remove Item">
                            <div
                                class="w-12 h-12 rounded-full bg-red-50 border border-red-200 shadow-xl shadow-red-500/10 flex items-center justify-center text-red-600 group-hover:bg-red-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-bold text-red-600" id="remove-btn-text">Remove</span>
                        </button>

                        {{-- 6. Add to Cart Button --}}
                        <form action="{{ route('flow-pc.cart.add') }}" method="POST" id="checkout-form"
                            class="flex justify-center">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="upload_ids" id="upload_ids_field">
                            <button type="button" id="submit-btn" onclick="customizer.submitAllCanvases()"
                                class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Add To Cart">
                                <div
                                    class="w-12 h-12 rounded-full bg-white text-pink-500 shadow-2xl shadow-slate-900/40 flex items-center justify-center group-hover:bg-brand-50">
                                    <i data-lucide="save" class="w-5 h-5 text-pink-500"></i>
                                </div>
                                <span class="text-xs font-black text-slate-900">Add to cart</span>
                            </button>
                        </form>
                    </div>

                    {{-- ═══ FLYOUT TYPOGRAPHY STUDIO DRAWER ═══ --}}
                    <div id="text-studio-drawer"
                        class="hidden absolute right-24 top-1/2 -translate-y-1/2 w-80 bg-white/95 backdrop-blur-xl border border-slate-200/90 rounded-[28px] p-5 shadow-2xl z-40 space-y-4 transition-all duration-300">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                    <i data-lucide="type" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Typography
                                    Studio</span>
                            </div>
                            <button type="button" onclick="toggleTextDrawer()"
                                class="text-slate-400 hover:text-slate-600 p-1">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        {{-- Text Content Input --}}
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Text
                                Message</label>
                            <div class="relative">
                                <textarea id="text-input" placeholder="Type your text here..." rows="2"
                                    oninput="customizer.onTextInputChange(this.value)"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:bg-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition-all resize-none"></textarea>
                                <button id="clear-text-btn" type="button" onclick="customizer.clearSelection()"
                                    class="hidden absolute right-2.5 top-2.5 text-slate-300 hover:text-slate-500">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Font Family Selector --}}
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Font
                                Style</label>
                            <select id="font-family-select"
                                onchange="customizer._updateSelectedStyle('fontFamily', this.value)"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-purple-500 outline-none cursor-pointer">
                                <option value="Inter" style="font-family: 'Inter'">Inter (Clean Sans)</option>
                                <option value="Playfair Display" style="font-family: 'Playfair Display'">Playfair Display
                                    (Luxury Serif)</option>
                                <option value="Dancing Script" style="font-family: 'Dancing Script'">Dancing Script
                                    (Cursive)</option>
                                <option value="Great Vibes" style="font-family: 'Great Vibes'">Great Vibes (Elegant
                                    Script)</option>
                                <option value="Pacifico" style="font-family: 'Pacifico'">Pacifico (Fun Brush)</option>
                                <option value="Permanent Marker" style="font-family: 'Permanent Marker'">Permanent Marker
                                    (Bold Marker)</option>
                                <option value="Roboto" style="font-family: 'Roboto'">Roboto (Modern)</option>
                                <option value="Open Sans" style="font-family: 'Open Sans'">Open Sans (Minimal)</option>
                                <option value="Poppins" style="font-family: 'Poppins'">Poppins (Geometric)</option>
                                <option value="Lato" style="font-family: 'Lato'">Lato (Warm Sans)</option>
                                <option value="Oswald" style="font-family: 'Oswald'">Oswald (Condensed)</option>
                                <option value="Bebas Neue" style="font-family: 'Bebas Neue'">Bebas Neue (Headline)
                                </option>
                                <option value="Anton" style="font-family: 'Anton'">Anton (Impact)</option>
                                <option value="Satisfy" style="font-family: 'Satisfy'">Satisfy (Signature)</option>
                                <option value="Caveat" style="font-family: 'Caveat'">Caveat (Handwritten)</option>
                                <option value="Lobster" style="font-family: 'Lobster'">Lobster (Vintage)</option>
                                <option value="Bangers" style="font-family: 'Bangers'">Bangers (Comic)</option>
                            </select>
                        </div>

                        {{-- Font Size & Alignment Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Size</label>
                                <select id="font-size-select"
                                    onchange="customizer._updateSelectedStyle('fontSize', parseInt(this.value))"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-purple-500 outline-none cursor-pointer">
                                    @for ($i = 10; $i <= 120; $i += 2)
                                        <option value="{{ $i }}" {{ $i == 28 ? 'selected' : '' }}>
                                            {{ $i }}px</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Alignment</label>
                                <select id="text-align-select"
                                    onchange="customizer._updateSelectedStyle('textAlign', this.value)"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-purple-500 outline-none cursor-pointer">
                                    <option value="center">Center</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                </select>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-2">
                            <button type="button" id="add-text-btn" onclick="customizer.addText()"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs py-3 rounded-xl shadow-lg shadow-purple-500/20 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Text to Canvas
                            </button>
                            <div id="editing-badge"
                                class="hidden w-full bg-purple-50 border border-purple-200 text-purple-700 font-extrabold text-xs py-2.5 rounded-xl text-center">
                                Editing Selected Text
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ── 2. HERO HEADER SECTION (BELOW EDITOR SECTION) ── --}}
        <section class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 mt-8 mb-6">
            <div
                class="hero-glass-card hero-cust-pattern relative overflow-hidden rounded-3xl p-6 sm:p-8 lg:p-10 border border-white/80 transition-all duration-300">
                {{-- Ambient lighting blobs --}}
                <div
                    class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none animate-pulse">
                </div>
                <div
                    class="absolute -bottom-20 -left-20 w-80 h-80 bg-gradient-to-tr from-pink-400/15 via-rose-400/15 to-purple-400/15 rounded-full blur-3xl pointer-events-none">
                </div>
                <div class="hero-dots opacity-40"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <a href="javascript:history.back()"
                            class="w-12 h-12 bg-white/90 hover:bg-white text-slate-600 hover:text-brand-600 rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center shrink-0 group active:scale-95"
                            title="Go Back">
                            <i data-lucide="arrow-left"
                                class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform"></i>
                        </a>
                        <div class="space-y-1">
                            <div
                                class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/90 border border-violet-200/80 text-violet-600 text-[11px] font-black shadow-2xs backdrop-blur-md tracking-wider uppercase">
                                <i data-lucide="crop" class="w-3.5 h-3.5 text-violet-500 animate-spin-slow"></i>
                                Masked Design Customizer
                            </div>
                            <h1
                                class="text-2xl lg:text-3xl xl:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                                Customize <span class="text-slate-900 italic"
                                    style="font-family: 'Playfair Display', serif;">{{ $product->name }}</span>
                            </h1>
                            <p class="text-xs lg:text-sm text-slate-500 font-semibold flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full  bg-gray-500 animate-ping"></span>
                                Upload your photo — it will be fitted to the shape guide on canvas.
                            </p>
                        </div>
                    </div>

                    @if (isset($flowData['size_width']) && isset($flowData['size_height']))
                        <div class="flex items-center gap-3 shrink-0 self-start lg:self-center">
                            <div
                                class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white/90 backdrop-blur-md border border-slate-200/90 shadow-sm text-slate-800">
                                <div
                                    class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="ruler" class="w-4.5 h-4.5"></i>
                                </div>
                                <div>
                                    <span
                                        class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Dimensions</span>
                                    <span
                                        class="text-xs font-black text-slate-900">{{ $flowData['size_width'] }}&times;{{ $flowData['size_height'] }}{{ $flowData['size_unit'] ?? '' }}</span>
                                </div>
                            </div>

                            @if (isset($flowData['size_price']))
                                <div
                                    class="flex items-center gap-3 px-4 py-2.5 rounded-2xl  bg-gray-50/90 backdrop-blur-md border  border-gray-200/90 shadow-sm  text-gray-800">
                                    <div
                                        class="w-9 h-9 rounded-xl  bg-gray-100/80  text-gray-600 flex items-center justify-center shrink-0">
                                        <i data-lucide="tag" class="w-4.5 h-4.5"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="block text-[10px] font-black uppercase tracking-wider  text-gray-600">Unit
                                            Price</span>
                                        <span
                                            class="text-xs font-black  text-gray-900">{{ \App\Services\CurrencyService::format($flowData['size_price']) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ── 3. HOW TO USE TOOL INSTRUCTION GUIDE ── --}}
        @include('quick-flow-pc.partials.customizer-instructions')

    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script>
        function toggleTextDrawer(focusTarget) {
            const textDrawer = document.getElementById('text-studio-drawer');
            const layersDrawer = document.getElementById('layers-studio-drawer');
            const templatesDrawer = document.getElementById('templates-studio-drawer');
            if (layersDrawer) layersDrawer.classList.add('hidden');
            if (templatesDrawer) templatesDrawer.classList.add('hidden');
            if (!textDrawer) return;

            if (textDrawer.classList.contains('hidden')) {
                textDrawer.classList.remove('hidden');
                if (focusTarget === 'font') {
                    document.getElementById('font-family-select')?.focus();
                } else {
                    document.getElementById('text-input')?.focus();
                }
            } else {
                textDrawer.classList.add('hidden');
            }
        }

        function toggleLayersDrawer() {
            const layersDrawer = document.getElementById('layers-studio-drawer');
            const textDrawer = document.getElementById('text-studio-drawer');
            const templatesDrawer = document.getElementById('templates-studio-drawer');
            if (textDrawer) textDrawer.classList.add('hidden');
            if (templatesDrawer) templatesDrawer.classList.add('hidden');
            if (!layersDrawer) return;

            if (layersDrawer.classList.contains('hidden')) {
                layersDrawer.classList.remove('hidden');
                customizer.renderLayersPanel();
            } else {
                layersDrawer.classList.add('hidden');
            }
        }

        function toggleTemplatesDrawer() {
            const templatesDrawer = document.getElementById('templates-studio-drawer');
            const layersDrawer = document.getElementById('layers-studio-drawer');
            const textDrawer = document.getElementById('text-studio-drawer');
            if (layersDrawer) layersDrawer.classList.add('hidden');
            if (textDrawer) textDrawer.classList.add('hidden');
            if (!templatesDrawer) return;

            if (templatesDrawer.classList.contains('hidden')) {
                templatesDrawer.classList.remove('hidden');
                customizer._initTemplates();
            } else {
                templatesDrawer.classList.add('hidden');
            }
        }

        const customizer = {
            activeCanvas: 'frame_image',
            canvases: {},
            canvasEnabled: {},
            canvasImages: {},
            uploadIds: {},
            imgScales: {},
            isUploading: false,
            isSavingComposite: false,
            selectedObject: null,

            // Default Text Styles
            textFontSize: '28',
            textFontFamily: 'Inter',
            textColor: '#000000',
            textAlign: 'center',

            // Ready-made Templates
            templates: @json($activeTemplates ?? ($product->templates ?? [])),
            templateCategories: @json($templateCategories ?? []),
            activeTplCategory: null,

            // Injected from PHP
            imageTypes: <?php echo json_encode($imageTypes); ?>,
            allMaskData: <?php echo json_encode($maskData); ?>,
            productId: <?php echo $product->id; ?>,

            _initTemplates() {
                this.renderCategoryFilter();
                this.renderTemplateChips();
            },

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

                const rawCategories = Array.isArray(this.templateCategories) ?
                    this.templateCategories :
                    Object.values(this.templateCategories || {});

                rawCategories.forEach(cat => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    const catId = cat.id !== undefined ? cat.id : cat;
                    const catName = cat.name !== undefined ? cat.name : cat;
                    const isActive = (this.activeTplCategory !== null && String(this.activeTplCategory) ==
                        String(catId));
                    btn.className = 'template-cat-chip' + (isActive ? ' active' : '');
                    btn.dataset.cat = String(catId);
                    btn.textContent = catName;
                    btn.onclick = () => this.filterTemplates(catId);
                    filter.appendChild(btn);
                });
            },

            filterTemplates(catId) {
                this.activeTplCategory = catId;
                document.querySelectorAll('.template-cat-chip').forEach(el => {
                    const isMatch = el.dataset.cat === (catId === null ? '' : String(catId));
                    el.classList.toggle('active', isMatch);
                });
                this.renderTemplateChips();
            },

            renderTemplateChips() {
                const strip = document.getElementById('template-strip');
                if (!strip) return;

                strip.querySelectorAll('.template-chip').forEach(el => el.remove());

                const tplMap = this.templates || {};
                const tplKeys = Object.keys(tplMap);

                if (tplKeys.length === 0) {
                    strip.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-6 text-slate-400 space-y-1.5 w-full text-center">
                        <i data-lucide="layout-template" class="w-8 h-8 opacity-40"></i>
                        <p class="text-xs font-semibold">No templates available</p>
                    </div>
                `;
                    if (window.lucide) window.lucide.createIcons();
                    return;
                }

                tplKeys.forEach(id => {
                    const tpl = tplMap[id];
                    if (!tpl) return;

                    const tplCatId = tpl.categoryId !== undefined ? tpl.categoryId : tpl.category_id;
                    if (this.activeTplCategory !== null && tplCatId != this.activeTplCategory) return;

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'template-chip';
                    btn.setAttribute('data-template', id);
                    btn.onclick = () => this.applyTemplate(id);

                    const iconHtml = tpl.iconUrl ?
                        `<img src="${tpl.iconUrl}" alt="" style="width:1em;height:1em;object-fit:contain;">` :
                        `<i data-lucide="${tpl.icon || 'layout-template'}"></i>`;

                    btn.innerHTML = `${iconHtml}<span>${tpl.label || tpl.name || id}</span>`;
                    strip.appendChild(btn);
                });

                if (window.lucide) window.lucide.createIcons();
            },

            async applyTemplate(id) {
                const tpl = this.templates[id];
                if (!tpl) return;

                const cv = this.canvases[this.activeCanvas];
                if (!cv || !cv.fabricCanvas) return;
                const fc = cv.fabricCanvas;
                const W = fc.width,
                    H = fc.height;

                this.showCanvasLoading('Loading Template...');

                try {
                    if (tpl.replace !== false) {
                        fc.getObjects().filter(o => o._isTemplateText || o._isTemplateImage || o._isTemplateSvg)
                            .forEach(o => fc.remove(o));
                    }

                    for (const spec of (tpl.images || [])) {
                        await this._addTemplateImage(fc, spec);
                    }

                    for (const spec of (tpl.svgs || [])) {
                        await this._addTemplateSvg(fc, spec);
                    }

                    (tpl.texts || tpl.layers || []).forEach(spec => {
                        const align = spec.textAlign || 'center';
                        const textStr = spec.text || (spec.type === 'textbox' ? 'Text' : '');
                        const t = new fabric.Textbox(textStr, {
                            left: W * (spec.xFrac ?? spec.left ?? 0.1),
                            top: H * (spec.yFrac ?? spec.top ?? 0.3),
                            originX: align === 'right' ? 'right' : (align === 'left' ? 'left' :
                                'center'),
                            originY: 'top',
                            width: W * (spec.widthFrac ?? spec.width ?? 0.8),
                            fontSize: spec.fontSize || 24,
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

                        // Mask clipping
                        const masks = (this.allMaskData[this.activeCanvas] || {}).masks || this.allMaskData
                            .masks;
                        if (Array.isArray(masks) && masks.length > 0) {
                            const clip = this._createMaskObject(masks[0], cv.scaleFactor, {
                                absolutePositioned: true
                            });
                            if (clip) t.set('clipPath', clip);
                        }

                        fc.add(t);
                    });

                    if (cv.maskGuide) cv.maskGuide.bringToFront();
                    fc.renderAll();
                    this._saveCanvasState(this.activeCanvas);
                    this.updateUI();
                } catch (err) {
                    console.error('Template loading error:', err);
                } finally {
                    setTimeout(() => {
                        this.hideCanvasLoading();
                    }, 300);
                }
            },

            showCanvasLoading(msg = 'Loading Template...') {
                const overlay = document.getElementById('canvas-loading-overlay');
                if (overlay) {
                    const txt = overlay.querySelector('span');
                    if (txt) txt.textContent = msg;
                    overlay.classList.remove('hidden');
                    if (window.lucide) window.lucide.createIcons();
                }
            },

            hideCanvasLoading() {
                const overlay = document.getElementById('canvas-loading-overlay');
                if (overlay) {
                    overlay.classList.add('hidden');
                }
            },

            _addTemplateImage(fc, spec) {
                return new Promise((resolve) => {
                    const imgUrl = spec.url || spec.src || spec.image_url || spec.image;
                    if (!imgUrl) return resolve(null);

                    const loadImage = (url, useCors) => {
                        const opts = useCors ? {
                            crossOrigin: 'anonymous'
                        } : {};
                        fabric.Image.fromURL(url, img => {
                            if (!img || !img.width) {
                                if (useCors) return loadImage(url, false);
                                return resolve(null);
                            }
                            const W = fc.width,
                                H = fc.height;
                            const scale = (W * (spec.widthFrac || spec.width_frac || 0.3)) / img
                                .width;
                            img.set({
                                originX: 'center',
                                originY: 'center',
                                left: W * (spec.xFrac ?? spec.left ?? 0.5),
                                top: H * (spec.yFrac ?? spec.top ?? 0.5),
                                scaleX: scale,
                                scaleY: scale,
                                angle: spec.angle || 0,
                                label: spec.label || 'Template Image',
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

                            // Apply mask clip
                            const masks = (this.allMaskData[this.activeCanvas] || {}).masks || this
                                .allMaskData.masks;
                            if (Array.isArray(masks) && masks.length > 0) {
                                const cv = this.canvases[this.activeCanvas];
                                const clip = this._createMaskObject(masks[0], cv ? cv.scaleFactor :
                                    1, {
                                        absolutePositioned: true
                                    });
                                if (clip) img.set('clipPath', clip);
                            }

                            fc.add(img);
                            resolve(img);
                        }, opts);
                    };

                    loadImage(imgUrl, true);
                });
            },

            _addTemplateSvg(fc, spec) {
                return new Promise((resolve) => {
                    const onLoaded = (objects, options) => {
                        if (!objects || !objects.length) return resolve(null);
                        const obj = fabric.util.groupSVGElements(objects, options);
                        const W = fc.width;
                        const baseW = obj.width || 100;
                        const scale = (W * (spec.widthFrac || 0.15)) / baseW;
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

                        // Apply mask clip
                        const masks = (this.allMaskData[this.activeCanvas] || {}).masks || this.allMaskData
                            .masks;
                        if (Array.isArray(masks) && masks.length > 0) {
                            const cv = this.canvases[this.activeCanvas];
                            const clip = this._createMaskObject(masks[0], cv ? cv.scaleFactor : 1, {
                                absolutePositioned: true
                            });
                            if (clip) obj.set('clipPath', clip);
                        }

                        fc.add(obj);
                        resolve(obj);
                    };
                    if (spec.url) fabric.loadSVGFromURL(spec.url, onLoaded);
                    else if (spec.svg) fabric.loadSVGFromString(spec.svg, onLoaded);
                    else resolve(null);
                });
            },

            init() {
                if (typeof this.allMaskData === 'string') {
                    try {
                        this.allMaskData = JSON.parse(this.allMaskData);
                    } catch (e) {
                        this.allMaskData = {};
                    }
                }

                Object.keys(this.imageTypes).forEach(key => {
                    const config = this.allMaskData[key];
                    this.canvasEnabled[key] = (config && config.enabled === false) ? false : true;
                    this.canvasImages[key] = null;
                    this.uploadIds[key] = null;
                    this.imgScales[key] = 1;
                });

                const waitForLayout = () => {
                    const cont = document.getElementById('canvas-container');
                    if (cont && cont.offsetWidth > 200) {
                        this._initAllCanvases();
                        this.updateUI();
                        this._initTemplates();
                    } else {
                        setTimeout(waitForLayout, 50);
                    }
                };
                setTimeout(waitForLayout, 50);
                window.addEventListener('resize', this._debounce(() => this._resizeAllCanvases(), 150));

                window.addEventListener('keydown', (e) => {
                    if ((e.key === 'Delete' || e.key === 'Backspace') && !['INPUT', 'TEXTAREA'].includes(
                            document.activeElement.tagName)) {
                        if (this.selectedObject) {
                            e.preventDefault();
                            this.handleRemove();
                        }
                    }
                });

                this.updateUI();
            },

            updateUI() {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                const userImagesCount = cv ? cv.fabricCanvas.getObjects().filter(o => o._isUserImage).length : 0;
                const hasImg = userImagesCount > 0 || (this.canvasImages[key] !== null);

                // Canvas visibility
                const el = document.getElementById('canvas-wrapper-' + key);
                if (el) {
                    el.style.position = 'relative';
                    el.style.visibility = 'visible';
                    el.style.pointerEvents = 'auto';
                    el.style.zIndex = '1';
                }

                // Upload icon badge & text
                const uploadIconBg = document.getElementById('upload-icon-bg');
                const uploadText = document.getElementById('upload-text');
                if (hasImg) {
                    if (uploadIconBg) uploadIconBg.className =
                        'w-12 h-12 rounded-full  bg-gray-500 text-white shadow-xl flex items-center justify-center border  border-gray-400';
                    if (uploadText) uploadText.textContent = userImagesCount > 1 ? `Photo (${userImagesCount})` :
                        'Photo';
                } else {
                    if (uploadIconBg) uploadIconBg.className =
                        'w-12 h-12 rounded-full bg-white text-pink-500 shadow-xl flex items-center justify-center border border-slate-200';
                    if (uploadText) uploadText.textContent = 'Photo';
                }

                // Zoom
                document.getElementById('zoom-control')?.classList.toggle('hidden', !hasImg);

                // Selection sync
                this._syncToolbarToSelection(this.selectedObject);

                // Remove button
                const showRemove = hasImg || !!this.selectedObject;
                const removeBtn = document.getElementById('remove-btn');
                if (removeBtn) removeBtn.classList.toggle('hidden', !showRemove);
                const removeBtnText = document.getElementById('remove-btn-text');
                if (showRemove && removeBtnText) {
                    if (this.selectedObject && (this.selectedObject._isUserText || this.selectedObject
                            ._isTemplateText)) {
                        removeBtnText.textContent = 'Remove Text';
                    } else if (this.selectedObject && (this.selectedObject._isUserImage || this.selectedObject
                            ._isTemplateImage)) {
                        removeBtnText.textContent = 'Remove Image';
                    } else {
                        removeBtnText.textContent = 'Remove';
                    }
                }

                this.renderLayersPanel();
                if (window.lucide) window.lucide.createIcons();
            },

            _calcCanvasDimensions(stageEl, customConfig) {
                const stageH = (stageEl && stageEl.offsetHeight > 200) ? stageEl.offsetHeight : Math.round(window
                    .innerHeight * 0.8);
                const stageW = (stageEl && stageEl.offsetWidth > 200) ? stageEl.offsetWidth - 180 : 900;

                const isPortrait = <?php echo ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false'; ?>;
                const adminW = (customConfig && customConfig.canvasWidth) || (isPortrait ? 400 : 560);
                const adminH = (customConfig && customConfig.canvasHeight) || (isPortrait ? 560 : 400);

                let targetH = Math.round(stageH * 0.8);
                let scaleFactor = targetH / adminH;
                let targetW = Math.round(adminW * scaleFactor);

                if (targetW > stageW) {
                    targetW = stageW;
                    scaleFactor = targetW / adminW;
                    targetH = Math.round(adminH * scaleFactor);
                }

                return {
                    displayWidth: targetW,
                    displayHeight: targetH,
                    scaleFactor,
                    adminW,
                    adminH
                };
            },

            _initAllCanvases() {
                const containerEl = document.getElementById('canvas-container');
                const stageEl = document.getElementById('canvas-stage');
                if (!containerEl) return;

                const firstKey = Object.keys(this.imageTypes)[0];
                const config = this.allMaskData[firstKey] || this.allMaskData || {};

                const {
                    displayWidth,
                    displayHeight,
                    scaleFactor,
                    adminW,
                    adminH
                } = this._calcCanvasDimensions(stageEl, config);

                containerEl.style.width = displayWidth + 'px';
                containerEl.style.height = displayHeight + 'px';
                containerEl.style.maxWidth = '100%';

                Object.keys(this.imageTypes).forEach(key => {
                    const canvasEl = document.getElementById('canvas-' + key);
                    if (!canvasEl) return;

                    const fc = new fabric.Canvas('canvas-' + key, {
                        width: displayWidth,
                        height: displayHeight,
                        backgroundColor: '#ffffff',
                        selection: false,
                        preserveObjectStacking: true,
                        allowTouchScrolling: true
                    });

                    this.canvases[key] = {
                        fabricCanvas: fc,
                        imgObj: null,
                        maskGuide: null,
                        scaleFactor,
                        adminW,
                        adminH
                    };

                    const url = this.imageTypes[key]?.url;
                    if (url) {
                        fabric.Image.fromURL(url, img => {
                            img.set({
                                left: 0,
                                top: 0,
                                scaleX: displayWidth / img.width,
                                scaleY: displayHeight / img.height,
                                selectable: false,
                                evented: false
                            });
                            fc.setBackgroundImage(img, fc.requestRenderAll.bind(fc));
                        }, {
                            crossOrigin: 'anonymous'
                        });
                    }

                    // Add mask guide (dashed outline)
                    const mData = this.allMaskData[key] || {};
                    const masks = mData.masks || this.allMaskData.masks;
                    if (Array.isArray(masks) && masks.length > 0) {
                        const m = masks[0];
                        const guide = this._createMaskObject(m, scaleFactor, {
                            fill: 'transparent',
                            stroke: '#3b82f6',
                            strokeWidth: 2,
                            strokeDashArray: [5, 5],
                            selectable: false,
                            evented: false,
                            name: 'mask_guide'
                        });
                        if (guide) {
                            fc.add(guide);
                            this.canvases[key].maskGuide = guide;
                        }
                    }

                    fc.on('selection:created', (e) => {
                        this.selectedObject = e.selected[0];
                        this.updateUI();
                    });
                    fc.on('selection:updated', (e) => {
                        this.selectedObject = e.selected[0];
                        this.updateUI();
                    });
                    fc.on('selection:cleared', () => {
                        this.selectedObject = null;
                        this.updateUI();
                    });
                    fc.on('object:modified', () => this._saveCanvasState(key));
                    fc.on('object:added', () => this._saveCanvasState(key));
                    fc.on('object:removed', () => this._saveCanvasState(key));
                    fc.on('object:scaling', (e) => {
                        if (e.target._isUserImage) {
                            this.imgScales[key] = e.target.scaleX;
                            const s = document.getElementById('zoom-slider');
                            if (s) s.value = e.target.scaleX;
                        }
                    });

                    this._loadCanvasState(key);
                    fc.renderAll();
                });
            },

            _resizeAllCanvases() {
                const containerEl = document.getElementById('canvas-container');
                const stageEl = document.getElementById('canvas-stage');
                if (!containerEl || !stageEl) return;

                const firstKey = Object.keys(this.imageTypes)[0];
                const config = this.allMaskData[firstKey] || this.allMaskData || {};

                const {
                    displayWidth: newWidth,
                    displayHeight: newH,
                    scaleFactor: newSF
                } = this._calcCanvasDimensions(stageEl, config);

                containerEl.style.width = newWidth + 'px';
                containerEl.style.height = newH + 'px';

                Object.keys(this.canvases).forEach(key => {
                    const cv = this.canvases[key];
                    if (!cv) return;

                    const ratio = newWidth / (cv.fabricCanvas.width || newWidth);
                    cv.fabricCanvas.setWidth(newWidth);
                    cv.fabricCanvas.setHeight(newH);
                    cv.fabricCanvas.getObjects().forEach(o => {
                        if (o._isUserImage || o._isUserText || o._isTemplateText || o
                            ._isTemplateImage || o._isTemplateSvg) {
                            const ratio = newWidth / (cv.fabricCanvas.width || newWidth);
                            o.set({
                                left: o.left * ratio,
                                top: o.top * ratio,
                                scaleX: o.scaleX * ratio,
                                scaleY: o.scaleY * ratio
                            });
                            const masks = (this.allMaskData[key] || {}).masks || this.allMaskData.masks;
                            if (o.clipPath && Array.isArray(masks) && masks.length > 0) {
                                const clip = this._createMaskObject(masks[0], newSF, {
                                    absolutePositioned: true,
                                    strokeWidth: 0
                                });
                                if (clip) {
                                    clip.canvas = cv.fabricCanvas;
                                    o.set('clipPath', clip);
                                }
                            }
                            o.setCoords();
                        }
                    });
                    if (cv.maskGuide) {
                        const masks = (this.allMaskData[key] || {}).masks || this.allMaskData.masks;
                        if (Array.isArray(masks) && masks.length > 0) {
                            cv.fabricCanvas.remove(cv.maskGuide);
                            const guide = this._createMaskObject(masks[0], newSF, {
                                fill: 'transparent',
                                stroke: '#3b82f6',
                                strokeWidth: 2,
                                strokeDashArray: [5, 5],
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
                    }
                    cv.scaleFactor = newSF;
                    cv.adminW = adminW;
                    cv.adminH = adminH;
                    cv.fabricCanvas.renderAll();
                });
            },

            _saveCanvasState(key) {
                const cv = this.canvases[key];
                if (!cv) return;
                const objects = cv.fabricCanvas.getObjects().filter(o => o._isUserImage || o._isUserText || o
                    ._isTemplateText || o._isTemplateImage || o._isTemplateSvg);
                const data = {
                    objects: objects.map(o => o.toObject(['_isUserImage', '_isUserText', '_isTemplateText',
                        '_isTemplateImage', '_isTemplateSvg', '_uploadId'
                    ])),
                    imgScale: this.imgScales[key],
                    uploadId: this.uploadIds[key]
                };
                localStorage.setItem(`qrinto_mask_v1_${this.productId}_${key}`, JSON.stringify(data));
            },

            _loadCanvasState(key) {
                const saved = localStorage.getItem(`qrinto_mask_v1_${this.productId}_${key}`);
                if (!saved) return;
                try {
                    const data = JSON.parse(saved);
                    const cv = this.canvases[key];
                    const fc = cv.fabricCanvas;
                    this.imgScales[key] = data.imgScale || 1;
                    this.uploadIds[key] = data.uploadId;
                    if (data.objects?.length > 0) {
                        fabric.util.enlivenObjects(data.objects, (objs) => {
                            objs.forEach(obj => {
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
                                const masks = (this.allMaskData[key] || {}).masks || this.allMaskData
                                    .masks;
                                if (Array.isArray(masks) && masks.length > 0) {
                                    const clip = this._createMaskObject(masks[0], cv.scaleFactor, {
                                        absolutePositioned: true
                                    });
                                    if (clip) obj.set('clipPath', clip);
                                }
                                if (obj._isUserImage) {
                                    cv.imgObj = obj;
                                    this.canvasImages[key] = true;
                                }
                                fc.add(obj);
                            });
                            if (cv.maskGuide) cv.maskGuide.bringToFront();
                            fc.renderAll();
                            this.updateUI();
                        });
                    }
                } catch (e) {
                    console.error('Restore error:', e);
                }
            },

            _syncToolbarToSelection(obj) {
                this._syncShapeMaskLibrary();
                this._syncShapeLibraryBorderControls();
                const clearBtn = document.getElementById('clear-text-btn');
                const addBtn = document.getElementById('add-text-btn');
                const editBadge = document.getElementById('editing-badge');
                if (!obj || !['i-text', 'text', 'textbox'].includes(obj.type)) {
                    clearBtn?.classList.add('hidden');
                    addBtn?.classList.remove('hidden');
                    editBadge?.classList.add('hidden');
                    return;
                }
                const ti = document.getElementById('text-input');
                if (ti && ti.value !== obj.text) ti.value = obj.text;
                const ff = document.getElementById('font-family-select');
                if (ff) ff.value = obj.fontFamily;
                const fs = document.getElementById('font-size-select');
                if (fs) fs.value = obj.fontSize.toString();
                const tc = document.getElementById('text-color-input');
                if (tc) tc.value = obj.fill;
                const tp = document.getElementById('text-color-preview');
                if (tp) tp.style.background = obj.fill;
                const ta = document.getElementById('text-align-select');
                if (ta) ta.value = obj.textAlign || 'center';
                clearBtn?.classList.remove('hidden');
                addBtn?.classList.add('hidden');
                editBadge?.classList.remove('hidden');
            },

            async _updateSelectedStyle(property, value) {
                if (property === 'fill') {
                    const tp = document.getElementById('text-color-preview');
                    if (tp) tp.style.background = value;
                }
                if (!this.selectedObject) return;
                if (property === 'fontFamily') {
                    try {
                        await document.fonts.load(`1em "${value}"`);
                    } catch (e) {}
                }
                this.selectedObject.set(property, value);
                const cv = this.canvases[this.activeCanvas];
                if (cv) {
                    cv.fabricCanvas.requestRenderAll();
                    this._saveCanvasState(this.activeCanvas);
                }
            },

            onTextInputChange(value) {
                if (this.selectedObject && ['i-text', 'text', 'textbox'].includes(this.selectedObject.type)) {
                    this.selectedObject.set('text', value);
                    this.canvases[this.activeCanvas]?.fabricCanvas?.requestRenderAll();
                    this._saveCanvasState(this.activeCanvas);
                    if (!value.trim()) this.handleRemove();
                } else if (value.trim()) {
                    this.addText();
                }
            },

            clearSelection() {
                const cv = this.canvases[this.activeCanvas];
                if (cv) {
                    cv.fabricCanvas.discardActiveObject().renderAll();
                    this.selectedObject = null;
                    const ti = document.getElementById('text-input');
                    if (ti) ti.value = '';
                    this.updateUI();
                }
            },

            addText() {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                if (!cv || !this.canvasEnabled[key]) return;
                const ti = document.getElementById('text-input');
                const str = (ti && ti.value.trim()) ? ti.value.trim() : 'Your Text Here';

                const ffSelect = document.getElementById('font-family-select');
                const fsSelect = document.getElementById('font-size-select');
                const tcInput = document.getElementById('text-color-input');
                const taSelect = document.getElementById('text-align-select');

                const fontFamily = ffSelect ? ffSelect.value : this.textFontFamily;
                const fontSize = fsSelect ? parseInt(fsSelect.value) : parseInt(this.textFontSize);
                const fill = tcInput ? tcInput.value : this.textColor;
                const align = taSelect ? taSelect.value : this.textAlign;

                document.fonts.load(`1em "${fontFamily}"`).then(() => {
                    const W = cv.fabricCanvas.width;
                    const H = cv.fabricCanvas.height;

                    const t = new fabric.Textbox(str, {
                        left: W / 2,
                        top: H / 2,
                        originX: 'center',
                        originY: 'center',
                        width: W * 0.7,
                        fontSize: fontSize,
                        fontFamily: fontFamily,
                        fill: fill,
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

                    // Apply mask clip
                    const masks = (this.allMaskData[key] || {}).masks || this.allMaskData.masks;
                    if (Array.isArray(masks) && masks.length > 0) {
                        const clip = this._createMaskObject(masks[0], cv.scaleFactor, {
                            absolutePositioned: true
                        });
                        if (clip) t.set('clipPath', clip);
                    }

                    cv.fabricCanvas.add(t);
                    if (cv.maskGuide) cv.maskGuide.bringToFront();
                    cv.fabricCanvas.setActiveObject(t);
                    cv.fabricCanvas.renderAll();
                    this.selectedObject = t;
                    this.updateUI();
                    if (t.canvas) {
                        t.set('fontFamily', fontFamily);
                        t.setCoords();
                        t.canvas.requestRenderAll();
                    }
                }).catch(() => {});
                this.updateUI();
            },

            _addImageToCanvas(key, url) {
                const cv = this.canvases[key];
                if (!cv || !this.canvasEnabled[key]) return;

                fabric.Image.fromURL(url, img => {
                    const fc = cv.fabricCanvas;
                    const canvasW = fc.width;
                    const canvasH = fc.height;
                    const s = Math.min(canvasW / img.width, canvasH / img.height) * 0.8;

                    const userImages = fc.getObjects().filter(o => o._isUserImage);
                    const userImgCount = userImages.length;
                    const offset = (userImgCount % 8) * 22;

                    img.set({
                        left: (canvasW - img.width * s) / 2 + offset,
                        top: (canvasH - img.height * s) / 2 + offset,
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
                        uniformScaling: true
                    });

                    // Apply mask clip
                    const masks = (this.allMaskData[key] || {}).masks || this.allMaskData.masks;
                    if (Array.isArray(masks) && masks.length > 0) {
                        const clip = this._createMaskObject(masks[0], cv.scaleFactor, {
                            absolutePositioned: true
                        });
                        if (clip) img.set('clipPath', clip);
                    }

                    fc.add(img);
                    if (cv.maskGuide) cv.maskGuide.bringToFront();
                    fc.setActiveObject(img);
                    fc.renderAll();
                    img.setCoords();
                    cv.imgObj = img;
                    this.imgScales[key] = s;
                    this.updateUI();
                }, {
                    crossOrigin: 'anonymous'
                });
            },

            async handleFileUpload(input) {
                const files = Array.from(input.files);
                if (!files.length) return;
                const key = this.activeCanvas;
                if (!this.canvasEnabled[key]) return;

                this.isUploading = true;
                this.updateUI();

                try {
                    for (const file of files) {
                        const optimized = await this._processImage(file);
                        this.canvasImages[key] = optimized.dataUrl;
                        this._addImageToCanvas(key, optimized.dataUrl);

                        const fd = new FormData();
                        fd.append('photo', file);
                        fd.append('canvas_key', key);
                        fd.append('_token', '<?php echo csrf_token(); ?>');

                        const res = await fetch('<?php echo route('flow-pc.upload'); ?>', {
                            method: 'POST',
                            body: fd
                        });
                        const dat = await res.json();
                        if (dat.success) {
                            this.uploadIds[key] = dat.upload_id;
                        }
                    }
                } catch (e) {
                    console.error('Upload error:', e);
                } finally {
                    this.isUploading = false;
                    input.value = '';
                    this.updateUI();
                }
            },

            _processImage(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            const MAX = 1600;
                            let w = img.width,
                                h = img.height;
                            if (w > MAX || h > MAX) {
                                if (w > h) {
                                    h = Math.round((h * MAX) / w);
                                    w = MAX;
                                } else {
                                    w = Math.round((w * MAX) / h);
                                    h = MAX;
                                }
                            }
                            const c = document.createElement('canvas');
                            c.width = w;
                            c.height = h;
                            const ctx = c.getContext('2d');
                            ctx.drawImage(img, 0, 0, w, h);
                            resolve({
                                dataUrl: c.toDataURL('image/jpeg', 0.88),
                                width: w,
                                height: h
                            });
                        };
                        img.onerror = reject;
                        img.src = e.target.result;
                    };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            },

            renderLayersPanel() {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                const listEl = document.getElementById('layers-list');
                if (!cv || !cv.fabricCanvas || !listEl) return;

                const objects = cv.fabricCanvas.getObjects().filter(o =>
                    o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg
                );

                if (objects.length === 0) {
                    listEl.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-8 text-slate-400 space-y-2">
                        <i data-lucide="layers" class="w-8 h-8 opacity-40"></i>
                        <p class="text-xs font-semibold">No layers added yet</p>
                        <p class="text-[10px] text-slate-400">Add photos, text or templates to manage layers</p>
                    </div>
                `;
                    if (window.lucide) window.lucide.createIcons();
                    return;
                }

                const reversed = [...objects].reverse();

                listEl.innerHTML = '';
                reversed.forEach((obj, displayIndex) => {
                    const isSelected = this.selectedObject === obj;
                    const layerItem = document.createElement('div');
                    layerItem.className = `group flex items-center justify-between gap-2.5 p-3 rounded-2xl border transition-all duration-200 cursor-pointer ${
                    isSelected ? 'bg-purple-50/90 border-purple-300 shadow-sm ring-2 ring-purple-500/20' : 'bg-slate-50/80 border-slate-200/80 hover:bg-slate-100/80'
                }`;
                    layerItem.draggable = true;

                    let iconHtml = '';
                    let labelText = '';
                    let badgeText = 'Layer';
                    let badgeColorClass = 'text-purple-500';

                    if (obj._isUserImage || obj._isTemplateImage) {
                        const src = obj._element ? obj._element.src : (obj.src || '');
                        iconHtml = src ?
                            `<img src="${src}" class="w-9 h-9 object-cover rounded-xl border border-slate-200/80 shrink-0 shadow-2xs">` :
                            `<div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="image" class="w-4 h-4"></i></div>`;
                        labelText = obj._isTemplateImage ? (obj.label || 'Template Photo') : 'Photo Layer';
                        badgeText = 'Image';
                        badgeColorClass = 'text-pink-500';
                    } else if (obj._isUserText || obj._isTemplateText) {
                        iconHtml =
                            `<div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="type" class="w-4 h-4"></i></div>`;
                        labelText = obj.text ? (obj.text.length > 16 ? obj.text.substring(0, 16) + '...' : obj
                            .text) : 'Text Layer';
                        badgeText = 'Text';
                        badgeColorClass = 'text-purple-500';
                    } else if (obj._isTemplateSvg) {
                        iconHtml =
                            `<div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="sparkles" class="w-4 h-4"></i></div>`;
                        labelText = obj.label || 'Design SVG';
                        badgeText = 'Graphic';
                        badgeColorClass = 'text-brand-500';
                    }

                    layerItem.innerHTML = `
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="cursor-grab active:cursor-grabbing text-slate-300 group-hover:text-slate-500 shrink-0 px-0.5" title="Drag to reorder">
                            <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                        </div>
                        ${iconHtml}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold ${isSelected ? 'text-purple-900' : 'text-slate-700'} truncate">${labelText}</p>
                            <span class="text-[10px] font-extrabold ${badgeColorClass} uppercase tracking-wider">${badgeText}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" class="move-up-btn p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-100/60 transition-colors" title="Bring Forward">
                            <i data-lucide="chevron-up" class="w-4 h-4"></i>
                        </button>
                        <button type="button" class="move-down-btn p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-100/60 transition-colors" title="Send Backward">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </button>
                        <button type="button" class="delete-layer-btn p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-100/60 transition-colors" title="Delete Layer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                `;

                    layerItem.addEventListener('click', (e) => {
                        if (e.target.closest('button')) return;
                        cv.fabricCanvas.setActiveObject(obj);
                        cv.fabricCanvas.renderAll();
                        this.selectedObject = obj;
                        this.updateUI();
                    });

                    const upBtn = layerItem.querySelector('.move-up-btn');
                    const downBtn = layerItem.querySelector('.move-down-btn');
                    const deleteBtn = layerItem.querySelector('.delete-layer-btn');

                    if (upBtn) upBtn.onclick = (e) => {
                        e.stopPropagation();
                        this.moveLayerUp(obj);
                    };
                    if (downBtn) downBtn.onclick = (e) => {
                        e.stopPropagation();
                        this.moveLayerDown(obj);
                    };
                    if (deleteBtn) deleteBtn.onclick = (e) => {
                        e.stopPropagation();
                        cv.fabricCanvas.remove(obj);
                        if (this.selectedObject === obj) this.selectedObject = null;
                        cv.fabricCanvas.renderAll();
                        this._saveCanvasState(key);
                        this.updateUI();
                    };

                    layerItem.addEventListener('dragstart', (e) => {
                        e.dataTransfer.setData('text/plain', displayIndex.toString());
                        layerItem.classList.add('opacity-40');
                    });
                    layerItem.addEventListener('dragend', () => {
                        layerItem.classList.remove('opacity-40');
                    });
                    layerItem.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        layerItem.classList.add('border-purple-500', 'bg-purple-50/50');
                    });
                    layerItem.addEventListener('dragleave', () => {
                        layerItem.classList.remove('border-purple-500', 'bg-purple-50/50');
                    });
                    layerItem.addEventListener('drop', (e) => {
                        e.preventDefault();
                        layerItem.classList.remove('border-purple-500', 'bg-purple-50/50');
                        const fromIndex = parseInt(e.dataTransfer.getData('text/plain'));
                        const toIndex = displayIndex;
                        if (!isNaN(fromIndex) && fromIndex !== toIndex) {
                            this.reorderLayers(fromIndex, toIndex);
                        }
                    });

                    listEl.appendChild(layerItem);
                });

                if (window.lucide) window.lucide.createIcons();
            },

            moveLayerUp(obj) {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                if (!cv || !obj) return;
                cv.fabricCanvas.bringForward(obj);
                if (cv.maskGuide) cv.maskGuide.bringToFront();
                cv.fabricCanvas.renderAll();
                this._saveCanvasState(key);
                this.renderLayersPanel();
            },

            moveLayerDown(obj) {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                if (!cv || !obj) return;
                cv.fabricCanvas.sendBackwards(obj);
                if (cv.maskGuide) cv.maskGuide.bringToFront();
                cv.fabricCanvas.renderAll();
                this._saveCanvasState(key);
                this.renderLayersPanel();
            },

            reorderLayers(fromDisplayIndex, toDisplayIndex) {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                if (!cv || !cv.fabricCanvas) return;

                const allObjects = cv.fabricCanvas.getObjects();
                const manageableObjects = allObjects.filter(o =>
                    o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg
                );
                if (manageableObjects.length === 0) return;

                const reversed = [...manageableObjects].reverse();

                if (fromDisplayIndex < 0 || fromDisplayIndex >= reversed.length) return;
                if (toDisplayIndex < 0 || toDisplayIndex >= reversed.length) return;

                const [movedObj] = reversed.splice(fromDisplayIndex, 1);
                reversed.splice(toDisplayIndex, 0, movedObj);

                const newCanvasOrder = [...reversed].reverse();

                const originalIndices = manageableObjects.map(o => allObjects.indexOf(o)).sort((a, b) => a - b);

                newCanvasOrder.forEach((obj, idx) => {
                    const targetFabricIndex = originalIndices[idx] !== undefined ? originalIndices[idx] : idx;
                    cv.fabricCanvas.moveTo(obj, targetFabricIndex);
                });

                if (cv.maskGuide) cv.maskGuide.bringToFront();
                cv.fabricCanvas.renderAll();
                this._saveCanvasState(key);
                this.renderLayersPanel();
            },

            handleRemove() {
                const cv = this.canvases[this.activeCanvas];
                if (!cv) return;
                if (this.selectedObject) {
                    cv.fabricCanvas.remove(this.selectedObject);
                    cv.fabricCanvas.discardActiveObject();
                    this.selectedObject = null;
                    const ti = document.getElementById('text-input');
                    if (ti) ti.value = '';
                } else {
                    const userImages = cv.fabricCanvas.getObjects().filter(o => o._isUserImage);
                    if (userImages.length > 0) {
                        const lastImg = userImages[userImages.length - 1];
                        cv.fabricCanvas.remove(lastImg);
                    }
                }
                const remainingImages = cv.fabricCanvas.getObjects().filter(o => o._isUserImage);
                if (remainingImages.length === 0) {
                    this.canvasImages[this.activeCanvas] = null;
                    this.uploadIds[this.activeCanvas] = null;
                }
                if (cv.maskGuide) cv.maskGuide.bringToFront();
                cv.fabricCanvas.renderAll();
                this.updateUI();
            },

            submitAllCanvases() {
                if (this.isSavingComposite) return;
                const hasUpload = Object.values(this.uploadIds).some(id => id !== null) ||
                    Object.keys(this.canvases).some(k => this.canvases[k].fabricCanvas.getObjects().some(o => o
                        ._isUserText || o._isTemplateText));
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
                    const hasEdit = this.canvasImages[key] !== null || cv.fabricCanvas.getObjects().some(
                        o => o._isUserText || o._isTemplateText);
                    if (!hasEdit) return;

                    cv.fabricCanvas.discardActiveObject();
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

                    if (guide) {
                        guide.set('visible', true);
                        cv.fabricCanvas.renderAll();
                    }

                    const res = await fetch('<?php echo route('flow-pc.upload_composite'); ?>', {
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
                    btn.innerHTML = '<i data-lucide="save"></i> Add to cart';
                    lucide.createIcons();
                });
            },

            _createMaskObject(m, sf, extraProps = {}) {
                if (!m) return null;
                const type = m.type || 'rectangle';
                const base = {
                    left: m.left * sf,
                    top: m.top * sf,
                    scaleX: (m.scaleX || 1) * sf,
                    scaleY: (m.scaleY || 1) * sf,
                    angle: m.angle || 0,
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
                        return new fabric.Polygon(Array.from({
                            length: 5
                        }, (_, i) => {
                            const a = (Math.PI * 2 * i / 5) - Math.PI / 2;
                            return {
                                x: 55 * Math.cos(a),
                                y: 55 * Math.sin(a)
                            };
                        }), base);
                    case 'star':
                        return new fabric.Polygon(Array.from({
                            length: 10
                        }, (_, i) => {
                            const r = (i % 2 === 0) ? 55 : 25;
                            const a = (Math.PI * 2 * i / 10) - Math.PI / 2;
                            return {
                                x: r * Math.cos(a),
                                y: r * Math.sin(a)
                            };
                        }), base);
                    case 'heart': {
                        const heartPathData =
                            'M 50 90 C 25 70 0 50 0 30 C 0 12 12 0 25 0 C 35 0 45 7 50 18 C 55 7 65 0 75 0 C 88 0 100 12 100 30 C 100 50 75 70 50 90 Z';
                        const hScaleX = ((m.width || 100) / 100) * (m.scaleX || 1) * sf;
                        const hScaleY = ((m.height || 90) / 90) * (m.scaleY || 1) * sf;
                        return new fabric.Path(heartPathData, {
                            ...base,
                            scaleX: hScaleX,
                            scaleY: hScaleY
                        });
                    }
                    case 'arch': {
                        const archPathData = 'M 10 120 L 10 50 C 10 15 30 0 60 0 C 90 0 110 15 110 50 L 110 120 Z';
                        const aScaleX = ((m.width || 100) / 100) * (m.scaleX || 1) * sf;
                        const aScaleY = ((m.height || 120) / 120) * (m.scaleY || 1) * sf;
                        return new fabric.Path(archPathData, {
                            ...base,
                            scaleX: aScaleX,
                            scaleY: aScaleY
                        });
                    }
                    case 'custom_polygon':
                        return new fabric.Polygon(m.points || [], base);
                    default:
                        return new fabric.Rect({
                            ...base,
                            width: m.width || 100,
                            height: m.height || 100
                        });
                }
            },

            _debounce(fn, delay) {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, a), delay);
                };
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            customizer.init();
            // Live preview
            (function() {
                let lastData = null;

                function refresh() {
                    try {
                        const key = customizer.activeCanvas;
                        const cv = customizer.canvases[key];
                        if (!cv) return;
                        const guide = cv.maskGuide;
                        if (guide) {
                            guide.set('visible', false);
                            cv.fabricCanvas.renderAll();
                        }
                        let data = cv.fabricCanvas.toDataURL({
                            format: 'jpeg',
                            quality: 0.6,
                            multiplier: 0.6
                        });
                        if (guide) {
                            guide.set('visible', true);
                            cv.fabricCanvas.renderAll();
                        }
                        if (data && data !== lastData) {
                            lastData = data;
                            const img = document.getElementById('mockup-image');
                            const frame = document.getElementById('mockup-frame');
                            const empty = document.getElementById('mockup-empty');
                            if (img) img.src = data;
                            if (frame) frame.classList.add('has-preview');
                            if (empty) empty.style.display = 'none';
                        }
                    } catch (e) {}
                }
                setInterval(refresh, 800);
            })();
        });
    </script>
@endpush
