@extends('layouts.quick-flow-pc')

@section('title', ($type->name ?? 'Category') . ' | Qrinto Custom Print Studio')
@section('header_title', $type->name ?? 'Category')
@section('meta_description', $type->title ?? $type->description ?? 'Custom print options and card selections.')
@section('canonical_url', route('flow.category', ['type' => $type->slug ?? 'cards']))

@section('content')
    <div class="w-full bg-[#fafcf9] min-h-screen py-10 px-6 lg:px-16 font-sans">
        <div class="max-w-[1240px] mx-auto">

            {{-- Back Navigation --}}
            <div class="mb-6">
                <a href="{{ route('flow.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">
                    <span>← All products</span>
                </a>
            </div>

            {{-- Title Header (Dynamic from $type) --}}
            <div class="mb-10">
                <h1 class="text-2xl font-extrabold text-[#112419] tracking-tight">
                    @if (Str::contains(strtolower($type->name ?? ''), 'card'))
                        Choose a {{ strtolower(Str::singular($type->name)) }}
                    @else
                        Choose {{ $type->name ?? 'a product' }}
                    @endif
                </h1>
                @if ($type->title || $type->short_description)
                    <p class="text-sm text-slate-500 font-normal mt-1.5">
                        {{ $type->title ?? $type->short_description }}
                    </p>
                @endif
            </div>

            @php
                $groupedSubTypes = $subTypes->groupBy(fn($item) => trim($item->title ?? $item->name));
            @endphp

            {{-- Dynamic 3-Column Card Grouped Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                @forelse ($groupedSubTypes as $groupTitle => $items)
                    @php
                        $firstItem = $items->first();
                        $displayTitle = Str::replace(' - double', ', double-sided', $groupTitle);
                        
                        $desc = match(strtolower($groupTitle)) {
                            'folded' => 'Opens upward. Cover and inside are printed.',
                            'flat' => 'One sheet, front printed.',
                            'flat - double', 'flat, double-sided' => 'One sheet, both sides printed.',
                            'standard size' => 'Flexible photo magnets printed with crisp colors.',
                            default => $firstItem->description ?? '',
                        };

                        if ($desc && (strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $desc)) === strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $displayTitle)) || strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $desc)) === strtolower(str_replace([' ', 'in', '×'], ['', '', 'x'], $firstItem->title ?? '')))) {
                            $desc = null;
                        }

                        $isFolded = Str::contains(strtolower($groupTitle), 'folded');
                        $isDouble = Str::contains(strtolower($groupTitle), ['double', 'both']);
                        $hasMultipleSizes = $items->count() > 1;
                    @endphp

                    <div x-data="{ 
                            selectedId: '{{ $firstItem->id }}',
                            selectedPrice: '{{ \App\Services\CurrencyService::formatWithCurrency($firstItem->price, $firstItem->currency ?? 'USD') }}',
                            selectedTitleSlug: '{{ Str::slug($groupTitle) }}',
                            selectedSizeCode: '{{ ($firstItem->width + 0) . 'x' . ($firstItem->height + 0) }}'
                        }"
                        class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
                        
                        {{-- Top Image Area --}}
                        <div class="bg-[#f2f7f2] h-48 sm:h-52 flex items-center justify-center p-6 border-b border-slate-100 relative">
                            @if ($firstItem->icon_svg)
                                <div class="w-24 h-24 text-emerald-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                                    {!! $firstItem->icon_svg !!}
                                </div>
                            @elseif ($isFolded)
                                <svg class="w-28 h-28 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform" viewBox="0 0 90 90">
                                    {{-- Left panel (Inside page) --}}
                                    <path d="M 21 22 L 45 16 L 45 74 L 21 80 A 3 3 0 0 1 18 77 L 18 25 A 3 3 0 0 1 21 22 Z" stroke="currentColor" fill="none"/>
                                    {{-- Right panel (Front cover) --}}
                                    <path d="M 45 16 L 69 22 A 3 3 0 0 1 72 25 L 72 77 A 3 3 0 0 1 69 80 L 45 74 Z" stroke="currentColor" fill="none"/>
                                    {{-- Fold spine crease --}}
                                    <line x1="45" y1="16" x2="45" y2="74" stroke="currentColor" stroke-dasharray="2 2"/>
                                    {{-- Inside page lines --}}
                                    <line x1="25" y1="36" x2="38" y2="33" stroke="currentColor"/>
                                    <line x1="25" y1="44" x2="38" y2="41" stroke="currentColor"/>
                                    <line x1="25" y1="52" x2="34" y2="50" stroke="currentColor"/>
                                    {{-- Front cover heart icon & line --}}
                                    <path d="M 58.5 37.5 C 56.5 35 52.5 36.5 54.5 40.5 L 58.5 44.5 L 62.5 40.5 C 64.5 36.5 60.5 35 58.5 37.5 Z" stroke="currentColor" fill="none"/>
                                    <line x1="52" y1="54" x2="65" y2="57.25" stroke="currentColor"/>
                                </svg>
                            @elseif ($isDouble)
                                <svg class="w-24 h-24 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform" viewBox="0 0 90 90">
                                    <rect x="15" y="15" width="35" height="50" rx="3" stroke="currentColor" fill="none"/>
                                    <rect x="42" y="10" width="35" height="50" rx="3" stroke="currentColor" fill="none"/>
                                    <line x1="49" y1="20" x2="68" y2="20" stroke="currentColor"/>
                                    <line x1="49" y1="26" x2="65" y2="26" stroke="currentColor"/>
                                    <path d="M60 62 C50 72 30 72 25 65" stroke="currentColor" stroke-dasharray="2 2"/>
                                    <path d="M22 68 L25 65 L28 71" stroke="currentColor"/>
                                </svg>
                            @else
                                <svg class="w-24 h-24 stroke-emerald-700 stroke-[1.25] fill-none group-hover:scale-105 transition-transform" viewBox="0 0 80 90">
                                    <rect x="20" y="12" width="40" height="60" rx="4" stroke="currentColor" fill="none"/>
                                    <rect x="27" y="20" width="26" height="30" rx="2" stroke="currentColor" fill="none"/>
                                    <line x1="27" y1="58" x2="47" y2="58" stroke="currentColor"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Bottom Info & Action Area --}}
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                {{-- Card Title from Product Title --}}
                                <h3 class="font-extrabold text-slate-900 text-lg sm:text-xl group-hover:text-emerald-700 transition-colors">
                                    {{ $displayTitle }}
                                </h3>
                                @if ($desc)
                                    <p class="text-xs text-slate-500 font-normal mt-1.5 leading-relaxed">
                                        {{ $desc }}
                                    </p>
                                @endif

                                {{-- Dimension Pills (NO item name, dimensions only) --}}
                                <div class="mt-4">
                                    @if ($hasMultipleSizes)
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            @foreach ($items as $item)
                                                @php
                                                    $dimLabel = ($item->width + 0) . ' × ' . ($item->height + 0);
                                                    $sizeCode = ($item->width + 0) . 'x' . ($item->height + 0);
                                                    $itemPrice = \App\Services\CurrencyService::formatWithCurrency($item->price, $item->currency ?? 'USD');
                                                @endphp
                                                <button type="button" 
                                                    @click="selectedId = '{{ $item->id }}'; selectedPrice = '{{ $itemPrice }}'; selectedSizeCode = '{{ $sizeCode }}'"
                                                    :class="selectedId === '{{ $item->id }}' ? 'border-emerald-600 bg-emerald-50/80 text-emerald-900 font-bold' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 font-normal'"
                                                    class="px-2.5 py-1 rounded-xl border text-[11px] transition-all cursor-pointer">
                                                    {{ $dimLabel }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        @php
                                            $singleDim = ($firstItem->width + 0) . ' × ' . ($firstItem->height + 0) . ' in';
                                            $normTitle = str_replace([' ', 'in', '×'], ['', '', 'x'], strtolower($displayTitle));
                                            $normDim = ($firstItem->width + 0) . 'x' . ($firstItem->height + 0);
                                            $titleContainsDim = str_contains($normTitle, $normDim);
                                        @endphp
                                        @if (!$titleContainsDim)
                                            <span class="text-xs text-slate-400 font-mono">{{ $singleDim }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Bottom Line: Price + Design Button --}}
                            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-normal">
                                    <strong class="font-extrabold text-slate-900 text-sm" x-text="selectedPrice"></strong> each
                                </span>

                                @if (Str::contains(strtolower($type->slug ?? ''), 'magnet') || Str::contains(strtolower($type->name ?? ''), 'magnet'))
                                    <a :href="'{{ url('/') }}/magnets/' + selectedSizeCode + '/design'"
                                        class="bg-[#287d3c] hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-2xs inline-flex items-center gap-1 active:scale-95">
                                        <span>Design →</span>
                                    </a>
                                @else
                                    <a :href="'{{ url('/') }}/{{ $type->slug }}/' + selectedTitleSlug + '/' + selectedSizeCode + '/templates'"
                                        class="bg-[#287d3c] hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-2xs inline-flex items-center gap-1 active:scale-95">
                                        <span>Design →</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-3xl border border-slate-200">
                        <p class="text-slate-500 text-sm font-medium">No sub-categories available for {{ $type->name }}.</p>
                    </div>
                @endforelse

            </div>

        </div>
    </div>
@endsection
