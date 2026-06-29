@extends('layouts.quick-flow-pc')

@section('title', 'Custom Print')
@section('header_title', 'Custom Print')

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
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
    }

    .processing-overlay .spinner {
        width: 70px;
        height: 70px;
        border: 6px solid #f1f5f9;
        border-top: 6px solid #0ea5e9;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .success-check {
        width: 80px;
        height: 80px;
        background: #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
    }

    @keyframes popScale {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-24 text-center min-h-screen pt-4" x-data="qrintoFlow()">
    <!-- Processing Overlay (Moved out of teleport for better visibility) -->
    <div x-show="isProcessing" 
         class="processing-overlay" 
         style="display: none;" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100">
        <template x-if="!paymentSuccess">
            <div class="text-center">
                <div class="spinner mx-auto mb-6"></div>
                <h3 class="text-[28px] font-black text-slate-900 tracking-tight">Processing Payment</h3>
                <p class="text-slate-500 font-bold text-lg mt-1">Please wait while we confirm your order...</p>
            </div>
        </template>
        <template x-if="paymentSuccess">
            <div class="text-center">
                <div class="success-check mx-auto mb-6">
                    <i data-lucide="check" class="w-12 h-12 text-white"></i>
                </div>
                <h3 class="text-[28px] font-black text-slate-900 tracking-tight">Order Confirmed!</h3>
                <p class="text-slate-500 font-bold text-lg mt-1">Redirecting you to your receipt...</p>
            </div>
        </template>
    </div>

    <!-- Step Indicator -->
    <div class="flex justify-center items-center gap-2 mb-8">
        <template x-for="i in 3">
            <div class="h-1.5 rounded-full transition-all duration-500"
                :class="currentStep >= i ? 'w-8 bg-brand-600' : 'w-4 bg-slate-200'"></div>
        </template>
    </div>

    <!-- Step 1: Upload -->
    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" class="space-y-6">
        <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center shadow-lg shadow-brand-100 mb-6 mx-auto">
            <i data-lucide="upload-cloud" class="w-8 h-8 text-brand-600"></i>
        </div>

        <h1 class="text-2xl font-black text-slate-900 mb-2">Upload Your Design</h1>
        <p class="text-slate-500 text-sm mb-8 px-4">Upload your print-ready file (JPG, PNG, or WebP). We'll print it exactly as provided.</p>

        <div class="relative group"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop($event)">

            <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="handleFileSelect($event)">

            <div @click="$refs.fileInput.click()"
                :class="[
                    isDragging ? 'border-brand-500 bg-brand-50 scale-[1.02]' : 'border-slate-200 bg-white',
                    previewUrl ? 'p-0 border-solid overflow-hidden' : 'p-12 border-dashed'
                 ]"
                class="border-2 rounded-[32px] transition-all cursor-pointer hover:border-brand-400 relative min-h-[300px] flex items-center justify-center">

                <template x-if="!previewUrl">
                    <div class="space-y-4">
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto text-slate-400 group-hover:text-brand-500 transition-colors">
                            <i data-lucide="plus" class="w-6 h-6"></i>
                        </div>
                        <p class="font-bold text-slate-900">Tap to select or drag & drop</p>
                        <p class="text-xs text-slate-400">Max file size: 10MB</p>
                    </div>
                </template>

                <template x-if="previewUrl">
                    <div class="w-full h-full">
                        <img :src="previewUrl" class="w-full h-full object-cover rounded-[30px]">
                        <div class="absolute top-4 right-4 w-10 h-10 bg-white/90 backdrop-blur shadow-xl text-red-500 rounded-full flex items-center justify-center transition-transform hover:scale-110 active:scale-95 z-20" @click.stop="removeFile()">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Uploading Overlay -->
            <div x-show="isUploading" class="absolute inset-0 bg-white/90 backdrop-blur-sm rounded-[32px] flex flex-col items-center justify-center z-10">
                <div class="w-12 h-12 border-4 border-slate-100 border-t-brand-600 rounded-full animate-spin mb-4"></div>
                <p class="font-black text-slate-900 text-sm" x-text="`Uploading ${uploadProgress}%`"></p>
            </div>
        </div>

        <div class="pt-8">
            <button @click="currentStep = 2"
                :disabled="!uploadId"
                :class="!uploadId ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-brand-600 text-white shadow-lg shadow-brand-100 active:scale-95'"
                class="w-full py-5 rounded-2xl font-black text-sm uppercase tracking-widest transition-all">
                Continue to Sizes
            </button>
        </div>
    </div>

    <!-- Step 2: Size Selection -->
    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" class="space-y-6">
        <h1 class="text-2xl font-black text-slate-900 mb-2" x-text="!selectedParent ? 'Choose Category' : 'Select Size'"></h1>
        <p class="text-slate-500 text-sm mb-8 px-4" x-text="!selectedParent ? 'Pick the type of product you want to print.' : `Select the perfect dimensions for your ${selectedParent.name}.`"></p>

        <!-- Parent Selection -->
        <div x-show="!selectedParent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="parent in productTypes" :key="parent.id">
                <button @click="selectedParent = parent"
                    class="flex items-center justify-between p-6 rounded-2xl border-2 border-slate-100 bg-white transition-all group hover:border-brand-500 hover:shadow-md text-left active:scale-[0.98]">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-50 text-slate-400 group-hover:bg-brand-50 group-hover:text-brand-600 rounded-2xl flex items-center justify-center transition-colors overflow-hidden">
                            <template x-if="parent.icon_svg">
                                <div class="w-6 h-6 fill-current" x-html="parent.icon_svg"></div>
                            </template>
                            <template x-if="!parent.icon_svg">
                                <i data-lucide="layers" class="w-6 h-6"></i>
                            </template>
                        </div>
                        <div>
                            <p class="font-black text-slate-900" x-text="parent.name"></p>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest" x-text="parent.title || 'Various Sizes'"></p>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-brand-600 group-hover:text-white transition-all">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </div>
                </button>
            </template>
        </div>

        <!-- Subtype Selection -->
        <div x-show="selectedParent" class="space-y-4">
            <button @click="selectedParent = null; selectedSize = null" class="flex items-center gap-2 text-xs font-black text-brand-600 uppercase tracking-widest mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Categories
            </button>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="size in selectedParent?.subtypes || []" :key="size.id">
                    <button @click="selectedSize = { ...size, label: `${selectedParent.name}: ${size.name}` }"
                        :class="selectedSize?.id === size.id ? 'border-brand-500 bg-brand-50 shadow-md ring-2 ring-brand-100' : 'border-slate-100 bg-white'"
                        class="flex items-center justify-between p-5 rounded-2xl border-2 transition-all group text-left">
                        <div class="flex items-center gap-4">
                            <div :class="selectedSize?.id === size.id ? 'bg-brand-600 text-white' : 'bg-slate-50 text-slate-400'"
                                class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors">
                                <i data-lucide="maximize" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="font-black text-slate-900" x-text="size.name"></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="size.dimensions"></p>
                                <template x-if="size.title">
                                    <p class="text-[9px] font-medium text-slate-400 mt-0.5" x-text="size.title"></p>
                                </template>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-brand-600" x-text="__price(size.price)"></p>
                            <template x-if="size.popular">
                                <span class="text-[8px] font-black bg-brand-100 text-brand-600 px-1.5 py-0.5 rounded uppercase">Popular</span>
                            </template>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        <div class="pt-8 flex gap-3">
            <button @click="currentStep = 1" class="flex-1 py-5 rounded-2xl bg-slate-100 text-slate-600 font-black text-sm uppercase tracking-widest">Back</button>
            <button @click="currentStep = 3"
                :disabled="!selectedSize"
                :class="!selectedSize ? 'bg-slate-100 text-slate-400' : 'bg-brand-600 text-white shadow-lg shadow-brand-100 active:scale-95'"
                class="flex-[2] py-5 rounded-2xl font-black text-sm uppercase tracking-widest transition-all">
                Review Order
            </button>
        </div>
    </div>

    <!-- Step 3: Checkout -->
    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" class="space-y-6">
        <h1 class="text-2xl font-black text-slate-900 mb-2">Final Review</h1>

        <div class="bg-white rounded-3xl border border-slate-100 p-6 text-left space-y-6 shadow-sm">
            <div class="flex gap-4">
                <img :src="previewUrl" class="w-20 h-20 object-cover rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex-1 flex flex-col justify-center">
                    <p class="font-black text-slate-900" x-text="selectedSize?.label"></p>
                    <p class="text-xs font-bold text-slate-400 uppercase" x-text="selectedSize?.dimensions"></p>
                    <div class="flex items-center gap-3 mt-2">
                        <button @click="quantity = Math.max(1, quantity - 1)" class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-600"><i data-lucide="minus" class="w-3 h-3"></i></button>
                        <span class="font-black text-sm" x-text="quantity"></span>
                        <button @click="quantity = Math.min(10, quantity + 1)" class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-600"><i data-lucide="plus" class="w-3 h-3"></i></button>
                    </div>
                </div>
                <div class="text-right flex flex-col justify-center">
                    <p class="font-black text-brand-600 text-lg" x-text="__price(selectedSize?.price * quantity)"></p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-50 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Your Name</label>
                    <input type="text" x-model="pickupName" placeholder="Full Name" class="w-full bg-slate-50 border-0 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</label>
                    <input type="email" x-model="pickupEmail" placeholder="email@example.com" class="w-full bg-slate-50 border-0 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone Number</label>
                    <input type="tel" x-model="contactNumber" placeholder="000-000-0000" class="w-full bg-slate-50 border-0 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Inline Payment Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6">
            <button type="button" 
                    @click="openPaypal()"
                    :disabled="isProcessing || !isValid"
                    :class="isProcessing || !isValid ? 'bg-slate-300 shadow-none scale-100' : 'bg-brand-600 hover:bg-brand-700 active:scale-[0.97] shadow-lg shadow-brand-100'"
                    class="w-full text-white font-extrabold py-5 rounded-2xl transition-all flex items-center justify-center gap-3 text-base uppercase tracking-widest">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span x-text="isValid ? 'Pay Now — ' + __price(selectedSize?.price * quantity) : 'Complete All Info'"></span>
            </button>
            <button type="button" 
                    @click="processCheckout('cash')"
                    :disabled="isProcessing || !isValid"
                    :class="isProcessing || !isValid ? 'bg-slate-50 text-slate-400 border-slate-100 scale-100' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 active:scale-[0.97]'"
                    class="w-full font-extrabold py-5 rounded-2xl border-2 transition-all flex items-center justify-center gap-3 text-base uppercase tracking-widest">
                <i data-lucide="banknote" class="w-5 h-5"></i>
                <span>Pay by Cash at Store</span>
            </button>
            
            <button @click="currentStep = 2" 
                    :disabled="isProcessing"
                    :class="isProcessing ? 'opacity-30 pointer-events-none' : ''"
                    class="md:col-span-2 w-full py-6 text-slate-400 font-bold text-xs uppercase tracking-widest hover:text-slate-600 transition-colors">
                <i data-lucide="arrow-left" class="w-3 h-3 inline-block mr-1"></i> Back to Sizes
            </button>
        </div>
    </div>
</div>

<!-- ═══ PayPal & Processing Modals ═══ -->
<template x-teleport="body">
    <div x-cloak>
        <!-- PayPal Bottom Sheet -->
        <div x-show="showPaypal" class="paypal-overlay" @click.self="showPaypal = false">
            <div class="paypal-sheet" @click.stop>
                <div class="paypal-sheet-handle"></div>

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-extrabold text-slate-900">Pay with PayPal</h3>
                    <button @click="showPaypal = false" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Amount to Pay</p>
                        <p class="text-2xl font-black text-slate-900" x-text="__price(selectedSize?.price * quantity)"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-400 uppercase">Pickup</p>
                        <p class="text-sm font-bold text-slate-700" x-text="pickupName || 'Guest'"></p>
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

    </div>
</template>
</div>

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ \App\Services\CurrencyService::getCode() }}"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('qrintoFlow', () => ({
            currentStep: 1,
            isDragging: false,
            isUploading: false,
            uploadProgress: 0,
            previewUrl: '',
            uploadId: null,
            productTypes: @json($productTypes),
            selectedParent: null,
            selectedSize: null,
            quantity: 1,
            pickupName: '',
            pickupEmail: '',
            contactNumber: '',
            isProcessing: false,
            processingMode: '',
            paymentSuccess: false,
            showPaypal: false,
            paypalRendered: false,

            get isValid() {
                return this.uploadId && this.selectedSize && this.pickupName && this.pickupEmail && this.contactNumber;
            },

            init() {
                // Restore state from local storage
                this.loadState();

                // Initial icons
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });

                this.$watch('currentStep', (val) => {
                    this.saveState();
                    if (val === 3) {
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                });

                this.$watch('selectedParent', () => {
                    this.saveState();
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                });

                this.$watch('selectedSize', () => this.saveState());
                this.$watch('quantity', () => this.saveState());
                this.$watch('pickupName', () => this.saveState());
                this.$watch('pickupEmail', () => this.saveState());
                this.$watch('contactNumber', () => this.saveState());

                this.$watch('previewUrl', () => {
                    this.saveState();
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                });
            },

            saveState() {
                const state = {
                    uploadId: this.uploadId,
                    previewUrl: this.previewUrl,
                    selectedParentId: this.selectedParent ? this.selectedParent.id : null,
                    selectedSizeId: this.selectedSize ? this.selectedSize.id : null,
                    quantity: this.quantity,
                    pickupName: this.pickupName,
                    pickupEmail: this.pickupEmail,
                    contactNumber: this.contactNumber,
                    currentStep: this.currentStep
                };
                localStorage.setItem('qrinto_custom_print_state', JSON.stringify(state));
            },

            loadState() {
                try {
                    const saved = localStorage.getItem('qrinto_custom_print_state');
                    if (saved) {
                        const state = JSON.parse(saved);
                        this.uploadId = state.uploadId;
                        this.previewUrl = state.previewUrl;
                        this.quantity = state.quantity || 1;
                        this.pickupName = state.pickupName || '';
                        this.pickupEmail = state.pickupEmail || '';
                        this.contactNumber = state.contactNumber || '';
                        this.currentStep = state.currentStep || 1;

                        if (state.selectedParentId) {
                            this.selectedParent = this.productTypes.find(p => p.id === state.selectedParentId);
                            if (this.selectedParent && state.selectedSizeId) {
                                this.selectedSize = this.selectedParent.subtypes.find(s => s.id === state.selectedSizeId);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error loading saved state:', e);
                }
            },

            clearState() {
                localStorage.removeItem('qrinto_custom_print_state');
            },

            handleFileSelect(e) {
                const file = e.target.files[0];
                if (file) this.uploadFile(file);
            },

            handleDrop(e) {
                this.isDragging = false;
                const file = e.dataTransfer.files[0];
                if (file) this.uploadFile(file);
            },

            uploadFile(file) {
                if (!file.type.startsWith('image/')) {
                    alert('Please upload an image file.');
                    return;
                }

                this.isUploading = true;
                this.uploadProgress = 0;

                const formData = new FormData();
                formData.append('image', file);

                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        this.uploadId = data.upload.id;
                        this.previewUrl = data.upload.url;
                        this.isUploading = false;
                    } else {
                        alert('Upload failed');
                        this.isUploading = false;
                    }
                });

                xhr.open('POST', '{{ route("qrinto.upload") }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.send(formData);
            },

            removeFile() {
                this.uploadId = null;
                this.previewUrl = '';
                this.$refs.fileInput.value = '';
            },

            async processCheckout(mode) {
                if (!this.isValid) return;

                this.isProcessing = true;
                this.processingMode = mode;
                this.paymentSuccess = false;

                try {
                    const response = await fetch('{{ route("flow-pc.qrinto.checkout.cash") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            upload_id: this.uploadId,
                            size_id: this.selectedSize.id,
                            quantity: this.quantity,
                            pickup_name: this.pickupName,
                            pickup_email: this.pickupEmail,
                            contact_number: this.contactNumber
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.paymentSuccess = true;
                        this.clearState();
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1500);
                    } else {
                        alert(data.error || 'Checkout failed');
                        this.isProcessing = false;
                    }
                } catch (e) {
                    console.error(e);
                    alert('An error occurred');
                    this.isProcessing = false;
                }
            },

            openPaypal() {
                if (!this.isValid) return;
                this.showPaypal = true;

                this.$nextTick(() => {
                    if (!this.paypalRendered) {
                        this.initPaypal();
                        this.paypalRendered = true;
                    }
                    setTimeout(() => {
                        if (window.lucide) lucide.createIcons();
                    }, 200);
                });
            },

            initPaypal() {
                if (!document.getElementById('paypal-button-container')) return;

                paypal.Buttons({
                    createOrder: async (data, actions) => {
                        const response = await fetch('{{ route("flow-pc.qrinto.paypal.create") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                size_id: this.selectedSize.id,
                                quantity: this.quantity
                            })
                        });
                        const order = await response.json();
                        return order.id;
                    },
                    onApprove: async (data, actions) => {
                        this.showPaypal = false;
                        this.isProcessing = true;
                        this.processingMode = 'paypal';
                        this.paymentSuccess = false;

                        const response = await fetch('{{ route("flow-pc.qrinto.paypal.capture") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                paypal_order_id: data.orderID,
                                upload_id: this.uploadId,
                                size_id: this.selectedSize.id,
                                quantity: this.quantity,
                                pickup_name: this.pickupName,
                                pickup_email: this.pickupEmail,
                                contact_number: this.contactNumber
                            })
                        });

                        const result = await response.json();
                        if (result.success) {
                            this.paymentSuccess = true;
                            this.clearState();
                            this.$nextTick(() => {
                                if (window.lucide) lucide.createIcons();
                            });
                            setTimeout(() => {
                                window.location.href = result.redirect_url;
                            }, 1500);
                        } else {
                            alert('Payment capture failed');
                            this.isProcessing = false;
                        }
                    }
                }).render('#paypal-button-container');
            }
        }));
    });
</script>
@endpush
@endsection