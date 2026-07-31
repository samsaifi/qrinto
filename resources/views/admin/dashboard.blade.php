@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@if(auth()->user()->isStoreAdmin() && $store)
{{-- ═══════════════════════════════════════════════════════
     STORE ADMIN WELCOME SECTION
     ═══════════════════════════════════════════════════════ --}}

{{-- Welcome Row: 2/3 Welcome + 1/3 QR Code --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 animate-fade-in">

    {{-- ── Welcome Card (2/3) ────────────────────────────── --}}
    <div class="lg:col-span-2 relative rounded-2xl overflow-hidden shadow-premium">
        {{-- Background gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-600 to-brand-700"></div>
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.15&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

        <div class="relative px-6 py-8 sm:px-8 sm:py-10 flex flex-col justify-between h-full">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                {{-- Left: Greeting --}}
                <div class="flex items-center gap-5">
                    @if($store->logo)
                    <div class="w-16 h-16 rounded-2xl overflow-hidden ring-4 ring-white/20 shadow-lg flex-shrink-0">
                        <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->store_name }}" class="w-full h-full object-cover">
                    </div>
                    @else
                    <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center flex-shrink-0 ring-4 ring-white/10">
                        <i data-lucide="store" class="w-8 h-8 text-white"></i>
                    </div>
                    @endif
                    <div>
                        <p class="text-brand-100 text-sm font-medium">
                            @php
                                $hour = now()->format('H');
                                $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                            @endphp
                            {{ $greeting }} 👋
                        </p>
                        <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mt-1">{{ auth()->user()->name }}</h1>
                        <p class="text-brand-200 text-sm mt-1 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                            {{ $store->store_name }}
                            @if($store->city) &middot; {{ $store->city }} @endif
                        </p>
                    </div>
                </div>

                {{-- Right: Quick Actions --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 backdrop-blur-sm text-white text-sm font-medium hover:bg-white/25 transition-all border border-white/10">
                        <i data-lucide="package" class="w-4 h-4"></i>
                        View Orders
                    </a>
                    <a href="{{ route('admin.stores.edit', $store->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-brand-700 text-sm font-semibold hover:bg-brand-50 transition-all shadow-lg">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Store Settings
                    </a>
                </div>
            </div>

            {{-- Latest Order (single, with images) --}}
            @if($recentOrders->count() > 0)
            @php $latestOrder = $recentOrders->first(); @endphp
            <div class="mt-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-white/80 text-xs font-semibold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                        Latest Order
                    </p>
                    <a href="{{ route('admin.orders.index') }}" class="text-white/60 text-xs hover:text-white transition">View all →</a>
                </div>
                <a href="{{ route('admin.orders.show', $latestOrder) }}" class="block bg-white/10 backdrop-blur-sm rounded-xl border border-white/10 p-4 hover:bg-white/15 transition-all group">
                    <div class="flex items-start gap-4">
                        {{-- Order Images --}}
                        <div class="flex -space-x-2 flex-shrink-0">
                            @php
                                $allImages = collect();
                                foreach ($latestOrder->items as $item) {
                                    if (!empty($item->uploaded_images) && is_array($item->uploaded_images)) {
                                        foreach ($item->uploaded_images as $img) {
                                            $allImages->push(str_starts_with($img, 'http') ? $img : asset('storage/' . $img));
                                        }
                                    } elseif (!empty($item->customization_data['preview_url'])) {
                                        $allImages->push($item->customization_data['preview_url']);
                                    }
                                }
                                $displayImages = $allImages->take(3);
                                $extraCount = $allImages->count() - 3;
                            @endphp
                            @forelse($displayImages as $imgUrl)
                            <div class="w-14 h-14 rounded-xl overflow-hidden ring-2 ring-white/20 shadow-lg flex-shrink-0 bg-white/10">
                                <img src="{{ $imgUrl }}" alt="Order image" class="w-full h-full object-cover">
                            </div>
                            @empty
                            <div class="w-14 h-14 rounded-xl bg-white/10 ring-2 ring-white/10 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="image" class="w-6 h-6 text-white/30"></i>
                            </div>
                            @endforelse
                            @if($extraCount > 0)
                            <div class="w-14 h-14 rounded-xl bg-white/20 ring-2 ring-white/10 flex items-center justify-center flex-shrink-0 backdrop-blur-sm">
                                <span class="text-white text-xs font-bold">+{{ $extraCount }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Order Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-sm font-bold text-white group-hover:text-brand-200 transition">{{ $latestOrder->order_number }}</span>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-400/20 text-yellow-200',
                                        'processing' => 'bg-blue-400/20 text-blue-200',
                                        'completed' => 'bg-green-400/20 text-green-200',
                                        'delivered' => 'bg-emerald-400/20 text-emerald-200',
                                        'cancelled' => 'bg-red-400/20 text-red-200',
                                    ];
                                    $sClass = $statusColors[$latestOrder->status] ?? 'bg-white/10 text-white/70';
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sClass }}">
                                    {{ ucfirst($latestOrder->status) }}
                                </span>
                            </div>
                            <p class="text-white/60 text-xs mb-1">
                                {{ $latestOrder->shipping_address['name'] ?? ($latestOrder->user->name ?? 'Guest') }}
                                · {{ $latestOrder->items->count() }} {{ Str::plural('item', $latestOrder->items->count()) }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-white font-bold text-lg">{{ \App\Services\CurrencyService::formatWithCurrency($latestOrder->total, $latestOrder->currency) }}</span>
                                <span class="text-white/40 text-xs">{{ $latestOrder->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="flex-shrink-0 self-center">
                            <i data-lucide="chevron-right" class="w-5 h-5 text-white/30 group-hover:text-white/60 transition"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endif

            {{-- Today's Snapshot --}}
            <div class="grid grid-cols-3 gap-4 mt-8">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                    <p class="text-brand-200 text-xs font-medium uppercase tracking-wider">Today's Orders</p>
                    <p class="text-white text-xl font-bold mt-1">{{ $todayOrders }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                    <p class="text-brand-200 text-xs font-medium uppercase tracking-wider">Today's Revenue</p>
                    <p class="text-white text-xl font-bold mt-1">{{ \App\Services\CurrencyService::formatOnly($todayRevenue, 0) }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                    <p class="text-brand-200 text-xs font-medium uppercase tracking-wider">Completed</p>
                    <p class="text-white text-xl font-bold mt-1">{{ $completedOrders }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── QR Code Card (1/3) ────────────────────────────── --}}
    <div class="lg:col-span-1 bg-white rounded-2xl border border-surface-100 shadow-card flex flex-col items-center justify-center p-6 text-center">
        {{-- Badge --}}
        <div class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-gradient-to-r from-brand-600 to-brand-700 rounded-full text-white text-[11px] font-bold uppercase tracking-wider mb-4 shadow-lg shadow-brand-200">
            <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
            Store QR Code
        </div>

        {{-- Store Name & Code --}}
        <h3 class="font-display font-extrabold text-lg text-surface-900 mb-0.5">{{ $store->store_name }}</h3>
        <p class="font-mono text-xs text-surface-400 mb-4">Code: {{ $store->store_code }}</p>

        {{-- QR Code Container --}}
        <div id="dashboard-qr-container" class="bg-surface-50 rounded-xl p-3 border border-surface-100 inline-block mb-4">
            {{-- QR code will be generated here by JS --}}
        </div>

        {{-- Scan Label --}}
        <p class="text-sm font-semibold text-surface-700 mb-1">📱 Scan to Start Ordering</p>
        <p class="text-xs text-surface-400 mb-3">Customers scan this code in-store</p>

        {{-- URL Display --}}
        <div class="w-full font-mono text-[11px] text-surface-400 bg-surface-50 px-3 py-2 rounded-lg border border-surface-100 truncate mb-4" title="{{ url('store/' . $store->store_code) }}">
            {{ url('store/' . $store->store_code) }}
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 w-full">
            <a href="{{ route('store.qr', $store->store_code) }}" target="_blank"
               class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 transition shadow-md">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                Full QR Page
            </a>
            <button onclick="downloadDashboardQr()"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-surface-100 text-surface-700 text-xs font-semibold rounded-xl hover:bg-surface-200 transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Download
            </button>
        </div>
    </div>

</div>

{{-- Store Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    @php
    $stats = [
        ['label' => 'Total Revenue', 'value' => \App\Services\CurrencyService::formatOnly($totalRevenue, 0), 'icon' => 'dollar-sign', 'color' => 'emerald', 'sub' => 'All time earnings'],
        ['label' => 'Total Orders', 'value' => number_format($totalOrders), 'icon' => 'package', 'color' => 'blue', 'sub' => $pendingOrders . ' pending'],
        ['label' => 'Pending Orders', 'value' => number_format($pendingOrders), 'icon' => 'clock', 'color' => 'amber', 'sub' => 'Awaiting processing'],
        ['label' => 'Completion Rate', 'value' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100) . '%' : '0%', 'icon' => 'check-circle', 'color' => 'violet', 'sub' => $completedOrders . ' completed'],
    ];
    @endphp

    @foreach($stats as $stat)
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 hover:shadow-hover transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-{{ $stat['color'] }}-100 flex items-center justify-center">
                <i data-lucide="{{ $stat['icon'] }}" class="w-6 h-6 text-{{ $stat['color'] }}-600"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-surface-900">{{ $stat['value'] }}</p>
        <p class="text-sm text-surface-500 mt-1">{{ $stat['label'] }}</p>
        <p class="text-xs text-surface-400 mt-1">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>

@else
{{-- ═══════════════════════════════════════════════════════
     DEFAULT ADMIN DASHBOARD
     ═══════════════════════════════════════════════════════ --}}
<div class="mb-8">
    <h1 class="font-display font-bold text-2xl text-surface-900">Dashboard</h1>
    <p class="text-sm text-surface-500">Welcome back! Here's what's happening.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    @php
    $stats = [
    ['label' => 'Total Revenue', 'value' => \App\Services\CurrencyService::formatOnly($totalRevenue, 0), 'icon' => 'dollar-sign', 'color' => 'brand', 'sub' => 'All time'],
    ['label' => 'Total Orders', 'value' => number_format($totalOrders), 'icon' => 'package', 'color' => 'blue', 'sub' => $pendingOrders . ' pending'],
    ];

    if (!auth()->user()->store_id) {
    $stats[] = ['label' => 'Customers', 'value' => number_format($totalCustomers), 'icon' => 'users', 'color' => 'accent', 'sub' => 'Registered users'];
    $stats[] = ['label' => 'Products', 'value' => number_format($totalProducts), 'icon' => 'box', 'color' => 'purple', 'sub' => 'Active listings'];
    }
    @endphp

    @foreach($stats as $stat)
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-xl bg-{{ $stat['color'] }}-100 flex items-center justify-center">
                <i data-lucide="{{ $stat['icon'] }}" class="w-6 h-6 text-{{ $stat['color'] }}-600"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-surface-900">{{ $stat['value'] }}</p>
        <p class="text-sm text-surface-500 mt-1">{{ $stat['label'] }}</p>
        <p class="text-xs text-surface-400 mt-1">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
</div>
@endif

<!-- Stores Summary -->
<div class="bg-white rounded-2xl border border-surface-100 shadow-card">
    <div class="p-6 border-b border-surface-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-semibold text-lg text-surface-900">Stores Summary</h2>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" data-preset="all" class="store-preset-btn active px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-600 text-white transition">All Time</button>
                <button type="button" data-preset="today" class="store-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">Today</button>
                <button type="button" data-preset="week" class="store-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">This Week</button>
                <button type="button" data-preset="month" class="store-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">This Month</button>
                <button type="button" data-preset="year" class="store-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">This Year</button>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" id="store-date-start" class="px-3 py-1.5 text-xs border border-surface-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                <span class="text-xs text-surface-400">to</span>
                <input type="date" id="store-date-end" class="px-3 py-1.5 text-xs border border-surface-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                <button type="button" id="store-date-apply" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-100 text-brand-700 hover:bg-brand-200 transition">Apply</button>
            </div>

            <select id="store-filter-select" class="px-3 py-1.5 text-xs border border-surface-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none min-w-[160px]">
                <option value="">All Stores</option>
                @foreach($stores as $s)
                <option value="{{ $s->id }}">{{ $s->store_name }}</option>
                @endforeach
            </select>

            <button type="button" id="store-export-excel" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-accent-600 text-white hover:bg-accent-700 transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Download Excel
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-surface-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="store_name">Store <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="total_orders">Total Orders <span class="sort-arrow">&#9662;</span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="online_amount">Online Payment <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="cash_amount">Cash Payment <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="pending_orders">Pending Orders <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="pending_amount">Pending Amt <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="paid_orders">Paid Orders <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none summary-sort-col" data-col="paid_amount">Paid Amt <span class="sort-arrow"></span></th>
                </tr>
            </thead>
            <tbody id="stores-summary-body" class="divide-y divide-surface-100">
                <tr><td colspan="9" class="px-6 py-12 text-center text-surface-400">Loading...</td></tr>
            </tbody>
            <tfoot id="stores-summary-totals" class="border-t-2 border-surface-200">
            </tfoot>
        </table>
    </div>

    <div id="stores-summary-pagination" class="flex items-center justify-between px-6 py-4 border-t border-surface-100">
    </div>
</div>

<!-- Store Statistics -->
<div class="bg-white rounded-2xl border border-surface-100 shadow-card mt-8">
    <div class="p-6 border-b border-surface-100">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-semibold text-lg text-surface-900">Store Statistics</h2>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <div class="flex items-center gap-1.5">
                <button type="button" data-preset="all" class="stats-preset-btn active px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-600 text-white transition">All Time</button>
                <button type="button" data-preset="today" class="stats-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">Today</button>
                <button type="button" data-preset="week" class="stats-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">This Week</button>
                <button type="button" data-preset="month" class="stats-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">This Month</button>
                <button type="button" data-preset="year" class="stats-preset-btn px-3 py-1.5 text-xs font-semibold rounded-lg bg-surface-100 text-surface-600 hover:bg-surface-200 transition">This Year</button>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" id="stats-date-start" class="px-3 py-1.5 text-xs border border-surface-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                <span class="text-xs text-surface-400">to</span>
                <input type="date" id="stats-date-end" class="px-3 py-1.5 text-xs border border-surface-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                <button type="button" id="stats-date-apply" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-100 text-brand-700 hover:bg-brand-200 transition">Apply</button>
            </div>

            <select id="stats-store-select" class="px-3 py-1.5 text-xs border border-surface-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none min-w-[160px]">
                <option value="">All Stores</option>
                <option value="admin">Admin (Global)</option>
                @foreach($stores as $s)
                <option value="{{ $s->id }}">{{ $s->store_name }}</option>
                @endforeach
            </select>

            <button type="button" id="stats-export-excel" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-accent-600 text-white hover:bg-accent-700 transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Download Excel
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-surface-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Store</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none stats-sort-col" data-col="products">Products <span class="sort-arrow">&#9662;</span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none stats-sort-col" data-col="categories">Categories <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none stats-sort-col" data-col="card_types">Card Types <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none stats-sort-col" data-col="templates">Templates <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none stats-sort-col" data-col="coupons">Coupons <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none stats-sort-col" data-col="events">Events <span class="sort-arrow"></span></th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase cursor-pointer select-none stats-sort-col" data-col="paper_types">Paper Types <span class="sort-arrow"></span></th>
                </tr>
            </thead>
            <tbody id="stats-body" class="divide-y divide-surface-100">
                <tr><td colspan="9" class="px-6 py-12 text-center text-surface-400">Loading...</td></tr>
            </tbody>
            <tfoot id="stats-totals" class="border-t-2 border-surface-200">
            </tfoot>
        </table>
    </div>

    <div id="stats-pagination" class="flex items-center justify-between px-6 py-4 border-t border-surface-100">
    </div>
</div>
@endsection

@if(auth()->user()->isStoreAdmin() && isset($store) && $store)
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrContainer = document.getElementById('dashboard-qr-container');
    if (qrContainer) {
        new QRCode(qrContainer, {
            text: @json(url('store/' . $store->store_code)),
            width: 180,
            height: 180,
            colorDark: '#0f172a',
            colorLight: '#f8fafc',
            correctLevel: QRCode.CorrectLevel.H,
        });
    }
});

function downloadDashboardQr() {
    const container = document.getElementById('dashboard-qr-container');
    const canvas = container ? container.querySelector('canvas') : null;
    if (canvas) {
        const link = document.createElement('a');
        link.download = @json(($store->store_code ?? 'store') . '-qr-code.png');
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
}
</script>
@endpush
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const summaryUrl = @json(route('admin.stores.summary'));
    let currentPreset = 'all';
    let summarySortBy = 'total_orders';
    let summarySortDir = 'desc';
    let currentPage = 1;

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function fmtNum(v) { return Number(v).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

    function buildSummaryParams() {
        const params = new URLSearchParams();
        const storeId = document.getElementById('store-filter-select').value;
        if (storeId) params.set('store_id', storeId);
        const ds = document.getElementById('store-date-start').value;
        const de = document.getElementById('store-date-end').value;
        if (currentPreset === 'custom' && ds && de) {
            params.set('date_start', ds);
            params.set('date_end', de);
        } else if (currentPreset && currentPreset !== 'all') {
            params.set('preset', currentPreset);
        }
        return params;
    }

    function fetchStores(page) {
        currentPage = page || 1;
        const params = buildSummaryParams();
        params.set('page', currentPage);
        params.set('sort_by', summarySortBy);
        params.set('sort_dir', summarySortDir);

        fetch(summaryUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('stores-summary-body');
            if (!data.data.length) {
                tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-surface-400">No stores found.</td></tr>';
            } else {
                const si = (data.current_page - 1) * 10;
                tbody.innerHTML = data.data.map((s, i) => `
                    <tr class="hover:bg-surface-50 transition">
                        <td class="px-4 py-3 text-sm text-surface-400">${si + i + 1}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-surface-800">${escHtml(s.store_name)}</td>
                        <td class="px-4 py-3 text-right"><span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 text-sm font-bold rounded-lg ${s.total_orders > 0 ? 'bg-brand-100 text-brand-700' : 'bg-surface-100 text-surface-400'}">${s.total_orders}</span></td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-blue-700">${fmtNum(s.online_amount)}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-emerald-700">${fmtNum(s.cash_amount)}</td>
                        <td class="px-4 py-3 text-right"><span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 text-sm font-bold rounded-lg ${s.pending_orders > 0 ? 'bg-amber-100 text-amber-700' : 'bg-surface-100 text-surface-400'}">${s.pending_orders}</span></td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-amber-700">${fmtNum(s.pending_amount)}</td>
                        <td class="px-4 py-3 text-right"><span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-0.5 text-sm font-bold rounded-lg ${s.paid_orders > 0 ? 'bg-accent-100 text-accent-700' : 'bg-surface-100 text-surface-400'}">${s.paid_orders}</span></td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-accent-700">${fmtNum(s.paid_amount)}</td>
                    </tr>
                `).join('');
            }

            // Grand totals
            const t = data.totals;
            document.getElementById('stores-summary-totals').innerHTML = `
                <tr class="bg-surface-50 font-bold">
                    <td class="px-4 py-3"></td>
                    <td class="px-4 py-3 text-sm text-surface-900">Grand Total</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.total_orders}</td>
                    <td class="px-4 py-3 text-right text-sm text-blue-800">${fmtNum(t.online_amount)}</td>
                    <td class="px-4 py-3 text-right text-sm text-emerald-800">${fmtNum(t.cash_amount)}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.pending_orders}</td>
                    <td class="px-4 py-3 text-right text-sm text-amber-800">${fmtNum(t.pending_amount)}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.paid_orders}</td>
                    <td class="px-4 py-3 text-right text-sm text-accent-800">${fmtNum(t.paid_amount)}</td>
                </tr>`;

            const pag = document.getElementById('stores-summary-pagination');
            if (data.last_page <= 1) {
                pag.innerHTML = `<span class="text-xs text-surface-400">${data.total} store${data.total !== 1 ? 's' : ''}</span><span></span>`;
            } else {
                let btns = '';
                for (let p = 1; p <= data.last_page; p++) {
                    btns += `<button data-page="${p}" class="px-3 py-1 text-xs rounded-lg font-semibold ${p === data.current_page ? 'bg-brand-600 text-white' : 'bg-surface-100 text-surface-600 hover:bg-surface-200'} transition">${p}</button>`;
                }
                pag.innerHTML = `<span class="text-xs text-surface-400">Page ${data.current_page} of ${data.last_page} (${data.total} stores)</span><div class="flex gap-1">${btns}</div>`;
                pag.querySelectorAll('[data-page]').forEach(btn => {
                    btn.addEventListener('click', () => fetchStores(parseInt(btn.dataset.page)));
                });
            }
        });
    }

    // Preset buttons
    document.querySelectorAll('.store-preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.store-preset-btn').forEach(b => {
                b.classList.remove('active', 'bg-brand-600', 'text-white');
                b.classList.add('bg-surface-100', 'text-surface-600');
            });
            btn.classList.add('active', 'bg-brand-600', 'text-white');
            btn.classList.remove('bg-surface-100', 'text-surface-600');
            currentPreset = btn.dataset.preset;
            document.getElementById('store-date-start').value = '';
            document.getElementById('store-date-end').value = '';
            fetchStores(1);
        });
    });

    document.getElementById('store-date-apply').addEventListener('click', () => {
        if (document.getElementById('store-date-start').value && document.getElementById('store-date-end').value) {
            document.querySelectorAll('.store-preset-btn').forEach(b => {
                b.classList.remove('active', 'bg-brand-600', 'text-white');
                b.classList.add('bg-surface-100', 'text-surface-600');
            });
            currentPreset = 'custom';
            fetchStores(1);
        }
    });

    document.getElementById('store-filter-select').addEventListener('change', () => fetchStores(1));

    // Sortable columns
    document.querySelectorAll('.summary-sort-col').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            if (summarySortBy === col) {
                summarySortDir = summarySortDir === 'desc' ? 'asc' : 'desc';
            } else {
                summarySortBy = col;
                summarySortDir = 'desc';
            }
            document.querySelectorAll('.summary-sort-col .sort-arrow').forEach(a => a.innerHTML = '');
            th.querySelector('.sort-arrow').innerHTML = summarySortDir === 'desc' ? '&#9662;' : '&#9652;';
            fetchStores(currentPage);
        });
    });

    // Export
    const exportUrl = @json(route('admin.stores.summary.export'));
    document.getElementById('store-export-excel').addEventListener('click', () => {
        const params = buildSummaryParams();
        window.location.href = exportUrl + '?' + params.toString();
    });

    fetchStores(1);

    // ── Store Statistics ──
    const statsUrl = @json(route('admin.stores.statistics'));
    const statsExportUrl = @json(route('admin.stores.statistics.export'));
    let statsPreset = 'all';
    let statsSortBy = 'products';
    let statsSortDir = 'desc';
    let statsPage = 1;

    function fetchStats(page) {
        statsPage = page || 1;
        const params = new URLSearchParams();
        params.set('page', statsPage);
        params.set('sort_by', statsSortBy);
        params.set('sort_dir', statsSortDir);

        const storeId = document.getElementById('stats-store-select').value;
        if (storeId) params.set('store_id', storeId);

        const ds = document.getElementById('stats-date-start').value;
        const de = document.getElementById('stats-date-end').value;
        if (statsPreset === 'custom' && ds && de) {
            params.set('date_start', ds);
            params.set('date_end', de);
        } else if (statsPreset && statsPreset !== 'all') {
            params.set('preset', statsPreset);
        }

        fetch(statsUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('stats-body');
            if (!data.data.length) {
                tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-surface-400">No data found.</td></tr>';
            } else {
                const si = (data.current_page - 1) * 10;
                tbody.innerHTML = data.data.map((s, i) => `
                    <tr class="hover:bg-surface-50 transition">
                        <td class="px-4 py-3 text-sm text-surface-400">${si + i + 1}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-surface-800">${escHtml(s.store_name)}</td>
                        <td class="px-4 py-3 text-right">${statsBadge(s.products)}</td>
                        <td class="px-4 py-3 text-right">${statsBadge(s.categories)}</td>
                        <td class="px-4 py-3 text-right">${statsBadge(s.card_types)}</td>
                        <td class="px-4 py-3 text-right">${statsBadge(s.templates)}</td>
                        <td class="px-4 py-3 text-right">${statsBadge(s.coupons)}</td>
                        <td class="px-4 py-3 text-right">${statsBadge(s.events)}</td>
                        <td class="px-4 py-3 text-right">${statsBadge(s.paper_types)}</td>
                    </tr>
                `).join('');
            }

            const t = data.totals;
            document.getElementById('stats-totals').innerHTML = `
                <tr class="bg-surface-50 font-bold">
                    <td class="px-4 py-3"></td>
                    <td class="px-4 py-3 text-sm text-surface-900">Grand Total</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.products}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.categories}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.card_types}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.templates}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.coupons}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.events}</td>
                    <td class="px-4 py-3 text-right text-sm text-surface-900">${t.paper_types}</td>
                </tr>`;

            const pag = document.getElementById('stats-pagination');
            if (data.last_page <= 1) {
                pag.innerHTML = `<span class="text-xs text-surface-400">${data.total} row${data.total !== 1 ? 's' : ''}</span><span></span>`;
            } else {
                let btns = '';
                for (let p = 1; p <= data.last_page; p++) {
                    btns += `<button data-spage="${p}" class="px-3 py-1 text-xs rounded-lg font-semibold ${p === data.current_page ? 'bg-brand-600 text-white' : 'bg-surface-100 text-surface-600 hover:bg-surface-200'} transition">${p}</button>`;
                }
                pag.innerHTML = `<span class="text-xs text-surface-400">Page ${data.current_page} of ${data.last_page} (${data.total} rows)</span><div class="flex gap-1">${btns}</div>`;
                pag.querySelectorAll('[data-spage]').forEach(btn => {
                    btn.addEventListener('click', () => fetchStats(parseInt(btn.dataset.spage)));
                });
            }
        });
    }

    function statsBadge(val) {
        const cls = val > 0 ? 'bg-brand-100 text-brand-700' : 'bg-surface-100 text-surface-400';
        return `<span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 text-sm font-bold rounded-lg ${cls}">${val}</span>`;
    }

    // Stats preset buttons
    document.querySelectorAll('.stats-preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.stats-preset-btn').forEach(b => {
                b.classList.remove('active', 'bg-brand-600', 'text-white');
                b.classList.add('bg-surface-100', 'text-surface-600');
            });
            btn.classList.add('active', 'bg-brand-600', 'text-white');
            btn.classList.remove('bg-surface-100', 'text-surface-600');
            statsPreset = btn.dataset.preset;
            document.getElementById('stats-date-start').value = '';
            document.getElementById('stats-date-end').value = '';
            fetchStats(1);
        });
    });

    document.getElementById('stats-date-apply').addEventListener('click', () => {
        if (document.getElementById('stats-date-start').value && document.getElementById('stats-date-end').value) {
            document.querySelectorAll('.stats-preset-btn').forEach(b => {
                b.classList.remove('active', 'bg-brand-600', 'text-white');
                b.classList.add('bg-surface-100', 'text-surface-600');
            });
            statsPreset = 'custom';
            fetchStats(1);
        }
    });

    document.getElementById('stats-store-select').addEventListener('change', () => fetchStats(1));

    // Sortable columns
    document.querySelectorAll('.stats-sort-col').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            if (statsSortBy === col) {
                statsSortDir = statsSortDir === 'desc' ? 'asc' : 'desc';
            } else {
                statsSortBy = col;
                statsSortDir = 'desc';
            }
            document.querySelectorAll('.stats-sort-col .sort-arrow').forEach(a => a.innerHTML = '');
            th.querySelector('.sort-arrow').innerHTML = statsSortDir === 'desc' ? '&#9662;' : '&#9652;';
            fetchStats(statsPage);
        });
    });

    // Stats export
    document.getElementById('stats-export-excel').addEventListener('click', () => {
        const params = new URLSearchParams();
        const storeId = document.getElementById('stats-store-select').value;
        if (storeId) params.set('store_id', storeId);
        const ds = document.getElementById('stats-date-start').value;
        const de = document.getElementById('stats-date-end').value;
        if (statsPreset === 'custom' && ds && de) {
            params.set('date_start', ds);
            params.set('date_end', de);
        } else if (statsPreset && statsPreset !== 'all') {
            params.set('preset', statsPreset);
        }
        window.location.href = statsExportUrl + '?' + params.toString();
    });

    fetchStats(1);
});
</script>
@endpush