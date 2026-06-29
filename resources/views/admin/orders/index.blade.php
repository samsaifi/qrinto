@extends('layouts.admin')
@section('title', 'Orders')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Orders</h1>
        <p class="text-sm text-surface-500">Manage and track all orders</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-6">
    <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-surface-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, customer..."
                   class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-surface-500 mb-1">Status</label>
            <select name="status" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All</option>
                @foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Filter</button>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-surface-500 hover:text-brand-600">Clear</a>
    </form>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-surface-50">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Pickup User Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Pickup User Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Payment</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-100">
                @forelse($orders as $order)
                <tr class="hover:bg-surface-50 transition">
                    <td class="px-6 py-4"><span class="font-mono font-semibold text-brand-600">{{ $order->order_number }}</span></td>
                    <td class="px-6 py-4 text-sm text-surface-600">
                        {{ $order->shipping_address['name'] ?? ($order->user->name ?? 'Guest') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-surface-600">
                        {{ $order->shipping_address['email'] ?? ($order->guest_email ?? ($order->user->email ?? 'N/A')) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-surface-600">{{ $order->items->count() }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg" style="background-color: {{ ($order->status_color ?? '#6b7280') }}20; color: {{ $order->status_color ?? '#6b7280' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-surface-600">
                        @if($order->store)
                            <span class="inline-flex items-center gap-1"><i data-lucide="store" class="w-3 h-3 text-surface-400"></i> {{ $order->store->store_name }}</span>
                        @else
                            <span class="text-surface-400 italic">Online</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $order->payment_status === 'paid' ? 'bg-accent-100 text-accent-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right font-semibold">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency, 0) }}</td>
                    <td class="px-6 py-4 text-right text-sm text-surface-500">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right flex items-center justify-end gap-3">
                        <button onclick="openDuplicateModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ $order->items->first()?->quantity ?? 1 }}')" 
                                class="text-surface-500 hover:text-brand-600 transition" 
                                title="Duplicate Order">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 hover:text-brand-700 text-sm font-medium">View →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-6 py-12 text-center text-surface-400">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
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
            <button onclick="closeDuplicateModal()" class="p-1 rounded-full hover:bg-surface-50 text-surface-400 hover:text-surface-600 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form id="duplicate-form" method="POST" action="">
            @csrf
            <div class="mb-5">
                <p class="text-sm text-surface-500 mb-4">
                    You are duplicating order <span id="duplicate-order-number" class="font-mono font-bold text-brand-600"></span>. All customization, uploads, and details will remain the same. Please specify the quantity for the new order.
                </p>
                
                <label for="duplicate-quantity" class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2">Quantity</label>
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
            // Set action URL dynamically
            form.action = `/admin/orders/${orderId}/duplicate`;
            orderNumSpan.textContent = orderNumber;
            qtyInput.value = currentQty;
            
            // Show modal
            modal.classList.remove('hidden');
            qtyInput.focus();
            
            // Re-render Lucide icons inside modal if needed
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
