@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-extrabold text-[#111827] mb-3 font-display">
            Print Beautiful Memories
        </h1>
        <p class="text-[15px] text-[#4b5563] max-w-xl mx-auto">
            Select a product below to customize with your own photos and personal touch.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 auto-rows-[1fr]">
        @foreach($products as $product)
            <a href="{{ route('noritsu.editor', $product->slug) }}" class="group block bg-white rounded-2xl overflow-hidden shadow-card border border-[#f3f4f6] hover:shadow-hover transition-all duration-300 flex flex-col no-underline text-inherit transform hover:-translate-y-1">
                <div class="aspect-[4/3] bg-[#f8fafc] flex items-center justify-center p-6 border-b border-[#f3f4f6] relative overflow-hidden">
                    <img src="{{ $product->display_image }}" alt="{{ $product->name }}" class="max-w-full max-h-full object-contain drop-shadow-md group-hover:scale-105 transition-transform duration-500 ease-out" loading="lazy" />
                    <!-- Hover overlay -->
                    <div class="absolute inset-0 bg-brand-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <h3 class="text-[15px] font-bold text-[#1f2937] mb-2 leading-tight group-hover:text-brand-primary transition-colors">
                        {{ $product->name }}
                    </h3>
                    <p class="text-[#6b7280] text-[13px] line-clamp-2 leading-relaxed mb-4 flex-1">
                        {{ $product->short_description ?? 'Customize this premium product with your photos.' }}
                    </p>
                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-[15px] font-bold text-[#111827]">{{ \App\Services\CurrencyService::format($product->base_price) }}</span>
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-brand-primary/10 text-brand-primary group-hover:bg-brand-primary group-hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
