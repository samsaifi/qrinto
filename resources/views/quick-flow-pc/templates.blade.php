@extends('layouts.quick-flow-pc')

@section('title', $type->name . ' — Choose a Template (Step 3)')
@section('header_title', $type->name)

@push('styles')
<style>
    .hero-mesh-overlay {
        position: relative;
    }
    .hero-orb-1 {
        position: absolute; top: -100px; right: -50px; width: 450px; height: 450px;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.25) 0%, rgba(147, 51, 234, 0.15) 50%, transparent 70%);
        border-radius: 50%; filter: blur(80px); pointer-events: none; animation: orbPulse 8s infinite alternate ease-in-out;
    }
    .hero-orb-2 {
        position: absolute; bottom: -120px; left: -80px; width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(168, 85, 247, 0.15) 50%, transparent 70%);
        border-radius: 50%; filter: blur(90px); pointer-events: none; animation: orbPulse 10s infinite alternate-reverse ease-in-out;
    }
    @keyframes orbPulse {
        0% { transform: scale(1) translate(0, 0); }
        50% { transform: scale(1.1) translate(20px, -15px); }
        100% { transform: scale(0.95) translate(-15px, 20px); }
    }
    .search-input-hero-wrapper {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .search-input-hero-wrapper:focus-within {
        box-shadow: 0 0 0 4px rgba(236,72,153,0.25), 0 25px 50px -12px rgba(0,0,0,0.3);
        border-color: #ec4899;
    }
    .filter-pill {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .filter-pill:hover {
        transform: translateY(-1px);
    }
    .template-card-premium {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .template-card-premium:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px -16px rgba(0,0,0,0.12);
    }
    .template-card-premium:hover .tpl-overlay {
        opacity: 1;
    }
    .template-card-premium:hover .tpl-img {
        transform: scale(1.06);
    }
    .tpl-overlay {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .tpl-img {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .tpl-fade {
        animation: tplFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes tplFade {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div x-data="{ activeCategory: 'all', searchQuery: '' }" class="w-full overflow-hidden pb-24">

    {{-- ===== HERO HEADER (STEP 3 LUXURY DARK PURPLE & FAN SLIDER) ===== --}}
    <section class="hero-mesh-overlay w-full px-6 lg:px-12 pt-12 pb-20 relative overflow-hidden text-white shadow-xl"
        style="background: linear-gradient(135deg, #0d061c 0%, #1c0836 35%, #2a074a 70%, #0d061c 100%) !important;">
        <!-- Animated Ambient Gradient Blobs -->
        <div class="hero-orb-1"></div>
        <div class="hero-orb-2"></div>

        <!-- Giant Background Watermark Text "Step 3" (Bottom Right) -->
        <div class="absolute right-4 sm:right-10 bottom-2 sm:bottom-4 text-[140px] sm:text-[220px] lg:text-[300px] font-black text-white/[0.035] select-none pointer-events-none tracking-tighter leading-none z-0">
            Step 3
        </div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            {{-- Top Navigation & Store Indicator Row --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 tpl-fade" style="animation-delay:0s">
                {{-- Breadcrumbs --}}
                <nav class="flex flex-wrap items-center gap-2 text-xs sm:text-sm font-semibold">
                    <a href="{{ route('flow-pc.index') }}" class="text-slate-300 hover:text-white transition-colors flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 px-3 py-1.5 rounded-full shadow-2xs">
                        <i data-lucide="home" class="w-3.5 h-3.5 text-pink-400"></i> Home
                    </a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    @if($type->parent)
                    <a href="{{ route('flow-pc.category', $type->parent->slug) }}" class="text-slate-300 hover:text-white transition-colors bg-white/10 backdrop-blur-md border border-white/15 px-3 py-1.5 rounded-full shadow-2xs">
                        {{ $type->parent->name }}
                    </a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    @endif
                    <span class="text-pink-300 bg-pink-500/10 backdrop-blur-md border border-pink-500/30 px-3 py-1.5 rounded-full shadow-2xs font-extrabold">{{ $type->name }}</span>
                </nav>

                {{-- Active Branch Pill --}}
                @if(session()->has('active_store_id'))
                    @php $activeStore = \App\Models\Store::find(session('active_store_id')); @endphp
                    @if($activeStore)
                    <div class="flex items-center gap-2.5 bg-emerald-500/10 backdrop-blur-md border border-emerald-500/30 rounded-full px-4 py-1.5 shadow-2xs shrink-0 self-start md:self-auto">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-[11px] font-black text-slate-200">Active Branch: <strong class="text-emerald-300 font-black">{{ $activeStore->store_name }}</strong></span>
                        <a href="{{ route('flow-pc.find-store') }}" class="text-[10px] font-black text-emerald-400 hover:underline uppercase tracking-wider ml-1">Change</a>
                    </div>
                    @endif
                @endif
            </div>

            <div class="grid grid-cols-12 gap-10 lg:gap-14 items-center">

                {{-- Left Column --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-6">
                    <!-- Badge Tag -->
                    <div>
                        <span class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-md border border-pink-500/30 text-pink-300 text-[11px] font-black px-4 py-1.5 rounded-full mb-6 uppercase tracking-widest shadow-lg">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-pink-400"></i>
                            READY-MADE DESIGN STUDIO
                        </span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-4xl sm:text-5xl xl:text-6xl font-black text-white leading-[1.08] tracking-tight drop-shadow-md">
                        {{ $type->name }}<br>
                        <span class="bg-gradient-to-r from-pink-300 via-purple-300 to-pink-400 bg-clip-text text-transparent italic"
                            style="font-family: 'Playfair Display', serif;">Templates & Studio.</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-base sm:text-lg text-slate-200 font-medium mt-5 leading-relaxed max-w-xl">
                        Select a professionally crafted template or start with a blank canvas to customize your {{ strtolower($type->parent->name ?? $type->name) }}.
                    </p>

                    {{-- Hero Search Bar (Live Filter) --}}
                    <div class="mt-8">
                        <div class="search-input-hero-wrapper bg-slate-950/80 backdrop-blur-xl rounded-2xl border border-white/20 p-2 flex flex-col sm:flex-row items-center gap-3 shadow-2xl max-w-xl">
                            <div class="flex items-center flex-1 gap-3 pl-4 py-2 w-full">
                                <i data-lucide="search" class="w-5 h-5 text-pink-400 shrink-0"></i>
                                <input type="text" placeholder="Search templates by title or style..."
                                    x-model="searchQuery" autocomplete="off"
                                    class="w-full text-sm sm:text-base text-white placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0">
                            </div>
                            <button class="w-full sm:w-auto bg-gradient-to-r from-pink-500 via-purple-600 to-indigo-600 hover:from-pink-600 hover:to-indigo-700 text-white font-black px-7 py-3.5 rounded-xl transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 shadow-lg shrink-0 cursor-pointer">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Filter</span>
                            </button>
                        </div>
                    </div>

                    {{-- Template Specs Pills --}}
                    <div class="mt-5 flex items-center flex-wrap gap-2.5 text-xs font-bold text-slate-300">
                        @if($type->width && $type->height)
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 text-slate-200 px-3.5 py-1.5 rounded-full">
                            <i data-lucide="ruler" class="w-3.5 h-3.5 text-pink-400"></i>
                            {{ $type->width }}&times;{{ $type->height }} {{ $type->unit }}
                        </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 text-slate-200 px-3.5 py-1.5 rounded-full">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-pink-400"></i>
                            {{ $templates->count() }} Designs Available
                        </span>
                        @if($type->price)
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 text-pink-300 px-3.5 py-1.5 rounded-full">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-pink-400"></i>
                            From {{ \App\Services\CurrencyService::format($type->price) }}
                        </span>
                        @endif
                    </div>

                    {{-- Trust Stats Bar --}}
                    <div class="mt-10 pt-8 border-t border-white/15 flex items-center gap-8 text-white">
                        <div>
                            <p class="text-3xl font-black bg-gradient-to-r from-pink-300 to-cyan-300 bg-clip-text text-transparent">100%</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Customizable</p>
                        </div>
                        <div class="h-8 w-px bg-white/15"></div>
                        <div>
                            <p class="text-3xl font-black bg-gradient-to-r from-amber-300 to-pink-300 bg-clip-text text-transparent">Live 3D</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Print Preview</p>
                        </div>
                        <div class="h-8 w-px bg-white/15 hidden sm:block"></div>
                        <div class="hidden sm:block">
                            <p class="text-3xl font-black bg-gradient-to-r from-emerald-300 to-cyan-300 bg-clip-text text-transparent">Express</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Local Pickup</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Multi-Layer Stacked Fanned Cards Showcase --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-6 hidden lg:block">
                    <div x-data="templateFanSlider()" class="relative w-[480px] h-[480px] max-w-full ml-auto flex items-center justify-center pt-8 overflow-visible">
                        
                        <!-- Cards Fan Deck Container -->
                        <div class="relative w-[300px] h-[420px] flex items-center justify-center overflow-visible">
                            <template x-for="(card, index) in cards" :key="index">
                                <a :href="card.url" class="absolute inset-0 w-full h-full rounded-[36px] overflow-hidden shadow-2xl border-2 border-white/40 bg-slate-900 transition-all duration-700 ease-out cursor-pointer select-none block group"
                                    :style="getCardStyle(index)">
                                    
                                    <!-- Card Image Background -->
                                    <img :src="card.img" :alt="card.name" class="w-full h-full object-cover opacity-85 transition-transform duration-700 group-hover:scale-105">
                                    
                                    <!-- Card Content Overlay -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent p-7 flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="inline-flex items-center gap-1.5 bg-slate-900/85 backdrop-blur-md border border-pink-400/40 text-pink-300 text-[11px] font-black uppercase tracking-widest px-3.5 py-1.5 rounded-full shadow-lg">
                                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-pink-400"></i>
                                                Featured Design
                                            </span>
                                            <span class="text-xs font-black bg-white/20 backdrop-blur-md text-white px-3 py-1 rounded-full border border-white/20 shadow-md">
                                                Card <span x-text="((currentIndex + (cards.length - index - 1)) % cards.length) + 1"></span>/<span x-text="cards.length"></span>
                                            </span>
                                        </div>

                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-widest text-pink-300 block mb-1" x-text="card.orientation"></span>
                                            <h3 class="text-2xl sm:text-3xl font-black text-white leading-tight drop-shadow-md line-clamp-2" x-text="card.name"></h3>
                                            <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between text-xs font-extrabold text-slate-300">
                                                <span>Customizable Template</span>
                                                <span class="text-pink-300 group-hover:translate-x-1 transition-transform flex items-center gap-1">Customize <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </template>
                        </div>

                        <!-- Fan Navigation Controls -->
                        <div class="absolute bottom-0 right-4 flex items-center gap-2 z-50">
                            <button @click.stop="prev()" class="w-9 h-9 rounded-full bg-slate-900/90 hover:bg-pink-600 text-white flex items-center justify-center backdrop-blur-md border border-white/30 shadow-xl transition-all active:scale-95 cursor-pointer">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button @click.stop="next()" class="w-9 h-9 rounded-full bg-slate-900/90 hover:bg-pink-600 text-white flex items-center justify-center backdrop-blur-md border border-white/30 shadow-xl transition-all active:scale-95 cursor-pointer">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===== TEMPLATE GALLERY & FILTER TOOLBAR ===== --}}
    <section class="max-w-[1400px] mx-auto pt-12 pb-16 px-6 sm:px-10">

        {{-- Combined Filter Bar --}}
        <div class="bg-white border border-slate-200/90 rounded-[28px] p-4 sm:p-5 mb-10 shadow-xs flex flex-col lg:flex-row items-center justify-between gap-4 sm:gap-5 w-full">
            
            {{-- Category Filter Pills --}}
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                <button
                    @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600'"
                    class="filter-pill px-4 py-2 rounded-xl border text-xs font-black transition-all cursor-pointer">
                    All Templates ({{ $templates->count() }})
                </button>
                @foreach($categories as $cat)
                    @php $catCount = $templates->where('category_id', $cat->id)->count(); @endphp
                    @if($catCount > 0)
                    <button
                        @click="activeCategory = {{ $cat->id }}"
                        :class="activeCategory === {{ $cat->id }} ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600'"
                        class="filter-pill px-4 py-2 rounded-xl border text-xs font-black transition-all cursor-pointer flex items-center gap-1.5">
                        <span>{{ $cat->name }}</span>
                        <span class="bg-white/30 text-current text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $catCount }}</span>
                    </button>
                    @endif
                @endforeach
            </div>

            {{-- Live Search Filter Input --}}
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200/90 rounded-2xl px-3.5 py-2 w-full lg:w-72 shadow-2xs">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input type="text" x-model="searchQuery" placeholder="Search templates..." class="w-full text-xs text-slate-900 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0 font-semibold">
            </div>
        </div>

        {{-- Template Cards Grid (4 Cards Per Row) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            @forelse($templates as $index => $tpl)
            <a href="{{ route('flow-pc.customize', $tpl->slug) }}"
                x-show="(activeCategory === 'all' || activeCategory === {{ $tpl->category_id }}) && ('{{ strtolower(addslashes($tpl->name)) }}'.includes(searchQuery.toLowerCase()) || searchQuery === '')"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="template-card-premium group relative bg-slate-900 border border-slate-200/90 rounded-[28px] overflow-hidden shadow-xs hover:shadow-2xl hover:border-brand-400 cursor-pointer block h-[420px]"
                style="animation: tplFade 0.45s cubic-bezier(0.16,1,0.3,1) {{ min($index * 0.04, 0.4) }}s both;">

                {{-- Full Width & Height Background Image --}}
                <img src="{{ $tpl->frame_image_thumbnail ?? 'https://placehold.co/400x550/1e1b4b/ec4899?text=' . urlencode($tpl->name) }}"
                    class="tpl-img w-full h-full object-cover"
                    alt="{{ $tpl->name }}"
                    loading="lazy">

                {{-- Top Right Orientation Badge --}}
                <div class="absolute top-4 right-4 z-20">
                    <span class="inline-flex items-center gap-1.5 bg-slate-900/80 backdrop-blur-md border border-white/20 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-full shadow-md">
                        @if($tpl->pdf_orientation == 'portrait')
                            <i data-lucide="rectangle-vertical" class="w-3.5 h-3.5 text-pink-400"></i> Portrait
                        @else
                            <i data-lucide="rectangle-horizontal" class="w-3.5 h-3.5 text-pink-400"></i> Landscape
                        @endif
                    </span>
                </div>

                {{-- Absolute Bottom Center Overlay Container --}}
                <div class="absolute inset-x-0 bottom-0 p-6 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent flex flex-col items-center justify-end text-center z-10 pt-20">
                    {{-- Template Title (Center Aligned) --}}
                    <h3 class="text-base sm:text-lg font-black text-white leading-snug line-clamp-2 drop-shadow-md group-hover:text-pink-300 transition-colors mb-3">
                        {{ $tpl->name }}
                    </h3>

                    {{-- Customize Action Button (Center Aligned) --}}
                    <span class="w-full bg-brand-600 group-hover:bg-pink-600 text-white text-xs font-black py-3 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        Customize Design
                    </span>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-20 bg-slate-50/80 rounded-[32px] border border-slate-200/90">
                <div class="w-16 h-16 bg-white shadow-xs rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h4 class="text-xl font-black text-slate-900">No templates found</h4>
                <p class="text-sm text-slate-500 font-medium mt-2">Please select a different category or search term.</p>
                <a href="{{ route('flow-pc.index') }}" class="inline-flex items-center gap-2 mt-6 text-xs font-black text-brand-600 hover:text-brand-700 bg-white border border-slate-200/90 px-4 py-2.5 rounded-xl shadow-2xs transition-all">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to product catalog
                </a>
            </div>
            @endforelse
        </div>
    </section>

    {{-- ===== BOTTOM TRUST BAR ===== --}}
    <section class="max-w-[1400px] mx-auto px-6 sm:px-10">
        <div class="bg-white border border-slate-200/90 rounded-[28px] p-8 shadow-xs">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-brand-50 text-brand-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="palette" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm">Fully Customizable Studio</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Edit text, photos, layout vectors, and filters in real-time.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="eye" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm">Live 3D & Print Preview</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Inspect your exact print preview before confirming your order.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm">Local Branch Quality</h4>
                        <p class="text-xs text-slate-500 font-medium mt-1">Printed on professional equipment at your chosen Qrinto store.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('templateFanSlider', () => ({
            currentIndex: 0,
            timer: null,
            cards: <?php
                $featuredCards = [];
                foreach($templates->take(5) as $t) {
                    $featuredCards[] = [
                        'name' => $t->name,
                        'img' => $t->frame_image_thumbnail ?? 'https://placehold.co/400x550/1e1b4b/ec4899?text=' . urlencode($t->name),
                        'orientation' => ucfirst($t->pdf_orientation ?? 'Portrait'),
                        'url' => route('flow-pc.customize', $t->slug)
                    ];
                }
                if (empty($featuredCards)) {
                    $featuredCards[] = [
                        'name' => $type->name . ' Studio Canvas',
                        'img' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80',
                        'orientation' => 'Portrait',
                        'url' => '#'
                    ];
                }
                echo json_encode($featuredCards);
            ?>,

            init() {
                this.startAutoplay();
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },

            next() {
                this.currentIndex = (this.currentIndex + 1) % this.cards.length;
                this.resetAutoplay();
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },

            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.cards.length) % this.cards.length;
                this.resetAutoplay();
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },

            startAutoplay() {
                this.timer = setInterval(() => {
                    this.next();
                }, 4000);
            },

            resetAutoplay() {
                if (this.timer) clearInterval(this.timer);
                this.startAutoplay();
            },

            getCardStyle(index) {
                const total = this.cards.length;
                let diff = (index - this.currentIndex + total) % total;

                const angles = [0, -10, -20, -30, -40];
                const translateX = [0, -24, -48, -72, -96];
                const translateY = [0, 6, 15, 27, 40];

                const rotateDeg = angles[diff] || 0;
                const tx = translateX[diff] || 0;
                const ty = translateY[diff] || 0;

                const zIndex = 30 - diff;
                const opacity = diff === 0 ? 1 : (1 - diff * 0.12);

                return `transform: translate(${tx}px, ${ty}px) rotate(${rotateDeg}deg); transform-origin: 75% 100%; z-index: ${zIndex}; opacity: ${opacity}; transition: transform 0.7s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.5s ease;`;
            }
        }));
    });
</script>
@endpush