{{--
    "Print Settings" modal for /new-testing/print/pdf/check (new PrintTrays
    Adobe bridge). Forked from test-print-settings-modal; adds the new demo's
    options — printer default orientation, editable custom size, safety shrink,
    fast-raster, native image path, classic image engine.
    Requires public/js/new-test-print-settings.js.

    Shared "Print Settings" modal.

    Requires the host Alpine component to include window.printSettingsMixin(...)
    (see public/js/print-settings.js) and to define an applyPaperSettings()
    method that persists / applies the chosen settings on "Apply".

    Bindings used (all provided by the mixin):
      showPaperModal, psLabel, psDriver, psStatusText, psStatusOk,
      printPaperSize / psPaperSizeList(), sizesLoading, sizesSource,
      printLandscape / orientationCaps, printDuplex / duplexOptions,
      printColor / colorCaps, printInputBin / inputBins,
      printQuality / resolutions, printMediaType / mediaTypes, capsSource
--}}
<div x-show="showPaperModal" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4"
    @keydown.escape.window="showPaperModal = false">
    <div class="absolute inset-0 bg-black/40" @click="showPaperModal = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-h-[90vh] overflow-hidden flex flex-col md:flex-row"
        :class="psHasPreview ? 'max-w-4xl' : 'max-w-md'" @click.stop>

        {{-- Live preview column (only when the host enables it: check & own-design
             pages). Shows the design on the selected paper exactly as it will print
             — E2E fills the sheet, non-E2E leaves the white border, and the Scale /
             Orientation choices update it in real time. --}}
        <template x-if="psHasPreview">
            <div
                class="w-full md:flex-1 md:w-1/2 bg-[#f2f7f2] border-b md:border-b-0 md:border-r border-slate-200 p-5 flex flex-col items-center justify-center gap-2 overflow-y-auto">
                <p class="mono text-[10px] text-slate-500 text-center px-2 max-w-full truncate" x-text="trayPaperInfo()">
                </p>
                {{-- Page navigation for multi-page files / designs. --}}
                <div x-show="psPreviewCount > 1" class="flex items-center gap-2 text-[11px] text-slate-500">
                    <button type="button" @click="psPreviewPrev()" :disabled="psPreviewIndex <= 0"
                        class="w-6 h-6 rounded-lg border border-slate-200 bg-white flex items-center justify-center hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    </button>
                    <span class="tabular-nums"><span x-text="psPreviewIndex + 1"></span> / <span
                            x-text="psPreviewCount"></span></span>
                    <button type="button" @click="psPreviewNext()" :disabled="psPreviewIndex >= psPreviewCount - 1"
                        class="w-6 h-6 rounded-lg border border-slate-200 bg-white flex items-center justify-center hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div class="relative shadow-lg border border-slate-800 overflow-hidden" :style="trayPaperStyle()">
                    <div class="absolute inset-0 pointer-events-none" x-show="!isE2ESelected()"
                        style="background: repeating-linear-gradient(45deg, #ffffff 0 4px, rgba(229,83,75,.3) 4px 8px);">
                    </div>
                    <div class="absolute inset-0 bg-white" x-show="isE2ESelected()"></div>
                    <div class="absolute bg-white overflow-hidden" :style="trayContentStyle()">
                        <div class="absolute left-1/2 top-1/2" :style="trayFileStyle()">
                            <img x-show="psPreviewSrc" :src="psPreviewSrc" class="object-contain"
                                style="width:100%; height:100%; display:block;" draggable="false">
                        </div>
                    </div>
                </div>
                <div x-show="!isE2ESelected()" class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-1">
                    <span class="inline-block w-2.5 h-2.5 shrink-0"
                        style="background: repeating-linear-gradient(45deg, #ffffff 0 2px, rgba(229,83,75,.3) 2px 4px); border: 1px solid #d1d5db;"></span>
                    <span>White border on every side</span>
                </div>
                <div x-show="isE2ESelected()" class="flex items-center gap-1.5 text-[10px] text-emerald-700 mt-1">
                    <span>Borderless — prints to the edge</span>
                </div>
            </div>
        </template>

        <div class="flex-1 overflow-y-auto p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-slate-900 text-base">Print Settings</h3>
            <button type="button" @click="showPaperModal = false"
                class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div x-show="psLabel" class="mb-4">
            <p class="text-[12px] text-slate-500">
                Configuring <span class="font-bold text-slate-700" x-text="psLabel"></span>
            </p>
            <p class="text-[11px] text-slate-400 mt-0.5" x-show="psDriver" x-text="psDriver"></p>
            <p class="text-[11px] mt-0.5" x-show="psStatusText"
                :class="psStatusOk ? 'text-emerald-600' : 'text-amber-600'" x-text="'Status: ' + psStatusText"></p>
        </div>

        <div class="grid grid-cols-2 gap-x-3 gap-y-4">
            {{-- Paper Size --}}
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    Paper Size
                    <span x-show="sizesLoading"
                        class="w-3 h-3 border-2 border-slate-300 border-t-[#287d3c] rounded-full animate-spin inline-block"></span>
                    <span x-show="sizesSource === 'printer'"
                        class="text-[10px] font-bold text-[#287d3c] bg-[#f2f7f2] px-1.5 py-0.5 rounded"
                        title="Fetched live from the selected printer">LIVE</span>
                    <span x-show="sizesSource === 'printer' && psIsCustomMedia()"
                        class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded"
                        title="A custom page size defined in the printer driver">CUSTOM</span>
                </label>
                <select x-model="printPaperSize"
                    class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                    <template x-for="opt in psPaperSizeList()" :key="opt.value">
                        <option :value="opt.value" x-text="opt.label"></option>
                    </template>
                </select>
                {{-- Driver custom page size (e.g. Noritsu "PostScript Custom Page
                     Size"): operator sets exact W×H in inches; sent to the bridge as
                     an explicit pageSize. Pre-filled with the driver's last-used size. --}}
                <div x-show="psIsCustomMedia()" x-cloak class="mt-2">
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Width (in)</label>
                            <input type="number" x-model.number="printCustomW" min="0.1" step="0.01"
                                :disabled="customSizeLocked" :readonly="customSizeLocked"
                                class="w-full rounded-lg border border-slate-200 text-sm py-2 px-2.5"
                                :class="customSizeLocked ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none'">
                        </div>
                        <span class="pb-2.5 text-slate-400">×</span>
                        <div class="flex-1">
                            <label class="text-[11px] font-semibold text-slate-600 mb-1 block">Height (in)</label>
                            <input type="number" x-model.number="printCustomH" min="0.1" step="0.01"
                                :disabled="customSizeLocked" :readonly="customSizeLocked"
                                class="w-full rounded-lg border border-slate-200 text-sm py-2 px-2.5"
                                :class="customSizeLocked ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none'">
                        </div>
                    </div>
                    <p class="text-[11px] text-amber-700 mt-1 leading-snug" x-show="customSizeLocked">
                        Custom page size from the printer driver<span x-show="customSizeName"
                            x-text="' (' + customSizeName + ')'"></span>, locked to the size saved in the
                        printer's preferences so its bleed/E2E setting carries over. To change it, edit
                        Properties → Preferences → Advanced, then reload.
                    </p>
                    <p class="text-[11px] text-amber-700 mt-1 leading-snug" x-show="!customSizeLocked">
                        Windows has no saved custom size for this printer yet — type one, or set it in
                        Properties → Preferences → Advanced and reload.
                    </p>
                    {{-- Printer's CURRENT default orientation (new bridge:
                         getPrinterCurrentDefaults). Custom-size prints go through
                         Acrobat's silent print, which always uses it. --}}
                    <div class="mt-2 text-[11px] leading-snug rounded-lg px-2.5 py-2 border"
                        :class="psCurrentDefaults && psCurrentDefaults.orientation === 'landscape' ?
                            'bg-amber-50 border-amber-300 text-amber-800' :
                            'bg-slate-50 border-transparent text-slate-500'">
                        <span x-show="!psCurrentDefaults">Checking the printer's current default orientation…</span>
                        <span x-show="psCurrentDefaults && psCurrentDefaults.orientation === 'unknown'">
                            Couldn't read the printer's current default orientation.</span>
                        <span
                            x-show="psCurrentDefaults && (psCurrentDefaults.orientation === 'portrait' || psCurrentDefaults.orientation === 'landscape')">
                            Printer's current default orientation:
                            <b x-text="psCurrentDefaults && psCurrentDefaults.orientation === 'landscape' ? 'Landscape' : 'Portrait'"></b>.
                            Custom-size prints go through Acrobat's silent print, which always uses this.
                            <span x-show="psCurrentDefaults && psCurrentDefaults.orientation === 'landscape'">
                                For an upright (portrait) print, open this printer's Windows “Printing
                                Preferences” once and set Orientation to Portrait.</span>
                        </span>
                    </div>
                    <label class="flex items-start gap-2 mt-2 cursor-pointer select-none text-[11px] text-slate-600">
                        <input type="checkbox" x-model="printSafetyShrink"
                            class="w-3.5 h-3.5 mt-0.5 rounded border-slate-300 accent-[#287d3c] shrink-0">
                        <span>Shrink ~4% for safety — only if the loaded paper might be smaller than this size
                            (e.g. plain stock instead of bleed stock). Leave off when the paper matches.</span>
                    </label>
                </div>
            </div>

            {{-- Orientation --}}
            <div>
                <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    Orientation
                    <span x-show="capsSource === 'printer'"
                        class="text-[10px] font-bold text-[#287d3c] bg-[#f2f7f2] px-1.5 py-0.5 rounded"
                        title="Fetched live from the selected printer">LIVE</span>
                </label>
                <div class="flex gap-1.5">
                    <button type="button" @click="orientationLock !== 'landscape' && (printLandscape = false)"
                        x-show="orientationCaps.includes('portrait')" :disabled="orientationLock === 'landscape'"
                        :title="orientationLock === 'landscape' ? 'This design must be printed in Landscape' : ''"
                        class="flex-1 flex items-center justify-center gap-1 mono text-[11px] px-2 py-2.5 rounded-lg border transition"
                        :class="orientationLock === 'landscape'
                            ?
                            'border-red-200 text-red-400 bg-red-50 cursor-not-allowed' :
                            (!printLandscape ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                                'border-slate-200 text-slate-600 hover:border-slate-300')">
                        <svg class="w-3.5 h-[18px] shrink-0" viewBox="0 0 16 20" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <rect x="2" y="1" width="12" height="18" rx="1.5" />
                        </svg>
                        Portrait
                    </button>
                    <button type="button" @click="orientationLock !== 'portrait' && (printLandscape = true)"
                        x-show="orientationCaps.includes('landscape')" :disabled="orientationLock === 'portrait'"
                        :title="orientationLock === 'portrait' ? 'This design must be printed in Portrait' : ''"
                        class="flex-1 flex items-center justify-center gap-1 mono text-[11px] px-2 py-2.5 rounded-lg border transition"
                        :class="orientationLock === 'portrait'
                            ?
                            'border-red-200 text-red-400 bg-red-50 cursor-not-allowed' :
                            (printLandscape ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                                'border-slate-200 text-slate-600 hover:border-slate-300')">
                        <svg class="w-[18px] h-3.5 shrink-0" viewBox="0 0 20 16" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <rect x="1" y="2" width="18" height="12" rx="1.5" />
                        </svg>
                        Landscape
                    </button>
                </div>
            </div>

            {{-- Duplex --}}
            <div x-show="duplexOptions.length > 1">
                <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    Duplex
                    <span x-show="capsSource === 'printer'"
                        class="text-[10px] font-bold text-[#287d3c] bg-[#f2f7f2] px-1.5 py-0.5 rounded"
                        title="Fetched live from the selected printer">LIVE</span>
                </label>
                <select x-model="printDuplex"
                    class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                    <template x-for="d in duplexOptions" :key="d.value">
                        <option :value="d.value" x-text="d.label"></option>
                    </template>
                </select>
            </div>

            {{-- Page order (MULTI-PAGE only) — mirrors the PrintTrays demo. Only
                 affects multi-page PDFs; a one-page doc or a photo has no order to
                 reverse. Guarded with typeof so an older cached print-settings.js
                 (without printPageOrder) can't crash Alpine. --}}
            <template x-if="typeof printPageOrder !== 'undefined'">
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        Page order
                        <span
                            class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded"
                            title="Only affects multi-page PDFs — a one-page document or photo has no order to reverse">MULTI-PAGE</span>
                    </label>
                    <select x-model="printPageOrder"
                        class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                        <template x-for="o in pageOrderOptions" :key="o.value">
                            <option :value="o.value" x-text="o.label"></option>
                        </template>
                    </select>
                </div>
            </template>

            {{-- Color Mode --}}
            <div x-show="colorCaps.length > 1">
                <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    Color
                    <span x-show="capsSource === 'printer'"
                        class="text-[10px] font-bold text-[#287d3c] bg-[#f2f7f2] px-1.5 py-0.5 rounded"
                        title="Fetched live from the selected printer">LIVE</span>
                </label>
                <div class="flex gap-1.5">
                    <button type="button" @click="printColor = true" x-show="colorCaps.includes('color')"
                        class="flex-1 mono text-[11px] px-2 py-2.5 rounded-lg border transition text-center"
                        :class="printColor ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                            'border-slate-200 text-slate-600 hover:border-slate-300'">Color</button>
                    <button type="button" @click="printColor = false" x-show="colorCaps.includes('mono')"
                        class="flex-1 mono text-[11px] px-2 py-2.5 rounded-lg border transition text-center"
                        :class="!printColor ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                            'border-slate-200 text-slate-600 hover:border-slate-300'">B&amp;W</button>
                </div>
            </div>

            {{-- Scale (wired) — guarded with typeof so an older cached
                 print-settings.js (without printScaleMode) can't crash Alpine;
                 the block simply doesn't render until the updated JS is loaded. --}}
            <template x-if="typeof printScaleMode !== 'undefined'">
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                        Scale
                    </label>
                    <div class="flex gap-2 items-center">
                        {{-- On an E2E (borderless) paper only "Fit to paper" makes
                             sense, so hide the other modes and force 'fit'. --}}
                        <select x-model="printScaleMode"
                            x-effect="if (isE2ESelected() && printScaleMode !== 'fit') printScaleMode = 'fit'"
                            class="flex-1 rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                            <option value="fit">Fit to paper</option>
                            <template x-if="!isE2ESelected()">
                                <option value="fit_area">Fit to printable area</option>
                            </template>
                            <template x-if="!isE2ESelected()">
                                <option value="actual">Actual size</option>
                            </template>
                            <template x-if="!isE2ESelected()">
                                <option value="custom">Custom</option>
                            </template>
                        </select>
                        {{-- Custom percentage — only meaningful when "Custom" is picked. --}}
                        <div class="flex items-center gap-1 rounded-xl border px-2 shrink-0 transition"
                            x-show="printScaleMode === 'custom'" :class="'border-[#287d3c] bg-[#f2f7f2]'">
                            <input type="number" min="1" max="400" x-model.number="printScaleFactor"
                                class="w-12 text-center bg-transparent text-[12px] font-bold text-[#287d3c] py-2 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <span class="text-[11px] font-semibold text-[#287d3c]">%</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-amber-700 mt-1" x-show="psIsCustomMedia()">
                        Custom size in use — Scale is always sent as “Fit” (the custom-size print path requires it).
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1"
                        x-text="printScaleMode === 'fit' ? 'Fit to paper — fills the whole sheet, no white space (may crop).' :
                            (printScaleMode === 'fit_area' ? 'Fit to content — whole design fits inside, may leave a white border.' :
                            (printScaleMode === 'actual' ? 'Prints at the file\'s true size.' :
                            'Scales by the percentage you set.'))">
                    </p>
                </div>
            </template>

            {{-- Paper Source (wired) --}}
            <div x-show="inputBins.length > 0">
                <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    Paper Source
                    <span class="text-[10px] font-bold text-[#287d3c] bg-[#f2f7f2] px-1.5 py-0.5 rounded"
                        title="Fetched live from the selected printer">LIVE</span>
                </label>
                <select x-model="printInputBin"
                    class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                    <option value="">Printer default</option>
                    <template x-for="b in inputBins" :key="b.value">
                        <option :value="b.value" x-text="b.label"></option>
                    </template>
                </select>
            </div>

            {{-- Quality (reference only) --}}
            <div x-show="resolutions.length > 0">
                <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    Quality
                    <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded"
                        title="Reported by the printer, but not yet applied to the print job">NOT WIRED</span>
                </label>
                <select x-model="printQuality"
                    class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                    <option value="">Printer default</option>
                    <template x-for="r in resolutions" :key="r.value">
                        <option :value="r.value" x-text="r.label"></option>
                    </template>
                </select>
            </div>

            {{-- Media Type (reference only) --}}
            <div x-show="mediaTypes.length > 0">
                <label class="text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-1.5">
                    Media Type
                    <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded"
                        title="Reported by the printer, but not yet applied to the print job">NOT WIRED</span>
                </label>
                <select x-model="printMediaType"
                    class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                    <option value="">Printer default</option>
                    <template x-for="m in mediaTypes" :key="m.value">
                        <option :value="m.value" x-text="m.label"></option>
                    </template>
                </select>
            </div>
        </div>

        {{-- New PrintTrays (Adobe) bridge options — same as the new demo's
             upload section. Guarded with typeof so a stale cached mixin can't
             crash Alpine. --}}
        <template x-if="typeof printFastRaster !== 'undefined'">
            <div class="mt-5 border-t border-slate-100 pt-4 space-y-2.5">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Print engine</p>
                <label class="flex items-start gap-2 cursor-pointer select-none text-[12px] text-slate-600">
                    <input type="checkbox" x-model="printFastRaster"
                        class="w-4 h-4 mt-0.5 rounded border-slate-300 accent-[#287d3c] shrink-0">
                    <span><b class="text-slate-700">Fast-raster mode</b> (experimental) — rasterizes the PDF and
                        sends a raw image feed instead of the normal PDF pipeline. Much faster on some drivers,
                        but only works if the printer queue accepts a raw image feed.</span>
                </label>
                <label class="flex items-start gap-2 cursor-pointer select-none text-[12px] text-slate-600"
                    x-show="file && file.type === 'image'">
                    <input type="checkbox" x-model="printImageAsImage"
                        class="w-4 h-4 mt-0.5 rounded border-slate-300 accent-[#287d3c] shrink-0">
                    <span><b class="text-slate-700">Send image as image</b> — uses the bridge's fast image path
                        (like Ctrl+P) instead of wrapping the photo in a PDF first. The whole image always
                        prints — never cropped; any gap to the paper's shape is filled by extending its edges.</span>
                </label>
                <label class="flex items-start gap-2 cursor-pointer select-none text-[12px] text-slate-600 pl-6"
                    x-show="file && file.type === 'image' && printImageAsImage">
                    <input type="checkbox" x-model="printClassicImageEngine"
                        class="w-4 h-4 mt-0.5 rounded border-slate-300 accent-[#287d3c] shrink-0">
                    <span>Use the classic print engine for images (slower — only for comparing speed)</span>
                </label>
            </div>
        </template>

        {{-- Reference-only notice --}}
        <p x-show="printQuality || printMediaType" x-cloak class="text-[11px] text-slate-400 mt-3 leading-relaxed">
            Quality / Media Type are shown for reference only — the print pipeline can't yet set a driver's DPI
            or media-type option, so these won't change the actual printout.
        </p>

        {{-- Preflight checks (only when the host enables them via psShowChecks) --}}
        <template x-if="psShowChecks">
            <div class="mt-5 border-t border-slate-100 pt-4 space-y-1">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Checks</p>
                <template x-for="c in psModalChecks()" :key="'m-' + c.key">
                    <div class="flex items-start gap-2.5 py-2.5 transition-colors"
                        :class="c.ok ? 'border-b border-slate-100' : (c.severity === 'error' ?
                            'bg-red-50 border border-red-100 rounded-lg px-3 my-1' :
                            'bg-amber-50 border border-amber-100 rounded-lg px-3 my-1')">
                        <template x-if="c.ok">
                            <svg class="w-4 h-4 text-[#287d3c] mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </template>
                        <template x-if="!c.ok && c.severity === 'warn'">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                <path d="M12 9v4" />
                                <path d="M12 17h.01" />
                            </svg>
                        </template>
                        <template x-if="!c.ok && c.severity === 'error'">
                            <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </template>
                        <div class="min-w-0">
                            <p class="text-[13px] font-bold text-slate-900" x-text="c.title"></p>
                            <p class="text-[12px] text-slate-500 mt-0.5" x-text="c.detail"></p>
                            <template x-if="!c.ok && c.ackLabel">
                                <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
                                    <input type="checkbox" x-model="acks[c.key]"
                                        class="w-4 h-4 rounded border-slate-300 shrink-0"
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

        <div class="flex gap-3 mt-6">
            <button type="button" @click="applyPaperSettings()"
                :disabled="psApplying || (psShowChecks && !psChecksPrintable(psModalChecks()))"
                class="flex-1 flex items-center justify-center gap-2 text-white font-bold py-2.5 rounded-xl text-sm transition disabled:bg-slate-300 disabled:cursor-not-allowed"
                :class="(psApplying || (psShowChecks && !psChecksPrintable(psModalChecks()))) ? '' :
                ((psShowChecks && !psChecksAllClear(psModalChecks())) ?
                    'bg-red-600 hover:bg-red-700' :
                    'bg-[#287d3c] hover:bg-emerald-800')">
                <template x-if="psApplying">
                    <span class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                </template>
                <span x-text="psApplying ? 'Saving…' : 'Apply'"></span>
            </button>
            <button type="button" @click="showPaperModal = false" :disabled="psApplying"
                class="flex-1 border border-slate-200 text-slate-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Cancel
            </button>
        </div>
        </div>
    </div>
</div>
