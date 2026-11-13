@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
{{-- Card editor Text panel style: one or more lines added together. Text-only canvas builder. --}}
@php
    $template = $template ?? null;
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
    $cfg = $template->canvas_config ?? [];
    $lines = old('lines') ? json_decode(old('lines'), true) : ($cfg['lines'] ?? [
        ['text' => 'Add a heading', 'font' => 'Playfair Display', 'size' => 42, 'color' => '#2a2a2a'],
        ['text' => 'Add a short line', 'font' => 'Onest', 'size' => 16, 'color' => '#555555'],
    ]);
    $categories = \App\Models\Template::where('kind', 'text_style')->get()
        ->pluck('canvas_config.category')->filter()->unique()->sort()->values();
@endphp
@section('title', $template ? 'Edit Text Style' : 'New Text Style')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Great+Vibes&family=Pinyon+Script&family=Caveat:wght@400;700&family=Bebas+Neue&family=Josefin+Sans:wght@300;600&family=DM+Serif+Display&family=Dancing+Script:wght@400;700&family=Pacifico&family=Lobster&family=Abril+Fatface&family=Montserrat:wght@400;600;700&family=Lora:ital,wght@0,400;0,700;1,400&family=Sacramento&family=Alex+Brush&family=Cinzel:wght@400;700&family=Amatic+SC:wght@400;700&family=Righteous&family=Fredoka:wght@400;700&family=Permanent+Marker&display=swap">
    <style>
        .tb-stage { background: #f5f3ee; border-radius: 14px; padding: 18px; display: flex; justify-content: center; }
        .tb-page { position: relative; overflow: hidden; background: #fff; box-shadow: 0 4px 18px rgba(0,0,0,.12); user-select: none; }
        .tb-el { position: absolute; cursor: move; white-space: pre-wrap; word-break: break-word; box-sizing: border-box; }
        .tb-el.sel { outline: 2px solid #2563eb; outline-offset: 1px; }
        .tb-el[contenteditable="true"] { cursor: text; outline: 2px solid #2563eb; background: rgba(37,99,235,.06); }
        .tb-h { position: absolute; right: -6px; top: 50%; margin-top: -6px; width: 12px; height: 12px; background: #fff; border: 2px solid #2563eb; border-radius: 3px; cursor: ew-resize; display: none; }
        .tb-el.sel .tb-h { display: block; }
        .tb-tool { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; font-size: 13px; font-weight: 600; color: #475569; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
        .tb-tool:hover { background: #f8fafc; }
        .tb-tool.danger { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
        .tb-lbl { display: block; font-size: 11px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .tb-in { width: 100%; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; background: #fff; }
        .tb-in:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px #dbeafe; }
    </style>
@endpush

@section('content')
    <form id="text-style-form" method="POST" action="{{ $template ? route($rPrefix . 'templates.update', $template) : route($rPrefix . 'templates.store') }}">
        @csrf
        @if ($template)
            @method('PUT')
        @endif
        <input type="hidden" name="kind" value="text_style">
        <input type="hidden" name="lines" id="linesInput">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="font-display font-bold text-2xl text-surface-900">{{ $template ? 'Edit Text Style' : 'New Text Style' }}</h1>
                <p class="text-sm text-surface-500">Lines in the card editor's Text panel. They are added to the card together, in the positions set here.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route($rPrefix . 'templates.index', ['kind' => 'text_style']) }}"
                    class="px-4 py-2 text-sm font-semibold text-surface-600 bg-white border border-surface-200 rounded-xl hover:bg-surface-50 transition">Cancel</a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Text Style
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
            {{-- LEFT: text-only canvas --}}
            <div class="xl:col-span-3">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-surface-800">Canvas Builder</h2>
                        <span class="text-xs text-surface-400">Text only · 360 × 504 card page</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <button type="button" class="tb-tool" id="tbAdd"><i data-lucide="type" class="w-4 h-4"></i> Add line</button>
                        <span class="flex-1"></span>
                        <button type="button" class="tb-tool danger" id="tbDelete"><i data-lucide="trash-2" class="w-4 h-4"></i> Delete</button>
                        <button type="button" class="tb-tool" id="tbClear"><i data-lucide="eraser" class="w-4 h-4"></i> Clear</button>
                    </div>
                    <div class="tb-stage"><div class="tb-page" id="tbPage"></div></div>
                    <p class="text-xs text-surface-400 text-center mt-3">Click a line to select it — drag to move, drag the side handle to change its width — double-click to type</p>
                </div>
            </div>

            {{-- RIGHT: settings + line properties --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 space-y-4">
                    <h2 class="font-semibold text-surface-800">Text Style Settings</h2>
                    <div>
                        <label class="tb-lbl">Name *</label>
                        <input name="name" required class="tb-in" value="{{ old('name', $template->name ?? '') }}" placeholder="Happy birthday">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="tb-lbl">Group *</label>
                            <input name="category" required list="tbCats" class="tb-in" value="{{ old('category', $cfg['category'] ?? '') }}" placeholder="Birthday">
                            <datalist id="tbCats">
                                @foreach ($categories as $c)
                                    <option value="{{ $c }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="tb-lbl">Sort order</label>
                            <input type="number" name="sort_order" min="0" class="tb-in" value="{{ old('sort_order', $template->sort_order ?? 0) }}">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-surface-800"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active (visible in card editor)</label>
                </div>
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5" id="tbProps"></div>
            </div>
        </div>
    </form>

    <script>
    (function () {
        const PW = 360, PH = 504, TOP = 170;
        const FONTS = ['Onest', 'Playfair Display', 'Cormorant Garamond', 'DM Serif Display', 'Great Vibes', 'Pinyon Script', 'Caveat',
            'Bebas Neue', 'Josefin Sans', 'Montserrat', 'Lora', 'Cinzel', 'Abril Fatface', 'Dancing Script', 'Sacramento', 'Alex Brush',
            'Pacifico', 'Lobster', 'Permanent Marker', 'Amatic SC', 'Righteous', 'Fredoka'];
        const $ = id => document.getElementById(id);
        const page = $('tbPage'), props = $('tbProps');
        const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

        /* Lines without saved positions are stacked the way the editor adds them */
        const lines = @json($lines).map(l => ({ x: 20, w: 320, align: 'center', color: '#2a2a2a', font: 'Onest', size: 20, lh: 1.35, ls: 0, bold: false, italic: false, ...l }));
        let y = 0;
        lines.forEach(l => { if (typeof l.y !== 'number') l.y = y; y = l.y + Math.round(l.size * (l.lh || 1.35)) + 14; });
        let sel = lines.length ? 0 : -1, k = 1;

        function fit() { k = Math.min(1.1, (page.parentElement.clientWidth - 36) / PW); page.style.width = PW * k + 'px'; page.style.height = PH * k + 'px'; render(); }

        function render() {
            page.innerHTML = '';
            lines.forEach((l, i) => {
                const d = document.createElement('div');
                d.className = 'tb-el' + (i === sel ? ' sel' : '');
                Object.assign(d.style, { left: l.x * k + 'px', top: (TOP + l.y) * k + 'px', width: l.w * k + 'px',
                    fontFamily: `'${l.font}'`, fontSize: l.size * k + 'px', color: l.color, textAlign: l.align,
                    fontWeight: l.bold ? 700 : 400, fontStyle: l.italic ? 'italic' : 'normal', lineHeight: l.lh || 1.35, letterSpacing: (l.ls || 0) * k + 'px' });
                d.textContent = l.text;
                const h = document.createElement('span'); h.className = 'tb-h'; d.appendChild(h);
                d.onpointerdown = e => drag(e, i, e.target === h);
                d.ondblclick = () => {
                    h.remove(); d.contentEditable = 'true'; d.focus(); document.getSelection().selectAllChildren(d);
                    d.onblur = () => { l.text = d.innerText.replace(/\n$/, '') || 'Text'; render(); renderProps(); };
                };
                page.appendChild(d);
            });
        }

        function drag(e, i, resize) {
            if (e.target.isContentEditable) return;
            e.preventDefault();
            if (sel !== i) { sel = i; render(); renderProps(); }
            const l = lines[i], sx = e.clientX, sy = e.clientY, o = { ...l };
            const move = ev => {
                const dx = (ev.clientX - sx) / k, dy = (ev.clientY - sy) / k;
                if (resize) l.w = Math.max(30, Math.round(o.w + dx)); else { l.x = Math.round(o.x + dx); l.y = Math.round(o.y + dy); }
                render();
            };
            const up = () => { removeEventListener('pointermove', move); removeEventListener('pointerup', up); renderProps(); };
            addEventListener('pointermove', move); addEventListener('pointerup', up);
        }

        function renderProps() {
            const l = lines[sel];
            if (!l) { props.innerHTML = '<div class="py-8 text-center text-sm text-surface-400">Select a line to edit its style</div>'; return; }
            const num = (lbl, key, step = 1) => `<div><label class="tb-lbl">${lbl}</label><input type="number" step="${step}" data-k="${key}" class="tb-in" value="${l[key] ?? 0}"></div>`;
            props.innerHTML = `<div class="flex items-center justify-between mb-3"><h3 class="font-semibold text-surface-800">Line ${sel + 1} of ${lines.length}</h3>
                <div class="flex gap-1"><button type="button" class="tb-tool" data-act="dup" title="Duplicate">⧉</button></div></div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2"><label class="tb-lbl">Text</label><textarea data-k="text" rows="2" class="tb-in">${esc(l.text)}</textarea></div>
                    <div><label class="tb-lbl">Font</label><select data-k="font" class="tb-in">${FONTS.map(f => `<option ${f === l.font ? 'selected' : ''} style="font-family:'${f}'">${f}</option>`).join('')}</select></div>
                    ${num('Size', 'size')}
                    <div><label class="tb-lbl">Colour</label><input type="color" data-k="color" class="tb-in h-10 p-1" value="${/^#[0-9a-f]{6}$/i.test(l.color) ? l.color : '#2a2a2a'}"></div>
                    <div><label class="tb-lbl">Align</label><select data-k="align" class="tb-in">${['center', 'left', 'right'].map(a => `<option ${a === l.align ? 'selected' : ''}>${a}</option>`).join('')}</select></div>
                    ${num('Line height', 'lh', 0.05)}${num('Letter spacing', 'ls', 0.5)}
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" data-k="bold" ${l.bold ? 'checked' : ''}> Bold</label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" data-k="italic" ${l.italic ? 'checked' : ''}> Italic</label>
                    ${num('X', 'x')}${num('Width', 'w')}
                </div>`;
            props.querySelectorAll('[data-k]').forEach(inp => inp.oninput = inp.onchange = () => {
                const key = inp.dataset.k;
                l[key] = inp.type === 'checkbox' ? inp.checked : inp.type === 'number' ? Number(inp.value || 0) : inp.value;
                render();
            });
            props.querySelector('[data-act=dup]').onclick = () => { lines.push({ ...l, y: l.y + Math.round(l.size * 1.4) + 10 }); sel = lines.length - 1; render(); renderProps(); };
        }

        $('tbAdd').onclick = () => {
            const last = lines[lines.length - 1];
            lines.push({ text: 'New line', font: 'Onest', size: 18, color: '#2a2a2a', align: 'center', x: 20, w: 320, lh: 1.35, ls: 0, bold: false, italic: false,
                y: last ? last.y + Math.round(last.size * (last.lh || 1.35)) + 14 : 0 });
            sel = lines.length - 1; render(); renderProps();
        };
        $('tbDelete').onclick = () => { if (sel < 0) return; lines.splice(sel, 1); sel = Math.min(sel, lines.length - 1); render(); renderProps(); };
        $('tbClear').onclick = () => { if (lines.length && confirm('Remove every line?')) { lines.length = 0; sel = -1; render(); renderProps(); } };
        page.addEventListener('pointerdown', e => { if (e.target === page) { sel = -1; render(); renderProps(); } });
        document.addEventListener('keydown', e => {
            if (sel < 0 || /INPUT|TEXTAREA|SELECT/.test(e.target.tagName) || e.target.isContentEditable) return;
            const step = e.shiftKey ? 10 : 1, mv = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] }[e.key];
            if (mv) { e.preventDefault(); lines[sel].x += mv[0]; lines[sel].y += mv[1]; render(); renderProps(); }
        });

        /* Save positions relative to the top line, so the editor can place the group anywhere */
        $('text-style-form').addEventListener('submit', () => {
            const top = Math.min(...lines.map(l => l.y));
            $('linesInput').value = JSON.stringify(lines.map(l => ({ ...l, y: l.y - top })));
        });

        window.addEventListener('resize', fit);
        fit(); renderProps();
        document.fonts && document.fonts.ready.then(render);
    })();
    </script>
@endsection
