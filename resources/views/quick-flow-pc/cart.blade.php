@extends('layouts.quick-flow-pc')

@section('title', 'Shopping Cart — Qrinto Print Studio')

@php
    $routePrefix = $routePrefix ?? 'flow-pc.';
@endphp

@push('styles')
<style>
    .cart-item-enter { animation: slideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
    @keyframes slideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .cart-item-remove { animation: fadeOut 0.25s ease-in forwards; }
    @keyframes fadeOut { to { opacity: 0; transform: scale(0.95); height: 0; margin: 0; padding: 0; overflow: hidden; } }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.92);
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
        margin-left: calc(var(--book-w, 180px) / 2 + 70px);
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
        box-shadow: inset 3px 0 10px rgba(0,0,0,0.05), 5px 5px 15px rgba(0,0,0,0.15);
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
</style>
@endpush

@section('content')
<div x-data="cartPage()" class="ambient-bg min-h-screen pt-5 pb-6">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Sleek Compact Top Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 bg-white/90 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center gap-3">
                <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <a href="{{ route($routePrefix . 'index') }}" class="text-slate-500 hover:text-brand-600 transition-colors flex items-center gap-1">
                        <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Home</span>
                    </a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                </nav>
                <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    Shopping Cart
                </h1>
                <span class="text-xs font-extrabold text-brand-700 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200/60">
                    <span x-text="itemCount"></span> item<span x-show="itemCount !== 1">s</span>
                </span>
            </div>

            {{-- Step Indicator & Actions --}}
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-2 text-[11px] font-bold">
                    <span class="text-emerald-600 flex items-center gap-1"><i data-lucide="check-circle" class="w-3 h-3"></i> Customize</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                    <span class="text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200/60">2. Review Cart</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                    <span class="text-slate-400">3. Checkout</span>
                </div>
                <div class="h-4 w-px bg-slate-200 hidden md:block"></div>
                <a href="{{ route($routePrefix . 'index') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-2xs active:scale-95">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Add Designs
                </a>
            </div>
        </div>

        @if($cart->items->count() > 0)
            <div class="flex flex-col lg:flex-row gap-5 items-start">
                
                {{-- Left Column: Cart Items List --}}
                <div class="w-full flex-1 min-w-0 space-y-3">
                    
                    {{-- Table Header Bar (Desktop Only) --}}
                    <div class="hidden lg:grid grid-cols-[1fr_130px_110px_36px] gap-4 px-5 py-2 bg-slate-100/70 backdrop-blur-xs rounded-xl border border-slate-200/80 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <span>Product Specifications</span>
                        <span class="text-center">Quantity</span>
                        <span class="text-right">Total Price</span>
                        <span></span>
                    </div>

                    {{-- Cart Items Loop --}}
                    @foreach($cart->items as $item)
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
                                } elseif ($index === 0 && is_scalar($uploadIds) && isset($uploads[$uploadIds]) && !empty($uploads[$uploadIds]->url)) {
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
                            $isPortrait = ($orientation === 'portrait');
                            $baseWidth = $isPortrait ? 180 : 240;

                            $ratio = 1;
                            if ($product && $product->productType && $product->productType->width && $product->productType->height) {
                                $ratio = $product->productType->width / $product->productType->height;
                            } else {
                                $ratio = $isPortrait ? 0.75 : 1.33;
                            }
                            $baseHeight = (int) round($baseWidth / $ratio);
                        @endphp

                        <div class="glass-card border border-slate-200/90 rounded-2xl p-3.5 sm:p-4 shadow-2xs hover:shadow-md hover:border-brand-300 transition-all duration-300 cart-item-enter relative group overflow-hidden"
                             x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})"
                             x-show="!removed" x-transition>
                            
                            {{-- Top Accent Indicator Bar --}}
                            <div class="absolute top-0 left-0 right-0 h-0.5 bg-brand-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                            <div class="grid grid-cols-1 lg:grid-cols-[1fr_130px_110px_36px] gap-3 sm:gap-4 items-center">
                                
                                {{-- Product info & Thumbnail --}}
                                <div class="flex items-center gap-3 sm:gap-4">
                                    
                                    {{-- Thumbnail / Fanned Card Deck Preview Stage --}}
                                    <div class="fanned-card-stage relative flex items-center justify-center w-24 h-24 sm:w-28 sm:h-24 shrink-0 select-none py-1 px-1">
                                        @if($imageCount > 0)
                                            @foreach($itemImageUrls as $idx => $imgUrl)
                                                @php
                                                    $count = $imageCount;
                                                    if ($count == 1) {
                                                        $rot = 0; $tx = 0; $ty = 0;
                                                    } elseif ($count == 2) {
                                                        $rot = $idx == 0 ? -12 : 12;
                                                        $tx = $idx == 0 ? -12 : 12;
                                                        $ty = 2;
                                                    } elseif ($count == 3) {
                                                        $rot = ($idx - 1) * 15;
                                                        $tx = ($idx - 1) * 15;
                                                        $ty = abs($idx - 1) * 2;
                                                    } elseif ($count == 4) {
                                                        $rots = [-18, -6, 6, 18];
                                                        $txs = [-18, -6, 6, 18];
                                                        $tys = [4, 1, 1, 4];
                                                        $rot = $rots[$idx];
                                                        $tx = $txs[$idx];
                                                        $ty = $tys[$idx];
                                                    } else {
                                                        $step = 40 / max(1, $count - 1);
                                                        $rot = -20 + ($idx * $step);
                                                        $tx = -22 + ($idx * (44 / max(1, $count - 1)));
                                                        $ty = abs($idx - ($count - 1) / 2) * 2;
                                                    }
                                                    $zIndex = ($idx + 1) * 10;
                                                @endphp
                                                <div class="fanned-card absolute top-1/2 left-1/2 rounded-lg sm:rounded-xl overflow-hidden bg-white border border-white shadow-sm transition-all duration-300 hover:!z-50 hover:!scale-115 hover:!rotate-0"
                                                     style="width: {{ $count > 1 ? '52px' : '72px' }}; height: {{ $count > 1 ? '70px' : '82px' }}; margin-left: -{{ $count > 1 ? '26px' : '36px' }}; margin-top: -{{ $count > 1 ? '35px' : '41px' }}; transform: translate({{ $tx }}px, {{ $ty }}px) rotate({{ $rot }}deg); transform-origin: 50% 120%; z-index: {{ $zIndex }}; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.18);"
                                                     title="Page {{ $idx + 1 }}">
                                                    <img src="{{ $imgUrl }}" alt="Page {{ $idx + 1 }}" class="w-full h-full object-cover">
                                                    @if($count > 1)
                                                        <div class="absolute bottom-0.5 right-0.5 bg-slate-900/85 text-white text-[7px] font-black px-0.5 rounded">
                                                            P{{ $idx + 1 }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach

                                            @if($count > 1)
                                                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-slate-900/90 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-md backdrop-blur-xs whitespace-nowrap z-40 flex items-center gap-1 border border-slate-700/80">
                                                    <i data-lucide="layers" class="w-2.5 h-2.5 text-brand-400"></i>
                                                    {{ $count }} Pages
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-slate-50 border border-slate-200/80 shrink-0 relative flex flex-col items-center justify-center text-slate-300 shadow-2xs">
                                                <i data-lucide="image" class="w-6 h-6 mb-1"></i>
                                                <span class="text-[8px] font-bold text-slate-400">Custom Design</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Details --}}
                                    <div class="min-w-0 flex-1">
                                        @if(!empty($customization['type_name']))
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-brand-50 text-brand-600 border border-brand-100 text-[9px] font-black uppercase tracking-wider mb-1">
                                                <i data-lucide="tag" class="w-2.5 h-2.5"></i>
                                                {{ $customization['type_name'] }}
                                            </span>
                                        @endif
                                        
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h3 class="font-black text-slate-900 text-sm sm:text-base leading-snug group-hover:text-brand-600 transition-colors truncate">
                                                {{ $item->product->name ?? 'Custom Print' }}
                                            </h3>

                                            {{-- Preview Icon Button --}}
                                            <button type="button" 
                                                @click="openPreviewModal({{ json_encode([
                                                    'id' => $item->id,
                                                    'name' => $item->product->name ?? 'Custom Print',
                                                    'pages' => $itemImageUrls,
                                                    'width' => min($baseWidth, 180),
                                                    'height' => min($baseHeight, 240)
                                                ]) }})"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-brand-50 hover:bg-brand-500 text-brand-700 hover:text-white border border-brand-200/80 text-xs font-black transition-all shadow-2xs active:scale-95 cursor-pointer shrink-0"
                                                title="Preview Design">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                <span>Preview Design</span>
                                            </button>
                                        </div>

                                        {{-- Specifications Badges --}}
                                        <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                            @if(!empty($customization['size_name']))
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/80">
                                                    <i data-lucide="ruler" class="w-3 h-3 text-slate-400"></i>
                                                    {{ $customization['size_name'] }}
                                                    @if(!empty($customization['size_width']) && !empty($customization['size_height']))
                                                        <span class="text-slate-500 font-medium">({{ $customization['size_width'] }}×{{ $customization['size_height'] }}{{ $customization['size_unit'] ?? '' }})</span>
                                                    @endif
                                                </span>
                                            @endif
                                            
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">
                                                <i data-lucide="sparkles" class="w-3 h-3 text-emerald-500"></i> High Res
                                            </span>
                                        </div>

                                        <div class="mt-1 text-[11px] font-semibold text-slate-500">
                                            Unit Price: <strong class="text-slate-900 font-extrabold" x-text="__price(price)"></strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quantity Stepper --}}
                                <div class="flex flex-col items-center justify-center border-t lg:border-t-0 border-slate-100 pt-2 lg:pt-0">
                                    <span class="lg:hidden text-[10px] font-bold text-slate-400 uppercase mb-1">Quantity</span>
                                    <div class="inline-flex items-center bg-slate-100/80 p-0.5 rounded-xl border border-slate-200/80 shadow-inner">
                                        <button type="button" @click="changeQty(qty - 1)" :disabled="qty <= 1 || loading"
                                            class="w-7 h-7 rounded-lg bg-white text-slate-800 hover:bg-brand-600 hover:text-white flex items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:hover:bg-white shadow-2xs font-black text-sm border border-slate-200/80 active:scale-95">
                                            −
                                        </button>
                                        <span class="w-9 text-center font-black text-slate-900 text-sm" x-text="qty"></span>
                                        <button type="button" @click="changeQty(qty + 1)" :disabled="loading"
                                            class="w-7 h-7 rounded-lg bg-white text-slate-800 hover:bg-brand-600 hover:text-white flex items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:hover:bg-white shadow-2xs font-black text-sm border border-slate-200/80 active:scale-95">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Item Total Price --}}
                                <div class="flex items-center justify-between lg:justify-end border-t lg:border-t-0 border-slate-100 pt-2 lg:pt-0">
                                    <span class="lg:hidden text-[10px] font-bold text-slate-400 uppercase">Item Total</span>
                                    <div class="text-right">
                                        <span class="font-black text-lg text-slate-900" x-text="__price(price * qty)"></span>
                                    </div>
                                </div>

                                {{-- Remove Button --}}
                                <div class="flex justify-end lg:justify-center">
                                    <button type="button" @click="removeItem()"
                                        class="w-8 h-8 rounded-xl bg-slate-100/80 hover:bg-red-50 text-slate-400 hover:text-red-500 flex items-center justify-center transition-all duration-200 border border-slate-200/80 hover:border-red-200 group/btn"
                                        title="Remove item from cart">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 group-hover/btn:scale-110 transition-transform"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endforeach

                    {{-- Guarantee Banner Below Items --}}
                    <div class="bg-emerald-50/80 border border-emerald-200/80 text-slate-900 rounded-2xl py-2 px-4 flex flex-col sm:flex-row items-center justify-between gap-2 shadow-2xs">
                        <div class="flex items-center gap-2 text-xs">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span class="font-extrabold text-slate-900">100% Print Guarantee</span>
                            <span class="hidden sm:inline text-slate-500 font-medium">— Free reprint or refund if not satisfied</span>
                        </div>
                        <a href="{{ route($routePrefix . 'index') }}" class="shrink-0 text-[11px] font-black text-emerald-700 hover:text-emerald-800 transition-colors flex items-center gap-1">
                            Continue Shopping <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>

                {{-- Right Column: Order Summary Sidebar --}}
                <div class="w-full lg:w-[360px] shrink-0 sticky top-20">
                    <div class="glass-card border border-slate-200/90 rounded-2xl p-4 lg:p-5 shadow-lg shadow-slate-200/40 relative overflow-hidden">
                        
                        {{-- Top Accent Line --}}
                        <div class="absolute top-0 left-0 right-0 h-1 bg-brand-500"></div>

                        {{-- Summary Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 mb-3.5">
                            <h2 class="text-lg font-black text-slate-900 tracking-tight">Order Summary</h2>
                            <span class="inline-flex items-center gap-1 text-[10px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                <i data-lucide="shield-check" class="w-3 h-3 text-emerald-500"></i> SSL Secure
                            </span>
                        </div>

                        {{-- Priority Delivery Bar --}}
                        <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-2.5 mb-3.5 shadow-2xs">
                            <div class="flex items-center justify-between text-[11px] font-extrabold text-amber-900 mb-1">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-600"></i> Priority Delivery
                                </span>
                                <span class="text-amber-700 font-bold">Express Door Shipping</span>
                            </div>
                            <div class="w-full bg-amber-200/60 rounded-full h-1.5 overflow-hidden p-0.5">
                                <div class="bg-amber-500 h-full rounded-full w-full"></div>
                            </div>
                        </div>

                        {{-- Promo Code Input Box --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                    <i data-lucide="ticket" class="w-3 h-3 text-brand-500"></i> Promo Code
                                </label>
                                <template x-if="appliedCoupon">
                                    <button type="button" @click="removeCoupon()" class="text-[10px] font-bold text-red-500 hover:text-red-600 transition-colors">
                                        Remove
                                    </button>
                                </template>
                            </div>
                            <div class="flex gap-1.5">
                                <div class="relative flex-1">
                                    <input type="text" x-model="couponInput" :disabled="appliedCoupon" placeholder="ENTER CODE"
                                        class="w-full bg-white border border-slate-200 focus:border-brand-500 rounded-xl py-1.5 px-3 text-xs font-bold uppercase tracking-wider transition-all outline-none disabled:bg-slate-100 text-slate-800 shadow-2xs"
                                        @keydown.enter.prevent="applyCoupon()">
                                </div>
                                <button type="button" @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                                    class="px-4 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-black uppercase tracking-wider disabled:opacity-40 transition-all shadow-2xs active:scale-95 cursor-pointer">
                                    Apply
                                </button>
                            </div>
                            <p x-show="couponMessage" x-text="couponMessage"
                                :class="appliedCoupon ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-red-500 bg-red-50 border-red-200'"
                                class="text-xs font-bold mt-2 p-1.5 rounded-lg border" style="display:none"></p>
                        </div>

                        {{-- Totals Breakdown --}}
                        <div class="space-y-2 pb-3.5 border-b border-slate-200/80 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-semibold">Subtotal</span>
                                <span class="font-extrabold text-slate-800" x-text="__price(subtotal)"></span>
                            </div>

                            <template x-if="discount > 0">
                                <div class="flex justify-between items-center">
                                    <span class="text-emerald-600 font-bold flex items-center gap-1">
                                        <i data-lucide="tag" class="w-3 h-3"></i> Discount (<span x-text="appliedCoupon"></span>)
                                    </span>
                                    <span class="font-extrabold text-emerald-600" x-text="'-' + __price(discount)"></span>
                                </div>
                            </template>

                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-semibold">Shipping</span>
                                <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">Calculated at Checkout</span>
                            </div>
                        </div>

                        {{-- Grand Total Box --}}
                        <div class="my-3.5 bg-brand-50/90 rounded-xl p-3.5 border border-brand-200/80 shadow-2xs">
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs font-extrabold text-slate-700">Total Amount</span>
                                <span class="text-2xl font-black text-slate-900 tracking-tight" x-text="__price(total)"></span>
                            </div>
                        </div>

                        {{-- Checkout CTA Button --}}
                        <div class="space-y-3">
                            <a href="{{ route($routePrefix . 'cart-checkout') }}"
                                class="shimmer-cta w-full bg-brand-500 hover:bg-brand-600 text-white font-black py-3 px-5 rounded-xl shadow-md shadow-brand-500/20 transition-all duration-300 flex items-center justify-center gap-2 text-sm tracking-wide active:scale-[0.99] cursor-pointer">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                Proceed to Checkout
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>

                            {{-- Trust Highlights --}}
                            <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-1.5 text-[10px] font-semibold text-slate-400">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-500 shrink-0"></i>
                                    <span>Print Guarantee</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i data-lucide="truck" class="w-3 h-3 text-brand-500 shrink-0"></i>
                                    <span>Express Shipping</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        @else
            {{-- Empty Cart State --}}
            <div class="max-w-md mx-auto text-center py-20 px-8 bg-white/90 backdrop-blur-md rounded-3xl border border-slate-200/90 shadow-2xl shadow-slate-200/40">
                <div class="w-28 h-28 rounded-3xl bg-brand-50 border border-brand-100 flex items-center justify-center mx-auto mb-6 shadow-inner relative">
                    <i data-lucide="shopping-bag" class="w-14 h-14 text-brand-500"></i>
                    <div class="absolute -top-1 -right-1 w-7 h-7 rounded-full bg-brand-500 text-white flex items-center justify-center text-xs font-black shadow-md">0</div>
                </div>
                <h2 class="font-black text-2xl text-slate-900 mb-2">Your cart is empty</h2>
                <p class="text-slate-500 font-medium text-sm mb-8 leading-relaxed">You haven't added any custom print templates to your cart yet. Explore our templates and start customizing!</p>
                <a href="{{ route($routePrefix . 'index') }}"
                    class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-brand-600 hover:bg-brand-700 text-white font-black rounded-2xl transition shadow-xl shadow-brand-500/25 active:scale-95 text-base">
                    <i data-lucide="sparkles" class="w-5 h-5"></i> Start Creating Now
                </a>
            </div>
        @endif

        {{-- Design Preview Flipbook Modal --}}
        <div x-show="previewModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="bg-white border border-slate-200/90 rounded-3xl shadow-2xl max-w-xl w-full p-5 sm:p-6 relative overflow-hidden transform transition-all"
                @click.away="previewModalOpen = false">

                {{-- Header --}}
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-200/80 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight" x-text="previewItem?.name || 'Order Design Preview'"></h3>
                            <span class="text-[11px] font-bold text-slate-500" x-text="previewItem?.pages ? previewItem.pages.length + ' Design Pages' : ''"></span>
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
                        <div class="book-container py-3">
                            <div class="book" :style="`--book-w: ${previewItem.width}px; --book-h: ${previewItem.height}px;`">
                                <template x-for="(pair, pIdx) in getPagePairs(previewItem.pages)" :key="pIdx">
                                    <div class="page" :style="`--i: ${pIdx}`" @click="$el.classList.toggle('flipped')">
                                        <div class="page-face front">
                                            <img :src="pair.front" :alt="`Page ${pIdx * 2 + 1}`">
                                        </div>
                                        <div class="page-face back">
                                            <template x-if="pair.back">
                                                <img :src="pair.back" :alt="`Page ${pIdx * 2 + 2}`">
                                            </template>
                                            <template x-if="!pair.back">
                                                <div class="w-full h-full bg-slate-50 flex items-center justify-center">
                                                    <span class="text-slate-300 font-bold text-xs">Back Page</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <p class="text-center text-[11px] text-slate-600 font-bold flex items-center justify-center gap-1.5 mt-3 bg-slate-100/80 py-2 px-3 rounded-xl border border-slate-200/60">
                            <i data-lucide="mouse-pointer-2" class="w-3.5 h-3.5 text-brand-600"></i>
                            Click pages above to flip through design pages
                        </p>
                    </div>
                </template>

                {{-- Body: Single Page Card --}}
                <template x-if="previewItem && previewItem.pages && previewItem.pages.length === 1">
                    <div class="flex flex-col items-center justify-center py-4">
                        <div class="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-xl p-1.5" :style="`max-width: ${previewItem.width}px; max-height: ${previewItem.height}px;`">
                            <img :src="previewItem.pages[0]" :alt="previewItem.name" class="w-full h-full object-cover rounded-xl">
                        </div>
                        <p class="text-center text-[11px] text-slate-500 font-bold mt-3">
                            Single Page Custom Print Design
                        </p>
                    </div>
                </template>

                <template x-if="!previewItem || !previewItem.pages || previewItem.pages.length === 0">
                    <div class="py-8 text-center text-slate-400 font-medium text-xs">
                        No preview available for this design item.
                    </div>
                </template>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function cartPage() {
    return {
        subtotal: {{ $cart->subtotal }},
        discount: {{ $cart->discount }},
        total: {{ $cart->total }},
        itemCount: {{ $cart->item_count }},
        couponInput: '{{ $cart->coupon->code ?? '' }}',
        appliedCoupon: {!! $cart->coupon ? "'" . $cart->coupon->code . "'" : 'null' !!},
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

        updateFromResponse(data) {
            this.subtotal = data.subtotal;
            this.discount = data.discount;
            this.total = data.total;
            this.itemCount = data.cart_count;
            if (data.cart_count === 0) {
                setTimeout(() => location.reload(), 300);
            }
        },

        async applyCoupon() {
            if (!this.couponInput || this.appliedCoupon) return;
            try {
                const res = await fetch('{{ route($routePrefix . "cart.apply-coupon") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ code: this.couponInput })
                });
                const data = await res.json();
                if (data.success) {
                    this.appliedCoupon = this.couponInput;
                    this.discount = parseFloat(data.discount);
                    this.total = Math.max(0, this.subtotal - this.discount);
                    this.couponMessage = data.message;
                } else {
                    this.couponMessage = data.message;
                }
            } catch(e) { this.couponMessage = 'Error applying coupon.'; }
        },

        async removeCoupon() {
            try {
                await fetch('{{ route($routePrefix . "cart.remove-coupon") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.appliedCoupon = null;
                this.discount = 0;
                this.total = this.subtotal;
                this.couponInput = '';
                this.couponMessage = '';
            } catch(e) { console.error(e); }
        }
    }
}

function cartItem(id, initialQty, unitPrice) {
    return {
        id: id,
        qty: initialQty,
        price: unitPrice,
        loading: false,
        removed: false,

        async changeQty(newQty) {
            if (newQty < 1 || this.loading) return;
            this.loading = true;
            try {
                const res = await fetch(`{{ url(str_starts_with($routePrefix, 'flow-pc') ? 'pc/cart/update' : 'cart/update') }}/${this.id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ quantity: newQty })
                });
                const data = await res.json();
                if (data.success) {
                    this.qty = newQty;
                    const page = document.querySelector('[x-data^="cartPage"]');
                    if (page && page.__x) {
                        page.__x.$data.subtotal = data.subtotal;
                        page.__x.$data.discount = data.discount;
                        page.__x.$data.total = data.total;
                        page.__x.$data.itemCount = data.cart_count;
                    }
                }
            } catch(e) { console.error(e); }
            this.loading = false;
        },

        async removeItem() {
            this.loading = true;
            try {
                const res = await fetch(`{{ url(str_starts_with($routePrefix, 'flow-pc') ? 'pc/cart/remove' : 'cart/remove') }}/${this.id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                if (data.success) {
                    this.removed = true;
                    const page = document.querySelector('[x-data^="cartPage"]');
                    if (page && page.__x) {
                        page.__x.$data.subtotal = data.subtotal;
                        page.__x.$data.discount = data.discount;
                        page.__x.$data.total = data.total;
                        page.__x.$data.itemCount = data.cart_count;
                        if (data.cart_count === 0) setTimeout(() => location.reload(), 300);
                    }
                }
            } catch(e) { console.error(e); }
            this.loading = false;
        }
    }
}
</script>
@endpush
