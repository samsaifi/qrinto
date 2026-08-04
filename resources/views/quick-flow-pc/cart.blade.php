@extends('layouts.quick-flow-pc')

@section('title', 'My Cart')

@push('styles')
<style>
    .cart-item-enter { animation: slideIn 0.35s cubic-bezier(0.16,1,0.3,1); }
    @keyframes slideIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .cart-item-remove { animation: fadeOut 0.25s ease-in forwards; }
    @keyframes fadeOut { to { opacity: 0; transform: scale(0.96); height: 0; margin: 0; padding: 0; overflow: hidden; } }
</style>
@endpush

@section('content')
<div x-data="cartPage()" class="py-8">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm mb-8">
        <a href="{{ route($routePrefix . 'index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
            <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
        <span class="text-slate-700 font-semibold">Shopping Cart</span>
    </nav>

    <h1 class="text-3xl font-extrabold text-slate-900 mb-1">Shopping Cart</h1>
    <p class="text-slate-500 font-medium mb-8">
        <span x-text="itemCount"></span> item<span x-show="itemCount !== 1">s</span> in your cart
    </p>

    @if($cart->items->count() > 0)
        <div class="flex gap-8 items-start">
            {{-- Left: Cart Items --}}
            <div class="flex-1 min-w-0 space-y-4">
                {{-- Table Header --}}
                <div class="hidden lg:grid grid-cols-[1fr_140px_140px_48px] gap-6 px-6 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <span>Product</span>
                    <span class="text-center">Quantity</span>
                    <span class="text-right">Total</span>
                    <span></span>
                </div>

                @foreach($cart->items as $item)
                    @php
                        $customization = $item->customization_data ?? [];
                        $uploadIds = $customization['upload_ids'] ?? [];
                        $firstUploadId = !empty($uploadIds) ? reset($uploadIds) : null;
                        $thumbUrl = null;
                        if ($firstUploadId && isset($uploads[$firstUploadId])) {
                            $thumbUrl = $uploads[$firstUploadId]->url;
                        } elseif ($item->product && $item->product->frame_image_url) {
                            $thumbUrl = $item->product->frame_image_url;
                        }
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden cart-item-enter hover:shadow-md transition-shadow"
                         x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})"
                         x-show="!removed" x-transition>
                        <div class="p-5 grid grid-cols-1 lg:grid-cols-[1fr_140px_140px_48px] gap-5 items-center">
                            {{-- Product Info --}}
                            <div class="flex items-center gap-5">
                                <div class="w-24 h-24 rounded-xl overflow-hidden bg-slate-50 flex-shrink-0 border border-slate-100">
                                    @if($thumbUrl)
                                        <img src="{{ $thumbUrl }}" alt="{{ $item->product->name ?? 'Design' }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i data-lucide="image" class="w-8 h-8 text-slate-300"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    @if(!empty($customization['type_name']))
                                        <span class="text-[10px] font-black text-brand-600 uppercase tracking-[0.15em]">{{ $customization['type_name'] }}</span>
                                    @endif
                                    <h3 class="font-bold text-base text-slate-900 leading-tight">
                                        {{ $item->product->name ?? 'Custom Print' }}
                                    </h3>
                                    @if(!empty($customization['size_name']))
                                        <p class="text-xs font-medium text-slate-400 mt-1">
                                            {{ $customization['size_name'] }}
                                            @if(!empty($customization['size_width']) && !empty($customization['size_height']))
                                                · {{ $customization['size_width'] }}×{{ $customization['size_height'] }}{{ $customization['size_unit'] ?? '' }}
                                            @endif
                                        </p>
                                    @endif
                                    <p class="text-sm font-bold text-slate-600 mt-1" x-text="__price(price)"></p>
                                </div>
                            </div>

                            {{-- Quantity Controls --}}
                            <div class="flex items-center justify-center">
                                <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-xl border border-slate-200">
                                    <button @click="changeQty(qty - 1)" :disabled="qty <= 1 || loading"
                                        class="w-9 h-9 rounded-lg bg-white hover:bg-brand-50 text-slate-600 hover:text-brand-600 flex items-center justify-center transition-all disabled:opacity-40 shadow-sm text-sm font-bold border border-slate-100">
                                        −
                                    </button>
                                    <span class="w-10 text-center font-extrabold text-slate-900 text-sm" x-text="qty"></span>
                                    <button @click="changeQty(qty + 1)" :disabled="loading"
                                        class="w-9 h-9 rounded-lg bg-white hover:bg-brand-50 text-slate-600 hover:text-brand-600 flex items-center justify-center transition-all disabled:opacity-40 shadow-sm text-sm font-bold border border-slate-100">
                                        +
                                    </button>
                                </div>
                            </div>

                            {{-- Line Total --}}
                            <div class="text-right">
                                <span class="font-extrabold text-lg text-slate-900" x-text="__price(price * qty)"></span>
                            </div>

                            {{-- Remove --}}
                            <div class="flex justify-center">
                                <button @click="removeItem()"
                                    class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition-all border border-slate-200 hover:border-red-200">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Continue Shopping --}}
                <div class="pt-4">
                    <a href="{{ route($routePrefix . 'index') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Continue Shopping
                    </a>
                </div>
            </div>

            {{-- Right: Order Summary Sidebar --}}
            <div class="w-[380px] flex-shrink-0 sticky top-24">
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-slate-100">
                        <h2 class="text-lg font-extrabold text-slate-900">Order Summary</h2>
                    </div>

                    {{-- Coupon --}}
                    <div class="p-6 border-b border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Promo Code</span>
                            <template x-if="appliedCoupon">
                                <button @click="removeCoupon()" class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors">Remove</button>
                            </template>
                        </div>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <i data-lucide="ticket" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                                <input type="text" x-model="couponInput" :disabled="appliedCoupon" placeholder="Enter code"
                                    class="w-full bg-slate-50 border border-slate-200 focus:border-brand-500 rounded-xl py-2.5 pl-10 pr-3 text-sm font-bold uppercase transition-all outline-none focus:ring-2 focus:ring-brand-100"
                                    @keydown.enter.prevent="applyCoupon()">
                            </div>
                            <button @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                                class="px-5 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-brand-600 disabled:opacity-40 transition-all">
                                Apply
                            </button>
                        </div>
                        <p x-show="couponMessage" x-text="couponMessage"
                            :class="appliedCoupon ? 'text-emerald-600' : 'text-red-500'"
                            class="text-xs font-medium mt-2" style="display:none"></p>
                    </div>

                    {{-- Totals --}}
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">Subtotal</span>
                            <span class="font-bold text-slate-700" x-text="__price(subtotal)"></span>
                        </div>
                        <template x-if="discount > 0">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-emerald-600 font-medium" x-text="'Discount (' + appliedCoupon + ')'"></span>
                                <span class="font-bold text-emerald-600" x-text="'-' + __price(discount)"></span>
                            </div>
                        </template>
                        <div class="flex justify-between items-center border-t border-slate-100 pt-4">
                            <span class="text-lg font-extrabold text-slate-900">Total</span>
                            <span class="text-2xl font-extrabold text-brand-600" x-text="__price(total)"></span>
                        </div>
                    </div>

                    {{-- Checkout Button --}}
                    <div class="p-6 pt-0">
                        <a href="{{ route($routePrefix . 'cart-checkout') }}"
                            class="w-full bg-slate-900 hover:bg-brand-600 text-white font-bold py-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2.5 text-base">
                            <i data-lucide="credit-card" class="w-5 h-5"></i>
                            Proceed to Checkout
                        </a>
                        <div class="flex items-center justify-center gap-2 mt-4 text-xs text-slate-400">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            <span>Secure checkout · PayPal or Cash</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- Empty Cart --}}
        <div class="text-center py-24 bg-white rounded-2xl border border-slate-200">
            <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-6">
                <i data-lucide="shopping-bag" class="w-10 h-10 text-slate-300"></i>
            </div>
            <h2 class="font-extrabold text-xl text-slate-700 mb-2">Your cart is empty</h2>
            <p class="text-slate-400 font-medium mb-8">Start creating your custom prints!</p>
            <a href="{{ route($routePrefix . 'index') }}"
                class="inline-flex items-center gap-2 px-10 py-3.5 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-100">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Start Creating
            </a>
        </div>
    @endif
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
