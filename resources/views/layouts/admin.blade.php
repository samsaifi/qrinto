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
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
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
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0"></i>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
                @endforeach

                @if(count($catalogItems))
                <div x-data="{ open: {{ $catalogOpen ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $catalogOpen ? 'text-white' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                        <i data-lucide="shopping-bag" class="w-5 h-5 flex-shrink-0"></i>
                        <span x-show="sidebarOpen" x-transition class="whitespace-nowrap flex-1 text-left">Catalog</span>
                        <i x-show="sidebarOpen" data-lucide="chevron-down" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="ml-3 pl-3 border-l border-surface-700 space-y-0.5 mt-0.5">
                        @foreach($catalogItems as $item)
                        <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['match']) ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                            <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0"></i>
                            <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @foreach($bottomItems as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs($item['match']) ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/20' : 'text-surface-400 hover:bg-surface-800 hover:text-white' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0"></i>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
                @endforeach
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