/**
 * Card preview mixin for the local-print check page (/print/pdf/check).
 *
 * Spread into checkFlow():  ...window.cardPreviewMixin({ kind, shape, limit })
 *
 * Renders the first `limit` pages (or uploaded images) into source canvases,
 * maps them onto card panels (front / inL / inR / back) and paints every
 * <canvas data-cp="<panel>"> inside the component. The same page order /
 * flips are applied to the print PDF via cpPrepareBytes().
 *
 * Source canvases live in a closure (not Alpine state) so they are never
 * wrapped in a reactive proxy.
 */
(function () {
    'use strict';

    var PDFJS_CDN = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/';
    var MAX_PX = 1400;

    window.cardPreviewMixin = function (opts) {
        var srcPages = [];   // HTMLCanvasElement per used page / image
        var job = 0;

        return {
            cpKind: opts.kind || 'single',          // single | duplex | fold
            cpShape: opts.shape || 'portrait',      // portrait | landscape
            cpLimit: opts.limit || 1,               // pages this kind uses
            // false = preview only (the print file is built elsewhere, e.g. the
            // customizer's server PDF), so swap / flip are hidden.
            cpEditable: opts.editable !== false,
            cpUsed: 0,                              // pages actually rendered
            cpReady: false,
            cpSwap: false,                          // swap page 1 and 2
            cpFlip: {},                             // page index -> 180° turned
            cpPlaying: true,
            cpDelay: 0,

            get cpPortrait() { return this.cpShape !== 'landscape'; },

            // Panels this kind has, in reading order.
            cpPanels() {
                var P = this.cpPortrait;
                if (this.cpKind === 'fold') return [
                    { key: 'front', label: 'Front cover' },
                    { key: 'inL', label: P ? 'Inside left' : 'Inside top' },
                    { key: 'inR', label: P ? 'Inside right' : 'Inside bottom' },
                    { key: 'back', label: 'Back cover' },
                ];
                if (this.cpKind === 'duplex') return [{ key: 'front', label: 'Front' }, { key: 'back', label: 'Back' }];
                return [{ key: 'front', label: 'Front' }];
            },

            cpPhases() {
                if (this.cpKind === 'fold') return [['Front', -0.4], ['Inside', -3.6], ['Back', -7.6]];
                if (this.cpKind === 'duplex') return [['Front', -0.5], ['Back', -3.6]];
                return [['Front', 0]];
            },
            // Jump to a side: seek every card animation to that moment and hold.
            // (Changing animation-delay alone shifts from wherever the running
            // animation already is, so it landed on the wrong side.)
            cpPickPhase(d) {
                this.cpPlaying = false;
                this.cpDelay = d;
                this.$nextTick(() => {
                    var stage = this.$root.querySelector('.ck-stage');
                    if (!stage || !stage.getAnimations) return;
                    stage.getAnimations({ subtree: true }).forEach(function (a) {
                        if (a.animationName === 'ckspin') return;
                        a.currentTime = -d * 1000;
                        a.pause();
                    });
                });
            },
            cpTogglePlay() {
                this.cpPlaying = !this.cpPlaying;
                var stage = this.$root.querySelector('.ck-stage');
                if (!stage || !stage.getAnimations) return;
                var on = this.cpPlaying;
                stage.getAnimations({ subtree: true }).forEach(function (a) { on ? a.play() : a.pause(); });
            },
            cpPhaseOn(d) { return !this.cpPlaying && this.cpDelay === d; },
            cpAnimStyle() {
                return 'animation-play-state:' + (this.cpPlaying ? 'running' : 'paused');
            },

            // Page index (into the uploaded file) after the swap.
            cpOrder() {
                var o = [];
                for (var i = 0; i < this.cpUsed; i++) o.push(i);
                if (this.cpSwap && o.length >= 2) { o[0] = 1; o[1] = 0; }
                return o;
            },

            // Which part of which page lands on each panel, and how it is turned
            // upright (clockwise degrees). Matches the Printo folded template: on a
            // portrait card the outside spread (page 1) has the artwork top facing
            // right, the inside spread (page 2) facing left.
            cpSpec() {
                var n = this.cpUsed;
                if (!n) return {};
                var order = this.cpOrder();
                var P = this.cpPortrait;
                var dims = function (i) { var c = srcPages[order[i]]; return c ? { w: c.width, h: c.height } : { w: 1, h: 1 }; };
                var tall = function (i) { var d = dims(i); return d.h >= d.w; };
                var FULL = [0, 0, 1, 1], TOP = [0, 0, 1, .5], BOT = [0, .5, 1, .5], LEFT = [0, 0, .5, 1], RIGHT = [.5, 0, .5, 1];
                var self = this;
                var mk = function (i, crop, rot, where) {
                    var p = order[i];
                    return {
                        p: p, crop: crop, rot: rot + (self.cpFlip[p] ? 180 : 0), full: !where,
                        src: 'Page ' + (p + 1) + (where ? ' · ' + where : '')
                    };
                };
                var fit = function (i) { return tall(i) === P ? 0 : -90; };

                if (this.cpKind === 'single') return { front: mk(0, FULL, fit(0), '') };
                if (this.cpKind === 'duplex') {
                    var d = { front: mk(0, FULL, fit(0), '') };
                    if (n >= 2) d.back = mk(1, FULL, fit(1), '');
                    return d;
                }
                if (n >= 4) return {
                    front: mk(0, FULL, fit(0), ''), inL: mk(1, FULL, fit(1), ''),
                    inR: mk(2, FULL, fit(2), ''), back: mk(3, FULL, fit(3), '')
                };
                var o = {};
                if (P) {
                    if (tall(0)) { o.back = mk(0, TOP, -90, 'top half'); o.front = mk(0, BOT, -90, 'bottom half'); }
                    else { o.back = mk(0, LEFT, 0, 'left half'); o.front = mk(0, RIGHT, 0, 'right half'); }
                    if (n >= 2) {
                        if (tall(1)) { o.inR = mk(1, TOP, 90, 'top half'); o.inL = mk(1, BOT, 90, 'bottom half'); }
                        else { o.inL = mk(1, LEFT, 0, 'left half'); o.inR = mk(1, RIGHT, 0, 'right half'); }
                    }
                } else {
                    if (tall(0)) { o.back = mk(0, TOP, 180, 'top half'); o.front = mk(0, BOT, 0, 'bottom half'); }
                    else { o.back = mk(0, LEFT, 90, 'left half'); o.front = mk(0, RIGHT, -90, 'right half'); }
                    if (n >= 2) {
                        if (tall(1)) { o.inL = mk(1, TOP, 0, 'top half'); o.inR = mk(1, BOT, 0, 'bottom half'); }
                        else { o.inL = mk(1, LEFT, -90, 'left half'); o.inR = mk(1, RIGHT, -90, 'right half'); }
                    }
                }
                return o;
            },

            cpSource(key) { var s = this.cpSpec()[key]; return s ? s.src : 'Blank'; },
            // A flip only maps to the print file when the panel is a whole page.
            cpCanFlip(key) { var s = this.cpSpec()[key]; return this.cpEditable && this.file.type !== 'image' && !!(s && s.full); },
            cpToggleFlip(key) {
                var s = this.cpSpec()[key];
                if (!s || !s.full) return;
                var f = Object.assign({}, this.cpFlip);
                f[s.p] = !f[s.p];
                this.cpFlip = f;
                this.cpChanged();
            },
            cpToggleSwap() { this.cpSwap = !this.cpSwap; this.cpChanged(); },
            cpChanged() {
                // Pre-built ("Applied") print PDF no longer matches the panels.
                this.savedPdfB64 = null;
                this.savedPdfUrl = null;
                this.$nextTick(() => this.cpPaint());
            },

            cpNote() {
                var pc = this.pages || 0, k = this.cpKind;
                if (k === 'fold' && pc === 1) return 'Your file has 1 page, so the inside of the card will print blank. Add an inside page, or choose Single-sided.';
                if (k === 'fold' && pc === 3) return 'Your file has 3 pages. Pages 1 and 2 are used as the outside and inside spreads. Upload 2 or 4 pages for a folded card.';
                if (k === 'duplex' && pc === 1) return 'Your file has 1 page, so the back will print blank.';
                if (pc > this.cpLimit) return 'Your file has ' + pc + ' pages. Only the first ' +
                    (this.cpLimit === 1 ? 'page is' : this.cpLimit + ' pages are') + ' printed.';
                return '';
            },

            // ── loading ────────────────────────────────────────────
            async cpLoadPdf(pdf) {
                var my = ++job;
                var n = Math.min(pdf.numPages, k4(this.cpKind, this.cpLimit, pdf.numPages));
                var out = [];
                var stash = this.$refs.cpStash;
                for (var i = 1; i <= n; i++) {
                    var page = await pdf.getPage(i);
                    var v1 = page.getViewport({ scale: 1 });
                    var vp = page.getViewport({ scale: MAX_PX / Math.max(v1.width, v1.height) });
                    var c = document.createElement('canvas');
                    c.width = Math.round(vp.width); c.height = Math.round(vp.height);
                    // Rendered while attached (off-screen): pdf.js can stall on
                    // detached / display:none canvases.
                    if (stash) stash.appendChild(c);
                    var ctx = c.getContext('2d');
                    ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
                    await page.render({ canvasContext: ctx, viewport: vp }).promise;
                    out.push(c);
                }
                if (my !== job) return;
                srcPages = out;
                this.cpUsed = out.length;
                this.cpReady = true;
                this.$nextTick(() => this.cpPaint());
            },

            async cpLoadImages(urls) {
                var my = ++job;
                var out = [];
                for (var i = 0; i < Math.min(urls.length, this.cpLimit); i++) {
                    var img = await loadImage(urls[i]);
                    var s = Math.min(1, MAX_PX / Math.max(img.naturalWidth, img.naturalHeight));
                    var c = document.createElement('canvas');
                    c.width = Math.round(img.naturalWidth * s); c.height = Math.round(img.naturalHeight * s);
                    c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
                    out.push(c);
                }
                if (my !== job) return;
                srcPages = out;
                this.cpUsed = out.length;
                this.cpReady = true;
                this.$nextTick(() => this.cpPaint());
            },

            // ── painting ───────────────────────────────────────────
            cpPaint() {
                var spec = this.cpSpec();
                var list = this.$root.querySelectorAll('canvas[data-cp]');
                for (var i = 0; i < list.length; i++) {
                    this.cpWatchSize(list[i]);
                    paintPanel(list[i], spec[list[i].dataset.cp], srcPages);
                }
            },

            // A canvas painted while hidden (x-show / x-cloak) has no size and
            // ends up a blurry 2×2 bitmap — repaint it once it is laid out.
            cpWatchSize(el) {
                if (el._cpObserved || typeof ResizeObserver === 'undefined') return;
                el._cpObserved = true;
                var self = this;
                new ResizeObserver(function () {
                    if (!el.offsetWidth) return;
                    paintPanel(el, self.cpSpec()[el.dataset.cp], srcPages);
                }).observe(el);
            },

            // ── print file ─────────────────────────────────────────
            // Keep only the pages this kind uses, apply the swap, and turn any
            // flipped page 180°. Untouched files are returned as-is (byte-for-byte),
            // so the existing Canva / E2E handling downstream sees the original.
            async cpPrepareBytes(bytes) {
                if (!window.PDFLib) return bytes;
                var PDFDocument = window.PDFLib.PDFDocument, degrees = window.PDFLib.degrees;
                var src = await PDFDocument.load(bytes);
                var total = src.getPageCount();
                var keep = Math.min(total, k4(this.cpKind, this.cpLimit, total));
                var order = [];
                for (var i = 0; i < keep; i++) order.push(i);
                if (this.cpSwap && keep >= 2) { order[0] = 1; order[1] = 0; }
                var flips = order.some((p) => this.cpFlip[p]);
                if (keep === total && !this.cpSwap && !flips) return bytes;

                var out = await PDFDocument.create();
                var copied = await out.copyPages(src, order);
                copied.forEach((pg, i) => {
                    if (this.cpFlip[order[i]]) pg.setRotation(degrees((pg.getRotation().angle + 180) % 360));
                    out.addPage(pg);
                });
                return await out.save();
            },
        };
    };

    // Folded cards use 2 spreads or 4 panels; a 3-page file uses the first 2.
    function k4(kind, limit, total) {
        if (kind === 'fold' && total === 3) return 2;
        return limit;
    }

    function loadImage(url) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function () { resolve(img); };
            img.onerror = function () { reject(new Error('Could not load image')); };
            img.src = url;
        });
    }

    function paintPanel(el, sp, pages) {
        if (!el.offsetWidth) return; // hidden: cpWatchSize repaints once visible
        var W = Math.max(1, Math.round((el.offsetWidth || 1) * 2));
        var H = Math.max(1, Math.round((el.offsetHeight || 1) * 2));
        if (el.width !== W) el.width = W;
        if (el.height !== H) el.height = H;
        var ctx = el.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, W, H);
        var src = sp && pages[sp.p];
        if (!src) return;
        var rot = ((sp.rot % 360) + 360) % 360;
        var sx = sp.crop[0] * src.width, sy = sp.crop[1] * src.height;
        var sw = sp.crop[2] * src.width, sh = sp.crop[3] * src.height;
        var side = rot % 180 !== 0;
        var s = Math.max(W / (side ? sh : sw), H / (side ? sw : sh));
        ctx.save();
        ctx.translate(W / 2, H / 2);
        ctx.rotate(rot * Math.PI / 180);
        ctx.drawImage(src, sx, sy, sw, sh, -sw * s / 2, -sh * s / 2, sw * s, sh * s);
        ctx.restore();
    }
})();
