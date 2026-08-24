@extends('layouts.quick-flow-pc')
@section('title', 'What is loaded in each tray | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[860px] mx-auto">

            <a href="{{ route('localprint.check') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>

            <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight mt-4">What is loaded in each tray</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-xl leading-relaxed">
                Say what paper is in the printer. The right printer setting is worked out from this, so you never pick a User Type yourself.
            </p>

            @if (session('success'))
                <div class="mt-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('localprint.trays.save') }}" class="mt-8"
                x-data="trayForm({{ Illuminate\Support\Js::from($rows) }})">
                @csrf
                <div class="space-y-3">
                    <template x-for="row in rows" :key="row.key">
                        <div class="bg-white border border-slate-200/80 rounded-2xl px-5 py-4 flex items-center gap-4 flex-wrap"
                            :class="(row.key === 'mp' || row.enabled) ? '' : 'opacity-55'">
                            <div class="w-24 shrink-0 font-bold text-slate-900" x-text="row.label"></div>

                            <select x-model="row.size" :name="`trays[${row.key}][size]`" :disabled="row.key !== 'mp' && !row.enabled"
                                class="rounded-xl border border-slate-200 text-sm py-2 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                                @foreach ($sizeOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                            </select>

                            <select x-model="row.media" :name="`trays[${row.key}][media]`" :disabled="row.key !== 'mp' && !row.enabled"
                                class="rounded-xl border border-slate-200 text-sm py-2 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none min-w-[150px]">
                                @foreach ($mediaOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                            </select>

                            <select x-model="row.gsm" :name="`trays[${row.key}][gsm]`" :disabled="row.key !== 'mp' && !row.enabled"
                                class="rounded-xl border border-slate-200 text-sm py-2 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                                @foreach ($gsmOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                            </select>

                            <div class="ml-auto text-right">
                                <p class="mono text-[13px] font-bold text-slate-900">User Type <span x-text="userType(row) ?? '—'"></span></p>
                                <p class="text-[11px] text-slate-400">set automatically</p>
                            </div>

                            <div class="w-16 shrink-0 flex justify-end">
                                <template x-if="row.key === 'mp'"><span class="text-[13px] text-slate-400 font-medium">Built in</span></template>
                                <template x-if="row.key !== 'mp'">
                                    <button type="button" @click="row.enabled = !row.enabled" role="switch" :aria-checked="row.enabled"
                                        class="relative w-11 h-6 rounded-full transition-colors" :class="row.enabled ? 'bg-[#287d3c]' : 'bg-slate-300'">
                                        <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform" :class="row.enabled ? 'translate-x-5' : ''"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" :name="`trays[${row.key}][enabled]`" :value="row.enabled ? 1 : 0">
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-3 mt-8">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition">Save tray setup</button>
                    <a href="{{ route('localprint.check') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function trayForm(rows) {
            return {
                rows: rows.map(r => ({ ...r, enabled: r.key === 'mp' ? true : !!r.enabled })),
                userType(row) {
                    const { size, media, gsm } = row;
                    if (!media) return null;
                    if (media === 'film') return 7;
                    if (['envelopes', 'labels'].includes(media)) return 3;
                    if (media === 'magnets') return 6;
                    let ut = { '120': 1, '120-150': 2, '150-270': 5, '270-324': 6 }[gsm] ?? 1;
                    if (media === 'cardstock_scored') ut = (gsm === '270-324' && size === '5x7') ? 5 : 6;
                    if (['photo_glossy', 'photo_lustre'].includes(media)) ut = ({ 1: 2, 2: 5, 5: 6 })[ut] ?? ut;
                    return ut;
                },
            };
        }
    </script>
@endpush
