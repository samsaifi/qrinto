<!-- Customer Sidebar -->
<aside class="lg:w-64 flex-shrink-0">
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
        <!-- Profile -->
        <div class="flex items-center gap-3 pb-5 border-b border-surface-100 mb-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                <span class="text-white font-bold text-lg">{{ substr(auth()->user()->name, 0, 1) }}</span>
            </div>
            <div>
                <p class="font-semibold text-surface-800 text-sm">{{ auth()->user()->name }}</p>
                <p class="text-xs text-surface-400">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <nav class="space-y-1">
            <a href="{{ route('customer.dashboard') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('customer.dashboard') ? 'bg-brand-50 text-brand-600' : 'text-surface-600 hover:bg-surface-50' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>
            <a href="{{ route('customer.orders') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('customer.orders*') ? 'bg-brand-50 text-brand-600' : 'text-surface-600 hover:bg-surface-50' }}">
                <i data-lucide="package" class="w-4 h-4"></i> My Orders
            </a>
            <a href="{{ route('customer.profile') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('customer.profile*') ? 'bg-brand-50 text-brand-600' : 'text-surface-600 hover:bg-surface-50' }}">
                <i data-lucide="user" class="w-4 h-4"></i> Profile & Addresses
            </a>
            <div class="border-t border-surface-100 my-2"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 w-full transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                </button>
            </form>
        </nav>
    </div>
</aside>
