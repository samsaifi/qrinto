@extends('layouts.quick-flow')

@section('title', 'Review & Pay')
@section('header_title', 'Review & Pay')

@push('styles')
    <style>
        .paypal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 80;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .paypal-sheet {
            width: 100%;
            max-width: 28rem;
            background: #fff;
            border-radius: 2rem 2rem 0 0;
            padding: 1.5rem;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.35s cubic-bezier(0.32, 0.72, 0, 1);
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .paypal-sheet-handle {
            width: 36px;
            height: 4px;
            background: #cbd5e1;
            border-radius: 999px;
            margin: 0 auto 1rem;
        }

        /* Processing overlay */
        .processing-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
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
            border-top: 4px solid #0ea5e9;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

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
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
            }
        }

        /* 3D FlipBook Styles */
        .book-container {
            perspective: 1500px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            position: relative;
            height: calc(var(--book-h, 190px) * 2 + 20px);
            margin: 40px auto 20px auto;
            width: 100%;
            max-width: 320px;
            box-sizing: border-box;
        }

        .book {
            position: relative;
            width: var(--book-w, 140px);
            height: var(--book-h, 190px);
            transform-style: preserve-3d;
            transition: transform 0.6s ease;
        }

        .book::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(15, 23, 42, 0.08);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            z-index: 100;
            pointer-events: none;
        }

        .page {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
            transform-origin: top center;
            cursor: pointer;
            will-change: transform;
        }

        .page-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            border-radius: 0;
            box-shadow: inset 0 3px 10px rgba(0, 0, 0, 0.02), 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 16px -6px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transform-style: preserve-3d;
        }

        .page-face.front {
            transform: translateZ(2px);
        }

        .page-face.back {
            transform: rotateX(180deg) translateZ(2px);
        }

        .page img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #fff;
        }

        /* Flip Logic via Index Variables */
        .page.flipped {
            transform: rotateX(180deg);
        }

        .page {
            z-index: calc(10 - var(--i));
        }

        .page.flipped {
            z-index: calc(10 + var(--i));
        }

        /* Prevent interaction with backfaces */
        .page:not(.flipped) .page-face.back {
            pointer-events: none;
        }

        .page.flipped .page-face.front {
            pointer-events: none;
        }

        /* Hide shadows at hinge where pages 2 & 3 meet */
        .book.spread .page.flipped .page-face.back {
            box-shadow: none;
        }

        .book.spread .page:not(.flipped) .page-face.front {
            box-shadow: none;
        }

        .book-hint {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div x-data="checkoutFlow()" class="space-y-6 pb-48 font-sans text-slate-900">
        <div class="space-y-1">
            <h1 class="text-2xl font-extrabold flex items-center gap-3">
                <a href="javascript:history.back()"
                    class="w-8 h-8 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors shrink-0">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                Final Review
            </h1>
            <p class="text-slate-500 font-medium text-xs ml-11">Review your custom design before payment</p>
        </div>

        @php
            $types = [
                'frame_image' => 'Frame',
                'sample_image' => 'Sample',
                'background_image' => 'Background',
                'overlay_image' => 'Overlay',
            ];

            $orientation = $product->pdf_orientation ?? 'portrait';
            $isPortrait = $orientation === 'portrait';
            $baseWidth = $isPortrait ? 140 : 190;

            $ratio = 1;
            if ($product->productType && $product->productType->width && $product->productType->height) {
                $ratio = $product->productType->width / $product->productType->height;
            } else {
                $ratio = $isPortrait ? 0.75 : 1.33;
            }

            $baseHeight = $baseWidth / $ratio;

            $flipPages = [];
            foreach ($types as $key => $label) {
                $uploadId = $uploadIds[$key] ?? null;
                $currentUpload = $uploadId && isset($uploads[$uploadId]) ? $uploads[$uploadId] : null;
                $defaultUrl = $product->{$key . '_url'} ?? null;
                $displayUrl = $currentUpload ? $currentUpload->url : $defaultUrl;
                if ($displayUrl) {
                    $flipPages[] = $displayUrl;
                }
            }
        @endphp

        @if (count($flipPages) >= 2)
            <!-- 3D Interactive Preview -->
            <div class="space-y-4 py-6 bg-gray-100 border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium">
                <div class="text-center space-y-1">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Interactive Preview</h3>
                    <p id="page-indicator" class="text-xs font-extrabold text-brand-500 uppercase tracking-wider">Showing
                        Page 1</p>
                </div>

                <div class="book-container" style="--book-w: {{ $baseWidth }}px; --book-h: {{ $baseHeight }}px;">
                    <div class="book" id="design-book">
                        @for ($i = 0; $i < count($flipPages); $i += 2)
                            <div class="page" style="--i: {{ $i / 2 }}" onclick="flipPage({{ $i / 2 }})">
                                <div class="page-face front">
                                    <img src="{{ $flipPages[$i] }}" alt="Page {{ $i + 1 }}">
                                </div>
                                <div class="page-face back">
                                    @if (isset($flipPages[$i + 1]))
                                        <img src="{{ $flipPages[$i + 1] }}" alt="Page {{ $i + 2 }}">
                                    @else
                                        <div class="w-full h-full bg-slate-50 flex items-center justify-center">
                                            <span class="text-slate-300 font-bold">End</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
                <div class="book-hint">
                    <i data-lucide="mouse-pointer-2" class="w-3 h-3"></i>
                    Tap to flip pages
                </div>
            </div>
        @elseif(count($flipPages) == 1)
            <!-- Single Image Preview -->
            <div class="py-6 bg-gray-100 border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium">
                <div class="text-center space-y-1 mb-4">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Your Design</h3>
                    <p class="text-xs font-extrabold text-brand-500 uppercase tracking-wider">Preview </p>

                </div>
                <div class="flex justify-center">
                    <div style="width: {{ $baseWidth + 40 }}px; max-width: 90%;">
                        <img src="{{ $flipPages[0] }}" alt="Custom Design"
                            class="w-full h-auto rounded-xl shadow-lg border border-slate-200 {{ $orientation }}">
                    </div>
                </div>
            </div>
        @endif

        <!-- Unified Order Summary Card -->
        <div class="bg-white border-2 border-slate-50 rounded-[2.5rem] shadow-premium overflow-hidden">
            <!-- 1: Product Specs -->
            <div class="p-6 space-y-4">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <span
                            class="text-[10px] font-black text-brand-600 uppercase tracking-[0.2em]">{{ session('quick_flow_data.type_name', 'Custom Product') }}</span>
                        <h3 class="font-black text-xl text-slate-900 leading-tight">{{ $product->name }}</h3>
                    </div>
                    <div
                        class="bg-brand-50 text-brand-600 px-3 py-1.5 rounded-xl font-black text-[10px] uppercase tracking-widest">
                        <span x-text="quantity"></span> Units
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i data-lucide="maximize" class="w-4 h-4"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Size</span>
                            <span
                                class="text-xs font-black text-slate-700">{{ session('quick_flow_data.size_name', 'Standard') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                            <i data-lucide="ruler" class="w-4 h-4"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Dimensions</span>
                            <span class="text-xs font-black text-slate-700">
                                {{ session('quick_flow_data.size_width', '0') }} x
                                {{ session('quick_flow_data.size_height', '0') }}{{ session('quick_flow_data.size_unit', '"') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2: Quantity Adjustment -->
            <div class="bg-slate-50/50 border-y border-slate-100 p-6 flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Update Units</span>
                    <span class="text-sm font-black text-slate-900">Adjust Quantity</span>
                </div>
                <div class="flex items-center gap-2 bg-white p-1 rounded-2xl border border-slate-200">
                    <button type="button" @click="quantity > 1 ? quantity-- : null"
                        class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-brand-50 text-slate-600 hover:text-brand-600 transition-all active:scale-90 flex items-center justify-center">
                        <i data-lucide="minus" class="w-4 h-4"></i>
                    </button>
                    <div class="w-12 text-center font-black text-slate-900 text-lg" x-text="quantity"></div>
                    <button type="button" @click="quantity++"
                        class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-brand-50 text-slate-600 hover:text-brand-600 transition-all active:scale-90 flex items-center justify-center">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- 2.5: Coupon Section -->
            <div class="p-6 bg-white border-b border-slate-100">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Promo Code</span>
                        <template x-if="appliedCoupon">
                            <button @click="removeCoupon()"
                                class="text-[10px] font-black text-red-500 uppercase hover:text-red-600 transition-colors">Remove</button>
                        </template>
                    </div>

                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="ticket"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            <input type="text" x-model="couponInput" :disabled="appliedCoupon" placeholder="Enter code"
                                class="w-full bg-slate-50 border-2 border-transparent focus:border-brand-500 rounded-xl py-2.5 pl-10 pr-3 text-sm font-bold uppercase transition-all outline-none"
                                @keydown.enter.prevent="applyCoupon()">
                        </div>
                        <button type="button" @click="applyCoupon()" :disabled="appliedCoupon || !couponInput"
                            class="px-4 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-brand-600 disabled:opacity-50 transition-all active:scale-95">
                            Apply
                        </button>
                    </div>
                    <p x-show="couponMessage" x-text="couponMessage"
                        :class="appliedCoupon ? 'text-emerald-600' : 'text-red-500'"
                        class="text-[10px] font-bold mt-1 ml-1" style="display:none"></p>
                </div>
            </div>

            <!-- 3: Pricing Summary -->
            <div class="p-6 space-y-3">
                <div class="flex justify-between items-center text-slate-400 text-xs font-bold uppercase tracking-[0.2em]">
                    <span>Subtotal</span>
                    <span x-text="__price(unitPrice * quantity)"></span>
                </div>

                <template x-if="discountAmount > 0">
                    <div
                        class="flex justify-between items-center text-emerald-600 text-xs font-bold uppercase tracking-[0.2em]">
                        <span x-text="'Discount (' + appliedCoupon + ')'"></span>
                        <span x-text="'-' + __price(discountAmount)"></span>
                    </div>
                </template>

                <div class="flex justify-between items-center border-t border-slate-100 pt-4">
                    <span class="text-lg font-black text-slate-900">Total Amount</span>
                    <span class="text-2xl font-black text-brand-600" x-text="__price(calculateTotal())"></span>
                </div>
            </div>
        </div>

        <!-- Pickup Info -->
        <div class="space-y-3">
            <h3 class="text-base font-extrabold">Pickup Information</h3>
            <div class="space-y-3">
                <div class="relative group">
                    <div
                        class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <input type="text" x-model="pickupName"
                        class="w-full bg-white border-2 border-slate-100 rounded-2xl py-4 pl-13 pr-5 font-bold text-slate-700 focus:border-brand-500 focus:ring-0 transition-all outline-none shadow-sm"
                        placeholder="Pickup Name" style="padding-left: 3rem;">
                </div>
                <div class="relative group">
                    <div
                        class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </div>
                    <input type="email" x-model="pickupEmail"
                        class="w-full bg-white border-2 border-slate-100 rounded-2xl py-4 pl-13 pr-5 font-bold text-slate-700 focus:border-brand-500 focus:ring-0 transition-all outline-none shadow-sm"
                        placeholder="Email Address (For Updates)" style="padding-left: 3rem;">
                </div>
                <div class="relative group">
                    <div
                        class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                        <i data-lucide="phone" class="w-5 h-5"></i>
                    </div>
                    <input type="tel" x-model="contactNumber"
                        class="w-full bg-white border-2 border-slate-100 rounded-2xl py-4 pl-13 pr-5 font-bold text-slate-700 focus:border-brand-500 focus:ring-0 transition-all outline-none shadow-sm"
                        placeholder="Contact Number" style="padding-left: 3rem;">
                </div>
            </div>
        </div>

        <!-- Secure Payment Notice -->
        <div class="bg-brand-50 border-2 border-brand-100 p-4 rounded-2xl flex items-start gap-3">
            <div class="w-9 h-9 bg-brand-100 rounded-lg flex items-center justify-center text-brand-600 flex-shrink-0">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h4 class="font-bold text-brand-800 text-sm">Secure Payment via PayPal</h4>
                <p class="text-brand-600 text-xs font-medium">Pay safely with PayPal, cards, or your PayPal balance.</p>
            </div>
        </div>

        <!-- ═══ PayPal Payment Modal ═══ -->
        <template x-teleport="body">
            <div x-cloak>
                <!-- PayPal Bottom Sheet -->
                <div x-show="showPaypal" class="paypal-overlay" @click.self="showPaypal = false">
                    <div class="paypal-sheet" @click.stop>
                        <div class="paypal-sheet-handle"></div>

                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-extrabold text-slate-900">Pay with PayPal</h3>
                            <button @click="showPaypal = false"
                                class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-4 mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase">Amount to Pay</p>
                                <p class="text-2xl font-black text-slate-900" x-text="__price(calculateTotal())"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-400 uppercase">Pickup</p>
                                <p class="text-sm font-bold text-slate-700" x-text="pickupName"></p>
                            </div>
                        </div>

                        <!-- PayPal Button Container -->
                        <div id="paypal-button-container" class="mb-2"></div>

                        <p class="text-center text-xs text-slate-400 font-medium mt-3">
                            <i data-lucide="lock" class="w-3 h-3 inline-block mr-1"></i>
                            Payments are processed securely by PayPal
                        </p>
                    </div>
                </div>

                <!-- Processing Overlay -->
                <div x-show="isProcessing" class="processing-overlay" x-cloak>
                    <template x-if="!paymentSuccess">
                        <div class="text-center">
                            <div class="spinner mx-auto mb-4"></div>
                            <h3 class="text-xl font-extrabold text-slate-900">Processing Payment</h3>
                            <p class="text-slate-500 font-medium text-sm">Please wait while we confirm your order…</p>
                        </div>
                    </template>
                    <template x-if="paymentSuccess">
                        <div class="text-center">
                            <div class="success-check mx-auto mb-4">
                                <i data-lucide="check" class="w-8 h-8 text-white"></i>
                            </div>
                            <h3 class="text-xl font-extrabold text-slate-900">Payment Successful!</h3>
                            <p class="text-slate-500 font-medium text-sm">Redirecting to your order…</p>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Sticky Bottom Pay Buttons -->
        <div
            class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-4 glass border-t border-slate-100 safe-bottom z-50 space-y-3">
            <!-- Terms and Conditions Checkbox -->
            <div class="flex items-center gap-3 px-1 mb-1">
                <input type="checkbox" x-model="acceptedTerms" id="terms-checkbox"
                    class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                <label for="terms-checkbox"
                    class="text-[11px] font-bold text-slate-600 cursor-pointer select-none leading-tight">
                    I have read and accept the <a href="{{ asset('Qrinto_Terms_and_Privacy_Notice.pdf') }}"
                        target="_blank" class="text-brand-600 underline">Terms and Conditions</a>
                </label>
            </div>

            <button type="button" @click="openPaypal()"
                :disabled="!pickupName || !contactNumber || !pickupEmail || !acceptedTerms"
                class="w-full bg-brand-500 disabled:bg-slate-300 hover:bg-brand-600 text-white font-extrabold py-3.5 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span
                    x-text="pickupName && contactNumber && pickupEmail && acceptedTerms ? 'Pay Now — ' + __price(calculateTotal()) : (acceptedTerms ? 'Complete All Info' : 'Accept Terms to Continue')"></span>
            </button>
            <button type="button" @click="payByCash()"
                :disabled="!pickupName || !contactNumber || !pickupEmail || !acceptedTerms"
                class="w-full bg-white disabled:bg-slate-50 disabled:text-slate-400 border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-extrabold py-3.5 rounded-2xl shadow-sm transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base">
                <i data-lucide="banknote" class="w-5 h-5"></i>
                <span>Pay by Cash at Counter</span>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- PayPal SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientId; ?>&currency=<?php echo \App\Services\CurrencyService::getCode(); ?>&intent=capture">
    </script>
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

                // Coupon State
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

                    fetch('<?php echo route(Route::currentRouteName() === 'flow-pc.checkout' ? 'flow-pc.apply-coupon' : 'flow.apply-coupon'); ?>', {
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

                    fetch('<?php echo route(Route::currentRouteName() === 'flow-pc.checkout' ? 'flow-pc.checkout.cash' : 'flow.checkout.cash'); ?>', {
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
                                coupon_code: this.appliedCoupon, // Send coupon code
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

                        // 1) Create PayPal Order
                        createOrder: function(data, actions) {
                            return fetch('<?php echo route(Route::currentRouteName() === 'flow-pc.checkout' ? 'flow-pc.paypal.create' : 'flow.paypal.create'); ?>', {
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
                                        coupon_code: self
                                        .appliedCoupon, // Send coupon code for price validation
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

                        // 2) On Approve — capture and create our Order
                        onApprove: function(data, actions) {
                            self.showPaypal = false;
                            self.isProcessing = true;

                            return fetch('<?php echo route(Route::currentRouteName() === 'flow-pc.checkout' ? 'flow-pc.paypal.capture' : 'flow.paypal.capture'); ?>', {
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
                                        coupon_code: self.appliedCoupon, // Send coupon code
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
                                        alert(result.error || result.message ||
                                            'Payment failed. Please try again.');
                                    }
                                })
                                .catch(err => {
                                    console.error('Capture error:', err);
                                    self.isProcessing = false;
                                    alert('An error occurred. Please try again.');
                                });
                        },

                        onCancel: function() {
                            // User cancelled — just close the modal
                        },

                        onError: function(err) {
                            console.error('PayPal Error:', err);
                            alert('PayPal encountered an error. Please try again.');
                        }
                    }).render('#paypal-button-container');
                }
            }
        }
    </script>
    <script>
        function flipPage(sheetIndex) {
            const pages = document.querySelectorAll('.page');
            if (pages.length < 2) return;

            const sheet1 = pages[0];
            const sheet2 = pages[1];

            if (sheetIndex === 0) {
                if (sheet1.classList.contains('flipped')) {
                    sheet1.classList.remove('flipped');
                    if (sheet2) sheet2.classList.remove('flipped');
                } else {
                    sheet1.classList.add('flipped');
                }
            } else if (sheetIndex === 1 && sheet2) {
                if (sheet2.classList.contains('flipped')) {
                    sheet2.classList.remove('flipped');
                } else {
                    sheet2.classList.add('flipped');
                }
            }

            // Update active page indicator and spread state
            const indicator = document.getElementById('page-indicator');
            const book = document.getElementById('design-book');
            const s1Flipped = sheet1.classList.contains('flipped');
            const s2Flipped = sheet2 ? sheet2.classList.contains('flipped') : false;

            // Toggle spread class to hide shadows at hinge
            if (s1Flipped && !s2Flipped) {
                book.classList.add('spread');
            } else {
                book.classList.remove('spread');
            }

            if (indicator) {
                if (!s1Flipped && !s2Flipped) {
                    indicator.textContent = 'Showing Page 1';
                } else if (s1Flipped && !s2Flipped) {
                    indicator.textContent = 'Showing Pages 2 & 3';
                } else if (s1Flipped && s2Flipped) {
                    indicator.textContent = 'Showing Page 4';
                }
            }
        }
    </script>
@endpush
