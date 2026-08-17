@extends('layouts.quick-flow-pc')

@section('title', 'Order Confirmed | Qrinto Custom Print Studio')
@section('header_title', 'Order Confirmed')
@section('meta_robots', 'noindex, nofollow')

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
            background: linear-gradient(135deg, #0ea5e9, #16a34a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 35px -8px rgba(34, 197, 94, 0.45);
            animation: bounceIn 0.65s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0) rotate(-45deg);
                opacity: 0;
            }

            60% {
                transform: scale(1.15) rotate(5deg);
            }

            100% {
                transform: scale(1) rotate(0);
                opacity: 1;
            }
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: transform 0.8s cubic-bezier(0.645, 0.045, 0.355, 1);
            transform-style: preserve-3d;
            transform-origin: left;
            cursor: pointer;
        }

        .page-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 4px 12px 12px 4px;
            box-shadow: inset 3px 0 10px rgba(0, 0, 0, 0.05), 5px 5px 15px rgba(0, 0, 0, 0.1);
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
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #fff;
        }

        .page.flipped {
            transform: rotateY(-180deg);
        }

        .page {
            z-index: calc(10 - var(--i));
        }

        .page.flipped {
            z-index: calc(10 + var(--i));
        }

        .fade-up {
            animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')
    @php
        $itemPreviews = [];
        $slots = ['frame_image', 'sample_image', 'background_image', 'overlay_image'];

        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;
            $orientation = $product->pdf_orientation ?? 'portrait';
            $isPortrait = $orientation === 'portrait';
            $baseWidth = $isPortrait ? 160 : 240;

            $ratio = 1;
            if ($product && $product->productType && $product->productType->width && $product->productType->height) {
                $ratio = $product->productType->width / $product->productType->height;
            } else {
                $ratio = $isPortrait ? 0.75 : 1.33;
            }
            $baseHeight = $baseWidth / $ratio;

            $uploadedImages = $orderItem->uploaded_images ?? [];
            $itemPages = [];

            $noOfPages = (int) ($product->no_of_pages ?? 1);
            if ($noOfPages <= 1) {
                $activeSlots = ['frame_image'];
            } elseif ($noOfPages == 2) {
                $activeSlots = ['frame_image', 'sample_image'];
            } else {
                $activeSlots = array_slice($slots, 0, min($noOfPages, 4));
            }

            foreach ($activeSlots as $idx => $slotKey) {
                $path = $uploadedImages[$slotKey] ?? null;
                $url = null;
                if ($path) {
                    $url = str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
                } elseif ($product) {
                    $url = $product->{$slotKey . '_url'} ?? null;
                }
                if ($url) {
                    $itemPages[] = $url;
                }
            }

            if (empty($itemPages)) {
                if ($uploadedImages && is_array($uploadedImages)) {
                    foreach ($uploadedImages as $p) {
                        if ($p) {
                            $itemPages[] = str_starts_with($p, 'http') ? $p : asset('storage/' . $p);
                        }
                    }
                }
                if (empty($itemPages) && $product && $product->frame_image_url) {
                    $itemPages[] = $product->frame_image_url;
                }
            }

            $itemPreviews[] = [
                'id' => $orderItem->id,
                'name' => $orderItem->product_name ?? ($product->name ?? 'Custom Print'),
                'quantity' => $orderItem->quantity,
                'pages' => $itemPages,
                'base_width' => $baseWidth,
                'base_height' => $baseHeight,
            ];
        }

        $firstItemId = $itemPreviews[0]['id'] ?? 0;
    @endphp

    <div class="ambient-bg min-h-screen pt-5 pb-6 font-sans text-slate-900">
        <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Sleek Compact Top Header Bar --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 bg-white/90 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-3">
                    <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <a href="{{ route('flow-pc.index') }}"
                            class="text-slate-500 hover:text-brand-600 transition-colors flex items-center gap-1">
                            <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Home</span>
                        </a>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                    </nav>
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        Order Confirmation
                    </h1>
                    <span
                        class="text-xs font-extrabold  text-gray-700  bg-gray-50 px-2.5 py-0.5 rounded-full border  border-gray-200/60 flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5  text-gray-500"></i> Confirmed!
                    </span>
                </div>

                {{-- Step Indicator --}}
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 text-[11px] font-bold">
                        <span class=" text-gray-600 flex items-center gap-1"><i data-lucide="check-circle"
                                class="w-3 h-3"></i> 1. Customize</span>
                        <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                        <span class=" text-gray-600 flex items-center gap-1"><i data-lucide="check-circle"
                                class="w-3 h-3"></i> 2. Cart</span>
                        <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                        <span class=" text-gray-700  bg-gray-50 px-2.5 py-0.5 rounded-full border  border-gray-200/80">3.
                            Confirmed!</span>
                    </div>
                    <div class="h-4 w-px bg-slate-200 hidden md:block"></div>
                    <a href="{{ route('flow-pc.index') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-2xs active:scale-95">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> New Order
                    </a>
                </div>
            </div>

            {{-- Celebratory Light Hero Banner --}}
            <div
                class=" bg-gray-50/90 border  border-gray-200/90 text-slate-900 rounded-2xl p-4 sm:p-5 mb-4 shadow-2xs relative overflow-hidden">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div
                            class="w-11 h-11  bg-gray-500 text-white rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                            <i data-lucide="check" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                                Order Placed Successfully!
                            </h1>
                            <p class="text-xs text-slate-600 font-medium">
                                Your custom print is queued for high-resolution processing. Updates sent to your email.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <span
                            class="inline-flex items-center gap-1.5 bg-white border  border-gray-200 rounded-xl px-3 py-1.5 text-slate-900 text-xs font-black shadow-2xs">
                            <i data-lucide="hash" class="w-3.5 h-3.5  text-gray-600"></i>
                            <span>{{ $order->order_number }}</span>
                        </span>
                        @if ($order->store)
                            <span
                                class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700 text-xs font-bold shadow-2xs">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-brand-600"></i>
                                <span>{{ $order->store->store_name }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 2-Column Desktop Content Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                {{-- LEFT COLUMN: Design Preview & Order Progress --}}
                <div class="lg:col-span-7 space-y-4">

                    {{-- Multi-Item Interactive Design Preview Section --}}
                    @if (count($itemPreviews) > 0)
                        <div class="glass-card border border-slate-200/90 rounded-2xl p-4 shadow-2xs"
                            x-data="{ activePreviewId: {{ $firstItemId }} }">

                            {{-- Header & Item Switcher Tabs --}}
                            <div class="pb-3 border-b border-slate-200/80 mb-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-lg bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <h2 class="text-sm font-black text-slate-900 tracking-tight">Order Design Previews
                                        </h2>
                                    </div>
                                    <span
                                        class="text-[11px] font-extrabold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200/60">
                                        {{ count($itemPreviews) }} Item{{ count($itemPreviews) > 1 ? 's' : '' }}
                                    </span>
                                </div>

                                {{-- Tab Buttons --}}
                                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                                    @foreach ($itemPreviews as $pIdx => $preview)
                                        <button type="button" @click="activePreviewId = {{ $preview['id'] }}"
                                            :class="activePreviewId === {{ $preview['id'] }} ?
                                                'bg-brand-500 text-white border-brand-500 shadow-2xs' :
                                                'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'"
                                            class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                            <span
                                                class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-black"
                                                :class="activePreviewId === {{ $preview['id'] }} ? 'bg-white/20 text-white' :
                                                    'bg-slate-100 text-slate-600'">
                                                {{ $pIdx + 1 }}
                                            </span>
                                            <span class="truncate max-w-[150px]">{{ $preview['name'] }}</span>
                                            <span class="text-[10px] opacity-80">({{ count($preview['pages']) }}P)</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Item Preview Canvas Containers --}}
                            @foreach ($itemPreviews as $preview)
                                <div x-show="activePreviewId === {{ $preview['id'] }}" x-transition>
                                    @if (count($preview['pages']) >= 2)
                                        {{-- 3D Interactive Design Flipbook --}}
                                        <div class="book-container py-2">
                                            <div class="book"
                                                style="--book-w: {{ min($preview['base_width'], 160) }}px; --book-h: {{ min($preview['base_height'], 210) }}px;">
                                                @for ($i = 0; $i < count($preview['pages']); $i += 2)
                                                    <div class="page" style="--i: {{ $i / 2 }}"
                                                        onclick="this.classList.toggle('flipped')">
                                                        <div class="page-face front">
                                                            <img src="{{ $preview['pages'][$i] }}"
                                                                alt="Page {{ $i + 1 }}">
                                                        </div>
                                                        <div class="page-face back">
                                                            @if (isset($preview['pages'][$i + 1]))
                                                                <img src="{{ $preview['pages'][$i + 1] }}"
                                                                    alt="Page {{ $i + 2 }}">
                                                            @else
                                                                <div
                                                                    class="w-full h-full bg-slate-50 flex items-center justify-center">
                                                                    <span class="text-slate-300 font-bold text-xs">Back
                                                                        Page</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        <p
                                            class="text-center text-[11px] text-slate-500 font-bold flex items-center justify-center gap-1.5 mt-2 bg-slate-100/70 py-1.5 rounded-xl border border-slate-200/60">
                                            <i data-lucide="mouse-pointer-2" class="w-3.5 h-3.5 text-brand-600"></i>
                                            Click pages above to flip through {{ $preview['name'] }}
                                        </p>
                                    @elseif(count($preview['pages']) === 1)
                                        {{-- Single Page Card Preview --}}
                                        <div class="flex flex-col items-center justify-center py-2">
                                            <div class="rounded-xl overflow-hidden bg-white border border-slate-200 shadow-md p-1"
                                                style="max-width: {{ min($preview['base_width'], 180) }}px; max-height: {{ min($preview['base_height'], 240) }}px;">
                                                <img src="{{ $preview['pages'][0] }}" alt="{{ $preview['name'] }}"
                                                    class="w-full h-full object-cover rounded-lg">
                                            </div>
                                            <p class="text-center text-[11px] text-slate-500 font-bold mt-2">
                                                Single Page Custom Design
                                            </p>
                                        </div>
                                    @else
                                        <div class="py-6 text-center text-slate-400 font-medium text-xs">
                                            No preview available for this design item.
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    @endif

                    {{-- Order Progress Tracker Card --}}
                    <div class="glass-card border border-slate-200/90 rounded-2xl p-4 shadow-2xs">
                        <div class="flex items-center gap-2 mb-4 pb-2.5 border-b border-slate-200/80">
                            <div
                                class="w-7 h-7 rounded-lg  bg-gray-50 border  border-gray-100  text-gray-600 flex items-center justify-center">
                                <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                            </div>
                            <h2 class="text-sm font-black text-slate-900 tracking-tight">Live Order Fulfillment Progress
                            </h2>
                        </div>

                        @include('quick-flow-pc.partials.tracker', ['order' => $order])
                    </div>

                    {{-- What's Next Timeline Steps --}}
                    <div class="glass-card border border-slate-200/90 rounded-2xl p-4 shadow-2xs">
                        <div class="flex items-center gap-2 mb-3 pb-2.5 border-b border-slate-200/80">
                            <div
                                class="w-7 h-7 rounded-lg bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                                <i data-lucide="list-checks" class="w-3.5 h-3.5"></i>
                            </div>
                            <h2 class="text-sm font-black text-slate-900 tracking-tight">What Happens Next?</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                                <div
                                    class="w-7 h-7 bg-brand-50 border border-brand-100 rounded-lg flex items-center justify-center text-brand-600 mb-2">
                                    <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-xs font-extrabold text-slate-900">1. Instant Updates</h4>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Notifications via email & SMS.</p>
                            </div>

                            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                                <div
                                    class="w-7 h-7  bg-gray-50 border  border-gray-100 rounded-lg flex items-center justify-center  text-gray-600 mb-2">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-xs font-extrabold text-slate-900">2. Priority Printing</h4>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">High-res print processing.</p>
                            </div>

                            <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                                <div
                                    class="w-7 h-7 bg-amber-50 border border-amber-100 rounded-lg flex items-center justify-center text-amber-600 mb-2">
                                    <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                                </div>
                                <h4 class="text-xs font-extrabold text-slate-900">3. Store Pickup</h4>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Collect at store location.</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: Order Summary & Actions --}}
                <div class="lg:col-span-5 sticky top-20">
                    <div
                        class="glass-card border border-slate-200/90 rounded-2xl p-4 lg:p-5 shadow-lg shadow-slate-200/40 relative overflow-hidden space-y-4">

                        {{-- Top Accent Line --}}
                        <div class="absolute top-0 left-0 right-0 h-1  bg-gray-500"></div>

                        {{-- Summary Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Order Details</h3>
                            <span
                                class="inline-flex items-center gap-1 text-[10px] font-black  text-gray-700  bg-gray-50 px-2 py-0.5 rounded-full border  border-gray-200/60">
                                <i data-lucide="shield-check" class="w-3 h-3  text-gray-500"></i> Confirmed
                            </span>
                        </div>

                        {{-- Specs List --}}
                        <div class="divide-y divide-slate-100 text-xs">
                            <div class="py-2.5 flex justify-between items-center">
                                <span class="text-slate-500 font-semibold">Product Name</span>
                                <span
                                    class="font-extrabold text-slate-900 text-right max-w-[180px] truncate">{{ $item?->product_name ?? 'Custom Print' }}</span>
                            </div>
                            <div class="py-2.5 flex justify-between items-center">
                                <span class="text-slate-500 font-semibold">Total Quantity</span>
                                <span class="font-extrabold text-slate-900">{{ $item?->quantity ?? 1 }}</span>
                            </div>
                            <div class="py-2.5 flex justify-between items-center">
                                <span class="text-slate-500 font-semibold">Payment Status</span>
                                @if ($order->payment_status === 'paid')
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] font-extrabold  text-gray-700  bg-gray-50 px-2 py-0.5 rounded-full border  border-gray-200/60">
                                        <i data-lucide="check-circle-2" class="w-3 h-3  text-gray-500"></i> Paid Online
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">
                                        <i data-lucide="clock" class="w-3 h-3 text-amber-500"></i> Pay at Counter
                                    </span>
                                @endif
                            </div>
                            <div class="py-2.5 flex justify-between items-center">
                                <span class="text-slate-500 font-semibold">Fulfillment Status</span>
                                <span
                                    class="inline-flex items-center gap-1 text-[10px] font-extrabold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full border border-brand-200/60">
                                    <i data-lucide="printer" class="w-3 h-3 text-brand-500"></i>
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        {{-- Light High-Contrast Total Box --}}
                        <div class="my-3.5 bg-brand-50/90 rounded-xl p-3.5 border border-brand-200/80 shadow-2xs">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <span
                                    class="text-xs font-extrabold text-slate-700">{{ $order->payment_status === 'paid' ? 'Total Amount Paid' : 'Total Amount Due' }}</span>
                                <span
                                    class="text-2xl font-black text-slate-900 tracking-tight">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 text-right">Includes taxes & priority print
                                processing</p>
                        </div>

                        {{-- Action CTA Buttons --}}
                        <div class="space-y-2.5 pt-1">
                            <a href="{{ route('flow-pc.track.order', $order->order_number) }}"
                                class="shimmer-cta w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-3 px-5 rounded-xl shadow-md shadow-brand-500/20 transition-all duration-300 flex items-center justify-center gap-2 text-sm no-underline tracking-wide active:scale-[0.99] cursor-pointer">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                                Track Order Status
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>

                            <a href="{{ route('flow-pc.index') }}"
                                class="w-full bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-800 font-extrabold py-2.5 rounded-xl shadow-2xs transition-all active:scale-[0.98] flex items-center justify-center gap-2 text-xs no-underline cursor-pointer">
                                <i data-lucide="shopping-bag" class="w-4 h-4 text-brand-600"></i>
                                <span>Continue Shopping</span>
                            </a>
                        </div>

                        {{-- Trust Security Indicators --}}
                        <div
                            class="pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-1.5 text-[10px] font-semibold text-slate-400">
                            <div class="flex items-center gap-1">
                                <i data-lucide="check-circle-2" class="w-3 h-3  text-gray-500 shrink-0"></i>
                                <span>Print Guarantee</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="truck" class="w-3 h-3 text-brand-500 shrink-0"></i>
                                <span>Store Pickup</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="shield" class="w-3 h-3 text-brand-500 shrink-0"></i>
                                <span>SSL Security</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="headphones" class="w-3 h-3 text-brand-500 shrink-0"></i>
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
