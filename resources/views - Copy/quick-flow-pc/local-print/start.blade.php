@extends('layouts.quick-flow-pc')
@section('title', 'Print on your own 931BL | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[960px] mx-auto">

            <h1 class="text-lg md:text-2xl font-extrabold text-[#112419] tracking-tight">Print on your own 931BL</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1 md:mt-2">No store, no pickup. Prints come out on the printer
                next to you.</p>

            {{-- Two entry cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-5 mt-4 md:mt-6">
                <a href="{{ route('localprint.pdf') }}"
                    class="group bg-white border border-slate-200/90 rounded-xl md:rounded-2xl p-4 md:p-6 hover:shadow-md hover:border-slate-300 transition-all">
                    <i data-lucide="file-text" class="w-5 h-5 md:w-6 md:h-6 text-[#287d3c]"></i>
                    <h2
                        class="font-extrabold text-slate-900 text-base md:text-lg mt-2.5 md:mt-4 group-hover:text-emerald-700 transition-colors">
                        Upload a Media</h2>
                    <p class="text-[11px] md:text-xs text-slate-500 leading-relaxed mt-1">Send a finished file straight to
                        the printer. It is checked against the loaded media first.</p>
                </a>

                <a href="{{ route('localprint.design') }}"
                    class="group bg-white border border-slate-200/90 rounded-xl md:rounded-2xl p-4 md:p-6 hover:shadow-md hover:border-slate-300 transition-all">
                    <i data-lucide="pencil" class="w-5 h-5 md:w-6 md:h-6 text-[#287d3c]"></i>
                    <h2
                        class="font-extrabold text-slate-900 text-base md:text-lg mt-2.5 md:mt-4 group-hover:text-emerald-700 transition-colors">
                        Design something</h2>
                    <p class="text-[11px] md:text-xs text-slate-500 leading-relaxed mt-1">Start from a template or a blank
                        size, then print when you are happy with it.</p>
                </a>
            </div>

            {{-- Recovery link --}}
            <a href="{{ route('localprint.setup') }}"
                class="inline-block mt-6 md:mt-10 text-xs md:text-sm font-bold text-[#287d3c] hover:text-emerald-800 transition-colors">
                Printer not showing? Set up the helper
            </a>

        </div>
    </div>
@endsection
