@extends('layouts.quick-flow-pc')

@section('title', 'Order Confirmed')
@section('header_title', 'Order Confirmed')

@push('styles')
<style>
    .confirm-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .confirm-grid { grid-template-columns: 1fr; }
    }

    .confirm-hero {
        background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 40%, #f0fdf4 100%);
    }
    .confirm-pattern {
        background-image: radial-gradient(circle at 1px 1px, rgba(99,102,241,0.04) 1px, transparent 0);
        background-size: 32px 32px;
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

    .book-container {
        perspective: 1200px;
        display: flex;
        justify-content: center;
        padding: 1.5rem 0;
        margin: 0 auto;
        width: 100%;
    }
    .book {
        position: relative;
        width: var(--book-w, 180px);
        height: var(--book-h, 240px);
        transition: transform 0.5s;
        transform-style: preserve-3d;
        margin-left: calc(var(--book-w, 180px) / 2 + 90px);
    }
    .page {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        transition: transform 0.8s cubic-bezier(0.645, 0.045, 0.355, 1);
        transform-style: preserve-3d;
        transform-origin: left;
        cursor: pointer;
    }
    .page-face {
        position: absolute;
        width: 100%; height: 100%;
        backface-visibility: hidden;
        border-radius: 4px 12px 12px 4px;
        box-shadow: inset 3px 0 10px rgba(0,0,0,0.05), 5px 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .page-face.back {
        transform: rotateY(180deg);
        border-radius: 12px 4px 4px 12px;
    }
    .page img {
        width: 100%; height: 100%;
        object-fit: cover;
        background: #fff;
    }
    .page.flipped { transform: rotateY(-180deg); }
    .page { z-index: calc(10 - var(--i)); }
    .page.flipped { z-index: calc(10 + var(--i)); }

    .fade-up {
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
@php
    $item = $order->items->first();
    $product = $item?->product;
    $orientation = $product->pdf_orientation ?? 'portrait';
    $aspectRatioStr = $product->aspect_ratio ?? ($orientation === 'portrait' ? '4/5' : '5/4');

    $isPortrait = ($orientation === 'portrait');
    $baseWidth = $isPortrait ? 200 : 280;

    $ratio = 1;
    if ($product->productType && $product->productType->width && $product->productType->height) {
        $ratio = $product->productType->width / $product->productType->height;
    } else {
        $ratio = $isPortrait ? 0.75 : 1.33;
    }
    $baseHeight = $baseWidth / $ratio;

    $uploadedImages = $item?->uploaded_images ?? [];
    $pages = [];
    $slots = ['frame_image', 'sample_image', 'background_image', 'overlay_image'];

    foreach($slots as $slot) {
        $path = $uploadedImages[$slot] ?? null;
        if ($path) {
            $pages[] = str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
        }
    }

    $designUrl = null;
    $upImages = $item?->uploaded_images;
    if ($upImages && is_array($upImages) && count($upImages) > 0) {
        $firstImage = reset($upImages);
        $designUrl = str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage);
    } elseif ($item && !empty($item->customization_data['preview_url'])) {
        $designUrl = $item->customization_data['preview_url'];
    }
@endphp

<div class="max-w-[1400px] mx-auto pb-16">

    {{-- ── Hero ── --}}
    <section class="confirm-hero confirm-pattern -mx-10 -mt-4 px-10 pt-14 pb-16 relative overflow-hidden">
        <div class="max-w-3xl mx-auto text-center">
            <div class="flex justify-center mb-6 fade-up">
                <div class="success-ring">
                    <i data-lucide="check" class="w-10 h-10 text-white"></i>
                </div>
            </div>

            <h1 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight fade-up" style="animation-delay: 0.05s">
                Order Placed Successfully!
            </h1>
            <p class="text-lg text-slate-500 mt-2 fade-up" style="animation-delay: 0.1s">
                Your custom print is being prepared. We'll notify you when it's ready.
            </p>

            <div class="flex items-center justify-center gap-3 mt-6 fade-up" style="animation-delay: 0.15s">
                <span class="inline-flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-5 py-2.5 shadow-sm">
                    <i data-lucide="hash" class="w-4 h-4 text-indigo-500"></i>
                    <span class="font-extrabold text-slate-900 text-sm">{{ $order->order_number }}</span>
                </span>
                @if($order->store)
                <span class="inline-flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-5 py-2.5 shadow-sm">
                    <i data-lucide="store" class="w-4 h-4 text-indigo-500"></i>
                    <span class="font-bold text-slate-700 text-sm">{{ $order->store->store_name }}</span>
                </span>
                @endif
            </div>
        </div>
    </section>

    {{-- ── Content Grid ── --}}
    <div class="confirm-grid max-w-[1100px] mx-auto -mt-6">

        {{-- LEFT: Design Preview --}}
        <div class="space-y-6">

            @if(count($pages) >= 2)
            {{-- 3D Flipbook --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6 fade-up" style="animation-delay: 0.15s">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Your Design</h2>
                </div>

                <div class="book-container">
                    <div class="book" id="design-book" style="--book-w: {{ $baseWidth }}px; --book-h: {{ $baseHeight }}px;">
                        @for($i = 0; $i < count($pages); $i += 2)
                        <div class="page" style="--i: {{ $i / 2 }}" onclick="this.classList.toggle('flipped')">
                            <div class="page-face front">
                                <img src="{{ $pages[$i] }}" alt="Page {{ $i + 1 }}">
                            </div>
                            <div class="page-face back">
                                @if(isset($pages[$i+1]))
                                <img src="{{ $pages[$i+1] }}" alt="Page {{ $i + 2 }}">
                                @else
                                <div class="w-full h-full bg-slate-50 flex items-center justify-center">
                                    <span class="text-slate-300 font-bold text-sm">Back</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>

                <p class="text-center text-[11px] text-slate-400 font-semibold flex items-center justify-center gap-1.5 mt-2">
                    <i data-lucide="mouse-pointer-2" class="w-3 h-3"></i>
                    Click to flip pages
                </p>
            </div>
            @endif

            {{-- Order Progress --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6 fade-up" style="animation-delay: 0.2s">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order Progress</h2>
                </div>

                @include('quick-flow-pc.partials.tracker', ['order' => $order])
            </div>
        </div>

        {{-- RIGHT: Order Summary --}}
        <div class="space-y-5">

            {{-- Order Details --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden fade-up" style="animation-delay: 0.15s">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order Details</h2>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="px-5 py-4 flex justify-between items-center">
                        <span class="text-sm text-slate-500 font-medium">Product</span>
                        <span class="text-sm font-bold text-slate-900 text-right max-w-[200px] truncate">{{ $item?->product_name ?? 'Custom Print' }}</span>
                    </div>
                    <div class="px-5 py-4 flex justify-between items-center">
                        <span class="text-sm text-slate-500 font-medium">Quantity</span>
                        <span class="text-sm font-bold text-slate-900">{{ $item?->quantity ?? 1 }}</span>
                    </div>
                    <div class="px-5 py-4 flex justify-between items-center">
                        <span class="text-sm text-slate-500 font-medium">Payment</span>
                        @if($order->payment_status === 'paid')
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            Paid online
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-100">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            Pay at store
                        </span>
                        @endif
                    </div>
                    <div class="px-5 py-4 flex justify-between items-center">
                        <span class="text-sm text-slate-500 font-medium">Status</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-slate-900">{{ $order->payment_status === 'paid' ? 'Total Paid' : 'Total Due' }}</span>
                        <span class="text-2xl font-extrabold text-indigo-600">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-3 fade-up" style="animation-delay: 0.2s">
                <a href="{{ route('flow-pc.track.order', $order->order_number) }}"
                    class="w-full bg-slate-900 hover:bg-black text-white font-extrabold py-4 rounded-2xl shadow-lg flex items-center justify-center gap-2.5 text-[15px] transition-all active:scale-[0.98] no-underline">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                    Track Order
                </a>

                <a href="{{ route('flow-pc.index') }}"
                    class="w-full bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-4 rounded-2xl flex items-center justify-center gap-2.5 text-[15px] transition-all active:scale-[0.98] no-underline">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    Continue Shopping
                </a>
            </div>

            {{-- What's Next --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 fade-up" style="animation-delay: 0.25s">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">What's Next</h2>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="bell" class="w-4 h-4 text-indigo-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Order Confirmation</p>
                            <p class="text-xs text-slate-400 mt-0.5">You'll receive updates via email and SMS.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="printer" class="w-4 h-4 text-emerald-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Print in Progress</p>
                            <p class="text-xs text-slate-400 mt-0.5">Your design is printed with premium materials.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="package-check" class="w-4 h-4 text-amber-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Ready for Pickup</p>
                            <p class="text-xs text-slate-400 mt-0.5">Collect from your selected store location.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('qrinto_')) {
                localStorage.removeItem(key);
            }
        });
    });
</script>
@endpush
