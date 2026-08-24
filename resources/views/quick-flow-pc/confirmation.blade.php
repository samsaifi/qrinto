@extends('layouts.quick-flow-pc')

@section('title', 'Order Placed | Qrinto Custom Print Studio')
@section('header_title', 'Order Placed')
@section('meta_robots', 'noindex, nofollow')

@php
    $routePrefix = $routePrefix ?? 'flow.';
    $items = $order->items;
    $firstItem = $items->first();

    // Build dynamic summary text for all order items
    $itemSummaries = [];
    foreach ($items as $item) {
        $customization = $item->customization_data ?? [];
        $pName = $item->product_name ?? ($item->product->name ?? 'Custom Print');
        
        $width = $customization['size_width'] ?? ($item->product->productType->width ?? null);
        $height = $customization['size_height'] ?? ($item->product->productType->height ?? null);
        $sizeStr = ($width && $height) ? (($width + 0) . ' × ' . ($height + 0)) : null;

        $summary = $item->quantity . ' × ' . $pName;
        if ($sizeStr) {
            $summary .= ', ' . $sizeStr;
        }
        $itemSummaries[] = $summary;
    }

    $itemsText = implode('; ', $itemSummaries);

    $storeName = $order->store->store_name ?? ($order->store->name ?? ($order->store->title ?? 'Billmeijer Camera'));
    $storeCity = $order->store->city ?? '';

    $ordNum = $order->order_number ?? '';
    $last5 = strlen($ordNum) >= 5 ? substr($ordNum, -5) : $ordNum;
    $prefix = strlen($ordNum) >= 5 ? substr($ordNum, 0, -5) : '';
@endphp

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-16 px-6 lg:px-20 font-sans flex items-center justify-center">
        <div class="max-w-[640px] w-full text-left flex flex-col items-start">

            {{-- Top Green Checkmark Circle --}}
            <div class="w-12 h-12 rounded-full bg-[#eaf3ea] flex items-center justify-center mb-6 text-[#287d3c] shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            {{-- Title Header --}}
            <h1 class="text-3xl sm:text-4xl font-extrabold text-[#112419] tracking-tight mb-5 text-left">
                Order placed
            </h1>

            {{-- Order Code Badge (Light Green Rounded Box with bold last 5 digits) --}}
            <div class="inline-block bg-[#f2f7f2] border border-emerald-100 rounded-xl px-4 py-2.5 mb-6">
                <span class="font-mono text-xs text-slate-700 font-medium tracking-wide">
                    Order {{ $prefix }}<strong class="font-extrabold text-slate-900">{{ $last5 }}</strong> &bull; show this at the counter
                </span>
            </div>

            {{-- Order Description Paragraph --}}
            <p class="text-sm sm:text-base text-slate-600 font-normal leading-relaxed max-w-xl mb-8 text-left">
                {{ $itemsText }}, at <strong class="font-bold text-slate-900">{{ $storeName }}</strong>@if($storeCity), {{ $storeCity }}@endif. Usually ready the same day. An email goes out the moment it is ready.
            </p>

            {{-- Print Something Else Action Link --}}
            <div class="text-left">
                <a href="{{ route($routePrefix . 'index') }}"
                    class="inline-block text-sm font-bold text-[#287d3c] hover:underline transition-all">
                    Print something else
                </a>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Object.keys(localStorage).forEach(key => {
                if (key.startsWith('qrinto_')) {
                    localStorage.removeItem(key);
                }
            });
        });
    </script>
@endpush
