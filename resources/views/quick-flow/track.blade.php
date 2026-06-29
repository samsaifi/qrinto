@extends('layouts.quick-flow')

@section('title', 'Track Order ' . $order->order_number)
@section('header_title', 'Track Order')

@push('styles')
<style>
    .success-badge {
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: #4f46e5;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-24 text-center pb-36 min-h-screen pt-4">

    <!-- Header info: Order ORD-2026...  -->
    <div class="flex justify-between items-start text-left mb-6">
        <div>
            <h1 class="text-xl font-black text-slate-900 mb-1">Order {{ $order->order_number }}</h1>
            <p class="text-slate-500 font-medium text-xs">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 font-bold text-blue-600 text-xs bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-100">
                {{ ucfirst($order->status) }}
            </span>
        </div>
    </div>

    <!-- The Tracker Component -->
    @include('quick-flow.partials.tracker', ['order' => $order])

    <!-- Order Details / Summary -->
    <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-5 shadow-premium text-left mt-6">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Order Details</h3>
        
        <div class="flex justify-between items-center py-2 border-b border-slate-50">
            <span class="text-slate-500 font-medium text-sm">Product</span>
            <span class="font-bold text-slate-900 text-sm">{{ $order->items->first()?->product_name ?? 'Custom Print' }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-slate-50">
            <span class="text-slate-500 font-medium text-sm">Quantity</span>
            <span class="font-bold text-slate-900 text-sm">{{ $order->items->first()?->quantity ?? 1 }}</span>
        </div>
        @if($order->store)
        <div class="flex justify-between items-center py-2 border-b border-slate-50">
            <span class="text-slate-500 font-medium text-sm">Store</span>
            <span class="font-bold text-slate-900 text-sm">{{ $order->store->store_name }}</span>
        </div>
        @endif
        <div class="flex justify-between items-center py-2 pt-3">
            <span class="text-slate-500 font-bold text-sm">Total</span>
            <span class="font-black text-brand-600 text-[17px]">{{ \App\Services\CurrencyService::formatWithCurrency($order->total, $order->currency) }}</span>
        </div>
    </div>

    <!-- Sticky Bottom Create New -->
    <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto p-5 glass border-t border-slate-100 safe-bottom z-50 flex gap-3">
        <a href="{{ route('flow.index') }}" 
           class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-extrabold py-4 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 text-base">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Create New Print
        </a>
    </div>

</div>
@endsection
