@php
    $currentRoute = Route::currentRouteName();
@endphp

<nav aria-label="Primary Navigation" class="hidden lg:flex items-center gap-1.5">
    {{-- Home --}}
    <a href="{{ route('flow.index') }}"
        class="px-3.5 py-2 rounded-xl text-sm font-bold transition-colors duration-150 flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('flow.index') ? 'text-brand-600 bg-brand-50/80' : 'text-slate-700 hover:text-brand-600 hover:bg-slate-50' }}">
        <i data-lucide="home"
            class="w-4 h-4 text-slate-400 shrink-0 {{ request()->routeIs('flow.index') ? 'text-brand-600' : '' }}"></i>
        <span>Home</span>
    </a>

    {{-- Categories Megamenu Dropdown --}}
    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
        <button type="button" @click="open = !open"
            class="px-3.5 py-2 rounded-xl text-sm font-bold text-slate-700 hover:text-brand-600 hover:bg-slate-50 transition-colors duration-150 flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
            <span>Products & Services</span>
            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 shrink-0"
                :class="{ 'rotate-180 text-brand-600': open }"></i>
        </button>

        {{-- Dropdown Megamenu Panel --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="absolute left-0 top-full mt-2 w-[520px] bg-white rounded-3xl shadow-2xl border border-slate-200/80 p-5 z-50 overflow-hidden"
            x-cloak>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <span class="text-xs font-black text-brand-600 uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i> Printing Categories
                </span>
                <a href="{{ route('flow.find-store') }}"
                    class="text-xs font-bold text-slate-400 hover:text-brand-600 transition-colors flex items-center gap-1">
                    Find Stores <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('flow.qrinto') }}"
                    class="p-3 rounded-2xl bg-slate-50/80 hover:bg-brand-50/60 border border-slate-100 hover:border-brand-200 transition-all group flex items-start gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-brand-100/80 text-brand-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <i data-lucide="image" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-brand-600 transition-colors">
                            Photo Prints</h4>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">High glossy & matte prints</p>
                    </div>
                </a>

                <a href="{{ route('flow.find-store') }}"
                    class="p-3 rounded-2xl bg-slate-50/80 hover:bg-violet-50/60 border border-slate-100 hover:border-violet-200 transition-all group flex items-start gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-violet-100/80 text-violet-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <i data-lucide="frame" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-violet-600 transition-colors">
                            Canvas Art</h4>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">Museum quality stretched canvas</p>
                    </div>
                </a>

                <a href="{{ route('flow.find-store') }}"
                    class="p-3 rounded-2xl bg-slate-50/80 hover:bg-rose-50/60 border border-slate-100 hover:border-rose-200 transition-all group flex items-start gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-rose-100/80 text-rose-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-rose-600 transition-colors">
                            Photo Books</h4>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">Hardcover & softcover albums</p>
                    </div>
                </a>

                <a href="{{ route('flow.find-store') }}"
                    class="p-3 rounded-2xl bg-slate-50/80 hover: bg-gray-50/60 border border-slate-100 hover: border-gray-200 transition-all group flex items-start gap-3">
                    <div
                        class="w-9 h-9 rounded-xl  bg-gray-100/80  text-gray-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <i data-lucide="gift" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-900 group-hover: text-gray-600 transition-colors">
                            Custom Gifts</h4>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">Personalized mugs & items</p>
                    </div>
                </a>
            </div>

            <div
                class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-500">
                <span class="flex items-center gap-1.5 text-slate-600"><i data-lucide="zap"
                        class="w-3.5 h-3.5 text-amber-500"></i> Fast 24-48hr turnaround</span>
                <a href="{{ route('flow.index') }}" class="text-brand-600 font-bold hover:underline">Start New Custom
                    Print &rarr;</a>
            </div>
        </div>
    </div>

    {{-- Direct Upload / Custom Print CTA --}}
    <a href="{{ route('flow.qrinto') }}"
        class="px-3.5 py-2 rounded-xl text-sm font-bold transition-colors duration-150 flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('flow.qrinto') ? 'text-brand-600 bg-brand-50/80' : 'text-slate-700 hover:text-brand-600 hover:bg-slate-50' }}">
        <i data-lucide="upload-cloud" class="w-4 h-4 text-slate-400 shrink-0"></i>
        <span>Custom Print</span>
        <span
            class="text-[10px] font-extrabold bg-brand-100 text-brand-700 px-1.5 py-0.5 rounded-md uppercase tracking-wider ml-0.5">Upload</span>
    </a>

    {{-- Find Stores --}}
    <a href="{{ route('flow.find-store') }}"
        class="px-3.5 py-2 rounded-xl text-sm font-bold transition-colors duration-150 flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('flow.find-store') ? 'text-brand-600 bg-brand-50/80' : 'text-slate-700 hover:text-brand-600 hover:bg-slate-50' }}">
        <i data-lucide="map-pin"
            class="w-4 h-4 text-slate-400 shrink-0 {{ request()->routeIs('flow.find-store') ? 'text-brand-600' : '' }}"></i>
        <span>Find Stores</span>
    </a>

    {{-- Track Order --}}
    <a href="{{ route('flow.track.form') }}"
        class="px-3.5 py-2 rounded-xl text-sm font-bold transition-colors duration-150 flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('flow.track.form') ? 'text-brand-600 bg-brand-50/80' : 'text-slate-700 hover:text-brand-600 hover:bg-slate-50' }}">
        <i data-lucide="package"
            class="w-4 h-4 text-slate-400 shrink-0 {{ request()->routeIs('flow.track.form') ? 'text-brand-600' : '' }}"></i>
        <span>Track Order</span>
    </a>
</nav>
