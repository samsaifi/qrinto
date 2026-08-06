@extends('layouts.quick-flow-pc')

@section('title', 'Track Order ' . $order->order_number)
@section('header_title', 'Track Order')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .shimmer-cta {
        position: relative;
        overflow: hidden;
    }
    .shimmer-cta::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 50%;
        height: 200%;
        background: linear-gradient(60deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(25deg);
        transition: all 0.75s ease;
    }
    .shimmer-cta:hover::after {
        left: 140%;
    }

    .ambient-bg {
        background-color: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(236, 72, 153, 0.05) 0px, transparent 50%),
            radial-gradient(circle at 50% 50%, rgba(241, 245, 249, 0.5) 0px, transparent 100%);
    }

    .fade-up {
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="ambient-bg min-h-screen py-8 -mt-6 font-sans text-slate-900">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Breadcrumb Navigation --}}
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-6">
            <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
            <a href="{{ route('flow-pc.track') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors">
                Track Order
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
            <span class="text-slate-900 font-bold">{{ $order->order_number }}</span>
        </nav>

        {{-- Main Page Title Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-slate-200/70 shadow-xs">
            <div class="flex items-center gap-4">
                <a href="{{ route('flow-pc.track') }}"
                    class="w-10 h-10 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-center text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition-all shrink-0 active:scale-95"
                    title="Search Another Order">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-brand-50 border border-brand-100 text-brand-600 text-xs font-extrabold mb-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> Placed on {{ $order->created_at->format('M d, Y &bull; h:i A') }}
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                        Order <span class="bg-gradient-to-r from-brand-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent italic">{{ $order->order_number }}</span>
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-center">
                <span class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 bg-brand-50 border border-brand-200/80 px-4 py-2 rounded-2xl shadow-2xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
                    Status: {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        {{-- 2-Column Full Desktop Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- LEFT COLUMN: Live Tracker & Store Info --}}
            <div class="lg:col-span-7 space-y-6">
                
                {{-- Tracker Component Card --}}
                <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-sm">
                    <div class="flex items-center gap-2.5 mb-6 pb-4 border-b border-slate-200/80">
                        <div class="w-8 h-8 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                            <i data-lucide="activity" class="w-4.5 h-4.5"></i>
                        </div>
                        <h2 class="text-base font-black text-slate-900 tracking-tight">Fulfillment Stage Timeline</h2>
                    </div>

                    @include('quick-flow-pc.partials.tracker', ['order' => $order])
                </div>

                {{-- Fulfillment Store Details Card --}}
                @if($order->store)
                <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-sm">
                    <div class="flex items-center gap-3.5 mb-5 pb-4 border-b border-slate-200/80">
                        <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shrink-0">
                            <i data-lucide="store" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Fulfillment Store Location</h3>
                            <p class="text-xs text-slate-500 font-medium">Pickup address for this print order</p>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h4 class="font-extrabold text-base text-slate-900">{{ $order->store->store_name }}</h4>
                            @if($order->store->address)
                                <p class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $order->store->address }}
                                </p>
                            @endif
                        </div>
                        @if($order->store->phone)
                            <a href="tel:{{ $order->store->phone }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-600 text-xs font-bold transition-all border border-slate-200/80 shrink-0">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i> Call Store
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Order Details & Actions Sidebar --}}
            <div class="lg:col-span-5 sticky top-24 space-y-6">
                <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-2xl shadow-slate-200/50 relative overflow-hidden space-y-6">
                    
                    {{-- Top Multi-Color Gradient Line --}}
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-500 via-indigo-500 to-purple-600"></div>

                    {{-- Summary Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Order Summary</h3>
                        <span class="inline-flex items-center gap-1 text-[11px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i> Verified
                        </span>
                    </div>

                    {{-- Specs List --}}
                    <div class="divide-y divide-slate-100">
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Product Name</span>
                            <span class="font-extrabold text-slate-900 text-right max-w-[200px] truncate">{{ $order->items->first()?->product_name ?? 'Custom Print' }}</span>
                        </div>
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Quantity</span>
                            <span class="font-extrabold text-slate-900">{{ $order->items->first()?->quantity ?? 1 }}</span>
                        </div>
                        @if($order->store)
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Fulfillment Store</span>
                            <span class="font-extrabold text-slate-900 text-right max-w-[180px] truncate">{{ $order->store->store_name }}</span>
                        </div>
                        @endif
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Payment Status</span>
                            @if($order->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200/60">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i> Paid Online
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200/60">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i> Pay at Counter
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Dark Luxury Grand Total Card --}}
                    <div class="bg-slate-900 text-white rounded-2xl p-5 border border-slate-800 shadow-xl relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-brand-500/20 rounded-full blur-xl pointer-events-none"></div>
                        <div class="flex justify-between items-baseline mb-1 relative z-10">
                            <span class="text-sm font-bold text-slate-300">Total Paid</span>
                            <span class="text-3xl font-black text-white tracking-tight">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
                        </div>
                        <p class="text-[11px] font-medium text-slate-400 text-right relative z-10">Includes taxes & print setup</p>
                    </div>

                    {{-- Actions CTA Buttons --}}
                    <div class="space-y-3 pt-1">
                        <a href="{{ route('flow-pc.index') }}"
                            class="shimmer-cta w-full bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 hover:from-brand-600 hover:to-indigo-600 text-white font-black py-4 px-6 rounded-2xl shadow-xl shadow-slate-900/20 hover:shadow-brand-500/30 transition-all duration-300 flex items-center justify-center gap-3 text-base no-underline tracking-wide active:scale-[0.99] cursor-pointer">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                            Continue Shopping
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>

                        <a href="{{ route('flow-pc.track') }}"
                            class="w-full bg-white border-2 border-slate-200/90 hover:border-slate-300 hover:bg-slate-50 text-slate-800 font-extrabold py-3.5 rounded-2xl shadow-xs transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-sm no-underline cursor-pointer">
                            <i data-lucide="search" class="w-5 h-5 text-brand-600"></i>
                            <span>Search Another Order</span>
                        </a>
                    </div>

                    {{-- Trust Security Indicators --}}
                    <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-[11px] font-semibold text-slate-500">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                            <span>100% Print Guarantee</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                            <span>Store Pickup</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                            <span>SSL Security</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="headphones" class="w-3.5 h-3.5 text-purple-500 shrink-0"></i>
                            <span>Store Support</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
