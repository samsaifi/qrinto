@extends('layouts.app')
@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        @include('customer.partials.sidebar')

        <div class="flex-1">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-display font-bold text-2xl text-surface-900">Order {{ $order->order_number }}</h1>
                    <p class="text-sm text-surface-500">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <span class="px-4 py-2 text-sm font-bold rounded-xl" style="background: {{ $order->status_color }}15; color: {{ $order->status_color }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <!-- Tracking -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 mb-6">
                <h2 class="font-display font-semibold text-lg mb-5">Order Progress</h2>
                @php
                    $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                    $currentIndex = array_search($order->status, $statuses) ?: 0;
                @endphp
                <div class="flex items-center justify-between relative">
                    <div class="absolute top-5 left-0 right-0 h-1 bg-surface-200 rounded-full">
                        <div class="h-full bg-brand-500 rounded-full transition-all" style="width: {{ ($currentIndex / (count($statuses) - 1)) * 100 }}%"></div>
                    </div>
                    @foreach($statuses as $i => $status)
                    <div class="relative flex flex-col items-center gap-2 z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold {{ $i <= $currentIndex ? 'bg-brand-600 text-white' : 'bg-surface-200 text-surface-400' }}">
                            @if($i < $currentIndex) ✓ @else {{ $i + 1 }} @endif
                        </div>
                        <span class="text-xs font-medium {{ $i <= $currentIndex ? 'text-brand-600' : 'text-surface-400' }} capitalize hidden sm:block">{{ $status }}</span>
                    </div>
                    @endforeach
                </div>
                @if($order->tracking_number)
                <div class="mt-6 p-4 bg-surface-50 rounded-xl">
                    <p class="text-sm text-surface-500">Tracking Number: <span class="font-mono font-bold text-surface-800">{{ $order->tracking_number }}</span></p>
                </div>
                @endif
            </div>

            <!-- Items -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 mb-6">
                <h2 class="font-display font-semibold text-lg mb-5">Order Items</h2>
                <div class="space-y-4">
                    @forelse($order->items as $item)
                    <div class="flex gap-4 p-4 bg-surface-50 rounded-xl">
                        <div class="w-20 h-20 rounded-lg overflow-hidden bg-surface-200 flex-shrink-0">
                            @if(!empty($item->customization_data['preview_url']))
                            <a href="{{ $item->customization_data['preview_url'] }}" target="_blank" title="View Full Design">
                                <img src="{{ $item->customization_data['preview_url'] }}" alt="Custom Design Preview" class="w-full h-full object-cover hover:opacity-80 transition">
                            </a>
                            @elseif($item->product && $item->product->featured_image_url)
                            <img src="{{ $item->product->featured_image_url }}" alt="" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center"><i data-lucide="image" class="w-6 h-6 text-surface-300"></i></div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-surface-800">{{ $item->product_name }}</h3>
                            <p class="text-xs text-surface-500 mb-2">${{ number_format($item->unit_price, 0) }} × {{ $item->quantity }}</p>

                            @if($item->selected_options && count($item->selected_options) > 0)
                            @php
                                $selectedOptionValues = \App\Models\ProductOptionValue::with('optionGroup')->whereIn('id', $item->selected_options)->get();
                            @endphp
                            <div class="mt-2 text-xs space-y-1">
                                @foreach($selectedOptionValues as $optVal)
                                    <p class="text-surface-600">
                                        <span class="font-medium text-surface-700">{{ $optVal->optionGroup->name }}:</span> 
                                        {{ $optVal->label }}
                                    </p>
                                @endforeach
                            </div>
                            @endif

                            @if($item->customization_data)
                            <div class="text-xs text-surface-500 mt-2 space-y-1">
                                @foreach((array)$item->customization_data as $key => $val)
                                    @if(!in_array($key, ['preview_url', 'design_id']))
                                        <p><span class="font-medium text-surface-700 capitalize">{{ str_replace('_', ' ', $key) }}:</span> {{ is_string($val) ? $val : json_encode($val) }}</p>
                                    @endif
                                @endforeach
                            </div>
                            
                            @if(!empty($item->customization_data['preview_url']))
                                <div class="mt-3">
                                    <a href="{{ $item->customization_data['preview_url'] }}" download="my-design-{{ $item->order_id }}-{{ $item->id }}.png" target="_blank"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-brand-50 border border-brand-200 text-brand-700 text-xs font-semibold rounded-lg hover:bg-brand-100 transition">
                                        <i data-lucide="download" class="w-3 h-3"></i> Download Design
                                    </a>
                                </div>
                            @endif
                            @endif

                            <div class="flex justify-between items-end mt-3 border-t border-surface-200 pt-3">
                                <span class="text-sm text-surface-500 font-medium">Qty: {{ $item->quantity }}</span>
                                <span class="font-bold text-lg text-surface-800">Total: ${{ number_format($item->total_price, 0) }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-surface-500 bg-surface-50 rounded-xl border border-surface-100">
                        <i data-lucide="package-x" class="w-10 h-10 mx-auto mb-3 text-surface-400"></i>
                        <h4 class="font-semibold text-surface-800 mb-1">No items found</h4>
                        <p class="text-sm">The items for this order may have been removed or deleted from the store.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Summary -->
            <div class="grid sm:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h3 class="font-display font-semibold text-lg mb-4">Shipping Address</h3>
                    @if($order->shipping_address)
                    @php $addr = $order->shipping_address; @endphp
                    <p class="text-sm text-surface-600 leading-relaxed">
                        {{ $addr['full_name'] ?? '' }}<br>
                        {{ $addr['address_line_1'] ?? '' }}<br>
                        @if(!empty($addr['address_line_2'])){{ $addr['address_line_2'] }}<br>@endif
                        {{ $addr['city'] ?? '' }}, {{ $addr['state'] ?? '' }} {{ $addr['postal_code'] ?? '' }}<br>
                        Phone: {{ $addr['phone'] ?? '' }}
                    </p>
                    @endif
                </div>
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h3 class="font-display font-semibold text-lg mb-4">Payment Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-surface-500">Subtotal</span><span>${{ number_format($order->subtotal, 0) }}</span></div>
                        @if($order->discount > 0)
                        <div class="flex justify-between text-accent-600"><span>Discount</span><span>-${{ number_format($order->discount, 0) }}</span></div>
                        @endif
                        <div class="flex justify-between"><span class="text-surface-500">Shipping</span><span>${{ number_format($order->shipping_cost, 0) }}</span></div>
                        <div class="border-t border-surface-100 pt-2 flex justify-between text-lg font-bold">
                            <span>Total</span><span class="text-brand-600">${{ number_format($order->total, 0) }}</span>
                        </div>
                        <div class="flex justify-between mt-2 items-center">
                            <span class="text-surface-500">Payment Status</span>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 text-xs font-bold rounded {{ $order->payment_status === 'paid' ? 'bg-accent-100 text-accent-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                                @if($order->payment_status !== 'paid' && !in_array($order->status, ['cancelled', 'delivered']))
                                <a href="{{ route('checkout.payment', $order) }}" class="px-3 py-1 bg-brand-600 text-white text-xs font-bold rounded hover:bg-brand-700 transition shadow-sm">
                                    Pay Now
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
