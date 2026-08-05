{{-- ═══════════════════════════════════════════════════════════════════════
     Four-Canvas Customizer (Desktop PC Version) — DRY Refactored
     Uses shared partials + customizer-base.js
     - Four independent canvases (frame_image, sample_image, background_image, overlay_image)
     - Left Floating Dock: Page 1–4 circle buttons + Templates & Layers drawers
     - Right Floating Dock: Photo, Text, Color, Fonts, Delete, Add to Cart
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
    'background_image' => 'Page 3',
    'overlay_image' => 'Page 4',
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
@endphp

<div id="customizer-app" class="pb-16">

    {{-- ── Hero Header ── --}}
    <section class="hero-cust-gradient hero-cust-pattern -mx-10 -mt-4 px-10 pt-8 pb-10 mb-8 relative overflow-hidden">
        <div class="hero-blob-1"></div>
        <div class="hero-blob-2"></div>
        <div class="hero-blob-3"></div>
        <div class="hero-dots"></div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            <nav class="cust-breadcrumb flex items-center gap-2 text-sm mb-4">
                <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                @if(isset($flowData['type_slug']))
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <a href="{{ route('flow-pc.category', $flowData['type_slug']) }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors">{{ $flowData['type_name'] ?? 'Category' }}</a>
                @endif
                @if(isset($flowData['size_slug']))
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <a href="{{ route('flow-pc.category', $flowData['size_slug']) }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors">{{ $flowData['size_name'] ?? 'Size' }}</a>
                @endif
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-700 font-semibold">Customize</span>
            </nav>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="javascript:history.back()"
                        class="w-11 h-11 bg-white/90 shadow-sm border border-slate-200/80 rounded-2xl flex items-center justify-center hover:bg-white hover:border-brand-200 transition-all text-slate-500 hover:text-brand-600 shrink-0">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <div>
                        <div class="inline-flex items-center gap-2 bg-white/80 border border-brand-100 text-brand-600 text-xs font-semibold px-3 py-0.5 rounded-full mb-1 shadow-sm backdrop-blur-sm">
                            <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                            Interactive Design Studio
                        </div>
                        <h1 class="text-2xl xl:text-3xl font-extrabold text-slate-900 tracking-tight">Customize <span class="bg-gradient-to-r from-brand-600 to-violet-500 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">{{ $product->name }}</span></h1>
                        <p class="text-xs text-slate-500 mt-0.5">Switch between pages from the left dock to design all four sides.</p>
                    </div>
                </div>
                @if(isset($flowData['size_width']) && isset($flowData['size_height']))
                <div class="hidden lg:flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-brand-700 text-xs font-semibold px-3.5 py-2 rounded-xl border border-brand-100 shadow-sm">
                        <i data-lucide="ruler" class="w-3.5 h-3.5 text-brand-500"></i>
                        {{ $flowData['size_width'] }}&times;{{ $flowData['size_height'] }}{{ $flowData['size_unit'] ?? '' }}
                    </span>
                    @if(isset($flowData['size_price']))
                    <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-emerald-700 text-xs font-bold px-3.5 py-2 rounded-xl border border-emerald-100 shadow-sm">
                        {{ \App\Services\CurrencyService::format($flowData['size_price']) }}
                    </span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ── 1. STUDIO EDITOR WORKSPACE (DOTTED BACKGROUND) ── --}}
    <section class="w-full relative py-8 px-4 sm:px-6 lg:px-10 border-b border-slate-200/80"
        style="background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;">

        <div class="max-w-[1400px] mx-auto">

            {{-- Studio Independent Floating Layout --}}
            <div class="relative w-full min-h-[80vh] flex items-center justify-center">

                {{-- ═══ LEFT: ABSOLUTE FLOATING VERTICAL TOOL DOCK ═══ --}}
                <div class="absolute left-2 lg:left-6 top-1/2 -translate-y-1/2 flex flex-col items-center gap-5 shrink-0 z-30 py-4 px-2">

                    {{-- Page Circle Buttons --}}
                    @foreach ($imageTypes as $key => $img)
                        @php
                            $config = $maskData[$key] ?? [];
                            $enabled = ($config['enabled'] ?? true) !== false;
                            $isFirst = $loop->first;
                        @endphp
                        <button type="button" onclick="customizer.switchCanvas('{{ $key }}')"
                            class="thumb-nav-btn group flex flex-col items-center gap-1.5 cursor-pointer {{ !$enabled ? 'disabled-tab' : '' }}"
                            data-key="{{ $key }}" title="{{ $img['label'] }}">
                            <div id="page-btn-{{ $key }}"
                                class="w-14 h-14 rounded-full bg-white {{ $isFirst ? 'text-pink-500 shadow-xl border-2 border-pink-500 scale-105' : 'text-slate-400 shadow-xl shadow-slate-300/40 border border-slate-200/90 group-hover:text-pink-500 group-hover:border-pink-300' }} flex items-center justify-center transition-all duration-200 relative {{ !$enabled ? 'opacity-70' : '' }}">
                                <i data-lucide="file-text" class="w-6 h-6"></i>
                                @if (!$enabled)
                                    <div class="thumb-lock-badge absolute -top-1 -right-1 w-5 h-5 rounded-full bg-slate-700 text-white flex items-center justify-center border-2 border-white shadow-sm z-20" title="Canvas Locked">
                                        <i data-lucide="lock" class="w-2.5 h-2.5"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs {{ $isFirst ? 'font-black text-pink-600' : 'font-bold text-slate-600 group-hover:text-pink-600' }} transition-colors">
                                Page {{ $loop->iteration }}
                            </span>
                        </button>
                    @endforeach

                    <div class="w-8 h-px bg-slate-200/80 my-0.5"></div>

                    {{-- Templates Button --}}
                    <button type="button" onclick="toggleTemplatesDrawer()"
                        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Ready-Made Templates">
                        <div id="templates-dock-btn"
                            class="w-14 h-14 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
                            <i data-lucide="layout-template" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors">Templates</span>
                    </button>

                    {{-- Layers Button --}}
                    <button type="button" onclick="toggleLayersDrawer()"
                        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Layers Panel">
                        <div id="layers-dock-btn"
                            class="w-14 h-14 rounded-full bg-white shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-600 group-hover:text-white group-hover:scale-110 transition-all duration-200">
                            <i data-lucide="layers" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black text-slate-900">Layers</span>
                    </button>
                </div>

                {{-- Shared Drawers (Templates, Layers, Typography) --}}
                @include('quick-flow-pc.partials.customizer-drawers')

                {{-- ═══ CENTER: CENTERED CANVAS WORKSPACE ═══ --}}
                <div class="w-full flex flex-col items-center justify-center min-w-0 space-y-6">

                    <div class="w-full flex items-center justify-center min-h-[80vh] py-6 relative" id="canvas-stage">

                        {{-- Locked Canvas Banner --}}
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
                            <input type="range" id="zoom-slider" min="0.1" max="3" step="0.01" value="1"
                                oninput="customizer.updateImageScale(this.value)">
                        </div>
                    </div>
                </div>

                {{-- ═══ RIGHT: ABSOLUTE FLOATING VERTICAL STUDIO DOCK ═══ --}}
                @include('quick-flow-pc.partials.customizer-right-dock', ['multiUpload' => false])

            </div>
        </div>
    </section>

    {{-- ── 2. AFTER EDITOR ACTIONS & PRODUCT OVERVIEW ── --}}
    <section class="max-w-[1400px] mx-auto px-6 py-8">
        <div class="flex items-center justify-between bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Four Page Editor</h3>
                    <p class="text-xs text-slate-500 mt-0.5">You can switch pages using the Left Floating Dock (Page 1 / 2 / 3 / 4 icons).</p>
                </div>
            </div>

            <button type="button" onclick="customizer.clearAll()" class="px-5 py-3 bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 rounded-xl text-xs font-extrabold transition-all flex items-center gap-2 shadow-xs active:scale-95">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Clear All Designs
            </button>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script src="{{ asset('js/customizer-base.js') }}"></script>
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
                    if (this.selectedObject) { e.preventDefault(); this.handleRemove(); }
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
                    if (!overlay) { overlay = document.createElement('div'); overlay.className = 'canvas-disabled-overlay'; wrapper.appendChild(overlay); }
                } else if (overlay) { overlay.remove(); }
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

            // Close drawers when switching to a locked canvas
            if (!enabled) {
                const textDrawer = document.getElementById('text-studio-drawer');
                if (textDrawer) textDrawer.classList.add('hidden');
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
                            lockBadge.className = 'thumb-lock-badge absolute -top-1 -right-1 w-5 h-5 rounded-full bg-slate-700 text-white flex items-center justify-center border-2 border-white shadow-sm z-20';
                            lockBadge.innerHTML = '<i data-lucide="lock" class="w-2.5 h-2.5"></i>';
                            btnIcon.appendChild(lockBadge);
                        }
                    } else if (lockBadge) { lockBadge.remove(); }

                    if (k === key) {
                        btnIcon.className = 'w-14 h-14 rounded-full bg-white text-pink-500 shadow-xl border-2 border-pink-500 flex items-center justify-center scale-105 transition-all duration-200 relative ' + (!isEnabled ? 'opacity-70' : '');
                        if (btnWrapper) { const txt = btnWrapper.querySelector('span'); if (txt) txt.className = 'text-xs font-black text-pink-600'; }
                    } else {
                        btnIcon.className = 'w-14 h-14 rounded-full bg-white text-slate-400 shadow-xl shadow-slate-300/40 border border-slate-200/90 flex items-center justify-center group-hover:text-pink-500 group-hover:border-pink-300 transition-all duration-200 relative ' + (!isEnabled ? 'opacity-60' : '');
                        if (btnWrapper) { const txt = btnWrapper.querySelector('span'); if (txt) txt.className = 'text-xs font-bold text-slate-600 group-hover:text-pink-600 transition-colors'; }
                    }
                }
            });

            // Canvas Visibility
            Object.keys(this.imageTypes).forEach(k => {
                const el = document.getElementById('canvas-wrapper-' + k);
                if (el) {
                    if (k === key) {
                        el.style.position = 'relative'; el.style.visibility = 'visible';
                        el.style.pointerEvents = 'auto'; el.style.zIndex = '1';
                    } else {
                        el.style.position = 'absolute'; el.style.visibility = 'hidden';
                        el.style.pointerEvents = 'none'; el.style.zIndex = '-1';
                    }
                }
            });

            // Upload icon badge & text
            const uploadIconBg = document.getElementById('upload-icon-bg');
            const uploadText = document.getElementById('upload-text');
            if (hasImg) {
                if (uploadIconBg) uploadIconBg.className = 'w-14 h-14 rounded-full bg-emerald-500 text-white shadow-xl flex items-center justify-center border border-emerald-400';
                if (uploadText) uploadText.textContent = userImagesCount > 1 ? 'Photo (' + userImagesCount + ')' : 'Photo';
            } else {
                if (uploadIconBg) uploadIconBg.className = 'w-14 h-14 rounded-full bg-white text-pink-500 shadow-xl flex items-center justify-center border border-slate-200';
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
                if (this.selectedObject && (this.selectedObject._isUserText || this.selectedObject._isTemplateText)) {
                    removeBtnText.textContent = 'Remove Text';
                } else if (this.selectedObject && (this.selectedObject._isUserImage || this.selectedObject._isTemplateImage)) {
                    removeBtnText.textContent = 'Remove Image';
                } else {
                    removeBtnText.textContent = 'Remove';
                }
            }

            this.renderLayersPanel();
            if (window.lucide) window.lucide.createIcons();
        },

        _initAllCanvases() {
            const containerEl = document.getElementById('canvas-container');
            const stageEl = document.getElementById('canvas-stage');
            if (!containerEl) return;

            const isPortrait = this._config.isPortrait;
            const stageH = (stageEl && stageEl.offsetHeight > 200) ? stageEl.offsetHeight : Math.round(window.innerHeight * 0.8);
            const targetH = Math.round(stageH * 0.8);

            const sharedConfig = Object.keys(this.imageTypes)
                .map(k => this.allMaskData[k] || {})
                .find(c => c.canvasWidth && c.canvasHeight) || {};
            const adminW = sharedConfig.canvasWidth || (isPortrait ? 400 : 560);
            const adminH = sharedConfig.canvasHeight || (isPortrait ? 560 : 400);

            const scaleFactor = targetH / adminH;
            const targetW = Math.round(adminW * scaleFactor);

            containerEl.style.height = targetH + 'px';
            containerEl.style.width = targetW + 'px';
            containerEl.style.maxWidth = '100%';

            const displayWidth = targetW;
            const displayHeight = targetH;

            Object.keys(this.imageTypes).forEach(key => {
                const canvasEl = document.getElementById('canvas-' + key);
                if (!canvasEl) return;

                const fc = new fabric.Canvas('canvas-' + key, {
                    width: displayWidth, height: displayHeight,
                    backgroundColor: null, selection: false,
                    preserveObjectStacking: true, allowTouchScrolling: true
                });

                this.canvases[key] = {
                    fabricCanvas: fc, imgObj: null,
                    scaleFactor: scaleFactor, adminW: adminW, adminH: adminH
                };

                const url = this.imageTypes[key]?.url;
                if (url) {
                    fabric.Image.fromURL(url, img => {
                        img.set({ left: -1, top: -1, scaleX: (displayWidth + 2) / img.width, scaleY: (displayHeight + 2) / img.height, selectable: false, evented: false });
                        fc.setBackgroundImage(img, fc.requestRenderAll.bind(fc));
                    }, { crossOrigin: 'anonymous' });
                }

                // Add mask guides if applicable
                const firstKey = Object.keys(this.imageTypes)[0];
                const mData = this.allMaskData[key] || {};
                const masks = mData.masks || this.allMaskData.masks;
                if (key === firstKey && Array.isArray(masks) && masks.length > 0) {
                    this.canvases[key].maskGuides = [];
                    masks.forEach((m, idx) => {
                        const guide = this._createMaskObject(m, scaleFactor, {
                            fill: 'transparent', stroke: 'rgba(0, 80, 220, 0.5)',
                            strokeWidth: 1, selectable: false, evented: false, name: 'mask_guide_' + idx
                        });
                        if (guide) { fc.add(guide); this.canvases[key].maskGuides.push(guide); }
                    });
                }

                if (this.canvasEnabled[key] === false) {
                    fc.selection = false; fc.interactive = false; fc.skipTargetFind = true;
                    fc.defaultCursor = 'not-allowed'; fc.hoverCursor = 'not-allowed';
                }

                fc.on('selection:created', (e) => {
                    if (this.canvasEnabled[key] === false) { fc.discardActiveObject(); fc.renderAll(); return; }
                    this.selectedObject = e.selected[0]; this.updateUI();
                });
                fc.on('selection:updated', (e) => {
                    if (this.canvasEnabled[key] === false) { fc.discardActiveObject(); fc.renderAll(); return; }
                    this.selectedObject = e.selected[0]; this.updateUI();
                });
                fc.on('selection:cleared', () => {
                    if (this.activeCanvas !== key) return;
                    this.selectedObject = null; this.updateUI();
                });
                fc.on('object:modified', () => this._saveCanvasState(key));
                fc.on('object:added', () => this._saveCanvasState(key));
                fc.on('object:removed', () => this._saveCanvasState(key));
                fc.on('object:scaling', (e) => {
                    if (e.target._isUserImage) {
                        this.imgScales[key] = e.target.scaleX;
                        const s = document.getElementById('zoom-slider'); if (s) s.value = e.target.scaleX;
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

            const stageH = stageEl.offsetHeight || Math.round(window.innerHeight * 0.8);
            const targetH = Math.round(stageH * 0.8);

            Object.keys(this.canvases).forEach(key => {
                const cv = this.canvases[key];
                if (!cv || !cv.fabricCanvas) return;

                const newSf = targetH / cv.adminH;
                const newW = Math.round(cv.adminW * newSf);
                const ratio = newW / (cv.fabricCanvas.width || newW);

                containerEl.style.width = newW + 'px';
                containerEl.style.height = targetH + 'px';

                cv.fabricCanvas.setWidth(newW);
                cv.fabricCanvas.setHeight(targetH);

                const bg = cv.fabricCanvas.backgroundImage;
                if (bg) { bg.left = -1; bg.top = -1; bg.scaleX = (newW + 2) / bg.width; bg.scaleY = (targetH + 2) / bg.height; }

                cv.fabricCanvas.getObjects().forEach(o => {
                    o.left *= ratio; o.top *= ratio; o.scaleX *= ratio; o.scaleY *= ratio;
                    if (o.clipPath) {
                        const newClip = this._createCombinedClipPath(key, newSf);
                        if (newClip) { newClip.canvas = cv.fabricCanvas; o.set('clipPath', newClip); }
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
                                fill: 'transparent', stroke: 'rgba(0, 80, 220, 0.5)',
                                strokeWidth: 1, selectable: false, evented: false, name: 'mask_guide_' + idx
                            });
                            if (guide) { cv.fabricCanvas.add(guide); cv.maskGuides.push(guide); guide.bringToFront(); }
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
    });
</script>
@endpush
