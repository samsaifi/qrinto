<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'CustomPrint')) - Custom Photo Printing</title>
    <meta name="description" content="@yield('meta_description', 'Transform your photos into stunning wall art. Custom acrylic, canvas, and poster prints with personalized framing options.')">
    <meta name="keywords" content="@yield('meta_keywords', 'custom prints, acrylic photo, wall art, canvas prints, photo framing')">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="alternate icon" href="{{ asset('images/svg-logo/Q-only.svg') }}">
    <link rel="shortcut icon" href="{{ asset('images/svg-logo/Q-only.svg') }}">

    <!-- OG Tags -->
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', 'Custom photo printing and wall art')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <!-- Schema.org -->
    @hasSection('schema')
    @yield('schema')
    @endif
</head>
<body class="font-sans antialiased bg-surface-50 text-surface-800" x-data="{ mobileMenu: false, cartOpen: false }">
    <!-- Top Bar -->
    <div class="bg-surface-900 text-white text-xs py-2">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1">
                    <i data-lucide="truck" class="w-3 h-3"></i> Free Shipping on $999+
                </span>
                <span class="hidden sm:flex items-center gap-1">
                    <i data-lucide="shield-check" class="w-3 h-3"></i> Quality Guaranteed
                </span>
            </div>
            <div class="flex items-center gap-4">
                <a href="mailto:support@customprint.com" class="hover:text-brand-300 transition">support@customprint.com</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-surface-100 shadow-glass">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-lg group-hover:shadow-brand-200 transition-shadow">
                        <i data-lucide="image" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-display font-bold text-xl text-surface-900 tracking-tight">
                        Custom<span class="text-brand-600">Print</span>
                    </span>
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-surface-600 hover:text-brand-600 transition-colors {{ request()->routeIs('home') ? 'text-brand-600' : '' }}">Home</a>
                    <a href="{{ route('products.index') }}" class="text-sm font-medium text-surface-600 hover:text-brand-600 transition-colors {{ request()->routeIs('products.*') ? 'text-brand-600' : '' }}">Products</a>
                    @auth
                    <a href="{{ route('customer.dashboard') }}" class="text-sm font-medium text-surface-600 hover:text-brand-600 transition-colors">My Account</a>
                    @endauth
                </nav>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <!-- Cart Button -->
                    <a href="{{ route('flow.cart.index') }}" class="relative p-2.5 rounded-xl bg-surface-100 hover:bg-brand-50 text-surface-600 hover:text-brand-600 transition-all group" id="cart-btn">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        @php $cartCount = app(\App\Services\CartService::class)->getCart()->item_count; @endphp
                        @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-brand-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center animate-pulse-soft" id="cart-count">{{ $cartCount }}</span>
                        @endif
                    </a>

                    <!-- Auth -->
                    @guest
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-brand-600 to-brand-700 rounded-xl hover:from-brand-700 hover:to-brand-800 shadow-lg shadow-brand-200 hover:shadow-brand-300 transition-all transform hover:-translate-y-0.5">
                        <i data-lucide="log-in" class="w-4 h-4"></i> Login
                    </a>
                    @else
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-surface-100 hover:bg-surface-200 transition-all">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                                <span class="text-white text-xs font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <span class="hidden sm:block text-sm font-medium text-surface-700">{{ auth()->user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-surface-400"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-premium border border-surface-100 py-2 z-50">
                            @if(auth()->user()->canAccessAdmin())
                            @php
                                $panelUrl = match(auth()->user()->role ?? '') {
                                    'admin' => url('/admin/dashboard'),
                                    'store_admin', 'storeadmin' => url('/store-admin/dashboard'),
                                    'staff' => url('/staff/dashboard'),
                                    default => url('/admin/dashboard'),
                                };
                                $panelName = match(auth()->user()->role ?? '') {
                                    'admin' => 'Admin Panel',
                                    'store_admin', 'storeadmin' => 'Store Panel',
                                    'staff' => 'Staff Panel',
                                    default => 'Admin Panel',
                                };
                            @endphp
                            <a href="{{ $panelUrl }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-surface-600 hover:bg-brand-50 hover:text-brand-600">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> {{ $panelName }}
                            </a>
                            <div class="border-t border-surface-100 my-1"></div>
                            @endif
                            <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-surface-600 hover:bg-brand-50 hover:text-brand-600">
                                <i data-lucide="user" class="w-4 h-4"></i> My Dashboard
                            </a>
                            <a href="{{ route('customer.orders') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-surface-600 hover:bg-brand-50 hover:text-brand-600">
                                <i data-lucide="package" class="w-4 h-4"></i> My Orders
                            </a>
                            <div class="border-t border-surface-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 w-full text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    @endguest

                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-xl bg-surface-100 hover:bg-surface-200 transition">
                        <i data-lucide="menu" class="w-5 h-5 text-surface-600" x-show="!mobileMenu"></i>
                        <i data-lucide="x" class="w-5 h-5 text-surface-600" x-show="mobileMenu" x-cloak></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav -->
            <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="lg:hidden border-t border-surface-100 py-4" x-cloak>
                <nav class="flex flex-col gap-1">
                    <a href="{{ route('home') }}" class="px-4 py-3 rounded-xl text-sm font-medium text-surface-600 hover:bg-brand-50 hover:text-brand-600">Home</a>
                    <a href="{{ route('products.index') }}" class="px-4 py-3 rounded-xl text-sm font-medium text-surface-600 hover:bg-brand-50 hover:text-brand-600">Products</a>
                    <a href="{{ route('flow.cart.index') }}" class="px-4 py-3 rounded-xl text-sm font-medium text-surface-600 hover:bg-brand-50 hover:text-brand-600">Cart</a>
                    @guest
                    <a href="{{ route('login') }}" class="px-4 py-3 rounded-xl text-sm font-medium text-brand-600 bg-brand-50">Login / Register</a>
                    @endguest
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition class="fixed top-24 right-4 z-50 max-w-sm">
        <div class="bg-accent-50 border border-accent-200 text-accent-800 px-5 py-4 rounded-2xl shadow-premium flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-accent-600 flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
            <button @click="show = false" class="ml-auto text-accent-400 hover:text-accent-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition class="fixed top-24 right-4 z-50 max-w-sm">
        <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl shadow-premium flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('error') }}</span>
            <button @click="show = false" class="ml-auto text-red-400 hover:text-red-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-surface-900 text-surface-300 mt-20">
        <div class="max-w-7xl mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <!-- Brand -->
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                            <i data-lucide="image" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="font-display font-bold text-xl text-white">
                            Custom<span class="text-brand-400">Print</span>
                        </span>
                    </a>
                    <p class="text-sm leading-relaxed text-surface-400">
                        Transform your favorite photos into stunning wall art. Premium quality prints with personalized customization options.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-display font-semibold text-white mb-5">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('products.index') }}" class="text-sm hover:text-brand-400 transition">All Products</a></li>
                        <li><a href="{{ route('flow.cart.index') }}" class="text-sm hover:text-brand-400 transition">Cart</a></li>
                        @auth
                        <li><a href="{{ route('customer.orders') }}" class="text-sm hover:text-brand-400 transition">Track Order</a></li>
                        @endauth
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h4 class="font-display font-semibold text-white mb-5">Support</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-2 text-sm"><i data-lucide="mail" class="w-4 h-4 text-brand-400"></i> support@customprint.com</li>
                        <li class="flex items-center gap-2 text-sm"><i data-lucide="phone" class="w-4 h-4 text-brand-400"></i> (555) 123-4567</li>
                        <li class="flex items-center gap-2 text-sm"><i data-lucide="clock" class="w-4 h-4 text-brand-400"></i> Mon-Sat: 10AM - 7PM</li>
                    </ul>
                </div>

                <!-- Policies -->
                <div>
                    <h4 class="font-display font-semibold text-white mb-5">Policies</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-sm hover:text-brand-400 transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm hover:text-brand-400 transition">Terms & Conditions</a></li>
                        <li><a href="#" class="text-sm hover:text-brand-400 transition">Return Policy</a></li>
                        <li><a href="#" class="text-sm hover:text-brand-400 transition">Shipping Info</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-surface-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-surface-500">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <span class="text-xs text-surface-500">Secure Payments:</span>
                    <div class="flex gap-2">
                        <div class="px-3 py-1 bg-surface-800 rounded-lg text-xs font-medium text-surface-300">Razorpay</div>
                        <div class="px-3 py-1 bg-surface-800 rounded-lg text-xs font-medium text-surface-300">Stripe</div>
                        <div class="px-3 py-1 bg-surface-800 rounded-lg text-xs font-medium text-surface-300">UPI</div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        window.__currency = @json(\App\Services\CurrencyService::toArray());
        window.__price = function(amount, decimals) {
            decimals = decimals !== undefined ? decimals : 2;
            var converted = parseFloat(amount) * (window.__currency.rate || 1);
            return window.__currency.symbol + converted.toFixed(decimals);
        };
    </script>
    @stack('scripts')
    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
        // Re-init after Alpine updates
        document.addEventListener('alpine:initialized', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>
