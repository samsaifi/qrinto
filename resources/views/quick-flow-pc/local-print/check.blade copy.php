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

                    <div class="bg-[#f2f7f2] rounded-xl mt-4 p-4 flex items-center justify-center relative"
                        :class="orientation === 'landscape' ? 'min-h-[100px] md:min-h-[200px]' : 'min-h-[140px] md:min-h-[280px]'">
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
                        <canvas x-show="ready && !pdfError && file.type === 'pdf'" x-ref="pdfCanvas"
                            class="max-w-full rounded-sm shadow-lg bg-white"
                            :class="orientation === 'landscape' ? 'max-h-[130px] md:max-h-[260px]' : 'max-h-[210px] md:max-h-[420px]'"</canvas>
                        <img x-ref="imgPreview" x-show="ready && !pdfError && file.type === 'image'" :src="file.url"
                            class="max-w-full max-h-[160px] md:max-h-[320px] rounded-sm shadow-lg bg-white object-contain">
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

                    {{-- Orientation check (only shown when there's a mismatch) --}}
                    <template x-if="orientationCheck && !orientationCheck.ok">
                        <div class="pb-4 border-b border-slate-100">
                            <div class="flex items-start gap-2.5">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <p class="text-[13px] font-bold text-slate-900">Orientation does not match</p>
                                    <p class="text-[12px] text-slate-500 mt-0.5" x-text="orientationCheck.detail"></p>
                                    <div class="flex gap-2 mt-2.5">
                                        <button type="button" @click="orientationAction = 'rotate'"
                                            class="mono text-[12px] px-3 py-1.5 rounded-lg border transition"
                                            :class="orientationAction === 'rotate' ?
                                                'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                                                'border-slate-200 text-slate-600 hover:border-slate-300'">Rotate
                                            the file</button>
                                        <button type="button" @click="orientationAction = 'asis'"
                                            class="mono text-[12px] px-3 py-1.5 rounded-lg border transition"
                                            :class="orientationAction === 'asis' ?
                                                'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                                                'border-slate-200 text-slate-600 hover:border-slate-300'">Print
                                            as is</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

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

                        {{-- QZ Tray status --}}
                        <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-lg text-[12px]"
                            :class="qzReady ? 'bg-emerald-50 text-emerald-700' : (qzChecking ? 'bg-slate-50 text-slate-500' :
                                'bg-red-50 text-red-600')">
                            <template x-if="qzChecking">
                                <span>
                                    <div
                                        class="w-3.5 h-3.5 border-2 border-slate-300 border-t-slate-600 rounded-full animate-spin inline-block">
                                    </div>
                                    Connecting to QZ Tray…
                                </span>
                            </template>
                            <template x-if="!qzChecking && qzReady">
                                <span><i data-lucide="check-circle" class="w-3.5 h-3.5 inline -mt-0.5"></i> QZ Tray
                                    connected</span>
                            </template>
                            <template x-if="!qzChecking && !qzReady">
                                <span class="flex items-center gap-1.5 w-full">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span class="flex-1">QZ Tray is not installed or not running</span>
                                    <button type="button" @click="showQzModal = true"
                                        class="font-bold underline hover:no-underline shrink-0">Fix this</button>
                                </span>
                            </template>
                        </div>

                        {{-- QZ Tray download modal --}}
                        <div x-show="showQzModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                            @keydown.escape.window="showQzModal = false">
                            <div class="absolute inset-0 bg-black/40" @click="showQzModal = false"></div>
                            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-bold text-slate-900 text-base">QZ Tray Required</h3>
                                    <button type="button" @click="showQzModal = false"
                                        class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="text-center py-4">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center mx-auto mb-4">
                                        <i data-lucide="printer" class="w-7 h-7 text-red-500"></i>
                                    </div>
                                    <p class="text-sm text-slate-700 font-medium">QZ Tray is required to connect to your
                                        local printer.</p>
                                    <p class="text-[12px] text-slate-500 mt-2 leading-relaxed">
                                        Download and install QZ Tray, then reload this page. It runs in the background and
                                        lets your browser communicate with printers on this PC.
                                    </p>
                                </div>

                                <div class="space-y-2 mt-4">
                                    <a href="https://qz.io/download/" target="_blank"
                                        class="w-full flex items-center justify-center gap-2 bg-[#287d3c] hover:bg-emerald-800 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                                        <i data-lucide="download" class="w-4 h-4"></i> Download QZ Tray
                                    </a>
                                    <a href="https://www.dropbox.com/scl/fi/d5f74l6ekg7r3hoxhvhwm/Noritsu_931BL_Driver_Setup-v2.2.exe?rlkey=m94hgu9eww95zj7mr7wtpli30&st=e658g2hc&e=1&dl=1"
                                        target="_blank"
                                        class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                                        <i data-lucide="download" class="w-4 h-4"></i>Download Download 931-BL Multi
                                        Driver
                                    </a>
                                    <button type="button" @click="showQzModal = false; qzChecking = true; initQz();"
                                        class="w-full flex items-center justify-center gap-2 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> I've installed it - retry
                                        connection
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Printer list from QZ Tray --}}
                        <div x-show="qzReady">
                            <label class="text-sm font-semibold text-slate-700 mb-2 block">Select Printer</label>
                            <div class="space-y-2" x-show="printers.length > 0">
                                <template x-for="p in printers" :key="p">
                                    <label
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                                        :class="selectedPrinter === p ? 'border-[#287d3c] bg-[#f2f7f2]' :
                                            'border-slate-200 hover:border-slate-300'"
                                        @click="selectPrinter(p)">
                                        <input type="radio" name="printer_radio" :checked="selectedPrinter === p"
                                            :value="p" class="accent-[#287d3c] w-4 h-4">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-[13px] text-slate-900" x-text="p"></span>
                                            <template x-if="selectedPrinter === p && printSize">
                                                <p class="text-[11px] text-slate-500 mt-0.5"
                                                    x-text="(sizeOptionsMap[printSize] || printSize) + ' · ' + (mediaOptionsMap[printMedia] || printMedia) + ' · ' + (gsmOptionsMap[printGsm] || printGsm)">
                                                </p>
                                            </template>
                                        </div>
                                        <template x-if="selectedPrinter === p">
                                            <button type="button" @click.stop="showPaperModal = true"
                                                class="text-[11px] text-[#287d3c] font-bold hover:underline shrink-0">Change</button>
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
                                <input type="number" name="copies" x-model.number="copies" min="1" max="999"
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

                        <p class="text-center mt-3">
                            <a href="https://qz.io/download/" target="_blank"
                                class="text-[12px] text-slate-400 hover:text-[#287d3c] transition inline-flex items-center gap-1">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download QZ Tray
                            </a>
                        </p>
                    </div>

                </div>

            </div>

            {{-- Paper settings modal --}}
            <div x-show="showPaperModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @keydown.escape.window="showPaperModal = false">
                <div class="absolute inset-0 bg-black/40" @click="showPaperModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-bold text-slate-900 text-base">Paper Settings</h3>
                        <button type="button" @click="showPaperModal = false"
                            class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <p class="text-[12px] text-slate-500 mb-4" x-show="selectedPrinter">
                        Configuring <span class="font-bold text-slate-700" x-text="selectedPrinter"></span>
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Paper Size</label>
                            <select x-model="printSize"
                                class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                                @foreach ($sizeOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-1.5 block">Paper Type</label>
                            <select x-model="printMedia"
                                class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                                @foreach ($mediaOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700 mb-1.5 block">GSM</label>
                            <select x-model="printGsm"
                                class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                                @foreach ($gsmOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" @click="applyPaperSettings()"
                            class="flex-1 bg-[#287d3c] hover:bg-emerald-800 text-white font-bold py-2.5 rounded-xl text-sm transition">
                            Apply
                        </button>
                        <button type="button" @click="showPaperModal = false"
                            class="flex-1 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-slate-50 transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
    <script src="https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script>
        if (window['pdfjsLib']) {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
        }

            if (window.qz && qz.security) {
                var __qzCertCache = null;
                qz.security.setCertificatePromise(function(resolve, reject) {
                    if (__qzCertCache) {
                        resolve(__qzCertCache);
                        return;
                    }
                    fetch("{{ route('storepanel.qz.cert') }}", {
                            credentials: 'same-origin'
                        })
                        .then(function(r) {
                            return r.ok ? r.text() : Promise.reject('cert ' + r.status);
                        })
                        .then(function(pem) {
                            __qzCertCache = pem;
                            resolve(pem);
                        })
                        .catch(function(e) {
                            console.warn('[QZ] Certificate fetch failed:', e);
                            reject(e);
                        });
                });
                qz.security.setSignatureAlgorithm('SHA512');
                qz.security.setSignaturePromise(function(toSign) {
                    return function(resolve, reject) {
                        var csrf = document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value;
                        fetch("{{ route('storepanel.qz.sign') }}", {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'text/plain',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    request: toSign
                                }),
                            })
                            .then(function(r) {
                                return r.ok ? r.text() : Promise.reject('sign ' + r.status);
                            })
                            .then(resolve)
                            .catch(function() {
                                resolve('');
                            });
                    };
                });
            }

        function checkFlow(file, trays, trayDims) {
            let _pdf = null;
            let _inited = false;

            return {
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

                qzChecking: true,
                qzReady: false,
                printers: [],
                selectedPrinter: null,
                printing: false,
                printMsg: null,
                printMsgKind: 'info',

                showQzModal: false,
                showPaperModal: false,
                printSize: '{{ array_key_first($sizeOptions) }}',
                printMedia: '{{ array_key_first($mediaOptions) }}',
                printGsm: '{{ array_key_first($gsmOptions) }}',
                sizeOptionsMap: @json($sizeOptions),
                mediaOptionsMap: @json($mediaOptions),
                gsmOptionsMap: @json($gsmOptions),

                async init() {
                    if (_inited) return;
                    _inited = true;
                    if (file.type === 'image') {
                        await this.initImage();
                    } else {
                        await this.initPdf();
                    }
                    this.initQz();
                },

                async initQz() {
                    if (!window.qz) {
                        this.qzChecking = false;
                        this.showQzModal = true;
                        return;
                    }
                    try {
                        try {
                            if (qz.websocket.isActive()) await qz.websocket.disconnect();
                        } catch (e) {}
                        await Promise.race([
                            qz.websocket.connect(),
                            new Promise((_, r) => setTimeout(() => r(new Error('timeout')), 5000))
                        ]);
                        this.qzReady = !!(qz.websocket.isActive());
                        if (this.qzReady) {
                            const list = await qz.printers.find();
                            const all = Array.isArray(list) ? list : [list].filter(Boolean);
                            const noritsu = all.filter(p => /noritsu/i.test(p));
                            const trayOrder = ['MP Tray', 'Tray 1', 'Tray 2', 'Tray 3', 'Tray 4', 'Tray 5'];
                            noritsu.sort((a, b) => {
                                const ai = trayOrder.findIndex(t => a.includes(t));
                                const bi = trayOrder.findIndex(t => b.includes(t));
                                return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi);
                            });
                            this.printers = noritsu;
                            if (this.printers.length > 0) this.selectedPrinter = this.printers[0];
                        }
                    } catch (e) {
                        console.warn('[localprint] QZ Tray connection failed:', e.message || e);
                        this.qzReady = false;
                    }
                    this.qzChecking = false;
                    if (!this.qzReady) this.showQzModal = true;
                },

                selectPrinter(p) {
                    if (this.selectedPrinter !== p) {
                        this.selectedPrinter = p;
                        this.showPaperModal = true;
                    }
                },

                applyPaperSettings() {
                    this.showPaperModal = false;
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

                        _pdf = await pdfjsLib.getDocument({
                            data: bytes
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

                        // Fonts: getTextContent surfaces font ids per page.
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
                    const canvas = this.$refs.pdfCanvas;
                    if (!canvas) return;
                    canvas.width = vp.width;
                    canvas.height = vp.height;
                    await pg.render({
                        canvasContext: canvas.getContext('2d'),
                        viewport: vp
                    }).promise;
                },

                nextPage() {
                    if (this.page < this.pages) {
                        this.page++;
                        this.renderPage(this.page);
                    }
                },
                prevPage() {
                    if (this.page > 1) {
                        this.page--;
                        this.renderPage(this.page);
                    }
                },

                // ── Derived state ──────────────────────────────

                get selectedTray() {
                    return this.trays.find(t => t.key === this.tray);
                },

                /** Physical dimensions of the media loaded in the selected tray. */
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

                /** Approximate trim size (subtract 0.25" of bleed on each axis if the file is oversized to media). */
                get trimSize() {
                    const m = this.trayMedia;
                    if (!this.dims) return null;
                    if (!m) return this.dims;
                    // Match orientation of media before comparing to describe trim size honestly.
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
                    // Heuristic: assume the file is 300 dpi at trim if dims match, degrading proportionally.
                    // Without a raster inspection we can only cap at a sane assumption.
                    if (!this.dims || !this.trayMedia) return {
                        key: 'res',
                        ok: false,
                        severity: 'warn',
                        title: 'Resolution',
                        detail: 'Chech the paper type.'
                    };
                    const df = this.trimSize;
                    // Effective dpi at the final print size (assuming the source is 300 dpi at file dims).
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
                    const list = [this.sizeCheck, this.resolutionCheck];
                    if (this.file.type === 'pdf') list.push(this.fontCheck);
                    return list;
                },

                get canPrint() {
                    if (!this.ready || this.pdfError) return false;
                    if (this.copies < 1) return false;
                    if (this.qzReady) return !!this.selectedPrinter;
                    if (!this.selectedTray || this.selectedTray.out) return false;
                    return true;
                },

                get blocker() {
                    if (!this.ready) return 'Reading file…';
                    if (this.pdfError) return 'Fix the file to continue';
                    if (this.qzChecking) return 'Connecting to QZ Tray…';
                    if (this.qzReady && !this.selectedPrinter) return 'Select a printer';
                    if (!this.qzReady && (!this.selectedTray || this.selectedTray.out)) return 'Choose a loaded tray';
                    if (!this.qzReady) return 'QZ Tray not connected';
                    return null;
                },

                async doPrint() {
                    if (!this.canPrint || this.printing) return;
                    this.printing = true;
                    this.printMsg = 'Sending to printer…';
                    this.printMsgKind = 'info';

                    try {
                        if (!qz.websocket.isActive()) await qz.websocket.connect();

                        const configOpts = {
                            copies: this.copies || 1
                        };
                        const dim = this.trayDims[this.printSize];
                        if (dim) {
                            configOpts.size = {
                                width: dim.w,
                                height: dim.h
                            };
                            configOpts.units = 'in';
                        }
                        const config = qz.configs.create(this.selectedPrinter, configOpts);

                        let data;
                        if (this.file.type === 'image') {
                            const ext = this.file.name.split('.').pop().toLowerCase();
                            const needsConvert = ['webp', 'tiff', 'tif'].includes(ext);
                            let b64;
                            if (needsConvert) {
                                b64 = await this.imageToBase64Png(this.file.url);
                            } else {
                                const res = await fetch(this.file.url, {
                                    credentials: 'same-origin'
                                });
                                b64 = this.arrayBufToBase64(await res.arrayBuffer());
                            }
                            data = [{
                                type: 'pixel',
                                format: 'image',
                                flavor: 'base64',
                                data: b64
                            }];
                        } else {
                            const res = await fetch(this.file.url, {
                                credentials: 'same-origin'
                            });
                            const b64 = this.arrayBufToBase64(await res.arrayBuffer());
                            data = [{
                                type: 'pixel',
                                format: 'pdf',
                                flavor: 'base64',
                                data: b64
                            }];
                        }

                        await qz.print(config, data);
                        this.printMsg = 'Sent to ' + this.selectedPrinter + '!';
                        this.printMsgKind = 'success';
                    } catch (e) {
                        console.error('[localprint] QZ print failed:', e);
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
