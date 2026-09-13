<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

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
    <script></script>
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
            <div class="flex h-14 md:h-20 items-center justify-between gap-3 md:gap-6">
                {{-- Left Side: Logo --}}
                <div class="flex items-center gap-5 shrink-0">
                    {{-- Logo (returns to store search from every screen) --}}
                    <a href="{{ route('flow.find-store') }}" class="flex items-center gap-3 shrink-0 group">
                        <img src="{{ asset('logo/Qrinto-logo-small.png') }}" alt="Qrinto Logo"
                            class="h-10 sm:h-12 w-auto object-contain group-hover:scale-105 transition-transform duration-300">
                    </a>
                </div>

                {{-- Right Side: Pickup store + Menu --}}
                <div class="flex items-center gap-3 shrink-0">
                    {{-- Active Store Selector (hidden on local print pages, hidden on mobile — mobile bar below) --}}
                    @if (!request()->routeIs('localprint.*'))
                        <div class="hidden md:flex items-center">
                            @include('layouts.pc.store')
                        </div>
                    @endif

                    {{-- Menu Dropdown Container --}}
                    <div class="relative" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
                        {{-- Menu Toggle Button --}}
                        <button type="button"
                            @click="menuOpen = !menuOpen; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                            class="h-11 flex items-center gap-2 px-4 rounded-xl hover:bg-slate-100 text-slate-800 hover:text-brand-600 font-bold text-xs uppercase tracking-wider transition-all cursor-pointer active:scale-95">
                            <i data-lucide="menu" class="w-4 h-4 text-slate-600"></i>
                            <span>Menu</span>
                            <i data-lucide="chevron-down"
                                class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                :class="{ 'rotate-180': menuOpen }"></i>
                        </button>

                        {{-- Dropdown Menu Panel --}}
                        <div x-show="menuOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-56 bg-white border border-slate-200/90 rounded-2xl shadow-xl py-2 z-50 overflow-hidden"
                            style="display: none;" x-cloak>

                            <a href="{{ route('flow.find-store') }}" @click="menuOpen = false"
                                class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-800 hover:bg-slate-50 hover:text-[#287d3c] transition-colors">
                                <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                                <span>Print at a store near you</span>
                            </a>

                            <a href="{{ route('localprint.index') }}" @click="menuOpen = false"
                                class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-800 hover:bg-slate-50 hover:text-[#287d3c] transition-colors">
                                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                                <span>Print on your own 931BL</span>
                            </a>

                            <a href="{{ route('flow.track.form') }}" @click="menuOpen = false"
                                class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-slate-800 hover:bg-slate-50 hover:text-[#287d3c] transition-colors">
                                <i data-lucide="package" class="w-4 h-4 text-slate-400"></i>
                                <span>Track an order</span>
                            </a>

                            <div class="my-1 border-t border-slate-100"></div>

                            <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank"
                                @click="menuOpen = false"
                                class="flex items-center gap-3 px-4 py-2 text-xs font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                                <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
                                <span>Terms and privacy</span>
                            </a>

                            {{-- Auth-aware entry: login when signed out; store panel /
                                 admin dashboard when signed in. --}}
                            @auth
                                @php
                                    $u = auth()->user();
                                    $isAdmin = method_exists($u, 'isAdmin')
                                        ? $u->isAdmin()
                                        : ($u->role ?? '') === 'admin';
                                    $isStoreStaff = in_array($u->role ?? '', ['store_admin', 'storeadmin', 'staff']);
                                    $storeLabel = $u->store->store_name ?? null;
                                @endphp
                                @if ($isAdmin)
                                    <a href="{{ url('/admin/dashboard') }}" @click="menuOpen = false"
                                        class="flex items-center gap-3 px-4 py-2 text-xs font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-400"></i>
                                        <span>Admin dashboard</span>
                                    </a>
                                @elseif ($isStoreStaff)
                                    <a href="{{ route('storepanel.orders') }}" @click="menuOpen = false"
                                        class="flex items-center gap-3 px-4 py-2 text-xs font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                                        <i data-lucide="briefcase" class="w-4 h-4 text-slate-400"></i>
                                        <span class="truncate max-w-[160px]" title="{{ $storeLabel ?? 'Store panel' }}">
                                            {{ $storeLabel ?? 'Store panel' }}
                                        </span>
                                    </a>
                                @else
                                    <a href="{{ url('/store') }}" @click="menuOpen = false"
                                        class="flex items-center gap-3 px-4 py-2 text-xs font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                                        <i data-lucide="briefcase" class="w-4 h-4 text-slate-400"></i>
                                        <span>For stores</span>
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" @click="menuOpen = false"
                                    class="flex items-center gap-3 px-4 py-2 text-xs font-medium text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                                    <i data-lucide="log-in" class="w-4 h-4 text-slate-400"></i>
                                    <span>For stores · Log in</span>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Mobile store bar (below header, visible only on mobile) --}}
    @if (!request()->routeIs('localprint.*'))
        <div class="md:hidden sticky top-[56px] z-40 bg-white border-b border-slate-200/80 px-4 py-2">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    <i data-lucide="map-pin"
                        class="w-4 h-4 shrink-0 {{ $activeStore ? 'text-[#287d3c]' : 'text-slate-400' }}"></i>
                    @if ($activeStore)
                        <span
                            class="text-[13px] font-bold text-slate-800 truncate">{{ $activeStore->store_name }}</span>
                    @else
                        <span class="text-[13px] font-medium text-slate-400">No store selected</span>
                    @endif
                </div>
                <a href="{{ route('flow.find-store') }}"
                    class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#287d3c] text-white text-[11px] font-bold transition active:scale-95">
                    <i data-lucide="search" class="w-3 h-3"></i>
                    {{ $activeStore ? 'Change' : 'Find Store' }}
                </a>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1  ">
        @yield('content')
    </main>

    {{-- ===== PREMIUM LUXURY FOOTER ===== --}}
    <footer class="w-full bg-slate-950   text-white relative overflow-hidden  ">
        <!-- Ambient Glowing Background Orbs -->


        <div class="max-w-7xl mx-auto px-6 sm:px-10 relative z-10">


            <!-- Bottom Copyright, Centered Noritsu Logo & Legal Links Bar -->
            <div
                class="  flex flex-col md:flex-row items-center justify-between gap-6 text-xs font-medium text-slate-400">
                <p> <a href="{{ route('flow.index') }}" class="flex items-center gap-3 mb-4 shrink-0 group w-fit">
                        <img src="{{ asset('images/svg-logo/Qrinto-logo-one-color-white-only.svg') }}"
                            alt="Qrinto Logo"
                            class="h-10 sm:h-12 w-auto object-contain group-hover:scale-105 transition-transform duration-300">
                    </a></p>

                <!-- Orange marked Noritsu Logo in Center (Yellow Mark) -->
                <div class="flex items-center gap-2.5 bg-white/5   px-4 py-2 rounded-xl  ">
                    <span class="text-[11px] text-slate-300 font-semibold">Powered by </span>
                    <a href="https://www.noritsu.com/" target="_blank" class="hover:opacity-90 transition-opacity">
                        <img src="{{ asset('nortisu.webp') }}" alt="Noritsu"
                            class="h-5 w-auto object-contain bg-white/90 px-1.5 py-0.5 rounded">
                    </a>
                </div>

                <div class="flex items-center gap-6">
                    <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank"
                        class="hover:text-white transition-colors">Terms & Privacy</a>
                    <a href="{{ route('flow.find-store') }}" class="hover:text-white transition-colors">Store
                        Locations</a>
                    <a href="{{ route('flow.track.form') }}" class="hover:text-white transition-colors">Track
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

        function footerSubscribeComponent() {
            return {
                email: '',
                loading: false,
                subscribed: false,
                successMessage: '',
                errorMessage: '',
                validateEmail(email) {
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return re.test(String(email).toLowerCase());
                },
                async submitSubscribe() {
                    this.errorMessage = '';
                    const cleanEmail = (this.email || '').trim();
                    if (!cleanEmail) {
                        this.errorMessage = 'Please enter your email address.';
                        return;
                    }
                    if (!this.validateEmail(cleanEmail)) {
                        this.errorMessage = 'Please enter a valid email address.';
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch('{{ route('flow.subscribe') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                email: cleanEmail
                            })
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.subscribed = true;
                            this.successMessage = data.message || 'Thanks for subscribing!';
                            if (typeof lucide !== 'undefined') {
                                this.$nextTick(() => lucide.createIcons());
                            }
                        } else {
                            this.errorMessage = data.message || 'Validation failed. Please check your email.';
                        }
                    } catch (err) {
                        console.error(err);
                        this.errorMessage = 'An error occurred. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
    @stack('scripts')
    @stack('body_end')
</body>

</html>
