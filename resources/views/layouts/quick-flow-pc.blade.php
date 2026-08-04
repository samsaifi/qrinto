<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Qrinto - Custom Print Studio')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Dancing+Script:wght@700&family=Playfair+Display:ital,wght@0,700;1,700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-tap-highlight-color: transparent;
        }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom, 1rem);
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 99px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>

<body class="antialiased select-none min-h-screen flex flex-col bg-slate-50">
    @php
        $activeStore = session()->has('active_store_id')
            ? \App\Models\Store::find(session('active_store_id'))
            : null;
        $cartCount = 0;
        $cartSubtotal = 0;
        try {
            $cart = app(\App\Services\CartService::class)->getCart();
            $cartCount = $cart->item_count;
            $cartSubtotal = $cart->subtotal;
        } catch (\Exception $e) {}
    @endphp

    {{-- Top Announcement & Utility Bar --}}
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 sm:px-6 lg:px-10 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            {{-- Left: Store Status / Pickup info --}}
            <div class="flex items-center gap-3 truncate">
                @if($activeStore)
                    <span class="inline-flex items-center gap-1.5 text-emerald-400 font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Connected: {{ $activeStore->store_name }}
                    </span>
                    <span class="text-slate-600 hidden sm:inline">•</span>
                    <span class="text-slate-400 truncate hidden sm:inline">
                        <i data-lucide="map-pin" class="w-3 h-3 inline-block mr-1"></i>{{ $activeStore->city }}, {{ $activeStore->state }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-amber-400 font-bold">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-400"></i>
                        No Store Selected
                    </span>
                    <a href="{{ route('flow-pc.find-store') }}" class="text-slate-300 underline font-semibold hover:text-white transition-colors">
                        Select a branch for local pickup &rarr;
                    </a>
                @endif
            </div>

            {{-- Right: Quick Utility Links --}}
            <div class="flex items-center gap-5 shrink-0 text-slate-400 font-medium">
                <a href="{{ route('flow-pc.track.form') }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                    <span class="hidden sm:inline">Track Order</span>
                </a>
                <button type="button" data-qt-restart="{{ route('flow-pc.find-store') }}" class="hover:text-white transition-colors flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span class="hidden sm:inline">How it Works</span>
                </button>
                <div class="h-3 w-px bg-slate-700 hidden sm:block"></div>
                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider hidden md:inline">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 inline-block mr-1 text-brand-400"></i>
                    100% Quality Guaranteed
                </span>
            </div>
        </div>
    </div>

    {{-- Main Desktop Header --}}
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
            <div class="flex h-20 items-center justify-between gap-6">
                {{-- Logo --}}
                <a href="{{ route('flow-pc.index') }}" class="flex items-center gap-3 shrink-0 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-brand-600 to-brand-700 text-white font-black text-xl flex items-center justify-center shadow-md shadow-brand-600/30 group-hover:scale-105 transition-all duration-300">
                        Q
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-brand-600 transition-colors">Qrinto</span>
                        <span class="text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">Print Studio</span>
                    </div>
                </a>

                {{-- Desktop Navigation (Centered) --}}
                @include('layouts.pc.nav')

                {{-- Store Selector & Actions (Right side) --}}
                <div class="flex items-center gap-4 shrink-0">
                    {{-- Active Store Selector --}}
                    @include('layouts.pc.store')

                    {{-- Cart Button --}}
                    <a href="{{ route('flow-pc.cart.index') }}" id="cart-btn-pc"
                        class="relative flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 border border-slate-200/80 hover:border-brand-200 rounded-2xl text-slate-800 text-sm font-extrabold transition-all duration-200 group shadow-xs">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-slate-600 group-hover:text-brand-600 transition-colors"></i>
                        <span class="hidden sm:inline">Cart</span>
                        @if($cartCount > 0)
                            <span id="cart-count-pc" class="bg-brand-600 text-white text-[11px] font-black px-2 py-0.5 rounded-full shadow-xs">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    {{-- Primary CTA Button --}}
                    <a href="{{ route('flow-pc.index') }}"
                        class="hidden sm:flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-extrabold rounded-2xl text-sm shadow-md shadow-brand-600/20 hover:shadow-lg hover:shadow-brand-600/30 transition-all duration-200 active:scale-95">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Start Print Order</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1 px-4 sm:px-6 lg:px-10 py-6">
        <div class="max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    @include('helper.footer')

    <script>
        // Init Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        window.__currency = @json(\App\Services\CurrencyService::toArray());
        window.__price = function(amount, decimals) {
            decimals = decimals !== undefined ? decimals : 2;
            var converted = parseFloat(amount) * (window.__currency.rate || 1);
            return window.__currency.symbol + converted.toFixed(decimals);
        };
    </script>
    @stack('scripts')
</body>

</html>