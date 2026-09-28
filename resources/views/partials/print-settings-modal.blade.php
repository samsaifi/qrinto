{{--
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
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6" @click.stop>
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
                :class="psStatusOk ? 'text-emerald-600' : 'text-amber-600'"
                x-text="'Status: ' + psStatusText"></p>
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
                </label>
                <select x-model="printPaperSize"
                    class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                    <template x-for="opt in psPaperSizeList()" :key="opt.value">
                        <option :value="opt.value" x-text="opt.label"></option>
                    </template>
                </select>
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
                    <button type="button" @click="printLandscape = false"
                        x-show="orientationCaps.includes('portrait')"
                        class="flex-1 flex items-center justify-center gap-1 mono text-[11px] px-2 py-2.5 rounded-lg border transition"
                        :class="!printLandscape ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                            'border-slate-200 text-slate-600 hover:border-slate-300'">
                        <svg class="w-3.5 h-[18px] shrink-0" viewBox="0 0 16 20" fill="none" stroke="currentColor"
                            stroke-width="1.5">
                            <rect x="2" y="1" width="12" height="18" rx="1.5" />
                        </svg>
                        Portrait
                    </button>
                    <button type="button" @click="printLandscape = true"
                        x-show="orientationCaps.includes('landscape')"
                        class="flex-1 flex items-center justify-center gap-1 mono text-[11px] px-2 py-2.5 rounded-lg border transition"
                        :class="printLandscape ? 'border-[#287d3c] text-[#287d3c] bg-[#f2f7f2] font-bold' :
                            'border-slate-200 text-slate-600 hover:border-slate-300'">
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
                        <select x-model="printScaleMode"
                            class="flex-1 rounded-xl border border-slate-200 text-sm py-2.5 px-3 bg-white focus:ring-2 focus:ring-[#287d3c] focus:outline-none">
                            <option value="fit">Fit to paper (edge-to-edge)</option>
                            <option value="fit_area">Fit to printable area</option>
                            <option value="actual">Actual size</option>
                            <option value="custom">Custom</option>
                        </select>
                        {{-- Custom percentage — only meaningful when "Custom" is picked. --}}
                        <div class="flex items-center gap-1 rounded-xl border px-2 shrink-0 transition"
                            x-show="printScaleMode === 'custom'"
                            :class="'border-[#287d3c] bg-[#f2f7f2]'">
                            <input type="number" min="1" max="400" x-model.number="printScaleFactor"
                                class="w-12 text-center bg-transparent text-[12px] font-bold text-[#287d3c] py-2 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <span class="text-[11px] font-semibold text-[#287d3c]">%</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1"
                        x-text="printScaleMode === 'fit' ? 'Fills the whole sheet edge-to-edge (may crop slightly).' :
                            (printScaleMode === 'fit_area' ? 'Scales to fit inside the printer\'s margins.' :
                            (printScaleMode === 'actual' ? 'Prints at the file\'s true size.' :
                            'Scales by the percentage you set.'))"></p>
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

        {{-- Reference-only notice --}}
        <p x-show="printQuality || printMediaType" x-cloak class="text-[11px] text-slate-400 mt-3 leading-relaxed">
            Quality / Media Type are shown for reference only — the print pipeline can't yet set a driver's DPI
            or media-type option, so these won't change the actual printout.
        </p>

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
