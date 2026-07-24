@extends('layouts.quick-flow-pc')

@section('title', $type->name . ' — Choose a Template')
@section('header_title', $type->name)

@push('styles')
<style>
    .tpl-hero-gradient {
        background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 40%, #faf5ff 100%);
    }
    .tpl-hero-pattern {
        background-image: radial-gradient(circle at 1px 1px, rgba(99,102,241,0.05) 1px, transparent 0);
        background-size: 32px 32px;
    }
    .filter-pill {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .filter-pill:hover {
        transform: translateY(-1px);
    }
    .template-card {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .template-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px -16px rgba(0,0,0,0.12);
    }
    .template-card:hover .tpl-overlay {
        opacity: 1;
    }
    .template-card:hover .tpl-img {
        transform: scale(1.05);
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
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<div x-data="{ activeCategory: 'all' }" class="pb-24">

    {{-- ===== HERO HEADER ===== --}}
    <section class="tpl-hero-gradient tpl-hero-pattern -mx-10 -mt-4 px-10 pt-12 pb-14 relative overflow-hidden">
        <div class="max-w-[1400px] mx-auto">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 text-sm mb-8 tpl-fade" style="animation-delay:0s">
                <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-indigo-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                @if($type->parent)
                <a href="{{ route('flow-pc.category', $type->parent->slug) }}" class="text-slate-400 font-medium hover:text-indigo-600 transition-colors">
                    {{ $type->parent->name }}
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                @endif
                <span class="text-slate-700 font-semibold">{{ $type->name }}</span>
            </nav>

            <div class="flex items-start justify-between gap-8">
                {{-- Left --}}
                <div class="flex items-start gap-5">
                    <div class="w-16 h-16 bg-white/80 border border-indigo-100 rounded-2xl flex items-center justify-center flex-shrink-0 tpl-fade" style="animation-delay:0.05s">
                        <i data-lucide="layout-template" class="w-8 h-8 text-indigo-500"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl xl:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight tpl-fade" style="animation-delay:0.1s">
                            {{ $type->name }}
                        </h1>
                        <p class="text-lg text-slate-500 mt-2 max-w-xl tpl-fade" style="animation-delay:0.15s">
                            Choose a template to start designing your {{ strtolower($type->parent->name ?? $type->name) }}.
                        </p>
                        {{-- Info pills --}}
                        <div class="flex flex-wrap items-center gap-3 mt-5 tpl-fade" style="animation-delay:0.2s">
                            @if($type->width && $type->height)
                            <span class="inline-flex items-center gap-1.5 bg-white/80 border border-slate-200 text-slate-600 text-sm font-medium px-3.5 py-1.5 rounded-full">
                                <i data-lucide="ruler" class="w-4 h-4 text-indigo-500"></i>
                                {{ $type->width }}&times;{{ $type->height }}{{ $type->unit }}
                            </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 bg-white/80 border border-slate-200 text-slate-600 text-sm font-medium px-3.5 py-1.5 rounded-full">
                                <i data-lucide="layers" class="w-4 h-4 text-indigo-500"></i>
                                {{ $templates->count() }} templates
                            </span>
                            @if($type->title)
                            <span class="inline-flex items-center gap-1.5 bg-white/80 border border-slate-200 text-slate-600 text-sm font-medium px-3.5 py-1.5 rounded-full">
                                <i data-lucide="file-text" class="w-4 h-4 text-indigo-500"></i>
                                {{ $type->title }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right: Price --}}
                @if($type->price)
                <div class="hidden lg:block text-right flex-shrink-0 tpl-fade" style="animation-delay:0.15s">
                    <div class="bg-white/80 border border-slate-200 rounded-2xl px-6 py-4">
                        <div class="flex items-center gap-3 justify-end">
                            @if($type->old_price && $type->old_price > $type->price)
                            <span class="text-slate-400 line-through text-lg">{{ \App\Services\CurrencyService::format($type->old_price) }}</span>
                            @endif
                            <span class="text-3xl font-extrabold text-slate-900">{{ \App\Services\CurrencyService::format($type->price) }}</span>
                        </div>
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mt-1">Starting price</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ===== TEMPLATE GALLERY ===== --}}
    <section class="max-w-[1400px] mx-auto pt-12 pb-16">

        {{-- Filter Bar --}}
        <div class="flex items-center justify-between mb-8 gap-4">
            {{-- Category Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                <button
                    @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-600/20' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:text-indigo-600'"
                    class="filter-pill px-4 py-2 rounded-xl border text-sm font-semibold transition-all">
                    All
                </button>
                @foreach($categories as $cat)
                    @php $catCount = $templates->where('category_id', $cat->id)->count(); @endphp
                    @if($catCount > 0)
                    <button
                        @click="activeCategory = {{ $cat->id }}"
                        :class="activeCategory === {{ $cat->id }} ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-600/20' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:text-indigo-600'"
                        class="filter-pill px-4 py-2 rounded-xl border text-sm font-semibold transition-all">
                        {{ $cat->name }}
                        <span class="ml-1 opacity-60">{{ $catCount }}</span>
                    </button>
                    @endif
                @endforeach
            </div>

            {{-- Results count --}}
            <p class="hidden lg:block text-sm text-slate-400 font-medium flex-shrink-0">
                <span class="font-semibold text-slate-600">{{ $templates->count() }}</span> templates available
            </p>
        </div>

        {{-- Template Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
            @forelse($templates as $index => $tpl)
            <a href="{{ route('flow-pc.customize', $tpl->slug) }}"
                x-show="activeCategory === 'all' || activeCategory === {{ $tpl->category_id }}"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="template-card group relative bg-white border border-slate-200 rounded-2xl overflow-hidden"
                style="animation: tplFade 0.45s cubic-bezier(0.16,1,0.3,1) {{ min($index * 0.04, 0.4) }}s both;">

                {{-- Image Container --}}
                <div class="relative overflow-hidden" style="aspect-ratio: {{ $tpl->aspect_ratio }};">
                    <img src="{{ $tpl->frame_image_thumbnail ?? 'https://placehold.co/300x400/eee/999?text=' . urlencode($tpl->name) }}"
                        class="tpl-img w-full h-full object-cover"
                        alt="{{ $tpl->name }}"
                        loading="lazy">

                    {{-- Orientation badge --}}
                    <div class="absolute top-3 right-3 w-7 h-7 bg-white/90 backdrop-blur-sm rounded-lg flex items-center justify-center shadow-sm border border-white/50">
                        @if($tpl->pdf_orientation == 'portrait')
                        <i data-lucide="rectangle-vertical" class="w-3.5 h-3.5 text-slate-500"></i>
                        @else
                        <i data-lucide="rectangle-horizontal" class="w-3.5 h-3.5 text-slate-500"></i>
                        @endif
                    </div>

                    {{-- Hover overlay --}}
                    <div class="tpl-overlay absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent flex items-end justify-center pb-5">
                        <span class="bg-white text-slate-900 text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg flex items-center gap-2">
                            <i data-lucide="pen-tool" class="w-4 h-4"></i>
                            Customize
                        </span>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-slate-900 leading-snug line-clamp-2">{{ $tpl->name }}</h3>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-200">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
                </div>
                <p class="text-xl font-bold text-slate-900">No templates found</p>
                <p class="text-slate-500 mt-2">Please select a different size or category.</p>
                <a href="{{ route('flow-pc.index') }}" class="inline-flex items-center gap-2 mt-6 text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to categories
                </a>
            </div>
            @endforelse
        </div>
    </section>

    {{-- ===== BOTTOM TRUST BAR ===== --}}
    <section class="max-w-[1400px] mx-auto pt-4 pb-4 border-t border-slate-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-8">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="palette" class="w-5 h-5 text-indigo-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Fully Customizable</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Edit text, photos, colors, and layout to make it yours.</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="eye" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Live Preview</h4>
                    <p class="text-xs text-slate-500 mt-0.5">See exactly how your design will look before printing.</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Satisfaction Guaranteed</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Not happy? We'll reprint or refund your order.</p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection