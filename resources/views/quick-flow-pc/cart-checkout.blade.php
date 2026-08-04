@extends('layouts.quick-flow-pc')

@section('title', 'Checkout')
@section('header_title', 'Checkout')

@push('styles')
<style>
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
        max-width: 480px;
        background: #fff;
        border-radius: 1.75rem;
        padding: 2rem;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 25px 60px -12px rgba(0,0,0,0.25);
        animation: modalIn 0.3s cubic-bezier(0.32, 0.72, 0, 1);
    }
    @keyframes modalIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
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
        border-top: 4px solid #ec4899;
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
        animation: popIn 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
    }
    @keyframes popIn { 0% { transform: scale(0); } 100% { transform: scale(1); } }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-4px); }
        40%, 80% { transform: translateX(4px); }
    }
    .animate-shake {
        animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    }
</style>
@endpush

@section('content')
<div x-data="cartCheckoutFlow()" class="py-6 font-sans text-slate-900">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm mb-6">
        <a href="{{ route('flow-pc.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors flex items-center gap-1.5">
            <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
        <a href="{{ route('flow-pc.cart.index') }}" class="text-slate-400 font-medium hover:text-brand-600 transition-colors">
            Shopping Cart
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
        <span class="text-slate-700 font-semibold">Checkout</span>
    </nav>

    {{-- Title Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <a href="{{ route('flow-pc.cart.index') }}"
                    class="w-9 h-9 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-brand-600 transition-all shrink-0">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                Checkout & Review
            </h1>
            <p class="text-slate-500 font-medium text-sm mt-1 ml-12">
                Review your {{ $cart->item_count }} item{{ $cart->item_count > 1 ? 's' : '' }} and complete your pickup order
            </p>
        </div>
    </div>

    {{-- Form Global Error Banner --}}
    <div x-show="errors.form" x-cloak class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-2xl flex items-center gap-3 text-red-700 animate-shake shadow-sm">
        <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-red-600 flex-shrink-0">
            <i data-lucide="alert-circle" class="w-5 h-5"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-sm text-red-900">Validation Notice</h4>
            <p class="text-xs font-semibold text-red-600" x-text="errors.form"></p>
        </div>
        <button @click="errors.form = ''" class="text-red-400 hover:text-red-600 p-1"><i data-lucide="x" class="w-4 h-4"></i></button>
    </div>

    {{-- 2-Column Desktop Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Left Column: Items, Pickup Info & Instructions --}}
        <div class="lg:col-span-7 space-y-6">
            {{-- Cart Items Summary Card --}}
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                        <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wider text-xs">Order Items</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                        {{ $cart->item_count }} Item{{ $cart->item_count > 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($cart->items as $item)
                        @php
                            $customization = $item->customization_data ?? [];
                            $itemUploadIds = $customization['upload_ids'] ?? [];
                            $firstUpId = !empty($itemUploadIds) ? reset($itemUploadIds) : null;
                            $thumb = null;
                            if ($firstUpId && isset($uploads[$firstUpId])) {
                                $thumb = $uploads[$firstUpId]->url;
                            } elseif ($item->product && $item->product->frame_image_url) {
                                $thumb = $item->product->frame_image_url;
                            }
                        @endphp
                        <div class="py-4 first:pt-0 last:pb-0 flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-50 flex-shrink-0 border border-slate-200/80 shadow-xs">
                                @if($thumb)
                                    <img src="{{ $thumb }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center"><i data-lucide="image" class="w-6 h-6 text-slate-300"></i></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-extrabold text-sm text-slate-900 truncate mb-1">{{ $item->product->name ?? 'Custom Print' }}</h4>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 font-medium">
                                    <span>Quantity: <strong class="text-slate-800 font-bold">{{ $item->quantity }}</strong></span>
                                    @if(!empty($customization['size_name']))
                                        <span class="text-slate-300">•</span>
                                        <span>Size: <strong class="text-slate-800 font-bold">{{ $customization['size_name'] }}</strong></span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="font-extrabold text-base text-slate-900 block" x-text="__price({{ $item->unit_price }} * {{ $item->quantity }})"></span>
                                <span class="text-[11px] font-medium text-slate-400" x-text="__price({{ $item->unit_price }}) + ' ea'"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pickup Information Card --}}
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-600 flex-shrink-0">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Pickup Information</h3>
                        <p class="text-xs text-slate-500 font-medium">Details of the person who will pick up the order</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Pickup Name --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pickup Name <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 transition-colors"
                                :class="errors.pickupName ? 'text-red-400' : 'text-slate-400 group-focus-within:text-brand-500'">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <input type="text" x-model="pickupName"
                                @blur="validatePickupName()"
                                @input="if(touched.pickupName) validatePickupName()"
                                :class="errors.pickupName ? 'border-red-400 bg-red-50/20 focus:border-red-500' : (touched.pickupName && !errors.pickupName ? 'border-emerald-400 bg-emerald-50/10' : 'border-slate-200 focus:border-brand-500')"
                                class="w-full bg-slate-50/50 border-2 focus:bg-white rounded-2xl py-3.5 pl-12 pr-4 font-bold text-slate-800 focus:ring-0 transition-all outline-none text-sm"
                                placeholder="Full Name">
                        </div>
                        <p x-show="errors.pickupName" x-text="errors.pickupName" class="text-xs font-bold text-red-500 mt-1.5 ml-1 flex items-center gap-1" style="display:none">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                        </p>
                    </div>

                    {{-- Email Address --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Email Address <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 transition-colors"
                                :class="errors.pickupEmail ? 'text-red-400' : 'text-slate-400 group-focus-within:text-brand-500'">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input type="email" x-model="pickupEmail"
                                @blur="validatePickupEmail()"
                                @input="if(touched.pickupEmail) validatePickupEmail()"
                                :class="errors.pickupEmail ? 'border-red-400 bg-red-50/20 focus:border-red-500' : (touched.pickupEmail && !errors.pickupEmail ? 'border-emerald-400 bg-emerald-50/10' : 'border-slate-200 focus:border-brand-500')"
                                class="w-full bg-slate-50/50 border-2 focus:bg-white rounded-2xl py-3.5 pl-12 pr-4 font-bold text-slate-800 focus:ring-0 transition-all outline-none text-sm"
                                placeholder="name@example.com">
                        </div>
                        <p x-show="errors.pickupEmail" x-text="errors.pickupEmail" class="text-xs font-bold text-red-500 mt-1.5 ml-1 flex items-center gap-1" style="display:none">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                        </p>
                    </div>

                    {{-- Contact Number --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Contact Phone <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 transition-colors"
                                :class="errors.contactNumber ? 'text-red-400' : 'text-slate-400 group-focus-within:text-brand-500'">
                                <i data-lucide="phone" class="w-5 h-5"></i>
                            </div>
                            <input type="tel" x-model="contactNumber"
                                @blur="validateContactNumber()"
                                @input="if(touched.contactNumber) validateContactNumber()"
                                :class="errors.contactNumber ? 'border-red-400 bg-red-50/20 focus:border-red-500' : (touched.contactNumber && !errors.contactNumber ? 'border-emerald-400 bg-emerald-50/10' : 'border-slate-200 focus:border-brand-500')"
                                class="w-full bg-slate-50/50 border-2 focus:bg-white rounded-2xl py-3.5 pl-12 pr-4 font-bold text-slate-800 focus:ring-0 transition-all outline-none text-sm"
                                placeholder="Phone number (e.g. 555-123-4567)">
                        </div>
                        <p x-show="errors.contactNumber" x-text="errors.contactNumber" class="text-xs font-bold text-red-500 mt-1.5 ml-1 flex items-center gap-1" style="display:none">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Special Instructions Card --}}
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-600 flex-shrink-0">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Special Instructions</h3>
                        <p class="text-xs text-slate-500 font-medium">Optional requests or store notes</p>
                    </div>
                </div>
                <div class="relative group">
                    <textarea x-model="specialInstructions" rows="3"
                        class="w-full bg-slate-50/50 border-2 border-slate-200 focus:bg-white rounded-2xl p-4 font-medium text-slate-800 focus:border-brand-500 focus:ring-0 transition-all outline-none text-sm resize-none"
                        placeholder="Rush order requests, custom instructions, or store pickup notes..."></textarea>
                </div>
            </div>
        </div>

        {{-- Right Column: Sticky Summary & Checkout Panel --}}
        <div class="lg:col-span-5 sticky top-24 space-y-6">
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-6">
                <h3 class="text-lg font-extrabold text-slate-900 pb-4 border-b border-slate-100">Payment Summary</h3>

                {{-- Promo Code Section --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Promo Code</label>
                        <template x-if="appliedCoupon">
                            <button @click="removeCoupon()" class="text-xs font-bold text-red-500 hover:underline uppercase">Remove Code</button>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="ticket" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" x-model="couponInput" :disabled="appliedCoupon" placeholder="Enter coupon code"
                                class="w-full bg-slate-50 border-2 border-slate-100 focus:border-brand-500 focus:bg-white rounded-xl py-2.5 pl-10 pr-3 text-xs font-bold uppercase transition-all outline-none"
                                @keydown.enter.prevent="applyCoupon()">
                        </div>
                        <button @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                            class="px-4 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-brand-600 disabled:opacity-50 transition-all active:scale-95 cursor-pointer">
                            Apply
                        </button>
                    </div>
                    <p x-show="couponMessage" x-text="couponMessage" :class="appliedCoupon ? 'text-emerald-600' : 'text-red-500'"
                        class="text-xs font-bold mt-1.5 ml-1" style="display:none"></p>
                </div>

                {{-- Price Breakdown --}}
                <div class="space-y-3 pt-2">
                    <div class="flex justify-between items-center text-slate-500 text-sm font-semibold">
                        <span>Subtotal</span>
                        <span class="font-extrabold text-slate-800" x-text="__price(subtotal)"></span>
                    </div>
                    <template x-if="discountAmount > 0">
                        <div class="flex justify-between items-center text-emerald-600 text-sm font-semibold">
                            <span x-text="'Discount (' + appliedCoupon + ')'"></span>
                            <span class="font-extrabold" x-text="'-' + __price(discountAmount)"></span>
                        </div>
                    </template>
                    <div class="flex justify-between items-center border-t border-slate-100 pt-4 mt-3">
                        <div>
                            <span class="text-base font-extrabold text-slate-900 block">Total Amount</span>
                            <span class="text-xs text-slate-400 font-medium">Includes all applicable taxes</span>
                        </div>
                        <span class="text-3xl font-black text-brand-600" x-text="__price(calculateTotal())"></span>
                    </div>
                </div>

                {{-- Terms & Conditions Checkbox --}}
                <div class="pt-2">
                    <div class="flex items-start gap-3 p-3.5 bg-slate-50 border-2 transition-all rounded-2xl"
                        :class="errors.terms ? 'border-red-400 bg-red-50/20' : 'border-slate-200/80'">
                        <input type="checkbox" x-model="acceptedTerms" @change="validateTerms()" id="terms-checkbox-pc"
                            class="mt-0.5 w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                        <label for="terms-checkbox-pc" class="text-xs font-medium text-slate-600 cursor-pointer select-none leading-relaxed">
                            I agree to the <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank" class="text-brand-600 font-bold underline hover:text-brand-700">Terms and Conditions</a> and privacy notice.
                        </label>
                    </div>
                    <p x-show="errors.terms" x-text="errors.terms" class="text-xs font-bold text-red-500 mt-1.5 ml-1 flex items-center gap-1" style="display:none">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                    </p>
                </div>

                {{-- Checkout Action Buttons --}}
                <div class="space-y-3 pt-1">
                    <button type="button" @click="openPaypal()"
                        :disabled="!isFormValid()"
                        class="w-full bg-brand-600 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed hover:bg-brand-700 text-white font-extrabold py-4 rounded-2xl shadow-lg shadow-brand-500/20 disabled:shadow-none transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-base cursor-pointer">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                        <span x-text="getButtonText()"></span>
                    </button>

                    <button type="button" @click="payByCash()"
                        :disabled="!isFormValid()"
                        class="w-full bg-white disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 font-extrabold py-3.5 rounded-2xl shadow-xs transition-all active:scale-[0.98] flex items-center justify-center gap-2.5 text-sm cursor-pointer">
                        <i data-lucide="banknote" class="w-5 h-5 text-emerald-600"></i>
                        <span>Pay by Cash at Counter</span>
                    </button>
                </div>

                {{-- Secure Payment Notice --}}
                <div class="bg-brand-50/60 border border-brand-100/80 p-3.5 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 bg-brand-100 rounded-xl flex items-center justify-center text-brand-600 flex-shrink-0">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <div class="text-xs">
                        <h4 class="font-bold text-brand-900">Secure Payment Guarantee</h4>
                        <p class="text-brand-700 font-medium">Encrypted & powered by PayPal</p>
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
                            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-900">Pay with PayPal</h3>
                        </div>
                        <button @click="showPaypal = false" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors cursor-pointer">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-4 mb-5 flex items-center justify-between border border-slate-200/60">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Amount to Pay</p>
                            <p class="text-2xl font-black text-slate-900" x-text="__price(calculateTotal())"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pickup For</p>
                            <p class="text-sm font-bold text-slate-800" x-text="pickupName"></p>
                        </div>
                    </div>
                    <div id="paypal-button-container" class="mb-2"></div>
                    <p class="text-center text-xs text-slate-400 font-medium mt-4 flex items-center justify-center gap-1.5">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        Payments are processed securely by PayPal
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
                        <p class="text-slate-500 font-medium text-sm mt-1">Redirecting to your order details...</p>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ \App\Services\CurrencyService::getCode() }}&intent=capture"></script>
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
                const res = await fetch('{{ route("flow-pc.cart.apply-coupon") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ code: this.couponInput })
                });
                const data = await res.json();
                if (data.success) {
                    this.appliedCoupon = this.couponInput;
                    this.discountAmount = parseFloat(data.discount);
                    this.couponMessage = data.message;
                } else {
                    this.couponMessage = data.message;
                }
            } catch(e) { this.couponMessage = 'Error applying coupon.'; }
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
                if (firstErrorEl) firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
                if (firstErrorEl) firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            this.isProcessing = true;
            this.errors.form = '';

            fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow-pc.cart-checkout.cash' : 'flow.cart-checkout.cash'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                    setTimeout(() => { window.location.href = result.redirect_url; }, 1800);
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
                style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'paypal', height: 48 },

                createOrder(data, actions) {
                    return fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow-pc.cart-checkout.paypal.create' : 'flow.cart-checkout.paypal.create'); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                        if (order.error) { alert(order.error); throw new Error(order.error); }
                        return order.id;
                    });
                },

                onApprove(data, actions) {
                    self.showPaypal = false;
                    self.isProcessing = true;

                    return fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow-pc.cart-checkout.paypal.capture' : 'flow.cart-checkout.paypal.capture'); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                            setTimeout(() => { window.location.href = result.redirect_url; }, 1800);
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
