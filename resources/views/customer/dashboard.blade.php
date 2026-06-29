@extends('layouts.app')
@section('title', 'My Account')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        @include('customer.partials.sidebar')

        <!-- Main Content -->
        <div class="flex-1">
            <h1 class="font-display font-bold text-2xl text-surface-900 mb-8">Welcome back, {{ auth()->user()->name }}!</h1>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-brand-100 flex items-center justify-center">
                            <i data-lucide="package" class="w-6 h-6 text-brand-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-surface-500">Total Orders</p>
                            <p class="text-2xl font-bold text-surface-900">{{ $totalOrders }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-accent-100 flex items-center justify-center">
                            <i data-lucide="dollar-sign" class="w-6 h-6 text-accent-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-surface-500">Total Spent</p>
                            <p class="text-2xl font-bold text-surface-900">${{ number_format($totalSpent, 0) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i data-lucide="truck" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-surface-500">In Progress</p>
                            <p class="text-2xl font-bold text-surface-900">{{ $pendingOrders }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card">
                <div class="p-6 border-b border-surface-100 flex justify-between items-center">
                    <h2 class="font-display font-semibold text-lg text-surface-900">Recent Orders</h2>
                    <a href="{{ route('customer.orders') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">View All →</a>
                </div>
                @if($recentOrders->count() > 0)
                <div class="divide-y divide-surface-100">
                    @foreach($recentOrders as $order)
                    <a href="{{ route('customer.orders.show', $order) }}" class="flex items-center justify-between p-5 hover:bg-surface-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-surface-100 flex items-center justify-center">
                                <i data-lucide="package" class="w-5 h-5 text-surface-500"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-surface-800 text-sm">{{ $order->order_number }}</p>
                                <p class="text-xs text-surface-500">{{ $order->created_at->format('M d, Y') }} • {{ $order->items->count() }} items</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-surface-800">${{ number_format($order->total, 0) }}</p>
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-lg" style="background: {{ $order->status_color }}20; color: {{ $order->status_color }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="p-12 text-center">
                    <p class="text-surface-500">No orders yet. Start shopping!</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
