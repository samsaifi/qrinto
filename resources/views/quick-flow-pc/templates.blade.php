@extends('layouts.quick-flow-pc')

@php

    $sizeWidth = isset($flowData['size_width']) ? $flowData['size_width'] + 0 : ($type->width ? $type->width + 0 : 5);
    $sizeHeight = isset($flowData['size_height'])
        ? $flowData['size_height'] + 0
        : ($type->height
            ? $type->height + 0
            : 7);
    $sizeLabel = "{$sizeWidth} × {$sizeHeight}";

    $titleLabel = $flowData['size_title'] ?? ($type->title ?? ($type->name ?? 'Folded'));
    $parentName = $type->parent->name ?? ($type->name ?? 'Cards');
@endphp

@section('title', 'Pick a starting point | Qrinto Custom Studio')
@section('header_title', $parentName)
@section('meta_description', 'Choose a starting template for your custom ' . strtolower($parentName) . '.')

@section('content')
    <div x-data="{ activeCategory: 'all', searchQuery: '' }" class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[1240px] mx-auto">

            {{-- Back + Title (single row on mobile) --}}
            <div class="flex items-center gap-2 mb-3 md:flex-col md:items-start md:gap-0 md:mb-8">
                <a href="{{ route('canonical.cards') }}"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ←
                    <span class="hidden md:inline">{{ $parentName }}</span>
                </a>
                <div class="min-w-0">
                    <h1 class="text-base md:text-2xl font-extrabold text-[#112419] tracking-tight leading-tight truncate md:mt-4">
                        Pick a starting point
                    </h1>
                    <p class="hidden md:block text-sm text-slate-500 font-normal mt-1.5">
                        {{ $titleLabel }}, {{ $sizeLabel }}. Every template can be changed once it is open.
                    </p>
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div class="flex items-center gap-1.5 md:gap-2 mb-4 md:mb-10 flex-wrap overflow-x-auto scrollbar-hide">
                <button type="button" @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-[#112419] text-white font-bold' :
                        'bg-white text-slate-700 border border-slate-200/90 hover:border-slate-300 font-medium'"
                    class="px-3 py-1.5 md:px-4 md:py-2 rounded-full text-[11px] md:text-xs transition-all cursor-pointer shadow-2xs shrink-0">
                    All
                </button>

                @foreach ($categories as $cat)
                    @php $catCount = $templates->where('category_id', $cat->id)->count(); @endphp
                    @if ($catCount > 0 || $loop->iteration <= 5)
                        <button type="button" @click="activeCategory = '{{ $cat->id }}'"
                            :class="activeCategory === '{{ $cat->id }}' ? 'bg-[#112419] text-white font-bold' :
                                'bg-white text-slate-700 border border-slate-200/90 hover:border-slate-300 font-medium'"
                            class="px-3 py-1.5 md:px-4 md:py-2 rounded-full text-[11px] md:text-xs transition-all cursor-pointer shadow-2xs shrink-0">
                            {{ $cat->name }}
                        </button>
                    @endif
                @endforeach
            </div>

            {{-- 6-Column Templates Grid --}}
            <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-4 md:gap-5">

                {{-- CARD 1: Start Blank (Only shown when activeCategory is 21) --}}
                <div x-show="activeCategory === '21'"
                    class="bg-white border border-slate-200/90 rounded-xl md:rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
                    <a href="{{ url($type->slug . '/' . Str::slug($titleLabel) . '/' . str_replace(' × ', 'x', $sizeLabel) . '/design') }}"
                        class="block h-full flex flex-col justify-between">
                        {{-- Top Image Area --}}
                        <div
                            class="bg-[#f2f7f2] h-28 md:h-56 flex flex-col items-center justify-center p-3 md:p-4 border-b border-slate-100 relative group-hover:bg-[#ebf3eb] transition-colors">
                            <div
                                class="w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center text-emerald-800 text-xl md:text-2xl font-light mb-1">
                                +
                            </div>
                            <span class="text-[10px] md:text-xs font-bold text-emerald-800">
                                Start blank
                            </span>
                        </div>

                        {{-- Bottom Info Area --}}
                        <div class="px-2 py-1.5 md:p-5 bg-white flex-1 flex items-center">
                            <h3 class="font-extrabold text-slate-900 text-[11px] md:text-sm line-clamp-1">
                                Blank {{ $sizeLabel }}
                            </h3>
                        </div>
                    </a>
                </div>

                {{-- TEMPLATE CARDS LOOP --}}
                @forelse ($templates as $index => $tpl)
                    @php
                        $imageSrc = $tpl->frame_image_thumbnail ?? ($tpl->image_url ?? ($tpl->preview_image ?? null));
                    @endphp

                    <div x-show="activeCategory === 'all' || activeCategory === '{{ $tpl->category_id }}'"
                        class="bg-white border border-slate-200/90 rounded-xl md:rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">

                        <a href="{{ url($type->slug . '/' . Str::slug($titleLabel) . '/' . str_replace(' × ', 'x', $sizeLabel) . '/design/' . $tpl->slug) }}"
                            class="block h-full flex flex-col justify-between">
                            {{-- Top Image Area (Real image instead of colorful box) --}}
                            <div class="h-28 md:h-56 bg-slate-100 border-b border-slate-100 overflow-hidden relative">
                                @if ($imageSrc)
                                    <img src="{{ $imageSrc }}" alt="{{ $tpl->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy">
                                @else
                                    {{-- Clean Realistic Card Placeholder Image --}}
                                    <div
                                        class="w-full h-full bg-[#f8faf7] flex flex-col items-center justify-center p-6 text-center group-hover:scale-105 transition-transform duration-300 relative">
                                        <div
                                            class="w-3/4 h-3/4 bg-white rounded-xl shadow-xs border border-slate-200/70 p-4 flex flex-col justify-between items-center">
                                            <div class="w-full h-2 bg-emerald-100 rounded"></div>
                                            <span
                                                class="text-xs font-black text-slate-700 line-clamp-2 px-2">{{ $tpl->name }}</span>
                                            <div class="w-2/3 h-1.5 bg-slate-200 rounded"></div>
                                        </div>
                                    </div>
                                @endif

                                @if ($tpl->category_id == 21)
                                    {{-- Overlay Layer over product image if category_id = 21 --}}
                                    <div
                                        class="absolute inset-0 bg-[#f2f7f2]/90 backdrop-blur-xs flex flex-col items-center justify-center p-4 transition-all group-hover:bg-[#ebf3eb]/95 z-10">
                                        <div
                                            class="w-10 h-10 rounded-full bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-800 text-2xl font-light mb-2 shadow-2xs group-hover:scale-110 transition-transform">
                                            +
                                        </div>
                                        <span class="text-xs font-bold text-emerald-800 tracking-wide uppercase">
                                            Start blank
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Bottom Info Area --}}
                            <div class="px-2 py-1.5 md:p-5 bg-white flex-1 flex items-center">
                                <h3
                                    class="font-bold md:font-extrabold text-slate-900 text-[11px] md:text-sm group-hover:text-emerald-700 transition-colors line-clamp-1">
                                    {{ $tpl->name }}
                                </h3>
                            </div>
                        </a>

                    </div>
                @empty
                    {{-- Default Sample Templates if database empty --}}
                    @php
                        $sampleTemplates = [
                            [
                                'name' => 'Happy Anniversary',
                                'img' =>
                                    'https://images.unsplash.com/photo-1518199266791-5375a83190b7?auto=format&fit=crop&w=600&q=80',
                            ],
                            [
                                'name' => 'Love Birds',
                                'img' =>
                                    'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=600&q=80',
                            ],
                            [
                                'name' => 'Birthday Cake',
                                'img' =>
                                    'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=600&q=80',
                            ],
                            [
                                'name' => 'Polka Dot Birthday',
                                'img' =>
                                    'https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?auto=format&fit=crop&w=600&q=80',
                            ],
                            [
                                'name' => 'Merry Christmas',
                                'img' =>
                                    'https://images.unsplash.com/photo-1543589077-47d513165776?auto=format&fit=crop&w=600&q=80',
                            ],
                            [
                                'name' => 'Hanukkah Lights',
                                'img' =>
                                    'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80',
                            ],
                            [
                                'name' => 'Thank You, Floral',
                                'img' =>
                                    'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=600&q=80',
                            ],
                        ];
                    @endphp

                    @foreach ($sampleTemplates as $sample)
                        <div
                            class="bg-white border border-slate-200/90 rounded-xl md:rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
                            <a href="{{ url($type->slug . '/' . Str::slug($titleLabel) . '/' . str_replace(' × ', 'x', $sizeLabel) . '/design/' . Str::slug($sample['name'])) }}"
                                class="block h-full flex flex-col justify-between">
                                {{-- Top Image Area --}}
                                <div class="h-28 md:h-56 bg-slate-100 border-b border-slate-100 overflow-hidden relative">
                                    <img src="{{ $sample['img'] }}" alt="{{ $sample['name'] }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>

                                {{-- Bottom Info Area --}}
                                <div class="px-2 py-1.5 md:p-5 bg-white flex-1 flex items-center">
                                    <h3
                                        class="font-bold md:font-extrabold text-slate-900 text-[11px] md:text-sm group-hover:text-emerald-700 transition-colors line-clamp-1">
                                        {{ $sample['name'] }}
                                    </h3>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @endforelse

            </div>

        </div>
    </div>
@endsection
