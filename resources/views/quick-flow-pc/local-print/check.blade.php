@extends('layouts.quick-flow-pc')
@section('title', 'Check & print | Qrinto')

@section('content')
    @php
        $kindLabel = ['single' => 'Single-sided', 'duplex' => 'Double-sided card', 'fold' => 'Folded card'][$file['kind']] ?? 'Single-sided';
    @endphp
    <div class="ck" x-data="checkFlow(
        {{ Illuminate\Support\Js::from([
            'url' => $file['url'], 'name' => $file['name'], 'bytes' => $file['bytes'], 'type' => $file['type'],
            'images' => $file['images'], 'kind' => $file['kind'], 'shape' => $file['shape'], 'page_limit' => $file['page_limit'],
        ]) }},
        {{ Illuminate\Support\Js::from($trays) }},
        {{ Illuminate\Support\Js::from($trayDims) }}
    )" x-init="init()">

        {{-- Off-screen render targets: the existing tray/file canvases (still used
             for the Print Settings modal preview) and the card-preview page stash. --}}
        <div class="ck-offscreen" aria-hidden="true">
            <canvas x-ref="pdfCanvas"></canvas>
            <canvas x-ref="pdfCanvasTray"></canvas>
            <div x-ref="cpStash"></div>
        </div>

        <main class="ck-main">
            {{-- header --}}
            <div class="ck-head">
                <div class="ck-head-l">
                    <a href="{{ route('localprint.pdf') }}" class="ck-iconbtn" aria-label="Back to upload">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 6l-6 6 6 6" />
                        </svg>
                    </a>
                    <div class="ck-min0">
                        <h1 x-text="file.name" :title="file.name"></h1>
                        <p class="ck-sub">
                            {{ $kindLabel }} · <span x-text="cpPortrait ? 'Portrait' : 'Landscape'"></span>
                            · <span x-text="(pages || '–') + (pages === 1 ? ' page' : ' pages')"></span>
                            · <span x-text="prettySize(file.bytes)"></span>
                        </p>
                    </div>
                </div>
                <a href="{{ route('localprint.pdf') }}" class="ck-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                    </svg>Replace file</a>
            </div>

            <div class="ck-grid">
                {{-- ── preview ───────────────────────────────────── --}}
                <section class="ck-card ck-preview" aria-label="Animated preview">
                    <div class="ck-bar">
                        <div class="ck-chips" role="group" aria-label="Jump to side">
                            <template x-for="ph in cpPhases()" :key="ph[0]">
                                <button type="button" class="ck-chip" :class="cpPhaseOn(ph[1]) && 'on'"
                                    @click="cpPickPhase(ph[1])" x-text="ph[0]"></button>
                            </template>
                        </div>
                        <button type="button" class="ck-chip ck-play" x-show="cpKind !== 'single'"
                            @click="cpTogglePlay()">
                            <svg x-show="cpPlaying" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                                <path d="M8 5v14M16 5v14" />
                            </svg>
                            <svg x-show="!cpPlaying" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.4" stroke-linejoin="round" aria-hidden="true">
                                <path d="M7 5l12 7-12 7z" />
                            </svg>
                            <span x-text="cpPlaying ? 'Pause' : 'Play'"></span>
                        </button>
                    </div>

                    <div class="ck-stage">
                        <div x-show="!cpReady && !pdfError" class="ck-loading">
                            <span class="ck-spin"></span>
                            <span>Building preview…</span>
                        </div>
                        <p x-show="pdfError" x-cloak class="ck-error" x-text="pdfError"></p>

                        <div x-show="cpReady && !pdfError" x-cloak class="ck-stage-in">
                            {{-- single --}}
                            <template x-if="cpKind === 'single'">
                                <div class="ck-col">
                                    <div class="ck-face-box" :class="cpPortrait ? 'p' : 'l'">
                                        <div class="face"><canvas data-cp="front"></canvas></div>
                                    </div>
                                    <span class="ck-pill">Front · Page 1</span>
                                </div>
                            </template>

                            {{-- double-sided: flips over --}}
                            <template x-if="cpKind === 'duplex'">
                                <div class="ck-col">
                                    <div class="ck-face-box flip-card" :class="cpPortrait ? 'p' : 'l'"
                                        :style="cpAnimStyle()" role="img" aria-label="Card flipping over: front, then back">
                                        <div class="face"><canvas data-cp="front"></canvas></div>
                                        <div class="face face-back"><canvas data-cp="back"></canvas></div>
                                    </div>
                                    <div class="ck-caps" aria-hidden="true">
                                        <div class="cap d-front" :style="cpAnimStyle()"><span class="ck-pill">Front · Page 1</span></div>
                                        <div class="cap d-back" :style="cpAnimStyle()"><span class="ck-pill">Back · Page 2</span></div>
                                    </div>
                                </div>
                            </template>

                            {{-- folded portrait: opens like a book --}}
                            <template x-if="cpKind === 'fold' && cpPortrait">
                                <div class="ck-col ck-gap40">
                                    <div class="fold-wrap" :style="cpAnimStyle()" role="img"
                                        aria-label="Folded card opening like a book, then turning over to the back">
                                        <div class="fold-base">
                                            <div class="face"><canvas data-cp="inR"></canvas></div>
                                            <div class="face face-back"><canvas data-cp="back"></canvas></div>
                                        </div>
                                        <div class="fold-leaf" :style="cpAnimStyle()">
                                            <div class="face"><canvas data-cp="front"></canvas></div>
                                            <div class="face face-back ck-inner-l"><canvas data-cp="inL"></canvas></div>
                                        </div>
                                    </div>
                                    <div class="ck-caps" aria-hidden="true">
                                        <div class="cap f-front" :style="cpAnimStyle()"><span class="ck-pill">Front cover</span></div>
                                        <div class="cap f-in" :style="cpAnimStyle()"><span class="ck-pill">Inside spread</span></div>
                                        <div class="cap f-back" :style="cpAnimStyle()"><span class="ck-pill">Back cover</span></div>
                                    </div>
                                </div>
                            </template>

                            {{-- folded landscape: opens upward --}}
                            <template x-if="cpKind === 'fold' && !cpPortrait">
                                <div class="ck-col">
                                    <div class="foldl-wrap" :style="cpAnimStyle()" role="img"
                                        aria-label="Folded card opening upward, then turning over to the back">
                                        <div class="foldl-base">
                                            <div class="face"><canvas data-cp="inR"></canvas></div>
                                            <div class="face face-back"><canvas data-cp="back"></canvas></div>
                                        </div>
                                        <div class="foldl-leaf" :style="cpAnimStyle()">
                                            <div class="face"><canvas data-cp="front"></canvas></div>
                                            <div class="face face-backx ck-inner-t"><canvas data-cp="inL"></canvas></div>
                                        </div>
                                    </div>
                                    <div class="ck-caps" aria-hidden="true">
                                        <div class="cap f-front" :style="cpAnimStyle()"><span class="ck-pill">Front cover</span></div>
                                        <div class="cap f-in" :style="cpAnimStyle()"><span class="ck-pill">Inside spread</span></div>
                                        <div class="cap f-back" :style="cpAnimStyle()"><span class="ck-pill">Back cover</span></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="ck-panels">
                        <div class="ck-panels-head">
                            <h2>Check each panel</h2>
                            <button type="button" class="ck-btn" x-show="cpEditable && cpUsed >= 2" @click="cpToggleSwap()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 4L3 8l4 4M3 8h14M17 20l4-4-4-4M21 16H7" />
                                </svg>Swap page 1 and 2</button>
                        </div>
                        <div class="ck-panel-grid">
                            <template x-for="(pn, i) in cpPanels()" :key="pn.key">
                                <div class="ck-panel">
                                    <div class="ck-thumb" :class="cpPortrait ? 'p' : 'l'"><canvas :data-cp="pn.key"></canvas></div>
                                    <span class="ck-min0 ck-grow">
                                        <span class="ck-panel-n" x-text="(i + 1) + ' · ' + pn.label"></span>
                                        <span class="ck-panel-src" x-text="cpSource(pn.key)"></span>
                                    </span>
                                    <button type="button" class="ck-iconbtn sm" x-show="cpCanFlip(pn.key)"
                                        @click="cpToggleFlip(pn.key)" :aria-label="'Turn ' + pn.label.toLowerCase() + ' upside down'"
                                        title="Turn upside down">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20 11a8 8 0 1 0-2.3 5.7" />
                                            <path d="M20 4v7h-7" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div class="ck-note" x-show="cpNote()" x-cloak>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v5M12 16.5v.5" />
                            </svg>
                            <span x-text="cpNote()"></span>
                        </div>
                    </div>
                </section>

                {{-- ── print settings ────────────────────────────── --}}
                <aside class="ck-card ck-side" aria-label="Print settings">
                    <div>
                        <div class="ck-side-head">
                            <h2>Select Printer</h2>
                            <button type="button" class="ck-textbtn" x-show="selectedPrinter" @click="openSettings()">Settings</button>
                        </div>

                        <div x-show="bridgeChecking" class="ck-muted ck-row"><span class="ck-spin sm"></span>Connecting to PrintTrays…</div>
                        <div x-show="!bridgeChecking && !bridgeReady" x-cloak class="ck-alert">
                            <span>PrintTrays is not installed or not running.</span>
                            <button type="button" class="ck-textbtn" @click="showBridgeModal = true">Fix this</button>
                        </div>
                        <p x-show="bridgeReady && printers.length === 0" x-cloak class="ck-muted">No Noritsu printers found on this PC.</p>

                        <div class="ck-trays" x-show="bridgeReady && printers.length">
                            <template x-for="(p, i) in printers" :key="p.name">
                                <button type="button" class="ck-tray" :class="selectedPrinter === p.name && 'on'"
                                    :aria-pressed="String(selectedPrinter === p.name)" @click="selectPrinter(p.name)">
                                    <span class="ck-radio"><span></span></span>
                                    <span class="ck-min0">
                                        <span class="ck-tray-name" x-text="p.name" :title="p.name"></span>
                                        <span class="ck-tray-paper" x-show="trayPaper(p.name)" x-text="trayPaper(p.name)"></span>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="ck-copies">
                        <label for="ck-copies">Copies</label>
                        <div class="ck-stepper">
                            <button type="button" aria-label="Fewer copies" @click="copies = Math.max(1, copies - 1)" :disabled="copies <= 1">−</button>
                            <input id="ck-copies" type="number" min="1" max="999" x-model.number="copies">
                            <button type="button" aria-label="More copies" @click="copies = Math.min(999, copies + 1)" :disabled="copies >= 999">+</button>
                        </div>
                    </div>

                    <div class="ck-foot">
                        <div class="ck-muted">
                            {{ $kindLabel }} · <span x-text="cpShape"></span>
                            · <span x-text="selectedPrinter || 'no printer'"></span>
                            · <span x-text="copies + (copies == 1 ? ' copy' : ' copies')"></span>
                            @if ($storeName)
                                · {{ $storeName }}
                            @endif
                        </div>

                        <div x-show="printMsg" x-cloak class="ck-msg" :class="printMsgKind" x-text="printMsg"></div>

                        <button type="button" class="ck-print" @click="doPrint()" :disabled="!canPrint || printing">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linejoin="round" aria-hidden="true">
                                <path d="M7 9V3h10v6" />
                                <rect x="3" y="9" width="18" height="8" rx="2" />
                                <path d="M7 14h10v7H7z" />
                            </svg>
                            <span x-text="printing ? 'Sending to printer…' : (canPrint ? ('Print ' + copies + (copies == 1 ? ' copy' : ' copies')) : (blocker || 'Not ready to print'))"></span>
                        </button>

                        <div class="ck-status">
                            <span class="ck-row">
                                <span class="ck-dot" :class="bridgeReady ? 'ok' : (bridgeChecking ? '' : 'bad')"></span>
                                <span x-text="bridgeReady ? ('PrintTrays connected' + (bridgeVersion ? ' · v' + bridgeVersion : '')) : (bridgeChecking ? 'Connecting…' : 'PrintTrays not connected')"></span>
                            </span>
                            <a href="https://noritsucanada.com/new-print-trays/download/" target="_blank" rel="noopener">Download PrintTrays</a>
                        </div>
                    </div>
                </aside>
            </div>
        </main>

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
                    <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="printer" class="w-7 h-7 text-red-500"></i>
                    </div>
                    <p class="text-sm text-slate-700 font-medium">PrintTrays is required to connect to your local printer.</p>
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
                        <i data-lucide="download" class="w-4 h-4"></i>Download 931-BL Multi Driver
                    </a>
                    <button type="button" @click="trustCertificate()"
                        class="w-full flex items-center justify-center gap-2 border border-amber-300 bg-amber-50 text-amber-800 font-semibold py-2.5 rounded-xl text-sm hover:bg-amber-100 transition">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Trust PrintTrays's certificate
                    </button>
                    <p class="text-[11px] text-slate-400 text-center leading-snug">
                        Click <em>Advanced → Proceed</em> in the window that opens, then close it — the
                        connection retries automatically. Not needed when installed with the real installer.
                    </p>
                    <button type="button" @click="showBridgeModal = false; bridgeChecking = true; initBridge();"
                        class="w-full flex items-center justify-center gap-2 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> I've installed it - retry connection
                    </button>
                </div>
            </div>
        </div>

        @include('partials.new-test-print-settings-modal')
    </div>
@endsection

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/local-print-check.css') }}?v={{ filemtime(public_path('css/local-print-check.css')) }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
    <script
        src="{{ asset('js/new-test-print-settings.js') }}?v={{ filemtime(public_path('js/new-test-print-settings.js')) }}">
    </script>
    <script src="{{ asset('js/card-preview.js') }}?v={{ filemtime(public_path('js/card-preview.js')) }}"></script>
    <script src="{{ asset('js/local-print-log.js') }}?v={{ filemtime(public_path('js/local-print-log.js')) }}"></script>
    <script>
        // Full route URLs so LPLog works under a sub-path (e.g. /qrinto).
        window.LPLOG_ROUTES = {
            start: @json(route('localprint.log.start')),
            completeBase: @json(url('print/log')),
        };
    </script>
    <script>
        if (window['pdfjsLib']) {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
        }

        var __ptInstance = null;

        function checkFlow(file, trays, trayDims) {
            let _pdf = null;
            let _inited = false;

            return {
                // Shared Print Settings modal (state + capability loading).
                // Animated card preview + panel mapping (public/js/card-preview.js).
                ...window.cardPreviewMixin({
                    kind: file.kind,
                    shape: file.shape,
                    limit: file.page_limit
                }),

                ...window.printSettingsMixin({
                    sizeOptions: @json($sizeOptions),
                    sizeDims: trayDims,
                    allowCustomSize: true
                }),

                file,
                trays,
                trayDims,

                page: 1,
                pages: 0,
                dims: null,
                dpi: null,
                colorMode: null,
                fontsEmbedded: null,
                fontCount: 0,
                ready: false,
                pdfError: null,

                tray: (trays.find(t => !t.out) || trays[0])?.key,
                copies: 1,
                orientationAction: 'rotate',

                // Preview tab: 'file' (raw artwork) or 'tray' (how it prints on the
                // selected paper — E2E fills the sheet, non-E2E leaves a white border).
                viewMode: 'tray',

                // The PDF that Print will send. Set by "Apply": the file is
                // regenerated at the selected paper size (cover-fill) and kept IN
                // MEMORY (base64), so Print sends this exact file. null = not applied
                // yet, so Print builds it on the fly.
                //
                // NOTE: we deliberately no longer POST the regenerated PDF to the
                // server (save-regenerated). A large cover-filled image PDF made the
                // JSON body exceed the web server's body-size limit → HTTP 413. The
                // regenerated file is only needed for the current print (sent to the
                // local PrintTrays bridge), so keeping it in memory avoids the round
                // trip and the 413 entirely.
                savedPdfB64: null,
                savedPdfUrl: null,

                bridgeChecking: true,
                bridgeReady: false,
                bridgeVersion: null,
                printers: [],
                selectedPrinter: null,
                printing: false,
                printMsg: null,
                printMsgKind: 'info',

                showBridgeModal: false,

                // Activity log (newest first) — same as the new demo's log.
                ptLogLines: [],
                showPtLog: false,
                testPagePrinting: false,

                ptLog(msg) {
                    this.ptLogLines.unshift(new Date().toLocaleTimeString() + '  ' + msg);
                    if (this.ptLogLines.length > 200) this.ptLogLines.length = 200;
                },

                // The new bridge's cert must be trusted once (unless installed via its
                // real installer). Open its cert page in a popup; when the operator
                // closes it, retry the connection — same flow as the new demo.
                trustCertificate() {
                    const url = 'https://127.0.0.1:8191';
                    this.ptLog("Opening PrintTrays's certificate page in a small window...");
                    const w = window.open(url, 'printtrays-trust', 'width=520,height=420');
                    if (!w) {
                        this.ptLog('Popup blocked — allow popups for this site, or open ' + url +
                        ' manually in a new tab.');
                        return;
                    }
                    const t = setInterval(() => {
                        if (w.closed) {
                            clearInterval(t);
                            this.ptLog('Certificate window closed — retrying connection...');
                            this.showBridgeModal = false;
                            this.bridgeChecking = true;
                            this.initBridge();
                        }
                    }, 400);
                },

                // One-page HTML test print (type:'html') with the current modal
                // settings — mirrors the new demo's "Print test page".
                async printTestPage() {
                    const pp = __ptInstance;
                    if (!pp || !this.selectedPrinter || this.testPagePrinting) return;
                    this.testPagePrinting = true;
                    const label = this.selectedSizeLabel();
                    const cfg = {
                        type: 'html',
                        landscape: !!this.printLandscape,
                        color: !!this.printColor,
                        copies: 1,
                        pageOrder: this.printPageOrder || 'frontToBack',
                    };
                    if (this.sizesSource === 'printer' && this.dynamicDims && this.dynamicDims[this.printPaperSize] &&
                        this.printPaperSize !== this.psCustomSizeValue) cfg.paperSize = this.printPaperSize;
                    if (this.printDuplex && this.printDuplex !== 'simplex') cfg.duplex = this.printDuplex;
                    if (this.printScaleMode === 'custom') cfg.scaleFactor = this.printScaleFactor || 100;
                    const esc = (v) => String(v).replace(/[&<>"]/g, (c) => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;'
                    })[c]);
                    const html = '<html><body style="font-family:sans-serif;padding:40px">' +
                        '<h1>PrintTrays test page</h1>' +
                        '<p>If you can read this on paper, the bridge is working.</p>' +
                        '<p>Paper: ' + esc(label) + (cfg.landscape ? ' (landscape)' : '') + '</p>' +
                        // Closing tags escaped as <\/…>: the host (GoDaddy) injects its
                        // tracking script before the first literal closing body tag in
                        // the page — even inside a JS string — which breaks this script.
                        '<p>Printed at ' + esc(new Date().toString()) + '<\/p><\/body><\/html>';
                    this.ptLog('Sending HTML test page to "' + this.selectedPrinter + '" (' + label +
                        (cfg.landscape ? ', landscape' : '') + (cfg.duplex ? ', duplex ' + cfg.duplex : '') +
                        (cfg.color ? '' : ', grayscale') + ')...');
                    console.log('[localprint] HTML test print — request:', {
                        printer: this.selectedPrinter,
                        ...cfg
                    });
                    try {
                        await pp.print(this.selectedPrinter, html, cfg);
                        this.ptLog('Test page sent successfully.');
                        this.printMsg = 'Test page sent to ' + this.selectedPrinter + '.';
                        this.printMsgKind = 'success';
                    } catch (e) {
                        this.ptLog('Test page failed: ' + ((e && e.message) || e));
                        this.printMsg = (e && e.message) || 'Test page failed.';
                        this.printMsgKind = 'error';
                    } finally {
                        this.testPagePrinting = false;
                    }
                },

                // Image files go out as type:'image' when the operator picks that
                // path in the modal (new bridge's fast Chromium image path).
                sendsImageNatively() {
                    // Several images only go out as one merged PDF.
                    return this.file.type === 'image' && !!this.printImageAsImage && this.cpImageUrls().length <= 1;
                },

                // Uploaded images in panel order (swap applied), capped to the kind.
                cpImageUrls() {
                    const all = (this.file.images || []).slice(0, this.cpLimit);
                    if (!all.length) return [this.file.url];
                    if (this.cpSwap && all.length >= 2) [all[0], all[1]] = [all[1], all[0]];
                    return all;
                },
                // Print Settings modal state (showPaperModal, printPaperSize,
                // dynamicSizes, caps, etc.) comes from window.printSettingsMixin.

                async init() {
                    if (_inited) return;
                    _inited = true;
                    // Pre-tick the size / orientation / E2E acknowledgements so those
                    // check rows show checked and don't block printing until the
                    // operator explicitly un-checks one.
                    this.acks = Object.assign({
                        size: true,
                        orient: true,
                        e2e: true
                    }, this.acks);
                    // Show the live preview inside the Print Settings modal too.
                    this.psHasPreview = true;
                    if (file.type === 'image') {
                        await this.initImage();
                    } else {
                        await this.initPdf();
                    }
                    if (this.dims) this.printLandscape = this.dims.w > this.dims.h;

                    // Invalidate any pre-built ("Applied") print PDF whenever a setting
                    // that affects its size/orientation/scaling changes, so Print always
                    // rebuilds at the CURRENT paper instead of sending a stale PDF.
                    ['printPaperSize', 'printLandscape', 'printScaleMode', 'printScaleFactor',
                        'selectedPrinter', 'printCustomW', 'printCustomH', 'printImageAsImage'
                    ].forEach((k) => {
                        this.$watch(k, () => {
                            this.savedPdfB64 = null;
                            this.savedPdfUrl = null;
                        });
                    });

                    this.initBridge();
                },

                async initBridge() {
                    try {
                        const {
                            PrintTrays
                        } = await import(
                            '{{ asset('js/new-testing-printtrays.js') }}?v={{ filemtime(public_path('js/new-testing-printtrays.js')) }}'
                            );
                        const pp = new PrintTrays({
                            downloadUrl: 'https://noritsucanada.com/new-print-trays/download/',
                            onNotInstalled: () => {},
                        });
                        this.ptLog('Connecting to the new PrintTrays bridge (wss://127.0.0.1:8191) ...');
                        await pp.connect();
                        __ptInstance = pp;
                        window.__ptInstance = pp;
                        this.bridgeReady = true;
                        this.bridgeVersion = pp.version || null;

                        this.ptLog('Connected to PrintTrays v' + (pp.version || 'unknown') +
                            '. Fetching printer list...');
                        const list = await pp.getPrinters();
                        const raw = Array.isArray(list) ? list : [list].filter(Boolean);
                        this.printers = this.orderPrinters(raw);
                        this.ptLog('Found ' + this.printers.length + ' printer(s).');
                        if (this.printers.length > 0) {
                            this.selectedPrinter = this.printers[0].name;
                            this.psLoad(this.selectedPrinter);
                        }
                    } catch (e) {
                        console.warn('[localprint] PrintTrays connection failed:', e.message || e);
                        this.ptLog("Couldn't connect: " + ((e && e.message) || e));
                        this.bridgeReady = false;
                    }
                    this.bridgeChecking = false;
                    if (!this.bridgeReady) this.showBridgeModal = true;
                },

                // Order printers by tray number, regardless of the exact printer-name
                // string. The MP / multi tray (no tray number) comes first, then Tray
                // 1, 2, 3, 4, 5…
                orderPrinters(list) {
                    const trayNum = (name) => {
                        const m = String(name).match(/tray\s*(\d+)/i);
                        return m ? parseInt(m[1], 10) : null;
                    };
                    const rank = (name) => {
                        const n = trayNum(name);
                        // No tray number (MP / multi tray) sorts first, then
                        // numbered trays in ascending order.
                        return n === null ? -1 : n;
                    };
                    return (list || [])
                        .filter(p => p && typeof p.name === 'string')
                        // Noritsu-only filter — show ONLY Noritsu printer queues.
                        .filter(p => p.name.toLowerCase().includes('noritsu'))
                        .sort((a, b) => {
                            const ra = rank(a.name),
                                rb = rank(b.name);
                            if (ra !== rb) return ra - rb;
                            return a.name.localeCompare(b.name);
                        });
                },

                // Short tray name for a printer queue ("… MP Tray" → "MP Tray").
                // Paper loaded in a printer queue: the store's tray setup first,
                // then the live driver settings for the selected queue.
                storeTrayFor(name) {
                    const n = String(name || '').trim().toLowerCase();
                    return this.trays.find(t => String(t.printer || '').trim().toLowerCase() === n) || null;
                },
                trayPaper(name) {
                    const t = this.storeTrayFor(name);
                    if (t && (t.desc || t.gsm)) return [t.desc, t.gsm ? t.gsm + ' gsm' : ''].filter(Boolean).join(' · ');
                    if (name === this.selectedPrinter) return this.psSummary();
                    return '';
                },

                trayLabel(name) {
                    const m = String(name || '').match(/(mp|multi[\s-]*purpose)\s*tray|tray\s*\d+/i);
                    return m ? m[0].replace(/^mp/i, 'MP').replace(/^tray/i, 'Tray') : name;
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

                // Apply: regenerate the print PDF at the selected paper size
                // (cover-fill) and keep it IN MEMORY, so Print sends this exact file.
                // No server round trip — see savedPdfB64 note above (avoids HTTP 413).
                async applyPaperSettings() {
                    if (this.psApplying) return;
                    this.psApplying = true;
                    try {
                        this.savedPdfB64 = await this.buildPrintPdfBase64();
                        this.savedPdfUrl = null;
                        this.printMsg = 'Print file ready (' + this.selectedSizeLabel() + ').';
                        this.printMsgKind = 'success';
                        this.showPaperModal = false;
                    } catch (e) {
                        console.error('[localprint] regenerate failed:', e);
                        this.savedPdfB64 = null;
                        this.printMsg = (e && e.message) || 'Could not prepare the print file.';
                        this.printMsgKind = 'error';
                    } finally {
                        this.psApplying = false;
                    }
                },

                // Build the print-ready PDF (base64) from the uploaded file at the
                // selected paper size. Images are cover-filled into a paper-sized PDF;
                // uploaded PDFs are rotated to the chosen orientation. Shared by
                // Apply (save) and Print (fallback when nothing was saved).
                async buildPrintPdfBase64() {
                    if (this.file.type === 'image') {
                        const urls = this.cpImageUrls();
                        if (urls.length <= 1 || !window.PDFLib) return await this.imageToPdfBase64(urls[0] || this.file.url);
                        // Several images (duplex / folded card): one page each, in
                        // panel order, merged into a single print PDF.
                        const merged = await window.PDFLib.PDFDocument.create();
                        for (const u of urls) {
                            const one = await window.PDFLib.PDFDocument.load(await this.imageToPdfBase64(u));
                            (await merged.copyPages(one, one.getPageIndices())).forEach(p => merged.addPage(p));
                        }
                        return this.arrayBufToBase64((await merged.save()).buffer);
                    }
                    const res = await fetch(this.file.url, {
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('Failed to fetch file: HTTP ' + res.status);
                    let bytes = new Uint8Array(await res.arrayBuffer());
                    // Only the pages this card kind uses, in panel order (swap / flips).
                    try {
                        bytes = await this.cpPrepareBytes(bytes);
                    } catch (e) {
                        console.warn('[localprint] page selection skipped:', e && e.message || e);
                    }
                    if (window.PDFLib) {
                        try {
                            const {
                                PDFDocument,
                                degrees
                            } = window.PDFLib;
                            const src = await PDFDocument.load(bytes);
                            const srcPages = src.getPages();

                            // Target paper size (inches → points) at the SELECTED paper's
                            // FULL bleed dims (trim filter disabled), in the chosen
                            // orientation. Each source page is re-laid onto a page of
                            // this exact size so the output PDF really is e.g.
                            // 5.57 × 7.49 — not the original 5 × 7.
                            const PT = 72;
                            // Output is ALWAYS portrait — the landscape toggle only
                            // drives the preview, never the printed/saved PDF.
                            const outLandscape = false;
                            // Start from the paper dims (live driver dims, then the static
                            // table, then psDim()); E2E paper is reduced to its trim below.
                            const _live = this.dynamicDims && this.dynamicDims[this.printPaperSize];
                            const _stat = this.trayDims && this.trayDims[this.printPaperSize];
                            let dim = ((_live && _live.w > 0) ? _live :
                                ((_stat && _stat.w > 0) ? _stat :
                                    (this.psDim() || {
                                        w: 8.5,
                                        h: 11
                                    })));
                            // Driver CUSTOM page size: use the reported dims as-is
                            // (the 0.57 / 0.49 in trim is applied below, as for E2E).
                            if (this.printPaperSize === this.psCustomSizeValue) {
                                const _cd = this.psDim();
                                if (_cd && _cd.w > 0 && _cd.h > 0) {
                                    const _mn = Math.min(_cd.w, _cd.h),
                                        _mx = Math.max(_cd.w, _cd.h);
                                    dim = {
                                        w: _mn,
                                        h: _mx
                                    };
                                }
                            }
                            // E2E paper: the PDF is built at the TRIM size — bleed dims minus
                            // 0.57 in (width) / 0.49 in (height), e.g. 7.57 × 10.49 → 7 × 10 —
                            // not at the bleed size. The driver's E2E form adds the bleed.
                            const _e2e = this.trimsBleed();
                            if (_e2e) dim = this.e2eTrimDims(dim);
                            // PDF already AT the selected paper's trim size (e.g. a 5 × 7
                            // PDF on "5 x 7in E2E (5.57 × 7.49 in)"): do NOT rebuild it.
                            // Portrait → send the original bytes untouched (same as the new
                            // demo, which never rebuilds a PDF); landscape → only turn it
                            // portrait, keeping the trim page size.
                            if (_e2e || this.printPaperSize !== this.psCustomSizeValue) {
                                const _trim = _e2e ? [dim.w, dim.h] : (this.trimSizeFromCode(this.printPaperSize) ||
                                    this.trimSizeFromCode(this.selectedSizeLabel ? this.selectedSizeLabel() : ''));
                                if (_trim) {
                                    const tMin = Math.min(_trim[0], _trim[1]),
                                        tMax = Math.max(_trim[0], _trim[1]);
                                    const TOL = 0.03; // inches
                                    let allPortrait = true;
                                    const allMatch = srcPages.length > 0 && srcPages.every((sp) => {
                                        const b = (sp.getCropBox && sp.getCropBox()) || sp.getMediaBox();
                                        let w = b.width / 72,
                                            h = b.height / 72;
                                        const rot = ((sp.getRotation && sp.getRotation().angle) || 0) % 180;
                                        if (rot)[w, h] = [h, w];
                                        if (w > h) allPortrait = false;
                                        return Math.abs(Math.min(w, h) - tMin) <= TOL && Math.abs(Math.max(w,
                                            h) - tMax) <= TOL;
                                    });
                                    if (allMatch) {
                                        console.log('[localprint] pdf→print: source already at trim ' + tMin + ' × ' +
                                            tMax +
                                            ' in — ' + (allPortrait ? 'sending ORIGINAL PDF (no rebuild).' :
                                                'rotating only, keeping trim size.'));
                                        if (allPortrait) return this.arrayBufToBase64(bytes.buffer || bytes);
                                        dim = {
                                            w: tMin,
                                            h: tMax
                                        };
                                    }
                                }
                            }
                            console.log('[localprint] pdf→print paper dim →', {
                                code: this.printPaperSize,
                                live: _live,
                                stat: _stat,
                                dim,
                            });
                            const rawMax = Math.max(dim.w, dim.h),
                                rawMin = Math.min(dim.w, dim.h);
                            const pageW = (outLandscape ? rawMax : rawMin) * PT;
                            const pageH = (outLandscape ? rawMin : rawMax) * PT;

                            const out = await PDFDocument.create();
                            // E2E: always zoom from the centre until the page covers the
                            // trim sheet edge to edge ('fit' = cover), whatever the shape.
                            const mode = _e2e ? 'fit' : (this.printScaleMode || 'fit');

                            // Diagnostic: source page size vs target paper size. If the
                            // output still shows white, compare the source page dims here
                            // against the target (pageW/pageH ÷ 72) — a big white border
                            // usually means the uploaded PDF has margins baked INTO its
                            // own page (scaling the page can't remove in-content white).
                            try {
                                const _s0 = srcPages[0];
                                const _ss = _s0 ? _s0.getSize() : {
                                    width: 0,
                                    height: 0
                                };
                                console.log('[localprint] pdf→print re-lay →', {
                                    sourcePage_in: {
                                        w: +(_ss.width / 72).toFixed(3),
                                        h: +(_ss.height / 72).toFixed(3)
                                    },
                                    targetPaper_in: {
                                        w: +(pageW / 72).toFixed(3),
                                        h: +(pageH / 72).toFixed(3)
                                    },
                                    paperCode: this.printPaperSize,
                                    scaleMode: mode,
                                    pages: srcPages.length,
                                });
                            } catch (_e) {}

                            for (const sp of srcPages) {
                                // CropBox/MediaBox origin offset. Canva (and some other
                                // exporters) emit pages whose box does NOT start at (0,0)
                                // — e.g. [0 7.92 360 511.92]. pdf-lib's embedPage() forces the
                                // bounding box to left/bottom = 0 (fullPageBoundingBox), so it
                                // clips the content at the top and leaves a WHITE LINE at the
                                // bottom of the PRINTED copy — even though the on-screen
                                // preview (pdf.js, which honours the box) looks fine. Pass the
                                // REAL box + an adjusting matrix so the content is translated
                                // to a true (0,0) origin before we lay it onto the sheet.
                                let emb;
                                try {
                                    const cb = (sp.getCropBox && sp.getCropBox()) || sp.getMediaBox();
                                    const ox = cb.x || 0,
                                        oy = cb.y || 0;
                                    if (ox || oy) {
                                        emb = await out.embedPage(sp, {
                                                left: ox,
                                                bottom: oy,
                                                right: ox + cb.width,
                                                top: oy + cb.height
                                            },
                                            [1, 0, 0, 1, -ox, -oy]);
                                    }
                                } catch (_e) {}
                                if (!emb) emb = await out.embedPage(sp);
                                const sw = emb.width,
                                    sh = emb.height;
                                const srcLandscape = sw > sh;
                                const needSwap = outLandscape !== srcLandscape;
                                // Footprint on the sheet after any 90° turn.
                                const ew = needSwap ? sh : sw,
                                    eh = needSwap ? sw : sh;
                                let k;
                                if (mode === 'actual') k = 1;
                                else if (mode === 'custom') k = Math.max(0.01, (this.printScaleFactor || 100) / 100);
                                else if (mode === 'fit_area') k = Math.min(pageW / ew, pageH / eh);
                                else k = Math.max(pageW / ew, pageH / eh); // 'fit' = crop to cover
                                const drawW = sw * k,
                                    drawH = sh * k;
                                const page = out.addPage([pageW, pageH]);
                                if (needSwap) {
                                    // -90° (clockwise) turn, centred: a (drawW×drawH) page
                                    // rotated -90° occupies drawH×drawW; place the pivot
                                    // on the sheet's left/top for a clockwise turn.
                                    page.drawPage(emb, {
                                        x: (pageW - drawH) / 2,
                                        y: (pageH + drawW) / 2,
                                        xScale: k,
                                        yScale: k,
                                        rotate: degrees(-90),
                                    });
                                } else {
                                    page.drawPage(emb, {
                                        x: (pageW - drawW) / 2,
                                        y: (pageH - drawH) / 2,
                                        xScale: k,
                                        yScale: k,
                                    });
                                }
                            }
                            bytes = await out.save();
                            console.log('[localprint] pdf→print re-lay OK (cover-filled to paper size).');
                        } catch (e) {
                            // IMPORTANT: on failure we fall back to the ORIGINAL PDF bytes
                            // (design size, e.g. 5×7) — which on a larger media prints with
                            // a WHITE BORDER. If you see this, the cover-fill did NOT run.
                            console.error('[localprint] PDF re-lay FAILED — sending ORIGINAL (may show white border):',
                                e && e.message || e);
                        }
                    }
                    return this.arrayBufToBase64(bytes.buffer || bytes);
                },


                async initImage() {
                    try {
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        await new Promise((resolve, reject) => {
                            img.onload = resolve;
                            img.onerror = () => reject(new Error('Failed to load image'));
                            img.src = file.url;
                        });
                        this.dims = {
                            w: img.naturalWidth / 300,
                            h: img.naturalHeight / 300
                        };
                        this.pages = 1;
                        this.colorMode = 'RGB';
                        this.fontsEmbedded = true;
                        this.fontCount = 0;
                        // Artwork source for the modal's live preview.
                        this.psPreviewSrc = file.url;
                        this.ready = true;
                        this.pages = (file.images || []).length || 1;
                        this.cpLoadImages(file.images && file.images.length ? file.images : [file.url])
                            .catch(e => console.warn('[localprint] card preview failed:', e));
                    } catch (e) {
                        this.pdfError = 'Could not load this image.';
                        this.ready = true;
                    }
                },

                async initPdf() {
                    if (!window['pdfjsLib']) {
                        this.pdfError = 'PDF viewer failed to load.';
                        this.ready = true;
                        return;
                    }
                    try {
                        const res = await fetch(file.url, {
                            credentials: 'same-origin'
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status + ' fetching ' + file.url);
                        const bytes = new Uint8Array(await res.arrayBuffer());

                        // cMapUrl / standardFontDataUrl are REQUIRED for PDFs with
                        // embedded or CID fonts — without them pdf.js render() can hang
                        // or draw a blank page even though getDocument() succeeds.
                        const _CDN = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/';
                        _pdf = await pdfjsLib.getDocument({
                            data: bytes,
                            cMapUrl: _CDN + 'cmaps/',
                            cMapPacked: true,
                            standardFontDataUrl: _CDN + 'standard_fonts/'
                        }).promise;
                        this.pages = _pdf.numPages;

                        const first = await _pdf.getPage(1);
                        const vp = first.getViewport({
                            scale: 1
                        });
                        this.dims = {
                            w: vp.width / 72,
                            h: vp.height / 72
                        };

                        await this.introspectPage(first);
                        this.ready = true;
                        // Enable multi-page navigation in the modal preview.
                        this.psPreviewCount = this.pages || 1;
                        this.psPreviewIndex = 0;

                        // Sequential: pdf.js stalls when one page renders onto two
                        // canvases at the same time.
                        this.$nextTick(async () => {
                            try {
                                await this.renderPage(1);
                            } catch (e) {}
                            this.cpLoadPdf(_pdf).catch(e => console.warn('[localprint] card preview failed:', e));
                        });
                    } catch (e) {
                        console.error('[localprint] PDF read failed:', e);
                        this.pdfError = (e && e.message) ? ('Could not read this PDF: ' + e.message) :
                            'Could not read this PDF.';
                        this.ready = true;
                    }
                },

                async introspectPage(page) {
                    try {
                        const ops = await page.getOperatorList();
                        const fnMap = pdfjsLib.OPS;
                        let usesRGB = false,
                            usesCMYK = false,
                            usesGray = false;
                        for (let i = 0; i < ops.fnArray.length; i++) {
                            const fn = ops.fnArray[i];
                            if (fn === fnMap.setFillRGBColor || fn === fnMap.setStrokeRGBColor) usesRGB = true;
                            if (fn === fnMap.setFillCMYKColor || fn === fnMap.setStrokeCMYKColor) usesCMYK = true;
                            if (fn === fnMap.setFillGray || fn === fnMap.setStrokeGray) usesGray = true;
                        }
                        this.colorMode = usesCMYK ? 'CMYK' : (usesRGB ? 'RGB' : (usesGray ? 'Grayscale' : 'RGB'));

                        const tc = await page.getTextContent();
                        const fontIds = new Set();
                        for (const item of (tc.items || []))
                            if (item.fontName) fontIds.add(item.fontName);
                        this.fontCount = fontIds.size;
                        this.fontsEmbedded = fontIds.size === 0 ? false : true;
                    } catch (e) {
                        /* leave fields as null */
                    }
                },

                async renderPage(num) {
                    if (!_pdf) return;
                    const pg = await _pdf.getPage(num);
                    const scale = 2;
                    const vp = pg.getViewport({
                        scale
                    });
                    // Render the page ONCE to the currently VISIBLE canvas, then copy
                    // the bitmap onto the hidden one with drawImage. pdf.js hangs when
                    // rendering straight into a display:none (or detached) canvas, and
                    // it can't render the same page object onto two canvases at once —
                    // so we render only the visible target and mirror the result.
                    const visible = (this.viewMode === 'file') ?
                        this.$refs.pdfCanvas : this.$refs.pdfCanvasTray;
                    const main = visible || this.$refs.pdfCanvas || this.$refs.pdfCanvasTray;
                    if (!main) return;
                    main.width = vp.width;
                    main.height = vp.height;
                    await pg.render({
                        canvasContext: main.getContext('2d'),
                        viewport: vp
                    }).promise;
                    const other = (main === this.$refs.pdfCanvas) ?
                        this.$refs.pdfCanvasTray : this.$refs.pdfCanvas;
                    if (other) {
                        other.width = vp.width;
                        other.height = vp.height;
                        other.getContext('2d').drawImage(main, 0, 0);
                    }
                    // Snapshot the rendered page as the modal preview's artwork.
                    try {
                        this.psPreviewSrc = main.toDataURL('image/png');
                    } catch (e) {}
                },

                setViewMode(mode) {
                    this.viewMode = mode;
                    if (mode === 'tray' && this.file.type === 'pdf' && this.ready) {
                        this.$nextTick(() => this.renderPage(this.page));
                    }
                },

                // Paper dims (inches) in the orientation it will print, honouring the
                // portrait/landscape toggle.
                trayPaperDims() {
                    const d = this.psDim() || this.trayMedia || {
                        w: 8.5,
                        h: 11
                    };
                    const land = !!this.printLandscape;
                    return {
                        w: land ? Math.max(d.w, d.h) : Math.min(d.w, d.h),
                        h: land ? Math.min(d.w, d.h) : Math.max(d.w, d.h),
                    };
                },

                // Shared px-per-inch for BOTH tabs, so the artwork is the exact same
                // display size whether you're looking at "Your file" or the tray view.
                // Based on fitting the LARGER of the file and the selected paper into
                // the preview box (the paper is the bigger object in the tray view).
                previewScale() {
                    const f = (this.dims && this.dims.w) ? this.dims : {
                        w: 1,
                        h: 1
                    };
                    const p = this.trayPaperDims();
                    const narrow = typeof window !== 'undefined' && window.innerWidth < 768;
                    const boxW = narrow ? 230 : 330;
                    const boxH = narrow ? 260 : 300;
                    const maxW = Math.max(f.w, p.w);
                    const maxH = Math.max(f.h, p.h);
                    return Math.min(boxW / maxW, boxH / maxH);
                },

                // Pixel size of the file artwork at the shared scale (its own orientation).
                fileBoxStyle() {
                    const f = (this.dims && this.dims.w) ? this.dims : {
                        w: 1,
                        h: 1
                    };
                    const s = this.previewScale();
                    return `width: ${Math.max(20, Math.round(f.w * s))}px; height: ${Math.max(20, Math.round(f.h * s))}px;`;
                },

                // Printable area (inches) inside the sheet: full sheet on E2E,
                // sheet minus the ~0.2 in unprintable margin otherwise.
                trayPrintableDims() {
                    const p = this.trayPaperDims();
                    if (this.isE2ESelected()) return {
                        w: p.w,
                        h: p.h
                    };
                    const m = 0.2;
                    return {
                        w: Math.max(0.1, p.w - 2 * m),
                        h: Math.max(0.1, p.h - 2 * m)
                    };
                },

                // Size + rotation (px) of the artwork on the sheet. Mirrors what the
                // print path does: when the chosen orientation differs from the file's
                // own, the design is turned 90° so it fills the sheet the same way it
                // will print. The Scale dropdown then fits that (rotated) footprint
                // against the printable area — live, so the preview updates instantly:
                //   fit (Crop to Cover) → cover: fills the area, overflow cropped from
                //                         centre (clipped by the printable container).
                //   fit_area           → contain: whole design fits, white around it.
                //   actual             → true size.  custom → true size × factor.
                trayFileStyle() {
                    const f = (this.dims && this.dims.w) ? this.dims : {
                        w: 1,
                        h: 1
                    };
                    const fileLandscape = f.w > f.h;
                    const needSwap = !!this.printLandscape !== fileLandscape;
                    // Footprint after any 90° turn, used for fitting to the paper.
                    const ew = needSwap ? f.h : f.w;
                    const eh = needSwap ? f.w : f.h;
                    const c = this.trayPrintableDims();
                    const s = this.previewScale();
                    const mode = this.printScaleMode || 'fit';
                    let k;
                    if (mode === 'actual') k = 1;
                    else if (mode === 'custom') k = Math.max(0.01, (this.printScaleFactor || 100) / 100);
                    else if (mode === 'fit_area') k = Math.min(c.w / ew, c.h / eh);
                    else k = Math.max(c.w / ew, c.h / eh); // 'fit' = crop to cover
                    // The element is sized in its OWN (unrotated) frame; the rotation
                    // below gives it the ew × eh footprint on the sheet.
                    const w = Math.max(8, Math.round(f.w * k * s));
                    const h = Math.max(8, Math.round(f.h * k * s));
                    const rot = needSwap ? ' rotate(-90deg)' : ''; // -90° (clockwise), matches print
                    return `width: ${w}px; height: ${h}px; transform: translate(-50%, -50%)${rot};`;
                },

                // Pixel size of the paper rectangle at the shared scale.
                trayPaperStyle() {
                    const d = this.trayPaperDims();
                    const s = this.previewScale();
                    const w = Math.max(60, Math.round(d.w * s));
                    const h = Math.max(60, Math.round(d.h * s));
                    return `width: ${w}px; height: ${h}px;`;
                },

                // Inset of the printable area inside the sheet. E2E = flush to edges;
                // non-E2E = the ~0.2 in white margin the printer leaves on every side.
                trayContentStyle() {
                    if (this.isE2ESelected()) return 'inset: 0;';
                    const d = this.trayPaperDims();
                    const marginIn = 0.2;
                    const mw = Math.min(45, (marginIn / d.w) * 100);
                    const mh = Math.min(45, (marginIn / d.h) * 100);
                    return `top: ${mh.toFixed(2)}%; bottom: ${mh.toFixed(2)}%; left: ${mw.toFixed(2)}%; right: ${mw.toFixed(2)}%;`;
                },

                trayPaperInfo() {
                    // Show the TRIM size (e.g. 5 × 7) rather than the E2E bleed size
                    // (5.57 × 7.49). The trim is parsed from the size code/label; fall
                    // back to the raw paper dims only when no trim can be derived.
                    const trim = this.trimSizeFromCode(this.printPaperSize) ||
                        this.trimSizeFromCode(this.selectedSizeLabel ? this.selectedSizeLabel() : '');
                    const d = trim ? {
                        w: trim[0],
                        h: trim[1]
                    } : this.psDim();
                    const sz = d ? `${this.fmt(d.w)} × ${this.fmt(d.h)} in` : '';
                    const parts = [];
                    if (sz) parts.push(sz);
                    parts.push(this.isE2ESelected() ? 'Edge-to-edge' : 'With white border');
                    return parts.join(' · ');
                },

                nextPage() {
                    if (this.page < this.pages) {
                        this.page++;
                        this.psPreviewIndex = this.page - 1;
                        this.renderPage(this.page);
                    }
                },
                prevPage() {
                    if (this.page > 1) {
                        this.page--;
                        this.psPreviewIndex = this.page - 1;
                        this.renderPage(this.page);
                    }
                },

                // Modal preview page navigation (overrides the mixin no-op): jump to
                // the chosen page, keep the main pager in sync, and re-render (which
                // refreshes psPreviewSrc for the modal image).
                psPreviewSync(i) {
                    this.page = Math.min(this.pages, Math.max(1, i + 1));
                    this.renderPage(this.page);
                },

                // ── Derived state ──────────────────────────────

                get selectedTray() {
                    return this.trays.find(t => t.key === this.tray);
                },

                get trayMedia() {
                    const t = this.selectedTray;
                    if (!t || !t.size) return null;
                    const d = this.trayDims[t.size];
                    if (!d) return null;
                    return {
                        w: d.w,
                        h: d.h,
                        size: t.size
                    };
                },

                get orientation() {
                    if (!this.dims) return null;
                    return this.dims.w > this.dims.h ? 'landscape' : (this.dims.h > this.dims.w ? 'portrait' :
                        'square');
                },

                get trayOrientation() {
                    const m = this.trayMedia;
                    if (!m) return null;
                    return m.w > m.h ? 'landscape' : (m.h > m.w ? 'portrait' : 'square');
                },

                get orientationCheck() {
                    if (!this.dims || !this.trayMedia) return null;
                    const ok = this.orientation === this.trayOrientation || this.orientation === 'square' || this
                        .trayOrientation === 'square';
                    return {
                        ok,
                        detail: ok ?
                            'File and media orient the same way.' :
                            `The file is ${this.orientation}. The selected media (${this.trayMedia.size.replace('x', ' × ')}) is ${this.trayOrientation}.`,
                    };
                },

                get trimSize() {
                    const m = this.trayMedia;
                    if (!this.dims) return null;
                    if (!m) return this.dims;
                    const df = (this.orientation === this.trayOrientation) ? this.dims : {
                        w: this.dims.h,
                        h: this.dims.w
                    };
                    return df;
                },

                get sizeCheck() {
                    if (!this.dims || !this.trayMedia) {
                        return {
                            key: 'size',
                            ok: !!this.dims,
                            severity: 'warn',
                            title: 'Size',
                            detail: this.dims ? 'Select a print size.' : 'Reading page size…'
                        };
                    }
                    const df = this.trimSize;
                    const m = this.trayMedia;
                    const bleedW = Math.max(0, (df.w - m.w) / 2);
                    const bleedH = Math.max(0, (df.h - m.h) / 2);
                    const withinTrim = Math.abs(df.w - m.w) < 0.06 && Math.abs(df.h - m.h) < 0.06;
                    const withinBleed = df.w >= m.w && df.h >= m.h && df.w - m.w <= 0.5 && df.h - m.h <= 0.5;
                    if (withinTrim) {
                        return {
                            key: 'size',
                            ok: true,
                            severity: 'ok',
                            title: 'Size matches the media',
                            detail: `${this.fmt(m.w)} × ${this.fmt(m.h)} in trim.`
                        };
                    }
                    if (withinBleed) {
                        return {
                            key: 'size',
                            ok: true,
                            severity: 'ok',
                            title: 'Size matches the media',
                            detail: `${this.fmt(m.w)} × ${this.fmt(m.h)} in trimmed, ${this.fmt(Math.max(bleedW, bleedH))} in bleed on all sides.`
                        };
                    }
                    const bigger = df.w > m.w || df.h > m.h;
                    return {
                        key: 'size',
                        ok: false,
                        severity: 'warn',
                        title: 'Size does not match the media',
                        detail: `File is ${this.fmt(df.w)} × ${this.fmt(df.h)} in, media is ${this.fmt(m.w)} × ${this.fmt(m.h)} in.` +
                            (bigger ? ' The printer will crop the excess.' : ' The printer will scale to fit.'),
                    };
                },

                get resolutionCheck() {
                    if (!this.dims || !this.trayMedia) return {
                        key: 'res',
                        ok: false,
                        severity: 'warn',
                        title: 'Resolution',
                        detail: 'Chech the paper type.'
                    };
                    const df = this.trimSize;
                    const eff = Math.round(300 * Math.min(this.trayMedia.w / df.w, this.trayMedia.h / df.h));
                    this.dpi = eff;
                    if (eff >= 250) return {
                        key: 'res',
                        ok: true,
                        severity: 'ok',
                        title: 'Resolution is high enough',
                        detail: `${eff} dpi at final size.`
                    };
                    return {
                        key: 'res',
                        ok: false,
                        severity: 'warn',
                        title: 'Resolution is low',
                        detail: `${eff} dpi at final size. Print may look soft.`
                    };
                },

                get fontCheck() {
                    if (this.fontsEmbedded === null) {
                        return {
                            key: 'font',
                            ok: false,
                            severity: 'warn',
                            title: 'Fonts',
                            detail: 'Checking fonts…'
                        };
                    }
                    if (this.fontCount === 0) {
                        return {
                            key: 'font',
                            ok: true,
                            severity: 'ok',
                            title: 'GSM',
                            detail: 'Check the GSM'
                        };
                    }
                    return {
                        key: 'font',
                        ok: true,
                        severity: 'ok',
                        title: 'Fonts are embedded',
                        detail: `${this.fontCount} font${this.fontCount === 1 ? '' : 's'} found in the file.`
                    };
                },

                get checks() {
                    if (!this.ready || this.pdfError) return [];
                    const list = [];
                    // File-ready row.
                    list.push({
                        key: 'pdf',
                        ok: true,
                        severity: 'ok',
                        title: this.file.type === 'pdf' ? 'PDF generated' : 'Image ready',
                        detail: (this.file.type === 'pdf' ?
                                (this.pages + ' page' + (this.pages === 1 ? '' : 's')) : 'Image') +
                            ' ready to print.',
                    });
                    // Shared size / orientation / E2E checks against the SELECTED paper.
                    const cc = this.psComputeChecks({
                        designW: this.dims ? this.dims.w : 0,
                        designH: this.dims ? this.dims.h : 0,
                        pages: this.file.type === 'pdf' ? this.pages : 1,
                        paperCode: this.printPaperSize,
                        paperLabel: this.selectedSizeLabel(),
                        paperDim: this.psDim(),
                        chosenLandscape: this.printLandscape,
                    });
                    return list.concat(cc);
                },

                // Every failing check that offers an ack checkbox must be ticked.
                // The size / E2E warning cards were removed from this page, so
                // nothing is left to acknowledge — never block Print on them.
                get checksPrintable() {
                    return true;
                },

                get canPrint() {
                    if (!this.ready || this.pdfError) return false;
                    if (this.copies < 1) return false;
                    // Block until every failing check with an ack checkbox is ticked
                    // (size mismatch = error, non-E2E border = warning).
                    if (!this.checksPrintable) return false;
                    if (this.bridgeReady) return !!this.selectedPrinter;
                    if (!this.selectedTray || this.selectedTray.out) return false;
                    return true;
                },

                get blocker() {
                    if (!this.ready) return 'Reading file…';
                    if (this.pdfError) return 'Fix the file to continue';
                    if (this.bridgeChecking) return 'Connecting to PrintTrays…';
                    if (this.bridgeReady && !this.selectedPrinter) return 'Select a printer';
                    if (!this.bridgeReady && (!this.selectedTray || this.selectedTray.out))
                        return 'Choose a loaded tray';
                    if (!this.bridgeReady) return 'PrintTrays not connected';
                    if (!this.checksPrintable) return 'Acknowledge the checks to print';
                    return null;
                },

                // Translate the Print Settings modal state into QZ Tray print-config
                // options. The bridge spreads these into qz.configs.create(printer, …),
                // so the key names here must match QZ's config schema exactly.
                buildPrintConfig(type) {
                    // PrintTrays bridge has its OWN high-level option schema — it is
                    // NOT raw QZ config. The official demo
                    // (noritsucanada.com/print-trays/demo) sends exactly these keys:
                    //   { type, paperSize:<driver media NAME>, landscape:<bool>,
                    //     color:<bool>, copies, duplex?, inputBin? }
                    // Sending QZ-style keys instead (size:{w,h}, orientation,
                    // colorType, printerTray, flavor, margins…) makes the bridge
                    // ignore them and fall back to the printer's DEFAULT media —
                    // which prints inside the hardware printable area and leaves
                    // white space on the sides. Passing the driver's real media
                    // name in `paperSize` is what makes it print edge-to-edge.
                    const cfg = {
                        type: type || 'pdf',
                        // Orientation is BAKED into the generated PDF page for BOTH
                        // images (imageToPdfBase64) and PDFs (buildPrintPdfBase64 now
                        // re-lays each page at the target size + orientation). The page
                        // is therefore already correctly oriented, so we always tell the
                        // bridge `landscape: false` — sending the chosen orientation here
                        // would rotate it a SECOND time at the printer (double rotation).
                        landscape: false,
                        color: !!this.printColor,
                        copies: this.copies || 1,
                        // Page order for MULTI-PAGE PDFs. The bridge only acts on it
                        // when the job has more than one page ('frontToBack' = normal,
                        // 'backToFront' = reversed); single-page jobs ignore it. Sent
                        // exactly like the official PrintTrays demo.
                        pageOrder: this.printPageOrder || 'frontToBack',
                    };

                    // Map the modal's scale choice to the bridge's scaleMode:
                    //   'fit' (fit-to-paper) & 'fit_area' (fit printable) → 'fit'
                    //   'actual' → 'actual', 'custom' → 'custom' (+ scaleFactor).
                    const _m = this.printScaleMode || 'fit';
                    cfg.scaleMode = (_m === 'actual') ? 'actual' : (_m === 'custom' ? 'custom' : 'fit');
                    if (_m === 'custom') cfg.scaleFactor = this.printScaleFactor || 100;

                    // paperSize goes STRAIGHT into SumatraPDF's `paper=<name>` on
                    // the bridge (pdf-to-printer). SumatraPDF matches it against the
                    // driver's own media forms, so it MUST be a real driver media
                    // name — only the live capabilities (getPrinterDetails) give us
                    // those. Our static fallback names (e.g. "Letter") don't exist
                    // on a Noritsu photo printer: sending one makes the whole print
                    // command FAIL ("Command failed … paper=Letter"). So send
                    // paperSize ONLY when it came from the live printer; otherwise
                    // omit it and let SumatraPDF use the printer's DEFAULT loaded
                    // media (the borderless photo paper) — which also prints
                    // edge-to-edge.
                    //
                    // The printer-driver's own CUSTOM page size (e.g. Noritsu's
                    // "PostScript Custom Page Size (11.69 × 44.00 in)") is NOT a
                    // SumatraPDF media name — the bridge/official demo print it by
                    // sending an explicit pageSize {width,height,units} and NO
                    // paperSize. Everything else sends the live driver media NAME as
                    // paperSize (static fallback names are never sent — they don't
                    // exist on the Noritsu driver).
                    if (this.printPaperSize === this.psCustomSizeValue) {
                        // Driver custom page size (PostScript Custom Page Size):
                        //   • send the COMPOSED portrait PDF at the TRIM size (buildPrintPdfBase64
                        //     subtracts the 0.57/0.49 E2E bleed and rotates a landscape design
                        //     -90° into portrait), via doPrint — NO pageSize, NO paperSize.
                        //   • useDriverDefaultSize:true — tells the PrintTrays bridge to use the
                        //     printer DRIVER's own saved custom page size (its dimensions +
                        //     orientation + E2E/bleed), exactly like the official demo does for
                        //     a custom size. The driver then expands our trim PDF to its bleed
                        //     form for a borderless, correctly-oriented print.
                        cfg.useDriverDefaultSize = true;
                        cfg.scaleMode = 'fit';
                        delete cfg.scaleFactor;
                        // New bridge (same as the new demo): also send the custom size
                        // explicitly, and the optional ~4% safety shrink.
                        const _cd = this.psDim();
                        if (_cd && _cd.w > 0 && _cd.h > 0) {
                            cfg.pageSize = {
                                width: Math.min(_cd.w, _cd.h),
                                height: Math.max(_cd.w, _cd.h),
                                units: 'in'
                            };
                        }
                        if (this.printSafetyShrink) cfg.paperSafetyShrink = true;
                    } else if (this.sizesSource === 'printer' &&
                        this.dynamicDims && this.dynamicDims[this.printPaperSize]) {
                        cfg.paperSize = this.printPaperSize;
                    }

                    // Duplex values match the bridge's own select: '' (simplex),
                    // 'longEdge', 'shortEdge'. Only send when the user picked one.
                    // 'simplex' is the modal's own value — the bridge expects no key.
                    if (this.printDuplex && this.printDuplex !== 'simplex') cfg.duplex = this.printDuplex;

                    // Paper source → bridge `inputBin` (goes into SumatraPDF
                    // `bin=`). Only from live capabilities; empty = printer default.
                    if (this.printInputBin) cfg.inputBin = this.printInputBin;

                    // New bridge options (new demo): fast-raster for PDFs; for a
                    // native image job, the page size of the composed image and the
                    // optional classic (Sumatra) engine.
                    cfg.fastRaster = !!this.printFastRaster;
                    if (cfg.type === 'image') {
                        const _d = this.imagePageDims();
                        if (!cfg.pageSize) cfg.pageSize = {
                            width: _d.w,
                            height: _d.h,
                            units: 'in'
                        };
                        if (this.printClassicImageEngine) cfg.imageEngine = 'sumatra';
                    }

                    console.log('[localprint] print config →', JSON.parse(JSON.stringify(cfg)));
                    return cfg;
                },

                // Assemble the audit-log payload from the current print state.
                // Records only what is already being sent to PrintTrays (plus file
                // / design context) — it never influences the print itself.
                buildLogPayload(cfg) {
                    const tray = this.selectedTray || {};
                    return {
                        source: 'pdf_check',
                        source_url: '/print/pdf/check',
                        print_type: this.file?.type || 'pdf',
                        file: {
                            url: this.file?.url || null,
                            name: this.file?.name || null,
                            type: this.file?.type || null,
                            size: this.file?.bytes || this.file?.size || null,
                            pages: this.pages || null,
                            dimensions: this.selectedSizeLabel ? this.selectedSizeLabel() : null,
                        },
                        printer_settings: {
                            printer_name: this.selectedPrinter || null,
                            printer_model: this.selectedPrinterInfo?.driver || null,
                            tray_key: tray.key || null,
                            tray_label: tray.label || null,
                            paper_source: cfg.inputBin || null,
                            paper_size: this.printPaperSize || null,
                            media_size: this.selectedSizeLabel ? this.selectedSizeLabel() : null,
                            orientation: this.printLandscape ? 'landscape' : 'portrait',
                            color_mode: this.printColor ? 'color' : 'grayscale',
                            duplex: cfg.duplex || 'simplex',
                            page_order: cfg.pageOrder || 'frontToBack',
                            scale_mode: cfg.scaleMode || null,
                            scale_percentage: cfg.scaleFactor || null,
                            copies: this.copies || 1,
                            fast_raster: !!cfg.fastRaster,
                            image_engine: cfg.imageEngine || null,
                            paper_safety_shrink: !!cfg.paperSafetyShrink,
                            printer_default_orientation: (this.psCurrentDefaults && this.psCurrentDefaults.orientation) ||
                                null,
                        },
                        request_payload: cfg,
                        design_settings: {
                            size_label: this.selectedSizeLabel ? this.selectedSizeLabel() : null,
                        },
                    };
                },

                async doPrint() {
                    if (!this.canPrint || this.printing) return;
                    this.printing = true;
                    this.printMsg = 'Sending to printer…';
                    this.printMsgKind = 'info';

                    let job = null;
                    const _native = this.sendsImageNatively();
                    const cfg = this.buildPrintConfig(_native ? 'image' : 'pdf');
                    try {
                        const pp = __ptInstance;
                        if (!pp) throw new Error('PrintTrays not connected');

                        // The bridge only accepts type 'pdf' ("Unsupported print type: image").
                        let b64;
                        let _usedSaved = false;
                        if (_native) {
                            // Same as the new demo: the WHOLE image is sent (never
                            // cropped), edge-extended to the paper's aspect, type:'image'.
                            b64 = await this.imageToNativeB64(this.file.url);
                        } else if (this.savedPdfB64) {
                            // "Apply" already regenerated the PDF at the selected paper
                            // size and kept it in memory — send that exact file as-is.
                            b64 = this.savedPdfB64;
                            _usedSaved = true;
                        } else {
                            // No Apply yet — build the print PDF on the fly.
                            b64 = await this.buildPrintPdfBase64();
                        }

                        // Print-time log: ALWAYS fires on every Print (whether the PDF was
                        // pre-built by Apply or built just now), showing the FINAL page size
                        // actually sent to the printer. Parses the first page via pdf-lib.
                        try {
                            if (_native) {
                                const _d = this.imagePageDims();
                                this.printMsg = 'Printing image ' + this.fmt(_d.w) + ' × ' + this.fmt(_d.h) + ' in' +
                                    ' · ' + (cfg.copies || 1) + ' ' + ((cfg.copies || 1) == 1 ? 'copy' : 'copies');
                                this.printMsgKind = 'info';
                            } else if (window.PDFLib) {
                                const _bin = atob(String(b64).replace(/^data:[^;]+;base64,/, ''));
                                const _u8 = new Uint8Array(_bin.length);
                                for (let _i = 0; _i < _bin.length; _i++) _u8[_i] = _bin.charCodeAt(_i);
                                const _doc = await window.PDFLib.PDFDocument.load(_u8);
                                const _p0 = _doc.getPage(0);
                                const _sz = _p0.getSize();
                                const _w = +(_sz.width / 72).toFixed(2);
                                const _h = +(_sz.height / 72).toFixed(2);
                                console.log('[localprint] PRINT → sending PDF', {
                                    source: _usedSaved ? 'applied (saved)' : 'built on print',
                                    pdfPage_in: {
                                        w: _w,
                                        h: _h
                                    },
                                    paperCode: this.printPaperSize,
                                    scaleMode: cfg.scaleMode,
                                    paperSize: cfg.paperSize || '(driver default)',
                                    copies: cfg.copies,
                                    printer: this.selectedPrinter,
                                });
                                // Show it ON-SCREEN too (visible toast), so the operator can
                                // confirm the PDF size + paper the job is going out at.
                                this.printMsg = 'Printing PDF ' + _w + ' × ' + _h + ' in' +
                                    ' · paper: ' + (this.selectedSizeLabel ? this.selectedSizeLabel() : this
                                        .printPaperSize) +
                                    ' · ' + (cfg.copies || 1) + ' ' + ((cfg.copies || 1) == 1 ? 'copy' : 'copies');
                                this.printMsgKind = 'info';
                            }
                        } catch (_e) {
                            console.info('[localprint] print-time dim log skipped:', _e && _e.message);
                        }

                        // Create the audit log BEFORE sending to PrintTrays (non-fatal).
                        job = await LPLog.start(this.buildLogPayload(cfg));
                        // Persist the exact print-ready PDF with the log row (non-fatal,
                        // multipart upload so it never trips the 413 body limit).
                        LPLog.attachPdf(job, b64);

                        // The bridge decodes PDF jobs with Buffer.from(req.data,
                        // 'base64') and does NOT strip a data: URL prefix, so send the
                        // RAW base64 (no "data:…," prefix, no `flavor` key). type:'pdf'
                        // routes it to printPdfBase64 on the bridge.
                        this.ptLog('Sending "' + this.file.name + '" to "' + this.selectedPrinter + '" as ' + cfg.type +
                            ' (' + this.selectedSizeLabel() + (cfg.duplex ? ', duplex ' + cfg.duplex : '') +
                            (cfg.color ? '' : ', grayscale') + ', ' + cfg.copies + ' cop' + (cfg.copies === 1 ?
                                'y' : 'ies') +
                            (cfg.pageOrder === 'backToFront' ? ', back to front' : '') +
                            (cfg.fastRaster ? ', fast-raster mode' : '') + (cfg.imageEngine ? ', classic engine' :
                                '') +
                            (cfg.useDriverDefaultSize ? ', driver custom size' : '') +
                            (cfg.paperSafetyShrink ? ', safety shrink' : '') + ')...');
                        const _t0 = performance.now();
                        const resp = await pp.print(this.selectedPrinter, b64, cfg);
                        this.ptLog('Print job sent successfully (spooler accepted it after ' +
                            ((performance.now() - _t0) / 1000).toFixed(1) + 's).');
                        await LPLog.finish(job, {
                            ok: true,
                            response: resp
                        });
                        this.printMsg = 'Sent to ' + this.selectedPrinter + '!';
                        this.printMsgKind = 'success';
                    } catch (e) {
                        console.error('[localprint] PrintTrays print failed:', e);
                        this.ptLog('Print failed: ' + ((e && e.message) || e));
                        await LPLog.finish(job, {
                            ok: false,
                            error: (e && e.message) || 'Print failed.'
                        });
                        this.printMsg = (e && e.message) || 'Print failed.';
                        this.printMsgKind = 'error';
                    } finally {
                        this.printing = false;
                    }
                },

                imageToBase64Png(url) {
                    return new Promise((resolve, reject) => {
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = () => {
                            const c = document.createElement('canvas');
                            c.width = img.naturalWidth;
                            c.height = img.naturalHeight;
                            c.getContext('2d').drawImage(img, 0, 0);
                            resolve(c.toDataURL('image/png').split(',')[1]);
                        };
                        img.onerror = () => reject(new Error('Failed to load image for conversion'));
                        img.src = url;
                    });
                },

                // Wrap a raster image into a minimal single-page PDF (JPEG/DCTDecode),
                // so it can be sent to the bridge as type 'pdf'. Handles webp/tiff/etc.
                // too, since the canvas re-encodes whatever the browser can decode.
                // Paper dims (inches, portrait) the image is composed onto — same
                // resolution order as imageToPdfBase64.
                imagePageDims() {
                    const live = this.dynamicDims && this.dynamicDims[this.printPaperSize];
                    const stat = this.trayDims && this.trayDims[this.printPaperSize];
                    let dim = (live && live.w > 0 ? live : (stat && stat.w > 0 ? stat : (this.psDim() || {
                        w: 8.5,
                        h: 11
                    })));
                    if (this.printPaperSize === this.psCustomSizeValue) {
                        const _cd = this.psDim();
                        if (_cd && _cd.w > 0 && _cd.h > 0) dim = _cd;
                    }
                    return {
                        w: Math.min(dim.w, dim.h),
                        h: Math.max(dim.w, dim.h)
                    };
                },

                // Native type:'image' job — mirrors the new PrintTrays demo. The image
                // is NEVER cropped: it is kept at its own resolution, turned portrait
                // (the page is always fed portrait, and we send landscape:false), then
                // padded to exactly the paper's aspect by stretching its outermost
                // row/column of pixels into the gap (a soft edge-extend, no white bars,
                // no content removed). The bridge then fits it onto the page.
                // PNG sources stay PNG; everything else goes out as JPEG 0.95.
                imageToNativeB64(url) {
                    return new Promise((resolve, reject) => {
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = () => {
                            try {
                                const iw = img.naturalWidth,
                                    ih = img.naturalHeight;
                                const needSwap = iw > ih;
                                const sw = needSwap ? ih : iw;
                                const sh = needSwap ? iw : ih;
                                const src = document.createElement('canvas');
                                src.width = sw;
                                src.height = sh;
                                const sctx = src.getContext('2d');
                                if (needSwap) {
                                    sctx.translate(sw / 2, sh / 2);
                                    sctx.rotate(-Math.PI / 2); // -90°, same as imageToPdfBase64
                                    sctx.drawImage(img, -iw / 2, -ih / 2);
                                } else {
                                    sctx.drawImage(img, 0, 0);
                                }

                                const page = this.imagePageDims();
                                const target = page.w / page.h;
                                const ratio = sw / sh;
                                let W = sw,
                                    H = sh;
                                if (Math.abs(ratio - target) >= 0.001) {
                                    if (ratio > target) H = Math.round(sw / target);
                                    else W = Math.round(sh * target);
                                }
                                const dx = Math.round((W - sw) / 2);
                                const dy = Math.round((H - sh) / 2);
                                const c = document.createElement('canvas');
                                c.width = W;
                                c.height = H;
                                const ctx = c.getContext('2d');
                                if (dy > 0) {
                                    ctx.drawImage(src, 0, 0, sw, 1, 0, 0, W, dy);
                                    ctx.drawImage(src, 0, sh - 1, sw, 1, 0, dy + sh, W, H - dy - sh);
                                }
                                if (dx > 0) {
                                    ctx.drawImage(src, 0, 0, 1, sh, 0, dy, dx, sh);
                                    ctx.drawImage(src, sw - 1, 0, 1, sh, dx + sw, dy, W - dx - sw, sh);
                                }
                                ctx.drawImage(src, dx, dy);

                                const isPng = /\.png($|\?)/i.test(this.file.name || url);
                                console.log('[localprint] native image →', {
                                    source_px: {
                                        w: iw,
                                        h: ih
                                    },
                                    rotated: needSwap,
                                    padded_px: {
                                        w: W,
                                        h: H
                                    },
                                    page_in: page,
                                    format: isPng ? 'png' : 'jpeg',
                                });
                                const out = isPng ? c.toDataURL('image/png') : c.toDataURL('image/jpeg', 0.95);
                                resolve(out.split(',')[1]);
                            } catch (e) {
                                reject(e);
                            }
                        };
                        img.onerror = () => reject(new Error('Failed to load image for printing'));
                        img.src = url;
                    });
                },

                // asJpeg = true returns the composed page as raw JPEG base64 (for a
                // type:'image' job on the new bridge) instead of wrapping it in a PDF.
                imageToPdfBase64(url, asJpeg) {
                    return new Promise((resolve, reject) => {
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = () => {
                            try {
                                // Size the canvas to the ACTUAL selected paper size (in
                                // inches, from trayDims), not the image's raw pixel size —
                                // then crop-to-cover so the image fills it with zero gaps,
                                // regardless of the source image's own aspect ratio.
                                // Prefer live driver dims, then the static table; E2E paper
                                // is reduced to its trim size below.
                                const live = this.dynamicDims && this.dynamicDims[this.printPaperSize];
                                const stat = this.trayDims && this.trayDims[this.printPaperSize];
                                let dim = (live && live.w > 0 ? live :
                                    (stat && stat.w > 0 ? stat :
                                        (this.psDim() || {
                                            w: 8.5,
                                            h: 11
                                        })));
                                // Driver CUSTOM page size: use the reported dims as-is
                                // (the 0.57 / 0.49 in trim is applied below, as for E2E).
                                if (this.printPaperSize === this.psCustomSizeValue) {
                                    const _cd = this.psDim();
                                    if (_cd && _cd.w > 0 && _cd.h > 0) {
                                        const _mn = Math.min(_cd.w, _cd.h),
                                            _mx = Math.max(_cd.w, _cd.h);
                                        dim = {
                                            w: _mn,
                                            h: _mx
                                        };
                                    }
                                }
                                // E2E paper → build at the trim size (bleed − 0.57 / 0.49 in),
                                // cover-filled from the centre, same as the PDF path.
                                const _e2e = this.trimsBleed();
                                if (_e2e) dim = this.e2eTrimDims(dim);
                                console.log('[localprint] image→pdf size →', {
                                    code: this.printPaperSize,
                                    label: this.selectedSizeLabel(),
                                    live,
                                    stat,
                                    dim,
                                    landscape: this.printLandscape,
                                });
                                const dpi = 300;
                                // Output is ALWAYS portrait — the landscape toggle only
                                // affects the on-screen preview, never the printed/saved
                                // PDF (the Noritsu media is fed portrait, so the real
                                // page must stay portrait).
                                const outLandscape = false;
                                const rawMax = Math.max(dim.w, dim.h);
                                const rawMin = Math.min(dim.w, dim.h);
                                const pageW = outLandscape ? rawMax : rawMin;
                                const pageH = outLandscape ? rawMin : rawMax;
                                const outW = Math.max(1, Math.round(pageW * dpi));
                                const outH = Math.max(1, Math.round(pageH * dpi));
                                const c = document.createElement('canvas');
                                c.width = outW;
                                c.height = outH;
                                const ctx = c.getContext('2d');
                                ctx.fillStyle = '#ffffff';
                                ctx.fillRect(0, 0, outW, outH);
                                // Rotate the image 90° whenever the REQUESTED
                                // orientation differs from the image's OWN orientation
                                // — so a portrait image printed landscape has its
                                // design turned with the page (not just the paper).
                                const iw = img.naturalWidth,
                                    ih = img.naturalHeight;
                                const imgLandscape = iw > ih;
                                const needSwap = outLandscape !== imgLandscape;
                                // Effective image box after any 90° rotation.
                                const effW = needSwap ? ih : iw;
                                const effH = needSwap ? iw : ih;
                                // The Scale dropdown drives how the image is placed on
                                // the page (baked in here, since images become a PDF we
                                // build ourselves — SumatraPDF's own scaling can't act
                                // on it):
                                //   'fit'      → crop-to-COVER: fill the whole sheet,
                                //                zero white space (excess is cropped).
                                //   'fit_area' → fit-to-CONTENT (contain): the whole
                                //                image fits inside, centred, with white
                                //                bars on the shorter axis (no cropping).
                                //   'actual'   → true size: native pixels at 300 dpi.
                                //   'custom'   → 'actual' scaled by printScaleFactor %.
                                const _m = _e2e ? 'fit' : (this.printScaleMode || 'fit');
                                let scale;
                                if (_m === 'actual') {
                                    scale = 1;
                                } else if (_m === 'custom') {
                                    scale = Math.max(0.01, (this.printScaleFactor || 100) / 100);
                                } else if (_m === 'fit_area') {
                                    scale = Math.min(outW / effW, outH / effH); // contain
                                } else {
                                    scale = Math.max(outW / effW, outH / effH); // cover
                                }
                                ctx.save();
                                ctx.translate(outW / 2, outH / 2);
                                if (needSwap) ctx.rotate(-Math.PI / 2); // -90° (clockwise)
                                const drawW = iw * scale;
                                const drawH = ih * scale;
                                ctx.drawImage(img, -drawW / 2, -drawH / 2, drawW, drawH);
                                ctx.restore();
                                const jpegB64 = c.toDataURL('image/jpeg', 0.92).split(',')[1];
                                if (asJpeg) {
                                    resolve(jpegB64);
                                    return;
                                }
                                const jpeg = this.base64ToBytes(jpegB64);
                                resolve(this.buildImagePdf(jpeg, outW, outH, pageW * 72, pageH * 72));
                            } catch (e) {
                                reject(e);
                            }
                        };
                        img.onerror = () => reject(new Error('Failed to load image for conversion'));
                        img.src = url;
                    });
                },
                base64ToBytes(b64) {
                    const bin = atob(b64);
                    const bytes = new Uint8Array(bin.length);
                    for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
                    return bytes;
                },
                // Assemble a minimal PDF embedding a JPEG image, sized to the target
                // PAPER size (pageWPt/pageHPt, in points) rather than the image's own
                // pixel dimensions — pixelW/pixelH describe the embedded JPEG itself,
                // pageWPt/pageHPt describe the page it's placed on. Then return it
                // base64-encoded.
                buildImagePdf(jpeg, pixelW, pixelH, pageWPt, pageHPt) {
                    const enc = new TextEncoder();
                    const parts = [];
                    const offsets = [];
                    let len = 0;
                    const pushStr = (s) => {
                        const b = enc.encode(s);
                        parts.push(b);
                        len += b.length;
                    };
                    const pushBytes = (b) => {
                        parts.push(b);
                        len += b.length;
                    };
                    const startObj = () => {
                        offsets.push(len);
                    };
                    pushStr('%PDF-1.4\n');
                    startObj(); // 1
                    pushStr('1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n');
                    startObj(); // 2
                    pushStr('2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n');
                    startObj(); // 3
                    pushStr('3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' + pageWPt + ' ' + pageHPt +
                        '] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>\nendobj\n');
                    startObj(); // 4 - image
                    pushStr('4 0 obj\n<< /Type /XObject /Subtype /Image /Width ' + pixelW + ' /Height ' + pixelH +
                        ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' +
                        jpeg.length + ' >>\nstream\n');
                    pushBytes(jpeg);
                    pushStr('\nendstream\nendobj\n');
                    startObj(); // 5 - content
                    const content = 'q\n' + pageWPt + ' 0 0 ' + pageHPt + ' 0 0 cm\n/Im0 Do\nQ\n';
                    const contentBytes = enc.encode(content);
                    pushStr('5 0 obj\n<< /Length ' + contentBytes.length + ' >>\nstream\n');
                    pushBytes(contentBytes);
                    pushStr('\nendstream\nendobj\n');
                    const xrefStart = len;
                    let xref = 'xref\n0 6\n0000000000 65535 f \n';
                    for (let i = 0; i < offsets.length; i++) {
                        xref += String(offsets[i]).padStart(10, '0') + ' 00000 n \n';
                    }
                    pushStr(xref);
                    pushStr('trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n' + xrefStart + '\n%%EOF');
                    const out = new Uint8Array(len);
                    let pos = 0;
                    for (const p of parts) {
                        out.set(p, pos);
                        pos += p.length;
                    }
                    let binary = '';
                    for (let i = 0; i < out.length; i++) binary += String.fromCharCode(out[i]);
                    return btoa(binary);
                },

                // Derive the TRIM size (in inches) from a tray/driver size code
                // — strips a "-E2E" / " E2E" suffix and parses "WxH" (e.g.
                // '7x10-E2E' → [7, 10], '4x6' → [4, 6]). Named sizes (Letter,
                // Legal) handled explicitly. Returns null on an unknown code
                // so the caller can fall back to live/static dims.
                trimSizeFromCode(code) {
                    const raw = String(code || '').trim();
                    const num = '(\\d+(?:\\.\\d+)?)';
                    const pair = num + '\\s*[x×]\\s*' + num;
                    // A size stated in MILLIMETRES, e.g.
                    // "16K 184 x 260mm (7.25 × 10.24 in)" — the mm value comes first,
                    // its inch equivalent is in the trailing "(… in)" part. Use the
                    // given inch value when present, else convert the mm to inches.
                    const mm = raw.match(new RegExp(pair + '\\s*mm\\b', 'i'));
                    const paren = raw.match(new RegExp('\\(\\s*' + pair + '\\s*in\\s*\\)', 'i'));
                    if (mm) {
                        if (paren) return [parseFloat(paren[1]), parseFloat(paren[2])];
                        return [parseFloat(mm[1]) / 25.4, parseFloat(mm[2]) / 25.4];
                    }
                    // A LEADING inch size, e.g. "5 x 7in E2E (5.57 × 7.49 in)" — this
                    // is the TRIM (5 × 7); the parenthesised value is the bleed, which
                    // we deliberately ignore so E2E still saves at the trim size.
                    const inLead = raw.match(new RegExp(pair + '\\s*in\\b', 'i'));
                    if (inLead) return [parseFloat(inLead[1]), parseFloat(inLead[2])];
                    // Any parenthesised inch value (e.g. a size given only in inches
                    // inside brackets).
                    if (paren) return [parseFloat(paren[1]), parseFloat(paren[2])];
                    // Bare "W x H" with no unit — assume inches.
                    const bare = raw.match(new RegExp(pair, 'i'));
                    if (bare) return [parseFloat(bare[1]), parseFloat(bare[2])];
                    const named = {
                        'letter': [8.5, 11],
                        'legal': [8.5, 14]
                    };
                    const key = raw.replace(/[-\s]*e2e.*$/i, '').trim().toLowerCase();
                    if (named[key]) return named[key];
                    return null;
                },

                // Human label of the currently selected paper size (e.g.
                // "5 x 7in E2E (5.57 × 7.49 in)"). The driver's size VALUE/code is
                // often an opaque id, so the trim/E2E info lives only in the label.
                selectedSizeLabel() {
                    const live = (this.dynamicSizes || []).find(s => s.value === this.printPaperSize);
                    if (live && live.label) return String(live.label);
                    const lbl = this.psSizeOptionsMap && this.psSizeOptionsMap[this.printPaperSize];
                    return String(lbl || this.printPaperSize || '');
                },

                // Is the currently selected paper an edge-to-edge / borderless media?
                // Checks the size code AND its human label (either may carry "E2E").
                isE2ESelected() {
                    return /e2e/i.test(String(this.printPaperSize || '')) ||
                        /e2e/i.test(this.selectedSizeLabel());
                },

                // Papers whose PDF is built at the trim size (bleed − 0.57 / 0.49 in):
                // any E2E size, and the driver's PostScript Custom Page Size.
                trimsBleed() {
                    return this.printPaperSize === this.psCustomSizeValue || this.isE2ESelected();
                },

                // E2E bleed paper dims → portrait TRIM dims the PDF is built at:
                // width − 0.57 in, height − 0.49 in (7.57 × 10.49 → 7 × 10).
                e2eTrimDims(dim) {
                    const mn = Math.min(dim.w, dim.h),
                        mx = Math.max(dim.w, dim.h);
                    return {
                        w: +Math.max(0.1, mn - 0.57).toFixed(2),
                        h: +Math.max(0.1, mx - 0.49).toFixed(2)
                    };
                },

                arrayBufToBase64(buf) {
                    const bytes = new Uint8Array(buf);
                    let binary = '';
                    for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
                    return btoa(binary);
                },

                fmt(n) {
                    return (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, '');
                },
                prettySize(bytes) {
                    if (!bytes) return '';
                    const kb = bytes / 1024;
                    return kb < 1024 ? `${kb.toFixed(0)} KB` : `${(kb / 1024).toFixed(1)} MB`;
                },
            };
        }
    </script>
@endpush
