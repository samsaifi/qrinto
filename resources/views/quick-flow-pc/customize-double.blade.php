{{-- ═══════════════════════════════════════════════════════════════════════
     Double Canvas Customizer (Desktop PC Version)
     Mobile equivalent: quick-flow/customize-double.blade.php
     - Two independent canvases (frame_image & sample_image)
     - Uses shared partials & customizer-base.js for DRY refactoring
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.quick-flow-pc')
@section('title', 'Customize Your ' . $product->name)

@push('styles')
@include('quick-flow-pc.partials.customizer-styles')
@include('quick-flow-pc.partials.customizer-fonts')
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
foreach($slots as $field => $label) {
    $url = $product->{$field . '_url'};
    if (!$url && isset($galleryImages[$galleryIndex])) {
        $url = asset('storage/' . $galleryImages[$galleryIndex]->image_path);
        $galleryIndex++;
    }
    $imageTypes[$field] = ['label' => $label, 'url' => $url];
}
$maskData = $product->mask_data ?? [];
if (is_string($maskData)) $maskData = json_decode($maskData, true) ?? [];
$flowData = session('quick_flow_data', []);

$bcStoreName = session('active_store_name') 
    ?? (session('active_store_id') ? \App\Models\Store::find(session('active_store_id'))?->name : null)
    ?? ($product->store->name ?? null)
    ?? ($flowData['store_name'] ?? 'Store');

$bcProductType = $flowData['type_name'] 
    ?? ($product->productType->name ?? ($flowData['category_name'] ?? 'Product Type'));

$bcTypeSlug = $flowData['type_slug'] ?? ($product->productType->slug ?? null);

$bcPageSizeSide = $flowData['size_name'] 
    ?? ($flowData['size_title'] ?? null)
    ?? (isset($flowData['size_width'], $flowData['size_height']) ? $flowData['size_width'].'×'.$flowData['size_height'].($flowData['size_unit'] ?? '') : null)
    ?? ($product->no_of_pages ? ($product->no_of_pages == 1 ? 'Single Side' : ($product->no_of_pages == 2 ? 'Double Side' : $product->no_of_pages.' Pages')) : 'Double Side');

$bcTemplateName = $product->name ?? 'Custom Template';
@endphp

<div id="customizer-app" class="pb-16">

    {{-- ── Breadcrumb Navigation (Home >> Store >> Product Type >> Page Size/Side >> Template Name) ── --}}
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 py-2.5">
        <nav class="cust-breadcrumb flex items-center flex-wrap gap-2 text-xs font-semibold">
            {{-- 1. Home --}}
            <a href="{{ route('flow-pc.index') }}"
                class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
            </a>

            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>

            {{-- 2. Selected Store --}}
            <span class="text-slate-500 font-medium flex items-center gap-1">
                <i data-lucide="store" class="w-3.5 h-3.5 text-slate-400"></i> {{ $bcStoreName }}
            </span>

            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>

            {{-- 3. Product Type --}}
            @if ($bcTypeSlug)
                <a href="{{ route('flow-pc.category', $bcTypeSlug) }}"
                    class="text-slate-400 font-medium hover:text-brand-600 transition-colors">{{ $bcProductType }}</a>
            @else
                <span class="text-slate-500 font-medium">{{ $bcProductType }}</span>
            @endif

            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>

            {{-- 4. Page Size / Side --}}
            <span class="text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60">
                {{ $bcPageSizeSide }}
            </span>

            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>

            {{-- 5. Template Name --}}
            <span class="text-brand-600 font-bold max-w-[280px] sm:max-w-xs truncate" title="{{ $bcTemplateName }}">
                {{ $bcTemplateName }}
            </span>
        </nav>
    </div>

    {{-- ── 1. STUDIO EDITOR WORKSPACE (DOTTED BACKGROUND) ── --}}
    <section class="w-full relative py-8 px-4 sm:px-6 lg:px-10 border-b border-slate-200/80"
        style="background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;">

        <div class="max-w-[1400px] mx-auto">

            {{-- Studio Independent Floating Layout (Centered Canvas + Absolute Floating Tools Docks) --}}
            <div class="relative w-full min-h-[80vh] flex items-center justify-center">

                {{-- ═══ LEFT: ABSOLUTE FLOATING VERTICAL TOOL DOCK (PAGE TABS ABOVE TEMPLATE & LAYERS) ═══ --}}
                <div class="absolute left-2 lg:left-6 top-1/2 -translate-y-1/2 flex flex-col items-center gap-3 shrink-0 z-30 py-2 px-1">

                    {{-- Page Circle Buttons --}}
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
                                class="w-11 h-11 rounded-2xl bg-white {{ $isFirst ? 'text-pink-600 shadow-md border-2 border-pink-500 scale-105' : 'text-slate-500 shadow-2xs border border-slate-200/90 group-hover:text-pink-600 group-hover:border-pink-300 group-hover:scale-105' }} flex items-center justify-center transition-all duration-200 relative {{ !$enabled ? 'opacity-70' : '' }}">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                                @if (!$enabled)
                                    <div class="thumb-lock-badge absolute -top-1 -right-1 w-4 h-4 rounded-full bg-slate-700 text-white flex items-center justify-center border border-white shadow-2xs z-20" title="Canvas Locked">
                                        <i data-lucide="lock" class="w-2.5 h-2.5"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="text-[10px] {{ $isFirst ? 'font-black text-pink-600' : 'font-bold text-slate-500 group-hover:text-pink-600' }} transition-colors">
                                Page {{ $loop->iteration }}
                            </span>
                        </button>
                    @endforeach

                    <div class="w-7 h-px bg-slate-200/80 my-0.5"></div>

                    {{-- 3. Ready-Made Templates Button --}}
                    <button type="button" onclick="toggleTemplatesDrawer()"
                        class="group flex flex-col items-center gap-1 cursor-pointer" title="Ready-Made Templates">
                        <div id="templates-dock-btn"
                            class="w-11 h-11 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                            <i data-lucide="layout-template" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-600 group-hover:text-indigo-600 transition-colors">Templates</span>
                    </button>

                    {{-- 4. Layers Button --}}
                    <button type="button" onclick="toggleLayersDrawer()"
                        class="group flex flex-col items-center gap-1 cursor-pointer" title="Layers Panel">
                        <div id="layers-dock-btn"
                            class="w-11 h-11 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-600 group-hover:text-emerald-600 transition-colors">Layers</span>
                    </button>

                    {{-- 5. Clear All Button --}}
                    <button type="button" onclick="customizer.clearAll()"
                        class="group flex flex-col items-center gap-1 cursor-pointer" title="Clear All Designs">
                        <div id="clear-dock-btn"
                            class="w-11 h-11 rounded-2xl bg-white shadow-2xs border border-slate-200/90 flex items-center justify-center text-red-500 group-hover:bg-red-600 group-hover:text-white group-hover:scale-105 transition-all duration-200">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[10px] font-black text-red-600">Clear All</span>
                    </button>
                </div>

                {{-- ═══ FLYOUT DRAWERS (Templates, Layers, Typography) ═══ --}}
                @include('quick-flow-pc.partials.customizer-drawers')

                {{-- ═══ CENTER: CENTERED CANVAS WORKSPACE ═══ --}}
                <div class="w-full flex flex-col items-center justify-center min-w-0 space-y-6">

                    {{-- Centered Canvas Stage --}}
                    <div class="w-full flex items-center justify-center min-h-[80vh] py-6 relative" id="canvas-stage">

                        {{-- Locked Canvas Banner (above canvas, not inside) --}}
                        <div id="canvas-locked-banner" class="hidden absolute top-8 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 bg-slate-800/90 backdrop-blur-sm text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-lg border border-slate-700/50">
                            <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-400"></i>
                            <span>Disabled for edit</span>
                        </div>

                        {{-- Canvas Wrapper --}}
                        <div class="canvas-wrapper bg-white shadow-2xl rounded-2xl overflow-hidden relative mx-auto flex items-center justify-center transition-all duration-200"
                            id="canvas-container">
                            <div id="canvas-loading-overlay"
                                class="hidden absolute inset-0 bg-white/80 backdrop-blur-xs z-50 flex flex-col items-center justify-center space-y-3 rounded-2xl transition-all duration-300">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center shadow-lg shadow-indigo-500/10">
                                    <i data-lucide="loader-2" class="w-6 h-6 text-indigo-600 animate-spin"></i>
                                </div>
                                <span class="text-xs font-black text-slate-800 tracking-wider uppercase">Loading Template...</span>
                            </div>

                            @foreach ($imageTypes as $key => $img)
                                @php
                                    $config = $maskData[$key] ?? [];
                                    $enabled = ($config['enabled'] ?? true) !== false;
                                @endphp
                                <div id="canvas-wrapper-{{ $key }}" class="canvas-layer"
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;visibility:hidden;pointer-events:none;z-index:-1;">
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
                            <input type="range" id="zoom-slider" min="0.1" max="3" step="0.01" value="1">
                        </div>
                    </div>
                </div>

                {{-- ═══ RIGHT: ABSOLUTE FLOATING VERTICAL STUDIO DOCK ═══ --}}
                @include('quick-flow-pc.partials.customizer-right-dock', ['multiUpload' => true])

            </div>
        </div>
    </section>

    {{-- ── 2. HERO HEADER SECTION (BELOW EDITOR SECTION) ── --}}
    <section class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 mt-8 mb-6">
        <div class="hero-glass-card hero-cust-pattern relative overflow-hidden rounded-3xl p-6 sm:p-8 lg:p-10 border border-white/80 transition-all duration-300">
            {{-- Ambient lighting blobs --}}
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-gradient-to-tr from-sky-400/15 via-indigo-400/15 to-purple-400/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="hero-dots opacity-40"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <a href="javascript:history.back()"
                        class="w-12 h-12 bg-white/90 hover:bg-white text-slate-600 hover:text-brand-600 rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center shrink-0 group active:scale-95"
                        title="Go Back">
                        <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform"></i>
                    </a>
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/90 border border-indigo-200/80 text-indigo-600 text-[11px] font-black shadow-2xs backdrop-blur-md tracking-wider uppercase">
                            <i data-lucide="copy" class="w-3.5 h-3.5 text-indigo-500 animate-spin-slow"></i>
                            Double Canvas Customizer
                        </div>
                        <h1 class="text-2xl lg:text-3xl xl:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                            Customize <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">{{ $product->name }}</span>
                        </h1>
                        <p class="text-xs lg:text-sm text-slate-500 font-semibold flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                            Switch between Page 1 and Page 2 from the left dock to design both sides.
                        </p>
                    </div>
                </div>

                @if(isset($flowData['size_width']) && isset($flowData['size_height']))
                    <div class="flex items-center gap-3 shrink-0 self-start lg:self-center">
                        <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white/90 backdrop-blur-md border border-slate-200/90 shadow-sm text-slate-800">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                <i data-lucide="ruler" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Dimensions</span>
                                <span class="text-xs font-black text-slate-900">{{ $flowData['size_width'] }}&times;{{ $flowData['size_height'] }}{{ $flowData['size_unit'] ?? '' }}</span>
                            </div>
                        </div>

                        @if(isset($flowData['size_price']))
                            <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-emerald-50/90 backdrop-blur-md border border-emerald-200/90 shadow-sm text-emerald-800">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100/80 text-emerald-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="tag" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black uppercase tracking-wider text-emerald-600">Unit Price</span>
                                    <span class="text-xs font-black text-emerald-900">{{ \App\Services\CurrencyService::format($flowData['size_price']) }}</span>
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
<script src="{{ asset('js/customizer-base.js') }}"></script>
<script>
const customizer = Object.assign(customizerBase({
    imageTypes: <?php echo json_encode($imageTypes); ?>,
    allMaskData: <?php echo json_encode($maskData); ?>,
    productId: <?php echo $product->id; ?>,
    templates: @json($activeTemplates ?? ($product->templates ?? [])),
    templateCategories: @json($templateCategories ?? []),
    csrfToken: '<?php echo csrf_token(); ?>',
    uploadRoute: '<?php echo route("flow-pc.upload"); ?>',
    uploadCompositeRoute: '<?php echo route("flow-pc.upload_composite"); ?>',
    isPortrait: <?php echo ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false'; ?>,
    storagePrefix: 'qrinto_design_v1',
    multiCanvas: true,
    hasMasks: true,
}), {
    init() {
        if (typeof this.allMaskData === 'string') {
            try { this.allMaskData = JSON.parse(this.allMaskData); } catch (e) { this.allMaskData = {}; }
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
            if ((e.key === 'Delete' || e.key === 'Backspace') && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
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

        // Right dock tools visibility based on lock state
        const uploadLabel = document.getElementById('upload-tool-label');
        const textBtn = document.getElementById('text-dock-trigger');
        const colorTool = document.getElementById('color-tool');
        const fontBtn = document.getElementById('fonts-dock-trigger');
        if (uploadLabel) uploadLabel.style.display = enabled ? '' : 'none';
        if (textBtn) textBtn.style.display = enabled ? '' : 'none';
        if (colorTool) colorTool.style.display = enabled ? '' : 'none';
        if (fontBtn) fontBtn.style.display = enabled ? '' : 'none';

        // Locked canvas banner
        const lockedBanner = document.getElementById('canvas-locked-banner');
        if (lockedBanner) lockedBanner.classList.toggle('hidden', enabled);

        // Close drawers when switching to locked canvas
        if (!enabled) {
            const textDrawer = document.getElementById('text-studio-drawer');
            if (textDrawer) textDrawer.classList.add('hidden');
        }

        // Left dock page buttons active/lock state sync
        Object.keys(this.imageTypes).forEach((k) => {
            const btnIcon = document.getElementById('page-btn-' + k);
            const btnWrapper = document.querySelector(`.thumb-nav-btn[data-key="${k}"]`);
            const isEnabled = this.canvasEnabled[k] !== false;

            if (btnIcon) {
                let lockBadge = btnIcon.querySelector('.thumb-lock-badge');
                if (!isEnabled) {
                    if (!lockBadge) {
                        lockBadge = document.createElement('div');
                        lockBadge.className = 'thumb-lock-badge absolute -top-1 -right-1 w-5 h-5 rounded-full bg-slate-700 text-white flex items-center justify-center border-2 border-white shadow-sm z-20';
                        lockBadge.innerHTML = '<i data-lucide="lock" class="w-2.5 h-2.5"></i>';
                        btnIcon.appendChild(lockBadge);
                    }
                } else if (lockBadge) {
                    lockBadge.remove();
                }

                if (k === key) {
                    btnIcon.className = `w-11 h-11 rounded-2xl bg-white text-pink-600 shadow-md border-2 border-pink-500 flex items-center justify-center scale-105 transition-all duration-200 relative ${!isEnabled ? 'opacity-70' : ''}`;
                    if (btnWrapper) {
                        const txt = btnWrapper.querySelector('span');
                        if (txt) txt.className = 'text-[10px] font-black text-pink-600';
                    }
                } else {
                    btnIcon.className = `w-11 h-11 rounded-2xl bg-white text-slate-500 shadow-2xs border border-slate-200/90 flex items-center justify-center group-hover:text-pink-600 group-hover:border-pink-300 group-hover:scale-105 transition-all duration-200 relative ${!isEnabled ? 'opacity-60' : ''}`;
                    if (btnWrapper) {
                        const txt = btnWrapper.querySelector('span');
                        if (txt) txt.className = 'text-[10px] font-bold text-slate-500 group-hover:text-pink-600 transition-colors';
                    }
                }
            }
        });

        // Canvas visibility toggling
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

        // Upload icon badge
        const uploadIconBg = document.getElementById('upload-icon-bg');
        const uploadText = document.getElementById('upload-text');
        if (hasImg) {
            if (uploadIconBg) uploadIconBg.className = 'w-14 h-14 rounded-full bg-emerald-500 text-white shadow-xl flex items-center justify-center border border-emerald-400';
            if (uploadText) uploadText.textContent = userImagesCount > 1 ? `Photo (${userImagesCount})` : 'Photo';
        } else {
            if (uploadIconBg) uploadIconBg.className = 'w-14 h-14 rounded-full bg-white text-pink-500 shadow-xl flex items-center justify-center border border-slate-200';
            if (uploadText) uploadText.textContent = 'Photo';
        }

        // Zoom control visibility
        document.getElementById('zoom-control')?.classList.toggle('hidden', !hasImg);

        // Selection sync
        this._syncToolbarToSelection(this.selectedObject);

        // Remove button text
        const showRemove = enabled && (hasImg || !!this.selectedObject);
        const removeBtn = document.getElementById('remove-btn');
        if (removeBtn) removeBtn.classList.toggle('hidden', !showRemove);
        const removeBtnText = document.getElementById('remove-btn-text');
        if (showRemove && removeBtnText) {
            if (this.selectedObject && (this.selectedObject._isUserText || this.selectedObject._isTemplateText)) {
                removeBtnText.textContent = 'Remove Text';
            } else if (this.selectedObject && (this.selectedObject._isUserImage || this.selectedObject._isTemplateImage)) {
                removeBtnText.textContent = 'Remove Image';
            } else {
                removeBtnText.textContent = 'Remove';
            }
        }

        // Render layers panel
        this.renderLayersPanel();
        if (window.lucide) window.lucide.createIcons();
    },

    _calcCanvasDimensions(stageEl, customConfig) {
        const stageH = (stageEl && stageEl.offsetHeight > 200) ? stageEl.offsetHeight : Math.round(window.innerHeight * 0.8);
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

        return { displayWidth: targetW, displayHeight: targetH, scaleFactor, adminW, adminH };
    },

    _initAllCanvases() {
        const containerEl = document.getElementById('canvas-container');
        const stageEl = document.getElementById('canvas-stage');
        if (!containerEl) return;

        const sharedConfig = Object.keys(this.imageTypes)
            .map(k => this.allMaskData[k] || {})
            .find(c => c.canvasWidth && c.canvasHeight) || {};

        const { displayWidth, displayHeight, scaleFactor, adminW, adminH } = this._calcCanvasDimensions(stageEl, sharedConfig);

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
                selection: false,
                preserveObjectStacking: true,
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
                fabric.Image.fromURL(url, img => {
                    img.set({
                        left: -1, top: -1,
                        scaleX: (displayWidth + 2) / img.width,
                        scaleY: (displayHeight + 2) / img.height,
                        selectable: false, evented: false,
                    });
                    fc.setBackgroundImage(img, fc.requestRenderAll.bind(fc));
                }, { crossOrigin: 'anonymous' });
            }

            // Add mask guides for firstKey
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

            // Disable canvas interaction if canvasEnabled[key] is false
            if (this.canvasEnabled[key] === false) {
                fc.selection = false;
                fc.interactive = false;
                fc.skipTargetFind = true;
                fc.defaultCursor = 'not-allowed';
                fc.hoverCursor = 'not-allowed';
            }

            // Bind selection/object events
            fc.on('selection:created', (e) => {
                if (this.canvasEnabled[key] === false) { fc.discardActiveObject(); fc.renderAll(); return; }
                this.selectedObject = e.selected[0];
                this.updateUI();
            });
            fc.on('selection:updated', (e) => {
                if (this.canvasEnabled[key] === false) { fc.discardActiveObject(); fc.renderAll(); return; }
                this.selectedObject = e.selected[0];
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
            fc.on('object:scaling', (e) => {
                if (e.target._isUserImage) {
                    this.imgScales[key] = e.target.scaleX;
                    const s = document.getElementById('zoom-slider');
                    if (s) s.value = e.target.scaleX;
                }
                this._saveCanvasState(key);
            });

            this._loadCanvasState(key);
            fc.renderAll();
        });
    },

    _resizeAllCanvases() {
        const containerEl = document.getElementById('canvas-container');
        const stageEl = document.getElementById('canvas-stage');
        if (!containerEl || !stageEl) return;

        const sharedConfig = Object.keys(this.imageTypes)
            .map(k => this.allMaskData[k] || {})
            .find(c => c.canvasWidth && c.canvasHeight) || {};

        const { displayWidth, displayHeight, scaleFactor: newSf } = this._calcCanvasDimensions(stageEl, sharedConfig);

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
                bg.left = -1; bg.top = -1;
                bg.scaleX = (newW + 2) / bg.width;
                bg.scaleY = (targetH + 2) / bg.height;
            }

            cv.fabricCanvas.getObjects().forEach(o => {
                o.left *= ratio; o.top *= ratio;
                o.scaleX *= ratio; o.scaleY *= ratio;
                if (o.clipPath) {
                    const newClip = this._createCombinedClipPath(key, newSf);
                    if (newClip) { newClip.canvas = cv.fabricCanvas; o.set('clipPath', newClip); }
                }
                o.setCoords();
            });

            // Recreate mask guides
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
    },
});

document.addEventListener('DOMContentLoaded', () => customizer.init());
</script>
@endpush
