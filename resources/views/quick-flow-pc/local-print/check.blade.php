@extends('layouts.quick-flow-pc')
@section('title', 'Check & print | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans">
        <div class="max-w-[1080px] mx-auto" x-data="checkFlow(
            {{ Illuminate\Support\Js::from(['url' => $file['url'], 'name' => $file['name'], 'bytes' => $file['bytes'], 'type' => $file['type']]) }},
            {{ Illuminate\Support\Js::from($trays) }},
            {{ Illuminate\Support\Js::from($trayDims) }}
        )" x-init="init()">

            <div class="flex items-center gap-2 mb-3 md:mb-4">
                <a href="{{ route('localprint.pdf') }}"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ← <span class="hidden md:inline">Back</span>
                </a>
                <h1 class="text-base md:hidden font-extrabold text-[#112419] tracking-tight">Check & print</h1>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 items-start">

                {{-- Left: file + specs --}}
                <div class="bg-white border border-slate-200/80 rounded-xl md:rounded-2xl p-4 md:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-bold text-slate-900 text-sm truncate" x-text="file.name" :title="file.name">
                            </h2>
                            <p class="mono text-[11px] text-slate-400 mt-0.5" x-text="prettySize(file.bytes)"></p>
                        </div>
                        <div x-show="file.type === 'pdf'" class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="prevPage()" :disabled="page <= 1 || pages <= 1"
                                class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            </button>
                            <span class="mono text-[11px] text-slate-500 tabular-nums w-14 text-center">
                                <span x-text="page"></span> / <span x-text="pages || '-'"></span>
                            </span>
                            <button type="button" @click="nextPage()" :disabled="page >= pages || pages <= 1"
                                class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    {{-- View tabs: raw file vs. "as it prints on this tray" --}}
                    <div class="inline-flex items-center gap-1 bg-slate-100 border border-slate-200 rounded-lg p-0.5 mt-4"
                        role="tablist">
                        <button type="button" role="tab" @click="setViewMode('file')"
                            :aria-selected="viewMode === 'file'"
                            class="px-3 py-1.5 rounded-md text-[12px] font-bold transition"
                            :class="viewMode === 'file' ? 'bg-white text-slate-900 shadow-sm' :
                                'text-slate-500 hover:text-slate-700'">
                            Your file
                        </button>
                        <button type="button" role="tab" @click="setViewMode('tray')"
                            :aria-selected="viewMode === 'tray'"
                            class="px-3 py-1.5 rounded-md text-[12px] font-bold transition"
                            :class="viewMode === 'tray' ? 'bg-white text-slate-900 shadow-sm' :
                                'text-slate-500 hover:text-slate-700'">
                            How it prints on this tray
                        </button>
                    </div>

                    <div class="bg-[#f2f7f2] rounded-xl mt-3 p-4 flex items-center justify-center relative overflow-hidden"
                        :class="orientation === 'landscape' ? 'min-h-[160px] md:min-h-[260px]' :
                            'min-h-[220px] md:min-h-[360px]'">
                        <div x-show="!ready" x-cloak class="text-center">
                            <div
                                class="w-8 h-8 border-2 border-slate-300 border-t-[#287d3c] rounded-full animate-spin mx-auto">
                            </div>
                            <p class="mono text-[11px] text-slate-500 mt-3">Reading PDF…</p>
                        </div>
                        <div x-show="pdfError" x-cloak class="text-center">
                            <i data-lucide="file-warning" class="w-6 h-6 text-red-500 mx-auto"></i>
                            <p class="text-[12px] text-red-600 font-medium mt-2" x-text="pdfError"></p>
                        </div>

                        {{-- "Your file" view: the artwork at the SHARED preview scale,
                             so it is the exact same display size as in the tray view. --}}
                        <div x-show="viewMode === 'file' && ready && !pdfError" class="flex items-center justify-center">
                            <div class="rounded-sm shadow-lg bg-white overflow-hidden" :style="fileBoxStyle()">
                                <canvas x-show="file.type === 'pdf'" x-ref="pdfCanvas"
                                    style="width:100%; height:100%; display:block;"></canvas>
                                <img x-ref="imgPreview" x-show="file.type === 'image'" :src="file.url"
                                    class="object-contain" style="width:100%; height:100%; display:block;" />
                            </div>
                        </div>

                        {{-- "How it prints on this tray" view: the file placed on the
                             selected paper. E2E fills the sheet (no border); non-E2E
                             shows the ~0.2 in white margin that the printer leaves. --}}
                        <div x-show="viewMode === 'tray' && ready && !pdfError"
                            class="flex flex-col items-center gap-2 w-full">
                            <p class="mono text-[10px] text-slate-500 text-center px-2 max-w-full truncate"
                                x-text="trayPaperInfo()"></p>
                            <div class="relative shadow-lg border border-slate-800 overflow-hidden"
                                :style="trayPaperStyle()">
                                {{-- Paper background: hatched red = white, unprinted
                                     margin the printer leaves on non-E2E media. --}}
                                <div class="absolute inset-0 pointer-events-none" x-show="!isE2ESelected()"
                                    style="background: repeating-linear-gradient(45deg, #ffffff 0 4px, rgba(229,83,75,.3) 4px 8px);">
                                </div>
                                <div class="absolute inset-0 bg-white" x-show="isE2ESelected()"></div>
                                {{-- Printable area: solid white, clips any artwork that
                                     would fall into the unprintable margin. --}}
                                <div class="absolute bg-white overflow-hidden" :style="trayContentStyle()">
                                    {{-- Artwork, centered on the sheet at the SHARED
                                         scale — same display size as the file tab. --}}
                                    <div class="absolute left-1/2 top-1/2" :style="trayFileStyle()">
                                        <canvas x-show="file.type === 'pdf'" x-ref="pdfCanvasTray"
                                            style="width:100%; height:100%; display:block;"></canvas>
                                        <img x-show="file.type === 'image'" :src="file.url" class="object-contain"
                                            style="width:100%; height:100%; display:block;" />
                                    </div>
                                </div>
                            </div>
                            <div x-show="!isE2ESelected()"
                                class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-1">
                                <span class="inline-block w-2.5 h-2.5 shrink-0"
                                    style="background: repeating-linear-gradient(45deg, #ffffff 0 2px, rgba(229,83,75,.3) 2px 4px); border: 1px solid #d1d5db;"></span>
                                <span>White, unprinted paper (border on every side)</span>
                            </div>
                            <div x-show="isE2ESelected()"
                                class="flex items-center gap-1.5 text-[10px] text-emerald-700 mt-1">
                                <i data-lucide="check-circle" class="w-3 h-3 shrink-0"></i>
                                <span>Borderless — prints to the edge</span>
                            </div>
                        </div>
                    </div>

                    <div class="mono text-[13px] text-slate-500 mt-5 space-y-1.5">
                        <p x-show="file.type === 'pdf'"><span class="text-slate-900 font-bold" x-text="pages || '-'"></span>
                            pages</p>
                        <p><span class="text-slate-900 font-bold">
                                <template x-if="dims"><span
                                        x-text="`${fmt(dims.w)} × ${fmt(dims.h)} in`"></span></template>
                                <template x-if="!dims"><span>-</span></template>
                            </span> <span x-text="orientation ? `(${orientation})` : ''"></span></p>
                        <p><span class="text-slate-900 font-bold" x-text="dpi ? `${dpi} dpi` : ''"></span> </p>
                        <p><span class="text-slate-900 font-bold" x-text="colorMode || '-'"></span> color</p>
                    </div>
                </div>

                {{-- Right: preflight + tray + print --}}
                <div class="bg-white border border-slate-200/80 rounded-xl md:rounded-2xl p-4 md:p-6">
                    <h2 class="font-bold text-slate-900 text-sm mb-4">Checks</h2>

                    {{-- Dynamic check rows (size / orientation / E2E) with ack checkboxes --}}
                    <div class="space-y-1">
                        <template x-for="c in checks" :key="c.key">
                            <div class="flex items-start gap-2.5 py-2.5 transition-colors"
                                :class="c.ok ? 'border-b border-slate-100' : (c.severity === 'error' ?
                                    'bg-red-50 border border-red-100 rounded-lg px-3 my-1' :
                                    'bg-amber-50 border border-amber-100 rounded-lg px-3 my-1')">
                                <template x-if="c.ok">
                                    <i data-lucide="check" class="w-4 h-4 text-[#287d3c] mt-0.5 shrink-0"></i>
                                </template>
                                <template x-if="!c.ok && c.severity === 'warn'">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
                                </template>
                                <template x-if="!c.ok && c.severity === 'error'">
                                    <i data-lucide="x" class="w-4 h-4 text-red-500 mt-0.5 shrink-0"></i>
                                </template>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-bold text-slate-900" x-text="c.title"></p>
                                    <p class="text-[12px] text-slate-500 mt-0.5" x-text="c.detail"></p>
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
                                        <i data-lucide="download" class="w-4 h-4"></i>Download 931-BL Multi
                                        Driver
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
                                <input type="number" name="copies" x-model.number="copies" min="1"
                                    max="999"
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
                            class="w-full mt-4 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99] disabled:bg-slate-300 disabled:text-white disabled:cursor-not-allowed"
                            :class="(!canPrint || printing) ? '' : (psChecksAllClear(checks) ?
                                'bg-[#287d3c] hover:bg-emerald-800' : 'bg-red-600 hover:bg-red-700')">
                            <template x-if="printing"><span>Sending to printer…</span></template>
                            <template x-if="!printing && canPrint"><span>Print <span x-text="copies"></span> <span
                                        x-text="copies == 1 ? 'copy' : 'copies'"></span></span></template>
                            <template x-if="!printing && !canPrint"><span
                                    x-text="blocker || 'Not ready to print'"></span></template>
                        </button>

                        <p class="text-center mt-3">
                            <a href="https://noritsucanada.com/print-trays/download/" target="_blank"
                                class="text-[12px] text-slate-400 hover:text-[#287d3c] transition inline-flex items-center gap-1">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download PrintTrays
                            </a>
                        </p>
                    </div>

                </div>

            </div>

            @include('partials.print-settings-modal')

        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
    <script src="{{ asset('js/print-settings.js') }}?v={{ filemtime(public_path('js/print-settings.js')) }}"></script>
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
                        'selectedPrinter'
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
                            this.psLoad(this.selectedPrinter);
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
                        // No tray number (MP / multi tray) sorts first, then
                        // numbered trays in ascending order.
                        return n === null ? -1 : n;
                    };
                    return (list || [])
                        // .filter(p => p && typeof p.name === 'string')
                        // Noritsu-only filter — DISABLED so ALL printer queues show.
                        // Uncomment to restore Noritsu-only.
                        .filter(p => p.name.toLowerCase().includes('noritsu'))
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
                        return await this.imageToPdfBase64(this.file.url);
                    }
                    const res = await fetch(this.file.url, {
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('Failed to fetch file: HTTP ' + res.status);
                    let bytes = new Uint8Array(await res.arrayBuffer());
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
                            // Use the TRIM size (e.g. 5x7-E2E → 5 × 7), NOT the full bleed
                            // (5.57 × 7.49): the PDF is saved/printed at the trim size and
                            // the driver's pinned E2E media form expands trim to bleed at
                            // print time for a borderless result. Matches the image path.
                            // Fall back to live/static dims (w > 0 guard) when no trim can
                            // be parsed from the size code/label.
                            const _trim = this.trimSizeFromCode(this.printPaperSize) ||
                                this.trimSizeFromCode(this.selectedSizeLabel());
                            const _live = this.dynamicDims && this.dynamicDims[this.printPaperSize];
                            const _stat = this.trayDims && this.trayDims[this.printPaperSize];
                            let dim = _trim ? {
                                    w: _trim[0],
                                    h: _trim[1]
                                } :
                                ((_live && _live.w > 0) ? _live :
                                    ((_stat && _stat.w > 0) ? _stat :
                                        (this.psDim() || {
                                            w: 8.5,
                                            h: 11
                                        })));
                            // Driver CUSTOM page size reports the BLEED dims (e.g.
                            // 5.57 × 7.49). The print PDF must be the TRIM size — bleed
                            // minus the fixed E2E margins (0.57 in short edge, 0.49 in
                            // long edge) — so 5.57 × 7.49 → 5 × 7 (matches the named E2E
                            // sizes). ONLY for the custom size.
                            if (this.printPaperSize === this.psCustomSizeValue) {
                                const _cd = this.psDim();
                                if (_cd && _cd.w > 0 && _cd.h > 0) {
                                    const _mn = Math.min(_cd.w, _cd.h),
                                        _mx = Math.max(_cd.w, _cd.h);
                                    dim = {
                                        w: Math.max(0.1, +(_mn - 0.57).toFixed(2)),
                                        h: Math.max(0.1, +(_mx - 0.49).toFixed(2))
                                    };
                                }
                            }
                            console.log('[localprint] pdf→print paper dim →', {
                                code: this.printPaperSize,
                                trim: _trim,
                                live: _live,
                                stat: _stat,
                                dim,
                            });
                            const rawMax = Math.max(dim.w, dim.h),
                                rawMin = Math.min(dim.w, dim.h);
                            const pageW = (outLandscape ? rawMax : rawMin) * PT;
                            const pageH = (outLandscape ? rawMin : rawMax) * PT;

                            const out = await PDFDocument.create();
                            const mode = this.printScaleMode || 'fit';

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

                        this.$nextTick(() => this.renderPage(1));
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
                get checksPrintable() {
                    return this.psChecksPrintable(this.checks);
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
                        type: 'pdf',
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
                        //     form for a borderless, corregictly-oriented print.
                        cfg.useDriverDefaultSize = true;
                        cfg.scaleMode = 'fit';
                        delete cfg.scaleFactor;
                    } else if (this.sizesSource === 'printer' &&
                        this.dynamicDims && this.dynamicDims[this.printPaperSize]) {
                        cfg.paperSize = this.printPaperSize;
                    }

                    // Duplex values match the bridge's own select: '' (simplex),
                    // 'longEdge', 'shortEdge'. Only send when the user picked one.
                    if (this.printDuplex) cfg.duplex = this.printDuplex;

                    // Paper source → bridge `inputBin` (goes into SumatraPDF
                    // `bin=`). Only from live capabilities; empty = printer default.
                    if (this.printInputBin) cfg.inputBin = this.printInputBin;

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
                    const cfg = this.buildPrintConfig('pdf');
                    try {
                        const pp = __ptInstance;
                        if (!pp) throw new Error('PrintTrays not connected');

                        // The bridge only accepts type 'pdf' ("Unsupported print type: image").
                        let b64;
                        let _usedSaved = false;
                        if (this.savedPdfB64) {
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
                            if (window.PDFLib) {
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
                        const resp = await pp.print(this.selectedPrinter, b64, cfg);
                        await LPLog.finish(job, {
                            ok: true,
                            response: resp
                        });
                        this.printMsg = 'Sent to ' + this.selectedPrinter + '!';
                        this.printMsgKind = 'success';
                    } catch (e) {
                        console.error('[localprint] PrintTrays print failed:', e);
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
                imageToPdfBase64(url) {
                    return new Promise((resolve, reject) => {
                        const img = new Image();
                        img.crossOrigin = 'anonymous';
                        img.onload = () => {
                            try {
                                // Size the canvas to the ACTUAL selected paper size (in
                                // inches, from trayDims), not the image's raw pixel size —
                                // then crop-to-cover so the image fills it with zero gaps,
                                // regardless of the source image's own aspect ratio.
                                // Prefer TRIM dims parsed from the size code (e.g.
                                // '7x10-E2E' → 7×10) so the produced PDF page size
                                // matches what the other flows write. Fall back to
                                // the LIVE driver dims (dynamicDims), then the
                                // static bleed table (trayDims). Letter is only a
                                // last-resort default when nothing is known.
                                // Use the TRIM size (e.g. 5x7-E2E → 5 × 7), NOT the full
                                // bleed (5.57 × 7.49): the PDF is saved/printed at the trim
                                // size and the driver's pinned E2E media form expands trim
                                // to bleed at print time for a borderless result.
                                const trim = this.trimSizeFromCode(this.printPaperSize) ||
                                    this.trimSizeFromCode(this.selectedSizeLabel());
                                const live = this.dynamicDims && this.dynamicDims[this.printPaperSize];
                                const stat = this.trayDims && this.trayDims[this.printPaperSize];
                                let dim = trim ? {
                                        w: trim[0],
                                        h: trim[1]
                                    } :
                                    (live && live.w > 0 ? live :
                                        (stat && stat.w > 0 ? stat : {
                                            w: 8.5,
                                            h: 11
                                        }));
                                // Driver CUSTOM page size reports the BLEED dims; the
                                // print PDF must be the TRIM size (bleed − 0.57 short
                                // edge / − 0.49 long edge), e.g. 5.57 × 7.49 → 5 × 7.
                                // ONLY for the custom size.
                                if (this.printPaperSize === this.psCustomSizeValue) {
                                    const _cd = this.psDim();
                                    if (_cd && _cd.w > 0 && _cd.h > 0) {
                                        const _mn = Math.min(_cd.w, _cd.h),
                                            _mx = Math.max(_cd.w, _cd.h);
                                        dim = {
                                            w: Math.max(0.1, +(_mn - 0.57).toFixed(2)),
                                            h: Math.max(0.1, +(_mx - 0.49).toFixed(2))
                                        };
                                    }
                                }
                                console.log('[localprint] image→pdf size →', {
                                    code: this.printPaperSize,
                                    label: this.selectedSizeLabel(),
                                    trim,
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
                                const _m = this.printScaleMode || 'fit';
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
