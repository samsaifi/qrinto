@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', isset($template) ? 'Edit Template' : 'New Template')

@push('styles')
    <style>
        /* ── Curated Fonts (synced with customizer) ── */
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
        #builder-canvas-container {
            position: relative;
            display: inline-block;
        }

        #builder-canvas-container canvas {
            display: block;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .prop-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .prop-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #0ea5e9;
        }

        .prop-input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
        }

        .prop-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
        }

        .builder-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            transition: all .15s;
        }

        .builder-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .builder-btn.primary {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff;
        }

        .builder-btn.primary:hover {
            background: #4f46e5;
        }

        .builder-btn.danger {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #dc2626;
        }

        .builder-btn.danger:hover {
            background: #fecaca;
        }

        .align-btn {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            cursor: pointer;
            background: #fff;
            line-height: 1;
        }

        .align-btn.active {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff;
        }

        .align-btn svg {
            width: 14px;
            height: 14px;
            display: block;
        }
    </style>
@endpush

@section('content')
    @php $template = $template ?? null; @endphp
    <form id="template-form" method="POST"
        action="{{ isset($template) ? route('admin.templates.update', $template) : route('admin.templates.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if (isset($template))
            @method('PUT')
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="font-display font-bold text-2xl text-surface-900">
                    {{ isset($template) ? 'Edit Template' : 'New Template' }}
                </h1>
                <p class="text-sm text-surface-500">Design the template layers on the canvas, then save.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.templates.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-surface-600 bg-white border border-surface-200 rounded-xl hover:bg-surface-50 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Template
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

            {{-- ── LEFT: Canvas Builder (3 cols) ── --}}
            <div class="xl:col-span-3 space-y-4">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-surface-800">Canvas Builder</h2>
                        <span class="text-xs text-surface-400">500 × 500 square canvas</span>
                    </div>

                    {{-- Toolbar --}}
                    <div class="flex flex-wrap gap-2 mb-4" id="toolbar">
                        <button type="button" class="builder-btn" onclick="TB.addText()">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor">
                                <path d="M1 2h14v2H9v10H7V4H1V2z" />
                            </svg>
                            Text
                        </button>
                        <label class="builder-btn cursor-pointer">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <rect x="1" y="3" width="14" height="10" rx="1.5" />
                                <path d="M1 11l4-4 3 3 2-2 4 4" />
                                <circle cx="11.5" cy="6.5" r="1.5" />
                            </svg>
                            Image
                            <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                onchange="TB.uploadAndAdd(this,'image')">
                        </label>
                        <label class="builder-btn cursor-pointer">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M8 1l2 5h5l-4 3 1.5 5L8 11l-4.5 3L5 9 1 6h5z" />
                            </svg>
                            SVG
                            <input type="file" accept=".svg,image/svg+xml" class="sr-only"
                                onchange="TB.uploadAndAdd(this,'svg')">
                        </label>
                        <div class="flex-1"></div>
                        <button type="button" class="builder-btn danger" onclick="TB.deleteSelected()">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M2 4h12M5 4V2h6v2M6 7v5M10 7v5M3 4l1 10h8l1-10" />
                            </svg>
                            Delete
                        </button>
                        <button type="button" class="builder-btn" onclick="TB.clearCanvas()" title="Clear all objects">
                            <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <path d="M1 1l14 14M11 5a4 4 0 0 0-6 0L2 8a4 4 0 0 0 6 6l3-3M14 8a4 4 0 0 0-4-4" />
                            </svg>
                            Clear
                        </button>
                    </div>

                    {{-- Canvas --}}
                    <div class="flex justify-center bg-surface-50 rounded-xl p-4 overflow-auto">
                        <div id="builder-canvas-container">
                            <canvas id="builder-canvas"></canvas>
                        </div>
                    </div>

                    {{-- Hint --}}
                    <p class="text-xs text-surface-400 mt-3 text-center">
                        Click an element to select it &mdash; drag to reposition &mdash; double-click text to edit
                    </p>
                </div>
            </div>

            {{-- ── RIGHT: Settings + Properties (2 cols) ── --}}
            <div class="xl:col-span-2 space-y-4">

                {{-- Template Settings --}}
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 space-y-4">
                    <h2 class="font-semibold text-surface-800">Template Settings</h2>

                    <div class="prop-row">
                        <label class="prop-label" for="name">Name *</label>
                        <input id="name" name="name" type="text" class="prop-input"
                            value="{{ old('name', $template->name ?? '') }}" placeholder="e.g. Birthday" required>
                    </div>

                    {{-- Category --}}
                    <div class="prop-row">
                        <label class="prop-label" for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="prop-input">
                            <option value="">All Categories (Global)</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $template->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-surface-400 mt-1">Leave as "Global" to show this template for all
                            products.</span>
                    </div>

                    {{-- Icon --}}
                    <div class="prop-row">
                        <span class="prop-label">Button Icon</span>
                        <div class="flex gap-3 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="radio" name="icon_type" value="lucide" id="icon_lucide_radio"
                                    {{ old('icon_type', $template->icon_type ?? 'lucide') === 'lucide' ? 'checked' : '' }}
                                    onchange="TB.toggleIconType()">
                                Lucide icon name
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="radio" name="icon_type" value="upload" id="icon_upload_radio"
                                    {{ old('icon_type', $template->icon_type ?? '') === 'upload' ? 'checked' : '' }}
                                    onchange="TB.toggleIconType()">
                                Upload SVG
                            </label>
                        </div>
                    </div>
                    <div id="icon-lucide-row" class="prop-row">
                        <label class="prop-label" for="icon_lucide">Lucide Icon Name</label>
                        <div class="flex gap-2 items-center">
                            <input id="icon_lucide" name="icon_lucide" type="text" class="prop-input"
                                value="{{ old('icon_lucide', $template && $template->icon_type === 'lucide' ? $template->icon_value : '') }}"
                                placeholder="e.g. cake, heart, sparkles">
                            <div id="icon-preview"
                                class="w-8 h-8 rounded-lg bg-surface-100 flex items-center justify-center flex-shrink-0">
                                <i id="icon-preview-el" data-lucide="layout-template"
                                    class="w-4 h-4 text-surface-500"></i>
                            </div>
                        </div>
                    </div>
                    <div id="icon-upload-row" class="prop-row hidden">
                        <label class="prop-label" for="icon_file">SVG Icon File</label>
                        @if (isset($template) && $template->icon_type === 'upload' && $template->icon_value)
                            <div class="flex items-center gap-3 mb-2">
                                <img src="{{ asset('storage/' . $template->icon_value) }}" alt=""
                                    class="w-8 h-8 object-contain">
                                <span class="text-xs text-surface-500">Current icon — upload new to replace</span>
                            </div>
                        @endif
                        <input id="icon_file" name="icon_file" type="file" accept=".svg,image/svg+xml"
                            class="prop-input text-sm py-1.5">
                    </div>

                    {{-- Behaviour --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="prop-row">
                            <label class="prop-label" for="apply_to">Apply To</label>
                            @php $cfgApplyTo = isset($template) ? ($template->canvas_config['applyTo'] ?? 'active') : 'active'; @endphp
                            <select id="apply_to" name="apply_to" class="prop-input">
                                <option value="active" {{ $cfgApplyTo === 'active' ? 'selected' : '' }}>Active page
                                </option>
                                <option value="all" {{ $cfgApplyTo === 'all' ? 'selected' : '' }}>All pages</option>
                            </select>
                        </div>
                        <div class="prop-row">
                            <label class="prop-label" for="sort_order">Sort Order</label>
                            <input id="sort_order" name="sort_order" type="number" min="0" class="prop-input"
                                value="{{ old('sort_order', $template->sort_order ?? 0) }}">
                        </div>
                    </div>

                    <div class="flex gap-4">
                        @php
                            $cfgReplace = isset($template) ? $template->canvas_config['replace'] ?? true : true;
                            $cfgIgnoreMask = isset($template) ? $template->canvas_config['ignoreMask'] ?? true : true;
                        @endphp
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" name="replace" id="replace" value="1"
                                class="rounded text-brand-600" {{ $cfgReplace ? 'checked' : '' }}>
                            Replace existing layers
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" name="ignore_mask" id="ignore_mask" value="1"
                                class="rounded text-brand-600" {{ $cfgIgnoreMask ? 'checked' : '' }}>
                            Ignore mask clip
                        </label>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer text-sm font-medium">
                        <input type="checkbox" name="is_active" value="1" class="rounded text-brand-600"
                            {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                        Active (visible in customizer)
                    </label>
                </div>

                {{-- Layer Properties (shown when an object is selected) --}}
                <div id="layer-props-panel" class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 hidden">
                    <h2 class="font-semibold text-surface-800 mb-4">Layer Properties</h2>
                    <div id="layer-props-content"></div>
                </div>

                {{-- No selection hint --}}
                <div id="no-selection-hint"
                    class="bg-surface-50 rounded-2xl border border-surface-100 p-5 text-center text-sm text-surface-400">
                    <i data-lucide="mouse-pointer-click" class="w-6 h-6 mx-auto mb-2 text-surface-300"></i>
                    Select a canvas element to edit its properties
                </div>
            </div>
        </div>

        {{-- Hidden inputs --}}
        <input type="hidden" name="canvas_config" id="canvas_config_input">
    </form>

    {{-- Hidden file input for image/SVG uploads used by toolbar --}}
    <input type="hidden" id="upload-csrf" value="{{ csrf_token() }}">
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script>
        (function() {
            // ── Constants ──────────────────────────────────────────────────────────
            const CANVAS_W = 500;
            const CANVAS_H = 500;
            const UPLOAD_URL = '{{ route('admin.templates.upload-asset') }}';
            const CSRF = document.getElementById('upload-csrf').value;

            const FONT_FAMILIES = [
                'Inter', 'Roboto', 'Open Sans', 'Poppins', 'Lato',
                'ABeeZee', 'Oswald', 'Bebas Neue', 'Anton', 'Alfa Slab One',
                'Playfair Display', 'Lora', 'Crimson Pro', 'Cinzel',
                'Dancing Script', 'Great Vibes', 'Pacifico', 'Alex Brush',
                'Satisfy', 'Courgette', 'Sacramento', 'Caveat',
                'Indie Flower', 'Amatic SC', 'Shadows Into Light',
                'Lobster', 'Righteous', 'Bangers', 'Bungee', 'Permanent Marker',
            ];

            // ── Existing config (for edit) ─────────────────────────────────────────
            @if (isset($template) && $template->canvas_config)
                const EXISTING_CONFIG = @json($template->canvas_config);
            @else
                const EXISTING_CONFIG = null;
            @endif

            // ── Fabric canvas ──────────────────────────────────────────────────────
            let canvas;

            function initCanvas() {
                canvas = new fabric.Canvas('builder-canvas', {
                    width: CANVAS_W,
                    height: CANVAS_H,
                    backgroundColor: '#f0f4f8',
                    selection: true,
                });

                // Draw photo-area guide
                const guide = new fabric.Rect({
                    left: 10,
                    top: 10,
                    width: CANVAS_W - 20,
                    height: CANVAS_H - 20,
                    fill: '#fff',
                    stroke: '#cbd5e1',
                    strokeWidth: 1,
                    strokeDashArray: [4, 3],
                    selectable: false,
                    evented: false,
                    excludeFromExport: true,
                    _isGuide: true,
                });
                canvas.add(guide);
                canvas.sendToBack(guide);

                canvas.on('selection:created', onSelectionChange);
                canvas.on('selection:updated', onSelectionChange);
                canvas.on('selection:cleared', onSelectionCleared);
                canvas.on('object:modified', onObjectModified);
                canvas.on('text:changed', onTextChanged);

                if (EXISTING_CONFIG) {
                    loadConfig(EXISTING_CONFIG);
                }
            }

            // ── Load existing config into canvas ──────────────────────────────────
            function loadConfig(cfg) {
                (cfg.texts || []).forEach(spec => {
                    const originX = spec.textAlign === 'left' ? 'left' :
                        spec.textAlign === 'right' ? 'right' : 'center';
                    const t = new fabric.Textbox(spec.text || 'Text', {
                        left: CANVAS_W * (spec.xFrac ?? 0.5),
                        top: CANVAS_H * (spec.yFrac ?? 0.1),
                        originX,
                        originY: 'top',
                        width: CANVAS_W * (spec.widthFrac || 0.8),
                        fontSize: spec.fontSize || 24,
                        fontFamily: spec.fontFamily || 'Inter',
                        fill: spec.fill || '#000000',
                        textAlign: spec.textAlign || 'center',
                        fontWeight: spec.fontWeight || 'normal',
                        fontStyle: spec.fontStyle || 'normal',
                        angle: spec.angle || 0,
                        _templateType: 'text',
                        ...FABRIC_STYLE,
                    });
                    canvas.add(t);
                });

                (cfg.images || []).forEach(spec => {
                    fabric.Image.fromURL(spec.url, img => {
                        if (!img) return;
                        const scale = (CANVAS_W * (spec.widthFrac || 0.3)) / img.width;
                        img.set({
                            left: CANVAS_W * (spec.xFrac ?? 0.5),
                            top: CANVAS_H * (spec.yFrac ?? 0.5),
                            originX: 'center',
                            originY: 'center',
                            scaleX: scale,
                            scaleY: scale,
                            angle: spec.angle || 0,
                            _templateType: 'image',
                            _assetUrl: spec.url,
                            _locked: spec.locked || false,
                            selectable: !spec.locked,
                            evented: !spec.locked,
                            ...FABRIC_STYLE,
                        });
                        canvas.add(img);
                        canvas.renderAll();
                    }, {
                        crossOrigin: 'anonymous'
                    });
                });

                (cfg.svgs || []).forEach(spec => {
                    const handler = (objects, options) => {
                        if (!objects) return;
                        const group = fabric.util.groupSVGElements(objects, options);
                        const scale = (CANVAS_W * (spec.widthFrac || 0.15)) / group.width;
                        group.set({
                            left: CANVAS_W * (spec.xFrac ?? 0.5),
                            top: CANVAS_H * (spec.yFrac ?? 0.5),
                            originX: 'center',
                            originY: 'center',
                            scaleX: scale,
                            scaleY: scale,
                            angle: spec.angle || 0,
                            _templateType: 'svg',
                            _assetUrl: spec.url || null,
                            _svgFill: spec.fill || null,
                            _locked: spec.locked || false,
                            selectable: !spec.locked,
                            evented: !spec.locked,
                            ...FABRIC_STYLE,
                        });
                        if (spec.fill) {
                            group.getObjects().forEach(o => o.set('fill', spec.fill));
                        }
                        canvas.add(group);
                        canvas.renderAll();
                    };
                    if (spec.url) {
                        fabric.loadSVGFromURL(spec.url, handler);
                    } else if (spec.svg) {
                        fabric.loadSVGFromString(spec.svg, handler);
                    }
                });

                canvas.renderAll();
            }

            const FABRIC_STYLE = {
                cornerSize: 10,
                transparentCorners: false,
                borderColor: '#6366f1',
                cornerColor: '#6366f1',
                cornerStyle: 'circle',
            };

            // ── Add Text ──────────────────────────────────────────────────────────
            function addText() {
                const t = new fabric.Textbox('Edit this text', {
                    left: CANVAS_W / 2,
                    top: CANVAS_H * 0.1,
                    originX: 'center',
                    originY: 'top',
                    width: CANVAS_W * 0.8,
                    fontSize: 28,
                    fontFamily: 'Poppins',
                    fill: '#1e293b',
                    textAlign: 'center',
                    fontWeight: 'normal',
                    fontStyle: 'normal',
                    _templateType: 'text',
                    ...FABRIC_STYLE,
                });
                canvas.add(t);
                canvas.setActiveObject(t);
                canvas.renderAll();
            }

            // ── Upload asset (image or svg) & add to canvas ───────────────────────
            async function uploadAndAdd(input, type) {
                const file = input.files[0];
                if (!file) return;
                input.value = '';

                const fd = new FormData();
                fd.append('file', file);
                fd.append('type', type);
                fd.append('_token', CSRF);

                let data;
                try {
                    const res = await fetch(UPLOAD_URL, {
                        method: 'POST',
                        body: fd
                    });
                    data = await res.json();
                } catch (e) {
                    alert('Upload failed. Please try again.');
                    return;
                }

                if (!data.url) {
                    alert('Upload failed.');
                    return;
                }

                if (type === 'image') {
                    fabric.Image.fromURL(data.url, img => {
                        if (!img) return;
                        const scale = (CANVAS_W * 0.4) / img.width;
                        img.set({
                            left: CANVAS_W / 2,
                            top: CANVAS_H / 2,
                            originX: 'center',
                            originY: 'center',
                            scaleX: scale,
                            scaleY: scale,
                            _templateType: 'image',
                            _assetUrl: data.url,
                            _locked: false,
                            ...FABRIC_STYLE,
                        });
                        canvas.add(img);
                        canvas.setActiveObject(img);
                        canvas.renderAll();
                    }, {
                        crossOrigin: 'anonymous'
                    });
                } else {
                    fabric.loadSVGFromURL(data.url, (objects, options) => {
                        if (!objects) return;
                        const group = fabric.util.groupSVGElements(objects, options);
                        const scale = (CANVAS_W * 0.2) / group.width;
                        group.set({
                            left: CANVAS_W / 2,
                            top: CANVAS_H / 2,
                            originX: 'center',
                            originY: 'center',
                            scaleX: scale,
                            scaleY: scale,
                            _templateType: 'svg',
                            _assetUrl: data.url,
                            _svgFill: null,
                            _locked: false,
                            ...FABRIC_STYLE,
                        });
                        canvas.add(group);
                        canvas.setActiveObject(group);
                        canvas.renderAll();
                    });
                }
            }

            function deleteSelected() {
                const obj = canvas.getActiveObject();
                if (!obj) return;
                canvas.remove(obj);
                canvas.discardActiveObject();
                canvas.renderAll();
                onSelectionCleared();
            }

            function clearCanvas() {
                if (!confirm('Remove all layers from the canvas?')) return;
                canvas.getObjects().forEach(o => {
                    if (!o._isGuide) canvas.remove(o);
                });
                canvas.discardActiveObject();
                canvas.renderAll();
                onSelectionCleared();
            }

            // ── Properties panel ──────────────────────────────────────────────────
            function onSelectionChange(e) {
                const obj = canvas.getActiveObject();
                if (!obj) return onSelectionCleared();
                renderPropsPanel(obj);
            }

            function onSelectionCleared() {
                document.getElementById('layer-props-panel').classList.add('hidden');
                document.getElementById('no-selection-hint').classList.remove('hidden');
            }

            function onObjectModified(e) {
                const obj = canvas.getActiveObject();
                if (obj) renderPropsPanel(obj);
            }

            function onTextChanged(e) {
                // sync text content field
                const obj = e.target;
                const el = document.getElementById('prop-text-content');
                if (el && obj) el.value = obj.text;
            }

            function renderPropsPanel(obj) {
                document.getElementById('layer-props-panel').classList.remove('hidden');
                document.getElementById('no-selection-hint').classList.add('hidden');

                const el = document.getElementById('layer-props-content');
                const type = obj._templateType || (obj.type === 'textbox' ? 'text' : 'image');

                if (type === 'text') {
                    el.innerHTML = buildTextProps(obj);
                    bindTextProps(obj);
                } else {
                    el.innerHTML = buildMediaProps(obj, type);
                    bindMediaProps(obj, type);
                }
            }

            function buildTextProps(obj) {
                const ff = FONT_FAMILIES.map(f =>
                    `<option value="${f}" ${obj.fontFamily === f ? 'selected' : ''}>${f}</option>`
                ).join('');

                return `
        <div class="space-y-3">
            <div class="prop-row">
                <label class="prop-label">Text Content</label>
                <textarea id="prop-text-content" class="prop-input" rows="2">${escHtml(obj.text)}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="prop-row">
                    <label class="prop-label">Font Family</label>
                    <select id="prop-font-family" class="prop-input">${ff}</select>
                </div>
                <div class="prop-row">
                    <label class="prop-label">Font Size</label>
                    <input id="prop-font-size" type="number" min="6" max="200" class="prop-input" value="${Math.round(obj.fontSize)}">
                </div>
            </div>
            <div class="flex gap-3 items-end">
                <div class="prop-row flex-1">
                    <label class="prop-label">Color</label>
                    <input id="prop-fill" type="color" class="w-full h-9 rounded-lg border border-surface-200 cursor-pointer" value="${obj.fill || '#000000'}">
                </div>
                <div class="prop-row">
                    <label class="prop-label">Style</label>
                    <div class="flex gap-1">
                        <button type="button" id="prop-bold" class="align-btn ${obj.fontWeight === 'bold' ? 'active' : ''}" title="Bold">
                            <svg viewBox="0 0 14 14" fill="currentColor"><path d="M3 2h5a3 3 0 0 1 0 6H3V2zm0 6h5.5a3.5 3.5 0 0 1 0 7H3V8z"/></svg>
                        </button>
                        <button type="button" id="prop-italic" class="align-btn ${obj.fontStyle === 'italic' ? 'active' : ''}" title="Italic">
                            <svg viewBox="0 0 14 14" fill="currentColor"><path d="M6 2h6v2H9.5L6.5 12H9v2H3v-2h2.5l3-8H6V2z"/></svg>
                        </button>
                    </div>
                </div>
                <div class="prop-row">
                    <label class="prop-label">Align</label>
                    <div class="flex gap-1">
                        ${['left','center','right'].map(a => `
                            <button type="button" class="align-btn ${obj.textAlign === a ? 'active' : ''}" data-align="${a}" title="${a}">
                                <svg viewBox="0 0 14 14" fill="currentColor">${alignIcon(a)}</svg>
                            </button>`).join('')}
                    </div>
                </div>
            </div>
            <div class="prop-row">
                <label class="prop-label">Rotation (°)</label>
                <input id="prop-angle" type="number" min="-180" max="180" class="prop-input" value="${Math.round(obj.angle || 0)}">
            </div>
        </div>`;
            }

            function bindTextProps(obj) {
                q('#prop-text-content').addEventListener('input', e => {
                    obj.set('text', e.target.value);
                    canvas.renderAll();
                });
                q('#prop-font-family').addEventListener('change', e => {
                    obj.set('fontFamily', e.target.value);
                    canvas.renderAll();
                });
                q('#prop-font-size').addEventListener('input', e => {
                    obj.set('fontSize', +e.target.value || 16);
                    canvas.renderAll();
                });
                q('#prop-fill').addEventListener('input', e => {
                    obj.set('fill', e.target.value);
                    canvas.renderAll();
                });
                q('#prop-bold').addEventListener('click', () => {
                    const isBold = obj.fontWeight === 'bold';
                    obj.set('fontWeight', isBold ? 'normal' : 'bold');
                    q('#prop-bold').classList.toggle('active', !isBold);
                    canvas.renderAll();
                });
                q('#prop-italic').addEventListener('click', () => {
                    const isItalic = obj.fontStyle === 'italic';
                    obj.set('fontStyle', isItalic ? 'normal' : 'italic');
                    q('#prop-italic').classList.toggle('active', !isItalic);
                    canvas.renderAll();
                });
                document.querySelectorAll('[data-align]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const a = btn.dataset.align;
                        const originX = a === 'left' ? 'left' : a === 'right' ? 'right' : 'center';
                        // Reposition left to keep visual center stable
                        const center = obj.getCenterPoint();
                        obj.set({
                            textAlign: a,
                            originX
                        });
                        obj.setPositionByOrigin(center, 'center', obj.originY);
                        document.querySelectorAll('[data-align]').forEach(b => b.classList.toggle(
                            'active', b.dataset.align === a));
                        canvas.renderAll();
                    });
                });
                q('#prop-angle').addEventListener('input', e => {
                    obj.rotate(+e.target.value || 0);
                    canvas.renderAll();
                });
            }

            function buildMediaProps(obj, type) {
                const widthFrac = ((obj.width * obj.scaleX) / CANVAS_W).toFixed(3);
                return `
        <div class="space-y-3">
            <div class="prop-row">
                <label class="prop-label">Width (fraction of canvas)</label>
                <input id="prop-width-frac" type="number" min="0.01" max="1" step="0.01" class="prop-input" value="${widthFrac}">
            </div>
            ${type === 'svg' ? `
                <div class="prop-row">
                    <label class="prop-label">Fill Color (SVG — leave blank to keep original)</label>
                    <input id="prop-svg-fill" type="color" class="w-full h-9 rounded-lg border border-surface-200 cursor-pointer"
                           value="${obj._svgFill || '#000000'}">
                    <label class="flex items-center gap-2 mt-1 text-xs text-surface-500 cursor-pointer">
                        <input type="checkbox" id="prop-svg-fill-enable" class="rounded" ${obj._svgFill ? 'checked' : ''}>
                        Apply fill color override
                    </label>
                </div>
                ` : ''}
            <div class="prop-row">
                <label class="prop-label">Rotation (°)</label>
                <input id="prop-angle" type="number" min="-180" max="180" class="prop-input" value="${Math.round(obj.angle || 0)}">
            </div>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" id="prop-locked" class="rounded text-brand-600" ${obj._locked ? 'checked' : ''}>
                Lock (customer cannot move/delete)
            </label>
        </div>`;
            }

            function bindMediaProps(obj, type) {
                q('#prop-width-frac').addEventListener('input', e => {
                    const wf = parseFloat(e.target.value) || 0.1;
                    const scale = (CANVAS_W * wf) / obj.width;
                    obj.set({
                        scaleX: scale,
                        scaleY: scale
                    });
                    canvas.renderAll();
                });
                if (type === 'svg') {
                    const fillInput = q('#prop-svg-fill');
                    const fillEnable = q('#prop-svg-fill-enable');
                    const applyFill = () => {
                        if (fillEnable.checked) {
                            const color = fillInput.value;
                            obj._svgFill = color;
                            obj.getObjects().forEach(o => o.set('fill', color));
                        } else {
                            obj._svgFill = null;
                        }
                        canvas.renderAll();
                    };
                    fillInput.addEventListener('input', applyFill);
                    fillEnable.addEventListener('change', applyFill);
                }
                q('#prop-angle').addEventListener('input', e => {
                    obj.rotate(+e.target.value || 0);
                    canvas.renderAll();
                });
                q('#prop-locked').addEventListener('change', e => {
                    obj._locked = e.target.checked;
                    obj.set({
                        selectable: !e.target.checked,
                        evented: !e.target.checked
                    });
                    canvas.renderAll();
                });
            }

            // ── Serialize canvas → JSON config ────────────────────────────────────
            function serializeConfig() {
                const W = CANVAS_W,
                    H = CANVAS_H;
                const config = {
                    applyTo: document.getElementById('apply_to').value,
                    replace: document.getElementById('replace').checked,
                    ignoreMask: document.getElementById('ignore_mask').checked,
                    texts: [],
                    images: [],
                    svgs: [],
                };

                canvas.getObjects().forEach(obj => {
                    if (obj._isGuide) return;

                    const type = obj._templateType || (obj.type === 'textbox' ? 'text' : 'image');

                    if (type === 'text' && obj.type === 'textbox') {
                        config.texts.push({
                            text: obj.text,
                            xFrac: round4(obj.left / W),
                            yFrac: round4(obj.top / H),
                            widthFrac: round4(obj.width / W),
                            fontSize: Math.round(obj.fontSize),
                            fontFamily: obj.fontFamily,
                            fill: obj.fill,
                            textAlign: obj.textAlign,
                            fontWeight: obj.fontWeight,
                            fontStyle: obj.fontStyle,
                            angle: Math.round(obj.angle || 0),
                        });
                    } else if (type === 'image') {
                        config.images.push({
                            url: obj._assetUrl,
                            xFrac: round4(obj.left / W),
                            yFrac: round4(obj.top / H),
                            widthFrac: round4((obj.width * obj.scaleX) / W),
                            locked: obj._locked || false,
                            angle: Math.round(obj.angle || 0),
                        });
                    } else if (type === 'svg') {
                        config.svgs.push({
                            url: obj._assetUrl || null,
                            xFrac: round4(obj.left / W),
                            yFrac: round4(obj.top / H),
                            widthFrac: round4((obj.width * obj.scaleX) / W),
                            fill: obj._svgFill || null,
                            locked: obj._locked || false,
                            angle: Math.round(obj.angle || 0),
                        });
                    }
                });

                return JSON.stringify(config);
            }

            // ── Icon preview update ───────────────────────────────────────────────
            function toggleIconType() {
                const isLucide = document.getElementById('icon_lucide_radio').checked;
                document.getElementById('icon-lucide-row').classList.toggle('hidden', !isLucide);
                document.getElementById('icon-upload-row').classList.toggle('hidden', isLucide);
            }

            let iconDebounce;

            function refreshIconPreview() {
                const name = document.getElementById('icon_lucide').value.trim() || 'layout-template';
                const el = document.getElementById('icon-preview-el');
                el.setAttribute('data-lucide', name);
                clearTimeout(iconDebounce);
                iconDebounce = setTimeout(() => {
                    if (window.lucide) lucide.createIcons({
                        nodes: [el]
                    });
                }, 300);
            }

            // ── Helpers ───────────────────────────────────────────────────────────
            function round4(n) {
                return Math.round(n * 10000) / 10000;
            }

            function q(sel) {
                return document.querySelector(sel);
            }

            function escHtml(s) {
                return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;');
            }

            function alignIcon(a) {
                if (a === 'left')
                return '<rect x="1" y="2" width="12" height="2" rx="1"/><rect x="1" y="6" width="8" height="2" rx="1"/><rect x="1" y="10" width="10" height="2" rx="1"/>';
                if (a === 'right')
                return '<rect x="1" y="2" width="12" height="2" rx="1"/><rect x="5" y="6" width="8" height="2" rx="1"/><rect x="3" y="10" width="10" height="2" rx="1"/>';
                return '<rect x="1" y="2" width="12" height="2" rx="1"/><rect x="3" y="6" width="8" height="2" rx="1"/><rect x="2" y="10" width="10" height="2" rx="1"/>';
            }

            // ── Form submit ───────────────────────────────────────────────────────
            document.getElementById('template-form').addEventListener('submit', function(e) {
                const nameEl = document.getElementById('name');
                if (!nameEl.value.trim()) {
                    e.preventDefault();
                    nameEl.focus();
                    alert('Please enter a template name.');
                    return;
                }
                document.getElementById('canvas_config_input').value = serializeConfig();
            });

            // ── Init ──────────────────────────────────────────────────────────────
            document.addEventListener('DOMContentLoaded', () => {
                initCanvas();
                toggleIconType();

                // Icon preview live update
                document.getElementById('icon_lucide').addEventListener('input', refreshIconPreview);
                refreshIconPreview();

                if (window.lucide) lucide.createIcons();
            });

            // Expose to onclick handlers
            window.TB = {
                addText,
                uploadAndAdd,
                deleteSelected,
                clearCanvas,
                toggleIconType
            };
        })();
    </script>
@endpush
