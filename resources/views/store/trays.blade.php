@extends('layouts.store')
@section('title', 'Trays')

@section('content')
    <a href="{{ route('storepanel.orders') }}"
        class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition mb-6">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
    </a>

    <h1 class="font-display font-bold text-2xl md:text-4xl text-slate-900 tracking-tight">What is loaded in each printer</h1>

    @if (session('success'))
        <div
            class="mt-5 max-w-2xl px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
            {{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div
            class="mt-5 max-w-2xl px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium">
            {{ session('warning') }}</div>
    @endif

    <div x-data="trayForm({{ Illuminate\Support\Js::from($rows) }}, {{ Illuminate\Support\Js::from($sizeOptions) }}, {{ Illuminate\Support\Js::from($sizeDims) }})"
        x-init="scan()">
    <form method="POST" action="{{ route('storepanel.trays.save') }}" class="mt-8 max-w-3xl">
        @csrf

        {{-- Scan status --}}
        <div class="mb-4 flex items-center gap-3">
            <span class="text-[11px] font-black uppercase tracking-widest text-slate-400">Noritsu Printers</span>
            <span x-show="scanState === 'loading'" class="text-[12px] text-slate-400 flex items-center gap-2">
                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Scanning printers…
            </span>
            <span x-show="scanState === 'ok'" class="mono text-[11px] text-slate-500"
                x-text="rows.length + ' printer' + (rows.length === 1 ? '' : 's')"></span>
            <button type="button" @click="scan()" x-show="scanState !== 'loading'"
                class="ml-auto inline-flex items-center gap-1.5 text-[12px] font-semibold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Rescan
            </button>
        </div>

        {{-- PrintTrays missing hint --}}
        <div x-show="scanState === 'error'"
            class="mb-4 flex items-center gap-3 px-4 py-3 rounded-2xl bg-amber-50 border border-amber-200">
            <i data-lucide="printer-off" class="w-4 h-4 text-amber-600 shrink-0"></i>
            <p class="text-[13px] text-amber-800 leading-snug flex-1">
                PrintTrays isn't running on this PC. Install it and reload to detect printers.
            </p>
            <a href="https://noritsucanada.com/print-trays/download/" target="_blank" rel="noopener"
                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#287d3c] hover:bg-emerald-800 text-white text-[12px] font-bold transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                Download PrintTrays
            </a>
        </div>

        {{-- Empty state --}}
        <div x-show="scanState === 'ok' && rows.length === 0"
            class="mb-4 px-4 py-6 rounded-2xl bg-slate-50 border border-slate-200 text-center text-[13px] text-slate-500">
            No Noritsu printers found on this PC.
        </div>

        {{-- Printer rows --}}
        <div class="space-y-2">
            <template x-for="(row, i) in rows" :key="row.key">
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 cursor-pointer transition hover:border-slate-300"
                    :class="row.enabled ? '' : 'opacity-60'"
                    @click="openConfig(i)">

                    <div class="flex items-center gap-3">
                        {{-- Enable toggle --}}
                        <button type="button" @click.stop="row.enabled = !row.enabled" role="switch"
                            :aria-checked="row.enabled" class="relative w-11 h-6 rounded-full transition-colors shrink-0"
                            :class="row.enabled ? 'bg-[#287d3c]' : 'bg-slate-300'">
                            <span
                                class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                :class="row.enabled ? 'translate-x-5' : ''"></span>
                        </button>

                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-[14px] text-slate-900 truncate" x-text="row.label" :title="row.label"></p>
                            <p class="mono text-[11px] text-slate-400 mt-0.5">
                                <span x-text="row.printer"></span>
                                <span x-show="row.defaultPrinter" class="ml-1 text-blue-600 font-bold">· default</span>
                            </p>
                        </div>

                        <button type="button" @click.stop="openConfig(i)"
                            class="text-[11px] text-[#287d3c] font-bold hover:underline shrink-0">Change</button>
                    </div>

                    {{-- Saved params summary --}}
                    <template x-if="row.size || row.inputBin || row.mediaTypeLive">
                        <div class="mt-2 ml-14 flex flex-wrap items-center gap-1.5">
                            <template x-if="row.size">
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200" x-text="sizeLabel(row)"></span>
                            </template>
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200" x-text="row.landscape ? 'Landscape' : 'Portrait'"></span>
                            <template x-if="row.duplex !== 'simplex'">
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200" x-text="row.duplex === 'longEdge' ? 'Duplex' : 'Duplex (short)'"></span>
                            </template>
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200" x-text="row.color ? 'Color' : 'B&W'"></span>
                            <template x-if="row.inputBin">
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200" x-text="inputBinLabel(row)"></span>
                            </template>
                            <template x-if="row.mediaTypeLive">
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200" x-text="mediaTypeLiveLabel(row)"></span>
                            </template>
                        </div>
                    </template>

                    {{-- Loading indicator --}}
                    <div x-show="row.detailsLoading" class="mt-2 ml-14">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Reading printer capabilities…
                        </span>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" :name="`trays[${i}][printer]`" :value="row.printer">
                    <input type="hidden" :name="`trays[${i}][enabled]`" :value="row.enabled ? 1 : 0">
                    <input type="hidden" :name="`trays[${i}][size]`" :value="row.size">
                    <input type="hidden" :name="`trays[${i}][media]`" :value="row.media">
                    <input type="hidden" :name="`trays[${i}][gsm]`" :value="row.gsm">
                    <input type="hidden" :name="`trays[${i}][density]`" :value="row.density">
                    <input type="hidden" :name="`trays[${i}][landscape]`" :value="row.landscape ? 1 : 0">
                    <input type="hidden" :name="`trays[${i}][duplex]`" :value="row.duplex">
                    <input type="hidden" :name="`trays[${i}][input_bin]`" :value="row.inputBin">
                    <input type="hidden" :name="`trays[${i}][quality]`" :value="row.quality">
                    <input type="hidden" :name="`trays[${i}][media_type_live]`" :value="row.mediaTypeLive">
                    <input type="hidden" :name="`trays[${i}][margins]`" :value="row.margins">
                    <input type="hidden" :name="`trays[${i}][color]`" :value="row.color ? 1 : 0">
                    <input type="hidden" :name="`trays[${i}][scale]`" :value="row.scale">
                    <input type="hidden" :name="`trays[${i}][size_width]`" :value="selectedPaperWidth(row)">
                    <input type="hidden" :name="`trays[${i}][size_height]`" :value="selectedPaperHeight(row)">
                </div>
            </template>
        </div>

        <div class="flex items-center gap-3 mt-8">
            <button type="submit" :disabled="rows.length === 0"
                class="px-5 py-2.5 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition disabled:bg-slate-300 disabled:cursor-not-allowed">
                Save tray setup
            </button>
            <a href="{{ route('storepanel.orders') }}"
                class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">Cancel</a>
        </div>
    </form>

        @include('partials.print-settings-modal')
    </div>
    </div>

    {{-- PrintTrays onboarding modal --}}
    @if ($needsQzOnboarding ?? false)
        <div x-data="bridgeOnboarding()" x-init="visible = true" x-cloak x-show="visible"
            class="fixed inset-0 z-[95] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-900/60"></div>

            <div class="relative bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                <div class="flex items-start gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-[#eaf3ea] border border-[#bfdcc4] flex items-center justify-center shrink-0">
                        <i data-lucide="printer" class="w-5 h-5 text-[#287d3c]"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-display font-bold text-lg text-slate-900">Do you have PrintTrays installed?</h3>
                        <p class="text-[13px] text-slate-600 mt-1 leading-relaxed">
                            PrintTrays is the local printer bridge that lets this PC
                            print to the Noritsu 931-BL. If it's already installed,
                            confirm below - we'll remember it for your next visit.
                        </p>

                        <div class="mt-5 flex items-center gap-2 flex-wrap">
                            <a href="https://noritsucanada.com/print-trays/download/" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                Download PrintTrays
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
@endsection

@push('scripts')
    <script src="{{ asset('js/print-settings.js') }}?v={{ filemtime(public_path('js/print-settings.js')) }}"></script>
    <script>
        var __ptInstance = null;

        function bridgeOnboarding() {
            return {
                visible: false,
                saving: false,
                error: '',
                async confirm() {
                    if (this.saving) return;
                    this.saving = true;
                    this.error = '';
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ||
                        document.querySelector('input[name="_token"]')?.value;
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

        function slugKey(name) {
            return String(name || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '') || 'printer';
        }

        function trayForm(savedRows, serverSizeOptions, serverSizeDims) {
            const savedByKey = {};
            (savedRows || []).forEach(r => {
                if (r && r.key) savedByKey[r.key] = r;
            });

            const STATIC_SIZES = Object.entries(serverSizeOptions || {}).map(([code, label]) => {
                const dims = (serverSizeDims || {})[code];
                return {
                    name: code,
                    label,
                    width: dims ? dims[0] : null,
                    height: dims ? dims[1] : null
                };
            });

            const KNOWN_DIMS = serverSizeDims || {};

            const MEDIA_LABELS = @json($mediaOptions);
            const GSM_LABELS = @json($gsmOptions);

            function normalizeSizeCode(w, h) {
                if (!w || !h) return null;
                for (const [code, [cw, ch]] of Object.entries(KNOWN_DIMS)) {
                    if ((Math.abs(w - cw) < 0.15 && Math.abs(h - ch) < 0.15) ||
                        (Math.abs(w - ch) < 0.15 && Math.abs(h - cw) < 0.15)) return code;
                }
                return null;
            }

            const TRAY_ORDER = ['MP Tray', 'Tray 1', 'Tray 2', 'Tray 3', 'Tray 4', 'Tray 5'];

            // Static paper dims as { code: { w, h } } for the shared mixin.
            const STATIC_DIMS = {};
            Object.entries(serverSizeDims || {}).forEach(([code, d]) => {
                if (d) STATIC_DIMS[code] = { w: d[0], h: d[1] };
            });

            return {
                // Shared Print Settings modal (state + capability loading).
                ...window.printSettingsMixin({ sizeOptions: serverSizeOptions, sizeDims: STATIC_DIMS }),

                rows: [],
                scanState: 'idle',
                defaultPrinter: null,
                staticSizes: STATIC_SIZES,
                editingIndex: null,

                // Open the shared modal for one printer row: seed the buffer from
                // the row's saved values, then load the printer's live caps.
                openConfig(i) {
                    const row = this.rows[i];
                    if (!row) return;
                    this.editingIndex = i;
                    this.psResetCaps();
                    if (row.size) this.printPaperSize = row.size;
                    this.printLandscape = row.landscape || false;
                    this.printDuplex = row.duplex || 'simplex';
                    this.printColor = row.color !== undefined ? row.color : true;
                    this.printInputBin = row.inputBin || '';
                    this.printQuality = row.quality || '';
                    this.printMediaType = row.mediaTypeLive || '';
                    this.psOpen(row.label, row.driver || '', this.psPrinterStatus(row));
                    this.psLoad(row.printer);
                },

                // Called by the shared modal's Apply button — copy the buffer
                // back into the row and its hidden form fields.
                applyPaperSettings() {
                    const r = this.rows[this.editingIndex];
                    if (r) {
                        r.size = this.printPaperSize;
                        r.landscape = this.printLandscape;
                        r.duplex = this.printDuplex;
                        r.color = this.printColor;
                        r.inputBin = this.printInputBin;
                        r.quality = this.printQuality;
                        r.mediaTypeLive = this.printMediaType;
                        const dim = this.psDim();
                        r.sizeWidth = dim ? dim.w : null;
                        r.sizeHeight = dim ? dim.h : null;
                        // Keep the resolved option lists so the card chips can
                        // show human labels for the saved bin / media type.
                        r.inputBins = this.inputBins.slice();
                        r.mediaTypesLive = this.mediaTypes.slice();
                        if (!r.enabled && (this.printPaperSize || this.printInputBin || this.printMediaType)) {
                            r.enabled = true;
                        }
                    }
                    this.showPaperModal = false;
                },

                sizeLabel(row) {
                    return this.psSizeOptionsMap[row.size] || row.size;
                },

                mediaLabel(val) {
                    return MEDIA_LABELS[val] || val;
                },

                inputBinLabel(row) {
                    const found = (row.inputBins || []).find(b => b.value === row.inputBin);
                    return found ? found.label : row.inputBin;
                },

                mediaTypeLiveLabel(row) {
                    const found = (row.mediaTypesLive || []).find(m => m.value === row.mediaTypeLive);
                    return found ? found.label : row.mediaTypeLive;
                },

                gsmLabel(val) {
                    return GSM_LABELS[val] || val;
                },

                async scan() {
                    this.scanState = 'loading';
                    try {
                        const { PrintTrays } = await import('{{ asset("js/printtrays.js") }}');
                        const pp = new PrintTrays({
                            downloadUrl: 'https://noritsucanada.com/print-trays/download/',
                            onNotInstalled: () => {},
                        });
                        await pp.connect();
                        __ptInstance = pp;
                        window.__ptInstance = pp;

                        const list = await pp.getPrinters();
                        const all = Array.isArray(list) ? list : [list].filter(Boolean);

                        this.defaultPrinter = (all.find(p => p.isDefault) || {}).name || null;

                        const noritsu = all.filter(p => /noritsu/i.test(p.name));
                        noritsu.sort((a, b) => {
                            const ai = TRAY_ORDER.findIndex(t => a.name.includes(t));
                            const bi = TRAY_ORDER.findIndex(t => b.name.includes(t));
                            return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi);
                        });

                        const seen = new Set();
                        this.rows = noritsu.map(p => {
                            const key = slugKey(p.name);
                            if (seen.has(key)) return null;
                            seen.add(key);
                            const prior = savedByKey[key] || {};
                            return {
                                key,
                                label: p.name,
                                printer: p.name,
                                driver: p.driver || '',
                                status: p.status || '',
                                defaultPrinter: p.name === this.defaultPrinter,
                                size: prior.size || '',
                                sizeWidth: prior.size_width || null,
                                sizeHeight: prior.size_height || null,
                                media: prior.media || '',
                                gsm: prior.gsm || '',
                                density: prior.density || '',
                                landscape: prior.landscape || false,
                                duplex: prior.duplex || 'simplex',
                                margins: prior.margins || 'default',
                                color: prior.color !== undefined ? prior.color : true,
                                scale: prior.scale || 100,
                                inputBin: prior.input_bin || '',
                                quality: prior.quality || '',
                                mediaTypeLive: prior.media_type_live || '',
                                enabled: prior.enabled ?? false,
                                // Resolved option lists kept for card-chip labels;
                                // populated on Apply from the live modal buffer.
                                inputBins: [],
                                mediaTypesLive: [],
                            };
                        }).filter(Boolean);
                        this.scanState = 'ok';
                    } catch (e) {
                        console.warn('[trays] PrintTrays connection failed:', e.message || e);
                        this.rows = [];
                        this.scanState = 'error';
                    }
                    this.$nextTick(() => {
                        if (window.lucide) lucide.createIcons();
                    });
                },

                selectedPaperWidth(row) {
                    if (row.sizeWidth) return row.sizeWidth;
                    const d = STATIC_DIMS[row.size];
                    return d ? d.w : '';
                },

                selectedPaperHeight(row) {
                    if (row.sizeHeight) return row.sizeHeight;
                    const d = STATIC_DIMS[row.size];
                    return d ? d.h : '';
                },
            };
        }
    </script>
@endpush
