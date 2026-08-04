@extends('layouts.quick-flow-pc')

@section('title', 'Track Your Order')
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
    <section class="hero-track-gradient hero-track-pattern -mx-10 -mt-4 px-10 pt-12 pb-16 relative overflow-hidden mb-12">
        <div class="hero-blob-1"></div>
        <div class="hero-blob-2"></div>
        <div class="hero-blob-3"></div>
        <div class="hero-dots"></div>

        <div class="max-w-[1400px] mx-auto relative z-10 text-center">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center justify-center gap-2 text-sm mb-6 fade-up">
                <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-700 font-semibold">Track Order</span>
            </nav>

            <div class="max-w-xl mx-auto">
                <div class="fade-up" style="animation-delay: 0.05s">
                    <span class="inline-flex items-center gap-2 bg-white/80 border border-brand-100 text-brand-600 text-xs font-semibold px-4 py-1.5 rounded-full mb-4 shadow-sm backdrop-blur-sm">
                        <i data-lucide="package-search" class="w-3.5 h-3.5"></i>
                        Real-Time Status Lookup
                    </span>
                </div>

                <h1 class="text-4xl xl:text-5xl font-extrabold text-slate-900 tracking-tight fade-up" style="animation-delay: 0.1s">
                    Track Your <span class="bg-gradient-to-r from-brand-600 to-violet-500 bg-clip-text text-transparent italic" style="font-family: 'Playfair Display', serif;">Order Status</span>
                </h1>
                <p class="text-slate-500 text-base mt-3 fade-up" style="animation-delay: 0.15s">
                    Enter your order number below to check the real-time progress of your print order.
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-md mx-auto px-4">
        @if(session('error'))
            <div class="w-full bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-2xl text-left text-sm font-medium shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-xl shadow-brand-500/5">
            <form action="{{ route('flow-pc.track') }}" method="POST" class="w-full space-y-5">
                @csrf
                
                <div class="relative text-left">
                    <label for="order_number" class="block text-xs font-bold text-slate-500 uppercase tracking-widest pl-2 mb-2">Order Number</label>
                    <div class="relative">
                        <i data-lucide="hash" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"></i>
                        <input type="text" id="order_number" name="order_number" required
                               placeholder="e.g. ORD-20260323... "
                               class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl py-4 pl-12 pr-4 text-slate-900 font-bold focus:border-brand-500 focus:bg-white focus:ring-0 transition-all outline-none"
                               value="{{ old('order_number') }}">
                    </div>
                    @error('order_number')
                        <p class="text-red-500 text-xs mt-1.5 pl-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" 
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-extrabold py-4 rounded-2xl shadow-lg shadow-brand-600/25 transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-base">
                    <i data-lucide="search" class="w-5 h-5"></i>
                    Track Order Now
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
