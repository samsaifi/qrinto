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
</head>

<body class="min-h-screen antialiased">
    @php
        $store = $store ?? (auth()->user()->store ?? null);
        $storeLabel = $store ? $store->store_name . ($store->city ? ', ' . $store->city : '') : 'Store';
    @endphp

    {{-- Top bar --}}
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-6">
            <div class="h-16 flex items-center justify-between gap-6">
                <div class="flex items-center gap-8 min-w-0">
                    {{-- Logo → store search (customer entry), per spec --}}
                    <a href="{{ url('store') }}" class="flex items-center gap-2.5 shrink-0">
                        <img src="{{ asset('logo/Qrinto-logo-small.png') }}" alt="Qrinto" class="h-8 w-auto">
                    </a>
                    <span
                        class="font-display font-bold text-sm text-slate-800 truncate hidden sm:block">{{ $storeLabel }}</span>
                </div>

                <nav class="flex items-center gap-1">
                    {{-- Catalog Dropdown --}}
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
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8">
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

    {{-- QZ Tray: local bridge to the Windows print system. --}}
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
    <script>
        if (window.lucide) lucide.createIcons();

        window.__qrintoQZ = (function() {
            let connecting = null;

            function toast(msg, kind) {
                window.dispatchEvent(new CustomEvent('qz-toast', {
                    detail: {
                        msg,
                        kind: kind || 'info'
                    }
                }));
            }

            // QZ Tray certificate-based trust: serve a real certificate and
            // sign each websocket handshake so QZ Tray skips the Allow/Deny
            // dialog. The cert must be installed once in QZ Tray's trusted
            // store (drag qz-cert.pem onto QZ Tray's "Site Manager" window).
            if (window.qz && qz.security) {
                var __qzCertCache = null;
                var __qzCsrf = function() {
                    return document.querySelector('meta[name="csrf-token"]')?.content ||
                        document.querySelector('input[name="_token"]')?.value;
                };
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
                            console.warn('[QZ] cert fetch failed, unsigned mode', e);
                            resolve();
                        });
                });
                qz.security.setSignatureAlgorithm('SHA512');
                qz.security.setSignaturePromise(function(toSign) {
                    return function(resolve, reject) {
                        fetch("{{ route('storepanel.qz.sign') }}", {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'text/plain',
                                    'X-CSRF-TOKEN': __qzCsrf(),
                                },
                                body: JSON.stringify({
                                    request: toSign
                                }),
                            })
                            .then(function(r) {
                                return r.ok ? r.text() : Promise.reject('sign ' + r.status);
                            })
                            .then(resolve)
                            .catch(function(e) {
                                console.warn('[QZ] sign failed, unsigned mode', e);
                                resolve('');
                            });
                    };
                });
            }

            async function connect() {
                if (!window.qz) throw new Error('QZ Tray library did not load.');
                if (qz.websocket.isActive()) return;
                if (!connecting) {
                    connecting = qz.websocket.connect().finally(() => {
                        connecting = null;
                    });
                }
                return connecting;
            }

            /**
             * Silent pre-flight: is QZ Tray installed and reachable on this PC?
             * Returns true when the local bridge answers, false otherwise. No
             * toast - callers decide how to surface the failure (e.g. a blocking
             * install modal on the orders page).
             */
            async function ensureReady() {
                if (!window.qz) return false;
                if (qz.websocket.isActive()) return true;
                // Race the connect against a hard timeout so a hung QZ handshake
                // can never keep the "Checking QZ Tray…" spinner up forever.
                // Poll isActive() after the race: even a rejected/timed-out
                // promise still counts as ready if the socket did come up.
                const timeout = new Promise(function(_, reject) {
                    setTimeout(function() {
                        reject(new Error('qz-timeout'));
                    }, 8000);
                });
                try {
                    await Promise.race([connect(), timeout]);
                } catch (e) {
                    /* fall through - check isActive() below */
                }
                return !!(window.qz && qz.websocket.isActive());
            }

            /**
             * Fire a print-log row (fire-and-forget). Never blocks the caller
             * or bubbles up an error - logging must not derail a real print.
             */
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
                    qz_tray_version: (window.qz && window.qz.version) || null,
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
                    }).catch(function() {
                        /* swallow - logging is best-effort */ });
                } catch (e) {
                    /* ignore */ }
            }

            /**
             * Print an order via QZ Tray using the operator's chosen tray.
             * chosen: full tray row incl. metadata (see chosenTarget()).
             * payload: { pdf_url, pdf_base64, copies, advance_url, log_url,
             *            order:{number, item_id} }
             */
            async function printOrder(chosen, payload, csrf) {
                const t0 = performance.now();
                const fail = function(message) {
                    logPrint(payload, chosen, 'failed', {
                        error_message: message,
                        duration_ms: Math.round(performance.now() - t0),
                    });
                    return false;
                };

                toast('Connecting to QZ Tray…', 'info');
                try {
                    await connect();
                } catch (e) {
                    toast('QZ Tray not running. Install it from qz.io then reload.', 'error');
                    return fail('QZ Tray not running / could not connect.');
                }

                let printer = chosen.printer;
                let isDefault = false;
                try {
                    const found = await qz.printers.find(printer);
                    printer = Array.isArray(found) ? (found[0] || printer) : (found || printer);
                    if (!printer) {
                        const any = await qz.printers.find('Noritsu');
                        printer = Array.isArray(any) ? any[0] : any;
                    }
                    if (!printer) throw new Error('No matching printer queue found.');
                    const defaultPrinter = await qz.printers.getDefault().catch(() => null);
                    isDefault = !!defaultPrinter && defaultPrinter === printer;
                } catch (e) {
                    toast('Printer "' + chosen.printer + '" not found on this PC.', 'error');
                    return fail('Printer "' + chosen.printer + '" not found on this PC.');
                }

                try {
                    const configOpts = {
                        copies: payload.copies || 1
                    };
                    if (chosen.size_width && chosen.size_height) {
                        configOpts.size = {
                            width: chosen.size_width,
                            height: chosen.size_height
                        };
                        configOpts.units = 'in';
                    }
                    if (chosen.density) {
                        configOpts.density = {
                            cross: parseInt(chosen.density),
                            feed: parseInt(chosen.density)
                        };
                    }
                    const config = qz.configs.create(printer, configOpts);
                    // Prefer base64 bytes (the server embedded them) - that way
                    // QZ Tray never has to fetch a URL from its desktop process,
                    // which sidesteps auth cookies, /public prefix quirks, and
                    // missing storage symlinks. Fall back to URL if bytes are
                    // missing (older payloads).
                    const data = payload.pdf_base64 ?
                        [{
                            type: 'pixel',
                            format: 'pdf',
                            flavor: 'base64',
                            data: payload.pdf_base64
                        }] :
                        [{
                            type: 'pixel',
                            format: 'pdf',
                            flavor: 'file',
                            data: payload.pdf_url
                        }];
                    await qz.print(config, data);
                } catch (e) {
                    console.error(e);
                    const msg = (e && e.message) ? e.message : 'unknown error';
                    toast('Print failed: ' + msg, 'error');
                    return fail('QZ print threw: ' + msg);
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
                    // Still log a success row: the physical print DID happen.
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
                ensureReady
            };
        })();
    </script>
    @stack('scripts')
</body>

</html>
