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
    <div x-data="{ activeCategory: 'all', searchQuery: '' }" class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[1240px] mx-auto">

            {{-- Back Navigation --}}
            <div class="mb-6">
                <a href="{{ route('canonical.cards') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                    <span>← {{ $parentName }}</span>
                </a>
            </div>

            {{-- Title Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">
                    Pick a starting point
                </h1>
                <p class="text-sm text-slate-500 font-normal mt-1.5">
                    {{ $titleLabel }}, {{ $sizeLabel }}. Every template can be changed once it is open.
                </p>
            </div>

            {{-- Category Filter Pills --}}
            <div class="flex items-center gap-2 mb-10 flex-wrap">
                <button type="button" @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-[#112419] text-white font-bold' :
                        'bg-white text-slate-700 border border-slate-200/90 hover:border-slate-300 font-medium'"
                    class="px-4 py-2 rounded-full text-xs transition-all cursor-pointer shadow-2xs">
                    All
                </button>

                @foreach ($categories as $cat)
                    @php $catCount = $templates->where('category_id', $cat->id)->count(); @endphp
                    @if ($catCount > 0 || $loop->iteration <= 5)
                        <button type="button" @click="activeCategory = '{{ $cat->id }}'"
                            :class="activeCategory === '{{ $cat->id }}' ? 'bg-[#112419] text-white font-bold' :
                                'bg-white text-slate-700 border border-slate-200/90 hover:border-slate-300 font-medium'"
                            class="px-4 py-2 rounded-full text-xs transition-all cursor-pointer shadow-2xs">
                            {{ $cat->name }}
                        </button>
                    @endif
                @endforeach
            </div>

            {{-- 6-Column Templates Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">

                {{-- CARD 1: Start Blank (Only shown when activeCategory is 21) --}}
                <div x-show="activeCategory === '21'"
                    class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
                    <a href="{{ url($type->slug . '/' . Str::slug($titleLabel) . '/' . str_replace(' × ', 'x', $sizeLabel) . '/design') }}"
                        class="block h-full flex flex-col justify-between">
                        {{-- Top Image Area --}}
                        <div
                            class="bg-[#f2f7f2] h-56 sm:h-64 flex flex-col items-center justify-center p-4 border-b border-slate-100 relative group-hover:bg-[#ebf3eb] transition-colors">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-emerald-800 text-2xl font-light mb-1">
                                +
                            </div>
                            <span class="text-xs font-bold text-emerald-800">
                                Start blank
                            </span>
                        </div>

                        {{-- Bottom Info Area --}}
                        <div class="p-5 bg-white flex-1 flex items-center">
                            <h3 class="font-extrabold text-slate-900 text-sm">
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
                        class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">

                        <a href="{{ url($type->slug . '/' . Str::slug($titleLabel) . '/' . str_replace(' × ', 'x', $sizeLabel) . '/design/' . $tpl->slug) }}"
                            class="block h-full flex flex-col justify-between">
                            {{-- Top Image Area (Real image instead of colorful box) --}}
                            <div class="h-56 sm:h-64 bg-slate-100 border-b border-slate-100 overflow-hidden relative">
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
                            <div class="p-5 bg-white flex-1 flex items-center">
                                <h3
                                    class="font-extrabold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors line-clamp-1">
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
                            class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
                            <a href="{{ url($type->slug . '/' . Str::slug($titleLabel) . '/' . str_replace(' × ', 'x', $sizeLabel) . '/design/' . Str::slug($sample['name'])) }}"
                                class="block h-full flex flex-col justify-between">
                                {{-- Top Image Area --}}
                                <div class="h-56 sm:h-64 bg-slate-100 border-b border-slate-100 overflow-hidden relative">
                                    <img src="{{ $sample['img'] }}" alt="{{ $sample['name'] }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>

                                {{-- Bottom Info Area --}}
                                <div class="p-5 bg-white flex-1 flex items-center">
                                    <h3
                                        class="font-extrabold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors line-clamp-1">
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
