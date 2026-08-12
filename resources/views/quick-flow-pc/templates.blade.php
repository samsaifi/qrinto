@extends('layouts.quick-flow-pc')

@section('title', $type->name . ' — Choose a Template (Step 3)')
@section('header_title', $type->name)

@push('styles')
    <style>
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

        .filter-pill {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .filter-pill:hover {
            transform: translateY(-1px);
        }

        .template-card-premium {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .template-card-premium:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px -16px rgba(0, 0, 0, 0.12);
        }

        .template-card-premium:hover .tpl-overlay {
            opacity: 1;
        }

        .template-card-premium:hover .tpl-img {
            transform: scale(1.06);
        }

        .tpl-overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .tpl-img {
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tpl-fade {
            animation: tplFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes tplFade {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
    <div x-data="{ activeCategory: 'all', searchQuery: '' }" class="w-full overflow-hidden pb-24">

        {{-- ===== HERO HEADER (WHITE PATTERN BACKGROUND HERO SECTION) ===== --}}
        <section
            class="hero-pattern-bg relative w-full px-6 lg:px-12 pt-8 sm:pt-10 pb-6 sm:pb-8 border-b border-slate-200/80 overflow-hidden">
            <!-- Ambient Soft Radial Glow -->
            <div class="absolute inset-0 pointer-events-none hero-gradient-overlay"></div>

            <!-- Giant Background Watermark Text "Step 3" (Bottom Right) -->
            <div
                class="absolute right-4 sm:right-10 bottom-2 text-[140px] sm:text-[200px] font-black text-slate-200/40 select-none pointer-events-none tracking-tighter leading-none z-0">
                Step 3
            </div>

            <div class="max-w-[1400px] mx-auto w-full relative z-10">
                {{-- Top Navigation / Breadcrumbs --}}
                <div class="flex items-center justify-between gap-3 mb-6 tpl-fade" style="animation-delay:0s">
                    <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <a href="{{ route('flow-pc.index') }}"
                            class="text-slate-500 hover:text-brand-600 transition-colors flex items-center gap-1">
                            <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Home</span>
                        </a>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        @if ($type->parent)
                            <a href="{{ route('flow-pc.category', $type->parent->slug) }}"
                                class="text-slate-500 hover:text-brand-600 transition-colors">
                                {{ $type->parent->name }}
                            </a>
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        @endif
                        <span class="text-slate-900 font-extrabold">{{ $type->name }}</span>
                    </nav>
                </div>

                <div class="max-w-3xl">
                    <!-- Heading -->
                    <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 leading-tight tracking-tight">
                        {{ $type->name }} <span class="text-brand-600 italic"
                            style="font-family: 'Playfair Display', serif;">Templates & Studio.</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-sm sm:text-base text-slate-600 font-medium mt-3 leading-relaxed max-w-2xl">
                        Select a professionally crafted template or start with a blank canvas to customize your
                        {{ strtolower($type->parent->name ?? $type->name) }}.
                    </p>
                </div>
            </div>
        </section>

        {{-- ===== TEMPLATE GALLERY & FILTER TOOLBAR ===== --}}
        <section class="max-w-[1600px] mx-auto pt-5 pb-16 px-6 lg:px-12">

            {{-- Premium Filter Header & Category Pills (Clean Floating Layout - No Outer Card Background or Outer Border) --}}
            <div class="mb-8">
                {{-- Header Row inside Filter Toolbar --}}
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 mb-5 border-b border-slate-200/80">
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 border border-brand-200/80 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full shadow-2xs">
                            <i data-lucide="layout-grid" class="w-3 h-3 text-brand-600"></i>
                            Template Library
                        </span>
                        <span class="text-xs font-bold text-slate-300">&bull;</span>
                        <span class="text-xs font-extrabold text-slate-500">
                            {{ $templates->count() }} Presets Available
                        </span>
                    </div>

                    {{-- Live Search Filter Input --}}
                    <div class="w-full sm:w-80 shrink-0">
                        <div
                            class="relative flex items-center bg-white border border-slate-200/90 rounded-2xl px-4 py-2 shadow-2xs focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/15 transition-all duration-300">
                            <i data-lucide="search" class="w-4 h-4 text-brand-500 shrink-0 mr-2.5"></i>
                            <input type="text" x-model="searchQuery" placeholder="Search templates by title..."
                                class="w-full text-xs sm:text-sm text-slate-900 placeholder-slate-400 bg-transparent border-0 outline-none focus:ring-0 font-bold">
                            <button x-show="searchQuery" @click="searchQuery = ''" type="button"
                                class="text-slate-400 hover:text-slate-600 ml-1 cursor-pointer">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Category Filter Pills Row --}}
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ?
                            'bg-brand-500 hover:bg-brand-600 text-white border-brand-500 shadow-md shadow-brand-500/25 ring-2 ring-brand-500/20' :
                            'bg-white hover:bg-slate-50 text-slate-700 hover:text-brand-600 border-slate-200/90 hover:border-brand-300 shadow-2xs'"
                        class="filter-pill px-4 py-2.5 rounded-xl border text-xs font-black transition-all duration-200 cursor-pointer flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"
                            :class="activeCategory === 'all' ? 'text-white' : 'text-brand-500'"></i>
                        <span>All Templates</span>
                        <span
                            :class="activeCategory === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'"
                            class="px-2 py-0.5 rounded-md text-[10px] font-black transition-colors">{{ $templates->count() }}</span>
                    </button>

                    @foreach ($categories as $cat)
                        @php $catCount = $templates->where('category_id', $cat->id)->count(); @endphp
                        @if ($catCount > 0)
                            <button @click="activeCategory = {{ $cat->id }}"
                                :class="activeCategory === {{ $cat->id }} ?
                                    'bg-brand-500 hover:bg-brand-600 text-white border-brand-500 shadow-md shadow-brand-500/25 ring-2 ring-brand-500/20' :
                                    'bg-white hover:bg-slate-50 text-slate-700 hover:text-brand-600 border-slate-200/90 hover:border-brand-300 shadow-2xs'"
                                class="filter-pill px-4 py-2.5 rounded-xl border text-xs font-black transition-all duration-200 cursor-pointer flex items-center gap-2">
                                <span>{{ $cat->name }}</span>
                                <span
                                    :class="activeCategory === {{ $cat->id }} ? 'bg-white/20 text-white' :
                                        'bg-slate-100 text-slate-700'"
                                    class="px-2 py-0.5 rounded-md text-[10px] font-black transition-colors">{{ $catCount }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Template Cards Grid (6 Cards Per Row) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-5">
                @forelse($templates as $index => $tpl)
                    <a href="{{ route('flow-pc.customize', $tpl->slug) }}"
                        x-show="(activeCategory === 'all' || activeCategory === {{ $tpl->category_id }}) && ('{{ strtolower(addslashes($tpl->name)) }}'.includes(searchQuery.toLowerCase()) || searchQuery === '')"
                        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="template-card-premium group relative bg-slate-900 border border-slate-200/90 rounded-2xl overflow-hidden shadow-xs hover:shadow-2xl hover:border-brand-400 cursor-pointer block h-[170px]"
                        style="animation: tplFade 0.45s cubic-bezier(0.16,1,0.3,1) {{ min($index * 0.04, 0.4) }}s both;">

                        {{-- Full Width & Height Background Image --}}
                        <img src="{{ $tpl->frame_image_thumbnail ?? 'https://placehold.co/400x550/1e1b4b/ec4899?text=' . urlencode($tpl->name) }}"
                            class="tpl-img w-full h-full object-cover" alt="{{ $tpl->name }}" loading="lazy">

                        @if ($tpl->category_id == 21)
                            <div
                                class="absolute inset-0 z-15 bg-white flex flex-col items-center justify-center p-3 text-center border border-slate-200/80">
                                <div
                                    class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-1.5 group-hover:bg-brand-50 group-hover:text-brand-600 group-hover:border-brand-200 transition-all">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </div>
                                <span
                                    class="text-xs font-black text-slate-700 group-hover:text-brand-600 transition-colors">Add
                                    your stuff</span>
                                <span class="text-[9px] font-semibold text-slate-400">Blank Canvas</span>
                            </div>
                        @endif

                        {{-- Top Left Category Badge --}}
                        <div class="absolute top-2 left-2 z-20 max-w-[55%]">
                            @php $catItem = $categories->firstWhere('id', $tpl->category_id); @endphp
                            <span
                                class="inline-flex items-center gap-1 bg-black/30 backdrop-blur-md border border-white/20 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full drop-shadow-md truncate max-w-full block">
                                <i data-lucide="tag" class="w-2.5 h-2.5 text-brand-400 shrink-0 inline"></i>
                                <span class="truncate">{{ $catItem->name ?? 'Preset' }}</span>
                            </span>
                        </div>

                        {{-- Top Right Orientation Badge --}}
                        <div class="absolute top-2 right-2 z-20">
                            <span
                                class="inline-flex items-center gap-1 bg-black/30 backdrop-blur-md border border-white/20 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full drop-shadow-md">
                                @if ($tpl->pdf_orientation == 'portrait')
                                    <i data-lucide="rectangle-vertical" class="w-2.5 h-2.5 text-brand-400"></i> Portrait
                                @else
                                    <i data-lucide="rectangle-horizontal" class="w-2.5 h-2.5 text-brand-400"></i> Landscape
                                @endif
                            </span>
                        </div>

                        {{-- Middle Hover Overlay: "Customize Design" Button --}}
                        <div
                            class="absolute inset-0 z-20 flex items-center justify-center p-2 opacity-0 group-hover:opacity-100 transition-all duration-300 bg-slate-950/50 backdrop-blur-[2px] pointer-events-none">
                            <span
                                class="bg-brand-500 group-hover:bg-brand-600 text-white text-[11px] font-black px-3.5 py-1.5 rounded-full shadow-2xl flex items-center justify-center gap-1.5 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300 border border-white/20">
                                <i data-lucide="pen-tool" class="w-3 h-3"></i>
                                <span>Customize</span>
                            </span>
                        </div>

                        {{-- Absolute Bottom Center Title Overlay Container --}}
                        <div
                            class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent flex flex-col items-center justify-end text-center z-10 pt-6">
                            {{-- Template Title (Center Aligned) --}}
                            <h3
                                class="text-xs font-black text-white leading-tight line-clamp-1 drop-shadow-md group-hover:text-brand-300 transition-colors">
                                {{ $tpl->name }}
                            </h3>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center py-20 bg-slate-50/80 rounded-[32px] border border-slate-200/90">
                        <div
                            class="w-16 h-16 bg-white shadow-xs rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-900">No templates found</h4>
                        <p class="text-sm text-slate-500 font-medium mt-2">Please select a different category or search
                            term.</p>
                        <a href="{{ route('flow-pc.index') }}"
                            class="inline-flex items-center gap-2 mt-6 text-xs font-black text-brand-600 hover:text-brand-700 bg-white border border-slate-200/90 px-4 py-2.5 rounded-xl shadow-2xs transition-all">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to product catalog
                        </a>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ===== BOTTOM TRUST BAR ===== --}}
        <section class="max-w-[1400px] mx-auto px-6 sm:px-10">
            <div class="bg-white border border-slate-200/90 rounded-[28px] p-8 shadow-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-brand-50 text-brand-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="palette" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-sm">Fully Customizable Studio</h4>
                            <p class="text-xs text-slate-500 font-medium mt-1">Edit text, photos, layout vectors, and
                                filters in real-time.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="eye" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-sm">Live 3D & Print Preview</h4>
                            <p class="text-xs text-slate-500 font-medium mt-1">Inspect your exact print preview before
                                confirming your order.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i data-lucide="shield-check" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-slate-900 text-sm">Local Branch Quality</h4>
                            <p class="text-xs text-slate-500 font-medium mt-1">Printed on professional equipment at your
                                chosen Qrinto store.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endpush
