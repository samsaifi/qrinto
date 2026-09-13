@extends('layouts.quick-flow-pc')

@section('title', 'Shopping Cart | Qrinto Custom Print Studio')
@section('meta_robots', 'noindex, nofollow')

@php
    $routePrefix = $routePrefix ?? 'flow.';
@endphp

@section('content')
    <div x-data="cartPage()" @cart-updated.window="updateFromResponse($event.detail)"
        class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[1240px] mx-auto">

            {{-- Back + Title (single row on mobile) --}}
            <div class="flex items-center gap-2 mb-3 md:hidden">
                <a href="{{ route($routePrefix . 'index') }}"
                    class="text-[11px] font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">←</a>
                <h1 class="text-base font-extrabold text-[#112419] tracking-tight">Shopping Cart</h1>
                <span class="ml-auto text-[10px] font-bold text-slate-500 bg-white border border-slate-200/90 px-2 py-1 rounded-full shadow-2xs shrink-0">
                    <span x-text="itemCount"></span> <span x-text="itemCount === 1 ? 'item' : 'items'"></span>
                </span>
            </div>

            {{-- Desktop header --}}
            <div class="hidden md:block">
                <div class="mb-6">
                    <a href="{{ route($routePrefix . 'index') }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                        <span>← Continue Shopping</span>
                    </a>
                </div>
                <div class="mb-8 flex items-baseline justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">
                            Shopping Cart
                        </h1>
                        <p class="text-sm text-slate-500 font-normal mt-1.5">
                            Review your custom items before proceeding to checkout.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-white border border-slate-200/90 px-3 py-1.5 rounded-full shadow-2xs">
                        <span x-text="itemCount"></span> <span x-text="itemCount === 1 ? 'item' : 'items'"></span>
                    </span>
                </div>
            </div>

            @if ($cart->items->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    {{-- Left Column: Items List --}}
                    <div class="lg:col-span-8 space-y-4">
                        @foreach ($cart->items as $item)
                            @php
                                $customization = $item->customization_data ?? [];
                                $uploadIds = $customization['upload_ids'] ?? [];
                                $itemImageUrls = [];

                                $slots = ['frame_image', 'sample_image', 'background_image', 'overlay_image'];
                                $noOfPages = (int) ($item->product->no_of_pages ?? 1);
                                $activeSlots = $noOfPages <= 1 ? ['frame_image'] : ($noOfPages == 2 ? ['frame_image', 'sample_image'] : array_slice($slots, 0, min($noOfPages, 4)));

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

                                if (empty($itemImageUrls) && $item->product && $item->product->frame_image_url) {
                                    $itemImageUrls[] = $item->product->frame_image_url;
                                }

                                $firstImage = $itemImageUrls[0] ?? null;
                                $pageCount = count($itemImageUrls);
                            @endphp

                            <div x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})" x-show="!removed" x-transition
                                class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 group">
                                
                                {{-- Left Info & Thumbnail --}}
                                <div class="flex items-center gap-4 sm:gap-6 flex-1 min-w-0">
                                    {{-- Thumbnail Box --}}
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-[#f2f7f2] rounded-2xl flex items-center justify-center p-2 relative overflow-hidden border border-slate-100 shrink-0">
                                        @if ($firstImage)
                                            <img src="{{ $firstImage }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover rounded-xl group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full bg-white rounded-xl border border-slate-200/80 flex items-center justify-center text-slate-400 font-bold text-xs">
                                                Card
                                            </div>
                                        @endif
                                        @if ($pageCount > 1)
                                            <span class="absolute bottom-1 right-1 bg-slate-900/90 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md backdrop-blur-xs">
                                                {{ $pageCount }} Pages
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Info Details --}}
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-extrabold text-slate-900 text-base sm:text-lg group-hover:text-emerald-700 transition-colors truncate">
                                            {{ $item->product->name }}
                                        </h3>
                                        
                                        <p class="text-xs text-slate-500 font-medium mt-1">
                                            @if (isset($customization['size_title']) || isset($customization['size_width']))
                                                {{ $customization['size_title'] ?? '' }}
                                                @if (isset($customization['size_width'], $customization['size_height']))
                                                    ({{ $customization['size_width'] + 0 }} × {{ $customization['size_height'] + 0 }} in)
                                                @endif
                                            @else
                                                Standard Specification
                                            @endif
                                        </p>

                                        <div class="text-xs font-bold text-slate-900 mt-2">
                                            {{ \App\Services\CurrencyService::format($item->unit_price) }} each
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Controls: Qty Stepper + Total + Remove --}}
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-4 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    {{-- Qty Stepper --}}
                                    <div class="flex items-center gap-1 bg-slate-50 border border-slate-200/90 rounded-2xl p-1">
                                        <button type="button" @click="changeQty(qty - 1)" :disabled="qty <= 1 || loading"
                                            class="w-7 h-7 rounded-xl bg-white border border-slate-200/80 flex items-center justify-center text-slate-700 font-bold text-sm hover:bg-slate-100 disabled:opacity-40 cursor-pointer shadow-2xs transition-all">
                                            -
                                        </button>

                                        <span class="w-8 text-center text-xs font-extrabold text-slate-900" x-text="qty"></span>

                                        <button type="button" @click="changeQty(qty + 1)" :disabled="loading"
                                            class="w-7 h-7 rounded-xl bg-white border border-slate-200/80 flex items-center justify-center text-slate-700 font-bold text-sm hover:bg-slate-100 disabled:opacity-40 cursor-pointer shadow-2xs transition-all">
                                            +
                                        </button>
                                    </div>

                                    {{-- Subtotal for Item --}}
                                    <div class="text-right">
                                        <div class="text-sm font-black text-slate-900" x-text="formatCurrency(qty * price)"></div>
                                    </div>

                                    {{-- Remove Button --}}
                                    <button type="button" @click="removeItem()" :disabled="loading"
                                        class="text-slate-400 hover:text-rose-600 transition-colors p-1.5 rounded-xl hover:bg-rose-50 cursor-pointer" title="Remove item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Right Column: Order Summary --}}
                    <div class="lg:col-span-4">
                        <div class="bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-2xs space-y-6">
                            <h2 class="font-extrabold text-slate-900 text-lg pb-4 border-b border-slate-100">
                                Order Summary
                            </h2>

                            {{-- Summary Lines --}}
                            <div class="space-y-3 pt-2 text-xs font-medium text-slate-600">
                                <div class="flex justify-between items-center">
                                    <span>Subtotal</span>
                                    <span class="font-bold text-slate-900" x-text="formatCurrency(subtotal)"></span>
                                </div>

                                <div x-show="discount > 0" class="flex justify-between items-center text-emerald-700 font-semibold">
                                    <span>Discount</span>
                                    <span>-<span x-text="formatCurrency(discount)"></span></span>
                                </div>

                                <div class="flex justify-between items-center pt-3 border-t border-slate-100 text-base font-black text-slate-900">
                                    <span>Total</span>
                                    <span x-text="formatCurrency(total)"></span>
                                </div>
                            </div>

                            {{-- Checkout Button --}}
                            <div class="pt-2">
                                <a href="{{ route($routePrefix . 'cart-checkout') }}"
                                    class="block w-full bg-[#287d3c] hover:bg-emerald-800 text-white text-center font-bold px-6 py-3.5 rounded-xl text-sm transition-all shadow-2xs active:scale-95">
                                    Proceed to Checkout →
                                </a>
                            </div>

                            {{-- Pickup Info Note --}}
                            <p class="text-[11px] text-slate-400 text-center font-normal">
                                Free instant store pickup included on all orders.
                            </p>
                        </div>
                    </div>

                </div>
            @else
                {{-- Empty Cart State --}}
                <div class="bg-white border border-slate-200/90 rounded-3xl p-12 sm:p-16 text-center max-w-xl mx-auto shadow-2xs my-8">
                    <div class="w-16 h-16 bg-[#f2f7f2] rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-800">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-extrabold text-[#112419]">
                        Your cart is empty
                    </h2>

                    <p class="text-sm text-slate-500 font-normal mt-2 max-w-md mx-auto">
                        Looks like you haven't added any custom card or magnet designs to your cart yet.
                    </p>

                    <div class="mt-8">
                        <a href="{{ route($routePrefix . 'index') }}"
                            class="inline-flex items-center gap-2 bg-[#287d3c] hover:bg-emerald-800 text-white font-bold px-6 py-3.5 rounded-xl text-sm transition-all shadow-2xs active:scale-95">
                            <span>Browse Products →</span>
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function formatCurrency(val) {
            return '$' + parseFloat(val || 0).toFixed(2);
        }

        function cartPage() {
            return {
                subtotal: {{ (float) $cart->subtotal }},
                discount: {{ (float) ($cart->discount ?? 0) }},
                total: {{ (float) $cart->total }},
                itemCount: {{ (int) $cart->items->count() }},
                couponInput: '{{ session('applied_coupon.code', '') }}',
                appliedCoupon: '{{ session('applied_coupon.code', '') }}',
                couponMessage: '',

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
                        setTimeout(() => location.reload(), 200);
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
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                },

                async changeQty(newQty) {
                    if (newQty < 1 || this.loading) return;
                    this.loading = true;
                    try {
                        const res = await fetch(`{{ url('/cart/update') }}/${this.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ quantity: newQty })
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
                        const res = await fetch(`{{ url('/cart/remove') }}/${this.id}`, {
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
