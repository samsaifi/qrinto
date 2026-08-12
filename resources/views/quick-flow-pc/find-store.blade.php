@extends('layouts.quick-flow-pc')

@section('title', 'Qrinto Store Locator')

@section('header_title', 'Store Locator')

@push('styles')
    <!-- Swiper CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Leaflet OpenStreetMap CSS CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        .leaflet-container {
            font-family: inherit !important;
            z-index: 10 !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 16px !important;
            padding: 4px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        }

        .custom-user-pin {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-store-pin {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-pattern-bg {
            background-color: #ffffff;
            background-image:
                radial-gradient(rgba(148, 163, 184, 0.28) 1.2px, transparent 1.2px),
                linear-gradient(to right, rgba(241, 245, 249, 0.7) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(241, 245, 249, 0.7) 1px, transparent 1px);
            background-size: 24px 24px, 48px 48px, 48px 48px;
        }

        .hero-gradient-overlay {
            background: radial-gradient(circle at 85% 20%, rgba(111, 182, 58, 0.08) 0%, rgba(255, 255, 255, 0) 55%),
                radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.06) 0%, rgba(255, 255, 255, 0) 50%);
        }

        .store-card-premium {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .store-card-premium:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08), 0 0 0 2px rgba(111, 182, 58, 0.3);
        }

        .category-card-premium {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .category-card-premium:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.25), 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            border-color: #ec4899;
        }
    </style>
@endpush

@section('content')
    <div x-data="storeAutocomplete()" class="w-full">

        {{-- ===== SECTION 1: HERO HEADER (WHITE PATTERN BACKGROUND HERO SECTION) ===== --}}
        <section
            class="hero-pattern-bg relative w-full px-6 lg:px-12 py-12 sm:py-16 border-b border-slate-200/80 overflow-hidden">
            <!-- Ambient Soft Radial Glow -->
            <div class="absolute inset-0 pointer-events-none hero-gradient-overlay"></div>

            <!-- Giant Background Watermark Text "Step 1" (Bottom Right) -->
            <div
                class="absolute right-4 sm:right-10 bottom-2 text-[140px] sm:text-[200px] font-black text-slate-200/40 select-none pointer-events-none tracking-tighter leading-none z-0">
                Step 1
            </div>

            <div class="max-w-[1400px] mx-auto w-full relative z-10">
                {{-- Top Navigation / Breadcrumbs --}}
                <div class="flex items-center justify-between gap-3 mb-6">
                    <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <a href="{{ route('flow-pc.index') }}"
                            class="text-slate-500 hover:text-brand-600 transition-colors flex items-center gap-1">
                            <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Home</span>
                        </a>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <span class="text-slate-900 font-extrabold">Find a Store</span>
                    </nav>
                </div>

                <div class="max-w-5xl">
                    <!-- Badge Tag -->
                    <div class="mb-3">
                        <span
                            class="inline-flex items-center gap-2 bg-brand-50 text-brand-700 border border-brand-200/80 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest shadow-2xs">
                            <i data-lucide="map-pin" class="w-3 h-3 text-brand-600"></i>
                            LOCAL BRANCH LOCATOR ENGINE
                        </span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-xl sm:text-5xl font-extrabold text-slate-800 leading-tight tracking-tight">
                        Shop local, <span class="text-brand-600">print beautifully.</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-sm sm:text-base text-slate-600 font-normal mt-3 leading-relaxed max-w-5xl">
                        Find the nearest Qrinto print studio branch and experience full-bleed, edge-to-edge
                        production-quality prints. Using the Noritsu 931-BL, we deliver sharp, high-resolution 1200x1200 dpi
                        prints on a wide variety of media up to 324gsm. Enjoy museum-quality photo books, flat cards,
                        calendars, and magnets with express same-day pickup and artisan craftsmanship
                    </p>
                </div>
            </div>
        </section>

        {{-- ===== SECTION 2: FIND A STORE RESULTS GRID ===== --}}
        <section id="store-results" class="w-full bg-white py-16 px-6 sm:px-10 border-b border-slate-100/80 relative">


            <div class="max-w-[1400px] mx-auto relative z-10">
                <!-- Combined Single-Row Header Bar (Clean Borderless Layout) -->
                <div class="mb-10 flex flex-col lg:flex-row items-center justify-between gap-5 sm:gap-6 w-full">

                    {{-- Element 1 (Left): Title & Badge --}}
                    <div class="flex items-center gap-3 shrink-0">
                        <div
                            class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                            <i data-lucide="store" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-medium uppercase tracking-widest text-brand-600 block">Network
                                Directory</span>
                            <h2
                                class="text-base sm:text-lg font-normal text-slate-800 tracking-tight leading-tight whitespace-nowrap">
                                Select a Store Branch
                            </h2>
                        </div>
                    </div>

                    {{-- Element 2 (Center): Search Input & Near Me Filter Toolbar (Expanded Full Width) --}}
                    <div
                        class="flex items-center gap-2 bg-slate-50 border border-slate-200/90 rounded-2xl p-1.5 flex-1 w-full shadow-2xs">
                        <div class="flex items-center gap-2 flex-1 pl-3">
                            <i data-lucide="search" class="w-4.5 h-4.5 text-slate-400 shrink-0"></i>
                            <input type="text" placeholder="Filter by city, zip, or name..." x-model="query"
                                @input.debounce.300ms="fetchStores" autocomplete="off"
                                class="w-full text-xs sm:text-sm text-slate-900 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0 font-medium">
                            <div x-show="isLoading" class="shrink-0" x-cloak>
                                <div class="w-4 h-4 border-2 border-slate-300 border-t-brand-600 rounded-full animate-spin">
                                </div>
                            </div>
                        </div>
                        <button @click="detectLocation()"
                            class="flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-slate-700 hover:text-brand-600 bg-white hover:bg-brand-50 border border-slate-200/90 px-3.5 py-2 rounded-xl transition-all shadow-2xs cursor-pointer active:scale-95 shrink-0">
                            <i data-lucide="crosshair" class="w-3.5 h-3.5 text-brand-600"></i>
                            <span>Near Me</span>
                        </button>
                    </div>

                    {{-- Element 3 (Right): Currently Active Store Status Pill --}}
                    <div class="shrink-0">
                        @if (!session()->has('active_store_id'))
                            <div
                                class="flex items-center gap-3 bg-amber-50 border border-amber-200/90 rounded-2xl px-4 py-2.5">
                                <i data-lucide="alert-circle" class="w-4.5 h-4.5 text-amber-600 shrink-0"></i>
                                <div>
                                    <span
                                        class="text-[10px] font-black uppercase tracking-wider text-amber-700 block leading-none">Branch
                                        Notice</span>
                                    <span class="text-xs font-black text-amber-900 leading-tight">No Store Selected</span>
                                </div>
                            </div>
                        @elseif ($selectedStore = \App\Models\Store::find(session('active_store_id')))
                            <div
                                class="flex items-center gap-3 bg-emerald-50/90 border border-emerald-200/90 rounded-2xl px-4 py-2.5">
                                <div
                                    class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                    <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span
                                        class="text-[9px] font-black uppercase tracking-wider text-emerald-700 block leading-none">Currently
                                        Active Branch</span>
                                    <h4
                                        class="text-xs font-black text-slate-900 truncate max-w-[140px] sm:max-w-[180px] mt-0.5">
                                        {{ $selectedStore->store_name }}</h4>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1 text-[10px] font-black text-emerald-700 bg-white border border-emerald-200/80 px-2.5 py-1 rounded-full shrink-0 ml-1">
                                    Active <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                </span>
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Split 2-Column Main Layout: Store Cards List (Left) & Live Map (Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    {{-- Left Side: Store Cards List (6 Cols) --}}
                    <div class="lg:col-span-6 space-y-6">

                        {{-- Geolocation Loading State --}}
                        <div x-show="nearbyLoading" x-cloak x-transition
                            class="p-4 bg-slate-50/90 border border-slate-200/80 rounded-2xl flex items-center justify-center gap-3 shadow-2xs">
                            <div
                                class="w-7 h-7 rounded-full bg-brand-100 border border-brand-200/80 text-brand-700 flex items-center justify-center shrink-0">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-brand-600"></i>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-700">Detecting your nearest Qrinto branch
                                location...</span>
                        </div>

                        {{-- Nearby Detected Stores Grid --}}
                        <div x-show="geolocationChecked && nearbyStores.length > 0 && !hasSearched" x-cloak>
                            <h3 class="text-sm sm:text-base font-normal text-slate-800 mb-4 flex items-center gap-2">
                                <i data-lucide="navigation" class="w-4 h-4 text-brand-600"></i>
                                Branches Nearest To Your Location
                                <span class="text-xs font-normal text-slate-400"
                                    x-text="`(${nearbyStores.length} ${nearbyStores.length === 1 ? 'branch' : 'branches'})`"></span>
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <template x-for="store in nearbyStores" :key="store.id">
                                    <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                                        @csrf
                                        <input type="hidden" name="store_id" :value="store.id">
                                        <button type="submit"
                                            @mouseenter="selectStoreOnMap(store, store._mapLat, store._mapLon)"
                                            class="store-card-premium w-full h-full text-left bg-white border border-slate-200/90 rounded-[24px] p-4 sm:p-5 group cursor-pointer flex flex-col justify-between shadow-2xs hover:border-brand-500 transition-all">
                                            <div>
                                                <!-- Top Row: Logo + Info Side-by-Side -->
                                                <div class="flex items-start gap-3 mb-2">
                                                    <div
                                                        class="w-12 h-12 rounded-2xl bg-white border border-slate-200/90 shadow-2xs p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-all">
                                                        <template x-if="store.logo">
                                                            <img :src="'{{ asset('storage') }}/' + store.logo"
                                                                :alt="store.store_name"
                                                                class="w-full h-full object-cover rounded-xl">
                                                        </template>
                                                        <template x-if="!store.logo">
                                                            <div
                                                                class="w-full h-full rounded-xl bg-brand-500 text-white flex items-center justify-center text-lg font-black">
                                                                <span
                                                                    x-text="store.store_name ? store.store_name.charAt(0) : 'Q'"></span>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <h4 class="font-black text-brand-600 text-base group-hover:text-brand-700 transition-colors line-clamp-1 leading-tight"
                                                            x-text="store.store_name"></h4>

                                                        <div
                                                            class="flex items-center gap-1 mt-1 text-xs font-bold text-slate-600">
                                                            <div class="flex items-center gap-0.5 text-amber-400">
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                            </div>
                                                            <span
                                                                class="ml-0.5 text-slate-800 text-[11px] font-extrabold">4.9</span>
                                                        </div>

                                                        <p
                                                            class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1 leading-tight truncate">
                                                            <i data-lucide="map-pin"
                                                                class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                            <span class="truncate"
                                                                x-text="`${store.city || ''}, ${store.state || ''}`"></span>
                                                        </p>

                                                        <template x-if="userLat && store._calcDistance">
                                                            <div
                                                                class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full mt-2">
                                                                <i data-lucide="navigation"
                                                                    class="w-3 h-3 text-emerald-600"></i>
                                                                <span x-text="`${store._calcDistance} away`"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold">
                                                <span
                                                    class="text-slate-600 bg-slate-100/90 px-2.5 py-0.5 rounded-lg text-[10px] font-bold">Same-Day
                                                    Pickup</span>
                                                <span
                                                    class="text-brand-600 group-hover:text-brand-700 flex items-center gap-1 text-[11px] font-black">
                                                    Select Store <i data-lucide="arrow-right"
                                                        class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                                                </span>
                                            </div>
                                        </button>
                                    </form>
                                </template>
                            </div>
                        </div>

                        {{-- Search Results Grid --}}
                        <div x-show="hasSearched || stores.length > 0" x-cloak>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                    <i data-lucide="list" class="w-4 h-4 text-brand-600"></i>
                                    Available Stores
                                    <span x-show="stores.length > 0" class="text-xs font-normal text-slate-400"
                                        x-text="`(${stores.length} found)`"></span>
                                </h3>
                            </div>

                            {{-- Empty Search Results State --}}
                            <div x-show="!isLoading && stores.length === 0 && query.length > 0"
                                class="text-center py-12 bg-slate-50 rounded-2xl border border-slate-200/80">
                                <div
                                    class="w-12 h-12 bg-slate-200/80 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <i data-lucide="map-pin-off" class="w-6 h-6"></i>
                                </div>
                                <h4 class="text-base font-bold text-slate-900">No stores found</h4>
                                <p class="text-xs text-slate-500 font-medium mt-1">No matching branches for "<span
                                        x-text="query" class="font-bold text-slate-800"></span>".</p>
                            </div>

                            {{-- Stores Grid --}}
                            <div x-show="stores.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <template x-for="store in stores" :key="store.id">
                                    <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                                        @csrf
                                        <input type="hidden" name="store_id" :value="store.id">
                                        <button type="submit"
                                            @mouseenter="selectStoreOnMap(store, store._mapLat, store._mapLon)"
                                            class="store-card-premium w-full h-full text-left bg-white border border-slate-200/90 rounded-[24px] p-4 sm:p-5 group cursor-pointer flex flex-col justify-between shadow-2xs hover:border-brand-500 transition-all">
                                            <div>
                                                <div class="flex items-start gap-3 mb-2">
                                                    <div
                                                        class="w-12 h-12 rounded-2xl bg-white border border-slate-200/90 shadow-2xs p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-all">
                                                        <template x-if="store.logo">
                                                            <img :src="'{{ asset('storage') }}/' + store.logo"
                                                                :alt="store.store_name"
                                                                class="w-full h-full object-cover rounded-xl">
                                                        </template>
                                                        <template x-if="!store.logo">
                                                            <div
                                                                class="w-full h-full rounded-xl bg-brand-500 text-white flex items-center justify-center text-lg font-black">
                                                                <span
                                                                    x-text="store.store_name ? store.store_name.charAt(0) : 'Q'"></span>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <h4 class="font-black text-brand-600 text-base group-hover:text-brand-700 transition-colors line-clamp-1 leading-tight"
                                                            x-text="store.store_name"></h4>

                                                        <div
                                                            class="flex items-center gap-1 mt-1 text-xs font-bold text-slate-600">
                                                            <div class="flex items-center gap-0.5 text-amber-400">
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                                <i data-lucide="star" class="w-3 h-3 fill-amber-400"></i>
                                                            </div>
                                                            <span
                                                                class="ml-0.5 text-slate-800 text-[11px] font-extrabold">4.9</span>
                                                        </div>

                                                        <p
                                                            class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1 leading-tight truncate">
                                                            <i data-lucide="map-pin"
                                                                class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                            <span class="truncate"
                                                                x-text="`${store.city || ''}, ${store.state || ''}`"></span>
                                                        </p>

                                                        <template x-if="userLat && store._calcDistance">
                                                            <div
                                                                class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full mt-2">
                                                                <i data-lucide="navigation"
                                                                    class="w-3 h-3 text-emerald-600"></i>
                                                                <span x-text="`${store._calcDistance} away`"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs font-extrabold">
                                                <span
                                                    class="text-slate-600 bg-slate-100/90 px-2.5 py-0.5 rounded-lg text-[10px] font-bold">Same-Day
                                                    Pickup</span>
                                                <span
                                                    class="text-brand-600 group-hover:text-brand-700 flex items-center gap-1 text-[11px] font-black">
                                                    Select Store <i data-lucide="arrow-right"
                                                        class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                                                </span>
                                            </div>
                                        </button>
                                    </form>
                                </template>
                            </div>
                        </div>

                    </div>

                    {{-- Right Side: Live Interactive Map Box (6 Cols Sticky) --}}
                    <div class="lg:col-span-6 sticky top-[96px] self-start z-30">
                        <div class="bg-white border border-slate-200/90 rounded-[28px] p-3.5 shadow-xs">
                            <div class="flex items-center justify-between px-3 py-2 border-b border-slate-100 mb-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Live Store
                                        Location Map</span>
                                </div>
                                <template x-if="userLat && userLon">
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2.5 py-0.5 rounded-full">
                                        <i data-lucide="crosshair" class="w-3 h-3 text-emerald-600"></i> GPS Active
                                    </span>
                                </template>
                            </div>

                            <!-- Map Canvas Container (Dynamic Viewport Height for Sticky View) -->
                            <div id="store-leaflet-map"
                                class="w-full h-[calc(100vh-220px)] min-h-[440px] max-h-[580px] rounded-[22px] z-10 overflow-hidden bg-slate-100 shadow-inner">
                            </div>

                            <!-- Map Action / Distance Bar Footer -->
                            <div
                                class="mt-3 px-3.5 py-2.5 bg-slate-50 border border-slate-100 rounded-2xl flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                        <i data-lucide="navigation-2" class="w-4 h-4 text-brand-600"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="text-[10px] font-bold text-slate-400 uppercase block leading-none">Branch
                                            Proximity</span>
                                        <template x-if="selectedStoreDistance">
                                            <span class="text-xs font-extrabold text-slate-900"
                                                x-text="`${selectedStoreName || 'Branch'} is ${selectedStoreDistance} from you`"></span>
                                        </template>
                                        <template x-if="!selectedStoreDistance">
                                            <span class="text-xs font-medium text-slate-500">Select a branch to view
                                                route</span>
                                        </template>
                                    </div>
                                </div>

                                <template x-if="selectedStoreObj">
                                    <a :href="getDirectionsUrl(selectedStoreObj)" target="_blank"
                                        class="inline-flex items-center gap-1.5 text-[11px] font-black text-white bg-slate-900 hover:bg-brand-600 px-3.5 py-2 rounded-xl transition-all shadow-2xs">
                                        <span>Get Directions</span>
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ===== SECTION 3: COUNTRY-WISE STORE DIRECTORY ===== --}}
        @php
            $countryNameMap = [
                'US' => 'United States',
                'USA' => 'United States',
                'UNITED STATES' => 'United States',
                'CA' => 'Canada',
                'CANADA' => 'Canada',
                'IN' => 'India',
                'INDIA' => 'India',
                'UK' => 'United Kingdom',
                'GB' => 'United Kingdom',
                'UNITED KINGDOM' => 'United Kingdom',
                'AU' => 'Australia',
                'AUSTRALIA' => 'Australia',
                'DE' => 'Germany',
                'GERMANY' => 'Germany',
                'FR' => 'France',
                'FRANCE' => 'France',
                'JP' => 'Japan',
                'JAPAN' => 'Japan',
                'AE' => 'United Arab Emirates',
                'UAE' => 'United Arab Emirates',
            ];

            $allStoresByCountry = \App\Models\Store::active()
                ->get()
                ->groupBy(function ($store) use ($countryNameMap) {
                    $raw = trim($store->country ?: 'Global Network');
                    $upper = strtoupper($raw);
                    return $countryNameMap[$upper] ?? ($countryNameMap[$raw] ?? $raw);
                });
        @endphp

        @if ($allStoresByCountry->count() > 0)
            <section id="country-stores"
                class="w-full bg-slate-50/60 py-16 px-6 sm:px-10 border-b border-slate-100/80 relative overflow-hidden">
                <!-- Giant Background Watermark Text "Global" -->
                <div
                    class="absolute right-4 sm:right-10 bottom-2 sm:bottom-4 text-[140px] sm:text-[220px] lg:text-[280px] font-black text-slate-900/[0.025] select-none pointer-events-none tracking-tighter leading-none z-0">
                    Global
                </div>

                <div class="max-w-[1400px] mx-auto relative z-10">
                    <!-- Section Header -->
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                        <div>
                            <span
                                class="inline-flex items-center gap-2 bg-brand-50 text-brand-700 text-xs font-black px-4 py-2 rounded-full mb-3 uppercase tracking-widest border border-brand-100">
                                <i data-lucide="globe-2" class="w-4 h-4 text-brand-600"></i> Country Directory
                            </span>
                            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight">
                                Explore Stores <span class="text-slate-950 italic"
                                    style="font-family: 'Playfair Display', serif;">by Country</span>
                            </h2>
                            <p class="text-sm text-slate-500 font-medium mt-1">Browse active Qrinto branches grouped by
                                region and country.</p>
                        </div>
                        <div class="shrink-0">
                            <span
                                class="text-xs font-black text-slate-500 bg-white border border-slate-200/90 px-4 py-2 rounded-2xl shadow-2xs">
                                {{ $allStoresByCountry->count() }}
                                {{ Str::plural('Country', $allStoresByCountry->count()) }} Available
                            </span>
                        </div>
                    </div>

                    <!-- Country Rows -->
                    <div class="space-y-10">
                        @foreach ($allStoresByCountry as $countryName => $cStores)
                            <div class="bg-white border border-slate-200/90 rounded-[32px] p-6 sm:p-8 shadow-xs">
                                <!-- Country Header Row -->
                                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 border border-brand-200/80 flex items-center justify-center shrink-0">
                                            <i data-lucide="map-pin" class="w-5 h-5 text-brand-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-black text-slate-900 tracking-tight">
                                                {{ $countryName }}</h3>
                                            <span class="text-xs text-slate-400 font-bold">{{ $cStores->count() }}
                                                {{ Str::plural('Branch', $cStores->count()) }}</span>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs font-extrabold text-brand-600 bg-brand-50 px-3.5 py-1.5 rounded-full border border-brand-100">
                                        {{ $cStores->first()->currency ?? 'USD' }} Region
                                    </span>
                                </div>

                                <!-- Stores Grid for this Country -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                    @foreach ($cStores as $cStore)
                                        <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                                            @csrf
                                            <input type="hidden" name="store_id" value="{{ $cStore->id }}">
                                            <button type="submit"
                                                class="store-card-premium w-full h-full text-left bg-slate-50/70 hover:bg-white border border-slate-200/80 hover:border-brand-300 rounded-[28px] p-5 sm:p-6 group cursor-pointer flex flex-col justify-between transition-all shadow-2xs hover:shadow-md">
                                                <div>
                                                    <!-- Top Row: Logo + Info Side-by-Side -->
                                                    <div class="flex items-start gap-3.5 mb-2">
                                                        <div
                                                            class="w-14 h-14 rounded-2xl bg-white border border-slate-200/90 shadow-2xs p-1 flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-all">
                                                            @if ($cStore->logo)
                                                                <img src="{{ asset('storage/' . ltrim($cStore->logo, '/')) }}"
                                                                    alt="{{ $cStore->store_name }}"
                                                                    class="w-full h-full object-cover rounded-xl">
                                                            @else
                                                                <div
                                                                    class="w-full h-full rounded-xl bg-brand-500 text-white flex items-center justify-center text-xl font-black">
                                                                    <span>{{ Str::upper(substr($cStore->store_name, 0, 1)) }}</span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <div class="min-w-0 flex-1">
                                                            <!-- Store Title in Brand Green -->
                                                            <h4
                                                                class="font-black text-brand-600 text-base sm:text-lg group-hover:text-brand-700 transition-colors line-clamp-1 leading-tight">
                                                                {{ $cStore->store_name }}</h4>

                                                            <!-- Rating Stars & Badge -->
                                                            <div
                                                                class="flex items-center gap-1 mt-1 text-xs font-bold text-slate-600">
                                                                <div class="flex items-center gap-0.5 text-amber-400">
                                                                    @for ($i = 0; $i < 5; $i++)
                                                                        <i data-lucide="star"
                                                                            class="w-3.5 h-3.5 fill-amber-400"></i>
                                                                    @endfor
                                                                </div>
                                                                <span
                                                                    class="ml-0.5 text-slate-800 text-[11px] font-extrabold">4.9</span>
                                                                <span
                                                                    class="text-slate-400 font-normal text-[10px]">(Verified
                                                                    Store)</span>
                                                            </div>

                                                            <!-- Address -->
                                                            <p
                                                                class="text-xs text-slate-500 font-medium mt-1.5 flex items-center gap-1 leading-tight truncate">
                                                                <i data-lucide="map-pin"
                                                                    class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                                                <span
                                                                    class="truncate">{{ implode(', ', array_filter([$cStore->city, $cStore->state, $cStore->zip_code])) }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Card Action Footer -->
                                                <div
                                                    class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-extrabold">
                                                    <span
                                                        class="text-slate-600 bg-white border border-slate-200/80 px-3 py-1 rounded-xl text-[11px] font-bold">Same-Day
                                                        Pickup</span>
                                                    <span
                                                        class="text-brand-600 group-hover:text-brand-700 flex items-center gap-1 text-[12px] font-black">
                                                        Select Store <i data-lucide="arrow-right"
                                                            class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
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
@endsection

@push('scripts')
    <!-- Leaflet OpenStreetMap JS CDN -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('storeAutocomplete', () => ({
                query: '<?php echo request('q'); ?>',
                stores: <?php echo json_encode(isset($stores) ? $stores : []); ?>,
                isLoading: false,
                hasSearched: <?php echo isset($query) && $query !== '' ? 'true' : 'false'; ?>,
                nearbyStores: [],
                nearbyLoading: false,
                nearbyError: false,
                geolocationChecked: false,

                userLat: null,
                userLon: null,
                map: null,
                userMarker: null,
                storeMarkers: [],
                routeLine: null,
                selectedStoreDistance: null,
                selectedStoreName: null,
                selectedStoreObj: null,

                init() {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                    this.detectLocation();
                },

                calculateDistance(lat1, lon1, lat2, lon2) {
                    if (!lat1 || !lon1 || !lat2 || !lon2) return null;
                    const R = 6371; // Earth radius in km
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    const d = R * c;
                    return d < 1 ? `${Math.round(d * 1000)} m` : `${d.toFixed(1)} km`;
                },

                initMap() {
                    this.$nextTick(() => {
                        const container = document.getElementById('store-leaflet-map');
                        if (!container || typeof L === 'undefined') return;

                        if (!this.map) {
                            this.map = L.map('store-leaflet-map', {
                                zoomControl: false
                            }).setView([28.6139, 77.2090], 11);
                            L.control.zoom({
                                position: 'topright'
                            }).addTo(this.map);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(this.map);
                        }
                        this.updateMapMarkers();
                    });
                },

                updateMapMarkers() {
                    if (!this.map || typeof L === 'undefined') return;

                    this.storeMarkers.forEach(m => this.map.removeLayer(m));
                    this.storeMarkers = [];
                    if (this.routeLine) {
                        this.map.removeLayer(this.routeLine);
                        this.routeLine = null;
                    }

                    const bounds = L.latLngBounds();

                    if (this.userLat && this.userLon) {
                        const userLatLng = [this.userLat, this.userLon];
                        if (!this.userMarker) {
                            const userIcon = L.divIcon({
                                className: 'custom-user-pin',
                                html: `<div class="w-6 h-6 rounded-full bg-emerald-500 border-2 border-white shadow-md animate-pulse flex items-center justify-center text-white text-[10px]">📍</div>`,
                                iconSize: [24, 24],
                                iconAnchor: [12, 12]
                            });
                            this.userMarker = L.marker(userLatLng, {
                                    icon: userIcon
                                }).addTo(this.map)
                                .bindPopup('<strong style="font-size:12px;">Your Location</strong>');
                        } else {
                            this.userMarker.setLatLng(userLatLng);
                        }
                        bounds.extend(userLatLng);
                    }

                    const activeList = (this.nearbyStores.length > 0 && !this.hasSearched) ? this
                        .nearbyStores : this.stores;

                    activeList.forEach((store, idx) => {
                        let lat = parseFloat(store.lat);
                        let lon = parseFloat(store.lon);

                        if (!lat || !lon || isNaN(lat) || isNaN(lon)) {
                            if (this.userLat && this.userLon) {
                                lat = this.userLat + (idx === 0 ? 0.015 : (idx + 1) * 0.02);
                                lon = this.userLon + (idx === 0 ? 0.015 : (idx + 1) * 0.02);
                            } else {
                                lat = 28.6139 + (idx * 0.02);
                                lon = 77.2090 + (idx * 0.02);
                            }
                        }

                        store._mapLat = lat;
                        store._mapLon = lon;

                        const storeIcon = L.divIcon({
                            className: 'custom-store-pin',
                            html: `<div class="w-9 h-9 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg border-2 border-brand-400 font-bold text-xs cursor-pointer hover:scale-110 transition-transform">🏪</div>`,
                            iconSize: [36, 36],
                            iconAnchor: [18, 18]
                        });

                        const storeLatLng = [lat, lon];
                        const marker = L.marker(storeLatLng, {
                            icon: storeIcon
                        }).addTo(this.map);

                        let distStr = '';
                        if (this.userLat && this.userLon) {
                            const dist = this.calculateDistance(this.userLat, this.userLon, lat,
                                lon);
                            if (dist) {
                                distStr =
                                    `<div style="margin-top:4px;font-size:11px;font-weight:bold;color:#059669;">📍 ${dist} away</div>`;
                                store._calcDistance = dist;
                            }
                        }

                        marker.bindPopup(`
                            <div style="font-family:sans-serif;padding:4px;">
                                <div style="font-weight:bold;font-size:13px;color:#0f172a;">${store.store_name}</div>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">${store.city || ''}, ${store.state || ''}</div>
                                ${distStr}
                            </div>
                        `);

                        marker.on('click', () => {
                            this.selectStoreOnMap(store, lat, lon);
                        });

                        this.storeMarkers.push(marker);
                        bounds.extend(storeLatLng);

                        if (idx === 0) {
                            this.selectStoreOnMap(store, lat, lon);
                        }
                    });

                    if (bounds.isValid()) {
                        this.map.fitBounds(bounds, {
                            padding: [40, 40],
                            maxZoom: 14
                        });
                    }
                },

                selectStoreOnMap(store, lat, lon) {
                    this.selectedStoreObj = store;
                    this.selectedStoreName = store.store_name;
                    const targetLat = lat || store._mapLat;
                    const targetLon = lon || store._mapLon;

                    if (this.userLat && this.userLon && targetLat && targetLon) {
                        this.selectedStoreDistance = this.calculateDistance(this.userLat, this.userLon,
                            targetLat, targetLon);
                        if (this.map && typeof L !== 'undefined') {
                            if (this.routeLine) this.map.removeLayer(this.routeLine);
                            this.routeLine = L.polyline([
                                [this.userLat, this.userLon],
                                [targetLat, targetLon]
                            ], {
                                color: '#6fb63a',
                                weight: 4,
                                opacity: 0.85,
                                dashArray: '8, 8'
                            }).addTo(this.map);
                        }
                    }
                },

                getDirectionsUrl(store) {
                    if (!store) return '#';
                    const q = encodeURIComponent(
                        `${store.store_name}, ${store.city || ''} ${store.address || ''}`);
                    return `https://www.google.com/maps/search/?api=1&query=${q}`;
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
                        this.initMap();
                        return;
                    }

                    this.nearbyLoading = true;
                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                                const lat = position.coords.latitude;
                                const lon = position.coords.longitude;
                                this.userLat = lat;
                                this.userLon = lon;

                                try {
                                    const response = await fetch(
                                        `<?php echo url()->current(); ?>?lat=${lat}&lon=${lon}`, {
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
                                    this.initMap();
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
                                this.initMap();
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
                        this.initMap();
                        return;
                    }

                    this.isLoading = true;
                    this.hasSearched = true;

                    try {
                        const response = await fetch(
                            `<?php echo url()->current(); ?>?q=${encodeURIComponent(this.query)}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                        if (response.ok) {
                            const data = await response.json();
                            this.stores = data.stores || [];
                            this.initMap();
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
