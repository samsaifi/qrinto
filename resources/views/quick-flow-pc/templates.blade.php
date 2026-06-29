@extends('layouts.quick-flow-pc')

@section('title', 'Choose a template')
@section('header_title', $type->name)

@section('content')
<div class="space-y-6" x-data="{ activeCategory: 'all' }">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-extrabold text-slate-900 flex items-center gap-4">
                <a href="javascript:history.back()" class="w-10 h-10 rounded-full bg-white border-2 border-slate-100 flex items-center justify-center hover:bg-slate-50 transition-colors">
                    <i data-lucide="arrow-left" class="w-3 h-3 text-slate-600"></i>
                </a>
                {{ $type->name }}
            </h1>
            <p class="text-slate-500 font-medium text-xs md:ml-14">Choose a template</p>
        </div>
        @if($type->price)
        <div class="text-right">
            <div class="flex items-center gap-2 justify-end">
                @if($type->old_price && $type->old_price > $type->price)
                <span class="text-slate-400 line-through text-sm">{{ \App\Services\CurrencyService::format($type->old_price) }}</span>
                @endif
                <span class="text-2xl font-black text-slate-900 font-display">{{ \App\Services\CurrencyService::format($type->price) }}</span>
            </div>
            <p class="text-[10px] font-bold text-brand-600 uppercase tracking-widest bg-brand-50 px-2 py-0.5 rounded-full inline-block mt-1">Starting Price</p>
        </div>
        @endif
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex flex-wrap gap-2 overflow-x-auto pb-4 scrollbar-hide -mx-6 px-6">
        <button
            @click="activeCategory = 'all'"
            :class="activeCategory === 'all' ? 'bg-brand-500 text-white border-brand-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            class="px-3 py-1 rounded-full border text-xs font-bold whitespace-nowrap transition-colors">
            All
        </button>
        @foreach($categories as $cat)
        @php
        $catProductCount = $templates->where('category_id', $cat->id)->count();
        @endphp

        @if($catProductCount > 0)
        <button
            @click="activeCategory = {{ $cat->id }}"
            :class="activeCategory === {{ $cat->id }} ? 'bg-brand-500 text-white border-brand-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            class="px-3 py-1 rounded-full border text-xs font-bold whitespace-nowrap transition-colors">
            {{ $cat->name }} ({{ $catProductCount }})
        </button>
        @endif
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @forelse($templates as $tpl)
        <a href="{{ route('flow-pc.customize', $tpl->slug) }}"
            x-show="activeCategory === 'all' || activeCategory === {{ $tpl->category_id }}"
            class="group relative bg-slate-50   overflow-hidden border-2 border-transparent hover:border-brand-500 transition-all duration-300 shadow-sm hover:shadow-premium"
            style="aspect-ratio: {{ $tpl->aspect_ratio }};">
            @if($tpl->pdf_orientation == 'portrait')
            <!-- display fixed rectangle-vertical icon in div -->
            <div class="absolute top-4 right-4 w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center shadow-lg">
                <i data-lucide="rectangle-vertical" class="w-5 h-5 text-white"></i>
            </div>
            @else
            <!-- display fixed rectangle-horizontal icon in div -->
            <div class="absolute top-4 right-4 w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center shadow-lg">
                <i data-lucide="rectangle-horizontal" class="w-5 h-5 text-white"></i>
            </div>
            @endif
            <img src="{{ $tpl->frame_image_thumbnail ?? 'https://placehold.co/300x400/eee/999?text=' . urlencode($tpl->name) }}"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                alt="{{ $tpl->name }}">

            <div class="absolute bg-black/50 inset-x-0 bottom-0 px-4 py-2 ">
                <p class="text-white text-xs font-bold uppercase tracking-wider">{{ \Illuminate\Support\Str::words($tpl->name, 2, '..') }}</p>
            </div>

            @if(request()->is('*/' . $tpl->slug))
            <div class="absolute top-4 right-4 w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center shadow-lg">
                <i data-lucide="check" class="w-5 h-5 text-white"></i>
            </div>
            @endif
        </a>
        @empty
        <div class="col-span-1 py-12 text-center space-y-2">
            <h3 class="text-lg font-bold text-slate-400">No templates found</h3>
            <p class="text-slate-400 text-sm">Please select a different size or category</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush