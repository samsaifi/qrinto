@extends('layouts.app')
@section('title', 'My Orders')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        @include('customer.partials.sidebar')

        <div class="flex-1">
            <h1 class="font-display font-bold text-2xl text-surface-900 mb-8">My Orders</h1>

            @if($orders->count() > 0)
            <div class="space-y-4">
                @foreach($orders as $order)
                <a href="{{ route('customer.orders.show', $order) }}" class="block bg-white rounded-2xl border border-surface-100 shadow-card hover:shadow-hover transition-all p-5">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-surface-800">{{ $order->order_number }}</span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-lg" style="background: {{ $order->status_color }}15; color: {{ $order->status_color }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="text-sm text-surface-500">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @foreach($order->items->take(3) as $item)
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-surface-100">
                                @if(!empty($item->customization_data['preview_url']))
                                <img src="{{ $item->customization_data['preview_url'] }}" alt="Custom Design" class="w-full h-full object-cover">
                                @elseif($item->product && $item->product->featured_image_url)
                                <img src="{{ $item->product->featured_image_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center"><i data-lucide="image" class="w-5 h-5 text-surface-300"></i></div>
                                @endif
                            </div>
                            @endforeach
                            @if($order->items->count() > 3)
                            <span class="text-xs text-surface-400 font-medium">+{{ $order->items->count() - 3 }} more</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-brand-600">${{ number_format($order->total, 0) }}</p>
                            <p class="text-xs text-surface-400">{{ $order->items->count() }} items</p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $orders->links() }}</div>
            @else
            <div class="text-center py-16 bg-white rounded-2xl border border-surface-100 shadow-card">
                <i data-lucide="package" class="w-16 h-16 text-surface-300 mx-auto mb-4"></i>
                <h3 class="font-display font-semibold text-xl text-surface-700 mb-2">No orders yet</h3>
                <p class="text-surface-500 mb-6">Start shopping and your orders will appear here.</p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Browse Products
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
