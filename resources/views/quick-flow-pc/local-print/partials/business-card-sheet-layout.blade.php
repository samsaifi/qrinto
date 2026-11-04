{{-- ═══════════════════════════════════════════════════════════════════════
     Business card "Sheet layout" step (only on the business card preview page,
     replaces the generic preview <main> content). Runs inside previewFlow():
     every bc* value / method comes from preview.blade.php, and the print step
     reuses that page's PrintTrays state (printers, copies, doPrint, …).

     Cells are percentages of the sheet from the SAME CardSheetLayout the PDFs
     are rendered from, drawn inside a box with the sheet's real aspect ratio.
     On a back sheet, cell n is the back of front card n.
     ═══════════════════════════════════════════════════════════════════════ --}}
<div class="bcl">
    {{-- ── Title row ─────────────────────────────────────────── --}}
    <div class="bcl-top">
        <div class="ck-min0">
            <nav class="bcl-progress" aria-label="Progress">
                <span class="done"><span class="dot" aria-hidden="true">✓</span>Size</span>
                <span class="done"><span class="dot" aria-hidden="true">✓</span>Design</span>
                <span :class="bcStep === 'layout' ? 'now' : 'done'"
                    :aria-current="bcStep === 'layout' ? 'step' : null">
                    <span class="dot" aria-hidden="true" x-text="bcStep === 'layout' ? '3' : '✓'"></span>Sheet
                    layout</span>
                <span :class="bcStep === 'print' ? 'now' : ''" :aria-current="bcStep === 'print' ? 'step' : null">
                    <span class="dot" aria-hidden="true">4</span>Print</span>
            </nav>
            <a href="{{ $bizCard['editUrl'] }}" class="bcl-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 6l-6 6 6 6" />
                </svg>Back to design</a>
            <h1 class="bcl-h1">Sheet layout</h1>
            <p class="bcl-sub"
                x-text="bcF.cards + ' business cards on one ' + bcF.sheet_name + ' sheet, printed on both sides and lined up for cutting.'">
            </p>
        </div>
        <div class="bcl-seg" role="tablist" aria-label="Preview">
            <template
                x-for="v in [['side', 'Front and back'], ['flip', 'Turn the sheet'], ['check', 'Check alignment']]"
                :key="v[0]">
                <button type="button" role="tab" :id="'bcl-view-' + v[0]" :aria-selected="String(bcView === v[0])"
                    :class="bcView === v[0] && 'on'" @click="bcView = v[0]; bcPlaying = true" x-text="v[1]"></button>
            </template>
        </div>
    </div>

    <div class="bcl-grid">
        {{-- ── Sheet preview ──────────────────────────────────── --}}
        <section class="bcl-stage" aria-label="Sheet preview" :style="'--w:' + bcF.width_in + ';--h:' + bcF.height_in">

            {{-- Front and back --}}
            <div class="bcl-pair" x-show="bcView === 'side'">
                <template x-for="sd in ['front', 'back']" :key="sd">
                    <figure class="bcl-fig">
                        <div class="bcl-sheet" role="img"
                            :aria-label="(sd === 'front' ? 'Front' : 'Back') + ' sheet, ' + bcF.cards + ' cards'">
                            <template x-for="c in (sd === 'front' ? bcF.front : bcBackCells())"
                                :key="sd + bcFormat + bcFlip + c.n">
                                <div class="bcl-cell" :style="bcBoxStyle(c)">
                                    <img :src="bcSides[sd]" alt="" :style="bcImgStyle(sd)">
                                    <span class="bcl-guide cut" x-show="bcGuides"
                                        :style="bcGuideStyle(c, false)"></span>
                                    <span class="bcl-guide safe" x-show="bcGuides"
                                        :style="bcGuideStyle(c, true)"></span>
                                    <span class="bcl-num" x-show="bcNums" x-text="c.n"></span>
                                </div>
                            </template>
                            <template x-if="bcCuts">
                                <div>
                                    <template x-for="x in bcF.cuts_x" :key="'x' + x"><span class="bcl-cut v"
                                            :style="'left:' + x + '%'"></span></template>
                                    <template x-for="y in bcF.cuts_y" :key="'y' + y"><span class="bcl-cut h"
                                            :style="'top:' + y + '%'"></span></template>
                                </div>
                            </template>
                        </div>
                        <figcaption>
                            <span x-text="sd === 'front' ? 'Front' : 'Back'"></span>
                            <span class="muted"
                                x-text="sd === 'front' ? '· logo side' : ('· details side, ' + (bcFlip === 'long' ? 'columns swapped' : 'rows swapped'))"></span>
                        </figcaption>
                    </figure>
                </template>
            </div>

            {{-- Turn the sheet --}}
            <div class="bcl-turn" x-show="bcView === 'flip'" x-cloak>
                <div class="bcl-3d">
                    <div class="bcl-sheet bcl-flipper" :class="bcFlip === 'long' ? 'flipY' : 'flipX'"
                        :style="'animation-play-state:' + (bcPlaying ? 'running' : 'paused')" role="img"
                        :aria-label="'Sheet turning over on its ' + bcFlip + ' edge; each card number lines up front and back'">
                        <template x-for="sd in ['front', 'back']" :key="'face' + sd">
                            <div class="bcl-face"
                                :class="sd === 'back' && (bcFlip === 'long' ? 'backY' : 'backX')">
                                <template x-for="c in (sd === 'front' ? bcF.front : bcBackCells())"
                                    :key="'t' + sd + bcFormat + bcFlip + c.n">
                                    <div class="bcl-cell" :style="bcBoxStyle(c)">
                                        <img :src="bcSides[sd]" alt="" :style="bcImgStyle(sd)">
                                        <span class="bcl-guide cut" x-show="bcGuides"
                                            :style="bcGuideStyle(c, false)"></span>
                                        <span class="bcl-num" x-text="c.n"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="bcl-row">
                    <button type="button" class="bcl-pill" @click="bcPlaying = !bcPlaying"
                        x-text="bcPlaying ? 'Pause' : 'Play'"></button>
                    <span class="muted">Card 1 stays card 1 when the sheet turns over.</span>
                </div>
            </div>

            {{-- Check alignment --}}
            <div class="bcl-check" x-show="bcView === 'check'" x-cloak>
                <div class="bcl-sheet">
                    <template x-for="c in bcF.front" :key="'cf' + bcFormat + c.n">
                        <div>
                            <div class="bcl-cell faded" :style="bcBoxStyle(c)"><img :src="bcSides.front"
                                    alt="" :style="bcImgStyle('front')"></div>
                            <span class="bcl-outline" :style="bcCellStyle(c)" aria-hidden="true"></span>
                        </div>
                    </template>
                    <template x-for="c in bcF.front" :key="'cg' + bcFormat + c.n">
                        <span class="bcl-ghost" :style="bcGhostStyle(c)" aria-hidden="true"></span>
                    </template>
                </div>
                <div class="bcl-legend">
                    <span><span class="ln solid"></span>Front cards</span>
                    <span><span class="ln dashed"></span>Back cards, seen through the paper</span>
                </div>
                <div role="status" class="bcl-status" :class="(bcDx || bcDy) ? 'warn' : 'ok'" x-text="bcCheckMsg">
                </div>
            </div>
        </section>

        {{-- ── Settings / print ───────────────────────────────── --}}
        <aside class="bcl-aside" aria-label="Sheet settings">

            <template x-if="bcStep === 'layout'">
                <div class="bcl-aside-in">
                    <div class="bcl-box">
                        <div>
                            <h2 id="bcl-paper">Paper</h2>
                            <div class="bcl-seg three" role="radiogroup" aria-labelledby="bcl-paper">
                                <template x-for="p in bcPapers" :key="p[0]">
                                    <button type="button" role="radio" class="bcl-paper"
                                        :aria-checked="String(bcPaper === p[0])" :class="bcPaper === p[0] && 'on'"
                                        @click="bcSetPaper(p[0])">
                                        <span class="pt" x-text="p[1]"></span><span class="ps"
                                            x-text="p[2]"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div x-show="bcF.gaps">
                            <h2 id="bcl-gap">Space between cards</h2>
                            <p class="bcl-help">A small gap gives the blade room, so a cut that drifts a little doesn’t
                                clip the next card.</p>
                            <div class="bcl-seg three" role="radiogroup" aria-labelledby="bcl-gap">
                                <template x-for="n in bcGapSteps" :key="'g' + n">
                                    <button type="button" role="radio" :aria-checked="String(bcGap === n)"
                                        :class="bcGap === n && 'on'" @click="bcSetGap(n)"
                                        x-text="bcGapLabel(n)"></button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <h2 id="bcl-bleed">Bleed</h2>
                            <p class="bcl-help" x-text="bcBleedHelp"></p>
                            <div class="bcl-seg three" role="radiogroup" aria-labelledby="bcl-bleed">
                                <template x-for="mm in bcBleedSteps" :key="'b' + mm">
                                    <button type="button" role="radio" :aria-checked="String(bcBleedOn(mm))"
                                        :class="bcBleedOn(mm) && 'on'" :disabled="!bcBleedFits(mm)"
                                        @click="bcSetBleed(mm)" x-text="mm + ' mm'"></button>
                                </template>
                            </div>
                            <label class="bcl-check-row">
                                <input type="checkbox" :checked="bcGuides"
                                    @change="bcGuides = $event.target.checked; bcSync()">
                                Show cut line <span class="ln cut" aria-hidden="true"></span> and safe area <span
                                    class="ln safe" aria-hidden="true"></span>
                            </label>
                        </div>

                        <div>
                            <h2 id="bcl-per-sheet">Cards per sheet</h2>
                            <div class="bcl-options" role="radiogroup" aria-labelledby="bcl-per-sheet">
                                <template x-for="f in bcPaperFormats(bcPaper)" :key="f.key">
                                    <button type="button" role="radio" class="bcl-option"
                                        :aria-checked="String(bcFormat === f.key)" :class="bcFormat === f.key && 'on'"
                                        :disabled="!bcFits(f)" @click="bcSelect(f.key)">
                                        <span class="ring" aria-hidden="true"><span></span></span>
                                        <span class="ck-grow">
                                            <span class="t" x-text="f.title"></span>
                                            <span class="d" x-text="bcFormatDesc(f)"></span>
                                        </span>
                                        <span class="bcl-badge" x-show="f.recommended">Recommended</span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <h2>How the printer turns the sheet</h2>
                            <p class="bcl-help">Match your printer’s duplex setting. We place the backs to suit.</p>
                            <div class="bcl-seg two" role="radiogroup" aria-label="Duplex flip edge">
                                <button type="button" role="radio" :aria-checked="String(bcFlip === 'long')"
                                    :class="bcFlip === 'long' && 'on'" @click="bcSetFlip('long')">Long edge (like
                                    a book)</button>
                                <button type="button" role="radio" :aria-checked="String(bcFlip === 'short')"
                                    :class="bcFlip === 'short' && 'on'" @click="bcSetFlip('short')">Short edge
                                    (like a notepad)</button>
                            </div>
                        </div>

                        <div>
                            <div class="bcl-h2row">
                                <h2>Fine-tune back position</h2>
                                <button type="button" class="bcl-link" @click="bcResetNudge()">Reset</button>
                            </div>
                            <p class="bcl-help">Only if a test sheet shows the backs a little off. Moves every back
                                together.</p>
                            <div class="bcl-nudges">
                                <div>
                                    <span class="lbl" id="bcl-dx">Left / right</span>
                                    <div class="bcl-stepper sm" role="group" aria-labelledby="bcl-dx">
                                        <button type="button" aria-label="Move backs left 0.1 mm"
                                            @click="bcNudge('x', -0.1)" :disabled="bcDx <= -bcNudgeMax">−</button>
                                        <span x-text="bcMm(bcDx)" aria-live="polite"></span>
                                        <button type="button" aria-label="Move backs right 0.1 mm"
                                            @click="bcNudge('x', 0.1)" :disabled="bcDx >= bcNudgeMax">+</button>
                                    </div>
                                </div>
                                <div>
                                    <span class="lbl" id="bcl-dy">Up / down</span>
                                    <div class="bcl-stepper sm" role="group" aria-labelledby="bcl-dy">
                                        <button type="button" aria-label="Move backs up 0.1 mm"
                                            @click="bcNudge('y', -0.1)" :disabled="bcDy <= -bcNudgeMax">−</button>
                                        <span x-text="bcMm(bcDy)" aria-live="polite"></span>
                                        <button type="button" aria-label="Move backs down 0.1 mm"
                                            @click="bcNudge('y', 0.1)" :disabled="bcDy >= bcNudgeMax">+</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="bcl-btn" @click="bcStartTest()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    aria-hidden="true">
                                    <circle cx="12" cy="12" r="8" />
                                    <path d="M12 2v6M12 16v6M2 12h6M16 12h6" />
                                </svg>Print an alignment test sheet</button>
                        </div>

                        <div class="bcl-row wrap">
                            <button type="button" class="bcl-toggle" :class="bcCuts && 'on'"
                                :aria-pressed="String(bcCuts)" @click="bcCuts = !bcCuts">Cut lines</button>
                            <button type="button" class="bcl-toggle" :class="bcNums && 'on'"
                                :aria-pressed="String(bcNums)" @click="bcNums = !bcNums">Card numbers</button>
                        </div>
                    </div>

                    <div class="bcl-box tight">
                        <div class="bcl-between">
                            <span><span class="t">Sheets</span><span class="d"
                                    x-text="(copies * bcF.cards) + ' business cards'"></span></span>
                            <div class="bcl-stepper" role="group" aria-label="Sheets">
                                <button type="button" aria-label="Fewer sheets"
                                    @click="copies = Math.max(1, copies - 1)" :disabled="copies <= 1">−</button>
                                <span x-text="copies" aria-live="polite"></span>
                                <button type="button" aria-label="More sheets"
                                    @click="copies = Math.min(999, copies + 1)" :disabled="copies >= 999">+</button>
                            </div>
                        </div>
                        <div class="bcl-cutbox">
                            {{-- x-html: <template x-for> doesn't work inside <svg>. --}}
                            <svg width="44" height="44" viewBox="0 0 44 44" aria-hidden="true"
                                x-html="bcCutIcon"></svg>
                            <span><strong x-text="bcCutCount + ' straight cuts'"></strong><br>
                                <span class="muted" x-text="bcCutBody"></span></span>
                        </div>
                        <div class="bcl-note" x-show="bcEdgeNote">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v5M12 16.5v.5" />
                            </svg>
                            <span x-text="bcEdgeNote"></span>
                        </div>
                        <button type="button" class="bcl-primary" @click="bcContinue()">Continue to print
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg></button>
                        <span class="bcl-foot"
                            x-text="'Prints on ' + bcF.sheet_name + ' paper, both sides, flip on ' + bcFlip + ' edge.'"></span>
                    </div>
                </div>
            </template>

            {{-- Print step: the page's existing PrintTrays printer flow. --}}
            <template x-if="bcStep === 'print'">
                <div class="bcl-aside-in">
                    <div class="bcl-box">
                        <div>
                            <button type="button" class="bcl-back" @click="bcBackToLayout()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true">
                                    <path d="M15 6l-6 6 6 6" />
                                </svg>Back to sheet layout</button>
                            <h2 class="bcl-print-h">Print</h2>
                        </div>

                        <div class="bcl-test" x-show="bcTest">
                            <strong>Alignment test sheet — no artwork.</strong> 1 sheet with numbered outlines on
                            both sides. Hold it up to the light: each back outline should sit behind its front.
                            <button type="button" class="bcl-test-exit" @click="bcExitTest()">Print my cards
                                instead</button>
                        </div>

                        <dl class="bcl-summary">
                            <div>
                                <dt>Sheet</dt>
                                <dd x-text="bcF.title"></dd>
                            </div>
                            <div>
                                <dt>Sides</dt>
                                <dd x-text="'Both, flip on ' + bcFlip + ' edge'"></dd>
                            </div>
                            <div x-show="bcDx || bcDy">
                                <dt>Backs moved</dt>
                                <dd x-text="bcMm(bcDx) + ', ' + bcMm(bcDy)"></dd>
                            </div>
                            <div>
                                <dt>Sheets</dt>
                                <dd x-text="copies + (bcTest ? ' (test)' : ' · ' + (copies * bcF.cards) + ' cards')">
                                </dd>
                            </div>
                        </dl>

                        <div>
                            <div class="ck-side-head">
                                <h2>Select Printer</h2>
                                <button type="button" class="ck-textbtn" x-show="selectedPrinter"
                                    @click="openSettings()">Settings</button>
                            </div>
                            <div x-show="bridgeChecking" class="ck-muted ck-row"><span
                                    class="ck-spin sm"></span>Connecting to PrintTrays…</div>
                            <div x-show="!bridgeChecking && !bridgeReady" class="ck-alert">
                                <span>PrintTrays is not installed or not running.</span>
                                <button type="button" class="ck-textbtn" @click="showBridgeModal = true">Fix
                                    this</button>
                            </div>
                            <p x-show="bridgeReady && printers.length === 0" class="ck-muted">No Noritsu printers
                                found on this PC.</p>
                            <div class="ck-trays" x-show="bridgeReady && printers.length">
                                <template x-for="p in printers" :key="p.name">
                                    <button type="button" class="ck-tray"
                                        :class="selectedPrinter === p.name && 'on'"
                                        :aria-pressed="String(selectedPrinter === p.name)"
                                        @click="selectPrinter(p.name)">
                                        <span class="ck-radio"><span></span></span>
                                        <span class="ck-min0">
                                            <span class="ck-tray-name" x-text="p.name" :title="p.name"></span>
                                            <span class="ck-tray-paper" x-show="trayPaper(p.name)"
                                                x-text="trayPaper(p.name)"></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div x-show="printMsg" class="ck-msg" :class="printMsgKind" x-text="printMsg"></div>

                        <button type="button" class="bcl-primary" @click="doPrint()"
                            :disabled="!canPrint || printing">
                            <span x-show="printing" class="ck-spin sm" aria-hidden="true"></span>
                            <span
                                x-text="printing ? 'Preparing PDF…' : (canPrint ? (bcTest ? 'Print test sheet (no artwork)' : 'Print ' + copies + (copies == 1 ? ' sheet' : ' sheets')) : (blocker || 'Not ready to print'))"></span>
                        </button>
                        <a class="bcl-dl" :href="bcPdfUrl()">Download this PDF</a>

                        <div class="ck-status">
                            <span class="ck-row">
                                <span class="ck-dot"
                                    :class="bridgeReady ? 'ok' : (bridgeChecking ? '' : 'bad')"></span>
                                <span
                                    x-text="bridgeReady ? ('PrintTrays connected' + (bridgeVersion ? ' · v' + bridgeVersion : '')) : (bridgeChecking ? 'Connecting…' : 'PrintTrays not connected')"></span>
                            </span>
                            <a href="https://noritsucanada.com/new-print-trays/download/" target="_blank"
                                rel="noopener">Download PrintTrays</a>
                        </div>
                    </div>
                </div>
            </template>
        </aside>
    </div>
</div>

<style>
    .bcl {
        --g: #2F7A3B;
        --g-dk: #1F6B2E;
        --ink: #17201A;
        --mut: #4C5A4E;
        --line: #E2E7E0;
        --ctl: #D5DCD2;
        --s: 46px
    }

    .bcl .muted {
        color: var(--mut);
        font-weight: 500
    }

    .bcl-top {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 16px
    }

    .bcl-progress {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 13px;
        font-weight: 600;
        color: #5B6659;
        margin-bottom: 10px
    }

    .bcl-progress>span {
        display: flex;
        align-items: center;
        gap: 6px
    }

    .bcl-progress .dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1px solid #A9B3A6;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        background: #fff;
        color: #2F6E35
    }

    .bcl-progress .done {
        color: #2F6E35
    }

    .bcl-progress .done .dot {
        background: #E3F0E1;
        border: none
    }

    .bcl-progress .now {
        color: var(--ink);
        font-weight: 700
    }

    .bcl-progress .now .dot {
        background: var(--ink);
        color: #fff;
        border: none
    }

    .bcl-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 40px;
        padding: 0;
        border: none;
        background: transparent;
        font-size: 14px;
        font-weight: 600;
        color: #2F6E35 !important;
        text-decoration: none;
        cursor: pointer
    }

    .bcl-h1 {
        margin: 2px 0 4px;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -.02em
    }

    .bcl-sub {
        margin: 0;
        font-size: 15px;
        color: var(--mut)
    }

    .bcl-seg {
        display: flex;
        gap: 4px;
        padding: 4px;
        border-radius: 12px;
        background: #E8ECE6
    }

    .bcl-seg.two {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        background: #F0F3EE
    }

    .bcl-seg button {
        min-height: 40px;
        padding: 0 14px;
        border: none;
        border-radius: 9px;
        background: transparent;
        font-size: 14px;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer
    }

    .bcl-seg.two button {
        min-height: 44px;
        font-size: 13px;
        padding: 0 8px
    }

    .bcl-seg.three {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        background: #F0F3EE
    }

    .bcl-seg.three button {
        min-height: 44px;
        font-weight: 700
    }

    .bcl-seg button.bcl-paper {
        min-height: 52px;
        padding: 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1px
    }

    .bcl-paper .pt {
        font-size: 14px;
        font-weight: 700
    }

    .bcl-paper .ps {
        font-size: 12px;
        font-weight: 500;
        color: var(--mut)
    }

    .bcl-option:disabled {
        opacity: .5;
        cursor: not-allowed
    }

    .bcl-seg button:disabled {
        opacity: .45;
        cursor: not-allowed
    }

    .bcl-guide {
        position: absolute;
        pointer-events: none
    }

    .bcl-guide.cut {
        border: 1px dashed rgba(214, 69, 53, .9)
    }

    .bcl-guide.safe {
        border: 1px dashed rgba(36, 70, 122, .75)
    }

    .bcl-check-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        min-height: 36px;
        font-size: 13px;
        cursor: pointer
    }

    .bcl-check-row input {
        width: 18px;
        height: 18px;
        accent-color: var(--g);
        margin: 0 2px 0 0
    }

    .bcl-check-row .ln {
        display: inline-block;
        width: 18px;
        border-top: 1.5px dashed #D64535
    }

    .bcl-check-row .ln.safe {
        border-top-color: #24467A
    }

    .bcl-seg button.on {
        background: #fff;
        box-shadow: 0 1px 3px rgba(23, 32, 26, .18)
    }

    .bcl-seg button:focus-visible,
    .bcl button:focus-visible {
        outline: 2px solid var(--g);
        outline-offset: 2px
    }

    .bcl-grid {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
        align-items: flex-start
    }

    .bcl-stage {
        flex: 999 1 600px;
        min-width: 0;
        min-height: 600px;
        border-radius: 20px;
        border: 1px solid var(--line);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 16px;
        padding: 24px;
        box-sizing: border-box;
        background-color: #F4F6F3;
        background-image: radial-gradient(#D6DDD3 1px, transparent 1px);
        background-size: 18px 18px
    }

    /* Front and back stay side by side, shrinking to fit the stage. */
    .bcl-pair {
        display: flex;
        gap: 32px;
        justify-content: center;
        width: 100%
    }

    .bcl-fig {
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        flex: 0 1 calc(var(--w) * var(--s));
        min-width: 0
    }

    .bcl-pair .bcl-sheet {
        width: 100%
    }

    .bcl-fig figcaption {
        font-size: 14px;
        font-weight: 700
    }

    .bcl-sheet {
        position: relative;
        width: calc(var(--w) * var(--s));
        max-width: 100%;
        aspect-ratio: var(--w) / var(--h);
        background: #fff;
        box-shadow: 0 10px 30px rgba(23, 32, 26, .18)
    }

    .bcl-cell {
        position: absolute;
        overflow: hidden
    }

    .bcl-cell img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        max-width: none;
        display: block;
        object-fit: fill
    }

    .bcl-cell.faded {
        opacity: .55
    }

    .bcl-num {
        position: absolute;
        left: 4px;
        top: 4px;
        min-width: 18px;
        height: 18px;
        padding: 0 4px;
        border-radius: 9px;
        background: rgba(255, 255, 255, .95);
        color: var(--ink);
        font-size: 11px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box
    }

    .bcl-cut {
        position: absolute;
        pointer-events: none
    }

    .bcl-cut.v {
        top: -8px;
        height: calc(100% + 16px);
        width: 0;
        border-left: 1.5px dashed #D64535
    }

    .bcl-cut.h {
        left: -8px;
        width: calc(100% + 16px);
        height: 0;
        border-top: 1.5px dashed #D64535
    }

    .bcl-turn,
    .bcl-check {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        width: 100%
    }

    .bcl-3d {
        perspective: 1800px;
        max-width: 100%
    }

    .bcl-flipper {
        transform-style: preserve-3d;
        background: transparent;
        box-shadow: none
    }

    .bcl-face {
        position: absolute;
        inset: 0;
        background: #fff;
        box-shadow: 0 10px 30px rgba(23, 32, 26, .18);
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden
    }

    .bcl-face.backY {
        transform: rotateY(180deg)
    }

    .bcl-face.backX {
        transform: rotateX(180deg)
    }

    .bcl .flipY {
        animation: bclFlipY 6s ease-in-out infinite
    }

    .bcl .flipX {
        animation: bclFlipX 6s ease-in-out infinite
    }

    @keyframes bclFlipY {

        0%,
        25% {
            transform: rotateY(0deg)
        }

        45%,
        75% {
            transform: rotateY(180deg)
        }

        95%,
        100% {
            transform: rotateY(360deg)
        }
    }

    @keyframes bclFlipX {

        0%,
        25% {
            transform: rotateX(0deg)
        }

        45%,
        75% {
            transform: rotateX(180deg)
        }

        95%,
        100% {
            transform: rotateX(360deg)
        }
    }

    .bcl-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px
    }

    .bcl-row.wrap {
        flex-wrap: wrap
    }

    .bcl-pill {
        height: 40px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid #C7CFC4;
        background: #fff;
        font-size: 13px;
        font-weight: 700;
        color: var(--ink);
        cursor: pointer
    }

    .bcl-outline {
        position: absolute;
        box-sizing: border-box;
        border: 2px solid var(--g)
    }

    .bcl-ghost {
        position: absolute;
        box-sizing: border-box;
        border: 2px dashed #D64535;
        transition: left .3s ease, top .3s ease
    }

    .bcl-legend {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        justify-content: center;
        font-size: 13px;
        color: #33403A
    }

    .bcl-legend>span {
        display: flex;
        align-items: center;
        gap: 6px
    }

    .bcl-legend .ln {
        width: 18px;
        height: 0;
        border-top: 2px solid var(--g)
    }

    .bcl-legend .ln.dashed {
        border-top: 2px dashed #D64535
    }

    .bcl-status {
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600
    }

    .bcl-status.ok {
        background: #E3F0E1;
        color: #1D4D22
    }

    .bcl-status.warn {
        background: #FFF6E5;
        color: #6B4A00
    }

    .bcl-aside {
        flex: 1 1 360px;
        min-width: 0
    }

    .bcl-aside-in {
        display: flex;
        flex-direction: column;
        gap: 16px
    }

    .bcl-box {
        padding: 20px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid var(--line);
        display: flex;
        flex-direction: column;
        gap: 18px
    }

    .bcl-box.tight {
        gap: 14px
    }

    .bcl-box h2 {
        margin: 0 0 10px;
        font-size: 15px;
        font-weight: 700
    }

    .bcl-help {
        margin: -6px 0 10px;
        font-size: 13px;
        color: var(--mut)
    }

    .bcl-h2row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px
    }

    .bcl-h2row+.bcl-help {
        margin-top: -6px
    }

    .bcl-link {
        border: none;
        background: transparent;
        font-size: 13px;
        font-weight: 600;
        color: #2F6E35;
        cursor: pointer;
        min-height: 32px
    }

    .bcl-options {
        display: flex;
        flex-direction: column;
        gap: 8px
    }

    .bcl-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 14px;
        border: 1px solid var(--ctl);
        background: #fff;
        text-align: left;
        cursor: pointer;
        color: var(--ink)
    }

    .bcl-option.on {
        border: 2px solid var(--g);
        background: #F0F7EF;
        padding: 11px 13px
    }

    .bcl-option .ring {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #A9B3A6;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0
    }

    .bcl-option .ring span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--g);
        opacity: 0
    }

    .bcl-option.on .ring {
        border-color: var(--g)
    }

    .bcl-option.on .ring span {
        opacity: 1
    }

    .bcl .t {
        display: block;
        font-size: 15px;
        font-weight: 700
    }

    .bcl .d {
        display: block;
        font-size: 13px;
        color: var(--mut);
        margin-top: 1px
    }

    .bcl-badge {
        padding: 3px 8px;
        border-radius: 999px;
        background: #E3F0E1;
        color: #1D4D22;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap
    }

    .bcl-nudges {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px
    }

    .bcl-nudges .lbl {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px
    }

    .bcl-stepper {
        display: flex;
        align-items: center;
        border: 1px solid #C7CFC4;
        border-radius: 10px;
        overflow: hidden;
        height: 44px
    }

    .bcl-stepper button {
        width: 44px;
        height: 44px;
        border: none;
        background: #fff;
        font-size: 20px;
        cursor: pointer;
        color: var(--ink)
    }

    .bcl-stepper.sm button {
        width: 40px;
        font-size: 18px
    }

    .bcl-stepper button:disabled {
        color: #A9B3A6;
        cursor: default
    }

    .bcl-stepper>span {
        flex: 1;
        min-width: 44px;
        text-align: center;
        font-size: 14px;
        font-weight: 700
    }

    .bcl-btn {
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 12px;
        border-radius: 10px;
        border: 1px solid var(--ctl);
        background: #fff;
        font-size: 13px;
        font-weight: 700;
        color: var(--ink);
        cursor: pointer
    }

    .bcl-toggle {
        height: 40px;
        padding: 0 12px;
        border-radius: 10px;
        border: 1px solid var(--ctl);
        background: #fff;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer
    }

    .bcl-toggle.on {
        border-color: var(--g);
        background: #F0F7EF
    }

    .bcl-between {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px
    }

    .bcl-cutbox {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 12px;
        border-radius: 12px;
        background: #F4F6F3;
        font-size: 13px;
        line-height: 1.45
    }

    .bcl-cutbox svg {
        flex-shrink: 0
    }

    .bcl-note {
        display: flex;
        gap: 10px;
        padding: 12px;
        border-radius: 12px;
        background: #FFF6E5;
        color: #6B4A00;
        font-size: 13px;
        line-height: 1.45
    }

    .bcl-note svg {
        flex-shrink: 0
    }

    .bcl-primary {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        height: 54px;
        border: none;
        border-radius: 14px;
        background: var(--g-dk);
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        padding: 0 16px
    }

    .bcl-primary:disabled {
        background: #A9B3A6;
        cursor: not-allowed
    }

    .bcl-foot {
        font-size: 12px;
        color: var(--mut);
        text-align: center
    }

    .bcl-print-h {
        margin: 4px 0 0 !important;
        font-size: 22px !important;
        font-weight: 800 !important
    }

    .bcl-test {
        padding: 12px 14px;
        border-radius: 12px;
        background: #FFF6E5;
        color: #6B4A00;
        font-size: 13px;
        line-height: 1.45
    }

    .bcl-test-exit {
        display: block;
        margin-top: 10px;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 10px;
        border: 1px solid #C7CFC4;
        background: #FFFFFF;
        font-size: 13px;
        font-weight: 700;
        color: #1F6B2E;
        cursor: pointer;
        font-family: inherit
    }

    .bcl-summary {
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #F4F6F3;
        font-size: 13px
    }

    .bcl-summary>div {
        display: flex;
        justify-content: space-between;
        gap: 12px
    }

    .bcl-summary dt {
        color: var(--mut)
    }

    .bcl-summary dd {
        margin: 0;
        font-weight: 700;
        text-align: right
    }

    .bcl-dl {
        align-self: center;
        font-size: 13px;
        font-weight: 600
    }

    @media (max-width: 640px) {
        .bcl {
            --s: 30px
        }

        .bcl-stage {
            min-height: 0;
            padding: 16px
        }

        .bcl-pair {
            gap: 16px
        }

        .bcl-h1 {
            font-size: 24px
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .bcl .flipY,
        .bcl .flipX {
            animation: none !important
        }
    }
</style>


@push('scripts')
    <script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/4459000.js"></script>
    <!-- End of HubSpot Embed Code -->
@endpush
