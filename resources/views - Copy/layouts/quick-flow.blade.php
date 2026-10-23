<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Create Your Custom Print')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="alternate icon" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/svg-logo/Q-only.svg') }}">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Qrinto">
    <link rel="apple-touch-icon" href="/logo/Qrinto-logo-med.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Onboarding Tour CSS (loaded only when tour is active) -->
    <script>
        (function() {
            var d = 'qrinto_tour_completed_v1';
            try {
                if (localStorage.getItem(d) === '1' && localStorage.getItem('qrinto_tour_active_v1') !== '1') return
            } catch (e) {}
            var h = document.head;
            var a = document.createElement('link');
            a.rel = 'stylesheet';
            a.href = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.css';
            h.appendChild(a);
            var b = document.createElement('link');
            b.rel = 'stylesheet';
            b.href = '{{ asset('css/qrinto-tour.css') }}';
            h.appendChild(b);
            window.__qrintoTourCSS = true
        })();
    </script>

    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-tap-highlight-color: transparent;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
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
            width: 0;
            height: 0;
        }

        /* ═══ Store Context Topbar ═══ */
        .topbar-store {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1.5px solid rgba(226, 232, 240, 0.6);
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.3s ease, background 0.3s ease;
        }

        .topbar-store-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            gap: 12px;
            min-height: 46px;
        }

        .topbar-store-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .topbar-store-indicator {
            width: 8px;
            height: 8px;
            background: #0ea5e9;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 0 3px #7dd3fc;
            animation: topbar-pulse 2.5s ease-in-out infinite;
        }

        @keyframes topbar-pulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px #7dd3fc;
            }

            50% {
                box-shadow: 0 0 0 6px #7dd3fc;
            }
        }

        .topbar-store-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #e0f2fe, #e0f2fe);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            border: 1.5px solid #7dd3fc;
        }

        .topbar-store-icon-warn {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-color: #7dd3fc;
        }

        .topbar-store-logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .topbar-pin-icon {
            width: 16px;
            height: 16px;
            color: #0ea5e9;
        }

        .topbar-store-icon-warn .topbar-pin-icon {
            color: #d97706;
        }

        .topbar-store-details {
            display: flex;
            flex-direction: column;
            min-width: 0;
            gap: 1px;
        }

        .topbar-store-label {
            font-size: 9px;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            line-height: 1;
            display: none;
        }

        .topbar-store-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.01em;
        }

        .topbar-store-name-muted {
            color: #94a3b8;
            font-weight: 700;
        }

        .topbar-store-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #0ea5e9;
            background: linear-gradient(135deg, #e0f2fe, #e0f2fe);
            border: 1.5px solid #7dd3fc;
            border-radius: 20px;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px #7dd3fc;
        }

        .topbar-store-btn:hover {
            background: linear-gradient(135deg, #e0f2fe, #e0f2fe);
            border-color: #7dd3fc;
            box-shadow: 0 3px 12px #7dd3fc;
            transform: translateY(-1px);
        }

        .topbar-store-btn:active {
            transform: scale(0.97) translateY(0);
        }

        .topbar-store-btn-action {
            background: linear-gradient(135deg, #0ea5e9, #0369a1);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 2px 8px #7dd3fc;
        }

        .topbar-store-btn-action:hover {
            background: linear-gradient(135deg, #0369a1, #0369a1);
            box-shadow: 0 4px 16px #7dd3fc;
        }

        .topbar-btn-icon {
            width: 13px;
            height: 13px;
        }

        .topbar-store-empty {
            background: linear-gradient(135deg, rgba(254, 243, 199, 0.3), rgba(255, 255, 255, 0));
        }

        /* Topbar responsive fine-tuning */
        @media (max-width: 380px) {
            .topbar-store-inner {
                padding: 8px 12px;
                gap: 8px;
            }

            .topbar-store-name {
                font-size: 12px;
            }

            .topbar-store-btn {
                padding: 6px 10px;
                font-size: 9px;
            }

            .topbar-btn-icon {
                width: 11px;
                height: 11px;
            }
        }
    </style>
    @stack('styles')
    <script>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    </script>
</head>

<body class="antialiased select-none ">
    <div x-data="{ mobileMenu: false }" class="max-w-md mx-auto min-h-screen flex flex-col bg-slate-100 shadow-2xl relative">

        <!-- Full-Screen Modal Menu -->
        <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4" class="absolute inset-0 z-[100] bg-white flex flex-col"
            style="display: none;" x-cloak>

            @php
                $activeStore = session()->has('active_store_id')
                    ? \App\Models\Store::find(session('active_store_id'))
                    : null;
            @endphp

            <div class="flex flex-col h-full ">
                <!-- Modal Header (Blue Theme) -->
                <div class="bg-mobile-600 px-6 py-8 rounded-b-[40px] shadow-lg shadow-mobile-900/10 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('/images/svg-logo/Qrinto-logo-one-color-white-only.svg') }}" />
                        </div>
                        <button @click="mobileMenu = false"
                            class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 text-white hover:bg-white/20 transition-all active:scale-90 border border-white/10">
                            <i data-lucide="x" class="w-7 h-7"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Scrollable Content -->
                <div class="flex-1 px-6 pb-8 space-y-6 overflow-y-auto">
                    <!-- Main Call to Action -->
                    <a href="{{ route('flow.find-store') }}" @click="mobileMenu = false"
                        class="block w-full group relative overflow-hidden bg-mobile-600 p-6 rounded-[32px] shadow-lg shadow-mobile-100 transition-all hover:bg-mobile-700 active:scale-[0.98]">
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex flex-col text-left">
                                <span
                                    class="text-[10px] font-black text-mobile-200 uppercase tracking-[0.2em] mb-1">Start
                                    New Flow</span>
                                <span class="text-xl font-black text-white leading-tight">Print at a store near
                                    you</span>
                            </div>
                            <div
                                class="w-12 h-12 flex-shrink-0 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                <i data-lucide="plus" class="w-7 h-7"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Direct Print CTA -->
                    <a href="{{ route('localprint.index') }}" @click="mobileMenu = false"
                        class="block w-full group relative overflow-hidden bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 transition-all hover:border-mobile-500 active:scale-[0.98]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-slate-50 flex items-center justify-center rounded-2xl text-slate-400 group-hover:text-mobile-600 group-hover:bg-mobile-50 transition-all">
                                    <i data-lucide="upload" class="w-6 h-6"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Already
                                        have a design?</span>
                                    <span class="text-lg font-black text-slate-900 leading-tight">Print on your own
                                        931BL</span>
                                </div>
                            </div>
                            <div
                                class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 group-hover:bg-mobile-600 group-hover:text-white group-hover:border-mobile-600 transition-all">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Store Context -->
                    <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-1.5 h-4 bg-mobile-500 rounded-full"></div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Active
                                Branch</span>
                        </div>

                        @if ($activeStore)
                            <div
                                class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 mb-4 group transition-all hover:bg-white hover:border-mobile-200">
                                <div
                                    class="w-14 h-14 bg-white rounded-xl shadow-sm flex items-center justify-center text-mobile-600 group-hover:bg-mobile-50 transition-colors">
                                    <i data-lucide="store" class="w-7 h-7"></i>
                                </div>
                                <div class="flex flex-col flex-1 overflow-hidden">
                                    <span
                                        class="text-lg font-black text-slate-900 truncate tracking-tight">{{ $activeStore->store_name }}</span>
                                    <div class="flex flex-col gap-0.5 mt-0.5">
                                        <span class="text-[9px] font-bold text-slate-400 flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-2.5 h-2.5"></i>
                                            {{ $activeStore->city }}, {{ $activeStore->state }}
                                        </span>
                                        @if ($activeStore->phone)
                                            <span class="text-[9px] font-bold text-slate-400 flex items-center gap-1">
                                                <i data-lucide="phone" class="w-2.5 h-2.5"></i>
                                                {{ $activeStore->phone }}
                                            </span>
                                        @endif
                                    </div>
                                    <span
                                        class="text-[9px] font-black text-green-500 flex items-center gap-1.5 uppercase mt-2">
                                        <span
                                            class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse shadow-sm"></span>
                                        Connected
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('flow.find-store') }}"
                                class="flex items-center justify-center gap-2 w-full py-4 rounded-2xl bg-mobile-600 text-white font-black text-sm uppercase tracking-widest hover:bg-mobile-700 transition-all shadow-lg shadow-mobile-100 active:scale-[0.98]">
                                Change Location
                                <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                            </a>
                        @else
                            <a href="{{ route('flow.find-store') }}"
                                class="flex items-center gap-4 p-5 rounded-2xl bg-amber-50 border border-amber-100 text-amber-700 hover:bg-amber-100 transition-colors">
                                <i data-lucide="alert-octagon" class="w-7 h-7"></i>
                                <div class="flex flex-col">
                                    <span class="font-black text-sm uppercase tracking-tight">No Store Selected</span>
                                    <span class="text-[10px] font-bold opacity-80">Link to a branch to begin the
                                        flow</span>
                                </div>
                            </a>
                        @endif
                    </div>

                    <!-- Tracking Quick Link -->
                    <a href="{{ route('flow.track.form') }}"
                        class="block bg-white rounded-[28px] shadow-sm border border-slate-100 transition-all hover:border-mobile-200 group overflow-hidden">
                        <div class="flex items-center justify-between p-5">
                            <div class="flex items-center gap-5">
                                <div
                                    class="w-12 h-12 bg-slate-50 flex items-center justify-center rounded-2xl text-slate-400 group-hover:text-mobile-600 group-hover:bg-mobile-50 transition-all">
                                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-black text-slate-900 uppercase text-xs tracking-widest">Track My
                                        Order</span>
                                    <span class="text-[10px] font-bold text-slate-400">Real-time status check</span>
                                </div>
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center group-hover:bg-mobile-600 group-hover:text-white group-hover:border-mobile-600 transition-all">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Replay Onboarding Tour -->
                    <button type="button" data-qt-restart="{{ route('flow.find-store') }}"
                        class="w-full text-left bg-white rounded-[28px] shadow-sm border border-slate-100 transition-all hover:border-mobile-200 group overflow-hidden">
                        <div class="flex items-center justify-between p-5">
                            <div class="flex items-center gap-5">
                                <div
                                    class="w-12 h-12 bg-slate-50 flex items-center justify-center rounded-2xl text-slate-400 group-hover:text-mobile-600 group-hover:bg-mobile-50 transition-all">
                                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-black text-slate-900 uppercase text-xs tracking-widest">Replay
                                        Tutorial</span>
                                    <span class="text-[10px] font-bold text-slate-400">See how QRinto works</span>
                                </div>
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center group-hover:bg-mobile-600 group-hover:text-white group-hover:border-mobile-600 transition-all">
                                <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </button>

                    <!-- Auth-aware quiet link: login when signed out; store panel /
                         admin dashboard when signed in. -->
                    @auth
                        @php
                            $u = auth()->user();
                            $isAdmin = method_exists($u, 'isAdmin') ? $u->isAdmin() : ($u->role ?? '') === 'admin';
                            $isStoreStaff = in_array($u->role ?? '', ['store_admin', 'storeadmin', 'staff']);
                            $storeLabel = $u->store->store_name ?? null;
                        @endphp
                        @if ($isAdmin)
                            <a href="{{ url('/admin/dashboard') }}" @click="mobileMenu = false"
                                class="flex items-center justify-center gap-2 text-[11px] font-bold text-slate-400 hover:text-mobile-600 uppercase tracking-widest py-2 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                                Admin dashboard
                            </a>
                        @elseif ($isStoreStaff)
                            <a href="{{ route('storepanel.orders') }}" @click="mobileMenu = false"
                                class="flex items-center justify-center gap-2 text-[11px] font-bold text-slate-400 hover:text-mobile-600 uppercase tracking-widest py-2 transition-colors">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                <span class="truncate max-w-[180px]" title="{{ $storeLabel ?? 'Store panel' }}">
                                    {{ $storeLabel ?? 'Store panel' }}
                                </span>
                            </a>
                        @else
                            <a href="{{ url('/store') }}" @click="mobileMenu = false"
                                class="flex items-center justify-center gap-2 text-[11px] font-bold text-slate-400 hover:text-mobile-600 uppercase tracking-widest py-2 transition-colors">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                For stores
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" @click="mobileMenu = false"
                            class="flex items-center justify-center gap-2 text-[11px] font-bold text-slate-400 hover:text-mobile-600 uppercase tracking-widest py-2 transition-colors">
                            <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                            For stores · Log in
                        </a>
                    @endauth
                </div>

                <!-- Modal Footer -->
                <div class="mt-auto pt-8 border-t border-slate-100 flex flex-col items-center gap-5">
                    <div class="flex gap-8">
                        <a href="#" class="text-slate-300 hover:text-mobile-600 transition-colors"><i
                                data-lucide="instagram" class="w-6 h-6"></i></a>
                        <a href="#" class="text-slate-300 hover:text-mobile-600 transition-colors"><i
                                data-lucide="mail" class="w-6 h-6"></i></a>
                    </div>
                    <div class="flex flex-col items-center">
                        <p class="text-[10px] font-black text-slate-200 uppercase tracking-[0.4em] mb-1">Qrinto Custom
                            Studio</p>
                        <div class="w-10 h-1 bg-slate-50 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simplified Modern Header (Blue Theme) -->
        <header
            class="sticky top-0 z-50 bg-mobile-600 border-b border-mobile-700/50 px-5   shadow-lg shadow-mobile-900/10">
            <div class="flex items-center justify-between">
                <!-- Branding (returns to store search from every screen) -->
                <a href="{{ route('flow.find-store') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('/images/svg-logo/Qrinto-logo-one-color-white-only.svg') }}" class="w-32" />
                </a>

                <!-- Simplified Actions -->
                <div class="flex items-center gap-3">
                    @php
                        $currentRoute = Route::currentRouteName();
                        $pcRoute = $currentRoute ? str_replace('flow.', 'flow.', $currentRoute) : null;
                        $params = Route::current() ? Route::current()->parameters() : [];
                        $cartCount = 0;
                        try {
                            $cartCount = app(\App\Services\CartService::class)->getCart()->item_count;
                        } catch (\Exception $e) {
                        }
                    @endphp
                    @if ($pcRoute && Route::has($pcRoute))
                        <!-- <a href="{{ route($pcRoute, $params) }}" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 text-mobile-100 hover:text-white hover:bg-white/20 transition-all border border-white/20 active:scale-95" title="Switch to PC View">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </a> -->
                    @endif

                    <button @click="mobileMenu = true; $nextTick(() => lucide.createIcons())"
                        class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 text-white hover:bg-white/20 transition-all border border-white/20 active:scale-95">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Store Context Topbar -->
        @php
            $topbarStore = session()->has('active_store_id')
                ? \App\Models\Store::find(session('active_store_id'))
                : null;
        @endphp
        @if ($topbarStore)
            <div class="topbar-store sticky top-[72px] z-40">
                <div class="topbar-store-inner">
                    <div class="topbar-store-info">
                        <div class="topbar-store-indicator"></div>
                        <div class="topbar-store-icon">
                            @if ($topbarStore->logo)
                                <img src="{{ asset('/storage/') . '/' . $topbarStore->logo }}"
                                    alt="{{ $topbarStore->store_name }}" class="topbar-store-logo-img">
                            @else
                                <i data-lucide="map-pin" class="topbar-pin-icon"></i>
                            @endif
                        </div>
                        <div class="topbar-store-details">
                            <span
                                class="topbar-store-name">{{ $topbarStore->store_name }}{{ $topbarStore->city ? ' · ' . $topbarStore->city : '' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('flow.find-store') }}" class="topbar-store-btn">
                        <i data-lucide="store" class="topbar-btn-icon"></i>
                        YOUR STORE
                    </a>
                </div>
            </div>
        @else
            <div class="topbar-store sticky top-[72px] z-40">
                <div class="topbar-store-inner topbar-store-empty">
                    <div class="topbar-store-info">
                        <div class="topbar-store-icon topbar-store-icon-warn">
                            <i data-lucide="map-pin-off" class="topbar-pin-icon"></i>
                        </div>
                        <div class="topbar-store-details">
                            <span class="topbar-store-name topbar-store-name-muted">No store selected</span>
                        </div>
                    </div>
                    <a href="{{ route('flow.find-store') }}" class="topbar-store-btn topbar-store-btn-action">
                        <i data-lucide="search" class="topbar-btn-icon"></i>
                        FIND STORE
                    </a>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto   py-4">
            @yield('content')
        </main>

        @include('helper.footer')
    </div>

    <script>
        window.__currency = @json(\App\Services\CurrencyService::toArray());
        window.__price = function(amount, decimals) {
            decimals = decimals !== undefined ? decimals : 2;
            var converted = parseFloat(amount) * (window.__currency.rate || 1);
            return window.__currency.symbol + converted.toFixed(decimals);
        };
    </script>
    @stack('scripts')

    <!-- Onboarding Tour JS (loaded only when tour is active) -->
    <script>
        (function() {
            try {
                if (localStorage.getItem('qrinto_tour_completed_v1') === '1' && localStorage.getItem(
                        'qrinto_tour_active_v1') !== '1') return
            } catch (e) {}
            var a = document.createElement('script');
            a.src = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.js.iife.js';
            a.onload = function() {
                var b = document.createElement('script');
                b.src = '{{ asset('js/qrinto-tour.js') }}';
                document.body.appendChild(b)
            };
            document.body.appendChild(a)
        })();
    </script>

    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>

</html>
