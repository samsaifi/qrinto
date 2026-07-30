@extends('layouts.admin')
@section('title', isset($coupon) ? 'Edit Coupon' : 'Create Coupon')

@section('content')
<div class="max-w-2xl">
    <h1 class="font-display font-bold text-2xl text-surface-900 mb-8">{{ isset($coupon) ? 'Edit Coupon' : 'Create Coupon' }}</h1>

    <form action="{{ isset($coupon) ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}"
          method="POST" class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
        @csrf
        @if(isset($coupon)) @method('PUT') @endif

        <div class="space-y-4">
            @if(Auth::user()->isAdmin())
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Store</label>
                    <select name="store_id" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="">— No Store (Global) —</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id', $coupon->store_id ?? '') == $store->id ? 'selected' : '' }}>{{ $store->store_name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="store_id" value="{{ Auth::user()->store_id }}">
            @endif

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Coupon Code *</label>
                    <input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" required
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500 uppercase font-mono">
                    @error('code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Discount Type *</label>
                    <select name="type" class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                        <option value="percentage" {{ old('type', $coupon->type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('type', $coupon->type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Discount Value *</label>
                    <input type="number" name="value" step="0.01" value="{{ old('value', $coupon->value ?? '') }}" required
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Minimum Order ($)</label>
                    <input type="number" name="min_order_amount" step="0.01" value="{{ old('min_order_amount', $coupon->min_order_amount ?? '') }}"
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Max Uses</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500" placeholder="Unlimited">
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1">Valid Until</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '') }}"
                           class="w-full rounded-xl border-surface-200 focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-surface-700">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}
                       class="rounded text-brand-600 focus:ring-brand-500"> Active
            </label>

            @if(isset($coupon) && $coupon->user)
            <div>
                <label class="block text-sm font-medium text-surface-700 mb-1">Created By</label>
                <p class="text-sm text-surface-600">{{ $coupon->user->name }}</p>
            </div>
            @endif
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                {{ isset($coupon) ? 'Update' : 'Create' }} Coupon
            </button>
            <a href="{{ route('admin.coupons.index') }}" class="px-6 py-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
