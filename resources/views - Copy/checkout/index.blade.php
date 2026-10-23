@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="font-display font-bold text-3xl text-surface-900 mb-8">Checkout</h1>

    <form action="{{ route('checkout.process') }}" method="POST" class="grid lg:grid-cols-3 gap-8">
        @csrf

        <div class="lg:col-span-2 space-y-6">
            <!-- Shipping Address -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6" x-data="{ useNew: {{ $addresses->isEmpty() ? 'true' : 'false' }} }">
                <h2 class="font-display font-semibold text-xl text-surface-900 mb-5 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-brand-500"></i> Shipping Address
                </h2>

                @if($addresses->count() > 0)
                <div class="space-y-3 mb-4">
                    @foreach($addresses as $address)
                    <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                           :class="!useNew ? 'hover:border-brand-300' : 'opacity-50'">
                        <input type="radio" name="address_id" value="{{ $address->id }}"
                               @click="useNew = false" {{ $address->is_default ? 'checked' : '' }}
                               class="mt-1 text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="font-semibold text-surface-800">{{ $address->full_name }}</span>
                            <span class="inline-block ml-2 px-2 py-0.5 bg-surface-100 text-xs font-medium text-surface-500 rounded">{{ $address->label }}</span>
                            <p class="text-sm text-surface-500 mt-1">{{ $address->full_address }}</p>
                            <p class="text-sm text-surface-500">Phone: {{ $address->phone }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                <button type="button" @click="useNew = !useNew" class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1 mb-4">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span x-text="useNew ? 'Use saved address' : 'Add new address'"></span>
                </button>
                @endif

                <div x-show="useNew" x-transition>
                    <input type="hidden" name="new_address" :value="useNew ? 1 : 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @guest
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-surface-700 mb-1">Email Address *</label>
                            <input type="email" name="guest_email" value="{{ old('guest_email') }}" :required="useNew" :disabled="!useNew"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" placeholder="For order confirmation">
                        </div>
                        @endguest
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Full Name *</label>
                            <input type="text" name="full_name" value="{{ old('full_name', auth()->user()?->name) }}" :required="useNew" :disabled="!useNew"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Phone *</label>
                            <input type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" :required="useNew" :disabled="!useNew"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-surface-700 mb-1">Address Line 1 *</label>
                            <input type="text" name="address_line_1" value="{{ old('address_line_1') }}" :required="useNew" :disabled="!useNew"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" placeholder="Street, House/Flat no.">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-surface-700 mb-1">Address Line 2</label>
                            <input type="text" name="address_line_2" value="{{ old('address_line_2') }}" :disabled="!useNew"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" placeholder="Landmark, Area">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">City *</label>
                            <input type="text" name="city" value="{{ old('city') }}" :required="useNew" :disabled="!useNew"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">State *</label>
                            <input type="text" name="state" value="{{ old('state') }}" :required="useNew" :disabled="!useNew"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">PIN Code *</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}" :required="useNew" :disabled="!useNew" maxlength="6"
                                   class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1">Country</label>
                            <input type="text" name="country" value="USA" readonly
                                   class="w-full rounded-xl border-surface-200 bg-surface-50 text-surface-500">
                        </div>
                    </div>
                </div>

                @error('address_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                <h2 class="font-display font-semibold text-xl text-surface-900 mb-5 flex items-center gap-2">
                    <i data-lucide="package" class="w-5 h-5 text-brand-500"></i> Order Items
                </h2>
                <div class="space-y-3">
                    @foreach($cart->items as $item)
                    <div class="flex items-center gap-4 p-3 bg-surface-50 rounded-xl">
                        <div class="w-14 h-14 rounded-lg overflow-hidden bg-surface-200 flex-shrink-0">
                            @if(isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print' && isset($item->customization_data['upload_url']))
                            <img src="{{ $item->customization_data['upload_url'] }}" alt="Custom Print Design" class="w-full h-full object-contain bg-white">
                            @elseif(isset($item->customization_data['preview_url']))
                            <img src="{{ $item->customization_data['preview_url'] }}" alt="Custom Design" class="w-full h-full object-cover">
                            @elseif($item->product && $item->product->featured_image_url)
                            <img src="{{ $item->product->featured_image_url }}" alt="" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center"><i data-lucide="image" class="w-6 h-6 text-surface-300"></i></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @if(isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print')
                            <h4 class="text-sm font-semibold text-brand-600 truncate">Custom Print</h4>
                            <p class="text-xs text-surface-500">{{ $item->customization_data['size_label'] ?? '' }} ({{ $item->customization_data['size_dimensions'] ?? '' }}) · Qty: {{ $item->quantity }}</p>
                            @else
                            <h4 class="text-sm font-semibold text-surface-800 truncate">{{ $item->product->name ?? 'Custom Item' }}</h4>
                            <p class="text-xs text-surface-500">Qty: {{ $item->quantity }}</p>
                            @endif

                            <!-- Options Summary -->
                            @if($item->selected_options && count($item->selected_options) > 0 && !(isset($item->customization_data['type']) && $item->customization_data['type'] === 'custom_print'))
                            @php
                                $selectedOptionValues = \App\Models\ProductOptionValue::with('optionGroup')->whereIn('id', $item->selected_options)->get();
                            @endphp
                            <div class="mt-1 space-y-0.5">
                                @foreach($selectedOptionValues as $optVal)
                                    <p class="text-xs text-surface-600">
                                        <span class="font-medium">{{ $optVal->optionGroup->name }}:</span> 
                                        {{ $optVal->label }}
                                    </p>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <span class="font-semibold text-surface-800">{{ \App\Services\CurrencyService::formatOnly($item->total, 0) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div>
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 sticky top-28">
                <h3 class="font-display font-semibold text-lg text-surface-900 mb-5">Payment Summary</h3>
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-surface-500">Subtotal</span>
                        <span class="font-medium">{{ \App\Services\CurrencyService::formatOnly($cart->subtotal, 0) }}</span>
                    </div>
                    @if($cart->discount > 0)
                    <div class="flex justify-between text-sm text-accent-600">
                        <span>Discount</span>
                        <span>-{{ \App\Services\CurrencyService::formatOnly($cart->discount, 0) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-surface-500">Shipping</span>
                        <span class="text-accent-600 font-medium">FREE</span>
                    </div>
                </div>
                <div class="border-t border-surface-100 pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="font-display font-bold text-surface-900">Total</span>
                        <span class="text-2xl font-bold text-brand-600">{{ \App\Services\CurrencyService::formatOnly($cart->total, 0) }}</span>
                    </div>
                </div>
                <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-brand-600 to-brand-700 text-white font-bold rounded-2xl hover:from-brand-700 hover:to-brand-800 shadow-xl shadow-brand-200 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-5 h-5"></i> Place Order & Pay
                </button>
                <p class="text-xs text-center text-surface-400 mt-3">🔒 Your payment info is secure & encrypted</p>
            </div>
        </div>
    </form>
</div>
@endsection
