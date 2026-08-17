@extends('layouts.quick-flow')

@section('title', 'Checkout')
@section('header_title', 'Checkout')

@push('styles')
    <style>
        .paypal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 80;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .paypal-sheet {
            width: 100%;
            max-width: 28rem;
            background: #fff;
            border-radius: 2rem 2rem 0 0;
            padding: 1.5rem;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.35s cubic-bezier(0.32, 0.72, 0, 1);
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .paypal-sheet-handle {
            width: 36px;
            height: 4px;
            background: #cbd5e1;
            border-radius: 999px;
            margin: 0 auto 1rem;
        }

        .processing-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .processing-overlay .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #38bdf8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .success-check {
            width: 64px;
            height: 64px;
            background: #0ea5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
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
            width: var(--book-w, 160px);
            height: var(--book-h, 210px);
            transition: transform 0.5s;
            transform-style: preserve-3d;
            margin-left: calc(var(--book-w, 160px) / 2 + 50px);
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
            box-shadow: inset 3px 0 10px rgba(0, 0, 0, 0.05), 5px 5px 15px rgba(0, 0, 0, 0.15);
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
    </style>
@endpush

@section('content')
    <div x-data="cartCheckoutFlow()" class="space-y-6 pb-48 px-6 font-sans text-slate-900">
        <div class="space-y-1">
            <h1 class="text-2xl font-extrabold flex items-center gap-3">
                <a href="{{ route('flow.cart.index') }}"
                    class="w-8 h-8 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors shrink-0">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                Checkout
            </h1>
            <p class="text-slate-500 font-medium text-xs ml-11">Review your {{ $cart->item_count }}
                item{{ $cart->item_count > 1 ? 's' : '' }} and complete payment</p>
        </div>

        {{-- Cart Items Summary --}}
        <div class="bg-white border-2 border-slate-50 rounded-[2.5rem] shadow-premium overflow-hidden">
            <div class="p-5 space-y-4">
                <span class="text-[10px] font-black text-mobile-600 uppercase tracking-[0.2em]">Order Items</span>

                @foreach ($cart->items as $item)
                    @php
                        $customization = $item->customization_data ?? [];
                        $uploadIds = $customization['upload_ids'] ?? [];
                        $itemImageUrls = [];

                        $slots = ['frame_image', 'sample_image', 'background_image', 'overlay_image'];
                        $noOfPages = (int) ($item->product->no_of_pages ?? 1);
                        if ($noOfPages <= 1) {
                            $activeSlots = ['frame_image'];
                        } elseif ($noOfPages == 2) {
                            $activeSlots = ['frame_image', 'sample_image'];
                        } else {
                            $activeSlots = array_slice($slots, 0, min($noOfPages, 4));
                        }

                        foreach ($activeSlots as $index => $slotKey) {
                            $url = null;
                            if (is_array($uploadIds)) {
                                $upId = $uploadIds[$slotKey] ?? ($uploadIds[$index] ?? null);
                                if ($upId && isset($uploads[$upId]) && !empty($uploads[$upId]->url)) {
                                    $url = $uploads[$upId]->url;
                                }
                            } elseif (
                                $index === 0 &&
                                is_scalar($uploadIds) &&
                                isset($uploads[$uploadIds]) &&
                                !empty($uploads[$uploadIds]->url)
                            ) {
                                $url = $uploads[$uploadIds]->url;
                            }

                            if (!$url && $item->product) {
                                $url = $item->product->{$slotKey . '_url'} ?? null;
                            }

                            if ($url) {
                                $itemImageUrls[] = $url;
                            }
                        }

                        if (empty($itemImageUrls)) {
                            if (is_array($uploadIds)) {
                                foreach ($uploadIds as $upId) {
                                    if ($upId && isset($uploads[$upId]) && !empty($uploads[$upId]->url)) {
                                        $itemImageUrls[] = $uploads[$upId]->url;
                                    }
                                }
                            }
                            if (empty($itemImageUrls) && $item->product && $item->product->frame_image_url) {
                                $itemImageUrls[] = $item->product->frame_image_url;
                            }
                        }

                        $imageCount = count($itemImageUrls);

                        $product = $item->product;
                        $orientation = $product->pdf_orientation ?? 'portrait';
                        $isPortrait = $orientation === 'portrait';
                        $baseWidth = $isPortrait ? 160 : 210;

                        $ratio = 1;
                        if (
                            $product &&
                            $product->productType &&
                            $product->productType->width &&
                            $product->productType->height
                        ) {
                            $ratio = $product->productType->width / $product->productType->height;
                        } else {
                            $ratio = $isPortrait ? 0.75 : 1.33;
                        }
                        $baseHeight = (int) round($baseWidth / $ratio);
                    @endphp
                    <div class="flex items-center gap-3 {{ !$loop->last ? 'pb-4 border-b border-slate-50' : '' }}">
                        {{-- Fanned Card Deck Preview Stage --}}
                        <div class="fanned-card-stage relative flex items-center justify-center w-20 h-20 shrink-0 select-none py-1 px-1 cursor-pointer"
                            @click="openPreviewModal({{ json_encode([
                                'id' => $item->id,
                                'name' => $item->product->name ?? 'Custom Print',
                                'pages' => $itemImageUrls,
                                'width' => min($baseWidth, 160),
                                'height' => min($baseHeight, 210),
                            ]) }})">
                            @if ($imageCount > 0)
                                @foreach ($itemImageUrls as $idx => $imgUrl)
                                    @php
                                        $count = $imageCount;
                                        if ($count == 1) {
                                            $rot = 0;
                                            $tx = 0;
                                            $ty = 0;
                                        } elseif ($count == 2) {
                                            $rot = $idx == 0 ? -12 : 12;
                                            $tx = $idx == 0 ? -10 : 10;
                                            $ty = 2;
                                        } elseif ($count == 3) {
                                            $rot = ($idx - 1) * 14;
                                            $tx = ($idx - 1) * 12;
                                            $ty = abs($idx - 1) * 2;
                                        } elseif ($count == 4) {
                                            $rots = [-16, -5, 5, 16];
                                            $txs = [-16, -5, 5, 16];
                                            $tys = [3, 1, 1, 3];
                                            $rot = $rots[$idx];
                                            $tx = $txs[$idx];
                                            $ty = $tys[$idx];
                                        } else {
                                            $step = 36 / max(1, $count - 1);
                                            $rot = -18 + $idx * $step;
                                            $tx = -18 + $idx * (36 / max(1, $count - 1));
                                            $ty = abs($idx - ($count - 1) / 2) * 2;
                                        }
                                        $zIndex = ($idx + 1) * 10;
                                    @endphp
                                    <div class="fanned-card absolute top-1/2 left-1/2 rounded-lg overflow-hidden bg-white border border-white shadow-sm transition-all duration-300 hover:!z-50 hover:!scale-115 hover:!rotate-0"
                                        style="width: {{ $count > 1 ? '44px' : '56px' }}; height: {{ $count > 1 ? '58px' : '64px' }}; margin-left: -{{ $count > 1 ? '22px' : '28px' }}; margin-top: -{{ $count > 1 ? '29px' : '32px' }}; transform: translate({{ $tx }}px, {{ $ty }}px) rotate({{ $rot }}deg); transform-origin: 50% 120%; z-index: {{ $zIndex }}; box-shadow: 0 4px 10px -2px rgba(0,0,0,0.18);"
                                        title="Page {{ $idx + 1 }}">
                                        <img src="{{ $imgUrl }}" alt="Page {{ $idx + 1 }}"
                                            class="w-full h-full object-cover">
                                        @if ($count > 1)
                                            <div
                                                class="absolute bottom-0.5 right-0.5 bg-slate-900/85 text-white text-[6px] font-black px-0.5 rounded">
                                                P{{ $idx + 1 }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                @if ($count > 1)
                                    <div
                                        class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-slate-900/90 text-white text-[7px] font-black px-1.5 py-0.5 rounded-full shadow-md backdrop-blur-xs whitespace-nowrap z-40 flex items-center gap-0.5 border border-slate-700/80">
                                        <i data-lucide="layers" class="w-2 h-2 text-mobile-400"></i>
                                        {{ $count }} Pages
                                    </div>
                                @endif
                            @else
                                <div
                                    class="w-14 h-14 rounded-xl overflow-hidden bg-slate-50 border border-slate-200/80 shrink-0 relative flex flex-col items-center justify-center text-slate-300 shadow-2xs">
                                    <i data-lucide="image" class="w-5 h-5"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @if (!empty($customization['type_name']))
                                <span
                                    class="text-[9px] font-black text-mobile-600 uppercase tracking-[0.15em] block leading-tight">{{ $customization['type_name'] }}</span>
                            @endif
                            <h4 class="font-extrabold text-xs text-slate-900 truncate">
                                {{ $item->product->name ?? 'Custom Print' }}</h4>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                Qty: {{ $item->quantity }}
                                @if (!empty($customization['size_name']))
                                    · {{ $customization['size_name'] }}
                                    @if (!empty($customization['size_width']) && !empty($customization['size_height']))
                                        ·
                                        {{ $customization['size_width'] }}×{{ $customization['size_height'] }}{{ $customization['size_unit'] ?? '' }}
                                    @endif
                                @endif
                            </p>

                            {{-- Preview Design Button --}}
                            <button type="button"
                                @click="openPreviewModal({{ json_encode([
                                    'id' => $item->id,
                                    'name' => $item->product->name ?? 'Custom Print',
                                    'pages' => $itemImageUrls,
                                    'width' => min($baseWidth, 160),
                                    'height' => min($baseHeight, 210),
                                ]) }})"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-mobile-50 hover:bg-mobile-500 text-mobile-700 hover:text-white border border-mobile-200/80 text-[10px] font-extrabold transition-all active:scale-95 cursor-pointer mt-1 shadow-2xs"
                                title="Preview Design">
                                <i data-lucide="eye" class="w-3 h-3"></i>
                                <span>Preview</span>
                            </button>
                        </div>
                        <span class="font-extrabold text-sm text-slate-900 flex-shrink-0"
                            x-text="__price({{ $item->unit_price }} * {{ $item->quantity }})"></span>
                    </div>
                @endforeach
            </div>

            {{-- Coupon --}}
            <div class="p-5 bg-white border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Promo Code</span>
                    <template x-if="appliedCoupon">
                        <button @click="removeCoupon()"
                            class="text-[10px] font-black text-red-500 uppercase">Remove</button>
                    </template>
                </div>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <i data-lucide="ticket" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" x-model="couponInput" :disabled="appliedCoupon" placeholder="Enter code"
                            class="w-full bg-slate-50 border-2 border-transparent focus:border-mobile-500 rounded-xl py-2.5 pl-10 pr-3 text-sm font-bold uppercase transition-all outline-none"
                            @keydown.enter.prevent="applyCoupon()">
                    </div>
                    <button @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                        class="px-4 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-mobile-600 disabled:opacity-50 transition-all active:scale-95">
                        Apply
                    </button>
                </div>
                <p x-show="couponMessage" x-text="couponMessage" :class="appliedCoupon ? ' text-gray-600' : 'text-red-500'"
                    class="text-[10px] font-bold mt-2 ml-1" style="display:none"></p>
            </div>

            {{-- Pricing --}}
            <div class="p-5 space-y-3 border-t border-slate-100">
                <div class="flex justify-between items-center text-slate-400 text-xs font-bold uppercase tracking-[0.2em]">
                    <span>Subtotal</span>
                    <span x-text="__price(subtotal)"></span>
                </div>
                <template x-if="discountAmount > 0">
                    <div
                        class="flex justify-between items-center  text-gray-600 text-xs font-bold uppercase tracking-[0.2em]">
                        <span x-text="'Discount (' + appliedCoupon + ')'"></span>
                        <span x-text="'-' + __price(discountAmount)"></span>
                    </div>
                </template>
                <div class="flex justify-between items-center border-t border-slate-100 pt-4">
                    <span class="text-lg font-black text-slate-900">Total Amount</span>
                    <span class="text-2xl font-black text-mobile-600" x-text="__price(calculateTotal())"></span>
                </div>
            </div>
        </div>

        {{-- Pickup Info --}}
        <div class="space-y-3">
            <h3 class="text-base font-extrabold">Pickup Information</h3>
            <div class="space-y-3">
                <div class="relative group">
                    <div
                        class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-mobile-500 transition-colors">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <input type="text" x-model="pickupName"
                        class="w-full bg-white border-2 border-slate-100 rounded-2xl py-4 pl-13 pr-5 font-bold text-slate-700 focus:border-mobile-500 focus:ring-0 transition-all outline-none shadow-sm"
                        placeholder="Pickup Name" style="padding-left: 3rem;">
                </div>
                <div class="relative group">
                    <div
                        class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-mobile-500 transition-colors">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </div>
                    <input type="email" x-model="pickupEmail"
                        class="w-full bg-white border-2 border-slate-100 rounded-2xl py-4 pl-13 pr-5 font-bold text-slate-700 focus:border-mobile-500 focus:ring-0 transition-all outline-none shadow-sm"
                        placeholder="Email Address" style="padding-left: 3rem;">
                </div>
                <div class="relative group">
                    <div
                        class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-mobile-500 transition-colors">
                        <i data-lucide="phone" class="w-5 h-5"></i>
                    </div>
                    <input type="tel" x-model="contactNumber"
                        class="w-full bg-white border-2 border-slate-100 rounded-2xl py-4 pl-13 pr-5 font-bold text-slate-700 focus:border-mobile-500 focus:ring-0 transition-all outline-none shadow-sm"
                        placeholder="Contact Number" style="padding-left: 3rem;">
                </div>
            </div>
        </div>

        {{-- Special Instructions --}}
        <div class="space-y-3">
            <h3 class="text-base font-extrabold">Special Notes</h3>
            <div class="relative group">
                <div class="absolute left-5 top-4 text-slate-400 group-focus-within:text-mobile-500 transition-colors">
                    <i data-lucide="message-square-text" class="w-5 h-5"></i>
                </div>
                <textarea x-model="specialInstructions" rows="3"
                    class="w-full bg-white border-2 border-slate-100 rounded-2xl py-4 pl-13 pr-5 font-bold text-slate-700 focus:border-mobile-500 focus:ring-0 transition-all outline-none shadow-sm resize-none"
                    placeholder="Rush requests, return address info, or other notes…" style="padding-left: 3rem;"></textarea>
            </div>
            <p class="text-xs text-slate-400 font-medium ml-1">Optional — add any special requests or notes for your order
            </p>
        </div>

        {{-- Secure Payment Notice --}}
        <div class="bg-mobile-50 border-2 border-mobile-100 p-4 rounded-2xl flex items-start gap-3">
            <div class="w-9 h-9 bg-mobile-100 rounded-lg flex items-center justify-center text-mobile-600 flex-shrink-0">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h4 class="font-bold text-mobile-800 text-sm">Secure Payment via PayPal</h4>
                <p class="text-mobile-600 text-xs font-medium">Pay safely with PayPal, cards, or your PayPal balance.</p>
            </div>
        </div>

        {{-- PayPal Modal --}}
        <template x-teleport="body">
            <div x-cloak>
                <div x-show="showPaypal" class="paypal-overlay" @click.self="showPaypal = false">
                    <div class="paypal-sheet" @click.stop>
                        <div class="paypal-sheet-handle"></div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-extrabold text-slate-900">Pay with PayPal</h3>
                            <button @click="showPaypal = false"
                                class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-4 mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase">Amount to Pay</p>
                                <p class="text-2xl font-black text-slate-900" x-text="__price(calculateTotal())"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-400 uppercase">Pickup</p>
                                <p class="text-sm font-bold text-slate-700" x-text="pickupName"></p>
                            </div>
                        </div>
                        <div id="paypal-button-container" class="mb-2"></div>
                        <p class="text-center text-xs text-slate-400 font-medium mt-3">
                            <i data-lucide="lock" class="w-3 h-3 inline-block mr-1"></i>
                            Payments are processed securely by PayPal
                        </p>
                    </div>
                </div>

                <div x-show="isProcessing" class="processing-overlay" x-cloak>
                    <template x-if="!paymentSuccess">
                        <div class="text-center">
                            <div class="spinner mx-auto mb-4"></div>
                            <h3 class="text-xl font-extrabold text-slate-900">Processing Payment</h3>
                            <p class="text-slate-500 font-medium text-sm">Please wait while we confirm your order…</p>
                        </div>
                    </template>
                    <template x-if="paymentSuccess">
                        <div class="text-center">
                            <div class="success-check mx-auto mb-4">
                                <i data-lucide="check" class="w-8 h-8 text-white"></i>
                            </div>
                            <h3 class="text-xl font-extrabold text-slate-900">Payment Successful!</h3>
                            <p class="text-slate-500 font-medium text-sm">Redirecting to your order…</p>
                        </div>
                    </template>
                </div>
                {{-- Design Preview Flipbook Modal --}}
                <div x-show="previewModalOpen" x-cloak
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                    <div class="bg-white border border-slate-200/90 rounded-3xl shadow-2xl max-w-lg w-full p-4 sm:p-6 relative overflow-hidden transform transition-all max-h-[90vh] overflow-y-auto"
                        @click.away="previewModalOpen = false">

                        {{-- Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 mb-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-xl bg-mobile-50 border border-mobile-100 text-mobile-600 flex items-center justify-center">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 leading-tight"
                                        x-text="previewItem?.name || 'Order Design Preview'"></h3>
                                    <span class="text-[10px] font-bold text-slate-500"
                                        x-text="previewItem?.pages ? previewItem.pages.length + ' Design Pages' : ''"></span>
                                </div>
                            </div>

                            <button type="button" @click="previewModalOpen = false"
                                class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-all cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        {{-- Body: Flip Card Book --}}
                        <template x-if="previewItem && previewItem.pages && previewItem.pages.length >= 2">
                            <div>
                                <div class="book-container py-2 overflow-x-auto">
                                    <div class="book"
                                        :style="`--book-w: ${previewItem.width}px; --book-h: ${previewItem.height}px;`">
                                        <template x-for="(pair, pIdx) in getPagePairs(previewItem.pages)"
                                            :key="pIdx">
                                            <div class="page" :style="`--i: ${pIdx}`"
                                                @click="$el.classList.toggle('flipped')">
                                                <div class="page-face front">
                                                    <img :src="pair.front" :alt="`Page ${pIdx * 2 + 1}`">
                                                </div>
                                                <div class="page-face back">
                                                    <template x-if="pair.back">
                                                        <img :src="pair.back" :alt="`Page ${pIdx * 2 + 2}`">
                                                    </template>
                                                    <template x-if="!pair.back">
                                                        <div
                                                            class="w-full h-full bg-slate-50 flex items-center justify-center">
                                                            <span class="text-slate-300 font-bold text-xs">Back Page</span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <p
                                    class="text-center text-[10px] text-slate-600 font-bold flex items-center justify-center gap-1 mt-3 bg-slate-100/80 py-1.5 px-3 rounded-xl border border-slate-200/60">
                                    <i data-lucide="mouse-pointer-2" class="w-3 h-3 text-mobile-600"></i>
                                    Tap pages above to flip through design pages
                                </p>
                            </div>
                        </template>

                        {{-- Body: Single Page Card --}}
                        <template x-if="previewItem && previewItem.pages && previewItem.pages.length === 1">
                            <div class="flex flex-col items-center justify-center py-3">
                                <div class="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-xl p-1"
                                    :style="`max-width: ${previewItem.width * 1.2}px; max-height: ${previewItem.height * 1.2}px;`">
                                    <img :src="previewItem.pages[0]" :alt="previewItem.name"
                                        class="w-full h-full object-cover rounded-xl">
                                </div>
                                <p class="text-center text-[10px] text-slate-500 font-bold mt-2">
                                    Single Page Custom Print Design
                                </p>
                            </div>
                        </template>

                        <template x-if="!previewItem || !previewItem.pages || previewItem.pages.length === 0">
                            <div class="py-6 text-center text-slate-400 font-medium text-xs">
                                No preview available for this design item.
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </template>

        {{-- Sticky Bottom --}}
        <div
            class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 glass border-t border-slate-100 safe-bottom z-50 space-y-3">
            <div class="flex items-center gap-3 px-1 mb-1">
                <input type="checkbox" x-model="acceptedTerms" id="terms-checkbox"
                    class="w-5 h-5 rounded border-slate-300 text-mobile-600 focus:ring-mobile-500 cursor-pointer">
                <label for="terms-checkbox"
                    class="text-[11px] font-bold text-slate-600 cursor-pointer select-none leading-tight">
                    I have read and accept the <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}"
                        target="_blank" class="text-mobile-600 underline">Terms and Conditions</a>
                </label>
            </div>
            <button type="button" @click="openPaypal()"
                :disabled="!pickupName || !contactNumber || !pickupEmail || !acceptedTerms"
                class="w-full bg-mobile-500 disabled:bg-slate-300 hover:bg-mobile-600 text-white font-extrabold py-3.5 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span
                    x-text="pickupName && contactNumber && pickupEmail && acceptedTerms ? 'Pay Now — ' + __price(calculateTotal()) : (acceptedTerms ? 'Complete All Info' : 'Accept Terms to Continue')"></span>
            </button>
            <button type="button" @click="payByCash()"
                :disabled="!pickupName || !contactNumber || !pickupEmail || !acceptedTerms"
                class="w-full bg-white disabled:bg-slate-50 disabled:text-slate-400 border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-extrabold py-3.5 rounded-2xl shadow-sm transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base">
                <i data-lucide="banknote" class="w-5 h-5"></i>
                <span>Pay by Cash at Counter</span>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ \App\Services\CurrencyService::getCode() }}&intent=capture">
    </script>
    <script>
        function cartCheckoutFlow() {
            return {
                subtotal: {{ $cart->subtotal }},
                pickupName: '',
                pickupEmail: '',
                contactNumber: '',
                showPaypal: false,
                isProcessing: false,
                paymentSuccess: false,
                paypalRendered: false,
                acceptedTerms: false,
                specialInstructions: '',

                couponInput: '{{ $cart->coupon->code ?? '' }}',
                appliedCoupon: {!! $cart->coupon ? "'" . $cart->coupon->code . "'" : 'null' !!},
                discountAmount: {{ $cart->discount }},
                couponMessage: '',
                previewModalOpen: false,
                previewItem: null,

                openPreviewModal(item) {
                    this.previewItem = item;
                    this.previewModalOpen = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                getPagePairs(pages) {
                    if (!pages || !pages.length) return [];
                    const pairs = [];
                    for (let i = 0; i < pages.length; i += 2) {
                        pairs.push({
                            front: pages[i],
                            back: pages[i + 1] || null
                        });
                    }
                    return pairs;
                },

                calculateTotal() {
                    return Math.max(0, this.subtotal - this.discountAmount).toFixed(2);
                },

                async applyCoupon() {
                    if (!this.couponInput || this.appliedCoupon) return;
                    try {
                        const res = await fetch('{{ route('flow.cart.apply-coupon') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                code: this.couponInput
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.appliedCoupon = this.couponInput;
                            this.discountAmount = parseFloat(data.discount);
                            this.couponMessage = data.message;
                        } else {
                            this.couponMessage = data.message;
                        }
                    } catch (e) {
                        this.couponMessage = 'Error applying coupon.';
                    }
                },

                removeCoupon() {
                    this.appliedCoupon = null;
                    this.discountAmount = 0;
                    this.couponInput = '';
                    this.couponMessage = '';
                },

                openPaypal() {
                    if (!this.pickupName || !this.contactNumber || !this.pickupEmail) return;
                    this.showPaypal = true;
                    this.$nextTick(() => {
                        if (!this.paypalRendered) {
                            this.renderPaypalButtons();
                            this.paypalRendered = true;
                        }
                        setTimeout(() => lucide.createIcons(), 200);
                    });
                },

                payByCash() {
                    if (!this.pickupName || !this.contactNumber || !this.pickupEmail) return;
                    this.isProcessing = true;

                    fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow-pc.cart-checkout.cash' : 'flow.cart-checkout.cash'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                pickup_name: this.pickupName,
                                pickup_email: this.pickupEmail,
                                contact_number: this.contactNumber,
                                coupon_code: this.appliedCoupon,
                                special_instructions: this.specialInstructions,
                            }),
                        })
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                this.paymentSuccess = true;
                                setTimeout(() => lucide.createIcons(), 100);
                                setTimeout(() => {
                                    window.location.href = result.redirect_url;
                                }, 1800);
                            } else {
                                this.isProcessing = false;
                                alert(result.error || result.message || 'Failed to process order.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            this.isProcessing = false;
                            alert('An error occurred.');
                        });
                },

                renderPaypalButtons() {
                    const self = this;
                    paypal.Buttons({
                        style: {
                            layout: 'vertical',
                            color: 'gold',
                            shape: 'rect',
                            label: 'paypal',
                            height: 48
                        },

                        createOrder(data, actions) {
                            return fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow-pc.cart-checkout.paypal.create' : 'flow.cart-checkout.paypal.create'); ?>', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        pickup_name: self.pickupName,
                                        pickup_email: self.pickupEmail,
                                        contact_number: self.contactNumber,
                                        coupon_code: self.appliedCoupon,
                                        special_instructions: self.specialInstructions,
                                    }),
                                })
                                .then(res => res.json())
                                .then(order => {
                                    if (order.error) {
                                        alert(order.error);
                                        throw new Error(order.error);
                                    }
                                    return order.id;
                                });
                        },

                        onApprove(data, actions) {
                            self.showPaypal = false;
                            self.isProcessing = true;

                            return fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow-pc.cart-checkout.paypal.capture' : 'flow.cart-checkout.paypal.capture'); ?>', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        paypal_order_id: data.orderID,
                                        pickup_name: self.pickupName,
                                        pickup_email: self.pickupEmail,
                                        contact_number: self.contactNumber,
                                        coupon_code: self.appliedCoupon,
                                        special_instructions: self.specialInstructions,
                                    }),
                                })
                                .then(res => res.json())
                                .then(result => {
                                    if (result.success) {
                                        self.paymentSuccess = true;
                                        setTimeout(() => lucide.createIcons(), 100);
                                        setTimeout(() => {
                                            window.location.href = result.redirect_url;
                                        }, 1800);
                                    } else {
                                        self.isProcessing = false;
                                        alert(result.error || 'Payment failed.');
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    self.isProcessing = false;
                                    alert('An error occurred.');
                                });
                        },

                        onCancel() {},
                        onError(err) {
                            console.error('PayPal Error:', err);
                            alert('PayPal encountered an error.');
                        }
                    }).render('#paypal-button-container');
                }
            }
        }
    </script>
@endpush
