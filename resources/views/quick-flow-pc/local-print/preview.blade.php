@extends('layouts.quick-flow-pc')
@section('title', 'Preview & Print | Qrinto')

@section('content')
    @php
        $uploadList = $uploads->values();
        $imageUrls = $uploadList->map(fn($u) => asset('storage/' . ltrim($u->file_path ?? $u->path, '/')))->all();
        $totalPages = count($imageUrls);
    @endphp

    <div class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[1080px] mx-auto" x-data="previewFlow()" x-init="init()">

            <div class="flex items-center gap-2 mb-3 md:mb-4">
                <a href="{{ route('localprint.sizes') }}"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ← <span class="hidden md:inline">Back to sizes</span>
                </a>
                <h1 class="text-base md:hidden font-extrabold text-[#112419] tracking-tight">Preview & Print</h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 items-start">

                {{-- ═══ Left: swipe slider preview ═══ --}}
                <div class="bg-white border border-slate-200/80 rounded-xl md:rounded-2xl p-4 md:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-bold text-slate-900 text-sm">Your Design</h2>
                            @if (!empty($flowData['size_name']))
                                <p class="mono text-[11px] text-slate-400 mt-0.5">
                                    {{ $flowData['size_name'] }} · {{ ucfirst($flowData['orientation'] ?? 'portrait') }}
                                </p>
                            @endif
                        </div>
                        @if ($totalPages > 1)
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" @click="slidePrev()" :disabled="slide <= 0"
                                    class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                </button>
                                <span class="mono text-[11px] text-slate-500 tabular-nums w-14 text-center">
                                    <span x-text="slide + 1"></span> / {{ $totalPages }}
                                </span>
                                <button type="button" @click="slideNext()" :disabled="slide >= {{ $totalPages - 1 }}"
                                    class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Swipe slider --}}
                    <div class="slider-viewport rounded-xl mt-4 bg-[#f2f7f2] overflow-hidden relative"
                        @keydown.right.window="slideNext()" @keydown.left.window="slidePrev()"
                        @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)">
                        <div class="slider-track flex"
                            :style="'transform: translateX(-' + (slide * 100) +
                            '%); transition: transform 0.4s cubic-bezier(.4,0,.2,1);'">
                            @foreach ($imageUrls as $idx => $url)
                                <div class="slider-slide flex-shrink-0 w-full flex items-center justify-center p-4"
                                    style="min-height: 150px;">
                                    <img src="{{ $url }}" alt="Page {{ $idx + 1 }}"
                                        class="max-w-full max-h-[170px] md:max-h-[340px] rounded-lg bg-white object-contain"
                                        style="box-shadow: 0 4px 20px rgba(0,0,0,.10), 0 0 0 1px rgba(0,0,0,.04);"
                                        draggable="false">
                                </div>
                            @endforeach
                        </div>

                        {{-- Prev/Next overlay arrows for mouse --}}
                        @if ($totalPages > 1)
                            <button type="button" @click="slidePrev()" x-show="slide > 0"
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm shadow-md flex items-center justify-center text-slate-600 hover:bg-white hover:scale-110 transition z-10">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button type="button" @click="slideNext()" x-show="slide < {{ $totalPages - 1 }}"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm shadow-md flex items-center justify-center text-slate-600 hover:bg-white hover:scale-110 transition z-10">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Page dots --}}
                    @if ($totalPages > 1)
                        <div class="flex items-center justify-center gap-1.5 mt-3">
                            @foreach ($imageUrls as $idx => $url)
                                <button type="button" @click="slide = {{ $idx }}"
                                    class="w-2 h-2 rounded-full transition-all duration-300"
                                    :class="slide === {{ $idx }} ? 'bg-[#287d3c] scale-125' :
                                        'bg-slate-300 hover:bg-slate-400'">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Page label --}}
                    @if ($totalPages > 1)
                        <p class="text-center text-[11px] text-slate-400 mt-1 font-medium" x-text="'Page ' + (slide + 1)">
                        </p>
                    @endif

                    {{-- Specs --}}
                    <div class="mono text-[13px] text-slate-500 mt-4 space-y-1.5">
                        <p><span class="text-slate-900 font-bold">{{ $totalPages }}</span>
                            {{ $totalPages === 1 ? 'page' : 'pages' }}</p>
                        @if (!empty($flowData['size_name']))
                            <p><span class="text-slate-900 font-bold">{{ $flowData['size_name'] }}</span>
                                <span>({{ ucfirst($flowData['orientation'] ?? 'portrait') }})</span>
                            </p>
                        @endif
                    </div>

                    {{-- Download --}}
                    @if (!empty($pdfUrl))
                        <div class="mt-4">
                            <a href="{{ $pdfUrl }}" download
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 text-[12px] font-semibold hover:bg-slate-50 transition">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                Download PDF
                            </a>
                        </div>
                    @endif
                </div>

                {{-- ═══ Right: preflight checks + tray + print (same as check.blade.php) ═══ --}}
                <div class="bg-white border border-slate-200/80 rounded-xl md:rounded-2xl p-4 md:p-6">
                    <h2 class="font-bold text-slate-900 text-sm mb-4">Checks</h2>

                    {{-- Dynamic check rows --}}
                    <div class="grid grid-cols-2 md:grid-cols-1 gap-x-4">
                    <template x-for="c in checks" :key="c.key">
                        <div class="flex items-start gap-2.5 py-3.5 border-b border-slate-100">
                            <template x-if="c.ok">
                                <i data-lucide="check" class="w-4 h-4 text-[#287d3c] mt-0.5 shrink-0"></i>
                            </template>
                            <template x-if="!c.ok && c.severity === 'warn'">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
                            </template>
                            <template x-if="!c.ok && c.severity === 'error'">
                                <i data-lucide="x" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
                            </template>
                            <div>
                                <p class="text-[13px] font-bold text-slate-900" x-text="c.title"></p>
                                <p class="text-[12px] text-slate-500 mt-0.5" x-text="c.detail"></p>
                            </div>
                        </div>
                    </template>
                    </div>

                    <div class="mt-5">
                        @if ($storeName)
                            <p class="text-[12px] text-slate-500 mb-3">
                                <i data-lucide="store" class="w-3.5 h-3.5 inline -mt-0.5"></i>
                                Printing as <span class="font-bold text-slate-700">{{ $storeName }}</span>
                            </p>
                        @endif

                        {{-- PrintTrays bridge status --}}
                        <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-lg text-[12px]"
                            :class="bridgeReady ? 'bg-emerald-50 text-emerald-700' : (bridgeChecking ? 'bg-slate-50 text-slate-500' :
                                'bg-red-50 text-red-600')">
                            <template x-if="bridgeChecking">
                                <span>
                                    <div
                                        class="w-3.5 h-3.5 border-2 border-slate-300 border-t-slate-600 rounded-full animate-spin inline-block">
                                    </div>
                                    Connecting to PrintTrays…
                                </span>
                            </template>
                            <template x-if="!bridgeChecking && bridgeReady">
                                <span><i data-lucide="check-circle" class="w-3.5 h-3.5 inline -mt-0.5"></i> PrintTrays
                                    connected <span class="text-slate-400" x-show="bridgeVersion" x-text="'v' + bridgeVersion"></span></span>
                            </template>
                            <template x-if="!bridgeChecking && !bridgeReady">
                                <span class="flex items-center gap-1.5 w-full">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span class="flex-1">PrintTrays is not installed or not running</span>
                                    <button type="button" @click="showBridgeModal = true"
                                        class="font-bold underline hover:no-underline shrink-0">Fix this</button>
                                </span>
                            </template>
                        </div>

                        {{-- PrintTrays download modal --}}
                        <div x-show="showBridgeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                            @keydown.escape.window="showBridgeModal = false">
                            <div class="absolute inset-0 bg-black/40" @click="showBridgeModal = false"></div>
                            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-bold text-slate-900 text-base">PrintTrays Required</h3>
                                    <button type="button" @click="showBridgeModal = false"
                                        class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="text-center py-4">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                                        <i data-lucide="printer" class="w-7 h-7 text-red-500"></i>
                                    </div>
                                    <p class="text-sm text-slate-700 font-medium">PrintTrays is required to connect to your
                                        local printer.</p>
                                    <p class="text-[12px] text-slate-500 mt-2 leading-relaxed">
                                        Download and install PrintTrays, then reload this page. It runs in the background and
                                        lets your browser communicate with printers on this PC.
                                    </p>
                                </div>

                                <div class="space-y-2 mt-4">
                                    <a href="https://noritsucanada.com/print-trays/download/" target="_blank"
                                        class="w-full flex items-center justify-center gap-2 bg-[#287d3c] hover:bg-emerald-800 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                                        <i data-lucide="download" class="w-4 h-4"></i> Download PrintTrays
                                    </a>
                                    <a href="https://www.dropbox.com/scl/fi/d5f74l6ekg7r3hoxhvhwm/Noritsu_931BL_Driver_Setup-v2.2.exe?rlkey=m94hgu9eww95zj7mr7wtpli30&st=e658g2hc&e=1&dl=1"
                                        target="_blank"
                                        class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                                        <i data-lucide="download" class="w-4 h-4"></i> Download 931-BL Multi Driver
                                    </a>
                                    <button type="button" @click="showBridgeModal = false; bridgeChecking = true; initBridge();"
                                        class="w-full flex items-center justify-center gap-2 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> I've installed it - retry
                                        connection
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Printer list from PrintTrays --}}
                        <div x-show="bridgeReady">
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Select Printer</label>
                            <div class="space-y-2" x-show="printers.length > 0">
                                <template x-for="p in printers" :key="p.name">
                                    <label
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                                        :class="selectedPrinter === p.name ? 'border-[#287d3c] bg-[#f2f7f2]' :
                                            'border-slate-200 hover:border-slate-300'"
                                        @click="selectPrinter(p.name)">
                                        <input type="radio" name="printer_radio" :checked="selectedPrinter === p.name"
                                            :value="p.name" class="accent-[#287d3c] w-4 h-4">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-[13px] text-slate-900" x-text="p.name"></span>
                                            <p class="text-[11px] text-slate-400 mt-0.5" x-show="p.driver" x-text="p.driver"></p>
                                            <template x-if="selectedPrinter === p.name">
                                                <p class="text-[11px] text-slate-500 mt-0.5"
                                                    x-text="psSummary()"></p>
                                            </template>
                                        </div>
                                        <template x-if="selectedPrinter === p.name">
                                            <button type="button" @click.stop="openSettings()"
                                                class="text-[11px] text-[#287d3c] font-bold hover:underline shrink-0">Settings</button>
                                        </template>
                                    </label>
                                </template>
                            </div>
                            <p x-show="printers.length === 0" class="text-[12px] text-slate-400">No printers found on this
                                PC.</p>
                        </div>

                        <div class="flex items-center justify-between mt-5">
                            <label class="text-sm font-semibold text-slate-700">Copies</label>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="copies = Math.max(1, copies - 1)"
                                    class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 active:scale-95 transition disabled:opacity-40"
                                    :disabled="copies <= 1">−</button>
                                <input type="number" x-model.number="copies" min="1" max="999"
                                    class="w-16 text-center rounded-lg border border-slate-200 px-2 py-1.5 text-sm font-bold focus:ring-2 focus:ring-[#287d3c] focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" @click="copies = Math.min(999, copies + 1)"
                                    class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 active:scale-95 transition disabled:opacity-40"
                                    :disabled="copies >= 999">+</button>
                            </div>
                        </div>

                        {{-- Print status toast --}}
                        <div x-show="printMsg" x-cloak x-transition
                            class="mt-3 px-3 py-2 rounded-lg text-[12px] font-medium"
                            :class="printMsgKind === 'success' ? 'bg-emerald-50 text-emerald-700' : (
                                printMsgKind === 'error' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'
                            )"
                            x-text="printMsg"></div>

                        <button type="button" @click="doPrint()" :disabled="!canPrint || printing"
                            class="w-full mt-4 bg-[#287d3c] hover:bg-emerald-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                            <template x-if="printing"><span>Sending to printer…</span></template>
                            <template x-if="!printing && canPrint"><span>Print <span x-text="copies"></span> <span
                                        x-text="copies == 1 ? 'copy' : 'copies'"></span></span></template>
                            <template x-if="!printing && !canPrint"><span
                                    x-text="blocker || 'Not ready to print'"></span></template>
                        </button>

                    </div>
                </div>

            </div>

            @include('partials.print-settings-modal')

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .slider-viewport {
            touch-action: pan-y;
            user-select: none;
        }

        .slider-track {
            will-change: transform;
        }

        .slider-slide img {
            pointer-events: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/print-settings.js') }}?v={{ filemtime(public_path('js/print-settings.js')) }}"></script>
    <script>
        var __ptInstance = null;

        function previewFlow() {
            const pdfUrl = @json($pdfUrl ?? null);
            const trayDims = @json($trayDims);
            const totalPages = {{ $totalPages }};
            const designSize = {
                w: {{ floatval($flowData['size_width'] ?? 0) }},
                h: {{ floatval($flowData['size_height'] ?? 0) }}
            };
            const designOrientation = '{{ $flowData['orientation'] ?? 'portrait' }}';

            return {
                // Shared Print Settings modal (state + capability loading).
                ...window.printSettingsMixin({ sizeOptions: @json($sizeOptions), sizeDims: trayDims }),

                slide: 0,
                touchX: 0,
                copies: 1,

                bridgeChecking: true,
                bridgeReady: false,
                bridgeVersion: null,
                printers: [],
                selectedPrinter: null,
                printing: false,
                printMsg: null,
                printMsgKind: 'info',

                showBridgeModal: false,

                init() {
                    if (designSize.w && designSize.h) {
                        this.printLandscape = designSize.w > designSize.h;
                    }
                    this.initBridge();
                },

                // ── Swipe slider ──
                slideNext() {
                    if (this.slide < totalPages - 1) this.slide++;
                },
                slidePrev() {
                    if (this.slide > 0) this.slide--;
                },
                touchStart(e) {
                    this.touchX = e.changedTouches[0].clientX;
                },
                touchEnd(e) {
                    const dx = e.changedTouches[0].clientX - this.touchX;
                    if (Math.abs(dx) > 40) {
                        dx < 0 ? this.slideNext() : this.slidePrev();
                    }
                },

                // ── Checks ──
                get checks() {
                    const list = [];

                    list.push({
                        key: 'pdf',
                        ok: !!pdfUrl,
                        severity: pdfUrl ? 'ok' : 'error',
                        title: pdfUrl ? 'PDF generated' : 'PDF not generated',
                        detail: pdfUrl ? totalPages + ' page' + (totalPages === 1 ? '' : 's') +
                            ' ready to print.' : 'Something went wrong generating the PDF.',
                    });

                    if (designSize.w && designSize.h) {
                        const dim = this.dynamicDims[this.printPaperSize] || trayDims[this.printPaperSize];
                        if (dim) {
                            const dw = Math.max(designSize.w, designSize.h);
                            const dh = Math.min(designSize.w, designSize.h);
                            const tw = Math.max(dim.w, dim.h);
                            const th = Math.min(dim.w, dim.h);
                            const match = Math.abs(dw - tw) < 0.1 && Math.abs(dh - th) < 0.1;
                            list.push({
                                key: 'size',
                                ok: match,
                                severity: match ? 'ok' : 'warn',
                                title: match ? 'Size matches paper' : 'Size does not match paper',
                                detail: 'Design: ' + designSize.w + ' × ' + designSize.h + ' in. Paper: ' + dim
                                    .w + ' × ' + dim.h + ' in.',
                            });
                        } else {
                            list.push({
                                key: 'size',
                                ok: true,
                                severity: 'ok',
                                title: 'Design size',
                                detail: designSize.w + ' × ' + designSize.h + ' in (' + designOrientation + ')',
                            });
                        }
                    }

                    if (designSize.w && designSize.h) {
                        const dim = this.dynamicDims[this.printPaperSize] || trayDims[this.printPaperSize];
                        if (dim) {
                            const designLandscape = designSize.w > designSize.h;
                            const paperLandscape = dim.w > dim.h;
                            const orientMatch = designLandscape === paperLandscape || (designSize.w === designSize.h) ||
                                (dim.w === dim.h);
                            if (!orientMatch) {
                                list.push({
                                    key: 'orient',
                                    ok: false,
                                    severity: 'warn',
                                    title: 'Orientation mismatch',
                                    detail: 'Design is ' + designOrientation + ', paper is ' + (paperLandscape ?
                                        'landscape' : 'portrait') + '.',
                                });
                            }
                        }
                    }

                    return list;
                },

                // ── PrintTrays bridge ──
                async initBridge() {
                    try {
                        const { PrintTrays } = await import('{{ asset("js/printtrays.js") }}');
                        const pp = new PrintTrays({
                            downloadUrl: 'https://noritsucanada.com/print-trays/download/',
                            onNotInstalled: () => {},
                        });
                        await pp.connect();
                        __ptInstance = pp;
                        window.__ptInstance = pp;
                        this.bridgeReady = true;
                        this.bridgeVersion = pp.version || null;

                        const list = await pp.getPrinters();
                        const all = Array.isArray(list) ? list : [list].filter(Boolean);
                        const trayOrder = ['MP Tray', 'Tray 1', 'Tray 2', 'Tray 3', 'Tray 4', 'Tray 5'];
                        all.sort((a, b) => {
                            const aN = /noritsu/i.test(a.name) ? 0 : 1;
                            const bN = /noritsu/i.test(b.name) ? 0 : 1;
                            if (aN !== bN) return aN - bN;
                            const ai = trayOrder.findIndex(t => a.name.includes(t));
                            const bi = trayOrder.findIndex(t => b.name.includes(t));
                            return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi);
                        });
                        this.printers = all;
                        if (this.printers.length > 0) {
                            this.selectedPrinter = this.printers[0].name;
                            this.psLoad(this.selectedPrinter);
                        }
                    } catch (e) {
                        console.warn('[localprint] PrintTrays connection failed:', e.message || e);
                        this.bridgeReady = false;
                    }
                    this.bridgeChecking = false;
                    if (!this.bridgeReady) this.showBridgeModal = true;
                },

                get selectedPrinterInfo() {
                    return this.printers.find(p => p.name === this.selectedPrinter) || null;
                },



                openSettings() {
                    this.psOpen(this.selectedPrinter, this.selectedPrinterInfo?.driver,
                        this.psPrinterStatus(this.selectedPrinterInfo));
                },

                selectPrinter(p) {
                    if (this.selectedPrinter !== p) {
                        this.selectedPrinter = p;
                        // A different printer can support a different paper set.
                        this.psResetCaps();
                        this.psLoad(p);
                        this.openSettings();
                    }
                },

                applyPaperSettings() {
                    this.showPaperModal = false;
                },




                get canPrint() {
                    if (this.copies < 1) return false;
                    if (!pdfUrl) return false;
                    return this.bridgeReady && !!this.selectedPrinter;
                },

                get blocker() {
                    if (!pdfUrl) return 'No PDF generated';
                    if (this.bridgeChecking) return 'Connecting to PrintTrays…';
                    if (!this.bridgeReady) return 'PrintTrays not connected';
                    if (!this.selectedPrinter) return 'Select a printer';
                    return null;
                },

                // Translate the Print Settings modal state into QZ Tray print-config
                // options. The bridge spreads these into qz.configs.create(printer, …),
                // so the key names here must match QZ's config schema exactly.
                buildPrintConfig(type) {
                    const cfg = {
                        type: type,
                        flavor: 'base64',
                        copies: this.copies || 1,
                        orientation: this.printLandscape ? 'landscape' : 'portrait',
                        colorType: this.printColor ? 'color' : 'grayscale',
                    };

                    // Paper size → explicit media size in inches. Prefer the live
                    // printer dimensions, then the static tray-dim table.
                    const dim = this.dynamicDims[this.printPaperSize] || trayDims[this.printPaperSize];
                    if (dim) {
                        cfg.size = {
                            width: dim.w,
                            height: dim.h
                        };
                        cfg.units = 'in';
                    }

                    // Paper source → QZ `printerTray`. Empty means printer default.
                    if (this.printInputBin) {
                        cfg.printerTray = this.printInputBin;
                    }

                    // Duplex: QZ expects false for single-sided, or the two-sided
                    // long/short-edge strings.
                    if (this.printDuplex === 'longEdge') {
                        cfg.duplex = 'two-sided-long-edge';
                    } else if (this.printDuplex === 'shortEdge') {
                        cfg.duplex = 'two-sided-short-edge';
                    } else {
                        cfg.duplex = false;
                    }

                    // For PDF jobs QZ frequently ignores `orientation` and honours
                    // `rotation` (degrees) instead. If the requested orientation
                    // differs from the design's natural orientation, rotate 90°.
                    const wantLandscape = this.printLandscape;
                    const designIsLandscape = (designSize.w && designSize.h) ? designSize.w > designSize.h :
                        wantLandscape;
                    if (wantLandscape !== designIsLandscape) {
                        cfg.rotation = 90;
                    }

                    console.log('[localprint] print config →', JSON.parse(JSON.stringify(cfg)));
                    return cfg;
                },

                async doPrint() {
                    if (!this.canPrint || this.printing) return;
                    this.printing = true;
                    this.printMsg = 'Sending to printer…';
                    this.printMsgKind = 'info';

                    try {
                        const pp = __ptInstance;
                        if (!pp) throw new Error('PrintTrays not connected');

                        const res = await fetch(pdfUrl, { credentials: 'same-origin' });
                        if (!res.ok) throw new Error('Failed to fetch PDF: HTTP ' + res.status);
                        const b64 = this.arrayBufToBase64(await res.arrayBuffer());

                        // The bridge reads a top-level `type` (pdf|image) — without it,
                        // it reports "Unsupported print type: undefined". It also needs
                        // the QZ `flavor:'base64'` key to actually base64-decode the data;
                        // otherwise it writes the raw text and Chromium fails to load the
                        // temp file (ERR_FAILED loading printport-*.pdf).
                        await pp.print(this.selectedPrinter, b64, this.buildPrintConfig('pdf'));
                        this.printMsg = 'Sent to ' + this.selectedPrinter + '!';
                        this.printMsgKind = 'success';
                    } catch (e) {
                        console.error('[localprint] PrintTrays print failed:', e);
                        this.printMsg = (e && e.message) || 'Print failed.';
                        this.printMsgKind = 'error';
                    } finally {
                        this.printing = false;
                    }
                },

                arrayBufToBase64(buf) {
                    const bytes = new Uint8Array(buf);
                    let binary = '';
                    for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
                    return btoa(binary);
                },
            };
        }
    </script>
@endpush
