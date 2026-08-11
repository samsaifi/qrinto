@extends('layouts.admin')
@section('title', 'Mask Editor — ' . $product->name)

@section('content')
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ route('admin.products.index') }}" class="text-surface-400 hover:text-surface-600 transition">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <h1 class="font-display font-bold text-xl text-surface-900">Mask Editor</h1>
                </div>
                <p class="text-sm text-surface-500 ml-8">{{ $product->name }} — Define mask regions for all 4 images</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="clearAllMasks()"
                    class="px-4 py-2 text-sm font-medium text-surface-600 bg-surface-100 rounded-xl hover:bg-surface-200 transition">
                    <i data-lucide="trash-2" class="w-4 h-4 inline-block mr-1"></i> Clear Current
                </button>
                <button type="button" onclick="saveMaskData()"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-brand-600 rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <i data-lucide="save" class="w-4 h-4 inline-block mr-1"></i> Save All Masks
                </button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-accent-50 text-accent-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
     
    @php
    
        if($product->no_of_pages ==4){
        $imageTypes = [
            'frame_image' => ['label' => 'Front Image', 'icon' => 'frame', 'url' => $product->frame_image_url], 
            'sample_image' => ['label' => 'Inside Left Image', 'icon' => 'image', 'url' => $product->sample_image_url],
            'background_image' => [
                'label' => 'Inside Right Image',
                'icon' => 'layers',
                'url' => $product->background_image_url,
            ],
            'overlay_image' => ['label' => 'Back Cover Image', 'icon' => 'sparkles', 'url' => $product->overlay_image_url],
        ];
        }elseif($product->no_of_pages ==2){
             $imageTypes = [
                'frame_image' => ['label' => 'Front Image', 'icon' => 'frame', 'url' => $product->frame_image_url], 
                'sample_image' => ['label' => 'Inside Left Image', 'icon' => 'image', 'url' => $product->sample_image_url],
                 
            ];
        }else{
            $imageTypes = [
                'frame_image' => ['label' => 'Front Image', 'icon' => 'frame', 'url' => $product->frame_image_url],   
            ];
        }
        
        
        $savedMaskData = $product->mask_data ?? [];
        if (is_string($savedMaskData)) {
            $savedMaskData = json_decode($savedMaskData, true) ?? [];
        }
    @endphp

    <!-- Tab Navigation -->
    <div class="flex gap-2 mb-4 flex-wrap">
        @foreach ($imageTypes as $key => $img)
            <button type="button" onclick="switchTab('{{ $key }}')" id="tab-{{ $key }}"
                class="mask-tab px-4 py-2.5 text-sm font-semibold rounded-xl border-2 transition-all flex items-center gap-2
            {{ $loop->first ? 'bg-brand-50 border-brand-500 text-brand-700' : 'bg-white border-surface-100 text-surface-500 hover:border-surface-300' }}">
                <i data-lucide="{{ $img['icon'] }}" class="w-4 h-4"></i>
                {{ $img['label'] }}
                @if (!$img['url'])
                    <span class="text-[9px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full font-bold">No
                        Image</span>
                @endif
                <!-- Enabled toggle -->
                <label class="relative inline-flex items-center cursor-pointer ml-1" onclick="event.stopPropagation()">
                    <input type="checkbox" class="sr-only peer" id="enable-{{ $key }}"
                        {{ isset($savedMaskData[$key]['enabled']) ? ($savedMaskData[$key]['enabled'] ? 'checked' : '') : 'checked' }}
                        onchange="toggleCanvas('{{ $key }}', this.checked)">
                    <div
                        class="w-8 h-4 bg-surface-200 peer-checked:bg-brand-500 rounded-full peer-focus:ring-2 peer-focus:ring-brand-200 transition-all
                after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-4">
                    </div>
                </label>
            </button>
        @endforeach
    </div>

    <!-- Aspect ratio info badge -->
    <div class="mb-3 flex items-center gap-2">
        <span class="text-xs font-semibold text-surface-500 bg-surface-100 px-3 py-1 rounded-full">
            Canvas: <span id="canvasDimBadge">600 × 429 px</span>
            &nbsp;·&nbsp; Ratio: <strong>7 : 5</strong>
            &nbsp;·&nbsp; Matches product image exactly
        </span>
    </div>

    <div style="display:flex; gap:12px;   min-height:450px; overflow-x:auto; padding-bottom:20px;">

        <!-- ═══ Canvas Area ═══ -->
        <div style="flex:none; position:relative;" id="canvasPanel"
            class="bg-white rounded-2xl border border-surface-100 shadow-card flex flex-col">

            <!-- Toolbar -->
            <div
                style="display:flex; align-items:center; justify-content:space-between; padding:8px 16px; background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span
                        style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;"
                        id="canvasLabel">Canvas — Frame Image</span>
                    <span id="canvasInfo" style="font-size:11px; color:#94a3b8;"></span>
                </div>

                <!-- Polygon drawing hint -->
                <div id="polygonHelp"
                    style="display:none; align-items:center; gap:8px; background:#ecfdf5; padding:4px 12px; border-radius:6px; border:1px solid #a7f3d0;">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span style="font-size:11px; color:#065f46; font-weight:500;">Click to add points. Double-click or press
                        Enter to finish.</span>
                    <button type="button" onclick="finishPolygon()"
                        style="background:#10b981; color:white; border:none; padding:2px 8px; font-size:10px; border-radius:4px; cursor:pointer; font-weight:bold;">Finish</button>
                    <button type="button" onclick="cancelPolygon()"
                        style="background:white; color:#ef4444; border:1px solid #fca5a5; padding:1px 7px; font-size:10px; border-radius:4px; cursor:pointer;">Cancel</button>
                </div>

                <div style="display:flex; align-items:center; gap:6px;">
                    <button type="button" onclick="deleteSelected()" id="deleteBtn"
                        style="display:none; padding:4px 10px; font-size:11px; font-weight:500; color:#dc2626; background:#fef2f2; border:none; border-radius:6px; cursor:pointer;">
                        ✕ Delete Selected
                    </button>
                    <button type="button" onclick="resetView()"
                        style="padding:4px 10px; font-size:11px; font-weight:500; color:#475569; background:#f1f5f9; border:none; border-radius:6px; cursor:pointer;">
                        Reset View
                    </button>
                </div>
            </div>

            <!-- Canvas -->
            <div style="flex:1; position:relative; overflow:hidden; background:repeating-conic-gradient(#f1f5f9 0% 25%, #fff 0% 50%) 50% / 20px 20px;"
                id="canvasContainer">
                <canvas id="maskCanvas"></canvas>
            </div>

            <!-- Disabled overlay -->
            <div id="canvasDisabledOverlay"
                style="display:none; position:absolute; inset:0; background:rgba(148,163,184,0.25); backdrop-filter:blur(2px); z-index:40; border-radius:1rem; cursor:not-allowed; flex-direction:column; align-items:center; justify-content:center;">
                <i data-lucide="lock" style="width:32px; height:32px; color:#94a3b8;"></i>
                <p style="font-size:13px; font-weight:600; color:#64748b; margin-top:8px;">Canvas disabled — toggle to
                    enable</p>
            </div>
        </div>

        <!-- ═══ Sidebar ═══ -->
        <div style="width:220px; flex-shrink:0; position:sticky; top:0;"
            class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-y-auto flex flex-col">

            <div class="px-4 py-3 border-b border-surface-100">
                <h2 class="font-display font-semibold text-sm text-surface-900">Mask Shapes</h2>
                <p class="text-xs text-surface-400 mt-0.5">Click to add on canvas</p>
            </div>

            <!-- Shape Grid -->
            <div style="padding:10px; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <button type="button" onclick="addShape('square')" class="shape-btn group">
                    <div class="w-8 h-8 bg-emerald-500/40 border-2 border-emerald-500 rounded-sm"></div><span>Square</span>
                </button>
                <button type="button" onclick="addShape('rectangle')" class="shape-btn group">
                    <div class="w-10 h-7 bg-emerald-500/40 border-2 border-emerald-500 rounded-sm"></div><span>Rectangle</span>
                </button>
                <button type="button" onclick="addShape('circle')" class="shape-btn group">
                                    <div class="w-8 h-8 bg-emerald-500/40 border-2 border-emerald-500 rounded-full"></div><span>Circle</span>
                                </button>
                                <button type="button" onclick="addShape('ellipse')" class="shape-btn group">
                                    <div class="w-10 h-7 bg-emerald-500/40 border-2 border-emerald-500 rounded-full"></div><span>Ellipse</span>
                                </button>
                                <button type="button" onclick="addShape('triangle')" class="shape-btn group">
                                    <div class="w-0 h-0 border-l-[20px] border-r-[20px] border-b-[35px] border-l-transparent border-r-transparent border-b-emerald-500/60"></div><span>Triangle</span>
                                </button>
                                <button type="button" onclick="addShape('diamond')" class="shape-btn group">
                                    <div class="w-7 h-7 bg-emerald-500/40 border-2 border-emerald-500 rotate-45 rounded-sm"></div><span>Diamond</span>
                                </button>
                                <button type="button" onclick="addShape('pentagon')" class="shape-btn group">
                                    <svg class="w-8 h-8" viewBox="0 0 40 40">
                                        <polygon points="20,2 38,15 31,37 9,37 2,15" fill="rgba(16,185,129,0.4)" stroke="rgb(16,185,129)" stroke-width="2" />
                                    </svg><span>Pentagon</span>
                                </button>
                                <button type="button" onclick="addShape('hexagon')" class="shape-btn group">
                                    <svg class="w-8 h-8" viewBox="0 0 40 40">
                                        <polygon points="20,2 36,10 36,30 20,38 4,30 4,10" fill="rgba(16,185,129,0.4)" stroke="rgb(16,185,129)" stroke-width="2" />
                                    </svg><span>Hexagon</span>
                                </button>
                                <button type="button" onclick="addShape('star')" class="shape-btn group">
                                    <svg class="w-8 h-8" viewBox="0 0 40 40">
                                        <polygon points="20,2 25,15 39,15 27,24 31,38 20,29 9,38 13,24 1,15 15,15" fill="rgba(16,185,129,0.4)" stroke="rgb(16,185,129)" stroke-width="2" />
                                    </svg><span>Star</span>
                                </button>
                                <button type="button" onclick="addShape('heart')" class="shape-btn group">
                                    <svg class="w-8 h-8" viewBox="0 0 40 40">
                                        <path d="M20 36 C10 28 2 22 2 14 C2 8 6 4 12 4 C16 4 19 6 20 9 C21 6 24 4 28 4 C34 4 38 8 38 14 C38 22 30 28 20 36Z" fill="rgba(16,185,129,0.4)" stroke="rgb(16,185,129)" stroke-width="2" />
                                    </svg><span>Heart</span>
                                </button>
                                <button type="button" onclick="addShape('arch')" class="shape-btn group">
                                    <svg class="w-10 h-10" viewBox="0 0 40 40">
                                        <path d="M4 38 L4 18 C4 8 12 2 20 2 C28 2 36 8 36 18 L36 38 Z" fill="rgba(16,185,129,0.4)" stroke="rgb(16,185,129)" stroke-width="2" />
                                    </svg><span>Arch</span>
                                </button>
                                <button type="button" onclick="addShape('oval')" class="shape-btn group">
                                    <div class="w-6 h-9 bg-emerald-500/40 border-2 border-emerald-500 rounded-full"></div><span>Oval</span>
                                </button>
 
                <button type="button" onclick="startPolygonDraw()" class="shape-btn group col-span-2 bg-emerald-50/50">
                    <svg class="w-8 h-8" viewBox="0 0 40 40">
                        <path d="M 10,30 L 5,10 L 25,5 L 35,20 L 25,35 Z" fill="rgba(16,185,129,0.4)"
                            stroke="rgb(16,185,129)" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                    <span>Custom (Freehand)</span>
                </button>
            </div>

            <!-- Add Text
                <div style="padding:10px; border-top:1px solid #f1f5f9;">
                    <h3
                        style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">
                        Add Text</h3>
                    <input type="text" id="maskTextInput" placeholder="Enter text…"
                        style="width:100%; padding:6px 10px; font-size:12px; border:1px solid #e2e8f0; border-radius:8px; outline:none; margin-bottom:6px; box-sizing:border-box;">
                    <div style="display:flex; gap:6px; margin-bottom:6px;">
                        <select id="maskFontSize"
                            style="flex:1; padding:5px 6px; font-size:11px; border:1px solid #e2e8f0; border-radius:6px; background:#fff; outline:none;">
                            <option value="14">14px</option>
                            <option value="18">18px</option>
                            <option value="24" selected>24px</option>
                            <option value="32">32px</option>
                            <option value="40">40px</option>
                            <option value="48">48px</option>
                            <option value="64">64px</option>
                            <option value="80">80px</option>
                        </select>
                        <input type="color" id="maskTextColor" value="#0064ff"
                            style="width:34px; height:30px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer; padding:2px;">
                    </div>
                    <select id="maskFontFamily"
                        style="width:100%; padding:5px 6px; font-size:11px; border:1px solid #e2e8f0; border-radius:6px; background:#fff; outline:none; margin-bottom:8px;">
                        <option>Arial</option>
                        <option>Helvetica</option>
                        <option>Times New Roman</option>
                        <option>Georgia</option>
                        <option>Courier New</option>
                        <option>Verdana</option>
                        <option>Impact</option>
                        <option>Comic Sans MS</option>
                    </select>
                    <button type="button" onclick="addTextMask()"
                        style="width:100%; padding:7px 0; font-size:12px; font-weight:600; color:#fff; background:#2563eb; border:none; border-radius:8px; cursor:pointer;">
                        + Add Text
                    </button>
                </div>
                -->
            <!-- Active Mask List -->
            <div class="border-t border-surface-100 px-4 py-3 flex-1">
                <h3 class="text-xs font-semibold text-surface-500 uppercase tracking-wider mb-3">Active Masks</h3>
                <div id="maskList" class="space-y-2">
                    <p class="text-xs text-surface-400 italic" id="noMaskMsg">No masks added yet</p>
                </div>
            </div>

            <!-- Properties Panel -->
            <div id="propsPanel" style="border-top:1px solid #f1f5f9; padding:10px; display:none;">
                <h3
                    style="font-size:11px; font-weight:600; color:#64748b; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">
                    Properties</h3>
                <!-- Shape props -->
                <div id="shapeProps" style="display:none;">
                    <div style="margin-bottom:6px;">
                        <label style="font-size:10px; color:#94a3b8; display:block; margin-bottom:3px;">Fill Color</label>
                        <input type="color" id="propFillColor" value="#0064ff"
                            onchange="applyShapeProp('fill',this.value)"
                            style="width:100%; height:28px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer; padding:2px;">
                    </div>
                    <div style="margin-bottom:6px;">
                        <label style="font-size:10px; color:#94a3b8; display:block; margin-bottom:3px;">Border
                            Color</label>
                        <input type="color" id="propStrokeColor" value="#0050dc"
                            onchange="applyShapeProp('stroke',this.value)"
                            style="width:100%; height:28px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer; padding:2px;">
                    </div>
                    <div style="margin-bottom:6px;">
                        <label style="font-size:10px; color:#94a3b8; display:block; margin-bottom:3px;">Opacity</label>
                        <input type="range" id="propOpacity" min="0" max="100" value="45"
                            oninput="applyOpacity(this.value)" style="width:100%; cursor:pointer;">
                        <span id="propOpacityVal" style="font-size:10px; color:#64748b;">45%</span>
                    </div>
                </div>
                <!-- Text props -->
                <div id="textProps" style="display:none;">
                    <div style="margin-bottom:6px;">
                        <label style="font-size:10px; color:#94a3b8; display:block; margin-bottom:3px;">Text Color</label>
                        <input type="color" id="propTextColor" value="#0064ff"
                            onchange="applyTextProp('fill',this.value)"
                            style="width:100%; height:28px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer; padding:2px;">
                    </div>
                    <div style="margin-bottom:6px;">
                        <label style="font-size:10px; color:#94a3b8; display:block; margin-bottom:3px;">Font Size</label>
                        <select id="propFontSize" onchange="applyTextProp('fontSize',parseInt(this.value))"
                            style="width:100%; padding:5px 6px; font-size:11px; border:1px solid #e2e8f0; border-radius:6px; background:#fff; outline:none;">
                            <option value="14">14px</option>
                            <option value="18">18px</option>
                            <option value="24">24px</option>
                            <option value="32">32px</option>
                            <option value="40">40px</option>
                            <option value="48">48px</option>
                            <option value="64">64px</option>
                            <option value="80">80px</option>
                        </select>
                    </div>
                    <div style="margin-bottom:6px;">
                        <label style="font-size:10px; color:#94a3b8; display:block; margin-bottom:3px;">Font Family</label>
                        <select id="propFontFamily" onchange="applyTextProp('fontFamily',this.value)"
                            style="width:100%; padding:5px 6px; font-size:11px; border:1px solid #e2e8f0; border-radius:6px; background:#fff; outline:none;">
                            <option>Arial</option>
                            <option>Helvetica</option>
                            <option>Times New Roman</option>
                            <option>Georgia</option>
                            <option>Courier New</option>
                            <option>Verdana</option>
                            <option>Impact</option>
                            <option>Comic Sans MS</option>
                        </select>
                    </div>
                    <div style="margin-bottom:6px;">
                        <label style="font-size:10px; color:#94a3b8; display:block; margin-bottom:3px;">Bold /
                            Italic</label>
                        <div style="display:flex; gap:4px;">
                            <button type="button" id="propBold" onclick="toggleBold()"
                                style="flex:1; padding:4px; font-size:12px; font-weight:700; border:1px solid #e2e8f0; border-radius:6px; background:#fff; cursor:pointer;">B</button>
                            <button type="button" id="propItalic" onclick="toggleItalic()"
                                style="flex:1; padding:4px; font-size:12px; font-style:italic; border:1px solid #e2e8f0; border-radius:6px; background:#fff; cursor:pointer;">I</button>
                        </div>
                    </div>
                </div>
                <!-- Position readout -->
                <div style="margin-top:8px; display:grid; grid-template-columns:1fr 1fr; gap:4px; font-size:11px;">
                    <div style="background:#f8fafc; border-radius:6px; padding:4px 8px;"><span
                            style="color:#94a3b8;">X:</span> <span id="posX"
                            style="font-family:monospace; color:#334155;">—</span></div>
                    <div style="background:#f8fafc; border-radius:6px; padding:4px 8px;"><span
                            style="color:#94a3b8;">Y:</span> <span id="posY"
                            style="font-family:monospace; color:#334155;">—</span></div>
                    <div style="background:#f8fafc; border-radius:6px; padding:4px 8px;"><span
                            style="color:#94a3b8;">W:</span> <span id="posW"
                            style="font-family:monospace; color:#334155;">—</span></div>
                    <div style="background:#f8fafc; border-radius:6px; padding:4px 8px;"><span
                            style="color:#94a3b8;">H:</span> <span id="posH"
                            style="font-family:monospace; color:#334155;">—</span></div>
                </div>
            </div>
        </div><!-- /sidebar -->
    </div>

    <!-- Hidden save form -->
    <form id="saveMaskForm" action="{{ route('admin.products.mask.save', $product) }}" method="POST">
        @csrf
        <input type="hidden" name="mask_data" id="maskDataInput">
    </form>

    <style>
        .shape-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
            cursor: pointer;
            background: white;
            transition: all 0.15s;
        }

        .shape-btn:hover {
            border-color: #93c5fd;
            background: #eff6ff;
        }

        .shape-btn span {
            font-size: 10px;
            font-weight: 500;
            color: #64748b;
        }

        .shape-btn:hover span {
            color: #2563eb;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <script>
        (function() {
            // ─────────────────────────────────────────────────────────────────────────
            // CONSTANTS & STATE
            // ─────────────────────────────────────────────────────────────────────────
            const BASE_WIDTH = 600; // Drive canvas width, height will follow aspect ratio
            const PADDING = 100; // Bleed area padding around the image
            const MASK_COLOR = 'rgba(0, 100, 255, 0.15)';
            const MASK_BORDER = 'rgba(0, 80, 220, 0.6)';

            const imageData = @json($imageTypes);
            const allSavedMasks = @json($savedMaskData);

            // Per-tab state
            const canvasStates = {};
            let activeTab = 'frame_image';
            let canvas = null;
            let bgImage = null;
            let maskCounter = 0;

            // Initialise state for each tab
            Object.keys(imageData).forEach(key => {
                canvasStates[key] = {
                    masks: (allSavedMasks?.[key]?.masks) ?? [],
                    enabled: (allSavedMasks?.[key]?.enabled !== undefined) ? allSavedMasks[key].enabled :
                        true,
                    canvasWidth: BASE_WIDTH,
                    canvasHeight: (allSavedMasks?.[key]?.canvasHeight) || 400
                };
            });

            // Preload every image's natural dimensions so each tab persists the CORRECT
            // canvasHeight even if the admin never clicks into it. Without this, only the
            // tab(s) actually visited get real dimensions (saveCurrentTabState runs on the
            // active tab only); unvisited tabs would save the default 600×400 landscape,
            // which squishes portrait pages on the customer-facing customizer.
            const imageProbes = {};
            Object.keys(imageData).forEach(key => {
                const url = imageData[key]?.url;
                if (!url) return;
                const probe = new Image();
                probe.onload = function() {
                    if (probe.width > 0) {
                        canvasStates[key].canvasHeight = Math.round(BASE_WIDTH * probe.height / probe
                            .width);
                    }
                };
                probe.src = url;
                imageProbes[key] = probe;
            });

            // ─────────────────────────────────────────────────────────────────────────
            // INIT
            // ─────────────────────────────────────────────────────────────────────────
            function initCanvas() {
                canvas = new fabric.Canvas('maskCanvas', {
                    width: BASE_WIDTH,
                    height: 400,
                    backgroundColor: '#e8e8e8',
                    selection: true,
                    preserveObjectStacking: true,
                });

                canvas.on('selection:created', onSelect);
                canvas.on('selection:updated', onSelect);
                canvas.on('selection:cleared', onDeselect);
                canvas.on('object:modified', e => {
                    updatePositionDisplay(e.target);
                    updateMaskList();
                });
                canvas.on('object:moving', e => {
                    containObject(e.target);
                    updatePositionDisplay(e.target);
                });
                canvas.on('object:scaling', e => {
                    containObject(e.target);
                    updatePositionDisplay(e.target);
                });
                canvas.on('object:rotating', e => {
                    containObject(e.target);
                    updatePositionDisplay(e.target);
                });
                canvas.on('mouse:down', onMouseDown);
                canvas.on('mouse:move', onMouseMove);
                canvas.on('mouse:dblclick', () => {
                    if (isDrawingPolygon) finishPolygon();
                });

                document.addEventListener('keydown', e => {
                    if (isDrawingPolygon) {
                        if (e.key === 'Enter') finishPolygon();
                        if (e.key === 'Escape') cancelPolygon();
                    } else if ((e.key === 'Delete' || e.key === 'Backspace') && !e.target.matches(
                            'input,textarea')) {
                        deleteSelected();
                    }
                });

                loadActiveTab();
            }

            function containObject(obj) {
                if (!obj || obj.name === 'bgImage' || obj.name === 'boundaryBorder') return;
                // Bounding limits removed as requested to allow full canvas bleed and placement
            }

            // ─────────────────────────────────────────────────────────────────────────
            // TAB SWITCHING
            // ─────────────────────────────────────────────────────────────────────────
            window.switchTab = function(key) {
                saveCurrentTabState();
                activeTab = key;

                document.querySelectorAll('.mask-tab').forEach(t => {
                    t.classList.remove('bg-brand-50', 'border-brand-500', 'text-brand-700');
                    t.classList.add('bg-white', 'border-surface-100', 'text-surface-500');
                });
                const tab = document.getElementById('tab-' + key);
                if (tab) {
                    tab.classList.remove('bg-white', 'border-surface-100', 'text-surface-500');
                    tab.classList.add('bg-brand-50', 'border-brand-500', 'text-brand-700');
                }
                loadActiveTab();
            };

            window.toggleCanvas = function(key, enabled) {
                canvasStates[key].enabled = enabled;
                if (key === activeTab) updateDisabledOverlay();
            };

            function updateDisabledOverlay() {
                const ov = document.getElementById('canvasDisabledOverlay');
                ov.style.display = canvasStates[activeTab].enabled ? 'none' : 'flex';
            }

            function saveCurrentTabState() {
                if (canvas) {
                    canvasStates[activeTab].masks = serializeCurrentMasks();
                    canvasStates[activeTab].canvasWidth = BASE_WIDTH;
                    canvasStates[activeTab].canvasHeight = canvas.height - 2 * PADDING;
                }
            }

            function loadActiveTab() {
                if (!canvas) return;

                canvas.clear();
                canvas.setBackgroundColor('#e8e8e8', canvas.renderAll.bind(canvas));
                bgImage = null;

                const labels = {
                    frame_image: 'Frame Image',
                    sample_image: 'Sample Image',
                    background_image: 'Background',
                    overlay_image: 'Overlay',
                };
                document.getElementById('canvasLabel').textContent = 'Canvas — ' + (labels[activeTab] || activeTab);
                updateDisabledOverlay();

                const imgUrl = imageData[activeTab]?.url;
                if (imgUrl) {
                    fabric.Image.fromURL(imgUrl, img => {
                        bgImage = img;

                        // 1. Determine natural aspect ratio and resize canvas
                        const ratio = img.height / img.width;
                        const newCanvasH = Math.round(BASE_WIDTH * ratio);

                        canvas.setDimensions({
                            width: BASE_WIDTH + 2 * PADDING,
                            height: newCanvasH + 2 * PADDING
                        });

                        // Update UI info
                        document.getElementById('canvasDimBadge').textContent = BASE_WIDTH + ' × ' +
                            newCanvasH + ' px';
                        document.getElementById('canvasPanel').style.width = (BASE_WIDTH + 2 * PADDING + 2) +
                            'px';

                        // 2. Add Background Image
                        img.set({
                            left: PADDING,
                            top: PADDING,
                            scaleX: BASE_WIDTH / img.width,
                            scaleY: newCanvasH / img.height,
                            selectable: false,
                            evented: false,
                            hasControls: false,
                            hasBorders: false,
                            name: 'bgImage',
                        });
                        canvas.add(img);
                        canvas.sendToBack(img);

                        // Draw a dashed visual boundary for the product image area
                        const boundary = new fabric.Rect({
                            left: PADDING,
                            top: PADDING,
                            width: BASE_WIDTH,
                            height: newCanvasH,
                            fill: 'transparent',
                            stroke: '#10b981', // Nice brand emerald color
                            strokeWidth: 2,
                            strokeDashArray: [6, 4],
                            selectable: false,
                            evented: false,
                            hasControls: false,
                            hasBorders: false,
                            name: 'boundaryBorder'
                        });
                        canvas.add(boundary);
                        boundary.moveTo(1); // Place just above background image

                        // 3. Restore Masks
                        (canvasStates[activeTab].masks || []).forEach(restoreMask);

                        canvas.renderAll();
                        updateMaskList();
                        updateCanvasInfo();
                    }, {
                        crossOrigin: 'anonymous'
                    });
                } else {
                    // Default if no image
                    canvas.setDimensions({
                        width: BASE_WIDTH,
                        height: 400
                    });
                    document.getElementById('canvasDimBadge').textContent = 'No Image';

                    canvas.add(new fabric.Text('No image uploaded for this slot', {
                        left: BASE_WIDTH / 2,
                        top: 200,
                        originX: 'center',
                        originY: 'center',
                        fontSize: 16,
                        fill: '#94a3b8',
                        fontFamily: 'sans-serif',
                        selectable: false,
                        evented: false,
                    }));
                    canvas.renderAll();
                    updateMaskList();
                    updateCanvasInfo();
                }
            }

            // ─────────────────────────────────────────────────────────────────────────
            // SELECTION CALLBACKS
            // ─────────────────────────────────────────────────────────────────────────
            function onSelect(e) {
                const obj = e.selected?.[0] ?? canvas.getActiveObject();
                if (!obj) return;
                document.getElementById('deleteBtn').style.display = 'inline-block';
                updatePositionDisplay(obj);
                showPropsPanel(obj);
            }

            function onDeselect() {
                document.getElementById('deleteBtn').style.display = 'none';
                ['posX', 'posY', 'posW', 'posH'].forEach(id => document.getElementById(id).textContent = '—');
                hidePropsPanel();
            }

            function showPropsPanel(obj) {
                const panel = document.getElementById('propsPanel');
                const shapePr = document.getElementById('shapeProps');
                const textPr = document.getElementById('textProps');
                panel.style.display = 'block';

                if (obj.maskType === 'text') {
                    shapePr.style.display = 'none';
                    textPr.style.display = 'block';
                    document.getElementById('propTextColor').value = rgbToHex(obj.fill || '#0064ff');
                    document.getElementById('propFontSize').value = obj.fontSize || 24;
                    document.getElementById('propFontFamily').value = obj.fontFamily || 'Arial';
                    document.getElementById('propBold').style.background = obj.fontWeight === 'bold' ? '#dbeafe' :
                        '#fff';
                    document.getElementById('propItalic').style.background = obj.fontStyle === 'italic' ? '#dbeafe' :
                        '#fff';
                } else if (obj.name?.startsWith('mask_')) {
                    textPr.style.display = 'none';
                    shapePr.style.display = 'block';
                    document.getElementById('propFillColor').value = rgbToHex(obj.fill || '#0064ff');
                    document.getElementById('propStrokeColor').value = rgbToHex(obj.stroke || '#0050dc');
                    const op = Math.round(obj.opacity * 100);
                    document.getElementById('propOpacity').value = op;
                    document.getElementById('propOpacityVal').textContent = op + '%';
                } else {
                    panel.style.display = 'none';
                }
            }

            function hidePropsPanel() {
                document.getElementById('propsPanel').style.display = 'none';
            }

            window.applyShapeProp = (prop, val) => {
                canvas.getActiveObject()?.set(prop, val);
                canvas.renderAll();
            };
            window.applyOpacity = val => {
                canvas.getActiveObject()?.set('opacity', val / 100);
                document.getElementById('propOpacityVal').textContent = val + '%';
                canvas.renderAll();
            };
            window.applyTextProp = (prop, val) => {
                const o = canvas.getActiveObject();
                if (o?.maskType === 'text') {
                    o.set(prop, val);
                    canvas.renderAll();
                }
            };
            window.toggleBold = () => {
                const o = canvas.getActiveObject();
                if (!o || o.maskType !== 'text') return;
                const b = o.fontWeight === 'bold';
                o.set('fontWeight', b ? 'normal' : 'bold');
                document.getElementById('propBold').style.background = b ? '#fff' : '#dbeafe';
                canvas.renderAll();
            };
            window.toggleItalic = () => {
                const o = canvas.getActiveObject();
                if (!o || o.maskType !== 'text') return;
                const it = o.fontStyle === 'italic';
                o.set('fontStyle', it ? 'normal' : 'italic');
                document.getElementById('propItalic').style.background = it ? '#fff' : '#dbeafe';
                canvas.renderAll();
            };

            function rgbToHex(color) {
                if (!color) return '#000000';
                if (color.startsWith('#') && color.length <= 7) return color;
                if (color.startsWith('#') && color.length > 7) return color.slice(0, 7);
                const m = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
                if (m) return '#' + [m[1], m[2], m[3]].map(x => (+x).toString(16).padStart(2, '0')).join('');
                return '#000000';
            }

            function updatePositionDisplay(obj) {
                if (!obj) return;
                document.getElementById('posX').textContent = Math.round(obj.left);
                document.getElementById('posY').textContent = Math.round(obj.top);
                document.getElementById('posW').textContent = Math.round(obj.getScaledWidth());
                document.getElementById('posH').textContent = Math.round(obj.getScaledHeight());
            }

            function updateCanvasInfo() {
                document.getElementById('canvasInfo').textContent = getMaskObjects().length + ' mask(s)';
            }

            function getMaskObjects() {
                return canvas.getObjects().filter(o => o.name?.startsWith('mask_'));
            }

            // ─────────────────────────────────────────────────────────────────────────
            // SHAPE FACTORY
            // ─────────────────────────────────────────────────────────────────────────
            window.addShape = function(type) {
                maskCounter++;
                const id = 'mask_' + maskCounter;
                const centre = canvas.getCenter();
                const jitter = () => Math.random() * 30 - 15;
                const base = {
                    left: centre.left - 50 + jitter(),
                    top: centre.top - 50 + jitter(),
                    fill: MASK_COLOR,
                    stroke: MASK_BORDER,
                    strokeWidth: 1,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                    cornerColor: '#2563eb',
                    cornerStyle: 'circle',
                    cornerSize: 9,
                    transparentCorners: false,
                    borderColor: '#2563eb',
                    name: id,
                    maskType: type,
                };

                let shape;
                switch (type) {
                    case 'square':
                        shape = new fabric.Rect({
                            ...base,
                            width: 100,
                            height: 100
                        });
                        break;
                    case 'rectangle':
                        shape = new fabric.Rect({
                            ...base,
                            width: 150,
                            height: 100
                        });
                        break;
                    case 'circle':
                        shape = new fabric.Circle({
                            ...base,
                            radius: 55
                        });
                        break;
                    case 'ellipse':
                        shape = new fabric.Ellipse({
                            ...base,
                            rx: 75,
                            ry: 50
                        });
                        break;
                    case 'triangle':
                        shape = new fabric.Triangle({
                            ...base,
                            width: 110,
                            height: 100
                        });
                        break;
                    case 'diamond':
                        shape = new fabric.Rect({
                            ...base,
                            width: 80,
                            height: 80,
                            angle: 45
                        });
                        break;
                    case 'pentagon':
                        shape = new fabric.Polygon(polygonPoints(5, 55), base);
                        break;
                    case 'hexagon':
                        shape = new fabric.Polygon(polygonPoints(6, 55), base);
                        break;
                    case 'star':
                        shape = new fabric.Polygon(starPoints(5, 55, 25), base);
                        break;
                    case 'heart':
                        shape = new fabric.Path(heartPath(), {
                            ...base,
                            scaleX: 0.8,
                            scaleY: 0.8
                        });
                        break;
                    case 'arch':
                        shape = new fabric.Path(archPath(), {
                            ...base,
                            scaleX: 0.7,
                            scaleY: 0.7
                        });
                        break;
                    case 'oval':
                        shape = new fabric.Ellipse({
                            ...base,
                            rx: 50,
                            ry: 70
                        });
                        break;
                    default:
                        shape = new fabric.Rect({
                            ...base,
                            width: 100,
                            height: 100
                        });
                }

                canvas.add(shape);
                canvas.setActiveObject(shape);
                canvas.renderAll();
                updateMaskList();
                updateCanvasInfo();
            };

            // ─────────────────────────────────────────────────────────────────────────
            // TEXT MASK
            // ─────────────────────────────────────────────────────────────────────────
            window.addTextMask = function() {
                const textVal = document.getElementById('maskTextInput').value.trim();
                if (!textVal) {
                    alert('Please enter text first');
                    return;
                }
                maskCounter++;
                const id = 'mask_' + maskCounter;
                const fontSize = parseInt(document.getElementById('maskFontSize').value) || 24;
                const family = document.getElementById('maskFontFamily').value || 'Arial';
                const color = document.getElementById('maskTextColor').value || '#0064ff';
                const centre = canvas.getCenter();

                const t = new fabric.IText(textVal, {
                    left: centre.left - 60 + Math.random() * 30,
                    top: centre.top - 20 + Math.random() * 30,
                    fontSize,
                    fontFamily: family,
                    fill: color,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                    cornerColor: '#2563eb',
                    cornerStyle: 'circle',
                    cornerSize: 9,
                    transparentCorners: false,
                    borderColor: '#2563eb',
                    name: id,
                    maskType: 'text',
                });
                canvas.add(t);
                canvas.setActiveObject(t);
                canvas.renderAll();
                updateMaskList();
                updateCanvasInfo();
                document.getElementById('maskTextInput').value = '';
            };

            // ─────────────────────────────────────────────────────────────────────────
            // FREEHAND POLYGON
            // ─────────────────────────────────────────────────────────────────────────
            let isDrawingPolygon = false,
                polyPoints = [],
                polyLines = [],
                polyActiveLine = null,
                polyActiveShape = null;

            function onMouseDown(opt) {
                if (!isDrawingPolygon) return;
                const ptr = canvas.getPointer(opt.e);
                if (polyPoints.length > 2 && opt.e.detail === 2) {
                    finishPolygon();
                    return;
                }
                if (polyPoints.length > 2) {
                    const fp = polyPoints[0];
                    if (opt.e.detail === 1 && Math.hypot(fp.x - ptr.x, fp.y - ptr.y) < 12) {
                        finishPolygon();
                        return;
                    }
                }
                polyPoints.push({
                    x: ptr.x,
                    y: ptr.y
                });

                const dot = new fabric.Circle({
                    radius: 4,
                    fill: '#2563eb',
                    left: ptr.x,
                    top: ptr.y,
                    originX: 'center',
                    originY: 'center',
                    selectable: false,
                    hasBorders: false,
                    hasControls: false,
                    id: 'poly_point',
                });

                if (polyPoints.length === 1) {
                    polyActiveShape = new fabric.Polygon(polyPoints, {
                        fill: 'rgba(59,130,246,0.2)',
                        stroke: '#2563eb',
                        strokeWidth: 1,
                        selectable: false,
                        hasBorders: false,
                        hasControls: false,
                        evented: false,
                        id: 'poly_shape',
                    });
                    canvas.add(polyActiveShape);
                }
                if (polyPoints.length > 1) {
                    const lp = polyPoints[polyPoints.length - 2];
                    const ln = new fabric.Line([lp.x, lp.y, ptr.x, ptr.y], {
                        strokeWidth: 1,
                        fill: '#2563eb',
                        stroke: '#2563eb',
                        originX: 'center',
                        originY: 'center',
                        selectable: false,
                        hasBorders: false,
                        hasControls: false,
                        evented: false,
                        id: 'poly_line',
                    });
                    polyLines.push(ln);
                    canvas.add(ln);
                }
                polyActiveLine = new fabric.Line([ptr.x, ptr.y, ptr.x, ptr.y], {
                    strokeWidth: 1,
                    fill: '#2563eb',
                    stroke: '#2563eb',
                    originX: 'center',
                    originY: 'center',
                    selectable: false,
                    hasBorders: false,
                    hasControls: false,
                    evented: false,
                    id: 'poly_line',
                });
                canvas.add(polyActiveLine);
                canvas.add(dot);
            }

            function onMouseMove(opt) {
                if (!isDrawingPolygon || !polyActiveLine) return;
                const ptr = canvas.getPointer(opt.e);
                polyActiveLine.set({
                    x2: ptr.x,
                    y2: ptr.y
                });
                if (polyActiveShape) polyActiveShape.set({
                    points: [...polyPoints, {
                        x: ptr.x,
                        y: ptr.y
                    }]
                });
                canvas.renderAll();
            }

            window.finishPolygon = function() {
                if (polyPoints.length < 3) {
                    cancelPolygon();
                    return;
                }
                canvas.getObjects().forEach(o => {
                    if (o.id === 'poly_point' || o.id === 'poly_line' || o.id === 'poly_shape') canvas
                        .remove(o);
                });
                maskCounter++;
                const id = 'mask_' + maskCounter;
                const poly = new fabric.Polygon(polyPoints, {
                    fill: MASK_COLOR,
                    stroke: MASK_BORDER,
                    strokeWidth: 1,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                    cornerColor: '#2563eb',
                    cornerStyle: 'circle',
                    cornerSize: 9,
                    transparentCorners: false,
                    borderColor: '#2563eb',
                    name: id,
                    maskType: 'custom_polygon',
                });
                canvas.add(poly);
                canvas.setActiveObject(poly);
                isDrawingPolygon = false;
                polyPoints = [];
                polyLines = [];
                polyActiveLine = null;
                polyActiveShape = null;
                document.getElementById('polygonHelp').style.display = 'none';
                canvas.renderAll();
                updateMaskList();
                updateCanvasInfo();
            };
            window.cancelPolygon = function() {
                canvas.getObjects().forEach(o => {
                    if (o.id === 'poly_point' || o.id === 'poly_line' || o.id === 'poly_shape') canvas
                        .remove(o);
                });
                isDrawingPolygon = false;
                polyPoints = [];
                polyLines = [];
                polyActiveLine = null;
                polyActiveShape = null;
                document.getElementById('polygonHelp').style.display = 'none';
                canvas.renderAll();
            };
            window.startPolygonDraw = function() {
                if (isDrawingPolygon) {
                    cancelPolygon();
                    return;
                }
                isDrawingPolygon = true;
                polyPoints = [];
                polyLines = [];
                polyActiveLine = null;
                polyActiveShape = null;
                canvas.discardActiveObject();
                document.getElementById('polygonHelp').style.display = 'flex';
            };

            // ─────────────────────────────────────────────────────────────────────────
            // GEOMETRY HELPERS
            // ─────────────────────────────────────────────────────────────────────────
            function polygonPoints(sides, r) {
                return Array.from({
                    length: sides
                }, (_, i) => {
                    const a = (Math.PI * 2 * i / sides) - Math.PI / 2;
                    return {
                        x: r + r * Math.cos(a),
                        y: r + r * Math.sin(a)
                    };
                });
            }

            function starPoints(pts, outer, inner) {
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
            }

            function heartPath() {
                return 'M 50 90 C 25 70 0 50 0 30 C 0 12 12 0 25 0 C 35 0 45 7 50 18 C 55 7 65 0 75 0 C 88 0 100 12 100 30 C 100 50 75 70 50 90 Z';
            }

            function archPath() {
                return 'M 10 120 L 10 50 C 10 15 30 0 60 0 C 90 0 110 15 110 50 L 110 120 Z';
            }

            // ─────────────────────────────────────────────────────────────────────────
            // MASK MANAGEMENT
            // ─────────────────────────────────────────────────────────────────────────
            window.deleteSelected = function() {
                const active = canvas.getActiveObject();
                if (!active || active.name === 'bgImage' || active.name === 'boundaryBorder') return;
                if (active.type === 'activeSelection') {
                    active.forEachObject(o => {
                        if (o.name !== 'bgImage' && o.name !== 'boundaryBorder') canvas.remove(o);
                    });
                    canvas.discardActiveObject();
                } else {
                    canvas.remove(active);
                }
                canvas.renderAll();
                updateMaskList();
                updateCanvasInfo();
            };
            window.clearAllMasks = function() {
                if (!confirm('Remove all masks from this canvas?')) return;
                getMaskObjects().forEach(o => canvas.remove(o));
                canvas.discardActiveObject();
                canvas.renderAll();
                updateMaskList();
                updateCanvasInfo();
            };
            window.selectMask = name => {
                const o = canvas.getObjects().find(o => o.name === name);
                if (o) {
                    canvas.setActiveObject(o);
                    canvas.renderAll();
                }
            };
            window.removeMask = name => {
                const o = canvas.getObjects().find(o => o.name === name);
                if (o) {
                    canvas.remove(o);
                    canvas.renderAll();
                    updateMaskList();
                    updateCanvasInfo();
                }
            };

            function updateMaskList() {
                const list = document.getElementById('maskList');
                const noMsg = document.getElementById('noMaskMsg');
                const masks = getMaskObjects();
                list.querySelectorAll('.mask-item').forEach(el => el.remove());
                if (masks.length === 0) {
                    noMsg.style.display = '';
                    return;
                }
                noMsg.style.display = 'none';
                masks.forEach((m, i) => {
                    const div = document.createElement('div');
                    div.className =
                        'mask-item flex items-center justify-between px-3 py-2 bg-surface-50 rounded-lg cursor-pointer hover:bg-emerald-50 transition group';
                    div.onclick = () => selectMask(m.name);
                    div.innerHTML = `
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-sm" style="background:${MASK_COLOR}; border:1px solid ${MASK_BORDER}"></div>
                    <span class="text-xs font-medium text-surface-700">${(m.maskType||'shape')[0].toUpperCase()+(m.maskType||'shape').slice(1)} ${i+1}</span>
                </div>
                <button type="button" onclick="event.stopPropagation();removeMask('${m.name}')"
                    class="p-1 rounded text-surface-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>`;
                    list.appendChild(div);
                });
            }

            // ─────────────────────────────────────────────────────────────────────────
            // RESTORE SAVED MASKS
            // ─────────────────────────────────────────────────────────────────────────
            function restoreMask(m) {
                maskCounter++;
                const id = 'mask_' + maskCounter;
                const base = {
                    left: m.left + PADDING,
                    top: m.top + PADDING,
                    scaleX: m.scaleX || 1,
                    scaleY: m.scaleY || 1,
                    angle: m.angle || 0,
                    fill: m.fill || MASK_COLOR,
                    stroke: m.stroke || MASK_BORDER,
                    strokeWidth: m.strokeWidth ?? 1,
                    opacity: m.opacity ?? 1,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                    cornerColor: '#2563eb',
                    cornerStyle: 'circle',
                    cornerSize: 9,
                    transparentCorners: false,
                    borderColor: '#2563eb',
                    name: id,
                    maskType: m.type,
                };

                let shape;
                switch (m.type) {
                    case 'square':
                    case 'rectangle':
                        shape = new fabric.Rect({
                            ...base,
                            width: m.width,
                            height: m.height
                        });
                        break;
                    case 'circle':
                        shape = new fabric.Circle({
                            ...base,
                            radius: m.radius
                        });
                        break;
                    case 'ellipse':
                    case 'oval':
                        shape = new fabric.Ellipse({
                            ...base,
                            rx: m.rx,
                            ry: m.ry
                        });
                        break;
                    case 'triangle':
                        shape = new fabric.Triangle({
                            ...base,
                            width: m.width,
                            height: m.height
                        });
                        break;
                    case 'diamond':
                        shape = new fabric.Rect({
                            ...base,
                            width: m.width,
                            height: m.height
                        });
                        break;
                    case 'pentagon':
                        shape = new fabric.Polygon(polygonPoints(5, 55), base);
                        break;
                    case 'hexagon':
                        shape = new fabric.Polygon(polygonPoints(6, 55), base);
                        break;
                    case 'star':
                        shape = new fabric.Polygon(starPoints(5, 55, 25), base);
                        break;
                    case 'heart':
                        shape = new fabric.Path(heartPath(), base);
                        break;
                    case 'arch':
                        shape = new fabric.Path(archPath(), base);
                        break;
                    case 'custom_polygon':
                        shape = new fabric.Polygon(m.points, base);
                        break;
                    case 'text':
                        shape = new fabric.IText(m.text || 'Text', {
                            ...base,
                            stroke: null,
                            strokeWidth: 0,
                            fontSize: m.fontSize || 24,
                            fontFamily: m.fontFamily || 'Arial',
                            fill: m.fill || '#0064ff',
                            fontWeight: m.fontWeight || 'normal',
                            fontStyle: m.fontStyle || 'normal',
                        });
                        break;
                    default:
                        shape = new fabric.Rect({
                            ...base,
                            width: m.width || 100,
                            height: m.height || 100
                        });
                }
                canvas.add(shape);
                updateMaskList();
            }

            // ─────────────────────────────────────────────────────────────────────────
            // SERIALIZE
            // Saves positions/sizes in ADMIN CANVAS PIXELS (CANVAS_W × CANVAS_H).
            // The frontend will scale them using canvasWidth/canvasHeight from the payload.
            // ─────────────────────────────────────────────────────────────────────────
            function serializeCurrentMasks() {
                return getMaskObjects().map(obj => {
                    const data = {
                        type: obj.maskType || 'square',
                        left: round4(obj.left - PADDING),
                        top: round4(obj.top - PADDING),
                        scaleX: round4(obj.scaleX),
                        scaleY: round4(obj.scaleY),
                        angle: round4(obj.angle),
                    };

                    if (obj.maskType === 'text') {
                        Object.assign(data, {
                            text: obj.text,
                            fontSize: obj.fontSize,
                            fontFamily: obj.fontFamily,
                            fill: obj.fill,
                            fontWeight: obj.fontWeight || 'normal',
                            fontStyle: obj.fontStyle || 'normal',
                        });
                    } else if (obj.maskType === 'custom_polygon') {
                        Object.assign(data, {
                            fill: obj.fill,
                            stroke: obj.stroke,
                            strokeWidth: obj.strokeWidth,
                            opacity: round4(obj.opacity),
                            points: obj.points,
                        });
                    } else {
                        Object.assign(data, {
                            fill: obj.fill,
                            stroke: obj.stroke,
                            strokeWidth: obj.strokeWidth,
                            opacity: round4(obj.opacity),
                        });
                        // Dimension fields — only relevant ones per shape type
                        if (obj.width !== undefined) data.width = Math.round(obj.width);
                        if (obj.height !== undefined) data.height = Math.round(obj.height);
                        if (obj.radius !== undefined) data.radius = Math.round(obj.radius);
                        if (obj.rx !== undefined) data.rx = Math.round(obj.rx);
                        if (obj.ry !== undefined) data.ry = Math.round(obj.ry);
                    }
                    return data;
                });
            }

            function round4(v) {
                return Math.round((v || 0) * 10000) / 10000;
            }

            // ─────────────────────────────────────────────────────────────────────────
            // SAVE ALL
            // ─────────────────────────────────────────────────────────────────────────
            window.saveMaskData = function() {
                saveCurrentTabState();

                const payload = {};
                Object.keys(canvasStates).forEach(key => {
                    // Read the real dimensions straight from the preloaded image if it has
                    // finished loading. This makes the save authoritative regardless of which
                    // tabs were visited, so skipping tabs can never persist the stale default.
                    const probe = imageProbes[key];
                    if (probe && probe.complete && probe.naturalWidth > 0) {
                        canvasStates[key].canvasHeight = Math.round(BASE_WIDTH * probe.naturalHeight / probe.naturalWidth);
                    }
                    payload[key] = {
                        enabled: canvasStates[key].enabled,
                        masks: canvasStates[key].masks,
                        // ← These values let the frontend reconstruct exact pixel coords for each specific image
                        canvasWidth: canvasStates[key].canvasWidth,
                        canvasHeight: canvasStates[key].canvasHeight,
                    };
                });

                document.getElementById('maskDataInput').value = JSON.stringify(payload);
                document.getElementById('saveMaskForm').submit();
            };

            window.resetView = function() {
                if (!bgImage) return;
                bgImage.set({
                    left: PADDING,
                    top: PADDING,
                    scaleX: BASE_WIDTH / bgImage._element.width,
                    scaleY: (canvas.height - 2 * PADDING) / bgImage._element.height
                });
                bgImage.setCoords();
                canvas.renderAll();
            };

            // ─────────────────────────────────────────────────────────────────────────
            // BOOT
            // ─────────────────────────────────────────────────────────────────────────
            document.addEventListener('DOMContentLoaded', initCanvas);
        })();
    </script>
@endpush
