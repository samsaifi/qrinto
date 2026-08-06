@extends('layouts.quick-flow-pc')

@section('title', 'Order Confirmed — Qrinto Print Studio')
@section('header_title', 'Order Confirmed')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .shimmer-cta {
        position: relative;
        overflow: hidden;
    }
    .shimmer-cta::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -60%;
        width: 50%;
        height: 200%;
        background: linear-gradient(60deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(25deg);
        transition: all 0.75s ease;
    }
    .shimmer-cta:hover::after {
        left: 140%;
    }

    .ambient-bg {
        background-color: #FCFBF9;
        background-image: 
            radial-gradient(at 0% 0%, rgba(214, 95, 50, 0.06) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(185, 79, 40, 0.06) 0px, transparent 50%),
            radial-gradient(circle at 50% 50%, rgba(250, 247, 244, 0.5) 0px, transparent 100%);
    }

    .success-ring {
        width: 88px;
        height: 88px;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 12px 35px -8px rgba(34, 197, 94, 0.45);
        animation: bounceIn 0.65s cubic-bezier(0.175, 0.885, 0.32, 1.275);
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
        from { opacity: 0; transform: translateY(14px); }
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

<div class="ambient-bg min-h-screen py-8 -mt-6 font-sans text-slate-900">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Top Navigation & Completed Step Indicator Bar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-900 font-bold">Order Confirmation</span>
            </nav>

            {{-- Checkout Completed 3-Step Bar --}}
            <div class="flex items-center gap-2 bg-white/80 backdrop-blur-md px-4 py-2 rounded-full border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]">✓</span>
                    <span>Customize</span>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]">✓</span>
                    <span>Review Cart</span>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <div class="flex items-center gap-1.5 text-xs font-black text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200/80">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                    <span>Confirmed!</span>
                </div>
            </div>
        </div>

        {{-- Celebratory Hero Banner --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-8 lg:p-10 mb-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-64 h-64 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-3xl mx-auto text-center relative z-10">
                <div class="flex justify-center mb-6 fade-up">
                    <div class="success-ring">
                        <i data-lucide="check" class="w-12 h-12 text-white"></i>
                    </div>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight fade-up" style="animation-delay: 0.05s">
                    Order Placed <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-200 bg-clip-text text-transparent italic">Successfully!</span>
                </h1>
                <p class="text-base sm:text-lg text-slate-300 mt-3 font-medium max-w-xl mx-auto fade-up" style="animation-delay: 0.1s">
                    Your custom print is queued for high-resolution processing. We'll send status updates directly to your email.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3 mt-8 fade-up" style="animation-delay: 0.15s">
                    <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-5 py-3 text-white shadow-lg">
                        <i data-lucide="hash" class="w-4 h-4 text-emerald-400"></i>
                        <span class="font-black text-base">{{ $order->order_number }}</span>
                    </span>
                    @if($order->store)
                    <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl px-5 py-3 text-white shadow-lg">
                        <i data-lucide="store" class="w-4 h-4 text-brand-400"></i>
                        <span class="font-bold text-sm">{{ $order->store->store_name }}</span>
                    </span>
                    @endif
                    <span class="inline-flex items-center gap-2 bg-emerald-500/20 backdrop-blur-md border border-emerald-400/40 rounded-2xl px-5 py-3 text-emerald-300 font-bold text-sm shadow-lg">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                        Verified Order
                    </span>
                </div>
            </div>
        </div>

        {{-- 2-Column Full Desktop Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- LEFT COLUMN: Design Preview & Order Progress --}}
            <div class="lg:col-span-7 space-y-6">

                @if(count($pages) >= 2)
                {{-- 3D Interactive Design Flipbook --}}
                <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-sm fade-up" style="animation-delay: 0.15s">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                                <i data-lucide="book-open" class="w-4.5 h-4.5"></i>
                            </div>
                            <h2 class="text-base font-black text-slate-900 tracking-tight">Interactive 3D Design Preview</h2>
                        </div>
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200/60">
                            {{ count($pages) }} Pages
                        </span>
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
                                        <span class="text-slate-300 font-bold text-sm">Back Page</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                    <p class="text-center text-xs text-slate-500 font-bold flex items-center justify-center gap-2 mt-4 bg-slate-100/70 py-2 rounded-xl border border-slate-200/60">
                        <i data-lucide="mouse-pointer-2" class="w-4 h-4 text-brand-600"></i>
                        Click on pages above to flip through your custom print design
                    </p>
                </div>
                @endif

                {{-- Order Progress Tracker Card --}}
                <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-sm fade-up" style="animation-delay: 0.2s">
                    <div class="flex items-center gap-2.5 mb-6 pb-4 border-b border-slate-200/80">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="activity" class="w-4.5 h-4.5"></i>
                        </div>
                        <h2 class="text-base font-black text-slate-900 tracking-tight">Live Order Fulfillment Progress</h2>
                    </div>

                    @include('quick-flow-pc.partials.tracker', ['order' => $order])
                </div>

                {{-- What's Next Timeline Steps --}}
                <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-sm fade-up" style="animation-delay: 0.25s">
                    <div class="flex items-center gap-2.5 mb-5 pb-4 border-b border-slate-200/80">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="list-checks" class="w-4.5 h-4.5"></i>
                        </div>
                        <h2 class="text-base font-black text-slate-900 tracking-tight">What Happens Next?</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                            <div class="w-9 h-9 bg-brand-50 border border-brand-100 rounded-xl flex items-center justify-center text-brand-600 mb-3">
                                <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900">1. Instant Updates</h4>
                            <p class="text-xs text-slate-500 font-medium mt-1">You'll receive order updates via email and SMS notifications.</p>
                        </div>

                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                            <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 mb-3">
                                <i data-lucide="printer" class="w-4.5 h-4.5"></i>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900">2. Priority Printing</h4>
                            <p class="text-xs text-slate-500 font-medium mt-1">Your design is sent to high-resolution print presses immediately.</p>
                        </div>

                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                            <div class="w-9 h-9 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center text-amber-600 mb-3">
                                <i data-lucide="package-check" class="w-4.5 h-4.5"></i>
                            </div>
                            <h4 class="text-sm font-extrabold text-slate-900">3. Store Pickup</h4>
                            <p class="text-xs text-slate-500 font-medium mt-1">Collect your finished prints from your chosen store location.</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Order Summary & Actions (Hero Sidebar) --}}
            <div class="lg:col-span-5 sticky top-24 space-y-6">
                <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-2xl shadow-slate-200/50 relative overflow-hidden space-y-6 fade-up" style="animation-delay: 0.15s">
                    
                    {{-- Top Multi-Color Gradient Line --}}
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-brand-500 to-indigo-600"></div>

                    {{-- Summary Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Order Details</h3>
                        <span class="inline-flex items-center gap-1 text-[11px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i> Confirmed
                        </span>
                    </div>

                    {{-- Specs List --}}
                    <div class="divide-y divide-slate-100">
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Product Name</span>
                            <span class="font-extrabold text-slate-900 text-right max-w-[200px] truncate">{{ $item?->product_name ?? 'Custom Print' }}</span>
                        </div>
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Total Quantity</span>
                            <span class="font-extrabold text-slate-900">{{ $item?->quantity ?? 1 }}</span>
                        </div>
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Payment Status</span>
                            @if($order->payment_status === 'paid')
                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200/60">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i> Paid Online
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200/60">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i> Pay at Counter
                            </span>
                            @endif
                        </div>
                        <div class="py-3 flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-semibold">Fulfillment Status</span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-brand-700 bg-brand-50 px-3 py-1 rounded-full border border-brand-200/60">
                                <i data-lucide="printer" class="w-3.5 h-3.5 text-brand-500"></i> {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Dark Luxury Total Box --}}
                    <div class="bg-slate-900 text-white rounded-2xl p-5 border border-slate-800 shadow-xl relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/20 rounded-full blur-xl pointer-events-none"></div>
                        <div class="flex justify-between items-baseline mb-1 relative z-10">
                            <span class="text-sm font-bold text-slate-300">{{ $order->payment_status === 'paid' ? 'Total Amount Paid' : 'Total Amount Due' }}</span>
                            <span class="text-3xl font-black text-white tracking-tight">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
                        </div>
                        <p class="text-[11px] font-medium text-slate-400 text-right relative z-10">Includes taxes & priority print processing</p>
                    </div>

                    {{-- Action CTA Buttons --}}
                    <div class="space-y-3 pt-1">
                        <a href="{{ route('flow-pc.track.order', $order->order_number) }}"
                            class="shimmer-cta w-full bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 hover:from-brand-600 hover:to-indigo-600 text-white font-black py-4 px-6 rounded-2xl shadow-xl shadow-slate-900/20 hover:shadow-brand-500/30 transition-all duration-300 flex items-center justify-center gap-3 text-base no-underline tracking-wide active:scale-[0.99] cursor-pointer">
                            <i data-lucide="map-pin" class="w-5 h-5"></i>
                            Track Order Status
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>

                        <a href="{{ route('flow-pc.index') }}"
                            class="w-full bg-white border-2 border-slate-200/90 hover:border-slate-300 hover:bg-slate-50 text-slate-800 font-extrabold py-3.5 rounded-2xl shadow-xs transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-sm no-underline cursor-pointer">
                            <i data-lucide="shopping-bag" class="w-5 h-5 text-brand-600"></i>
                            <span>Continue Shopping</span>
                        </a>
                    </div>

                    {{-- Trust Security Indicators --}}
                    <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-[11px] font-semibold text-slate-500">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                            <span>100% Print Guarantee</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                            <span>Store Pickup</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                            <span>SSL Security</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="headphones" class="w-3.5 h-3.5 text-purple-500 shrink-0"></i>
                            <span>Store Support</span>
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
