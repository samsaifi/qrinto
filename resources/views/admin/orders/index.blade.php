@extends('layouts.admin')
@section('title', 'Orders')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display font-bold text-2xl text-surface-900">Orders</h1>
            <p class="text-sm text-surface-500">Manage and track all customer orders</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-surface-200/70 shadow-xs p-4 mb-6">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-surface-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search Order #, Customer, Email..."
                    class="w-full text-xs px-3.5 py-2 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Status</label>
                <select name="status"
                    class="text-xs px-3.5 py-2 rounded-xl border border-surface-200 bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-brand-600 text-white text-xs font-semibold rounded-xl hover:bg-brand-700 transition shadow-2xs">Filter</button>
                <a href="{{ route('admin.orders.index') }}"
                    class="px-3 py-2 text-xs font-medium text-surface-500 hover:text-brand-600 transition">Clear</a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-surface-200/70 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs align-middle">
                <thead>
                    <tr
                        class="bg-surface-50 border-b border-surface-100 text-surface-500 uppercase tracking-wider font-semibold">
                        <th class="px-4 py-3.5 w-[150px]">Order</th>
                        <th class="px-4 py-3.5 w-[130px]">Pickup User</th>
                        <th class="px-4 py-3.5 w-[220px]">Email</th>
                        <th class="px-4 py-3.5 w-[60px] text-center">Items</th>
                        <th class="px-4 py-3.5 w-[100px] text-center">Status</th>
                        <th class="px-4 py-3.5 w-[150px]">Store</th>
                        <th class="px-4 py-3.5 w-[100px] text-center">Payment</th>
                        <th class="px-4 py-3.5 w-[80px] text-right">Total</th>
                        <th class="px-4 py-3.5 w-[100px] text-center">Date</th>
                        <th class="px-4 py-3.5 w-[100px] text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 font-medium">
                    @forelse($orders as $order)
                        @php
                            $rawName = $order->shipping_address['name'] ?? ($order->user->name ?? 'Guest');
                            $formattedName = ucwords(strtolower(trim($rawName)));

                            $rawEmail =
                                $order->shipping_address['email'] ??
                                ($order->guest_email ?? ($order->user->email ?? 'N/A'));

                            $storeName = $order->store ? ucwords(strtolower(trim($order->store->store_name))) : null;
                        @endphp
                        <tr class="hover:bg-surface-50/70 transition h-14">
                            <!-- Order ID -->
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                    class="font-mono text-xs font-bold text-brand-600 hover:text-brand-700 transition whitespace-nowrap block"
                                    title="{{ $order->order_number }}">
                                    {{ $order->order_number }}
                                </a>
                            </td>

                            <!-- Pickup User Name -->
                            <td class="px-4 py-3">
                                <span class="text-xs text-surface-900 font-semibold truncate block max-w-[125px]"
                                    title="{{ $formattedName }}">
                                    {{ $formattedName }}
                                </span>
                            </td>

                            <!-- Pickup User Email -->
                            <td class="px-4 py-3">
                                <span class="text-xs text-surface-600 truncate block max-w-[210px]"
                                    title="{{ $rawEmail }}">
                                    {{ $rawEmail }}
                                </span>
                            </td>

                            <!-- Items Count -->
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-surface-100 text-surface-700 font-bold text-xs">
                                    {{ $order->items->count() }}
                                </span>
                            </td>

                            <!-- Order Status -->
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg inline-block whitespace-nowrap shadow-2xs"
                                    style="background-color: {{ $order->status_color ?? '#6b7280' }}20; color: {{ $order->status_color ?? '#6b7280' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>

                            <!-- Store Name -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5 min-w-0 max-w-[145px]">
                                    @if ($storeName)
                                        <i data-lucide="store" class="w-3.5 h-3.5 text-surface-400 flex-shrink-0"></i>
                                        <span class="text-xs text-surface-700 truncate font-medium"
                                            title="{{ $storeName }}">{{ $storeName }}</span>
                                    @else
                                        <span class="text-xs text-surface-400 italic">Online</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Payment Status -->
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 text-[10px] uppercase font-bold rounded inline-block whitespace-nowrap {{ $order->payment_status === 'paid' ? ' bg-gray-100  text-gray-800 border  border-gray-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>

                            <!-- Total -->
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold text-surface-900 text-xs">
                                    {{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs text-surface-600 whitespace-nowrap font-medium">
                                    {{ $order->created_at->format('d M Y') }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2.5 whitespace-nowrap">
                                    <button type="button"
                                        onclick="openDuplicateModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ $order->items->first()?->quantity ?? 1 }}')"
                                        class="p-1 text-surface-400 hover:text-brand-600 transition"
                                        title="Duplicate Order">
                                        <i data-lucide="copy" class="w-4 h-4"></i>
                                    </button>
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                        class="text-brand-600 hover:text-brand-700 text-xs font-bold inline-flex items-center gap-1">
                                        View <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-surface-400">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="px-6 py-4 border-t border-surface-100">{{ $orders->links() }}</div>
        @endif
    </div>

    <!-- Duplicate Order Modal -->
    <div id="duplicate-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-surface-900/40 backdrop-blur-sm" onclick="closeDuplicateModal()"></div>

        <!-- Content Card -->
        <div class="bg-white rounded-2xl border border-surface-100 shadow-xl max-w-md w-full mx-4 p-6 relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-bold text-lg text-surface-900 flex items-center gap-2">
                    <i data-lucide="copy" class="w-5 h-5 text-brand-500"></i>
                    Duplicate Order
                </h3>
                <button onclick="closeDuplicateModal()"
                    class="p-1 rounded-full hover:bg-surface-50 text-surface-400 hover:text-surface-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="duplicate-form" method="POST" action="">
                @csrf
                <div class="mb-5">
                    <p class="text-sm text-surface-500 mb-4">
                        You are duplicating order <span id="duplicate-order-number"
                            class="font-mono font-bold text-brand-600"></span>. All customization, uploads, and details
                        will remain the same. Please specify the quantity for the new order.
                    </p>

                    <label for="duplicate-quantity"
                        class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2">Quantity</label>
                    <div class="relative rounded-xl shadow-sm">
                        <input type="number" name="quantity" id="duplicate-quantity" min="1" required
                            class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500 pl-4 pr-12 py-2.5 font-bold text-surface-900">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-sm text-surface-400 font-medium">pcs</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeDuplicateModal()"
                        class="px-4 py-2.5 border border-surface-200 text-surface-500 rounded-xl hover:bg-surface-50 text-sm font-semibold transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 text-sm font-semibold shadow-md shadow-brand-100 transition active:scale-[0.98]">
                        Duplicate Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDuplicateModal(orderId, orderNumber, currentQty) {
            const modal = document.getElementById('duplicate-modal');
            const form = document.getElementById('duplicate-form');
            const orderNumSpan = document.getElementById('duplicate-order-number');
            const qtyInput = document.getElementById('duplicate-quantity');

            if (modal && form && orderNumSpan && qtyInput) {
                form.action = `/admin/orders/${orderId}/duplicate`;
                orderNumSpan.textContent = orderNumber;
                qtyInput.value = currentQty;

                modal.classList.remove('hidden');
                qtyInput.focus();

                if (window.lucide) window.lucide.createIcons();
            }
        }

        function closeDuplicateModal() {
            const modal = document.getElementById('duplicate-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
@endsection
