@extends('layouts.quick-flow-pc')

@section('title', 'Custom ' . $type->name . ' — Select Format & Dimensions | Qrinto')
@section('meta_description', 'Explore customizable ' . $type->name . ' templates with custom sizes, paper types, and instant online editor. High quality printing at Qrinto.')
@section('meta_keywords', 'custom ' . strtolower($type->name) . ', ' . strtolower($type->name) . ' printing, personalized ' . strtolower($type->name) . ', Qrinto ' . strtolower($type->name))
@section('header_title', $type->name)

@section('json_ld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "{{ route('flow-pc.index') }}"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "{{ $type->name }}",
      "item": "{{ url()->current() }}"
    }
  ]
}
</script>
@endsection

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

        .size-card-premium {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .size-card-premium:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(74, 133, 36, 0.2);
            border-color: var(--color-brand-500, #6FB63A);
        }

        .size-card-premium:hover .size-arrow {
            background-color: var(--color-brand-600, #4A8524);
            color: white;
            transform: translateX(4px);
        }

        .size-card-premium:hover .size-icon-box {
            transform: scale(1.08);
            background-color: var(--color-brand-100, #E0F0D0);
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

        .fade-up-cat {
            animation: fadeUpCat 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeUpCat {
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
    @php
        $subTypesCount = isset($subTypes) ? $subTypes->count() : 0;
        $activeStore = session()->has('active_store_id') ? \App\Models\Store::find(session('active_store_id')) : null;

        $heroPills = [
            ['icon' => 'ruler', 'text' => "{$subTypesCount} Sizes Available"],
            ['icon' => 'award', 'text' => 'Studio Print Quality'],
            ['icon' => 'truck', 'text' => 'Express Store Pickup'],
        ];

        $trustStats = [
            ['val' => '100%', 'label' => 'Quality Guaranteed'],
            ['val' => 'Live 3D', 'label' => 'Design Studio'],
            ['val' => 'Same-Day', 'label' => 'Local Pickup'],
        ];
    @endphp

    <div class="w-full overflow-hidden pb-24">

        {{-- ===== HERO HEADER (WHITE PATTERN BACKGROUND HERO SECTION) ===== --}}
        <section
            class="hero-pattern-bg relative w-full px-6 lg:px-12 pt-8 sm:pt-10 pb-6 sm:pb-8 border-b border-slate-200/80 overflow-hidden">
            <!-- Ambient Soft Radial Glow -->
            <div class="absolute inset-0 pointer-events-none hero-gradient-overlay"></div>

            <!-- Giant Background Watermark Text "Step 2" (Bottom Right) -->
            <div
                class="absolute right-4 sm:right-10 bottom-2 text-[140px] sm:text-[200px] font-black text-slate-200/40 select-none pointer-events-none tracking-tighter leading-none z-0">
                Step 2
            </div>

            <div class="max-w-[1400px] mx-auto w-full relative z-10">
                {{-- Top Navigation / Breadcrumbs --}}
                <div class="flex items-center justify-between gap-3 mb-6 fade-up-cat" style="animation-delay:0s">
                    <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <a href="{{ route('flow-pc.index') }}"
                            class="text-slate-500 hover:text-brand-600 transition-colors flex items-center gap-1">
                            <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Home</span>
                        </a>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <span class="text-slate-900 font-extrabold">{{ $type->name }}</span>
                    </nav>
                </div>

                <div class="max-w-3xl">
                    <!-- Badge Tag -->
                    <div class="mb-3">
                        <span
                            class="inline-flex items-center gap-2 bg-brand-50 text-brand-700 border border-brand-200/80 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-widest shadow-2xs">
                            <i data-lucide="ruler" class="w-3.5 h-3.5 text-brand-600"></i>
                            STEP 2 &bull; SIZE SELECTION
                        </span>
                    </div>

                    <!-- Heading -->
                    <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 leading-tight tracking-tight">
                        {{ $type->name }} <span class="text-brand-600 italic"
                            style="font-family: 'Playfair Display', serif;">Collection & Sizes.</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-sm sm:text-base text-slate-600 font-medium mt-3 leading-relaxed max-w-2xl">
                        @if ($type->title)
                            {{ $type->title }} &mdash;
                        @endif
                        Choose your preferred dimensions to browse design templates and customize your
                        {{ strtolower($type->name) }}.
                    </p>
                </div>
            </div>
        </section>

        {{-- ===== SIZE SELECTION GRID (ULTRA-PREMIUM RICH CARD UI) ===== --}}
        <section id="size-grid" class="max-w-[1400px] mx-auto pt-5 pb-16 px-6 sm:px-10">

            {{-- Compact Toolbar Row (Clean & Non-Repetitive) --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-5 border-b border-slate-200/80">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500">Available Formats</span>
                    <span class="text-slate-300 font-bold">&bull;</span>
                    <span
                        class="inline-flex items-center gap-1.5 bg-brand-50 text-brand-700 border border-brand-200/80 text-[11px] font-black px-3 py-1 rounded-full shadow-2xs">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-brand-600"></i>
                        {{ $subTypesCount }} Sizes Available
                    </span>
                </div>

                <a href="{{ route('flow-pc.index') }}"
                    class="inline-flex items-center gap-2 text-xs font-black text-slate-700 hover:text-white hover:bg-slate-900 bg-white border border-slate-200/90 px-4 py-2.5 rounded-xl transition-all duration-300 shadow-2xs hover:shadow-md cursor-pointer shrink-0 self-start sm:self-auto">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-brand-600"></i>
                    <span>Back to {{ session()->get('quick_flow_data')['type_name'] ?? 'Cards' }}</span>
                </a>
            </div>

            @if (isset($subTypes) && $subTypes->isNotEmpty())
                {{-- Size Cards Grid (Compact Half-Size Cards with Icon & Title side-by-side in one row) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                    @foreach ($subTypes as $index => $sub)
                        <a href="{{ route('flow-pc.category', $sub->slug) }}"
                            class="size-card-premium group relative bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-2xs hover:shadow-xl hover:border-brand-500 transition-all duration-300 cursor-pointer block"
                            style="animation: fadeUpCat 0.5s cubic-bezier(0.16,1,0.3,1) {{ $index * 0.04 }}s both;">

                            <!-- Subtle Top Brand Glow Accent -->
                            <div
                                class="absolute top-0 inset-x-6 h-0.5 bg-brand-500 rounded-b-full opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>

                            <div>
                                {{-- Header Row: Icon + Title & Subtitle side-by-side in ONE ROW, plus Dimension Badge --}}
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <!-- Icon + Title & Subtitle side-by-side -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-icon-box w-11 h-11 bg-brand-100 border border-brand-200/80 rounded-xl flex items-center justify-center text-brand-700 shadow-2xs group-hover:scale-105 transition-transform duration-300 p-2.5 shrink-0">
                                            @if ($type->icon_svg)
                                                {!! $type->icon_svg !!}
                                            @else
                                                <i data-lucide="maximize-2" class="w-5 h-5 text-brand-700"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h3
                                                class="text-base font-black text-slate-900 group-hover:text-brand-600 transition-colors leading-tight">
                                                {{ $sub->name }}
                                            </h3>
                                            <p
                                                class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mt-0.5">
                                                {{ $sub->title ?: 'Custom Studio Dimension' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($sub->width && $sub->height)
                                        <span
                                            class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 border border-slate-200/90 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-2xs shrink-0">
                                            <i data-lucide="ruler" class="w-3 h-3 text-slate-500"></i>
                                            {{ $sub->width }}&times;{{ $sub->height }} {{ $sub->unit }}
                                        </span>
                                    @endif
                                </div>

                            </div>

                            {{-- Bottom Card Footer: Price & CTA --}}
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span
                                        class="text-[9px] font-black uppercase tracking-widest text-slate-400 block">Starting
                                        From</span>
                                    <div class="flex items-baseline gap-1.5 mt-0.5">
                                        @if ($sub->price)
                                            <span class="text-lg font-black text-slate-900">
                                                {{ \App\Services\CurrencyService::format($sub->price) }}
                                            </span>
                                            @if ($sub->old_price && $sub->old_price > $sub->price)
                                                <span class="text-[11px] text-slate-400 line-through font-bold">
                                                    {{ \App\Services\CurrencyService::format($sub->old_price) }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs font-black text-brand-600">Studio Pricing</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- CTA Button --}}
                                <div
                                    class="size-arrow bg-slate-100 text-slate-700 border border-slate-200/80 group-hover:bg-brand-500 group-hover:text-white group-hover:border-brand-500 hover:!bg-brand-600 font-black text-[11px] px-3.5 py-2 rounded-xl shadow-2xs flex items-center gap-1.5 transition-all duration-300">
                                    <span>Select Size</span>
                                    <i data-lucide="arrow-right"
                                        class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-slate-50/80 rounded-[32px] border border-slate-200/90">
                    <div
                        class="w-16 h-16 bg-white shadow-xs rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-400">
                        <i data-lucide="inbox" class="w-8 h-8 text-slate-400"></i>
                    </div>
                    <h4 class="text-xl font-black text-slate-900">No sizes found</h4>
                    <p class="text-sm text-slate-500 font-medium mt-2">No size subcategories configured for
                        {{ $type->name }}.</p>
                    <a href="{{ route('flow-pc.index') }}"
                        class="inline-flex items-center gap-2 mt-6 text-xs font-black text-brand-600 hover:text-brand-700 bg-white border border-slate-200/90 px-4 py-2.5 rounded-xl shadow-2xs transition-all">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to product catalog
                    </a>
                </div>
            @endif
        </section>

    </div>
@endsection
