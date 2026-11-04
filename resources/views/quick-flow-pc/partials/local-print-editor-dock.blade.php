{{-- Local-print editor left dock (own-design sizes + business card; replaces the
     generic Page 1/2… dock in quick-flow-pc.customize). One live thumbnail per
     page (kept in sync by partials/local-print-editor-ui), then Templates and
     Layers. Keeps the ids the editor JS relies on (templates-dock-trigger,
     layers-dock-trigger, left-dock-divider). Expects $lpUi. --}}
<div class="cust-left-dock lp-dock absolute left-1 lg:left-4 z-30" role="toolbar" aria-label="Pages and tools">
    @foreach ($lpUi['pages'] as $key => $label)
        <button type="button" class="lp-page-btn {{ $loop->first ? 'on' : '' }}" data-lp-page="{{ $key }}"
            aria-pressed="{{ $loop->first ? 'true' : 'false' }}" onclick="customizer.switchCanvas('{{ $key }}')"
            title="Edit the {{ strtolower($label) }}">
            <span class="lp-page-thumb" aria-hidden="true"><img data-lp-thumb="{{ $key }}" alt="" hidden></span>
            <span class="lp-page-label">{{ $label }}</span>
        </button>
    @endforeach

    <div id="left-dock-divider" class="lp-dock-div" role="separator"></div>

    <button type="button" id="templates-dock-trigger" class="lp-tool" onclick="toggleTemplatesDrawer()" title="Ready-Made Templates">
        <i data-lucide="layout-template" class="w-5 h-5" aria-hidden="true"></i>
        <span>Templates</span>
    </button>
    <button type="button" id="layers-dock-trigger" class="lp-tool" onclick="toggleLayersDrawer()" title="Layers Panel">
        <i data-lucide="layers" class="w-5 h-5" aria-hidden="true"></i>
        <span>Layers</span>
    </button>
</div>

<style>
    /* Docks sit at the top, level with the toolbar (not vertically centred). */
    .cust-workspace-inner:has(.lp-dock) { align-items: flex-start !important }
    .lp-dock, .lp-rdock { top: 0 !important; transform: none !important }
    .lp-dock { display: flex; flex-direction: column; gap: 10px; padding: 10px; border-radius: 16px; background: #FFFFFF; border: 1px solid #E2E7E0; box-shadow: 0 1px 3px rgba(23, 32, 26, .06); max-height: calc(100vh - 160px); overflow-y: auto }
    .lp-page-btn { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 6px; border-radius: 12px; border: 2px solid transparent; background: transparent; cursor: pointer; color: #17201A; font-family: inherit }
    .lp-page-btn:hover { background: #F4F6F3 }
    .lp-page-btn.on { border-color: #2F7A3B; background: #F0F7EF }
    .lp-page-btn:focus-visible, .lp-tool:focus-visible { outline: 2px solid #2F7A3B; outline-offset: 2px }
    .lp-page-thumb { display: block; width: {{ $lpUi['thumb']['w'] }}px; height: {{ $lpUi['thumb']['h'] }}px; border-radius: 3px; overflow: hidden; background: #FFFFFF; box-shadow: 0 1px 3px rgba(23, 32, 26, .2) }
    .lp-page-thumb img { display: block; width: 100%; height: 100%; object-fit: cover }
    .lp-page-label { font-size: 12px; font-weight: 700; line-height: 1; white-space: nowrap }
    .lp-dock-div { height: 1px; background: #EDF0EB; margin: 2px 4px; flex-shrink: 0 }
    .lp-tool { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 8px; border: none; border-radius: 12px; background: transparent; cursor: pointer; font-family: inherit; font-size: 12px; font-weight: 600; color: #17201A }
    .lp-tool:hover { background: #F0F3EE }
</style>
