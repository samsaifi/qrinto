@extends('layouts.quick-flow-pc')
@section('title', 'Set up the print helper | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[720px] mx-auto">

            <a href="{{ route('localprint.start') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>

            <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight mt-4">Set up the print helper</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-lg leading-relaxed">
                The helper is a small Windows app that lets this page talk to your 931BL. It comes bundled with the driver
                installer - if the printer is not showing, install it once and reload this page.
            </p>

            <div class="mt-8 space-y-3">
                @php
                    $steps = [
                        [
                            'Install the driver + helper',
                            'The v2.2 driver installer includes the helper. One install covers both.',
                        ],
                        [
                            'Or grab the helper on its own',
                            'Already have the driver? Download just the helper for an existing install.',
                        ],
                        [
                            'Reload the print page',
                            'Come back to “Print on your own 931BL” - the printer and trays should appear.',
                        ],
                    ];
                @endphp
                @foreach ($steps as $i => [$t, $d])
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 flex items-start gap-4">
                        <span
                            class="mono w-7 h-7 rounded-lg bg-[#eaf3ea] text-[#287d3c] font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{ $t }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $d }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="https://www.dropbox.com/scl/fi/d5f74l6ekg7r3hoxhvhwm/Noritsu_931BL_Driver_Setup-v2.2.exe?rlkey=m94hgu9eww95zj7mr7wtpli30&st=e658g2hc&e=1&dl=1"
                    download
                    class="inline-flex items-center gap-2 bg-[#287d3c] text-white font-bold px-5 py-3 rounded-xl text-sm  ">
                    <i data-lucide="download" class="w-4 h-4"></i> Driver + helper installer (v2.2)
                </a>
                <span
                    class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 font-bold px-5 py-3 rounded-xl text-sm opacity-60 cursor-not-allowed">
                    <i data-lucide="download" class="w-4 h-4"></i> Helper only
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Download links activate once the helper build is published.</p>

        </div>
    </div>
@endsection
