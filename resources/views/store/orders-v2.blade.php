@extends('layouts.store')
@section('title', 'Orders')

@section('content')
    @php
        $stages = \App\Models\Order::pickupStages();
        $rPrefix = 'store.';
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <div id="live-stat" class="text-[13px] text-slate-500 font-medium"></div>
        <div class="flex items-center gap-3">
            <a href="{{ route('storepanel.printLogs') }}" class="btn-ghost-v2">
                <i data-lucide="printer" class="w-4 h-4"></i> Print logs
            </a>
            <a href="{{ route('storepanel.kioskLogs') }}" class="btn-ghost-v2">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Kiosk Logs
            </a>
        </div>
    </div>

    @foreach (['success' => 'emerald', 'warning' => 'amber', 'error' => 'red'] as $flash => $color)
        @if (session($flash))
            <div
                class="mb-4 px-4 py-3 rounded-xl bg-{{ $color }}-50 border border-{{ $color }}-200 text-{{ $color }}-800 text-sm font-medium">
                {{ session($flash) }}</div>
        @endif
    @endforeach

    <div id="order-error-banner"
        class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium hidden"></div>

    <div class="space-y-8" id="orders-board">
        @foreach ($stages as $stageKey => $stage)
            @php $cards = $queue[$stageKey] ?? collect(); @endphp
            <section>
                <div class="flex items-baseline gap-2 mb-3">
                    <h2 class="font-display font-bold text-[15px] text-slate-900">{{ $stage['label'] }}</h2>
                    <span class="v2-count">{{ $cards->count() }}</span>
                </div>

                @if ($cards->count())
                    <div class="space-y-3">
                        @foreach ($cards as $order)
                            @include('store.partials.queue-row-v2', [
                                'order' => $order,
                                'rPrefix' => $rPrefix,
                                'store' => $store,
                            ])
                        @endforeach
                    </div>
                @else
                    <div class="v2-empty">
                        @if ($stageKey === 'new')
                            New orders will land here the moment they're placed.
                        @elseif ($stageKey === 'printing')
                            Nothing on the press right now.
                        @else
                            Nothing waiting at the counter.
                        @endif
                    </div>
                @endif
            </section>
        @endforeach

        @if ($recentlyDone->total() > 0)
            <section>
                <div class="flex items-baseline gap-2 mb-3">
                    <h2 class="font-display font-bold text-[15px] text-slate-900">Picked up</h2>
                    <span class="v2-count">{{ $recentlyDone->total() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach ($recentlyDone as $order)
                        @include('store.partials.queue-row-v2', [
                            'order' => $order,
                            'rPrefix' => $rPrefix,
                            'store' => $store,
                        ])
                    @endforeach
                </div>
                @if ($recentlyDone->hasPages())
                    <div class="mt-4">{{ $recentlyDone->onEachSide(1)->links() }}</div>
                @endif
            </section>
        @endif
    </div>

    {{-- Tray picker modal --}}
    <div x-data="printPickerV2()" x-cloak @open-print-modal.window="await open($event.detail.prepareUrl)" x-show="visible"
        class="fixed inset-0 z-[80] flex items-center justify-center px-4">

        <div class="absolute inset-0 bg-slate-900/50" @click="close()"></div>

        <div class="relative bg-white border border-slate-200 rounded-2xl shadow-2xl w-full max-w-lg p-6" @click.stop>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-display font-bold text-lg text-slate-900">Pick a tray</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Order <span class="mono text-slate-700" x-text="payload?.order?.number"></span>
                        · size <span class="mono text-slate-700" x-text="payload?.order?.size || 'unknown'"></span>
                    </p>
                </div>
                <button type="button" @click="close()"
                    class="w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-500 flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <template x-if="loading">
                <div class="py-10 text-center text-slate-400 text-sm">Loading trays…</div>
            </template>

            <template x-if="!loading && payload">
                <div class="mt-5 space-y-4 max-h-[65vh] overflow-y-auto pr-1">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Configured trays</p>
                        <div class="space-y-2">
                            <template x-for="t in payload.trays" :key="'tray-' + t.key">
                                <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                                    :class="!t.enabled ? 'border-slate-100 bg-slate-50/60 cursor-not-allowed' :
                                        (choice === 'tray:' + t.key ? 'border-[#287d3c] bg-[#f2f7f2]' :
                                            'border-slate-200 hover:border-slate-300')">
                                    <input type="radio" name="print_pick" :value="'tray:' + t.key" x-model="choice"
                                        :disabled="!t.enabled" class="accent-[#287d3c] w-4 h-4"
                                        :class="!t.enabled ? 'opacity-40' : ''">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-[13px]"
                                            :class="t.enabled ? 'text-slate-900' : 'text-slate-400'">
                                            <span x-text="t.label"></span>
                                            <template x-if="t.recommended">
                                                <span
                                                    class="ml-2 text-[10px] font-extrabold text-[#287d3c] bg-[#eaf3ea] border border-[#bfdcc4] rounded-full px-2 py-0.5 uppercase tracking-wider">Recommended</span>
                                            </template>
                                        </p>
                                        <p class="mono text-[11px] mt-0.5 truncate"
                                            :class="t.enabled ? 'text-slate-500' : 'text-slate-300'" :title="t.printer">
                                            <span x-text="t.size_pretty"></span> · <span x-text="t.media_label"></span>
                                            <template x-if="t.user_type"><span class="text-slate-400"> · UT<span
                                                        x-text="t.user_type"></span></span></template>
                                            <template x-if="t.printer"><span class="text-slate-400"> · <span
                                                        x-text="t.printer"></span></span></template>
                                        </p>

                                        {{-- Configured print settings — mirrors the tray setup page so the
                                             operator sees exactly what will be sent to the printer. --}}
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1">
                                            <template x-if="t.size_pretty">
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600 border border-slate-200" x-text="t.size_pretty"></span>
                                            </template>
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600 border border-slate-200" x-text="t.landscape ? 'Landscape' : 'Portrait'"></span>
                                            <template x-if="t.duplex && t.duplex !== 'simplex'">
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600 border border-slate-200" x-text="t.duplex === 'longEdge' ? 'Duplex' : 'Duplex (short)'"></span>
                                            </template>
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600 border border-slate-200" x-text="t.color === false ? 'B&amp;W' : 'Color'"></span>
                                            <template x-if="t.input_bin">
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-600 border border-slate-200" x-text="t.input_bin"></span>
                                            </template>
                                            {{-- Media Type / Quality are intentionally NOT shown here: the
                                                 print pipeline can't set a driver's media-type or DPI, so they
                                                 never reach the printer. Only wired settings are displayed. --}}
                                        </div>
                                    </div>
                                    <template x-if="!t.enabled">
                                        <span class="text-[11px] text-slate-400">Off</span>
                                    </template>
                                </label>
                            </template>
                        </div>
                    </div>

                    <p x-show="error" x-text="error" class="text-[12px] text-red-600 font-medium"></p>

                    <div class="pt-3 sticky bottom-0 bg-white pb-1 space-y-2">
                        <div x-show="pcState === 'error' || (pcState === 'ok' && pcPrinters.length === 0)"
                            class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl bg-amber-50 border border-amber-200">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="printer-off" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                <p class="text-[12px] text-amber-800 leading-snug">Printer not visible? Install PrintTrays to
                                    detect this PC's printers.</p>
                            </div>
                            <a href="https://noritsucanada.com/print-trays/download/" target="_blank" rel="noopener"
                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#287d3c] hover:bg-emerald-800 text-white text-[12px] font-bold transition">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download PrintTrays
                            </a>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="close()"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">Cancel</button>
                            <button type="button" @click="confirm()" :disabled="!chosenTarget() || sending"
                                class="px-5 py-2 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition disabled:bg-slate-300 disabled:cursor-not-allowed">
                                <span x-show="!sending">Print to <span
                                        x-text="chosenTarget()?.label || 'printer'"></span></span>
                                <span x-show="sending">Sending…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .btn-ghost-v2 {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            background: #fff;
            border: 1px solid #e6e9e4;
            transition: all .15s ease;
        }

        .btn-ghost-v2:hover {
            border-color: #c8d0c3;
        }

        .v2-count {
            font-size: 12px;
            color: #6b7280;
            background: #e3e7de;
            padding: 1px 8px;
            border-radius: 10px;
            font-weight: 600;
        }

        .v2-empty {
            border: 1px dashed #e6e9e4;
            border-radius: 12px;
            padding: 16px 18px;
            color: #9aa0a6;
            font-size: 13px;
            background: #f7f9f5;
        }

        .v2-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(20, 23, 26, 0.04), 0 1px 8px rgba(20, 23, 26, 0.03);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid transparent;
            transition: box-shadow .25s ease, border-color .25s ease;
        }

        .v2-card.is-printing {
            border-color: #cfe6d9;
        }

        .v2-card.is-done {
            opacity: 0.7;
        }

        .v2-card.just-printed {
            animation: v2-printed-pop .5s cubic-bezier(.34, 1.56, .64, 1);
        }

        @keyframes v2-printed-pop {
            0% {
                transform: scale(0.98);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .v2-mobile-top {
            display: none;
        }

        .v2-col-id {
            width: 104px;
            flex-shrink: 0;
            font-size: 11px;
            color: #9aa0a6;
            line-height: 1.4;
            font-variant-numeric: tabular-nums;
        }

        .v2-col-main {
            flex: 1;
            min-width: 0;
        }

        .v2-col-main .v2-title {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 3px;
            color: #14171a;
        }

        .v2-col-main .v2-sub {
            font-size: 12.5px;
            color: #6b7280;
            margin: 0;
        }

        .v2-journey {
            display: flex;
            align-items: center;
            margin-top: 10px;
            gap: 0;
        }

        .v2-j-step {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .v2-j-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #e7ebe3;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .3s ease;
        }

        .v2-j-dot svg {
            width: 9px;
            height: 9px;
        }

        .v2-j-step.done .v2-j-dot {
            background: #1c7a43;
            border-color: #1c7a43;
        }

        .v2-j-step.done .v2-j-dot svg {
            stroke: #fff;
        }

        .v2-j-step.current .v2-j-dot {
            border-color: #1c7a43;
            background: #fff;
            position: relative;
        }

        .v2-j-step.current .v2-j-dot::after {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #1c7a43;
        }

        .v2-j-label {
            font-size: 10.5px;
            color: #9aa0a6;
            white-space: nowrap;
        }

        .v2-j-step.done .v2-j-label {
            color: #155f34;
        }

        .v2-j-step.current .v2-j-label {
            color: #14171a;
            font-weight: 700;
        }

        .v2-j-line {
            width: 22px;
            height: 2px;
            background: #e7ebe3;
            margin: 0 3px;
        }

        .v2-j-line.done {
            background: #1c7a43;
        }

        .v2-col-price {
            flex-shrink: 0;
        }

        .v2-pill {
            background: #fbf3d9;
            border: 1px solid #efdfa2;
            color: #8a6a15;
            font-size: 12.5px;
            font-weight: 700;
            padding: 7px 12px;
            border-radius: 9px;
            white-space: nowrap;
        }

        .v2-pill-paid {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            font-size: 12.5px;
            font-weight: 700;
            padding: 7px 12px;
            border-radius: 9px;
            white-space: nowrap;
        }

        .v2-col-action {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            min-width: 150px;
        }

        .v2-btn-primary {
            background: #1c7a43;
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 9px 16px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            transition: background .15s ease;
        }

        .v2-btn-primary:hover {
            background: #155f34;
        }

        .v2-btn-primary svg {
            width: 15px;
            height: 15px;
        }

        .v2-btn-neutral {
            background: #fff;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 9px 16px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .v2-btn-neutral:hover {
            background: #f9fafb;
        }

        .v2-btn-done {
            background: #c9d3cc;
            color: #5a625c;
            border: none;
            border-radius: 9px;
            padding: 9px 16px;
            font-size: 13.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: default;
        }

        .v2-link-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .v2-link {
            font-size: 12px;
            color: #6b7280;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color .15s;
        }

        .v2-link:hover {
            color: #14171a;
        }

        .v2-undo {
            font-size: 12px;
            color: #9aa0a6;
            text-decoration: underline;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .v2-undo:hover {
            color: #6b7280;
        }

        .v2-print-widget {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .v2-ring-wrap {
            position: relative;
            width: 46px;
            height: 46px;
            flex-shrink: 0;
        }

        .v2-ring-wrap>svg {
            transform: rotate(-90deg);
        }

        .v2-ring-bg {
            fill: none;
            stroke: #e7ebe3;
            stroke-width: 4;
        }

        .v2-ring-fg {
            fill: none;
            stroke: #1c7a43;
            stroke-width: 4;
            stroke-linecap: round;
            transition: stroke-dashoffset .3s linear;
        }

        .v2-ring-icon {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .v2-ring-icon svg {
            width: 16px;
            height: 16px;
            stroke: #155f34;
            transform: none;
        }

        .v2-print-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .v2-print-text .p-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #155f34;
        }

        .v2-print-text .p-time {
            font-size: 11.5px;
            color: #9aa0a6;
        }

        .v2-check-pop {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .v2-check-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #dcf3e4;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: v2-pop .35s cubic-bezier(.34, 1.56, .64, 1);
        }

        @keyframes v2-pop {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .v2-check-circle svg {
            width: 22px;
            height: 22px;
            stroke: #155f34;
        }

        .v2-check-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #155f34;
        }

        .v2-card-error {
            padding: 6px 10px;
            border-radius: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        @media (max-width: 720px) {
            .v2-card {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 14px 14px;
            }

            .v2-mobile-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }

            .v2-mobile-top .v2-col-id {
                width: auto;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .v2-mobile-top .v2-col-id br {
                display: none;
            }

            .v2-desktop-id,
            .v2-desktop-price {
                display: none !important;
            }

            .v2-col-id br {
                display: none;
            }

            .v2-col-main {
                min-width: 0;
            }

            .v2-col-main .v2-title {
                font-size: 14px;
            }

            .v2-journey {
                flex-wrap: wrap;
                gap: 2px;
            }

            .v2-j-line {
                width: 12px;
            }

            .v2-j-label {
                font-size: 9.5px;
            }

            .v2-col-price {
                align-self: flex-start;
            }

            .v2-pill,
            .v2-pill-paid {
                font-size: 11px;
                padding: 5px 10px;
            }

            .v2-col-action {
                align-items: flex-start;
                min-width: 0;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 8px;
            }

            .v2-btn-primary,
            .v2-btn-neutral,
            .v2-btn-done {
                font-size: 12.5px;
                padding: 8px 14px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const V2_STAGES = ['new', 'printing', 'ready', 'done'];
        const V2_LABELS = {
            new: 'New',
            printing: 'Printing',
            ready: 'Ready',
            done: 'Picked up'
        };
        const printingOrders = {};

        function svgCheck(size) {
            return '<svg width="' + size + '" height="' + size +
                '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
        }

        function svgPrinter(size) {
            return '<svg width="' + size + '" height="' + size +
                '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><rect x="4" y="9" width="16" height="9" rx="1"/><path d="M6 14h12v7H6z"/></svg>';
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-pt-print]');
            if (!btn) return;
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-print-modal', {
                detail: {
                    prepareUrl: btn.dataset.prepareUrl
                }
            }));
        });

        function printPickerV2() {
            return {
                visible: false,
                loading: false,
                sending: false,
                payload: null,
                choice: null,
                error: '',
                pcState: 'idle',
                pcPrinters: [],
                defaultPrinter: null,

                async open(prepareUrl) {
                    this.visible = true;
                    this.loading = true;
                    this.payload = null;
                    this.choice = null;
                    this.error = '';
                    try {
                        const res = await fetch(prepareUrl, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (!res.ok || !data.ok) {
                            this.error = data.error || 'Could not prepare this order.';
                            this.loading = false;
                            return;
                        }
                        this.payload = data;
                        const first = data.matched_key || data.trays.find(t => t.enabled)?.key;
                        this.choice = first ? ('tray:' + first) : null;
                    } catch (e) {
                        this.error = 'Network error while preparing the order.';
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    }
                    this.scanPc();
                },

                async scanPc() {
                    this.pcState = 'loading';
                    try {
                        const pp = await window.__qrintoPT.connect();
                        const list = await pp.getPrinters();
                        const all = Array.isArray(list) ? list : [list].filter(Boolean);
                        this.pcPrinters = all.map(p => p.name);
                        this.defaultPrinter = (all.find(p => p.isDefault) || {}).name || null;
                        this.pcState = 'ok';
                    } catch (e) {
                        this.pcState = 'error';
                    }
                },

                chosenTarget() {
                    if (!this.choice || !this.payload) return null;
                    if (this.choice.startsWith('tray:')) {
                        const key = this.choice.slice(5);
                        const t = this.payload.trays.find(x => x.key === key && x.enabled);
                        if (!t) return null;
                        return {
                            printer: t.printer || 'Noritsu 931BL',
                            label: t.label,
                            tray_key: t.key,
                            size: t.size,
                            size_width: t.size_width,
                            size_height: t.size_height,
                            media: t.media,
                            gsm: t.gsm,
                            user_type: t.user_type,
                            landscape: t.landscape,
                            duplex: t.duplex,
                            color: t.color,
                            input_bin: t.input_bin,
                            quality: t.quality,
                            media_type_live: t.media_type_live
                        };
                    }
                    return null;
                },

                close() {
                    this.visible = false;
                },

                async confirm() {
                    const target = this.chosenTarget();
                    if (!target || this.sending) return;
                    this.sending = true;
                    this.error = '';
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector(
                        'input[name="_token"]')?.value;
                    const orderId = this.payload?.order?.id;
                    const orderNumber = this.payload?.order?.number;
                    const card = orderId ? document.getElementById('v2-card-' + orderId) : null;

                    if (card) startPrintingAnimation(card, orderId);
                    this.close();
                    this.sending = false;

                    const ok = await window.__qrintoPT.printOrder(target, this.payload, csrf);

                    if (ok) {
                        if (card) showPrintSuccess(card, orderId);
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        if (card) {
                            stopPrintingAnimation(orderId);
                            showCardError(card, orderId);
                        }
                        showBannerError('Print failed for ' + (orderNumber || 'order') +
                            '. Check that PrintTrays is running and the printer is online.');
                    }
                },
            };
        }

        function startPrintingAnimation(card, orderId) {
            card.classList.add('is-printing');
            const actionCol = card.querySelector('.v2-col-action');
            if (!actionCol) return;
            const r = 19,
                c = 2 * Math.PI * r;
            actionCol.innerHTML =
                '<div class="v2-print-widget" id="v2-pw-' + orderId + '">' +
                '<div class="v2-ring-wrap">' +
                '<svg width="46" height="46" viewBox="0 0 46 46">' +
                '<circle class="v2-ring-bg" cx="23" cy="23" r="' + r + '"/>' +
                '<circle class="v2-ring-fg" cx="23" cy="23" r="' + r + '" stroke-dasharray="' + c +
                '" stroke-dashoffset="' + c + '" id="v2-ring-' + orderId + '"/>' +
                '</svg>' +
                '<div class="v2-ring-icon">' + svgPrinter(16) + '</div>' +
                '</div>' +
                '<div class="v2-print-text">' +
                '<span class="p-label" id="v2-plabel-' + orderId + '">Sending to printer…</span>' +
                '<span class="p-time" id="v2-ptime-' + orderId + '"></span>' +
                '</div>' +
                '</div>';

            const startTime = Date.now(),
                duration = 8000;
            printingOrders[orderId] = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const pct = Math.min(95, Math.round((elapsed / duration) * 100));
                const ring = document.getElementById('v2-ring-' + orderId);
                const label = document.getElementById('v2-plabel-' + orderId);
                const time = document.getElementById('v2-ptime-' + orderId);
                if (ring) ring.style.strokeDashoffset = c - (pct / 100) * c;
                if (label) label.textContent = 'Printing… ' + pct + '%';
                if (time) time.textContent = Math.max(0, Math.ceil((duration - elapsed) / 1000)) + 's left';
            }, 200);
        }

        function stopPrintingAnimation(orderId) {
            if (printingOrders[orderId]) {
                clearInterval(printingOrders[orderId]);
                delete printingOrders[orderId];
            }
        }

        function showPrintSuccess(card, orderId) {
            stopPrintingAnimation(orderId);
            card.classList.remove('is-printing');
            card.classList.add('just-printed');
            const actionCol = card.querySelector('.v2-col-action');
            if (actionCol) {
                actionCol.innerHTML =
                    '<div class="v2-check-pop">' +
                    '<div class="v2-check-circle">' + svgCheck(22) + '</div>' +
                    '<span class="v2-check-label">Printed - moving to Ready</span>' +
                    '</div>';
            }
            const journey = card.querySelector('.v2-journey');
            if (journey) journey.innerHTML = buildJourneyHTML('ready');
        }

        function showCardError(card, orderId) {
            stopPrintingAnimation(orderId);
            card.classList.remove('is-printing');
            const actionCol = card.querySelector('.v2-col-action');
            if (actionCol) {
                const prepareUrl = card.dataset.prepareUrl || '';
                actionCol.innerHTML =
                    '<div class="v2-card-error">' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>' +
                    'Print failed - check PrintTrays and try again.' +
                    '</div>' +
                    '<button class="v2-btn-primary" style="margin-top:6px;" onclick="window.location.reload()">' +
                    svgPrinter(15) + ' Retry' +
                    '</button>';
            }
        }

        function showBannerError(msg) {
            const banner = document.getElementById('order-error-banner');
            if (banner) {
                banner.textContent = msg;
                banner.classList.remove('hidden');
                setTimeout(() => banner.classList.add('hidden'), 10000);
            }
        }

        function buildJourneyHTML(currentStage) {
            const idx = V2_STAGES.indexOf(currentStage);
            return V2_STAGES.map((s, i) => {
                const state = i < idx ? 'done' : (i === idx ? 'current' : '');
                const dotInner = i < idx ? svgCheck(8) : '';
                const line = i > 0 ? '<div class="v2-j-line ' + (i <= idx ? 'done' : '') + '"></div>' : '';
                return line + '<div class="v2-j-step ' + state + '"><div class="v2-j-dot">' + dotInner +
                    '</div><span class="v2-j-label">' + V2_LABELS[s] + '</span></div>';
            }).join('');
        }

        (function updateStat() {
            const n = document.querySelectorAll('.v2-card:not(.is-done)').length;
            const el = document.getElementById('live-stat');
            if (el) el.textContent = n === 0 ? 'Queue is clear' : n + ' order' + (n > 1 ? 's' : '') + ' in the queue';
        })();
    </script>
@endpush
