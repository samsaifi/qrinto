@extends('layouts.quick-flow-pc')

@section('title', 'Shopping Cart — Qrinto Print Studio')

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
</style>
@endpush

@section('content')
<div x-data="cartPage()" class="ambient-bg min-h-screen py-8 -mt-6">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Top Navigation & Step Indicator Bar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route($routePrefix . 'index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
                </a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <span class="text-slate-900 font-bold">Shopping Cart</span>
            </nav>

            {{-- Checkout Step Progress Bar --}}
            <div class="flex items-center gap-2 bg-white/80 backdrop-blur-md px-4 py-2 rounded-full border border-slate-200/80 shadow-2xs">
                <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]">✓</span>
                    <span>Customize</span>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <div class="flex items-center gap-1.5 text-xs font-black text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200/60">
                    <span class="w-5 h-5 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px]">2</span>
                    <span>Review Cart</span>
                </div>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                    <span class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px]">3</span>
                    <span>Checkout</span>
                </div>
            </div>
        </div>

        {{-- Main Page Title Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-slate-200/70 shadow-xs">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gradient-to-r from-brand-50 to-indigo-50 border border-brand-100 text-brand-600 text-xs font-extrabold mb-1">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Qrinto Print Studio Order
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Shopping Cart</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-sm font-extrabold text-slate-900">
                        <span x-text="itemCount"></span> item<span x-show="itemCount !== 1">s</span> selected
                    </div>
                    <div class="text-xs text-slate-400 font-semibold">Ready for instant printing</div>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <a href="{{ route($routePrefix . 'index') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-brand-600 text-white text-xs font-bold transition-all shadow-sm hover:shadow-md active:scale-95">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Add More Designs
                </a>
            </div>
        </div>

        @if($cart->items->count() > 0)
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                {{-- Left Column: Cart Items List --}}
                <div class="w-full flex-1 min-w-0 space-y-4">
                    
                    {{-- Table Header Bar (Desktop Only) --}}
                    <div class="hidden lg:grid grid-cols-[1fr_160px_140px_48px] gap-6 px-6 py-3 bg-slate-100/70 backdrop-blur-xs rounded-2xl border border-slate-200/80 text-[11px] font-black text-slate-500 uppercase tracking-widest">
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
                            $firstUploadId = !empty($uploadIds) ? reset($uploadIds) : null;
                            $thumbUrl = null;
                            if ($firstUploadId && isset($uploads[$firstUploadId])) {
                                $thumbUrl = $uploads[$firstUploadId]->url;
                            } elseif ($item->product && $item->product->frame_image_url) {
                                $thumbUrl = $item->product->frame_image_url;
                            }
                        @endphp

                        <div class="glass-card border border-slate-200/90 rounded-3xl p-5 lg:p-6 shadow-sm hover:shadow-xl hover:border-brand-300 transition-all duration-300 cart-item-enter relative group overflow-hidden"
                             x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }})"
                             x-show="!removed" x-transition>
                            
                            {{-- Top Accent Indicator Bar --}}
                            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-500 via-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                            <div class="grid grid-cols-1 lg:grid-cols-[1fr_160px_140px_48px] gap-5 lg:gap-6 items-center">
                                
                                {{-- Product info & Thumbnail --}}
                                <div class="flex items-center gap-4 sm:gap-6">
                                    
                                    {{-- Thumbnail Wrapper with Ambient Glow --}}
                                    <div class="w-20 h-20 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-slate-50 border border-slate-200/80 shrink-0 relative group-hover:border-brand-300 transition-all duration-300 shadow-sm group-hover:shadow-md">
                                        @if($thumbUrl)
                                            <img src="{{ $thumbUrl }}" alt="{{ $item->product->name ?? 'Design Preview' }}" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500">
                                            <div class="absolute bottom-1 right-1 bg-slate-900/80 backdrop-blur-xs text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs">
                                                HD PRINT
                                            </div>
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                                                <i data-lucide="image" class="w-8 h-8 mb-1"></i>
                                                <span class="text-[9px] font-bold text-slate-400">Custom Design</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Details --}}
                                    <div class="min-w-0 flex-1">
                                        @if(!empty($customization['type_name']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-600 border border-brand-100 text-[10px] font-black uppercase tracking-wider mb-1.5">
                                                <i data-lucide="tag" class="w-3 h-3"></i>
                                                {{ $customization['type_name'] }}
                                            </span>
                                        @endif
                                        
                                        <h3 class="font-black text-slate-900 text-base sm:text-lg leading-snug group-hover:text-brand-600 transition-colors truncate">
                                            {{ $item->product->name ?? 'Custom Print' }}
                                        </h3>

                                        {{-- Specifications Badges --}}
                                        <div class="flex items-center gap-2 mt-2 flex-wrap">
                                            @if(!empty($customization['size_name']))
                                                <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200/80">
                                                    <i data-lucide="ruler" class="w-3.5 h-3.5 text-slate-400"></i>
                                                    {{ $customization['size_name'] }}
                                                    @if(!empty($customization['size_width']) && !empty($customization['size_height']))
                                                        <span class="text-slate-500 font-medium">({{ $customization['size_width'] }}×{{ $customization['size_height'] }}{{ $customization['size_unit'] ?? '' }})</span>
                                                    @endif
                                                </span>
                                            @endif
                                            
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/60">
                                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-500"></i> High Resolution
                                            </span>
                                        </div>

                                        <div class="mt-2.5 text-xs font-semibold text-slate-500 flex items-center gap-2">
                                            <span>Unit Price: <strong class="text-slate-900 font-extrabold" x-text="__price(price)"></strong></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quantity Stepper --}}
                                <div class="flex flex-col items-center justify-center border-t lg:border-t-0 border-slate-100 pt-3 lg:pt-0">
                                    <span class="lg:hidden text-xs font-bold text-slate-400 uppercase mb-2">Quantity</span>
                                    <div class="inline-flex items-center bg-slate-100/80 p-1 rounded-2xl border border-slate-200/80 shadow-inner">
                                        <button type="button" @click="changeQty(qty - 1)" :disabled="qty <= 1 || loading"
                                            class="w-9 h-9 rounded-xl bg-white text-slate-800 hover:bg-brand-600 hover:text-white flex items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:hover:bg-white shadow-2xs font-black text-base border border-slate-200/80 active:scale-95">
                                            −
                                        </button>
                                        <span class="w-12 text-center font-black text-slate-900 text-base" x-text="qty"></span>
                                        <button type="button" @click="changeQty(qty + 1)" :disabled="loading"
                                            class="w-9 h-9 rounded-xl bg-white text-slate-800 hover:bg-brand-600 hover:text-white flex items-center justify-center transition-all duration-200 disabled:opacity-30 disabled:hover:bg-white shadow-2xs font-black text-base border border-slate-200/80 active:scale-95">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Item Total Price --}}
                                <div class="flex items-center justify-between lg:justify-end border-t lg:border-t-0 border-slate-100 pt-3 lg:pt-0">
                                    <span class="lg:hidden text-xs font-bold text-slate-400 uppercase">Item Total</span>
                                    <div class="text-right">
                                        <span class="font-black text-xl text-slate-900" x-text="__price(price * qty)"></span>
                                    </div>
                                </div>

                                {{-- Remove Button --}}
                                <div class="flex justify-end lg:justify-center">
                                    <button type="button" @click="removeItem()"
                                        class="w-10 h-10 rounded-2xl bg-slate-100/80 hover:bg-red-50 text-slate-400 hover:text-red-500 flex items-center justify-center transition-all duration-200 border border-slate-200/80 hover:border-red-200 group/btn"
                                        title="Remove item from cart">
                                        <i data-lucide="trash-2" class="w-4 h-4 group-hover/btn:scale-110 transition-transform"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @endforeach

                    {{-- Guarantee Banner Below Items --}}
                    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-amber-400 shrink-0">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-sm text-white">Qrinto 100% Print Guarantee</h4>
                                <p class="text-xs text-slate-300 font-medium">Free reprint or 100% refund if you are not satisfied with your print quality.</p>
                            </div>
                        </div>
                        <a href="{{ route($routePrefix . 'index') }}" class="shrink-0 text-xs font-extrabold text-brand-300 hover:text-white transition-colors flex items-center gap-1">
                            Continue Shopping <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                {{-- Right Column: Order Summary Sidebar (The Hero Card) --}}
                <div class="w-full lg:w-[400px] shrink-0 sticky top-24">
                    <div class="glass-card border border-slate-200/90 rounded-3xl p-6 lg:p-7 shadow-2xl shadow-slate-200/50 relative overflow-hidden">
                        
                        {{-- Top Decorative Gradient Line --}}
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-500 via-indigo-500 to-purple-600"></div>

                        {{-- Summary Header --}}
                        <div class="flex items-center justify-between pb-5 border-b border-slate-200/80 mb-5">
                            <h2 class="text-xl font-black text-slate-900 tracking-tight">Order Summary</h2>
                            <span class="inline-flex items-center gap-1 text-[11px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i> 256-Bit SSL
                            </span>
                        </div>

                        {{-- Free Shipping Gamified Progress Bar --}}
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl p-3.5 mb-5 shadow-2xs">
                            <div class="flex items-center justify-between text-xs font-extrabold text-amber-900 mb-1.5">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-600"></i> Priority Delivery
                                </span>
                                <span class="text-amber-700">Express Door Shipping</span>
                            </div>
                            <div class="w-full bg-amber-200/60 rounded-full h-2 overflow-hidden p-0.5">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-full rounded-full w-full"></div>
                            </div>
                        </div>

                        {{-- Promo Code Input Box --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-black text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="ticket" class="w-3.5 h-3.5 text-brand-500"></i> Promo Code
                                </label>
                                <template x-if="appliedCoupon">
                                    <button type="button" @click="removeCoupon()" class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors">
                                        Remove Code
                                    </button>
                                </template>
                            </div>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" x-model="couponInput" :disabled="appliedCoupon" placeholder="Enter code"
                                        class="w-full bg-white border border-slate-200 focus:border-brand-500 rounded-xl py-2.5 px-3 text-xs font-bold uppercase tracking-wider transition-all outline-none focus:ring-2 focus:ring-brand-100 disabled:bg-slate-100 text-slate-800 shadow-2xs"
                                        @keydown.enter.prevent="applyCoupon()">
                                </div>
                                <button type="button" @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                                    class="px-5 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-brand-600 disabled:opacity-40 transition-all shadow-md active:scale-95">
                                    Apply
                                </button>
                            </div>
                            <p x-show="couponMessage" x-text="couponMessage"
                                :class="appliedCoupon ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-red-500 bg-red-50 border-red-200'"
                                class="text-xs font-bold mt-2.5 p-2 rounded-xl border" style="display:none"></p>
                        </div>

                        {{-- Totals Breakdown --}}
                        <div class="space-y-3 pb-6 border-b border-slate-200/80">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-semibold">Subtotal</span>
                                <span class="font-extrabold text-slate-800" x-text="__price(subtotal)"></span>
                            </div>

                            <template x-if="discount > 0">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-emerald-600 font-bold flex items-center gap-1">
                                        <i data-lucide="tag" class="w-3.5 h-3.5"></i> Discount (<span x-text="appliedCoupon"></span>)
                                    </span>
                                    <span class="font-extrabold text-emerald-600" x-text="'-' + __price(discount)"></span>
                                </div>
                            </template>

                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500 font-semibold">Standard Shipping</span>
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60">Calculated at Checkout</span>
                            </div>
                        </div>

                        {{-- Dark Luxury Grand Total Card --}}
                        <div class="my-5 bg-slate-900 text-white rounded-2xl p-5 border border-slate-800 shadow-xl relative overflow-hidden">
                            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-brand-500/20 rounded-full blur-xl pointer-events-none"></div>
                            <div class="flex justify-between items-baseline mb-1 relative z-10">
                                <span class="text-sm font-bold text-slate-300">Total Amount</span>
                                <span class="text-3xl font-black text-white tracking-tight" x-text="__price(total)"></span>
                            </div>
                            <p class="text-[11px] font-medium text-slate-400 text-right relative z-10">Includes taxes & print setup</p>
                        </div>

                        {{-- Checkout CTA Button --}}
                        <div class="space-y-4">
                            <a href="{{ route($routePrefix . 'cart-checkout') }}"
                                class="shimmer-cta w-full bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 hover:from-brand-600 hover:to-indigo-600 text-white font-black py-4 px-6 rounded-2xl shadow-xl shadow-slate-900/20 hover:shadow-brand-500/30 transition-all duration-300 flex items-center justify-center gap-3 text-base tracking-wide active:scale-[0.99] cursor-pointer">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                                Proceed to Checkout
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </a>

                            {{-- Security & Trust Highlights --}}
                            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-[11px] font-semibold text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                    <span>Print Guarantee</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                                    <span>Express Shipping</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                    <span>Encrypted Payment</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="headphones" class="w-3.5 h-3.5 text-purple-500 shrink-0"></i>
                                    <span>24/7 Support</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        @else
            {{-- Empty Cart State --}}
            <div class="max-w-md mx-auto text-center py-20 px-8 bg-white/90 backdrop-blur-md rounded-3xl border border-slate-200/90 shadow-2xl shadow-slate-200/40">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-tr from-brand-50 via-indigo-50 to-purple-50 border border-brand-100 flex items-center justify-center mx-auto mb-6 shadow-inner relative">
                    <i data-lucide="shopping-bag" class="w-14 h-14 text-brand-500"></i>
                    <div class="absolute -top-1 -right-1 w-7 h-7 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-black shadow-md">0</div>
                </div>
                <h2 class="font-black text-2xl text-slate-900 mb-2">Your cart is empty</h2>
                <p class="text-slate-500 font-medium text-sm mb-8 leading-relaxed">You haven't added any custom print templates to your cart yet. Explore our templates and start customizing!</p>
                <a href="{{ route($routePrefix . 'index') }}"
                    class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-brand-600 hover:bg-brand-700 text-white font-black rounded-2xl transition shadow-xl shadow-brand-500/25 active:scale-95 text-base">
                    <i data-lucide="sparkles" class="w-5 h-5"></i> Start Creating Now
                </a>
            </div>
        @endif

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
