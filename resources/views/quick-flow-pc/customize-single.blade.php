{{-- ═══════════════════════════════════════════════════════════════════════
     Single Canvas Customizer (Desktop) — No Masking
     Mobile equivalent: quick-flow/customize-single.blade.php
     - Single canvas (frame_image only, no page tabs)
     - Dynamic aspect ratio from product pdf_orientation
     - No masking / clip-path
     ═══════════════════════════════════════════════════════════════════════ --}}

@extends('layouts.quick-flow-pc')
@section('title', 'Customize Your ' . $product->name)

@push('styles')
<style>
    /* ── Hero Header ── */
    .hero-cust-gradient {
        background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #faf0ff 60%, #f0f4ff 100%);
    }
    .hero-cust-pattern {
        background-image: radial-gradient(circle at 1px 1px, rgba(236,72,153,0.04) 1px, transparent 0);
        background-size: 32px 32px;
    }
    .hero-blob-1 {
        position: absolute; top: -60px; right: 15%; width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, transparent 70%);
        border-radius: 50%; filter: blur(40px); pointer-events: none;
    }
    .hero-blob-2 {
        position: absolute; bottom: -40px; right: 5%; width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(249, 168, 212, 0.2) 0%, transparent 70%);
        border-radius: 50%; filter: blur(30px); pointer-events: none;
    }
    .hero-blob-3 {
        position: absolute; top: 20%; right: 35%; width: 80px; height: 80px;
        background: rgba(236, 72, 153, 0.15); border-radius: 50%; filter: blur(10px); pointer-events: none;
    }
    .hero-dots {
        position: absolute; top: 10%; right: 3%; width: 80px; height: 80px;
        background-image: radial-gradient(circle, rgba(236,72,153,0.2) 2px, transparent 2px);
        background-size: 10px 10px; border-radius: 50%; pointer-events: none;
    }

    .cust-page { display: grid; grid-template-columns: 1fr 420px; gap: 32px; align-items: start; }
    @media (max-width: 1024px) { .cust-page { grid-template-columns: 1fr; } }
    .cust-breadcrumb a { transition: color 0.2s ease; }

    /* Canvas */
    .canvas-wrapper { position: relative; background: #fff; border-radius: 1rem; overflow: hidden; touch-action: none; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 8px 24px -4px rgba(0,0,0,0.06); }
    .canvas-hidden { display: none !important; }
    .canvas-container { z-index: 100; touch-action: none; }
    .hidden { display: none !important; }

    /* Upload Zone */
    .upload-zone { border: 2px dashed #e2e8f0; border-radius: 1rem; padding: 1rem; background: #f8fafc; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; }
    .upload-zone:hover { border-color: var(--color-brand-500, #ec4899); background: #fdf2f8; transform: translateY(-1px); }
    .upload-zone.has-image { border-style: solid; border-color: #10b981; background: #f0fdf4; }

    /* Text Toolbar */
    .text-toolbar { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    .text-toolbar input[type="text"], .text-toolbar textarea { border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 10px 14px; font-size: 14px; background: #f8fafc; transition: all 0.2s; width: 100%; resize: none; min-height: 44px; }
    .text-toolbar input[type="text"]:focus, .text-toolbar textarea:focus { border-color: var(--color-brand-500, #ec4899); box-shadow: 0 0 0 3px rgba(236,72,153,0.1); outline: none; background: #fff; }
    .text-toolbar select { border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 8px 12px; font-size: 13px; font-weight: 600; background: #f8fafc; cursor: pointer; outline: none; -webkit-appearance: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; transition: border-color 0.2s; }
    .text-toolbar select:focus { border-color: var(--color-brand-500, #ec4899); }
    .text-toolbar button { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; }

    /* Mockup Preview */
    .preview-toggle-btn { color: #64748b; transition: all 0.2s ease; cursor: pointer; border: none; background: transparent; }
    .preview-toggle-btn.active { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
    .mockup-stage { border-radius: 16px; overflow: hidden; transition: background 0.35s ease, padding 0.35s ease; display: flex; align-items: center; justify-content: center; min-height: 200px; }
    .mockup-stage.flat { background: #f8fafc; padding: 24px; }
    .mockup-stage.room { background: linear-gradient(180deg, #eef2f7 0%, #e6ebf2 62%, #dfe5ee 62%, #d3dae4 100%); padding: 24px 24px 40px; position: relative; }
    .mockup-stage.room::after { content: ""; position: absolute; left: 0; right: 0; bottom: 26px; height: 2px; background: rgba(15,23,42,0.08); }
    .mockup-scene { display: flex; align-items: center; justify-content: center; width: 100%; }
    .mockup-frame { position: relative; display: inline-block; background: #fff; transition: max-width 0.35s cubic-bezier(0.4,0,0.2,1), border 0.35s ease, box-shadow 0.35s ease, padding 0.35s ease; }
    .mockup-stage.flat .mockup-frame { max-width: 100%; border-radius: 6px; padding: 0; box-shadow: 0 10px 30px -12px rgba(15,23,42,0.28); }
    .mockup-stage.room .mockup-frame { max-width: 74%; border: 10px solid #fff; border-radius: 2px; padding: 4px; box-shadow: 0 2px 3px rgba(0,0,0,0.1), 0 22px 44px -14px rgba(15,23,42,0.48); }
    .mockup-frame img { display: block; width: 100%; height: auto; border-radius: 2px; }
    .mockup-empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; min-height: 160px; color: #94a3b8; font-size: 12px; font-weight: 600; }

    /* Template strip */
    .template-strip { display: flex; gap: 8px; overflow-x: auto; padding: 4px 4px 2px; scrollbar-width: none; flex-wrap: wrap; }
    .template-strip::-webkit-scrollbar { display: none; }
    .template-chip { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9999px; background: #f1f5f9; border: 1px solid #e2e8f0; font-size: 12px; font-weight: 700; color: #334155; cursor: pointer; transition: all .2s; white-space: nowrap; }
    .template-chip:hover { background: #e2e8f0; }
    .template-chip.active { background: var(--color-brand-600, #db2777); border-color: var(--color-brand-600, #db2777); color: #fff; }
    .template-cat-filter { display: flex; gap: 6px; overflow-x: auto; padding: 4px 4px 2px; scrollbar-width: none; }
    .template-cat-filter::-webkit-scrollbar { display: none; }
    .template-cat-chip { flex: 0 0 auto; padding: 5px 13px; border-radius: 9999px; font-size: 11px; font-weight: 700; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #64748b; cursor: pointer; transition: all .2s; white-space: nowrap; }
    .template-cat-chip.active { background: var(--color-brand-500, #ec4899); border-color: var(--color-brand-500, #ec4899); color: #fff; }

    /* Fonts */
    @font-face { font-family: 'ABeeZee'; src: url("{{ asset('fonts/ABeeZeeRegular.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Alex Brush'; src: url("{{ asset('fonts/AlexBrush.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Alfa Slab One'; src: url("{{ asset('fonts/AlfaSlabOne.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Amatic SC'; src: url("{{ asset('fonts/AmaticSC.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Anton'; src: url("{{ asset('fonts/Anton.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Bangers'; src: url("{{ asset('fonts/Bangers.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Bebas Neue'; src: url("{{ asset('fonts/BebasNeue.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Bungee'; src: url("{{ asset('fonts/Bungee.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Caveat'; src: url("{{ asset('fonts/Caveat.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Cinzel'; src: url("{{ asset('fonts/Cinzel.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Courgette'; src: url("{{ asset('fonts/Courgette.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Crimson Pro'; src: url("{{ asset('fonts/CrimsonPro.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Dancing Script'; src: url("{{ asset('fonts/DancingScript.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Great Vibes'; src: url("{{ asset('fonts/GreatVibes.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Indie Flower'; src: url("{{ asset('fonts/IndieFlower.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Inter'; src: url("{{ asset('fonts/Inter.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Lato'; src: url("{{ asset('fonts/Lato.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Lobster'; src: url("{{ asset('fonts/Lobster.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Lora'; src: url("{{ asset('fonts/Lora.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Open Sans'; src: url("{{ asset('fonts/OpenSans.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Oswald'; src: url("{{ asset('fonts/Oswald.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Pacifico'; src: url("{{ asset('fonts/Pacifico.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Permanent Marker'; src: url("{{ asset('fonts/PermanentMarker.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Playfair Display'; src: url("{{ asset('fonts/PlayfairDisplay.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Poppins'; src: url("{{ asset('fonts/Poppins.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Righteous'; src: url("{{ asset('fonts/Righteous.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Roboto'; src: url("{{ asset('fonts/Roboto.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Sacramento'; src: url("{{ asset('fonts/Sacramento.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Satisfy'; src: url("{{ asset('fonts/Satisfy.ttf') }}"); font-display: swap; }
    @font-face { font-family: 'Shadows Into Light'; src: url("{{ asset('fonts/ShadowsIntoLightTwo.ttf') }}"); font-display: swap; }
</style>
@endpush

@section('content')
@php
$imageTypes = [];
$slots = ['frame_image' => 'Page 1'];
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
$maskData = [];
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
                            Single Page Customizer
                        </div>
                        <h1 class="text-2xl xl:text-3xl font-extrabold text-slate-900 tracking-tight">Customize <span class="bg-gradient-to-r from-brand-600 to-violet-500 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">{{ $product->name }}</span></h1>
                        <p class="text-xs text-slate-500 mt-0.5">Upload your photo and add text to personalize your design.</p>
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

    <div class="max-w-[1400px] mx-auto px-4">

    {{-- ── Main 2-Column Layout ── --}}
    <div class="cust-page">

        {{-- ═══ LEFT: Canvas ═══ --}}
        <div class="min-w-0 w-full space-y-5">
            {{-- Canvas (single page, no tab navigation) --}}
            <div class="canvas-wrapper w-full bg-white" id="canvas-container">
                @foreach($imageTypes as $key => $img)
                <div id="canvas-wrapper-{{ $key }}" class="canvas-layer" style="position:absolute;top:0;left:0;width:100%;visibility:hidden;pointer-events:none;z-index:-1;">
                    <canvas id="canvas-{{ $key }}"></canvas>
                </div>
                @endforeach
            </div>

            {{-- Zoom --}}
            <div id="zoom-control" class="hidden flex items-center gap-4 px-5 bg-white border border-slate-200 rounded-xl py-3">
                <div class="p-2 bg-slate-50 rounded-lg"><i data-lucide="image" class="w-4 h-4 text-slate-400"></i></div>
                <input type="range" id="zoom-slider" oninput="customizer.updateImageScale(this.value)"
                    min="0.1" max="3" step="0.01" value="1"
                    class="flex-1 h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-brand-500">
                <div class="p-2 bg-slate-50 rounded-lg"><i data-lucide="zoom-in" class="w-5 h-5 text-slate-400"></i></div>
            </div>

            {{-- Templates --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1.5 h-4 bg-brand-500 rounded-full"></div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Templates</span>
                </div>
                <div id="template-cat-filter" class="template-cat-filter mb-3"></div>
                <div class="template-strip" id="template-strip">
                    <span class="text-xs text-slate-400 py-2">No templates for this product.</span>
                </div>
            </div>
        </div>

        {{-- ═══ RIGHT: Tools Panel ═══ --}}
        <div class="space-y-5 w-full lg:sticky lg:top-20">

            {{-- Live Preview --}}
            <div id="mockup-preview-card" class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-4 bg-brand-500 rounded-full"></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">See it real</span>
                    </div>
                    <div class="flex items-center gap-1 bg-slate-100 rounded-full p-1" id="preview-toggle">
                        <button type="button" data-mode="flat" class="preview-toggle-btn active px-3 py-1 rounded-full text-[11px] font-bold">Flat</button>
                        <button type="button" data-mode="room" class="preview-toggle-btn px-3 py-1 rounded-full text-[11px] font-bold">In the room</button>
                    </div>
                </div>
                <div id="mockup-stage" class="mockup-stage flat">
                    <div class="mockup-scene">
                        <div id="mockup-frame" class="mockup-frame">
                            <img id="mockup-image" alt="Live preview of your design">
                            <div id="mockup-empty" class="mockup-empty">
                                <i data-lucide="image" class="w-6 h-6"></i>
                                <span>Your design appears here</span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 font-medium text-center mt-3">Live preview &middot; updates as you design</p>
            </div>

            {{-- Upload --}}
            <div id="upload-area" class="relative">
                <label class="block cursor-pointer h-full">
                    <div id="upload-zone" class="upload-zone !p-4 h-full flex items-center">
                        <div class="flex items-center gap-4 w-full">
                            <div id="upload-icon-bg" class="w-12 h-12 rounded-xl flex items-center justify-center shadow-sm shrink-0 bg-white text-slate-400 border border-slate-200">
                                <i id="upload-icon" data-lucide="camera" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 id="upload-text" class="font-bold text-sm text-slate-800">Upload Your Photo</h4>
                                <p class="text-xs font-medium text-slate-400 mt-0.5">Click to browse or drag &amp; drop</p>
                            </div>
                            <i data-lucide="upload-cloud" class="w-5 h-5 text-slate-300"></i>
                        </div>
                    </div>
                    <input type="file" onchange="customizer.handleFileUpload(this)"
                        class="absolute opacity-0 w-0 h-0 pointer-events-none"
                        id="photo-upload-input" accept="image/*">
                </label>
            </div>

            {{-- Text Toolbar --}}
            <div id="text-toolbar" class="text-toolbar flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[140px]">
                    <textarea id="text-input" placeholder="Add text..."
                        class="w-full" oninput="customizer.onTextInputChange(this.value)" rows="1"></textarea>
                    <button id="clear-text-btn" onclick="customizer.clearSelection()"
                        class="hidden absolute right-3 top-3 text-slate-300 hover:text-slate-500 transition-colors">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="flex flex-col gap-2 w-full md:flex-1">
                    <div class="flex items-center gap-2 w-full">
                        <select id="font-family-select" onchange="customizer._updateSelectedStyle('fontFamily', this.value)" class="flex-1 bg-transparent min-w-0">
                            <option style="font-family: 'Inter'">Inter</option>
                            <option style="font-family: 'Roboto'">Roboto</option>
                            <option style="font-family: 'Open Sans'">Open Sans</option>
                            <option style="font-family: 'Poppins'">Poppins</option>
                            <option style="font-family: 'Lato'">Lato</option>
                            <option style="font-family: 'Oswald'">Oswald</option>
                            <option style="font-family: 'Bebas Neue'">Bebas Neue</option>
                            <option style="font-family: 'Anton'">Anton</option>
                            <option style="font-family: 'Playfair Display'">Playfair Display</option>
                            <option style="font-family: 'Lora'">Lora</option>
                            <option style="font-family: 'Dancing Script'">Dancing Script</option>
                            <option style="font-family: 'Great Vibes'">Great Vibes</option>
                            <option style="font-family: 'Pacifico'">Pacifico</option>
                            <option style="font-family: 'Satisfy'">Satisfy</option>
                            <option style="font-family: 'Caveat'">Caveat</option>
                            <option style="font-family: 'Amatic SC'">Amatic SC</option>
                            <option style="font-family: 'Lobster'">Lobster</option>
                            <option style="font-family: 'Bangers'">Bangers</option>
                            <option style="font-family: 'Permanent Marker'">Permanent Marker</option>
                        </select>
                        <select id="text-align-select" onchange="customizer._updateSelectedStyle('textAlign', this.value)" class="flex-1 bg-transparent min-w-0">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                            <option value="justify">Justify</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 w-full">
                        <select id="font-size-select" onchange="customizer._updateSelectedStyle('fontSize', parseInt(this.value))" class="flex-1 bg-transparent min-w-0">
                            @for($i=8; $i<=96; $i+=2)
                            <option value="{{ $i }}" {{ $i == 24 ? 'selected' : '' }}>{{ $i }}</option>
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
                            <button id="add-text-btn" onclick="customizer.addText()" class="w-full bg-brand-600 hover:bg-brand-700 text-white h-10 px-2 rounded-xl text-sm font-bold shadow-sm transition-all active:scale-95">
                                + Add
                            </button>
                            <div id="editing-badge" class="hidden w-full flex items-center justify-center gap-1 h-10 px-2 bg-brand-50 text-brand-600 rounded-xl border border-brand-100">
                                <i data-lucide="type" class="w-4 h-4 shrink-0"></i>
                                <span class="text-[10px] font-extrabold uppercase tracking-widest truncate">Editing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Remove Button --}}
            <button id="remove-btn" onclick="customizer.handleRemove()"
                class="hidden w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 font-semibold text-sm hover:bg-red-100 transition-all">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span id="remove-btn-text">Remove Photo</span>
            </button>

            {{-- Checkout --}}
            <div class="pt-2 border-t border-slate-100">
                <form action="{{ route('flow-pc.cart.add') }}" method="POST" id="checkout-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="upload_ids" id="upload_ids_field">
                    <button type="button" id="submit-btn" onclick="customizer.submitAllCanvases()"
                        class="w-full bg-slate-900 hover:bg-black text-white font-extrabold py-4 rounded-2xl flex items-center justify-center gap-3 transition-all active:scale-[0.98] shadow-lg">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                        Add to Cart
                    </button>
                </form>
            </div>

            {{-- Help --}}
            <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center flex-shrink-0 border border-slate-200">
                    <i data-lucide="help-circle" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-600">Need help designing?</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Upload a photo, add text, then confirm to continue.</p>
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
        activeCanvas: 'frame_image',
        canvases: {},
        canvasEnabled: { 'frame_image': true },
        canvasImages: { 'frame_image': null },
        uploadIds: { 'frame_image': null },
        imgScales: { 'frame_image': 1 },
        isUploading: false,
        isSavingComposite: false,
        selectedObject: null,

        imageTypes: <?php echo json_encode($imageTypes); ?>,
        allMaskData: {},
        productId: <?php echo $product->id; ?>,

        init() {
            const waitForLayout = () => {
                const cont = document.getElementById('canvas-container');
                if (cont && cont.offsetWidth > 200) {
                    this._initAllCanvases();
                    this.updateUI();
                    this._initTemplates();
                } else { setTimeout(waitForLayout, 50); }
            };
            setTimeout(waitForLayout, 50);
            window.addEventListener('resize', this._debounce(() => this._resizeAllCanvases(), 150));

            document.getElementById('preview-toggle')?.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-mode]'); if (!btn) return;
                document.querySelectorAll('.preview-toggle-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById('mockup-stage').className = 'mockup-stage ' + btn.dataset.mode;
            });

            window.addEventListener('keydown', (e) => {
                if ((e.key === 'Delete' || e.key === 'Backspace') && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    if (this.selectedObject) { e.preventDefault(); this.handleRemove(); }
                }
            });

            this.updateUI();
        },

        updateUI() {
            const key = this.activeCanvas;
            const hasImg = this.canvasImages[key] !== null;

            // Canvas visibility (single canvas)
            const el = document.getElementById('canvas-wrapper-' + key);
            if (el) { el.style.position = 'relative'; el.style.visibility = 'visible'; el.style.pointerEvents = 'auto'; el.style.zIndex = '1'; }

            // Upload zone
            const uploadZone = document.getElementById('upload-zone');
            const uploadIconBg = document.getElementById('upload-icon-bg');
            const uploadText = document.getElementById('upload-text');
            if (uploadZone) uploadZone.classList.toggle('has-image', hasImg);
            if (hasImg) {
                if (uploadIconBg) uploadIconBg.className = 'w-12 h-12 rounded-xl flex items-center justify-center shadow-sm shrink-0 bg-emerald-100 text-emerald-600 border border-emerald-200';
                if (uploadText) uploadText.textContent = 'Photo Uploaded ✓';
            } else {
                if (uploadIconBg) uploadIconBg.className = 'w-12 h-12 rounded-xl flex items-center justify-center shadow-sm shrink-0 bg-white text-slate-400 border border-slate-200';
                if (uploadText) uploadText.textContent = 'Upload Your Photo';
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
            if (showRemove && removeBtnText) removeBtnText.textContent = this.selectedObject ? 'Remove Selected Text' : 'Remove Photo';

            if (window.lucide) window.lucide.createIcons();
        },

        _initAllCanvases() {
            const containerEl = document.getElementById('canvas-container');
            if (!containerEl) return;
            const displayWidth = containerEl.offsetWidth;

            Object.keys(this.imageTypes).forEach(key => {
                const canvasEl = document.getElementById('canvas-' + key);
                if (!canvasEl) return;

                const isPortrait = <?php echo ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false'; ?>;
                const adminW = isPortrait ? 400 : 560;
                const adminH = isPortrait ? 560 : 400;
                const scaleFactor = displayWidth / adminW;
                const displayHeight = Math.round(adminH * scaleFactor);

                containerEl.style.height = displayHeight + 'px';

                const fc = new fabric.Canvas('canvas-' + key, {
                    width: displayWidth, height: displayHeight,
                    backgroundColor: '#ffffff', selection: false,
                    preserveObjectStacking: true, allowTouchScrolling: true
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
                fc.on('object:scaling', (e) => { if (e.target._isUserImage) { this.imgScales[key] = e.target.scaleX; const s = document.getElementById('zoom-slider'); if (s) s.value = e.target.scaleX; } });

                this._loadCanvasState(key);
                fc.renderAll();
            });
        },

        _resizeAllCanvases() {
            const containerEl = document.getElementById('canvas-container');
            if (!containerEl) return;
            const newWidth = containerEl.offsetWidth;
            Object.keys(this.canvases).forEach(key => {
                const cv = this.canvases[key]; if (!cv) return;
                const isPortrait = <?php echo ($product->pdf_orientation ?? 'portrait') === 'portrait' ? 'true' : 'false'; ?>;
                const adminW = isPortrait ? 400 : 560; const adminH = isPortrait ? 560 : 400;
                const newH = Math.round(adminH * (newWidth / adminW));
                cv.fabricCanvas.setWidth(newWidth); cv.fabricCanvas.setHeight(newH);
                containerEl.style.height = newH + 'px';
                cv.fabricCanvas.requestRenderAll();
            });
        },

        _saveCanvasState(key) {
            const cv = this.canvases[key]; if (!cv) return;
            const objects = cv.fabricCanvas.getObjects().filter(o => o._isUserImage || o._isUserText);
            const data = { objects: objects.map(o => o.toObject(['_isUserImage', '_isUserText', '_uploadId'])), imgScale: this.imgScales[key], uploadId: this.uploadIds[key] };
            localStorage.setItem(`qrinto_single_v1_${this.productId}_${key}`, JSON.stringify(data));
        },

        _loadCanvasState(key) {
            const saved = localStorage.getItem(`qrinto_single_v1_${this.productId}_${key}`);
            if (!saved) return;
            try {
                const data = JSON.parse(saved);
                const cv = this.canvases[key]; const fc = cv.fabricCanvas;
                this.imgScales[key] = data.imgScale || 1;
                this.uploadIds[key] = data.uploadId;
                if (data.objects?.length > 0) {
                    fabric.util.enlivenObjects(data.objects, (objs) => {
                        objs.forEach(obj => {
                            obj.set({ selectable: true, evented: true, hasControls: true, lockScalingFlip: true, uniformScaling: true, cornerSize: 12, transparentCorners: false, borderColor: '#378ADD', cornerColor: '#378ADD', cornerStyle: 'circle' });
                            if (obj._isUserImage) { cv.imgObj = obj; this.canvasImages[key] = true; }
                            fc.add(obj);
                        });
                        fc.renderAll(); this.updateUI();
                    });
                }
            } catch (e) { console.error('Restore error:', e); }
        },

        _syncToolbarToSelection(obj) {
            const textInput = document.getElementById('text-input');
            const clearBtn = document.getElementById('clear-text-btn');
            const addBtn = document.getElementById('add-text-btn');
            const editBadge = document.getElementById('editing-badge');
            if (!obj || !['i-text', 'text', 'textbox'].includes(obj.type)) {
                if (textInput) textInput.placeholder = 'Add text...';
                clearBtn?.classList.add('hidden'); addBtn?.classList.remove('hidden'); editBadge?.classList.add('hidden'); return;
            }
            if (textInput && textInput.value !== obj.text) textInput.value = obj.text;
            const ff = document.getElementById('font-family-select'); if (ff) ff.value = obj.fontFamily;
            const fs = document.getElementById('font-size-select'); if (fs) fs.value = obj.fontSize.toString();
            const tc = document.getElementById('text-color-input'); if (tc) tc.value = obj.fill;
            const tp = document.getElementById('text-color-preview'); if (tp) tp.style.background = obj.fill;
            const ta = document.getElementById('text-align-select'); if (ta) ta.value = obj.textAlign || 'center';
            clearBtn?.classList.remove('hidden'); addBtn?.classList.add('hidden'); editBadge?.classList.remove('hidden');
        },

        async _updateSelectedStyle(property, value) {
            if (property === 'fill') { const tp = document.getElementById('text-color-preview'); if (tp) tp.style.background = value; }
            if (!this.selectedObject) return;
            if (property === 'fontFamily') { try { await document.fonts.load(`1em "${value}"`); } catch (e) {} }
            this.selectedObject.set(property, value);
            const cv = this.canvases[this.activeCanvas];
            if (cv) { cv.fabricCanvas.requestRenderAll(); this._saveCanvasState(this.activeCanvas); }
        },

        onTextInputChange(value) {
            if (this.selectedObject && ['i-text', 'text', 'textbox'].includes(this.selectedObject.type)) {
                this.selectedObject.set('text', value);
                this.canvases[this.activeCanvas]?.fabricCanvas?.requestRenderAll();
                this._saveCanvasState(this.activeCanvas);
                if (!value.trim()) this.handleRemove();
            } else if (value.trim()) { this.addText(true); }
        },

        clearSelection() {
            const cv = this.canvases[this.activeCanvas];
            if (cv) { cv.fabricCanvas.discardActiveObject().renderAll(); this.selectedObject = null; const ti = document.getElementById('text-input'); if (ti) ti.value = ''; this.updateUI(); }
        },

        _addImageToCanvas(key, url) {
            const cv = this.canvases[key]; if (!cv) return;
            if (cv.imgObj) cv.fabricCanvas.remove(cv.imgObj);
            fabric.Image.fromURL(url, img => {
                const canvasW = cv.fabricCanvas.width; const canvasH = cv.fabricCanvas.height;
                const s = Math.min(canvasW / img.width, canvasH / img.height) * 0.8;
                img.set({ left: (canvasW - img.width * s) / 2, top: (canvasH - img.height * s) / 2, scaleX: s, scaleY: s, cornerStyle: 'circle', cornerSize: 12, transparentCorners: false, borderColor: '#378ADD', cornerColor: '#378ADD', hasControls: true, hasBorders: true, selectable: true, _isUserImage: true, objectCaching: true, lockScalingFlip: true, uniformScaling: true });
                cv.fabricCanvas.add(img); cv.fabricCanvas.setActiveObject(img); cv.fabricCanvas.renderAll();
                img.setCoords(); cv.imgObj = img; this.imgScales[key] = s; this.updateUI();
            }, { crossOrigin: 'anonymous' });
        },

        async handleFileUpload(input) {
            const file = input.files[0]; if (!file) return;
            const key = this.activeCanvas;
            this.isUploading = true; this.updateUI();
            try {
                const optimized = await this._processImage(file);
                this.canvasImages[key] = optimized.dataUrl;
                this._addImageToCanvas(key, optimized.dataUrl);
                const fd = new FormData();
                fd.append('image', optimized.blob, 'upload.webp');
                fd.append('_token', '<?php echo csrf_token(); ?>');
                const res = await fetch('<?php echo route("flow-pc.upload"); ?>', { method: 'POST', body: fd });
                const dat = await res.json();
                if (dat.success) this.uploadIds[key] = dat.upload_id;
            } catch (err) { console.error('Upload error:', err); }
            finally { this.isUploading = false; input.value = ''; this.updateUI(); }
        },

        _processImage(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let w = img.width, h = img.height; const maxDim = 1200;
                    if (w > maxDim || h > maxDim) { if (w > h) { h *= maxDim / w; w = maxDim; } else { w *= maxDim / h; h = maxDim; } }
                    canvas.width = w; canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    const dataUrl = canvas.toDataURL('image/webp', 0.85);
                    canvas.toBlob((blob) => resolve({ blob, dataUrl }), 'image/webp', 0.85);
                };
                img.onerror = reject;
                img.src = URL.createObjectURL(file);
            });
        },

        updateImageScale(val) {
            const cv = this.canvases[this.activeCanvas]; if (!cv?.imgObj) return;
            this.imgScales[this.activeCanvas] = val;
            cv.imgObj.set({ scaleX: parseFloat(val), scaleY: parseFloat(val) });
            cv.imgObj.setCoords(); cv.fabricCanvas.requestRenderAll();
        },

        addText() {
            const key = this.activeCanvas;
            const cv = this.canvases[key]; if (!cv) return;
            const textVal = document.getElementById('text-input').value;
            if (!textVal.trim()) return;
            const fontSize = parseInt(document.getElementById('font-size-select').value);
            const fontFamily = document.getElementById('font-family-select').value;
            const color = document.getElementById('text-color-input').value;
            const align = document.getElementById('text-align-select').value;
            const t = new fabric.Textbox(textVal, {
                left: cv.fabricCanvas.width * 0.1, top: cv.fabricCanvas.height / 3,
                width: cv.fabricCanvas.width * 0.8, fontSize, fontFamily, fill: color, textAlign: align,
                _isUserText: true, objectCaching: false, cornerSize: 12, transparentCorners: false,
                borderColor: '#378ADD', cornerColor: '#378ADD', cornerStyle: 'circle', lockScalingFlip: true
            });
            this.selectedObject = t;
            cv.fabricCanvas.add(t); t.setCoords(); cv.fabricCanvas.setActiveObject(t); cv.fabricCanvas.renderAll();
            document.fonts.load(`${fontSize}px "${fontFamily}"`).then(() => {
                if (t.canvas) { t.set('fontFamily', fontFamily); t.setCoords(); t.canvas.requestRenderAll(); }
            }).catch(() => {});
            this.updateUI();
        },

        handleRemove() {
            const cv = this.canvases[this.activeCanvas]; if (!cv) return;
            if (this.selectedObject) {
                cv.fabricCanvas.remove(this.selectedObject); cv.fabricCanvas.discardActiveObject();
                this.selectedObject = null; const ti = document.getElementById('text-input'); if (ti) ti.value = '';
            } else if (cv.imgObj) {
                cv.fabricCanvas.remove(cv.imgObj);
                cv.imgObj = null; this.canvasImages[this.activeCanvas] = null; this.uploadIds[this.activeCanvas] = null;
            }
            cv.fabricCanvas.renderAll(); this.updateUI();
        },

        submitAllCanvases() {
            if (this.isSavingComposite) return;
            const hasUpload = Object.values(this.uploadIds).some(id => id !== null) ||
                Object.keys(this.canvases).some(k => this.canvases[k].fabricCanvas.getObjects().some(o => o._isUserText));
            if (!hasUpload) {
                document.getElementById('upload_ids_field').value = JSON.stringify({});
                document.getElementById('checkout-form').submit(); return;
            }
            this.isSavingComposite = true;
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="animate-spin" data-lucide="loader-2"></i> Saving...';
            lucide.createIcons();
            const ids = {};
            const uploadPromises = Object.keys(this.canvases).map(async key => {
                const cv = this.canvases[key]; if (!cv) return;
                const hasEdit = this.canvasImages[key] !== null || cv.fabricCanvas.getObjects().some(o => o._isUserText);
                if (!hasEdit) return;
                cv.fabricCanvas.discardActiveObject();
                const b64 = cv.fabricCanvas.toDataURL({ format: 'jpeg', quality: 0.9, multiplier: 2 });
                const res = await fetch('<?php echo route("flow-pc.upload_composite"); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>' },
                    body: JSON.stringify({ image_data: b64, canvas_key: key }),
                });
                const dat = await res.json();
                if (dat.success) ids[key] = dat.upload_id;
            });
            Promise.all(uploadPromises).then(() => {
                document.getElementById('upload_ids_field').value = JSON.stringify(ids);
                document.getElementById('checkout-form').submit();
            }).catch(err => {
                console.error(err); this.isSavingComposite = false; btn.disabled = false;
                btn.innerHTML = '<i data-lucide="shopping-cart"></i> Add to Cart'; lucide.createIcons();
            });
        },

        _initTemplates() {
            const strip = document.getElementById('template-strip');
            if (!strip) return;
            const productTemplates = <?php echo json_encode($product->templates ?? []); ?>;
            if (!productTemplates?.length) return;
            strip.innerHTML = '';
            productTemplates.forEach(tpl => {
                const chip = document.createElement('button');
                chip.className = 'template-chip';
                chip.innerHTML = `<i data-lucide="layout-template"></i>${tpl.name}`;
                chip.onclick = () => this._applyTemplate(tpl);
                strip.appendChild(chip);
            });
            if (window.lucide) window.lucide.createIcons();
        },

        _applyTemplate(tpl) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv) return;
            const fc = cv.fabricCanvas;
            fc.getObjects().filter(o => o._isUserText || o._isTemplateText).forEach(o => fc.remove(o));
            (tpl.layers || []).forEach(layer => {
                if (['text', 'i-text', 'textbox'].includes(layer.type)) {
                    const t = new fabric.Textbox(layer.text || 'Text', {
                        left: (layer.left || 0.1) * fc.width, top: (layer.top || 0.3) * fc.height,
                        width: (layer.width || 0.8) * fc.width, fontSize: layer.fontSize || 24,
                        fontFamily: layer.fontFamily || 'Inter', fill: layer.fill || '#000000',
                        textAlign: layer.textAlign || 'center', _isUserText: true, _isTemplateText: true,
                        cornerSize: 10, transparentCorners: false, borderColor: '#378ADD', cornerColor: '#378ADD', cornerStyle: 'circle'
                    });
                    fc.add(t);
                }
            });
            fc.renderAll(); this.updateUI();
        },

        _debounce(fn, delay) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn.apply(this, a), delay); }; },

        clearAll() {
            const cv = this.canvases[this.activeCanvas]; if (!cv) return;
            cv.fabricCanvas.getObjects().filter(o => o._isUserImage || o._isUserText).forEach(o => cv.fabricCanvas.remove(o));
            cv.imgObj = null; this.canvasImages[this.activeCanvas] = null;
            this.selectedObject = null; cv.fabricCanvas.renderAll(); this.updateUI();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        customizer.init();
        // Live preview interval
        setInterval(() => {
            try {
                const key = customizer.activeCanvas;
                const cv = customizer.canvases[key]; if (!cv) return;
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
