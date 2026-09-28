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
                            <p><span class="text-slate-900 font-bold">Design Style </span>
                                {{ ucfirst($flowData['orientation']) }}</p>
                            <p><span class="text-slate-900 font-bold">Size </span> {{ $flowData['size_name'] }} </p>
                            <p><span class="text-slate-900 font-bold"> Print Mode : </span>
                                <span>

                                    @switch((int)$totalPages)
                                        @case(2)
                                            Landscape
                                        @break

                                        @case(4)
                                            Portrait
                                        @break

                                        @default
                                            {{ ucfirst($flowData['orientation']) }}
                                    @endswitch

                                </span>

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
                            <div class="flex items-start gap-2.5 py-3.5 transition-colors"
                                :class="c.ok ? 'border-b border-slate-100' : (c.severity === 'error' ?
                                    'bg-red-50 border border-red-100 rounded-lg px-3 my-1' :
                                    (c.severity === 'warn' ?
                                        'bg-amber-50 border border-amber-100 rounded-lg px-3 my-1' :
                                        'border-b border-slate-100'))">
                                {{-- Inline SVGs (not lucide <i>) so the icon + colour
                                     re-render reactively when a check's state changes. --}}
                                <template x-if="c.ok">
                                    <svg class="w-4 h-4 text-[#287d3c] mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                </template>
                                <template x-if="!c.ok && c.severity === 'warn'">
                                    <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                        <path d="M12 9v4" />
                                        <path d="M12 17h.01" />
                                    </svg>
                                </template>
                                <template x-if="!c.ok && c.severity === 'error'">
                                    <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M18 6 6 18" />
                                        <path d="m6 6 12 12" />
                                    </svg>
                                </template>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-bold text-slate-900" x-text="c.title"></p>
                                    <p class="text-[12px] text-slate-500 mt-0.5" x-text="c.detail"></p>
                                    {{-- Acknowledgement checkbox for a failing check.
                                         For errors it must be ticked before printing;
                                         for warnings (E2E) it's optional. --}}
                                    <template x-if="!c.ok && c.ackLabel">
                                        <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
                                            <input type="checkbox" x-model="acks[c.key]"
                                                class="w-4 h-4 rounded border-slate-300 shrink-0"
                                                :class="c.severity === 'error' ? 'accent-red-600' : 'accent-amber-500'">
                                            <span class="text-[12px] font-semibold"
                                                :class="c.severity === 'error' ? 'text-red-700' : 'text-amber-700'"
                                                x-text="c.ackLabel"></span>
                                        </label>
                                    </template>
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
                            :class="bridgeReady ? 'bg-emerald-50 text-emerald-700' : (bridgeChecking ?
                                'bg-slate-50 text-slate-500' :
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
                                    connected <span class="text-slate-400" x-show="bridgeVersion"
                                        x-text="'v' + bridgeVersion"></span></span>
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
                        <div x-show="showBridgeModal" x-cloak
                            class="fixed inset-0 z-50 flex items-center justify-center p-4"
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
                                        Download and install PrintTrays, then reload this page. It runs in the background
                                        and
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
                                    <button type="button"
                                        @click="showBridgeModal = false; bridgeChecking = true; initBridge();"
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
                                            <p class="text-[11px] text-slate-400 mt-0.5" x-show="p.driver"
                                                x-text="p.driver"></p>
                                            <template x-if="selectedPrinter === p.name">
                                                <p class="text-[11px] text-slate-500 mt-0.5" x-text="psSummary()"></p>
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

                        {{-- Button colour:
                             • disabled (grey) → not printable yet (unacknowledged error, etc.)
                             • green          → everything clean (no error, no warning)
                             • red            → printable but with acknowledged issues
                                                (errors ticked, or a non-E2E warning). --}}
                        <button type="button" @click="doPrint()" :disabled="!canPrint || printing"
                            class="w-full mt-4 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99] disabled:bg-slate-300 disabled:cursor-not-allowed"
                            :class="(!canPrint || printing) ? '' : (checksAllClear ?
                                'bg-[#287d3c] hover:bg-emerald-800' :
                                'bg-red-600 hover:bg-red-700')">
                            <template x-if="printing"><span>Sending to printer…</span></template>
                            <template x-if="!printing && canPrint && checksAllClear"><span>Print <span
                                        x-text="copies"></span> <span
                                        x-text="copies == 1 ? 'copy' : 'copies'"></span></span></template>
                            <template x-if="!printing && canPrint && !checksAllClear"><span>Print <span
                                        x-text="copies"></span> <span x-text="copies == 1 ? 'copy' : 'copies'"></span>
                                    anyway</span></template>
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
    {{-- pdf-lib: scale/cover-fill the server PDF onto the selected media size so
         it prints edge-to-edge (the raw design PDF is the DESIGN size, e.g. 5×7,
         which SumatraPDF would otherwise centre small on a larger sheet). --}}
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
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
                ...window.printSettingsMixin({
                    sizeOptions: @json($sizeOptions),
                    sizeDims: trayDims
                }),

                slide: 0,
                touchX: 0,
                copies: 1,

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

                init() {
                    let pages = parseInt({{ $totalPages }});
                    // Some products only work in one orientation:
                    //   2-page (double) → landscape-only
                    //   4-page          → portrait-only
                    // Lock the orientation so the modal can grey/redden the other
                    // option (instead of the old alert()), and force the correct one.
                    if (pages == 2) {
                        this.orientationLock = 'landscape';
                        this.printLandscape = true;
                    } else if (pages == 4) {
                        this.orientationLock = 'portrait';
                        this.printLandscape = false;
                    } else if (designSize.w && designSize.h) {
                        this.orientationLock = null;
                        this.printLandscape = designSize.w > designSize.h;
                    }
                    // Auto-select the Paper Size that matches the DESIGN's actual size
                    // (e.g. a 5 × 7 design → the 5 × 7 paper option) from the static
                    // list now; re-applied after live sizes load (see initBridge).
                    if (designSize.w && designSize.h) {
                        this.psSelectSizeForDims(designSize.w, designSize.h);
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

                            // Orientation — compare the INTENDED print orientation (the
                            // one the job will actually print in) against the selected
                            // paper's orientation. The print orientation follows the
                            // product page count (2-page → landscape, 4-page → portrait)
                            // and otherwise the chosen printLandscape toggle — NOT the
                            // design's raw stored dimensions (a 2-page card is stored
                            // 7×10 portrait but prints landscape).
                            // Match the "Print Mode" shown on the page exactly:
                            //   2-page → Landscape, 4-page → Portrait,
                            //   otherwise → the design's own orientation.
                            const pages = parseInt({{ $totalPages }});
                            let wantLandscape;
                            if (pages === 2) wantLandscape = true;
                            else if (pages === 4) wantLandscape = false;
                            else wantLandscape = String(designOrientation).toLowerCase() === 'landscape';

                            // Compare the design's REQUIRED orientation against the
                            // orientation the operator selected in Print Settings
                            // (printLandscape) — NOT the paper's trim aspect. The paper
                            // is printed in the chosen orientation (a 3×5 card printed
                            // Landscape becomes 5×3), so the chosen toggle is the real
                            // "paper" orientation. This updates live as the toggle changes.
                            const chosenLandscape = !!this.printLandscape;
                            const orientOk = wantLandscape === chosenLandscape;
                            const needOrient = wantLandscape ? 'landscape' : 'portrait';
                            const chosenOrient = chosenLandscape ? 'landscape' : 'portrait';
                            list.push({
                                key: 'orient',
                                ok: orientOk,
                                severity: orientOk ? 'ok' : 'error',
                                title: orientOk ? 'Orientation matches' : 'Orientation mismatch',
                                detail: orientOk ?
                                    'Design and print are both ' + needOrient + '.' : 'Design needs ' +
                                    needOrient + ', but ' + chosenOrient + ' is selected.',
                                ackLabel: orientOk ? null : 'I understand. Print anyway.',
                            });
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
                        } = await import('{{ asset('js/printtrays.js') }}');
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
                        const raw = Array.isArray(list) ? list : [list].filter(Boolean);
                        this.printers = this.orderPrinters(raw);
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

                applyPaperSettings() {
                    this.showPaperModal = false;
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
                get allAcksTicked() {
                    return this.checks
                        .filter(c => !c.ok && c.ackLabel)
                        .every(c => !!this.acks[c.key]);
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
                    // This is a server-generated real PDF already laid out in the
                    // design's orientation, so `landscape` is passed straight through.
                    const cfg = {
                        type: 'pdf',
                        landscape: !!this.printLandscape,
                        color: !!this.printColor,
                        copies: this.copies || 1,
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
                    if (this.sizesSource === 'printer' &&
                        this.dynamicDims && this.dynamicDims[this.printPaperSize]) {
                        cfg.paperSize = this.printPaperSize;
                    }

                    // Duplex values match the bridge's own select: '' (simplex),
                    // 'longEdge', 'shortEdge'. Only send when the user picked one.
                    if (this.printDuplex) cfg.duplex = this.printDuplex;

                    // Paper source → bridge `inputBin` (goes into SumatraPDF `bin=`).
                    // Only from live capabilities; empty = printer default.
                    if (this.printInputBin) cfg.inputBin = this.printInputBin;

                    console.log('[localprint] print config →', JSON.parse(JSON.stringify(cfg)));
                    return cfg;
                },

                // Explicit opt-in: when true, the server PDF is rebuilt/cover-filled
                // onto the selected media size before printing (the legacy behaviour,
                // isolated in transformPdfForPrint below). Default is FALSE so the
                // already-generated server PDF is treated as the source of truth and
                // sent to PrintTrays unchanged. Only flip this if a specific printer
                // capability is found to genuinely require in-browser PDF transformation.
                forcePdfTransform: false,

                async doPrint() {
                    if (!this.canPrint || this.printing) return;
                    this.printing = true;
                    this.printMsg = 'Sending to printer…';
                    this.printMsgKind = 'info';

                    try {
                        const pp = __ptInstance;
                        if (!pp) throw new Error('PrintTrays not connected');

                        // DEFAULT PRINT PATH — no PDF reconstruction.
                        // Fetch the already-generated server PDF and send those exact
                        // bytes straight to PrintTrays. The server PDF is the source of
                        // truth: a 7×10 design PDF stays a 7×10 PDF. Scaling / fit /
                        // edge-to-edge is left to PrintTrays → the driver → the printer.
                        const res = await fetch(pdfUrl, {
                            credentials: 'same-origin'
                        });
                        if (!res.ok) throw new Error('Failed to fetch PDF: HTTP ' + res.status);
                        let bytes = new Uint8Array(await res.arrayBuffer());

                        // ROTATION-ONLY (no rebuild, no resize). The driver does NOT
                        // reliably honour the `landscape` flag, so if a page's own
                        // orientation doesn't match the chosen print orientation we
                        // rotate it 90° in place — dimensions stay the same (7×10 stays
                        // 7×10), only a rotation flag is set. This fixes landscape /
                        // portrait without regenerating the PDF or adding margins.
                        bytes = await this.rotatePdfToOrientation(bytes);

                        // FALLBACK ONLY — never runs during normal printing. Kept as an
                        // explicit escape hatch (transformPdfForPrint) for a future
                        // printer capability that genuinely needs the PDF rebuilt.
                        if (this.forcePdfTransform &&
                            (this.printScaleMode || 'fit') === 'fit') {
                            bytes = await this.transformPdfForPrint(bytes);
                        }

                        const b64 = this.arrayBufToBase64(bytes.buffer);

                        // The bridge decodes PDF jobs with Buffer.from(req.data,
                        // 'base64') and routes on the top-level `type`. Send RAW
                        // base64 (no data: prefix, no `flavor` key).
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

                // ── ROTATION-ONLY (lightweight, on the default path) ──
                // Rotates each page 90° ONLY when its orientation doesn't match the
                // chosen print orientation. No re-embed, no scaling, no size change —
                // pdf-lib's setRotation just flips the page's rotation flag, so a 7×10
                // design PDF stays a 7×10 PDF (its printed footprint becomes 10×7).
                // Best-effort: returns the original bytes on any failure or when no
                // rotation is needed.
                async rotatePdfToOrientation(bytes) {
                    try {
                        if (!window.PDFLib) return bytes;
                        const {
                            PDFDocument,
                            degrees
                        } = window.PDFLib;

                        let pages = parseInt({{ $totalPages }});
                        // 2-page (double) products are ALWAYS landscape; 4-page ALWAYS
                        // portrait; otherwise follow the modal's chosen orientation.
                        let wantLandscape;
                        if (pages === 2) wantLandscape = true;
                        else if (pages === 4) wantLandscape = false;
                        else wantLandscape = !!this.printLandscape;

                        const src = await PDFDocument.load(bytes);
                        const list = src.getPages();
                        let changed = false;
                        for (const page of list) {
                            const {
                                width,
                                height
                            } = page.getSize();
                            const isLandscape = width > height;
                            if (isLandscape === wantLandscape) continue; // already correct
                            // Add 90° to whatever rotation the page already has.
                            const cur = page.getRotation().angle || 0;
                            page.setRotation(degrees((cur + 90) % 360));
                            changed = true;
                        }
                        if (!changed) return bytes; // nothing to do → original bytes
                        return await src.save();
                    } catch (e) {
                        console.warn('[localprint] PDF rotate skipped:', e && e.message || e);
                        return bytes;
                    }
                },

                // ── FALLBACK PDF TRANSFORM (isolated, not on the default path) ──
                // Rebuilds the server PDF so every page is the SELECTED media size,
                // cover-filling the original design onto it. This is the LEGACY
                // edge-to-edge behaviour, extracted verbatim from doPrint(). It is
                // ONLY invoked when forcePdfTransform is explicitly enabled — normal
                // printing sends the untouched server PDF. Best-effort: returns the
                // original bytes on any failure.
                async transformPdfForPrint(bytes) {
                    const media = this.dynamicDims[this.printPaperSize] || trayDims[this.printPaperSize];
                    if (!(media && media.w > 0 && media.h > 0)) return bytes;

                    // Use TRIM dims (parsed from the size code) instead of the BLEED
                    // sheet dims, so the saved PDF is e.g. 7×10 rather than 7.57×10.49.
                    let trim = this.trimSizeFromCode(this.printPaperSize) || [media.w, media.h];
                    let tw = trim[0],
                        th = trim[1];
                    let pages = parseInt({{ $totalPages }});
                    // 2-page (double) products are ALWAYS landscape; other page counts
                    // follow the chosen orientation.
                    const wantLandscape = (pages === 2) ? true : this.printLandscape;
                    if (pages != 4 && wantLandscape && tw < th) {
                        const t = tw;
                        tw = th;
                        th = t;
                    }
                    return await this.fitPdfToMedia(bytes, tw * 72, th * 72);
                },

                // Derive the TRIM size (in inches) from a tray size code —
                // strips a "-E2E" suffix and parses "WxH" (e.g. '7x10-E2E' →
                // [7, 10]). Named sizes (Letter, Legal) handled explicitly.
                // Returns null on an unknown code so the caller can fall back
                // to the bleed dims from trayDims / dynamicDims.
                trimSizeFromCode(code) {
                    const raw = String(code || '').trim();
                    const base = raw.replace(/[-\s]*e2e$/i, '').trim();
                    const m = base.match(/^(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)$/i);
                    if (m) return [parseFloat(m[1]), parseFloat(m[2])];
                    const named = {
                        'letter': [8.5, 11],
                        'legal': [8.5, 14]
                    };
                    const key = base.toLowerCase();
                    if (named[key]) return named[key];
                    return null;
                },

                async fitPdfToMedia(bytes, mediaWpt, mediaHpt) {
                    try {
                        if (!window.PDFLib) return bytes;
                        const {
                            PDFDocument,
                            degrees
                        } = window.PDFLib;
                        const src = await PDFDocument.load(bytes);
                        const out = await PDFDocument.create();
                        const count = src.getPageCount();
                        for (let i = 0; i < count; i++) {
                            const emb = await out.embedPage(src.getPage(i));
                            const pw = emb.width,
                                ph = emb.height;
                            const page = out.addPage([mediaWpt, mediaHpt]);
                            // If the design's orientation differs from the media page's
                            // orientation, rotate the DESIGN 90° (not just the page) so
                            // the content turns with the paper — no cropping.
                            const rotate = (pw > ph) !== (mediaWpt > mediaHpt);
                            if (rotate) {
                                // 90° CCW: the design's footprint swaps axes, so cover
                                // the media using the swapped extents and offset origin.
                                const s = Math.max(mediaWpt / ph, mediaHpt / pw);
                                const wW = ph * s,
                                    wH = pw * s;
                                page.drawPage(emb, {
                                    x: (mediaWpt + wW) / 2,
                                    y: (mediaHpt - wH) / 2,
                                    xScale: s,
                                    yScale: s,
                                    rotate: degrees(90),
                                });
                            } else {
                                const scale = Math.max(mediaWpt / pw, mediaHpt / ph); // cover
                                const dw = pw * scale,
                                    dh = ph * scale;
                                page.drawPage(emb, {
                                    x: (mediaWpt - dw) / 2,
                                    y: (mediaHpt - dh) / 2,
                                    xScale: scale,
                                    yScale: scale,
                                });
                            }
                        }
                        return await out.save();
                    } catch (e) {
                        console.warn('[localprint] PDF fit skipped:', e && e.message || e);
                        return bytes;
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
