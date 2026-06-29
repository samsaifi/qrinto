@extends('layouts.admin')
@section('title', 'Order #' . $order->order_number)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Order {{ $order->order_number }}</h1>
        <p class="text-sm text-surface-500">Placed {{ $order->created_at->format('M d, Y h:i A') }} by {{ $order->user->name ?? 'N/A' }}</p>
    </div>
    <div class="flex items-center gap-3">
        @if($order->store)
        <a href="{{ route('admin.stores.edit', $order->store) }}" class="px-4 py-2 text-sm font-semibold rounded-xl bg-purple-50 text-purple-700 border border-purple-100 flex items-center gap-2 hover:bg-purple-100 transition">
            <i data-lucide="store" class="w-4 h-4"></i> {{ $order->store->store_name }}
        </a>
        @endif
        @php
        $adminPrintUrl = null;
        $items = $order->items;
        if ($items && $items->isNotEmpty()) {
        $images = $items->first()->uploaded_images;
        if ($images && is_array($images) && count($images) > 0) {
        $firstImage = reset($images);
        $adminPrintUrl = str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage);
        } elseif (!empty($items->first()->customization_data['preview_url'])) {
        $adminPrintUrl = $items->first()->customization_data['preview_url'];
        }
        }
        @endphp

        @if($adminPrintUrl)

        <button type="button" onclick="printBrowserPdf('{{ $items && $items->isNotEmpty() && $items->first()->pdf_path ? asset('storage/' . $items->first()->pdf_path) : route('admin.orders.print.page', $order->id) }}')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
            <i data-lucide="printer" class="w-4 h-4"></i> Print Design
        </button>
        
        @endif 
         
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Items -->
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="font-display font-semibold text-lg mb-5">Order Items</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                <div class="flex gap-4 p-4 bg-surface-50 rounded-xl">
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        @php
                        $previewUrl = !empty($item->customization_data['preview_url']) ? $item->customization_data['preview_url'] : null;
                        @endphp

                        @if(!empty($item->uploaded_images) && is_array($item->uploaded_images))
                        @foreach($item->uploaded_images as $idx => $img)
                        @php
                        $imgUrl = str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        @endphp
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-surface-200 flex-shrink-0">
                            <a href="{{ $imgUrl }}" target="_blank" title="View Design Canvas">
                                <img src="{{ $imgUrl }}" class="w-full h-full object-cover hover:opacity-80 transition">
                            </a>
                        </div>
                        @endforeach
                        @elseif($previewUrl)
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-surface-200 flex-shrink-0">
                            <a href="{{ $previewUrl }}" target="_blank" title="View Full Design">
                                <img src="{{ $previewUrl }}" class="w-full h-full object-cover hover:opacity-80 transition">
                            </a>
                        </div>
                        @elseif($item->product && $item->product->featured_image_url)
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-surface-200 flex-shrink-0">
                            <img src="{{ $item->product->featured_image_url }}" class="w-full h-full object-cover">
                        </div>
                        @else
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-surface-200 flex-shrink-0 flex items-center justify-center">
                            <i data-lucide="image" class="w-5 h-5 text-surface-300"></i>
                        </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-surface-800">{{ $item->product_name }}</h3>
                        <p class="text-xs text-surface-500">{{ \App\Services\CurrencyService::formatWithCurrency($item->unit_price, $order->currency) }} × {{ $item->quantity }}</p>

                        {{-- Display Selected Options --}}
                        @if($item->selected_options && count($item->selected_options) > 0)
                        @php
                        // Handle both ID-based options and string-based options
                        $optionIds = array_filter($item->selected_options, fn($val) => is_numeric($val));
                        $stringOptions = array_filter($item->selected_options, fn($val) => !is_numeric($val));
                        $selectedOptionValues = !empty($optionIds) ? \App\Models\ProductOptionValue::with('optionGroup')->whereIn('id', $optionIds)->get() : collect();
                        @endphp
                        <div class="mt-2 space-y-1">
                            @foreach($selectedOptionValues as $optVal)
                            <p class="text-xs text-surface-600">
                                <span class="font-medium text-surface-700">{{ $optVal->optionGroup->name }}:</span>
                                {{ $optVal->label }}
                            </p>
                            @endforeach

                            @foreach($stringOptions as $key => $val)
                            <p class="text-xs text-surface-600">
                                <span class="font-medium text-surface-700">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                {{ $val }}
                            </p>
                            @endforeach
                        </div>
                        @endif

                        {{-- Display Customization Data --}}
                        @if($item->customization_data)
                        <div class="mt-2 text-xs text-surface-500 space-y-1">
                            @foreach((array)$item->customization_data as $k => $v)
                            @if(!in_array($k, ['preview_url', 'design_id', 'style', 'uploaded_images']))
                            <p>
                                <span class="font-medium text-surface-700">{{ ucfirst(str_replace('_', ' ', $k)) }}:</span>
                                @if(is_array($v))
                                {{ implode(', ', $v) }}
                                @else
                                {{ $v }}
                                @endif
                            </p>
                            @endif

                            {{-- Special display for style data (like in Split Canvas) --}}
                            @if($k === 'style' && is_array($v))
                            @foreach($v as $styleKey => $styleVal)
                            @if(in_array($styleKey, ['fontSize', 'fontFamily', 'textAlign', 'isPortrait']))
                            <p>
                                <span class="font-medium text-surface-700">{{ ucfirst(preg_replace('/(?<!^)[A-Z]/', ' $0', $styleKey)) }}:</span>
                                {{ is_bool($styleVal) ? ($styleVal ? 'Yes' : 'No') : $styleVal }}
                            </p>
                            @endif
                            @endforeach
                            @endif
                            @endforeach
                        </div>

                        @if($item->pdf_path)
                        <div class="mt-3">
                            <a href="{{ asset('storage/' . $item->pdf_path) }}" download="design-pdf-{{ $item->order_id }}-{{ $item->id }}.pdf" target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-lg hover:bg-rose-100 transition">
                                <i data-lucide="download" class="w-3 h-3"></i> Download Combined PDF
                            </a>
                        </div>
                        @elseif($previewUrl)
                        <div class="mt-3">
                            <a href="{{ $previewUrl }}" download="design-{{ $item->order_id }}-{{ $item->id }}.jpg" target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-brand-50 border border-brand-200 text-brand-700 text-xs font-semibold rounded-lg hover:bg-brand-100 transition">
                                <i data-lucide="download" class="w-3 h-3"></i> Download Design
                            </a>
                        </div>
                        @endif
                        @endif
                    </div>
                    <span class="font-bold text-surface-800">{{ \App\Services\CurrencyService::formatWithCurrency($item->total_price, $order->currency) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Flow Selections --}}
        @if($order->flow_data)
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="font-display font-semibold text-lg mb-4">Flow Selections</h2>
            <div class="grid grid-cols-2 gap-4">
                @if(isset($order->flow_data['category_name']))
                <div>
                    <label class="block text-xs font-semibold text-surface-500 uppercase tracking-widest mb-1">Category</label>
                    <p class="text-sm font-medium text-surface-900">{{ $order->flow_data['category_name'] }}</p>
                </div>
                @endif
                @if(isset($order->flow_data['size_name']))
                <div>
                    <label class="block text-xs font-semibold text-surface-500 uppercase tracking-widest mb-1">Selected Size</label>
                    <p class="text-sm font-medium text-surface-900">{{ $order->flow_data['size_name'] }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Shipping/Pickup Details -->
        @if($order->shipping_address)
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            @php $addr = $order->shipping_address; @endphp

            @if(($addr['type'] ?? '') === 'store_pickup')
            <h2 class="font-display font-semibold text-lg mb-4">Pickup Details</h2>
            <p class="text-sm text-surface-600 leading-relaxed">
                <strong>Pickup Store:</strong> {{ $order->store ? $order->store->store_name : 'No Store Selected' }}<br>
                <strong>Pickup Name:</strong> {{ $addr['name'] ?? '' }}<br>
                <strong>Pickup Email:</strong> {{ $addr['pickup_email'] ?? ($order->guest_email ?? ($order->user->email ?? 'N/A')) }}<br>
                <strong>Contact Number:</strong> {{ $addr['phone'] ?? '' }}<br>
                <strong>Payment Method:</strong>
                @if($order->payment_gateway === 'cash')
                <span class="text-amber-600 font-semibold">Pay at Store (Cash)</span>
                @elseif($order->payment_gateway === 'paypal')
                <span class="text-indigo-600 font-semibold">Online (PayPal)</span>
                @else
                <span class="text-surface-600 font-semibold">{{ ucfirst($order->payment_gateway ?? 'Unknown') }}</span>
                @endif
            </p>
            @else
            <h2 class="font-display font-semibold text-lg mb-4">Shipping Address</h2>
            <p class="text-sm text-surface-600 leading-relaxed">
                <strong>{{ $addr['full_name'] ?? '' }}</strong><br>
                {{ $addr['address_line_1'] ?? '' }}<br>
                @if(!empty($addr['address_line_2'])){{ $addr['address_line_2'] }}<br>@endif
                @if(!empty($addr['city']) || !empty($addr['state']))
                {{ $addr['city'] ?? '' }}, {{ $addr['state'] ?? '' }} {{ $addr['postal_code'] ?? '' }}<br>
                @endif
                @if(!empty($addr['phone'])) Phone: {{ $addr['phone'] }} @endif
            </p>
            @endif
        </div>
        @endif

        <!-- Admin Notes -->
        @if($order->admin_notes)
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 border-l-4 border-l-brand-600">
            <h2 class="font-display font-semibold text-lg mb-4 text-brand-700">Admin Notes</h2>
            <p class="text-sm text-surface-600 leading-relaxed whitespace-pre-wrap">{{ $order->admin_notes }}</p>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        <!-- Update Status -->
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="font-display font-semibold text-lg mb-4">Update Status</h2>
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 uppercase tracking-widest mb-1.5">Order Status</label>
                        <select name="status" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 text-sm">
                            @foreach(['pending','confirmed','processing','shipped','delivered','cancelled', 'refunded'] as $s)
                            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-500 uppercase tracking-widest mb-1.5">Payment Status</label>
                        <select name="payment_status" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 text-sm">
                            @foreach(['pending','paid','failed','refunded'] as $ps)
                            <option value="{{ $ps }}" {{ $order->payment_status === $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Tracking Number</label>
                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}"
                        class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Admin Notes</label>
                    <textarea name="admin_notes" rows="3" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">{{ $order->admin_notes }}</textarea>
                </div>
                <button type="submit" class="w-full px-5 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    Update Order
                </button>
            </form>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="font-display font-semibold text-lg mb-4">Summary</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-surface-500">Subtotal</span><span>{{ \App\Services\CurrencyService::formatWithCurrency($order->subtotal, $order->currency) }}</span></div>
                @if($order->discount > 0)
                <div class="flex justify-between text-accent-600"><span>Discount</span><span>-{{ \App\Services\CurrencyService::formatWithCurrency($order->discount, $order->currency) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-surface-500">Shipping</span><span>{{ \App\Services\CurrencyService::formatWithCurrency($order->shipping_cost, $order->currency) }}</span></div>
                <div class="border-t border-surface-100 pt-2 flex justify-between font-bold text-lg">
                    <span>Total</span><span class="text-brand-600">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
                </div>
                <div class="flex justify-between mt-2">
                    <span class="text-surface-500">Payment</span>
                    <span class="px-2 py-0.5 text-xs font-bold rounded {{ $order->payment_status === 'paid' ? 'bg-accent-100 text-accent-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                @if($order->payment_method)
                <div class="flex justify-between"><span class="text-surface-500">Method</span><span>{{ ucfirst($order->payment_method) }}</span></div>
                @endif
            </div>
        </div>

        <!-- Status Change History -->
        @if($order->statusHistories && count($order->statusHistories) > 0)
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="font-display font-semibold text-lg mb-4">Status History</h2>
            <div class="space-y-4">
                @foreach($order->statusHistories as $history)
                <div class="relative pl-4 border-l-2 border-surface-200">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-brand-500"></div>
                    <p class="text-sm font-semibold text-surface-800">
                        {{ ucfirst($history->new_status) }}
                        @if($history->old_status && $history->old_status !== $history->new_status)
                        <span class="text-xs font-normal text-surface-400"> (from {{ ucfirst($history->old_status) }})</span>
                        @endif
                    </p>
                    <p class="text-xs text-surface-500 mt-1">{{ $history->created_at->format('M d, Y h:i A') }} @if($history->user)by {{ $history->user->name }}@endif</p>
                    @if($history->notes)
                    <p class="text-xs text-surface-600 mt-2 bg-surface-50 p-2 rounded border border-surface-100 italic">{{ $history->notes }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Print History -->
        @if($order->printLogs && count($order->printLogs) > 0)
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="font-display font-semibold text-lg mb-4">Print History</h2>
            <div class="space-y-4">
                @foreach($order->printLogs as $log)
                <div class="relative pl-4 border-l-2 {{ $log->status === 'sent' ? 'border-accent-400' : ($log->status === 'failed' ? 'border-red-400' : 'border-surface-200') }}">
                    <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full {{ $log->status === 'sent' ? 'bg-accent-500' : ($log->status === 'failed' ? 'bg-red-500' : 'bg-surface-400') }}"></div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-surface-800">
                            {{ $log->store->store_name ?? 'Printer' }}
                        </p>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase {{ $log->status === 'sent' ? 'bg-accent-100 text-accent-700' : ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-surface-100 text-surface-600') }}">
                            {{ $log->status }}
                        </span>
                    </div>
                    <p class="text-xs text-surface-500 mt-1">{{ $log->created_at->format('M d, h:i A') }} @if($log->user)by {{ $log->user->name }}@endif</p>
                    @if($log->error_message)
                    <p class="text-xs text-red-600 mt-2 bg-red-50 p-2 rounded border border-red-100">{{ $log->error_message }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

{{-- Print Store Selection Modal --}}
<div id="printStoreModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closePrintModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[80vh] flex flex-col animate-in z-10">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-surface-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <i data-lucide="printer" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-lg text-surface-900">Select Store Printer</h3>
                        <p class="text-xs text-surface-500">Choose a store to print this order</p>
                    </div>
                </div>
                <button onclick="closePrintModal()" class="p-2 rounded-xl hover:bg-surface-100 text-surface-400 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Search --}}
            <div class="px-6 py-4 border-b border-surface-50">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400"></i>
                    <input type="text" id="printStoreSearch" placeholder="Search store by name or city..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border-surface-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                <div class="flex items-center gap-3 px-4 py-3 bg-indigo-50 rounded-xl">
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
                    <span id="sendPrintBtnText">Send Print</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-in {
        animation: modalIn 0.25s ease-out;
    }

    @keyframes modalIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .store-row {
        transition: all 0.15s ease;
    }

    .store-row:hover {
        background: #f8fafc;
    }

    .store-row.selected {
        background: #eef2ff;
        border-color: #6366f1;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            lucide.createIcons();
        }, 100);
    }

    function closePrintModal() {
        document.getElementById('printStoreModal').classList.add('hidden');
    }

    function loadStores(query) {
        const loading = document.getElementById('storeListLoading');
        const list = document.getElementById('storeList');
        const empty = document.getElementById('storeListEmpty');

        loading.classList.remove('hidden');
        list.classList.add('hidden');
        empty.classList.add('hidden');

        fetch(`{{ route('admin.stores.search') }}?q=${encodeURIComponent(query)}`, {
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

                list.innerHTML = stores.map(store => `
            <div class="store-row flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-transparent cursor-pointer ${store.id == selectedStoreId ? 'selected' : ''}"
                 onclick="selectStore(${store.id}, '${store.store_name.replace(/'/g, "\\'")}', '${store.city || ''}', '${store.printer_ip_address || ''}')"
                 data-store-id="${store.id}">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">${store.store_name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-surface-800">${store.store_name}</p>
                    <p class="text-xs text-surface-500">${store.city || 'N/A'} · ${store.printer_ip_address || 'No IP'}</p>
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-lg ${store.is_active ? 'bg-accent-100 text-accent-700' : 'bg-red-100 text-red-700'}">
                        ${store.is_active ? 'Online' : 'Offline'}
                    </span>
                </div>
            </div>
        `).join('');

                list.classList.remove('hidden');
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
        document.getElementById('selectedStoreDetails').textContent = `${city} · ${ip}`;
        document.getElementById('sendPrintBtn').disabled = false;

        // Highlight selected row
        document.querySelectorAll('.store-row').forEach(row => {
            row.classList.toggle('selected', row.dataset.storeId == id);
        });
    }

    function sendPrint() {
        if (!selectedStoreId) return;

        const btn = document.getElementById('sendPrintBtn');
        const btnText = document.getElementById('sendPrintBtnText');
        const storeName = document.getElementById('selectedStoreName').textContent;

        btn.disabled = true;
        btnText.innerHTML = '<span class="inline-flex items-center gap-2"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span> Sending...</span>';

        fetch(`{{ route('admin.orders.print', $order->id) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    store_id: selectedStoreId
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Print command sent to ${storeName}`,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    setTimeout(() => closePrintModal(), 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Print Failed',
                        html: data.message || 'Failed to send print command.',
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: 'A network error occurred. Please try again.',
                });
            })
            .finally(() => {
                btn.disabled = false;
                btnText.textContent = 'Send Print';
            });
    }

    function sendDirectPrint(storeId, storeName, printType = 'design') {
        const titleText = printType === 'invoice' ? `Send Invoice to ${storeName}?` : `Send Design to ${storeName}?`;
        const descText = printType === 'invoice' ?
            "This will send the order receipt to the store's printer via FTP." :
            "This will send the design file to the store's printer via FTP.";

        Swal.fire({
            title: titleText,
            text: descText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, send it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sending...',
                    text: 'Connecting to printer...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`{{ route('admin.orders.print', $order->id) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            store_id: storeId,
                            print_type: printType
                        })
                    })
                    .then(r => r.json())
                    .then(data => {

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Print Sent!',
                                text: `Print command successfully dispatched to ${storeName}.`,
                                timer: 2500
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Print Failed',
                                html: data.message || 'Failed to send print command.',
                            });
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: 'A network error occurred. Please try again.',
                        });
                    });
            }
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

    function printBrowserPdf(pdfUrl) {
        if (!pdfUrl) return;
        
        let iframe = document.getElementById('print-pdf-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print-pdf-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        
        iframe.src = pdfUrl;
        
        iframe.onload = function() {
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 500);
        };
    }
</script>
@endpush