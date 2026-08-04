@extends('layouts.quick-flow')

@section('title', 'My Cart')
@section('header_title', 'My Cart')

@push('styles')
<style>
    .cart-item-enter { animation: slideIn 0.3s ease-out; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .cart-item-remove { animation: slideOut 0.3s ease-in forwards; }
    @keyframes slideOut { to { opacity: 0; transform: translateX(60px); height: 0; margin: 0; padding: 0; overflow: hidden; } }
</style>
@endpush

@section('content')
<div x-data="cartPage()" class="space-y-5 pb-48">
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

    @if($cart->items->count() > 0)
        {{-- Cart Items --}}
        <div class="space-y-3">
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
                <div class="bg-white border-2 border-slate-50 rounded-[2rem] shadow-premium overflow-hidden cart-item-enter"
                     x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})"
                     x-show="!removed" x-transition>
                    <div class="p-4 flex gap-4">
                        <div class="w-20 h-20 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 border border-slate-100">
                            @if($thumbUrl)
                                <img src="{{ $thumbUrl }}" alt="{{ $item->product->name ?? 'Design' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i data-lucide="image" class="w-7 h-7 text-slate-300"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    @if(!empty($customization['type_name']))
                                        <span class="text-[9px] font-black text-brand-600 uppercase tracking-[0.15em]">{{ $customization['type_name'] }}</span>
                                    @endif
                                    <h3 class="font-extrabold text-sm text-slate-900 leading-tight truncate">
                                        {{ $item->product->name ?? 'Custom Print' }}
                                    </h3>
                                    @if(!empty($customization['size_name']))
                                        <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                                            {{ $customization['size_name'] }}
                                            @if(!empty($customization['size_width']) && !empty($customization['size_height']))
                                                · {{ $customization['size_width'] }}×{{ $customization['size_height'] }}{{ $customization['size_unit'] ?? '' }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <button @click="removeItem()"
                                    class="w-8 h-8 rounded-xl bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-all active:scale-90 flex-shrink-0">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center gap-1 bg-slate-50 p-0.5 rounded-xl border border-slate-100">
                                    <button @click="changeQty(qty - 1)" :disabled="qty <= 1 || loading"
                                        class="w-8 h-8 rounded-lg bg-white hover:bg-brand-50 text-slate-600 hover:text-brand-600 flex items-center justify-center transition-all active:scale-90 disabled:opacity-40 shadow-sm text-sm font-bold">
                                        −
                                    </button>
                                    <span class="w-8 text-center font-extrabold text-slate-900 text-sm" x-text="qty"></span>
                                    <button @click="changeQty(qty + 1)" :disabled="loading"
                                        class="w-8 h-8 rounded-lg bg-white hover:bg-brand-50 text-slate-600 hover:text-brand-600 flex items-center justify-center transition-all active:scale-90 disabled:opacity-40 shadow-sm text-sm font-bold">
                                        +
                                    </button>
                                </div>
                                <span class="font-extrabold text-slate-900 text-sm" x-text="__price(price * qty)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Coupon Section --}}
        <div class="bg-white border-2 border-slate-50 rounded-[2rem] shadow-premium p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Promo Code</span>
                <template x-if="appliedCoupon">
                    <button @click="removeCoupon()" class="text-[10px] font-black text-red-500 uppercase hover:text-red-600 transition-colors">Remove</button>
                </template>
            </div>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <i data-lucide="ticket" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" x-model="couponInput" :disabled="appliedCoupon" placeholder="Enter code"
                        class="w-full bg-slate-50 border-2 border-transparent focus:border-brand-500 rounded-xl py-2.5 pl-10 pr-3 text-sm font-bold uppercase transition-all outline-none"
                        @keydown.enter.prevent="applyCoupon()">
                </div>
                <button @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                    class="px-4 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-brand-600 disabled:opacity-50 transition-all active:scale-95">
                    Apply
                </button>
            </div>
            <p x-show="couponMessage" x-text="couponMessage"
                :class="appliedCoupon ? 'text-emerald-600' : 'text-red-500'"
                class="text-[10px] font-bold mt-2 ml-1" style="display:none"></p>
        </div>

        {{-- Order Summary --}}
        <div class="bg-white border-2 border-slate-50 rounded-[2rem] shadow-premium p-5 space-y-3">
            <div class="flex justify-between items-center text-slate-400 text-xs font-bold uppercase tracking-[0.2em]">
                <span>Subtotal</span>
                <span x-text="__price(subtotal)"></span>
            </div>
            <template x-if="discount > 0">
                <div class="flex justify-between items-center text-emerald-600 text-xs font-bold uppercase tracking-[0.2em]">
                    <span x-text="'Discount (' + appliedCoupon + ')'"></span>
                    <span x-text="'-' + __price(discount)"></span>
                </div>
            </template>
            <div class="flex justify-between items-center border-t border-slate-100 pt-3">
                <span class="text-lg font-black text-slate-900">Total</span>
                <span class="text-2xl font-black text-brand-600" x-text="__price(total)"></span>
            </div>
        </div>

        <a href="{{ route($routePrefix . 'index') }}"
            class="flex items-center justify-center gap-2 text-sm font-bold text-slate-500 hover:text-brand-600 transition-colors py-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Add More Items
        </a>

        <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 glass border-t border-slate-100 safe-bottom z-50">
            <a href="{{ route($routePrefix . 'cart-checkout') }}"
                class="w-full bg-brand-500 hover:bg-brand-600 text-white font-extrabold py-3.5 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base">
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
                class="inline-flex items-center gap-2 px-8 py-3 bg-brand-600 text-white font-extrabold rounded-2xl hover:bg-brand-700 transition shadow-lg shadow-brand-100 active:scale-[0.97]">
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
