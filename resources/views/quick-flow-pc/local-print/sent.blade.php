@extends('layouts.quick-flow-pc')
@section('title', 'Sent to the printer | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-20 px-6 font-sans">
        <div class="max-w-[520px] mx-auto text-center">
            <div class="w-16 h-16 rounded-full bg-[#eaf3ea] text-[#287d3c] flex items-center justify-center mx-auto">
                <i data-lucide="printer" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight mt-6">Sent to the printer</h1>
            @if ($summary)
                <p class="text-sm text-slate-500 mt-2">
                    {{ $summary['copies'] }} {{ $summary['copies'] == 1 ? 'copy' : 'copies' }} of
                    <span class="font-semibold text-slate-700">{{ $summary['name'] }}</span>
                    to <span class="font-semibold text-slate-700">{{ $summary['tray'] }}</span>.
                </p>
            @endif
            <p class="text-xs text-slate-400 mt-1">Collect your prints from the 931BL next to you.</p>

            <a href="{{ route('localprint.start') }}"
                class="inline-block mt-8 bg-[#287d3c] hover:bg-emerald-800 text-white font-bold px-6 py-3 rounded-xl text-sm transition">
                Print something else
            </a>
        </div>
    </div>
@endsection
