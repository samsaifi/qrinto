{{-- Local-print editor canvas frame, wrapped around #canvas-container in
     quick-flow-pc.customize (own-design sizes + business card). Included twice:
       part=open  - opens the column + frame and adds the guide labels
       part=close - adds the trim / safe-area overlay, closes the frame and adds
                    the "Front · 7 × 10 in   [Show …]" row.
     The overlay is plain DOM above the Fabric canvases (pointer-events: none),
     so it never ends up in the exported artwork. Zoom scales the whole frame
     (see local-print-editor-ui), keeping the guides on the page.
     Expects $lpUi (safe = 1/8 in margin as % of the page width / height). --}}
@if ($part === 'open')
<div class="lp-canvas-col">
    <div class="lp-canvas-frame" id="lp-canvas-frame">
        <span class="lp-guide-label lp-guide-trim" aria-hidden="true">Trim line</span>
        <span class="lp-guide-label lp-guide-safe" aria-hidden="true">Keep text inside the dashed line</span>
@else
        <div class="lp-guides" aria-hidden="true">
            <span class="lp-safe"></span>
        </div>
        @if ($lpUi['businessCard'])
            {{-- Business card file upload (partials/business-card-editor): shown on an empty side. --}}
            <label id="bc-dropzone" class="bc-dropzone" hidden>
                <span class="bc-dz-ico" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5M5 20h14" /></svg></span>
                <span class="bc-dz-title">Drop your business card here</span>
                <span class="bc-dz-sub">A 2-page PDF (front, then back), or a PNG/JPG for this side - two images fill the front and back. 3.5 × 2 in fits exactly.</span>
                <span class="bc-dz-btn">Choose a file</span>
                <span class="bc-dz-sub">or start from scratch with Text, Photo and Shapes on the right</span>
                <input type="file" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple class="bc-file-input" data-bc-pdf-input>
            </label>
            <div id="bc-reading" class="bc-reading" role="status" hidden><span class="bc-spin" aria-hidden="true"></span>Reading your PDF…</div>
        @endif
    </div>
    <div class="lp-side-row">
        <span id="lp-page-caption">{{ reset($lpUi['pages']) }} · {{ $lpUi['sizeLabel'] }}</span>
        @if (count($lpUi['pages']) > 1)
            <button type="button" id="lp-show-next" class="lp-show-next" onclick="customizer.lpShowNextPage()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4L3 8l4 4M3 8h14M17 20l4-4-4-4M21 16H7" /></svg>
                <span>Show {{ strtolower(array_values($lpUi['pages'])[1]) }}</span>
            </button>
        @endif
        @if ($lpUi['businessCard'])
            <label id="bc-replace" class="lp-show-next bc-file-btn" hidden>Replace file
                <input type="file" accept=".pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg" multiple class="bc-file-input" data-bc-pdf-input>
            </label>
            <button type="button" id="bc-remove" class="lp-show-next bc-remove" onclick="customizer.bcRemoveUpload()" hidden>Remove</button>
        @endif
    </div>
    @if ($lpUi['businessCard'])
        <p id="bc-upload-msg" class="bc-upload-msg" role="status" hidden></p>
    @endif
</div>

<style>
    .lp-canvas-col { display: flex; flex-direction: column; align-items: center; gap: 14px; max-width: 100% }
    .lp-canvas-frame { position: relative; max-width: 100%; margin-top: 26px; transition: transform .2s ease }
    .lp-canvas-frame #canvas-container { border-radius: 0 !important; box-shadow: 0 16px 40px rgba(23, 32, 26, .18) !important }
    .lp-guides { position: absolute; inset: 0; z-index: 15; pointer-events: none; outline: 2px solid #D64535; outline-offset: -1px }
    /* 1/8 in safe margin. A white dashed line beside a dark one stays visible on
       light and dark artwork. */
    .lp-safe { position: absolute; left: {{ $lpUi['safe']['x'] }}%; right: {{ $lpUi['safe']['x'] }}%; top: {{ $lpUi['safe']['y'] }}%; bottom: {{ $lpUi['safe']['y'] }}%;
        border: 1.5px dashed rgba(255, 255, 255, .9); outline: 1px dashed rgba(23, 32, 26, .45) }
    .lp-guide-label { position: absolute; top: -26px; font-size: 12px; font-weight: 600; line-height: 20px; white-space: nowrap; font-family: 'Figtree', system-ui, sans-serif }
    .lp-guide-trim { left: 0; color: #8A2018 }
    .lp-guide-safe { right: 0; color: #4C5A4E }
    .lp-canvas-frame.guides-off .lp-guides, .lp-canvas-frame.guides-off .lp-guide-label { display: none }
    .lp-side-row { display: flex; align-items: center; gap: 12px; font-size: 13px; color: #4C5A4E; font-family: 'Figtree', system-ui, sans-serif }
    .lp-show-next { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 12px; border-radius: 10px; border: 1px solid #D5DCD2; background: #FFFFFF; font-size: 13px; font-weight: 700; color: #17201A; cursor: pointer; font-family: inherit }
    .lp-show-next:hover { background: #F4F6F3 }
    .lp-show-next:focus-visible { outline: 2px solid #2F7A3B; outline-offset: 2px }
    /* Business card PDF upload */
    .bc-dropzone { position: absolute; inset: 14px; z-index: 16; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 16px; border: 2px dashed #A9B3A6; border-radius: 10px; background: rgba(255, 255, 255, .92); text-align: center; cursor: pointer; font-family: 'Figtree', system-ui, sans-serif; color: #17201A; transition: background .15s ease, border-color .15s ease }
    .bc-dropzone[hidden], .bc-reading[hidden], .bc-file-btn[hidden], .bc-remove[hidden], .bc-upload-msg[hidden] { display: none !important }
    .bc-dropzone:hover, .bc-dropzone.is-over { background: rgba(240, 247, 239, .96); border-color: #1F6B2E }
    .bc-dropzone:focus-within { outline: 3px solid #9FCB98; outline-offset: -6px }
    .bc-dz-ico { width: 52px; height: 52px; border-radius: 50%; background: #E3F0E1; color: #1F6B2E; display: flex; align-items: center; justify-content: center }
    .bc-dz-title { font-size: 18px; font-weight: 800 }
    .bc-dz-sub { font-size: 13px; color: #4C5A4E; max-width: 420px }
    .bc-dz-btn { display: inline-flex; align-items: center; height: 40px; padding: 0 16px; border-radius: 10px; background: #1F6B2E; color: #FFFFFF; font-size: 14px; font-weight: 700 }
    .bc-file-input { position: absolute; width: 1px; height: 1px; opacity: 0; overflow: hidden }
    .bc-reading { position: absolute; inset: 0; z-index: 17; display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(255, 255, 255, .85); font-size: 14px; font-weight: 700; font-family: 'Figtree', system-ui, sans-serif }
    .bc-spin { width: 20px; height: 20px; border-radius: 50%; border: 3px solid #C7CFC4; border-top-color: #1F6B2E; box-sizing: border-box; animation: bcSpin 1s linear infinite }
    @keyframes bcSpin { to { transform: rotate(360deg) } }
    .bc-file-btn { position: relative }
    .bc-file-btn:focus-within { outline: 2px solid #2F7A3B; outline-offset: 2px }
    .bc-remove { color: #8A2018 }
    .bc-upload-msg { margin: 0; max-width: 630px; padding: 10px 14px; border-radius: 12px; font-size: 13px; line-height: 1.45; font-family: 'Figtree', system-ui, sans-serif }
    .bc-upload-msg.ok { background: #E3F0E1; color: #1D4D22 }
    .bc-upload-msg.err { background: #FDECEA; color: #8A2018 }
    .bc-upload-msg.warn { background: #FFF6E5; color: #6B4A00 }
    /* Workspace: light dotted board, as in the design. */
    .cust-workspace:has(.lp-canvas-frame) { background-color: #F4F6F3 !important; background-image: radial-gradient(#D6DDD3 1px, transparent 1px) !important; background-size: 18px 18px !important }
</style>
@endif
