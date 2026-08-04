@extends('layouts.quick-flow-pc')

@section('title', $type->name . ' — Choose Your Size')
@section('header_title', $type->name)

@push('styles')
<style>
    .size-card-premium {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .size-card-premium:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.1), 0 0 0 1px rgba(236,72,153,0.15);
        border-color: var(--color-brand-500, #ec4899);
    }
    .size-card-premium:hover .size-arrow {
        background-color: var(--color-brand-600, #db2777);
        color: white;
        transform: translateX(4px);
    }
    .size-card-premium:hover .size-icon-box {
        transform: scale(1.08);
        background-color: var(--color-brand-100, #fce7f3);
    }
    .size-arrow {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .size-icon-box {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .size-icon-box svg {
        width: 100%;
        height: 100%;
        fill: currentColor !important;
    }
    .breadcrumb-link {
        transition: color 0.2s ease;
    }
    .breadcrumb-link:hover {
        color: var(--color-brand-600, #db2777);
    }
    .hero-cat-gradient {
        background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #faf0ff 60%, #f0f4ff 100%);
    }
    .hero-cat-pattern {
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
    .fade-up-cat {
        animation: fadeUpCat 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes fadeUpCat {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="pb-24">

    {{-- ===== HERO / PAGE HEADER ===== --}}
    <section class="hero-cat-gradient hero-cat-pattern -mx-10 -mt-4 px-10 pt-12 pb-16 relative overflow-hidden">
        <div class="hero-blob-1"></div>
        <div class="hero-blob-2"></div>
        <div class="hero-blob-3"></div>
        <div class="hero-dots"></div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 text-sm mb-8 fade-up-cat" style="animation-delay:0s">
                <a href="{{ route('flow-pc.index') }}" class="breadcrumb-link text-slate-400 font-medium hover:text-brand-600 flex items-center gap-1.5 transition-colors">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-700 font-semibold">{{ $type->name }}</span>
            </nav>

            <div class="grid grid-cols-12 gap-10 items-center">
                {{-- Left: Content --}}
                <div class="col-span-12 lg:col-span-7">
                    <div class="fade-up-cat" style="animation-delay: 0.05s">
                        <span class="inline-flex items-center gap-2 bg-white/80 border border-brand-100 text-brand-600 text-sm font-semibold px-4 py-2 rounded-full mb-6 shadow-sm backdrop-blur-sm">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            Select Your Preferred Size
                        </span>
                    </div>

                    <div class="flex items-start gap-6">
                        <div class="size-icon-box w-20 h-20 bg-white/90 shadow-lg shadow-brand-500/10 border border-brand-100 rounded-2xl flex items-center justify-center p-5 flex-shrink-0 fade-up-cat text-brand-500" style="animation-delay:0.1s">
                            @if($type->icon_svg)
                                {!! $type->icon_svg !!}
                            @else
                                <i data-lucide="package" class="w-10 h-10 text-brand-500"></i>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-4xl xl:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight fade-up-cat" style="animation-delay:0.15s">
                                {{ $type->name }} <span class="bg-gradient-to-r from-brand-600 to-violet-500 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">Collection</span>
                            </h1>
                            <p class="text-lg text-slate-500 mt-2 max-w-xl fade-up-cat leading-relaxed" style="animation-delay:0.2s">
                                @if($type->title)
                                    {{ $type->title }} &mdash;
                                @endif
                                Choose your preferred size to get started with your custom design.
                            </p>
                        </div>
                    </div>

                    {{-- Quick info pills --}}
                    <div class="flex flex-wrap items-center gap-3 mt-8 fade-up-cat" style="animation-delay:0.25s">
                        <span class="inline-flex items-center gap-1.5 bg-white/80 backdrop-blur-sm border border-slate-200/80 text-slate-700 text-sm font-medium px-4 py-2 rounded-full shadow-sm">
                            <i data-lucide="ruler" class="w-4 h-4 text-brand-500"></i>
                            {{ isset($subTypes) ? $subTypes->count() : 0 }} sizes available
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/80 backdrop-blur-sm border border-slate-200/80 text-slate-700 text-sm font-medium px-4 py-2 rounded-full shadow-sm">
                            <i data-lucide="award" class="w-4 h-4 text-brand-500"></i>
                            Studio print quality
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/80 backdrop-blur-sm border border-slate-200/80 text-slate-700 text-sm font-medium px-4 py-2 rounded-full shadow-sm">
                            <i data-lucide="truck" class="w-4 h-4 text-brand-500"></i>
                            Fast store pickup
                        </span>
                    </div>
                </div>

                {{-- Right: Decorative --}}
                <div class="col-span-12 lg:col-span-5 hidden lg:flex justify-end">
                    <div class="grid grid-cols-2 gap-4 max-w-xs fade-up-cat" style="animation-delay:0.2s">
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl p-5 shadow-xl shadow-brand-500/5 border border-slate-100 transform rotate-[-2deg] hover:rotate-0 transition-all duration-500">
                            <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="image" class="w-5 h-5 text-brand-500"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-900">Upload Photos</p>
                            <p class="text-xs text-slate-400 mt-0.5">Drag & drop canvas</p>
                        </div>
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl p-5 shadow-xl shadow-brand-500/5 border border-slate-100 transform rotate-[2deg] hover:rotate-0 transition-all duration-500 mt-6">
                            <div class="w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="palette" class="w-5 h-5 text-violet-500"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-900">Customize</p>
                            <p class="text-xs text-slate-400 mt-0.5">Full design suite</p>
                        </div>
                        <div class="bg-white rounded-2xl p-5 shadow-md shadow-slate-200/50 border border-slate-100 transform rotate-[1deg] hover:rotate-0 transition-transform duration-500 -mt-2">
                            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="printer" class="w-5 h-5 text-emerald-500"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-900">Print</p>
                            <p class="text-xs text-slate-400 mt-0.5">Museum grade</p>
                        </div>
                        <div class="bg-white rounded-2xl p-5 shadow-md shadow-slate-200/50 border border-slate-100 transform rotate-[-1deg] hover:rotate-0 transition-transform duration-500 mt-4">
                            <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center mb-3">
                                <i data-lucide="package-check" class="w-5 h-5 text-rose-500"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-900">Collect</p>
                            <p class="text-xs text-slate-400 mt-0.5">Same-day pickup</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== SIZE SELECTION GRID ===== --}}
    <section class="max-w-[1400px] mx-auto pt-16 pb-12">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl xl:text-3xl font-extrabold text-slate-900 tracking-tight">Select a Size</h2>
                <p class="text-base text-slate-500 mt-1">Pick the perfect dimensions for your {{ strtolower($type->name) }}.</p>
            </div>
            <a href="{{ route('flow-pc.index') }}" class="hidden lg:flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to categories
            </a>
        </div>

        @if(isset($subTypes) && $subTypes->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($subTypes as $index => $sub)
            <a href="{{ route('flow-pc.category', $sub->slug) }}"
               class="size-card-premium bg-white border border-slate-200 rounded-2xl p-6 flex items-center gap-5 group"
               style="animation: fadeUpCat 0.5s cubic-bezier(0.16,1,0.3,1) {{ $index * 0.06 }}s both;">

                {{-- Left: Icon --}}
                <div class="size-icon-box w-14 h-14 bg-brand-50 rounded-xl flex items-center justify-center flex-shrink-0 p-3 text-brand-500">
                    @if($type->icon_svg)
                        {!! $type->icon_svg !!}
                    @else
                        <i data-lucide="maximize-2" class="w-6 h-6 text-brand-500"></i>
                    @endif
                </div>

                {{-- Middle: Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="font-bold text-slate-900 text-lg leading-tight">{{ $sub->name }}</h3>
                        @if($sub->width && $sub->height)
                        <span class="inline-flex items-center bg-slate-100 text-slate-500 text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-lg">
                            {{ $sub->width }}&times;{{ $sub->height }}{{ $sub->unit }}
                        </span>
                        @endif
                    </div>

                    @if($sub->title)
                    <p class="text-sm text-slate-400 mt-1 truncate">{{ $sub->title }}</p>
                    @endif

                    @if($sub->price)
                    <div class="flex items-center gap-2.5 mt-2.5">
                        <span class="text-brand-600 font-bold text-[15px]">
                            Starting at {{ \App\Services\CurrencyService::format($sub->price) }}
                        </span>
                        @if($sub->old_price)
                        <span class="text-slate-300 line-through text-sm font-medium">
                            {{ \App\Services\CurrencyService::format($sub->old_price) }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Right: Arrow --}}
                <div class="size-arrow w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0 text-slate-400">
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-20 bg-white rounded-3xl border border-slate-200">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
            </div>
            <p class="text-xl font-bold text-slate-900">No sizes available</p>
            <p class="text-slate-500 mt-2">This product doesn't have any sizes configured yet.</p>
            <a href="{{ route('flow-pc.index') }}" class="inline-flex items-center gap-2 mt-6 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to categories
            </a>
        </div>
        @endif
    </section>

    {{-- ===== WHY THIS PRODUCT ===== --}}
    <section class="max-w-[1400px] mx-auto py-12 border-t border-slate-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 flex items-start gap-4">
                <div class="w-11 h-11 bg-brand-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="award" class="w-5 h-5 text-brand-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Premium Materials</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">Museum-grade paper and inks for vivid, long-lasting results.</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 flex items-start gap-4">
                <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="zap" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">Fast Turnaround</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">Most orders ready within 48 hours. Same-day available at select stores.</p>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 flex items-start gap-4">
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-1">100% Satisfaction</h4>
                    <p class="text-sm text-slate-500 leading-relaxed">Not happy with your order? We'll reprint or refund — no questions asked.</p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection