@extends('layouts.quick-flow')

@section('title', 'My Cart')
@section('header_title', 'My Cart')

@push('styles')
    <style>
        .cart-item-enter {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cart-item-remove {
            animation: slideOut 0.3s ease-in forwards;
        }

        @keyframes slideOut {
            to {
                opacity: 0;
                transform: translateX(60px);
                height: 0;
                margin: 0;
                padding: 0;
                overflow: hidden;
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
    <div x-data="cartPage()" @cart-updated.window="updateFromResponse($event.detail)" class="space-y-5 pb-48 px-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-extrabold flex items-center gap-3">
                <a href="{{ route($routePrefix . 'index') }}"
                    class="w-8 h-8 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors shrink-0">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                My Cart
            </h1>
            <p class="text-slate-500 font-medium text-xs ml-11">
                <span x-text="itemCount"></span> item<span x-show="itemCount !== 1">s</span> in your cart
            </p>
        </div>

        @if ($cart->items->count() > 0)
            {{-- Cart Items --}}
            <div class="space-y-3">
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
                    <div class="bg-white border-2 border-slate-50 rounded-[2rem] shadow-premium overflow-hidden cart-item-enter"
                        x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})" x-show="!removed" x-transition>
                        <div class="p-4 flex gap-4">
                            {{-- Thumbnail / Fanned Card Deck Preview Stage --}}
                            <div class="fanned-card-stage relative flex items-center justify-center w-24 h-24 shrink-0 select-none py-1 px-1 cursor-pointer"
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
                                                $rot = -20 + $idx * $step;
                                                $tx = -22 + $idx * (44 / max(1, $count - 1));
                                                $ty = abs($idx - ($count - 1) / 2) * 2;
                                            }
                                            $zIndex = ($idx + 1) * 10;
                                        @endphp
                                        <div class="fanned-card absolute top-1/2 left-1/2 rounded-lg overflow-hidden bg-white border border-white shadow-sm transition-all duration-300 hover:!z-50 hover:!scale-115 hover:!rotate-0"
                                            style="width: {{ $count > 1 ? '50px' : '68px' }}; height: {{ $count > 1 ? '68px' : '76px' }}; margin-left: -{{ $count > 1 ? '25px' : '34px' }}; margin-top: -{{ $count > 1 ? '34px' : '38px' }}; transform: translate({{ $tx }}px, {{ $ty }}px) rotate({{ $rot }}deg); transform-origin: 50% 120%; z-index: {{ $zIndex }}; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.18);"
                                            title="Page {{ $idx + 1 }}">
                                            <img src="{{ $imgUrl }}" alt="Page {{ $idx + 1 }}"
                                                class="w-full h-full object-cover">
                                            @if ($count > 1)
                                                <div
                                                    class="absolute bottom-0.5 right-0.5 bg-slate-900/85 text-white text-[7px] font-black px-0.5 rounded">
                                                    P{{ $idx + 1 }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if ($count > 1)
                                        <div
                                            class="absolute bottom-0 left-1/2 -translate-x-1/2 bg-slate-900/90 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-md backdrop-blur-xs whitespace-nowrap z-40 flex items-center gap-1 border border-slate-700/80">
                                            <i data-lucide="layers" class="w-2.5 h-2.5 text-mobile-400"></i>
                                            {{ $count }} Pages
                                        </div>
                                    @endif
                                @else
                                    <div
                                        class="w-16 h-16 rounded-xl overflow-hidden bg-slate-50 border border-slate-200/80 shrink-0 relative flex flex-col items-center justify-center text-slate-300 shadow-2xs">
                                        <i data-lucide="image" class="w-6 h-6 mb-1"></i>
                                        <span class="text-[8px] font-bold text-slate-400">Custom Design</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        @if (!empty($customization['type_name']))
                                            <span
                                                class="text-[9px] font-black text-mobile-600 uppercase tracking-[0.15em]">{{ $customization['type_name'] }}</span>
                                        @endif
                                        <h3 class="font-extrabold text-sm text-slate-900 leading-tight truncate">
                                            {{ $item->product->name ?? 'Custom Print' }}
                                        </h3>
                                        @if (!empty($customization['size_name']))
                                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                                {{ $customization['size_name'] }}
                                                @if (!empty($customization['size_width']) && !empty($customization['size_height']))
                                                    ·
                                                    {{ $customization['size_width'] }}×{{ $customization['size_height'] }}{{ $customization['size_unit'] ?? '' }}
                                                @endif
                                            </p>
                                        @endif

                                        {{-- Preview Design Button --}}
                                        <button type="button"
                                            @click="openPreviewModal({{ json_encode([
                                                'id' => $item->id,
                                                'name' => $item->product->name ?? 'Custom Print',
                                                'pages' => $itemImageUrls,
                                                'width' => min($baseWidth, 160),
                                                'height' => min($baseHeight, 210),
                                            ]) }})"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-mobile-50 hover:bg-mobile-500 text-mobile-700 hover:text-white border border-mobile-200/80 text-[10px] font-extrabold transition-all active:scale-95 cursor-pointer mt-1.5 shadow-2xs"
                                            title="Preview Design">
                                            <i data-lucide="eye" class="w-3 h-3"></i>
                                            <span>Preview Design</span>
                                        </button>
                                    </div>
                                    <button @click="removeItem()"
                                        class="w-8 h-8 rounded-xl bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-all active:scale-90 flex-shrink-0">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between mt-3">
                                    <div
                                        class="flex items-center gap-1 bg-slate-50 p-0.5 rounded-xl border border-slate-100">
                                        <button @click="changeQty(qty - 1)" :disabled="qty <= 1 || loading"
                                            class="w-8 h-8 rounded-lg bg-white hover:bg-mobile-50 text-slate-600 hover:text-mobile-600 flex items-center justify-center transition-all active:scale-90 disabled:opacity-40 shadow-sm text-sm font-bold">
                                            −
                                        </button>
                                        <span class="w-8 text-center font-extrabold text-slate-900 text-sm"
                                            x-text="qty"></span>
                                        <button @click="changeQty(qty + 1)" :disabled="loading"
                                            class="w-8 h-8 rounded-lg bg-white hover:bg-mobile-50 text-slate-600 hover:text-mobile-600 flex items-center justify-center transition-all active:scale-90 disabled:opacity-40 shadow-sm text-sm font-bold">
                                            +
                                        </button>
                                    </div>
                                    <span class="font-extrabold text-slate-900 text-sm"
                                        x-text="__price(price * qty)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Order Summary --}}
            <div class="bg-white border-2 border-slate-50 rounded-[2rem] shadow-premium p-5 space-y-3">
                <div class="flex justify-between items-center text-slate-400 text-xs font-bold uppercase tracking-[0.2em]">
                    <span>Subtotal</span>
                    <span x-text="__price(subtotal)"></span>
                </div>
                <template x-if="discount > 0">
                    <div
                        class="flex justify-between items-center  text-gray-600 text-xs font-bold uppercase tracking-[0.2em]">
                        <span x-text="'Discount (' + appliedCoupon + ')'"></span>
                        <span x-text="'-' + __price(discount)"></span>
                    </div>
                </template>
                <div class="flex justify-between items-center border-t border-slate-100 pt-3">
                    <span class="text-lg font-black text-slate-900">Total</span>
                    <span class="text-2xl font-black text-mobile-600" x-text="__price(total)"></span>
                </div>
            </div>

            <a href="{{ route($routePrefix . 'index') }}"
                class="flex items-center justify-center gap-2 text-sm font-bold text-slate-500 hover:text-mobile-600 transition-colors py-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Add More Items
            </a>

            <div
                class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 glass border-t border-slate-100 safe-bottom z-50">
                <a href="{{ route($routePrefix . 'cart-checkout') }}"
                    class="w-full bg-mobile-500 hover:bg-mobile-600 text-white font-extrabold py-3.5 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    <span>Proceed to Checkout — <span x-text="__price(total)"></span></span>
                </a>
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-[2rem] border-2 border-slate-50 shadow-premium">
                <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-5">
                    <i data-lucide="shopping-bag" class="w-8 h-8 text-slate-300"></i>
                </div>
                <h2 class="font-extrabold text-lg text-slate-700 mb-2">Your cart is empty</h2>
                <p class="text-slate-400 text-sm font-medium mb-6">Start creating your custom prints!</p>
                <a href="{{ route($routePrefix . 'index') }}"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-mobile-600 text-white font-extrabold rounded-2xl hover:bg-mobile-700 transition shadow-lg shadow-mobile-100 active:scale-[0.97]">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Start Creating
                </a>
            </div>
        @endif
        {{-- Design Preview Flipbook Modal --}}
        <template x-teleport="body">
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
        </template>
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
                    if (!data) return;
                    this.subtotal = parseFloat(data.subtotal || 0);
                    this.discount = parseFloat(data.discount || 0);
                    this.total = parseFloat(data.total || 0);
                    this.itemCount = parseInt(data.cart_count || 0);

                    const pcCount = document.getElementById('cart-count-pc');
                    if (pcCount) pcCount.textContent = this.itemCount;
                    const mobCount = document.getElementById('cart-count');
                    if (mobCount) mobCount.textContent = this.itemCount;

                    if (data.cart_count === 0) {
                        setTimeout(() => location.reload(), 300);
                    }
                },

                async applyCoupon() {
                    if (!this.couponInput || this.appliedCoupon) return;
                    try {
                        const res = await fetch('{{ route($routePrefix . 'cart.apply-coupon') }}', {
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
                            this.discount = parseFloat(data.discount);
                            this.total = Math.max(0, this.subtotal - this.discount);
                            this.couponMessage = data.message;
                        } else {
                            this.couponMessage = data.message;
                        }
                    } catch (e) {
                        this.couponMessage = 'Error applying coupon.';
                    }
                },

                async removeCoupon() {
                    try {
                        await fetch('{{ route($routePrefix . 'cart.remove-coupon') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        this.appliedCoupon = null;
                        this.discount = 0;
                        this.total = this.subtotal;
                        this.couponInput = '';
                        this.couponMessage = '';
                    } catch (e) {
                        console.error(e);
                    }
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

                updateCartPage(data) {
                    const page = document.querySelector('[x-data^="cartPage"]');
                    if (page) {
                        if (window.Alpine && window.Alpine.$data) {
                            const pageData = window.Alpine.$data(page);
                            if (pageData && typeof pageData.updateFromResponse === 'function') {
                                pageData.updateFromResponse(data);
                            }
                        } else if (page.__x && page.__x.$data) {
                            page.__x.$data.subtotal = parseFloat(data.subtotal);
                            page.__x.$data.discount = parseFloat(data.discount);
                            page.__x.$data.total = parseFloat(data.total);
                            page.__x.$data.itemCount = parseInt(data.cart_count);
                        }
                    }
                    window.dispatchEvent(new CustomEvent('cart-updated', {
                        detail: data
                    }));
                },

                async changeQty(newQty) {
                    if (newQty < 1 || this.loading) return;
                    this.loading = true;
                    try {
                        const res = await fetch(
                            `{{ url(str_starts_with($routePrefix, 'flow-pc') ? 'pc/cart/update' : 'cart/update') }}/${this.id}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    quantity: newQty
                                })
                            });
                        const data = await res.json();
                        if (data.success) {
                            this.qty = newQty;
                            this.updateCartPage(data);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.loading = false;
                },

                async removeItem() {
                    this.loading = true;
                    try {
                        const res = await fetch(
                            `{{ url(str_starts_with($routePrefix, 'flow-pc') ? 'pc/cart/remove' : 'cart/remove') }}/${this.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                        const data = await res.json();
                        if (data.success) {
                            this.removed = true;
                            this.updateCartPage(data);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                    this.loading = false;
                }
            }
        }
    </script>
@endpush
