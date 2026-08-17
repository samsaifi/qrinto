@extends('layouts.kiosks')
@section('title', isset($kiosk) ? 'Edit Kiosk Record' : 'Add Kiosk Record')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.kiosks.index') }}"
            class="inline-flex items-center gap-2 text-sm text-surface-500 hover:text-brand-600 mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Kiosk List
        </a>
        <h1 class="font-display font-bold text-2xl text-surface-900">
            {{ isset($kiosk) ? 'Edit Kiosk Record #' . $kiosk->id : 'Add New Kiosk Record' }}
        </h1>
        <p class="text-sm text-surface-500">Configure kiosk item specs, store linkage, and print parameters</p>
    </div>

    <form action="{{ isset($kiosk) ? route('admin.kiosks.update', $kiosk) : route('admin.kiosks.store') }}" method="POST"
        enctype="multipart/form-data" class="max-w-4xl space-y-6">
        @csrf
        @if (isset($kiosk))
            @method('PUT')
        @endif

        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-6">
            <h2 class="font-display font-semibold text-lg text-surface-900 border-b border-surface-100 pb-3">Association &
                Core Flags</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Store -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Store <span
                            class="text-red-500">*</span></label>
                    <select name="store_id" required
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Select Store</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}"
                                {{ old('store_id', $kiosk->store_id ?? '') == $store->id ? 'selected' : '' }}>
                                {{ $store->store_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Product (Optional)</label>
                    <select name="product_id"
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Select Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}"
                                {{ old('product_id', $kiosk->product_id ?? '') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Is Kiosk Toggle -->
            <div>
                <label class="block text-sm font-semibold text-surface-700 mb-2">Kiosk Status</label>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="kiosk" value="1"
                            {{ old('kiosk', $kiosk->kiosk ?? 1) == 1 ? 'checked' : '' }}
                            class="text-brand-600 focus:ring-brand-500">
                        <span class="ml-2 text-sm font-medium text-surface-800">Yes (Is Kiosk Item/Order)</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="kiosk" value="0"
                            {{ old('kiosk', $kiosk->kiosk ?? 1) == 0 ? 'checked' : '' }}
                            class="text-brand-600 focus:ring-brand-500">
                        <span class="ml-2 text-sm font-medium text-surface-800">No (Standard Item)</span>
                    </label>
                </div>
                @error('kiosk')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Print & File Parameters -->
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-6">
            <h2 class="font-display font-semibold text-lg text-surface-900 border-b border-surface-100 pb-3">Print
                Specifications & File Path</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Printer Tray -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Printer Tray</label>
                    <input type="text" name="printer_tray" value="{{ old('printer_tray', $kiosk->printer_tray ?? '') }}"
                        placeholder="e.g. Tray 1, Manual Feed"
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('printer_tray')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Print Size -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Print Size</label>
                    <input type="text" name="print_size" value="{{ old('print_size', $kiosk->print_size ?? '') }}"
                        placeholder="e.g. A4, A3, 4x6 inch"
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('print_size')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- File Path / File Upload -->
            <div class="space-y-3">
                <label class="block text-sm font-semibold text-surface-700">Uploaded / Printed File Path</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="block text-xs font-medium text-surface-500 mb-1">Direct File Path / URL</span>
                        <input type="text" name="file_path" value="{{ old('file_path', $kiosk->file_path ?? '') }}"
                            placeholder="e.g. kiosks/sample.pdf or https://..."
                            class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-surface-500 mb-1">Or Upload New File</span>
                        <input type="file" name="file"
                            class="w-full text-xs text-surface-500 border border-surface-200 rounded-xl p-2 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                </div>
                @if (isset($kiosk->file_path) && $kiosk->file_path)
                    <p class="text-xs text-surface-500">Current path: <code
                            class="bg-surface-100 px-2 py-0.5 rounded text-surface-800">{{ $kiosk->file_path }}</code></p>
                @endif
            </div>
        </div>

        <!-- Pricing & Financials -->
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-6">
            <h2 class="font-display font-semibold text-lg text-surface-900 border-b border-surface-100 pb-3">Pricing &
                Financials</h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Quantity <span
                            class="text-red-500">*</span></label>
                    <input type="number" id="quantity" name="quantity"
                        value="{{ old('quantity', $kiosk->quantity ?? 1) }}" min="1" required
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('quantity')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Unit Price <span
                            class="text-red-500">*</span></label>
                    <input type="number" id="price" step="0.01" name="price"
                        value="{{ old('price', $kiosk->price ?? '0.00') }}" min="0" required
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('price')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Amount -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Total Amount</label>
                    <input type="number" id="total_amount" step="0.01" name="total_amount"
                        value="{{ old('total_amount', $kiosk->total_amount ?? '0.00') }}" min="0"
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-[11px] text-surface-400">Calculated automatically if left blank</span>
                    @error('total_amount')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Currency <span
                            class="text-red-500">*</span></label>
                    <select name="currency" required
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="INR"
                            {{ old('currency', $kiosk->currency ?? 'INR') === 'INR' ? 'selected' : '' }}>INR (₹)</option>
                        <option value="USD" {{ old('currency', $kiosk->currency ?? '') === 'USD' ? 'selected' : '' }}>
                            USD ($)</option>
                        <option value="EUR" {{ old('currency', $kiosk->currency ?? '') === 'EUR' ? 'selected' : '' }}>
                            EUR (€)</option>
                        <option value="GBP" {{ old('currency', $kiosk->currency ?? '') === 'GBP' ? 'selected' : '' }}>
                            GBP (£)</option>
                    </select>
                    @error('currency')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.kiosks.index') }}"
                class="px-5 py-2.5 bg-surface-100 text-surface-700 text-sm font-semibold rounded-xl hover:bg-surface-200 transition">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                {{ isset($kiosk) ? 'Update Record' : 'Create Record' }}
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const qtyInput = document.getElementById('quantity');
                const priceInput = document.getElementById('price');
                const totalInput = document.getElementById('total_amount');

                function recalculate() {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    totalInput.value = (qty * price).toFixed(2);
                }

                qtyInput.addEventListener('input', recalculate);
                priceInput.addEventListener('input', recalculate);
            });
        </script>
    @endpush
@endsection
