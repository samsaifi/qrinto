@extends('layouts.quick-flow-pc')
@section('title', 'Check & print | Qrinto')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[1080px] mx-auto"
            x-data="checkFlow(
                {{ Illuminate\Support\Js::from(['url' => $file['url'], 'name' => $file['name'], 'bytes' => $file['bytes']]) }},
                {{ Illuminate\Support\Js::from($trays) }},
                {{ Illuminate\Support\Js::from($trayDims) }}
            )" x-init="init()">

            <a href="{{ route('localprint.pdf') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                {{-- Left: file + specs --}}
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-bold text-slate-900 text-sm truncate" x-text="file.name" :title="file.name"></h2>
                            <p class="mono text-[11px] text-slate-400 mt-0.5" x-text="prettySize(file.bytes)"></p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="prevPage()" :disabled="page <= 1 || pages <= 1"
                                class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            </button>
                            <span class="mono text-[11px] text-slate-500 tabular-nums w-14 text-center">
                                <span x-text="page"></span> / <span x-text="pages || '—'"></span>
                            </span>
                            <button type="button" @click="nextPage()" :disabled="page >= pages || pages <= 1"
                                class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="bg-[#f2f7f2] rounded-xl mt-4 p-6 flex items-center justify-center relative min-h-[280px]">
                        <div x-show="!ready" x-cloak class="text-center">
                            <div class="w-8 h-8 border-2 border-slate-300 border-t-[#287d3c] rounded-full animate-spin mx-auto"></div>
                            <p class="mono text-[11px] text-slate-500 mt-3">Reading PDF…</p>
                        </div>
                        <div x-show="pdfError" x-cloak class="text-center">
                            <i data-lucide="file-warning" class="w-6 h-6 text-red-500 mx-auto"></i>
                            <p class="text-[12px] text-red-600 font-medium mt-2" x-text="pdfError"></p>
                        </div>
                        <canvas x-ref="preview" x-show="ready && !pdfError"
                            class="max-w-full max-h-[320px] rounded-sm shadow-lg bg-white"></canvas>
                    </div>

                    <div class="mono text-[13px] text-slate-500 mt-5 space-y-1.5">
                        <p><span class="text-slate-900 font-bold" x-text="pages || '—'"></span> pages</p>
                        <p><span class="text-slate-900 font-bold">
                            <template x-if="dims"><span x-text="`${fmt(dims.w)} × ${fmt(dims.h)} in`"></span></template>
                            <template x-if="!dims"><span>—</span></template>
                        </span> <span x-text="orientation ? `(${orientation})` : ''"></span></p>
                        <p><span class="text-slate-900 font-bold" x-text="dpi ? `${dpi} dpi` : '—'"></span> effective at final size</p>
                        <p><span class="text-slate-900 font-bold" x-text="colorMode || '—'"></span> color</p>
                    </div>
                </div>

                {{-- Right: preflight + tray + print --}}
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6">
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
                                            :class="orientationAction === 'rotate' ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'">Rotate the file</button>
                                        <button type="button" @click="orientationAction = 'asis'"
                                            class="mono text-[12px] px-3 py-1.5 rounded-lg border transition"
                                            :class="orientationAction === 'asis' ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' : 'border-slate-200 text-slate-600 hover:border-slate-300'">Print as is</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Dynamic check rows --}}
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

                    <form method="POST" action="{{ route('localprint.print') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="tray" :value="tray">
                        <input type="hidden" name="orientation" :value="orientationAction">

                        {{-- Tray selector, reactive --}}
                        <div class="space-y-2">
                            <template x-for="t in trays" :key="t.key">
                                <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                                    :class="t.out ? 'border-slate-100 bg-slate-50/60 cursor-not-allowed'
                                        : (tray === t.key ? 'border-[#287d3c] bg-[#f2f7f2]' : 'border-slate-200 hover:border-slate-300')">
                                    <input type="radio" name="tray_radio" x-model="tray" :value="t.key" :disabled="t.out"
                                        class="accent-[#287d3c] w-4 h-4" :class="t.out ? 'opacity-40' : ''">
                                    <span class="font-bold text-[13px]" :class="t.out ? 'text-slate-400' : 'text-slate-900'" x-text="t.label"></span>
                                    <span class="mono text-[12px] ml-1" :class="t.out ? 'text-slate-300' : 'text-slate-500'" x-text="t.desc"></span>
                                    <template x-if="t.out">
                                        <span class="ml-auto text-[12px] text-slate-400">Out of paper</span>
                                    </template>
                                </label>
                            </template>
                        </div>

                        <div class="flex items-center justify-between mt-5">
                            <label class="text-sm font-semibold text-slate-700">Copies</label>
                            <input type="number" name="copies" x-model.number="copies" min="1" max="999"
                                class="w-20 text-right rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                        </div>

                        <a href="{{ route('localprint.trays') }}" class="inline-block mt-4 text-[13px] font-bold text-[#287d3c] hover:text-emerald-800 transition-colors">
                            Change what is loaded in each tray
                        </a>

                        <button type="submit" :disabled="!canPrint"
                            class="w-full mt-4 bg-[#287d3c] hover:bg-emerald-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold py-3 rounded-xl text-sm transition active:scale-[0.99]">
                            <template x-if="canPrint"><span>Print <span x-text="copies"></span> <span x-text="copies == 1 ? 'copy' : 'copies'"></span></span></template>
                            <template x-if="!canPrint"><span x-text="blocker || 'Not ready to print'"></span></template>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script>
        // Point PDF.js at its matching worker. Same origin as the loader above
        // so PDF.js can boot even if the worker's own network fetch is slow.
        if (window['pdfjsLib']) {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://unpkg.com/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
        }

        function checkFlow(file, trays, trayDims) {
            // Hold the PDF.js objects in closure scope, not on the Alpine
            // component. Alpine wraps every returned property in a Proxy, and
            // PDF.js's PDFDocumentProxy uses private class fields (#d) that
            // throw when accessed through a Proxy — "Cannot read private
            // member #d from an object whose class did not declare it".
            let _pdf = null;

            return {
                file, trays, trayDims,

                // Reactive PDF state (metadata only — the doc itself lives in _pdf).
                page: 1,
                pages: 0,
                dims: null,          // { w, h } in inches, first page
                dpi: null,           // effective dpi at trim size (if raster estimate is derivable)
                colorMode: null,     // RGB / CMYK / Grayscale
                fontsEmbedded: null, // true/false when known
                fontCount: 0,
                ready: false,
                pdfError: null,

                // User choices
                tray: (trays.find(t => !t.out) || trays[0])?.key,
                copies: 1,
                orientationAction: 'rotate',

                async init() {
                    if (!window['pdfjsLib']) {
                        console.error('[localprint] PDF.js failed to load — check the CDN.');
                        this.pdfError = 'PDF viewer failed to load.';
                        this.ready = true;
                        return;
                    }
                    try {
                        // Fetch the PDF ourselves so we get a clear error if the URL is
                        // wrong, and can hand PDF.js the raw bytes (bypasses CORS on the
                        // worker for the actual document fetch).
                        const res = await fetch(file.url, { credentials: 'same-origin' });
                        if (!res.ok) throw new Error('HTTP ' + res.status + ' fetching ' + file.url);
                        const bytes = new Uint8Array(await res.arrayBuffer());

                        _pdf = await pdfjsLib.getDocument({ data: bytes }).promise;
                        this.pages = _pdf.numPages;

                        // Read first-page dimensions (PDF units = 1/72 in).
                        const first = await _pdf.getPage(1);
                        const vp = first.getViewport({ scale: 1 });
                        this.dims = { w: vp.width / 72, h: vp.height / 72 };

                        // Render page 1 and gather colour/font info.
                        await this.renderPage(1);
                        await this.introspectPage(first);

                        this.ready = true;
                    } catch (e) {
                        console.error('[localprint] PDF read failed:', e);
                        this.pdfError = (e && e.message) ? ('Could not read this PDF: ' + e.message) : 'Could not read this PDF.';
                        this.ready = true;
                    }
                },

                async renderPage(n) {
                    if (!_pdf) return;
                    this.page = n;
                    const page = await _pdf.getPage(n);
                    const scale = 1.4;
                    const vp = page.getViewport({ scale });
                    const canvas = this.$refs.preview;
                    const ctx = canvas.getContext('2d');
                    canvas.width = vp.width;
                    canvas.height = vp.height;
                    await page.render({ canvasContext: ctx, viewport: vp }).promise;
                },

                async introspectPage(page) {
                    try {
                        const ops = await page.getOperatorList();
                        const fnMap = pdfjsLib.OPS;
                        let usesRGB = false, usesCMYK = false, usesGray = false;
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
                        for (const item of (tc.items || [])) if (item.fontName) fontIds.add(item.fontName);
                        this.fontCount = fontIds.size;
                        this.fontsEmbedded = fontIds.size === 0 ? null : true; // best-effort: PDF.js resolves fonts, so if they're readable they're either embedded or substituted; assume embedded unless we can prove otherwise
                    } catch (e) { /* leave fields as null */ }
                },

                nextPage() { if (this.page < this.pages) this.renderPage(this.page + 1); },
                prevPage() { if (this.page > 1) this.renderPage(this.page - 1); },

                // ── Derived state ──────────────────────────────

                get selectedTray() { return this.trays.find(t => t.key === this.tray); },

                /** Physical dimensions of the media loaded in the selected tray. */
                get trayMedia() {
                    const t = this.selectedTray; if (!t || !t.size) return null;
                    const d = this.trayDims[t.size]; if (!d) return null;
                    return { w: d.w, h: d.h, size: t.size };
                },

                get orientation() {
                    if (!this.dims) return null;
                    return this.dims.w > this.dims.h ? 'landscape' : (this.dims.h > this.dims.w ? 'portrait' : 'square');
                },

                get trayOrientation() {
                    const m = this.trayMedia; if (!m) return null;
                    return m.w > m.h ? 'landscape' : (m.h > m.w ? 'portrait' : 'square');
                },

                get orientationCheck() {
                    if (!this.dims || !this.trayMedia) return null;
                    const ok = this.orientation === this.trayOrientation || this.orientation === 'square' || this.trayOrientation === 'square';
                    return {
                        ok,
                        detail: ok
                            ? 'File and media orient the same way.'
                            : `The file is ${this.orientation}. The selected media (${this.trayMedia.size.replace('x', ' × ')}) is ${this.trayOrientation}.`,
                    };
                },

                /** Approximate trim size (subtract 0.25" of bleed on each axis if the file is oversized to media). */
                get trimSize() {
                    const m = this.trayMedia; if (!this.dims) return null;
                    if (!m) return this.dims;
                    // Match orientation of media before comparing to describe trim size honestly.
                    const df = (this.orientation === this.trayOrientation) ? this.dims : { w: this.dims.h, h: this.dims.w };
                    return df;
                },

                get sizeCheck() {
                    if (!this.dims || !this.trayMedia) {
                        return { key: 'size', ok: !!this.dims, severity: 'warn', title: 'Size', detail: this.dims ? 'Select a tray to compare.' : 'Reading page size…' };
                    }
                    const df = this.trimSize;
                    const m = this.trayMedia;
                    const bleedW = Math.max(0, (df.w - m.w) / 2);
                    const bleedH = Math.max(0, (df.h - m.h) / 2);
                    const withinTrim = Math.abs(df.w - m.w) < 0.06 && Math.abs(df.h - m.h) < 0.06;
                    const withinBleed = df.w >= m.w && df.h >= m.h && df.w - m.w <= 0.5 && df.h - m.h <= 0.5;
                    if (withinTrim) {
                        return { key: 'size', ok: true, severity: 'ok', title: 'Size matches the media', detail: `${this.fmt(m.w)} × ${this.fmt(m.h)} in trim.` };
                    }
                    if (withinBleed) {
                        return { key: 'size', ok: true, severity: 'ok', title: 'Size matches the media', detail: `${this.fmt(m.w)} × ${this.fmt(m.h)} in trimmed, ${this.fmt(Math.max(bleedW, bleedH))} in bleed on all sides.` };
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
                    if (!this.dims || !this.trayMedia) return { key: 'res', ok: false, severity: 'warn', title: 'Resolution', detail: 'Waiting on size to check.' };
                    const df = this.trimSize;
                    // Effective dpi at the final print size (assuming the source is 300 dpi at file dims).
                    const eff = Math.round(300 * Math.min(this.trayMedia.w / df.w, this.trayMedia.h / df.h));
                    this.dpi = eff;
                    if (eff >= 250) return { key: 'res', ok: true, severity: 'ok', title: 'Resolution is high enough', detail: `${eff} dpi at final size.` };
                    return { key: 'res', ok: false, severity: 'warn', title: 'Resolution is low', detail: `${eff} dpi at final size. Print may look soft.` };
                },

                get fontCheck() {
                    if (this.fontsEmbedded === null) {
                        return { key: 'font', ok: false, severity: 'warn', title: 'Fonts', detail: 'Checking fonts…' };
                    }
                    if (this.fontCount === 0) {
                        return { key: 'font', ok: true, severity: 'ok', title: 'No text fonts to embed', detail: 'This file is image-only.' };
                    }
                    return { key: 'font', ok: true, severity: 'ok', title: 'Fonts are embedded', detail: `${this.fontCount} font${this.fontCount === 1 ? '' : 's'} found in the file.` };
                },

                get checks() {
                    if (!this.ready || this.pdfError) return [];
                    return [this.sizeCheck, this.resolutionCheck, this.fontCheck];
                },

                get canPrint() {
                    if (!this.ready || this.pdfError) return false;
                    if (!this.selectedTray || this.selectedTray.out) return false;
                    if (this.copies < 1) return false;
                    return true;
                },

                get blocker() {
                    if (!this.ready) return 'Reading PDF…';
                    if (this.pdfError) return 'Fix the PDF to continue';
                    if (!this.selectedTray || this.selectedTray.out) return 'Choose a loaded tray';
                    return null;
                },

                fmt(n) { return (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, ''); },
                prettySize(bytes) {
                    if (!bytes) return '';
                    const kb = bytes / 1024;
                    return kb < 1024 ? `${kb.toFixed(0)} KB` : `${(kb / 1024).toFixed(1)} MB`;
                },
            };
        }
    </script>
@endpush
