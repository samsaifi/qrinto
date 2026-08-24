@extends('layouts.quick-flow-pc')

@section('title', 'Local Print Studio | Qrinto')
@section('header_title', 'Local Print')
@section('meta_description', 'Upload your photo or artwork for direct local print and fast store pickup.')
@section('canonical_url', route('flow.qrinto'))

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
            padding: 1rem;
        }

        .paypal-sheet {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border-radius: 1.5rem;
            padding: 1.75rem;
            max-height: 88vh;
            overflow-y: auto;
            box-shadow: 0 20px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .processing-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
        }

        .processing-overlay .spinner {
            width: 48px;
            height: 48px;
            border: 3px solid #e2e8f0;
            border-top: 3px solid #287d3c;
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
    <div class="bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans text-slate-900" x-data="qrintoFlow()">

        {{-- Fullscreen Processing Overlay --}}
        <div x-show="isProcessing" class="processing-overlay" style="display: none;"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <template x-if="!paymentSuccess">
                <div class="text-center">
                    <div class="spinner mx-auto mb-5"></div>
                    <h3 class="text-xl font-extrabold text-slate-900">Processing Order</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">Please wait while your order is being sent...</p>
                </div>
            </template>
            <template x-if="paymentSuccess">
                <div class="text-center">
                    <div class="w-14 h-14 bg-[#eaf3ea] text-[#287d3c] rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900">Order Confirmed</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">Redirecting to order confirmation details...</p>
                </div>
            </template>
        </div>

        <div class="max-w-[1240px] mx-auto">

            {{-- Quiet Step Navigation Bar --}}
            <div class="flex items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                    <a href="{{ route('flow.index') }}" class="hover:text-slate-700 transition-colors">
                        Shop
                    </a>
                    <span>/</span>
                    <span class="text-slate-900 font-bold">Local Print</span>
                </div>

                {{-- Quiet Step Indicator --}}
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span :class="currentStep === 1 ? 'text-[#287d3c] font-extrabold' : 'text-slate-400'">Upload</span>
                    <span class="text-slate-300">→</span>
                    <span :class="currentStep === 2 ? 'text-[#287d3c] font-extrabold' : 'text-slate-400'">Dimensions</span>
                    <span class="text-slate-300">→</span>
                    <span :class="currentStep === 3 ? 'text-[#287d3c] font-extrabold' : 'text-slate-400'">Review & Pay</span>
                </div>
            </div>

            {{-- Title Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">
                    Local Print Studio
                </h1>
                <p class="text-sm text-slate-500 font-normal mt-1">
                    Upload photo artwork and select print dimensions for same-day store pickup.
                </p>
            </div>

            {{-- STEP 1: UPLOAD DESIGN --}}
            <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-200" class="max-w-xl mx-auto">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 text-center shadow-2xs space-y-6">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Upload Image</h2>
                        <p class="text-xs text-slate-500 font-normal mt-1">
                            Upload a photo or design file (JPG, PNG, or WebP up to 10MB).
                        </p>
                    </div>

                    <div class="relative" @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)">

                        <input type="file" x-ref="fileInput" class="hidden" accept="image/*"
                            @change="handleFileSelect($event)">

                        <div @click="$refs.fileInput.click()"
                            :class="[
                                isDragging ? 'border-slate-400 bg-slate-50' : 'border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300',
                                previewUrl ? 'p-0 border-solid overflow-hidden bg-slate-900' : 'p-8 border-dashed'
                            ]"
                            class="border-2 rounded-xl transition-all cursor-pointer min-h-[220px] flex items-center justify-center">

                            <template x-if="!previewUrl">
                                <div class="space-y-2">
                                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center mx-auto text-slate-400 border border-slate-200 shadow-2xs">
                                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">Click to browse or drag image here</p>
                                        <p class="text-[11px] text-slate-400 font-normal mt-0.5">JPG, PNG, WebP up to 10MB</p>
                                    </div>
                                </div>
                            </template>

                            <template x-if="previewUrl">
                                <div class="w-full h-[260px] relative">
                                    <img :src="previewUrl" class="w-full h-full object-contain rounded-xl">
                                    <button type="button" @click.stop="removeFile()"
                                        class="absolute top-3 right-3 w-8 h-8 bg-white/90 shadow-xs text-rose-600 rounded-lg flex items-center justify-center hover:bg-rose-50 cursor-pointer border border-slate-200"
                                        title="Remove image">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Upload Progress --}}
                        <div x-show="isUploading" x-cloak
                            class="absolute inset-0 bg-white/95 rounded-xl flex flex-col items-center justify-center z-30">
                            <div class="w-8 h-8 border-2 border-slate-300 border-t-[#287d3c] rounded-full animate-spin mb-3"></div>
                            <p class="text-xs font-bold text-slate-800" x-text="`Uploading... ${uploadProgress}%`"></p>
                        </div>
                    </div>

                    <button @click="currentStep = 2" :disabled="!uploadId"
                        :class="!uploadId ? 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200' :
                            'bg-[#287d3c] hover:bg-[#1e5e2d] text-white shadow-2xs cursor-pointer active:scale-95'"
                        class="w-full py-3 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                        <span>Continue to Dimensions</span>
                        <span>→</span>
                    </button>
                </div>
            </div>

            {{-- STEP 2: SIZE & CATEGORY SELECTION --}}
            <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-200"
                class="max-w-3xl mx-auto space-y-6">
                <div class="mb-4">
                    <h2 class="text-lg font-extrabold text-slate-900"
                        x-text="!selectedParent ? 'Select Print Category' : 'Select Paper Dimensions'"></h2>
                    <p class="text-xs text-slate-500 font-normal mt-1"
                        x-text="!selectedParent ? 'Choose product style.' : `Choose paper size for ${selectedParent.name}.`">
                    </p>
                </div>

                {{-- Category Grid --}}
                <div x-show="!selectedParent" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <template x-for="parent in productTypes" :key="parent.id">
                        <button @click="selectedParent = parent"
                            class="bg-white border border-slate-200 hover:border-slate-300 rounded-2xl p-5 flex items-center justify-between transition-all text-left cursor-pointer group shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-50 text-slate-500 rounded-xl flex items-center justify-center shrink-0 border border-slate-200/80">
                                    <template x-if="parent.icon_svg">
                                        <div class="w-5 h-5 fill-current" x-html="parent.icon_svg"></div>
                                    </template>
                                    <template x-if="!parent.icon_svg">
                                        <i data-lucide="layers" class="w-5 h-5"></i>
                                    </template>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-[#287d3c] transition-colors"
                                        x-text="parent.name"></h3>
                                    <p class="text-[11px] text-slate-400 font-normal mt-0.5"
                                        x-text="parent.title || 'Dimensions'"></p>
                                </div>
                            </div>
                            <span class="text-slate-400 group-hover:text-slate-800 transition-colors">→</span>
                        </button>
                    </template>
                </div>

                {{-- Subtype Sizes Grid --}}
                <div x-show="selectedParent" class="space-y-4">
                    <button @click="selectedParent = null; selectedSize = null"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                        <span>← Back to Categories</span>
                    </button>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="size in selectedParent?.subtypes || []" :key="size.id">
                            <button @click="selectedSize = { ...size, label: `${selectedParent.name}: ${size.name}` }"
                                :class="selectedSize?.id === size.id ?
                                    'border-2 border-slate-900 bg-slate-50' :
                                    'border border-slate-200 bg-white hover:border-slate-300'"
                                class="p-4 rounded-2xl transition-all text-left cursor-pointer flex items-center justify-between shadow-2xs">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-xs sm:text-sm" x-text="size.name"></h4>
                                    <p class="text-xs font-mono text-slate-500 mt-1" x-text="size.dimensions"></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-mono font-bold text-sm text-slate-900" x-text="__price(size.price)"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="pt-4 flex items-center gap-3">
                    <button @click="currentStep = 1"
                        class="flex-1 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors cursor-pointer">
                        Back to Upload
                    </button>
                    <button @click="currentStep = 3" :disabled="!selectedSize"
                        :class="!selectedSize ? 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200' :
                            'bg-[#287d3c] hover:bg-[#1e5e2d] text-white shadow-2xs cursor-pointer active:scale-95'"
                        class="flex-1 py-3 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                        <span>Review Order</span>
                        <span>→</span>
                    </button>
                </div>
            </div>

            {{-- STEP 3: FINAL REVIEW & CHECKOUT GRID --}}
            <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-200" class="max-w-4xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    {{-- Left Column: Product & Pickup Info --}}
                    <div class="lg:col-span-7 space-y-6">

                        {{-- Specifications Card --}}
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-4">
                            <h2 class="font-extrabold text-slate-900 text-sm">Print Item Details</h2>

                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-200">
                                    <img :src="previewUrl" class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-slate-900 text-sm" x-text="selectedSize?.label"></h3>
                                    <p class="text-xs font-mono text-slate-500 mt-0.5" x-text="selectedSize?.dimensions"></p>
                                    <div class="mt-2 flex items-center gap-2 text-xs font-medium text-slate-600">
                                        <span>Quantity:</span>
                                        <div class="inline-flex items-center bg-slate-50 rounded-lg border border-slate-200">
                                            <button type="button" @click="quantity = Math.max(1, quantity - 1)"
                                                class="w-6 h-6 flex items-center justify-center text-slate-700 font-bold text-xs hover:bg-slate-100 cursor-pointer">−</button>
                                            <span class="w-7 text-center font-mono font-bold text-slate-900 text-xs" x-text="quantity"></span>
                                            <button type="button" @click="quantity = Math.min(10, quantity + 1)"
                                                class="w-6 h-6 flex items-center justify-center text-slate-700 font-bold text-xs hover:bg-slate-100 cursor-pointer">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pickup Contact Details Form --}}
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-4">
                            <h2 class="font-extrabold text-slate-900 text-sm">Pickup Information</h2>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
                                    <input type="text" x-model="pickupName" placeholder="Full name for pickup counter"
                                        class="w-full bg-slate-50 border border-slate-200 focus:border-slate-400 focus:bg-white rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 outline-none transition-all">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Email Address</label>
                                        <input type="email" x-model="pickupEmail" placeholder="Email for confirmation"
                                            class="w-full bg-slate-50 border border-slate-200 focus:border-slate-400 focus:bg-white rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 outline-none transition-all">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number</label>
                                        <input type="tel" x-model="contactNumber" placeholder="Phone number"
                                            class="w-full bg-slate-50 border border-slate-200 focus:border-slate-400 focus:bg-white rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 outline-none transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Payment Summary --}}
                    <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-4">
                        <h2 class="font-extrabold text-slate-900 text-sm border-b border-slate-100 pb-3">Order Summary</h2>

                        <div class="space-y-2 text-xs font-medium text-slate-600">
                            <div class="flex justify-between items-center">
                                <span>Unit Price</span>
                                <span class="font-mono font-bold text-slate-900" x-text="__price(selectedSize?.price)"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Quantity</span>
                                <span class="font-mono font-bold text-slate-900" x-text="quantity"></span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-100 text-sm font-extrabold text-slate-900">
                                <span>Total Amount</span>
                                <span class="font-mono text-base font-extrabold text-slate-900" x-text="__price(selectedSize?.price * quantity)"></span>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <button type="button" @click="processCheckout('cash')" :disabled="isProcessing || !isValid"
                                :class="!isValid ? 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200' :
                                    'bg-[#287d3c] hover:bg-[#1e5e2d] text-white shadow-2xs cursor-pointer active:scale-95'"
                                class="w-full py-3 rounded-xl font-bold text-xs transition-all">
                                Pay at Store & Pick Up
                            </button>

                            <button type="button" @click="openPaypal()" :disabled="isProcessing || !isValid"
                                class="w-full bg-slate-900 hover:bg-slate-800 disabled:opacity-40 text-white font-bold py-3 rounded-xl text-xs transition-all cursor-pointer">
                                Pay Online via PayPal
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- PayPal Modal Sheet --}}
        <div x-show="showPaypal" class="paypal-overlay" style="display: none;" x-cloak>
            <div class="paypal-sheet text-center space-y-4">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Complete Online Payment</h3>
                    <button @click="showPaypal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
                </div>
                <div id="paypal-button-container" class="pt-2"></div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script
        src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ \App\Services\CurrencyService::getCode() }}">
    </script>
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
                    this.loadState();
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });

                    this.$watch('currentStep', () => {
                        this.saveState();
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
                    this.$watch('previewUrl', () => this.saveState());
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
                            this.uploadId = data.upload_id || (data.upload ? data.upload.id : data.id);
                            this.previewUrl = data.url || (data.upload ? data.upload.url : '');
                            this.isUploading = false;
                        } else {
                            alert('Upload failed');
                            this.isUploading = false;
                        }
                    });

                    xhr.open('POST', '{{ route('flow.upload') }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.send(formData);
                },

                removeFile() {
                    this.uploadId = null;
                    this.previewUrl = '';
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                },

                async processCheckout(mode) {
                    if (!this.isValid) return;

                    this.isProcessing = true;
                    this.processingMode = mode;
                    this.paymentSuccess = false;

                    try {
                        const response = await fetch(
                            '{{ route('flow.qrinto.checkout.cash') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
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
                        if (response.ok && data.success) {
                            this.paymentSuccess = true;
                            this.clearState();
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1200);
                        } else {
                            alert(data.error || data.message || 'Checkout failed');
                            this.isProcessing = false;
                        }
                    } catch (e) {
                        console.error(e);
                        alert('An error occurred while connecting to server.');
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
                    });
                },

                initPaypal() {
                    if (!document.getElementById('paypal-button-container')) return;

                    paypal.Buttons({
                        createOrder: async (data, actions) => {
                            const response = await fetch(
                                '{{ route('flow.qrinto.paypal.create') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
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

                            const response = await fetch(
                                '{{ route('flow.qrinto.paypal.capture') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
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
                            if (response.ok && result.success) {
                                this.paymentSuccess = true;
                                this.clearState();
                                setTimeout(() => {
                                    window.location.href = result.redirect_url;
                                }, 1200);
                            } else {
                                alert(result.error || result.message || 'Payment capture failed');
                                this.isProcessing = false;
                            }
                        }
                    }).render('#paypal-button-container');
                }
            }));
        });
    </script>
@endpush
