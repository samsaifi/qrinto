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
        border-radius: 50%;
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
                <span class="text-xl font-black text-brand-600">${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Order Tracker Card -->
    @include('quick-flow.partials.tracker', ['order' => $order])

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
        // Clear all Qrinto-related local storage upon order confirmation
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('qrinto_')) {
                localStorage.removeItem(key);
            }
        });
    });
</script>
@endpush
@endsection
