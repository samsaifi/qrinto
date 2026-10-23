@extends('layouts.quick-flow')

@section('title', 'Track Your Order')
@section('header_title', 'Track Order')

@section('content')
    <div class="max-w-md mx-auto min-h-screen py-10 px-6 flex flex-col items-center text-center">

        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center shadow-lg shadow-blue-100 mb-6">
            <i data-lucide="map-pin" class="w-8 h-8 text-blue-600"></i>
        </div>

        <h1 class="text-2xl font-black text-slate-900 mb-2">Track Your Order</h1>
        <p class="text-slate-500 text-sm mb-8 px-4">Enter your order number below to check the current status of your print.
        </p>

        @if (session('error'))
            <div
                class="w-full bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg text-left text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('flow.track') }}" method="POST" class="w-full space-y-4">
            @csrf

            <div class="relative text-left">
                <label for="order_number"
                    class="block text-xs font-bold text-slate-500 uppercase tracking-widest pl-4 mb-1">Order Number</label>
                <i data-lucide="hash" class="absolute left-4 top-[32px] w-5 h-5 text-slate-400"></i>
                <input type="text" id="order_number" name="order_number" required placeholder="e.g. ORD-20260323... "
                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl py-4 pl-12 pr-4 text-slate-900 font-bold focus:border-mobile-500 focus:ring-0 transition-all outline-none"
                    value="{{ old('order_number') }}">
                @error('order_number')
                    <p class="text-red-500 text-xs mt-1 pl-4">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-mobile-500 hover:bg-mobile-600 text-white font-extrabold py-4 rounded-2xl shadow-xl transition-all active:scale-[0.97] flex items-center justify-center gap-2 mt-4">
                <i data-lucide="search" class="w-5 h-5"></i>
                Track Now
            </button>
        </form>

    </div>
@endsection
