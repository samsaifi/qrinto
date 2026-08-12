@extends('layouts.quick-flow-pc')

@section('title', 'Checkout — Qrinto Print Studio')
@section('header_title', 'Checkout')

@php
    $routePrefix = $routePrefix ?? 'flow-pc.';
@endphp

@push('styles')
    <style>
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

        .paypal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 80;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .paypal-sheet {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 2rem;
            padding: 2rem;
            max-height: 88vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.3);
            animation: modalIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalIn {
            from {
                transform: scale(0.94);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .processing-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .processing-overlay .spinner {
            width: 52px;
            height: 52px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #ec4899;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .success-check {
            width: 72px;
            height: 72px;
            background: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.4);
            animation: popIn 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-4px);
            }

            40%,
            80% {
                transform: translateX(4px);
            }
        }

        .animate-shake {
            animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }
    </style>
@endpush

@section('content')
    <div x-data="cartCheckoutFlow()" class="ambient-bg min-h-screen pt-5 pb-6 font-sans text-slate-900">
        <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Sleek Compact Top Header Bar --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 bg-white/90 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-3">
                    <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <a href="{{ route($routePrefix . 'index') }}"
                            class="text-slate-500 hover:text-brand-600 transition-colors flex items-center gap-1">
                            <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Home</span>
                        </a>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <a href="{{ route('flow-pc.cart.index') }}"
                            class="text-slate-500 hover:text-brand-600 transition-colors">Cart</a>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                    </nav>
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        Checkout & Review
                    </h1>
                    <span
                        class="text-xs font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60">
                        <i data-lucide="shield-check" class="w-3 h-3 inline"></i> 256-Bit SSL
                    </span>
                </div>

                {{-- Step Indicator & Order Count --}}
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 text-[11px] font-bold">
                        <span class="text-emerald-600 flex items-center gap-1"><i data-lucide="check-circle"
                                class="w-3 h-3"></i> 1. Customize</span>
                        <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                        <span class="text-emerald-600 flex items-center gap-1"><i data-lucide="check-circle"
                                class="w-3 h-3"></i> 2. Cart</span>
                        <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
                        <span class="text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200/60">3.
                            Checkout & Pay</span>
                    </div>
                    <div class="h-4 w-px bg-slate-200 hidden md:block"></div>
                    <span
                        class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl border border-slate-200/60">
                        {{ $cart->item_count }} Item{{ $cart->item_count > 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            {{-- Global Form Validation Error Banner --}}
            <div x-show="errors.form" x-cloak
                class="mb-8 p-5 bg-red-50/90 border-2 border-red-200 rounded-3xl flex items-center gap-4 text-red-700 animate-shake shadow-lg shadow-red-500/5">
                <div class="w-10 h-10 bg-red-100 rounded-2xl flex items-center justify-center text-red-600 shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <h4 class="font-extrabold text-sm text-red-900">Validation Notice</h4>
                    <p class="text-xs font-semibold text-red-600 mt-0.5" x-text="errors.form"></p>
                </div>
                <button @click="errors.form = ''"
                    class="text-red-400 hover:text-red-600 p-1.5 rounded-xl hover:bg-red-100 transition-colors"><i
                        data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            {{-- 2-Column Desktop Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                {{-- Left Column: Order Items Summary & Pickup Information Form --}}
                <div class="lg:col-span-7 space-y-4">

                    {{-- Cart Items Summary Card --}}
                    <div class="glass-card border border-slate-200/90 rounded-2xl p-4 shadow-2xs">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 mb-3.5">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                                    <i data-lucide="package" class="w-4 h-4"></i>
                                </div>
                                <h2 class="text-sm font-black text-slate-900 tracking-tight">Order Items Breakdown</h2>
                            </div>
                            <span
                                class="text-[11px] font-extrabold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200/60">
                                {{ $cart->item_count }} Item{{ $cart->item_count > 1 ? 's' : '' }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach ($cart->items as $item)
                                @php
                                    $customization = $item->customization_data ?? [];
                                    $itemUploadIds = $customization['upload_ids'] ?? [];
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
                                        if (is_array($itemUploadIds)) {
                                            $upId = $itemUploadIds[$slotKey] ?? ($itemUploadIds[$index] ?? null);
                                            if ($upId && isset($uploads[$upId]) && !empty($uploads[$upId]->url)) {
                                                $url = $uploads[$upId]->url;
                                            }
                                        } elseif (
                                            $index === 0 &&
                                            is_scalar($itemUploadIds) &&
                                            isset($uploads[$itemUploadIds]) &&
                                            !empty($uploads[$itemUploadIds]->url)
                                        ) {
                                            $url = $uploads[$itemUploadIds]->url;
                                        }

                                        if (!$url && $item->product) {
                                            $url = $item->product->{$slotKey . '_url'} ?? null;
                                        }

                                        if ($url) {
                                            $itemImageUrls[] = $url;
                                        }
                                    }

                                    if (empty($itemImageUrls)) {
                                        if (is_array($itemUploadIds)) {
                                            foreach ($itemUploadIds as $upId) {
                                                if ($upId && isset($uploads[$upId]) && !empty($uploads[$upId]->url)) {
                                                    $itemImageUrls[] = $uploads[$upId]->url;
                                                }
                                            }
                                        }
                                        if (
                                            empty($itemImageUrls) &&
                                            $item->product &&
                                            $item->product->frame_image_url
                                        ) {
                                            $itemImageUrls[] = $item->product->frame_image_url;
                                        }
                                    }

                                    $imageCount = count($itemImageUrls);
                                    $product = $item->product;
                                    $orientation = $product->pdf_orientation ?? 'portrait';
                                    $isPortrait = $orientation === 'portrait';
                                    $baseWidth = $isPortrait ? 180 : 240;

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
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center gap-5 sm:gap-6">
                                    {{-- Fanned Card Deck Preview Stage --}}
                                    <div
                                        class="fanned-card-stage relative flex items-center justify-center w-24 h-20 sm:w-28 sm:h-22 shrink-0 select-none py-1 px-1">
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
                                                        $txs = [-14, -4, 4, 14];
                                                        $tys = [3, 1, 1, 3];
                                                        $rot = $rots[$idx];
                                                        $tx = $txs[$idx];
                                                        $ty = $tys[$idx];
                                                    } else {
                                                        $step = 36 / max(1, $count - 1);
                                                        $rot = -18 + $idx * $step;
                                                        $tx = -16 + $idx * (32 / max(1, $count - 1));
                                                        $ty = abs($idx - ($count - 1) / 2) * 2;
                                                    }
                                                    $zIndex = ($idx + 1) * 10;
                                                @endphp
                                                <div class="fanned-card absolute top-1/2 left-1/2 rounded-md overflow-hidden bg-white border border-white shadow-sm transition-all duration-300 hover:!z-50 hover:!scale-115 hover:!rotate-0"
                                                    style="width: {{ $count > 1 ? '42px' : '56px' }}; height: {{ $count > 1 ? '56px' : '66px' }}; margin-left: -{{ $count > 1 ? '21px' : '28px' }}; margin-top: -{{ $count > 1 ? '28px' : '33px' }}; transform: translate({{ $tx }}px, {{ $ty }}px) rotate({{ $rot }}deg); transform-origin: 50% 120%; z-index: {{ $zIndex }}; box-shadow: 0 3px 8px -2px rgba(0,0,0,0.15);"
                                                    title="Page {{ $idx + 1 }}">
                                                    <img src="{{ $imgUrl }}" alt="Page {{ $idx + 1 }}"
                                                        class="w-full h-full object-cover">
                                                </div>
                                            @endforeach
                                        @else
                                            <div
                                                class="w-14 h-14 rounded-xl overflow-hidden bg-slate-50 border border-slate-200/80 shrink-0 relative flex items-center justify-center text-slate-300">
                                                <i data-lucide="image" class="w-5 h-5"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                            <h4 class="font-black text-xs sm:text-sm text-slate-900 truncate mb-0.5">
                                                {{ $item->product->name ?? 'Custom Print' }}</h4>

                                            {{-- Preview Icon Button --}}
                                            <button type="button"
                                                @click="openPreviewModal({{ json_encode([
                                                    'id' => $item->id,
                                                    'name' => $item->product->name ?? 'Custom Print',
                                                    'pages' => $itemImageUrls,
                                                    'width' => min($baseWidth, 180),
                                                    'height' => min($baseHeight, 240),
                                                ]) }})"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-brand-50 hover:bg-brand-500 text-brand-700 hover:text-white border border-brand-200/80 text-[11px] font-black transition-all shadow-2xs active:scale-95 cursor-pointer shrink-0"
                                                title="Preview Design">
                                                <i data-lucide="eye" class="w-3 h-3"></i>
                                                <span>Preview</span>
                                            </button>
                                        </div>
                                        <div
                                            class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500 font-semibold">
                                            <span class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-bold">Qty:
                                                {{ $item->quantity }}</span>
                                            @if (!empty($customization['size_name']))
                                                <span
                                                    class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-bold">Size:
                                                    {{ $customization['size_name'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="font-black text-sm sm:text-base text-slate-900 block"
                                            x-text="__price({{ $item->unit_price }} * {{ $item->quantity }})"></span>
                                        <span class="text-[10px] font-semibold text-slate-400"
                                            x-text="__price({{ $item->unit_price }}) + ' ea'"></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pickup Information Form Card --}}
                    <div class="glass-card border border-slate-200/90 rounded-2xl p-4 shadow-2xs">
                        <div class="flex items-center gap-2.5 mb-3.5 pb-2.5 border-b border-slate-200/80">
                            <div
                                class="w-8 h-8 bg-brand-50 border border-brand-100 rounded-lg flex items-center justify-center text-brand-600 flex-shrink-0">
                                <i data-lucide="user-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 tracking-tight">Pickup Information</h3>
                                <p class="text-[10px] text-slate-500 font-medium">Details of the person collecting this
                                    print order</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            {{-- Pickup Name --}}
                            <div class="md:col-span-2">
                                <label
                                    class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">Pickup
                                    Full Name <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 transition-colors"
                                        :class="errors.pickupName ? 'text-red-400' :
                                            'text-slate-400 group-focus-within:text-brand-500'">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" x-model="pickupName" @blur="validatePickupName()"
                                        @input="if(touched.pickupName) validatePickupName()"
                                        :class="errors.pickupName ? 'border-red-400 bg-red-50/20 focus:border-red-500' : (
                                            touched.pickupName && !errors.pickupName ?
                                            'border-emerald-400 bg-emerald-50/10' :
                                            'border-slate-200 focus:border-brand-500')"
                                        class="w-full bg-white border focus:bg-white rounded-xl py-2 pl-9 pr-3 font-bold text-slate-900 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-xs shadow-2xs"
                                        placeholder="Enter your full name">
                                </div>
                                <p x-show="errors.pickupName" x-text="errors.pickupName"
                                    class="text-[10px] font-bold text-red-500 mt-1 ml-1 flex items-center gap-1"
                                    style="display:none">
                                    <i data-lucide="alert-circle" class="w-3 h-3 flex-shrink-0"></i>
                                </p>
                            </div>

                            {{-- Email Address --}}
                            <div>
                                <label
                                    class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">Email
                                    Address <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 transition-colors"
                                        :class="errors.pickupEmail ? 'text-red-400' :
                                            'text-slate-400 group-focus-within:text-brand-500'">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                    </div>
                                    <input type="email" x-model="pickupEmail" @blur="validatePickupEmail()"
                                        @input="if(touched.pickupEmail) validatePickupEmail()"
                                        :class="errors.pickupEmail ? 'border-red-400 bg-red-50/20 focus:border-red-500' : (
                                            touched.pickupEmail && !errors.pickupEmail ?
                                            'border-emerald-400 bg-emerald-50/10' :
                                            'border-slate-200 focus:border-brand-500')"
                                        class="w-full bg-white border focus:bg-white rounded-xl py-2 pl-9 pr-3 font-bold text-slate-900 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-xs shadow-2xs"
                                        placeholder="name@example.com">
                                </div>
                                <p x-show="errors.pickupEmail" x-text="errors.pickupEmail"
                                    class="text-[10px] font-bold text-red-500 mt-1 ml-1 flex items-center gap-1"
                                    style="display:none">
                                    <i data-lucide="alert-circle" class="w-3 h-3 flex-shrink-0"></i>
                                </p>
                            </div>

                            {{-- Contact Phone Number --}}
                            <div>
                                <label
                                    class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider mb-1">Contact
                                    Phone <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 transition-colors"
                                        :class="errors.contactNumber ? 'text-red-400' :
                                            'text-slate-400 group-focus-within:text-brand-500'">
                                        <i data-lucide="phone" class="w-4 h-4"></i>
                                    </div>
                                    <input type="tel" x-model="contactNumber" @blur="validateContactNumber()"
                                        @input="if(touched.contactNumber) validateContactNumber()"
                                        :class="errors.contactNumber ? 'border-red-400 bg-red-50/20 focus:border-red-500' : (
                                            touched.contactNumber && !errors.contactNumber ?
                                            'border-emerald-400 bg-emerald-50/10' :
                                            'border-slate-200 focus:border-brand-500')"
                                        class="w-full bg-white border focus:bg-white rounded-xl py-2 pl-9 pr-3 font-bold text-slate-900 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-xs shadow-2xs"
                                        placeholder="Phone number">
                                </div>
                                <p x-show="errors.contactNumber" x-text="errors.contactNumber"
                                    class="text-[10px] font-bold text-red-500 mt-1 ml-1 flex items-center gap-1"
                                    style="display:none">
                                    <i data-lucide="alert-circle" class="w-3 h-3 flex-shrink-0"></i>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Special Instructions Card --}}
                    <div class="glass-card border border-slate-200/90 rounded-2xl p-4 shadow-2xs">
                        <div class="flex items-center gap-2.5 mb-2.5">
                            <div
                                class="w-7 h-7 bg-slate-100 border border-slate-200/80 rounded-lg flex items-center justify-center text-slate-700 flex-shrink-0">
                                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-black text-slate-900 tracking-tight">Special Instructions</h3>
                                <p class="text-[10px] text-slate-500 font-medium">Optional print notes or store pickup
                                    requests</p>
                            </div>
                        </div>
                        <div class="relative group">
                            <textarea x-model="specialInstructions" rows="2"
                                class="w-full bg-white border border-slate-200 focus:bg-white rounded-xl p-2.5 font-medium text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all outline-none text-xs resize-none shadow-2xs"
                                placeholder="Rush order requests, custom paper instructions, or store pickup notes..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Order Summary & Payment Panel --}}
                <div class="lg:col-span-5 sticky top-20">
                    <div
                        class="glass-card border border-slate-200/90 rounded-2xl p-4 lg:p-5 shadow-lg shadow-slate-200/40 relative overflow-hidden space-y-4">

                        {{-- Top Accent Line --}}
                        <div class="absolute top-0 left-0 right-0 h-1 bg-brand-500"></div>

                        {{-- Summary Header --}}
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Payment Summary</h3>
                            <span
                                class="inline-flex items-center gap-1 text-[10px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                <i data-lucide="shield-check" class="w-3 h-3 text-emerald-500"></i> Encrypted
                            </span>
                        </div>

                        {{-- Promo Code Section --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label
                                    class="text-[10px] font-black text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                    <i data-lucide="ticket" class="w-3 h-3 text-brand-500"></i> Promo Code
                                </label>
                                <template x-if="appliedCoupon">
                                    <button type="button" @click="removeCoupon()"
                                        class="text-[10px] font-bold text-red-500 hover:underline uppercase">Remove</button>
                                </template>
                            </div>
                            <div class="flex gap-1.5">
                                <div class="relative flex-1">
                                    <input type="text" x-model="couponInput" :disabled="appliedCoupon"
                                        placeholder="ENTER CODE"
                                        class="w-full bg-white border border-slate-200 focus:border-brand-500 rounded-xl py-1.5 px-3 text-xs font-bold uppercase tracking-wider transition-all outline-none disabled:bg-slate-100 text-slate-800 shadow-2xs"
                                        @keydown.enter.prevent="applyCoupon()">
                                </div>
                                <button type="button" @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                                    class="px-3.5 bg-slate-800 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-brand-600 disabled:opacity-40 transition-all shadow-2xs active:scale-95 cursor-pointer">
                                    Apply
                                </button>
                            </div>
                            <p x-show="couponMessage" x-text="couponMessage"
                                :class="appliedCoupon ? 'text-emerald-600 bg-emerald-50 border-emerald-200' :
                                    'text-red-500 bg-red-50 border-red-200'"
                                class="text-xs font-bold mt-1.5 p-1.5 rounded-lg border" style="display:none"></p>
                        </div>

                        {{-- Price Breakdown --}}
                        <div class="space-y-2 pt-1 pb-3.5 border-b border-slate-200/80 text-xs">
                            <div class="flex justify-between items-center text-slate-600 font-semibold">
                                <span>Subtotal</span>
                                <span class="font-extrabold text-slate-800" x-text="__price(subtotal)"></span>
                            </div>
                            <template x-if="discountAmount > 0">
                                <div class="flex justify-between items-center text-emerald-600 font-semibold">
                                    <span x-text="'Discount (' + appliedCoupon + ')'" class="flex items-center gap-1"><i
                                            data-lucide="tag" class="w-3 h-3"></i></span>
                                    <span class="font-black" x-text="'-' + __price(discountAmount)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600 font-semibold">Shipping / Pickup Fee</span>
                                <span
                                    class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">FREE
                                    Pickup</span>
                            </div>
                        </div>

                        {{-- Light High-Contrast Grand Total Block --}}
                        <div class="my-3.5 bg-brand-50/90 rounded-xl p-3.5 border border-brand-200/80 shadow-2xs">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <span class="text-xs font-extrabold text-slate-700">Total Amount</span>
                                <span class="text-2xl font-black text-slate-900 tracking-tight"
                                    x-text="__price(calculateTotal())"></span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 text-right">Includes taxes & print setup</p>
                        </div>

                        {{-- Terms & Conditions Checkbox Card --}}
                        <div>
                            <div class="flex items-start gap-2.5 p-3 bg-slate-50 border transition-all rounded-xl"
                                :class="errors.terms ? 'border-red-400 bg-red-50/30' : (acceptedTerms ?
                                    'border-emerald-300 bg-emerald-50/20' : 'border-slate-200')">
                                <input type="checkbox" x-model="acceptedTerms" @change="validateTerms()"
                                    id="terms-checkbox-pc"
                                    class="mt-0.5 w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                <label for="terms-checkbox-pc"
                                    class="text-[11px] font-medium text-slate-700 cursor-pointer select-none leading-tight">
                                    I agree to the <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}"
                                        target="_blank"
                                        class="text-brand-600 font-bold underline hover:text-brand-700">Terms and
                                        Conditions</a> and privacy notice.
                                </label>
                            </div>
                            <p x-show="errors.terms" x-text="errors.terms"
                                class="text-[10px] font-bold text-red-500 mt-1 ml-1 flex items-center gap-1"
                                style="display:none">
                                <i data-lucide="alert-circle" class="w-3 h-3 flex-shrink-0"></i>
                            </p>
                        </div>

                        {{-- Checkout Action Buttons --}}
                        <div class="space-y-2.5 pt-1">
                            <button type="button" @click="openPaypal()" :disabled="!isFormValid()"
                                class="shimmer-cta w-full bg-brand-500 hover:bg-brand-600 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black py-3 rounded-xl shadow-md shadow-brand-500/20 disabled:shadow-none transition-all active:scale-[0.99] flex items-center justify-center gap-2 text-sm cursor-pointer">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                <span x-text="getButtonText()"></span>
                                <i data-lucide="arrow-right" class="w-4 h-4" x-show="isFormValid()"></i>
                            </button>

                            <button type="button" @click="payByCash()" :disabled="!isFormValid()"
                                class="w-full bg-white disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-800 font-extrabold py-2.5 rounded-xl shadow-2xs transition-all active:scale-[0.98] flex items-center justify-center gap-2 text-xs cursor-pointer">
                                <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
                                <span>Pay by Cash at Counter</span>
                            </button>
                        </div>

                        {{-- Security & Trust Highlights Grid --}}
                        <div
                            class="pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-1.5 text-[10px] font-semibold text-slate-400">
                            <div class="flex items-center gap-1">
                                <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-500 shrink-0"></i>
                                <span>Print Guarantee</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="truck" class="w-3 h-3 text-brand-500 shrink-0"></i>
                                <span>Express Pickup</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="shield" class="w-3 h-3 text-brand-500 shrink-0"></i>
                                <span>Encrypted PayPal</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="headphones" class="w-3 h-3 text-brand-500 shrink-0"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- PayPal Modal Teleport --}}
            <template x-teleport="body">
                <div x-cloak>
                    <div x-show="showPaypal" class="paypal-overlay" @click.self="showPaypal = false">
                        <div class="paypal-sheet" @click.stop>
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600">
                                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-900">Pay with PayPal</h3>
                                        <p class="text-xs text-slate-400 font-semibold">Instant & secure 256-Bit
                                            transaction</p>
                                    </div>
                                </div>
                                <button @click="showPaypal = false"
                                    class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors cursor-pointer">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <div
                                class="bg-brand-50/90 text-slate-900 rounded-xl p-3.5 mb-4 flex items-center justify-between border border-brand-200/80 shadow-2xs">
                                <div>
                                    <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">Amount
                                        to Pay</p>
                                    <p class="text-xl font-black text-slate-900" x-text="__price(calculateTotal())"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">Pickup
                                        For</p>
                                    <p class="text-xs font-bold text-slate-800 truncate max-w-[140px]"
                                        x-text="pickupName"></p>
                                </div>
                            </div>

                            <div id="paypal-button-container" class="mb-2"></div>

                            <p
                                class="text-center text-xs text-slate-400 font-medium mt-4 flex items-center justify-center gap-1.5">
                                <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-500"></i>
                                Payments are processed securely by PayPal
                            </p>
                        </div>
                    </div>

                    {{-- Fullscreen Processing Overlay --}}
                    <div x-show="isProcessing" class="processing-overlay" x-cloak>
                        <template x-if="!paymentSuccess">
                            <div class="text-center">
                                <div class="spinner mx-auto mb-5"></div>
                                <h3 class="text-2xl font-black text-slate-900">Processing Your Order</h3>
                                <p class="text-slate-500 font-semibold text-sm mt-1.5">Please wait while we confirm your
                                    payment...</p>
                            </div>
                        </template>
                        <template x-if="paymentSuccess">
                            <div class="text-center">
                                <div class="success-check mx-auto mb-5">
                                    <i data-lucide="check" class="w-10 h-10 text-white"></i>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900">Payment Successful!</h3>
                                <p class="text-slate-500 font-semibold text-sm mt-1.5">Redirecting to your order
                                    confirmation details...</p>
                            </div>
                        </template>
                    </div>
                    {{-- Design Preview Flipbook Modal --}}
                    <div x-show="previewModalOpen" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md"
                        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                        <div class="bg-white border border-slate-200/90 rounded-3xl shadow-2xl max-w-xl w-full p-5 sm:p-6 relative overflow-hidden transform transition-all"
                            @click.away="previewModalOpen = false">

                            {{-- Header --}}
                            <div class="flex items-center justify-between pb-3.5 border-b border-slate-200/80 mb-4">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                                        <i data-lucide="layers" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight"
                                            x-text="previewItem?.name || 'Order Design Preview'"></h3>
                                        <span class="text-[11px] font-bold text-slate-500"
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
                                    <div class="book-container py-3">
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
                                                                <span class="text-slate-300 font-bold text-xs">Back
                                                                    Page</span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <p
                                        class="text-center text-[11px] text-slate-600 font-bold flex items-center justify-center gap-1.5 mt-3 bg-slate-100/80 py-2 px-3 rounded-xl border border-slate-200/60">
                                        <i data-lucide="mouse-pointer-2" class="w-3.5 h-3.5 text-brand-600"></i>
                                        Click pages above to flip through design pages
                                    </p>
                                </div>
                            </template>

                            {{-- Body: Single Page Card --}}
                            <template x-if="previewItem && previewItem.pages && previewItem.pages.length === 1">
                                <div class="flex flex-col items-center justify-center py-4">
                                    <div class="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-xl p-1.5"
                                        :style="`max-width: ${previewItem.width}px; max-height: ${previewItem.height}px;`">
                                        <img :src="previewItem.pages[0]" :alt="previewItem.name"
                                            class="w-full h-full object-cover rounded-xl">
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
            </template>
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

                couponInput: '{{ $cart->coupon->code ?? '' }}',
                appliedCoupon: {!! $cart->coupon ? "'" . $cart->coupon->code . "'" : 'null' !!},
                discountAmount: {{ $cart->discount }},
                couponMessage: '',

                errors: {
                    pickupName: '',
                    pickupEmail: '',
                    contactNumber: '',
                    terms: '',
                    form: ''
                },
                touched: {
                    pickupName: false,
                    pickupEmail: false,
                    contactNumber: false
                },

                __price(amount) {
                    const val = parseFloat(amount) || 0;
                    if (typeof window.__price === 'function') {
                        return window.__price(val);
                    }
                    const symbol = (window.__currency && window.__currency.symbol) ? window.__currency.symbol : '$';
                    const rate = (window.__currency && window.__currency.rate) ? window.__currency.rate : 1;
                    return symbol + (val * rate).toFixed(2);
                },

                calculateTotal() {
                    return Math.max(0, this.subtotal - this.discountAmount).toFixed(2);
                },

                isValidEmail(email) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email).trim());
                },

                isValidPhone(phone) {
                    const str = String(phone).trim();
                    const digitsOnly = str.replace(/\D/g, '');
                    return /^[\d\+\-\s\(\)]{7,20}$/.test(str) && digitsOnly.length >= 7;
                },

                validatePickupName() {
                    this.touched.pickupName = true;
                    if (!this.pickupName || !this.pickupName.trim()) {
                        this.errors.pickupName = 'Pickup name is required.';
                        return false;
                    } else if (this.pickupName.trim().length < 2) {
                        this.errors.pickupName = 'Name must be at least 2 characters.';
                        return false;
                    }
                    this.errors.pickupName = '';
                    return true;
                },

                validatePickupEmail() {
                    this.touched.pickupEmail = true;
                    if (!this.pickupEmail || !this.pickupEmail.trim()) {
                        this.errors.pickupEmail = 'Email address is required.';
                        return false;
                    } else if (!this.isValidEmail(this.pickupEmail)) {
                        this.errors.pickupEmail = 'Please enter a valid email address (e.g. name@example.com).';
                        return false;
                    }
                    this.errors.pickupEmail = '';
                    return true;
                },

                validateContactNumber() {
                    this.touched.contactNumber = true;
                    if (!this.contactNumber || !this.contactNumber.trim()) {
                        this.errors.contactNumber = 'Contact phone number is required.';
                        return false;
                    } else if (!this.isValidPhone(this.contactNumber)) {
                        this.errors.contactNumber = 'Please enter a valid phone number (at least 7 digits).';
                        return false;
                    }
                    this.errors.contactNumber = '';
                    return true;
                },

                validateTerms() {
                    if (!this.acceptedTerms) {
                        this.errors.terms = 'You must accept the Terms and Conditions.';
                        return false;
                    }
                    this.errors.terms = '';
                    return true;
                },

                validateAll() {
                    const v1 = this.validatePickupName();
                    const v2 = this.validatePickupEmail();
                    const v3 = this.validateContactNumber();
                    const v4 = this.validateTerms();

                    if (!v1 || !v2 || !v3 || !v4) {
                        this.errors.form = 'Please fix the highlighted errors before continuing.';
                        return false;
                    }
                    this.errors.form = '';
                    return true;
                },

                isFormValid() {
                    return this.pickupName && this.pickupName.trim().length >= 2 &&
                        this.pickupEmail && this.isValidEmail(this.pickupEmail) &&
                        this.contactNumber && this.isValidPhone(this.contactNumber) &&
                        this.acceptedTerms;
                },

                getButtonText() {
                    if (!this.acceptedTerms) {
                        return 'Accept Terms to Continue';
                    }
                    if (!this.pickupName || !this.pickupName.trim()) {
                        return 'Enter Pickup Name';
                    }
                    if (!this.pickupEmail || !this.pickupEmail.trim()) {
                        return 'Enter Email Address';
                    }
                    if (!this.isValidEmail(this.pickupEmail)) {
                        return 'Enter Valid Email Address';
                    }
                    if (!this.contactNumber || !this.contactNumber.trim()) {
                        return 'Enter Phone Number';
                    }
                    if (!this.isValidPhone(this.contactNumber)) {
                        return 'Enter Valid Phone Number';
                    }
                    return 'Pay Now — ' + this.__price(this.calculateTotal());
                },

                async applyCoupon() {
                    if (!this.couponInput || this.appliedCoupon) return;
                    try {
                        const res = await fetch('{{ route('flow-pc.cart.apply-coupon') }}', {
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
                    if (!this.validateAll()) {
                        const firstErrorEl = document.querySelector('.border-red-400');
                        if (firstErrorEl) firstErrorEl.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return;
                    }
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
                    if (!this.validateAll()) {
                        const firstErrorEl = document.querySelector('.border-red-400');
                        if (firstErrorEl) firstErrorEl.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return;
                    }
                    this.isProcessing = true;
                    this.errors.form = '';

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
                                this.errors.form = result.error || result.message || 'Failed to process order.';
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            this.isProcessing = false;
                            this.errors.form = 'An error occurred while connecting to the server.';
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
                                        self.errors.form = result.error || 'Payment failed.';
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    self.isProcessing = false;
                                    self.errors.form = 'An error occurred during PayPal processing.';
                                });
                        },

                        onCancel() {},
                        onError(err) {
                            console.error('PayPal Error:', err);
                            self.errors.form = 'PayPal encountered an error. Please try again or pay by cash.';
                        }
                    }).render('#paypal-button-container');
                }
            }
        }
    </script>
@endpush
