@extends('layouts.quick-flow')

@section('title', 'Choose a template')
@section('header_title', $type->name)

@section('content')
    <div class="space-y-6 px-6" x-data="templateBookmarks()">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-extrabold text-slate-700 flex items-center gap-3">
                    <a href="javascript:history.back()"
                        class="w-8 h-8 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-colors shrink-0">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    </a>
                    {{ $type->name }}
                </h1>
                <p class="text-slate-500 font-medium text-xs ml-11">Choose a template</p>
            </div>
            @if ($type->price)
                <div class="text-right">
                    <div class="flex items-center gap-2 justify-end">
                        @if ($type->old_price && $type->old_price > $type->price)
                            <span
                                class="text-slate-400 line-through text-sm">{{ \App\Services\CurrencyService::format($type->old_price) }}</span>
                        @endif
                        <span
                            class="text-2xl font-black text-slate-700 font-display">{{ \App\Services\CurrencyService::format($type->price) }}</span>
                    </div>
                    <p
                        class="text-[10px] font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-full inline-block mt-1">
                        Starting Price</p>
                </div>
            @endif
        </div>

        <!-- Category Filter Tabs -->
        <div class="flex flex-wrap gap-2 overflow-x-auto pb-4 scrollbar-hide -mx-6 px-6" data-tour="template-filters">
            <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-slate-800 text-white border-slate-800' :
                    'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                class="px-3 py-1 rounded-full border text-xs font-bold whitespace-nowrap transition-colors">
                All
            </button>
            @foreach ($categories as $cat)
                @php
                    $catProductCount = $templates->where('category_id', $cat->id)->count();
                @endphp

                @if ($catProductCount > 0)
                    <button @click="activeCategory = {{ $cat->id }}"
                        :class="activeCategory === {{ $cat->id }} ? 'bg-slate-800 text-white border-slate-800' :
                            'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                        class="px-3 py-1 rounded-full border text-xs font-bold whitespace-nowrap transition-colors">
                        {{ $cat->name }} ({{ $catProductCount }})
                    </button>
                @endif
            @endforeach
        </div>

        <div class="template-grid grid grid-cols-2 gap-4  " data-tour="template-grid">
            @forelse($templates as $tpl)
                <a href="{{ route('flow.customize', $tpl->slug) }}" data-tpl-id="{{ $tpl->id }}"
                    x-show="activeCategory === 'all' || activeCategory === {{ $tpl->category_id }}"
                    class="group relative bg-slate-50 aspect-[5/7] max-h-[145px] w-full overflow-hidden border-2 border-transparent hover:border-slate-400 transition-all duration-300 shadow-sm hover:shadow-premium"
                    style="aspect-ratio: {{ $tpl->aspect_ratio }};">
                    @php
                        $event = '';
                    @endphp
                    @if ($tpl->event_id != '')
                        <div class="absolute z-20 top-4  left-4 w-8 h-8   flex items-center justify-center  ">

                            <div class="relative group/event">
                                @php
                                    $event = \App\Models\Event::find($tpl->event_id);
                                @endphp
                                @if (!empty($event['icon_svg']))
                                    <span class="w-8 h-8 flex justify-center items-center rounded-full"
                                        style="color: {{ $event['color'] }}; background-color: {{ $event['color'] }}3d;">
                                        {!! $event->icon($event['icon_svg']) !!}
                                    </span>
                                @else
                                    <span
                                        class="w-8 h-8 flex justify-center items-center rounded-full text-xs font-semibold   text-white"
                                        style="background: {{ $event['color'] }};">
                                        {{ strtoupper(substr($event['title'], 0, 1)) }}
                                    </span>
                                @endif

                                <!-- Tooltip -->
                                <div
                                    class="absolute   left-1/2  -translate-x-12 -top-full mt-2
                                                whitespace-nowrap rounded-md bg-gray-900 px-2 py-1
                                                text-xs text-white shadow-lg
                                                opacity-0 invisible transition-all duration-200
                                                group-hover/event:opacity-100 group-hover:visible z-[999]">
                                    {{ $event['title'] }} Event's
                                </div>
                            </div>
                        </div>
                    @elseif($tpl->product_store != '')
                        <div class="absolute z-20 top-4 left-4 rounded-full w-8 h-8 flex items-center justify-center">
                            @if ($tpl->product_store && ($store = \App\Models\Store::find($tpl->product_store)))
                                <img src="{{ asset('storage/' . $store->logo) }}" alt="" class="rounded-full">
                            @endif
                            <div
                                class="absolute group/store left-1/2 -translate-x-12 -top-full mt-2
                                                whitespace-nowrap rounded-md bg-gray-900 px-2 py-1
                                                text-xs text-white shadow-lg
                                                opacity-0 invisible transition-all duration-200
                                                group-hover/store:opacity-100 group-hover:visible z-[999]">
                                {{ $store->store_name }} templates
                            </div>
                        </div>
                    @endif


                    @if ($tpl->category_id == 21)
                        <div
                            class="absolute  z-20 top-0 text-gray-400 left-0 w-full h-full bg-white   flex items-center justify-center shadow-lg">
                            Add your stuff
                        </div>
                    @endif



                    @if ($tpl->pdf_orientation == 'portrait')
                        <!-- display fixed rectangle-vertical icon in div -->
                        <div
                            class="absolute group/port z-20 bottom-4 right-4 w-8 h-8 bg-slate-800 rounded-full flex items-center justify-center shadow-lg">
                            <i data-lucide="rectangle-vertical" class="w-5 h-5 text-white"></i>
                            <div
                                class="absolute left-1/2 -translate-x-12 -top-full mt-2
                                        whitespace-nowrap rounded-md bg-gray-900 px-2 py-1
                                        text-xs text-white shadow-lg
                                        opacity-0 invisible transition-all duration-200
                                        group-hover/port:opacity-100 group-hover:visible z-[999]">
                                Portrait templates
                            </div>
                        </div>
                    @else
                        <!-- display fixed rectangle-horizontal icon in div -->
                        <div
                            class="absolute group/land z-20 bottom-4 right-4 w-8 h-8 bg-slate-800 rounded-full flex items-center justify-center shadow-lg">
                            <i data-lucide="rectangle-horizontal" class="w-5 h-5 text-white"></i>
                            <div
                                class="absolute left-1/2 -translate-x-12 -top-full mt-2
                                        whitespace-nowrap rounded-md bg-gray-900 px-2 py-1
                                        text-xs text-white shadow-lg
                                        opacity-0 invisible transition-all duration-200
                                        group-hover/land:opacity-100 group-hover:visible z-[999]">
                                Landscape templates
                            </div>
                        </div>
                    @endif
                    <img src="{{ $tpl->frame_image_thumbnail ?? 'https://placehold.co/300x400/eee/999?text=' . urlencode($tpl->name) }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        alt="{{ $tpl->name }}">

                    <div class="absolute bg-black/50 inset-x-0 bottom-0 px-4 py-2 ">
                        <p class="text-white text-xs font-bold uppercase tracking-wider">
                            {{ \Illuminate\Support\Str::words($tpl->name, 2, '..') }}</p>
                    </div>
                    @if (!empty($event) && !empty($event['title']))
                        <div class="absolute bg-black/50 inset-x-0 pl-14 pt-4 top-0 px-4 py-2 ">
                            <p class="text-white text-xs font-bold uppercase tracking-wider">
                                {{ $event['title'] }} Event's </p>
                        </div>
                    @elseif($tpl->store_id)
                        <div class="absolute bg-black/50 inset-x-0 pl-14 pt-4 top-0 px-4 py-2 ">
                            <p class="text-white text-xs font-bold uppercase tracking-wider">
                                ${{ $tpl->base_price }} Store-exclusive </p>
                        </div>
                    @endif
                    <!-- Bookmark button -->
                    <!-- <button type="button" @click.prevent.stop="toggleBookmark($el)" data-tpl-id="{{ $tpl->id }}"
                                            data-tpl-slug="{{ $tpl->slug }}" data-tpl-name="{{ $tpl->name }}"
                                            data-tpl-thumbnail="{{ $tpl->frame_image_thumbnail ?? '' }}"
                                            class="absolute top-14 right-4 z-10 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg"
                                            :title="isBookmarked({{ $tpl->id }}) ? 'Remove bookmark' :
                                                'Bookmark this template'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                                :class="isBookmarked({{ $tpl->id }}) ? 'stroke-mobile-500 fill-mobile-500' :
                                                    'stroke-white fill-none'"
                                                class="transition-all duration-200">
                                                <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                                            </svg>
                                        </button> -->

                    @if (request()->is('*/' . $tpl->slug))
                        <div
                            class="absolute top-4 right-4 w-8 h-8 bg-slate-800 rounded-full flex items-center justify-center shadow-lg">
                            <i data-lucide="check" class="w-5 h-5 text-white"></i>
                        </div>
                    @endif
                </a>
            @empty
                <div class="col-span-1 py-12 text-center space-y-2">
                    <h3 class="text-lg font-bold text-slate-400">No templates found</h3>
                    <p class="text-slate-400 text-sm">Please select a different size or category</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function templateBookmarks() {
            return {
                activeCategory: 'all',
                bookmarks: {},
                init() {
                    try {
                        const stored = localStorage.getItem('qrinto_bookmarks');
                        if (stored) this.bookmarks = JSON.parse(stored);
                    } catch (e) {
                        this.bookmarks = {};
                    }
                    this.$nextTick(() => this.sortCards());
                },
                sortCards() {
                    const grid = this.$el.querySelector('.template-grid');
                    if (!grid) return;
                    const cards = Array.from(grid.querySelectorAll('a[data-tpl-id]'));
                    cards.sort((a, b) => {
                        const aB = !!this.bookmarks[a.dataset.tplId];
                        const bB = !!this.bookmarks[b.dataset.tplId];
                        return bB - aB;
                    });
                    cards.forEach(card => grid.appendChild(card));
                },
                toggleBookmark(el) {
                    const id = el.dataset.tplId;
                    if (this.bookmarks[id]) {
                        const updated = {
                            ...this.bookmarks
                        };
                        delete updated[id];
                        this.bookmarks = updated;
                    } else {
                        this.bookmarks = {
                            ...this.bookmarks,
                            [id]: {
                                id: el.dataset.tplId,
                                slug: el.dataset.tplSlug,
                                name: el.dataset.tplName,
                                thumbnail: el.dataset.tplThumbnail,
                            }
                        };
                    }
                    localStorage.setItem('qrinto_bookmarks', JSON.stringify(this.bookmarks));
                    this.$nextTick(() => this.sortCards());
                },
                isBookmarked(id) {
                    return !!this.bookmarks[String(id)];
                }
            };
        }
    </script>
@endpush
