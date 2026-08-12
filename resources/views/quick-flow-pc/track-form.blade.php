@extends('layouts.quick-flow-pc')

@section('title', 'Track Your Print Order Status — Qrinto')
@section('meta_description', 'Track your Qrinto print order in real-time. Enter your order number and contact details to check print and store pickup status.')
@section('meta_keywords', 'track order, Qrinto order tracking, print job status, order lookup Qrinto')
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
        background-color: #FCFBF9;
        background-image: 
            radial-gradient(at 0% 0%, rgba(214, 95, 50, 0.06) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(185, 79, 40, 0.06) 0px, transparent 50%),
            radial-gradient(circle at 50% 50%, rgba(250, 247, 244, 0.5) 0px, transparent 100%);
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
        <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold mb-6">
            <a href="{{ route('flow-pc.index') }}" class="text-slate-500 hover:text-brand-600 transition-colors flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Home</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
            <span class="text-slate-900 font-extrabold">Track Order</span>
        </nav>

        {{-- Main Hero Header Card --}}
        <div class="text-center max-w-2xl mx-auto mb-10 fade-up">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-100 text-brand-600 text-xs font-extrabold mb-3">
                <i data-lucide="package-search" class="w-3.5 h-3.5"></i>
                Real-Time Status Lookup
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight">
                Track Your <span class="text-slate-950 italic">Print Order</span>
            </h1>
            <p class="text-slate-500 text-sm sm:text-base font-medium mt-3 leading-relaxed">
                Enter your order tracking number below to view real-time fulfillment, printing status, and store pickup details.
            </p>
        </div>

        {{-- Search Form Box Container --}}
        <div class="max-w-lg mx-auto mb-14 fade-up" style="animation-delay: 0.1s">
            
            {{-- Error Session Alert Banner --}}
            @if(session('error'))
                <div class="w-full bg-red-50/90 border-2 border-red-200 text-red-700 p-4 mb-6 rounded-2xl text-left text-xs font-bold flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="glass-card border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-slate-200/50 relative overflow-hidden">
                
                {{-- Decorative Line --}}
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-brand-500"></div>

                <form action="{{ route('flow-pc.track') }}" method="POST" class="w-full space-y-6">
                    @csrf
                    
                    <div class="text-left">
                        <label for="order_number" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                            Order Tracking Code <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                <i data-lucide="hash" class="w-5 h-5"></i>
                            </div>
                            <input type="text" id="order_number" name="order_number" required
                                   placeholder="e.g. ORD-20260806-5A3B4"
                                   class="w-full bg-white border-2 border-slate-200/90 rounded-2xl py-4 pl-12 pr-4 text-slate-900 font-extrabold focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-base shadow-2xs"
                                   value="{{ old('order_number') }}">
                        </div>
                        @error('order_number')
                            <p class="text-red-500 text-xs font-bold mt-2 ml-1 flex items-center gap-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="shimmer-cta w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-4 px-6 rounded-2xl shadow-xl shadow-brand-500/20 hover:shadow-brand-500/30 transition-all duration-300 flex items-center justify-center gap-3 text-base cursor-pointer tracking-wide active:scale-[0.99]">
                        <i data-lucide="search" class="w-5 h-5"></i>
                        Track Order Now
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- 3 Features Grid Below Form --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto fade-up" style="animation-delay: 0.2s">
            <div class="glass-card p-6 rounded-3xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center shrink-0">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-base text-slate-900">Live Stage Tracking</h4>
                    <p class="text-xs text-slate-500 font-medium mt-1">Track exact prep, printing, and packaging progress in real time.</p>
                </div>
            </div>

            <div class="glass-card p-6 rounded-3xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="store" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-base text-slate-900">Store Pickup Info</h4>
                    <p class="text-xs text-slate-500 font-medium mt-1">View pickup location, hours, and store contact information.</p>
                </div>
            </div>

            <div class="glass-card p-6 rounded-3xl border border-slate-200/80 shadow-xs flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-base text-slate-900">Instant Updates</h4>
                    <p class="text-xs text-slate-500 font-medium mt-1">Receive automated email & SMS alerts when your print is ready.</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
