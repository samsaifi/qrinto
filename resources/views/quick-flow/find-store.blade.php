@extends('layouts.quick-flow')

@section('title', 'Find a Store')
@section('header_title', 'Find Store')

@section('content')
    <div class="space-y-6 pb-24 px-6 text-center min-h-screen pt-4" x-data="storeAutocomplete()">

        @if (!session()->has('active_store_id'))
            <div
                class="mb-8 p-4 bg-red-50 border-2 border-red-100 rounded-[2rem] flex items-center gap-4 text-left animate-pulse">
                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-red-600 flex-shrink-0">
                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-black text-red-800 uppercase tracking-[0.1em]">Action Required</p>
                    <p class="text-[11px] font-bold text-red-600 leading-tight">You haven't selected a store yet. Please
                        choose a branch below to continue.</p>
                </div>
            </div>
        @else
            @php
                $selectedStore = \App\Models\Store::find(session('active_store_id'));
            @endphp
            @if ($selectedStore)
                <div class="mb-8 p-5  bg-gray-50 border-2  border-gray-100 rounded-[2rem] flex items-center gap-4 text-left">
                    <div
                        class="w-14 h-14  bg-gray-100 rounded-2xl flex items-center justify-center  text-gray-600 flex-shrink-0">
                        <i data-lucide="check-circle" class="w-7 h-7"></i>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-[10px] font-black  text-gray-600 uppercase tracking-widest mb-1">Currently Selected
                            Branch</p>
                        <h3 class="font-black text-slate-900 text-lg leading-tight truncate">
                            {{ $selectedStore->store_name }}</h3>
                        <p class="text-[11px] font-bold text-slate-500 mt-1 flex items-center gap-1">
                            <i data-lucide="navigation" class="w-3 h-3"></i> {{ $selectedStore->city }},
                            {{ $selectedStore->state }} {{ $selectedStore->zip_code }}
                        </p>
                        @if ($selectedStore->phone)
                            <p class="text-[11px] font-bold text-slate-500 flex items-center gap-1 mt-0.5">
                                <i data-lucide="phone" class="w-3 h-3"></i> {{ $selectedStore->phone }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <!-- Nearby Stores Section -->
        <div class="mb-8 text-left bg-white border-2 border-slate-100 p-6 rounded-[2rem] shadow-sm"
            data-tour="nearby-stores" x-show="geolocationChecked || nearbyLoading" x-cloak>
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-mobile-500"></i> Nearby Stores
            </h3>

            <!-- Loading state -->
            <div x-show="nearbyLoading" class="py-4 text-center">
                <div class="w-6 h-6 border-2 border-slate-200 border-t-mobile-500 rounded-full animate-spin mx-auto mb-2">
                </div>
                <p class="text-slate-500 text-xs font-medium">Detecting your location...</p>
            </div>

            <!-- Matching Stores list -->
            <div x-show="!nearbyLoading && nearbyStores.length > 0" class="space-y-3">
                <p class="text-slate-500 text-xs font-semibold mb-2">We found these stores near you:</p>
                <template x-for="store in nearbyStores" :key="store.id">
                    <form action="{{ route('flow.set-store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="store_id" :value="store.id">
                        <button type="submit"
                            class="w-full text-left bg-white border-2 border-slate-100 p-4 rounded-2xl shadow-sm hover:border-mobile-500 hover:shadow-md transition-all group flex items-start gap-4 active:scale-[0.98]">
                            <div
                                class="w-10 h-10 bg-mobile-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-mobile-100 transition-colors">
                                <template x-if="store.logo">
                                    <img :src="'{{ asset('storage') }}/' + store.logo" :alt="store.store_name"
                                        class="w-full h-full object-cover">
                                </template>

                                <template x-if="!store.logo">
                                    <i data-lucide="map-pin" class="w-5 h-5 text-mobile-500"></i>
                                </template>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-900 text-base mb-0.5" x-text="store.store_name"></h4>
                                <p class="text-slate-500 text-xs mb-1 line-clamp-1 flex items-center gap-1">
                                    <i data-lucide="navigation" class="w-3 h-3"></i> <span
                                        x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                                </p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span x-show="store.phone"
                                        class="inline-flex items-center gap-1 bg-slate-50 text-slate-600 text-[10px] font-bold px-2 py-1 rounded-md border border-slate-100">
                                        <i data-lucide="phone" class="w-3 h-3"></i> <span x-text="store.phone"></span>
                                    </span>
                                    <span x-show="store.distance !== undefined && store.distance !== null"
                                        class="inline-flex items-center gap-1 bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-1 rounded-md border border-blue-100">
                                        <i data-lucide="map" class="w-3 h-3"></i> <span
                                            x-text="Number(store.distance).toFixed(1) + ' miles away'"></span>
                                    </span>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-center h-10 w-6 text-slate-300 group-hover:text-mobile-500 transition-colors">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </div>
                        </button>
                    </form>
                </template>
            </div>

            <!-- No Matching Stores or Error state -->
            <div x-show="!nearbyLoading && nearbyError && nearbyStores.length === 0" class="py-2 text-center">
                <p class="text-amber-600 font-bold text-xs flex items-center justify-center gap-1.5">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Nearby stores are not available. Please use the
                    search box to find a store.
                </p>
            </div>
        </div>

        <!-- Store Icon -->
        <div
            class="w-16 h-16 bg-mobile-100 rounded-full flex items-center justify-center shadow-lg shadow-mobile-100 mb-6 mx-auto">
            <i data-lucide="store" class="w-8 h-8 text-mobile-600"></i>
        </div>

        <h1 class="text-2xl font-black text-slate-900 mb-2">Find Your Store</h1>
        <p class="text-slate-500 text-sm mb-6 px-4">Search by name, city, zip code, or any store details to select your
            printing location.</p>

        <!-- Search Form (No full page reload needed, intercepts enter) -->
        <form action="{{ url()->current() }}" method="GET" class="w-full space-y-4 text-left"
            @submit.prevent="fetchStores">
            <div class="relative">
                <i data-lucide="search" class="absolute left-4 top-[18px] w-5 h-5 text-slate-400"></i>
                <input type="text" name="q" placeholder="Type to search stores..." data-tour="store-search"
                    x-model="query" @input.debounce.300ms="fetchStores" autocomplete="off"
                    class="w-full bg-white border-2 border-slate-200 rounded-2xl py-4 pl-12 pr-4 text-slate-900 font-bold focus:border-mobile-500 focus:ring-0 transition-all outline-none shadow-sm">
                <div x-show="isLoading" class="absolute right-4 top-[18px]">
                    <div class="w-5 h-5 border-2 border-slate-200 border-t-mobile-500 rounded-full animate-spin"></div>
                </div>
            </div>
        </form>

        <!-- How it Works (Instructions) -->
        <div x-show="!hasSearched && stores.length === 0" x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
            class="mt-12 text-left">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 text-center">Customer Guide:
                How to Order</h3>

            <div class="grid grid-cols-1 gap-4">
                <!-- Step 1 -->
                <div
                    class="bg-white p-5 rounded-[24px] border border-slate-100 shadow-sm flex gap-4 items-center group hover:border-mobile-200 transition-all hover:shadow-md">
                    <div
                        class="w-12 h-12 bg-mobile-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-mobile-600 group-hover:bg-mobile-100 transition-colors">
                        <i data-lucide="search" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm mb-0.5">1. Select a Store near you</h4>
                        <p class="text-slate-500 text-xs leading-tight font-medium">Search and pick the nearest Qrinto
                            branch to see their services.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div
                    class="bg-white p-5 rounded-[24px] border border-slate-100 shadow-sm flex gap-4 items-center group hover:border-mobile-200 transition-all hover:shadow-md">
                    <div
                        class="w-12 h-12 bg-mobile-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-mobile-600 group-hover:bg-mobile-100 transition-colors">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm mb-0.5">2. Choose Products</h4>
                        <p class="text-slate-500 text-xs leading-tight font-medium">Browse through categories like Photo
                            Prints, Gifts, or Cards.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div
                    class="bg-white p-5 rounded-[24px] border border-slate-100 shadow-sm flex gap-4 items-center group hover:border-mobile-200 transition-all hover:shadow-md">
                    <div
                        class="w-12 h-12 bg-mobile-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-mobile-600 group-hover:bg-mobile-100 transition-colors">
                        <i data-lucide="image-plus" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm mb-0.5">3. Design & Upload</h4>
                        <p class="text-slate-500 text-xs leading-tight font-medium">Upload your photos and use our editor
                            to customize your design.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div
                    class="bg-white p-5 rounded-[24px] border border-slate-100 shadow-sm flex gap-4 items-center group hover:border-mobile-200 transition-all hover:shadow-md">
                    <div
                        class="w-12 h-12 bg-mobile-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-mobile-600 group-hover:bg-mobile-100 transition-colors">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm mb-0.5">4. Order & Collect</h4>
                        <p class="text-slate-500 text-xs leading-tight font-medium">Checkout securely and collect your
                            order from your chosen store.</p>
                    </div>
                </div>
            </div>

            <!-- Help Note -->
            <div class="mt-8 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Need Assistance?</p>
                <p class="text-xs text-slate-500 mt-1">Our support team is ready to help you with your order.</p>
            </div>
        </div>

        <!-- Autocomplete Results -->
        <div x-show="hasSearched || stores.length > 0" x-cloak class="mt-8 text-left transition-all">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">
                Search Results</h3>

            <div x-show="!isLoading && stores.length === 0 && query.length > 0"
                class="py-8 text-center bg-slate-50 rounded-2xl border-2 border-slate-100 border-dashed">
                <i data-lucide="map-pin-off" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                <p class="text-slate-500 font-medium text-sm">No stores found matching "<span x-text="query"></span>"</p>
                <p class="text-slate-400 text-xs mt-1">Try searching by city or zip code.</p>
            </div>

            <div x-show="stores.length > 0" class="space-y-3">
                <template x-for="store in stores" :key="store.id">
                    <form action="{{ route('flow.set-store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="store_id" :value="store.id">
                        <button type="submit"
                            class="w-full text-left bg-white border-2 border-slate-100 p-4 rounded-2xl shadow-sm hover:border-mobile-500 hover:shadow-md transition-all group flex items-start gap-4 active:scale-[0.98]">
                            <div
                                class="w-10 h-10 bg-mobile-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden group-hover:bg-mobile-100 transition-colors">

                                <template x-if="store.logo">
                                    <img :src="'{{ asset('storage') }}/' + store.logo" :alt="store.store_name"
                                        class="w-full h-full object-cover">
                                </template>

                                <template x-if="!store.logo">
                                    <i data-lucide="map-pin" class="w-5 h-5 text-mobile-500"></i>
                                </template>

                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-900 text-base mb-0.5" x-text="store.store_name"></h4>
                                <p class="text-slate-500 text-xs mb-1 line-clamp-1 flex items-center gap-1">
                                    <i data-lucide="navigation" class="w-3 h-3"></i> <span
                                        x-text="`${store.city || ''}, ${store.state || ''} ${store.zip_code || ''}`"></span>
                                </p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span x-show="store.phone"
                                        class="inline-flex items-center gap-1 bg-slate-50 text-slate-600 text-[10px] font-bold px-2 py-1 rounded-md border border-slate-100">
                                        <i data-lucide="phone" class="w-3 h-3"></i> <span x-text="store.phone"></span>
                                    </span>
                                    <span x-show="store.distance !== undefined && store.distance !== null"
                                        class="inline-flex items-center gap-1 bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-1 rounded-md border border-blue-100">
                                        <i data-lucide="map" class="w-3 h-3"></i> <span
                                            x-text="Number(store.distance).toFixed(1) + ' miles away'"></span>
                                    </span>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-center h-10 w-6 text-slate-300 group-hover:text-mobile-500 transition-colors">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </div>
                        </button>
                    </form>
                </template>
            </div>
        </div>


    </div>
@endsection

@push('scripts')
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
