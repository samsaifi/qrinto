@extends('layouts.quick-flow-pc')

@section('title', 'Choose a Size - Design & Print | Qrinto')
@section('header_title', 'Choose a Size')

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans" x-data="{ orientMode: 'portrait' }">
        <div class="max-w-[1240px] mx-auto">

            {{-- Back Navigation --}}
            <div class="mb-6">
                <a href="{{ route('localprint.start') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                    <span>← Back to Local Print</span>
                </a>
            </div>

            {{-- Title + Orientation Toggle --}}
            <div class="mb-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">Choose a size</h1>
                    <p class="text-sm text-slate-500 font-normal mt-1.5">
                        Pick a card type and size, then design it in the editor.
                    </p>
                </div>

                {{-- Portrait / Landscape switch --}}
                <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-2xs shrink-0">
                    <button type="button" @click="orientMode = 'portrait'"
                        :class="orientMode === 'portrait' ? 'bg-[#287d3c] text-white shadow-sm' :
                            'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                        <svg class="w-3.5 h-5" viewBox="0 0 14 20" fill="none" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="1" width="12" height="18" rx="1.5" />
                        </svg>
                        Portrait
                    </button>
                    <button type="button" @click="orientMode = 'landscape'"
                        :class="orientMode === 'landscape' ? 'bg-[#287d3c] text-white shadow-sm' :
                            'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all">
                        <svg class="w-5 h-3.5" viewBox="0 0 20 14" fill="none" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

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
                        class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">

                        {{-- Top Image Area --}}
                        <div
                            class="bg-[#f2f7f2] h-48 sm:h-52 flex items-center justify-center p-6 border-b border-slate-100 relative">
                            @if ($firstItem->icon_svg)
                                <div
                                    class="w-24 h-24 text-emerald-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    {!! $firstItem->icon_svg !!}
                                </div>
                            @elseif ($isFolded)
                                <svg class="w-28 h-28 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform"
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
                                <svg class="w-24 h-24 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform"
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
                                <svg class="w-24 h-24 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform"
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
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3
                                    class="font-extrabold text-slate-900 text-lg sm:text-xl group-hover:text-emerald-700 transition-colors">
                                    {{ $displayTitle }}
                                </h3>
                                @if ($desc)
                                    <p class="text-xs text-slate-500 font-normal mt-1.5 leading-relaxed">
                                        {{ $desc }}</p>
                                @endif

                                {{-- Dimension Pills --}}
                                <div class="mt-4">
                                    @if ($hasMultipleSizes)
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <template x-for="(s, idx) in sizes" :key="s.id">
                                                <button type="button" @click="selectedIdx = idx"
                                                    :class="selectedIdx === idx ?
                                                        'border-emerald-600 bg-emerald-50/80 text-emerald-900 font-bold' :
                                                        'border-slate-200 bg-white text-slate-600 hover:border-slate-300 font-normal'"
                                                    class="px-2.5 py-1 rounded-xl border text-[11px] transition-all cursor-pointer"
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
                            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end">
                                <a :href="'{{ route('localprint.customizer') }}?size=' + sizeCode +
                                    '&orientation=' + orientMode + ' & title = ' + titleSlug"
                                    class="bg-[#287d3c] hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-2xs inline-flex items-center gap-1 active:scale-95">
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

        </div>
    </div>
@endsection
