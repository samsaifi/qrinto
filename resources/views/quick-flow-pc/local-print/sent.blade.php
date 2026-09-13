@extends('layouts.quick-flow-pc')
@section('title', 'Sent to the printer | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-8 px-3 md:py-20 md:px-6 font-sans">
        <div class="max-w-[520px] mx-auto text-center">
            <div class="w-12 h-12 md:w-16 md:h-16 rounded-full bg-[#eaf3ea] text-[#287d3c] flex items-center justify-center mx-auto">
                <i data-lucide="printer" class="w-6 h-6 md:w-8 md:h-8"></i>
            </div>
            <h1 class="text-xl md:text-2xl font-extrabold text-[#112419] tracking-tight mt-4 md:mt-6">Sent to the printer</h1>
            @if ($summary)
                <p class="text-sm text-slate-500 mt-2">
                    {{ $summary['copies'] }} {{ $summary['copies'] == 1 ? 'copy' : 'copies' }} of
                    <span class="font-semibold text-slate-700">{{ $summary['name'] }}</span>
                    to <span class="font-semibold text-slate-700">{{ $summary['tray'] }}</span>.
                </p>
            @endif
            <p class="text-xs text-slate-400 mt-1">Collect your prints from the 931BL next to you.</p>

            <a href="{{ route('localprint.start') }}"
                class="inline-block mt-6 md:mt-8 bg-[#287d3c] hover:bg-emerald-800 text-white font-bold px-5 py-2.5 md:px-6 md:py-3 rounded-lg md:rounded-xl text-xs md:text-sm transition">
                Print something else
            </a>
        </div>
    </div>
@endsection
