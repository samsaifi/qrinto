@extends('layouts.admin')
@section('title', 'Coupons')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Coupons</h1>
        <p class="text-sm text-surface-500">Manage discount codes</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Coupon
    </a>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-surface-50">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Discount</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Usage</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Valid Until</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-100">
                @forelse($coupons as $coupon)
                <tr class="hover:bg-surface-50 transition">
                    <td class="px-6 py-4 font-mono font-bold text-surface-800">{{ $coupon->code }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($coupon->type === 'percentage')
                        <span class="text-brand-600 font-semibold">{{ $coupon->value }}%</span>
                        @else
                        <span class="text-brand-600 font-semibold">{{ \App\Services\CurrencyService::format($coupon->value, 0) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-surface-600">{{ $coupon->used_count ?? 0 }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                    <td class="px-6 py-4 text-sm text-surface-500">{{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : '—' }}</td>
                    <td class="px-6 py-4 text-sm text-surface-600">{{ $coupon->store->store_name ?? 'All Stores' }}</td>
                    <td class="px-6 py-4 text-sm text-surface-600">{{ $coupon->user->name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $coupon->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                            {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="p-2 rounded-lg hover:bg-surface-100 text-surface-500 hover:text-brand-600 transition">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-surface-500 hover:text-red-600 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-surface-400">No coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
