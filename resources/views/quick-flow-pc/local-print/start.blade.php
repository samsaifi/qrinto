@extends('layouts.quick-flow-pc')
@section('title', 'Print on your own 931BL | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[960px] mx-auto">

            <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">Print on your own 931BL</h1>
            <p class="text-sm text-slate-500 mt-2">No store, no pickup. Prints come out on the printer next to you.</p>

            {{-- Printer status strip --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl px-4 py-3 mt-6 flex items-center gap-3 flex-wrap text-[13px]">
                <span class="flex items-center gap-2 shrink-0">
                    <span class="w-2 h-2 rounded-full bg-[#287d3c]"></span>
                    <span class="font-bold text-slate-900">NORITSU 931-BL</span>
                </span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-600 font-medium">Ready</span>
                <span class="text-slate-300">|</span>
                <span class="text-slate-600">{{ $loadedCount }} of {{ $trayTotal }} trays loaded</span>
            </div>

            {{-- Two entry cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-6">
                <a href="{{ route('localprint.pdf') }}"
                    class="group bg-white border border-slate-200/90 rounded-2xl p-6 hover:shadow-md hover:border-slate-300 transition-all">
                    <i data-lucide="file-text" class="w-6 h-6 text-[#287d3c]"></i>
                    <h2 class="font-extrabold text-slate-900 text-lg mt-4 group-hover:text-emerald-700 transition-colors">Print a PDF</h2>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1.5">Send a finished file straight to the printer. It is checked against the loaded media first.</p>
                </a>

                <a href="{{ route('localprint.design') }}"
                    class="group bg-white border border-slate-200/90 rounded-2xl p-6 hover:shadow-md hover:border-slate-300 transition-all">
                    <i data-lucide="pencil" class="w-6 h-6 text-[#287d3c]"></i>
                    <h2 class="font-extrabold text-slate-900 text-lg mt-4 group-hover:text-emerald-700 transition-colors">Design something</h2>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1.5">Start from a template or a blank size, then print when you are happy with it.</p>
                </a>
            </div>

            {{-- Recovery link --}}
            <a href="{{ route('localprint.setup') }}" class="inline-block mt-10 text-sm font-bold text-[#287d3c] hover:text-emerald-800 transition-colors">
                Printer not showing? Set up the helper
            </a>

        </div>
    </div>
@endsection
