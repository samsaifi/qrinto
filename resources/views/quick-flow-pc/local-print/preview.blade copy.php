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

                        {{-- QZ Tray status --}}
                        <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-lg text-[12px]"
                            :class="(qzReady && printers.length > 0) ? 'bg-emerald-50 text-emerald-700' : (qzChecking ?
                                'bg-slate-50 text-slate-500' : 'bg-red-50 text-red-600')">
                            <template x-if="qzChecking">
                                <span>
                                    <div
                                        class="w-3.5 h-3.5 border-2 border-slate-300 border-t-slate-600 rounded-full animate-spin inline-block">
                                    </div>
                                    Connecting to QZ Tray…
                                </span>
                            </template>
                            <template x-if="!qzChecking && qzReady && printers.length > 0">
                                <span><i data-lucide="check-circle" class="w-3.5 h-3.5 inline -mt-0.5"></i> QZ Tray
                                    connected</span>
                            </template>
                            <template x-if="!qzChecking && qzReady && printers.length === 0">
                                <span class="flex items-center gap-1.5 w-full">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span class="flex-1">No printers found</span>
                                    <button type="button" @click="showQzModal = true"
                                        class="font-bold underline hover:no-underline shrink-0">Fix this</button>
                                </span>
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
                                    <template x-if="qzReady && printers.length === 0">
                                        <div>
                                            <p class="text-sm text-slate-700 font-medium">No Noritsu printers found</p>
                                            <p class="text-[12px] text-slate-500 mt-2 leading-relaxed">
                                                QZ Tray is connected but no printers were detected. Make sure your Noritsu
                                                printer is turned on, connected to this PC, and the driver is installed.
                                            </p>
                                        </div>
                                    </template>
                                    <template x-if="!qzReady">
                                        <div>
                                            <p class="text-sm text-slate-700 font-medium">QZ Tray is required to connect to
                                                your local printer.</p>
                                            <p class="text-[12px] text-slate-500 mt-2 leading-relaxed">
                                                Download and install QZ Tray, then reload this page. It runs in the
                                                background and lets your browser communicate with printers on this PC.
                                            </p>
                                        </div>
                                    </template>
                                </div>

                                <div class="space-y-2 mt-4">
                                    <a href="https://qz.io/download/" target="_blank"
                                        class="w-full flex items-center justify-center gap-2 bg-[#287d3c] hover:bg-emerald-800 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                                        <i data-lucide="download" class="w-4 h-4"></i> Download QZ Tray
                                    </a>
                                    <a href="https://www.dropbox.com/scl/fi/d5f74l6ekg7r3hoxhvhwm/Noritsu_931BL_Driver_Setup-v2.2.exe?rlkey=m94hgu9eww95zj7mr7wtpli30&st=e658g2hc&e=1&dl=1"
                                        target="_blank"
                                        class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                                        <i data-lucide="download" class="w-4 h-4"></i> Download 931-BL Multi Driver
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
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>

    <script>
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
                slide: 0,
                touchX: 0,
                copies: 1,

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

                init() {
                    this.initQz();
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

                // ── Checks (like check.blade.php) ──
                get checks() {
                    const list = [];

                    // PDF generated check
                    list.push({
                        key: 'pdf',
                        ok: !!pdfUrl,
                        severity: pdfUrl ? 'ok' : 'error',
                        title: pdfUrl ? 'PDF generated' : 'PDF not generated',
                        detail: pdfUrl ? totalPages + ' page' + (totalPages === 1 ? '' : 's') +
                            ' ready to print.' : 'Something went wrong generating the PDF.',
                    });

                    // Size check
                    if (designSize.w && designSize.h) {
                        const dim = trayDims[this.printSize];
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

                    // Orientation check
                    if (designSize.w && designSize.h) {
                        const dim = trayDims[this.printSize];
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

                // ── QZ Tray ──
                async initQz() {
                    if (!window.qz) {
                        this.qzChecking = false;
                        return;
                    }
                    try {
                        if (!qz.websocket.isActive()) {
                            await Promise.race([
                                qz.websocket.connect(),
                                new Promise((_, r) => setTimeout(() => r(new Error('timeout')), 5000))
                            ]);
                        }
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

                get canPrint() {
                    if (this.copies < 1) return false;
                    if (!pdfUrl) return false;
                    return this.qzReady && !!this.selectedPrinter;
                },

                get blocker() {
                    if (!pdfUrl) return 'No PDF generated';
                    if (this.qzChecking) return 'Connecting to QZ Tray…';
                    if (!this.qzReady) return 'QZ Tray not connected';
                    if (!this.selectedPrinter) return 'Select a printer';
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
                        const dim = trayDims[this.printSize];
                        if (dim) {
                            configOpts.size = {
                                width: dim.w,
                                height: dim.h
                            };
                            configOpts.units = 'in';
                        }
                        const config = qz.configs.create(this.selectedPrinter, configOpts);

                        const res = await fetch(pdfUrl, {
                            credentials: 'same-origin'
                        });
                        const b64 = this.arrayBufToBase64(await res.arrayBuffer());
                        const data = [{
                            type: 'pixel',
                            format: 'pdf',
                            flavor: 'base64',
                            data: b64
                        }];

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
