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
    <script>
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
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

            async function printOrder(chosen, payload, csrf) {
                const t0 = performance.now();
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
                    let b64 = payload.pdf_base64;
                    if (!b64 && payload.pdf_url) {
                        const res = await fetch(payload.pdf_url, {
                            credentials: 'same-origin'
                        });
                        if (!res.ok) throw new Error('Failed to fetch PDF: HTTP ' + res.status);
                        const bytes = new Uint8Array(await res.arrayBuffer());
                        let binary = '';
                        for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
                        b64 = btoa(binary);
                    }
                    if (!b64) throw new Error('No PDF data available.');

                    const printOpts = {
                        type: 'pdf',
                        encoding: 'base64',
                        copies: payload.copies || 1,
                    };
                    if (chosen.size) printOpts.paperSize = chosen.size;
                    if (chosen.landscape) printOpts.landscape = true;
                    if (chosen.duplex && chosen.duplex !== 'simplex') printOpts.duplex = chosen.duplex;
                    if (chosen.color !== undefined) printOpts.color = chosen.color;
                    if (chosen.input_bin) printOpts.printerTray = chosen.input_bin;
                    if (chosen.scale && chosen.scale !== 100) printOpts.scaleFactor = chosen.scale;
                    if (chosen.margins && chosen.margins !== 'default') printOpts.margins = {
                        marginType: chosen.margins
                    };

                    await pp.print(printer, b64, printOpts);
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
