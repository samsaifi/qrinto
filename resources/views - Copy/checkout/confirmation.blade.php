@extends('layouts.app')
@section('title', 'Order Confirmed - #' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-16 text-center">
    <div class="bg-white rounded-3xl border border-surface-100 shadow-premium p-8 lg:p-12">
        <div class="w-20 h-20 rounded-full bg-accent-100 flex items-center justify-center mx-auto mb-6 animate-pulse-soft">
            <i data-lucide="check-circle" class="w-10 h-10 text-accent-600"></i>
        </div>

        <h1 class="font-display font-bold text-3xl text-surface-900 mb-3">Order Confirmed!</h1>
        <p class="text-surface-500 text-lg mb-2">Thank you for your order. We'll get started right away!</p>
        <p class="text-sm text-surface-400">Order Number: <span class="font-mono font-bold text-surface-700">{{ $order->order_number }}</span></p>

        <div class="border-t border-surface-100 mt-8 pt-8">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
                <div>
                    <p class="text-xs text-surface-500 mb-1">Status</p>
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-sm font-semibold rounded-lg">{{ ucfirst($order->status) }}</span>
                </div>
                <div>
                    <p class="text-xs text-surface-500 mb-1">Items</p>
                    <p class="font-bold text-surface-800">{{ $order->items->sum('quantity') }}</p>
                </div>
                <div>
                    <p class="text-xs text-surface-500 mb-1">Payment</p>
                    <span class="inline-block px-3 py-1 bg-{{ $order->payment_status === 'paid' ? 'accent' : 'yellow' }}-100 text-{{ $order->payment_status === 'paid' ? 'accent' : 'yellow' }}-700 text-sm font-semibold rounded-lg">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-surface-500 mb-1">Total</p>
                    <p class="font-bold text-brand-600 text-lg">${{ number_format($order->total, 0) }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-center gap-4 mt-10">
            {{-- Print Order Button --}}
            <button type="button" onclick="openPrintModal()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 group">
                <i data-lucide="printer" class="w-4 h-4 group-hover:scale-110 transition-transform"></i> Print Order
            </button>
            @auth
            <a href="{{ route('customer.orders.show', $order) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                <i data-lucide="eye" class="w-4 h-4"></i> View Order Details
            </a>
            @endauth
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-surface-100 text-surface-700 font-semibold rounded-xl hover:bg-surface-200 transition">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i> Continue Shopping
            </a>
        </div>
    </div>
</div>

{{-- Print Store Selection Modal --}}
<div id="printStoreModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closePrintModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] flex flex-col animate-in z-10">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-surface-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <i data-lucide="printer" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-lg text-surface-900">Select Store Printer</h3>
                        <p class="text-xs text-surface-500">Choose a nearby store to print your order</p>
                    </div>
                </div>
                <button onclick="closePrintModal()" class="p-2 rounded-xl hover:bg-surface-100 text-surface-400 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Search --}}
            <div class="px-6 py-4 border-b border-surface-50">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400 pointer-events-none"></i>
                    <input type="text" id="printStoreSearch" placeholder="Search store by name or city..."
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl border-surface-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition">
                </div>
            </div>

            {{-- Store List --}}
            <div class="flex-1 overflow-y-auto px-6 py-3" id="storeListContainer" style="max-height: 300px;">
                <div id="storeListLoading" class="py-8 text-center text-surface-400">
                    <div class="w-6 h-6 border-2 border-indigo-300 border-t-indigo-600 rounded-full animate-spin mx-auto mb-2"></div>
                    <p class="text-sm">Loading stores...</p>
                </div>
                <div id="storeList" class="space-y-2 hidden"></div>
                <div id="storeListEmpty" class="py-8 text-center text-surface-400 hidden">
                    <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <p class="text-sm">No stores found</p>
                </div>
            </div>

            {{-- Selected Store --}}
            <div id="selectedStoreInfo" class="px-6 py-3 border-t border-surface-100 hidden">
                <div class="flex items-center gap-3 px-4 py-3 bg-indigo-50 rounded-xl border border-indigo-100">
                    <i data-lucide="check-circle" class="w-5 h-5 text-indigo-600 flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-indigo-900" id="selectedStoreName"></p>
                        <p class="text-xs text-indigo-600" id="selectedStoreDetails"></p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-surface-100">
                <button onclick="closePrintModal()"
                        class="px-5 py-2.5 text-sm font-semibold bg-surface-100 text-surface-700 rounded-xl hover:bg-surface-200 transition">
                    Cancel
                </button>
                <button id="sendPrintBtn" disabled onclick="sendPrint()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span id="sendPrintBtnText">Send to Printer</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Success Toast Container --}}
<div id="printToast" class="fixed top-6 right-6 z-[200] hidden">
    <div class="flex items-center gap-3 px-5 py-4 bg-white rounded-2xl shadow-2xl border border-surface-100 animate-in">
        <div class="w-10 h-10 rounded-xl bg-accent-100 flex items-center justify-center flex-shrink-0">
            <i data-lucide="check-circle" class="w-5 h-5 text-accent-600"></i>
        </div>
        <div>
            <p class="font-semibold text-surface-900 text-sm" id="toastTitle">Success!</p>
            <p class="text-xs text-surface-500" id="toastMessage">Print command sent</p>
        </div>
    </div>
</div>

<style>
.animate-in { animation: modalIn 0.25s ease-out; }
@keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.store-row { transition: all 0.15s ease; cursor: pointer; }
.store-row:hover { background: #f8fafc; transform: translateX(2px); }
.store-row.selected { background: #eef2ff; border-color: #6366f1; }
.store-row.selected:hover { background: #e0e7ff; }
</style>
@endsection

@push('scripts')
<script>
let selectedStoreId = null;
let searchTimeout = null;

function openPrintModal() {
    document.getElementById('printStoreModal').classList.remove('hidden');
    selectedStoreId = null;
    document.getElementById('selectedStoreInfo').classList.add('hidden');
    document.getElementById('sendPrintBtn').disabled = true;
    document.getElementById('printStoreSearch').value = '';
    loadStores('');
    setTimeout(() => {
        document.getElementById('printStoreSearch').focus();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }, 150);
}

function closePrintModal() {
    document.getElementById('printStoreModal').classList.add('hidden');
}

function showToast(title, message, isError = false) {
    const toast = document.getElementById('printToast');
    document.getElementById('toastTitle').textContent = title;
    document.getElementById('toastMessage').textContent = message;

    const icon = toast.querySelector('[data-lucide]');
    const iconWrap = icon.closest('div');
    if (isError) {
        iconWrap.classList.remove('bg-accent-100');
        iconWrap.classList.add('bg-red-100');
    } else {
        iconWrap.classList.remove('bg-red-100');
        iconWrap.classList.add('bg-accent-100');
    }

    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 4000);
}

function loadStores(query) {
    const loading = document.getElementById('storeListLoading');
    const list = document.getElementById('storeList');
    const empty = document.getElementById('storeListEmpty');

    loading.classList.remove('hidden');
    list.classList.add('hidden');
    empty.classList.add('hidden');

    fetch(`{{ route('stores.search') }}?q=${encodeURIComponent(query)}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(data => {
        loading.classList.add('hidden');
        const stores = data.stores || [];

        if (stores.length === 0) {
            empty.classList.remove('hidden');
            return;
        }

        list.innerHTML = stores.map(store => {
            const safeName = store.store_name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
            return `
            <div class="store-row flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-transparent ${store.id == selectedStoreId ? 'selected' : ''}"
                 onclick="selectStore(${store.id}, '${safeName}', '${(store.city || '').replace(/'/g, "\\'")}', '${store.printer_ip_address || ''}')"
                 data-store-id="${store.id}">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">${store.store_name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-surface-800">${store.store_name}</p>
                    <p class="text-xs text-surface-500">${store.city || 'N/A'}</p>
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-lg bg-accent-100 text-accent-700">
                        Available
                    </span>
                </div>
            </div>`;
        }).join('');

        list.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    })
    .catch(err => {
        loading.classList.add('hidden');
        empty.classList.remove('hidden');
        console.error('Failed to load stores:', err);
    });
}

function selectStore(id, name, city, ip) {
    selectedStoreId = id;
    document.getElementById('selectedStoreInfo').classList.remove('hidden');
    document.getElementById('selectedStoreName').textContent = name;
    document.getElementById('selectedStoreDetails').textContent = `${city}${ip ? ' · ' + ip : ''}`;
    document.getElementById('sendPrintBtn').disabled = false;

    // Highlight selected row
    document.querySelectorAll('.store-row').forEach(row => {
        row.classList.toggle('selected', row.dataset.storeId == id);
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function sendPrint() {
    if (!selectedStoreId) return;

    const btn = document.getElementById('sendPrintBtn');
    const btnText = document.getElementById('sendPrintBtnText');
    const storeName = document.getElementById('selectedStoreName').textContent;

    btn.disabled = true;
    btnText.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span> Sending...</span>';

    fetch(`{{ route('orders.print', $order->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ store_id: selectedStoreId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Print Sent!', `Print command sent to ${storeName}`);
            setTimeout(() => closePrintModal(), 2000);
        } else {
            showToast('Print Failed', data.message || 'Failed to send print command.', true);
        }
    })
    .catch(err => {
        showToast('Error', 'A network error occurred. Please try again.', true);
    })
    .finally(() => {
        btn.disabled = false;
        btnText.textContent = 'Send to Printer';
    });
}

// Debounced search
document.getElementById('printStoreSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadStores(this.value), 300);
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePrintModal();
});
</script>
@endpush
