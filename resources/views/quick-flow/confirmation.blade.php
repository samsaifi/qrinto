@extends('layouts.quick-flow')

@section('title', 'Order Confirmed')
@section('header_title', 'Order Confirmed')

@push('styles')
<style>
    .confetti-bg {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);
    }
    .success-ring {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-radius: 0%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 30px -8px rgba(34, 197, 94, 0.4);
        animation: bounceIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes bounceIn {
        0% { transform: scale(0) rotate(-45deg); opacity: 0; }
        60% { transform: scale(1.15) rotate(5deg); }
        100% { transform: scale(1) rotate(0); opacity: 1; }
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
    }
    .detail-row + .detail-row {
        border-top: 1px solid #f1f5f9;
    }

</style>
@endpush

@section('content')
<div class="space-y-6 pb-36 text-center confetti-bg -mx-6 -mt-8 px-6 pt-8 min-h-screen">
    
    <!-- Success Icon -->
    <div class="flex justify-center pt-4">
        <div class="success-ring">
            <i data-lucide="check" class="w-10 h-10 text-white"></i>
        </div>
    </div>

    <!-- Heading -->
    <div class="space-y-2">
        <h1 class="text-2xl font-extrabold text-slate-900">Order Placed!</h1>
        <p class="text-slate-500 font-medium text-sm">Your custom print is being prepared</p>
    </div>

    <!-- Order Number Badge -->
    <div class="inline-flex items-center gap-2 bg-white border-2 border-slate-100 rounded-full px-5 py-2.5 shadow-sm">
        <i data-lucide="hash" class="w-4 h-4 text-brand-500"></i>
        <span class="font-black text-slate-900 text-sm">{{ $order->order_number }}</span>
    </div>

    @if($order->store)
    <!-- Store Details Badge -->
    <div class="inline-flex items-center gap-2 bg-slate-50 border-2 border-slate-200 rounded-full px-4 py-2 mt-2">
        <i data-lucide="store" class="w-4 h-4 text-slate-600"></i>
        <span class="font-bold text-slate-800 text-xs">{{ $order->store->store_name }}</span>
    </div>
    @endif


    <!-- Order Details Card -->
    <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium text-left">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Order Details</h3>
        
        <div class="detail-row">
            <span class="text-slate-500 font-medium text-sm">Product</span>
            <span class="font-bold text-slate-900 text-sm">{{ $order->items->first()?->product_name ?? 'Custom Print' }}</span>
        </div>
        <div class="detail-row">
            <span class="text-slate-500 font-medium text-sm">Quantity</span>
            <span class="font-bold text-slate-900 text-sm">{{ $order->items->first()?->quantity ?? 1 }}</span>
        </div>
        <div class="detail-row">
            <span class="text-slate-500 font-medium text-sm">Payment</span>
            @if($order->payment_status === 'paid')
                <span class="inline-flex items-center gap-1.5 font-bold text-green-700 text-sm bg-green-50 px-2.5 py-1 rounded-full">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    Paid online
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 font-bold text-amber-700 text-sm bg-amber-50 px-2.5 py-1 rounded-full">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    Pay at store
                </span>
            @endif
        </div>
        <div class="detail-row">
            <span class="text-slate-500 font-medium text-sm">Status</span>
            <span class="inline-flex items-center gap-1.5 font-bold text-brand-700 text-sm bg-brand-50 px-2.5 py-1 rounded-full">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                {{ ucfirst($order->status) }}
            </span>
        </div>

        <div class="border-t border-slate-100 mt-2 pt-3">
            <div class="flex justify-between items-center">
                <span class="text-base font-bold text-slate-900">{{ $order->payment_status === 'paid' ? 'Total Paid' : 'Total Due' }}</span>
                <span class="text-xl font-black text-brand-600">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
            </div>
        </div>
    </div>

    <!-- Order Tracker Card -->
    @include('quick-flow.partials.tracker', ['order' => $order])

    <!-- Order Details Table -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm text-left mt-4">
        <h3 class="text-lg font-extrabold text-slate-800 mb-4 italic" style="font-family: Georgia, 'Times New Roman', serif;">Order Details</h3>
        
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gradient-to-r from-sky-600 to-sky-500 text-white">
                    <th class="text-left py-2 px-3 font-bold text-xs uppercase tracking-wider rounded-l-lg">Item</th>
                    <th class="text-center py-2 px-3 font-bold text-xs uppercase tracking-wider">Qty</th>
                    <th class="text-right py-2 px-3 font-bold text-xs uppercase tracking-wider rounded-r-lg">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr class="border-b border-slate-100">
                    <td class="py-3 px-3 text-slate-700 font-medium">{{ $item->product_name ?? 'Custom Print' }}</td>
                    <td class="py-3 px-3 text-center text-sky-600 font-semibold">{{ $item->quantity }}</td>
                    <td class="py-3 px-3 text-right text-slate-700 font-medium">{{ \App\Services\CurrencyService::formatWithCurrency($item->total_price, $order->currency) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t-2 border-slate-200 mt-2 pt-3 flex justify-between items-center px-3">
            <span class="text-base font-bold text-slate-800">Total</span>
            <span class="text-xl font-black text-sky-600">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
        </div>
    </div>

    <!-- Store & Pickup Location -->
    @if($order->store)
    <div class="text-left mt-4">
        <h3 class="text-lg font-extrabold text-slate-800 mb-4 italic" style="font-family: Georgia, 'Times New Roman', serif;">Store & Pickup Location</h3>
        
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-11 h-11 bg-sky-500 rounded-xl flex items-center justify-center shadow-md">
                    <i data-lucide="map-pin" class="w-5 h-5 text-white"></i>
                </div>
                <div class="flex-1 space-y-1">
                    <h4 class="font-bold text-slate-900 text-base">{{ $order->store->store_name }}</h4>
                    <p class="text-slate-500 text-sm">{{ $order->store->full_address }}</p>
                    
                    @if($order->store->phone)
                    <p class="text-sm pt-1">
                        <span class="font-bold text-slate-700">Phone:</span> 
                        <a href="tel:{{ $order->store->phone }}" class="text-sky-600 font-semibold hover:underline">{{ $order->store->phone }}</a>
                    </p>
                    @endif
                    
                    @if($order->store->email)
                    <p class="text-sm">
                        <span class="font-bold text-slate-700">Email:</span> 
                        <a href="mailto:{{ $order->store->email }}" class="text-sky-600 font-semibold hover:underline">{{ $order->store->email }}</a>
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Thank You Footer -->
    <div class="text-left mt-6 mb-4 px-1">
        <p class="text-slate-500 text-sm">Thanks for choosing Qrinto,</p>
        <p class="text-slate-800 font-bold text-sm">The Qrinto Team</p>
    </div>

    <!-- Sticky Bottom -->
    <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-5 glass border-t border-slate-100 safe-bottom z-50 flex gap-3">
        @php
            $designUrl = null;
            $item = $order->items->first();
            $uploadedImages = $item?->uploaded_images;
            
            if ($uploadedImages && is_array($uploadedImages) && count($uploadedImages) > 0) {
                // Get the first image value (handles associative arrays)
                $firstImage = reset($uploadedImages);
                // Check if it's already a full URL or just a path
                $designUrl = str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage);
            } elseif ($item && !empty($item->customization_data['preview_url'])) {
                $designUrl = $item->customization_data['preview_url'];
            }
        @endphp

       
        <a href="{{ route('flow.track.order', $order->order_number) }}" 
           class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-extrabold py-4 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base no-underline">
            <i data-lucide="map-pin" class="w-5 h-5"></i>
            Track Order
        </a>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Clear all Qrinto-related local storage upon order confirmation, but preserve tour completion flags.
        const tourKeys = new Set(['qrinto_tour_completed_v1']);
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('qrinto_') && !tourKeys.has(key)) {
                localStorage.removeItem(key);
            }
        });
    });
</script>
@endpush
@endsection
