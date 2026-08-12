{{-- ═══════════════════════════════════════════════════════════════════════
     Four-Canvas Customizer (Desktop PC Version) — DRY Refactored
     Uses shared partials + customizer-base.js
     - Four independent canvases (frame_image, sample_image, background_image, overlay_image)
     - Left Floating Dock: Page 1–4 circle buttons + Templates & Layers drawers
     - Right Floating Dock: Photo, Text, Color, Fonts, Delete, Add to Cart
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.quick-flow-pc')
@section('title', 'Customize ' . $product->name . ' Online — Qrinto Design Studio')
@section('meta_description', 'Personalize ' . $product->name . ' online with photos, custom text, shapes, and QR codes. High resolution print preview and fast store pickup.')
@section('meta_keywords', 'customize ' . strtolower($product->name) . ', design ' . strtolower($product->name) . ' online, custom ' . strtolower($product->name) . ' editor, Qrinto studio')

@section('json_ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "description": "{{ $product->short_description ?? 'Customizable print product at Qrinto' }}",
  "sku": "{{ $product->sku ?? ('SKU-' . $product->id) }}",
  "offers": {
    "@type": "Offer",
    "priceCurrency": "INR",
    "price": "{{ $product->base_price }}",
    "availability": "https://schema.org/InStock"
  }
}
</script>
@endsection

@push('styles')
    @include('quick-flow-pc.partials.customizer-styles')
    @include('quick-flow-pc.partials.customizer-fonts')
@endpush

@section('content')
    @php
        $noOfPages = (int) ($product->no_of_pages ?? 1);
        if ($noOfPages <= 1) {
            $slots = [
                'frame_image' => 'Page 1',
            ];
        } elseif ($noOfPages == 2) {
            $slots = [
                'frame_image' => 'Page 1',
                'sample_image' => 'Page 2',
            ];
        } else {
            $slots = [
                'frame_image' => 'Page 1',
                'sample_image' => 'Page 2',
                'background_image' => 'Page 3',
                'overlay_image' => 'Page 4',
            ];
        }

        $galleryImages = $product->images->values();
        $galleryIndex = 0;
        $fallbackUrl =
            $product->featured_image_url ??
            ($product->sample_image_url ??
                ($product->frame_image_url ??
                    ($product->background_image_url ??
                        'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="%2394a3b8" stroke-width="1.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/><circle cx="9" cy="9" r="2"/></svg>')));
        $imageTypes = [];
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
                            : 'Standard Size'))));

        $bcTemplateName = $product->name ?? 'Custom Template';
    @endphp

    <div id="customizer-app">

        {{-- ── Breadcrumb Navigation (Home >> Store >> Product Type >> Page Size/Side >> Template Name) ── --}}
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-1.5">
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

        {{-- ── 1. STUDIO EDITOR WORKSPACE (DOTTED BACKGROUND) ── --}}
        <section class="w-full relative py-3 px-3 sm:px-6 border-b border-slate-200/80"
            style="background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;">

            <div class="max-w-[1400px] mx-auto">

                {{-- Studio Independent Floating Layout --}}
                <div class="relative w-full min-h-[70vh] flex items-center justify-center">

                    {{-- ═══ LEFT: ABSOLUTE FLOATING VERTICAL TOOL DOCK ═══ --}}
                    <div
                        class="absolute left-1 lg:left-4 top-1/2 -translate-y-1/2 grid grid-cols-2 gap-x-2 gap-y-3 justify-items-center items-start shrink-0 z-30 py-3 px-2 bg-white/50 backdrop-blur-sm rounded-3xl border border-slate-200/60 shadow-sm">

                        {{-- Page Circle Buttons (Only if product has > 1 page) --}}
                        @if (count($imageTypes) > 1)
                            @foreach ($imageTypes as $key => $img)
                                @php
                                    $config = $maskData[$key] ?? [];
                                    $enabled = ($config['enabled'] ?? true) !== false;
                                    $isFirst = $loop->first;
                                @endphp
                                <button type="button" onclick="customizer.switchCanvas('{{ $key }}')"
                                    class="thumb-nav-btn group flex flex-col items-center gap-1 cursor-pointer {{ !$enabled ? 'disabled-tab' : '' }}"
                                    data-key="{{ $key }}" title="{{ $img['label'] }}">
                                    <div id="page-btn-{{ $key }}"
                                        class="w-12 h-12 rounded-2xl bg-white {{ $isFirst ? 'text-brand-600 shadow-md border-2 border-brand-500 scale-105' : 'text-slate-500 shadow-2xs border border-slate-200/90 group-hover:text-brand-600 group-hover:border-brand-300 group-hover:scale-105' }} flex items-center justify-center transition-all duration-200 relative {{ !$enabled ? 'opacity-70' : '' }}">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                        @if (!$enabled)
                                            <div class="thumb-lock-badge absolute -top-1 -right-1 w-4 h-4 rounded-full bg-slate-700 text-white flex items-center justify-center border border-white shadow-2xs z-20"
                                                title="Canvas Locked">
                                                <i data-lucide="lock" class="w-2.5 h-2.5"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <span
                                        class="text-[10px] {{ $isFirst ? 'font-black text-brand-600' : 'font-bold text-slate-500 group-hover:text-brand-600' }} transition-colors">
                                        Page {{ $loop->iteration }}
                                    </span>
                                </button>
                            @endforeach

                            <div id="left-dock-divider" class="col-span-2 w-full h-px bg-slate-200/80 my-0.5"></div>
                        @endif

                        {{-- Templates Button --}}
                        <button type="button" id="templates-dock-trigger" onclick="toggleTemplatesDrawer()"
                            class="group flex flex-col items-center gap-1 cursor-pointer" title="Ready-Made Templates">
                            <div id="templates-dock-btn"
                                class="w-12 h-12 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center text-brand-500 group-hover:bg-brand-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                                <i data-lucide="layout-template" class="w-5 h-5"></i>
                            </div>
                            <span
                                class="text-[10px] font-black text-slate-600 group-hover:text-brand-600 transition-colors">Templates</span>
                        </button>

                        {{-- Layers Button --}}
                        <button type="button" id="layers-dock-trigger" onclick="toggleLayersDrawer()"
                            class="group flex flex-col items-center gap-1 cursor-pointer" title="Layers Panel">
                            <div id="layers-dock-btn"
                                class="w-12 h-12 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center text-slate-600 group-hover:bg-brand-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                                <i data-lucide="layers" class="w-5 h-5"></i>
                            </div>
                            <span
                                class="text-[10px] font-black text-slate-600 group-hover:text-brand-600 transition-colors">Layers</span>
                        </button>

                    </div>

                    {{-- Shared Drawers (Templates, Layers, Typography, Shapes, QR) --}}
                    @include('quick-flow-pc.partials.customizer-drawers')
                    @include('quick-flow-pc.partials.customizer-context-menu')

                    {{-- ═══ CENTER: CENTERED CANVAS WORKSPACE ═══ --}}
                    <div class="w-full flex flex-col items-center justify-center min-w-0 space-y-2">

                        {{-- ── Studio Header Toolbar (Undo, Redo, Alignments, Zoom, Pre-flight) ── --}}
                        <div
                            class="w-full max-w-6xl bg-white/90 backdrop-blur-md border border-slate-200/90 rounded-2xl p-2.5 shadow-sm flex flex-nowrap items-center justify-between gap-2 overflow-x-auto z-20">
                            {{-- History & Edit --}}
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="customizer.undo()"
                                    class="p-2 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition-all"
                                    title="Undo (Ctrl+Z)">
                                    <i data-lucide="undo-2" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="customizer.redo()"
                                    class="p-2 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition-all"
                                    title="Redo (Ctrl+Y)">
                                    <i data-lucide="redo-2" class="w-4 h-4"></i>
                                </button>
                                <div class="w-px h-5 bg-slate-200 mx-1"></div>
                                <button type="button" onclick="customizer.duplicateSelected()"
                                    class="p-2 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition-all"
                                    title="Duplicate Object (Ctrl+D)">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="customizer.toggleLockSelected()"
                                    class="p-2 text-slate-600 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all"
                                    title="Lock / Unlock Object">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </button>

                                <div class="w-px h-5 bg-slate-200 mx-1"></div>

                                {{-- Clear All (multi trash — small faded bin beside a full bin) --}}
                                <button type="button" onclick="customizer.clearAll()"
                                    class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all"
                                    title="Clear All Designs">
                                    <span class="inline-flex items-end gap-0.5 align-middle">
                                        <i data-lucide="trash-2" class="w-3 h-3 opacity-50"></i>
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </span>
                                </button>

                                {{-- Remove Selected (single trash — only shown when something is selected) --}}
                                <button type="button" id="remove-btn" onclick="customizer.handleRemove()"
                                    class="hidden p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all"
                                    title="Remove Selected">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    <span id="remove-btn-text" class="sr-only">Remove</span>
                                </button>
                            </div>

                            {{-- Alignments --}}
                            <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200/60">
                                <button type="button" onclick="customizer.alignSelected('left')"
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-white rounded-lg transition-all"
                                    title="Align Left">
                                    <i data-lucide="align-start-vertical" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="customizer.alignSelected('center')"
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-white rounded-lg transition-all"
                                    title="Align Horizontal Center">
                                    <i data-lucide="align-center-vertical" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="customizer.alignSelected('right')"
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-white rounded-lg transition-all"
                                    title="Align Right">
                                    <i data-lucide="align-end-vertical" class="w-3.5 h-3.5"></i>
                                </button>
                                <div class="w-px h-4 bg-slate-200"></div>
                                <button type="button" onclick="customizer.alignSelected('top')"
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-white rounded-lg transition-all"
                                    title="Align Top">
                                    <i data-lucide="align-start-horizontal" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="customizer.alignSelected('middle')"
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-white rounded-lg transition-all"
                                    title="Align Vertical Middle">
                                    <i data-lucide="align-center-horizontal" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="customizer.alignSelected('bottom')"
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-white rounded-lg transition-all"
                                    title="Align Bottom">
                                    <i data-lucide="align-end-horizontal" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>

                            {{-- Rotate & Flip --}}
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="customizer.rotateSelected(90)"
                                    class="p-2 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition-all"
                                    title="Rotate 90°">
                                    <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="customizer.flipHSelected()"
                                    class="p-2 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition-all"
                                    title="Flip Horizontal">
                                    <i data-lucide="flip-horizontal" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="customizer.flipVSelected()"
                                    class="p-2 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-xl transition-all"
                                    title="Flip Vertical">
                                    <i data-lucide="flip-vertical" class="w-4 h-4"></i>
                                </button>
                                <div class="w-px h-5 bg-slate-200 mx-1"></div>
                                <button type="button" id="reposition-top-btn"
                                    onclick="customizer.enterImageRepositionMode()"
                                    class="hidden p-2 text-purple-700 bg-purple-100 hover:bg-purple-200 rounded-xl transition-all font-extrabold text-xs flex items-center gap-1.5 shadow-2xs"
                                    title="Reposition Image Inside Shape">
                                    <i data-lucide="move" class="w-4 h-4 text-purple-700"></i>
                                    <span class="hidden sm:inline">Move Image</span>
                                </button>
                                <button type="button" id="group-top-btn" onclick="customizer.groupSelected()"
                                    class="hidden p-2 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-all font-bold text-xs flex items-center gap-1.5 shadow-2xs"
                                    title="Group Selected Objects (Ctrl+G)">
                                    <i data-lucide="folder-plus" class="w-4 h-4 text-indigo-600"></i>
                                    <span class="hidden sm:inline">Group</span>
                                </button>
                                <button type="button" id="ungroup-top-btn" onclick="customizer.ungroupSelected()"
                                    class="hidden p-2 text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-xl transition-all font-bold text-xs flex items-center gap-1.5 shadow-2xs"
                                    title="Ungroup Objects (Ctrl+Shift+G)">
                                    <i data-lucide="folder-minus" class="w-4 h-4 text-amber-600"></i>
                                    <span class="hidden sm:inline">Ungroup</span>
                                </button>
                                <button type="button" id="crop-top-btn" onclick="customizer.enterCropMode()"
                                    class="hidden p-2 text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-xl transition-all font-bold text-xs flex items-center gap-1.5 shadow-2xs"
                                    title="Crop Image">
                                    <i data-lucide="crop" class="w-4 h-4 text-pink-600"></i>
                                    <span class="hidden sm:inline">Crop Image</span>
                                </button>
                                <button type="button" id="mask-top-btn" onclick="toggleShapeMaskDrawer()"
                                    class="hidden p-2 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-all font-bold text-xs flex items-center gap-1.5 shadow-2xs"
                                    title="Mask Image into Shape">
                                    <i data-lucide="shapes" class="w-4 h-4 text-emerald-600"></i>
                                    <span class="hidden sm:inline">Shape Mask</span>
                                </button>
                            </div>

                            {{-- Zoom Controls & Pre-flight Quality --}}
                            <div class="flex items-center gap-2">
                                <select onchange="customizer.setZoom(this.value)" title="Canvas Zoom Level"
                                    class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold px-2.5 py-1.5 rounded-xl outline-none cursor-pointer">
                                    <option value="1">100% (Normal)</option>
                                    <option value="1.25">125%</option>
                                    <option value="1.5">150%</option>
                                    <option value="2">200%</option>
                                </select>

                                {{-- 300 DPI Pre-flight Indicator --}}
                                <div id="preflight-badge"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[11px] font-extrabold shadow-2xs"
                                    title="Print Quality Score">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span id="preflight-text">300 DPI Optimal</span>
                                </div>
                            </div>
                        </div>

                        <div class="w-full flex items-center justify-center py-2 relative" id="canvas-stage">

                            {{-- Non-Destructive Image Crop Banner --}}
                            <div id="crop-mode-banner"
                                class="hidden absolute top-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 bg-slate-900/95 backdrop-blur-md text-white px-5 py-2.5 rounded-full shadow-2xl border border-slate-700/80 animate-fade-in">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-full bg-pink-500/20 text-pink-400 flex items-center justify-center">
                                        <i data-lucide="crop" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-200">Image Crop Mode</span>
                                </div>
                                <div class="w-px h-4 bg-slate-700"></div>

                                {{-- Aspect Ratio Selector --}}
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-slate-400">Ratio:</span>
                                    <select onchange="customizer.setCropAspectRatio(this.value)"
                                        class="bg-slate-800 text-white text-xs font-bold px-2.5 py-1 rounded-lg outline-none cursor-pointer border border-slate-700">
                                        <option value="free">Free</option>
                                        <option value="1:1">1:1 Square</option>
                                        <option value="4:3">4:3 Standard</option>
                                        <option value="16:9">16:9 Widescreen</option>
                                        <option value="original">Original</option>
                                    </select>
                                </div>

                                <div class="w-px h-4 bg-slate-700"></div>

                                {{-- Reset Crop --}}
                                <button type="button" onclick="customizer.resetCrop()"
                                    class="px-2.5 py-1 text-slate-300 hover:text-white text-xs font-bold hover:bg-slate-800 rounded-lg transition-all">
                                    Reset
                                </button>

                                {{-- Cancel --}}
                                <button type="button" onclick="customizer.cancelCrop()"
                                    class="px-2.5 py-1 text-red-400 hover:text-red-300 text-xs font-bold hover:bg-slate-800 rounded-lg transition-all flex items-center gap-1">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Cancel
                                </button>

                                {{-- Apply --}}
                                <button type="button" onclick="customizer.applyCrop()"
                                    class="bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-xs px-3.5 py-1.5 rounded-full flex items-center gap-1.5 transition-all shadow-md cursor-pointer">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Apply Crop
                                </button>
                            </div>

                            {{-- Image Reposition Mode Banner --}}
                            <div id="reposition-mode-banner"
                                class="hidden absolute top-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 bg-purple-950/95 backdrop-blur-md text-white px-5 py-2.5 rounded-full shadow-2xl border border-purple-700/80 animate-fade-in">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center">
                                        <i data-lucide="move" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs font-bold text-purple-100">Reposition Image Inside Shape
                                        Mode</span>
                                </div>
                                <div class="w-px h-4 bg-purple-700"></div>
                                <button type="button" onclick="customizer.exitImageRepositionMode()"
                                    class="bg-purple-500 hover:bg-purple-600 text-white font-black text-xs px-4 py-1.5 rounded-full flex items-center gap-1.5 transition-all shadow-md cursor-pointer">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Finish Reposition
                                </button>
                            </div>

                            {{-- Group Edit Mode Banner --}}
                            <div id="group-edit-banner"
                                class="hidden absolute top-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 bg-indigo-950/95 backdrop-blur-md text-white px-5 py-2.5 rounded-full shadow-2xl border border-indigo-700/80 animate-fade-in">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center">
                                        <i data-lucide="folder-open" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="text-xs font-bold text-indigo-100">Editing objects inside Group...</span>
                                </div>
                                <div class="w-px h-4 bg-indigo-700"></div>
                                <button type="button" onclick="customizer.exitGroupEditMode()"
                                    class="bg-indigo-500 hover:bg-indigo-600 text-white font-black text-xs px-3.5 py-1.5 rounded-full flex items-center gap-1.5 transition-all shadow-md cursor-pointer">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Done Editing
                                </button>
                            </div>

                            {{-- Locked Canvas Banner --}}
                            <div id="canvas-locked-banner"
                                class="hidden absolute top-8 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 bg-slate-800/90 backdrop-blur-sm text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-lg border border-slate-700/50">
                                <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span>Disabled for edit</span>
                            </div>

                            {{-- Canvas Wrapper --}}
                            <div class="canvas-wrapper bg-white shadow-2xl rounded-2xl overflow-hidden relative mx-auto flex items-center justify-center transition-all duration-200"
                                id="canvas-container">
                                <div id="canvas-loading-overlay"
                                    class="hidden absolute inset-0 bg-white/80 backdrop-blur-xs z-50 flex flex-col items-center justify-center space-y-3 rounded-2xl transition-all duration-300">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center shadow-lg shadow-brand-500/10">
                                        <i data-lucide="loader-2" class="w-6 h-6 text-brand-600 animate-spin"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-800 tracking-wider uppercase">Loading
                                        Template...</span>
                                </div>

                                @foreach ($imageTypes as $key => $img)
                                    @php
                                        $config = $maskData[$key] ?? [];
                                        $enabled = ($config['enabled'] ?? true) !== false;
                                        $isFirst = $loop->first;
                                    @endphp
                                    <div id="canvas-wrapper-{{ $key }}"
                                        class="canvas-layer {{ !$isFirst ? 'canvas-hidden' : '' }}"
                                        style="position:absolute;top:0;left:0;width:100%;height:100%;display:{{ $isFirst ? 'block' : 'none' }};visibility:{{ $isFirst ? 'visible' : 'hidden' }};pointer-events:{{ $isFirst ? 'auto' : 'none' }};z-index:{{ $isFirst ? '10' : '-1' }};">
                                        <canvas id="canvas-{{ $key }}"></canvas>
                                        @if (!$enabled)
                                            <div class="canvas-disabled-overlay"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Hidden JS Utility Elements --}}
                        <div style="display: none !important;">
                            <div id="zoom-control">
                                <input type="range" id="zoom-slider" min="0.1" max="3" step="0.01"
                                    value="1" oninput="customizer.updateImageScale(this.value)">
                            </div>
                        </div>
                    </div>

                    {{-- ═══ RIGHT: ABSOLUTE FLOATING VERTICAL STUDIO DOCK ═══ --}}
                    @include('quick-flow-pc.partials.customizer-right-dock', ['multiUpload' => false])

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
                    class="absolute -bottom-20 -left-20 w-80 h-80 bg-gradient-to-tr from-violet-400/15 via-fuchsia-400/15 to-pink-400/15 rounded-full blur-3xl pointer-events-none">
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

                            <h1
                                class="text-base sm:text-lg lg:text-2xl font-semibold text-slate-900 tracking-tight leading-tight">
                                Customize <span class="text-slate-800 font-semibold">{{ $product->name }}</span>
                            </h1>
                            <p class="text-xs text-slate-500 font-semibold flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                @if (count($imageTypes) == 1)
                                    Customize your design elements using the tools on the right dock.
                                @elseif (count($imageTypes) == 2)
                                    Switch between Page 1 and Page 2 from the left dock to design both sides.
                                @else
                                    Switch between pages from the left dock to design all {{ count($imageTypes) }} sides.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if (isset($flowData['size_width']) && isset($flowData['size_height']))
                        <div class="flex flex-col items-end gap-2.5 shrink-0 self-start lg:self-center">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/90 backdrop-blur-md border border-slate-200/90 shadow-2xs text-slate-800">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                        <i data-lucide="ruler" class="w-4 h-4"></i>
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
                                        class="flex items-center gap-3 px-4 py-2 rounded-2xl bg-emerald-50/90 backdrop-blur-md border border-emerald-200/90 shadow-2xs text-emerald-800">
                                        <div
                                            class="w-8 h-8 rounded-xl bg-emerald-100/80 text-emerald-600 flex items-center justify-center shrink-0">
                                            <i data-lucide="tag" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span
                                                class="block text-[10px] font-black uppercase tracking-wider text-emerald-600">Unit
                                                Price</span>
                                            <span
                                                class="text-xs font-black text-emerald-900">{{ \App\Services\CurrencyService::format($flowData['size_price']) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Add to Cart Button on Blue Marked Area --}}
                            <button type="button" onclick="customizer.submitAllCanvases()"
                                class="w-full inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider px-5 py-2.5 rounded-2xl shadow-md hover:shadow-lg transition-all cursor-pointer">
                                <i data-lucide="shopping-cart" class="w-4 h-4 text-white"></i>
                                <span>Add to Cart</span>
                            </button>
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
    <script src="{{ asset('js/customizer-base.js') }}?v={{ time() }}"></script>
    <script>
        const customizer = Object.assign(customizerBase({
            storagePrefix: 'qrinto_design_v1',
            multiCanvas: true,
            hasMasks: true,
            imageTypes: @json($imageTypes),
            allMaskData: @json($maskData),
            productId: {{ $product->id }},
            templates: @json($activeTemplates ?? ($product->templates ?? [])),
            templateCategories: @json($templateCategories ?? []),
            csrfToken: '{{ csrf_token() }}',
            uploadRoute: '{{ route('flow-pc.upload') }}',
            uploadCompositeRoute: '{{ route('flow-pc.upload_composite') }}',
            isPortrait: {{ ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false' }}
        }), {
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
                const firstEnabled = Object.keys(this.canvasEnabled).find(k => this.canvasEnabled[k] === true);
                this.activeCanvas = firstEnabled || Object.keys(this.imageTypes)[0];

                const waitForLayout = () => {
                    // Gate on the stage width, NOT the canvas-container: the container's
                    // canvas layers are all position:absolute, so it collapses to 0px until
                    // _initAllCanvases() sizes it. Waiting on it would deadlock (canvas only
                    // appeared after a manual resize fired _resizeAllCanvases).
                    const stage = document.getElementById('canvas-stage');
                    const cont = document.getElementById('canvas-container');
                    if (stage && cont && stage.offsetWidth > 200) {
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
                const enabled = this.canvasEnabled[key] !== false;
                const cv = this.canvases[key];
                const userImagesCount = cv ? cv.fabricCanvas.getObjects().filter(o => o._isUserImage).length : 0;
                const hasImg = userImagesCount > 0 || (this.canvasImages[key] !== null);

                // Canvas disabled overlay sync
                Object.keys(this.imageTypes).forEach(k => {
                    const wrapper = document.getElementById('canvas-wrapper-' + k);
                    if (!wrapper) return;
                    let overlay = wrapper.querySelector('.canvas-disabled-overlay');
                    if (this.canvasEnabled[k] === false) {
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'canvas-disabled-overlay';
                            wrapper.appendChild(overlay);
                        }
                    } else if (overlay) {
                        overlay.remove();
                    }
                });

                // Dock tools & panel triggers visibility based on lock state
                ['upload-tool-label', 'text-dock-trigger', 'shapes-dock-trigger', 'color-tool',
                    'fonts-dock-trigger', 'qr-dock-trigger', 'templates-dock-trigger', 'layers-dock-trigger',
                    'left-dock-divider'
                ].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.style.display = enabled ? '' : 'none';
                });

                // Locked canvas banner
                const lockedBanner = document.getElementById('canvas-locked-banner');
                if (lockedBanner) lockedBanner.classList.toggle('hidden', enabled);

                // Reposition image buttons visibility
                const activeObj = cv?.fabricCanvas?.getActiveObject() || this.selectedObject;
                const hasShapeImage = !!(activeObj && (activeObj._filledImage || activeObj._parentShape || activeObj
                    .clipPath || activeObj._shapeMaskType));
                const repoTopBtn = document.getElementById('reposition-top-btn');
                const repoDrawerWrap = document.getElementById('reposition-shape-btn-wrap');
                if (repoTopBtn) repoTopBtn.classList.toggle('hidden', !hasShapeImage || this.isRepositioningImage);
                if (repoDrawerWrap) repoDrawerWrap.classList.toggle('hidden', !hasShapeImage || this
                    .isRepositioningImage);

                // Shape border controls UI sync
                const borderTarget = activeObj ? (activeObj._filledImage ? activeObj : (activeObj._parentShape ||
                    activeObj)) : null;
                if (borderTarget && (borderTarget.clipPath || borderTarget._shapeMaskType || borderTarget
                        ._filledImage || borderTarget._isShape)) {
                    const isShow = borderTarget._shapeBorderShow !== false && (borderTarget._shapeBorderShow || (
                        borderTarget._shapeBorderWidth && borderTarget._shapeBorderWidth > 0));
                    const borderCol = borderTarget._shapeBorderColor || (borderTarget.stroke && borderTarget
                        .stroke !== 'transparent' ? borderTarget.stroke : '#378ADD');
                    const borderW = borderTarget._shapeBorderWidth !== undefined ? borderTarget._shapeBorderWidth :
                        (borderTarget.strokeWidth || 3);
                    const borderSt = borderTarget._shapeBorderStyle || 'solid';

                    const borderShowCb = document.getElementById('shape-border-show-checkbox');
                    const borderColorInput = document.getElementById('shape-border-color-input');
                    const borderColorVal = document.getElementById('shape-border-color-val');
                    const borderWidthSlider = document.getElementById('shape-border-width-slider');
                    const borderWidthVal = document.getElementById('shape-border-width-val');
                    const borderStyleSelect = document.getElementById('shape-border-style-select');

                    if (borderShowCb) borderShowCb.checked = !!isShow;
                    if (borderColorInput) borderColorInput.value = borderCol;
                    if (borderColorVal) borderColorVal.innerText = borderCol;
                    if (borderWidthSlider) borderWidthSlider.value = borderW;
                    if (borderWidthVal) borderWidthVal.innerText = borderW + 'px';
                    if (borderStyleSelect) borderStyleSelect.value = borderSt;
                }

                // Group / Ungroup buttons visibility sync
                const isMultiSelection = activeObj && activeObj.type === 'activeSelection';
                const isGroup = activeObj && activeObj.type === 'group';
                const groupTopBtn = document.getElementById('group-top-btn');
                const ungroupTopBtn = document.getElementById('ungroup-top-btn');
                if (groupTopBtn) groupTopBtn.classList.toggle('hidden', !isMultiSelection);
                if (ungroupTopBtn) ungroupTopBtn.classList.toggle('hidden', !isGroup);

                // Crop & Mask buttons visibility sync (ONLY for image objects)
                const isImage = !!(activeObj && (activeObj._isUserImage || activeObj._isTemplateImage || activeObj
                    .type === 'image'));
                const cropTopBtn = document.getElementById('crop-top-btn');
                const maskTopBtn = document.getElementById('mask-top-btn');
                if (cropTopBtn) cropTopBtn.classList.toggle('hidden', !isImage || this.isCroppingImage);
                if (maskTopBtn) maskTopBtn.classList.toggle('hidden', !isImage);

                // Close drawers when switching to a locked canvas
                if (!enabled && typeof closeAllDrawers === 'function') {
                    closeAllDrawers();
                }

                // Left Dock Page Buttons Active & Lock State Sync
                Object.keys(this.imageTypes).forEach((k) => {
                    const btnIcon = document.getElementById('page-btn-' + k);
                    const btnWrapper = document.querySelector('.thumb-nav-btn[data-key="' + k + '"]');
                    const isEnabled = this.canvasEnabled[k] !== false;

                    if (btnIcon) {
                        let lockBadge = btnIcon.querySelector('.thumb-lock-badge');
                        if (!isEnabled) {
                            if (!lockBadge) {
                                lockBadge = document.createElement('div');
                                lockBadge.className =
                                    'thumb-lock-badge absolute -top-1 -right-1 w-5 h-5 rounded-full bg-slate-700 text-white flex items-center justify-center border-2 border-white shadow-sm z-20';
                                lockBadge.innerHTML = '<i data-lucide="lock" class="w-2.5 h-2.5"></i>';
                                btnIcon.appendChild(lockBadge);
                            }
                        } else if (lockBadge) {
                            lockBadge.remove();
                        }

                        if (k === key) {
                            btnIcon.className =
                                'w-12 h-12 rounded-2xl bg-white text-brand-600 shadow-md border-2 border-brand-500 flex items-center justify-center scale-105 transition-all duration-200 relative ' +
                                (!isEnabled ? 'opacity-70' : '');
                            if (btnWrapper) {
                                const txt = btnWrapper.querySelector('span');
                                if (txt) txt.className = 'text-[10px] font-black text-brand-600';
                            }
                        } else {
                            btnIcon.className =
                                'w-12 h-12 rounded-2xl bg-white text-slate-500 shadow-2xs border border-slate-200/90 flex items-center justify-center group-hover:text-brand-600 group-hover:border-brand-300 group-hover:scale-105 transition-all duration-200 relative ' +
                                (!isEnabled ? 'opacity-60' : '');
                            if (btnWrapper) {
                                const txt = btnWrapper.querySelector('span');
                                if (txt) txt.className =
                                    'text-[10px] font-bold text-slate-500 group-hover:text-brand-600 transition-colors';
                            }
                        }
                    }
                });

                // Canvas Visibility
                Object.keys(this.imageTypes).forEach(k => {
                    const el = document.getElementById('canvas-wrapper-' + k);
                    if (el) {
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
                    }
                });

                // Upload icon badge & text
                const uploadIconBg = document.getElementById('upload-icon-bg');
                const uploadText = document.getElementById('upload-text');
                if (hasImg) {
                    if (uploadIconBg) uploadIconBg.className =
                        'w-12 h-12 rounded-full bg-emerald-500 text-white shadow-xl flex items-center justify-center border border-emerald-400';
                    if (uploadText) uploadText.textContent = userImagesCount > 1 ? 'Photo (' + userImagesCount +
                        ')' : 'Photo';
                } else {
                    if (uploadIconBg) uploadIconBg.className =
                        'w-12 h-12 rounded-full bg-white text-brand-500 shadow-xl flex items-center justify-center border border-slate-200';
                    if (uploadText) uploadText.textContent = 'Photo';
                }

                // Zoom
                document.getElementById('zoom-control')?.classList.toggle('hidden', !hasImg);

                // Selection sync
                this._syncToolbarToSelection(this.selectedObject);

                // Remove button
                const showRemove = enabled && (hasImg || !!this.selectedObject);
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

                const isPortrait = this._config.isPortrait;
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

                const sharedConfig = Object.keys(this.imageTypes)
                    .map(k => this.allMaskData[k] || {})
                    .find(c => c.canvasWidth && c.canvasHeight) || {};

                const {
                    displayWidth,
                    displayHeight,
                    scaleFactor,
                    adminW,
                    adminH
                } = this._calcCanvasDimensions(stageEl, sharedConfig);

                containerEl.style.height = displayHeight + 'px';
                containerEl.style.width = displayWidth + 'px';
                containerEl.style.maxWidth = '100%';

                Object.keys(this.imageTypes).forEach(key => {
                    const canvasEl = document.getElementById('canvas-' + key);
                    if (!canvasEl) return;

                    const fc = new fabric.Canvas('canvas-' + key, {
                        width: displayWidth,
                        height: displayHeight,
                        backgroundColor: null,
                        selection: true,
                        preserveObjectStacking: false,
                        allowTouchScrolling: true
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
                        const bgCors = (!url || url.startsWith('data:') || url.startsWith('blob:')) ? {} : {
                            crossOrigin: 'anonymous'
                        };
                        fabric.Image.fromURL(url, img => {
                            img.set({
                                left: -1,
                                top: -1,
                                scaleX: (displayWidth + 2) / img.width,
                                scaleY: (displayHeight + 2) / img.height,
                                selectable: false,
                                evented: false
                            });
                            fc.setBackgroundImage(img, fc.requestRenderAll.bind(fc));
                        }, bgCors);
                    }

                    // Add mask guides if applicable
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

                    if (this.canvasEnabled[key] === false) {
                        fc.selection = false;
                        fc.interactive = false;
                        fc.skipTargetFind = true;
                        fc.defaultCursor = 'not-allowed';
                        fc.hoverCursor = 'not-allowed';
                    }

                    fc.on('selection:created', (e) => {
                        if (this.canvasEnabled[key] === false) {
                            fc.discardActiveObject();
                            fc.renderAll();
                            return;
                        }
                        let sel = e.selected ? e.selected[0] : null;
                        if (!this.isRepositioningImage && sel && sel._parentShape) {
                            sel = sel._parentShape;
                            fc.setActiveObject(sel);
                        }
                        this.selectedObject = sel;
                        this.updateUI();
                    });
                    fc.on('selection:updated', (e) => {
                        if (this.canvasEnabled[key] === false) {
                            fc.discardActiveObject();
                            fc.renderAll();
                            return;
                        }
                        let sel = e.selected ? e.selected[0] : null;
                        if (!this.isRepositioningImage && sel && sel._parentShape) {
                            sel = sel._parentShape;
                            fc.setActiveObject(sel);
                        }
                        this.selectedObject = sel;
                        this.updateUI();
                    });
                    fc.on('selection:cleared', () => {
                        if (this.activeCanvas !== key) return;
                        this.selectedObject = null;
                        this.updateUI();
                    });
                    fc.on('object:modified', () => this._saveCanvasState(key));
                    fc.on('object:added', () => this._saveCanvasState(key));
                    fc.on('object:removed', () => this._saveCanvasState(key));
                    fc.on('object:moving', (e) => {
                        if (e.target && this._syncShapeAndImage) {
                            if (e.target._filledImage) {
                                this._syncShapeAndImage(e.target);
                            } else if (e.target._parentShape) {
                                this._syncShapeAndImage(e.target._parentShape);
                            } else if (e.target.clipPath || e.target._shapeMaskType) {
                                this._syncShapeAndImage(e.target);
                            }
                        }
                    });
                    fc.on('object:scaling', (e) => {
                        if (e.target._isUserImage) {
                            this.imgScales[key] = e.target.scaleX;
                            const s = document.getElementById('zoom-slider');
                            if (s) s.value = e.target.scaleX;
                        }
                        if (e.target && this._syncShapeAndImage) {
                            if (e.target._filledImage) {
                                this._syncShapeAndImage(e.target);
                            } else if (e.target._parentShape) {
                                this._syncShapeAndImage(e.target._parentShape);
                            } else if (e.target.clipPath || e.target._shapeMaskType) {
                                this._syncShapeAndImage(e.target);
                            }
                        }
                        this._saveCanvasState(key);
                    });
                    fc.on('object:rotating', (e) => {
                        if (e.target && this._syncShapeAndImage) {
                            if (e.target._filledImage) {
                                this._syncShapeAndImage(e.target);
                            } else if (e.target._parentShape) {
                                this._syncShapeAndImage(e.target._parentShape);
                            } else if (e.target.clipPath || e.target._shapeMaskType) {
                                this._syncShapeAndImage(e.target);
                            }
                        }
                    });

                    this._loadCanvasState(key);
                    fc.renderAll();
                });

                const firstKey = Object.keys(this.imageTypes)[0];
                if (firstKey) this.switchCanvas(firstKey);
            },

            _resizeAllCanvases() {
                const containerEl = document.getElementById('canvas-container');
                const stageEl = document.getElementById('canvas-stage');
                if (!containerEl || !stageEl) return;

                const sharedConfig = Object.keys(this.imageTypes)
                    .map(k => this.allMaskData[k] || {})
                    .find(c => c.canvasWidth && c.canvasHeight) || {};

                const {
                    displayWidth,
                    displayHeight,
                    scaleFactor: newSf
                } = this._calcCanvasDimensions(stageEl, sharedConfig);

                containerEl.style.width = displayWidth + 'px';
                containerEl.style.height = displayHeight + 'px';

                Object.keys(this.canvases).forEach(key => {
                    const cv = this.canvases[key];
                    if (!cv || !cv.fabricCanvas) return;

                    const newW = displayWidth;
                    const targetH = displayHeight;
                    const ratio = newW / (cv.fabricCanvas.width || newW);

                    cv.fabricCanvas.setWidth(newW);
                    cv.fabricCanvas.setHeight(targetH);

                    const bg = cv.fabricCanvas.backgroundImage;
                    if (bg) {
                        bg.left = -1;
                        bg.top = -1;
                        bg.scaleX = (newW + 2) / bg.width;
                        bg.scaleY = (targetH + 2) / bg.height;
                    }

                    cv.fabricCanvas.getObjects().forEach(o => {
                        o.left *= ratio;
                        o.top *= ratio;
                        o.scaleX *= ratio;
                        o.scaleY *= ratio;
                        if (o.clipPath) {
                            const newClip = this._createCombinedClipPath(key, newSf);
                            if (newClip) {
                                newClip.canvas = cv.fabricCanvas;
                                o.set('clipPath', newClip);
                            }
                        }
                        o.setCoords();
                    });

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
                    cv.scaleFactor = newSf;
                    cv.fabricCanvas.renderAll();
                });
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            customizer.init();
            if (customizer.initKeyboardShortcuts) customizer.initKeyboardShortcuts();

            const stage = document.getElementById('canvas-stage');
            if (stage) {
                stage.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'copy';
                    stage.classList.add('ring-4', 'ring-brand-500/50', 'rounded-2xl');
                });
                stage.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    stage.classList.remove('ring-4', 'ring-brand-500/50', 'rounded-2xl');
                });
                stage.addEventListener('drop', (e) => {
                    e.preventDefault();
                    stage.classList.remove('ring-4', 'ring-brand-500/50', 'rounded-2xl');
                    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                        customizer.handleFileDrop(e);
                    }
                });

                stage.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    const menu = document.getElementById('customizer-context-menu');
                    if (menu) {
                        menu.style.left = Math.min(e.clientX, window.innerWidth - 220) + 'px';
                        menu.style.top = Math.min(e.clientY, window.innerHeight - 260) + 'px';
                        menu.classList.remove('hidden');
                        if (window.lucide) window.lucide.createIcons();
                    }
                });
            }

            // ── Custom styled tooltips for the top toolbar ──
            (function initToolbarTooltips() {
                const bar = document.querySelector('#customizer-app .max-w-6xl');
                if (!bar) return;
                let pop = document.getElementById('tt-pop');
                if (!pop) {
                    pop = document.createElement('div');
                    pop.id = 'tt-pop';
                    document.body.appendChild(pop);
                }
                const show = (el) => {
                    let tip = el.getAttribute('data-tip') || el.getAttribute('title');
                    if (!tip) return;
                    // Move title → data-tip once, so the native tooltip doesn't double up.
                    if (el.hasAttribute('title')) {
                        el.setAttribute('data-tip', tip);
                        el.removeAttribute('title');
                    }
                    pop.textContent = tip;
                    const r = el.getBoundingClientRect();
                    let x = r.left + r.width / 2;
                    x = Math.max(48, Math.min(x, window.innerWidth - 48));
                    pop.style.left = x + 'px';
                    pop.style.top = (r.bottom + 10) + 'px';
                    pop.classList.add('show');
                };
                const hide = () => pop.classList.remove('show');
                bar.querySelectorAll('button, select, #preflight-badge').forEach((el) => {
                    el.addEventListener('mouseenter', () => show(el));
                    el.addEventListener('mouseleave', hide);
                    el.addEventListener('click', hide);
                    el.addEventListener('blur', hide);
                });
            })();
        });
    </script>
@endpush
