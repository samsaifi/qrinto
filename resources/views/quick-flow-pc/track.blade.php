@extends('layouts.quick-flow-pc')

@section('title', 'Order Details #' . $order->order_number . ' | Qrinto')
@section('header_title', 'Track Order')
@section('meta_robots', 'noindex, nofollow')

@php
    $routePrefix = $routePrefix ?? 'flow.';
    $firstItem = $order->items->first();
    $code = $order->order_number;
    $prefix = strlen($code) > 5 ? substr($code, 0, -5) : '';
    $last5 = strlen($code) > 5 ? substr($code, -5) : $code;
@endphp

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[1100px] mx-auto">

            {{-- Back + Title (single row on mobile) --}}
            <div class="flex items-center gap-2 mb-3 md:flex-col md:items-start md:gap-0 md:mb-6">
                <a href="{{ route($routePrefix . 'track') }}"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ←
                    <span class="hidden md:inline">Search Another Order</span>
                </a>
                <div class="min-w-0 md:mt-4">
                    <h1 class="text-base md:text-2xl font-extrabold text-[#112419] tracking-tight truncate">
                        Order {{ $prefix }}<strong class="font-extrabold text-[#287d3c]">{{ $last5 }}</strong>
                    </h1>
                </div>
            </div>

            {{-- Order meta (desktop: inline with title; mobile: below) --}}
            <div class="hidden md:flex md:items-center md:justify-between md:mb-6">
                <p class="text-xs text-slate-500 font-normal">
                    Placed on {{ $order->created_at->format('M d, Y · h:i A') }}
                </p>
                <span class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-[#287d3c] bg-[#f2f7f2] border border-emerald-200/80 px-3 py-1.5 rounded-full">
                    <span>Show this at the counter:</span>
                    <strong class="font-mono">{{ $prefix }}<span class="font-black underline">{{ $last5 }}</span></strong>
                </span>
            </div>
            <div class="md:hidden mb-3">
                <div class="bg-[#f2f7f2] border border-emerald-100 rounded-xl px-3 py-2 text-center">
                    <span class="font-mono text-[10px] text-[#287d3c] font-bold">Show at counter: {{ $prefix }}<span class="font-black underline">{{ $last5 }}</span></span>
                </div>
            </div>

            {{-- Two Column Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Left Main Card --}}
                <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-6">

                    {{-- Timeline Header --}}
                    <div>
                        <h2 class="font-extrabold text-[#112419] text-sm mb-4">
                            Fulfillment Timeline
                        </h2>
                        @include('quick-flow-pc.partials.tracker', ['order' => $order])
                    </div>

                    {{-- Store Pickup Location Banner with Operating Hours --}}
                    @if ($order->store)
                        <div class="bg-[#f2f7f2] rounded-2xl p-4 border border-emerald-100/80 space-y-2">
                            <div class="flex items-start gap-2.5">
                                <div class="w-4 h-4 text-[#287d3c] mt-0.5 shrink-0">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="font-bold text-[#112419] text-xs sm:text-sm">
                                        Pickup at {{ $order->store->store_name }}
                                    </h4>
                                    <p class="text-xs text-slate-600 font-normal">
                                        {{ $order->store->address ?? ($order->store->city ?? 'Local Store') }}
                                    </p>
                                    @if ($order->store->opening_time && $order->store->closing_time)
                                        <p class="text-[11px] text-slate-500 font-medium pt-0.5">
                                            🕒 <strong>Store Hours:</strong> {{ $order->store->opening_time }} - {{ $order->store->closing_time }}
                                        </p>
                                    @endif
                                    @if ($order->store->phone)
                                        <p class="text-[11px] text-slate-500 font-medium">
                                            📞 <strong>Store Phone:</strong> {{ $order->store->phone }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Right Column: Summary Card --}}
                <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-2xs space-y-5">
                    <h2 class="font-extrabold text-[#112419] text-sm">
                        Order Summary
                    </h2>

                    {{-- Lines --}}
                    <div class="space-y-2.5 text-xs text-slate-600 font-medium">
                        <div class="flex justify-between items-center">
                            <span>Product</span>
                            <span class="font-bold text-slate-900 truncate max-w-[160px]">{{ $firstItem?->product_name ?? 'Custom Print' }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span>Quantity</span>
                            <span class="font-bold text-slate-900">{{ $firstItem?->quantity ?? 1 }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span>Payment Status</span>
                            @if ($order->payment_status === 'paid')
                                <span class="font-bold text-[#287d3c]">Paid Online</span>
                            @else
                                <span class="font-bold text-slate-800">Pay at the counter when you pick up</span>
                            @endif
                        </div>

                        <div class="flex justify-between items-center">
                            <span>Fulfillment</span>
                            <span class="font-bold text-slate-900">{{ ucfirst($order->status) }}</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex justify-between items-baseline">
                        <span class="text-xs font-extrabold text-[#112419]">
                            {{ $order->payment_status === 'paid' ? 'Total Paid' : 'Amount Due at Pickup' }}
                        </span>
                        <span class="text-lg font-extrabold text-[#287d3c]">
                            {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}
                        </span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-2.5 pt-1">
                        <a href="{{ route($routePrefix . 'index') }}"
                            class="block w-full bg-[#287d3c] hover:bg-emerald-800 text-white font-bold py-3 rounded-xl text-xs text-center transition-all shadow-2xs active:scale-95 cursor-pointer">
                            Print Something Else →
                        </a>

                        <a href="{{ route($routePrefix . 'track') }}"
                            class="block w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 font-bold py-3 rounded-xl text-xs text-center transition-all shadow-2xs active:scale-95 cursor-pointer">
                            Search Another Order
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
