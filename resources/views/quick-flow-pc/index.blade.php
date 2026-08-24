@extends('layouts.quick-flow-pc')

@section('title', 'What are you printing? | Qrinto Custom Print Studio')
@section('header_title', 'Products')
@section('meta_description', 'Select custom printed cards, photo magnets, photo prints, and custom stationery.')
@section('canonical_url', route('flow.index'))

@push('styles')
    <style>
        /* Size admin-provided icon SVGs to fill the tile's icon box, regardless
           of the SVG's own width/height attributes. */
        .pc-type-icon svg {
            width: 100%;
            height: 100%;
            max-width: 6rem;
            max-height: 6rem;
        }

        /* Tint the icon to the tile colour (green when active, grey when
           "coming soon"). Admin SVGs ship with hard-coded fills, so override
           any real fill/stroke to currentColor while leaving transparent
           (fill="none") shapes alone. */
        .pc-type-icon svg,
        .pc-type-icon svg [fill]:not([fill="none"]) {
            fill: currentColor !important;
        }

        .pc-type-icon svg [stroke]:not([stroke="none"]) {
            stroke: currentColor !important;
        }
    </style>
@endpush

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[1240px] mx-auto">

            {{-- Title Header --}}
            <div class="mb-10">
                <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">
                    What are you printing?
                </h1>
            </div>

            {{-- Dynamic 3-Column Product Grid (driven by ProductType records) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                @forelse ($productTypes as $type)
                    @php
                        $live = (bool) $type->is_active;

                        // Lowest price across the type's sizes; "and up" implies the cheapest.
                        $startPrice = $type->children->where('is_active', true)->min('price')
                            ?? $type->children->min('price')
                            ?? $type->price;

                        $needle = strtolower(($type->name ?? '') . ' ' . ($type->slug ?? ''));
                        $unit = \Illuminate\Support\Str::contains($needle, 'magnet') ? 'per magnet'
                            : (\Illuminate\Support\Str::contains($needle, 'card') ? 'per card'
                            : (\Illuminate\Support\Str::contains($needle, ['photo', 'print']) ? 'per print' : 'each'));
                    @endphp

                    <div class="bg-white border rounded-3xl overflow-hidden shadow-2xs flex flex-col justify-between {{ $live ? 'border-slate-200/90 hover:shadow-md transition-all group' : 'border-slate-200/80 opacity-85' }}">
                        <a href="{{ $live ? route('flow.category', $type->slug) : '#' }}" @class(['block h-full flex flex-col justify-between', 'pointer-events-none' => !$live])>

                            {{-- Top Image Area --}}
                            <div class="h-48 sm:h-52 flex items-center justify-center p-6 border-b border-slate-100 relative {{ $live ? 'bg-[#f2f7f2]' : 'bg-[#f7f9f7]' }}">
                                <div class="pc-type-icon w-24 h-24 flex items-center justify-center {{ $live ? 'text-emerald-700 group-hover:scale-105 transition-transform' : 'text-slate-300' }}">
                                    @if ($type->icon_svg)
                                        {!! $type->icon_svg !!}
                                    @else
                                        <svg class="stroke-current stroke-[1.25] fill-none" viewBox="0 0 100 80">
                                            <rect x="25" y="15" width="50" height="40" rx="3" stroke="currentColor" fill="none"/>
                                            <rect x="30" y="20" width="40" height="30" rx="1" stroke="currentColor" fill="none"/>
                                            <path d="M10 65 L25 55 L75 55 L90 65 Z" stroke="currentColor" fill="none"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            {{-- Bottom Info Area --}}
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-extrabold text-lg sm:text-xl transition-colors {{ $live ? 'text-slate-900 group-hover:text-emerald-700' : 'text-slate-400 font-bold' }}">
                                        {{ $type->name }}
                                    </h3>
                                    @if ($type->title)
                                        <p class="text-xs font-normal mt-1.5 leading-relaxed {{ $live ? 'text-slate-500' : 'text-slate-400' }}">
                                            {{ $type->title }}
                                        </p>
                                    @endif
                                </div>

                                @if ($live)
                                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                        @if ($startPrice !== null)
                                            <span class="text-xs text-slate-600 font-normal">
                                                <strong class="font-extrabold text-slate-900 text-sm">{{ \App\Services\CurrencyService::format($startPrice) }}</strong> and up, {{ $unit }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-500 font-normal">Choose a size</span>
                                        @endif
                                        <span class="text-emerald-700 font-bold text-base group-hover:translate-x-1 transition-transform">→</span>
                                    </div>
                                @else
                                    <div class="mt-6 pt-4">
                                        <span class="inline-block bg-slate-100 text-slate-400 text-[10px] font-extrabold tracking-wider px-2.5 py-1 rounded-md border border-slate-200/60">Coming soon</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-16 bg-white rounded-3xl border border-slate-200">
                        <p class="text-slate-500 text-sm font-medium">No products available right now.</p>
                    </div>
                @endforelse

            </div>

        </div>
    </div>
@endsection
