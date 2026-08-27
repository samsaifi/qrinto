@extends('layouts.quick-flow-pc')

@section('title', 'Find a Store | Qrinto Custom Print Studio')
@section('header_title', 'Find a Store')
@section('meta_description', 'Locate Qrinto print studio stores near you for same-day store pickup.')
@section('canonical_url', route('flow.find-store'))

@push('styles')
    <!-- Leaflet OpenStreetMap CSS CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        .leaflet-container {
            font-family: inherit !important;
            z-index: 10 !important;
            background: #f4f7f4 !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 14px !important;
            padding: 2px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08) !important;
        }

        .custom-store-pin {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-user-pin {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
    <div x-data="storeAutocomplete()" class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[1240px] mx-auto">

            {{-- Title Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">
                    Find a store
                </h1>
                <p class="text-sm text-slate-500 font-medium mt-1.5">
                    Design online, pick up in store.
                </p>
            </div>

            {{-- Search Input & Near Me Toolbar --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-6 max-w-full">
                <div class="flex items-center gap-3 w-full sm:w-auto flex-1 max-w-md">
                    <!-- Search Input Box -->
                    <div
                        class="flex items-center gap-2.5 bg-white border border-slate-200 rounded-xl px-4 py-2.5 w-full shadow-2xs focus-within:border-slate-300 transition-all">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <input type="text" placeholder="City, ZIP, or store name" x-model="query"
                            @input.debounce.300ms="fetchStores" autocomplete="off"
                            class="w-full text-sm text-slate-800 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0 font-normal p-0">
                        <div x-show="isLoading" class="shrink-0" x-cloak>
                            <div class="w-4 h-4 border-2 border-slate-300 border-t-emerald-600 rounded-full animate-spin">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Near Me Button -->
                <button @click="detectLocation()"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 text-sm font-semibold text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 px-4 py-2.5 rounded-xl transition-all shadow-2xs cursor-pointer active:scale-95 shrink-0">
                    <i data-lucide="crosshair" class="w-4 h-4 text-slate-700"></i>
                    <span>Near me</span>
                </button>
            </div>

            {{-- Main 2-Column Section: Store List (Left) & Map (Right) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Left Side: Store Cards List (5 Cols) --}}
                <div class="lg:col-span-5 space-y-3">

                    {{-- Geolocation Loading State --}}
                    <div x-show="nearbyLoading" x-cloak x-transition
                        class="p-4 bg-white border border-slate-200 rounded-2xl flex items-center justify-center gap-3 shadow-2xs">
                        <div class="w-4 h-4 border-2 border-slate-300 border-t-emerald-600 rounded-full animate-spin"></div>
                        <span class="text-xs font-medium text-slate-600">Locating nearest stores...</span>
                    </div>

                    {{-- Store Cards --}}
                    <div class="space-y-3">
                        <template x-for="store in displayStores" :key="store.id">
                            <form action="{{ route('flow.set-store') }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="store_id" :value="store.id">
                                <button type="submit" @mouseenter="selectStoreOnMap(store, store._mapLat, store._mapLon)"
                                    :class="store.id === activeStoreId ? 'border-emerald-500 ring-1 ring-emerald-500/30' :
                                        'border-slate-200/90 hover:border-slate-300'"
                                    class="w-full text-left bg-white border rounded-2xl p-4 sm:p-4.5 flex items-center justify-between transition-all shadow-2xs group cursor-pointer">
                                    <div class="min-w-0 pr-3">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-bold text-slate-900 text-sm sm:text-base group-hover:text-emerald-700 transition-colors truncate"
                                                x-text="store.store_name"></h3>
                                            <span x-show="store.id === activeStoreId"
                                                class="shrink-0 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2 py-0.5">
                                                Your store
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 font-normal mt-0.5 truncate"
                                            x-text="`${store.city || ''}${store.state ? ', ' + store.state : ''}`"></p>
                                    </div>
                                    <div class="flex items-center gap-2.5 shrink-0">
                                        <template x-if="getStoreDistance(store)">
                                            <span class="text-xs text-slate-500 font-mono"
                                                x-text="getStoreDistance(store)"></span>
                                        </template>
                                        <span
                                            class="text-emerald-700 font-bold text-base group-hover:translate-x-1 transition-transform">→</span>
                                    </div>
                                </button>
                            </form>
                        </template>
                    </div>

                    {{-- Empty Search Results State --}}
                    <div x-show="!isLoading && displayStores.length === 0" x-cloak
                        class="text-center py-10 bg-white rounded-2xl border border-slate-200 p-6">
                        <h4 class="text-sm font-bold text-slate-800">No stores found</h4>
                        <p class="text-xs text-slate-500 font-normal mt-1">Try entering a different city, ZIP, or store
                            name.</p>
                    </div>

                </div>

                {{-- Right Side: Clean Map Container (7 Cols) --}}
                <div class="lg:col-span-7 sticky top-6">
                    <div class="bg-[#f4f7f4] border border-slate-200/80 rounded-3xl p-3 shadow-2xs">
                        <div id="store-leaflet-map" class="w-full h-[480px] rounded-2xl overflow-hidden z-10 bg-[#f4f7f4]">
                        </div>
                    </div>
                </div>

            </div>



        </div>
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
                activeStoreId: {{ session('active_store_id') ?? 'null' }},
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
                storeMarkerMap: {},
                searchResults: null,

                get displayStores() {
                    if (this.query && this.query.trim().length > 0) {
                        return this.searchResults !== null ? this.searchResults : this.stores;
                    }
                    if (this.nearbyStores && this.nearbyStores.length > 0) {
                        return this.nearbyStores;
                    }
                    if (this.stores && this.stores.length > 0) {
                        return this.stores;
                    }
                    return [];
                },

                init() {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                    this.initMap();
                    this.detectLocation();
                },

                getStoreDistance(store) {
                    if (store._calcDistance) return store._calcDistance;
                    if (!this.userLat || !this.userLon) return null;
                    const lat = parseFloat(store.lat || store._mapLat);
                    const lon = parseFloat(store.lon || store._mapLon);
                    if (!lat || !lon || isNaN(lat) || isNaN(lon)) return null;
                    const dist = this.calculateDistance(this.userLat, this.userLon, lat, lon);
                    if (dist) store._calcDistance = dist;
                    return dist;
                },

                calculateDistance(lat1, lon1, lat2, lon2) {
                    if (!lat1 || !lon1 || !lat2 || !lon2) return null;
                    const R = 3959; // Earth radius in miles
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    const d = R * c;
                    return `${d.toFixed(1)} mi`;
                },

                initMap() {
                    this.$nextTick(() => {
                        const container = document.getElementById('store-leaflet-map');
                        if (!container || typeof L === 'undefined') return;

                        if (!this.map) {
                            this.map = L.map('store-leaflet-map', {
                                zoomControl: false,
                                attributionControl: false
                            }).setView([38.5000, -96.0000], 4);

                            L.control.zoom({
                                position: 'topright'
                            }).addTo(this.map);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19
                            }).addTo(this.map);
                        }
                        this.updateMapMarkers();
                    });
                },

                updateMapMarkers() {
                    if (!this.map || typeof L === 'undefined') return;

                    this.storeMarkers.forEach(m => this.map.removeLayer(m));
                    this.storeMarkers = [];
                    this.storeMarkerMap = {};

                    const bounds = L.latLngBounds();

                    if (this.userLat && this.userLon) {
                        const userLatLng = [this.userLat, this.userLon];
                        if (!this.userMarker) {
                            const userIcon = L.divIcon({
                                className: 'custom-user-pin',
                                html: `<div class="w-4 h-4 rounded-full bg-slate-900 border-2 border-white shadow-md"></div>`,
                                iconSize: [16, 16],
                                iconAnchor: [8, 8]
                            });
                            this.userMarker = L.marker(userLatLng, {
                                icon: userIcon
                            }).addTo(this.map);
                        } else {
                            this.userMarker.setLatLng(userLatLng);
                        }
                        bounds.extend(userLatLng);
                    }

                    const activeList = this.displayStores;

                    activeList.forEach((store, idx) => {
                        let lat = parseFloat(store.lat);
                        let lon = parseFloat(store.lon);

                        if (!lat || !lon || isNaN(lat) || isNaN(lon)) {
                            if (this.userLat && this.userLon) {
                                lat = this.userLat + (idx * 0.03);
                                lon = this.userLon + (idx * 0.03);
                            } else {
                                lat = 39.5000 + (idx * 1.5);
                                lon = -98.0000 + (idx * 1.5);
                            }
                        }

                        store._mapLat = lat;
                        store._mapLon = lon;

                        if (this.userLat && this.userLon) {
                            const dist = this.calculateDistance(this.userLat, this.userLon, lat,
                                lon);
                            if (dist) store._calcDistance = dist;
                        }

                        const storeIcon = L.divIcon({
                            className: 'custom-store-pin',
                            html: `<div class="w-3.5 h-3.5 rounded-full bg-emerald-600 border-2 border-white shadow-sm hover:scale-125 transition-transform cursor-pointer"></div>`,
                            iconSize: [14, 14],
                            iconAnchor: [7, 7]
                        });

                        const storeLatLng = [lat, lon];
                        const marker = L.marker(storeLatLng, {
                            icon: storeIcon
                        }).addTo(this.map);

                        const distText = store._calcDistance || '';
                        marker.bindPopup(`
                            <div style="font-family:sans-serif;padding:6px 8px;">
                                <div style="font-weight:bold;font-size:13px;color:#0f172a;">${store.store_name}</div>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">${store.city || ''}, ${store.state || ''}</div>
                                ${distText ? '<div style="font-size:11px;color:#287d3c;font-weight:600;margin-top:3px;">📍 ' + distText + ' away</div>' : ''}
                            </div>
                        `);

                        marker.on('click', () => {
                            this.selectStoreOnMap(store, lat, lon);
                        });

                        this.storeMarkers.push(marker);
                        this.storeMarkerMap[store.id] = marker;
                        bounds.extend(storeLatLng);
                    });

                    if (bounds.isValid() && activeList.length > 0) {
                        this.map.fitBounds(bounds, {
                            padding: [50, 50],
                            maxZoom: 13
                        });
                    }
                },

                selectStoreOnMap(store, lat, lon) {
                    this.selectedStoreObj = store;
                    if (this.map && lat && lon) {
                        this.map.panTo([lat, lon]);
                        const marker = this.storeMarkerMap[store.id];
                        if (marker) marker.openPopup();
                    }
                },

                detectLocation() {
                    this.query = '';
                    this.searchResults = null;
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
                                    }
                                } catch (err) {
                                    console.error('Error fetching nearby stores:', err);
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
                                this.nearbyLoading = false;
                                this.geolocationChecked = true;
                                this.initMap();
                            }
                    );
                },

                async fetchStores() {
                    if (!this.query || this.query.trim().length === 0) {
                        this.hasSearched = false;
                        this.searchResults = null;
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
                            this.searchResults = data.stores || [];
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
