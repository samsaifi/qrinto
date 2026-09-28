 <div class="printo-ref">
     <main class="mx-auto max-w-[1060px] px-6 pt-9  ">
         <a class="back inline-block text-sm font-medium no-underline" href="#">← Back</a>
         <h1 class="mt-3.5 mb-1.5 text-[30px] leading-[1.15] tracking-[-0.015em] font-bold">Upload a Media</h1>
         <p class="lede mt-0 mb-7 max-w-[60ch] text-[color:var(--muted)]">Tell us what you're printing and we'll show you
             exactly how to set up your file, so every side lands the right way up.</p>

         {{-- choice rows --}}
         <section class="choose" aria-label="Print options">
             <div>
                 <h2 class="mb-3 text-[17px] tracking-[-0.005em]" id="lbl-type">What are you printing?</h2>
                 <div class="tiles" role="radiogroup" aria-labelledby="lbl-type">
                     <label class="tile">
                         <input type="radio" name="kind" value="single" checked>
                         <svg viewBox="0 0 40 40" aria-hidden="true">
                             <rect x="9" y="7" width="22" height="28" rx="2" fill="var(--surface)"
                                 stroke="currentColor" stroke-width="2" />
                             <path d="M13 27l5-6 4 4 3-3 3 5" fill="none" stroke="var(--green)" stroke-width="1.8"
                                 stroke-linejoin="round" />
                         </svg>
                         <span><span class="t">Single-sided</span>
                             <br><span class="s">Photo, poster, one
                                 side</span></span>
                     </label>

                     <label class="tile">
                         <input type="radio" name="kind" value="flat">
                         <svg viewBox="0 0 40 40" aria-hidden="true">
                             <rect x="11" y="6" width="20" height="26" rx="2" fill="var(--green-soft)"
                                 stroke="currentColor" stroke-width="1.6" opacity=".6" />
                             <rect x="7" y="10" width="20" height="26" rx="2" fill="var(--surface)"
                                 stroke="currentColor" stroke-width="2" />
                         </svg>
                         <span><span class="t">Flat card</span>
                             <br><span class="s">Printed front and
                                 back</span></span>
                     </label>
                     <label class="tile">
                         <input type="radio" name="kind" value="folded">
                         <svg viewBox="0 0 40 40" aria-hidden="true">
                             <path d="M8 32 L20 8 L32 32" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linejoin="round" />
                             <path d="M20 8 L26 30" stroke="var(--green)" stroke-width="2" />
                             <path d="M4 33h32" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                 opacity=".4" />
                         </svg>
                         <span><span class="t">Folded card</span>
                             <br><span class="s">4 sides, folds in
                                 half</span></span>
                     </label>
                 </div>
             </div>
             <div>
                 <h2 class="mb-3 text-[17px] tracking-[-0.005em]" id="lbl-or">Card shape</h2>
                 <div class="seg" id="seg" role="radiogroup" aria-labelledby="lbl-or">
                     <label>
                         <input type="radio" name="orient" value="portrait" checked>
                         <svg viewBox="0 0 14 18" aria-hidden="true">
                             <rect x="1" y="1" width="12" height="16" rx="1.5" fill="none"
                                 stroke="currentColor" stroke-width="1.6" />
                         </svg>Portrait</label>
                     <label>
                         <input type="radio" name="orient" value="landscape">
                         <svg viewBox="0 0 18 14" style="width:18px;height:14px" aria-hidden="true">
                             <rect x="1" y="1" width="16" height="12" rx="1.5" fill="none"
                                 stroke="currentColor" stroke-width="1.6" />
                         </svg>Landscape</label>
                 </div>
             </div>
         </section>

         {{-- guide --}}
         <section class="guide  " id="guide" aria-live="polite">
             <div class="guide-head">
                 <div>
                     <h2 id="g-title">How to set up a folded portrait card</h2>
                     <p id="g-sub">Your PDF should match this sample. Tap any panel to see where it ends up.</p>
                 </div>
                 <button class="btn" id="dl" type="button">
                     <svg viewBox="0 0 16 16" aria-hidden="true">
                         <path d="M8 2v8m0 0l-3.5-3.5M8 10l3.5-3.5M2.5 13.5h11" fill="none" stroke="currentColor"
                             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                     </svg><span>Download blank template</span></button>
             </div>
             <div class="guide-body">
                 <div class="col">
                     <h3>Your PDF</h3>
                     <p class="hint">Arrows show the top of your artwork. Rotate each design to match.</p>
                     <div class="sheets" id="sheets"></div>
                 </div>
                 <div class="col">
                     <h3>Your printed card</h3>
                     <p class="hint" id="p-hint">Drag the slider to open it, or jump to a side.</p>
                     <div class="stage" id="stage"></div>
                     <div class="controls">
                         <div class="chips" id="chips"></div>
                         <label class="slider" id="slider"><span>Closed</span>
                             <input type="range" id="open" min="0" max="180" value="0"
                                 aria-label="Open the card"><span>Open</span></label>
                     </div>
                 </div>
             </div>
             <div class="rules" id="rules"></div>
         </section>

         {{-- dropzone --}}
         <label class="drop hidden" id="drop">
             <input type="file" id="file"
                 accept="application/pdf,image/jpeg,image/png,image/webp,image/tiff">
             <svg viewBox="0 0 24 24" aria-hidden="true">
                 <path d="M12 15V4m0 0L7.5 8.5M12 4l4.5 4.5M4 15v3.5A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5V15"
                     fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                     stroke-linejoin="round" />
             </svg>
             <div class="big" id="d-big">Drop your PDF here, or click to choose</div>
             <div class="small" id="d-small">PDF, JPEG, PNG, WebP, or TIFF – up to 200 MB</div>
             <div class="expect" id="d-expect"></div>
         </label>
     </main>

     <div class="toast" id="toast" role="status"></div>

     <style>
         /* Scoped card-guide styles — vars live on .printo-ref so nothing leaks. */
         .printo-ref {
             --bg: #F7F8F5;
             --surface: #FFFFFF;
             --ink: #1C2620;
             --muted: #5E6A63;
             --line: #D9DED6;
             --line-strong: #B9C2B6;
             --green: #58B03C;
             --green-deep: #2E6A22;
             --green-soft: #E9F5E2;
             --warn: #9A5B00;
             --warn-soft: #FFF3DC;
             --err: #B3261E;
             --err-soft: #FDE8E6;
             --p-front: #DDEFD3;
             --p-back: #E3E7EE;
             --p-il: #FCEBD5;
             --p-ir: #F6DDE3;
             --paper: #FFFFFF;
             --panel-ink: #1F2A24;
             --shadow: 0 1px 2px rgba(28, 38, 32, .06), 0 8px 24px rgba(28, 38, 32, .08);
             color: var(--ink);
             font-family: "Schibsted Grotesk", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
         }

         @media (prefers-color-scheme:dark) {
             :root:not([data-theme="light"]) .printo-ref {
                 --bg: #141A16;
                 --surface: #1C241F;
                 --ink: #E8EEE9;
                 --muted: #A1ADA5;
                 --line: #2E3A32;
                 --line-strong: #46554B;
                 --green: #6CC64E;
                 --green-deep: #9BDC84;
                 --green-soft: #23331F;
                 --warn: #F2B75B;
                 --warn-soft: #352A14;
                 --err: #FF8A80;
                 --err-soft: #3A1E1C;
                 --shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 8px 24px rgba(0, 0, 0, .35);
             }
         }

         :root[data-theme="dark"] .printo-ref {
             --bg: #141A16;
             --surface: #1C241F;
             --ink: #E8EEE9;
             --muted: #A1ADA5;
             --line: #2E3A32;
             --line-strong: #46554B;
             --green: #6CC64E;
             --green-deep: #9BDC84;
             --green-soft: #23331F;
             --warn: #F2B75B;
             --warn-soft: #352A14;
             --err: #FF8A80;
             --err-soft: #3A1E1C;
             --shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 8px 24px rgba(0, 0, 0, .35);
         }

         .printo-ref img,
         .printo-ref svg,
         .printo-ref canvas {
             max-width: 100%;
         }

         .printo-ref button,
         .printo-ref input {
             font: inherit;
             color: inherit;
         }

         .printo-ref :focus-visible {
             outline: 3px solid var(--green);
             outline-offset: 2px;
             border-radius: 6px;
         }

         .printo-ref .back {
             color: var(--muted);
         }

         .printo-ref .back:hover {
             color: var(--ink);
         }

         /* choice rows */
         .printo-ref .choose {
             display: grid;
             grid-template-columns: 1fr auto;
             gap: 28px;
             align-items: end;
             margin-bottom: 24px;
         }

         .printo-ref .tiles {
             display: grid;
             grid-template-columns: repeat(3, minmax(0, 1fr));
             gap: 10px;
         }

         .printo-ref .tile {
             position: relative;
             display: flex;
             gap: 12px;
             align-items: center;
             padding: 12px 14px;
             background: var(--surface);
             border: 1.5px solid var(--line);
             border-radius: 12px;
             cursor: pointer;
             transition: border-color .15s, background .15s;
         }

         .printo-ref .tile:hover {
             border-color: var(--line-strong);
         }

         .printo-ref .tile input {
             position: absolute;
             opacity: 0;
             pointer-events: none;
         }

         .printo-ref .tile svg {
             flex: none;
             width: 40px;
             height: 40px;
         }

         .printo-ref .tile .t {
             font-weight: 600;
             font-size: 15px;
             line-height: 1.25;
         }

         .printo-ref .tile .s {
             font-size: 13px;
             color: var(--muted);
             line-height: 1.3;
         }

         .printo-ref .tile:has(input:checked) {
             border-color: var(--green);
             background: var(--green-soft);
         }

         .printo-ref .tile:has(input:focus-visible) {
             outline: 3px solid var(--green);
             outline-offset: 2px;
         }

         .printo-ref .seg {
             display: inline-flex;
             background: var(--surface);
             border: 1.5px solid var(--line);
             border-radius: 12px;
             padding: 4px;
             gap: 4px;
         }

         .printo-ref .seg label {
             display: flex;
             align-items: center;
             gap: 8px;
             padding: 9px 14px;
             border-radius: 9px;
             cursor: pointer;
             font-weight: 600;
             font-size: 14px;
             color: var(--muted);
         }

         .printo-ref .seg input {
             position: absolute;
             opacity: 0;
             pointer-events: none;
         }

         .printo-ref .seg label:has(input:checked) {
             background: var(--green-soft);
             color: var(--ink);
         }

         .printo-ref .seg label:has(input:focus-visible) {
             outline: 3px solid var(--green);
         }

         .printo-ref .seg.off {
             opacity: .45;
             pointer-events: none;
         }

         .printo-ref .seg svg {
             width: 14px;
             height: 18px;
         }

         /* guide */
         .printo-ref .guide {
             background: var(--surface);
             border: 1px solid var(--line);
             border-radius: 16px;
             box-shadow: var(--shadow);
             overflow: hidden;
             margin-bottom: 22px;
         }

         .printo-ref .guide-head {
             display: flex;
             justify-content: space-between;
             align-items: center;
             gap: 16px;
             padding: 18px 22px;
             border-bottom: 1px solid var(--line);
             flex-wrap: wrap;
         }

         .printo-ref .guide-head h2 {
             margin: 0;
             font-size: 17px;
         }

         .printo-ref .guide-head p {
             margin: 2px 0 0;
             color: var(--muted);
             font-size: 14px;
         }

         .printo-ref .btn {
             display: inline-flex;
             align-items: center;
             gap: 8px;
             border-radius: 10px;
             padding: 10px 16px;
             font-weight: 600;
             font-size: 14px;
             cursor: pointer;
             border: 1.5px solid var(--line-strong);
             background: var(--surface);
             transition: background .15s, border-color .15s;
         }

         .printo-ref .btn:hover {
             border-color: var(--ink);
         }

         .printo-ref .btn.primary {
             background: var(--green);
             border-color: var(--green);
             color: #fff;
         }

         .printo-ref .btn.primary:hover {
             background: var(--green-deep);
             border-color: var(--green-deep);
         }

         @media (prefers-color-scheme:dark) {
             :root:not([data-theme="light"]) .printo-ref .btn.primary {
                 color: #0E160F;
             }
         }

         :root[data-theme="dark"] .printo-ref .btn.primary {
             color: #0E160F;
         }

         .printo-ref .btn svg {
             width: 16px;
             height: 16px;
         }

         .printo-ref .btn[disabled] {
             opacity: .5;
             cursor: not-allowed;
         }

         .printo-ref .guide-body {
             display: grid;
             grid-template-columns: 1.15fr 1fr;
         }

         .printo-ref .col {
             padding: 22px;
         }

         .printo-ref .col+.col {
             border-left: 1px solid var(--line);
             background: color-mix(in srgb, var(--bg) 55%, var(--surface));
         }

         .printo-ref .col h3 {
             font-size: 14px;
             font-weight: 600;
             margin: 0 0 4px;
         }

         .printo-ref .col .hint {
             font-size: 13px;
             color: var(--muted);
             margin: 0 0 16px;
         }

         .printo-ref .sheets {
             display: flex;
             gap: 18px;
             justify-content: center;
             align-items: flex-start;
         }

         .printo-ref .sheet {
             flex: 1;
             max-width: 240px;
             margin: 0;
         }

         .printo-ref .sheet svg {
             display: block;
             width: 100%;
             height: auto;
             border-radius: 3px;
             box-shadow: 0 0 0 1px var(--line), 0 6px 16px rgba(28, 38, 32, .10);
         }

         .printo-ref .sheet figcaption {
             margin-top: 10px;
             font-size: 13px;
             line-height: 1.35;
         }

         .printo-ref .sheet figcaption b {
             display: block;
             font-size: 14px;
         }

         .printo-ref .sheet figcaption span {
             color: var(--muted);
         }

         .printo-ref .pnl {
             cursor: pointer;
         }

         .printo-ref .pnl .hlr {
             fill: none;
             stroke: var(--green);
             stroke-width: 0;
             transition: stroke-width .15s;
         }

         .printo-ref .pnl.hl .hlr {
             stroke-width: 10;
         }

         /* 3D stage */
         .printo-ref .stage {
             height: 330px;
             display: flex;
             align-items: center;
             justify-content: center;
             perspective: 1500px;
             margin: 6px 0 12px;
         }

         .printo-ref .card {
             position: relative;
             transform-style: preserve-3d;
             transition: transform .75s cubic-bezier(.3, .7, .2, 1);
         }

         .printo-ref .leaf {
             position: absolute;
             inset: 0;
             transform-style: preserve-3d;
             transition: transform .75s cubic-bezier(.3, .7, .2, 1);
         }

         .printo-ref .face {
             position: absolute;
             inset: 0;
             backface-visibility: hidden;
             -webkit-backface-visibility: hidden;
             border-radius: 2px;
             overflow: hidden;
             box-shadow: 0 0 0 1px rgba(28, 38, 32, .12), 0 10px 26px rgba(28, 38, 32, .16);
             transition: box-shadow .15s;
         }

         .printo-ref .face svg {
             display: block;
             width: 100%;
             height: 100%;
         }

         .printo-ref .face.hl {
             box-shadow: 0 0 0 3px var(--green), 0 10px 26px rgba(28, 38, 32, .16);
         }

         .printo-ref .axis-Y .f-back {
             transform: rotateY(180deg);
         }

         .printo-ref .axis-X .f-back {
             transform: rotateX(180deg);
         }

         .printo-ref .axis-Y .cover {
             transform-origin: left center;
         }

         .printo-ref .axis-X .cover {
             transform-origin: center top;
         }

         .printo-ref .cover .face {
             transform: translateZ(2px);
         }

         .printo-ref .axis-Y .cover .f-back {
             transform: translateZ(2px) rotateY(180deg);
         }

         .printo-ref .axis-X .cover .f-back {
             transform: translateZ(2px) rotateX(180deg);
         }

         .printo-ref .controls {
             display: flex;
             flex-direction: column;
             gap: 12px;
             align-items: center;
         }

         .printo-ref .chips {
             display: inline-flex;
             gap: 6px;
             flex-wrap: wrap;
             justify-content: center;
         }

         .printo-ref .chip {
             border: 1.5px solid var(--line-strong);
             background: var(--surface);
             border-radius: 999px;
             padding: 6px 14px;
             font-size: 13px;
             font-weight: 600;
             cursor: pointer;
         }

         .printo-ref .chip[aria-pressed="true"] {
             background: var(--ink);
             color: var(--surface);
             border-color: var(--ink);
         }

         .printo-ref .slider {
             display: flex;
             align-items: center;
             gap: 10px;
             font-size: 13px;
             color: var(--muted);
             width: 100%;
             max-width: 300px;
         }

         .printo-ref .slider input {
             flex: 1;
             accent-color: var(--green);
         }

         /* rules */
         .printo-ref .rules {
             display: grid;
             grid-template-columns: repeat(4, minmax(0, 1fr));
             gap: 0;
             border-top: 1px solid var(--line);
         }

         .printo-ref .rule {
             padding: 16px 20px;
             font-size: 13.5px;
             line-height: 1.4;
             color: var(--muted);
         }

         .printo-ref .rule+.rule {
             border-left: 1px solid var(--line);
         }

         .printo-ref .rule b {
             display: block;
             color: var(--ink);
             font-size: 14px;
             margin-bottom: 2px;
         }

         /* dropzone */
         .printo-ref .drop {
             display: none;
             background: var(--surface);
             border: 2px dashed var(--line-strong);
             border-radius: 16px;
             padding: 44px 24px;
             text-align: center;
             cursor: pointer;
             transition: border-color .15s, background .15s;
         }

         .printo-ref .drop:hover,
         .printo-ref .drop.over {
             border-color: var(--green);
             background: var(--green-soft);
         }

         .printo-ref .drop input {
             position: absolute;
             width: 1px;
             height: 1px;
             opacity: 0;
         }

         .printo-ref .drop:has(input:focus-visible) {
             outline: 3px solid var(--green);
             outline-offset: 3px;
         }

         .printo-ref .drop svg {
             width: 30px;
             height: 30px;
             color: var(--muted);
         }

         .printo-ref .drop .big {
             font-weight: 600;
             font-size: 17px;
             margin: 10px 0 4px;
         }

         .printo-ref .drop .small {
             font-size: 13px;
             color: var(--muted);
         }

         .printo-ref .drop .expect {
             display: inline-block;
             margin-top: 14px;
             font-size: 13px;
             background: var(--bg);
             border: 1px solid var(--line);
             border-radius: 999px;
             padding: 5px 12px;
         }

         /* toast */
         .printo-ref .toast {
             position: fixed;
             left: 50%;
             bottom: calc(24px + env(safe-area-inset-bottom, 0px));
             transform: translateX(-50%) translateY(20px);
             opacity: 0;
             background: var(--ink);
             color: var(--surface);
             padding: 10px 16px;
             border-radius: 10px;
             font-size: 14px;
             transition: .25s;
             pointer-events: none;
             z-index: 9;
         }

         .printo-ref .toast.show {
             opacity: 1;
             transform: translateX(-50%);
         }

         .printo-ref [hidden] {
             display: none !important;
         }

         @media (max-width:860px) {
             .printo-ref .choose {
                 grid-template-columns: 1fr;
             }

             .printo-ref .guide-body {
                 grid-template-columns: 1fr;
             }

             .printo-ref .col+.col {
                 border-left: 0;
                 border-top: 1px solid var(--line);
             }

             .printo-ref .rules {
                 grid-template-columns: 1fr 1fr;
             }

             .printo-ref .rule:nth-child(3) {
                 border-left: 0;
             }

             .printo-ref .rule:nth-child(n+3) {
                 border-top: 1px solid var(--line);
             }
         }

         @media (max-width:560px) {
             .printo-ref .tiles {
                 grid-template-columns: 1fr;
             }

             .printo-ref .rules {
                 grid-template-columns: 1fr;
             }

             .printo-ref .rule+.rule {
                 border-left: 0;
                 border-top: 1px solid var(--line);
             }

             .printo-ref .stage {
                 height: 280px;
             }
         }

         @media (prefers-reduced-motion:reduce) {

             .printo-ref .card,
             .printo-ref .leaf,
             .printo-ref .tile,
             .printo-ref .toast {
                 transition: none !important;
             }
         }
     </style>

     <script>
         (function() {
             const PT_MM = 25.4 / 72;
             const PANELS = {
                 front: {
                     name: 'Front',
                     cls: 'front'
                 },
                 back: {
                     name: 'Back',
                     cls: 'back'
                 },
                 il: {
                     name: 'Inside left',
                     cls: 'il'
                 },
                 ir: {
                     name: 'Inside right',
                     cls: 'ir'
                 }
             };
             // Layouts mirror the sample PDFs: [panelKey, cardPageNumber, rotation in degrees clockwise]
             const CFG = {
                 'folded-portrait': {
                     kind: 'folded',
                     axis: 'Y',
                     sheet: [545.04, 755.28],
                     face: [377.64, 545.04],
                     pages: [{
                         t: 'PDF page 1',
                         s: 'Inside of the card',
                         p: [
                             ['il', 2, 90],
                             ['ir', 3, 90]
                         ]
                     }, {
                         t: 'PDF page 2',
                         s: 'Outside of the card',
                         p: [
                             ['front', 1, -90],
                             ['back', 4, -90]
                         ]
                     }],
                     finalMM: '133 × 192 mm',
                     fold: 'Opens like a book, fold on the left'
                 },
                 'folded-landscape': {
                     kind: 'folded',
                     axis: 'X',
                     sheet: [545.04, 755.28],
                     face: [545.04, 377.64],
                     pages: [{
                         t: 'PDF page 1',
                         s: 'Inside of the card',
                         p: [
                             ['il', 2, 0],
                             ['ir', 3, 0]
                         ]
                     }, {
                         t: 'PDF page 2',
                         s: 'Outside of the card',
                         p: [
                             ['front', 1, 180],
                             ['back', 4, 0]
                         ]
                     }],
                     finalMM: '192 × 133 mm',
                     fold: 'Opens upward, fold along the top'
                 },
                 'flat-portrait': {
                     kind: 'flat',
                     axis: 'Y',
                     sheet: [539.28, 401.04],
                     face: [401.04, 539.28],
                     pages: [{
                         t: 'PDF page 1',
                         s: 'Front of the card',
                         p: [
                             ['front', 1, -90]
                         ]
                     }, {
                         t: 'PDF page 2',
                         s: 'Back of the card',
                         p: [
                             ['back', 2, -90]
                         ]
                     }],
                     finalMM: '141 × 190 mm',
                     fold: 'Turns over side to side'
                 },
                 'flat-landscape': {
                     kind: 'flat',
                     axis: 'X',
                     sheet: [539.28, 401.04],
                     face: [539.28, 401.04],
                     pages: [{
                         t: 'PDF page 1',
                         s: 'Front of the card',
                         p: [
                             ['front', 1, 180]
                         ]
                     }, {
                         t: 'PDF page 2',
                         s: 'Back of the card',
                         p: [
                             ['back', 2, 0]
                         ]
                     }],
                     finalMM: '190 × 141 mm',
                     fold: 'Turns over top to bottom'
                 }
             };
             // rename inside panels for landscape (top/bottom instead of left/right)
             const NAME_OVERRIDE = {
                 'folded-landscape': {
                     il: 'Inside top',
                     ir: 'Inside bottom'
                 }
             };

             const root = document.querySelector('.printo-ref');
             const $ = s => root.querySelector(s);
             const state = {
                 kind: 'folded',
                 orient: 'portrait',
                 open: 0,
                 turned: false
             };
             const key = () => state.kind + '-' + state.orient;
             const pname = (k, cfgKey) => (NAME_OVERRIDE[cfgKey] || {})[k] || PANELS[k].name;
             const mm = pt => Math.round(pt * PT_MM);

             /* ---------- panel drawing (SVG, point units) ---------- */
             function panelContent(cw, ch, k, num, cfgKey) {
                 const s = Math.min(cw, ch) / 10.5;
                 const ix = s * 1.35,
                     iy = s * 0.95;
                 return `<g transform="translate(0 ${s*1.6})">
                <g stroke="var(--panel-ink)" fill="none" stroke-width="${s*0.13}" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M0 ${-s*3.1} V ${-s*4.5} M ${-s*0.45} ${-s*4.05} L0 ${-s*4.55} L ${s*0.45} ${-s*4.05}"/>
                  <rect x="${-ix}" y="${-s*2.5}" width="${ix*2}" height="${iy*2}" rx="${s*0.15}"/>
                  <path d="M ${-ix*0.8} ${-s*2.5+iy*1.7} L ${-ix*0.25} ${-s*2.5+iy*0.9} L ${ix*0.15} ${-s*2.5+iy*1.4} L ${ix*0.45} ${-s*2.5+iy*1.05} L ${ix*0.85} ${-s*2.5+iy*1.7}"/>
                </g>
                <circle cx="${ix*0.45}" cy="${-s*2.5+iy*0.55}" r="${s*0.22}" fill="var(--panel-ink)"/>
                <text x="0" y="${s*0.35}" text-anchor="middle" font-family="Schibsted Grotesk,system-ui,sans-serif" font-weight="700" font-size="${s*0.95}" fill="var(--panel-ink)">${pname(k,cfgKey)}</text>
                <text x="0" y="${s*1.35}" text-anchor="middle" font-family="Schibsted Grotesk,system-ui,sans-serif" font-size="${s*0.55}" fill="var(--panel-ink)" opacity=".7">card page ${num}</text></g>`;
             }

             function panelSVG(x, y, w, h, k, num, rot, cfgKey) {
                 const sideways = Math.abs(rot) % 180 === 90;
                 const cw = sideways ? h : w,
                     ch = sideways ? w : h,
                     m = Math.min(w, h) * 0.04;
                 return `<g class="pnl" data-panel="${k}" role="button" tabindex="0" aria-label="${pname(k,cfgKey)}, card page ${num}">
                <rect x="${x}" y="${y}" width="${w}" height="${h}" fill="var(--p-${PANELS[k].cls})"/>
                <rect x="${x+m}" y="${y+m}" width="${w-2*m}" height="${h-2*m}" fill="none" stroke="#9AA59E" stroke-width="1.2" stroke-dasharray="5 5"/>
                <g transform="translate(${x+w/2} ${y+h/2}) rotate(${rot})">${panelContent(cw,ch,k,num,cfgKey)}</g>
                <rect class="hlr" x="${x+5}" y="${y+5}" width="${w-10}" height="${h-10}"/>
              </g>`;
             }

             function sheetSVG(cfg, page, cfgKey, withLabel) {
                 const [W, H] = cfg.sheet;
                 let inner = '';
                 if (page.p.length === 2) {
                     inner += panelSVG(0, 0, W, H / 2, ...page.p[0], cfgKey) + panelSVG(0, H / 2, W, H / 2, ...page.p[1],
                         cfgKey);
                     inner +=
                         `<line x1="0" y1="${H/2}" x2="${W}" y2="${H/2}" stroke="#2E6A22" stroke-width="2.5" stroke-dasharray="14 8"/>
                  <rect x="10" y="${H/2-13}" width="56" height="26" rx="13" fill="#2E6A22"/><text x="38" y="${H/2+5}" text-anchor="middle" font-family="Schibsted Grotesk,system-ui,sans-serif" font-weight="700" font-size="14" fill="#fff">Fold</text>`;
                 } else inner += panelSVG(0, 0, W, H, ...page.p[0], cfgKey);
                 return `<svg viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="${page.t}: ${page.s}"><rect width="${W}" height="${H}" fill="var(--paper)"/>${inner}</svg>`;
             }

             function faceSVG(cfg, k, num, cfgKey) {
                 const [w, h] = cfg.face;
                 return `<svg viewBox="0 0 ${w} ${h}" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="${w}" height="${h}" fill="var(--paper)"/>${panelSVG(0,0,w,h,k,num,0,cfgKey).replace('role="button" tabindex="0"','')}</svg>`;
             }

             /* ---------- guide render ---------- */
             function render() {
                 const single = state.kind === 'single';
                 $('#seg').classList.toggle('off', single);
                 $('#guide').hidden = single;
                 if (single) {
                     return;
                 }
                 const k = key(),
                     cfg = CFG[k];
                 const label = (state.kind === 'folded' ? 'folded ' : 'flat double-sided ') + state.orient + ' card';
                 $('#g-title').textContent = 'How to set up a ' + label;

                 $('#sheets').innerHTML = cfg.pages.map(pg =>
                         `<figure class="sheet">${sheetSVG(cfg,pg,k)}<figcaption><b>${pg.t}</b><span>${pg.s}</span></figcaption></figure>`
                     )
                     .join('');

                 const pages = cfg.pages.flatMap(p => p.p);
                 const num = x => pages.find(p => p[0] === x)[1];
                 const stage = $('#stage');
                 if (cfg.kind === 'folded') {
                     stage.innerHTML = `<div class="card axis-${cfg.axis}" id="card">
                  <div class="leaf base"><div class="face f-front" data-panel="ir">${faceSVG(cfg,'ir',num('ir'),k)}</div><div class="face f-back" data-panel="back">${faceSVG(cfg,'back',num('back'),k)}</div></div>
                  <div class="leaf cover" id="cover"><div class="face f-front" data-panel="front">${faceSVG(cfg,'front',num('front'),k)}</div><div class="face f-back" data-panel="il">${faceSVG(cfg,'il',num('il'),k)}</div></div>
                </div>`;
                     $('#chips').innerHTML = ['Front', 'Inside', 'Back'].map(c =>
                         `<button class="chip" type="button" data-view="${c.toLowerCase()}">${c}</button>`).join('');
                     $('#slider').hidden = false;
                     $('#p-hint').textContent = cfg.fold + '. Finished size ' + cfg.finalMM + '.';
                 } else {
                     stage.innerHTML = `<div class="card axis-${cfg.axis}" id="card">
                  <div class="leaf"><div class="face f-front" data-panel="front">${faceSVG(cfg,'front',1,k)}</div><div class="face f-back" data-panel="back">${faceSVG(cfg,'back',2,k)}</div></div>
                </div>`;
                     $('#chips').innerHTML = ['Front', 'Back'].map(c =>
                         `<button class="chip" type="button" data-view="${c.toLowerCase()}">${c}</button>`).join('');
                     $('#slider').hidden = true;
                     $('#p-hint').textContent = cfg.fold + '. Finished size ' + cfg.finalMM + '.';
                 }

                 const sheetOr = cfg.sheet[0] > cfg.sheet[1] ? 'landscape' : 'portrait';
                 const rotNote = {
                     'folded-portrait': 'Page 1 artwork turns clockwise, page 2 turns anticlockwise, as the arrows show.',
                     'folded-landscape': 'Inside panels stay upright. On page 2 the front goes upside down, the back stays upright.',
                     'flat-portrait': 'Turn both sides a quarter turn anticlockwise so the top points left.',
                     'flat-landscape': 'The front goes upside down, the back stays upright.'
                 } [k];
                 const order = cfg.kind === 'folded' ?
                     'Page 1 is the inside spread, page 2 is the front and back.' :
                     'Page 1 is the front, page 2 is the back.';
                 $('#rules').innerHTML = [
                     ['One PDF, two pages', order],
                     [mm(cfg.sheet[0]) + ' × ' + mm(cfg.sheet[1]) + ' mm', 'Each page is ' + sheetOr + ' (' + (cfg
                         .sheet[0] / 72).toFixed(2) + ' × ' + (cfg.sheet[1] / 72).toFixed(2) + ' in).'],
                     ['Rotate your artwork', rotNote],
                     ['Keep text inside the dashes', 'Anything outside the dashed line may be trimmed.']
                 ].map(([b, t]) => `<div class="rule"><b>${b}</b>${t}</div>`).join('');

                 state.open = 0;
                 state.turned = false;
                 $('#open').value = 0;
                 sizeCard();
                 applyCard();
             }

             function sizeCard() {
                 const card = $('#card');
                 if (!card) return;
                 const cfg = CFG[key()],
                     stage = $('#stage');
                 const sw = stage.clientWidth - 16,
                     sh = stage.clientHeight - 16;
                 const [fw, fh] = cfg.face;
                 let maxW = sw,
                     maxH = sh;
                 if (cfg.kind === 'folded') {
                     if (cfg.axis === 'Y') maxW = sw / 2;
                     else maxH = sh / 2;
                 }
                 const sc = Math.min(maxW / fw, maxH / fh, cfg.kind === 'flat' ? 0.62 : 1);
                 card.style.width = Math.round(fw * sc) + 'px';
                 card.style.height = Math.round(fh * sc) + 'px';
             }

             function applyCard() {
                 const card = $('#card');
                 if (!card) return;
                 const cfg = CFG[key()];
                 const t = state.open / 180;
                 if (cfg.kind === 'folded') {
                     const cover = $('#cover');
                     if (cfg.axis === 'Y') {
                         cover.style.transform = `rotateY(${-state.open}deg)`;
                         card.style.transform = `translateX(${t*50}%) rotateY(${state.turned?180:0}deg)`;
                     } else {
                         cover.style.transform = `rotateX(${state.open}deg)`;
                         card.style.transform = `translateY(${t*50}%) rotateX(${state.turned?180:0}deg)`;
                     }
                 } else {
                     card.style.transform = cfg.axis === 'Y' ? `rotateY(${state.turned?180:0}deg)` :
                         `rotateX(${state.turned?180:0}deg)`;
                 }
                 const view = state.turned ? 'back' : (state.open > 90 ? 'inside' : 'front');
                 root.querySelectorAll('.chip').forEach(c => c.setAttribute('aria-pressed', c.dataset.view === view));
             }

             function show(view) {
                 const cfg = CFG[key()];
                 const wasTurned = state.turned;
                 if (view === 'front') {
                     state.turned = false;
                     state.open = 0;
                 }
                 if (view === 'back') {
                     state.open = 0;
                     state.turned = true;
                 }
                 if (view === 'inside') {
                     state.turned = false;
                     state.open = 180;
                 }
                 $('#open').value = state.open;
                 // close before turning, so the card doesn't spin while open
                 if (cfg.kind === 'folded' && wasTurned !== state.turned && view === 'inside') {
                     state.open = 0;
                     applyCard();
                     setTimeout(() => {
                         state.open = 180;
                         $('#open').value = 180;
                         applyCard();
                     }, 500);
                 } else applyCard();
             }

             function panelView(p) {
                 return p === 'front' ? 'front' : p === 'back' ? 'back' : 'inside';
             }

             function highlight(p, on) {
                 root.querySelectorAll(`[data-panel="${p}"]`).forEach(el => el.classList.toggle('hl', on));
             }

             /* ---------- events ---------- */
             root.querySelectorAll('input[name=kind],input[name=orient]').forEach(i => i.addEventListener('change',
                 () => {
                     state.kind = root.querySelector('input[name=kind]:checked').value;
                     state.orient = root.querySelector('input[name=orient]:checked').value;
                     render();
                 }));
             $('#open').addEventListener('input', e => {
                 state.open = +e.target.value;
                 if (state.turned) {
                     state.turned = false;
                 }
                 applyCard();
             });
             $('#chips').addEventListener('click', e => {
                 const b = e.target.closest('.chip');
                 if (b) show(b.dataset.view);
             });
             const sheets = $('#sheets');
             sheets.addEventListener('mouseover', e => {
                 const g = e.target.closest('.pnl');
                 if (g) highlight(g.dataset.panel, true);
             });
             sheets.addEventListener('mouseout', e => {
                 const g = e.target.closest('.pnl');
                 if (g) highlight(g.dataset.panel, false);
             });
             sheets.addEventListener('focusin', e => {
                 const g = e.target.closest('.pnl');
                 if (g) highlight(g.dataset.panel, true);
             });
             sheets.addEventListener('focusout', e => {
                 const g = e.target.closest('.pnl');
                 if (g) highlight(g.dataset.panel, false);
             });
             const go = g => {
                 show(panelView(g.dataset.panel));
             };
             sheets.addEventListener('click', e => {
                 const g = e.target.closest('.pnl');
                 if (g) go(g);
             });
             sheets.addEventListener('keydown', e => {
                 const g = e.target.closest('.pnl');
                 if (g && (e.key === 'Enter' || e.key === ' ')) {
                     e.preventDefault();
                     go(g);
                 }
             });
             $('#stage').addEventListener('mouseover', e => {
                 const f = e.target.closest('.face');
                 if (f) highlight(f.dataset.panel, true);
             });
             $('#stage').addEventListener('mouseout', e => {
                 const f = e.target.closest('.face');
                 if (f) highlight(f.dataset.panel, false);
             });
             let rt;
             window.addEventListener('resize', () => {
                 clearTimeout(rt);
                 rt = setTimeout(sizeCard, 80);
             });

             function toast(msg) {
                 const t = $('#toast');
                 t.textContent = msg;
                 t.classList.add('show');
                 clearTimeout(toast.t);
                 toast.t = setTimeout(() => t.classList.remove('show'), 2600);
             }

             /* ---------- template download ---------- */
             $('#dl').addEventListener('click', () => {
                 let kind = state.kind;
                 let orient = state.orient;
                 let href = "{{ asset('tamplates') }}" + "/printo-" + kind + "-" + orient + "-template.pdf";
                 let a = document.createElement('a');
                 a.href = href;
                 a.download = kind + "-" + orient + "-template.pdf";
                 a.click();
                 toast('Template downloaded successfully.');
             });

             render();
         })();
     </script>
 </div>
