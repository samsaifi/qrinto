{{--
    Book-style design preview. Used by local-print/preview.blade.php and the
    store orders design-preview modal (store/partials/_design-preview-button).

    Mirrors the "Your printed card" stage of local-print/refrence.blade.php
    (/print/pdf), but shows the customer's real design images.

    @param array  $images       Image URLs in page order (page 1 first).
    @param string $orientation  'portrait' | 'landscape' | 'auto'. 'auto' decides
                                from the page-1 image once it loads: wider than
                                tall → landscape, otherwise portrait.

    Behaviour by image count:
      1 image  → single   : plain static preview, no animation.
      2 images → flat     : page 1 = front, page 2 = back. The card turns over
                            right → left (portrait) or top → bottom (landscape).
      4 images → folded   : page 1 = front, pages 2 + 3 = inside spread,
                            page 4 = back. The cover opens like a book, right →
                            left (portrait) or top → bottom (landscape), showing
                            pages 2 and 3 together.
      anything else falls back to the single (static) preview of page 1.

    Emits `book-preview-page` ({ index }) whenever the visible page changes, so
    the parent can keep its own page index (e.g. the print-settings modal) in sync.
--}}
@php
    $images = array_values($images ?? []);
    $count = count($images);
    $mode = $count === 4 ? 'folded' : ($count === 2 ? 'flat' : 'single');
    // Portrait cards turn around the vertical (Y) axis, landscape around the horizontal (X) axis.
    $orientation = strtolower($orientation ?? 'portrait');
    $auto = $orientation === 'auto';
    $axis = $orientation === 'landscape' ? 'X' : 'Y';
    $views = [
        'single' => [],
        'flat' => ['front' => 'Front', 'back' => 'Back'],
        'folded' => ['front' => 'Front', 'inside' => 'Inside', 'back' => 'Back'],
    ][$mode];
@endphp

<div class="book-preview bp-{{ $mode }}" :class="'bp-axis-' + axis"
    x-data="bookPreview({ mode: '{{ $mode }}', axis: '{{ $axis }}', auto: @js($auto), views: @js(array_keys($views)) })"
    x-init="init()">

    <div class="bp-stage" x-ref="stage">
        @if ($mode === 'single')
            @if ($count > 0)
                <img src="{{ $images[0] }}" alt="Page 1" class="bp-single" draggable="false">
            @endif
        @elseif ($mode === 'flat')
            {{-- One leaf: front face = page 1, back face = page 2. --}}
            <div class="bp-card" :style="cardStyle()">
                <div class="bp-leaf">
                    <div class="bp-face bp-f-front"><img src="{{ $images[0] }}" alt="Front" draggable="false" x-ref="sizer"></div>
                    <div class="bp-face bp-f-back"><img src="{{ $images[1] }}" alt="Back" draggable="false"></div>
                </div>
            </div>
        @else
            {{-- Base leaf: inside right (page 3) / back (page 4).
                 Cover leaf: front (page 1) / inside left (page 2), hinged on the fold. --}}
            <div class="bp-card" :style="cardStyle()">
                <div class="bp-leaf bp-base">
                    <div class="bp-face bp-f-front"><img src="{{ $images[2] }}" alt="Page 3" draggable="false"></div>
                    <div class="bp-face bp-f-back"><img src="{{ $images[3] }}" alt="Back" draggable="false"></div>
                </div>
                <div class="bp-leaf bp-cover" :style="coverStyle()">
                    <div class="bp-face bp-f-front"><img src="{{ $images[0] }}" alt="Front" draggable="false" x-ref="sizer"></div>
                    <div class="bp-face bp-f-back"><img src="{{ $images[1] }}" alt="Page 2" draggable="false"></div>
                </div>
            </div>
        @endif
    </div>

    @if ($mode !== 'single')
        <div class="bp-controls">
            <div class="bp-chips">
                @foreach ($views as $key => $label)
                    <button type="button" class="bp-chip" @click="pick('{{ $key }}')"
                        :aria-pressed="view === '{{ $key }}'">{{ $label }}</button>
                @endforeach
            </div>
            <button type="button" class="bp-play" @click="playing ? stop() : play()"
                :aria-label="playing ? 'Pause animation' : 'Play animation'">
                <svg x-show="playing" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M8 5v14M16 5v14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                </svg>
                <svg x-show="!playing" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M7 4.5v15l12-7.5z" fill="currentColor" />
                </svg>
            </button>
        </div>
    @endif
</div>

@once
    @push('styles')
        <style>
            .book-preview {
                --bp-shadow: 0 0 0 1px rgba(28, 38, 32, .10), 0 10px 26px rgba(28, 38, 32, .16);
            }

            .book-preview .bp-stage {
                height: 340px;
                display: flex;
                align-items: center;
                justify-content: center;
                perspective: 1500px;
                padding: 8px;
            }

            .book-preview .bp-single {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                background: #fff;
                border-radius: 4px;
                box-shadow: var(--bp-shadow);
            }

            .book-preview .bp-card,
            .book-preview .bp-leaf {
                transform-style: preserve-3d;
                transition: transform .75s cubic-bezier(.3, .7, .2, 1);
            }

            .book-preview .bp-card {
                position: relative;
            }

            .book-preview .bp-leaf {
                position: absolute;
                inset: 0;
            }

            .book-preview .bp-face {
                position: absolute;
                inset: 0;
                background: #fff;
                border-radius: 2px;
                overflow: hidden;
                box-shadow: var(--bp-shadow);
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
            }

            .book-preview .bp-face img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
                pointer-events: none;
            }

            /* Back faces are pre-turned so they read correctly once the leaf flips. */
            .book-preview.bp-axis-Y .bp-f-back {
                transform: rotateY(180deg);
            }

            .book-preview.bp-axis-X .bp-f-back {
                transform: rotateX(180deg);
            }

            /* The cover is hinged on the fold and sits just above the base leaf. */
            .book-preview.bp-axis-Y .bp-cover {
                transform-origin: left center;
            }

            .book-preview.bp-axis-X .bp-cover {
                transform-origin: center top;
            }

            .book-preview .bp-cover .bp-face {
                transform: translateZ(2px);
            }

            .book-preview.bp-axis-Y .bp-cover .bp-f-back {
                transform: translateZ(2px) rotateY(180deg);
            }

            .book-preview.bp-axis-X .bp-cover .bp-f-back {
                transform: translateZ(2px) rotateX(180deg);
            }

            .book-preview .bp-controls {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                margin-top: 6px;
            }

            .book-preview .bp-chips {
                display: inline-flex;
                gap: 6px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .book-preview .bp-chip,
            .book-preview .bp-play {
                border: 1.5px solid #cbd5e1;
                background: #fff;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 600;
                color: #334155;
                cursor: pointer;
                transition: background .15s, border-color .15s, color .15s;
            }

            .book-preview .bp-chip {
                padding: 5px 13px;
            }

            .book-preview .bp-chip:hover,
            .book-preview .bp-play:hover {
                border-color: #287d3c;
            }

            .book-preview .bp-chip[aria-pressed="true"] {
                background: #287d3c;
                border-color: #287d3c;
                color: #fff;
            }

            .book-preview .bp-play {
                width: 30px;
                height: 30px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0;
            }

            .book-preview .bp-play svg {
                width: 13px;
                height: 13px;
            }

            @media (max-width: 767px) {
                .book-preview .bp-stage {
                    height: 240px;
                }
            }

            @media (prefers-reduced-motion: reduce) {

                .book-preview .bp-card,
                .book-preview .bp-leaf {
                    transition: none !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function bookPreview({ mode, axis, auto, views }) {
                // Which page index is visible for each view (for the parent's page sync).
                const PAGE_OF_VIEW = { front: 0, inside: 1, back: mode === 'folded' ? 3 : 1 };
                const HOLD_MS = 2200; // time each view stays on screen while auto-playing
                const START_MS = 900; // page 1 is shown alone for this long first

                return {
                    view: 'front',
                    axis, // 'Y' = portrait (turns right → left), 'X' = landscape (turns top → bottom)
                    playing: false,
                    aspect: 0, // face width / height, read from the page-1 image
                    box: { w: 0, h: 0 },
                    timer: null,
                    observer: null,

                    init() {
                        if (mode === 'single') return;

                        const img = this.$refs.sizer;
                        const measure = () => {
                            if (img && img.naturalWidth) {
                                this.aspect = img.naturalWidth / img.naturalHeight;
                                // 'auto': page-1 image wider than tall → landscape.
                                if (auto) this.axis = img.naturalWidth > img.naturalHeight ? 'X' : 'Y';
                            }
                            this.resize();
                        };
                        if (img && img.complete) measure();
                        else if (img) img.addEventListener('load', measure, { once: true });

                        this.observer = new ResizeObserver(() => this.resize());
                        this.observer.observe(this.$refs.stage);

                        // Page 1 shows first; play() waits START_MS before the first turn.
                        this.play();
                    },

                    // Fit one face inside the stage. A folded card opens to twice its
                    // width (portrait) or height (landscape), so leave room for that.
                    resize() {
                        const stage = this.$refs.stage;
                        if (!stage) return;
                        const sw = stage.clientWidth - 16;
                        const sh = stage.clientHeight - 16;
                        const a = this.aspect || (this.axis === 'X' ? 7 / 5 : 5 / 7);
                        let maxW = sw, maxH = sh;
                        if (mode === 'folded') {
                            if (this.axis === 'Y') maxW = sw / 2;
                            else maxH = sh / 2;
                        }
                        const w = Math.min(maxW, maxH * a);
                        this.box = { w: Math.round(w), h: Math.round(w / a) };
                    },

                    // ── State → transforms ──
                    get opened() {
                        return this.view === 'inside';
                    },
                    get turned() {
                        return this.view === 'back';
                    },

                    cardStyle() {
                        const flip = this.turned ? 180 : 0;
                        const rot = this.axis === 'Y' ? `rotateY(${flip}deg)` : `rotateX(${flip}deg)`;
                        // An open folded card shifts by half a face so the spread stays centred.
                        const shift = mode === 'folded' && this.opened ?
                            (this.axis === 'Y' ? 'translateX(50%) ' : 'translateY(50%) ') : '';
                        return `width:${this.box.w}px;height:${this.box.h}px;transform:${shift}${rot};`;
                    },

                    coverStyle() {
                        const open = this.opened ? 180 : 0;
                        // Portrait: right edge swings to the left. Landscape: hinge on the top edge.
                        return this.axis === 'Y' ? `transform:rotateY(${-open}deg);` : `transform:rotateX(${open}deg);`;
                    },

                    // ── Views ──
                    show(view) {
                        this.view = view;
                        this.$dispatch('book-preview-page', { index: PAGE_OF_VIEW[view] });
                    },

                    pick(view) {
                        this.stop();
                        this.show(view);
                    },

                    // ── Auto-play: front → (inside) → back → front … ──
                    play() {
                        this.stop();
                        this.playing = true;
                        const step = () => {
                            const next = views[(views.indexOf(this.view) + 1) % views.length];
                            this.show(next);
                            this.timer = setTimeout(step, HOLD_MS);
                        };
                        // Always restart from page 1, shown alone for a moment.
                        if (this.view !== 'front') this.show('front');
                        this.timer = setTimeout(step, START_MS);
                    },

                    // Alpine calls this when the preview is removed (e.g. modal closed).
                    destroy() {
                        this.stop();
                        if (this.observer) this.observer.disconnect();
                    },

                    stop() {
                        clearTimeout(this.timer);
                        this.timer = null;
                        this.playing = false;
                    },
                };
            }
        </script>
    @endpush
@endonce
