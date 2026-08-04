@extends('layouts.app')

@section('title', 'Customize ' . $product->name)
@section('meta_description', 'Customize your ' . $product->name . '. Upload photos, select size, material, and frame options. See live pricing.')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8" x-data="customizer()">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-surface-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-brand-600 transition">Home</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <a href="{{ route('products.show', $product) }}" class="hover:text-brand-600 transition">{{ $product->name }}</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span class="text-surface-800 font-medium">Customize</span>
    </nav>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Left: Customization Steps -->
        <div class="lg:col-span-2 space-y-6">
            <h1 class="font-display font-bold text-2xl lg:text-3xl text-surface-900">Customize Your Print</h1>

            <!-- Step Progress -->
            <div class="flex items-center gap-2 mb-8">
                <template x-for="(stepLabel, index) in stepLabels" :key="index">
                    <div class="flex items-center gap-2">
                        <button @click="goToStep(index)"
                                :class="currentStep === index ? 'bg-brand-600 text-white shadow-lg shadow-brand-200' : (currentStep > index ? 'bg-accent-500 text-white' : 'bg-surface-200 text-surface-500')"
                                class="w-8 h-8 rounded-full text-sm font-bold flex items-center justify-center transition-all">
                            <template x-if="currentStep > index"><span>✓</span></template>
                            <template x-if="currentStep <= index"><span x-text="index + 1"></span></template>
                        </button>
                        <span class="text-xs font-medium hidden sm:inline" :class="currentStep === index ? 'text-brand-600' : 'text-surface-400'" x-text="stepLabel"></span>
                        <template x-if="index < stepLabels.length - 1">
                            <div class="w-8 h-0.5 bg-surface-200 hidden sm:block"><div class="h-full bg-brand-500 transition-all" :style="'width:' + (currentStep > index ? '100%' : '0%')"></div></div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Step 1: Upload Photos -->
            <div x-show="currentStep === 0" x-transition class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                        <i data-lucide="upload" class="w-5 h-5 text-brand-600"></i>
                    </div>
                    <div>
                        <h2 class="font-display font-semibold text-xl text-surface-900">Upload Your Photos</h2>
                        <p class="text-sm text-surface-500">Upload <span class="font-medium" x-text="minImages + '-' + maxImages"></span> high quality photos (JPEG, PNG, WebP)</p>
                    </div>
                </div>

                <!-- Upload Zone -->
                <div class="border-2 border-dashed rounded-2xl p-8 text-center transition-colors"
                     :class="isDragging ? 'border-brand-500 bg-brand-50' : 'border-surface-300 bg-surface-50'"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)">
                    <div class="w-16 h-16 rounded-2xl bg-brand-100 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="image-plus" class="w-8 h-8 text-brand-500"></i>
                    </div>
                    <p class="text-surface-600 font-medium mb-2">Drag & drop your photos here</p>
                    <p class="text-sm text-surface-400 mb-4">or click to browse</p>
                    <label class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 cursor-pointer transition shadow-lg shadow-brand-200">
                        <i data-lucide="plus" class="w-4 h-4"></i> Select Photos
                        <input type="file" accept="image/jpeg,image/png,image/webp" multiple @change="handleFiles($event)" class="hidden">
                    </label>
                    <p class="text-xs text-surface-400 mt-3">Max 10MB per image</p>
                </div>

                <!-- Uploaded Preview -->
                <div x-show="uploadedImages.length > 0" class="mt-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-semibold text-surface-700">Uploaded Photos (<span x-text="uploadedImages.length"></span>/<span x-text="maxImages"></span>)</h3>
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        <template x-for="(img, index) in uploadedImages" :key="img.id">
                            <div class="relative group aspect-square rounded-xl overflow-hidden border border-surface-200 shadow-sm">
                                <img :src="img.url" class="w-full h-full object-cover">
                                <button @click="removeImage(index)"
                                        class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-xs">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Validation -->
                <div x-show="uploadError" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    <span x-text="uploadError"></span>
                </div>
            </div>

            <!-- Step 2+: Product Options (dynamic) -->
            @foreach($product->optionGroups as $groupIndex => $group)
            <div x-show="currentStep === {{ $groupIndex + 1 }}" x-transition class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-5 h-5 text-brand-600"></i>
                    </div>
                    <div>
                        <h2 class="font-display font-semibold text-xl text-surface-900">Select {{ $group->name }}</h2>
                        @if($group->is_required)
                        <p class="text-sm text-surface-500">Choose one option below</p>
                        @endif
                    </div>
                </div>

                @if($group->display_type === 'buttons')
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($group->values->where('is_active', true) as $value)
                    <button @click="selectOption('{{ $group->slug }}', {{ $value->id }}, '{{ $value->label }}')"
                            :class="selectedOptions['{{ $group->slug }}'] === {{ $value->id }} ? 'ring-2 ring-brand-500 bg-brand-50 border-brand-200' : 'bg-white border-surface-200 hover:border-brand-300 hover:bg-brand-50/50'"
                            class="relative p-4 rounded-xl border text-left transition-all group">
                        <span class="text-sm font-semibold text-surface-800 block">{{ $value->label }}</span>
                        @if($value->description)
                        <span class="text-xs text-surface-500 mt-1 block">{{ $value->description }}</span>
                        @endif
                        <span class="text-xs font-medium mt-2 block {{ $value->price_modifier == 0 ? 'text-accent-600' : 'text-brand-600' }}">
                            {{ $value->formatted_price_modifier }}
                        </span>
                        <div x-show="selectedOptions['{{ $group->slug }}'] === {{ $value->id }}"
                             class="absolute top-2 right-2 w-5 h-5 bg-brand-600 text-white rounded-full flex items-center justify-center text-xs">✓</div>
                    </button>
                    @endforeach
                </div>
                @elseif($group->display_type === 'cards')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($group->values->where('is_active', true) as $value)
                    <button @click="selectOption('{{ $group->slug }}', {{ $value->id }}, '{{ $value->label }}')"
                            :class="selectedOptions['{{ $group->slug }}'] === {{ $value->id }} ? 'ring-2 ring-brand-500 bg-brand-50 border-brand-200' : 'bg-white border-surface-200 hover:border-brand-300'"
                            class="relative p-6 rounded-2xl border text-left transition-all">
                        @if($value->image)
                        <img src="{{ asset('storage/' . $value->image) }}" alt="{{ $value->label }}" class="w-12 h-12 rounded-lg object-cover mb-3">
                        @endif
                        <span class="text-base font-semibold text-surface-800 block">{{ $value->label }}</span>
                        @if($value->description)
                        <span class="text-sm text-surface-500 mt-1 block">{{ $value->description }}</span>
                        @endif
                        <span class="text-sm font-bold mt-3 block {{ $value->price_modifier == 0 ? 'text-accent-600' : 'text-brand-600' }}">
                            {{ $value->formatted_price_modifier }}
                        </span>
                    </button>
                    @endforeach
                </div>
                @else
                <select @change="selectOption('{{ $group->slug }}', parseInt($event.target.value), $event.target.options[$event.target.selectedIndex].text)"
                        class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 text-sm py-3">
                    <option value="">Select {{ $group->name }}...</option>
                    @foreach($group->values->where('is_active', true) as $value)
                    <option value="{{ $value->id }}">{{ $value->label }} ({{ $value->formatted_price_modifier }})</option>
                    @endforeach
                </select>
                @endif
            </div>
            @endforeach

            <!-- Last Step: Quantity -->
            <div x-show="currentStep === totalSteps - 1" x-transition class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                        <i data-lucide="hash" class="w-5 h-5 text-brand-600"></i>
                    </div>
                    <div>
                        <h2 class="font-display font-semibold text-xl text-surface-900">Quantity</h2>
                        <p class="text-sm text-surface-500">How many copies do you need?</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button @click="quantity = Math.max(1, quantity - 1); calculatePrice()"
                            class="w-12 h-12 rounded-xl bg-surface-100 hover:bg-surface-200 flex items-center justify-center transition text-lg font-bold text-surface-600">−</button>
                    <input type="number" x-model.number="quantity" @change="calculatePrice()" min="1" max="100"
                           class="w-20 text-center text-xl font-bold rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                    <button @click="quantity = Math.min(100, quantity + 1); calculatePrice()"
                            class="w-12 h-12 rounded-xl bg-surface-100 hover:bg-surface-200 flex items-center justify-center transition text-lg font-bold text-surface-600">+</button>
                </div>
            </div>

            <!-- Step Navigation -->
            <div class="flex justify-between items-center pt-4">
                <button @click="prevStep()" x-show="currentStep > 0"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-surface-100 text-surface-700 font-semibold rounded-xl hover:bg-surface-200 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Previous
                </button>
                <div></div>
                <button @click="nextStep()" x-show="currentStep < totalSteps - 1"
                        :disabled="!canProceed()"
                        :class="canProceed() ? 'bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-200' : 'bg-surface-300 cursor-not-allowed'"
                        class="inline-flex items-center gap-2 px-8 py-3 text-white font-semibold rounded-xl transition">
                    Next <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
                <button @click="addToCart()" x-show="currentStep === totalSteps - 1"
                        :disabled="!canAddToCart()"
                        :class="canAddToCart() ? 'bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 shadow-xl shadow-brand-200' : 'bg-surface-300 cursor-not-allowed'"
                        class="inline-flex items-center gap-2 px-8 py-4 text-white font-bold rounded-2xl transition-all transform hover:-translate-y-0.5">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i> Add to Cart — <span x-text="formattedTotalPrice"></span>
                </button>
            </div>
        </div>

        <!-- Right: Live Summary -->
        <div class="lg:col-span-1">
            <div class="sticky top-28 space-y-6">
                <!-- Price Card -->
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <h3 class="font-display font-semibold text-lg text-surface-900 mb-4">Order Summary</h3>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-surface-500">Product</span>
                            <span class="text-surface-800 font-medium">{{ $product->name }}</span>
                        </div>

                        <template x-for="(label, key) in selectedLabels" :key="key">
                            <div class="flex justify-between text-sm">
                                <span class="text-surface-500 capitalize" x-text="key.replace(/-/g, ' ')"></span>
                                <span class="text-surface-800 font-medium" x-text="label"></span>
                            </div>
                        </template>

                        <div class="flex justify-between text-sm">
                            <span class="text-surface-500">Photos</span>
                            <span class="text-surface-800 font-medium" x-text="uploadedImages.length + ' uploaded'"></span>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-surface-500">Quantity</span>
                            <span class="text-surface-800 font-medium" x-text="quantity"></span>
                        </div>
                    </div>

                    <div class="border-t border-surface-100 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-surface-500">Unit Price</span>
                            <span class="text-lg font-semibold text-surface-800" x-text="formattedUnitPrice">{{ \App\Services\CurrencyService::format($product->base_price, 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="font-display font-bold text-surface-900">Total</span>
                            <span class="text-2xl font-bold text-brand-600" x-text="formattedTotalPrice">{{ \App\Services\CurrencyService::format($product->base_price, 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Trust -->
                <div class="bg-surface-50 rounded-2xl p-5 space-y-3">
                    @foreach([
                        ['icon' => 'lock', 'text' => 'Secure checkout & payment'],
                        ['icon' => 'award', 'text' => 'Premium HD quality printing'],
                        ['icon' => 'package', 'text' => 'Careful packaging & delivery'],
                        ['icon' => 'refresh-cw', 'text' => 'Easy returns within 7 days'],
                    ] as $trust)
                    <div class="flex items-center gap-3">
                        <i data-lucide="{{ $trust['icon'] }}" class="w-4 h-4 text-accent-600 flex-shrink-0"></i>
                        <span class="text-sm text-surface-600">{{ $trust['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function customizer() {
    const optionGroups = @json($product->optionGroups->map(fn($g) => ['slug' => $g->slug, 'name' => $g->name, 'required' => $g->is_required]));
    const stepLabels = ['Upload', ...optionGroups.map(g => g.name), 'Quantity'];

    return {
        productId: {{ $product->id }},
        basePrice: {{ $product->active_price }},
        minImages: {{ $product->min_images }},
        maxImages: {{ $product->max_images }},
        currentStep: 0,
        totalSteps: stepLabels.length,
        stepLabels: stepLabels,
        optionGroups: optionGroups,

        // Upload state
        uploadedImages: [],
        isDragging: false,
        uploadError: '',
        isUploading: false,

        // Options state
        selectedOptions: {},
        selectedLabels: {},

        // Quantity & Price
        quantity: 1,
        unitPrice: {{ $product->active_price }},
        totalPrice: {{ $product->active_price }},

        get formattedUnitPrice() {
            return __price(this.unitPrice, 0);
        },
        get formattedTotalPrice() {
            return __price(this.totalPrice, 0);
        },

        goToStep(step) {
            if (step <= this.currentStep) {
                this.currentStep = step;
            }
        },
        nextStep() {
            if (this.canProceed() && this.currentStep < this.totalSteps - 1) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        },
        canProceed() {
            if (this.currentStep === 0) {
                return this.uploadedImages.length >= this.minImages;
            }
            const groupIndex = this.currentStep - 1;
            if (groupIndex < this.optionGroups.length) {
                const group = this.optionGroups[groupIndex];
                return !group.required || this.selectedOptions[group.slug] !== undefined;
            }
            return true;
        },
        canAddToCart() {
            if (this.uploadedImages.length < this.minImages) return false;
            for (const group of this.optionGroups) {
                if (group.required && !this.selectedOptions[group.slug]) return false;
            }
            return this.quantity > 0;
        },

        // Upload handlers
        async handleFiles(event) {
            const files = Array.from(event.target.files);
            for (const file of files) {
                await this.uploadFile(file);
            }
            event.target.value = '';
        },
        async handleDrop(event) {
            this.isDragging = false;
            const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            for (const file of files) {
                await this.uploadFile(file);
            }
        },
        async uploadFile(file) {
            this.uploadError = '';
            if (this.uploadedImages.length >= this.maxImages) {
                this.uploadError = `Maximum ${this.maxImages} images allowed.`;
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                this.uploadError = 'File size must be less than 10MB.';
                return;
            }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.uploadError = 'Only JPEG, PNG, and WebP formats are allowed.';
                return;
            }

            this.isUploading = true;
            const formData = new FormData();
            formData.append('image', file);

            try {
                const res = await fetch('{{ route("upload.store") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData,
                });
                const data = await res.json();
                if (data.success) {
                    this.uploadedImages.push(data.upload);
                } else {
                    this.uploadError = data.message || 'Upload failed.';
                }
            } catch (e) {
                this.uploadError = 'Upload failed. Please try again.';
            }
            this.isUploading = false;
        },
        async removeImage(index) {
            const img = this.uploadedImages[index];
            try {
                await fetch(`/upload/${img.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                });
            } catch (e) {}
            this.uploadedImages.splice(index, 1);
        },

        // Option selection
        selectOption(groupSlug, valueId, label) {
            this.selectedOptions[groupSlug] = valueId;
            this.selectedLabels[groupSlug] = label;
            this.calculatePrice();
        },

        // Price calculation
        async calculatePrice() {
            const optionIds = Object.values(this.selectedOptions);
            try {
                const res = await fetch(`/product/{{ $product->slug }}/calculate-price`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ options: optionIds, quantity: this.quantity }),
                });
                const data = await res.json();
                this.unitPrice = data.unit_price;
                this.totalPrice = data.total_price;
            } catch (e) {
                console.error('Price calc error:', e);
            }
        },

        // Add to cart
        async addToCart() {
            if (!this.canAddToCart()) return;

            const formData = {
                product_id: this.productId,
                quantity: this.quantity,
                selected_options: this.selectedOptions,
                customization_data: {
                    uploaded_images: this.uploadedImages.map(img => img.url),
                    selected_labels: this.selectedLabels,
                    upload_ids: this.uploadedImages.map(img => img.id),
                },
            };

            try {
                const res = await fetch('{{ route("flow.cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(formData),
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = '{{ route("flow.cart.index") }}';
                }
            } catch (e) {
                console.error('Add to cart error:', e);
            }
        },
    };
}
</script>
@endpush
@endsection
