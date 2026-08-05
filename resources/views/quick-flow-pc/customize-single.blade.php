{{-- ═══════════════════════════════════════════════════════════════════════
     Single Canvas Customizer (Desktop) — No Masking
     Mobile equivalent: quick-flow/customize-single.blade.php
     - Single canvas (frame_image only, no page tabs)
     - Dynamic aspect ratio from product pdf_orientation
     - No masking / clip-path
     - Uses shared partials + customizer-base.js for DRY
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.quick-flow-pc')
@section('title', 'Customize Your ' . $product->name)

@push('styles')
    @include('quick-flow-pc.partials.customizer-styles')
    @include('quick-flow-pc.partials.customizer-fonts')

    {{-- Single-specific CSS: upload-zone, text-toolbar, mockup preview --}}
    <style>
        /* Upload Zone */
        .upload-zone {
            border: 2px dashed #e2e8f0; border-radius: 1rem; padding: 1rem;
            background: #f8fafc; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;
        }
        .upload-zone:hover {
            border-color: var(--color-brand-500, #ec4899); background: #fdf2f8; transform: translateY(-1px);
        }
        .upload-zone.has-image { border-style: solid; border-color: #10b981; background: #f0fdf4; }

        /* Text Toolbar */
        .text-toolbar { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        .text-toolbar input[type="text"], .text-toolbar textarea {
            border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 10px 14px; font-size: 14px;
            background: #f8fafc; transition: all 0.2s; width: 100%; resize: none; min-height: 44px;
        }
        .text-toolbar input[type="text"]:focus, .text-toolbar textarea:focus {
            border-color: var(--color-brand-500, #ec4899); box-shadow: 0 0 0 3px rgba(236,72,153,0.1); outline: none; background: #fff;
        }
        .text-toolbar select {
            border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 8px 12px; font-size: 13px; font-weight: 600;
            background: #f8fafc; cursor: pointer; outline: none; -webkit-appearance: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; transition: border-color 0.2s;
        }
        .text-toolbar select:focus { border-color: var(--color-brand-500, #ec4899); }
        .text-toolbar button { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; }

        /* Mockup Preview */
        .preview-toggle-btn { color: #64748b; transition: all 0.2s ease; cursor: pointer; border: none; background: transparent; }
        .preview-toggle-btn.active { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
        .mockup-stage {
            border-radius: 16px; overflow: hidden; transition: background 0.35s ease, padding 0.35s ease;
            display: flex; align-items: center; justify-content: center; min-height: 200px;
        }
        .mockup-stage.flat { background: #f8fafc; padding: 24px; }
        .mockup-stage.room {
            background: linear-gradient(180deg, #eef2f7 0%, #e6ebf2 62%, #dfe5ee 62%, #d3dae4 100%);
            padding: 24px 24px 40px; position: relative;
        }
        .mockup-stage.room::after {
            content: ""; position: absolute; left: 0; right: 0; bottom: 26px; height: 2px; background: rgba(15,23,42,0.08);
        }
        .mockup-scene { display: flex; align-items: center; justify-content: center; width: 100%; }
        .mockup-frame {
            position: relative; display: inline-block; background: #fff;
            transition: max-width 0.35s cubic-bezier(0.4,0,0.2,1), border 0.35s ease, box-shadow 0.35s ease, padding 0.35s ease;
        }
        .mockup-stage.flat .mockup-frame { max-width: 100%; border-radius: 6px; padding: 0; box-shadow: 0 10px 30px -12px rgba(15,23,42,0.28); }
        .mockup-stage.room .mockup-frame { max-width: 74%; border: 10px solid #fff; border-radius: 2px; padding: 4px; box-shadow: 0 2px 3px rgba(0,0,0,0.1), 0 22px 44px -14px rgba(15,23,42,0.48); }
        .mockup-frame img { display: block; width: 100%; height: auto; border-radius: 2px; }
        .mockup-empty {
            position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 6px; min-height: 160px; color: #94a3b8; font-size: 12px; font-weight: 600;
        }
    </style>
@endpush

@section('content')
@php
    $imageTypes = [];
    $slots = ['frame_image' => 'Page 1'];
    $galleryImages = $product->images->values();
    $galleryIndex = 0;
    foreach ($slots as $field => $label) {
        $url = $product->{$field . '_url'};
        if (!$url && isset($galleryImages[$galleryIndex])) {
            $url = asset('storage/' . $galleryImages[$galleryIndex]->image_path);
            $galleryIndex++;
        }
        $imageTypes[$field] = ['label' => $label, 'url' => $url];
    }
    $maskData = [];
    $flowData = session('quick_flow_data', []);
@endphp

<div id="customizer-app" class="w-full pb-24">

    {{-- ── 1. STUDIO EDITOR WORKSPACE (DOTTED BACKGROUND) ── --}}
    <section class="w-full relative py-8 px-4 sm:px-6 lg:px-10 border-b border-slate-200/80"
        style="background-color: #f8fafc; background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;">

        <div class="max-w-[1400px] mx-auto">
            <div class="relative w-full min-h-[80vh] flex items-center justify-center">

                {{-- ═══ LEFT: FLOATING DOCK (Templates & Layers only — NO page buttons) ═══ --}}
                <div class="absolute left-2 lg:left-6 top-1/2 -translate-y-1/2 h-[488px] flex flex-col justify-end items-center gap-6 shrink-0 z-30 py-4 px-2">
                    {{-- Templates --}}
                    <button type="button" onclick="toggleTemplatesDrawer()"
                        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Ready-Made Templates">
                        <div id="templates-dock-btn"
                            class="w-14 h-14 rounded-full bg-white text-indigo-600 shadow-2xl shadow-slate-900/40 flex items-center justify-center border border-slate-200/90 group-hover:bg-indigo-50 group-hover:scale-110 transition-all duration-200">
                            <i data-lucide="layout-template" class="w-6 h-6 text-indigo-600"></i>
                        </div>
                        <span class="text-xs font-black text-slate-900">Templates</span>
                    </button>
                    {{-- Layers --}}
                    <button type="button" onclick="toggleLayersDrawer()"
                        class="group flex flex-col items-center gap-1.5 cursor-pointer" title="Canvas Layers Panel">
                        <div id="layers-dock-btn"
                            class="w-14 h-14 rounded-full bg-white text-emerald-600 shadow-2xl shadow-slate-900/40 flex items-center justify-center border border-slate-200/90 group-hover:bg-emerald-50 group-hover:scale-110 transition-all duration-200">
                            <i data-lucide="layers" class="w-6 h-6 text-emerald-600"></i>
                        </div>
                        <span class="text-xs font-black text-slate-900">Layers</span>
                    </button>
                </div>

                {{-- Shared Flyout Drawers (Templates, Layers, Typography) --}}
                @include('quick-flow-pc.partials.customizer-drawers')

                {{-- ═══ CENTER: CANVAS WORKSPACE ═══ --}}
                <div class="w-full flex flex-col items-center justify-center min-w-0 space-y-6">
                    <div class="w-full flex items-center justify-center min-h-[80vh] py-6 relative" id="canvas-stage">
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
                                <div id="canvas-wrapper-{{ $key }}" class="canvas-layer"
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;visibility:hidden;pointer-events:none;z-index:-1;">
                                    <canvas id="canvas-{{ $key }}"></canvas>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Hidden Mockup Preview Elements --}}
                    <div style="display: none !important;">
                        <div id="zoom-control">
                            <input type="range" id="zoom-slider" min="0.1" max="3" step="0.01" value="1">
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

                {{-- ═══ RIGHT: Shared Floating Studio Dock ═══ --}}
                @include('quick-flow-pc.partials.customizer-right-dock', ['multiUpload' => true])

            </div>
        </div>
    </section>

    {{-- ── 2. HERO HEADER SECTION (BELOW the editor) ── --}}
    <section class="hero-cust-gradient hero-cust-pattern w-full px-6 lg:px-12 pt-12 pb-16 relative overflow-hidden border-t border-b border-slate-200/60 mt-12">
        <div class="hero-blob-1"></div>
        <div class="hero-blob-2"></div>
        <div class="hero-blob-3"></div>
        <div class="hero-dots"></div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            <nav class="cust-breadcrumb flex items-center gap-2 text-sm mb-4">
                <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                @if (isset($flowData['type_slug']))
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                    <a href="{{ route('flow-pc.category', $flowData['type_slug']) }}"
                        class="text-slate-400 font-medium hover:text-brand-600 transition-colors">{{ $flowData['type_name'] ?? 'Category' }}</a>
                @endif
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-700 font-semibold">Customize</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 bg-white/80 border border-brand-100 text-brand-600 text-xs font-black px-3.5 py-1 rounded-full mb-2 shadow-2xs">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand-600"></i>
                        STUDIO PRODUCT SPECIFICATIONS
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                        {{ $product->name }} <span class="bg-gradient-to-r from-brand-600 to-pink-600 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">Studio Edition</span>
                    </h1>
                    <p class="text-sm text-slate-500 font-medium mt-1 max-w-xl">
                        Upload your photo and add custom typography to personalize your design canvas.
                    </p>
                </div>

                @if (isset($flowData['size_width']) && isset($flowData['size_height']))
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-slate-800 text-xs font-black px-4 py-2.5 rounded-xl border border-slate-200 shadow-2xs">
                            <i data-lucide="ruler" class="w-4 h-4 text-brand-600"></i>
                            {{ $flowData['size_width'] }}&times;{{ $flowData['size_height'] }}{{ $flowData['size_unit'] ?? '' }}
                        </span>
                        @if (isset($flowData['size_price']))
                            <span class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-sm text-emerald-800 text-xs font-black px-4 py-2.5 rounded-xl border border-emerald-200 shadow-2xs">
                                {{ \App\Services\CurrencyService::format($flowData['size_price']) }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script src="{{ asset('js/customizer-base.js') }}"></script>
    <script>
        const customizer = Object.assign(customizerBase({
            imageTypes: <?php echo json_encode($imageTypes); ?>,
            allMaskData: {},
            productId: <?php echo $product->id; ?>,
            templates: @json($activeTemplates ?? ($product->templates ?? [])),
            templateCategories: @json($templateCategories ?? []),
            csrfToken: '<?php echo csrf_token(); ?>',
            uploadRoute: '<?php echo route("flow-pc.upload"); ?>',
            uploadCompositeRoute: '<?php echo route("flow-pc.upload_composite"); ?>',
            isPortrait: <?php echo ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false'; ?>,
            storagePrefix: 'qrinto_single_v1',
            multiCanvas: false,
            hasMasks: false,
        }), {

            // ── Override init: no mask parsing, no lock state ──
            init() {
                this.canvasEnabled = { 'frame_image': true };
                this.canvasImages  = { 'frame_image': null };
                this.uploadIds     = { 'frame_image': null };
                this.imgScales     = { 'frame_image': 1 };
                this.activeCanvas  = 'frame_image';

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

                // Mockup preview toggle
                document.getElementById('preview-toggle')?.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-mode]');
                    if (!btn) return;
                    document.querySelectorAll('.preview-toggle-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    document.getElementById('mockup-stage').className = 'mockup-stage ' + btn.dataset.mode;
                });

                // Keyboard delete
                window.addEventListener('keydown', (e) => {
                    if ((e.key === 'Delete' || e.key === 'Backspace') && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                        if (this.selectedObject) { e.preventDefault(); this.handleRemove(); }
                    }
                });

                this.updateUI();
            },

            // ── Override updateUI: no lock state, no page buttons ──
            updateUI() {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                const userImagesCount = cv ? cv.fabricCanvas.getObjects().filter(o => o._isUserImage).length : 0;
                const hasImg = userImagesCount > 0 || (this.canvasImages[key] !== null);

                // Single canvas — just show it
                const el = document.getElementById('canvas-wrapper-' + key);
                if (el) {
                    el.style.position = 'relative';
                    el.style.visibility = 'visible';
                    el.style.pointerEvents = 'auto';
                    el.style.zIndex = '1';
                }

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

                // Zoom
                document.getElementById('zoom-control')?.classList.toggle('hidden', !hasImg);

                // Selection sync & remove button
                this._syncToolbarToSelection(this.selectedObject);
                const showRemove = hasImg || !!this.selectedObject;
                const removeBtn = document.getElementById('remove-btn');
                if (removeBtn) removeBtn.classList.toggle('hidden', !showRemove);
                const removeBtnText = document.getElementById('remove-btn-text');
                if (showRemove && removeBtnText) {
                    if (this.selectedObject && this.selectedObject._isUserText) removeBtnText.textContent = 'Remove Text';
                    else if (this.selectedObject && this.selectedObject._isUserImage) removeBtnText.textContent = 'Remove Image';
                    else removeBtnText.textContent = 'Remove';
                }

                this.renderLayersPanel();
                if (window.lucide) window.lucide.createIcons();
            },

            // ── Unique to single: responsive canvas dimension calculator ──
            _calcCanvasDimensions(stageEl) {
                const stageW = (stageEl ? stageEl.offsetWidth : 700) || 700;
                const targetH = Math.max(520, Math.round(window.innerHeight * 0.80));
                const isPortrait = this._config.isPortrait;
                const adminW = isPortrait ? 400 : 560;
                const adminH = isPortrait ? 560 : 400;
                let displayHeight = targetH;
                let displayWidth = Math.round(adminW * (displayHeight / adminH));
                if (displayWidth > stageW) {
                    displayWidth = stageW;
                    displayHeight = Math.round(adminH * (displayWidth / adminW));
                }
                return { displayWidth, displayHeight, adminW, adminH };
            },

            // ── Override _initAllCanvases: backgroundColor '#ffffff', left:0/top:0 bg, no mask guides ──
            _initAllCanvases() {
                const containerEl = document.getElementById('canvas-container');
                const stageEl = document.getElementById('canvas-stage');
                if (!containerEl) return;

                const { displayWidth, displayHeight, adminW, adminH } = this._calcCanvasDimensions(stageEl);
                const scaleFactor = displayWidth / adminW;

                containerEl.style.width = displayWidth + 'px';
                containerEl.style.height = displayHeight + 'px';

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

                    this.canvases[key] = { fabricCanvas: fc, imgObj: null, scaleFactor, adminW, adminH };

                    const url = this.imageTypes[key]?.url;
                    if (url) {
                        fabric.Image.fromURL(url, img => {
                            img.set({ left: 0, top: 0, scaleX: displayWidth / img.width, scaleY: displayHeight / img.height, selectable: false, evented: false });
                            fc.setBackgroundImage(img, fc.requestRenderAll.bind(fc));
                        }, { crossOrigin: 'anonymous' });
                    }

                    fc.on('selection:created', (e) => { this.selectedObject = e.selected[0]; this.updateUI(); });
                    fc.on('selection:updated', (e) => { this.selectedObject = e.selected[0]; this.updateUI(); });
                    fc.on('selection:cleared', () => { this.selectedObject = null; this.updateUI(); });
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

            // ── Override _resizeAllCanvases: simpler, no mask guides ──
            _resizeAllCanvases() {
                const containerEl = document.getElementById('canvas-container');
                const stageEl = document.getElementById('canvas-stage');
                if (!containerEl) return;
                const { displayWidth, displayHeight } = this._calcCanvasDimensions(stageEl);
                containerEl.style.width = displayWidth + 'px';
                containerEl.style.height = displayHeight + 'px';
                Object.keys(this.canvases).forEach(key => {
                    const cv = this.canvases[key];
                    if (!cv) return;
                    cv.fabricCanvas.setWidth(displayWidth);
                    cv.fabricCanvas.setHeight(displayHeight);
                    cv.fabricCanvas.requestRenderAll();
                });
            },

            // ── Override _addImageToCanvas: 0.6 scale factor, no clip path ──
            _addImageToCanvas(key, url) {
                const cv = this.canvases[key];
                if (!cv) return;
                fabric.Image.fromURL(url, img => {
                    const canvasW = cv.fabricCanvas.width;
                    const canvasH = cv.fabricCanvas.height;
                    const userImgCount = cv.fabricCanvas.getObjects().filter(o => o._isUserImage).length;
                    const s = Math.min(canvasW / img.width, canvasH / img.height) * 0.6;
                    const offset = (userImgCount % 8) * 22;
                    img.set({
                        left: (canvasW - img.width * s) / 2 + offset,
                        top: (canvasH - img.height * s) / 2 + offset,
                        scaleX: s, scaleY: s,
                        cornerStyle: 'circle', cornerSize: 12, transparentCorners: false,
                        borderColor: '#378ADD', cornerColor: '#378ADD',
                        hasControls: true, hasBorders: true, selectable: true,
                        _isUserImage: true, objectCaching: true,
                        lockScalingFlip: true, uniformScaling: true
                    });
                    cv.fabricCanvas.add(img);
                    cv.fabricCanvas.setActiveObject(img);
                    cv.fabricCanvas.renderAll();
                    img.setCoords();
                    cv.imgObj = img;
                    this.imgScales[key] = s;
                    this.selectedObject = img;
                    this.updateUI();
                }, { crossOrigin: 'anonymous' });
            },

            // ── Override handleFileUpload: uses 'image' field, sends blob, no canvas_key ──
            async handleFileUpload(input) {
                const files = Array.from(input.files || []);
                if (!files.length) return;
                const key = this.activeCanvas;
                this.isUploading = true;
                this.updateUI();
                try {
                    for (const file of files) {
                        const optimized = await this._processImage(file);
                        this.canvasImages[key] = optimized.dataUrl;
                        this._addImageToCanvas(key, optimized.dataUrl);
                        const fd = new FormData();
                        fd.append('image', optimized.blob, 'upload.webp');
                        fd.append('_token', this._config.csrfToken);
                        const res = await fetch(this._config.uploadRoute, { method: 'POST', body: fd });
                        const dat = await res.json();
                        if (dat.success) this.uploadIds[key] = dat.upload_id;
                    }
                } catch (err) {
                    console.error('Upload error:', err);
                } finally {
                    this.isUploading = false;
                    input.value = '';
                    this.updateUI();
                }
            },

            // ── Override _processImage: WebP at 0.85 quality, max 1200px, URL.createObjectURL ──
            _processImage(file) {
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let w = img.width, h = img.height;
                        const maxDim = 1200;
                        if (w > maxDim || h > maxDim) {
                            if (w > h) { h *= maxDim / w; w = maxDim; }
                            else { w *= maxDim / h; h = maxDim; }
                        }
                        canvas.width = w;
                        canvas.height = h;
                        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                        const dataUrl = canvas.toDataURL('image/webp', 0.85);
                        canvas.toBlob((blob) => resolve({ blob, dataUrl }), 'image/webp', 0.85);
                    };
                    img.onerror = reject;
                    img.src = URL.createObjectURL(file);
                });
            },

            // ── Override addText: places at left 10%, top 33% (not centered) ──
            addText() {
                const key = this.activeCanvas;
                const cv = this.canvases[key];
                if (!cv) return;
                const textVal = document.getElementById('text-input').value;
                if (!textVal.trim()) return;
                const fontSize = parseInt(document.getElementById('font-size-select').value);
                const fontFamily = document.getElementById('font-family-select').value;
                const color = document.getElementById('text-color-input').value;
                const align = document.getElementById('text-align-select').value;
                const t = new fabric.Textbox(textVal, {
                    left: cv.fabricCanvas.width * 0.1,
                    top: cv.fabricCanvas.height / 3,
                    width: cv.fabricCanvas.width * 0.8,
                    fontSize, fontFamily, fill: color, textAlign: align,
                    _isUserText: true, objectCaching: false,
                    cornerSize: 12, transparentCorners: false,
                    borderColor: '#378ADD', cornerColor: '#378ADD',
                    cornerStyle: 'circle', lockScalingFlip: true
                });
                this.selectedObject = t;
                cv.fabricCanvas.add(t);
                t.setCoords();
                cv.fabricCanvas.setActiveObject(t);
                cv.fabricCanvas.renderAll();
                document.fonts.load(`${fontSize}px "${fontFamily}"`).then(() => {
                    if (t.canvas) { t.set('fontFamily', fontFamily); t.setCoords(); t.canvas.requestRenderAll(); }
                }).catch(() => {});
                this.updateUI();
            },

            // ── Override _saveCanvasState: only saves _isUserImage and _isUserText ──
            _saveCanvasState(key) {
                const cv = this.canvases[key];
                if (!cv) return;
                const objects = cv.fabricCanvas.getObjects().filter(o => o._isUserImage || o._isUserText);
                const data = {
                    objects: objects.map(o => o.toObject(['_isUserImage', '_isUserText', '_uploadId'])),
                    imgScale: this.imgScales[key],
                    uploadId: this.uploadIds[key]
                };
                localStorage.setItem(`qrinto_single_v1_${this.productId}_${key}`, JSON.stringify(data));
            },

            // ── Override _loadCanvasState: no clip path restoration ──
            _loadCanvasState(key) {
                const saved = localStorage.getItem(`qrinto_single_v1_${this.productId}_${key}`);
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
                                    selectable: true, evented: true, hasControls: true,
                                    lockScalingFlip: true, uniformScaling: true,
                                    cornerSize: 12, transparentCorners: false,
                                    borderColor: '#378ADD', cornerColor: '#378ADD', cornerStyle: 'circle'
                                });
                                if (obj._isUserImage) { cv.imgObj = obj; this.canvasImages[key] = true; }
                                fc.add(obj);
                            });
                            fc.renderAll();
                            this.updateUI();
                        });
                    }
                } catch (e) {}
            },

            // ── Override clearAll: single canvas only, no confirm dialog ──
            clearAll() {
                const cv = this.canvases[this.activeCanvas];
                if (!cv) return;
                cv.fabricCanvas.getObjects().filter(o => o._isUserImage || o._isUserText).forEach(o => cv.fabricCanvas.remove(o));
                cv.imgObj = null;
                this.canvasImages[this.activeCanvas] = null;
                this.selectedObject = null;
                cv.fabricCanvas.renderAll();
                this.updateUI();
            },

            // ── Override submitAllCanvases: hardcoded upload routes ──
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
                if (window.lucide) lucide.createIcons();

                const ids = {};
                const uploadPromises = Object.keys(this.canvases).map(async key => {
                    const cv = this.canvases[key];
                    if (!cv) return;
                    const hasEdit = this.canvasImages[key] !== null || cv.fabricCanvas.getObjects().some(o => o._isUserText);
                    if (!hasEdit) return;
                    cv.fabricCanvas.discardActiveObject();
                    const b64 = cv.fabricCanvas.toDataURL({ format: 'jpeg', quality: 0.9, multiplier: 2 });
                    const res = await fetch(this._config.uploadCompositeRoute, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this._config.csrfToken },
                        body: JSON.stringify({ image_data: b64, canvas_key: key }),
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
                    btn.innerHTML = '<i data-lucide="shopping-cart"></i> Add to Cart';
                    if (window.lucide) lucide.createIcons();
                });
            },
        });

        document.addEventListener('DOMContentLoaded', () => {
            customizer.init();

            // Mockup live preview interval (800ms)
            setInterval(() => {
                try {
                    const key = customizer.activeCanvas;
                    const cv = customizer.canvases[key];
                    if (!cv) return;
                    const data = cv.fabricCanvas.toDataURL({ format: 'jpeg', quality: 0.6, multiplier: 0.6 });
                    if (data && data.length > 100) {
                        const img = document.getElementById('mockup-image');
                        const frame = document.getElementById('mockup-frame');
                        const empty = document.getElementById('mockup-empty');
                        if (img) img.src = data;
                        if (frame) frame.classList.add('has-preview');
                        if (empty) empty.style.display = 'none';
                    }
                } catch (e) {}
            }, 800);
        });
    </script>
@endpush
