@extends('layouts.quick-flow-pc')

@section('title', 'Track Your Print Order | Qrinto Order Lookup')
@section('header_title', 'Track Order')
@section('meta_robots', 'noindex, nofollow')

@php
    $routePrefix = $routePrefix ?? 'flow.';
@endphp

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[1100px] mx-auto">

            {{-- Back + Title (single row on mobile) --}}
            <div class="flex items-center gap-2 mb-3 md:flex-col md:items-start md:gap-0 md:mb-8">
                <a href="{{ route($routePrefix . 'index') }}"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ←
                    <span class="hidden md:inline">Back to Shop</span>
                </a>
                <div class="md:text-center md:max-w-lg md:mx-auto md:mt-4">
                    <h1 class="text-base md:text-2xl font-extrabold text-[#112419] tracking-tight">
                        Track Order
                    </h1>
                    <p class="hidden md:block text-xs text-slate-500 font-normal mt-1.5">
                        Enter your order tracking number and the email or phone on the order to view real-time status.
                    </p>
                </div>
            </div>

            {{-- Lookup Form Card --}}
            <div class="max-w-md mx-auto">
                @if (session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3.5 mb-4 rounded-xl text-xs font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-2xs space-y-5">
                    <form action="{{ route($routePrefix . 'track') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label for="order_number" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Order Tracking Code
                            </label>
                            <input type="text" id="order_number" name="order_number" required
                                placeholder="e.g. ORD-20260806-5A3B4"
                                value="{{ old('order_number') }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-900 outline-none focus:border-emerald-600 focus:bg-white transition-all uppercase placeholder:normal-case placeholder:font-normal">
                            @error('order_number')
                                <p class="text-rose-600 text-xs font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact" class="block text-xs font-semibold text-slate-600 mb-1.5">
                                Email or Phone Number
                            </label>
                            <input type="text" id="contact" name="contact" required
                                placeholder="Email address or phone number on order"
                                value="{{ old('contact') }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-900 outline-none focus:border-emerald-600 focus:bg-white transition-all placeholder:normal-case placeholder:font-normal">
                            @error('contact')
                                <p class="text-rose-600 text-xs font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-[#287d3c] hover:bg-emerald-800 text-white font-bold py-3 rounded-xl text-xs transition-all shadow-2xs active:scale-95 cursor-pointer">
                            Track Order Now →
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
