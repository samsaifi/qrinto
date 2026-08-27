@extends('layouts.store')
@section('title', 'Trays')

@section('content')
    {{-- QZ Tray onboarding modal: shown to a user only until they click
         "Already Downloaded". The click persists a row in
         user_qz_tray_confirmations for auth()->id(); subsequent visits skip
         this modal. --}}
    @if ($needsQzOnboarding ?? false)
        <div x-data="qzOnboarding()" x-init="visible = true" x-cloak
            x-show="visible"
            class="fixed inset-0 z-[95] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-900/60"></div>

            <div class="relative bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#eaf3ea] border border-[#bfdcc4] flex items-center justify-center shrink-0">
                        <i data-lucide="printer" class="w-5 h-5 text-[#287d3c]"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-display font-bold text-lg text-slate-900">Do you have QZ Tray installed?</h3>
                        <p class="text-[13px] text-slate-600 mt-1 leading-relaxed">
                            QZ Tray is the local printer bridge that lets this PC
                            print to the Noritsu 931-BL. If it's already installed,
                            confirm below — we'll remember it for your next visit.
                        </p>

                        <div class="mt-5 flex items-center gap-2 flex-wrap">
                            <a href="https://qz.io/download/" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                Download QZ Tray
                            </a>
                            <button type="button" @click="confirm()" :disabled="saving"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition disabled:opacity-60 disabled:cursor-not-allowed">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span x-show="!saving">Already Downloaded</span>
                                <span x-show="saving">Saving…</span>
                            </button>
                        </div>

                        <p x-show="error" x-text="error" class="text-[12px] text-red-600 font-medium mt-3"></p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <a href="{{ route('storepanel.orders') }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition mb-6">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
    </a>

    <h1 class="font-display font-bold text-4xl text-slate-900 tracking-tight">What is loaded in each printer</h1>
    <p class="text-slate-500 mt-3 max-w-xl leading-relaxed">
        Trays are read from this PC (via QZ Tray). Tell each printer what paper
        is loaded — the User Type is worked out from that, so you never pick
        one by hand. Enabled printers appear on the "Print on 931-BL" picker.
    </p>

    @if (session('success'))
        <div class="mt-5 max-w-2xl px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="mt-5 max-w-2xl px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium">{{ session('warning') }}</div>
    @endif

    <form method="POST" action="{{ route('storepanel.trays.save') }}" class="mt-8 max-w-3xl"
        x-data="trayForm({{ Illuminate\Support\Js::from($rows) }})" x-init="scan()">
        @csrf

        {{-- Scan status --}}
        <div class="mb-4 flex items-center gap-3">
            <span class="text-[11px] font-black uppercase tracking-widest text-slate-400">Detected on this PC</span>
            <span x-show="scanState === 'loading'" class="text-[12px] text-slate-400 flex items-center gap-2">
                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Scanning printers…
            </span>
            <span x-show="scanState === 'ok'" class="mono text-[11px] text-slate-500" x-text="rows.length + ' printer' + (rows.length === 1 ? '' : 's')"></span>
            <button type="button" @click="scan()" x-show="scanState !== 'loading'"
                class="ml-auto inline-flex items-center gap-1.5 text-[12px] font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Rescan
            </button>
        </div>

        {{-- QZ Tray missing hint --}}
        <div x-show="scanState === 'error'"
            class="mb-4 flex items-center gap-3 px-4 py-3 rounded-2xl bg-amber-50 border border-amber-200">
            <i data-lucide="printer-off" class="w-4 h-4 text-amber-600 shrink-0"></i>
            <p class="text-[13px] text-amber-800 leading-snug flex-1">
                QZ Tray isn't running on this PC. Install it and reload to detect printers.
            </p>
            <a href="https://qz.io/download/" target="_blank" rel="noopener"
                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#287d3c] hover:bg-emerald-800 text-white text-[12px] font-bold transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Download QZ Tray
            </a>
        </div>

        {{-- Empty state --}}
        <div x-show="scanState === 'ok' && rows.length === 0"
            class="mb-4 px-4 py-6 rounded-2xl bg-slate-50 border border-slate-200 text-center text-[13px] text-slate-500">
            No printers found on this PC.
        </div>

        {{-- Printer rows --}}
        <div class="space-y-3">
            <template x-for="(row, i) in rows" :key="row.key">
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4"
                    :class="row.enabled ? '' : 'opacity-60'">

                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <p class="font-bold text-[14px] text-slate-900 truncate" x-text="row.label" :title="row.label"></p>
                            <p class="mono text-[11px] text-slate-400 mt-0.5">
                                <span x-text="row.printer"></span>
                                <span x-show="row.defaultPrinter" class="ml-1 text-blue-600 font-bold">· default</span>
                            </p>
                        </div>

                        {{-- Enable toggle --}}
                        <button type="button" @click="row.enabled = !row.enabled" role="switch" :aria-checked="row.enabled"
                            class="relative w-11 h-6 rounded-full transition-colors shrink-0"
                            :class="row.enabled ? 'bg-[#287d3c]' : 'bg-slate-300'">
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                :class="row.enabled ? 'translate-x-5' : ''"></span>
                        </button>
                    </div>

                    {{-- Configuration row: only editable when enabled --}}
                    <div class="mt-3 flex items-center gap-2 flex-wrap" :class="row.enabled ? '' : 'pointer-events-none'">
                        <select x-model="row.size" :name="`trays[${i}][size]`" :disabled="!row.enabled"
                            class="rounded-xl border border-slate-200 text-sm py-2 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                            <option value="">Size…</option>
                            @foreach ($sizeOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <select x-model="row.media" :name="`trays[${i}][media]`" :disabled="!row.enabled"
                            class="rounded-xl border border-slate-200 text-sm py-2 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none min-w-[170px]">
                            <option value="">Paper…</option>
                            @foreach ($mediaOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <select x-model="row.gsm" :name="`trays[${i}][gsm]`" :disabled="!row.enabled"
                            class="rounded-xl border border-slate-200 text-sm py-2 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                            <option value="">gsm…</option>
                            @foreach ($gsmOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <div class="ml-auto text-right">
                            <p class="mono text-[13px] font-bold text-slate-900">
                                User Type <span x-text="userType(row) ?? '—'"></span>
                            </p>
                            <p class="text-[11px] text-slate-400">set automatically</p>
                        </div>
                    </div>

                    {{-- Hidden fields the server reads on save --}}
                    <input type="hidden" :name="`trays[${i}][printer]`" :value="row.printer">
                    <input type="hidden" :name="`trays[${i}][enabled]`" :value="row.enabled ? 1 : 0">
                </div>
            </template>
        </div>

        <div class="flex items-center gap-3 mt-8">
            <button type="submit" :disabled="rows.length === 0"
                class="px-5 py-2.5 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition disabled:bg-slate-300 disabled:cursor-not-allowed">
                Save tray setup
            </button>
            <a href="{{ route('storepanel.orders') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // QZ Tray onboarding: two-button modal shown on first visit; the
        // "Already Downloaded" click POSTs to the server so this user never
        // sees the modal again on subsequent /store/trays loads.
        function qzOnboarding() {
            return {
                visible: false,
                saving: false,
                error: '',
                async confirm() {
                    if (this.saving) return;
                    this.saving = true;
                    this.error = '';
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]')?.value;
                    try {
                        const res = await fetch("{{ route('storepanel.qz.confirm') }}", {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        this.visible = false;
                    } catch (e) {
                        this.error = 'Could not save. Please try again.';
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }

        // Turn a printer name into the same stable slug the server uses
        // (mirror of Store::trayKeyFromPrinter) so re-scans stay in sync
        // with saved config.
        function slugKey(name) {
            return String(name || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '') || 'printer';
        }

        function trayForm(savedRows) {
            // Saved rows come from the server; key them by printer slug so a
            // fresh scan can merge preserved size / media / gsm / enabled.
            const savedByKey = {};
            (savedRows || []).forEach(r => { if (r && r.key) savedByKey[r.key] = r; });

            return {
                rows: [],
                scanState: 'idle', // idle | loading | ok | error
                defaultPrinter: null,

                async scan() {
                    this.scanState = 'loading';
                    try {
                        if (!window.qz) throw new Error('QZ Tray not loaded');
                        if (!qz.websocket.isActive()) await qz.websocket.connect();
                        this.defaultPrinter = await qz.printers.getDefault().catch(() => null);
                        const names = await qz.printers.find();
                        const seen = new Set();
                        this.rows = (names || []).map(name => {
                            const key = slugKey(name);
                            if (seen.has(key)) return null;
                            seen.add(key);
                            const prior = savedByKey[key] || {};
                            return {
                                key,
                                label: name,
                                printer: name,
                                defaultPrinter: name === this.defaultPrinter,
                                size:    prior.size    || '',
                                media:   prior.media   || '',
                                gsm:     prior.gsm     || '',
                                enabled: prior.enabled ?? false,
                            };
                        }).filter(Boolean);
                        this.scanState = 'ok';
                    } catch (e) {
                        this.rows = [];
                        this.scanState = 'error';
                    }
                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                },

                // Mirror of App\Models\Store::deriveUserType so the badge updates live.
                userType(row) {
                    const { size, media, gsm } = row;
                    if (!media) return null;
                    if (media === 'film') return 7;
                    if (['envelopes', 'labels'].includes(media)) return 3;
                    if (media === 'magnets') return 6;
                    let ut = { '120': 1, '120-150': 2, '150-270': 5, '270-324': 6 }[gsm] ?? 1;
                    const scored = media === 'cardstock_scored';
                    const glossy = ['photo_glossy', 'photo_lustre'].includes(media);
                    if (scored) ut = (gsm === '270-324' && size === '5x7') ? 5 : 6;
                    if (glossy) ut = ({ 1: 2, 2: 5, 5: 6 })[ut] ?? ut;
                    return ut;
                },
            };
        }
    </script>
@endpush
