@extends('layouts.quick-flow-pc')
@section('title', 'Preview & Print | Qrinto')

@section('content')
    @php
        // Images in PAGE order (frame → sample → background → overlay = page 1 → 4:
        // front, inside left, inside right, back), not DB order.
        $slotOrder = ['frame_image', 'sample_image', 'background_image', 'overlay_image'];
        $uploadList = collect($uploadIds ?? [])
            ->sortBy(fn($uid, $slot) => ($i = array_search($slot, $slotOrder, true)) === false ? 99 : $i)
            ->map(fn($uid) => $uploads->get($uid))
            ->filter()
            ->values();
        if ($uploadList->isEmpty()) {
            $uploadList = $uploads->values();
        }
        $imageUrls = $uploadList->map(fn($u) => asset('storage/' . ltrim($u->file_path ?? $u->path, '/')))->all();
        $totalPages = count($imageUrls);
        $cardKind = $totalPages >= 4 ? 'fold' : ($totalPages === 2 ? 'duplex' : 'single');
        $kindLabel = ['single' => 'Single-sided', 'duplex' => 'Double-sided card', 'fold' => 'Folded card'][$cardKind];
        $cardShape = ($flowData['orientation'] ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait';
    @endphp
    <div class="ck" x-data="previewFlow()" x-init="init()">

        {{-- Off-screen stash for the card-preview source pages. --}}
        <div class="ck-offscreen" aria-hidden="true">
            <div x-ref="cpStash"></div>
        </div>

        <main class="ck-main">
            {{-- header --}}
            <div class="ck-head">
                <div class="ck-head-l">
                    <a href="{{ route('localprint.sizes') }}" class="ck-iconbtn" aria-label="Back to sizes">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 6l-6 6 6 6" />
                        </svg>
                    </a>
                    <div class="ck-min0">
                        <h1>{{ $product->name ?? 'Your design' }}</h1>
                        <p class="ck-sub">
                            {{ $kindLabel }} · <span x-text="cpPortrait ? 'Portrait' : 'Landscape'"></span>
                            · <span x-text="(pages || '–') + (pages === 1 ? ' page' : ' pages')"></span>
                            @if (!empty($flowData['size_name']))
                                · {{ $flowData['size_name'] }}
                            @endif
                        </p>
                    </div>
                </div>
                @if (!empty($pdfUrl))
                    <a href="{{ $pdfUrl }}" download class="ck-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 4v12M7 11l5 5 5-5M5 20h14" />
                        </svg>Download PDF</a>
                @endif
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
    {{-- pdf-lib: scale/cover-fill the server PDF onto the selected media size so
         it prints edge-to-edge (the raw design PDF is the DESIGN size, e.g. 5×7,
         which SumatraPDF would otherwise centre small on a larger sheet). --}}
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
    <script
        src="{{ asset('js/new-test-print-settings.js') }}?v={{ filemtime(public_path('js/new-test-print-settings.js')) }}">
    </script>
    <script src="{{ asset('js/new-print-relay.js') }}?v={{ filemtime(public_path('js/new-print-relay.js')) }}"></script>
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
        var __ptInstance = null;

        function previewFlow() {
            const pdfUrl = @json($pdfUrl ?? null);
            const trayDims = @json($trayDims);
            const imageUrls = @json($imageUrls ?? []);
            const totalPages = {{ $totalPages }};
            const designSize = {
                w: {{ floatval($flowData['size_width'] ?? 0) }},
                h: {{ floatval($flowData['size_height'] ?? 0) }}
            };
            const designOrientation = '{{ $flowData['orientation'] ?? 'portrait' }}';

            return {
                // Animated card preview (public/js/card-preview.js). Preview only:
                // the print file is the server-built design PDF.
                ...window.cardPreviewMixin({
                    kind: @json($cardKind),
                    shape: @json($cardShape),
                    limit: Math.max(1, totalPages),
                    editable: false
                }),

                // Shared Print Settings modal (state + capability loading).
                ...window.printSettingsMixin({
                    sizeOptions: @json($sizeOptions),
                    sizeDims: trayDims,
                    allowCustomSize: true
                }),

                slide: 0, // page shown in the book preview (synced via book-preview-page)
                copies: 1,

                // The PDF that Print will actually send. savedPdfB64 holds the
                // regenerated print-ready PDF IN MEMORY after "Apply" (base64) — no
                // server round trip, so a large cover-filled PDF never trips HTTP 413.
                // null = not applied yet, so Print builds it on the fly. savedPdfUrl is
                // kept only for the audit-log file reference (the original server PDF).
                savedPdfB64: null,
                savedPdfUrl: pdfUrl,

                // Per-check acknowledgements ({ [check.key]: true }). An error must be
                // acknowledged before the operator can print; a warning (E2E) shows a
                // checkbox too but never blocks printing.
                acks: {},

                bridgeChecking: true,
                bridgeReady: false,
                bridgeVersion: null,
                printers: [],
                selectedPrinter: null,
                printing: false,
                printMsg: null,
                printMsgKind: 'info',

                showBridgeModal: false,

                // The new modal shows its image-only options when file.type is
                // 'image'; this page always prints the generated design PDF.
                file: {
                    type: 'pdf'
                },
                pages: totalPages,
                pdfError: pdfUrl ? null : 'Something went wrong generating the PDF.',

                // Bridge activity log (newest first, console/debug only) — same as
                // /print/pdf/check.
                ptLogLines: [],

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

                init() {
                    let pages = parseInt({{ $totalPages }});
                    // Pre-tick the size / orientation / E2E acknowledgements so those
                    // check rows come up CHECKED (and don't block printing) even after
                    // the operator changes the paper size — same as the /print/pdf/check
                    // page. They can still un-tick one explicitly if they want to.
                    this.acks = Object.assign({ size: true, orient: true, e2e: true }, this.acks);
                    // The print PDF is ALWAYS re-laid in portrait (a landscape design is
                    // rotated -90° to fit), so the Orientation toggle only drives the
                    // on-screen preview — it never blocks or changes the printed output.
                    // Therefore DON'T lock orientation (no red/blocked Portrait button);
                    // just default the toggle to the design's own orientation for preview.
                    this.orientationLock = null;
                    if (pages == 2) {
                        this.printLandscape = true;
                    } else if (pages == 4) {
                        this.printLandscape = false;
                    } else if (designSize.w && designSize.h) {
                        this.printLandscape = designSize.w > designSize.h;
                    }
                    // Auto-select the Paper Size that matches the DESIGN's actual size
                    // (e.g. a 5 × 7 design → the 5 × 7 paper option) from the static
                    // list now; re-applied after live sizes load (see initBridge).
                    if (designSize.w && designSize.h) {
                        this.psSelectSizeForDims(designSize.w, designSize.h);
                    }
                    // Live preview inside the Print Settings modal: feed the design
                    // size + the current slide's rendered page image, and keep the
                    // artwork in sync as the operator swipes between pages.
                    this.psHasPreview = true;
                    if (designSize.w && designSize.h) this.psDesign = { w: designSize.w, h: designSize.h };
                    this.psPreviewSrc = imageUrls[this.slide] || imageUrls[0] || '';
                    this.psPreviewCount = imageUrls.length || totalPages || 1;
                    this.psPreviewIndex = this.slide;
                    this.$watch('slide', (i) => {
                        this.psPreviewSrc = imageUrls[i] || imageUrls[0] || '';
                        this.psPreviewIndex = i;
                    });
                    // Invalidate the pre-built ("Applied") print PDF whenever a setting
                    // that affects its size/orientation/scaling changes, so Print always
                    // rebuilds at the CURRENT paper instead of sending a stale PDF.
                    ['printPaperSize', 'printLandscape', 'printScaleMode', 'printScaleFactor',
                        'selectedPrinter', 'printCustomW', 'printCustomH'
                    ].forEach((k) => {
                        this.$watch(k, () => { this.savedPdfB64 = null; });
                    });
                    this.initBridge();
                    if (imageUrls.length) {
                        this.cpLoadImages(imageUrls).catch(e => console.warn('[localprint] card preview failed:', e));
                    }
                },

                // Paper shown under the selected printer (live driver settings).
                trayPaper(name) {
                    return name === this.selectedPrinter ? this.psSummary() : '';
                },

                // Modal preview page navigation (overrides the mixin no-op): the
                // slide watcher keeps psPreviewSrc / psPreviewIndex in sync.
                psPreviewSync(i) {
                    this.slide = Math.min((totalPages || 1) - 1, Math.max(0, i));
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
                        // Compare the design against the paper's TRIM size — not the
                        // larger BLEED dims — so a 7×10 design on a "7x10-E2E"
                        // (7.57×10.49 bleed) paper still reads as a match. Live driver
                        // media values are opaque names, so parse the TRIM from the
                        // size code first, then from the option's LABEL (e.g.
                        // "7 x 10in E2E (7.57 × 10.49 in)" → 7×10), then fall back to
                        // the bleed dims only if neither yields a WxH.
                        let selLabel = this.printPaperSize;
                        const _live = (this.dynamicSizes || []).find(s => s.value === this.printPaperSize);
                        if (_live && _live.label) selLabel = _live.label;
                        else if (this.psSizeOptionsMap && this.psSizeOptionsMap[this.printPaperSize])
                            selLabel = this.psSizeOptionsMap[this.printPaperSize];
                        // Lenient: grab the FIRST "W x H" / "W × H" from the label
                        // (e.g. "7 x 10in E2E (7.57 × 10.49 in)" → [7, 10]).
                        const _leadWH = (str) => {
                            const m = String(str || '').match(/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/i);
                            return m ? [parseFloat(m[1]), parseFloat(m[2])] : null;
                        };
                        const trim = this.trimSizeFromCode(this.printPaperSize) || _leadWH(selLabel);
                        const pdim = this.dynamicDims[this.printPaperSize] || trayDims[this.printPaperSize];
                        const paper = trim ? {
                            w: trim[0],
                            h: trim[1]
                        } : pdim;

                        if (paper) {
                            const dw = Math.max(designSize.w, designSize.h);
                            const dh = Math.min(designSize.w, designSize.h);
                            const tw = Math.max(paper.w, paper.h);
                            const th = Math.min(paper.w, paper.h);
                            const match = Math.abs(dw - tw) < 0.15 && Math.abs(dh - th) < 0.15;

                            // Order the displayed dims by the intended orientation:
                            //   portrait  → width small, height large ("5 × 7")
                            //   landscape → width large, height small ("7 × 5")
                            const _pages = parseInt({{ $totalPages }});
                            let _land;
                            if (_pages === 2) _land = true;
                            else if (_pages === 4) _land = false;
                            else _land = String(designOrientation).toLowerCase() === 'landscape';
                            const dW = _land ? Math.max(designSize.w, designSize.h) : Math.min(designSize.w, designSize.h);
                            const dH = _land ? Math.min(designSize.w, designSize.h) : Math.max(designSize.w, designSize.h);
                            const pW = _land ? tw : th;
                            const pH = _land ? th : tw;

                            list.push({
                                key: 'size',
                                ok: match,
                                severity: match ? 'ok' : 'error',
                                title: match ? 'Size matches paper' : 'Paper size doesn\'t match your design',
                                detail: 'Design: ' + dW + ' × ' + dH + ' in · Paper: ' +
                                    pW + ' × ' + pH + ' in.' + (match ? '' :
                                        ' Your design will be scaled to fit, leaving white areas or cutting off edges.'
                                    ),
                                ackLabel: match ? null : 'I understand. Print anyway.',
                            });

                            // Orientation check REMOVED: the final print is ALWAYS portrait
                            // (a landscape design is rotated -90° into a portrait page), so
                            // an "Orientation mismatch" error is meaningless here.
                        }
                    }

                    // Borderless / edge-to-edge — warn when the selected paper is not
                    // an E2E form (the print will have a white border).
                    if (this.printPaperSize) {
                        const sel = this.printPaperSize;
                        let label = sel;
                        const live = (this.dynamicSizes || []).find(s => s.value === sel);
                        if (live && live.label) label = live.label;
                        else if (this.psSizeOptionsMap && this.psSizeOptionsMap[sel]) label = this.psSizeOptionsMap[
                            sel];
                        const isE2E = /e2e/i.test(sel) || /e2e/i.test(label);
                        list.push({
                            key: 'e2e',
                            ok: isE2E,
                            severity: isE2E ? 'ok' : 'warn',
                            title: isE2E ? 'Borderless (edge to edge)' : 'Not edge to edge (E2E)',
                            detail: isE2E ? 'This paper prints to the edge of the paper.' :
                                'Your print won\'t be borderless — there will be a white border on every side. ' +
                                'Choose an E2E paper for a full-bleed print.',
                            ackLabel: isE2E ? null : 'Print with a white border',
                        });
                    }

                    return list;
                },

                // ── PrintTrays bridge ──
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
                            // psLoad resets the size to the printer's first form, so
                            // re-apply the design-size match once live sizes arrive.
                            await this.psLoad(this.selectedPrinter);
                            if (designSize.w && designSize.h) {
                                this.psSelectSizeForDims(designSize.w, designSize.h);
                            }
                        }
                    } catch (e) {
                        console.warn('[localprint] PrintTrays connection failed:', e.message || e);
                        this.ptLog("Couldn't connect: " + ((e && e.message) || e));
                        this.bridgeReady = false;
                    }
                    this.bridgeChecking = false;
                    if (!this.bridgeReady) this.showBridgeModal = true;
                },

                // Keep only Noritsu printers and order them by tray number,
                // regardless of the exact printer-name string. The MP / multi tray
                // (no tray number) comes first, then Tray 1, 2, 3, 4, 5…
                orderPrinters(list) {
                    const trayNum = (name) => {
                        const m = String(name).match(/tray\s*(\d+)/i);
                        return m ? parseInt(m[1], 10) : null;
                    };
                    const rank = (name) => {
                        const n = trayNum(name);
                        return n === null ? -1 : n;
                    };
                    return (list || [])
                        .filter(p => p && typeof p.name === 'string' &&
                            p.name.toLowerCase().includes('noritsu'))
                        .sort((a, b) => {
                            const ra = rank(a.name),
                                rb = rank(b.name);
                            if (ra !== rb) return ra - rb;
                            return a.name.localeCompare(b.name);
                        });
                },

                get selectedPrinterInfo() {
                    return this.printers.find(p => p.name === this.selectedPrinter) || null;
                },



                openSettings() {
                    // Auto-select the paper size on open, preferring an E2E variant
                    // that matches the design size (psSelectSizeForDims ranks E2E first).
                    if (designSize.w && designSize.h) {
                        this.psSelectSizeForDims(designSize.w, designSize.h);
                    }
                    this.psOpen(this.selectedPrinter, this.selectedPrinterInfo?.driver,
                        this.psPrinterStatus(this.selectedPrinterInfo));
                },

                async selectPrinter(p) {
                    if (this.selectedPrinter !== p) {
                        this.selectedPrinter = p;
                        // A different printer can support a different paper set.
                        this.psResetCaps();
                        await this.psLoad(p);
                        // Keep the design's size selected on the new printer too.
                        if (designSize.w && designSize.h) {
                            this.psSelectSizeForDims(designSize.w, designSize.h);
                        }
                        // NOTE: don't auto-open the modal here — it opens only when the
                        // user clicks the "Settings" button (openSettings()).
                    }
                },

                // Apply: regenerate the PDF at the selected paper size (cover-fill)
                // and SAVE it on the server, so Print later fetches the exact file
                // that will print. Shows a spinner on the button while saving.
                async applyPaperSettings() {
                    if (this.psApplying) return;
                    this.psApplying = true;
                    try {
                        // Always start from the ORIGINAL server design PDF so changing
                        // the size again never crops an already-cropped file.
                        const res = await fetch(pdfUrl, { credentials: 'same-origin' });
                        if (!res.ok) throw new Error('Failed to fetch PDF: HTTP ' + res.status);
                        let bytes = new Uint8Array(await res.arrayBuffer());

                        // Re-lay onto a PORTRAIT page at the selected paper size (trim
                        // for E2E), exactly like /new-testing/print/pdf/check.
                        bytes = await this.relayForPrint(bytes);

                        // Keep the regenerated PDF IN MEMORY (base64) — do NOT POST it to
                        // the server (save-regenerated). A large cover-filled PDF makes the
                        // JSON body exceed the web server's body-size limit → HTTP 413. The
                        // file is only needed for THIS print (sent to the local PrintTrays
                        // bridge), so keeping it in memory avoids the round trip and the
                        // 413 entirely — exactly like /print/pdf/check.
                        this.savedPdfB64 = this.arrayBufToBase64(bytes.buffer || bytes);
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

                // Human label of the selected paper size (for status text).
                selectedSizeLabel() {
                    const live = (this.dynamicSizes || []).find(s => s.value === this.printPaperSize);
                    if (live && live.label) return String(live.label);
                    const lbl = this.psSizeOptionsMap && this.psSizeOptionsMap[this.printPaperSize];
                    return String(lbl || this.printPaperSize || '');
                },




                // True when any check is a blocking error (red).
                get hasCheckErrors() {
                    return this.checks.some(c => !c.ok && c.severity === 'error');
                },

                // Every error check has its acknowledgement checkbox ticked.
                get errorsAcknowledged() {
                    return this.checks
                        .filter(c => !c.ok && c.severity === 'error')
                        .every(c => !!this.acks[c.key]);
                },

                // True when there's a warning (e.g. non-E2E border).
                get hasCheckWarnings() {
                    return this.checks.some(c => !c.ok && c.severity === 'warn');
                },

                // Every failing check that offers an acknowledgement checkbox
                // (errors AND warnings, e.g. the non-E2E "Print with a white
                // border" box) must be ticked before printing.
                // The size / E2E warning cards were removed from this page (same as
                // /print/pdf/check), so nothing is left to acknowledge.
                get allAcksTicked() {
                    return true;
                },

                // Fully clean: no errors and no warnings → the print button goes green.
                get checksAllClear() {
                    return !this.hasCheckErrors && !this.hasCheckWarnings;
                },

                get canPrint() {
                    if (this.copies < 1) return false;
                    if (!pdfUrl) return false;
                    // Every failing check with a checkbox — errors AND warnings
                    // (e.g. the non-E2E border) — must be acknowledged first.
                    if (!this.allAcksTicked) return false;
                    return this.bridgeReady && !!this.selectedPrinter;
                },

                get blocker() {
                    if (!pdfUrl) return 'No PDF generated';
                    if (this.bridgeChecking) return 'Connecting to PrintTrays…';
                    if (!this.bridgeReady) return 'PrintTrays not connected';
                    if (!this.selectedPrinter) return 'Select a printer';
                    if (!this.allAcksTicked)
                        return 'Confirm the warnings above to print';
                    return null;
                },

                // Translate the Print Settings modal state into QZ Tray print-config
                // options. The bridge spreads these into qz.configs.create(printer, …),
                // so the key names here must match QZ's config schema exactly.
                buildPrintConfig(type) {
                    // PrintTrays bridge has its OWN high-level option schema (NOT raw
                    // QZ config). The official demo sends exactly these keys:
                    //   { type, paperSize:<driver media NAME>, landscape:<bool>,
                    //     color:<bool>, copies, duplex?, inputBin? }
                    // Sending QZ-style keys (size:{w,h}, orientation, colorType,
                    // printerTray, flavor…) makes the bridge ignore them and fall
                    // back to the printer's DEFAULT media → white space on the sides.
                    // The print PDF is ALWAYS re-laid in PORTRAIT (relayForPrint bakes
                    // a landscape design 90° into a portrait
                    // page, like the /print/pdf/check page). The page is therefore already
                    // correctly oriented, so we always tell the bridge `landscape: false` —
                    // sending the toggle here would rotate it a SECOND time at the printer.
                    const cfg = {
                        type: 'pdf',
                        landscape: false,
                        color: !!this.printColor,
                        copies: this.copies || 1,
                        // Page order for MULTI-PAGE PDFs. Sent exactly like the
                        // official PrintTrays demo — 'frontToBack' (normal) or
                        // 'backToFront' (reversed). The bridge applies it as a reverse
                        // page-feed instruction (SumatraPDF / CUPS outputorder=reverse);
                        // single-page jobs ignore it.
                        pageOrder: this.printPageOrder || 'frontToBack',
                    };

                    // Map the modal's scale choice to the bridge's scaleMode:
                    //   'fit' (fit-to-paper) & 'fit_area' (fit printable) → 'fit'
                    //   'actual' → 'actual', 'custom' → 'custom' (+ scaleFactor).
                    const _m = this.printScaleMode || 'fit';
                    cfg.scaleMode = (_m === 'actual') ? 'actual' : (_m === 'custom' ? 'custom' : 'fit');
                    if (_m === 'custom') cfg.scaleFactor = this.printScaleFactor || 100;

                    // paperSize goes STRAIGHT into SumatraPDF's `paper=<name>` on the
                    // bridge, matched against the driver's own media forms — so it
                    // must be a REAL driver media name, which only the live
                    // capabilities give us. A static fallback name (e.g. "Letter")
                    // doesn't exist on a Noritsu photo printer and makes the whole
                    // print command FAIL. Send it only when it came from the live
                    // printer; otherwise omit it and let SumatraPDF use the printer's
                    // DEFAULT loaded media (the borderless photo paper) → edge-to-edge.
                    //
                    // The driver's own CUSTOM page size (e.g. Noritsu's "PostScript
                    // Custom Page Size") is NOT a SumatraPDF media name: send the trim
                    // PDF with useDriverDefaultSize + an explicit pageSize and NO
                    // paperSize — same as /print/pdf/check and the new demo.
                    if (this.printPaperSize === this.psCustomSizeValue) {
                        cfg.useDriverDefaultSize = true;
                        cfg.scaleMode = 'fit';
                        delete cfg.scaleFactor;
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

                    // Paper source → bridge `inputBin` (goes into SumatraPDF `bin=`).
                    // Only from live capabilities; empty = printer default.
                    if (this.printInputBin) cfg.inputBin = this.printInputBin;

                    // New bridge option (new demo): fast-raster for PDFs.
                    cfg.fastRaster = !!this.printFastRaster;

                    console.log('[localprint] print config →', JSON.parse(JSON.stringify(cfg)));
                    return cfg;
                },

                // Assemble the audit-log payload from the current print state.
                // Records only what is already being sent to PrintTrays (plus file
                // / design context) — it never influences the print itself.
                buildLogPayload(cfg) {
                    const sizeLabel = (this.psSizeOptionsMap && this.psSizeOptionsMap[this.printPaperSize]) || this.printPaperSize || null;
                    return {
                        source: 'own_design_customizer_preview',
                        source_url: '/print/own-design/sizes/customizer/preview',
                        print_type: 'pdf',
                        file: {
                            url: this.savedPdfUrl || pdfUrl || null,
                            name: (this.savedPdfUrl || pdfUrl || '').split('/').pop() || null,
                            type: 'pdf',
                            size: null,
                            pages: totalPages || null,
                            dimensions: (designSize.w && designSize.h) ? (designSize.w + ' x ' + designSize.h + ' in') : null,
                        },
                        printer_settings: {
                            printer_name: this.selectedPrinter || null,
                            printer_model: this.selectedPrinterInfo?.driver || null,
                            paper_source: cfg.inputBin || null,
                            paper_size: this.printPaperSize || null,
                            media_size: sizeLabel,
                            orientation: this.printLandscape ? 'landscape' : 'portrait',
                            color_mode: this.printColor ? 'color' : 'grayscale',
                            duplex: cfg.duplex || 'simplex',
                            page_order: cfg.pageOrder || 'frontToBack',
                            scale_mode: cfg.scaleMode || null,
                            scale_percentage: cfg.scaleFactor || null,
                            copies: this.copies || 1,
                            fast_raster: !!cfg.fastRaster,
                            paper_safety_shrink: !!cfg.paperSafetyShrink,
                            printer_default_orientation: (this.psCurrentDefaults && this.psCurrentDefaults.orientation) ||
                                null,
                        },
                        request_payload: cfg,
                        design_settings: {
                            product_id: @json($product->id ?? null),
                            size_label: {{ \Illuminate\Support\Js::from($flowData['size_name'] ?? null) }},
                            size_width: designSize.w || null,
                            size_height: designSize.h || null,
                            orientation: designOrientation,
                            page_count: totalPages || null,
                        },
                    };
                },

                async doPrint() {
                    if (!this.canPrint || this.printing) return;
                    this.printing = true;
                    this.printMsg = 'Sending to printer…';
                    this.printMsgKind = 'info';

                    let job = null;
                    const cfg = this.buildPrintConfig('pdf');
                    try {
                        const pp = __ptInstance;
                        if (!pp) throw new Error('PrintTrays not connected');

                        // If the operator hit "Apply", savedPdfB64 holds the regenerated
                        // print-ready PDF IN MEMORY (base64) — send it AS-IS. No server
                        // round trip, so a large cover-filled PDF never trips HTTP 413
                        // (the old save-regenerated POST did). Otherwise fetch the original
                        // design PDF and do the rotate + cover-fill here so a print without
                        // Apply still works.
                        const alreadyPrepared = !!this.savedPdfB64;
                        let bytes;
                        if (alreadyPrepared) {
                            const _bin = atob(this.savedPdfB64);
                            bytes = new Uint8Array(_bin.length);
                            for (let _i = 0; _i < _bin.length; _i++) bytes[_i] = _bin.charCodeAt(_i);
                        } else {
                            const res = await fetch(pdfUrl, { credentials: 'same-origin' });
                            if (!res.ok) throw new Error('Failed to fetch PDF: HTTP ' + res.status);
                            bytes = new Uint8Array(await res.arrayBuffer());
                            // The driver doesn't reliably honour the `landscape` flag, so
                            // orientation + size are baked into the PDF itself — same
                            // re-lay as /new-testing/print/pdf/check.
                            bytes = await this.relayForPrint(bytes);
                        }

                        const b64 = this.arrayBufToBase64(bytes.buffer || bytes);

                        // Print-time log + ON-SCREEN message: fires on every Print, showing
                        // the FINAL PDF page size actually sent to the printer.
                        try {
                            if (window.PDFLib) {
                                const _doc = await window.PDFLib.PDFDocument.load(bytes);
                                const _sz = _doc.getPage(0).getSize();
                                const _w = +(_sz.width / 72).toFixed(2);
                                const _h = +(_sz.height / 72).toFixed(2);
                                console.log('[localprint] PRINT → sending PDF', {
                                    source: alreadyPrepared ? 'applied (saved)' : 'built on print',
                                    pdfPage_in: { w: _w, h: _h },
                                    paperCode: this.printPaperSize,
                                    scaleMode: cfg.scaleMode,
                                    paperSize: cfg.paperSize || '(driver default)',
                                    copies: cfg.copies, printer: this.selectedPrinter,
                                });
                                this.printMsg = 'Printing PDF ' + _w + ' × ' + _h + ' in' +
                                    ' · paper: ' + (this.selectedSizeLabel ? this.selectedSizeLabel() : this.printPaperSize) +
                                    ' · ' + (cfg.copies || 1) + ' ' + ((cfg.copies || 1) == 1 ? 'copy' : 'copies');
                                this.printMsgKind = 'info';
                            }
                        } catch (_e) { console.info('[localprint] print-time dim log skipped:', _e && _e.message); }

                        // Create the audit log BEFORE sending to PrintTrays (non-fatal).
                        job = await LPLog.start(this.buildLogPayload(cfg));
                        // Persist the exact print-ready PDF with the log row (non-fatal,
                        // multipart upload so it never trips the 413 body limit).
                        LPLog.attachPdf(job, b64);

                        // The bridge decodes PDF jobs with Buffer.from(req.data,
                        // 'base64') and routes on the top-level `type`. Send RAW
                        // base64 (no data: prefix, no `flavor` key).
                        this.ptLog('Sending design PDF to "' + this.selectedPrinter + '" (' + this.selectedSizeLabel() +
                            (cfg.duplex ? ', duplex ' + cfg.duplex : '') + (cfg.color ? '' : ', grayscale') + ', ' +
                            cfg.copies + ' cop' + (cfg.copies === 1 ? 'y' : 'ies') +
                            (cfg.fastRaster ? ', fast-raster mode' : '') +
                            (cfg.useDriverDefaultSize ? ', driver custom size' : '') +
                            (cfg.paperSafetyShrink ? ', safety shrink' : '') + ')...');
                        const _t0 = performance.now();
                        const resp = await pp.print(this.selectedPrinter, b64, cfg);
                        this.ptLog('Print job sent successfully (spooler accepted it after ' +
                            ((performance.now() - _t0) / 1000).toFixed(1) + 's).');
                        await LPLog.finish(job, { ok: true, response: resp });
                        this.printMsg = 'Sent to ' + this.selectedPrinter + '!';
                        this.printMsgKind = 'success';
                    } catch (e) {
                        console.error('[localprint] PrintTrays print failed:', e);
                        this.ptLog('Print failed: ' + ((e && e.message) || e));
                        await LPLog.finish(job, { ok: false, error: (e && e.message) || 'Print failed.' });
                        this.printMsg = (e && e.message) || 'Print failed.';
                        this.printMsgKind = 'error';
                    } finally {
                        this.printing = false;
                    }
                },

                // Print-ready PDF: the SAME re-lay as /new-testing/print/pdf/check
                // (js/new-print-relay.js). Paper dims resolve in the same order as
                // that page — live driver dims, then the static table, then psDim();
                // the driver custom size uses psDim(). E2E / custom paper is built at
                // its trim size and cover-filled; other paper follows the scale mode.
                async relayForPrint(bytes) {
                    const _live = this.dynamicDims && this.dynamicDims[this.printPaperSize];
                    const _stat = trayDims && trayDims[this.printPaperSize];
                    let dim = (_live && _live.w > 0) ? _live : ((_stat && _stat.w > 0) ? _stat : this.psDim());
                    if (this.printPaperSize === this.psCustomSizeValue) {
                        const _cd = this.psDim();
                        if (_cd && _cd.w > 0 && _cd.h > 0) dim = _cd;
                    }
                    return await window.qrintoRelayPdf(bytes, {
                        paperW: dim ? dim.w : 0,
                        paperH: dim ? dim.h : 0,
                        e2e: this.trimsBleed(),
                        trim: this.trimSizeFromCode(this.printPaperSize) ||
                            this.trimSizeFromCode(this.selectedSizeLabel ? this.selectedSizeLabel() : ''),
                        mode: this.printScaleMode || 'fit',
                        scaleFactor: this.printScaleFactor || 100,
                    });
                },

                // Papers whose PDF is built at the trim size (bleed − 0.57 / 0.49 in):
                // any E2E size, and the driver's PostScript Custom Page Size.
                trimsBleed() {
                    return this.printPaperSize === this.psCustomSizeValue || this.isE2ESelected();
                },

                // Derive the TRIM size (in inches) from a tray size code —
                // strips a "-E2E" suffix and parses "WxH" (e.g. '7x10-E2E' →
                // [7, 10]). Named sizes (Letter, Legal) handled explicitly.
                // Returns null on an unknown code so the caller can fall back
                // to the bleed dims from trayDims / dynamicDims.
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
                    if (paren) return [parseFloat(paren[1]), parseFloat(paren[2])];
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
