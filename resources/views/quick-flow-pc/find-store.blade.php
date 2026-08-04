@extends('layouts.quick-flow-pc')

@section('title', 'Find a Store')
@section('header_title', 'Find Store')

@push('styles')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #faf0ff 60%, #f0f4ff 100%);
    }
    .hero-pattern {
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
    .store-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .store-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.08), 0 0 0 1px rgba(236,72,153,0.12);
    }
    .category-card {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .category-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px -16px rgba(0,0,0,0.1);
    }
    .review-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .review-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px -8px rgba(0,0,0,0.06);
    }
    .search-input-hero {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .search-input-hero:focus-within {
        box-shadow: 0 0 0 4px rgba(236,72,153,0.12), 0 20px 40px -12px rgba(0,0,0,0.08);
        border-color: var(--color-brand-500, #ec4899);
    }
    .fade-up {
        animation: fadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .fade-up-delay-1 { animation-delay: 0.1s; }
    .fade-up-delay-2 { animation-delay: 0.2s; }
    .fade-up-delay-3 { animation-delay: 0.3s; }
    .fade-up-delay-4 { animation-delay: 0.4s; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .tag-pill {
        transition: all 0.2s ease;
    }
    .tag-pill:hover {
        background-color: var(--color-brand-600, #db2777);
        color: white;
        transform: scale(1.05);
    }
    .stat-item {
        position: relative;
    }
    .stat-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 32px;
        width: 1px;
        background: #e2e8f0;
    }
    .badge-open {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        color: #065f46;
    }
    .badge-closed {
        background: linear-gradient(135deg, #fef2f2, #fecaca);
        color: #991b1b;
    }
</style>
@endpush

@section('content')
<div x-data="storeAutocomplete()" class="pb-24">

    {{-- ===== SECTION 1: HERO ===== --}}
    <section class="hero-gradient hero-pattern -mx-10 -mt-4 px-10 pt-16 pb-20 relative overflow-hidden">
        <div class="hero-blob-1"></div>
        <div class="hero-blob-2"></div>
        <div class="hero-blob-3"></div>
        <div class="hero-dots"></div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            <div class="grid grid-cols-12 gap-12 items-center">
                {{-- Left: Content --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-5">
                    <div class="fade-up">
                        <span class="inline-flex items-center gap-2 bg-white/80 border border-brand-100 text-brand-600 text-sm font-semibold px-4 py-2 rounded-full mb-6 shadow-sm backdrop-blur-sm">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            Discover local stores near you
                        </span>
                    </div>

                    <h1 class="text-5xl xl:text-6xl font-extrabold text-slate-900 leading-[1.1] tracking-tight fade-up fade-up-delay-1">
                        Shop local,<br>
                        <span class="bg-gradient-to-r from-brand-600 to-violet-500 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">print beautifully.</span>
                    </h1>

                    <p class="text-lg xl:text-xl text-slate-500 mt-6 leading-relaxed max-w-lg fade-up fade-up-delay-2">
                        Find the nearest Qrinto print studio and bring your memories to life with premium quality prints, fast turnaround, and expert service.
                    </p>

                    {{-- Hero Search --}}
                    <div class="mt-8 fade-up fade-up-delay-3">
                        <div class="search-input-hero bg-white rounded-2xl border-2 border-slate-200 flex items-center gap-3 pr-3" style="max-width:540px">
                            <div class="flex items-center flex-1 gap-3 pl-5 py-1">
                                <i data-lucide="search" class="w-5 h-5 text-slate-400 flex-shrink-0"></i>
                                <input type="text" placeholder="Search by city, zip code, or store name..."
                                    x-model="query" @input.debounce.300ms="fetchStores" autocomplete="off"
                                    @keydown.enter.prevent="fetchStores(); $nextTick(() => { document.getElementById('store-results')?.scrollIntoView({behavior:'smooth', block:'start'}) })"
                                    class="w-full py-4 text-base text-slate-900 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0">
                            </div>
                            <button @click="fetchStores(); $nextTick(() => { document.getElementById('store-results')?.scrollIntoView({behavior:'smooth', block:'start'}) })"
                                class="flex-shrink-0 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl transition-all duration-200 active:scale-95 flex items-center gap-2">
                                <i data-lucide="search" class="w-4 h-4"></i>
                                Find Stores
                            </button>
                        </div>
                    </div>

                    {{-- Popular Searches --}}
                    <div class="mt-6 fade-up fade-up-delay-4">
                        <span class="text-sm text-slate-400 font-medium">Popular:</span>
                        <div class="inline-flex flex-wrap gap-2 ml-2">
                            <button @click="query = 'New York'; fetchStores()" class="tag-pill text-sm font-medium text-slate-600 bg-white border border-slate-200 px-3 py-1 rounded-full">New York</button>
                            <button @click="query = 'Los Angeles'; fetchStores()" class="tag-pill text-sm font-medium text-slate-600 bg-white border border-slate-200 px-3 py-1 rounded-full">Los Angeles</button>
                            <button @click="query = 'Chicago'; fetchStores()" class="tag-pill text-sm font-medium text-slate-600 bg-white border border-slate-200 px-3 py-1 rounded-full">Chicago</button>
                            <button @click="query = 'Houston'; fetchStores()" class="tag-pill text-sm font-medium text-slate-600 bg-white border border-slate-200 px-3 py-1 rounded-full">Houston</button>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="mt-10 flex items-center gap-8 fade-up fade-up-delay-4">
                        <div class="stat-item pr-8">
                            <p class="text-3xl font-extrabold text-slate-900">500+</p>
                            <p class="text-sm text-slate-500 mt-1">Store locations</p>
                        </div>
                        <div class="stat-item pr-8">
                            <p class="text-3xl font-extrabold text-slate-900">4.9</p>
                            <p class="text-sm text-slate-500 mt-1">Average rating</p>
                        </div>
                        <div class="stat-item">
                            <p class="text-3xl font-extrabold text-slate-900">48hr</p>
                            <p class="text-sm text-slate-500 mt-1">Fast delivery</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Illustration / Visual --}}
                <div class="col-span-12 lg:col-span-6 xl:col-span-7 hidden lg:block">
                    <div class="relative fade-up fade-up-delay-2">
                        {{-- Decorative store cards --}}
                        <div class="grid grid-cols-2 gap-5 max-w-lg ml-auto">
                            <div class="bg-white rounded-3xl p-6 shadow-lg shadow-slate-200/60 border border-slate-100 transform rotate-[-2deg] hover:rotate-0 transition-transform duration-500">
                                <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center mb-4">
                                    <i data-lucide="printer" class="w-7 h-7 text-brand-600"></i>
                                </div>
                                <h4 class="font-bold text-slate-900 text-base">Photo Prints</h4>
                                <p class="text-sm text-slate-400 mt-1">Premium quality</p>
                                <div class="flex items-center gap-1 mt-3">
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded-3xl p-6 shadow-lg shadow-slate-200/60 border border-slate-100 transform rotate-[2deg] hover:rotate-0 transition-transform duration-500 mt-8">
                                <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center mb-4">
                                    <i data-lucide="frame" class="w-7 h-7 text-violet-600"></i>
                                </div>
                                <h4 class="font-bold text-slate-900 text-base">Canvas Art</h4>
                                <p class="text-sm text-slate-400 mt-1">Museum quality</p>
                                <div class="flex items-center gap-1 mt-3">
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded-3xl p-6 shadow-lg shadow-slate-200/60 border border-slate-100 transform rotate-[1deg] hover:rotate-0 transition-transform duration-500">
                                <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4">
                                    <i data-lucide="gift" class="w-7 h-7 text-emerald-600"></i>
                                </div>
                                <h4 class="font-bold text-slate-900 text-base">Custom Gifts</h4>
                                <p class="text-sm text-slate-400 mt-1">Personalized</p>
                                <div class="flex items-center gap-1 mt-3">
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                </div>
                            </div>
                            <div class="bg-white rounded-3xl p-6 shadow-lg shadow-slate-200/60 border border-slate-100 transform rotate-[-1deg] hover:rotate-0 transition-transform duration-500 mt-[-16px]">
                                <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center mb-4">
                                    <i data-lucide="book-open" class="w-7 h-7 text-rose-600"></i>
                                </div>
                                <h4 class="font-bold text-slate-900 text-base">Photo Books</h4>
                                <p class="text-sm text-slate-400 mt-1">Hardcover finish</p>
                                <div class="flex items-center gap-1 mt-3">
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                    <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                </div>
                            </div>
                        </div>
                        {{-- Floating accent dots --}}
                        <div class="absolute -top-4 -left-4 w-20 h-20 bg-brand-200/30 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-violet-200/30 rounded-full blur-3xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 2: FIND A STORE ===== --}}
    <section id="store-results" class="max-w-[1400px] mx-auto pt-20 pb-16 scroll-mt-20">
        {{-- Section Header --}}
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">Find a Store</h2>
                <p class="text-lg text-slate-500 mt-2">Browse our network of premium print studios near you.</p>
            </div>
        </div>

        {{-- Store Status Banner --}}
        @if(!session()->has('active_store_id'))
        <div class="mb-8 p-5 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-4">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-800">No store selected yet. Search and choose a branch to start ordering.</p>
            </div>
        </div>
        @else
            @php $selectedStore = \App\Models\Store::find(session('active_store_id')); @endphp
            @if($selectedStore)
            <div class="mb-8 p-5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-4">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-0.5">Currently Selected</p>
                    <p class="font-bold text-slate-900 truncate">{{ $selectedStore->store_name }} &middot; {{ $selectedStore->city }}, {{ $selectedStore->state }} {{ $selectedStore->zip_code }}</p>
                </div>
                <a href="#" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 flex-shrink-0">Change &rarr;</a>
            </div>
            @endif
        @endif

        {{-- Search + Filters Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-8 flex flex-wrap items-center gap-4 shadow-sm">
            <div class="flex items-center gap-3 flex-1 min-w-[300px]">
                <i data-lucide="search" class="w-5 h-5 text-slate-400 flex-shrink-0"></i>
                <input type="text" placeholder="Search stores by name, city, or zip code..."
                    x-model="query" @input.debounce.300ms="fetchStores" autocomplete="off"
                    class="w-full text-base text-slate-900 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0">
                <div x-show="isLoading" class="flex-shrink-0">
                    <div class="w-5 h-5 border-2 border-slate-200 border-t-brand-500 rounded-full animate-spin"></div>
                </div>
            </div>
            <div class="h-8 w-px bg-slate-200 hidden lg:block"></div>
            <div class="flex items-center gap-3">
                <button @click="detectLocation()" class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-brand-600 bg-slate-50 hover:bg-brand-50 border border-slate-200 hover:border-brand-200 px-4 py-2.5 rounded-xl transition-all">
                    <i data-lucide="crosshair" class="w-4 h-4"></i>
                    Near Me
                </button>
                <select class="text-sm font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-100 focus:border-brand-300 transition-all cursor-pointer">
                    <option>All Categories</option>
                    <option>Photo Prints</option>
                    <option>Canvas Art</option>
                    <option>Photo Books</option>
                    <option>Gifts & Mugs</option>
                </select>
                <select class="text-sm font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-brand-100 focus:border-brand-300 transition-all cursor-pointer">
                    <option>Sort: Nearest</option>
                    <option>Sort: Rating</option>
                    <option>Sort: Name A–Z</option>
                </select>
            </div>
        </div>

        {{-- Nearby Stores Section --}}
        <div x-show="geolocationChecked && nearbyStores.length > 0 && !hasSearched" x-cloak class="mb-10">
            <h3 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
                <i data-lucide="navigation" class="w-5 h-5 text-brand-500"></i>
                Stores near you
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <template x-for="store in nearbyStores" :key="store.id">
                    <form action="{{ route('flow-pc.set-store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="store_id" :value="store.id">
                        <button type="submit" class="store-card w-full text-left bg-white border border-slate-200 rounded-2xl p-6 group cursor-pointer">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-brand-100 transition-colors">
                                    <i data-lucide="store" class="w-6 h-6 text-brand-500"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-slate-900 truncate" x-text="store.store_name"></h4>
                                    <p class="text-sm text-slate-500 mt-1 flex items-center gap-1 truncate">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                        <span x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="badge-open text-xs font-semibold px-2.5 py-1 rounded-full">Open Now</span>
                                <span class="text-sm font-semibold text-brand-600 group-hover:text-brand-700 flex items-center gap-1 transition-colors">
                                    Visit <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                </span>
                            </div>
                        </button>
                    </form>
                </template>
            </div>
        </div>

        {{-- Geolocation Loading --}}
        <div x-show="nearbyLoading" x-cloak class="mb-10 text-center py-10">
            <div class="w-10 h-10 border-2 border-slate-200 border-t-brand-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-slate-500 font-medium">Detecting your location...</p>
        </div>

        {{-- How it Works (before search) --}}
        <div x-show="!hasSearched && stores.length === 0"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0">

            <div class="bg-white border border-slate-200 rounded-3xl p-10 xl:p-12">
                <div class="text-center mb-10">
                    <h3 class="text-2xl font-extrabold text-slate-900">How it works</h3>
                    <p class="text-slate-500 mt-2">Get started in four simple steps</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:bg-brand-100 group-hover:scale-110 transition-all duration-300">
                            <i data-lucide="search" class="w-7 h-7 text-brand-600"></i>
                        </div>
                        <div class="text-xs font-bold text-brand-600 uppercase tracking-wider mb-2">Step 1</div>
                        <h4 class="font-bold text-slate-900 text-lg mb-2">Select a Store</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Search and pick the nearest Qrinto branch to see available services.</p>
                    </div>
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-violet-50 rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:bg-violet-100 group-hover:scale-110 transition-all duration-300">
                            <i data-lucide="layers" class="w-7 h-7 text-violet-600"></i>
                        </div>
                        <div class="text-xs font-bold text-violet-600 uppercase tracking-wider mb-2">Step 2</div>
                        <h4 class="font-bold text-slate-900 text-lg mb-2">Choose Products</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Browse categories like Photo Prints, Canvas Art, or Custom Gifts.</p>
                    </div>
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:bg-emerald-100 group-hover:scale-110 transition-all duration-300">
                            <i data-lucide="image-plus" class="w-7 h-7 text-emerald-600"></i>
                        </div>
                        <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-2">Step 3</div>
                        <h4 class="font-bold text-slate-900 text-lg mb-2">Design & Upload</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Upload your photos and use our editor to customize your design.</p>
                    </div>
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-5 group-hover:bg-rose-100 group-hover:scale-110 transition-all duration-300">
                            <i data-lucide="package-check" class="w-7 h-7 text-rose-600"></i>
                        </div>
                        <div class="text-xs font-bold text-rose-600 uppercase tracking-wider mb-2">Step 4</div>
                        <h4 class="font-bold text-slate-900 text-lg mb-2">Order & Collect</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">Checkout securely and collect from your chosen store.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Results Grid --}}
        <div x-show="hasSearched || stores.length > 0" x-cloak>
            {{-- Results header --}}
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="list" class="w-5 h-5 text-slate-400"></i>
                    Search Results
                    <span x-show="stores.length > 0" class="text-sm font-medium text-slate-400" x-text="`(${stores.length} found)`"></span>
                </h3>
            </div>

            {{-- Empty state --}}
            <div x-show="!isLoading && stores.length === 0 && query.length > 0" class="text-center py-20 bg-white rounded-3xl border border-slate-200">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <i data-lucide="map-pin-off" class="w-8 h-8 text-slate-400"></i>
                </div>
                <p class="text-xl font-bold text-slate-900">No stores found</p>
                <p class="text-slate-500 mt-2">No results for "<span x-text="query" class="font-semibold text-slate-700"></span>". Try a different city or zip code.</p>
            </div>

            {{-- Store Cards Grid --}}
            <div x-show="stores.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <template x-for="store in stores" :key="store.id">
                    <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                        @csrf
                        <input type="hidden" name="store_id" :value="store.id">
                        <button type="submit" class="store-card w-full h-full text-left bg-white border border-slate-200 rounded-2xl p-6 group cursor-pointer flex flex-col">
                            {{-- Store header --}}
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-brand-50 to-brand-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                    <i data-lucide="store" class="w-6 h-6 text-brand-500"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-slate-900 line-clamp-2 leading-snug" x-text="store.store_name"></h4>
                                    <div class="flex items-center gap-1 mt-1.5">
                                        <i data-lucide="star" class="w-3.5 h-3.5 text-amber-400 fill-amber-400"></i>
                                        <span class="text-sm font-semibold text-slate-700">4.9</span>
                                        <span class="text-xs text-slate-400">(128)</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1">
                                <p class="text-sm text-slate-500 flex items-start gap-2 mb-2">
                                    <i data-lucide="map-pin" class="w-4 h-4 mt-0.5 flex-shrink-0 text-slate-400"></i>
                                    <span x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                                </p>
                                <p class="text-sm text-slate-500 flex items-start gap-2" x-show="store.phone">
                                    <i data-lucide="phone" class="w-4 h-4 mt-0.5 flex-shrink-0 text-slate-400"></i>
                                    <span x-text="store.phone"></span>
                                </p>
                            </div>

                            {{-- Footer --}}
                            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="badge-open text-xs font-semibold px-2.5 py-1 rounded-full">Open</span>
                                    <span class="text-xs font-medium text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full flex items-center gap-1">
                                        <i data-lucide="truck" class="w-3 h-3"></i> Pickup
                                    </span>
                                </div>
                                <span class="text-sm font-semibold text-brand-600 group-hover:text-brand-700 flex items-center gap-1 transition-colors">
                                    Visit <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-200"></i>
                                </span>
                            </div>
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </section>

    {{-- ===== SECTION 3: BROWSE CATEGORIES ===== --}}
    <section class="max-w-[1400px] mx-auto py-16 border-t border-slate-100">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">Browse Categories</h2>
                <p class="text-lg text-slate-500 mt-2">Explore our most popular printing categories.</p>
            </div>
            <a href="#" class="hidden lg:flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                View all categories <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-5">
            @php
                $categories = [
                    ['name' => 'Photo Prints', 'icon' => 'image', 'count' => 340, 'color' => 'brand'],
                    ['name' => 'Canvas Art', 'icon' => 'frame', 'count' => 185, 'color' => 'violet'],
                    ['name' => 'Photo Books', 'icon' => 'book-open', 'count' => 220, 'color' => 'rose'],
                    ['name' => 'Mugs & Gifts', 'icon' => 'gift', 'count' => 156, 'color' => 'emerald'],
                    ['name' => 'Calendars', 'icon' => 'calendar', 'count' => 98, 'color' => 'amber'],
                    ['name' => 'Cards', 'icon' => 'mail', 'count' => 275, 'color' => 'cyan'],
                    ['name' => 'Acrylic', 'icon' => 'diamond', 'count' => 64, 'color' => 'fuchsia'],
                ];
            @endphp

            @foreach($categories as $cat)
            <a href="#" class="category-card bg-white border border-slate-200 rounded-2xl p-6 text-center group">
                <div class="w-14 h-14 bg-{{ $cat['color'] }}-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-{{ $cat['color'] }}-100 group-hover:scale-110 transition-all duration-300">
                    <i data-lucide="{{ $cat['icon'] }}" class="w-7 h-7 text-{{ $cat['color'] }}-500"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm">{{ $cat['name'] }}</h4>
                <p class="text-xs text-slate-400 mt-1">{{ $cat['count'] }} stores</p>
            </a>
            @endforeach
        </div>
    </section>

    {{-- ===== SECTION 4: CUSTOMER REVIEWS ===== --}}
    <section class="max-w-[1400px] mx-auto py-16 border-t border-slate-100">
        <div class="grid grid-cols-12 gap-10">
            {{-- Left: Rating Summary --}}
            <div class="col-span-12 lg:col-span-4">
                <h2 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">What Our Customers Say</h2>
                <p class="text-lg text-slate-500 mt-2">Trusted by thousands of happy customers.</p>

                <div class="mt-8 bg-white border border-slate-200 rounded-2xl p-8">
                    <div class="text-center">
                        <p class="text-6xl font-extrabold text-slate-900">4.9</p>
                        <div class="flex items-center justify-center gap-1 mt-3">
                            <i data-lucide="star" class="w-6 h-6 text-amber-400 fill-amber-400"></i>
                            <i data-lucide="star" class="w-6 h-6 text-amber-400 fill-amber-400"></i>
                            <i data-lucide="star" class="w-6 h-6 text-amber-400 fill-amber-400"></i>
                            <i data-lucide="star" class="w-6 h-6 text-amber-400 fill-amber-400"></i>
                            <i data-lucide="star" class="w-6 h-6 text-amber-400 fill-amber-400"></i>
                        </div>
                        <p class="text-sm text-slate-500 mt-2">Based on 2,847 reviews</p>
                    </div>

                    <div class="mt-8 space-y-3">
                        @foreach([['5', 78], ['4', 15], ['3', 5], ['2', 1], ['1', 1]] as $rating)
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-600 w-3">{{ $rating[0] }}</span>
                            <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400 flex-shrink-0"></i>
                            <div class="flex-1 h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full" style="width: {{ $rating[1] }}%"></div>
                            </div>
                            <span class="text-xs font-medium text-slate-400 w-8 text-right">{{ $rating[1] }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right: Review Cards --}}
            <div class="col-span-12 lg:col-span-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @php
                        $reviews = [
                            [
                                'name' => 'Sarah Mitchell',
                                'initials' => 'SM',
                                'color' => 'brand',
                                'rating' => 5,
                                'text' => 'The canvas print quality blew me away. Colors are vibrant and true to the original photo. Arrived in perfect condition within two days. Already ordered three more for the living room.',
                                'store' => 'Qrinto NYC - Manhattan',
                                'date' => 'Jul 2026',
                            ],
                            [
                                'name' => 'James Thornton',
                                'initials' => 'JT',
                                'color' => 'emerald',
                                'rating' => 5,
                                'text' => 'Created a 100-page photo book of our wedding and it turned out absolutely stunning. The paper quality is fantastic and the binding is solid. Best decision for preserving our memories.',
                                'store' => 'Qrinto LA - Beverly Hills',
                                'date' => 'Jun 2026',
                            ],
                            [
                                'name' => 'Amara Osei',
                                'initials' => 'AO',
                                'color' => 'rose',
                                'rating' => 5,
                                'text' => 'The design studio is incredibly intuitive. I uploaded my artwork and had custom mugs designed in under five minutes. The print quality on the mugs exceeded my expectations.',
                                'store' => 'Qrinto Chicago - Loop',
                                'date' => 'Jun 2026',
                            ],
                            [
                                'name' => 'David Kim',
                                'initials' => 'DK',
                                'color' => 'amber',
                                'rating' => 5,
                                'text' => 'Outstanding service and quality! The acrylic prints look absolutely premium hanging in my office. The team at the store was incredibly helpful with the sizing recommendations.',
                                'store' => 'Qrinto SF - Financial District',
                                'date' => 'May 2026',
                            ],
                        ];
                    @endphp

                    @foreach($reviews as $review)
                    <div class="review-card bg-white border border-slate-200 rounded-2xl p-6">
                        <div class="flex items-center gap-1 mb-4">
                            @for($i = 0; $i < $review['rating']; $i++)
                                <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                            @endfor
                        </div>
                        <p class="text-slate-600 leading-relaxed text-sm">{{ $review['text'] }}</p>
                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-{{ $review['color'] }}-100 text-{{ $review['color'] }}-700 rounded-full flex items-center justify-center text-xs font-bold">
                                    {{ $review['initials'] }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $review['name'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $review['store'] }}</p>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400">{{ $review['date'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 text-center">
                    <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                        Read all reviews <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
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