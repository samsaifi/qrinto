{{-- Screen 1 of the Printo upload → preview → print flow.
     Kind (single / duplex / fold) caps what may be uploaded:
       images  — hard limit 1 / 2 / 4 files
       PDF     — any page count, but extra pages are ignored after a warning --}}
<div class="lp-up" x-data="lpUpload()" x-init="init()">
    <main class="lp-main">
        <div>
            <a class="lp-back" href="{{ route('localprint.start') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 6l-6 6 6 6" />
                </svg>Back</a>
            <h1>Upload a media</h1>
            <p class="lp-lede">Tell us what you’re printing. After you upload, we turn your file into a live preview
                of the finished card.</p>
        </div>

        @if ($errors->any())
            <p class="lp-err" role="alert">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('localprint.upload') }}" enctype="multipart/form-data" x-ref="form"
            class="lp-form">
            @csrf
            <input type="hidden" name="kind" :value="kind">
            <input type="hidden" name="shape" :value="shape">
            <input type="hidden" name="page_limit" :value="limit">

            <div class="lp-choose">
                <div class="lp-kinds-wrap">
                    <h2>What are you printing?</h2>
                    <div class="lp-kinds">
                        <template x-for="k in kinds" :key="k.id">
                            <button type="button" class="lp-kind" :class="kind === k.id && 'on'"
                                :aria-pressed="String(kind === k.id)" @click="pickKind(k.id)">
                                <span class="lp-ico" x-html="k.icon"></span>
                                <span><span class="t" x-text="k.title"></span><span class="s"
                                        x-text="k.sub"></span></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div class="lp-shape-wrap">
                    <h2>Card shape</h2>
                    <div class="lp-seg">
                        <button type="button" :class="shape === 'portrait' && 'on'"
                            :aria-pressed="String(shape === 'portrait')" @click="shape = 'portrait'">
                            <svg width="14" height="18" viewBox="0 0 14 18" fill="none" stroke="currentColor"
                                stroke-width="1.6" aria-hidden="true">
                                <rect x="1" y="1" width="12" height="16" rx="1.5" />
                            </svg>Portrait</button>
                        <button type="button" :class="shape === 'landscape' && 'on'"
                            :aria-pressed="String(shape === 'landscape')" @click="shape = 'landscape'">
                            <svg width="18" height="14" viewBox="0 0 18 14" fill="none" stroke="currentColor"
                                stroke-width="1.6" aria-hidden="true">
                                <rect x="1" y="1" width="16" height="12" rx="1.5" />
                            </svg>Landscape</button>
                    </div>
                </div>
            </div>

            {{-- dropzone --}}
            <label class="lp-drop" :class="over && 'over'" @dragover.prevent="over = true"
                @dragleave.prevent="over = false" @drop.prevent="over = false; take($event.dataTransfer.files)">
                <span class="lp-drop-ico">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                    </svg>
                </span>
                <span class="lp-drop-big">Drop your <span x-text="cur.noun"></span> file here, or <u>choose a
                        file</u></span>
                <span class="lp-drop-small">One PDF, or <span x-text="cur.imgText"></span> (JPEG, PNG, WebP) · up to
                    200 MB</span>
                <input type="file" x-ref="file" :multiple="limit > 1"
                    accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                    @change="take($event.target.files)">
            </label>
            <input type="file" name="file" x-ref="pdfField" hidden>
            <input type="file" name="images[]" x-ref="imgField" multiple hidden>

            <p class="lp-err" role="alert" x-show="error" x-text="error" x-cloak></p>

            {{-- PDF has more pages than this kind uses --}}
            <div class="lp-warn" role="alert" x-show="warn" x-cloak>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 8v5M12 16.5v.5" />
                </svg>
                <div>
                    <p x-text="warn"></p>
                    <div class="lp-warn-btns">
                        <button type="button" class="lp-btn primary" @click="submit()">Continue with <span
                                x-text="limit === 1 ? 'page 1' : 'the first ' + limit + ' pages'"></span></button>
                        <button type="button" class="lp-btn" @click="reset()">Choose another file</button>
                    </div>
                </div>
            </div>

            <p class="lp-busy" x-show="busy" x-cloak><span class="lp-spin"></span><span x-text="busy"></span></p>
        </form>

        {{-- setup guide --}}
        <section class="lp-guide" aria-label="File setup guide">
            <div class="lp-guide-head">
                <div>
                    <h2>How to set up a <span x-text="guide.title"></span></h2>
                    <p x-text="guide.sub"></p>
                </div>
                <a class="lp-btn" :href="template" download>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 4v12M7 11l5 5 5-5M5 20h14" />
                    </svg>Download blank template</a>
            </div>
            <div class="lp-guide-body">
                <template x-for="g in guide.pages" :key="g.caption">
                    <figure>
                        <div class="lp-sheet" :style="'flex-direction:' + g.dir">
                            <template x-for="h in g.halves" :key="h.label">
                                <div :class="h.cls" :style="'width:' + h.w + 'px;height:' + h.h + 'px'"><span
                                        x-text="h.label"></span></div>
                            </template>
                        </div>
                        <figcaption x-text="g.caption"></figcaption>
                    </figure>
                </template>
                <p class="lp-note" x-text="guide.note"></p>
            </div>
        </section>
    </main>
</div>

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        [x-cloak] { display: none !important }
        .lp-up { background: #F4F6F3; color: #17201A; font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif; min-height: 100vh }
        .lp-up a { color: #2F6E35 }
        .lp-up button { font-family: inherit }
        .lp-main { max-width: 960px; margin: 0 auto; padding: 28px 24px 56px; display: flex; flex-direction: column; gap: 22px }
        .lp-back { font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; min-height: 44px }
        .lp-up h1 { margin: 4px 0 6px; font-size: 32px; font-weight: 800; letter-spacing: -.02em; line-height: 1.15 }
        .lp-lede { margin: 0; font-size: 16px; line-height: 1.5; color: #4C5A4E; max-width: 520px }
        .lp-form { display: flex; flex-direction: column; gap: 14px }
        .lp-up h2 { margin: 0 0 10px; font-size: 15px; font-weight: 700 }
        .lp-choose { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; margin-bottom: 8px }
        .lp-kinds-wrap { flex: 999 1 520px; min-width: 0 }
        .lp-shape-wrap { flex: 1 1 220px }
        .lp-kinds { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px }
        .lp-kind { display: flex; align-items: center; gap: 12px; padding: 12px 14px; min-height: 64px; border-radius: 14px; border: 1px solid #D5DCD2; background: #fff; text-align: left; cursor: pointer; color: #17201A }
        .lp-kind.on { border: 2px solid #2F7A3B; background: #F0F7EF; padding: 11px 13px }
        .lp-kind .t { display: block; font-size: 15px; font-weight: 700 }
        .lp-kind .s { display: block; font-size: 13px; color: #4C5A4E; margin-top: 2px }
        .lp-ico { display: flex; flex-shrink: 0 }
        .lp-seg { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px; padding: 4px; border-radius: 12px; background: #E8ECE6 }
        .lp-seg button { height: 48px; border: none; border-radius: 9px; background: transparent; font-size: 14px; font-weight: 600; color: #17201A; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px }
        .lp-seg button.on { background: #fff; box-shadow: 0 1px 3px rgba(23, 32, 26, .18) }
        .lp-drop { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 200px; padding: 28px; border: 2px dashed #B5C0B1; border-radius: 18px; background: #fff; cursor: pointer; text-align: center }
        .lp-drop:hover, .lp-drop.over { border-color: #2F7A3B; background: #F6FAF5 }
        .lp-drop:focus-within { outline: 3px solid #9FCB98; outline-offset: 2px }
        .lp-drop input { position: absolute; width: 1px; height: 1px; opacity: 0; overflow: hidden }
        .lp-drop-ico { width: 56px; height: 56px; border-radius: 50%; background: #E3F0E1; color: #2F6E35; display: flex; align-items: center; justify-content: center }
        .lp-drop-big { font-size: 17px; font-weight: 700 }
        .lp-drop-big u { color: #2F6E35 }
        .lp-drop-small { font-size: 13px; color: #4C5A4E }
        .lp-err { margin: 0; font-size: 14px; color: #A3261C; font-weight: 600 }
        .lp-warn { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: 12px; background: #FFF6E5; color: #6B4A00; font-size: 14px; line-height: 1.45 }
        .lp-warn svg { flex-shrink: 0; margin-top: 1px }
        .lp-warn p { margin: 0 }
        .lp-warn-btns { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px }
        .lp-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 14px; border: 1px solid #D5DCD2; border-radius: 10px; background: #fff; font-size: 14px; font-weight: 600; text-decoration: none; color: #17201A !important; cursor: pointer }
        .lp-btn.primary { background: #1F6B2E; border-color: #1F6B2E; color: #fff !important }
        .lp-busy { margin: 0; display: flex; align-items: center; gap: 10px; font-size: 14px; color: #4C5A4E }
        .lp-spin { width: 18px; height: 18px; border-radius: 50%; border: 3px solid #D5DCD2; border-top-color: #2F7A3B; animation: lpspin 1s linear infinite }
        @keyframes lpspin { to { transform: rotate(360deg) } }
        .lp-guide { background: #fff; border: 1px solid #E2E7E0; border-radius: 18px; overflow: hidden }
        .lp-guide-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid #EDF0EB; flex-wrap: wrap }
        .lp-guide-head h2 { margin: 0; font-size: 16px }
        .lp-guide-head p { margin: 3px 0 0; font-size: 13px; color: #4C5A4E }
        .lp-guide-body { padding: 18px 20px; display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-start }
        .lp-guide-body figure { margin: 0; display: flex; flex-direction: column; gap: 8px }
        .lp-guide-body figcaption { font-size: 13px; font-weight: 600 }
        .lp-sheet { display: flex; gap: 2px; padding: 6px; background: #F0F3EE; border-radius: 8px }
        .lp-sheet > div { display: flex; align-items: flex-start; padding: 5px }
        .lp-sheet span { padding: 2px 6px; border-radius: 999px; background: #fff; color: #17201A; font-size: 11px; font-weight: 700 }
        .lp-note { flex: 1 1 220px; margin: 0; font-size: 14px; line-height: 1.55; color: #4C5A4E }
        .art-1 { background-color: #B3261E; background-image: radial-gradient(circle at 68% 32%, #E8574B 0 22%, transparent 23%), repeating-linear-gradient(135deg, rgba(0, 0, 0, .2) 0 6px, transparent 6px 14px) }
        .art-2 { background-color: #1F2A44; background-image: radial-gradient(circle at 30% 68%, #3E5C9A 0 26%, transparent 27%), repeating-linear-gradient(45deg, rgba(255, 255, 255, .08) 0 2px, transparent 2px 12px) }
        .art-3 { background-color: #9C6410; background-image: radial-gradient(circle at 50% 50%, #D9A13B 0 24%, transparent 25%), repeating-linear-gradient(90deg, rgba(0, 0, 0, .12) 0 3px, transparent 3px 12px) }
        .art-4 { background-color: #1E6B64; background-image: radial-gradient(circle at 25% 25%, #3FA095 0 18%, transparent 19%), repeating-linear-gradient(0deg, rgba(255, 255, 255, .08) 0 2px, transparent 2px 10px) }
        @media (max-width: 600px) { .lp-up h1 { font-size: 26px } .lp-main { padding: 16px 16px 40px } }
        @media (prefers-reduced-motion: reduce) { .lp-spin { animation: none } }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script>
        function lpUpload() {
            const ICON = {
                single: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"><rect x="5" y="3" width="14" height="18" rx="1.5"/><path d="M8 16l3-3 2 2 3-4"/></svg>',
                duplex: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"><rect x="8" y="3" width="11" height="17" rx="1.5"/><path d="M5 7v12.5A1.5 1.5 0 0 0 6.5 21H15"/></svg>',
                fold: '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M12 5l-8-2v16l8 2z"/><path d="M12 5l8-2v16l-8 2z"/></svg>',
            };
            // max = pages / images this kind can use
            const KINDS = {
                single: { id: 'single', title: 'Single-sided', sub: 'Photo, poster, one side', max: 1, noun: 'print', imgText: '1 image', label: 'Single-sided' },
                duplex: { id: 'duplex', title: 'Double-sided', sub: 'Printed front and back', max: 2, noun: 'double-sided', imgText: 'up to 2 images', label: 'Double-sided' },
                fold:   { id: 'fold',   title: 'Folded card',  sub: '4 sides, folds in half', max: 4, noun: 'folded card', imgText: 'up to 4 images', label: 'Folded card' },
            };
            const TEMPLATE_BASE = @json(asset('tamplates'));
            const MAX_BYTES = 200 * 1048576;

            return {
                kind: 'fold', shape: 'portrait', over: false, error: '', warn: '', busy: '',
                pending: null,
                kinds: Object.values(KINDS).map(k => ({ ...k, icon: ICON[k.id] })),

                init() {
                    try {
                        const saved = JSON.parse(sessionStorage.getItem('lp-upload-choice') || 'null');
                        if (saved && KINDS[saved.kind]) this.kind = saved.kind;
                        if (saved && ['portrait', 'landscape'].includes(saved.shape)) this.shape = saved.shape;
                    } catch (e) {}
                    this.$watch('kind', () => this.remember());
                    this.$watch('shape', () => this.remember());
                    if (window.pdfjsLib) {
                        pdfjsLib.GlobalWorkerOptions.workerSrc =
                            'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
                    }
                },
                remember() {
                    try { sessionStorage.setItem('lp-upload-choice', JSON.stringify({ kind: this.kind, shape: this.shape })); } catch (e) {}
                },

                get cur() { return KINDS[this.kind]; },
                get limit() { return this.cur.max; },
                get template() {
                    return TEMPLATE_BASE + '/' + (this.kind === 'fold' ? 'folded-' : 'flat-') + this.shape + '-template.pdf';
                },

                pickKind(k) {
                    this.kind = k;
                    // A pending PDF/images choice was validated against the old limit.
                    if (this.pending) this.reset();
                    this.error = '';
                },

                reset() {
                    this.pending = null; this.warn = ''; this.error = ''; this.busy = '';
                    this.$refs.file.value = '';
                },

                async take(fileList) {
                    const list = Array.from(fileList || []);
                    this.$refs.file.value = '';
                    this.warn = ''; this.error = ''; this.pending = null;
                    if (!list.length) return;

                    const ext = f => (f.name.split('.').pop() || '').toLowerCase();
                    const pdfs = list.filter(f => ext(f) === 'pdf');
                    const imgs = list.filter(f => ['jpg', 'jpeg', 'png', 'webp'].includes(ext(f)));
                    const lim = this.limit, label = this.cur.label;
                    const BAD_TYPE = 'Only PDF files or JPEG, PNG and WebP images can be uploaded.';

                    if (pdfs.length + imgs.length !== list.length) { this.error = BAD_TYPE; return; }
                    // The extension alone can be faked (e.g. setup.exe renamed to
                    // photo.jpg): check each file's real signature too.
                    for (const f of list) {
                        if (await this.sniff(f) !== (ext(f) === 'pdf' ? 'pdf' : 'image')) {
                            this.error = '“' + f.name + '” isn’t a real PDF or image file. ' + BAD_TYPE; return;
                        }
                    }
                    if (pdfs.length > 1 || (pdfs.length && imgs.length)) {
                        this.error = 'Upload one PDF, or images — not both, and not more than one PDF.'; return;
                    }
                    if (list.reduce((a, f) => a + f.size, 0) > MAX_BYTES) {
                        this.error = 'That file is over 200 MB. Try a smaller export.'; return;
                    }
                    if (imgs.length > lim) {
                        this.error = label + ' takes ' + (lim === 1 ? 'only 1 image' : 'up to ' + lim + ' images') +
                            '. You chose ' + imgs.length + '. Pick ' + (lim === 1 ? 'one image' : lim + ' or fewer') + ', or upload a PDF.';
                        return;
                    }

                    if (imgs.length) { this.pending = { images: imgs }; this.submit(); return; }

                    // PDF: count pages before upload. Extra pages are allowed, but the
                    // user must confirm only the first `lim` will be used.
                    const pdf = pdfs[0];
                    this.pending = { pdf };
                    let pages = 0;
                    if (window.pdfjsLib) {
                        this.busy = 'Checking your PDF…';
                        try {
                            const doc = await pdfjsLib.getDocument({ data: await pdf.arrayBuffer(), isEvalSupported: false }).promise;
                            pages = doc.numPages;
                            doc.destroy();
                        } catch (e) {
                            this.busy = ''; this.pending = null;
                            this.error = (e && e.name === 'PasswordException')
                                ? 'This PDF is password-protected. Remove the password and upload it again.'
                                : 'We couldn’t read that PDF. Try exporting it again.';
                            return;
                        }
                        this.busy = '';
                    }
                    if (this.pending?.pdf !== pdf) return; // kind changed / reset while counting

                    if (pages > lim) {
                        this.warn = 'This PDF has ' + pages + ' pages. ' + label + ' uses ' +
                            (lim === 1 ? 'only the first page' : 'only the first ' + lim + ' pages') +
                            ' — the other ' + (pages - lim) + (pages - lim === 1 ? ' page' : ' pages') + ' won’t be printed.';
                        return;
                    }
                    this.submit();
                },

                // 'pdf' | 'image' | null, from the file's first bytes.
                async sniff(f) {
                    const b = new Uint8Array(await f.slice(0, 16).arrayBuffer());
                    const at = (o, s) => [...s].every((ch, i) => b[o + i] === ch.charCodeAt(0));
                    if (at(0, '%PDF-')) return 'pdf';
                    if (b[0] === 0xFF && b[1] === 0xD8 && b[2] === 0xFF) return 'image';            // JPEG
                    if (b[0] === 0x89 && at(1, 'PNG')) return 'image';                              // PNG
                    if (at(0, 'RIFF') && at(8, 'WEBP')) return 'image';                             // WebP
                    return null;
                },

                submit() {
                    const p = this.pending;
                    if (!p) return;
                    const dt = new DataTransfer();
                    if (p.pdf) {
                        dt.items.add(p.pdf);
                        this.$refs.pdfField.files = dt.files;
                        this.$refs.imgField.value = '';
                    } else {
                        p.images.forEach(f => dt.items.add(f));
                        this.$refs.imgField.files = dt.files;
                        this.$refs.pdfField.value = '';
                    }
                    this.warn = '';
                    this.busy = 'Uploading…';
                    this.$refs.form.submit();
                },

                get guide() {
                    const P = this.shape === 'portrait';
                    const gh = (cls, label, w, h) => ({ cls, label, w, h });
                    if (this.kind === 'fold') return {
                        title: 'folded ' + this.shape + ' card',
                        sub: 'Upload one Letter PDF with 2 pages (outside, then inside), or 4 pages, one per panel.',
                        pages: [
                            { caption: 'Page 1 · Outside', dir: 'column', halves: [gh('art-4', '4 Back', 96, 62), gh('art-1', '1 Front', 96, 62)] },
                            { caption: 'Page 2 · Inside', dir: 'column', halves: P
                                ? [gh('art-3', '3 Inside right', 96, 62), gh('art-2', '2 Inside left', 96, 62)]
                                : [gh('art-2', '2 Inside top', 96, 62), gh('art-3', '3 Inside bottom', 96, 62)] },
                        ],
                        note: P
                            ? 'Fold line runs across the middle of each page. On page 1, turn the artwork so its top faces right. On page 2, turn it so its top faces left. Opens like a book, finished size 133 × 192 mm.'
                            : 'Fold line runs across the middle of each page, artwork upright. The back cover on page 1 goes upside down so it reads correctly once folded. Finished size 192 × 133 mm.',
                    };
                    const face = (cls, label) => P ? gh(cls, label, 72, 108) : gh(cls, label, 108, 72);
                    if (this.kind === 'duplex') return {
                        title: 'double-sided ' + this.shape + ' card',
                        sub: 'Upload 2 pages, both upright. Page 1 is the front, page 2 the back.',
                        pages: [
                            { caption: 'Page 1 · Front', dir: 'row', halves: [face('art-1', 'Front')] },
                            { caption: 'Page 2 · Back', dir: 'row', halves: [face('art-4', 'Back')] },
                        ],
                        note: 'Keep both pages the same way up. We flip the sheet on the correct edge so the back isn’t upside down.',
                    };
                    return {
                        title: 'single-sided ' + this.shape + ' print',
                        sub: 'Upload 1 page or 1 image at your card size.',
                        pages: [{ caption: 'Page 1 · Front', dir: 'row', halves: [face('art-1', 'Front')] }],
                        note: 'One side only. If your PDF has more pages, only the first page is printed.',
                    };
                },
            };
        }
    </script>
@endpush
