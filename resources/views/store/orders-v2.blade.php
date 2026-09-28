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
                        · size <span class="mono text-slate-700" x-text="orderSizePretty()"></span>
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
                                    <template x-if="t.enabled">
                                        <button type="button" @click.stop.prevent="openConfig(t)"
                                            class="shrink-0 text-[11px] text-[#287d3c] font-bold hover:underline">Change</button>
                                    </template>
                                    <template x-if="!t.enabled">
                                        <span class="text-[11px] text-slate-400">Off</span>
                                    </template>
                                </label>
                            </template>
                        </div>
                    </div>

                    <p x-show="error" x-text="error" class="text-[12px] text-red-600 font-medium"></p>

                    {{-- Preflight checks for the chosen tray --}}
                    <template x-if="chosenTarget()">
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Checks</p>
                            <template x-for="c in targetChecks()" :key="'pt-' + c.key">
                                <div class="flex items-start gap-2.5 py-2.5 transition-colors"
                                    :class="c.ok ? 'border-b border-slate-100' : (c.severity === 'error'
                                        ? 'bg-red-50 border border-red-100 rounded-lg px-3 my-1'
                                        : 'bg-amber-50 border border-amber-100 rounded-lg px-3 my-1')">
                                    <template x-if="c.ok">
                                        <svg class="w-4 h-4 text-[#287d3c] mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                    </template>
                                    <template x-if="!c.ok && c.severity === 'warn'">
                                        <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                            <path d="M12 9v4" />
                                            <path d="M12 17h.01" />
                                        </svg>
                                    </template>
                                    <template x-if="!c.ok && c.severity === 'error'">
                                        <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </template>
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-bold text-slate-900" x-text="c.title"></p>
                                        <p class="text-[12px] text-slate-500 mt-0.5" x-text="c.detail"></p>
                                        <template x-if="!c.ok && c.ackLabel">
                                            <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
                                                <input type="checkbox" x-model="acks[c.key]" class="w-4 h-4 rounded border-slate-300 shrink-0"
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
                    </template>

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
                            <button type="button" @click="confirm()"
                                :disabled="!chosenTarget() || sending || !targetPrintable"
                                class="px-5 py-2 rounded-xl text-white text-sm font-bold transition disabled:bg-slate-300 disabled:cursor-not-allowed"
                                :class="(!chosenTarget() || sending || !targetPrintable) ? ''
                                    : (targetAllClear ? 'bg-[#287d3c] hover:bg-emerald-800' : 'bg-red-600 hover:bg-red-700')">
                                <span x-show="!sending">Print to <span
                                        x-text="chosenTarget()?.label || 'printer'"></span><span
                                        x-show="chosenTarget() && !targetAllClear"> anyway</span></span>
                                <span x-show="sending">Sending…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Shared per-printer "Print Settings" modal (opened by the Change button). --}}
        @include('partials.print-settings-modal')
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
    <script src="{{ asset('js/print-settings.js') }}?v={{ filemtime(public_path('js/print-settings.js')) }}"></script>
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

        const V2_SIZE_OPTIONS = {{ Illuminate\Support\Js::from($sizeOptions) }};
        const V2_SIZE_DIMS_RAW = {{ Illuminate\Support\Js::from($sizeDims) }};
        const V2_SIZE_DIMS = {};
        Object.entries(V2_SIZE_DIMS_RAW || {}).forEach(([code, d]) => {
            if (d) V2_SIZE_DIMS[code] = { w: d[0], h: d[1] };
        });

        function printPickerV2() {
            return {
                // Shared Print Settings modal (state + live-capability loading).
                ...window.printSettingsMixin({
                    sizeOptions: V2_SIZE_OPTIONS,
                    sizeDims: V2_SIZE_DIMS
                }),

                visible: false,
                loading: false,
                sending: false,
                payload: null,
                choice: null,
                error: '',
                pcState: 'idle',
                pcPrinters: [],
                defaultPrinter: null,
                editingKey: null,
                savingTray: false,

                async open(prepareUrl) {
                    this.visible = true;
                    this.loading = true;
                    this.payload = null;
                    this.choice = null;
                    this.error = '';
                    this.acks = {}; // fresh acknowledgements per order
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
                        // The shared Print Settings mixin reads the live bridge from
                        // window.__ptInstance when loading a printer's capabilities.
                        window.__ptInstance = pp;
                        const list = await pp.getPrinters();
                        const all = Array.isArray(list) ? list : [list].filter(Boolean);
                        this.pcPrinters = all.map(p => p.name);
                        this.defaultPrinter = (all.find(p => p.isDefault) || {}).name || null;
                        this.pcState = 'ok';
                    } catch (e) {
                        this.pcState = 'error';
                    }
                },

                // Open the shared Print Settings modal for one tray row: seed the
                // buffer from its saved values, then load the printer's live caps.
                openConfig(t) {
                    if (!t || !t.enabled) return;
                    this.editingKey = t.key;
                    this.psResetCaps();
                    if (t.size) this.printPaperSize = t.size;
                    this.printLandscape = t.landscape || false;

                    // Lock orientation by the order's product page count (same rule as
                    // the customer preview flow): 2-page → Landscape-only, 4-page →
                    // Portrait-only. The shared modal greys/reddens the disallowed
                    // option and forces the correct one. null = both allowed.
                    const pages = parseInt(this.payload?.pages_count);
                    if (pages === 2) {
                        this.orientationLock = 'landscape';
                        this.printLandscape = true;
                    } else if (pages === 4) {
                        this.orientationLock = 'portrait';
                        this.printLandscape = false;
                    } else {
                        this.orientationLock = null;
                    }
                    this.printDuplex = t.duplex || 'simplex';
                    this.printColor = t.color !== undefined ? t.color : true;
                    this.printInputBin = t.input_bin || '';
                    this.printQuality = t.quality || '';
                    this.printMediaType = t.media_type_live || '';
                    this.printScaleMode = t.scale_mode || 'fit';
                    this.printScaleFactor = t.scale_factor || 100;

                    // Enable the shared modal's preflight checks against THIS order.
                    this.psDesign = {
                        w: parseFloat(this.payload?.order?.size_width) || 0,
                        h: parseFloat(this.payload?.order?.size_height) || 0,
                        pages: parseInt(this.payload?.pages_count) || 0,
                    };
                    this.psShowChecks = true;
                    this.acks = {}; // reset acknowledgements for this open

                    this.psOpen(t.label, t.printer || '', null);

                    // Auto-select the Paper Size that matches THIS ORDER's size
                    // (e.g. a 5 × 7 order → the 5 × 7 paper option), ignoring
                    // orientation. Try the static list immediately, then again after
                    // the printer's LIVE sizes load (psLoad resets to the first size,
                    // so re-apply the match afterwards).
                    const ow = this.payload?.order?.size_width;
                    const oh = this.payload?.order?.size_height;
                    if (ow && oh) this.psSelectSizeForDims(ow, oh);
                    (async () => {
                        if (t.printer) await this.psLoad(t.printer);
                        if (ow && oh) this.psSelectSizeForDims(ow, oh);
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    })();
                },

                // Called by the modal's Apply button — persist this one tray's
                // settings, then refresh its chips from the server response.
                async applyPaperSettings() {
                    if (this.savingTray || !this.editingKey) return;
                    this.savingTray = true;
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ||
                        document.querySelector('input[name="_token"]')?.value;
                    const dim = this.psDim();
                    const body = {
                        key: this.editingKey,
                        size: this.printPaperSize,
                        size_width: dim ? dim.w : '',
                        size_height: dim ? dim.h : '',
                        landscape: this.printLandscape ? 1 : 0,
                        duplex: this.printDuplex,
                        color: this.printColor ? 1 : 0,
                        input_bin: this.printInputBin,
                        quality: this.printQuality,
                        media_type_live: this.printMediaType,
                        scale_mode: this.printScaleMode || 'fit',
                        scale_factor: this.printScaleFactor || 100,
                    };
                    try {
                        const res = await fetch("{{ route('storepanel.trays.saveOne') }}", {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify(body),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.ok) {
                            this.error = data.error || 'Could not save printer settings.';
                        } else if (data.tray && this.payload) {
                            const idx = this.payload.trays.findIndex(x => x.key === data.tray.key);
                            if (idx !== -1) this.payload.trays[idx] = data.tray;
                        }
                    } catch (e) {
                        this.error = 'Network error while saving printer settings.';
                    } finally {
                        this.savingTray = false;
                        this.showPaperModal = false;
                        this.editingKey = null;
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
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
                            size_pretty: t.size_pretty,
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
                            media_type_live: t.media_type_live,
                            scale_mode: t.scale_mode,
                            scale_factor: t.scale_factor
                        };
                    }
                    return null;
                },

                // Order size string, ordered by the intended orientation:
                //   portrait  → width small, height large ("5 × 7")
                //   landscape → width large, height small ("7 × 5")
                // Falls back to the raw server string when numeric dims are absent.
                orderSizePretty() {
                    const o = this.payload?.order;
                    if (!o) return 'unknown';
                    const w = parseFloat(o.size_width), h = parseFloat(o.size_height);
                    if (!(w > 0) || !(h > 0)) return o.size || 'unknown';
                    const pages = parseInt(this.payload?.pages_count);
                    let land;
                    if (pages === 2) land = true;
                    else if (pages === 4) land = false;
                    else land = w > h;
                    const sw = land ? Math.max(w, h) : Math.min(w, h);
                    const sh = land ? Math.min(w, h) : Math.max(w, h);
                    return sw + ' × ' + sh + ' inch';
                },

                // Preflight checks for the currently CHOSEN tray (Pick-a-tray modal).
                targetChecks() {
                    const t = this.chosenTarget();
                    if (!t) return [];
                    return this.psComputeChecks({
                        designW: this.payload?.order?.size_width,
                        designH: this.payload?.order?.size_height,
                        pages: this.payload?.pages_count,
                        paperCode: t.size,
                        paperLabel: t.size_pretty || t.size,
                        paperDim: { w: parseFloat(t.size_width), h: parseFloat(t.size_height) },
                        chosenLandscape: t.landscape,
                    });
                },
                get targetPrintable() { return this.psChecksPrintable(this.targetChecks()); },
                get targetAllClear() { return this.psChecksAllClear(this.targetChecks()); },

                close() {
                    this.visible = false;
                    this.psShowChecks = false;
                },

                async confirm() {
                    const target = this.chosenTarget();
                    if (!target || this.sending) return;

                    // Block printing until every failing check with a checkbox is
                    // acknowledged — errors (size / orientation) AND warnings (the
                    // non-E2E "Print with a white border" box). Orientation itself
                    // is auto-corrected at print time by printOrder().
                    if (!this.targetPrintable) return;

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
