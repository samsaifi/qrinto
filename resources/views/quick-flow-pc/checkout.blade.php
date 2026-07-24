@extends('layouts.quick-flow-pc')

@section('title', 'Review & Pay')
@section('header_title', 'Review & Pay')

@push('styles')
<style>
    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 420px;
        gap: 32px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .checkout-grid { grid-template-columns: 1fr; }
    }

    .paypal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 80;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .paypal-sheet {
        width: 100%;
        max-width: 460px;
        background: #fff;
        border-radius: 1.5rem;
        padding: 2rem;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 25px 60px -12px rgba(0,0,0,0.25);
        animation: sheetIn 0.35s cubic-bezier(0.32, 0.72, 0, 1);
    }
    @keyframes sheetIn {
        from { transform: translateY(24px) scale(0.97); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }

    .processing-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255,255,255,0.97);
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
        border-top: 4px solid #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .success-check {
        width: 64px;
        height: 64px;
        background: #22c55e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes popIn {
        0% { transform: scale(0); }
        100% { transform: scale(1); }
    }

    .checkout-input {
        width: 100%;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.875rem;
        padding: 14px 16px 14px 48px;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
        outline: none;
        transition: all 0.2s ease;
    }
    .checkout-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        background: #fff;
    }
    .checkout-input::placeholder { color: #94a3b8; font-weight: 500; }

    .preview-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .preview-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
    }

    .fade-up {
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div x-data="checkoutFlow()" class="max-w-[1400px] mx-auto pb-32">

    {{-- ── Header ── --}}
    <div class="mb-8 fade-up">
        <nav class="flex items-center gap-2 text-sm mb-4">
            <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-indigo-600 transition-colors flex items-center gap-1.5">
                <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
            <span class="text-slate-700 font-semibold">Checkout</span>
        </nav>

        <div class="flex items-center gap-4">
            <a href="javascript:history.back()"
                class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center hover:bg-slate-50 hover:border-indigo-200 transition-all text-slate-500 hover:text-indigo-600">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <h1 class="text-2xl xl:text-3xl font-extrabold text-slate-900 tracking-tight">Review & Pay</h1>
                <p class="text-sm text-slate-500 mt-0.5">Review your custom design before payment.</p>
            </div>
        </div>
    </div>

    {{-- ── Two-Column Layout ── --}}
    <div class="checkout-grid">

        {{-- ═══ LEFT COLUMN ═══ --}}
        <div class="space-y-6">

            {{-- Design Preview --}}
            <div class="fade-up" style="animation-delay: 0.05s">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Design Preview</h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $types = [
                            'frame_image' => 'Page 1',
                            'sample_image' => 'Page 2',
                            'background_image' => 'Page 3',
                            'overlay_image' => 'Page 4'
                        ];
                    @endphp

                    @foreach($types as $key => $label)
                        @php
                            $uploadId = $uploadIds[$key] ?? null;
                            $currentUpload = ($uploadId && isset($uploads[$uploadId])) ? $uploads[$uploadId] : null;
                            $defaultUrl = $product->{$key . '_url'} ?? null;
                            $displayUrl = $currentUpload ? $currentUpload->url : $defaultUrl;
                        @endphp

                        @if($displayUrl)
                        <div class="preview-card bg-white border border-slate-200 rounded-2xl overflow-hidden">
                            <div class="relative bg-slate-50 overflow-hidden aspect-[4/5]">
                                <img src="{{ $displayUrl }}" class="w-full h-full object-cover" alt="{{ $label }}">
                                @if($currentUpload)
                                <div class="absolute top-2.5 right-2.5 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center shadow-sm">
                                    <i data-lucide="check" class="w-3 h-3 text-white"></i>
                                </div>
                                @endif
                            </div>
                            <div class="px-3.5 py-3 flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">{{ $label }}</span>
                                @if($currentUpload)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                    <i data-lucide="pen-tool" class="w-2.5 h-2.5"></i> Custom
                                </span>
                                @else
                                <span class="text-[10px] font-bold text-slate-400">Default</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Product Details --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden fade-up" style="animation-delay: 0.1s">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-1">{{ session('quick_flow_data.type_name', 'Custom Product') }}</p>
                            <h3 class="text-xl font-extrabold text-slate-900 leading-tight">{{ $product->name }}</h3>
                        </div>
                        <span class="inline-flex items-center bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-lg border border-indigo-100">
                            <span x-text="quantity"></span>&nbsp;Units
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
                            <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center border border-slate-200">
                                <i data-lucide="maximize" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Size</p>
                                <p class="text-sm font-bold text-slate-700">{{ session('quick_flow_data.size_name', 'Standard') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-3">
                            <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center border border-slate-200">
                                <i data-lucide="ruler" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dimensions</p>
                                <p class="text-sm font-bold text-slate-700">
                                    {{ session('quick_flow_data.size_width', '0') }}&times;{{ session('quick_flow_data.size_height', '0') }}{{ session('quick_flow_data.size_unit', '"') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quantity --}}
                <div class="border-t border-slate-100 px-6 py-4 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <p class="text-sm font-bold text-slate-700">Quantity</p>
                        <p class="text-xs text-slate-400 font-medium">Adjust number of prints</p>
                    </div>
                    <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1">
                        <button type="button" @click="quantity > 1 ? quantity-- : null"
                            class="w-9 h-9 rounded-lg bg-slate-50 hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 transition-all active:scale-90 flex items-center justify-center">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <div class="w-10 text-center font-extrabold text-slate-900 text-lg" x-text="quantity"></div>
                        <button type="button" @click="quantity++"
                            class="w-9 h-9 rounded-lg bg-slate-50 hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 transition-all active:scale-90 flex items-center justify-center">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Pickup Information --}}
            <div class="fade-up" style="animation-delay: 0.15s">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pickup Information</h2>
                </div>

                <div class="space-y-3">
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i data-lucide="user" class="w-4.5 h-4.5"></i>
                        </div>
                        <input type="text" x-model="pickupName" class="checkout-input" placeholder="Full name for pickup">
                    </div>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i data-lucide="mail" class="w-4.5 h-4.5"></i>
                        </div>
                        <input type="email" x-model="pickupEmail" class="checkout-input" placeholder="Email address (for order updates)">
                    </div>
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <i data-lucide="phone" class="w-4.5 h-4.5"></i>
                        </div>
                        <input type="tel" x-model="contactNumber" class="checkout-input" placeholder="Contact number">
                    </div>
                </div>
            </div>

            {{-- Security Notice --}}
            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100 fade-up" style="animation-delay: 0.2s">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center border border-slate-200 flex-shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5 text-indigo-500"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Secure Payment</p>
                    <p class="text-xs text-slate-400 mt-0.5">Pay safely with PayPal, cards, or cash at counter.</p>
                </div>
            </div>
        </div>

        {{-- ═══ RIGHT COLUMN: Order Summary ═══ --}}
        <div class="lg:sticky lg:top-20 space-y-5">

            {{-- Order Summary Card --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden fade-up" style="animation-delay: 0.1s">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order Summary</h2>
                    </div>
                </div>

                {{-- Coupon --}}
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-500">Promo Code</span>
                        <template x-if="appliedCoupon">
                            <button @click="removeCoupon()" class="text-[11px] font-bold text-red-500 hover:text-red-600 transition-colors">Remove</button>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="ticket" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" x-model="couponInput" :disabled="appliedCoupon"
                                placeholder="Enter code"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-bold uppercase transition-all outline-none focus:border-indigo-500 focus:ring-0"
                                @keydown.enter.prevent="applyCoupon()">
                        </div>
                        <button type="button" @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                            class="px-4 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-indigo-600 disabled:opacity-40 transition-all active:scale-95">
                            Apply
                        </button>
                    </div>
                    <p x-show="couponMessage" x-text="couponMessage"
                        :class="appliedCoupon ? 'text-emerald-600' : 'text-red-500'"
                        class="text-[11px] font-bold mt-2" style="display:none"></p>
                </div>

                {{-- Price Breakdown --}}
                <div class="p-5 space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Subtotal</span>
                        <span class="text-slate-700 font-semibold" x-text="__price(unitPrice * quantity)"></span>
                    </div>

                    <template x-if="discountAmount > 0">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-emerald-600 font-medium" x-text="'Discount (' + appliedCoupon + ')'"></span>
                            <span class="text-emerald-600 font-semibold" x-text="'-' + __price(discountAmount)"></span>
                        </div>
                    </template>

                    <div class="border-t border-slate-100 pt-4 flex justify-between items-center">
                        <span class="text-lg font-extrabold text-slate-900">Total</span>
                        <span class="text-2xl font-extrabold text-indigo-600" x-text="__price(calculateTotal())"></span>
                    </div>
                </div>
            </div>

            {{-- Terms & Payment Buttons --}}
            <div class="space-y-4 fade-up" style="animation-delay: 0.15s">
                <label class="flex items-start gap-3 cursor-pointer select-none p-3 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-200 transition-colors">
                    <input type="checkbox" x-model="acceptedTerms" id="terms-checkbox"
                        class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mt-0.5 cursor-pointer flex-shrink-0">
                    <span class="text-xs font-medium text-slate-600 leading-relaxed">
                        I have read and accept the <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank" class="text-indigo-600 font-semibold underline underline-offset-2">Terms and Conditions</a>
                    </span>
                </label>

                <button type="button"
                    @click="openPaypal()"
                    :disabled="!pickupName || !contactNumber || !pickupEmail || !acceptedTerms"
                    class="w-full bg-indigo-600 disabled:bg-slate-200 disabled:text-slate-400 hover:bg-indigo-700 text-white font-extrabold py-4 rounded-2xl shadow-lg shadow-indigo-600/20 disabled:shadow-none transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-[15px]">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                    <span x-text="pickupName && contactNumber && pickupEmail && acceptedTerms ? 'Pay Now — ' + __price(calculateTotal()) : (acceptedTerms ? 'Complete All Fields' : 'Accept Terms to Continue')"></span>
                </button>

                <button type="button"
                    @click="payByCash()"
                    :disabled="!pickupName || !contactNumber || !pickupEmail || !acceptedTerms"
                    class="w-full bg-white disabled:bg-slate-50 disabled:text-slate-300 border border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-4 rounded-2xl transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-[15px]">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                    <span>Pay by Cash at Counter</span>
                </button>
            </div>

            {{-- Trust Badges --}}
            <div class="grid grid-cols-3 gap-3 fade-up" style="animation-delay: 0.2s">
                <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 mx-auto mb-1.5"></i>
                    <p class="text-[10px] font-bold text-slate-500">Secure</p>
                </div>
                <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <i data-lucide="shield-check" class="w-4 h-4 text-slate-400 mx-auto mb-1.5"></i>
                    <p class="text-[10px] font-bold text-slate-500">Protected</p>
                </div>
                <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-slate-400 mx-auto mb-1.5"></i>
                    <p class="text-[10px] font-bold text-slate-500">Refundable</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ PayPal Modal ═══ --}}
    <template x-teleport="body">
        <div x-cloak>
            <div x-show="showPaypal" class="paypal-overlay" @click.self="showPaypal = false">
                <div class="paypal-sheet" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                                <i data-lucide="credit-card" class="w-5 h-5 text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900">Pay with PayPal</h3>
                                <p class="text-xs text-slate-400 font-medium">Secure checkout</p>
                            </div>
                        </div>
                        <button @click="showPaypal = false"
                            class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 mb-5 flex items-center justify-between border border-slate-100">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Amount</p>
                            <p class="text-2xl font-extrabold text-slate-900" x-text="__price(calculateTotal())"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pickup</p>
                            <p class="text-sm font-bold text-slate-700" x-text="pickupName"></p>
                        </div>
                    </div>

                    <div id="paypal-button-container" class="mb-3"></div>

                    <p class="text-center text-xs text-slate-400 font-medium mt-3 flex items-center justify-center gap-1.5">
                        <i data-lucide="lock" class="w-3 h-3"></i>
                        Payments processed securely by PayPal
                    </p>
                </div>
            </div>

            <div x-show="isProcessing" class="processing-overlay" x-cloak>
                <template x-if="!paymentSuccess">
                    <div class="text-center">
                        <div class="spinner mx-auto mb-4"></div>
                        <h3 class="text-xl font-extrabold text-slate-900">Processing Payment</h3>
                        <p class="text-slate-500 font-medium text-sm mt-1">Please wait while we confirm your order...</p>
                    </div>
                </template>
                <template x-if="paymentSuccess">
                    <div class="text-center">
                        <div class="success-check mx-auto mb-4">
                            <i data-lucide="check" class="w-8 h-8 text-white"></i>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900">Payment Successful!</h3>
                        <p class="text-slate-500 font-medium text-sm mt-1">Redirecting to your order...</p>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientId; ?>&currency=<?php echo \App\Services\CurrencyService::getCode(); ?>&intent=capture"></script>
<script>
    function checkoutFlow() {
        return {
            unitPrice: {{ $unitPrice }},
            quantity: {{ $quantity }},
            pickupName: '',
            pickupEmail: '',
            contactNumber: '',
            showPaypal: false,
            isProcessing: false,
            paymentSuccess: false,
            paypalRendered: false,
            acceptedTerms: false,

            couponInput: '',
            appliedCoupon: null,
            discountAmount: 0,
            couponMessage: '',

            calculateTotal() {
                const subtotal = this.unitPrice * this.quantity;
                return Math.max(0, subtotal - this.discountAmount).toFixed(2);
            },

            applyCoupon() {
                if (!this.couponInput || this.appliedCoupon) return;

                const subtotal = this.unitPrice * this.quantity;

                fetch('<?php echo route(Route::currentRouteName() === "flow-pc.checkout" ? "flow-pc.apply-coupon" : "flow.apply-coupon"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>',
                    },
                    body: JSON.stringify({
                        code: this.couponInput,
                        amount: subtotal
                    }),
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        this.appliedCoupon = result.code;
                        this.discountAmount = parseFloat(result.discount);
                        this.couponMessage = result.message;
                    } else {
                        this.couponMessage = result.message;
                        this.discountAmount = 0;
                        this.appliedCoupon = null;
                    }
                })
                .catch(err => {
                    console.error('Coupon error:', err);
                    this.couponMessage = 'Error applying coupon.';
                });
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

                fetch('<?php echo route("flow-pc.checkout.cash"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>',
                    },
                    body: JSON.stringify({
                        product_id: <?php echo $product->id; ?>,
                        quantity: this.quantity,
                        message: <?php echo json_encode($message); ?>,
                        style_data: <?php echo json_encode(json_encode($styleData)); ?>,
                        upload_ids: <?php echo json_encode($uploadIds); ?>,
                        pickup_name: this.pickupName,
                        pickup_email: this.pickupEmail,
                        contact_number: this.contactNumber,
                        coupon_code: this.appliedCoupon,
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
                        alert(result.error || result.message || 'Failed to process order. Please try again.');
                    }
                })
                .catch(err => {
                    console.error('Checkout error:', err);
                    this.isProcessing = false;
                    alert('An error occurred. Please try again.');
                });
            },

            renderPaypalButtons() {
                const self = this;
                const container = document.getElementById('paypal-button-container');
                if (!container) return;

                paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color: 'gold',
                        shape: 'rect',
                        label: 'paypal',
                        height: 48,
                    },

                    createOrder: function(data, actions) {
                        return fetch('<?php echo route("flow-pc.paypal.create"); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>',
                            },
                            body: JSON.stringify({
                                product_id: <?php echo $product->id; ?>,
                                quantity: self.quantity,
                                pickup_name: self.pickupName,
                                pickup_email: self.pickupEmail,
                                contact_number: self.contactNumber,
                                coupon_code: self.appliedCoupon,
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

                    onApprove: function(data, actions) {
                        self.showPaypal = false;
                        self.isProcessing = true;

                        return fetch('<?php echo route("flow-pc.paypal.capture"); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>',
                            },
                            body: JSON.stringify({
                                paypal_order_id: data.orderID,
                                product_id: <?php echo $product->id; ?>,
                                quantity: self.quantity,
                                message: <?php echo json_encode($message); ?>,
                                style_data: <?php echo json_encode(json_encode($styleData)); ?>,
                                upload_ids: <?php echo json_encode($uploadIds); ?>,
                                pickup_name: self.pickupName,
                                pickup_email: self.pickupEmail,
                                contact_number: self.contactNumber,
                                coupon_code: self.appliedCoupon,
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
                                alert(result.error || result.message || 'Payment failed. Please try again.');
                            }
                        })
                        .catch(err => {
                            console.error('Capture error:', err);
                            self.isProcessing = false;
                            alert('An error occurred. Please try again.');
                        });
                    },

                    onCancel: function() {},

                    onError: function(err) {
                        console.error('PayPal Error:', err);
                        alert('PayPal encountered an error. Please try again.');
                    }
                }).render('#paypal-button-container');
            }
        }
    }
</script>
@endpush
