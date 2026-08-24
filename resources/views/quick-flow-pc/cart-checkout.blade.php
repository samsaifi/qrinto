@extends('layouts.quick-flow-pc')

@section('title', 'Your order | Qrinto Custom Print Studio')
@section('header_title', 'Your order')
@section('meta_robots', 'noindex, nofollow')

@php
    $routePrefix = $routePrefix ?? 'flow.';
    
    $activeStoreId = session('active_store_id');
    $firstItem = $cart->items->first();
    $activeStore = $activeStoreId ? \App\Models\Store::find($activeStoreId) : ($firstItem->product->store ?? null);
    $storeName = $activeStore->name ?? 'Billmeijer Camera';
    $storeCity = $activeStore->city ?? 'Fenton';
    $storeState = $activeStore->state ?? 'Michigan';
@endphp

@push('styles')
    <style>
        .paypal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(17, 36, 25, 0.6);
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
            box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.25);
        }

        .processing-overlay {
            position: fixed;
            inset: 0;
            background: rgba(250, 252, 249, 0.97);
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #287d3c;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@section('content')
    <div x-data="cartCheckoutFlow()" class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[1100px] mx-auto">

            {{-- Back Navigation --}}
            <div class="mb-6">
                <a href="{{ route($routePrefix . 'index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                    <span>← All products</span>
                </a>
            </div>

            {{-- Title Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">
                    Your order
                </h1>
            </div>

            {{-- Two Column Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Left Main Card --}}
                <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-5">

                    {{-- Items Summary List --}}
                    <div class="space-y-4 pb-5 border-b border-slate-100">
                        @foreach ($cart->items as $item)
                            @php
                                $customization = $item->customization_data ?? [];
                                $uploadIds = $customization['upload_ids'] ?? [];
                                $itemImage = null;
                                
                                if (!empty($uploadIds)) {
                                    $uArr = is_array($uploadIds) ? array_values($uploadIds) : [$uploadIds];
                                    foreach ($uArr as $upId) {
                                        if ($upId) {
                                            $upObj = $uploads->get($upId) ?? ($uploads[$upId] ?? null);
                                            if ($upObj && !empty($upObj->url)) {
                                                $itemImage = $upObj->url;
                                                break;
                                            }
                                            $upObjDirect = \App\Models\CustomerUpload::find($upId);
                                            if ($upObjDirect && !empty($upObjDirect->url)) {
                                                $itemImage = $upObjDirect->url;
                                                break;
                                            }
                                        }
                                    }
                                }
                                
                                if (!$itemImage && $item->product) {
                                    $itemImage = $item->product->frame_image_url 
                                        ?? ($item->product->sample_image_url 
                                        ?? ($item->product->background_image_url 
                                        ?? ($item->product->overlay_image_url ?? null)));
                                }
                                
                                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->product->base_price ?? 0));
                                $itemSubtotal = $unitPrice * (int) $item->quantity;
                            @endphp

                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3.5">
                                    {{-- Thumbnail --}}
                                    <div class="w-14 h-18 bg-[#f2f7f2] rounded-xl flex items-center justify-center overflow-hidden border border-slate-100 shrink-0">
                                        @if ($itemImage)
                                            <img src="{{ $itemImage }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-slate-100 rounded-xl border border-slate-200/80 flex items-center justify-center text-slate-400 font-bold text-[10px]">
                                                Card
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Title & Specs --}}
                                    <div>
                                        <h3 class="font-extrabold text-slate-900 text-sm">
                                            {{ $item->product->name }}
                                        </h3>
                                        <p class="text-[11px] text-slate-500 font-normal mt-0.5">
                                            @if (isset($customization['size_title']) || isset($customization['size_width']))
                                                {{ $customization['size_title'] ?? 'Card' }} · {{ ($customization['size_width'] ?? 5) + 0 }} × {{ ($customization['size_height'] ?? 7) + 0 }} in
                                            @else
                                                Standard Specification
                                            @endif
                                        </p>

                                        {{-- Quantity stepper --}}
                                        <div class="flex items-center gap-2.5 mt-2">
                                            <button type="button" @click="updateQty(-1)" :disabled="quantity <= 1 || updatingQty"
                                                class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                                                aria-label="Decrease quantity">−</button>
                                            <span class="text-sm font-extrabold text-slate-900 w-6 text-center tabular-nums" x-text="quantity">{{ $item->quantity }}</span>
                                            <button type="button" @click="updateQty(1)" :disabled="quantity >= 100 || updatingQty"
                                                class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                                                aria-label="Increase quantity">+</button>
                                        </div>
                                    </div>
                                </div>

                                <span class="text-sm font-extrabold text-slate-900" x-text="__price(unitPrice * quantity)">
                                    {{ \App\Services\CurrencyService::format($itemSubtotal) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Store Pickup Banner (Light Green Box) --}}
                    <div class="bg-[#f2f7f2] rounded-2xl p-3.5 border border-emerald-100/80 flex items-start gap-2.5">
                        <div class="w-4 h-4 text-emerald-800 mt-0.5 shrink-0">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#112419] text-xs">
                                Pickup at {{ $storeName }}
                            </h4>
                            <p class="text-[11px] text-slate-500 font-normal mt-0.5">
                                {{ $storeCity }}, {{ $storeState }} · usually ready the same day
                            </p>
                        </div>
                    </div>

                    {{-- Form: Who is picking it up --}}
                    <div class="space-y-3.5 pt-1">
                        <h3 class="font-bold text-[#112419] text-xs">
                            Who is picking it up
                        </h3>

                        <div x-show="errors.form" class="bg-rose-50 text-rose-600 border border-rose-200 rounded-xl p-3 text-xs font-semibold" style="display:none">
                            <span x-text="errors.form"></span>
                        </div>

                        {{-- Name & Phone --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Name</label>
                                <input type="text" x-model="pickupName" @blur="validatePickupName()"
                                    :class="errors.pickupName && touched.pickupName ? 'border-rose-400 bg-rose-50/50' : 'border-slate-200'"
                                    class="w-full border rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all">
                                <p x-show="errors.pickupName && touched.pickupName" x-text="errors.pickupName" class="text-[10px] text-rose-600 font-medium mt-1"></p>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Phone</label>
                                <input type="tel" x-model="contactNumber" @blur="validateContactNumber()"
                                    :class="errors.contactNumber && touched.contactNumber ? 'border-rose-400 bg-rose-50/50' : 'border-slate-200'"
                                    class="w-full border rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all">
                                <p x-show="errors.contactNumber && touched.contactNumber" x-text="errors.contactNumber" class="text-[10px] text-rose-600 font-medium mt-1"></p>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Email, for the ready-for-pickup message</label>
                            <input type="email" x-model="pickupEmail" @blur="validatePickupEmail()"
                                :class="errors.pickupEmail && touched.pickupEmail ? 'border-rose-400 bg-rose-50/50' : 'border-slate-200'"
                                class="w-full border rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all">
                            <p x-show="errors.pickupEmail && touched.pickupEmail" x-text="errors.pickupEmail" class="text-[10px] text-rose-600 font-medium mt-1"></p>
                        </div>

                        {{-- Note to store --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Note to the store, optional</label>
                            <textarea x-model="specialInstructions" rows="2.5"
                                class="w-full border border-slate-200 rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all resize-none"></textarea>
                        </div>

                        {{-- Terms Checkbox --}}
                        <div class="pt-1">
                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" x-model="acceptedTerms" @change="validateTerms()"
                                    class="w-4 h-4 rounded border-slate-300 text-emerald-800 focus:ring-emerald-600 mt-0.5 cursor-pointer">
                                <span class="text-[11px] text-slate-600 font-medium">
                                    I agree to the <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}" target="_blank" class="text-emerald-800 font-bold underline">Terms & Conditions</a>.
                                </span>
                            </label>
                            <p x-show="errors.terms" x-text="errors.terms" class="text-[10px] text-rose-600 font-medium mt-1"></p>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Summary Card --}}
                <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-2xs space-y-5">
                    <h2 class="font-extrabold text-[#112419] text-sm">
                        Total
                    </h2>

                    {{-- Lines --}}
                    <div class="space-y-2.5 text-[11px] text-slate-600 font-medium">
                        <div class="flex justify-between items-center">
                            <span>Subtotal</span>
                            <span class="font-bold text-slate-900" x-text="__price(subtotal)"></span>
                        </div>

                        <template x-if="discountAmount > 0">
                            <div class="flex justify-between items-center text-emerald-700 font-semibold">
                                <span>Discount</span>
                                <span>-<span x-text="__price(discountAmount)"></span></span>
                            </div>
                        </template>

                        <div class="flex justify-between items-center">
                            <span>Pickup</span>
                            <span class="font-bold text-slate-900">Free</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex justify-between items-baseline">
                        <span class="text-sm font-extrabold text-[#112419]">Due at pickup</span>
                        <span class="text-lg font-extrabold text-[#112419]" x-text="__price(calculateTotal())"></span>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-2.5 pt-1">
                        {{-- Place order, pay at counter --}}
                        <button type="button" @click="payByCash()"
                            :disabled="!isFormValid()"
                            class="w-full bg-[#287d3c] hover:bg-emerald-800 disabled:bg-slate-200 text-white font-bold py-3 rounded-xl text-xs transition-all shadow-2xs active:scale-95 cursor-pointer disabled:cursor-not-allowed">
                            Place order, pay at the counter
                        </button>

                        {{-- Pay online now --}}
                        <button type="button" @click="openPaypal()"
                            :disabled="!isFormValid()"
                            class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 disabled:bg-slate-50 disabled:text-slate-300 font-bold py-3 rounded-xl text-xs transition-all shadow-2xs active:scale-95 cursor-pointer disabled:cursor-not-allowed">
                            Pay online now
                        </button>

                        <p class="text-[11px] text-slate-400 text-center font-normal pt-0.5">
                            No shipping. You collect it at the store.
                        </p>
                    </div>
                </div>

            </div>

        </div>

        {{-- PayPal Modal --}}
        <template x-teleport="body">
            <div x-cloak>
                <div x-show="showPaypal" class="paypal-overlay" @click.self="showPaypal = false">
                    <div class="paypal-sheet" @click.stop>
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900">Pay with PayPal</h3>
                                <p class="text-xs text-slate-500">Fast & secure online payment</p>
                            </div>
                            <button @click="showPaypal = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center font-bold">
                                ✕
                            </button>
                        </div>

                        <div class="bg-[#f2f7f2] rounded-2xl p-4 mb-5 flex items-center justify-between border border-emerald-100">
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
                    </div>
                </div>

                {{-- Processing Overlay --}}
                <div x-show="isProcessing" class="processing-overlay" x-cloak>
                    <template x-if="!paymentSuccess">
                        <div class="text-center">
                            <div class="spinner mx-auto mb-4"></div>
                            <h3 class="text-xl font-extrabold text-slate-900">Processing Order</h3>
                            <p class="text-slate-500 font-medium text-sm mt-1">Please wait while we confirm your print order...</p>
                        </div>
                    </template>
                    <template x-if="paymentSuccess">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-700">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-extrabold text-slate-900">Order Placed Successfully!</h3>
                            <p class="text-slate-500 font-medium text-sm mt-1">Redirecting to your confirmation details...</p>
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
        function cartCheckoutFlow() {
            return {
                subtotal: {{ (float) $cart->subtotal }},
                itemId: {{ optional($cart->items->first())->id ?? 'null' }},
                quantity: {{ (int) (optional($cart->items->first())->quantity ?? 1) }},
                unitPrice: {{ (float) (optional($cart->items->first())->unit_price ?: (optional(optional($cart->items->first())->product)->base_price ?? 0)) }},
                updatingQty: false,
                pickupName: '',
                pickupEmail: '',
                contactNumber: '',
                specialInstructions: '',
                acceptedTerms: false,
                showPaypal: false,
                isProcessing: false,
                paymentSuccess: false,
                paypalRendered: false,

                couponInput: '{{ $cart->coupon->code ?? '' }}',
                appliedCoupon: {!! $cart->coupon ? "'" . $cart->coupon->code . "'" : 'null' !!},
                discountAmount: {{ (float) ($cart->discount ?? 0) }},
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

                async updateQty(delta) {
                    const next = Math.max(1, Math.min(100, this.quantity + delta));
                    if (next === this.quantity || this.updatingQty || !this.itemId) return;
                    this.quantity = next;
                    this.subtotal = this.unitPrice * this.quantity;
                    this.updatingQty = true;
                    try {
                        const res = await fetch('{{ url('/cart/update') }}/' + this.itemId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ quantity: this.quantity })
                        });
                        const data = await res.json();
                        if (data && typeof data.subtotal !== 'undefined') {
                            this.subtotal = parseFloat(data.subtotal);
                            if (typeof data.discount !== 'undefined') this.discountAmount = parseFloat(data.discount);
                        }
                    } catch (e) {
                        // Keep the optimistic value; the server will reconcile at checkout.
                    } finally {
                        this.updatingQty = false;
                    }
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
                        this.errors.pickupEmail = 'Please enter a valid email address.';
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
                        this.errors.contactNumber = 'Please enter a valid phone number.';
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
                        this.errors.form = 'Please complete all required fields.';
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

                openPaypal() {
                    if (!this.validateAll()) return;
                    this.showPaypal = true;

                    this.$nextTick(() => {
                        if (!this.paypalRendered) {
                            this.renderPaypalButtons();
                            this.paypalRendered = true;
                        }
                    });
                },

                payByCash() {
                    if (!this.validateAll()) return;
                    this.isProcessing = true;
                    this.errors.form = '';

                    fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow.cart-checkout.cash' : 'flow.cart-checkout.cash'); ?>', {
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
                    const container = document.getElementById('paypal-button-container');
                    if (!container) return;

                    paypal.Buttons({
                        style: {
                            layout: 'vertical',
                            color: 'gold',
                            shape: 'rect',
                            label: 'paypal',
                            height: 48
                        },

                        createOrder(data, actions) {
                            return fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow.cart-checkout.paypal.create' : 'flow.cart-checkout.paypal.create'); ?>', {
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

                            return fetch('<?php echo route(str_contains(Route::currentRouteName(), 'flow-pc') ? 'flow.cart-checkout.paypal.capture' : 'flow.cart-checkout.paypal.capture'); ?>', {
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
