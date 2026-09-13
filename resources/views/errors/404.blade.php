@extends('layouts.quick-flow-pc')

@section('title', '404 · Page Not Found - Qrinto Print Studio')

@push('styles')
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        .float-anim { animation: float 4.5s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .float-anim { animation: none; } }
    </style>
@endpush

@section('content')
    <div class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-lg text-center">

            {{-- Glow --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden" aria-hidden="true">
                <div class="w-[500px] h-[500px] rounded-full bg-gradient-radial from-sky-500/10 to-transparent blur-3xl"></div>
            </div>

            {{-- Icon --}}
            <div class="relative mx-auto mb-6 w-24 h-24 rounded-3xl bg-gradient-to-br from-sky-50 to-sky-100 border border-sky-200/40 flex items-center justify-center text-sky-600 shadow-lg shadow-sky-500/20 float-anim">
                <svg class="w-11 h-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5.43 5.43A8.06 8.06 0 0 0 4 10c0 6 8 12 8 12a29.94 29.94 0 0 0 5-5"/>
                    <path d="M19.18 13.52A8.66 8.66 0 0 0 20 10a8 8 0 0 0-13.95-5.39"/>
                    <path d="M9.13 9.13A2.74 2.74 0 0 0 9 10a3 3 0 0 0 3 3 2.74 2.74 0 0 0 .87-.13"/>
                    <path d="m2 2 20 20"/>
                </svg>
            </div>

            {{-- 404 Code --}}
            <p class="relative text-[clamp(80px,16vw,120px)] font-black leading-none tracking-tighter bg-gradient-to-br from-sky-500 via-sky-400 to-indigo-400 bg-clip-text text-transparent select-none mb-2">
                404
            </p>

            {{-- Heading --}}
            <h1 class="relative text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
                This page took a wrong turn
            </h1>

            {{-- Description --}}
            <p class="relative text-sm md:text-base font-medium text-slate-500 leading-relaxed max-w-sm mx-auto mb-10">
                We couldn't find what you were looking for. It may have moved, expired, or the store link is no longer active.
            </p>

            {{-- Actions --}}
            <div class="relative flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-2xl text-sm font-extrabold text-white bg-gradient-to-br from-sky-500 to-sky-600 shadow-lg shadow-sky-500/30 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-sky-500/35 active:translate-y-0 active:scale-[0.99] transition-all duration-200">
                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <path d="M9 22V12h6v10"/>
                    </svg>
                    Back to Home
                </a>

                <a href="{{ url('/find-store') }}"
                   class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-2xl text-sm font-extrabold text-sky-600 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 active:scale-[0.99] transition-all duration-200">
                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                    Find a Store
                </a>
            </div>

            {{-- Footer Links --}}
            <div class="relative mt-14 flex items-center justify-center gap-5 text-xs">
                <a href="{{ url('/track') }}" class="font-bold text-slate-400 hover:text-sky-600 transition-colors">Track an Order</a>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <a href="{{ url('/find-store') }}" class="font-bold text-slate-400 hover:text-sky-600 transition-colors">Store Locations</a>
            </div>

            <p class="relative mt-4 text-[10px] font-extrabold uppercase tracking-[0.25em] text-slate-300">
                Qrinto Print Studio
            </p>
        </div>
    </div>
@endsection
