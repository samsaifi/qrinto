@extends('layouts.quick-flow-pc')

@section('title', 'Choose a Size - Design & Print | Qrinto')
@section('header_title', 'Choose a Size')

@section('content')
    <div class="lp-sizes w-full min-h-screen font-sans"
        x-data="{ orientMode: 'portrait', selectedCard: null, bcError: '' }">
        <div class="lp-wrap">

            {{-- Breadcrumb --}}
            <nav class="lp-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true">›</span>
                <a href="{{ route('localprint.start') }}">Local print</a>
                <span aria-hidden="true">›</span>
                <span class="cur" aria-current="page">Choose a size</span>
            </nav>

            {{-- Title + Portrait / Landscape --}}
            <div class="lp-titlebar">
                <h1 class="lp-h1">Choose a size</h1>

                <div class="lp-seg" role="radiogroup" aria-label="Orientation">
                    <button type="button" role="radio" @click="orientMode = 'portrait'"
                        :aria-checked="String(orientMode === 'portrait')" :class="orientMode === 'portrait' && 'on'">
                        <svg width="12" height="16" viewBox="0 0 14 20" fill="none" stroke="currentColor"
                            stroke-width="1.6" aria-hidden="true">
                            <rect x="1" y="1" width="12" height="18" rx="1.5" />
                        </svg>
                        Portrait
                    </button>
                    <button type="button" role="radio" @click="orientMode = 'landscape'"
                        :aria-checked="String(orientMode === 'landscape')" :class="orientMode === 'landscape' && 'on'">
                        <svg width="16" height="12" viewBox="0 0 20 14" fill="none" stroke="currentColor"
                            stroke-width="1.6" aria-hidden="true">
                            <rect x="1" y="1" width="18" height="12" rx="1.5" />
                        </svg>
                        Landscape
                    </button>
                </div>
            </div>

            @php
                // Folded, Flat, Flat double-sided, Business card; anything else after them.
                $rank = fn($t) => match (true) {
                    Str::contains(strtolower($t), 'folded') => 0,
                    Str::slug($t) === 'business-card' => 3,
                    Str::contains(strtolower($t), ['double', 'both']) => 2,
                    strtolower($t) === 'flat' => 1,
                    default => 4,
                };
                $groupedSubTypes = $subTypes
                    ->groupBy(fn($item) => trim($item->title ?? $item->name))
                    ->sortBy(fn($items, $title) => $rank($title));
            @endphp

            {{-- Size cards --}}
            <div class="lp-grid">

                @forelse ($groupedSubTypes as $groupTitle => $items)
                    @php
                        $firstItem = $items->first();
                        $slugTitle = Str::slug($groupTitle);
                        $displayTitle = Str::replace(' - double', ', double-sided', $groupTitle);
                        $isBiz = $slugTitle === 'business-card';
                        $isFolded = Str::contains(strtolower($groupTitle), 'folded');
                        $isDouble = !$isBiz && Str::contains(strtolower($groupTitle), ['double', 'both']);

                        $desc = match (true) {
                            $isBiz => 'Both sides · 10–12 per sheet',
                            $isFolded => 'Opens upward. Cover and inside are printed.',
                            $isDouble => 'One sheet, both sides printed.',
                            strtolower($groupTitle) === 'flat' => 'One sheet, front printed.',
                            default => $firstItem->description ?? '',
                        };
                        // Drop a description that only repeats the size / title.
                        $norm = fn($v) => strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], (string) $v));
                        if ($desc && in_array($norm($desc), [$norm($displayTitle), $norm($firstItem->title ?? '')], true)) {
                            $desc = null;
                        }

                        // Upload: business card opens the upload box below; flat cards go to
                        // the PDF upload page with the matching kind. Folded designs only.
                        $uploadKind = $isBiz ? 'biz' : ($isFolded ? null : ($isDouble ? 'duplex' : 'single'));

                        $sizesJs = $items
                            ->map(fn($item) => ['id' => (string) $item->id, 'w' => $item->width + 0, 'h' => $item->height + 0])
                            ->values()
                            ->all();
                    @endphp

                    <div x-data="{
                        titleSlug: '{{ $slugTitle }}',
                        isBiz: {{ $isBiz ? 'true' : 'false' }},
                        sizes: {{ Illuminate\Support\Js::from($sizesJs) }},
                        selectedIdx: 0,
                        get cur() { return this.sizes[this.selectedIdx] || this.sizes[0]; },
                        {{-- Business cards are always 3.5 × 2 landscape. --}}
                        dispW(s) { return (this.isBiz || orientMode === 'landscape') ? Math.max(s.w, s.h) : Math.min(s.w, s.h); },
                        dispH(s) { return (this.isBiz || orientMode === 'landscape') ? Math.min(s.w, s.h) : Math.max(s.w, s.h); },
                        get sizeCode() { return this.dispW(this.cur) + 'x' + this.dispH(this.cur); },
                    }"
                        @click="selectedCard = titleSlug" @focusin="selectedCard = titleSlug"
                        :class="selectedCard === titleSlug && 'lp-size-card-on'"
                        class="lp-size-card">

                        @if ($isBiz)
                            <span class="lp-new">New</span>
                        @endif

                        {{-- Illustration --}}
                        <div class="lp-ill-box" aria-hidden="true">
                            <svg class="lp-ill" viewBox="0 0 120 70" fill="none">
                                @if ($isFolded)
                                    <path d="M37 8h46l-3 7H40z" fill="#fff" stroke="#3B463D" stroke-width="1.1" />
                                    <rect x="37" y="15" width="46" height="46" rx="1.5" fill="#fff" stroke="#3B463D" stroke-width="1.1" />
                                    <line x1="46" y1="29" x2="74" y2="29" stroke="#2F7A3B" stroke-width="1.6" />
                                    <line x1="46" y1="35" x2="66" y2="35" stroke="#2F7A3B" stroke-width="1.2" />
                                @elseif ($isBiz)
                                    <rect x="39" y="15" width="48" height="28" rx="2" fill="#fff" stroke="#3B463D" stroke-width="1.1" />
                                    <rect x="33" y="21" width="48" height="28" rx="2" fill="#fff" stroke="#3B463D" stroke-width="1.1" />
                                    <line x1="39" y1="31" x2="65" y2="31" stroke="#2F7A3B" stroke-width="1.6" />
                                    <line x1="39" y1="37" x2="59" y2="37" stroke="#2F7A3B" stroke-width="1.2" />
                                @elseif ($isDouble)
                                    <rect x="40" y="10" width="50" height="38" rx="1.5" fill="#fff" stroke="#3B463D" stroke-width="1.1" />
                                    <rect x="32" y="17" width="50" height="38" rx="1.5" fill="#fff" stroke="#3B463D" stroke-width="1.1" />
                                    <line x1="40" y1="30" x2="68" y2="30" stroke="#2F7A3B" stroke-width="1.6" />
                                    <line x1="40" y1="36" x2="62" y2="36" stroke="#2F7A3B" stroke-width="1.2" />
                                @else
                                    <rect x="34" y="14" width="52" height="40" rx="1.5" fill="#fff" stroke="#3B463D" stroke-width="1.1" />
                                    <line x1="44" y1="28" x2="74" y2="28" stroke="#2F7A3B" stroke-width="1.6" />
                                    <line x1="44" y1="34" x2="68" y2="34" stroke="#2F7A3B" stroke-width="1.2" />
                                @endif
                            </svg>
                        </div>

                        {{-- Info + actions --}}
                        <div class="lp-card-body">
                            <h3 class="lp-card-title">{{ $displayTitle }}</h3>
                            @if ($desc)
                                <p class="lp-card-desc">{{ $desc }}</p>
                            @endif

                            {{-- Size pills --}}
                            <div class="lp-pills">
                                <template x-for="(s, idx) in sizes" :key="s.id">
                                    <button type="button" @click="selectedIdx = idx"
                                        :aria-pressed="String(selectedIdx === idx)"
                                        :class="selectedIdx === idx ? 'on' : ''" class="lp-pill"
                                        x-text="dispW(s) + ' × ' + dispH(s)"></button>
                                </template>
                            </div>

                            {{-- Design (always an empty canvas: fresh=1) + Upload --}}
                            <div class="lp-card-actions">
                                <a @click="window.BusinessCardPdf && BusinessCardPdf.clear()"
                                    :href="'{{ route('localprint.customizer') }}?size=' + sizeCode +
                                        '&orientation=' + (isBiz ? 'landscape' : orientMode) + '&title=' + titleSlug + '&fresh=1'"
                                    class="lp-btn lp-btn-solid">
                                    Design
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14M13 6l6 6-6 6" />
                                    </svg>
                                </a>
                                @if ($uploadKind === 'biz')
                                    {{-- Pick a PDF / images, read them in the browser, then open the editor with them. --}}
                                    <label class="lp-btn lp-btn-line bcu-pick" :class="bcBusy && 'is-busy'" @click.stop
                                        x-data="{
                                            bcBusy: false,
                                            async pick(e) {
                                                const files = e.target.files ? Array.from(e.target.files) : [];
                                                e.target.value = '';
                                                if (!files.length || this.bcBusy) return;
                                                this.bcBusy = true;
                                                bcError = '';
                                                try {
                                                    const res = await BusinessCardPdf.read(files);
                                                    if (!BusinessCardPdf.store(res)) throw new BusinessCardPdf.UploadError('Your browser could not keep this file. Close other tabs and try again.');
                                                    window.location.assign('{{ route('localprint.customizer', ['size' => '3.5x2', 'orientation' => 'landscape', 'title' => 'business-card']) }}');
                                                } catch (err) {
                                                    bcError = BusinessCardPdf.message(err);
                                                    this.bcBusy = false;
                                                }
                                            },
                                        }">
                                        <span class="bcu-spin" x-show="bcBusy" aria-hidden="true"></span>
                                        <span x-show="!bcBusy" class="inline-flex">@include('quick-flow-pc.local-print.partials.upload-icon')</span>
                                        <span x-text="bcBusy ? 'Reading file…' : 'Upload'">Upload</span>
                                        <input type="file" class="bcu-input" :disabled="bcBusy"
                                            accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple
                                            @change="pick($event)">
                                    </label>
                                    <p class="bcu-error" role="alert" x-show="bcError" x-cloak x-text="bcError"></p>
                                @elseif ($uploadKind)
                                    <a :href="'{{ route('localprint.pdf') }}?kind={{ $uploadKind }}&shape=' + orientMode"
                                        class="lp-btn lp-btn-line">
                                        @include('quick-flow-pc.local-print.partials.upload-icon')
                                        Upload
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="lp-grid-empty">
                        <p class="text-slate-500 text-sm font-medium">No sizes available.</p>
                    </div>
                @endforelse

            </div>

            {{-- Business card Upload: shared reader script + styles. --}}
            @include('quick-flow-pc.local-print.partials.business-card-upload')

            {{-- Custom Size (inches only) --}}
            <div x-data="{
                cardType: 'folded',
                w: 6,
                h: 4,
                min: 2,
                max: 12,
                presets: [
                    { label: 'Square 5 × 5 in', w: 5, h: 5 },
                    { label: 'Square 6 × 6 in', w: 6, h: 6 },
                    { label: 'Photo 4 × 6 in', w: 4, h: 6 },
                    { label: 'A6', w: 4.13, h: 5.83 },
                    { label: 'A5', w: 5.83, h: 8.27 },
                    { label: 'Slim 4 × 9 in', w: 4, h: 9 },
                ],
                showAllPresets: false,
                visiblePresets: 7,
                get shownPresets() {
                    return this.showAllPresets ? this.presets :
                        this.presets.filter((p, i) => i < this.visiblePresets || this.isPreset(p));
                },
                init() {
                    // Append printer paper sizes, skipping any already listed.
                    const paper = {{ Illuminate\Support\Js::from($paperPresets ?? []) }};
                    const key = p => Math.min(p.w, p.h) + 'x' + Math.max(p.w, p.h);
                    const seen = new Set(this.presets.map(key));
                    paper.forEach(p => {
                        if (seen.has(key(p))) return;
                        seen.add(key(p));
                        const dims = this.fmt(p.w) + ' × ' + this.fmt(p.h) + ' in';
                        this.presets.push({ label: p.label ? p.label + ' · ' + dims : dims, w: p.w, h: p.h });
                    });
                },
                fmt(n) { return (Math.round(Number(n) * 100) / 100).toString(); },
                isValid(n) { n = Number(n); return n >= this.min && n <= this.max; },
                get valid() { return this.isValid(this.w) && this.isValid(this.h); },
                clamp(key) {
                    let n = Number(this[key]);
                    if (!n || isNaN(n)) n = this.min;
                    this[key] = Math.round(Math.min(this.max, Math.max(this.min, n)) * 100) / 100;
                },
                swap() {
                    [this.w, this.h] = [this.h, this.w];
                },
                isPreset(p) { return Number(this.w) === p.w && Number(this.h) === p.h; },
                get pw() { return (this.isValid(this.w) ? Number(this.w) : this.min) * 17; },
                get ph() { return (this.isValid(this.h) ? Number(this.h) : this.min) * 17; },
                get note() {
                    const w = this.fmt(this.w),
                        h = this.fmt(this.h);
                    if (this.cardType === 'folded') return 'A ' + w + ' × ' + h + ' in folded card, from a ' + w + ' × ' + this.fmt(h * 2) + ' in sheet.';
                    if (this.cardType === 'double') return 'A ' + w + ' × ' + h + ' in double-sided card.';
                    return 'A ' + w + ' × ' + h + ' in flat card, front printed.';
                },
                get titleSlug() { return { folded: 'custom-folded', flat: 'custom-flat', double: 'custom-flat-double' } [this.cardType]; },
                get href() {
                    const orientation = Number(this.h) > Number(this.w) ? 'portrait' : 'landscape';
                    return '{{ route('localprint.customizer') }}?size=' + this.fmt(this.w) + 'x' + this.fmt(this.h) +
                        '&orientation=' + orientation + '&title=' + this.titleSlug + '&fresh=1';
                },
            }"
                class="lp-custom">

                {{-- Left: form --}}
                <div class="p-4 md:p-7">
                    <h3 class="font-extrabold text-slate-900 text-base md:text-lg">Custom size</h3>
                    <p class="text-xs text-slate-500 mt-0.5">2 to 12 in per side.</p>

                    {{-- Card type --}}
                    <p class="text-xs md:text-sm font-semibold text-slate-800 mt-4 md:mt-5 mb-1.5">Card type</p>
                    <div class="lp-seg" role="radiogroup" aria-label="Card type">
                        <template
                            x-for="opt in [{ k: 'folded', l: 'Folded' }, { k: 'flat', l: 'Flat' }, { k: 'double', l: 'Double-sided' }]"
                            :key="opt.k">
                            <button type="button" role="radio" @click="cardType = opt.k"
                                :aria-checked="String(cardType === opt.k)" :class="cardType === opt.k && 'on'"
                                x-text="opt.l"></button>
                        </template>
                    </div>

                    {{-- Size --}}
                    <p class="text-xs md:text-sm font-semibold text-slate-800 mt-4 md:mt-5 mb-1.5">Size</p>
                    <div class="flex items-end gap-2 md:gap-3">
                        <label class="flex-1 max-w-[130px]">
                            <span class="block text-[11px] md:text-xs font-semibold text-slate-700 mb-1">Width</span>
                            <div :class="isValid(w) ? 'border-slate-200 focus-within:border-emerald-600' : 'border-red-400'"
                                class="flex items-center border rounded-xl px-3 py-2 bg-white transition-colors">
                                <input type="number" inputmode="decimal" step="0.25" :min="min"
                                    :max="max" x-model="w" @blur="clamp('w')"
                                    class="w-full min-w-0 text-sm md:text-base font-semibold text-slate-900 outline-none border-0 p-0 focus:ring-0 bg-transparent">
                                <span class="text-xs text-slate-500 font-medium ml-1">in</span>
                            </div>
                        </label>
                        <button type="button" @click="swap()" title="Swap width and height"
                            class="h-[42px] w-[42px] shrink-0 flex items-center justify-center border border-slate-200 rounded-xl text-slate-600 hover:border-emerald-600 hover:text-emerald-700 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7 4 3 8l4 4M3 8h18M17 20l4-4-4-4M21 16H3" />
                            </svg>
                        </button>
                        <label class="flex-1 max-w-[130px]">
                            <span class="block text-[11px] md:text-xs font-semibold text-slate-700 mb-1">Height</span>
                            <div :class="isValid(h) ? 'border-slate-200 focus-within:border-emerald-600' : 'border-red-400'"
                                class="flex items-center border rounded-xl px-3 py-2 bg-white transition-colors">
                                <input type="number" inputmode="decimal" step="0.25" :min="min"
                                    :max="max" x-model="h" @blur="clamp('h')"
                                    class="w-full min-w-0 text-sm md:text-base font-semibold text-slate-900 outline-none border-0 p-0 focus:ring-0 bg-transparent">
                                <span class="text-xs text-slate-500 font-medium ml-1">in</span>
                            </div>
                        </label>
                    </div>
                    <p x-show="!valid" x-cloak class="text-[11px] md:text-xs text-red-500 mt-1.5">
                        Each side must be between 2 and 12 inches.
                    </p>

                    {{-- Popular sizes --}}
                    <p class="text-xs md:text-sm font-semibold text-slate-800 mt-4 md:mt-6 mb-2">Popular sizes</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="p in shownPresets" :key="p.label">
                            <button type="button" @click="w = p.w; h = p.h; if (p.type) cardType = p.type"
                                :class="isPreset(p) ? 'border-emerald-600 bg-emerald-50/80 text-emerald-900' :
                                    'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                class="px-3 md:px-4 py-1.5 rounded-full border text-xs md:text-sm font-semibold transition-all"
                                x-text="p.label"></button>
                        </template>
                        <button type="button" x-show="presets.length > visiblePresets"
                            @click="showAllPresets = !showAllPresets"
                            class="px-3 md:px-4 py-1.5 rounded-full text-xs md:text-sm font-semibold text-emerald-700 hover:text-emerald-900 hover:bg-emerald-50 transition-all"
                            x-text="showAllPresets ? 'Show fewer' : '+ ' + (presets.length - visiblePresets) + ' more sizes'"></button>
                    </div>
                </div>

                {{-- Right: live preview --}}
                <div
                    class="lp-dots">
                    <div class="flex-1 flex items-center justify-center w-full">
                        <div class="flex items-center gap-2">
                            {{-- Height label --}}
                            <div class="flex flex-col items-center text-slate-500" :style="'height:' + ph + 'px'">
                                <span class="w-2 border-t border-slate-400"></span>
                                <span class="flex-1 border-l border-slate-300"></span>
                                <span
                                    class="text-[10px] font-semibold [writing-mode:vertical-rl] rotate-180 my-1 whitespace-nowrap"
                                    x-text="fmt(h) + ' in'"></span>
                                <span class="flex-1 border-l border-slate-300"></span>
                                <span class="w-2 border-t border-slate-400"></span>
                            </div>
                            <div class="flex flex-col items-center gap-2">
                                {{-- Card --}}
                                <div class="relative bg-white rounded-sm shadow-[0_10px_18px_-8px_rgba(15,23,42,0.35)] transition-all duration-300 flex flex-col items-center justify-center gap-1.5"
                                    :style="'width:' + pw + 'px;height:' + ph + 'px'">
                                    <span
                                        class="w-4 h-4 rounded-full bg-gradient-to-br from-emerald-300 to-emerald-600"></span>
                                    <span class="w-[45%] max-w-[60px] border-t border-slate-500"></span>
                                    <span class="w-[30%] max-w-[40px] border-t border-slate-300"></span>
                                </div>
                                {{-- Width label --}}
                                <div class="flex items-center text-slate-500" :style="'width:' + pw + 'px'">
                                    <span class="h-2 border-l border-slate-400"></span>
                                    <span class="flex-1 border-t border-slate-300"></span>
                                    <span class="text-[10px] font-semibold mx-1 whitespace-nowrap"
                                        x-text="fmt(w) + ' in'"></span>
                                    <span class="flex-1 border-t border-slate-300"></span>
                                    <span class="h-2 border-l border-slate-400"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 text-center mt-4" x-text="note"></p>

                    <div class="w-full flex justify-end mt-4">
                        <a :href="valid ? href : null" :aria-disabled="!valid"
                            :class="valid ? '' : 'is-off'" class="lp-btn lp-btn-solid px-5">
                            <span x-text="'Design ' + fmt(w) + ' in × ' + fmt(h) + ' in'"></span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .lp-sizes { background: #EEF1EC }
        .lp-sizes { background: #EEF1EC; padding: 16px 12px 40px }
        .lp-wrap { max-width: 1240px; margin: 0 auto }
        .lp-crumb { display: flex; gap: 6px; flex-wrap: wrap; font-size: 12px; color: #6B776D; margin-bottom: 14px }
        .lp-crumb a { color: #1F6B2E; text-decoration: none }
        .lp-crumb a:hover { text-decoration: underline }
        .lp-crumb .cur { color: #17201A; font-weight: 600 }
        .lp-titlebar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px }
        .lp-h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.01em; color: #112419 }
        .lp-size-card { position: relative; display: flex; flex-direction: column; overflow: hidden; background: #FFFFFF; border: 1px solid #E2E7E0; border-radius: 16px }
        .lp-new { position: absolute; top: 10px; right: 10px; z-index: 1; padding: 2px 8px; border-radius: 999px; background: #1F6B2E; color: #FFFFFF; font-size: 10px; font-weight: 700 }
        .lp-ill-box { height: 80px; display: flex; align-items: center; justify-content: center; background: #EEF2EC }
        .lp-card-body { flex: 1; display: flex; flex-direction: column; padding: 12px }
        .lp-card-title { margin: 0; font-size: 14px; font-weight: 800; color: #17201A }
        .lp-card-desc { margin: 4px 0 0; font-size: 11px; line-height: 1.5; color: #6B776D }
        .lp-pills { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px }
        .lp-card-actions { margin-top: auto; padding-top: 16px; display: flex; flex-direction: column; gap: 8px }
        .lp-grid-empty { padding: 48px 16px; text-align: center; background: #FFFFFF; border: 1px solid #E2E7E0; border-radius: 16px; color: #6B776D }
        .lp-custom { margin-top: 16px; display: grid; grid-template-columns: 1fr; overflow: hidden; background: #FFFFFF; border: 1px solid #E2E7E0; border-radius: 16px }
        .lp-dots { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: space-between; min-height: 260px; padding: 16px; border-top: 1px solid #E2E7E0 }
        @media (min-width: 768px) {
            .lp-sizes { padding: 28px 24px 56px }
            .lp-h1 { font-size: 28px }
            .lp-titlebar { margin-bottom: 22px }
            .lp-ill-box { height: 88px }
            .lp-card-body { padding: 16px }
            .lp-card-title { font-size: 15px }
            .lp-card-desc { font-size: 12px }
            .lp-custom { grid-template-columns: 1fr 1fr; margin-top: 20px }
            .lp-dots { min-height: 320px; padding: 24px; border-top: none; border-left: 1px solid #E2E7E0 }
        }
        @media (min-width: 1024px) { .lp-sizes { padding-left: 48px; padding-right: 48px } }
        .lp-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px }
        .lp-grid-empty { grid-column: 1 / -1 }
        @media (min-width: 1024px) { .lp-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px } }
        .lp-seg { display: inline-flex; gap: 2px; padding: 3px; border-radius: 10px; background: #E3E8E1 }
        .lp-seg button { display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; border: none; border-radius: 8px; background: transparent; font-size: 12px; font-weight: 600; color: #4C5A4E; cursor: pointer; white-space: nowrap }
        .lp-seg button:hover { color: #17201A }
        .lp-seg button.on { background: #FFFFFF; color: #17201A; box-shadow: 0 1px 3px rgba(23, 32, 26, .14) }
        .lp-ill { width: 120px; height: 70px }
        .lp-pill { padding: 3px 10px; border-radius: 999px; border: 1px solid #D5DCD2; background: #FFFFFF; font-size: 11px; font-weight: 600; color: #4C5A4E; cursor: pointer }
        .lp-pill:hover { border-color: #A9B3A6 }
        .lp-pill.on { border-color: #2F7A3B; background: #EAF4E8; color: #1D4D22; box-shadow: 0 0 0 1px #2F7A3B }
        .lp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 38px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; transition: background .15s, border-color .15s }
        .lp-btn-solid { background: #1F6B2E; color: #FFFFFF !important; border: none }
        .lp-btn-solid:hover { background: #18572A }
        .lp-btn-solid.is-off { background: #B9C2B6; pointer-events: none }
        .lp-btn-line { background: #FFFFFF; color: #17201A; border: 1px solid #D5DCD2 }
        .lp-btn-line:hover { border-color: #1F6B2E; color: #1F6B2E }
        .lp-dots { background-color: #F6F8F5; background-image: radial-gradient(#D3DAD0 1px, transparent 1px); background-size: 14px 14px }
    </style>
@endpush
