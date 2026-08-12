<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Qrinto - Premium Custom Printing & Photo Cards Studio')</title>
    <meta name="description" content="@yield('meta_description', 'Design and order custom greeting cards, photo prints, business cards, and personalized stationery with Qrinto. Instant online customization & fast store pickup.')">
    <meta name="keywords" content="@yield('meta_keywords', 'custom printing, photo greeting cards, birthday cards, business cards, photo prints, custom stationery, Qrinto print studio')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="alternate icon" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/svg-logo/Q-only.svg') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:title" content="@yield('title', 'Qrinto - Premium Custom Printing & Photo Cards Studio')">
    <meta property="og:description" content="@yield('meta_description', 'Design and order custom greeting cards, photo prints, business cards, and personalized stationery with Qrinto.')">
    <meta property="og:image" content="@yield('og_image', asset('logo/Qrinto-logo-small.png'))">
    <meta property="og:site_name" content="Qrinto Print Studio">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Qrinto - Premium Custom Printing & Photo Cards Studio')">
    <meta name="twitter:description" content="@yield('meta_description', 'Design and order custom greeting cards, photo prints, business cards, and personalized stationery with Qrinto.')">
    <meta name="twitter:image" content="@yield('og_image', asset('logo/Qrinto-logo-small.png'))">

    @yield('json_ld')

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
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10">
            <div class="flex h-20 items-center justify-between gap-6">
                {{-- Left Side: Logo --}}
                <div class="flex items-center gap-5 shrink-0">
                    {{-- Logo --}}
                    <a href="{{ route('flow-pc.index') }}" class="flex items-center gap-3 shrink-0 group">
                        <img src="{{ asset('logo/Qrinto-logo-small.png') }}" alt="Qrinto Logo"
                            class="h-10 sm:h-12 w-auto object-contain group-hover:scale-105 transition-transform duration-300">
                    </a>
                </div>

                {{-- Right Side: Location + Cart + Menu --}}
                <div class="flex items-center gap-3 shrink-0">
                    {{-- Active Store Selector --}}
                    @include('layouts.pc.store')

                    {{-- Cart Button --}}
                    <a href="{{ route('flow-pc.cart.index') }}" id="cart-btn-pc"
                        class="relative h-11 flex items-center gap-2 px-4 rounded-xl hover:bg-slate-100 text-slate-800 hover:text-brand-600 font-bold text-xs uppercase tracking-wider transition-all duration-200 cursor-pointer active:scale-95">
                        <i data-lucide="shopping-cart" class="w-4 h-4 text-slate-600"></i>
                        <span>Cart</span>
                        @if ($cartCount > 0)
                            <span id="cart-count-pc"
                                class="bg-brand-500 text-white text-[11px] font-extrabold px-2 py-0.5 rounded-full shadow-xs">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    {{-- Menu Icon Button --}}
                    <button type="button" @click="pcMenu = true; $nextTick(() => lucide.createIcons())"
                        class="h-11 flex items-center gap-2 px-4 rounded-xl hover:bg-slate-100 text-slate-800 hover:text-brand-600 font-bold text-xs uppercase tracking-wider transition-all cursor-pointer active:scale-95">
                        <i data-lucide="menu" class="w-4 h-4 text-slate-600"></i>
                        <span>Menu</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Luxury Sentina-Style Full-Screen Overlay Menu --}}
    <div x-show="pcMenu" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-98" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-98"
        class="fixed inset-0 w-screen h-screen min-h-screen z-[9999] bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white flex flex-col justify-between p-6 sm:p-12 overflow-y-auto"
        style="display: none;" x-cloak>

        <!-- Giant MENU Watermark Background Text -->
        <div
            class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden z-0">
            <span
                class="text-[140px] sm:text-[240px] xl:text-[340px] font-black text-white/[0.04] uppercase tracking-widest leading-none">MENU</span>
        </div>

        {{-- Top Bar: Logo + Pill CLOSE Button --}}
        <div class="max-w-7xl mx-auto w-full flex items-center justify-between relative z-10">
            <a href="{{ route('flow-pc.index') }}" @click="pcMenu = false"
                class="flex items-center gap-3 shrink-0 group">
                <img src="{{ asset('logo/Qrinto-logo-small.png') }}" alt="Qrinto Logo"
                    class="h-10 sm:h-12 w-auto object-contain brightness-0 invert group-hover:scale-105 transition-transform duration-300">
            </a>

            <button @click="pcMenu = false" type="button"
                class="flex items-center gap-2.5 px-6 py-2.5 rounded-full border border-white/30 hover:border-white hover:bg-white hover:text-slate-900 text-white text-xs font-black uppercase tracking-widest transition-all duration-300 shadow-lg cursor-pointer group active:scale-95">
                <i data-lucide="x"
                    class="w-4 h-4 text-slate-300 group-hover:text-slate-900 group-hover:rotate-90 transition-all duration-300"></i>
                <span>Close</span>
            </button>
        </div>

        {{-- Centered Large Typography Navigation Links --}}
        <div
            class="flex-1 flex flex-col items-center justify-center text-center my-6 relative z-10 space-y-5 sm:space-y-7">
            <a href="{{ route('flow-pc.index') }}" @click="pcMenu = false"
                class="text-3xl sm:text-5xl xl:text-6xl font-extrabold text-white hover:text-brand-400 hover:scale-105 transition-all duration-300 tracking-tight block">
                Home Studio
            </a>

            <a href="{{ route('flow-pc.qrinto') }}" @click="pcMenu = false"
                class="text-3xl sm:text-5xl xl:text-6xl font-extrabold text-slate-200 hover:text-brand-400 hover:scale-105 transition-all duration-300 tracking-tight block">
                Custom Upload & Print
            </a>

            <a href="{{ route('flow-pc.index') }}#products" @click="pcMenu = false"
                class="text-3xl sm:text-5xl xl:text-6xl font-extrabold text-slate-200 hover:text-brand-300 hover:scale-105 transition-all duration-300 tracking-tight block">
                Print Categories
            </a>

            <a href="{{ route('flow-pc.index') }}#how-it-works" @click="pcMenu = false"
                class="text-3xl sm:text-5xl xl:text-6xl font-extrabold text-slate-200 hover:text-brand-400 hover:scale-105 transition-all duration-300 tracking-tight block">
                How It Works
            </a>

            <a href="{{ route('flow-pc.find-store') }}" @click="pcMenu = false"
                class="text-3xl sm:text-5xl xl:text-6xl font-extrabold text-slate-200 hover:text-brand-400 hover:scale-105 transition-all duration-300 tracking-tight block">
                Find Store Locations
            </a>

            <a href="{{ route('flow-pc.track.form') }}" @click="pcMenu = false"
                class="text-3xl sm:text-5xl xl:text-6xl font-extrabold text-slate-200 hover:text-amber-400 hover:scale-105 transition-all duration-300 tracking-tight block">
                Track Order
            </a>
        </div>

        {{-- Bottom Utility Bar --}}
        <div
            class="max-w-7xl mx-auto w-full flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-medium text-slate-400 border-t border-white/10 pt-6 relative z-10">
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6 text-slate-300">
                <a href="#products" @click="pcMenu = false" class="hover:text-white transition-colors">Canvas Art</a>
                <span>•</span>
                <a href="#products" @click="pcMenu = false" class="hover:text-white transition-colors">Photo
                    Books</a>
                <span>•</span>
                <a href="#products" @click="pcMenu = false" class="hover:text-white transition-colors">Custom
                    Apparel</a>
                <span>•</span>
                <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank"
                    class="hover:text-white transition-colors">Terms & Privacy</a>
            </div>

            <div class="flex items-center gap-4 text-slate-400">
                <span>&copy; {{ date('Y') }} Qrinto Custom Print Studio</span>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="flex-1  ">
        @yield('content')
    </main>

    {{-- ===== PREMIUM LUXURY FOOTER ===== --}}
    <footer
        class="w-full bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white relative overflow-hidden border-t border-white/10 pt-20 pb-10">
        <!-- Ambient Glowing Background Orbs -->
        <div class="absolute -top-32 left-1/4 w-96 h-96 bg-brand-500/10 rounded-full blur-[120px] pointer-events-none">
        </div>
        <div
            class="absolute -bottom-32 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-[120px] pointer-events-none">
        </div>

        <div class="max-w-[1400px] mx-auto px-6 sm:px-10 relative z-10">
            <!-- Top Section: Brand Callout & Newsletter -->
            <div class="pb-16 border-b border-white/10 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-6">
                    <a href="{{ route('flow-pc.index') }}" class="flex items-center gap-3 mb-4 shrink-0 group w-fit">
                        <img src="{{ asset('logo/Qrinto-logo-small.png') }}" alt="Qrinto Logo"
                            class="h-10 sm:h-12 w-auto object-contain brightness-0 invert group-hover:scale-105 transition-transform duration-300">
                    </a>
                    <p class="text-slate-400 text-sm sm:text-base max-w-lg leading-relaxed font-medium">
                        Next-generation print studio combining real-time vector editing tools with museum-quality
                        archival printing and fast local store pickup.
                    </p>
                </div>

                <div class="lg:col-span-6">
                    <div class="bg-white/5 backdrop-blur-md p-6 sm:p-8 rounded-3xl border border-white/10 shadow-xl">
                        <h4 class="text-base font-extrabold text-white mb-2">
                            Get Special Studio Offers & Print Guides
                        </h4>
                        <p class="text-xs text-slate-400 mb-4">Join over 50,000+ creators getting exclusive discounts &
                            design tutorials.</p>

                        <form onsubmit="event.preventDefault();" class="flex flex-col sm:flex-row gap-3">
                            <input type="email" placeholder="Enter your email address..."
                                class="bg-white/10 border border-white/20 text-white placeholder-slate-400 text-sm px-4.5 py-3 rounded-xl focus:outline-none focus:border-brand-400 flex-1 backdrop-blur-md">
                            <button type="submit"
                                class="bg-brand-500 hover:bg-brand-600 text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-xl transition-all shadow-lg shrink-0">
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Middle Section: 4 Links Columns -->
            <div class="py-16 grid grid-cols-2 md:grid-cols-4 gap-8 xl:gap-12 border-b border-white/10">
                <!-- Column 1: Print Products -->
                <div>
                    <h5 class="text-xs font-black uppercase tracking-widest text-slate-200 mb-5">
                        Print Products
                    </h5>
                    <ul class="space-y-3 text-xs sm:text-sm font-medium text-slate-400">
                        <li><a href="#products" class="hover:text-white transition-colors">Custom Canvas Prints</a>
                        </li>
                        <li><a href="#products" class="hover:text-white transition-colors">Hardcover Photo Books</a>
                        </li>
                        <li><a href="#products" class="hover:text-white transition-colors">Custom T-Shirts &
                                Apparel</a></li>
                        <li><a href="#products" class="hover:text-white transition-colors">Greeting Cards & Gifts</a>
                        </li>
                        <li><a href="#products" class="hover:text-white transition-colors">Acrylic Wall Art</a></li>
                    </ul>
                </div>

                <!-- Column 2: Design Editor -->
                <div>
                    <h5 class="text-xs font-black uppercase tracking-widest text-slate-200 mb-5">
                        Design Studio
                    </h5>
                    <ul class="space-y-3 text-xs sm:text-sm font-medium text-slate-400">
                        <li><a href="#products" class="hover:text-white transition-colors">Vector Editor Suite</a>
                        </li>
                        <li><a href="{{ route('flow-pc.track.form') }}"
                                class="hover:text-white transition-colors">Track Your Order</a></li>
                        <li><a href="{{ route('flow-pc.find-store') }}"
                                class="hover:text-white transition-colors">Local Store Pickup</a></li>
                        <li><a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank"
                                class="hover:text-white transition-colors">Print Quality Guide</a></li>
                    </ul>
                </div>

                <!-- Column 3: Store & Support -->
                <div>
                    <h5 class="text-xs font-black uppercase tracking-widest text-slate-200 mb-5">
                        Stores & Service
                    </h5>
                    <ul class="space-y-3 text-xs sm:text-sm font-medium text-slate-400">
                        <li><a href="{{ route('flow-pc.find-store') }}"
                                class="hover:text-white transition-colors">Find Nearby Studio</a></li>
                        <li><a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank"
                                class="hover:text-white transition-colors">Terms & Privacy Notice</a></li>
                        <li><a href="{{ route('flow-pc.track.form') }}"
                                class="hover:text-white transition-colors">Order Lookup</a></li>
                        <li><a href="mailto:info@qrinto.com" class="hover:text-white transition-colors">Customer
                                Support</a></li>
                    </ul>
                </div>

                <!-- Column 4: Quick Guarantees & Noritsu Badge -->
                <div>
                    <h5 class="text-xs font-black uppercase tracking-widest text-slate-200 mb-5">
                        Quality Standard
                    </h5>
                    <div class="space-y-3 text-xs text-slate-400 font-medium">
                        <div
                            class="bg-white/5 border border-white/10 p-3.5 rounded-xl flex items-center justify-between">
                            <span class="text-[11px] text-slate-300">Powered by high precision</span>
                            <a href="https://www.noritsu.com/" target="_blank"
                                class="hover:opacity-80 transition-opacity">
                                <img src="{{ asset('nortisu.webp') }}" alt="Noritsu"
                                    class="w-20 bg-white/90 p-1 rounded">
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright & Legal Links Bar -->
            <div
                class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-medium text-slate-400">
                <p>&copy; {{ date('Y') }} Qrinto Custom Print Studio. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank"
                        class="hover:text-white transition-colors">Terms & Privacy</a>
                    <a href="{{ route('flow-pc.find-store') }}" class="hover:text-white transition-colors">Store
                        Locations</a>
                    <a href="{{ route('flow-pc.track.form') }}" class="hover:text-white transition-colors">Track
                        Order</a>
                </div>
            </div>
        </div>
    </footer>

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
