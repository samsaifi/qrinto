@extends('layouts.app')
@section('title', 'Shopping Cart')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8" x-data="cartManager({{ $cart->subtotal }}, {{ $cart->total }}, {{ $cart->item_count }}, {{ $cart->discount }})">
    <h1 class="font-display font-bold text-3xl text-surface-900 mb-8">Shopping Cart</h1>

    @if($cart->items->count() > 0)
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
            @foreach($cart->items as $item)
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 flex gap-4"
                 x-data="cartItem({{ $item->id }}, {{ $item->quantity }}, {{ $item->total }})"
                 x-show="!isRemoved"
                 x-transition>
                <!-- Thumbnail -->
                <div class="w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden bg-surface-100">
                    @if(isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print' && isset($item->customization_data['upload_url']))
                    <img src="{{ $item->customization_data['upload_url'] }}" alt="Custom Print Design" class="w-full h-full object-contain bg-white">
                    @elseif(isset($item->customization_data['preview_url']))
                    <img src="{{ $item->customization_data['preview_url'] }}" alt="Custom Design" class="w-full h-full object-cover">
                    @elseif($item->product && $item->product->featured_image_url)
                    <img src="{{ $item->product->featured_image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center"><i data-lucide="image" class="w-8 h-8 text-surface-300"></i></div>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-display font-semibold text-surface-900">
                                @if(isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print')
                                    <span class="text-brand-600">Custom Print</span>
                                    <span class="block text-xs text-surface-500 mt-0.5">{{ $item->customization_data['size_label'] ?? '' }} ({{ $item->customization_data['size_dimensions'] ?? '' }})</span>
                                @elseif($item->product)
                                    <a href="{{ route('products.show', $item->product) }}" class="hover:text-brand-600 transition">{{ $item->product->name }}</a>
                                @else
                                    <span>Custom Item</span>
                                @endif
                            </h3>
                            <!-- Customization Summary -->
                            @if($item->customization_data)
                            <div class="mt-1 space-y-0.5">
                                @if(isset($item->customization_data['selected_labels']))
                                @foreach($item->customization_data['selected_labels'] as $key => $label)
                                <p class="text-xs text-surface-500"><span class="capitalize">{{ str_replace('-', ' ', $key) }}:</span> {{ $label }}</p>
                                @endforeach
                                @endif
                                @if(isset($item->customization_data['uploaded_images']))
                                <p class="text-xs text-surface-500">Photos: {{ count($item->customization_data['uploaded_images']) }} uploaded</p>
                                @endif
                            </div>
                            @endif
                            
                            <!-- Options Summary -->
                            @if($item->selected_options && count($item->selected_options) > 0 && !(isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print'))
                            @php
                                $selectedOptionValues = \App\Models\ProductOptionValue::with('optionGroup')->whereIn('id', $item->selected_options)->get();
                            @endphp
                            <div class="mt-2 space-y-1">
                                @foreach($selectedOptionValues as $optVal)
                                    <p class="text-xs text-surface-600">
                                        <span class="font-medium text-surface-700">{{ $optVal->optionGroup->name }}:</span> 
                                        {{ $optVal->label }}
                                    </p>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <button type="button" @click="removeItem" :disabled="isSubmitting" class="p-2 text-surface-400 hover:text-red-500 transition rounded-lg hover:bg-red-50">
                            <i data-lucide="trash-2" class="w-4 h-4" :class="isSubmitting ? 'animate-pulse opacity-50' : ''"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between mt-3">
                        <!-- Quantity -->
                        <div class="flex items-center gap-2">
                            <button type="button" @click="changeQuantity(quantity - 1)"
                                    class="w-8 h-8 rounded-lg bg-surface-100 hover:bg-surface-200 flex items-center justify-center text-surface-600 font-bold transition disabled:opacity-50"
                                    :disabled="isSubmitting || quantity <= 1">−</button>
                            <span class="w-8 text-center font-semibold text-surface-800" x-text="quantity">{{ $item->quantity }}</span>
                            <button type="button" @click="changeQuantity(quantity + 1)"
                                    class="w-8 h-8 rounded-lg bg-surface-100 hover:bg-surface-200 flex items-center justify-center text-surface-600 font-bold transition disabled:opacity-50"
                                    :disabled="isSubmitting">+</button>
                        </div>
                        <span class="font-bold text-surface-900"><span x-text="__price(totalPrice, 0)">{{ \App\Services\CurrencyService::formatOnly($item->total, 0) }}</span></span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Summary Sidebar -->
        <div>
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 sticky top-28">
                <h3 class="font-display font-semibold text-lg text-surface-900 mb-5">Order Summary</h3>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-surface-500">Subtotal (<span x-text="itemCount"></span> items)</span>
                        <span class="text-surface-800 font-medium"><span x-text="__price(subtotal, 0)">{{ \App\Services\CurrencyService::formatOnly($cart->subtotal, 0) }}</span></span>
                    </div>
                    <div class="flex justify-between text-sm" x-show="discount > 0" x-cloak>
                        <span class="text-accent-600">Discount</span>
                        <span class="text-accent-600 font-medium"><span x-text="'-' + __price(discount, 0)">-{{ \App\Services\CurrencyService::formatOnly($cart->discount, 0) }}</span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-surface-500">Shipping</span>
                        <span class="text-accent-600 font-medium">FREE</span>
                    </div>
                </div>

                <div class="border-t border-surface-100 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="font-display font-bold text-surface-900">Total</span>
                        <span class="text-2xl font-bold text-brand-600"><span x-text="__price(total, 0)">{{ \App\Services\CurrencyService::formatOnly($cart->total, 0) }}</span></span>
                    </div>
                </div>

                <!-- Coupon -->
                <div class="mb-6" x-data="{ showCoupon: false }">
                    @if($cart->coupon)
                    <div class="flex items-center justify-between p-3 bg-accent-50 rounded-xl border border-accent-200">
                        <div class="flex items-center gap-2">
                            <i data-lucide="tag" class="w-4 h-4 text-accent-600"></i>
                            <span class="text-sm font-semibold text-accent-700">{{ $cart->coupon->code }}</span>
                        </div>
                        <form action="{{ route('cart.removeCoupon') }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </div>
                    @else
                    <button @click="showCoupon = !showCoupon" class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                        <i data-lucide="tag" class="w-4 h-4"></i> Have a coupon code?
                    </button>
                    <form x-show="showCoupon" x-transition action="{{ route('cart.applyCoupon') }}" method="POST" class="mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="Enter code" required
                               class="flex-1 rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Apply</button>
                    </form>
                    @endif
                </div>

                <form action="{{ route('checkout.express') }}" method="POST">
                    @csrf
                    <button type="submit" class="block w-full text-center px-6 py-4 bg-gradient-to-r from-brand-600 to-brand-700 text-white font-bold rounded-2xl hover:from-brand-700 hover:to-brand-800 shadow-xl shadow-brand-200 transition-all transform hover:-translate-y-0.5">
                        Proceed to Payment
                    </button>
                </form>

                <a href="{{ route('products.index') }}" class="block text-center text-sm text-surface-500 hover:text-brand-600 mt-4 transition">
                    ← Continue Shopping
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-20 bg-white rounded-2xl border border-surface-100 shadow-card">
        <div class="w-20 h-20 rounded-2xl bg-surface-100 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="shopping-bag" class="w-10 h-10 text-surface-300"></i>
        </div>
        <h2 class="font-display font-semibold text-xl text-surface-700 mb-3">Your cart is empty</h2>
        <p class="text-surface-500 mb-6">Looks like you haven't added any items yet.</p>
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
            <i data-lucide="sparkles" class="w-4 h-4"></i> Start Shopping
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartManager', (initialSubtotal, initialTotal, initialItemCount, initialDiscount) => ({
            subtotal: initialSubtotal,
            total: initialTotal,
            itemCount: initialItemCount,
            discount: initialDiscount,
            
            updateSummary(data) {
                this.subtotal = data.subtotal;
                this.total = data.total;
                this.itemCount = data.cart_count;
                this.discount = data.discount;
                
                // Update header cart badge
                const headerCartBadge = document.getElementById('cart-count');
                if (headerCartBadge) {
                    headerCartBadge.textContent = data.cart_count;
                    if (data.cart_count === 0) {
                        headerCartBadge.style.display = 'none';
                    } else {
                        headerCartBadge.style.display = 'flex';
                    }
                } else if (data.cart_count > 0) {
                    const cartBtn = document.getElementById('cart-btn');
                    if (cartBtn) {
                        const badge = document.createElement('span');
                        badge.id = 'cart-count';
                        badge.className = 'absolute -top-1 -right-1 bg-brand-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center animate-pulse-soft';
                        badge.textContent = data.cart_count;
                        cartBtn.appendChild(badge);
                    }
                }
            },
            
            formatPrice(amount) {
                return new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(amount);
            }
        }));

        Alpine.data('cartItem', (id, initialQuantity, initialTotal) => ({
            id: id,
            quantity: initialQuantity,
            totalPrice: initialTotal,
            isSubmitting: false,
            isRemoved: false,
            
            async changeQuantity(newQuantity) {
                if (newQuantity < 1) return;
                this.isSubmitting = true;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                try {
                    const resp = await fetch(`{{ url('cart/update') }}/${this.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ quantity: newQuantity })
                    });
                    
                    const data = await resp.json();
                    if (data.success) {
                        this.quantity = newQuantity;
                        if(data.item_total !== undefined) {
                            this.totalPrice = data.item_total;
                        }
                        this.updateSummary(data);
                    } else {
                        alert(data.message || 'Failed to update quantity.');
                    }
                } catch(e) {
                    console.error('Network Error:', e);
                    alert('Network error when updating quantity.');
                } finally {
                    this.isSubmitting = false;
                }
            },
            
            async removeItem() {
                if (!confirm('Are you sure you want to remove this item?')) return;
                this.isSubmitting = true;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                try {
                    const resp = await fetch(`{{ url('cart/remove') }}/${this.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await resp.json();
                    if (data.success) {
                        this.isRemoved = true;
                        this.updateSummary(data);
                    } else {
                        alert(data.message || 'Failed to remove item.');
                    }
                } catch(e) {
                     console.error('Network Error:', e);
                     alert('Network error when removing item.');
                } finally {
                    this.isSubmitting = false;
                }
            }
        }));
    });
</script>
@endpush
