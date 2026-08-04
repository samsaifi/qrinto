@extends('layouts.app')

@section('title', 'Custom Print - Upload Your Design')
@section('meta_description', 'Upload your own custom print design and order it in your preferred size. Premium quality printing on demand.')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8" x-data="customPrint()">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-surface-500 mb-8">
        <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Home</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <a href="{{ route('products.index') }}" class="hover:text-brand-600 transition">Products</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span class="text-surface-800 font-medium">Custom Print</span>
    </nav>

    <!-- Page Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-brand-50 text-brand-700 text-sm font-semibold rounded-full mb-4">
            <i data-lucide="upload" class="w-4 h-4"></i>
            Upload & Print
        </div>
        <h1 class="font-display font-bold text-3xl lg:text-4xl text-surface-900 mb-3">
            Custom <span class="text-brand-600">Print</span>
        </h1>
        <p class="text-surface-500 max-w-lg mx-auto">Upload your already customized or edited print design, choose a size, and we'll print it for you on premium quality material.</p>
    </div>

    <!-- Main Content: Steps Flow -->
    <div class="max-w-4xl mx-auto">
        <!-- Progress Steps -->
        <div class="flex items-center justify-center gap-0 mb-12">
            <template x-for="(stepInfo, idx) in steps" :key="idx">
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <div :class="currentStep > idx + 1 ? 'bg-accent-500 text-white border-accent-500' : (currentStep === idx + 1 ? 'bg-brand-600 text-white border-brand-600 shadow-lg shadow-brand-200' : 'bg-white text-surface-400 border-surface-200')"
                             class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all duration-500">
                            <template x-if="currentStep > idx + 1">
                                <i data-lucide="check" class="w-5 h-5"></i>
                            </template>
                            <template x-if="currentStep <= idx + 1">
                                <span x-text="idx + 1"></span>
                            </template>
                        </div>
                        <span :class="currentStep >= idx + 1 ? 'text-surface-800 font-semibold' : 'text-surface-400'"
                              class="text-xs mt-2 transition-colors" x-text="stepInfo"></span>
                    </div>
                    <template x-if="idx < steps.length - 1">
                        <div :class="currentStep > idx + 1 ? 'bg-accent-500' : 'bg-surface-200'"
                             class="w-16 sm:w-24 h-0.5 mx-2 mb-5 transition-colors duration-500"></div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Step 1: Upload Design -->
        <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-3xl border border-surface-100 shadow-premium p-8 lg:p-12">
                <div class="text-center mb-8">
                    <h2 class="font-display font-bold text-2xl text-surface-900 mb-2">Upload Your Design</h2>
                    <p class="text-surface-500">Upload your print-ready design file. We accept JPG, PNG, and WebP formats up to 10MB.</p>
                </div>

                <!-- Upload Zone -->
                <div class="relative">
                    <!-- Drop Zone -->
                    <div x-show="!uploadedFile"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         :class="isDragging ? 'border-brand-500 bg-brand-50 scale-[1.02]' : 'border-surface-200 bg-surface-50 hover:border-brand-300 hover:bg-brand-50/50'"
                         class="border-2 border-dashed rounded-2xl p-12 text-center cursor-pointer transition-all duration-300 transform"
                         @click="$refs.fileInput.click()">
                        <div class="flex flex-col items-center gap-4">
                            <div :class="isDragging ? 'bg-brand-100 text-brand-600' : 'bg-surface-100 text-surface-400'"
                                 class="w-20 h-20 rounded-2xl flex items-center justify-center transition-all duration-300">
                                <i data-lucide="cloud-upload" class="w-10 h-10"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-surface-800 text-lg mb-1">
                                    <span x-show="!isDragging">Drag & drop your design here</span>
                                    <span x-show="isDragging" class="text-brand-600">Release to upload!</span>
                                </p>
                                <p class="text-surface-500 text-sm">or <span class="text-brand-600 font-semibold underline underline-offset-2">browse files</span></p>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-surface-400">
                                <span class="flex items-center gap-1"><i data-lucide="image" class="w-3 h-3"></i> JPG, PNG, WebP</span>
                                <span class="flex items-center gap-1"><i data-lucide="hard-drive" class="w-3 h-3"></i> Max 10MB</span>
                            </div>
                        </div>
                    </div>

                    <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" accept="image/jpeg,image/png,image/webp" class="hidden">

                    <!-- Upload Progress -->
                    <div x-show="isUploading" class="border-2 border-brand-200 bg-brand-50/50 rounded-2xl p-12 text-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 rounded-full border-4 border-brand-200 border-t-brand-600 animate-spin"></div>
                            <p class="font-semibold text-surface-800">Uploading your design...</p>
                            <div class="w-64 h-2 bg-brand-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-brand-500 to-brand-600 rounded-full transition-all duration-300"
                                     :style="'width: ' + uploadProgress + '%'"></div>
                            </div>
                            <p class="text-sm text-surface-500" x-text="uploadProgress + '% complete'"></p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div x-show="uploadedFile" class="border-2 border-accent-200 bg-accent-50/30 rounded-2xl p-8 text-center">
                        <div class="flex flex-col items-center gap-6">
                            <div class="relative group">
                                <div class="w-64 h-64 rounded-2xl overflow-hidden shadow-premium border-4 border-white">
                                    <img :src="previewUrl" class="w-full h-full object-contain bg-white" alt="Your design preview">
                                </div>
                                <button @click="removeFile()"
                                        class="absolute -top-3 -right-3 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition-all transform hover:scale-110 opacity-0 group-hover:opacity-100">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center gap-2 mb-1">
                                    <i data-lucide="check-circle" class="w-5 h-5 text-accent-600"></i>
                                    <span class="font-semibold text-accent-700">Design uploaded successfully!</span>
                                </div>
                                <p class="text-sm text-surface-500" x-text="uploadedFileName"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Button -->
                <div class="mt-8 flex justify-end">
                    <button @click="goToStep(2)" :disabled="!uploadedFile"
                            :class="uploadedFile ? 'bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-200 hover:shadow-brand-300 transform hover:-translate-y-0.5' : 'bg-surface-200 text-surface-400 cursor-not-allowed'"
                            class="inline-flex items-center gap-2 px-8 py-3.5 text-white font-semibold rounded-xl transition-all duration-300">
                        Continue to Size Selection <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Select Size -->
        <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-3xl border border-surface-100 shadow-premium p-8 lg:p-12">
                <div class="text-center mb-8">
                    <h2 class="font-display font-bold text-2xl text-surface-900 mb-2">Choose Your Size</h2>
                    <p class="text-surface-500">Select from our available print sizes. Each size is priced for premium quality output.</p>
                </div>

                <!-- Size Options -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <template x-for="size in sizes" :key="size.id">
                        <button @click="selectSize(size)"
                                :class="selectedSize && selectedSize.id === size.id
                                    ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-200 shadow-lg scale-[1.03]'
                                    : 'border-surface-200 bg-white hover:border-brand-300 hover:shadow-md'"
                                class="relative border-2 rounded-2xl p-6 text-center transition-all duration-300 transform group cursor-pointer">
                            <!-- Popular Badge -->
                            <template x-if="size.popular">
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-gradient-to-r from-brand-500 to-brand-600 text-white text-xs font-bold rounded-full shadow-lg">
                                    Popular
                                </span>
                            </template>

                            <!-- Size Icon -->
                            <div :class="selectedSize && selectedSize.id === size.id ? 'bg-brand-100 text-brand-600' : 'bg-surface-100 text-surface-400 group-hover:bg-brand-50 group-hover:text-brand-500'"
                                 class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4 transition-all duration-300">
                                <i data-lucide="maximize-2" class="w-7 h-7"></i>
                            </div>

                            <!-- Size Label -->
                            <h3 class="font-display font-bold text-xl text-surface-900 mb-1" x-text="size.label"></h3>
                            <p class="text-xs text-surface-400 mb-3" x-text="size.dimensions"></p>

                            <!-- Price -->
                            <div :class="selectedSize && selectedSize.id === size.id ? 'text-brand-700' : 'text-surface-900'"
                                 class="font-bold text-2xl transition-colors">
                                $<span x-text="size.price.toFixed(2)"></span>
                            </div>

                            <!-- Selected Checkmark -->
                            <div x-show="selectedSize && selectedSize.id === size.id"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="scale-0"
                                 x-transition:enter-end="scale-100"
                                 class="absolute top-3 right-3 w-6 h-6 bg-brand-600 text-white rounded-full flex items-center justify-center">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                        </button>
                    </template>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-8 flex justify-between">
                    <button @click="goToStep(1)"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition-all">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i> Back
                    </button>
                    <button @click="goToStep(3)" :disabled="!selectedSize"
                            :class="selectedSize ? 'bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-200 hover:shadow-brand-300 transform hover:-translate-y-0.5' : 'bg-surface-200 text-surface-400 cursor-not-allowed'"
                            class="inline-flex items-center gap-2 px-8 py-3.5 text-white font-semibold rounded-xl transition-all duration-300">
                        Review & Add to Cart <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Review & Add to Cart -->
        <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-3xl border border-surface-100 shadow-premium p-8 lg:p-12">
                <div class="text-center mb-8">
                    <h2 class="font-display font-bold text-2xl text-surface-900 mb-2">Review Your Order</h2>
                    <p class="text-surface-500">Double-check your design and selected options before adding to cart.</p>
                </div>

                <!-- Order Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Design Preview -->
                    <div class="flex flex-col items-center">
                        <div class="w-full max-w-sm rounded-2xl overflow-hidden shadow-premium border-4 border-white bg-white">
                            <img :src="previewUrl" class="w-full aspect-square object-contain bg-surface-50" alt="Your design">
                        </div>
                        <p class="text-sm text-surface-500 mt-3 text-center" x-text="uploadedFileName"></p>
                    </div>

                    <!-- Details -->
                    <div class="flex flex-col justify-center">
                        <h3 class="font-display font-bold text-xl text-surface-900 mb-6">Order Details</h3>

                        <div class="space-y-4">
                            <!-- Product -->
                            <div class="flex justify-between items-center py-3 border-b border-surface-100">
                                <span class="text-surface-500 text-sm">Product</span>
                                <span class="font-semibold text-surface-800">Custom Print</span>
                            </div>

                            <!-- Size -->
                            <div class="flex justify-between items-center py-3 border-b border-surface-100">
                                <span class="text-surface-500 text-sm">Size</span>
                                <span class="font-semibold text-surface-800" x-text="selectedSize ? selectedSize.label + ' (' + selectedSize.dimensions + ')' : ''"></span>
                            </div>

                            <!-- Quantity -->
                            <div class="flex justify-between items-center py-3 border-b border-surface-100">
                                <span class="text-surface-500 text-sm">Quantity</span>
                                <div class="flex items-center gap-3">
                                    <button @click="quantity = Math.max(1, quantity - 1)"
                                            class="w-8 h-8 rounded-lg bg-surface-100 hover:bg-surface-200 flex items-center justify-center transition">
                                        <i data-lucide="minus" class="w-4 h-4"></i>
                                    </button>
                                    <span class="font-bold text-lg w-8 text-center" x-text="quantity"></span>
                                    <button @click="quantity = Math.min(10, quantity + 1)"
                                            class="w-8 h-8 rounded-lg bg-surface-100 hover:bg-surface-200 flex items-center justify-center transition">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Unit Price -->
                            <div class="flex justify-between items-center py-3 border-b border-surface-100">
                                <span class="text-surface-500 text-sm">Unit Price</span>
                                <span class="font-semibold text-surface-800" x-text="selectedSize ? '$' + selectedSize.price.toFixed(2) : ''"></span>
                            </div>

                            <!-- Total Price -->
                            <div class="flex justify-between items-center py-4 bg-gradient-to-r from-brand-50 to-accent-50 rounded-xl px-4 -mx-4">
                                <span class="font-semibold text-surface-800">Total</span>
                                <span class="font-display font-bold text-2xl text-brand-700" x-text="selectedSize ? '$' + (selectedSize.price * quantity).toFixed(2) : ''"></span>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <button @click="addToCart()"
                                :disabled="isAddingToCart"
                                class="mt-8 w-full inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-brand-600 to-brand-700 text-white font-bold text-lg rounded-2xl hover:from-brand-700 hover:to-brand-800 shadow-xl shadow-brand-200 hover:shadow-brand-300 transition-all duration-300 transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                id="add-to-cart-btn">
                            <template x-if="!isAddingToCart">
                                <span class="inline-flex items-center gap-3">
                                    <i data-lucide="shopping-cart" class="w-6 h-6"></i>
                                    Add to Cart
                                </span>
                            </template>
                            <template x-if="isAddingToCart">
                                <span class="inline-flex items-center gap-3">
                                    <div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                    Adding...
                                </span>
                            </template>
                        </button>

                        <!-- Back Button -->
                        <button @click="goToStep(2)"
                                class="mt-3 w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition-all">
                            <i data-lucide="arrow-left" class="w-5 h-5"></i> Change Size
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success State -->
        <div x-show="addedToCart" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="bg-white rounded-3xl border border-accent-200 shadow-premium p-12 text-center">
                <div class="w-20 h-20 bg-accent-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                    <i data-lucide="check-circle" class="w-10 h-10 text-accent-600"></i>
                </div>
                <h2 class="font-display font-bold text-2xl text-surface-900 mb-3">Added to Cart!</h2>
                <p class="text-surface-500 mb-8 max-w-md mx-auto">Your custom print has been added to your cart successfully. You can continue shopping or proceed to checkout.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-surface-100 text-surface-700 font-semibold rounded-xl hover:bg-surface-200 transition-all">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i> Continue Shopping
                    </a>
                    <a href="{{ route('flow.cart.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white font-bold rounded-xl hover:from-brand-700 hover:to-brand-800 shadow-lg shadow-brand-200 transition-all transform hover:-translate-y-0.5">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i> View Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function customPrint() {
    return {
        steps: ['Upload Design', 'Select Size', 'Add to Cart'],
        currentStep: 1,
        isDragging: false,
        isUploading: false,
        uploadProgress: 0,
        uploadedFile: null,
        uploadedFileName: '',
        uploadedFilePath: '',
        previewUrl: '',
        selectedSize: null,
        quantity: 1,
        isAddingToCart: false,
        addedToCart: false,
        sizes: @json($sizes),

        goToStep(step) {
            this.currentStep = step;
            window.scrollTo({ top: 0, behavior: 'smooth' });
            this.$nextTick(() => lucide.createIcons());
        },

        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.uploadFile(file);
            }
        },

        uploadFile(file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please upload a JPG, PNG, or WebP image.');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be under 10MB.');
                return;
            }

            this.isUploading = true;
            this.uploadProgress = 0;
            this.uploadedFile = null;

            // Show preview immediately
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
            };
            reader.readAsDataURL(file);

            // Upload to server
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
                    const response = JSON.parse(xhr.responseText);
                    this.uploadedFile = response.upload;
                    this.uploadedFileName = file.name;
                    this.uploadedFilePath = response.upload.url;
                    this.isUploading = false;
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    alert('Upload failed. Please try again.');
                    this.isUploading = false;
                    this.previewUrl = '';
                }
            });

            xhr.addEventListener('error', () => {
                alert('Upload failed. Please try again.');
                this.isUploading = false;
                this.previewUrl = '';
            });

            xhr.open('POST', '{{ route("custom-print.upload") }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send(formData);
        },

        removeFile() {
            this.uploadedFile = null;
            this.uploadedFileName = '';
            this.uploadedFilePath = '';
            this.previewUrl = '';
            this.$refs.fileInput.value = '';
            this.$nextTick(() => lucide.createIcons());
        },

        selectSize(size) {
            this.selectedSize = size;
            this.$nextTick(() => lucide.createIcons());
        },

        async addToCart() {
            if (!this.uploadedFile || !this.selectedSize) return;

            this.isAddingToCart = true;

            try {
                const response = await fetch('{{ route("custom-print.add-to-cart") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        upload_id: this.uploadedFile.id,
                        size: this.selectedSize.id,
                        quantity: this.quantity,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.addedToCart = true;
                    this.currentStep = 0; // hide steps
                    // Update cart count in header
                    const cartCountEl = document.getElementById('cart-count');
                    if (cartCountEl) {
                        cartCountEl.textContent = data.cart_count;
                    } else {
                        const cartBtn = document.getElementById('cart-btn');
                        if (cartBtn) {
                            const badge = document.createElement('span');
                            badge.id = 'cart-count';
                            badge.className = 'absolute -top-1 -right-1 bg-brand-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center animate-pulse-soft';
                            badge.textContent = data.cart_count;
                            cartBtn.appendChild(badge);
                        }
                    }
                    this.$nextTick(() => lucide.createIcons());
                } else {
                    alert(data.message || 'Failed to add to cart. Please try again.');
                }
            } catch (error) {
                alert('Something went wrong. Please try again.');
                console.error(error);
            } finally {
                this.isAddingToCart = false;
            }
        }
    };
}
</script>
@endpush
@endsection
