<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Store Panel') · Qrinto</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/svg-logo/Q-only.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #f4f6f3;
            color: #16211a;
            font-family: 'Inter', system-ui, sans-serif;
        }

        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }

        .mono {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
        }

        [x-cloak] {
            display: none !important
        }

        .navlink {
            font-size: 14px;
            font-weight: 500;
            color: #5f6b60;
            padding: 6px 12px;
            border-radius: 9px;
            transition: all .15s;
        }

        .navlink:hover {
            color: #16211a;
            background: #eef1ec;
        }

        .navlink.active {
            color: #16532a;
            background: #e7f2e8;
        }
    </style>
    @stack('styles')
    {{-- pdf-lib: used to rotate a server-generated order PDF client-side so the
         operator's Portrait/Landscape choice is honoured (SumatraPDF on the
         bridge does NOT rotate PDF content for a `landscape` flag, but it DOES
         honour a page's own /Rotate, which pdf-lib sets). --}}
    <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
    {{-- Local-print audit log (same system as /print/pdf/check): records every
         print attempt AND saves the exact print-ready PDF to the log row. --}}
    <script src="{{ asset('js/local-print-log.js') }}?v={{ filemtime(public_path('js/local-print-log.js')) }}"></script>
    <script>
        // Full route URLs so LPLog works under a sub-path (e.g. /qrinto).
        window.LPLOG_ROUTES = {
            start: @json(route('localprint.log.start')),
            completeBase: @json(url('print/log')),
        };
    </script>
</head>

<body class="min-h-screen antialiased">
    @php
        $store = $store ?? (auth()->user()->store ?? null);
        $storeLabel = $store ? $store->store_name . ($store->city ? ', ' . $store->city : '') : 'Store';
    @endphp

    {{-- Top bar --}}
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40" x-data="{ mobileMenu: false }">
        <div class="max-w-6xl mx-auto px-4 md:px-6">
            <div class="h-14 md:h-16 flex items-center justify-between gap-4 md:gap-6">
                <div class="flex items-center gap-4 md:gap-8 min-w-0">
                    <a href="{{ url('store') }}" class="flex items-center gap-2.5 shrink-0">
                        <img src="{{ asset('logo/Qrinto-logo-small.png') }}" alt="Qrinto" class="h-7 md:h-8 w-auto">
                    </a>
                    <span
                        class="font-display font-bold text-sm text-slate-800 truncate hidden sm:block">{{ $storeLabel }}</span>
                </div>

                {{-- Mobile hamburger --}}
                <button type="button" @click="mobileMenu = !mobileMenu"
                    class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 transition">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenu"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileMenu" x-cloak></i>
                </button>

                {{-- Desktop nav --}}
                <nav class="hidden md:flex items-center gap-1">
                    {{-- Catalog Dropdown (hidden for now)
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                            class="navlink flex items-center gap-1.5 {{ request()->is('*products*') || request()->is('*categories*') || request()->is('*product-types*') || request()->is('*templates*') || request()->is('*coupons*') || request()->is('*events*') ? 'active' : '' }}">
                            <span>Catalog</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute left-0 mt-2 w-52 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-50 focus:outline-none"
                            x-cloak>
                            <a href="{{ url('/store/products') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#287d3c] transition">
                                <i data-lucide="box" class="w-4 h-4 text-emerald-600"></i>
                                Products
                            </a>
                            <a href="{{ url('/store/categories') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#287d3c] transition">
                                <i data-lucide="grid-2x2" class="w-4 h-4 text-emerald-600"></i>
                                Categories
                            </a>
                            <a href="{{ url('/store/product-types') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#287d3c] transition">
                                <i data-lucide="layers" class="w-4 h-4 text-emerald-600"></i>
                                Card Types/Sizes
                            </a>
                            <a href="{{ url('/store/templates') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#287d3c] transition">
                                <i data-lucide="layout-template" class="w-4 h-4 text-emerald-600"></i>
                                Templates
                            </a>
                            <a href="{{ url('/store/coupons') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#287d3c] transition">
                                <i data-lucide="tag" class="w-4 h-4 text-emerald-600"></i>
                                Coupons
                            </a>
                            <a href="{{ url('/store/events') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-[#287d3c] transition">
                                <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i>
                                Events
                            </a>
                        </div>
                    </div>
                    --}}

                    <a href="{{ route('storepanel.orders') }}"
                        class="navlink {{ request()->routeIs('storepanel.orders') || request()->routeIs('storepanel.home') ? 'active' : '' }}">Orders</a>
                    <a href="{{ route('storepanel.qr') }}"
                        class="navlink {{ request()->routeIs('storepanel.qr') ? 'active' : '' }}">Store QR</a>
                    <a href="{{ route('storepanel.trays') }}"
                        class="navlink {{ request()->routeIs('storepanel.trays') ? 'active' : '' }}">Trays</a>
                    <form method="POST" action="{{ route('logout') }}" class="ml-4">
                        @csrf
                        <button type="submit"
                            class="text-sm font-medium text-slate-500 hover:text-slate-800 transition">Sign out</button>
                    </form>
                </nav>
            </div>
        </div>

        {{-- Mobile dropdown menu --}}
        <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden border-t border-slate-100 bg-white shadow-lg" x-cloak @click.outside="mobileMenu = false">
            <div class="max-w-6xl mx-auto px-4 py-3 space-y-1">

                <a href="{{ route('storepanel.orders') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('storepanel.orders') || request()->routeIs('storepanel.home') ? 'text-[#16532a] bg-[#e7f2e8]' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="package" class="w-4 h-4"></i> Orders
                </a>
                <a href="{{ route('storepanel.qr') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('storepanel.qr') ? 'text-[#16532a] bg-[#e7f2e8]' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="qr-code" class="w-4 h-4"></i> Store QR
                </a>
                <a href="{{ route('storepanel.trays') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('storepanel.trays') ? 'text-[#16532a] bg-[#e7f2e8]' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="inbox" class="w-4 h-4"></i> Trays
                </a>

                {{-- Catalog section (hidden for now)
                <div x-data="{ catalogOpen: false }">
                    <button @click="catalogOpen = !catalogOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->is('*products*') || request()->is('*categories*') || request()->is('*product-types*') || request()->is('*templates*') || request()->is('*coupons*') || request()->is('*events*') ? 'text-[#16532a] bg-[#e7f2e8]' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="flex items-center gap-3"><i data-lucide="layout-grid" class="w-4 h-4"></i> Catalog</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': catalogOpen }"></i>
                    </button>
                    <div x-show="catalogOpen" x-transition class="ml-7 mt-1 space-y-0.5" x-cloak>
                        <a href="{{ url('/store/products') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-[#287d3c] transition">
                            <i data-lucide="box" class="w-3.5 h-3.5 text-emerald-600"></i> Products
                        </a>
                        <a href="{{ url('/store/categories') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-[#287d3c] transition">
                            <i data-lucide="grid-2x2" class="w-3.5 h-3.5 text-emerald-600"></i> Categories
                        </a>
                        <a href="{{ url('/store/product-types') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-[#287d3c] transition">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-emerald-600"></i> Card Types/Sizes
                        </a>
                        <a href="{{ url('/store/templates') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-[#287d3c] transition">
                            <i data-lucide="layout-template" class="w-3.5 h-3.5 text-emerald-600"></i> Templates
                        </a>
                        <a href="{{ url('/store/coupons') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-[#287d3c] transition">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-emerald-600"></i> Coupons
                        </a>
                        <a href="{{ url('/store/events') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-slate-600 hover:bg-slate-50 hover:text-[#287d3c] transition">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600"></i> Events
                        </a>
                    </div>
                </div>
                -->

                {{-- Print & Kiosk logs --}}
                <a href="{{ route('storepanel.printLogs') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print Logs
                </a>
                <a href="{{ route('storepanel.kioskLogs') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Kiosk Logs
                </a>

                <div class="border-t border-slate-100 pt-2 mt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition w-full">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 md:px-6 py-4 md:py-8">
        <div id="qz-toast" x-data="{ open: false, msg: '', kind: 'info' }" x-show="open" x-transition x-cloak
            @qz-toast.window="msg = $event.detail.msg; kind = $event.detail.kind || 'info'; open = true; setTimeout(() => open = false, 4200)"
            class="fixed top-4 right-4 z-[70] max-w-sm px-4 py-3 rounded-xl shadow-lg text-sm font-medium border"
            :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-800': kind === 'success',
                'bg-red-50 border-red-200 text-red-800': kind === 'error',
                'bg-slate-900 border-slate-800 text-white': kind === 'info',
            }"
            x-text="msg"></div>
        @yield('content')
    </main>

    {{-- PrintTrays: local bridge to the Windows print system. --}}
    <script>
        if (window.lucide) lucide.createIcons();

        window.__ptBridge = null;

        window.__qrintoPT = (function() {
            function toast(msg, kind) {
                window.dispatchEvent(new CustomEvent('qz-toast', {
                    detail: {
                        msg,
                        kind: kind || 'info'
                    }
                }));
            }

            async function connect() {
                if (window.__ptBridge) return window.__ptBridge;
                const {
                    PrintTrays
                } = await import('{{ asset('js/printtrays.js') }}');
                const pp = new PrintTrays({
                    downloadUrl: 'https://noritsucanada.com/print-trays/download/',
                    onNotInstalled: () => {},
                });
                await pp.connect();
                window.__ptBridge = pp;
                return pp;
            }

            async function ensureReady() {
                try {
                    await connect();
                    return true;
                } catch (e) {
                    return false;
                }
            }

            function logPrint(payload, chosen, status, extra) {
                if (!payload || !payload.log_url) return;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ||
                    document.querySelector('input[name="_token"]')?.value;
                const body = {
                    printer_name: chosen?.printer || null,
                    tray_key: chosen?.tray_key || null,
                    tray_label: chosen?.label || null,
                    size: chosen?.size || null,
                    media: chosen?.media || null,
                    gsm: chosen?.gsm || null,
                    user_type: chosen?.user_type || null,
                    is_default_printer: !!(extra && extra.is_default_printer),
                    copies: (payload.copies || 1),
                    order_item_id: payload.order?.item_id || null,
                    status: status,
                    error_message: (extra && extra.error_message) || null,
                    duration_ms: (extra && extra.duration_ms) || null,
                    qz_tray_version: (window.__ptBridge && window.__ptBridge.version) || null,
                };
                try {
                    fetch(payload.log_url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify(body),
                        keepalive: true,
                    }).catch(function() {});
                } catch (e) {}
            }

            // Rebuild the order PDF so every page IS the tray's media size, in the
            // chosen orientation, with the design cover-filled (scaled to cover,
            // rotated 90° when its orientation differs). This does two things at
            // once that the bridge/SumatraPDF can't:
            //   1. Orientation — SumatraPDF won't rotate PDF content for a
            //      `landscape` flag, so we bake it into the page here.
            //   2. Edge-to-edge — the raw design PDF is the DESIGN size (e.g. 5×7);
            //      on a larger sheet SumatraPDF would centre it small with margins.
            //      Cover-filling onto the media size makes it print edge-to-edge.
            // mediaWin/mediaHin are the tray's paper size in inches. Best-effort:
            // returns the original bytes on any failure so a print is never blocked.
            // ── ROTATION-ONLY (lightweight, default print path) ──
            // Rotates each page 90° ONLY when its orientation doesn't match the
            // desired print orientation. No re-embed, no scaling, no size change —
            // pdf-lib's setRotation just flips the page's rotation flag, so a 7×10
            // design PDF stays a 7×10 PDF (its printed footprint becomes 10×7). This
            // fixes the fact that the bridge/SumatraPDF won't rotate PDF content for
            // a `landscape` flag, WITHOUT rebuilding the server PDF or adding margins.
            // Best-effort: returns the original bytes on failure or when no change.
            async function rotatePdfToOrientation(bytes, wantLandscape) {
                try {
                    if (!window.PDFLib) return bytes;
                    const {
                        PDFDocument,
                        degrees
                    } = window.PDFLib;
                    const src = await PDFDocument.load(bytes);
                    const pages = src.getPages();
                    let changed = false;
                    for (const page of pages) {
                        const {
                            width,
                            height
                        } = page.getSize();
                        const isLandscape = width > height;
                        if (isLandscape === wantLandscape) continue; // already correct
                        const cur = page.getRotation().angle || 0;
                        page.setRotation(degrees((cur - 90 + 360) % 360)); // -90° (clockwise)
                        changed = true;
                    }
                    if (!changed) return bytes; // nothing to do → original bytes
                    return await src.save();
                } catch (e) {
                    console.warn('[store-print] PDF rotate skipped:', e && e.message || e);
                    return bytes;
                }
            }

            async function fitPdfToMedia(bytes, mediaWin, mediaHin, wantLandscape) {
                try {
                    if (!window.PDFLib || !(mediaWin > 0) || !(mediaHin > 0)) return bytes;
                    const {
                        PDFDocument,
                        degrees
                    } = window.PDFLib;
                    const src = await PDFDocument.load(bytes);
                    const out = await PDFDocument.create();
                    const n = src.getPageCount();
                    // `wantLandscape` is decided by the caller from the PRODUCT page
                    // count (4-page → portrait, 2-page → landscape). Orient the media
                    // to it; if the design page differs, the loop rotates it to fit.
                    const mediaWpt = (wantLandscape ? Math.max(mediaWin, mediaHin) : Math.min(mediaWin, mediaHin)) * 72;
                    const mediaHpt = (wantLandscape ? Math.min(mediaWin, mediaHin) : Math.max(mediaWin, mediaHin)) * 72;
                    for (let i = 0; i < n; i++) {
                        const emb = await out.embedPage(src.getPage(i));
                        const pw = emb.width,
                            ph = emb.height;
                        const rotate = (pw > ph) !== (mediaWpt > mediaHpt);
                        const page = out.addPage([mediaWpt, mediaHpt]);
                        if (rotate) {
                            // -90° (clockwise): the page's footprint swaps axes, so cover
                            // the media using the swapped extents; for a clockwise turn the
                            // pivot sits on the sheet's left/top side.
                            const s = Math.max(mediaWpt / ph, mediaHpt / pw);
                            const wW = ph * s,
                                wH = pw * s;
                            page.drawPage(emb, {
                                x: (mediaWpt - wW) / 2,
                                y: (mediaHpt + wH) / 2,
                                xScale: s,
                                yScale: s,
                                rotate: degrees(-90),
                            });
                        } else {
                            const s = Math.max(mediaWpt / pw, mediaHpt / ph);
                            const dw = pw * s,
                                dh = ph * s;
                            page.drawPage(emb, {
                                x: (mediaWpt - dw) / 2,
                                y: (mediaHpt - dh) / 2,
                                xScale: s,
                                yScale: s,
                            });
                        }
                    }
                    return await out.save();
                } catch (e) {
                    console.warn('[store-print] PDF fit skipped:', e && e.message || e);
                    return bytes;
                }
            }

            // Derive the TRIM size (in inches) from the tray's saved size code.
            // `traySizeDimensions()` returns the full BLEED sheet dims — e.g.
            //   '7x10-E2E' → [7.57, 10.49]
            //   '4x6-E2E'  → [4.57, 6.49]
            //   'Letter-E2E' → [9.07, 11.49]
            // The saved PDF should be the TRIM page (7×10, 4×6, 8.5×11 …) so
            // the output matches what /print/pdf/check produces. The physical
            // printer still prints edge-to-edge because we pin `paperSize` to
            // the driver's E2E form (see resolveLiveMedia below) — the
            // driver expands trim to bleed at print time.
            function trimSizeFromCode(chosen) {
                const raw = String(chosen && chosen.size || '').trim();
                const num = '(\\d+(?:\\.\\d+)?)';
                const pair = num + '\\s*[x×]\\s*' + num;
                // A size stated in MILLIMETRES, e.g.
                // "16K 184 x 260mm (7.25 × 10.24 in)" — the mm value comes first,
                // its inch equivalent is in the trailing "(… in)" part. Use the given
                // inch value when present, else convert the mm to inches.
                const mm = raw.match(new RegExp(pair + '\\s*mm\\b', 'i'));
                const paren = raw.match(new RegExp('\\(\\s*' + pair + '\\s*in\\s*\\)', 'i'));
                if (mm) {
                    if (paren) return [parseFloat(paren[1]), parseFloat(paren[2])];
                    return [parseFloat(mm[1]) / 25.4, parseFloat(mm[2]) / 25.4];
                }
                // A LEADING inch size, e.g. "5 x 7in E2E (5.57 × 7.49 in)" — this is
                // the TRIM (5 × 7); the parenthesised value is the bleed, ignored so
                // E2E still uses the trim size. Also matches plain "7x10" / "4x6".
                const inLead = raw.match(new RegExp(pair + '\\s*in\\b', 'i'));
                if (inLead) return [parseFloat(inLead[1]), parseFloat(inLead[2])];
                if (paren) return [parseFloat(paren[1]), parseFloat(paren[2])];
                // Bare "W x H" (internal tray codes like "7x10-E2E", "4x6") — strip a
                // trailing "-E2E"/" E2E" first, then parse.
                const base = raw.replace(/[-\s]*e2e.*$/i, '').trim();
                const bare = base.match(new RegExp(pair, 'i'));
                if (bare) return [parseFloat(bare[1]), parseFloat(bare[2])];
                // Named sizes.
                const named = { 'letter': [8.5, 11], 'legal': [8.5, 14] };
                const key = base.toLowerCase();
                if (named[key]) return named[key];
                // Unknown code: fall back to the tray's saved (bleed) dims.
                const sw = parseFloat(chosen && chosen.size_width);
                const sh = parseFloat(chosen && chosen.size_height);
                return (sw > 0 && sh > 0) ? [sw, sh] : null;
            }

            // Ask the bridge for the printer's live paper-size list and find one
            // whose dimensions match the tray's saved size (either orientation,
            // within 0.05 inch tolerance). Returns the driver's real media NAME
            // — safe to pass to the bridge as `paperSize` (goes into SumatraPDF's
            // `paper=<name>` and pins the print to that specific driver form).
            // Returns null on any failure (older bridge without getPrinterDetails,
            // no size match, malformed response) so the caller can fall back to
            // the driver-default media as before.
            // Resolve the LIVE driver media that best matches the tray's size.
            // Returns { name, w, h } with the media's REAL dimensions (inches,
            // portrait-normalised w≤h) so the PDF can be rebuilt at the exact
            // paper size that will actually be printed — or null if no match.
            async function resolveLiveMedia(pp, printer, mediaWin, mediaHin) {
                try {
                    if (!pp || !printer || !(mediaWin > 0) || !(mediaHin > 0)) return null;
                    const res = await pp.getPrinterDetails(printer);
                    const raw = (res && (res.sizes || res.paperSizes || res.media)) || [];
                    if (!raw.length) return null;
                    const TOL = 0.05;
                    const targetW = Math.min(mediaWin, mediaHin);
                    const targetH = Math.max(mediaWin, mediaHin);
                    let best = null;
                    let bestDelta = Infinity;
                    for (let i = 0; i < raw.length; i++) {
                        const s = raw[i];
                        const name = s.id || s.key || s.name || s.label || s.displayName;
                        if (!name) continue;
                        let w = s.width != null ? s.width : s.w;
                        let h = s.height != null ? s.height : s.h;
                        if (w == null || h == null) continue;
                        let units = (s.units || s.unit || '').toLowerCase();
                        if (!units) {
                            units = (Math.max(w, h) > 1000) ? 'micron' : (Math.max(w, h) > 100 ? 'mm' : 'in');
                        }
                        if (units === 'mm') { w = w / 25.4; h = h / 25.4; }
                        else if (units === 'micron' || units === 'um') { w = w / 25400; h = h / 25400; }
                        const sw = Math.min(w, h);
                        const sh = Math.max(w, h);
                        const delta = Math.abs(sw - targetW) + Math.abs(sh - targetH);
                        if (delta <= TOL * 2 && delta < bestDelta) {
                            bestDelta = delta;
                            best = { name: name, w: sw, h: sh };
                        }
                    }
                    return best;
                } catch (e) {
                    console.info('[store-print] live media lookup skipped:', (e && e.message) || e);
                    return null;
                }
            }

            async function printOrder(chosen, payload, csrf) {
                const t0 = performance.now();
                // Audit-log row (local-print-log system). Declared here so the print
                // try/catch below can record both success and failure on it.
                let lpJob = null;
                const fail = function(message) {
                    logPrint(payload, chosen, 'failed', {
                        error_message: message,
                        duration_ms: Math.round(performance.now() - t0),
                    });
                    return false;
                };

                toast('Connecting to PrintTrays…', 'info');
                let pp;
                try {
                    pp = await connect();
                } catch (e) {
                    toast('PrintTrays not running. Install it then reload.', 'error');
                    return fail('PrintTrays not running / could not connect.');
                }

                let printer = chosen.printer;
                let isDefault = false;
                try {
                    const list = await pp.getPrinters();
                    const all = Array.isArray(list) ? list : [list].filter(Boolean);
                    const match = all.find(p => p.name === printer);
                    if (!match) {
                        const noritsu = all.find(p => /noritsu/i.test(p.name));
                        if (noritsu) {
                            printer = noritsu.name;
                        } else {
                            throw new Error('No matching printer queue found.');
                        }
                    }
                    isDefault = !!(match && match.isDefault);
                } catch (e) {
                    toast('Printer "' + chosen.printer + '" not found on this PC.', 'error');
                    return fail('Printer "' + chosen.printer + '" not found on this PC.');
                }

                try {
                    // Always work from raw bytes so we can rotate the PDF to match
                    // the chosen orientation before encoding.
                    let bytes = null;
                    if (payload.pdf_base64) {
                        const bin = atob(payload.pdf_base64);
                        bytes = new Uint8Array(bin.length);
                        for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
                    } else if (payload.pdf_url) {
                        const res = await fetch(payload.pdf_url, {
                            credentials: 'same-origin'
                        });
                        if (!res.ok) throw new Error('Failed to fetch PDF: HTTP ' + res.status);
                        bytes = new Uint8Array(await res.arrayBuffer());
                    }
                    if (!bytes) throw new Error('No PDF data available.');

                    // DEFAULT PRINT PATH — do NOT rebuild the server PDF. The server
                    // PDF is the source of truth (a 7×10 design stays a 7×10 PDF).
                    // Since the bridge/SumatraPDF won't rotate PDF content for the
                    // `landscape` flag, we ONLY rotate pages 90° when their orientation
                    // doesn't match the desired one — no resize, no cover-fill, no
                    // margins. Scaling / edge-to-edge is left to the driver via
                    // paperSize + scaleMode. For 'actual' the operator wants the true
                    // size AND orientation, so the PDF is left completely untouched.
                    // Resolve the LIVE driver media that will actually be printed, so we
                    // can rebuild the PDF at the EXACT paper size that goes to the
                    // printer (its real dims) and pin the same media name below. Falls
                    // back to the tray's configured size when no live match is found.
                    const liveMedia = await resolveLiveMedia(
                        pp, printer,
                        parseFloat(chosen.size_width),
                        parseFloat(chosen.size_height)
                    );

                    const scaleMode = chosen.scale_mode || 'fit';
                    if (scaleMode === 'fit') {
                        // Output is ALWAYS portrait — same as the own-design preview &
                        // /print/pdf/check pages. A landscape design is rotated -90°
                        // (clockwise) to fit the portrait sheet. The tray's landscape
                        // flag / page count no longer changes the saved orientation.
                        const wantLandscape = false;
                        // Regenerate the PDF at the TRIM size (e.g. 5x7-E2E → 5 × 7), NOT
                        // the full bleed (5.57 × 7.49): the PDF is saved/printed at the
                        // trim size and the driver's pinned E2E media form (paperSize,
                        // below) expands trim to bleed at print time for a borderless
                        // result. Falls back to live/tray dims when no trim is parseable.
                        const _trim = trimSizeFromCode(chosen);
                        const mw = _trim ? _trim[0] : (liveMedia ? liveMedia.w : parseFloat(chosen.size_width));
                        const mh = _trim ? _trim[1] : (liveMedia ? liveMedia.h : parseFloat(chosen.size_height));
                        if (mw > 0 && mh > 0) {
                            bytes = await fitPdfToMedia(bytes, mw, mh, wantLandscape);
                        } else {
                            bytes = await rotatePdfToOrientation(bytes, wantLandscape);
                        }
                    } else {
                        // actual / custom keep the design's true size, but still save in
                        // PORTRAIT — a landscape design is rotated -90° to portrait.
                        bytes = await rotatePdfToOrientation(bytes, false);
                    }

                    let binary = '';
                    for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
                    const b64 = btoa(binary);

                    // PrintTrays bridge has its OWN high-level option schema — NOT
                    // raw QZ config. The official demo sends { type, paperSize:<driver
                    // media NAME>, landscape:<bool>, color:<bool>, copies, duplex?,
                    // inputBin? }. Sending QZ-style keys (size:{w,h}, orientation,
                    // colorType, printerTray, flavor…) makes the bridge ignore them
                    // and fall back to the printer's default media → white space on
                    // the sides. Kept in sync with quick-flow-pc buildPrintConfig().
                    const printOpts = {
                        type: 'pdf',
                        // The PDF is ALWAYS re-laid in portrait (a landscape design is
                        // baked -90° into a portrait page), so tell the bridge
                        // landscape:false — sending the tray flag would rotate it a
                        // SECOND time at the printer (double rotation).
                        landscape: false,
                        color: chosen.color !== false,
                        copies: payload.copies || 1,
                        // Map the tray's scale choice to the bridge's scaleMode:
                        //   'fit' (fit-to-paper) & 'fit_area' (fit printable) → 'fit'
                        //   'actual' → 'actual', 'custom' → 'custom' (+ scaleFactor).
                        scaleMode: (scaleMode === 'actual') ? 'actual' :
                            (scaleMode === 'custom' ? 'custom' : 'fit'),
                    };

                    // 'custom' scale → send the saved percentage as scaleFactor.
                    if (scaleMode === 'custom') {
                        printOpts.scaleFactor = parseFloat(chosen.scale_factor) || 100;
                    }

                    // paperSize goes STRAIGHT into SumatraPDF's `paper=<name>` on the
                    // bridge — it MUST be a real driver media name or the whole print
                    // command fails ("Command failed … paper=Letter"). Store trays are
                    // configured only with a size_width × size_height in inches, not
                    // a driver form name, so we ask the bridge for the printer's LIVE
                    // media list and match by dimensions. When we find a match, pin
                    // the print to that form (this is what makes /print/pdf/check
                    // reach the edges — see quick-flow-pc/local-print/check.blade.php
                    // buildPrintConfig). Without it SumatraPDF falls back to the
                    // driver's DEFAULT loaded form, whose printable area on a Noritsu
                    // 931BL is smaller than the borderless form → visible white
                    // borders. If no match / older bridge, omit paperSize so the
                    // driver default is used (previous behaviour).
                    if (liveMedia && liveMedia.name) {
                        printOpts.paperSize = liveMedia.name;
                        console.log('[store-print] pinned paperSize →', liveMedia.name,
                            '(' + liveMedia.w + ' × ' + liveMedia.h + ' in)');
                    } else {
                        console.log('[store-print] no live media match; using driver default');
                    }

                    // Paper source → bridge `inputBin` (goes into SumatraPDF `bin=`).
                    if (chosen.input_bin) printOpts.inputBin = chosen.input_bin;

                    // Duplex values match the bridge's own schema: 'longEdge' /
                    // 'shortEdge'. Anything else (simplex) is left unset.
                    if (chosen.duplex === 'longEdge' || chosen.duplex === 'shortEdge') {
                        printOpts.duplex = chosen.duplex;
                    }

                    // Page order for MULTI-PAGE PDFs — sent exactly like the demo
                    // ('frontToBack' / 'backToFront'). The bridge applies it as a
                    // reverse page-feed instruction; single-page jobs ignore it.
                    printOpts.pageOrder = chosen.page_order || 'frontToBack';

                    // Record the attempt AND save the exact print-ready PDF to the
                    // local-print log — same system as /print/pdf/check. Fail-soft:
                    // a logging error never blocks or breaks the print.
                    try {
                        lpJob = await LPLog.start({
                            source: 'store_orders',
                            source_url: location.pathname,
                            print_type: 'pdf',
                            device_name: printer || null,
                            file: {
                                url: payload.pdf_url || null,
                                name: (payload.pdf_url || '').split('/').pop() || null,
                                type: 'pdf',
                                pages: payload.pages_count || null,
                                dimensions: liveMedia ? (liveMedia.w + ' x ' + liveMedia.h + ' in') :
                                    ((chosen.size_width && chosen.size_height) ?
                                        (chosen.size_width + ' x ' + chosen.size_height + ' in') : null),
                            },
                            printer_settings: {
                                printer_name: printer || null,
                                tray_key: chosen.tray_key || null,
                                tray_label: chosen.label || null,
                                paper_size: printOpts.paperSize || chosen.size || null,
                                media: chosen.media || null,
                                gsm: chosen.gsm || null,
                                paper_source: printOpts.inputBin || null,
                                orientation: 'portrait',
                                color_mode: printOpts.color ? 'color' : 'grayscale',
                                duplex: printOpts.duplex || 'simplex',
                                page_order: printOpts.pageOrder || 'frontToBack',
                                scale_mode: printOpts.scaleMode || null,
                                scale_percentage: printOpts.scaleFactor || null,
                                copies: payload.copies || 1,
                            },
                            request_payload: printOpts,
                            design_settings: {
                                order_id: payload.order?.id || null,
                                order_number: payload.order?.number || null,
                                order_item_id: payload.order?.item_id || null,
                                size_width: parseFloat(payload.order?.size_width) || chosen.size_width || null,
                                size_height: parseFloat(payload.order?.size_height) || chosen.size_height || null,
                                page_count: payload.pages_count || null,
                                user_type: chosen.user_type || null,
                            },
                        });
                        LPLog.attachPdf(lpJob, b64);
                    } catch (e) { /* non-fatal */ }

                    // Print-time log + ON-SCREEN toast: fires on every Print, showing the
                    // FINAL PDF page size actually sent to the printer.
                    try {
                        if (window.PDFLib) {
                            const _doc = await window.PDFLib.PDFDocument.load(bytes);
                            const _sz = _doc.getPage(0).getSize();
                            const _w = +(_sz.width / 72).toFixed(2);
                            const _h = +(_sz.height / 72).toFixed(2);
                            console.log('[store-print] PRINT → sending PDF', {
                                pdfPage_in: { w: _w, h: _h },
                                paperCode: chosen.size,
                                scaleMode: printOpts.scaleMode,
                                paperSize: printOpts.paperSize || '(driver default)',
                                copies: printOpts.copies, printer: printer,
                            });
                            toast('Printing PDF ' + _w + ' × ' + _h + ' in · paper: ' +
                                (chosen.size || '') + ' · ' + (printOpts.copies || 1) +
                                ' ' + ((printOpts.copies || 1) == 1 ? 'copy' : 'copies'), 'info');
                        }
                    } catch (_e) { console.info('[store-print] print-time dim log skipped:', _e && _e.message); }

                    try {
                        await pp.print(printer, b64, printOpts);
                        await LPLog.finish(lpJob, { ok: true });
                    } catch (ePrint) {
                        await LPLog.finish(lpJob, { ok: false, error: (ePrint && ePrint.message) || 'Print failed.' });
                        throw ePrint;
                    }
                } catch (e) {
                    console.error(e);
                    const msg = (e && e.message) ? e.message : 'unknown error';
                    toast('Print failed: ' + msg, 'error');
                    return fail('PrintTrays print threw: ' + msg);
                }

                try {
                    const res = await fetch(payload.advance_url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'text/html'
                        },
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                } catch (e) {
                    toast('Sent to printer - but could not advance the order in Qrinto.', 'error');
                    logPrint(payload, chosen, 'success', {
                        is_default_printer: isDefault,
                        duration_ms: Math.round(performance.now() - t0),
                        error_message: 'advance_failed: ' + (e && e.message ? e.message : 'unknown'),
                    });
                    return false;
                }

                logPrint(payload, chosen, 'success', {
                    is_default_printer: isDefault,
                    duration_ms: Math.round(performance.now() - t0),
                });

                toast('Sent ' + payload.order.number + ' to ' + (chosen.label || printer), 'success');
                return true;
            }

            return {
                printOrder,
                toast,
                ensureReady,
                connect
            };
        })();
    </script>
    @stack('scripts')
</body>

</html>
