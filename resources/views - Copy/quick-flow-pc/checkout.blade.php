@extends('layouts.quick-flow-pc')

@section('title', 'Your order | Qrinto Custom Print Studio')
@section('header_title', 'Your order')
@section('meta_robots', 'noindex, nofollow')

@php
    $routePrefix = $routePrefix ?? 'flow.';
    $flowData = session('quick_flow_data', []);
    
    $titleLabel = $flowData['size_title'] ?? 'Folded';
    $sizeWidth = isset($flowData['size_width']) ? $flowData['size_width'] + 0 : 5;
    $sizeHeight = isset($flowData['size_height']) ? $flowData['size_height'] + 0 : 7;
    $sizeLabel = "{$sizeWidth} × {$sizeHeight}";
    
    $templateName = $flowData['template_name'] ?? ($product->name ?? 'Happy Anniversary');

    $activeStoreId = session('active_store_id');
    $activeStore = $activeStoreId ? \App\Models\Store::find($activeStoreId) : ($product->store ?? null);
    $storeName = $activeStore->name ?? 'Billmeijer Camera';
    $storeCity = $activeStore->city ?? 'Fenton';
    $storeState = $activeStore->state ?? 'Michigan';

    // First image thumbnail & edited pages collection
    $editedImages = [];
    if (!empty($uploads)) {
        foreach ($uploads as $u) {
            if (!empty($u->url)) {
                $editedImages[] = $u->url;
            }
        }
    }
    if (empty($editedImages) && isset($upload) && !empty($upload->url)) {
        $editedImages[] = $upload->url;
    }
    
    $firstImg = $editedImages[0] ?? null;
    if (!$firstImg && isset($product)) {
        $firstImg = $product->frame_image_url ?? ($product->sample_image_url ?? ($product->image_url ?? null));
    }
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
    <div x-data="checkoutFlow()" class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[1100px] mx-auto">

            {{-- Back + Title (single row on mobile) --}}
            <div class="flex items-center gap-2 mb-3 md:flex-col md:items-start md:gap-0 md:mb-6">
                <a href="javascript:history.back()"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ←
                    <span class="hidden md:inline">Keep editing</span>
                </a>
                <h1 class="text-base md:text-2xl font-extrabold text-[#112419] tracking-tight md:mt-4">
                    Your order
                </h1>
            </div>

            {{-- Two Column Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Left Main Card --}}
                <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-5">

                    {{-- Item Summary Row --}}
                    <div class="flex items-center justify-between gap-4 pb-5 border-b border-slate-100">
                        <div class="flex items-center gap-3.5">
                            {{-- Image Thumbnail(s) --}}
                            <div class="flex items-center gap-1.5">
                                @if (!empty($editedImages))
                                    @foreach (array_slice($editedImages, 0, 2) as $imgUrl)
                                        <div class="w-14 h-18 bg-[#f2f7f2] rounded-xl flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                                            <img src="{{ $imgUrl }}" alt="{{ $templateName }}" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                @elseif ($firstImg)
                                    <div class="w-14 h-18 bg-[#f2f7f2] rounded-xl flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                                        <img src="{{ $firstImg }}" alt="{{ $templateName }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-14 h-18 bg-pink-300 rounded-xl flex items-center justify-center text-white font-bold text-xs shrink-0">Card</div>
                                @endif
                            </div>

                            {{-- Product Title & Specs --}}
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">
                                    {{ $templateName }}
                                </h3>
                                <p class="text-[11px] text-slate-500 font-normal mt-0.5">
                                    {{ $titleLabel }} card · {{ $sizeLabel }} in · envelope included
                                </p>
                            </div>
                        </div>

                        {{-- Quantity Stepper & Price --}}
                        <div class="flex items-center gap-3">
                            <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-white">
                                <button type="button" @click="quantity > 1 ? quantity-- : null"
                                    class="w-7 h-7 flex items-center justify-center text-slate-600 font-bold hover:bg-slate-50 cursor-pointer text-xs">
                                    -
                                </button>
                                <span class="w-7 text-center text-xs font-bold text-slate-900" x-text="quantity"></span>
                                <button type="button" @click="quantity++"
                                    class="w-7 h-7 flex items-center justify-center text-slate-600 font-bold hover:bg-slate-50 cursor-pointer text-xs">
                                    +
                                </button>
                            </div>

                            <span class="text-sm font-extrabold text-slate-900" x-text="__price(unitPrice * quantity)">
                                {{ \App\Services\CurrencyService::format($unitPrice * $quantity) }}
                            </span>
                        </div>
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

                        {{-- Name & Phone --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Name</label>
                                <input type="text" x-model="pickupName"
                                    class="w-full border border-slate-200 rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Phone</label>
                                <input type="tel" x-model="contactNumber"
                                    class="w-full border border-slate-200 rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Email, for the ready-for-pickup message</label>
                            <input type="email" x-model="pickupEmail"
                                class="w-full border border-slate-200 rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all">
                        </div>

                        {{-- Note to store --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Note to the store, optional</label>
                            <textarea x-model="storeNote" rows="2.5"
                                class="w-full border border-slate-200 rounded-xl p-2.5 text-xs text-slate-900 font-medium outline-none focus:border-emerald-600 transition-all resize-none"></textarea>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Summary Card --}}
                <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-5">
                    <h2 class="font-extrabold text-[#112419] text-sm">
                        Total
                    </h2>

                    {{-- Lines --}}
                    <div class="space-y-2.5 text-[11px] text-slate-600 font-medium">
                        <div class="flex justify-between items-center">
                            <span><span x-text="quantity"></span> × {{ $titleLabel }} card, {{ $sizeLabel }}</span>
                            <span class="font-bold text-slate-900" x-text="__price(unitPrice * quantity)"></span>
                        </div>

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
                            :disabled="!pickupName || !contactNumber || !pickupEmail"
                            class="w-full bg-[#287d3c] hover:bg-emerald-800 disabled:bg-slate-200 text-white font-bold py-3 rounded-xl text-xs transition-all shadow-2xs active:scale-95 cursor-pointer disabled:cursor-not-allowed">
                            Place order, pay at the counter
                        </button>

                        {{-- Pay online now --}}
                        <button type="button" @click="openPaypal()"
                            :disabled="!pickupName || !contactNumber || !pickupEmail"
                            class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 disabled:bg-slate-50 disabled:text-slate-300 font-bold py-3 rounded-xl text-xs transition-all shadow-2xs active:scale-95 cursor-pointer disabled:cursor-not-allowed">
                            Pay online now
                        </button>

                        <p class="text-[11px] text-slate-400 text-center font-normal pt-0.5">
                            No shipping. You collect it at the store.
                        </p>
                    </div>
                </div>
                            class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 disabled:bg-slate-50 disabled:text-slate-300 font-bold py-3.5 rounded-xl text-sm transition-all shadow-2xs active:scale-95 cursor-pointer disabled:cursor-not-allowed">
                            Pay online now
                        </button>

                        <p class="text-xs text-slate-400 text-center font-normal pt-1">
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
        function __price(val) {
            return '$' + parseFloat(val || 0).toFixed(2);
        }

        function checkoutFlow() {
            return {
                unitPrice: {{ $unitPrice }},
                quantity: {{ $quantity }},
                pickupName: '',
                pickupEmail: '',
                contactNumber: '',
                storeNote: '',
                showPaypal: false,
                isProcessing: false,
                paymentSuccess: false,
                paypalRendered: false,

                calculateTotal() {
                    return (this.unitPrice * this.quantity).toFixed(2);
                },

                openPaypal() {
                    if (!this.pickupName || !this.contactNumber || !this.pickupEmail) return;
                    this.showPaypal = true;

                    this.$nextTick(() => {
                        if (!this.paypalRendered) {
                            this.renderPaypalButtons();
                            this.paypalRendered = true;
                        }
                    });
                },

                payByCash() {
                    if (!this.pickupName || !this.contactNumber || !this.pickupEmail) return;
                    this.isProcessing = true;

                    fetch('<?php echo route('flow.checkout.cash'); ?>', {
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
                            note: this.storeNote,
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
                            return fetch('<?php echo route('flow.paypal.create'); ?>', {
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
                                    note: self.storeNote,
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

                            return fetch('<?php echo route('flow.paypal.capture'); ?>', {
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
                                    note: self.storeNote,
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
