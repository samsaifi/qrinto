{{-- Local-print editor toolbar (own-design sizes + business card; replaces the
     generic studio toolbar in quick-flow-pc.customize): Undo / Redo, the
     "Trim and safe area" guide toggle, zoom and the print-quality badge.
     The selection-only tools (delete, crop, mask, move image, group/ungroup)
     keep their ids so the editor's updateUI() still shows them when relevant;
     everything else lives in the right-click menu. --}}
<div class="cust-toolbar lp-toolbar z-20" role="toolbar" aria-label="Editor">
    <button type="button" class="lp-tb-icon" onclick="customizer.undo()" title="Undo (Ctrl+Z)" aria-label="Undo">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14L4 9l5-5" /><path d="M4 9h11a5 5 0 0 1 0 10h-3" /></svg>
    </button>
    <button type="button" class="lp-tb-icon" onclick="customizer.redo()" title="Redo (Ctrl+Y)" aria-label="Redo">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 14l5-5-5-5" /><path d="M20 9H9a5 5 0 0 0 0 10h3" /></svg>
    </button>

    <span class="lp-tb-sep" aria-hidden="true"></span>

    <button type="button" id="lp-guides-toggle" class="lp-tb-toggle on" aria-pressed="true"
        onclick="customizer.lpToggleGuides()" title="Show the trim line and the safe area">Trim and safe area</button>

    {{-- Selection-only tools (hidden until something is selected). --}}
    <span class="lp-tb-ctx">
        <button type="button" id="remove-btn" onclick="customizer.handleRemove()" class="hidden lp-tb-icon danger" title="Remove Selected">
            <i data-lucide="trash-2" class="w-4 h-4" aria-hidden="true"></i>
            <span id="remove-btn-text" class="sr-only">Remove</span>
        </button>
        <button type="button" id="reposition-top-btn" onclick="customizer.enterImageRepositionMode()" class="hidden lp-tb-chip" title="Reposition Image Inside Shape">
            <i data-lucide="move" class="w-4 h-4" aria-hidden="true"></i>Move image</button>
        <button type="button" id="group-top-btn" onclick="customizer.groupSelected()" class="hidden lp-tb-chip" title="Group Selected Objects (Ctrl+G)">
            <i data-lucide="folder-plus" class="w-4 h-4" aria-hidden="true"></i>Group</button>
        <button type="button" id="ungroup-top-btn" onclick="customizer.ungroupSelected()" class="hidden lp-tb-chip" title="Ungroup Objects (Ctrl+Shift+G)">
            <i data-lucide="folder-minus" class="w-4 h-4" aria-hidden="true"></i>Ungroup</button>
        <button type="button" id="crop-top-btn" onclick="customizer.enterCropMode()" class="hidden lp-tb-chip" title="Crop Image">
            <i data-lucide="crop" class="w-4 h-4" aria-hidden="true"></i>Crop</button>
        <button type="button" id="mask-top-btn" onclick="toggleShapeMaskDrawer()" class="hidden lp-tb-chip" title="Mask Image into Shape">
            <i data-lucide="shapes" class="w-4 h-4" aria-hidden="true"></i>Shape mask</button>
    </span>

    <span class="lp-tb-sep" aria-hidden="true"></span>

    <select class="lp-tb-zoom" onchange="customizer.setZoom(this.value)" title="Canvas Zoom Level" aria-label="Zoom">
        <option value="1">100%</option>
        <option value="1.25">125%</option>
        <option value="1.5">150%</option>
        <option value="2">200%</option>
    </select>

    {{-- Business cards: pick the resolution each side is saved at (300 / 600 / 1200 DPI).
         business-card-editor exports and saves at the selected value. --}}
    @if ($lpUi['businessCard'])
        <label id="preflight-badge" class="lp-tb-badge lp-tb-dpi" title="Each side is saved at this resolution for print">
            <span class="dot" aria-hidden="true"></span>
            <select id="bc-dpi" aria-label="Save resolution">
                @foreach ($businessCard['dpis'] ?? [300, 600, 1200] as $dpi)
                    <option value="{{ $dpi }}" @selected($dpi === ($businessCard['dpi'] ?? 300))>{{ $dpi }} DPI · print ready</option>
                @endforeach
            </select>
        </label>
    @endif
</div>

<style>
    .lp-toolbar { display: flex; align-items: center; gap: 6px; padding: 6px; border-radius: 14px; background: #FFFFFF; border: 1px solid #E2E7E0; flex-wrap: wrap; justify-content: center; max-width: 100%; font-family: 'Figtree', system-ui, sans-serif; color: #17201A }
    .lp-tb-icon { width: 36px; height: 36px; border: none; border-radius: 8px; background: transparent; cursor: pointer; color: #17201A; display: inline-flex; align-items: center; justify-content: center }
    .lp-tb-icon:hover, .lp-tb-chip:hover { background: #F0F3EE }
    .lp-tb-icon.danger { color: #C0392B }
    .lp-tb-icon.hidden { display: none }
    .lp-tb-sep { width: 1px; height: 22px; background: #E2E7E0 }
    .lp-tb-toggle { height: 36px; padding: 0 12px; border: none; border-radius: 8px; background: transparent; font-size: 13px; font-weight: 600; color: #17201A; cursor: pointer; font-family: inherit }
    .lp-tb-toggle.on { background: #E3F0E1 }
    .lp-tb-ctx { display: contents }
    .lp-tb-chip { height: 36px; padding: 0 10px; border: none; border-radius: 8px; background: transparent; font-size: 13px; font-weight: 600; color: #17201A; cursor: pointer; align-items: center; gap: 6px; font-family: inherit }
    .lp-tb-chip:not(.hidden) { display: inline-flex }
    .lp-tb-zoom { height: 36px; padding: 0 6px; border: none; border-radius: 8px; background: transparent; font-size: 13px; font-weight: 600; color: #17201A; cursor: pointer; font-family: inherit; -webkit-appearance: none; appearance: none; text-align: center }
    .lp-tb-zoom:hover { background: #F0F3EE }
    .lp-tb-badge { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 10px; border-radius: 999px; background: #E3F0E1; color: #1D4D22; font-size: 12px; font-weight: 700; white-space: nowrap }
    .lp-tb-badge .dot { width: 7px; height: 7px; border-radius: 50%; background: #2F9E44 }
    .lp-tb-dpi { cursor: pointer; padding-right: 6px }
    .lp-tb-dpi:hover { background: #D5E8D2 }
    .lp-tb-dpi:focus-within { outline: 3px solid #9FCB98; outline-offset: 1px }
    .lp-tb-dpi select { appearance: none; -webkit-appearance: none; border: none; background: transparent url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%231D4D22' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 2px center; padding: 0 16px 0 0; font: inherit; color: inherit; cursor: pointer; outline: none }
    .lp-toolbar button:focus-visible, .lp-toolbar select:focus-visible { outline: 2px solid #2F7A3B; outline-offset: 2px }
</style>
