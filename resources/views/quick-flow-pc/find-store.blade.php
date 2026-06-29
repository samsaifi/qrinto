@extends('layouts.quick-flow-pc')

@section('title', 'Find a Store')
@section('header_title', 'Find Store')

@section('content')
<div class="pb-24 pt-8" x-data="storeAutocomplete()">

    <!-- Header & Search Container -->
    <div class="max-w-3xl mx-auto text-center mb-16">
        <!-- Store Icon -->
        <div class="w-20 h-20 bg-brand-100 rounded-full flex items-center justify-center shadow-lg shadow-brand-100 mb-8 mx-auto">
            <i data-lucide="store" class="w-10 h-10 text-brand-600"></i>
        </div>

        <h1 class="text-4xl font-black text-slate-900 mb-4 font-display">Find Your Store</h1>
        <p class="text-slate-500 text-lg mb-8">Search by name, city, zip code, or any store details to select your printing location.</p>

        @if(!session()->has('active_store_id'))
        <div class="mb-10 p-6 bg-red-50 border-2 border-red-100 rounded-3xl flex items-center gap-5 text-left animate-pulse">
            <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center text-red-600 flex-shrink-0">
                <i data-lucide="alert-circle" class="w-7 h-7"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-black text-red-800 uppercase tracking-[0.1em]">Action Required</p>
                <p class="text-sm font-bold text-red-600 mt-1">You haven't selected a store yet. Please choose a branch below to continue.</p>
            </div>
        </div>
        @else
            @php
                $selectedStore = \App\Models\Store::find(session('active_store_id'));
            @endphp
            @if($selectedStore)
            <div class="mb-10 p-6 bg-emerald-50 border-2 border-emerald-100 rounded-3xl flex items-center gap-5 text-left shadow-sm">
                <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 flex-shrink-0">
                    <i data-lucide="check-circle" class="w-8 h-8"></i>
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-xs font-black text-emerald-600 uppercase tracking-widest mb-1.5">Currently Selected Branch</p>
                    <h3 class="font-black text-slate-900 text-xl leading-tight truncate">{{ $selectedStore->store_name }}</h3>
                    <div class="flex items-center gap-6 mt-2">
                        <p class="text-sm font-bold text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="navigation" class="w-4 h-4"></i> {{ $selectedStore->city }}, {{ $selectedStore->state }} {{ $selectedStore->zip_code }}
                        </p>
                        @if($selectedStore->phone)
                        <p class="text-sm font-bold text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="phone" class="w-4 h-4"></i> {{ $selectedStore->phone }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        @endif

        <!-- Nearby Stores Section -->
        <div class="mb-12 text-left bg-white border-2 border-slate-100 p-8 rounded-[2rem] shadow-sm max-w-3xl mx-auto" x-show="geolocationChecked || nearbyLoading" x-cloak>
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-brand-500"></i> Nearby Stores
            </h3>

            <!-- Loading state -->
            <div x-show="nearbyLoading" class="py-6 text-center">
                <div class="w-8 h-8 border-2 border-slate-200 border-t-brand-500 rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-slate-500 text-sm font-medium">Detecting your location...</p>
            </div>

            <!-- Matching Stores list -->
            <div x-show="!nearbyLoading && nearbyStores.length > 0" class="space-y-4">
                <p class="text-slate-500 text-sm font-semibold mb-3">We found these stores near you:</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <template x-for="store in nearbyStores" :key="store.id">
                        <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                            @csrf
                            <input type="hidden" name="store_id" :value="store.id">
                            <button type="submit" class="w-full h-full text-left bg-white border-2 border-slate-100 p-5 rounded-2xl shadow-sm hover:border-brand-500 hover:shadow-md transition-all group flex flex-col justify-between active:scale-[0.98]">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-brand-100 transition-colors">
                                        <i data-lucide="map-pin" class="w-5 h-5 text-brand-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-slate-900 text-sm mb-1 line-clamp-2" x-text="store.store_name"></h4>
                                    </div>
                                    <div class="flex items-center justify-center h-10 w-4 text-slate-300 group-hover:text-brand-500 transition-colors">
                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </div>
                                </div>
                                <div class="mt-auto">
                                    <p class="text-slate-500 text-[11px] mb-0 flex items-start gap-1">
                                        <i data-lucide="navigation" class="w-3 h-3 mt-0.5 flex-shrink-0"></i> 
                                        <span x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                                    </p>
                                </div>
                            </button>
                        </form>
                    </template>
                </div>
            </div>

            <!-- No Matching Stores or Error state -->
            <div x-show="!nearbyLoading && nearbyError && nearbyStores.length === 0" class="py-4 text-center">
                <p class="text-amber-600 font-bold text-sm flex items-center justify-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i> Nearby stores are not available. Please use the search box to find a store.
                </p>
            </div>
        </div>

        <!-- Search Form -->
        <form action="{{ url()->current() }}" method="GET" class="w-full text-left relative shadow-xl shadow-slate-200/50 rounded-3xl" @submit.prevent="fetchStores">
            <i data-lucide="search" class="absolute left-6 top-6 w-6 h-6 text-slate-400"></i>
            <input type="text" name="q" placeholder="Type to search stores (e.g., New York, 10001)..."
                x-model="query" @input.debounce.300ms="fetchStores" autocomplete="off"
                class="w-full bg-white border-2 border-slate-200 rounded-3xl py-6 pl-16 pr-6 text-slate-900 text-lg font-bold focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 transition-all outline-none">
            <div x-show="isLoading" class="absolute right-6 top-6">
                <div class="w-6 h-6 border-2 border-slate-200 border-t-brand-500 rounded-full animate-spin"></div>
            </div>
        </form>
    </div>

    <!-- How it Works (Instructions) -->
    <div x-show="!hasSearched && stores.length === 0"
        x-transition:enter="transition ease-out duration-700"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="text-left mt-8">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-8 text-center">Customer Guide: How to Order</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Step 1 -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col gap-4 group hover:border-brand-200 transition-all hover:shadow-xl hover:-translate-y-1">
                <div class="w-16 h-16 bg-brand-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-brand-600 group-hover:bg-brand-100 transition-colors">
                    <i data-lucide="search" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-lg mb-2">1. Select a Store</h4>
                    <p class="text-slate-500 text-sm font-medium">Search and pick the nearest Qrinto branch to see their services.</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col gap-4 group hover:border-brand-200 transition-all hover:shadow-xl hover:-translate-y-1">
                <div class="w-16 h-16 bg-brand-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-brand-600 group-hover:bg-brand-100 transition-colors">
                    <i data-lucide="layers" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-lg mb-2">2. Choose Products</h4>
                    <p class="text-slate-500 text-sm font-medium">Browse through categories like Photo Prints, Gifts, or Cards.</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col gap-4 group hover:border-brand-200 transition-all hover:shadow-xl hover:-translate-y-1">
                <div class="w-16 h-16 bg-brand-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-brand-600 group-hover:bg-brand-100 transition-colors">
                    <i data-lucide="image-plus" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-lg mb-2">3. Design & Upload</h4>
                    <p class="text-slate-500 text-sm font-medium">Upload your photos and use our editor to customize your design.</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col gap-4 group hover:border-brand-200 transition-all hover:shadow-xl hover:-translate-y-1">
                <div class="w-16 h-16 bg-brand-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-brand-600 group-hover:bg-brand-100 transition-colors">
                    <i data-lucide="check-circle" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 text-lg mb-2">4. Order & Collect</h4>
                    <p class="text-slate-500 text-sm font-medium">Checkout securely and collect your order from your chosen store.</p>
                </div>
            </div>
        </div>

        <!-- Help Note -->
        <div class="mt-12 p-6 bg-slate-50 rounded-3xl border border-dashed border-slate-200 text-center max-w-2xl mx-auto">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Need Assistance?</p>
            <p class="text-sm text-slate-500 mt-2 font-medium">Our support team is ready to help you with your order.</p>
        </div>
    </div>

    <!-- Autocomplete Results -->
    <div x-show="hasSearched || stores.length > 0" x-cloak class="mt-8 text-left transition-all">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-100 pb-4">Search Results</h3>

        <div x-show="!isLoading && stores.length === 0 && query.length > 0" class="py-16 text-center bg-slate-50 rounded-3xl border-2 border-slate-100 border-dashed max-w-3xl mx-auto">
            <i data-lucide="map-pin-off" class="w-12 h-12 text-slate-300 mx-auto mb-4"></i>
            <p class="text-slate-500 font-bold text-lg">No stores found matching "<span x-text="query" class="text-slate-900"></span>"</p>
            <p class="text-slate-400 text-sm mt-2">Try searching by city or zip code.</p>
        </div>

        <div x-show="stores.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="store in stores" :key="store.id">
                <form action="{{ route('flow-pc.set-store') }}" method="POST" class="h-full">
                    @csrf
                    <input type="hidden" name="store_id" :value="store.id">
                    <button type="submit" class="w-full h-full text-left bg-white border-2 border-slate-100 p-6 rounded-3xl shadow-sm hover:border-brand-500 hover:shadow-xl transition-all group flex flex-col gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-brand-100 group-hover:scale-110 transition-all">
                                <i data-lucide="map-pin" class="w-6 h-6 text-brand-500"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-slate-900 text-lg mb-1 line-clamp-2" x-text="store.store_name"></h4>
                            </div>
                            <div class="flex items-center justify-center h-12 w-6 text-slate-300 group-hover:text-brand-500 group-hover:translate-x-1 transition-all">
                                <i data-lucide="chevron-right" class="w-6 h-6"></i>
                            </div>
                        </div>
                        
                        <div class="mt-auto">
                            <p class="text-slate-500 text-sm mb-3 flex items-start gap-2">
                                <i data-lucide="navigation" class="w-4 h-4 mt-0.5 flex-shrink-0"></i> 
                                <span x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                            </p>
                            <div class="flex flex-wrap gap-2" x-show="store.phone">
                                <span class="inline-flex items-center gap-1.5 bg-slate-50 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-lg border border-slate-100">
                                    <i data-lucide="phone" class="w-3.5 h-3.5"></i> <span x-text="store.phone"></span>
                                </span>
                            </div>
                        </div>
                    </button>
                </form>
            </template>
        </div>
    </div>

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

                        // Re-initialize lucide icons for new DOM elements
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