<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Qrinto - Custom Print Studio')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Dancing+Script:wght@700&family=Playfair+Display:ital,wght@0,700;1,700&family=Space+Mono:wght@700&display=swap"
        rel="stylesheet">

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

<body class="antialiased select-none min-h-screen flex flex-col bg-slate-50" x-data="{ pcMenu: false }">
    @php
        $activeStore = session()->has('active_store_id') ? \App\Models\Store::find(session('active_store_id')) : null;
        $cartCount = 0;
        $cartSubtotal = 0;
        try {
            $cart = app(\App\Services\CartService::class)->getCart();
            $cartCount = $cart->item_count;
            $cartSubtotal = $cart->subtotal;
        } catch (\Exception $e) {
        }
    @endphp

    {{-- Main Desktop Header --}}
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
            <div class="flex h-20 items-center justify-between gap-6">
                {{-- Left Side: Logo + Menu Icon --}}
                <div class="flex items-center gap-5 shrink-0">
                    {{-- Logo --}}
                    <a href="{{ route('flow-pc.index') }}" class="flex items-center gap-3 shrink-0 group">
                        <div
                            class="w-10 h-10 rounded-2xl bg-gradient-to-br from-brand-600 to-brand-700 text-white font-black text-xl flex items-center justify-center shadow-md shadow-brand-600/30 group-hover:scale-105 transition-all duration-300">
                            Q
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="text-xl font-black tracking-tight text-slate-900 group-hover:text-brand-600 transition-colors">Qrinto</span>
                            <span
                                class="text-[10px] font-extrabold tracking-widest text-slate-400 uppercase leading-none">Print
                                Studio</span>
                        </div>
                    </a>

                    {{-- Menu Icon Button --}}
                    <button type="button" @click="pcMenu = true; $nextTick(() => lucide.createIcons())"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-brand-50 text-brand-600 font-extrabold text-sm transition-all duration-200 group cursor-pointer active:scale-95 shadow-xs">
                        <i data-lucide="menu" class="w-5 h-5 text-brand-600"></i>
                        <span class="text-xs">Menu</span>
                    </button>
                </div>

                {{-- Right Side: Selected Store + Cart --}}
                <div class="flex items-center gap-3 shrink-0">
                    {{-- Active Store Selector --}}
                    @include('layouts.pc.store')

                    {{-- Cart Button --}}
                    <a href="{{ route('flow-pc.cart.index') }}" id="cart-btn-pc"
                        class="relative flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 border border-slate-200/80 hover:border-brand-200 rounded-2xl text-slate-800 text-sm font-extrabold transition-all duration-200 group shadow-xs">
                        <i data-lucide="shopping-cart"
                            class="w-4 h-4 text-slate-600 group-hover:text-brand-600 transition-colors"></i>
                        <span>Cart</span>
                        @if ($cartCount > 0)
                            <span id="cart-count-pc"
                                class="bg-brand-600 text-white text-[11px] font-black px-2.5 py-0.5 rounded-full shadow-xs">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Full-Screen Premium Desktop Overlay Menu --}}
    <div x-show="pcMenu" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[100] bg-brand-50 flex flex-col justify-between overflow-y-auto p-6 sm:p-10"
        style="display: none;" x-cloak>

        {{-- Menu Top Header Bar --}}
        <div class="max-w-6xl mx-auto w-full flex items-center justify-between pb-6 border-b border-brand-200/60">
            <div class="flex items-center gap-3">
                <div
                    class="w-11 h-11 bg-white rounded-2xl flex items-center justify-center shadow-md shadow-brand-500/10 border border-brand-100">
                    <span class="text-brand-600 font-black text-2xl">Q</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900 uppercase">Navigation Menu</span>
                    <span class="text-xs text-brand-600 font-bold">Qrinto Custom Print Studio</span>
                </div>
            </div>

            <button @click="pcMenu = false"
                class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white hover:bg-slate-100 text-slate-700 font-extrabold text-sm transition-all border border-slate-200/80 shadow-xs active:scale-95 cursor-pointer">
                <span>Close</span>
                <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
            </button>
        </div>

        {{-- Menu Content Body --}}
        <div class="max-w-6xl mx-auto w-full py-10 flex-1 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left 7 Cols: Major Action Cards & Category Links --}}
            <div class="lg:col-span-7 space-y-6">
                {{-- Main Call to Action: Start New Order --}}
                <a href="{{ route('flow-pc.index') }}" @click="pcMenu = false"
                    class="block group relative overflow-hidden bg-gradient-to-r from-brand-600 to-brand-700 p-8 rounded-[32px] shadow-xl shadow-brand-600/20 transition-all hover:scale-[1.01] active:scale-[0.99]">
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex flex-col text-left">
                            <span class="text-xs font-black text-brand-200 uppercase tracking-[0.2em] mb-1">Start New
                                Flow</span>
                            <span class="text-2xl font-black text-white leading-tight">Create Custom Print Order</span>
                            <p class="text-sm text-brand-100 font-medium mt-1">Design & customize photo prints, canvas,
                                gifts and albums</p>
                        </div>
                        <div
                            class="w-14 h-14 flex-shrink-0 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white group-hover:rotate-90 transition-transform duration-300">
                            <i data-lucide="plus" class="w-8 h-8"></i>
                        </div>
                    </div>
                </a>

                {{-- Direct Upload CTA --}}
                <a href="{{ route('flow-pc.qrinto') }}" @click="pcMenu = false"
                    class="block group relative overflow-hidden bg-white p-6 rounded-[32px] border border-brand-200/80 shadow-md shadow-brand-500/5 transition-all hover:border-brand-400 active:scale-[0.99]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-600 group-hover:bg-brand-600 group-hover:text-white transition-all">
                                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-xs font-black text-brand-600 uppercase tracking-widest mb-1">Already
                                    Have a Design?</span>
                                <span class="text-lg font-black text-slate-900 leading-tight">Custom Upload &
                                    Print</span>
                            </div>
                        </div>
                        <div
                            class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-brand-600 group-hover:text-white transition-all">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </div>
                    </div>
                </a>

                {{-- Categories Grid --}}
                <div
                    class="bg-white p-6 rounded-[32px] border border-brand-200/80 shadow-md shadow-brand-500/5 space-y-4">
                    <h3 class="text-xs font-black text-brand-600 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4"></i> Browse Print Categories
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <a href="{{ route('flow-pc.qrinto') }}" @click="pcMenu = false"
                            class="p-4 rounded-2xl bg-slate-50/80 hover:bg-brand-50 border border-slate-100 hover:border-brand-200 transition-all text-center group">
                            <div
                                class="w-10 h-10 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <i data-lucide="image" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-extrabold text-slate-900 block">Photo Prints</span>
                        </a>
                        <a href="{{ route('flow-pc.find-store') }}" @click="pcMenu = false"
                            class="p-4 rounded-2xl bg-slate-50/80 hover:bg-violet-50 border border-slate-100 hover:border-violet-200 transition-all text-center group">
                            <div
                                class="w-10 h-10 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <i data-lucide="frame" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-extrabold text-slate-900 block">Canvas Art</span>
                        </a>
                        <a href="{{ route('flow-pc.find-store') }}" @click="pcMenu = false"
                            class="p-4 rounded-2xl bg-slate-50/80 hover:bg-rose-50 border border-slate-100 hover:border-rose-200 transition-all text-center group">
                            <div
                                class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <i data-lucide="book-open" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-extrabold text-slate-900 block">Photo Books</span>
                        </a>
                        <a href="{{ route('flow-pc.find-store') }}" @click="pcMenu = false"
                            class="p-4 rounded-2xl bg-slate-50/80 hover:bg-emerald-50 border border-slate-100 hover:border-emerald-200 transition-all text-center group">
                            <div
                                class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                <i data-lucide="gift" class="w-5 h-5"></i>
                            </div>
                            <span class="text-xs font-extrabold text-slate-900 block">Custom Gifts</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right 5 Cols: Store Context & Utility Links --}}
            <div class="lg:col-span-5 space-y-6">
                {{-- Active Store Branch Card --}}
                <div class="bg-white p-6 rounded-[32px] border border-brand-200/80 shadow-md shadow-brand-500/5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span class="text-xs font-black text-emerald-700 uppercase tracking-widest">Active
                            Branch</span>
                    </div>

                    @if ($activeStore)
                        <div
                            class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 mb-4 flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-brand-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                                <i data-lucide="store" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-extrabold text-slate-900 truncate">
                                    {{ $activeStore->store_name }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $activeStore->city }},
                                    {{ $activeStore->state }}</p>
                            </div>
                        </div>
                        <a href="{{ route('flow-pc.find-store') }}" @click="pcMenu = false"
                            class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs uppercase tracking-widest transition-all shadow-md shadow-brand-600/20 active:scale-95">
                            Change Location <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                        </a>
                    @else
                        <a href="{{ route('flow-pc.find-store') }}" @click="pcMenu = false"
                            class="flex items-center gap-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 hover:bg-amber-100 transition-all">
                            <i data-lucide="alert-circle" class="w-6 h-6 text-amber-600"></i>
                            <div class="flex flex-col">
                                <span class="font-extrabold text-sm">No Store Selected</span>
                                <span class="text-xs opacity-80">Choose a local branch to begin</span>
                            </div>
                        </a>
                    @endif
                </div>

                {{-- Track Order Link Card --}}
                <a href="{{ route('flow-pc.track.form') }}" @click="pcMenu = false"
                    class="block bg-white p-5 rounded-[28px] border border-brand-200/80 shadow-sm hover:border-brand-400 transition-all group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-11 h-11 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-600 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                <i data-lucide="package" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="font-extrabold text-slate-900 text-sm block">Track Order</span>
                                <span class="text-xs text-slate-500 font-medium">Check real-time order status</span>
                            </div>
                        </div>
                        <i data-lucide="chevron-right"
                            class="w-5 h-5 text-slate-300 group-hover:text-brand-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </a>

                {{-- Replay Tutorial --}}
                <button type="button" data-qt-restart="{{ route('flow-pc.find-store') }}" @click="pcMenu = false"
                    class="w-full text-left bg-white p-5 rounded-[28px] border border-brand-200/80 shadow-sm hover:border-brand-400 transition-all group cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-11 h-11 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                <i data-lucide="sparkles" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="font-extrabold text-slate-900 text-sm block">Replay Tutorial</span>
                                <span class="text-xs text-slate-500 font-medium">Learn how Qrinto works</span>
                            </div>
                        </div>
                        <i data-lucide="rotate-ccw"
                            class="w-5 h-5 text-slate-300 group-hover:text-amber-600 group-hover:rotate-180 transition-all duration-300"></i>
                    </div>
                </button>
            </div>
        </div>

        {{-- Menu Footer --}}
        <div
            class="max-w-6xl mx-auto w-full pt-6 border-t border-brand-200/60 flex items-center justify-between text-xs text-slate-500 font-medium">
            <span>&copy; {{ date('Y') }} Qrinto Custom Print Studio. All rights reserved.</span>
            <div class="flex items-center gap-6">
                <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank"
                    class="hover:text-brand-600 transition-colors">Terms & Privacy</a>
                <a href="{{ route('flow-pc.find-store') }}" class="hover:text-brand-600 transition-colors">Store
                    Locations</a>
            </div>
        </div>
    </div>

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
