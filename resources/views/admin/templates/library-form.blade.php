@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
{{-- Card editor Stickers panel (emoji set) or Background panel (colour palette): one category per row. --}}
@php
    $template = $template ?? null;
    $isBg = $kind === 'bg_palette';
    $noun = $isBg ? 'Background Colours' : 'Emoji Set';
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
    $items = old('items', implode($isBg ? "\n" : ' ', $template->canvas_config['items'] ?? []));
@endphp
@section('title', ($template ? 'Edit ' : 'New ') . $noun)

@push('styles')
    <style>
        .lb-lbl { display: block; font-size: 11px; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
        .lb-in { width: 100%; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; background: #fff; }
        .lb-in:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px #dbeafe; }
        .lb-emojis { display: grid; grid-template-columns: repeat(auto-fill, minmax(52px, 1fr)); gap: 8px; }
        .lb-emojis span { display: grid; place-items: center; height: 52px; font-size: 30px; background: #f8fafc; border: 1px solid #eef2f7; border-radius: 12px; }
        .lb-swatches { display: grid; grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); gap: 8px; }
        .lb-swatches button { aspect-ratio: 1; border-radius: 50%; border: 1px solid rgba(0,0,0,.12); position: relative; }
        .lb-swatches button:hover::after { content: '×'; position: absolute; inset: 0; display: grid; place-items: center; color: #fff; font-weight: 700; text-shadow: 0 0 3px #000; }
        .lb-card { margin: 0 auto; width: 220px; aspect-ratio: 5 / 7; border-radius: 6px; box-shadow: 0 4px 18px rgba(0,0,0,.14); transition: background .2s; }
    </style>
@endpush

@section('content')
    <form method="POST" action="{{ $template ? route($rPrefix . 'templates.update', $template) : route($rPrefix . 'templates.store') }}">
        @csrf
        @if ($template)
            @method('PUT')
        @endif
        <input type="hidden" name="kind" value="{{ $kind }}">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="font-display font-bold text-2xl text-surface-900">{{ ($template ? 'Edit ' : 'New ') . $noun }}</h1>
                <p class="text-sm text-surface-500">
                    {{ $isBg ? "One colour group in the card editor's Background panel." : "One emoji group in the card editor's Stickers panel." }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route($rPrefix . 'templates.index', ['kind' => $kind]) }}"
                    class="px-4 py-2 text-sm font-semibold text-surface-600 bg-white border border-surface-200 rounded-xl hover:bg-surface-50 transition">Cancel</a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <i data-lucide="save" class="w-4 h-4"></i> Save
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
            {{-- LEFT: preview --}}
            <div class="xl:col-span-3 bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-surface-800">Preview</h2>
                    <span class="text-xs text-surface-400" id="lbCount"></span>
                </div>
                <div class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-3" id="lbTitle"></div>
                @if ($isBg)
                    <div class="grid sm:grid-cols-[1fr_240px] gap-6 items-start">
                        <div class="lb-swatches" id="lbPreview"></div>
                        <div>
                            <div class="lb-card" id="lbCard" style="background:#fff"></div>
                            <p class="text-xs text-surface-400 text-center mt-2">Hover a colour to see it on a card · click to remove it</p>
                        </div>
                    </div>
                @else
                    <div class="lb-emojis" id="lbPreview"></div>
                @endif
            </div>

            {{-- RIGHT: form --}}
            <div class="xl:col-span-2 bg-white rounded-2xl border border-surface-100 shadow-card p-5 space-y-4 self-start">
                <h2 class="font-semibold text-surface-800">Settings</h2>
                <div>
                    <label class="lb-lbl">{{ $isBg ? 'Group name *' : 'Category name *' }}</label>
                    <input name="name" id="lbName" required class="lb-in" value="{{ old('name', $template->name ?? '') }}" placeholder="{{ $isBg ? 'Pastel' : 'Love' }}">
                </div>
                @if ($isBg)
                    <div>
                        <label class="lb-lbl">Add a colour</label>
                        <div class="flex gap-2">
                            <input type="color" id="lbPick" value="#f9d2d2" class="h-10 w-14 rounded-lg border border-surface-200 cursor-pointer">
                            <button type="button" id="lbAdd" class="px-4 text-sm font-semibold rounded-xl border border-surface-200 hover:bg-surface-50">Add</button>
                        </div>
                    </div>
                @endif
                <div>
                    <label class="lb-lbl">{{ $isBg ? 'Colours (hex, one per line) *' : 'Emojis (separated by spaces) *' }}</label>
                    <textarea name="items" id="lbItems" rows="{{ $isBg ? 10 : 5 }}" required
                        class="lb-in {{ $isBg ? 'font-mono' : 'text-2xl leading-10' }}">{{ $items }}</textarea>
                </div>
                <div>
                    <label class="lb-lbl">Sort order</label>
                    <input type="number" name="sort_order" min="0" class="lb-in" value="{{ old('sort_order', $template->sort_order ?? 0) }}">
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-surface-800">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))> Active (visible in card editor)
                </label>
            </div>
        </div>
    </form>

    <script>
        (function () {
            const IS_BG = @json($isBg);
            const ta = document.getElementById('lbItems'), pv = document.getElementById('lbPreview'),
                name = document.getElementById('lbName'), card = document.getElementById('lbCard');
            const parse = () => ta.value.split(/[\s,]+/u).map(s => s.trim()).filter(Boolean)
                .map(v => IS_BG ? (v[0] === '#' ? v : '#' + v).toLowerCase() : v);
            const okHex = v => /^#([0-9a-f]{3}|[0-9a-f]{6})$/.test(v);
            function render() {
                const items = parse();
                document.getElementById('lbTitle').textContent = name.value || 'Untitled';
                document.getElementById('lbCount').textContent = items.length + (IS_BG ? ' colours' : ' emojis');
                pv.innerHTML = '';
                items.forEach((v, i) => {
                    if (IS_BG) {
                        const b = document.createElement('button');
                        b.type = 'button'; b.title = v + (okHex(v) ? '' : ' (not a valid colour, will be skipped)');
                        b.style.background = okHex(v) ? v : 'repeating-linear-gradient(45deg,#fee2e2,#fee2e2 4px,#fff 4px,#fff 8px)';
                        b.onmouseenter = () => { if (okHex(v)) card.style.background = v; };
                        b.onclick = () => { const a = parse(); a.splice(i, 1); ta.value = a.join('\n'); render(); };
                        pv.appendChild(b);
                    } else {
                        const s = document.createElement('span'); s.textContent = v; pv.appendChild(s);
                    }
                });
                if (IS_BG && items[0] && okHex(items[0]) && !card.dataset.touched) card.style.background = items[0];
            }
            if (IS_BG) document.getElementById('lbAdd').onclick = () => {
                const v = document.getElementById('lbPick').value.toLowerCase();
                const a = parse(); if (!a.includes(v)) a.push(v);
                ta.value = a.join('\n'); render();
            };
            ta.addEventListener('input', render); name.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
