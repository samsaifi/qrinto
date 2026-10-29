@extends('layouts.quick-flow-pc')

@section('title', 'Choose a Size - Design & Print | Qrinto')
@section('header_title', 'Choose a Size')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-4 px-3 md:py-10 md:px-6 lg:px-16 font-sans" x-data="{ orientMode: 'portrait' }">
        <div class="max-w-[1240px] mx-auto">

            {{-- Back + Title + Toggle (compact on mobile) --}}
            <div class="flex items-center gap-2 mb-2 md:flex-col md:items-start md:gap-0 md:mb-0">
                <a href="{{ route('localprint.start') }}"
                    class="text-[11px] md:text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors shrink-0">
                    ← <span class="hidden md:inline">Back to Local Print</span>
                </a>
                <h1 class="text-base md:text-2xl font-extrabold text-[#112419] tracking-tight md:mt-4">Choose a size</h1>
            </div>

            {{-- Orientation Toggle --}}
            <div class="mb-3 md:mb-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 md:gap-4">
                <p class="hidden md:block text-sm text-slate-500 font-normal mt-1.5">
                    Pick a card type and size, then design it in the editor.
                </p>

                {{-- Portrait / Landscape switch --}}
                <div
                    class="flex items-center bg-gray-50 w-auto md:bg-white md:border border-slate-200 rounded-lg md:rounded-xl p-0.5 md:p-1 shadow-2xs shrink-0">
                    <button type="button" @click="orientMode = 'portrait'"
                        :class="orientMode === 'portrait' ? 'bg-[#287d3c] text-white shadow-sm' :
                            'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1 md:gap-1.5 px-2.5 py-1 md:px-3 md:py-1.5 rounded-md md:rounded-lg text-[11px] md:text-xs font-bold transition-all">
                        <svg class="w-3 h-4 md:w-3.5 md:h-5" viewBox="0 0 14 20" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="1" width="12" height="18" rx="1.5" />
                        </svg>
                        Portrait
                    </button>
                    <button type="button" @click="orientMode = 'landscape'"
                        :class="orientMode === 'landscape' ? 'bg-[#287d3c] text-white shadow-sm' :
                            'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1 md:gap-1.5 px-2.5 py-1 md:px-3 md:py-1.5 rounded-md md:rounded-lg text-[11px] md:text-xs font-bold transition-all">
                        <svg class="w-4 h-3 md:w-5 md:h-3.5" viewBox="0 0 20 14" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="1" width="18" height="12" rx="1.5" />
                        </svg>
                        Landscape
                    </button>
                </div>
            </div>

            @php
                $groupedSubTypes = $subTypes->groupBy(fn($item) => trim($item->title ?? $item->name));
            @endphp

            {{-- 3-Column Card Grid --}}
            <div class="grid   grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">

                @forelse ($groupedSubTypes as $groupTitle => $items)
                    @php
                        $firstItem = $items->first();
                        $displayTitle = Str::replace(' - double', ', double-sided', $groupTitle);

                        $desc = match (strtolower($groupTitle)) {
                            'folded' => 'Opens upward. Cover and inside are printed.',
                            'flat' => 'One sheet, front printed.',
                            'flat - double', 'flat, double-sided' => 'One sheet, both sides printed.',
                            'standard size' => 'Flexible photo magnets printed with crisp colors.',
                            default => $firstItem->description ?? '',
                        };

                        if (
                            $desc &&
                            (strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $desc)) ===
                                strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $displayTitle)) ||
                                strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $desc)) ===
                                    strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $firstItem->title ?? '')))
                        ) {
                            $desc = null;
                        }

                        $isFolded = Str::contains(strtolower($groupTitle), 'folded');
                        $isDouble = Str::contains(strtolower($groupTitle), ['double', 'both']);
                        $hasMultipleSizes = $items->count() > 1;

                        // Build JS-friendly sizes array for this group
                        $sizesJs = $items
                            ->map(
                                fn($item) => [
                                    'id' => (string) $item->id,
                                    'w' => $item->width + 0,
                                    'h' => $item->height + 0,
                                ],
                            )
                            ->values()
                            ->all();
                    @endphp

                    <div x-data="{
                        titleSlug: '{{ Str::slug($groupTitle) }}',
                        sizes: {{ Illuminate\Support\Js::from($sizesJs) }},
                        selectedIdx: 0,
                        get cur() { return this.sizes[this.selectedIdx] || this.sizes[0]; },
                        dispW(s) { return orientMode === 'landscape' ? Math.max(s.w, s.h) : Math.min(s.w, s.h); },
                        dispH(s) { return orientMode === 'landscape' ? Math.min(s.w, s.h) : Math.max(s.w, s.h); },
                        get sizeCode() { return this.dispW(this.cur) + 'x' + this.dispH(this.cur); },
                    }"
                        class="bg-white border border-slate-200/90 rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">

                        {{-- Top Image Area --}}
                        <div
                            class="bg-[#f2f7f2] h-24 sm:h-32 md:h-48 flex items-center justify-center p-3 md:p-6 border-b border-slate-100 relative">
                            @if ($firstItem->icon_svg)
                                <div
                                    class="w-14 h-14 md:w-24 md:h-24 text-emerald-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    {!! $firstItem->icon_svg !!}
                                </div>
                            @elseif ($isFolded)
                                <svg class="w-16 h-16 md:w-28 md:h-28 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform"
                                    viewBox="0 0 90 90">
                                    <path d="M 21 22 L 45 16 L 45 74 L 21 80 A 3 3 0 0 1 18 77 L 18 25 A 3 3 0 0 1 21 22 Z"
                                        stroke="currentColor" fill="none" />
                                    <path d="M 45 16 L 69 22 A 3 3 0 0 1 72 25 L 72 77 A 3 3 0 0 1 69 80 L 45 74 Z"
                                        stroke="currentColor" fill="none" />
                                    <line x1="45" y1="16" x2="45" y2="74" stroke="currentColor"
                                        stroke-dasharray="2 2" />
                                    <line x1="25" y1="36" x2="38" y2="33"
                                        stroke="currentColor" />
                                    <line x1="25" y1="44" x2="38" y2="41"
                                        stroke="currentColor" />
                                    <line x1="25" y1="52" x2="34" y2="50"
                                        stroke="currentColor" />
                                    <path
                                        d="M 58.5 37.5 C 56.5 35 52.5 36.5 54.5 40.5 L 58.5 44.5 L 62.5 40.5 C 64.5 36.5 60.5 35 58.5 37.5 Z"
                                        stroke="currentColor" fill="none" />
                                    <line x1="52" y1="54" x2="65" y2="57.25"
                                        stroke="currentColor" />
                                </svg>
                            @elseif ($isDouble)
                                <svg class="w-14 h-14 md:w-24 md:h-24 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform"
                                    viewBox="0 0 90 90">
                                    <rect x="15" y="15" width="35" height="50" rx="3" stroke="currentColor"
                                        fill="none" />
                                    <rect x="42" y="10" width="35" height="50" rx="3" stroke="currentColor"
                                        fill="none" />
                                    <line x1="49" y1="20" x2="68" y2="20"
                                        stroke="currentColor" />
                                    <line x1="49" y1="26" x2="65" y2="26"
                                        stroke="currentColor" />
                                    <path d="M60 62 C50 72 30 72 25 65" stroke="currentColor" stroke-dasharray="2 2" />
                                    <path d="M22 68 L25 65 L28 71" stroke="currentColor" />
                                </svg>
                            @else
                                <svg class="w-14 h-14 md:w-24 md:h-24 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform"
                                    viewBox="0 0 80 90">
                                    <rect x="20" y="12" width="40" height="60" rx="4"
                                        stroke="currentColor" fill="none" />
                                    <rect x="27" y="20" width="26" height="30" rx="2"
                                        stroke="currentColor" fill="none" />
                                    <line x1="27" y1="58" x2="47" y2="58"
                                        stroke="currentColor" />
                                </svg>
                            @endif
                        </div>

                        {{-- Bottom Info & Action --}}
                        <div class="px-3 py-2.5 md:p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3
                                    class="font-extrabold text-slate-900 text-sm md:text-lg sm:text-xl group-hover:text-emerald-700 transition-colors">
                                    {{ $displayTitle }}
                                </h3>
                                @if ($desc)
                                    <p class="hidden md:block text-xs text-slate-500 font-normal mt-1.5 leading-relaxed">
                                        {{ $desc }}</p>
                                @endif

                                {{-- Dimension Pills --}}
                                <div class="mt-2 md:mt-4">
                                    @if ($hasMultipleSizes)
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <template x-for="(s, idx) in sizes" :key="s.id">
                                                <button type="button" @click="selectedIdx = idx"
                                                    :class="selectedIdx === idx ?
                                                        'border-emerald-600 bg-emerald-50/80 text-emerald-900 font-bold' :
                                                        'border-slate-200 bg-white text-slate-600 hover:border-slate-300 font-normal'"
                                                    class="px-2 py-0.5 md:px-2.5 md:py-1 rounded-lg md:rounded-xl border text-[10px] md:text-[11px] transition-all cursor-pointer"
                                                    x-text="dispW(s) + ' × ' + dispH(s)">
                                                </button>
                                            </template>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 font-mono"
                                            x-text="dispW(sizes[0]) + ' × ' + dispH(sizes[0]) + ' in'"></span>
                                    @endif
                                </div>
                            </div>

                            {{-- Design Button --}}
                            <?php $slug = '&title='; ?>
                            <div class="mt-3 md:mt-6 pt-3 md:pt-4 border-t border-slate-100 flex items-center justify-end">
                                <a :href="'{{ route('localprint.customizer') }}?size=' + sizeCode +
                                    '&orientation=' + orientMode + '{{ $slug }} ' + titleSlug"
                                    class="bg-[#287d3c] hover:bg-emerald-800 text-white font-bold px-3 py-1.5 md:px-4 md:py-2 rounded-lg md:rounded-xl text-[11px] md:text-xs transition-all shadow-2xs inline-flex items-center gap-1 active:scale-95">
                                    <span>Design →</span>
                                </a>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-3xl border border-slate-200">
                        <p class="text-slate-500 text-sm font-medium">No sizes available.</p>
                    </div>
                @endforelse

            </div>

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
                visiblePresets: 6,
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
                get pw() { return (this.isValid(this.w) ? Number(this.w) : this.min) * 15; },
                get ph() { return (this.isValid(this.h) ? Number(this.h) : this.min) * 15; },
                get note() {
                    const w = this.fmt(this.w),
                        h = this.fmt(this.h);
                    if (this.cardType === 'folded') return 'Folds to <b>' + w + ' × ' + h + ' in</b> from a <b>' + w + ' × ' + this.fmt(h * 2) + ' in</b> sheet.';
                    if (this.cardType === 'double') return 'Printed on both sides of a <b>' + w + ' × ' + h + ' in</b> sheet.';
                    return 'Printed on one side of a <b>' + w + ' × ' + h + ' in</b> sheet.';
                },
                get titleSlug() { return { folded: 'custom-folded', flat: 'custom-flat', double: 'custom-flat-double' } [this.cardType]; },
                get href() {
                    const orientation = Number(this.h) > Number(this.w) ? 'portrait' : 'landscape';
                    return '{{ route('localprint.customizer') }}?size=' + this.fmt(this.w) + 'x' + this.fmt(this.h) +
                        '&orientation=' + orientation + '&title=' + this.titleSlug;
                },
            }"
                class="mt-4 md:mt-8 bg-white border border-slate-200/90 rounded-2xl md:rounded-3xl overflow-hidden shadow-2xs grid grid-cols-1 md:grid-cols-[1.15fr_1fr]">

                {{-- Left: form --}}
                <div class="p-4 md:p-7">
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-slate-900 text-base md:text-xl">Custom size</h3>
                        <span
                            class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] md:text-[11px] font-bold">New</span>
                    </div>
                    <p class="text-xs md:text-sm text-slate-500 mt-1 leading-relaxed">
                        Any size from 2 to 12 inches per side. Good for square cards, place cards and slim invitations.
                    </p>

                    {{-- Card type --}}
                    <p class="text-xs md:text-sm font-semibold text-slate-800 mt-4 md:mt-5 mb-1.5">Card type</p>
                    <div class="inline-flex items-center bg-slate-100 rounded-xl p-1">
                        <template
                            x-for="opt in [{ k: 'folded', l: 'Folded' }, { k: 'flat', l: 'Flat' }, { k: 'double', l: 'Double-sided' }]"
                            :key="opt.k">
                            <button type="button" @click="cardType = opt.k"
                                :class="cardType === opt.k ? 'bg-white text-slate-900 shadow-sm' :
                                    'text-slate-500 hover:text-slate-700'"
                                class="px-3 md:px-4 py-1.5 rounded-lg text-xs md:text-sm font-semibold transition-all"
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
                            <button type="button" @click="w = p.w; h = p.h"
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
                    class="relative bg-[#f2f5f3] border-t md:border-t-0 md:border-l border-slate-100 flex flex-col items-center justify-between p-4 md:p-6 min-h-[260px] md:min-h-[340px]">
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

                    <p class="text-xs md:text-sm text-slate-600 text-center mt-4" x-html="note"></p>

                    <div class="w-full flex items-center justify-between mt-4">
                        {{-- 1 inch scale --}}
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400">
                            <span class="w-[15px] h-1.5 border-b border-x border-slate-400"></span>
                            1 in
                        </div>
                        <a :href="valid ? href : null" :aria-disabled="!valid"
                            :class="valid ? 'bg-[#287d3c] hover:bg-emerald-800 active:scale-95' :
                                'bg-slate-300 pointer-events-none'"
                            class="text-white font-bold px-3 py-1.5 md:px-4 md:py-2 rounded-lg md:rounded-xl text-[11px] md:text-xs transition-all shadow-2xs inline-flex items-center gap-1">
                            <span>Design →</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
