@extends('layouts.quick-flow-pc')

@section('title', 'Find a Store Location')
@section('header_title', 'Find Store')

@push('styles')
<!-- Swiper CSS CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

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
    .hero-orb-3 {
        position: absolute; top: 30%; left: 40%; width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(244, 63, 94, 0.18) 0%, transparent 70%);
        border-radius: 50%; filter: blur(70px); pointer-events: none; animation: orbPulse 6s infinite alternate ease-in-out;
    }
    @keyframes orbPulse {
        0% { transform: scale(1) translate(0, 0); }
        50% { transform: scale(1.1) translate(20px, -15px); }
        100% { transform: scale(0.95) translate(-15px, 20px); }
    }
    .store-card-premium {
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .store-card-premium:hover {
        transform: translateY(-6px);
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.12), 0 0 0 2px rgba(236,72,153,0.2);
    }
    .category-card-premium {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .category-card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
    }
    .city-global-swiper {
        height: 460px !important;
        width: 310px !important;
        max-width: 100%;
        overflow: visible !important;
    }
    .city-global-swiper .swiper-wrapper {
        height: 100% !important;
        overflow: visible !important;
    }
    .city-global-swiper .swiper-slide {
        height: 100% !important;
        width: 310px !important;
        border-radius: 32px !important;
        overflow: hidden !important;
        transform-origin: bottom left !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 0 2px rgba(255, 255, 255, 0.25);
    }
    .search-input-hero-wrapper {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .search-input-hero-wrapper:focus-within {
        box-shadow: 0 0 0 4px rgba(236,72,153,0.25), 0 25px 50px -12px rgba(0,0,0,0.3);
        border-color: #ec4899;
    }
</style>
@endpush

@section('content')
<div x-data="storeAutocomplete()" class="w-full overflow-hidden">

    {{-- ===== SECTION 1: HERO SECTION (WITH COLORFUL GRADIENT, ANIMATED SVG & SEARCH) ===== --}}
    <section class="hero-mesh-overlay w-full px-6 lg:px-12 pt-14 pb-20 relative overflow-hidden text-white shadow-xl"
        style="background: linear-gradient(135deg, #0d061c 0%, #1c0836 35%, #2a074a 70%, #0d061c 100%) !important;">
        <!-- Animated Ambient Gradient Blobs -->
        <div class="hero-orb-1"></div>
        <div class="hero-orb-2"></div>
        <div class="hero-orb-3"></div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            <div class="grid grid-cols-12 gap-10 lg:gap-14 items-center">

                {{-- Left Column: Search & Text Copy --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-6">
                    <!-- Badge Tag -->
                    <div>
                        <span class="inline-flex items-center gap-2 bg-white/5 backdrop-blur-md border border-pink-500/30 text-pink-300 text-[11px] font-black px-4 py-1.5 rounded-full mb-6 uppercase tracking-widest shadow-lg">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-pink-400"></i>
                            LOCAL BRANCH LOCATOR ENGINE
                        </span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-4xl sm:text-5xl xl:text-6xl font-black text-white leading-[1.08] tracking-tight drop-shadow-md">
                        Shop local,<br>
                        <span class="bg-gradient-to-r from-pink-300 via-purple-300 to-pink-400 bg-clip-text text-transparent italic"
                            style="font-family: 'Playfair Display', serif;">print beautifully.</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-base sm:text-lg text-slate-200 font-medium mt-5 leading-relaxed max-w-xl">
                        Find the nearest Qrinto print studio branch and experience museum-quality photo prints, express same-day pickup, and artisan craftsmanship.
                    </p>

                    {{-- Hero Search Bar --}}
                    <div class="mt-8">
                        <div class="search-input-hero-wrapper bg-slate-950/80 backdrop-blur-xl rounded-2xl border border-white/20 p-2 flex flex-col sm:flex-row items-center gap-3 shadow-2xl max-w-xl">
                            <div class="flex items-center flex-1 gap-3 pl-4 py-2 w-full">
                                <i data-lucide="search" class="w-5 h-5 text-pink-400 shrink-0"></i>
                                <input type="text" placeholder="Search by city, zip code, or store name..."
                                    x-model="query" @input.debounce.300ms="fetchStores" autocomplete="off"
                                    @keydown.enter.prevent="fetchStores(); $nextTick(() => { document.getElementById('store-results')?.scrollIntoView({behavior:'smooth', block:'start'}) })"
                                    class="w-full text-sm sm:text-base text-white placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0">
                            </div>
                            <button @click="fetchStores(); $nextTick(() => { document.getElementById('store-results')?.scrollIntoView({behavior:'smooth', block:'start'}) })"
                                class="w-full sm:w-auto bg-gradient-to-r from-pink-500 via-purple-600 to-indigo-600 hover:from-pink-600 hover:to-indigo-700 text-white font-black px-7 py-3.5 rounded-xl transition-all duration-300 active:scale-95 flex items-center justify-center gap-2 shadow-lg shrink-0 cursor-pointer">
                                <i data-lucide="search" class="w-4 h-4"></i>
                                <span>Find Stores</span>
                            </button>
                        </div>
                    </div>

                    {{-- Popular Quick Search Tags --}}
                    <div class="mt-5 flex items-center flex-wrap gap-2 text-xs font-bold text-slate-300">
                        <span class="text-slate-400 font-medium">Popular:</span>
                        <button @click="query = 'New York'; fetchStores()" class="bg-white/10 hover:bg-pink-600 text-white border border-white/15 px-3.5 py-1.5 rounded-full transition-all cursor-pointer">New York</button>
                        <button @click="query = 'Los Angeles'; fetchStores()" class="bg-white/10 hover:bg-pink-600 text-white border border-white/15 px-3.5 py-1.5 rounded-full transition-all cursor-pointer">Los Angeles</button>
                        <button @click="query = 'Chicago'; fetchStores()" class="bg-white/10 hover:bg-pink-600 text-white border border-white/15 px-3.5 py-1.5 rounded-full transition-all cursor-pointer">Chicago</button>
                        <button @click="query = 'Houston'; fetchStores()" class="bg-white/10 hover:bg-pink-600 text-white border border-white/15 px-3.5 py-1.5 rounded-full transition-all cursor-pointer">Houston</button>
                    </div>

                    {{-- Trust Stats Bar --}}
                    <div class="mt-10 pt-8 border-t border-white/15 flex items-center gap-8 text-white">
                        <div>
                            <p class="text-3xl font-black bg-gradient-to-r from-pink-300 to-cyan-300 bg-clip-text text-transparent">500+</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Store Branches</p>
                        </div>
                        <div class="h-8 w-px bg-white/15"></div>
                        <div>
                            <p class="text-3xl font-black bg-gradient-to-r from-amber-300 to-pink-300 bg-clip-text text-transparent">4.9★</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Verified Rating</p>
                        </div>
                        <div class="h-8 w-px bg-white/15 hidden sm:block"></div>
                        <div class="hidden sm:block">
                            <p class="text-3xl font-black bg-gradient-to-r from-emerald-300 to-cyan-300 bg-clip-text text-transparent">Same-Day</p>
                            <p class="text-[11px] font-black text-slate-400 mt-1 uppercase tracking-wider">Local Pickup</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Authentic Playing Card Hand Fan Showcase --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-6 hidden lg:block">
                    <div x-data="cardFanSlider()" class="relative w-[480px] h-[480px] max-w-full ml-auto flex items-center justify-center pt-8 overflow-visible">
                        
                        <!-- Cards Fan Deck Container -->
                        <div class="relative w-[300px] h-[420px] flex items-center justify-center overflow-visible">
                            
                            <template x-for="(card, index) in cards" :key="index">
                                <div class="absolute inset-0 w-full h-full rounded-[36px] overflow-hidden shadow-2xl border-2 border-white/40 bg-slate-900 transition-all duration-700 ease-out cursor-pointer select-none"
                                    :style="getCardStyle(index)"
                                    @click="next()">
                                    
                                    <!-- Card Image Background -->
                                    <img :src="card.img" :alt="card.title" class="w-full h-full object-cover opacity-80 transition-transform duration-700 hover:scale-105">
                                    
                                    <!-- Card Content Overlay -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent p-7 flex flex-col justify-between">
                                        <div class="flex items-center justify-between">
                                            <span class="inline-flex items-center gap-1.5 bg-slate-900/85 backdrop-blur-md border text-[11px] font-black uppercase tracking-widest px-3.5 py-1.5 rounded-full shadow-lg"
                                                :class="card.color === 'pink' ? 'border-pink-400/40 text-pink-300' : (card.color === 'purple' ? 'border-purple-400/40 text-purple-300' : (card.color === 'cyan' ? 'border-cyan-400/40 text-cyan-300' : (card.color === 'amber' ? 'border-amber-400/40 text-amber-300' : 'border-emerald-400/40 text-emerald-300')))">
                                                <i data-lucide="globe" class="w-3.5 h-3.5 animate-spin"></i>
                                                <span x-text="card.badge"></span>
                                            </span>
                                            <span class="text-xs font-black bg-white/20 backdrop-blur-md text-white px-3 py-1 rounded-full border border-white/20 shadow-md">
                                                Card <span x-text="((currentIndex + (4 - index)) % 5) + 1"></span>/5
                                            </span>
                                        </div>

                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-widest block mb-1"
                                                :class="card.color === 'pink' ? 'text-pink-300' : (card.color === 'purple' ? 'text-purple-300' : (card.color === 'cyan' ? 'text-cyan-300' : (card.color === 'amber' ? 'text-amber-300' : 'text-emerald-300')))"
                                                x-text="card.city"></span>
                                            <h3 class="text-2xl sm:text-3xl font-black text-white leading-tight drop-shadow-md" x-text="card.title"></h3>
                                            <p class="text-xs text-slate-200 mt-2 font-medium leading-relaxed" x-text="card.subtitle"></p>
                                            <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between text-xs font-extrabold text-slate-300">
                                                <span x-text="card.features"></span>
                                                <span class="text-emerald-400 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
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

    {{-- ===== SECTION 2: FIND A STORE RESULTS GRID ===== --}}
    <section id="store-results" class="w-full bg-white py-16 px-6 sm:px-10 border-b border-slate-100/80 relative overflow-hidden">
        <!-- Giant Background Watermark Text "Step 2" (Bottom Right) -->
        <div class="absolute right-4 sm:right-10 bottom-2 sm:bottom-4 text-[140px] sm:text-[220px] lg:text-[300px] font-black text-slate-900/[0.035] select-none pointer-events-none tracking-tighter leading-none z-0">
            Step 2
        </div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            <!-- Combined Single-Row Header Bar (Clean Borderless Layout) -->
            <div class="mb-10 flex flex-col lg:flex-row items-center justify-between gap-5 sm:gap-6 w-full">
                
                {{-- Element 1 (Left): Title & Badge --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <i data-lucide="store" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-brand-600 block">Network Directory</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight whitespace-nowrap">
                            Select a <span class="bg-gradient-to-r from-brand-600 via-purple-600 to-pink-600 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">Store Branch</span>
                        </h2>
                    </div>
                </div>

                {{-- Element 2 (Center): Search Input & Near Me Filter Toolbar (Expanded Full Width) --}}
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200/90 rounded-2xl p-1.5 flex-1 w-full shadow-2xs">
                    <div class="flex items-center gap-2 flex-1 pl-3">
                        <i data-lucide="search" class="w-4.5 h-4.5 text-slate-400 shrink-0"></i>
                        <input type="text" placeholder="Filter by city, zip, or name..."
                            x-model="query" @input.debounce.300ms="fetchStores" autocomplete="off"
                            class="w-full text-xs sm:text-sm text-slate-900 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0 font-medium">
                        <div x-show="isLoading" class="shrink-0" x-cloak>
                            <div class="w-4 h-4 border-2 border-slate-300 border-t-brand-600 rounded-full animate-spin"></div>
                        </div>
                    </div>
                    <button @click="detectLocation()" class="flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-slate-700 hover:text-brand-600 bg-white hover:bg-brand-50 border border-slate-200/90 px-3.5 py-2 rounded-xl transition-all shadow-2xs cursor-pointer active:scale-95 shrink-0">
                        <i data-lucide="crosshair" class="w-3.5 h-3.5 text-brand-600"></i>
                        <span>Near Me</span>
                    </button>
                </div>

                {{-- Element 3 (Right): Currently Active Store Status Pill --}}
                <div class="shrink-0">
                    @if(!session()->has('active_store_id'))
                        <div class="flex items-center gap-3 bg-amber-50 border border-amber-200/90 rounded-2xl px-4 py-2.5">
                            <i data-lucide="alert-circle" class="w-4.5 h-4.5 text-amber-600 shrink-0"></i>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 block leading-none">Branch Notice</span>
                                <span class="text-xs font-black text-amber-900 leading-tight">No Store Selected</span>
                            </div>
                        </div>
                    @else
                        @php $selectedStore = \App\Models\Store::find(session('active_store_id')); @endphp
                        @if($selectedStore)
                            <div class="flex items-center gap-3 bg-emerald-50/90 border border-emerald-200/90 rounded-2xl px-4 py-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                    <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[9px] font-black uppercase tracking-wider text-emerald-700 block leading-none">Currently Active Branch</span>
                                    <h4 class="text-xs font-black text-slate-900 truncate max-w-[140px] sm:max-w-[180px] mt-0.5">{{ $selectedStore->store_name }}</h4>
                                </div>
                                <span class="inline-flex items-center gap-1 text-[10px] font-black text-emerald-700 bg-white border border-emerald-200/80 px-2.5 py-1 rounded-full shrink-0 ml-1">
                                    Active <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>

            </div>

            {{-- Nearby Detected Stores Grid --}}
            <div x-show="geolocationChecked && nearbyStores.length > 0 && !hasSearched" x-cloak class="mb-12">
                <h3 class="text-xl font-black text-slate-900 mb-6 flex items-center gap-2">
                    <i data-lucide="navigation" class="w-5 h-5 text-brand-600"></i>
                    Branches Nearest To Your Location
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="store in nearbyStores" :key="store.id">
                        <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                            @csrf
                            <input type="hidden" name="store_id" :value="store.id">
                            <button type="submit" class="store-card-premium w-full h-full text-left bg-white border border-slate-200/90 rounded-[28px] p-6 group cursor-pointer flex flex-col justify-between shadow-xs">
                                <div>
                                    <!-- Store Logo & Badge Row -->
                                    <div class="flex items-start justify-between gap-4 mb-5">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200/90 shadow-sm p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 group-hover:border-brand-300 transition-all">
                                            <template x-if="store.logo">
                                                <img 
                                                    :src="'{{ asset('storage') }}/' + store.logo"
                                                    :alt="store.store_name"
                                                    class="w-full h-full object-cover rounded-xl"
                                                >
                                            </template>
                                            <template x-if="!store.logo">
                                                <div class="w-full h-full rounded-xl bg-gradient-to-br from-brand-600 via-purple-600 to-pink-600 text-white flex items-center justify-center text-xl font-black">
                                                    <span x-text="store.store_name ? store.store_name.charAt(0) : 'Q'"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Open Now
                                        </span>
                                    </div>

                                    <!-- Store Title & Rating -->
                                    <h4 class="font-black text-slate-900 text-lg group-hover:text-brand-600 transition-colors line-clamp-1" x-text="store.store_name"></h4>
                                    
                                    <div class="flex items-center gap-1 mt-2 text-xs font-bold text-slate-600">
                                        <div class="flex items-center gap-0.5 text-amber-400">
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                        </div>
                                        <span class="ml-1 text-slate-800">4.9</span>
                                        <span class="text-slate-400 font-normal">(Verified Store)</span>
                                    </div>

                                    <!-- Location Info -->
                                    <p class="text-xs text-slate-500 font-medium mt-3 flex items-start gap-1.5">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                                        <span x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                                    </p>
                                </div>

                                <!-- Card Action Footer -->
                                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold">
                                    <span class="text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">Same-Day Pickup</span>
                                    <span class="text-brand-600 group-hover:text-brand-700 flex items-center gap-1">
                                        Select Store <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                    </span>
                                </div>
                            </button>
                        </form>
                    </template>
                </div>
            </div>

            {{-- Geolocation Loading State --}}
            <div x-show="nearbyLoading" x-cloak class="mb-10 text-center py-12 bg-slate-50 rounded-3xl border border-slate-200/80">
                <div class="w-10 h-10 border-3 border-slate-200 border-t-brand-600 rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-sm font-bold text-slate-700">Detecting your nearest Qrinto branch location...</p>
            </div>

            {{-- Search Results Grid --}}
            <div x-show="hasSearched || stores.length > 0" x-cloak>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="list" class="w-5 h-5 text-brand-600"></i>
                        Available Stores
                        <span x-show="stores.length > 0" class="text-xs font-bold text-slate-400" x-text="`(${stores.length} found)`"></span>
                    </h3>
                </div>

                {{-- Empty Search Results State --}}
                <div x-show="!isLoading && stores.length === 0 && query.length > 0" class="text-center py-20 bg-slate-50 rounded-3xl border border-slate-200/80">
                    <div class="w-16 h-16 bg-slate-200/80 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <i data-lucide="map-pin-off" class="w-8 h-8"></i>
                    </div>
                    <h4 class="text-xl font-black text-slate-900">No stores found</h4>
                    <p class="text-sm text-slate-500 font-medium mt-2">No matching branches for "<span x-text="query" class="font-bold text-slate-800"></span>". Try searching by another city or zip code.</p>
                </div>

                {{-- Stores Grid --}}
                <div x-show="stores.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="store in stores" :key="store.id">
                        <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                            @csrf
                            <input type="hidden" name="store_id" :value="store.id">
                            <button type="submit" class="store-card-premium w-full h-full text-left bg-white border border-slate-200/90 rounded-[28px] p-6 group cursor-pointer flex flex-col justify-between shadow-xs">
                                <div>
                                    <!-- Store Logo & Badge Row -->
                                    <div class="flex items-start justify-between gap-4 mb-5">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200/90 shadow-sm p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 group-hover:border-brand-300 transition-all">
                                            <template x-if="store.logo">
                                                <img 
                                                    :src="'{{ asset('storage') }}/' + store.logo"
                                                    :alt="store.store_name"
                                                    class="w-full h-full object-cover rounded-xl"
                                                >
                                            </template>
                                            <template x-if="!store.logo">
                                                <div class="w-full h-full rounded-xl bg-gradient-to-br from-brand-600 via-purple-600 to-pink-600 text-white flex items-center justify-center text-xl font-black">
                                                    <span x-text="store.store_name ? store.store_name.charAt(0) : 'Q'"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Open Now
                                        </span>
                                    </div>

                                    <!-- Store Title & Rating -->
                                    <h4 class="font-black text-slate-900 text-lg group-hover:text-brand-600 transition-colors line-clamp-1" x-text="store.store_name"></h4>
                                    
                                    <div class="flex items-center gap-1 mt-2 text-xs font-bold text-slate-600">
                                        <div class="flex items-center gap-0.5 text-amber-400">
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                                        </div>
                                        <span class="ml-1 text-slate-800">4.9</span>
                                        <span class="text-slate-400 font-normal">(Verified Store)</span>
                                    </div>

                                    <!-- Location Info -->
                                    <p class="text-xs text-slate-500 font-medium mt-3 flex items-start gap-1.5">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                                        <span x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                                    </p>
                                    <p class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1.5" x-show="store.phone">
                                        <i data-lucide="phone" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                        <span x-text="store.phone"></span>
                                    </p>
                                </div>

                                <!-- Card Action Footer -->
                                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold">
                                    <span class="text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">Express Service</span>
                                    <span class="text-brand-600 group-hover:text-brand-700 flex items-center gap-1">
                                        Select Store <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                    </span>
                                </div>
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 3: COUNTRY-WISE STORE DIRECTORY ===== --}}
    @php
        $allStoresByCountry = \App\Models\Store::active()->get()->groupBy(function($store) {
            return trim($store->country ?: 'Global Network');
        });

        $countryFlags = [
            'India' => '🇮🇳',
            'IN' => '🇮🇳',
            'United States' => '🇺🇸',
            'USA' => '🇺🇸',
            'US' => '🇺🇸',
            'Canada' => '🇨🇦',
            'CA' => '🇨🇦',
            'United Kingdom' => '🇬🇧',
            'UK' => '🇬🇧',
            'Australia' => '🇦🇺',
            'AU' => '🇦🇺',
            'Germany' => '🇩🇪',
            'France' => '🇫🇷',
            'Japan' => '🇯🇵',
            'UAE' => '🇦🇪',
        ];
    @endphp

    @if($allStoresByCountry->count() > 0)
    <section id="country-stores" class="w-full bg-slate-50/60 py-16 px-6 sm:px-10 border-b border-slate-100/80 relative overflow-hidden">
        <!-- Giant Background Watermark Text "Global" -->
        <div class="absolute right-4 sm:right-10 bottom-2 sm:bottom-4 text-[140px] sm:text-[220px] lg:text-[280px] font-black text-slate-900/[0.025] select-none pointer-events-none tracking-tighter leading-none z-0">
            Global
        </div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                <div>
                    <span class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 text-xs font-black px-4 py-2 rounded-full mb-3 uppercase tracking-widest border border-indigo-100">
                        <i data-lucide="globe-2" class="w-4 h-4 text-indigo-600"></i> Country Directory
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                        Explore Stores <span class="bg-gradient-to-r from-brand-600 via-purple-600 to-pink-600 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">by Country</span>
                    </h2>
                    <p class="text-sm text-slate-500 font-medium mt-1">Browse active Qrinto branches grouped by region and country.</p>
                </div>
                <div class="shrink-0">
                    <span class="text-xs font-black text-slate-500 bg-white border border-slate-200/90 px-4 py-2 rounded-2xl shadow-2xs">
                        {{ $allStoresByCountry->count() }} {{ Str::plural('Country', $allStoresByCountry->count()) }} Available
                    </span>
                </div>
            </div>

            <!-- Country Rows -->
            <div class="space-y-10">
                @foreach($allStoresByCountry as $countryName => $cStores)
                    <div class="bg-white border border-slate-200/90 rounded-[32px] p-6 sm:p-8 shadow-xs">
                        <!-- Country Header Row -->
                        <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $countryFlags[$countryName] ?? '🌍' }}</span>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ $countryName }}</h3>
                                    <span class="text-xs text-slate-400 font-bold">{{ $cStores->count() }} {{ Str::plural('Branch', $cStores->count()) }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-brand-600 bg-brand-50 px-3.5 py-1.5 rounded-full border border-brand-100">
                                {{ $cStores->first()->currency ?? 'USD' }} Region
                            </span>
                        </div>

                        <!-- Stores Grid for this Country -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($cStores as $cStore)
                                <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                                    @csrf
                                    <input type="hidden" name="store_id" value="{{ $cStore->id }}">
                                    <button type="submit" class="store-card-premium w-full h-full text-left bg-slate-50/70 hover:bg-white border border-slate-200/80 hover:border-brand-300 rounded-[24px] p-5 group cursor-pointer flex flex-col justify-between transition-all shadow-2xs hover:shadow-md">
                                        <div>
                                            <!-- Store Logo & Badge Row -->
                                            <div class="flex items-start justify-between gap-4 mb-4">
                                                <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200/90 shadow-2xs p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-all">
                                                    @if($cStore->logo)
                                                        <img 
                                                            src="{{ asset('storage/' . ltrim($cStore->logo, '/')) }}"
                                                            alt="{{ $cStore->store_name }}"
                                                            class="w-full h-full object-cover rounded-xl"
                                                        >
                                                    @else
                                                        <div class="w-full h-full rounded-xl bg-gradient-to-br from-brand-600 via-purple-600 to-pink-600 text-white flex items-center justify-center text-lg font-black">
                                                            <span>{{ Str::upper(substr($cStore->store_name, 0, 1)) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-full">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Open
                                                </span>
                                            </div>

                                            <!-- Store Title & Rating -->
                                            <h4 class="font-black text-slate-900 text-base group-hover:text-brand-600 transition-colors line-clamp-1">{{ $cStore->store_name }}</h4>
                                            
                                            <div class="flex items-center gap-1 mt-1.5 text-xs font-bold text-slate-600">
                                                <div class="flex items-center gap-0.5 text-amber-400">
                                                    @for($i = 0; $i < 5; $i++)
                                                        <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                    @endfor
                                                </div>
                                                <span class="ml-1 text-slate-800 text-[11px]">4.9</span>
                                                <span class="text-slate-400 font-normal text-[10px]">(Verified)</span>
                                            </div>

                                            <!-- Location Info -->
                                            <p class="text-xs text-slate-500 font-medium mt-2.5 flex items-start gap-1.5">
                                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5"></i>
                                                <span>{{ implode(', ', array_filter([$cStore->city, $cStore->state, $cStore->zip_code])) }}</span>
                                            </p>
                                            @if($cStore->phone)
                                                <p class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                    <span>{{ $cStore->phone }}</span>
                                                </p>
                                            @endif
                                        </div>

                                        <!-- Card Action Footer -->
                                        <div class="mt-5 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-extrabold">
                                            <span class="text-slate-500 bg-white border border-slate-200/80 px-2.5 py-0.5 rounded-md text-[11px]">Pickup</span>
                                            <span class="text-brand-600 group-hover:text-brand-700 flex items-center gap-1 text-[11px]">
                                                Select <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                                            </span>
                                        </div>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif







</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cardFanSlider', () => ({
            currentIndex: 0,
            timer: null,
            cards: [
                {
                    city: 'New York, USA',
                    title: 'Manhattan Print Studio',
                    badge: '50+ US Labs',
                    subtitle: 'Flagship print lab with same-day express pickup',
                    features: 'Noritsu HD • Archival',
                    img: 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?auto=format&fit=crop&w=1000&q=80',
                    color: 'pink'
                },
                {
                    city: 'Tokyo, Japan',
                    title: 'Ginza Precision Hub',
                    badge: '35+ Asia Hubs',
                    subtitle: 'Ultra-HD color calibration & canvas studio',
                    features: 'Precision • 2hr Express',
                    img: 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1000&q=80',
                    color: 'purple'
                },
                {
                    city: 'London, UK',
                    title: 'Soho Fine Art Lab',
                    badge: '45+ EU Labs',
                    subtitle: 'Archival cotton papers & photobook suite',
                    features: 'Archival • Doorstep',
                    img: 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1000&q=80',
                    color: 'cyan'
                },
                {
                    city: 'Paris, France',
                    title: 'Paris Atelier Studio',
                    badge: '30+ EU Hubs',
                    subtitle: 'Handcrafted stretched canvas & albums',
                    features: 'Canvas • Courier',
                    img: 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1000&q=80',
                    color: 'amber'
                },
                {
                    city: 'Sydney, Australia',
                    title: 'Sydney Harbour Lab',
                    badge: '25+ Oceania',
                    subtitle: 'Coastal printing with eco archival inks',
                    features: 'Eco Inks • Same-Day',
                    img: 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1000&q=80',
                    color: 'emerald'
                }
            ],

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

                // Playing card hand fan array with Top Card ALWAYS at 0deg (straight & level):
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

        Alpine.data('storeAutocomplete', () => ({
            query: '<?php echo request('q'); ?>',
            stores: <?php echo json_encode(isset($stores) ? $stores : []); ?>,
            isLoading: false,
            hasSearched: <?php echo (isset($query) && $query !== '') ? 'true' : 'false'; ?>,
            nearbyStores: [],
            nearbyLoading: false,
            nearbyError: false,
            geolocationChecked: false,

            init() {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                this.detectLocation();
            },

            getLogoUrl(store) {
                if (!store) return '';
                if (store.logo && store.logo.trim() !== '') {
                    const logo = store.logo.trim();
                    if (logo.startsWith('http://') || logo.startsWith('https://')) return logo;
                    if (logo.startsWith('/storage/stores/logos/')) return logo;
                    if (logo.startsWith('storage/stores/logos/')) return '/' + logo;
                    if (logo.startsWith('stores/logos/')) return '/storage/' + logo;
                    if (logo.startsWith('/storage/')) return logo;
                    if (logo.startsWith('storage/')) return '/' + logo;
                    if (!logo.includes('/')) return '/storage/stores/logos/' + logo;
                    return '/storage/' + logo.replace(/^\//, '');
                }
                const name = encodeURIComponent(store.store_name || 'Store');
                return `https://ui-avatars.com/api/?name=${name}&background=4F46E5&color=fff&bold=true&font-size=0.45&rounded=true`;
            },

            detectLocation() {
                if (!navigator.geolocation) {
                    this.nearbyError = true;
                    this.geolocationChecked = true;
                    return;
                }

                this.nearbyLoading = true;
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        try {
                            const response = await fetch(`<?php echo url()->current(); ?>?lat=${lat}&lon=${lon}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (response.ok) {
                                const data = await response.json();
                                this.nearbyStores = data.stores || [];
                                if (this.nearbyStores.length === 0) {
                                    this.nearbyError = true;
                                }
                            } else {
                                this.nearbyError = true;
                            }
                        } catch (err) {
                            console.error('Error fetching nearby stores:', err);
                            this.nearbyError = true;
                        } finally {
                            this.nearbyLoading = false;
                            this.geolocationChecked = true;
                            this.$nextTick(() => {
                                if (typeof lucide !== 'undefined') {
                                    lucide.createIcons();
                                }
                            });
                        }
                    },
                    (error) => {
                        console.error('Geolocation error:', error);
                        this.nearbyError = true;
                        this.nearbyLoading = false;
                        this.geolocationChecked = true;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    }
                );
            },

            async fetchStores() {
                if (this.query.length === 0) {
                    this.stores = [];
                    this.hasSearched = false;
                    return;
                }

                this.isLoading = true;
                this.hasSearched = true;

                try {
                    const response = await fetch(`<?php echo url()->current(); ?>?q=${encodeURIComponent(this.query)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.stores = data.stores || [];

                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error fetching stores:', error);
                } finally {
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection