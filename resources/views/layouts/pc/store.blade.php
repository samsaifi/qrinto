@php
    $activeStore = session()->has('active_store_id')
        ? \App\Models\Store::find(session('active_store_id'))
        : null;
@endphp

<div x-data="headerStoreSelector()" class="relative flex items-center shrink-0 group">
    
    {{-- Location Icon Button (ONLY ICON IN HEADER) --}}
    <button type="button" @click="isOpen = !isOpen"
        class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/90 text-slate-800 transition-all cursor-pointer active:scale-95 relative shadow-2xs"
        title="Store Location">
        <i data-lucide="map-pin" class="w-5 h-5 text-slate-700"></i>

        {{-- Top Right Dot Badge --}}
        <template x-if="storeId">
            <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
        </template>
        <template x-if="!storeId">
            <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
        </template>
    </button>

    {{-- Absolute Tooltip Dropdown Below Icon --}}
    <div x-show="isOpen || isHovered" @mouseenter="isHovered = true" @mouseleave="isHovered = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute top-full right-0 mt-2.5 z-50 w-72 sm:w-80 pointer-events-auto shadow-2xl rounded-2xl bg-white border border-slate-200/90 overflow-hidden"
        style="display: none;"
        @click.outside="isOpen = false">

        {{-- Tooltip Pointer Arrow --}}
        <div class="absolute -top-1.5 right-4 w-3 h-3 bg-slate-900 rotate-45 z-20"></div>

        {{-- CASE A: STORE IS ALREADY SET IN SESSION --}}
        <template x-if="storeId">
            <div class="p-4 bg-slate-900 text-white space-y-3 relative z-10">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-emerald-400 tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Active Pickup Store
                    </span>
                    <a href="{{ route('flow-pc.find-store') }}" class="text-[11px] font-bold text-brand-400 hover:underline">Change</a>
                </div>
                <div>
                    <h4 class="font-black text-white text-base leading-tight" x-text="storeName"></h4>
                    <p class="text-xs text-slate-400 font-medium mt-1" x-text="storeAddress"></p>
                </div>
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Ready for instant pickup</span>
                    <a href="{{ route('flow-pc.find-store') }}" class="font-extrabold text-white hover:text-brand-400 transition-colors flex items-center gap-1">
                        Select another store <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </template>

        {{-- CASE B: STORE NOT SET - AUTOMATIC NEAREST SUGGESTION TOOLTIP --}}
        <template x-if="!storeId">
            <div class="p-4 bg-white text-slate-900 space-y-3 relative z-10">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-brand-700 bg-brand-50 border border-brand-200/80 px-2 py-0.5 rounded-md tracking-wider flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-3 h-3 text-brand-600"></i> We picked for you
                    </span>
                    <template x-if="suggestedStore && suggestedStore.distance">
                        <span class="text-[10px] font-extrabold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full"
                            x-text="`${parseFloat(suggestedStore.distance).toFixed(1)} miles away`"></span>
                    </template>
                </div>

                {{-- Loading State --}}
                <template x-if="isLoading">
                    <div class="py-4 text-center space-y-2">
                        <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-brand-600 mx-auto"></i>
                        <p class="text-xs font-bold text-slate-500">Detecting nearest store...</p>
                    </div>
                </template>

                {{-- Suggested Store Box --}}
                <template x-if="!isLoading && suggestedStore">
                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-brand-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="store" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-900 text-sm leading-tight" x-text="suggestedStore.store_name"></h4>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5"
                                        x-text="[suggestedStore.address, suggestedStore.city, suggestedStore.state].filter(Boolean).join(', ')"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons inside Tooltip --}}
                        <div class="space-y-2">
                            <button type="button" @click="confirmSuggestedStore()"
                                class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-hover text-white text-xs font-black shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Pick This Store for Print</span>
                            </button>

                            <a href="{{ route('flow-pc.find-store') }}"
                                class="w-full py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all flex items-center justify-center gap-1 cursor-pointer">
                                <span>Choose Another Store</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </template>

                {{-- Fallback if no store suggested --}}
                <template x-if="!isLoading && !suggestedStore">
                    <div class="py-3 text-center space-y-3">
                        <p class="text-xs font-bold text-slate-600">Please choose a store location for your order</p>
                        <a href="{{ route('flow-pc.find-store') }}"
                            class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-hover text-white text-xs font-black shadow-md transition-all flex items-center justify-center gap-1">
                            <span>Browse All Stores</span>
                        </a>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>

<script>
    function headerStoreSelector() {
        return {
            storeId: {{ session()->has('active_store_id') ? session('active_store_id') : 'null' }},
            storeName: '{{ $activeStore->store_name ?? "" }}',
            storeAddress: '{{ implode(", ", array_filter([$activeStore->city ?? "", $activeStore->state ?? ""])) }}',
            suggestedStore: null,
            isLoading: false,
            isOpen: false,
            isHovered: false,

            init() {
                if (!this.storeId) {
                    // Open tooltip automatically on page load to prompt user to choose/confirm nearest store
                    this.isOpen = true;
                    this.detectNearestStore();
                } else {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            },

            detectNearestStore() {
                this.isLoading = true;

                const fetchStore = async (lat = null, lon = null) => {
                    let url = '{{ route("flow-pc.nearest-store") }}';
                    if (lat && lon) {
                        url += `?lat=${lat}&lon=${lon}`;
                    }
                    try {
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && data.store) {
                                this.suggestedStore = data.store;
                            }
                        }
                    } catch (e) {
                        console.error("Header store suggestion error:", e);
                    } finally {
                        this.isLoading = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                };

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => fetchStore(pos.coords.latitude, pos.coords.longitude),
                        () => fetchStore(),
                        { timeout: 5000 }
                    );
                } else {
                    fetchStore();
                }
            },

            async confirmSuggestedStore() {
                if (!this.suggestedStore) return;
                const formData = new FormData();
                formData.append('store_id', this.suggestedStore.id);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch('{{ route("flow-pc.set-store") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.ok) {
                        this.storeId = this.suggestedStore.id;
                        this.storeName = this.suggestedStore.store_name;
                        this.storeAddress = [this.suggestedStore.city, this.suggestedStore.state].filter(Boolean).join(', ');
                        this.isOpen = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                } catch (e) {
                    console.error("Set store error:", e);
                }
            }
        }
    }
</script>