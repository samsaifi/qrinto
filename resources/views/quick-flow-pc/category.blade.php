@extends('layouts.quick-flow-pc')

@section('title', $type->name . ' — Choose Your Size')
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
    .size-card-premium {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .size-card-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.1), 0 0 0 1px rgba(236,72,153,0.15);
        border-color: var(--color-brand-500, #ec4899);
    }
    .size-card-premium:hover .size-arrow {
        background-color: var(--color-brand-600, #db2777);
        color: white;
        transform: translateX(4px);
    }
    .size-card-premium:hover .size-icon-box {
        transform: scale(1.08);
        background-color: var(--color-brand-100, #fce7f3);
    }
    .size-arrow {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .size-icon-box {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .size-icon-box svg {
        width: 100%;
        height: 100%;
        fill: currentColor !important;
    }
    .fade-up-cat {
        animation: fadeUpCat 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes fadeUpCat {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="w-full overflow-hidden pb-24">

    {{-- ===== HERO / PAGE HEADER (LUXURY DARK PURPLE & FAN SLIDER) ===== --}}
    <section class="hero-mesh-overlay w-full px-6 lg:px-12 pt-12 pb-20 relative overflow-hidden text-white shadow-xl"
        style="background: linear-gradient(135deg, #0d061c 0%, #1c0836 35%, #2a074a 70%, #0d061c 100%) !important;">
        <!-- Animated Ambient Gradient Blobs -->
        <div class="hero-orb-1"></div>
        <div class="hero-orb-2"></div>

        <!-- Giant Background Watermark Text "Category" (Bottom Right) -->
        <div class="absolute right-4 sm:right-10 bottom-2 sm:bottom-4 text-[140px] sm:text-[220px] lg:text-[300px] font-black text-white/[0.035] select-none pointer-events-none tracking-tighter leading-none z-0">
            Category
        </div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            {{-- Top Navigation & Store Indicator Row --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 fade-up-cat" style="animation-delay:0s">
                {{-- Breadcrumbs --}}
                <nav class="flex flex-wrap items-center gap-2 text-xs sm:text-sm font-semibold">
                    <a href="{{ route('flow-pc.index') }}" class="text-slate-300 hover:text-white transition-colors flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 px-3 py-1.5 rounded-full shadow-2xs">
                        <i data-lucide="home" class="w-3.5 h-3.5 text-pink-400"></i> Home
                    </a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
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

                {{-- Left Column: Content Copy --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-6">
                    <!-- Badge Tag -->
                    <div>
                        <span class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-md border border-pink-500/30 text-pink-300 text-[11px] font-black px-4 py-1.5 rounded-full mb-6 uppercase tracking-widest shadow-lg">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-pink-400"></i>
                            SELECT YOUR PREFERRED SIZE
                        </span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-4xl sm:text-5xl xl:text-6xl font-black text-white leading-[1.08] tracking-tight drop-shadow-md">
                        {{ $type->name }}<br>
                        <span class="bg-gradient-to-r from-pink-300 via-purple-300 to-pink-400 bg-clip-text text-transparent italic"
                            style="font-family: 'Playfair Display', serif;">Collection & Sizes.</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-base sm:text-lg text-slate-200 font-medium mt-5 leading-relaxed max-w-xl">
                        @if($type->title)
                            {{ $type->title }} &mdash;
                        @endif
                        Choose your preferred size and dimensions to get started with your custom studio design.
                    </p>

                    {{-- Quick Info Pills --}}
                    <div class="flex flex-wrap items-center gap-2.5 mt-8 fade-up-cat" style="animation-delay:0.25s">
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 text-slate-200 text-xs font-black px-3.5 py-1.5 rounded-full">
                            <i data-lucide="ruler" class="w-3.5 h-3.5 text-pink-400"></i>
                            {{ isset($subTypes) ? $subTypes->count() : 0 }} Sizes Available
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 text-slate-200 text-xs font-black px-3.5 py-1.5 rounded-full">
                            <i data-lucide="award" class="w-3.5 h-3.5 text-pink-400"></i>
                            Studio Print Quality
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-md border border-white/15 text-slate-200 text-xs font-black px-3.5 py-1.5 rounded-full">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-pink-400"></i>
                            Express Store Pickup
                        </span>
                    </div>

                    {{-- Trust Stats Bar --}}
                    <div class="mt-10 pt-8 border-t border-white/15 flex items-center gap-8 text-white">
                        <div>
                            <p class="text-3xl font-black bg-gradient-to-r from-pink-300 to-cyan-300 bg-clip-text text-transparent">100%</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Quality Guaranteed</p>
                        </div>
                        <div class="h-8 w-px bg-white/15"></div>
                        <div>
                            <p class="text-3xl font-black bg-gradient-to-r from-amber-300 to-pink-300 bg-clip-text text-transparent">Live 3D</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Design Studio</p>
                        </div>
                        <div class="h-8 w-px bg-white/15 hidden sm:block"></div>
                        <div class="hidden sm:block">
                            <p class="text-3xl font-black bg-gradient-to-r from-emerald-300 to-cyan-300 bg-clip-text text-transparent">Same-Day</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Local Pickup</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Creative Studio Size Selection Prompt Showcase --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-6 hidden lg:flex justify-end">
                    <div class="relative w-[440px] max-w-full">

                        <!-- Outer Glowing Glass Canvas Box -->
                        <div class="bg-slate-950/80 backdrop-blur-2xl rounded-[32px] border border-pink-500/30 p-7 shadow-2xl relative overflow-hidden group">
                            
                            <!-- Corner Crop Blueprint Indicators -->
                            <div class="absolute top-4 left-4 text-pink-400/40 text-[10px] font-mono font-bold select-none">┌ ── W: AUTO ── ┐</div>
                            <div class="absolute bottom-4 left-4 text-pink-400/40 text-[10px] font-mono font-bold select-none">└ ── H: AUTO ── ┘</div>

                            <!-- Top Header Tag -->
                            <div class="flex items-center justify-between mb-6">
                                <span class="inline-flex items-center gap-1.5 bg-pink-500/20 border border-pink-500/40 text-pink-300 text-[11px] font-black uppercase tracking-widest px-3.5 py-1 rounded-full shadow-md">
                                    <span class="w-2 h-2 rounded-full bg-pink-400 animate-ping"></span>
                                    STEP 2 OF 4: SIZE SELECTION
                                </span>
                                <span class="text-xs font-black text-slate-400 flex items-center gap-1">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 text-pink-400"></i> {{ isset($subTypes) ? $subTypes->count() : 0 }} Options
                                </span>
                            </div>

                            <!-- Studio Blueprint Graphic Overlay -->
                            <div class="relative bg-slate-900/90 rounded-2xl p-6 border border-white/10 my-4 text-center overflow-hidden">
                                <!-- Grid background pattern -->
                                <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(circle, #ec4899 1px, transparent 1px); background-size: 16px 16px;"></div>

                                <!-- Animated Icon Box -->
                                <div class="w-16 h-16 bg-gradient-to-tr from-pink-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-white shadow-lg shadow-pink-500/20 transform group-hover:scale-110 transition-transform duration-500">
                                    <i data-lucide="ruler" class="w-8 h-8"></i>
                                </div>

                                <h3 class="text-xl font-black text-white leading-tight">Please Select a Size</h3>
                                <p class="text-xs text-slate-300 font-medium mt-2 leading-relaxed max-w-xs mx-auto">
                                    Choose your desired dimension card from the selection list below to unlock custom design templates & studio canvas.
                                </p>

                                <!-- Animated Arrow Indicator -->
                                <div class="mt-5 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-xs font-black px-5 py-2.5 rounded-full shadow-lg animate-bounce cursor-pointer active:scale-95"
                                    onclick="document.getElementById('size-grid')?.scrollIntoView({behavior: 'smooth'})">
                                    <span>Scroll & Pick Size</span>
                                    <i data-lucide="arrow-down" class="w-4 h-4"></i>
                                </div>
                            </div>

                            <!-- Bottom Available Sizes Summary Chips -->
                            <div class="mt-4 pt-4 border-t border-white/15 flex items-center justify-between text-xs font-bold text-slate-300">
                                <span class="text-slate-400">Ready Studio Canvas:</span>
                                <span class="text-pink-300 flex items-center gap-1 font-black">
                                    <i data-lucide="check-circle2" class="w-4 h-4 text-emerald-400"></i> Standard & Custom Sizes
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===== SIZE SELECTION GRID (ULTRA-PREMIUM RICH CARD UI) ===== --}}
    <section id="size-grid" class="max-w-[1400px] mx-auto pt-16 pb-16 px-6 sm:px-10">
        
        {{-- Section Header & Filter Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10 pb-6 border-b border-slate-200/80">
            <div>
                <span class="inline-flex items-center gap-2 bg-pink-50 border border-pink-200/80 text-pink-700 text-[11px] font-black px-3.5 py-1.5 rounded-full mb-3 shadow-2xs">
                    <i data-lucide="ruler" class="w-3.5 h-3.5 text-pink-600"></i>
                    DIMENSION & SIZE CATALOG
                </span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                    Select a Size for <span class="bg-gradient-to-r from-pink-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">{{ $type->name }}</span>
                </h2>
                <p class="text-base text-slate-500 font-medium mt-2 max-w-xl">
                    Choose your preferred canvas dimensions to unlock custom studio templates & live 3D print preview.
                </p>
            </div>
            
            <a href="{{ route('flow-pc.index') }}" class="inline-flex items-center gap-2 text-xs font-black text-slate-700 hover:text-white hover:bg-slate-900 bg-white border border-slate-200/90 px-5 py-3 rounded-2xl transition-all duration-300 shadow-xs hover:shadow-md cursor-pointer shrink-0 self-start md:self-auto">
                <i data-lucide="arrow-left" class="w-4 h-4 text-pink-500"></i>
                <span>Back to Categories</span>
            </a>
        </div>

        @if(isset($subTypes) && $subTypes->isNotEmpty())
        {{-- Size Cards Grid (3 Cards Per Row on XL, 2 on MD) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 sm:gap-8">
            @foreach($subTypes as $index => $sub)
            <a href="{{ route('flow-pc.category', $sub->slug) }}"
               class="size-card-premium group relative bg-white border border-slate-200/90 rounded-[32px] p-7 flex flex-col justify-between shadow-xs hover:shadow-2xl hover:border-pink-400 transition-all duration-300 cursor-pointer block"
               style="animation: fadeUpCat 0.5s cubic-bezier(0.16,1,0.3,1) {{ $index * 0.06 }}s both;">

                <!-- Subtle Top Gradient Glow Accent -->
                <div class="absolute top-0 inset-x-8 h-1 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 rounded-b-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                <div>
                    {{-- Top Card Header: Icon & Dimension Tag --}}
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div class="size-icon-box w-16 h-16 bg-gradient-to-tr from-pink-500 via-purple-600 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-pink-500/20 group-hover:scale-110 transition-transform duration-300 p-3">
                            @if($type->icon_svg)
                                {!! $type->icon_svg !!}
                            @else
                                <i data-lucide="maximize-2" class="w-7 h-7 text-white"></i>
                            @endif
                        </div>

                        @if($sub->width && $sub->height)
                        <span class="inline-flex items-center gap-1.5 bg-slate-950 text-pink-300 border border-pink-500/30 text-[11px] font-black uppercase tracking-widest px-3.5 py-1.5 rounded-full shadow-xs">
                            <i data-lucide="ruler" class="w-3.5 h-3.5 text-pink-400"></i>
                            {{ $sub->width }}&times;{{ $sub->height }} {{ $sub->unit }}
                        </span>
                        @endif
                    </div>

                    {{-- Size Name & Description --}}
                    <h3 class="text-xl font-black text-slate-900 group-hover:text-pink-600 transition-colors leading-tight">
                        {{ $sub->name }}
                    </h3>
                    
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1.5">
                        {{ $sub->title ?: 'Custom Studio Dimension' }}
                    </p>

                    <!-- Feature Spec Tags -->
                    <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-slate-100">
                        <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">
                            <i data-lucide="check-circle2" class="w-3.5 h-3.5 text-emerald-500"></i> High Resolution
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-pink-500"></i> Studio Templates
                        </span>
                    </div>
                </div>

                {{-- Bottom Card Footer: Price & CTA --}}
                <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block">Starting From</span>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            @if($sub->price)
                                <span class="text-2xl font-black bg-gradient-to-r from-slate-900 via-purple-950 to-pink-600 bg-clip-text text-transparent">
                                    {{ \App\Services\CurrencyService::format($sub->price) }}
                                </span>
                                @if($sub->old_price && $sub->old_price > $sub->price)
                                    <span class="text-xs text-slate-400 line-through font-bold">
                                        {{ \App\Services\CurrencyService::format($sub->old_price) }}
                                    </span>
                                @endif
                            @else
                                <span class="text-sm font-black text-brand-600">Studio Pricing</span>
                            @endif
                        </div>
                    </div>

                    {{-- CTA Button --}}
                    <div class="size-arrow bg-gradient-to-r from-pink-500 via-purple-600 to-indigo-600 group-hover:from-pink-600 group-hover:to-indigo-700 text-white font-black text-xs px-5 py-3 rounded-2xl shadow-md group-hover:shadow-pink-500/25 flex items-center gap-2 transition-all duration-300 transform group-hover:scale-105">
                        <span>Select Size</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-20 bg-slate-50/80 rounded-[32px] border border-slate-200/90">
            <div class="w-16 h-16 bg-white shadow-xs rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
            </div>
            <h4 class="text-xl font-black text-slate-900">No sizes found</h4>
            <p class="text-sm text-slate-500 font-medium mt-2">No size subcategories configured for {{ $type->name }}.</p>
            <a href="{{ route('flow-pc.index') }}" class="inline-flex items-center gap-2 mt-6 text-xs font-black text-brand-600 hover:text-brand-700 bg-white border border-slate-200/90 px-4 py-2.5 rounded-xl shadow-2xs transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to product catalog
            </a>
        </div>
        @endif
    </section>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('subTypesFanSlider', () => ({
            currentIndex: 0,
            timer: null,
            cards: <?php
                $featuredSizes = [];
                if (isset($subTypes) && $subTypes->isNotEmpty()) {
                    foreach($subTypes->take(5) as $s) {
                        $featuredSizes[] = [
                            'name' => $s->name,
                            'dimensions' => ($s->width && $s->height) ? "{$s->width}×{$s->height} {$s->unit}" : "Custom Size",
                            'price' => $s->price ? \App\Services\CurrencyService::format($s->price) : 'Best Rate',
                            'url' => route('flow-pc.category', $s->slug),
                            'img' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80'
                        ];
                    }
                }
                if (empty($featuredSizes)) {
                    $featuredSizes[] = [
                        'name' => $type->name . ' Standard Size',
                        'dimensions' => 'Studio Grade',
                        'price' => 'Express',
                        'url' => '#',
                        'img' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80'
                    ];
                }
                echo json_encode($featuredSizes);
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