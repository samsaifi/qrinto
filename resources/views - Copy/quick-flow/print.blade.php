@php
    $item = $order->items->first();
    $dimW =
        $item?->customization_data['size_width'] ??
        ($item?->product?->width ?? ($item?->product?->productType?->width ?? '4'));
    $dimH =
        $item?->customization_data['size_height'] ??
        ($item?->product?->height ?? ($item?->product?->productType?->height ?? '6'));
    $dimensions = sprintf('%.2f x %.2f', (float) $dimW, (float) $dimH);
    $unit = $item?->customization_data['size_unit'] ?? 'inch';
    $sizeLabel = $item?->product?->size_label ?? $dimensions;
    $cardType = $item?->product?->productType?->name ?? ($item?->product?->title ?? 'Photo Magnets');
    $category = $item?->product?->category?->name ?? 'Empty canvas';

    $driverDownloadUrl =
        'https://www.dropbox.com/scl/fi/d5f74l6ekg7r3hoxhvhwm/Noritsu_931BL_Driver_Setup-v2.2.exe?rlkey=m94hgu9eww95zj7mr7wtpli30&st=e658g2hc&e=1&dl=1';

    $rPrefix = match (auth()->user()?->role ?? '') {
        'store_admin', 'storeadmin' => 'store.',
        'staff' => 'staff.',
        default => 'admin.',
    };
    $backUrl = route($rPrefix . 'orders.show', $order->id);

    $pdfUrl = !empty($item?->pdf_path)
        ? asset('storage/' . $item->pdf_path)
        : route($rPrefix . 'orders.show.pdf', $order->id);
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Station - Order #{{ $order->order_number }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    {{-- QZ Tray: enumerates the OPERATOR'S PC printers (not the server's), so
         localhost + production show the same printer list on the same PC. --}}
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#00a651',
                            700: '#15803d',
                        },
                        surface: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .print-only-stage {
                display: flex !important;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: {{ (float) $dimW }}in;
                height: {{ (float) $dimH }}in;
                margin: 0 auto;
                padding: 0;
            }

            .print-only-stage img {
                width: {{ (float) $dimW }}in;
                height: {{ (float) $dimH }}in;
                object-fit: contain;
            }

            .print-page-break {
                page-break-after: always;
                break-after: page;
                width: {{ (float) $dimW }}in;
                height: {{ (float) $dimH }}in;
            }

            @page {
                size: {{ (float) $dimW }}in {{ (float) $dimH }}in;
                margin: 0mm;
            }
        }
    </style>
</head>

<body class="bg-surface-900 text-white min-h-full flex flex-col antialiased">

    {{-- Top Bar --}}
    <header
        class="no-print bg-surface-800/80 backdrop-blur-md border-b border-surface-700/60 px-6 py-3.5 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ $backUrl }}"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-surface-700/70 hover:bg-surface-700 text-surface-200 hover:text-white text-xs font-semibold transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Order
            </a>
            <div class="h-4 w-px bg-surface-700"></div>
            <div>
                <h1 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>Print Design Station</span>
                    <span
                        class="text-xs font-mono font-normal text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded-md border border-brand-500/20">
                        #{{ $order->order_number }}
                    </span>
                </h1>
            </div>
        </div>

        <div></div>
    </header>

    {{-- Main Split View --}}
    <main class="no-print flex-1 flex flex-col md:flex-row overflow-hidden">

        {{-- Left Column: Print Design Preview --}}
        <div
            class="flex-1 bg-surface-950 p-6 flex flex-col items-center justify-center relative overflow-auto min-h-[400px]">
            <div
                class="absolute top-4 left-4 z-10 flex items-center gap-2 bg-surface-900/80 backdrop-blur px-3 py-1.5 rounded-xl border border-surface-800 text-xs text-surface-400">
                <i data-lucide="eye" class="w-3.5 h-3.5 text-brand-500"></i> Print Preview Stage
            </div>

            @php
                $imgCount = count($designImages ?? []);
                $getImgTitle = function ($idx, $count) {
                    if ($count === 4) {
                        return match ($idx) {
                            0 => 'Inside',
                            1 => 'Inside',
                            2 => 'Front Cover',
                            3 => 'End Cover',
                            default => 'Image ' . ($idx + 1),
                        };
                    }
                    return 'Image ' . ($idx + 1) . ' of ' . $count;
                };
            @endphp

            @if ($imgCount > 0)
                <div
                    class="w-full max-w-4xl max-h-[75vh] overflow-y-auto p-4 bg-surface-900/50 rounded-2xl border border-surface-800 shadow-2xl">
                    <div
                        class="grid gap-4 items-center justify-center 
                        {{ $imgCount === 1 ? 'grid-cols-1 max-w-xl mx-auto' : '' }}
                        {{ $imgCount === 2 ? 'grid-cols-1 sm:grid-cols-2 max-w-3xl mx-auto' : '' }}
                        {{ $imgCount === 3 ? 'grid-cols-1 sm:grid-cols-3' : '' }}
                        {{ $imgCount >= 4 ? 'grid-cols-2 sm:grid-cols-2 lg:grid-cols-4' : '' }}">
                        @foreach ($designImages as $idx => $imgUrl)
                            <div
                                class="relative group bg-surface-950/80 p-2 rounded-xl border border-surface-800/80 flex flex-col items-center justify-center">
                                <img src="{{ $imgUrl }}" alt="{{ $getImgTitle($idx, $imgCount) }}"
                                    class="max-w-full {{ $imgCount === 1 ? 'max-h-[65vh]' : 'max-h-[35vh]' }} object-contain rounded-lg shadow-md transition-all duration-300">
                                <span
                                    class="mt-2 text-xs font-bold text-surface-200 bg-surface-800/90 px-3 py-1 rounded-lg border border-surface-700/60 shadow-xs">
                                    {{ $getImgTitle($idx, $imgCount) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="py-20 text-center text-surface-500">
                    <i data-lucide="image-off" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
                    <p class="text-sm font-semibold">No design image found for this order.</p>
                </div>
            @endif

            <div class="mt-4 text-xs text-surface-500 flex items-center gap-4">
                <span><strong class="text-surface-400">Order ID:</strong> {{ $order->id }}</span>
                <span>•</span>
                <span><strong class="text-surface-400">Store:</strong>
                    {{ $order->store?->store_name ?? 'Main Studio' }}</span>
            </div>
        </div>

        {{-- Right Column: Print Details & Destination Window --}}
        <aside
            class="w-full md:w-[420px] bg-surface-900 border-l border-surface-800 p-6 flex flex-col justify-between overflow-y-auto shrink-0">

            <div class="space-y-6">

                {{-- Metadata Window Card --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-surface-400 flex items-center gap-2">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-brand-500"></i> Design Specifications
                        </h2>
                        <span
                            class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-surface-800 text-brand-400 border border-surface-700">
                            Verified
                        </span>
                    </div>

                    <div class="bg-surface-800/60 rounded-2xl border border-surface-700/60 p-4 space-y-3 shadow-inner">
                        <div class="flex justify-between items-center text-xs pb-2.5 border-b border-surface-700/40">
                            <span class="text-surface-400">Dimensions:</span>
                            <span
                                class="font-bold text-white font-mono bg-surface-800 px-2 py-0.5 rounded">{{ $dimensions }}</span>
                        </div>

                        <div class="flex justify-between items-center text-xs pb-2.5 border-b border-surface-700/40">
                            <span class="text-surface-400">Unit:</span>
                            <span
                                class="font-bold text-white uppercase font-mono bg-surface-800 px-2 py-0.5 rounded">{{ $unit }}</span>
                        </div>

                        <div class="flex justify-between items-center text-xs pb-2.5 border-b border-surface-700/40">
                            <span class="text-surface-400">Size label:</span>
                            <span
                                class="font-bold text-white font-mono bg-surface-800 px-2 py-0.5 rounded">{{ $sizeLabel }}</span>
                        </div>

                        <div class="flex justify-between items-center text-xs pb-2.5 border-b border-surface-700/40">
                            <span class="text-surface-400">Card Type:</span>
                            <span class="font-bold text-brand-400">{{ $cardType }}</span>
                        </div>

                        <div class="flex justify-between items-center text-xs">
                            <span class="text-surface-400">Category:</span>
                            <span class="font-medium text-surface-300">{{ $category }}</span>
                        </div>
                    </div>
                </div>

                {{-- Printer Destination Window --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-surface-400 flex items-center gap-2">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-brand-500"></i> Destination Printer
                        </h2>
                        <span id="printerStatusBadge"
                            class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            Scanning...
                        </span>
                    </div>

                    {{-- Detected Noritsu Printers Container --}}
                    <div id="noritsuPrinterContainer" class="space-y-2">
                        {{-- Populated by JavaScript or fallback --}}
                    </div>

                    {{-- Driver Download Box (If Noritsu printer not found) --}}
                    <div id="driverDownloadCard"
                        class="hidden mt-4 bg-amber-950/30 border border-amber-500/30 rounded-2xl p-4 text-left">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-xl bg-amber-500/10 text-amber-400 shrink-0 mt-0.5">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1 flex-1">
                                <h4 class="text-xs font-bold text-amber-300">Noritsu 931BL Printer Not Found</h4>
                                <p class="text-[11px] text-amber-200/70 leading-relaxed">
                                    No local Noritsu 931BL printer was detected on this PC. Please install the driver to
                                    continue.
                                </p>
                                <div class="pt-2">
                                    <a href="{{ $driverDownloadUrl }}" target="_blank"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-surface-950 font-bold text-xs transition shadow-md">
                                        <i data-lucide="download" class="w-4 h-4"></i> Download Noritsu 931BL Driver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Action Buttons --}}
            <div class="pt-6 border-t border-surface-800 space-y-2.5">
                {{-- QZ Tray install hint - shown only when the printer scan
                     came back empty (no PC printers visible). --}}
                <div id="qzInstallHint"
                    class="hidden bg-amber-500/10 border border-amber-500/30 rounded-xl px-3 py-2.5 flex items-center gap-2.5">
                    <i data-lucide="printer-off" class="w-4 h-4 text-amber-400 shrink-0"></i>
                    <p class="text-[11px] text-amber-200/90 leading-snug flex-1">
                        Printer not visible? Install QZ Tray to detect this PC's printers.
                    </p>
                    <a href="https://qz.io/download/" target="_blank" rel="noopener"
                        class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-surface-950 text-[11px] font-bold transition">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        Download QZ Tray
                    </a>
                </div>

                <button onclick="sendToNoritsuPrinter()"
                    class="w-full py-3 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm flex items-center justify-center gap-2 transition shadow-lg shadow-brand-600/20 active:scale-[0.98]">
                    <i data-lucide="printer" class="w-4 h-4"></i> Send to Noritsu Printer
                </button>
                <button onclick="openManualBrowserPrint()"
                    class="w-full py-2 px-3 rounded-xl bg-surface-800/60 hover:bg-surface-800 text-surface-400 hover:text-surface-200 text-[11px] font-medium transition flex items-center justify-center gap-1.5">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Open Browser Print Dialog
                </button>
                <a href="{{ $backUrl }}"
                    class="w-full py-2.5 px-4 rounded-xl bg-surface-800 hover:bg-surface-700 text-surface-300 hover:text-white font-medium text-xs flex items-center justify-center gap-2 transition">
                    Cancel & Return
                </a>
            </div>

        </aside>
    </main>

    {{-- Printable Stage (Full Screen during browser print) --}}
    <div class="print-only-stage hidden">
        @if (!empty($designImages))
            @foreach ($designImages as $imgUrl)
                <div class="print-page-break">
                    <img src="{{ $imgUrl }}" alt="Print Design">
                </div>
            @endforeach
        @endif
    </div>

    <script>
        lucide.createIcons();

        const serverHasNoritsu = @json((bool) $hasNoritsuPrinter);
        const serverNoritsuPrinters = @json($noritsuPrinters ?? []);
        // Full list of every installed printer/driver + connection status (from Windows spooler).
        const serverSystemPrinters = @json($systemPrinters ?? []);

        const defaultTrays = [{
                id: 'tray-5',
                name: 'Noritsu 931BL (Tray 5)',
                default: false
            },
            {
                id: 'tray-4',
                name: 'Noritsu 931BL (Tray 4)',
                default: false
            },
            {
                id: 'tray-3',
                name: 'Noritsu 931BL (Tray 3)',
                default: false
            },
            {
                id: 'tray-2',
                name: 'Noritsu 931BL (Tray 2)',
                default: false
            },
            {
                id: 'tray-1',
                name: 'Noritsu 931BL (Tray 1)',
                default: true
            },
            {
                id: 'tray-mp',
                name: 'Noritsu 931BL (MP Tray)',
                default: false
            }
        ];

        let selectedPrinter = 'Noritsu 931BL (Tray 1)';

        async function getConnectedPrinters() {
            let printers = [];
            try {
                if (typeof window.queryLocalPrinters === 'function') {
                    const list = await window.queryLocalPrinters();
                    printers = list.map(p => p.name || p.printerName || p.id || '');
                } else if (navigator.printers && typeof navigator.printers.getPrinters === 'function') {
                    const list = await navigator.printers.getPrinters();
                    printers = list.map(p => p.name || p || '');
                }
            } catch (e) {
                console.warn('Printer detection error:', e);
            }
            return printers;
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, s => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [s]));
        }

        /**
         * Populate the destination printer list from QZ Tray (browser-side -
         * reflects the operator's own PC). Falls back to the server-side
         * PowerShell list only if QZ Tray isn't installed / running, and
         * finally to the hard-coded Noritsu tray labels.
         */
        async function loadPrintersFromQz() {
            if (!window.qz) return null;
            try {
                if (!qz.websocket.isActive()) {
                    await qz.websocket.connect();
                }
                const [names, defaultPrinter] = await Promise.all([
                    qz.printers.find(),
                    qz.printers.getDefault().catch(() => null),
                ]);
                if (!names || !names.length) return null;
                return names.map(name => {
                    const isNoritsu = /noritsu\s*931bl/i.test(name);
                    return {
                        name,
                        driver: isNoritsu ? 'Noritsu 931BL' : name,
                        port: '',
                        connected: true, // QZ Tray only lists queues the OS can reach
                        status: name === defaultPrinter ? 'Default · Connected' : 'Connected',
                    };
                });
            } catch (e) {
                console.warn('[print-page] QZ Tray unavailable:', e && e.message);
                return null;
            }
        }

        async function detectPrinters() {
            const container = document.getElementById('noritsuPrinterContainer');
            const badge = document.getElementById('printerStatusBadge');
            const driverCard = document.getElementById('driverDownloadCard');
            const qzHint = document.getElementById('qzInstallHint');

            // 1) QZ Tray - the correct source (operator's own PC).
            let printers = await loadPrintersFromQz();
            const qzSawPrinters = Array.isArray(printers) && printers.length > 0;
            if (qzHint) qzHint.classList.toggle('hidden', qzSawPrinters);

            // 2) Server-side PowerShell fallback - only if QZ Tray is offline.
            if (!printers) {
                printers = (serverSystemPrinters || []).map(p => ({
                    name: p.name || '',
                    driver: p.driver || '',
                    port: p.port || '',
                    connected: !!p.connected,
                    status: p.status || (p.connected ? 'Connected' : 'Not connected'),
                })).filter(p => p.name);
            }

            // 3) Last resort: the hard-coded default Noritsu tray labels.
            if (!printers || printers.length === 0) {
                printers = defaultTrays.map(t => ({
                    name: t.name,
                    driver: 'Noritsu 931BL',
                    port: '',
                    connected: false,
                    status: 'Unknown'
                }));
            }

            const hasNoritsuPrinter = serverHasNoritsu ||
                printers.some(p => /noritsu\s*931bl/i.test(p.name));

            // Header badge summarises overall state.
            const connectedCount = printers.filter(p => p.connected).length;
            if (badge) {
                if (connectedCount > 0) {
                    badge.className =
                        'text-[10px] font-bold px-2 py-0.5 rounded bg-brand-500/10 text-brand-400 border border-brand-500/20';
                    badge.innerText = connectedCount + ' Connected';
                } else if (printers.length > 0) {
                    badge.className =
                        'text-[10px] font-bold px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20';
                    badge.innerText = printers.length + ' Installed';
                } else {
                    badge.className =
                        'text-[10px] font-bold px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20';
                    badge.innerText = 'No Printers';
                }
            }

            // Driver-missing card only when no Noritsu driver exists at all.
            if (driverCard) driverCard.classList.toggle('hidden', hasNoritsuPrinter);

            // Preselect: first connected Noritsu → first connected → first in list.
            let defaultIdx = printers.findIndex(p => p.connected && /noritsu\s*931bl/i.test(p.name));
            if (defaultIdx < 0) defaultIdx = printers.findIndex(p => p.connected);
            if (defaultIdx < 0) defaultIdx = 0;
            selectedPrinter = printers[defaultIdx].name;

            let html = '<div class="space-y-1.5 max-h-[300px] overflow-y-auto pr-1">';
            printers.forEach((p, idx) => {
                const isChecked = idx === defaultIdx ? 'checked' : '';
                const name = escapeHtml(p.name);
                const driver = escapeHtml(p.driver || p.name);
                const statusText = escapeHtml(p.status || (p.connected ? 'Connected' : 'Not connected'));
                let dotColor, textColor;
                if (p.connected) {
                    dotColor = 'bg-brand-500';
                    textColor = 'text-brand-400';
                } else if (p.status === 'Driver only') {
                    dotColor = 'bg-surface-500';
                    textColor = 'text-surface-400';
                } else {
                    dotColor = 'bg-rose-500';
                    textColor = 'text-rose-400';
                }
                const statusBadge =
                    `<span class="inline-flex items-center gap-1 text-[10px] font-bold ${textColor}"><span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span>${statusText}</span>`;
                html += `
                    <label class="flex items-center justify-between gap-3 p-2.5 rounded-xl border border-surface-700/60 bg-surface-800/40 hover:bg-surface-800/80 cursor-pointer transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <input type="radio" name="noritsu_destination" value="${name}" ${isChecked}
                                   onchange="selectedPrinter = this.value"
                                   class="w-4 h-4 shrink-0 text-brand-600 bg-surface-900 border-surface-600 focus:ring-brand-500">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="printer" class="w-4 h-4 text-surface-400 shrink-0"></i>
                                    <span class="text-xs font-semibold text-white truncate">${name}</span>
                                </div>
                                <p class="text-[10px] text-surface-500 truncate pl-6">${driver}</p>
                            </div>
                        </div>
                        <div class="shrink-0">${statusBadge}</div>
                    </label>
                `;
            });
            html += '</div>';
            if (container) container.innerHTML = html;

            lucide.createIcons();
        }

        const pdfUrl = "{{ $pdfUrl }}";
        const pdfWidth = "{{ $dimW }}";
        const pdfHeight = "{{ $dimH }}";
        const pdfUnit = "{{ $unit }}";
        const directPrintUrl = "{{ route($rPrefix . 'orders.print.direct', $order->id) }}";
        const csrfToken = "{{ csrf_token() }}";
        const orderItemId = "{{ $item?->id }}";

        function getSelectedPrinterName() {
            const checked = document.querySelector('input[name="noritsu_destination"]:checked');
            if (checked && checked.value) {
                return checked.value;
            }
            return selectedPrinter;
        }

        /**
         * Silent QZ Tray pre-flight - returns true only when the local QZ
         * Tray websocket accepts a connection (i.e. the desktop app is
         * installed and running on the operator's PC).
         */
        // Unsigned-cert mode: without these stubs, qz.websocket.connect() hangs
        // waiting for signature callbacks even after the socket is up (that's
        // what the "Failed to get certificate: undefined" warning means).
        if (window.qz && qz.security) {
            qz.security.setCertificatePromise(function(resolve) {
                resolve();
            });
            qz.security.setSignatureAlgorithm && qz.security.setSignatureAlgorithm('SHA512');
            qz.security.setSignaturePromise(function() {
                return function(resolve) {
                    resolve('');
                };
            });
        }

        async function sendToNoritsuPrinter() {
            const activePrinter = getSelectedPrinterName();
            const badge = document.getElementById('printerStatusBadge');
            if (badge) {
                badge.className =
                    'text-[10px] font-bold px-2 py-0.5 rounded bg-brand-500/20 text-brand-300 border border-brand-500/40 animate-pulse';
                badge.innerText = 'Sending to ' + activePrinter + ' (' + pdfWidth + 'x' + pdfHeight + ' ' + pdfUnit +
                    ')...';
            }

            try {
                // Send direct print request to Windows print spooler for activePrinter
                const response = await fetch(directPrintUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        printer_name: activePrinter,
                        item_id: orderItemId
                    })
                });
                const data = await response.json();
                console.log('Direct print server response:', data);

                if (badge) {
                    badge.className =
                        'text-[10px] font-bold px-2 py-0.5 rounded bg-brand-500/20 text-brand-400 border border-brand-500/40';
                    badge.innerText = '✓ Direct Sent to ' + activePrinter;
                }
                alert('✓ Print job sent directly to Windows printer queue: ' + activePrinter +
                    '!\n(Browser print screen bypassed)');
            } catch (err) {
                console.warn('Direct print request error:', err);
                alert('Could not dispatch print job to ' + activePrinter + '.');
            }
        }

        function openManualBrowserPrint() {
            let iframe = document.getElementById('noritsuPdfPrintIframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'noritsuPdfPrintIframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '-9999px';
                iframe.style.bottom = '-9999px';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);
            }

            iframe.onload = function() {
                setTimeout(function() {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        window.print();
                    }
                }, 500);
            };

            iframe.src = pdfUrl;
        }

        // Initialize printer check on page load
        document.addEventListener('DOMContentLoaded', detectPrinters);
    </script>
</body>

</html>
