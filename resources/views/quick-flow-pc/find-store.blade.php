@extends('layouts.quick-flow-pc')

@section('title', 'Find a Store | Qrinto Custom Print Studio')
@section('header_title', 'Find a Store')
@section('meta_description', 'Locate Qrinto print studio stores near you for same-day store pickup.')
@section('canonical_url', route('flow.find-store'))

@push('styles')
    <!-- Leaflet OpenStreetMap CSS CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- MapLibre GL (renders the free OpenFreeMap vector basemap inside Leaflet) -->
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" />

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
    <div x-data="storeAutocomplete()" class="w-full bg-[#f4f6f3] min-h-screen py-8 md:py-10 px-4 sm:px-6 lg:px-14">
        <div class="max-w-[1100px] mx-auto">

            {{-- Title Header --}}
            <div class="mb-6">
                <h1 class="font-display text-3xl md:text-4xl font-bold text-[#16211a] tracking-tight">Find a store</h1>
                <p class="text-sm md:text-base text-slate-600 mt-2">
                    Design online, then pick up your prints at a store near you.
                </p>
            </div>

            {{-- Search Input & Near Me --}}
            <div class="flex items-stretch gap-2 mb-5">
                <label
                    class="flex-1 flex items-center gap-3 bg-white border border-slate-300/80 rounded-xl px-4 h-12 focus-within:border-[#287d3c] focus-within:ring-2 focus-within:ring-[#287d3c]/15 transition">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500 shrink-0"></i>
                    <input type="text" placeholder="City, PIN code or store name" x-model="query"
                        @input.debounce.300ms="fetchStores" autocomplete="off"
                        class="w-full text-sm text-slate-800 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0 p-0">
                    <span x-show="isLoading" class="shrink-0" x-cloak>
                        <span class="block w-4 h-4 border-2 border-slate-300 border-t-[#287d3c] rounded-full animate-spin"></span>
                    </span>
                </label>

                <button type="button" @click="detectLocation()"
                    class="shrink-0 flex items-center justify-center gap-2 h-12 px-4 text-sm font-semibold text-slate-900 bg-white hover:bg-slate-50 border border-slate-300/80 rounded-xl transition active:scale-95 cursor-pointer">
                    <i data-lucide="locate-fixed" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Near me</span>
                </button>
            </div>

            {{-- Store list (left) & map (right) --}}
            <div class="grid grid-cols-1 lg:grid-cols-[330px_1fr] gap-4 items-start">

                <div class="order-2 lg:order-1">
                    <p class="text-xs font-semibold text-slate-600 mb-2"
                        x-show="!nearbyLoading && displayStores.length > 0"
                        x-text="displayStores.length + (displayStores.length === 1 ? ' store' : ' stores')" x-cloak></p>

                    {{-- Geolocation Loading State --}}
                    <div x-show="nearbyLoading" x-cloak
                        class="p-4 bg-white border border-slate-200 rounded-2xl flex items-center justify-center gap-3">
                        <span class="block w-4 h-4 border-2 border-slate-300 border-t-[#287d3c] rounded-full animate-spin"></span>
                        <span class="text-xs font-medium text-slate-600">Locating nearest stores...</span>
                    </div>

                    {{-- Store Cards --}}
                    <div class="space-y-3 lg:max-h-[420px] lg:overflow-y-auto lg:pr-1">
                        <template x-for="store in displayStores" :key="store.id">
                            <div @mouseenter="selectStoreOnMap(store, store._mapLat, store._mapLon)"
                                @click="selectStoreOnMap(store, store._mapLat, store._mapLon)"
                                :class="isHighlighted(store) ? 'border-[#287d3c] ring-1 ring-[#287d3c]/20' :
                                    'border-slate-200 hover:border-slate-300'"
                                class="bg-white border-2 rounded-2xl p-3.5 transition cursor-pointer">
                                <div class="flex items-start gap-3">
                                    <span
                                        class="w-9 h-9 shrink-0 rounded-lg bg-[#287d3c] text-white flex items-center justify-center">
                                        <i data-lucide="store" class="w-4 h-4"></i>
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="font-bold text-[15px] text-slate-900 leading-tight truncate"
                                                x-text="store.store_name"></h3>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5 truncate"
                                            x-text="`${store.city || ''}${store.state ? ', ' + store.state : ''}`"></p>
                                        <span x-show="store.id === activeStoreId"
                                            class="inline-block mt-1 text-[10px] font-bold text-[#16532a] bg-[#e7f2e8] rounded-full px-2 py-0.5">
                                            Your store
                                        </span>
                                    </div>
                                    <template x-if="getStoreDistance(store)">
                                        <div class="shrink-0 text-right leading-tight">
                                            <span class="block text-xs font-bold text-slate-900"
                                                x-text="getStoreDistance(store)"></span>
                                            <span class="block text-[11px] text-slate-500">away</span>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-3 grid grid-cols-[1fr_auto] gap-2">
                                    <form action="{{ route('flow.set-store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="store_id" :value="store.id">
                                        <button type="submit" @click.stop
                                            class="w-full h-10 rounded-lg bg-[#287d3c] hover:bg-[#1f6630] text-white text-sm font-semibold transition active:scale-[0.98]">
                                            Pick up here
                                        </button>
                                    </form>
                                    <a :href="directionsUrl(store)" target="_blank" rel="noopener" @click.stop
                                        class="h-10 px-3.5 inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-sm font-semibold text-slate-900 transition">
                                        <i data-lucide="navigation" class="w-4 h-4"></i>
                                        Directions
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Empty Search Results State --}}
                    <div x-show="!isLoading && !nearbyLoading && displayStores.length === 0" x-cloak
                        class="text-center py-10 bg-white rounded-2xl border border-slate-200 p-6">
                        <i data-lucide="map-pin-off" class="w-6 h-6 mx-auto text-slate-300 mb-2"></i>
                        <h4 class="text-sm font-bold text-slate-800">No stores found</h4>
                        <p class="text-xs text-slate-500 mt-1">Try a different city, PIN code or store name.</p>
                    </div>
                </div>

                {{-- Map --}}
                <div class="order-1 lg:order-2 lg:sticky lg:top-24 relative">
                    <div id="store-leaflet-map"
                        class="w-full h-[300px] lg:h-[420px] rounded-3xl overflow-hidden border border-slate-200 bg-[#eef1ec]">
                    </div>

                    {{-- Zoom controls --}}
                    <div
                        class="absolute top-3 right-3 z-[400] flex flex-col bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <button type="button" @click="map && map.zoomIn()" aria-label="Zoom in"
                            class="w-9 h-9 flex items-center justify-center text-slate-700 hover:bg-slate-50">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                        <span class="h-px bg-slate-200"></span>
                        <button type="button" @click="map && map.zoomOut()" aria-label="Zoom out"
                            class="w-9 h-9 flex items-center justify-center text-slate-700 hover:bg-slate-50">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                    </div>

                    {{-- Re-center --}}
                    <button type="button" @click="recenter()"
                        class="absolute bottom-3 left-3 z-[400] inline-flex items-center gap-2 h-9 px-3.5 rounded-full bg-white border border-slate-200 shadow-sm text-xs font-semibold text-slate-800 hover:bg-slate-50 transition">
                        <i data-lucide="locate" class="w-3.5 h-3.5"></i>
                        Re-center
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Leaflet OpenStreetMap JS CDN -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
    <script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet@0.0.22/leaflet-maplibre-gl.js"></script>
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
                selectedStoreId: null,

                isHighlighted(store) {
                    const id = this.selectedStoreId ?? this.activeStoreId ?? this.displayStores[0]?.id;
                    return store.id === id;
                },

                directionsUrl(store) {
                    const lat = parseFloat(store.lat), lon = parseFloat(store.lon);
                    const dest = (lat && lon) ? `${lat},${lon}` : [store.address, store.city, store.state, store.zip_code]
                        .filter(Boolean).join(', ');
                    return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(dest)}`;
                },

                recenter() {
                    this.selectedStoreId = null;
                    this.updateMapMarkers();
                },

                refreshIcons() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

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

                            // Map data licence requires visible credit.
                            L.control.attribution({ prefix: false, position: 'bottomright' }).addTo(this.map);

                            // Free, keyless OpenFreeMap basemap (light "liberty" style).
                            // Falls back to plain OSM raster tiles when WebGL or the
                            // MapLibre plugin is unavailable.
                            const canUseGl = typeof L.maplibreGL === 'function' &&
                                typeof maplibregl !== 'undefined' && maplibregl.supported?.() !== false;
                            if (canUseGl) {
                                L.maplibreGL({
                                    style: 'https://tiles.openfreemap.org/styles/liberty',
                                    attribution: '&copy; OpenFreeMap &copy; OpenStreetMap contributors'
                                }).addTo(this.map);
                            } else {
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19
                                }).addTo(this.map);
                            }
                        }
                        this.updateMapMarkers();
                        this.refreshIcons();
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
                            html: `<svg width="36" height="46" viewBox="0 0 36 46" class="drop-shadow-md cursor-pointer">
                                    <path d="M18 45C18 45 34 29.5 34 18A16 16 0 0 0 2 18C2 29.5 18 45 18 45Z" fill="#287d3c" stroke="#fff" stroke-width="2"/>
                                    <circle cx="18" cy="18" r="6" fill="#fff"/>
                                </svg>`,
                            iconSize: [36, 46],
                            iconAnchor: [18, 45],
                            popupAnchor: [0, -42]
                        });

                        const storeLatLng = [lat, lon];
                        const marker = L.marker(storeLatLng, {
                            icon: storeIcon
                        }).addTo(this.map);

                        const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({
                            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                        }[c]));
                        const distText = store._calcDistance || '';
                        const place = [store.city, store.state].filter(Boolean).join(', ');
                        marker.bindPopup(`
                            <div class="px-1 py-0.5">
                                <div class="font-bold text-[13px] text-slate-900">${esc(store.store_name)}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">${esc(place)}${distText ? ' · ' + esc(distText) : ''}</div>
                            </div>
                        `, { closeButton: false, autoPan: false });

                        marker.on('click', () => {
                            this.selectStoreOnMap(store, lat, lon);
                        });

                        this.storeMarkers.push(marker);
                        this.storeMarkerMap[store.id] = marker;
                        bounds.extend(storeLatLng);
                    });

                    if (bounds.isValid() && activeList.length > 0) {
                        // The container can be measured before layout settles;
                        // a stale 0-size map makes fitBounds zoom out to the world.
                        this.map.invalidateSize();
                        this.map.fitBounds(bounds, {
                            padding: [60, 60],
                            maxZoom: 14
                        });
                        // Mirror the list highlight with an open label on the map.
                        const first = this.storeMarkerMap[this.selectedStoreId ?? this.activeStoreId] ||
                            this.storeMarkerMap[activeList[0].id];
                        if (first) first.openPopup();
                    }
                },

                selectStoreOnMap(store, lat, lon) {
                    this.selectedStoreId = store.id;
                    if (this.map && lat && lon) {
                        this.map.panTo([lat, lon]);
                        const marker = this.storeMarkerMap[store.id];
                        if (marker) marker.openPopup();
                    }
                },

                async detectLocation() {
                    this.query = '';
                    this.searchResults = null;

                    // Primary: IP-based location (respects VPN). This resolves the
                    // visitor's location from their IP via the server (ipwho.is).
                    this.nearbyLoading = true;
                    await this.detectLocationByIp();
                    this.nearbyLoading = false;
                    this.geolocationChecked = true;
                    this.initMap();
                    return;

                    // eslint-disable-next-line no-unreachable
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
                            async (error) => {
                                console.error('Geolocation error:', error);
                                // Fallback: estimate location from the visitor's IP.
                                await this.detectLocationByIp();
                                this.nearbyLoading = false;
                                this.geolocationChecked = true;
                                this.initMap();
                            }
                    );
                },

                async detectLocationByIp() {
                    try {
                        // Resolve location from the browser's own connection so a
                        // browser-level VPN is respected. ipwho.is needs no API key.
                        let lat = null, lon = null;
                        try {
                            const ipRes = await fetch('https://ipwho.is/');
                            if (ipRes.ok) {
                                const ipData = await ipRes.json();
                                if (ipData.success && ipData.latitude && ipData.longitude) {
                                    lat = ipData.latitude;
                                    lon = ipData.longitude;
                                }
                            }
                        } catch (e) {
                            console.error('ipwho.is lookup failed:', e);
                        }

                        // Build the nearby-stores request. If the client-side IP
                        // lookup worked, pass the coords; otherwise let the server
                        // fall back to its own IP-based lookup.
                        const url = (lat && lon)
                            ? `<?php echo url()->current(); ?>?lat=${lat}&lon=${lon}`
                            : `<?php echo url()->current(); ?>?ip_locate=1`;

                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.nearbyStores = data.stores || [];
                            this.userLat = lat || data.lat || null;
                            this.userLon = lon || data.lon || null;
                        }
                    } catch (err) {
                        console.error('Error fetching IP-based stores:', err);
                        this.nearbyError = true;
                    }
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
