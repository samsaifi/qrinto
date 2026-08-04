@extends('layouts.quick-flow-pc')

@section('title', 'Track Order ' . $order->order_number)
@section('header_title', 'Track Order')

@push('styles')
<style>
    .hero-track-gradient {
        background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #faf0ff 60%, #f0f4ff 100%);
    }
    .hero-track-pattern {
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
    .fade-up {
        animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="pb-24">

    {{-- ===== HERO HEADER ===== --}}
    <section class="hero-track-gradient hero-track-pattern -mx-10 -mt-4 px-10 pt-10 pb-12 relative overflow-hidden mb-10">
        <div class="hero-blob-1"></div>
        <div class="hero-blob-2"></div>
        <div class="hero-blob-3"></div>
        <div class="hero-dots"></div>

        <div class="max-w-[1400px] mx-auto relative z-10">
            <nav class="flex items-center gap-2 text-sm mb-6 fade-up">
                <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <a href="{{ route('flow-pc.track') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors">Track Order</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-700 font-semibold">{{ $order->order_number }}</span>
            </nav>

            <div class="flex items-center justify-between">
                <div class="fade-up" style="animation-delay: 0.05s">
                    <div class="inline-flex items-center gap-2 bg-white/80 border border-brand-100 text-brand-600 text-xs font-semibold px-3.5 py-1 rounded-full mb-2 shadow-sm backdrop-blur-sm">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        Placed on {{ $order->created_at->format('M d, Y &bull; h:i A') }}
                    </div>
                    <h1 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Order <span class="bg-gradient-to-r from-brand-600 to-violet-500 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">{{ $order->order_number }}</span>
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">Live tracking and status timeline for your print order.</p>
                </div>

                <div class="fade-up" style="animation-delay: 0.1s">
                    <span class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 bg-brand-50 border border-brand-100 px-4 py-2 rounded-2xl shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 space-y-8">
        <!-- The Tracker Component -->
        @include('quick-flow-pc.partials.tracker', ['order' => $order])

        <!-- Order Details / Summary -->
        <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-xl shadow-brand-500/5 text-left">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Order Details</h3>
            
            <div class="flex justify-between items-center py-3 border-b border-slate-100">
                <span class="text-slate-500 font-medium text-sm">Product</span>
                <span class="font-bold text-slate-900 text-sm">{{ $order->items->first()?->product_name ?? 'Custom Print' }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-slate-100">
                <span class="text-slate-500 font-medium text-sm">Quantity</span>
                <span class="font-bold text-slate-900 text-sm">{{ $order->items->first()?->quantity ?? 1 }}</span>
            </div>
            @if($order->store)
            <div class="flex justify-between items-center py-3 border-b border-slate-100">
                <span class="text-slate-500 font-medium text-sm">Fulfillment Store</span>
                <span class="font-bold text-slate-900 text-sm">{{ $order->store->store_name }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center pt-4">
                <span class="text-slate-700 font-bold text-sm">Total Paid</span>
                <span class="font-extrabold text-brand-600 text-xl">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
