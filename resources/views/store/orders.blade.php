@extends('layouts.store')
@section('title', 'Orders')

@section('content')
    @php
        $stages = \App\Models\Order::pickupStages();
        $rPrefix = 'store.'; // reuse existing store.* order actions (advance / undo / download)
    @endphp

    {{-- Top action bar: Print logs & Kiosk logs links. --}}
    <div class="mb-4 md:mb-6 flex items-center justify-end gap-1.5 md:gap-3">
        <a href="{{ route('storepanel.printLogs') }}"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 md:px-4 md:py-2 rounded-lg md:rounded-xl border border-slate-200 bg-white text-slate-700 text-[12px] md:text-sm font-semibold hover:bg-slate-50 hover:text-[#287d3c] transition">
            <i data-lucide="printer" class="w-3.5 h-3.5 md:w-4 md:h-4"></i>
            Print logs
        </a>
        <a href="{{ route('storepanel.kioskLogs') }}"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 md:px-4 md:py-2 rounded-lg md:rounded-xl border border-slate-200 bg-white text-slate-700 text-[12px] md:text-sm font-semibold hover:bg-slate-50 hover:text-[#287d3c] transition">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 md:w-4 md:h-4"></i>
            Kiosk Logs
        </a>
    </div>

    @foreach (['success' => 'emerald', 'warning' => 'amber', 'error' => 'red'] as $flash => $color)
        @if (session($flash))
            <div
                class="mb-4 px-4 py-3 rounded-xl bg-{{ $color }}-50 border border-{{ $color }}-200 text-{{ $color }}-800 text-sm font-medium">
                {{ session($flash) }}</div>
        @endif
    @endforeach

    {{-- Queue: New / Printing / Ready for pickup as stacked groups. Each
         section shows just its stage label + count, with cards stacked below
         (mirrors the store-panel screenshot). --}}
    <div class="space-y-8">
        @foreach ($stages as $stageKey => $stage)
            @php $cards = $queue[$stageKey] ?? collect(); @endphp
            <section>
                <div class="flex items-baseline gap-2 mb-3">
                    <h2 class="font-display font-bold text-[15px] text-slate-900">{{ $stage['label'] }}</h2>
                    <span class="text-slate-400 text-[12px] font-medium mono">{{ $cards->count() }}</span>
                </div>

                @if ($cards->count())
                    <div class="space-y-3">
                        @foreach ($cards as $order)
                            @include('store.partials.queue-row', [
                                'order' => $order,
                                'rPrefix' => $rPrefix,
                                'store' => $store,
                            ])
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        {{-- Recently picked up - 5 per page. Older completions accessible
             via the pagination footer without cluttering the queue view. --}}
        @if ($recentlyDone->total() > 0)
            <section>
                <div class="flex items-baseline gap-2 mb-3">
                    <h2 class="font-display font-bold text-[15px] text-slate-900">Picked up</h2>
                    <span class="text-slate-400 text-[12px] font-medium mono">{{ $recentlyDone->total() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach ($recentlyDone as $order)
                        @include('store.partials.queue-row', [
                            'order' => $order,
                            'rPrefix' => $rPrefix,
                            'store' => $store,
                        ])
                    @endforeach
                </div>
                @if ($recentlyDone->hasPages())
                    <div class="mt-4">
                        {{ $recentlyDone->onEachSide(1)->links() }}
                    </div>
                @endif
            </section>
        @endif
    </div>

    {{-- Tray picker modal: opens on every "Print on 931BL" click, staff must choose a tray.
         The modal scans printers via PrintTrays in the background; if PrintTrays
         is not detected the footer surfaces a Download PrintTrays link. --}}
    <div x-data="printPicker()" x-cloak @open-print-modal.window="await open($event.detail.prepareUrl)" x-show="visible"
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

                    {{-- Section A: configured trays (recommendation lives here) --}}
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Configured trays
                        </p>
                        <div class="space-y-2">
                            <template x-for="t in payload.trays" :key="'tray-' + t.key">
                                <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                                    :class="choice === 'tray:' + t.key ? 'border-[#287d3c] bg-[#f2f7f2]' :
                                        'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="print_pick" :value="'tray:' + t.key" x-model="choice"
                                        class="accent-[#287d3c] w-4 h-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-[13px] text-slate-900">
                                            <span x-text="t.label"></span>
                                            <template x-if="t.recommended">
                                                <span
                                                    class="ml-2 text-[10px] font-extrabold text-[#287d3c] bg-[#eaf3ea] border border-[#bfdcc4] rounded-full px-2 py-0.5 uppercase tracking-wider">
                                                    Recommended
                                                </span>
                                            </template>
                                        </p>
                                        <p class="mono text-[11px] mt-0.5 truncate text-slate-500" :title="t.printer">
                                            <span x-text="t.size_pretty"></span> · <span x-text="t.media_label"></span>
                                            <template x-if="t.user_type"><span class="text-slate-400"> · UT<span
                                                        x-text="t.user_type"></span></span></template>
                                            <template x-if="t.printer"><span class="text-slate-400"> · <span
                                                        x-text="t.printer"></span></span></template>
                                        </p>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    <p x-show="error" x-text="error" class="text-[12px] text-red-600 font-medium"></p>

                    <div class="pt-3 sticky bottom-0 bg-white pb-1 space-y-2">
                        {{-- Footer install hint: only when the scan came back
                             empty or errored (PrintTrays missing / blocked). --}}
                        <div x-show="pcState === 'error' || (pcState === 'ok' && pcPrinters.length === 0)"
                            class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl bg-amber-50 border border-amber-200">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="printer-off" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                <p class="text-[12px] text-amber-800 leading-snug">
                                    Printer not visible? Install PrintTrays to detect this PC's printers.
                                </p>
                            </div>
                            <a href="https://noritsucanada.com/print-trays/download/" target="_blank" rel="noopener"
                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#287d3c] hover:bg-emerald-800 text-white text-[12px] font-bold transition">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                Download PrintTrays
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

@push('scripts')
    <script>
        // A. Delegate "Print on 931BL" clicks → open the tray-picker modal.
        //    The modal itself scans PrintTrays for printers and, if PrintTrays is
        //    not detected, surfaces a Download PrintTrays link in the footer.
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

        // B. Modal state: loads the tray list for the clicked order, lets the
        //    operator pick either a configured tray or any raw printer PrintTrays
        //    sees on this PC, then dispatches and advances the order.
        function printPicker() {
            return {
                visible: false,
                loading: false,
                sending: false,
                payload: null,
                choice: null, // 'tray:{key}' - key of a configured printer
                error: '',

                pcState: 'idle', // idle | loading | ok | error
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
                            },
                        });
                        const data = await res.json();
                        if (!res.ok || !data.ok) {
                            this.error = data.error || 'Could not prepare this order.';
                            this.loading = false;
                            return;
                        }
                        this.payload = data;
                        // Pre-select the recommended tray (auto-match), if any.
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
                    // Populate the PC printers list in parallel.
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

                /**
                 * Resolve the operator's radio choice to a concrete tray
                 * object the print bridge can send to. Includes the tray
                 * metadata so the print-log endpoint can snapshot it.
                 */
                chosenTarget() {
                    if (!this.choice || !this.payload) return null;
                    if (this.choice.startsWith('tray:')) {
                        const key = this.choice.slice(5);
                        const t = this.payload.trays.find(x => x.key === key);
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
                            density: t.density,
                            user_type: t.user_type,
                            landscape: t.landscape,
                            duplex: t.duplex,
                            color: t.color,
                            input_bin: t.input_bin,
                            quality: t.quality,
                            media_type_live: t.media_type_live,
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
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ||
                        document.querySelector('input[name="_token"]')?.value;

                    const ok = await window.__qrintoPT.printOrder(target, this.payload, csrf);
                    this.sending = false;
                    if (ok) {
                        this.close();
                        window.location.reload();
                    }
                },
            };
        }
    </script>
@endpush
