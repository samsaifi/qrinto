<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased bg-surface-50" x-data="{ sidebarOpen: true }">
    @php
        $isStoreAdmin = auth()->user()->isStoreAdmin();
        $userStore = $isStoreAdmin && auth()->user()->store_id ? auth()->user()->store : null;
    @endphp
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="fixed inset-y-0 left-0 {{ $isStoreAdmin ? 'bg-gradient-to-b from-surface-900 to-surface-950' : 'bg-surface-900' }} transition-all duration-300 z-50 flex flex-col">
            <!-- Logo -->
            @if($isStoreAdmin && $userStore)
            <div class="flex items-center gap-3 px-5 py-3 border-b border-surface-800">
                @if($userStore->logo)
                <div class="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0 ring-2 ring-emerald-500/30">
                    <img src="{{ asset('storage/' . $userStore->logo) }}" alt="{{ $userStore->store_name }}" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="store" class="w-4 h-4 text-white"></i>
                </div>
                @endif
                <div x-show="sidebarOpen" x-transition class="min-w-0">
                    <span class="font-display font-bold text-white text-sm whitespace-nowrap block">Store Panel</span>
                    <span class="text-xs text-emerald-400 truncate block max-w-[160px]" title="{{ $userStore->store_name }}">{{ $userStore->store_name }}</span>
                </div>
            </div>
            @else
            <div class="flex items-center gap-3 px-5 h-16 border-b border-surface-800">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-white"></i>
                </div>
                <span x-show="sidebarOpen" x-transition class="font-display font-bold text-white text-lg whitespace-nowrap">Admin Panel</span>
            </div>
            @endif

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto no-scrollbar">
                @php
                $topItems = [
                    ['route' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard', 'match' => 'admin.dashboard'],
                    ['route' => 'admin.orders.index', 'icon' => 'package', 'label' => 'Orders', 'match' => 'admin.orders*'],
                ];

                $catalogItems = [];
                $bottomItems = [];

                if (auth()->user()->isAdmin()) {
                    $catalogItems = [
                        ['route' => 'admin.products.index', 'icon' => 'box', 'label' => 'Products', 'match' => 'admin.products*'],
                        ['route' => 'admin.categories.index', 'icon' => 'grid-2x2', 'label' => 'Categories', 'match' => 'admin.categories*'],
                        ['route' => 'admin.product-types.index', 'icon' => 'layers', 'label' => 'Card Types/Sizes', 'match' => 'admin.product-types*'],
                        ['route' => 'admin.templates.index', 'icon' => 'layout-template', 'label' => 'Templates', 'match' => 'admin.templates*'],
                        ['route' => 'admin.coupons.index', 'icon' => 'tag', 'label' => 'Coupons', 'match' => 'admin.coupons*'],
                        ['route' => 'admin.events.index', 'icon' => 'calendar', 'label' => 'Events', 'match' => 'admin.events*'],
                        ['route' => 'admin.paper-types.index', 'icon' => 'scroll-text', 'label' => 'Paper Types', 'match' => 'admin.paper-types*'],
                        ];
                        $bottomItems = [
                        ['route' => 'admin.stores.index', 'icon' => 'store', 'label' => 'Stores', 'match' => 'admin.stores*'],
                        ['route' => 'admin.users.index', 'icon' => 'users', 'label' => 'Users', 'match' => 'admin.users*'],
                        ];
                    } elseif (auth()->user()->isStoreAdmin() && auth()->user()->store_id) {
                    $catalogItems = [
                        ['route' => 'admin.products.index', 'icon' => 'box', 'label' => 'Products', 'match' => 'admin.products*'],
                        ['route' => 'admin.categories.index', 'icon' => 'grid-2x2', 'label' => 'Categories', 'match' => 'admin.categories*'],
                        ['route' => 'admin.coupons.index', 'icon' => 'tag', 'label' => 'Coupons', 'match' => 'admin.coupons*'],
                        ['route' => 'admin.events.index', 'icon' => 'calendar', 'label' => 'Events', 'match' => 'admin.events*'],
                        ['route' => 'admin.paper-types.index', 'icon' => 'scroll-text', 'label' => 'Paper Types', 'match' => 'admin.paper-types*'],
                    ];
                    $bottomItems = [
                        ['route' => 'admin.stores.edit', 'params' => [auth()->user()->store_id], 'icon' => 'store', 'label' => 'Store Details', 'match' => 'admin.stores.edit'],
                    ];
                }

                $catalogOpen = collect($catalogItems)->contains(fn($item) => request()->routeIs($item['match']));
                @endphp

                @foreach($topItems as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['match']) ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0 {{ request()->routeIs($item['match']) ? 'text-white' : 'text-brand-500' }}"></i>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
                @endforeach

                @if(count($catalogItems))
                <div x-data="{ open: {{ $catalogOpen ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $catalogOpen ? 'text-white' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                        <i data-lucide="shopping-bag" class="w-5 h-5 flex-shrink-0 {{ $catalogOpen ? 'text-white' : 'text-brand-500' }}"></i>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-left">Catalog</span>
                        <i x-show="sidebarOpen" data-lucide="chevron-down" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="ml-3 pl-3 border-l border-surface-700 space-y-0.5 mt-0.5">
                        @foreach($catalogItems as $item)
                        <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['match']) ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs($item['match']) ? 'text-white' : 'text-brand-500' }}"></i>
                            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @foreach($bottomItems as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['match']) ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0 {{ request()->routeIs($item['match']) ? 'text-white' : 'text-brand-500' }}"></i>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
                @endforeach

                {{-- Documentations Accordion --}}
                @php
                $docOpen = request()->routeIs('admin.docs*');
                @endphp

                <div x-data="{ docOpen: {{ $docOpen ? 'true' : 'false' }} }">
                    <button @click="docOpen = !docOpen"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $docOpen ? 'text-white' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                        <i data-lucide="book-open" class="w-5 h-5 flex-shrink-0 {{ $docOpen ? 'text-white' : 'text-brand-500' }}"></i>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-left">Documentations</span>
                        <i x-show="sidebarOpen" data-lucide="chevron-down" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="docOpen ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <div x-show="docOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="ml-3 pl-3 border-l border-surface-700 space-y-0.5 mt-0.5">
                        <a href="{{ route('admin.docs.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.docs.index') ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                            <i data-lucide="help-circle" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('admin.docs.index') ? 'text-white' : 'text-brand-500' }}"></i>
                            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Help Center Home</span>
                        </a>

                        <a href="{{ route('admin.docs.show', 'getting-started') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/docs/getting-started*') || request()->is('admin/docs/understanding*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                            <i data-lucide="compass" class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/docs/getting-started*') || request()->is('admin/docs/understanding*') ? 'text-white' : 'text-brand-500' }}"></i>
                            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Getting Started</span>
                        </a>

                        <a href="{{ route('admin.docs.show', 'orders') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/docs/orders*') || request()->is('admin/docs/how-to-process-an-order*') || request()->is('admin/docs/order*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                            <i data-lucide="package" class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/docs/orders*') || request()->is('admin/docs/how-to-process-an-order*') || request()->is('admin/docs/order*') ? 'text-white' : 'text-brand-500' }}"></i>
                            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Orders</span>
                        </a>

                        {{-- Customer Experience Sub-menu --}}
                        <div x-data="{ custOpen: {{ request()->is('admin/docs/mobile-custom-editing-ordering-guide*') || request()->is('admin/docs/desktop-custom-editing-ordering-guide*') ? 'true' : 'false' }} }">
                            <button @click="custOpen = !custOpen"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all text-surface-400 hover:bg-surface-800 hover:text-white">
                                <i data-lucide="smartphone" class="w-4 h-4 flex-shrink-0 text-pink-400"></i>
                                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-left">Customer Flow</span>
                                <i x-show="sidebarOpen" data-lucide="chevron-down" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="custOpen ? 'rotate-180' : ''"></i>
                            </button>
                            
                            <div x-show="custOpen" class="ml-3 pl-2 border-l border-surface-700/60 space-y-0.5 mt-0.5">
                                <a href="{{ route('admin.docs.show', 'mobile-custom-editing-ordering-guide') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/mobile-custom-editing-ordering-guide*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="smartphone" class="w-3.5 h-3.5 {{ request()->is('admin/docs/mobile-custom-editing-ordering-guide*') ? 'text-white' : 'text-pink-400' }}"></i> Mobile Ordering
                                </a>
                                <a href="{{ route('admin.docs.show', 'desktop-custom-editing-ordering-guide') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/desktop-custom-editing-ordering-guide*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="monitor" class="w-3.5 h-3.5 {{ request()->is('admin/docs/desktop-custom-editing-ordering-guide*') ? 'text-white' : 'text-indigo-400' }}"></i> Desktop PC Studio
                                </a>
                            </div>
                        </div>

                        {{-- Catalog Sub-menu --}}
                        <div x-data="{ catOpen: {{ request()->is('admin/docs/products*') || request()->is('admin/docs/categories*') || request()->is('admin/docs/card-types*') || request()->is('admin/docs/templates*') || request()->is('admin/docs/coupons*') || request()->is('admin/docs/events*') || request()->is('admin/docs/paper-types*') || request()->is('admin/docs/catalog*') ? 'true' : 'false' }} }">
                            <button @click="catOpen = !catOpen"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all text-surface-400 hover:bg-surface-800 hover:text-white">
                                <i data-lucide="shopping-bag" class="w-4 h-4 flex-shrink-0 text-brand-500"></i>
                                <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-left">Catalog</span>
                                <i x-show="sidebarOpen" data-lucide="chevron-down" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="catOpen ? 'rotate-180' : ''"></i>
                            </button>
                            
                            <div x-show="catOpen" class="ml-3 pl-2 border-l border-surface-700/60 space-y-0.5 mt-0.5">
                                <a href="{{ route('admin.docs.show', 'products') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/products*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="box" class="w-3.5 h-3.5 {{ request()->is('admin/docs/products*') ? 'text-white' : 'text-brand-500' }}"></i> Products
                                </a>
                                <a href="{{ route('admin.docs.show', 'categories') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/categories*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="grid-2x2" class="w-3.5 h-3.5 {{ request()->is('admin/docs/categories*') ? 'text-white' : 'text-brand-500' }}"></i> Categories
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.docs.show', 'card-types-sizes') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/card-types*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="layers" class="w-3.5 h-3.5 {{ request()->is('admin/docs/card-types*') ? 'text-white' : 'text-brand-500' }}"></i> Card Types/Sizes
                                </a>
                                <a href="{{ route('admin.docs.show', 'templates') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/templates*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="layout-template" class="w-3.5 h-3.5 {{ request()->is('admin/docs/templates*') ? 'text-white' : 'text-brand-500' }}"></i> Templates
                                </a>
                                @endif
                                <a href="{{ route('admin.docs.show', 'coupons') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/coupons*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 {{ request()->is('admin/docs/coupons*') ? 'text-white' : 'text-brand-500' }}"></i> Coupons
                                </a>
                                <a href="{{ route('admin.docs.show', 'events') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/events*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 {{ request()->is('admin/docs/events*') ? 'text-white' : 'text-brand-500' }}"></i> Events
                                </a>
                                <a href="{{ route('admin.docs.show', 'paper-types') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-surface-400 hover:bg-surface-800 hover:text-white {{ request()->is('admin/docs/paper-types*') ? 'text-white font-bold bg-surface-800' : '' }}">
                                    <i data-lucide="scroll-text" class="w-3.5 h-3.5 {{ request()->is('admin/docs/paper-types*') ? 'text-white' : 'text-brand-500' }}"></i> Paper Types
                                </a>
                            </div>
                        </div>

                        @if(auth()->user()->isAdmin() || auth()->user()->isStoreAdmin())
                        <a href="{{ route('admin.docs.show', 'stores') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/docs/stores*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                            <i data-lucide="store" class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/docs/stores*') ? 'text-white' : 'text-brand-500' }}"></i>
                            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Stores</span>
                        </a>
                        @endif

                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.docs.show', 'users') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/docs/users*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                            <i data-lucide="users" class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/docs/users*') ? 'text-white' : 'text-brand-500' }}"></i>
                            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Users</span>
                        </a>
                        @endif
                    </div>
                </div>
            </nav>

            <!-- Footer -->
            <div class="px-3 py-4 border-t border-surface-800 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-surface-400 hover:bg-surface-800 hover:text-white transition">
                    <i data-lucide="external-link" class="w-5 h-5 flex-shrink-0"></i>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">View Site</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-400 hover:bg-red-500/10 hover:text-red-400 transition"
                        @click.prevent="$root.submit();">
                        <i data-lucide="log-out" class="w-5 h-5 flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap text-left">Log Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <div :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 transition-all duration-300">
            <!-- Top Bar -->
            <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-surface-100 shadow-glass">
                <div class="flex items-center justify-between h-16 px-6">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl hover:bg-surface-100 text-surface-500 transition">
                        <i data-lucide="panel-left" class="w-5 h-5"></i>
                    </button>
                    <div class="flex items-center gap-4">
                        {{-- Documentation Dropdown Menu --}}
                        <div class="relative" x-data="{ docMenuOpen: false }">
                            <button @click="docMenuOpen = !docMenuOpen" @click.away="docMenuOpen = false"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-surface-100 hover:bg-surface-200 text-surface-700 text-xs font-semibold transition border border-surface-200/80 shadow-2xs cursor-pointer">
                                <i data-lucide="book-open" class="w-4 h-4 text-brand-600"></i>
                                <span class="hidden sm:inline">Documentations</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-surface-400 transition-transform duration-200" :class="docMenuOpen ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="docMenuOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                                class="absolute right-0 mt-2 w-72 bg-white rounded-2xl border border-surface-200 shadow-xl py-2 z-50 divide-y divide-surface-100">
                                
                                <div class="px-4 py-2 bg-surface-50/70">
                                    <p class="text-[11px] font-bold text-surface-400 uppercase tracking-wider">Documentation Center</p>
                                    <a href="{{ route('admin.docs.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 block mt-0.5">
                                        View Help Center Home →
                                    </a>
                                </div>

                                <div class="py-1">
                                    <p class="px-4 py-1 text-[10px] font-bold text-surface-400 uppercase tracking-wider">Customer Flow</p>
                                    <a href="{{ route('admin.docs.show', 'mobile-custom-editing-ordering-guide') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                        <i data-lucide="smartphone" class="w-4 h-4 text-pink-500"></i>
                                        <span>Mobile Ordering Guide</span>
                                    </a>
                                    <a href="{{ route('admin.docs.show', 'desktop-custom-editing-ordering-guide') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                        <i data-lucide="monitor" class="w-4 h-4 text-indigo-500"></i>
                                        <span>Desktop PC Studio Guide</span>
                                    </a>
                                </div>

                                <div class="py-1">
                                    <p class="px-4 py-1 text-[10px] font-bold text-surface-400 uppercase tracking-wider">Admin Operations</p>
                                    <a href="{{ route('admin.docs.show', 'getting-started') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                        <i data-lucide="compass" class="w-4 h-4 text-brand-500"></i>
                                        <span>Getting Started</span>
                                    </a>
                                    <a href="{{ route('admin.docs.show', 'orders') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                        <i data-lucide="package" class="w-4 h-4 text-emerald-500"></i>
                                        <span>Orders & Workflow</span>
                                    </a>
                                    <a href="{{ route('admin.docs.show', 'products') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                        <i data-lucide="box" class="w-4 h-4 text-amber-500"></i>
                                        <span>Products & Masking</span>
                                    </a>
                                    <a href="{{ route('admin.docs.show', 'stores') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-surface-700 hover:bg-brand-50 hover:text-brand-700 transition">
                                        <i data-lucide="store" class="w-4 h-4 text-purple-500"></i>
                                        <span>Stores & Printers</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @if($isStoreAdmin && $userStore)
                        {{-- Store admin: show store logo + owner name --}}
                        <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100">
                            @if($userStore->logo)
                            <div class="w-8 h-8 rounded-lg overflow-hidden ring-2 ring-emerald-200 flex-shrink-0">
                                <img src="{{ asset('storage/' . $userStore->logo) }}" alt="{{ $userStore->store_name }}" class="w-full h-full object-cover">
                            </div>
                            @else
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="store" class="w-4 h-4 text-white"></i>
                            </div>
                            @endif
                            <div class="hidden sm:block">
                                <p class="text-sm font-semibold text-surface-800 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-emerald-600 leading-tight">{{ $userStore->store_name }}</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider">Store</span>
                        </div>
                        @else
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-surface-100">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                                <span class="text-white text-xs font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <span class="text-sm font-medium text-surface-700">{{ auth()->user()->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                @if(session('success'))
                <div class="mb-6 p-4 bg-accent-50 border border-accent-200 text-accent-800 rounded-xl flex items-center gap-3 text-sm">
                    <i data-lucide="check-circle" class="w-5 h-5 text-accent-600"></i> {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3 text-sm">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i> {{ session('error') }}
                </div>
                @endif
                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        window.__currency = @json(\App\Services\CurrencyService::toArray());
        window.__price = function(amount, decimals) {
            decimals = decimals !== undefined ? decimals : 2;
            return window.__currency.symbol + parseFloat(amount).toFixed(decimals);
        };
    </script>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>

</html>