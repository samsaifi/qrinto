@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', ($kind ?? '') === 'layout' ? (isset($template) ? 'Edit Layout' : 'New Layout') : (isset($template) ? 'Edit Card Template' : 'New Card Template'))

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Great+Vibes&family=Pinyon+Script&family=Caveat:wght@400;700&family=Bebas+Neue&family=Josefin+Sans:wght@300;600&family=DM+Serif+Display&family=Dancing+Script:wght@400;700&family=Pacifico&family=Lobster&family=Abril+Fatface&family=Montserrat:wght@400;600;700&family=Lora:ital,wght@0,400;0,700;1,400&family=Sacramento&family=Alex+Brush&family=Cinzel:wght@400;700&family=Amatic+SC:wght@400;700&family=Righteous&family=Fredoka:wght@400;700&family=Permanent+Marker&display=swap">
    <style>
        /* Card template canvas builder: one 360 x 504 page shown at --k scale */
        .cb-stage { background: #f5f3ee; border-radius: 14px; padding: 18px; display: flex; justify-content: center; }
        .cb-page { position: relative; overflow: hidden; box-shadow: 0 4px 18px rgba(0,0,0,.12); outline: 1px dashed #c7cfe0; outline-offset: 6px; user-select: none; }
        .cb-el { position: absolute; cursor: move; box-sizing: border-box; }
        .cb-el.sel { outline: 2px solid #2563eb; outline-offset: 1px; z-index: 5; }
        .cb-el img { width: 100%; height: 100%; display: block; pointer-events: none; }
        .cb-el .cb-text { white-space: pre-wrap; word-break: break-word; }
        .cb-el .cb-text[contenteditable="true"] { cursor: text; outline: none; background: rgba(37,99,235,.06); }
        .cb-slot { width: 100%; height: 100%; display: grid; place-items: center; color: #64748b; font: 600 11px Onest, sans-serif;
            background: repeating-linear-gradient(45deg, #e6ebf3, #e6ebf3 8px, #dbe2ee 8px, #dbe2ee 16px); }
        .cb-h { position: absolute; right: -6px; bottom: -6px; width: 12px; height: 12px; background: #fff; border: 2px solid #2563eb; border-radius: 3px; cursor: nwse-resize; display: none; }
        .cb-el.sel .cb-h { display: block; }
        .cb-tool { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; font-size: 13px; font-weight: 600; color: #475569; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
        .cb-tool:hover { background: #f8fafc; }
        .cb-tool.danger { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
        .cb-tab { padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 999px; color: #475569; background: #f1f5f9; }
        .cb-tab.on { background: #1e293b; color: #fff; }
        .cb-lbl { display: block; font-size: 11px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .cb-in { width: 100%; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; background: #fff; }
        .cb-in:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px #dbeafe; }
        /* Pickers (Text styles / Emojis / Background colours from the database) */
        .cb-pop { position: absolute; z-index: 60; width: 340px; max-height: 420px; overflow: auto; background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; box-shadow: 0 12px 32px rgba(15,23,42,.18); padding: 12px; }
        .cb-pop::before { content: ''; position: sticky; display: block; }
        .cb-pop-sec { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin: 10px 2px 6px; }
        .cb-pop-sec:first-child { margin-top: 0; }
        .cb-pop-styles { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .cb-pop-styles button { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; min-height: 72px; padding: 6px;
            border: 1px solid #eef2f7; border-radius: 10px; background: #fafafa; overflow: hidden; text-align: center; }
        .cb-pop-styles button:hover, .cb-pop-emojis button:hover { border-color: #3b82f6; background: #eff6ff; }
        .cb-pop-emojis { display: grid; grid-template-columns: repeat(8, 1fr); gap: 4px; }
        .cb-pop-emojis button { font-size: 22px; height: 36px; border-radius: 8px; border: 1px solid transparent; }
        .cb-pop-art { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
        .cb-pop-art button { aspect-ratio: 1; padding: 6px; border: 1px solid #eef2f7; border-radius: 10px; background: #fafafa; }
        .cb-pop-art button:hover { border-color: #3b82f6; background: #eff6ff; }
        .cb-pop-art img { width: 100%; height: 100%; object-fit: contain; }
        .cb-pop-sw { display: grid; grid-template-columns: repeat(10, 1fr); gap: 5px; }
        .cb-pop-sw button { aspect-ratio: 1; border-radius: 50%; border: 1px solid rgba(0,0,0,.12); }
        .cb-pop-sw button.on { outline: 2px solid #2563eb; outline-offset: 2px; }
        .cb-pop-plain { width: 100%; padding: 8px; border: 1px dashed #cbd5e1; border-radius: 10px; font-weight: 600; font-size: 13px; color: #334155; }
        .cb-pop-plain:hover { border-color: #3b82f6; background: #eff6ff; }
    </style>
@endpush

@section('content')
    @php
        $template = $template ?? null;
        $kind = $kind ?? ($template->kind ?? 'card');
        $isLayout = $kind === 'layout';
        $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
        $cfg = $template->canvas_config ?? [];
        $layoutView = old('view', $cfg['view'] ?? request('view', 'front'));
        $blank = ['bg' => '#ffffff', 'els' => []];
        $pages = old('pages') ? json_decode(old('pages'), true) : ($cfg['pages'] ?? []);
        foreach (['front', 'inL', 'inR', 'back'] as $pid) {
            // Layout pages are plain element arrays; templates are {bg, els}.
            $p = $pages[$pid] ?? [];
            $pages[$pid] = array_is_list($p) ? ['bg' => $cfg['bg'][$pid] ?? '#ffffff', 'els' => $p] : $p + $blank;
        }
    @endphp

    <form id="card-template-form" method="POST"
        action="{{ $template ? route($rPrefix . 'templates.update', $template) : route($rPrefix . 'templates.store') }}">
        @csrf
        @if ($template)
            @method('PUT')
        @endif
        <input type="hidden" name="kind" value="{{ $kind }}">
        <input type="hidden" name="pages" id="pagesInput">
        <input type="hidden" name="layout_bg" id="layoutBgInput">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="font-display font-bold text-2xl text-surface-900">{{ $isLayout ? ($template ? 'Edit Layout' : 'New Layout') : ($template ? 'Edit Card Template' : 'New Card Template') }}</h1>
                <p class="text-sm text-surface-500">{{ $isLayout ? "Arrange photo slots and text placeholders, then save. Shown in the card editor's Layouts panel; the customer's words and photos carry over into it." : "Design each page of the card on the canvas, then save. Shown in the card editor's Templates panel." }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route($rPrefix . 'templates.index', ['kind' => $kind]) }}"
                    class="px-4 py-2 text-sm font-semibold text-surface-600 bg-white border border-surface-200 rounded-xl hover:bg-surface-50 transition">Cancel</a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Template
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
                @foreach ($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            {{-- ── LEFT: Canvas Builder ── --}}
            <div class="xl:col-span-3">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-surface-800">Canvas Builder</h2>
                        <span class="text-xs text-surface-400">360 × 504 card page (5 × 7 in)</span>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-3" id="cbTabs"></div>

                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <button type="button" class="cb-tool" data-add="text"><i data-lucide="type" class="w-4 h-4"></i> Text</button>
                        <button type="button" class="cb-tool" data-add="image"><i data-lucide="image" class="w-4 h-4"></i> Image</button>
                        <button type="button" class="cb-tool" data-add="svg"><i data-lucide="star" class="w-4 h-4"></i> SVG</button>
                        <button type="button" class="cb-tool" data-add="photo"><i data-lucide="image-plus" class="w-4 h-4"></i> Photo slot</button>
                        <button type="button" class="cb-tool" data-add="sticker"><i data-lucide="smile" class="w-4 h-4"></i> Emoji</button>
                        <button type="button" class="cb-tool" data-add="qr"><i data-lucide="qr-code" class="w-4 h-4"></i> QR slot</button>
                        <span class="flex-1"></span>
                        <button type="button" class="cb-tool" id="cbBgBtn" title="Page background">
                            <i data-lucide="paint-bucket" class="w-4 h-4"></i>
                            <span id="cbBgDot" class="w-5 h-4 rounded border border-surface-300"></span>
                        </button>
                        @if ($isLayout)
                            {{-- Layouts only change the customer's background when one is set here --}}
                            <button type="button" class="cb-tool" id="cbBgOff" title="Keep the customer's background">No background</button>
                        @endif
                        <button type="button" class="cb-tool danger" id="cbDelete"><i data-lucide="trash-2" class="w-4 h-4"></i> Delete</button>
                        <button type="button" class="cb-tool" id="cbClear"><i data-lucide="eraser" class="w-4 h-4"></i> Clear</button>
                    </div>
                    <input type="file" id="cbFile" accept="image/png,image/jpeg,image/webp,image/svg+xml" hidden>

                    <div class="cb-stage"><div class="cb-page" id="cbPage"></div></div>
                    <p class="text-xs text-surface-400 text-center mt-3">Click an element to select it — drag to move, drag the corner to resize — double-click text to edit</p>
                </div>
            </div>

            {{-- ── RIGHT: Settings + element properties ── --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 space-y-4">
                    <h2 class="font-semibold text-surface-800">{{ $isLayout ? 'Layout Settings' : 'Template Settings' }}</h2>
                    <div>
                        <label class="cb-lbl">Name *</label>
                        <input name="name" required class="cb-in" value="{{ old('name', $template->name ?? '') }}">
                    </div>
                    @if ($isLayout)
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="cb-lbl">Used on *</label>
                                <select name="view" id="cbView" class="cb-in">
                                    @foreach (['front' => 'Front', 'inside' => 'Inside (left + right)', 'back' => 'Back'] as $v => $l)
                                        <option value="{{ $v }}" @selected($layoutView === $v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="cb-lbl">Sort order</label>
                                <input type="number" name="sort_order" min="0" class="cb-in" value="{{ old('sort_order', $template->sort_order ?? 0) }}">
                            </div>
                        </div>
                        <label class="flex items-center gap-2 text-sm font-semibold text-surface-800"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active (visible in card editor)</label>
                    @else
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="cb-lbl">Category id *</label>
                            <input name="category" required class="cb-in" placeholder="birthday" value="{{ old('category', $cfg['category'] ?? '') }}">
                        </div>
                        <div>
                            <label class="cb-lbl">Category name</label>
                            <input name="category_name" class="cb-in" placeholder="Birthday" value="{{ old('category_name', $cfg['category_name'] ?? '') }}">
                        </div>
                    </div>
                    <div>
                        <label class="cb-lbl">Style</label>
                        <input name="style" class="cb-in" placeholder="Colorful celebration" value="{{ old('style', $cfg['style'] ?? '') }}">
                    </div>
                    <div>
                        <label class="cb-lbl">Tags (comma separated)</label>
                        <input name="tags" class="cb-in" value="{{ old('tags', implode(', ', $cfg['tags'] ?? [])) }}">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="cb-lbl">Popularity</label>
                            <input type="number" name="popular" min="0" max="1000" class="cb-in" value="{{ old('popular', $cfg['popular'] ?? 0) }}">
                        </div>
                        <div>
                            <label class="cb-lbl">Sort order</label>
                            <input type="number" name="sort_order" min="0" class="cb-in" value="{{ old('sort_order', $template->sort_order ?? 0) }}">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-surface-700"><input type="checkbox" name="featured" value="1" @checked(old('featured', $cfg['featured'] ?? false))> Featured</label>
                    <label class="flex items-center gap-2 text-sm font-semibold text-surface-800"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active (visible in card editor)</label>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5" id="cbProps"></div>
            </div>
        </div>
    </form>

    <script>
    (function () {
        const PW = 360, PH = 504;
        const ASSET_BASE = @json(asset('assets/templates') . '/');
        const UPLOAD_URL = @json(route('admin.templates.upload-asset'));
        const CSRF = @json(csrf_token());
        const PAGE_LABEL = { front: 'Front', inL: 'Inside left', inR: 'Inside right', back: 'Back' };
        /* Text styles, emoji sets and background palettes saved in admin (same lists the card editor shows) */
        const LIBRARY = @json(\App\Http\Controllers\CardEditorController::libraryForEditor());
        const FONTS = ['Onest', 'Playfair Display', 'Cormorant Garamond', 'DM Serif Display', 'Great Vibes', 'Pinyon Script', 'Caveat',
            'Bebas Neue', 'Josefin Sans', 'Montserrat', 'Lora', 'Cinzel', 'Abril Fatface', 'Dancing Script', 'Sacramento', 'Alex Brush',
            'Pacifico', 'Lobster', 'Permanent Marker', 'Amatic SC', 'Righteous', 'Fredoka'];

        const pages = @json($pages);
        const IS_LAYOUT = @json($isLayout);
        const bgSet = Object.fromEntries(Object.keys(@json((object) ($cfg['bg'] ?? []))).map(p => [p, true]));
        const VIEW_PAGES = { front: ['front'], inside: ['inL', 'inR'], back: ['back'] };
        const viewSel = document.getElementById('cbView');
        const pageIds = () => IS_LAYOUT ? VIEW_PAGES[viewSel.value] : Object.keys(PAGE_LABEL);
        let pid = pageIds()[0], selIdx = -1, pendingAdd = null;
        const $ = id => document.getElementById(id);
        const pageEl = $('cbPage'), props = $('cbProps');
        const uid = () => 'a' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
        const abs = s => /^(https?:|data:|\/)/.test(s) ? s : ASSET_BASE + s;
        const els = () => pages[pid].els;
        const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

        /* Scale the 360 x 504 page to the column width (max 1:1.1) */
        let k = 1;
        function fit() {
            const avail = pageEl.parentElement.clientWidth - 36;
            k = Math.min(1.1, avail / PW);
            pageEl.style.width = PW * k + 'px'; pageEl.style.height = PH * k + 'px';
            render();
        }

        function tabs() {
            $('cbTabs').innerHTML = pageIds().map(p =>
                `<button type="button" class="cb-tab ${p === pid ? 'on' : ''}" data-p="${p}">${PAGE_LABEL[p]} <span class="opacity-60">(${pages[p].els.length})</span></button>`).join('');
            $('cbTabs').querySelectorAll('[data-p]').forEach(b => b.onclick = () => { pid = b.dataset.p; selIdx = -1; tabs(); render(); });
            $('cbBgDot').style.background = pages[pid].bg || '#ffffff';
            if (IS_LAYOUT) $('cbBgOff').style.opacity = bgSet[pid] ? 1 : .45;
        }

        function render() {
            pageEl.style.background = pages[pid].bg || '#fff';
            pageEl.innerHTML = '';
            els().forEach((el, i) => {
                const d = document.createElement('div');
                d.className = 'cb-el' + (i === selIdx ? ' sel' : '');
                Object.assign(d.style, { left: el.x * k + 'px', top: el.y * k + 'px', width: el.w * k + 'px',
                    transform: `rotate(${el.rot || 0}deg)` + (el.flipX ? ' scaleX(-1)' : '') });
                if (el.type !== 'text') d.style.height = el.h * k + 'px';
                if (el.type === 'text') {
                    const t = document.createElement('div');
                    t.className = 'cb-text';
                    Object.assign(t.style, { fontFamily: `'${el.font || 'Onest'}'`, fontSize: (el.size || 24) * k + 'px', color: el.color || '#000',
                        textAlign: el.align || 'center', fontWeight: el.bold ? 700 : 400, fontStyle: el.italic ? 'italic' : 'normal',
                        lineHeight: el.lh || 1.2, letterSpacing: (el.ls || 0) * k + 'px' });
                    t.textContent = el.text || '';
                    d.appendChild(t);
                    d.ondblclick = () => {
                        t.contentEditable = 'true'; t.focus();
                        document.getSelection().selectAllChildren(t);
                        t.onblur = () => { t.contentEditable = 'false'; el.text = t.innerText.replace(/\n$/, ''); renderProps(); };
                    };
                } else if (el.type === 'photo') {
                    d.innerHTML = el.src ? `<img src="${esc(abs(el.src))}" style="object-fit:cover">` : '<div class="cb-slot">Customer photo</div>';
                    d.firstChild.style.borderRadius = el.shape === 'circle' ? '50%' : el.shape === 'square' ? '0' : (14 * k) + 'px';
                } else if (el.type === 'qr') {
                    d.innerHTML = '<div class="cb-slot">QR code</div>';
                } else if (el.type === 'sticker') {
                    d.style.fontSize = Math.min(el.w, el.h) * k * 0.85 + 'px';
                    d.style.lineHeight = el.h * k + 'px'; d.style.textAlign = 'center';
                    d.textContent = el.char || '⭐';
                    if (!el.char) el.char = '⭐'; /* what you see is what gets saved */
                } else {
                    d.innerHTML = `<img src="${esc(abs(el.src || ''))}" style="object-fit:${el.fit === 'fill' ? 'fill' : el.fit === 'cover' ? 'cover' : 'contain'}">`;
                }
                const h = document.createElement('span'); h.className = 'cb-h'; d.appendChild(h);
                d.onpointerdown = e => startDrag(e, i, e.target === h);
                pageEl.appendChild(d);
            });
            const n = $('cbTabs').querySelector('.on span'); if (n) n.textContent = `(${els().length})`;
        }

        function startDrag(e, i, resize) {
            if (e.target.isContentEditable) return;
            e.preventDefault();
            if (selIdx !== i) { selIdx = i; render(); renderProps(); }
            const el = els()[i], sx = e.clientX, sy = e.clientY, o = { ...el };
            const move = ev => {
                const dx = (ev.clientX - sx) / k, dy = (ev.clientY - sy) / k;
                if (resize) {
                    el.w = Math.max(10, Math.round(o.w + dx));
                    if (el.type !== 'text') el.h = Math.max(10, Math.round(ev.shiftKey ? o.h * el.w / o.w : o.h + dy));
                } else { el.x = Math.round(o.x + dx); el.y = Math.round(o.y + dy); }
                render();
            };
            const up = () => { removeEventListener('pointermove', move); removeEventListener('pointerup', up); renderProps(); };
            addEventListener('pointermove', move); addEventListener('pointerup', up);
        }

        /* Properties panel for the selected element */
        function field(label, key, type = 'number', extra = '') {
            const el = els()[selIdx], v = el[key] ?? '';
            if (type === 'textarea') return `<div class="col-span-2"><label class="cb-lbl">${label}</label><textarea data-k="${key}" rows="3" class="cb-in">${esc(v)}</textarea></div>`;
            if (type === 'check') return `<label class="flex items-center gap-2 text-sm"><input type="checkbox" data-k="${key}" ${v ? 'checked' : ''}> ${label}</label>`;
            return `<div ${extra}><label class="cb-lbl">${label}</label><input type="${type}" data-k="${key}" class="cb-in" value="${esc(v)}" ${type === 'number' ? 'step="any"' : ''}></div>`;
        }
        function select(label, key, opts) {
            const v = els()[selIdx][key] ?? opts[0];
            return `<div><label class="cb-lbl">${label}</label><select data-k="${key}" class="cb-in">${opts.map(o => `<option ${o === v ? 'selected' : ''}>${o}</option>`).join('')}</select></div>`;
        }
        function renderProps() {
            const el = els()[selIdx];
            if (!el) {
                props.innerHTML = '<div class="py-8 text-center text-sm text-surface-400"><i data-lucide="mouse-pointer-click" class="w-5 h-5 mx-auto mb-2"></i>Select a canvas element to edit its properties</div>';
                window.lucide && lucide.createIcons(); return;
            }
            const names = { text: 'Text', art: 'Image / SVG', photo: 'Photo slot', sticker: 'Emoji', qr: 'QR code slot' };
            let h = `<div class="flex items-center justify-between mb-3"><h3 class="font-semibold text-surface-800">${names[el.type] || el.type}</h3>
                <div class="flex gap-1">
                    <button type="button" class="cb-tool" data-act="up" title="Bring forward">↑</button>
                    <button type="button" class="cb-tool" data-act="down" title="Send backward">↓</button>
                    <button type="button" class="cb-tool" data-act="dup" title="Duplicate">⧉</button>
                </div></div><div class="grid grid-cols-2 gap-3">`;
            if (el.type === 'text') {
                h += field('Text', 'text', 'textarea') + select('Font', 'font', FONTS) + field('Size', 'size')
                    + field('Color', 'color', 'color') + select('Align', 'align', ['center', 'left', 'right'])
                    + field('Line height', 'lh') + field('Letter spacing', 'ls') + field('Bold', 'bold', 'check') + field('Italic', 'italic', 'check');
            }
            if (el.type === 'art') h += select('Fit', 'fit', ['contain', 'cover', 'fill']) + field('Flip horizontally', 'flipX', 'check')
                + `<div class="col-span-2"><button type="button" class="cb-tool" data-act="replace">Replace file</button></div>`;
            if (el.type === 'photo') h += select('Shape', 'shape', ['rounded', 'square', 'circle']);
            if (el.type === 'sticker') h += field('Emoji', 'char', 'text');
            h += field('X', 'x') + field('Y', 'y') + field('Width', 'w') + (el.type !== 'text' ? field('Height', 'h') : '<div></div>') + field('Rotation°', 'rot');
            props.innerHTML = h + '</div>';
            props.querySelectorAll('[data-k]').forEach(inp => inp.oninput = inp.onchange = () => {
                const key = inp.dataset.k;
                el[key] = inp.type === 'checkbox' ? inp.checked : inp.type === 'number' ? (inp.value === '' ? 0 : Number(inp.value)) : inp.value;
                render();
            });
            props.querySelectorAll('[data-act]').forEach(b => b.onclick = () => {
                const a = els();
                if (b.dataset.act === 'up' && selIdx < a.length - 1) { [a[selIdx], a[selIdx + 1]] = [a[selIdx + 1], a[selIdx]]; selIdx++; }
                if (b.dataset.act === 'down' && selIdx > 0) { [a[selIdx], a[selIdx - 1]] = [a[selIdx - 1], a[selIdx]]; selIdx--; }
                if (b.dataset.act === 'dup') { a.push({ ...el, id: uid(), x: el.x + 12, y: el.y + 12 }); selIdx = a.length - 1; }
                if (b.dataset.act === 'replace') { pendingAdd = { replace: el }; $('cbFile').click(); return; }
                render(); renderProps();
            });
        }

        const PLAIN_TEXT = () => ({ type: 'text', x: 40, y: 220, w: 280, text: 'Your text', font: 'Playfair Display', size: 34, color: '#222222', align: 'center', bold: false, italic: false, lh: 1.2, ls: 0 });
        function add(el) { els().push({ id: uid(), rot: 0, ...el }); selIdx = els().length - 1; tabs(); render(); renderProps(); }

        document.querySelectorAll('[data-add]').forEach(b => b.onclick = () => {
            const t = b.dataset.add;
            if (t === 'text') return openPicker(b, 'text');

            if (t === 'qr') add({ type: 'qr', x: 120, y: 150, w: 120, h: 120, url: '', color: '#14201a', src: null });
            if (t === 'photo') add({ type: 'photo', x: 80, y: 120, w: 200, h: 200, shape: 'rounded', fit: 'cover' });
            if (t === 'sticker') return openPicker(b, 'emoji');

            if (t === 'svg') return openPicker(b, 'svg');
            if (t === 'image') { pendingAdd = { kind: t }; $('cbFile').accept = t === 'svg' ? 'image/svg+xml' : 'image/png,image/jpeg,image/webp'; $('cbFile').click(); }
        });

        /* Image / SVG upload -> storage URL (existing templates upload endpoint) */
        $('cbFile').onchange = async e => {
            const f = e.target.files[0]; e.target.value = '';
            if (!f || !pendingAdd) return;
            const fd = new FormData();
            fd.append('file', f); fd.append('type', f.type === 'image/svg+xml' ? 'svg' : 'image');
            try {
                const r = await fetch(UPLOAD_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }, body: fd });
                if (!r.ok) throw new Error('Upload failed (' + r.status + ')');
                const { url } = await r.json();
                if (pendingAdd.replace) { pendingAdd.replace.src = url; render(); renderProps(); }
                else {
                    const img = new Image();
                    img.onload = () => {
                        const s = Math.min(1, 200 / Math.max(img.naturalWidth || 200, img.naturalHeight || 200));
                        const w = Math.round((img.naturalWidth || 200) * s), h = Math.round((img.naturalHeight || 200) * s);
                        add({ type: 'art', src: url, fit: 'contain', x: Math.round((PW - w) / 2), y: Math.round((PH - h) / 2), w, h });
                    };
                    img.src = url;
                }
            } catch (err) { alert(err.message); }
            pendingAdd = null;
        };

        $('cbDelete').onclick = () => { if (selIdx < 0) return; els().splice(selIdx, 1); selIdx = -1; tabs(); render(); renderProps(); };
        $('cbClear').onclick = () => { if (!els().length || !confirm('Remove every element from the ' + PAGE_LABEL[pid].toLowerCase() + '?')) return; pages[pid].els = []; selIdx = -1; tabs(); render(); renderProps(); };
        const setBg = c => { pages[pid].bg = c; bgSet[pid] = true; tabs(); render(); };
        if (IS_LAYOUT) $('cbBgOff').onclick = () => { pages[pid].bg = '#ffffff'; delete bgSet[pid]; tabs(); render(); };
        pageEl.addEventListener('pointerdown', e => { if (e.target === pageEl) { selIdx = -1; render(); renderProps(); } });
        document.addEventListener('keydown', e => {
            if (selIdx < 0 || /INPUT|TEXTAREA|SELECT/.test(e.target.tagName) || e.target.isContentEditable) return;
            const el = els()[selIdx], step = e.shiftKey ? 10 : 1;
            const mv = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] }[e.key];
            if (mv) { e.preventDefault(); el.x += mv[0]; el.y += mv[1]; render(); renderProps(); }
            if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); $('cbDelete').click(); }
        });

        /* Store asset paths relative to assets/templates/ like the imported library */
        $('card-template-form').addEventListener('submit', () => {
            const out = JSON.parse(JSON.stringify(pages));
            if (IS_LAYOUT) Object.keys(out).forEach(p => { if (!pageIds().includes(p)) delete out[p]; });
            if (IS_LAYOUT) $('layoutBgInput').value = JSON.stringify(Object.fromEntries(pageIds().filter(p => bgSet[p]).map(p => [p, pages[p].bg])));
            Object.values(out).forEach(p => p.els.forEach(el => { if (el.src && el.src.startsWith(ASSET_BASE)) el.src = el.src.slice(ASSET_BASE.length); }));
            $('pagesInput').value = JSON.stringify(out);
        });

        if (viewSel) viewSel.onchange = () => { pid = pageIds()[0]; selIdx = -1; tabs(); render(); renderProps(); };
        /* ── Pickers: tooltip-style popovers under the toolbar buttons ── */
        let pop = null;
        function closePicker() { if (pop) { pop.remove(); pop = null; } }
        function openPicker(btn, type) {
            const same = pop && pop.dataset.type === type; closePicker(); if (same) return;
            pop = document.createElement('div'); pop.className = 'cb-pop'; pop.dataset.type = type;
            const sec = t => `<div class="cb-pop-sec">${esc(t)}</div>`;
            if (type === 'text') {
                pop.innerHTML = `<button type="button" class="cb-pop-plain" data-plain>+ Plain text</button>` +
                    Object.entries(LIBRARY.pairs || {}).map(([cat, list]) => sec(cat) + `<div class="cb-pop-styles">${list.map((lines, i) =>
                        `<button type="button" data-cat="${esc(cat)}" data-i="${i}" title="${esc(lines.map(l => l.text).join(' / '))}">${lines.map(l =>
                            `<span style="font-family:'${esc(l.font)}';font-size:${Math.max(9, Math.min(24, Math.round(l.size * .42)))}px;color:${esc(l.color)};font-weight:${l.bold ? 700 : 400};font-style:${l.italic ? 'italic' : 'normal'};letter-spacing:${(l.ls || 0) * .4}px;line-height:1.1">${esc(l.text)}</span>`).join('')}</button>`).join('')}</div>`).join('');
                pop.querySelector('[data-plain]').onclick = () => { closePicker(); add(PLAIN_TEXT()); };
                pop.querySelectorAll('[data-cat]').forEach(b => b.onclick = () => { addTextStyle(LIBRARY.pairs[b.dataset.cat][b.dataset.i]); closePicker(); });
            }
            if (type === 'emoji') {
                pop.innerHTML = Object.entries(LIBRARY.stickers || {}).map(([cat, list]) => sec(cat) +
                    `<div class="cb-pop-emojis">${list.map(c => `<button type="button" data-c="${esc(c)}" title="${esc(c)}">${esc(c)}</button>`).join('')}</div>`).join('')
                    || '<p class="text-sm text-surface-400">No emoji sets saved yet.</p>';
                pop.querySelectorAll('[data-c]').forEach(b => b.onclick = () => { add({ type: 'sticker', x: 150, y: 200, w: 60, h: 60, char: b.dataset.c }); closePicker(); });
            }
            if (type === 'svg') {
                pop.innerHTML = `<button type="button" class="cb-pop-plain" data-upload>+ Upload a new SVG</button>` +
                    Object.entries(LIBRARY.art || {}).map(([g, list]) => sec(g) + `<div class="cb-pop-art">${list.map((a, i) =>
                        `<button type="button" data-g="${esc(g)}" data-i="${i}" title="${esc(a.name)}"><img src="${esc(a.src)}" alt="" loading="lazy"></button>`).join('')}</div>`).join('');
                pop.querySelector('[data-upload]').onclick = () => { closePicker(); pendingAdd = { kind: 'svg' }; $('cbFile').accept = 'image/svg+xml'; $('cbFile').click(); };
                pop.querySelectorAll('[data-g]').forEach(b => b.onclick = () => {
                    const a = LIBRARY.art[b.dataset.g][b.dataset.i];
                    add({ type: 'art', src: a.src, fit: 'contain', x: a.x, y: a.y, w: a.w, h: a.h }); closePicker();
                });
            }
            if (type === 'bg') {
                const cur = (pages[pid].bg || '').toLowerCase();
                pop.innerHTML = `<div class="flex items-center gap-2 mb-1"><span class="text-xs font-semibold text-surface-600">Custom</span><input type="color" id="cbBgCustom" value="${/^#[0-9a-f]{6}$/i.test(cur) ? cur : '#ffffff'}" class="w-10 h-7 rounded border border-surface-200 cursor-pointer"></div>` +
                    Object.entries(LIBRARY.bgColors || {}).map(([theme, list]) => sec(theme) +
                        `<div class="cb-pop-sw">${list.map(c => `<button type="button" data-bg="${esc(c)}" title="${esc(c)}" style="background:${esc(c)}" class="${c.toLowerCase() === cur ? 'on' : ''}"></button>`).join('')}</div>`).join('');
                pop.querySelector('#cbBgCustom').oninput = e => setBg(e.target.value);
                pop.querySelectorAll('[data-bg]').forEach(b => b.onclick = () => { setBg(b.dataset.bg); pop.querySelectorAll('.on').forEach(x => x.classList.remove('on')); b.classList.add('on'); });
            }
            document.body.appendChild(pop);
            const r = btn.getBoundingClientRect();
            pop.style.top = (r.bottom + window.scrollY + 6) + 'px';
            pop.style.left = Math.max(8, Math.min(r.left + window.scrollX, window.scrollX + document.documentElement.clientWidth - pop.offsetWidth - 8)) + 'px';
        }
        /* A saved text style: its lines keep their saved positions, or stack like the editor does */
        function addTextStyle(lines) {
            const placed = lines.every(l => typeof l.y === 'number'), base = 170; let y = base;
            lines.forEach(l => {
                els().push({ id: uid(), rot: 0, type: 'text', x: 20, w: 320, align: 'center', color: '#2a2a2a', font: 'Onest', size: 20, lh: 1.35, ls: 0, bold: false, italic: false,
                    ...l, y: placed ? base + l.y : y });
                y += Math.round((l.size || 20) * (l.lh || 1.35)) + 14;
            });
            selIdx = els().length - lines.length; tabs(); render(); renderProps();
        }
        $('cbBgBtn').onclick = () => openPicker($('cbBgBtn'), 'bg');
        document.addEventListener('pointerdown', e => { if (pop && !pop.contains(e.target) && !e.target.closest('[data-add=text],[data-add=sticker],[data-add=svg],#cbBgBtn')) closePicker(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closePicker(); });
        window.addEventListener('resize', closePicker);

        window.addEventListener('resize', fit);
        tabs(); fit(); renderProps();
        document.fonts && document.fonts.ready.then(render);
    })();
    </script>
@endsection
