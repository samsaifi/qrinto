@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
{{-- Card editor SVG artwork: one SVG with the default place and size it gets when added to a card. --}}
@php
    $template = $template ?? null;
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
    $cfg = $template->canvas_config ?? [];
    $val = fn($k, $d) => old($k, $cfg[$k] ?? $d);
    $groups = \App\Models\Template::where('kind', 'svg_art')->get()
        ->pluck('canvas_config.group')->filter()->unique()->sort()->values();
@endphp
@section('title', $template ? 'Edit SVG' : 'New SVG')

@push('styles')
    <style>
        .sb-stage { background: #f5f3ee; border-radius: 14px; padding: 18px; display: flex; justify-content: center; }
        .sb-page { position: relative; overflow: hidden; background: #fff; box-shadow: 0 4px 18px rgba(0,0,0,.12); user-select: none;
            background-image: linear-gradient(#f1f5f9 1px, transparent 1px), linear-gradient(90deg, #f1f5f9 1px, transparent 1px); background-size: 20px 20px; }
        .sb-art { position: absolute; cursor: move; outline: 2px solid #2563eb; outline-offset: 1px; }
        .sb-art img { width: 100%; height: 100%; object-fit: contain; display: block; pointer-events: none; }
        .sb-art .sb-empty { width: 100%; height: 100%; display: grid; place-items: center; font: 600 12px Onest, sans-serif; color: #64748b; background: #f1f5f9; }
        .sb-h { position: absolute; right: -6px; bottom: -6px; width: 12px; height: 12px; background: #fff; border: 2px solid #2563eb; border-radius: 3px; cursor: nwse-resize; }
        .sb-tool { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; font-size: 13px; font-weight: 600; color: #475569; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
        .sb-tool:hover { background: #f8fafc; }
        .sb-lbl { display: block; font-size: 11px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .sb-in { width: 100%; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; background: #fff; }
        .sb-in:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px #dbeafe; }
    </style>
@endpush

@section('content')
    <form id="svg-form" method="POST" action="{{ $template ? route($rPrefix . 'templates.update', $template) : route($rPrefix . 'templates.store') }}">
        @csrf
        @if ($template)
            @method('PUT')
        @endif
        <input type="hidden" name="kind" value="svg_art">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="font-display font-bold text-2xl text-surface-900">{{ $template ? 'Edit SVG' : 'New SVG' }}</h1>
                <p class="text-sm text-surface-500">Artwork in the card editor's Stickers panel and in the template builder. Place and size it on the card: that is where it lands when added.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route($rPrefix . 'templates.index', ['kind' => 'svg_art']) }}"
                    class="px-4 py-2 text-sm font-semibold text-surface-600 bg-white border border-surface-200 rounded-xl hover:bg-surface-50 transition">Cancel</a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <i data-lucide="save" class="w-4 h-4"></i> Save SVG
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
            {{-- LEFT: canvas with the SVG only --}}
            <div class="xl:col-span-3">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-surface-800">Canvas Builder</h2>
                        <span class="text-xs text-surface-400">SVG only · 360 × 504 card page</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <button type="button" class="sb-tool" id="sbUpload"><i data-lucide="upload" class="w-4 h-4"></i> Upload SVG</button>
                        <span class="flex-1"></span>
                        <button type="button" class="sb-tool" id="sbCenter"><i data-lucide="align-center-horizontal" class="w-4 h-4"></i> Center</button>
                        <button type="button" class="sb-tool" id="sbFit"><i data-lucide="scaling" class="w-4 h-4"></i> Original ratio</button>
                    </div>
                    <input type="file" id="sbFile" accept="image/svg+xml,.svg" hidden>
                    <div class="sb-stage"><div class="sb-page" id="sbPage"></div></div>
                    <p class="text-xs text-surface-400 text-center mt-3">Drag the artwork to move it, drag the corner to resize (Shift keeps the ratio). Arrow keys nudge it.</p>
                </div>
            </div>

            {{-- RIGHT: settings --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 space-y-4">
                    <h2 class="font-semibold text-surface-800">SVG Settings</h2>
                    <div>
                        <label class="sb-lbl">Name *</label>
                        <input name="name" required class="sb-in" value="{{ old('name', $template->name ?? '') }}" placeholder="Birthday cake">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="sb-lbl">Group *</label>
                            <input name="group" required list="sbGroups" class="sb-in" value="{{ $val('group', '') }}" placeholder="Birthday">
                            <datalist id="sbGroups">
                                @foreach ($groups as $g)
                                    <option value="{{ $g }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="sb-lbl">Sort order</label>
                            <input type="number" name="sort_order" min="0" class="sb-in" value="{{ old('sort_order', $template->sort_order ?? 0) }}">
                        </div>
                    </div>
                    <div>
                        <label class="sb-lbl">File *</label>
                        <input name="src" id="sbSrc" required class="sb-in font-mono text-xs" value="{{ $val('src', '') }}" placeholder="birthday/cake.svg or an uploaded file URL">
                        <p class="text-xs text-surface-400 mt-1">Paths are relative to public/assets/templates/. "Upload SVG" fills this in.</p>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach (['x' => 110, 'y' => 180, 'w' => 140, 'h' => 140] as $k => $d)
                            <div>
                                <label class="sb-lbl">{{ strtoupper($k) }}</label>
                                <input type="number" name="{{ $k }}" id="sb{{ strtoupper($k) }}" class="sb-in" value="{{ $val($k, $d) }}">
                            </div>
                        @endforeach
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-surface-800"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active (visible in card editor)</label>
                </div>
            </div>
        </div>
    </form>

    <script>
    (function () {
        const PW = 360, PH = 504;
        const ASSET_BASE = @json(asset('assets/templates') . '/');
        const UPLOAD_URL = @json(route('admin.templates.upload-asset'));
        const CSRF = @json(csrf_token());
        const $ = id => document.getElementById(id);
        const page = $('sbPage'), src = $('sbSrc');
        const box = { x: +$('sbX').value, y: +$('sbY').value, w: +$('sbW').value, h: +$('sbH').value };
        const abs = s => /^(https?:|data:|\/)/.test(s) ? s : ASSET_BASE + s;
        let k = 1, ratio = null;

        function fit() { k = Math.min(1.1, (page.parentElement.clientWidth - 36) / PW); page.style.width = PW * k + 'px'; page.style.height = PH * k + 'px'; render(); }
        function sync() { ['x', 'y', 'w', 'h'].forEach(p => $('sb' + p.toUpperCase()).value = Math.round(box[p])); }
        function render() {
            page.innerHTML = '';
            const d = document.createElement('div'); d.className = 'sb-art';
            Object.assign(d.style, { left: box.x * k + 'px', top: box.y * k + 'px', width: box.w * k + 'px', height: box.h * k + 'px' });
            d.innerHTML = src.value.trim() ? `<img src="${abs(src.value.trim()).replace(/"/g, '&quot;')}" alt="">` : '<div class="sb-empty">Upload an SVG</div>';
            const img = d.querySelector('img');
            if (img) img.onload = () => { ratio = img.naturalWidth && img.naturalHeight ? img.naturalWidth / img.naturalHeight : null; };
            const h = document.createElement('span'); h.className = 'sb-h'; d.appendChild(h);
            d.onpointerdown = e => {
                e.preventDefault();
                const resize = e.target === h, sx = e.clientX, sy = e.clientY, o = { ...box };
                const move = ev => {
                    const dx = (ev.clientX - sx) / k, dy = (ev.clientY - sy) / k;
                    if (resize) { box.w = Math.max(10, o.w + dx); box.h = Math.max(10, ev.shiftKey ? box.w * o.h / o.w : o.h + dy); }
                    else { box.x = o.x + dx; box.y = o.y + dy; }
                    sync(); render();
                };
                const up = () => { removeEventListener('pointermove', move); removeEventListener('pointerup', up); };
                addEventListener('pointermove', move); addEventListener('pointerup', up);
            };
            page.appendChild(d);
        }

        ['X', 'Y', 'W', 'H'].forEach(p => $('sb' + p).addEventListener('input', e => { box[p.toLowerCase()] = Number(e.target.value || 0); render(); }));
        src.addEventListener('change', render);
        $('sbCenter').onclick = () => { box.x = (PW - box.w) / 2; box.y = (PH - box.h) / 2; sync(); render(); };
        $('sbFit').onclick = () => { if (ratio) { box.h = box.w / ratio; sync(); render(); } };
        document.addEventListener('keydown', e => {
            if (/INPUT|TEXTAREA|SELECT/.test(e.target.tagName)) return;
            const s = e.shiftKey ? 10 : 1, mv = { ArrowLeft: [-s, 0], ArrowRight: [s, 0], ArrowUp: [0, -s], ArrowDown: [0, s] }[e.key];
            if (mv) { e.preventDefault(); box.x += mv[0]; box.y += mv[1]; sync(); render(); }
        });

        /* Upload -> storage URL; new artwork lands centred at 140 px on its longer side */
        $('sbUpload').onclick = () => $('sbFile').click();
        $('sbFile').onchange = async e => {
            const f = e.target.files[0]; e.target.value = ''; if (!f) return;
            const fd = new FormData(); fd.append('file', f); fd.append('type', 'svg');
            try {
                const r = await fetch(UPLOAD_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }, body: fd });
                if (!r.ok) throw new Error('Upload failed (' + r.status + ')');
                const { url } = await r.json();
                src.value = url;
                const img = new Image();
                img.onload = () => {
                    const s = 140 / Math.max(img.naturalWidth || 140, img.naturalHeight || 140);
                    box.w = (img.naturalWidth || 140) * s; box.h = (img.naturalHeight || 140) * s;
                    box.x = (PW - box.w) / 2; box.y = (PH - box.h) / 2; sync(); render();
                };
                img.src = url;
                const name = document.querySelector('[name=name]');
                if (!name.value) name.value = f.name.replace(/\.svg$/i, '').replace(/[-_]+/g, ' ');
            } catch (err) { alert(err.message); }
        };

        window.addEventListener('resize', fit);
        fit();
    })();
    </script>
@endsection
