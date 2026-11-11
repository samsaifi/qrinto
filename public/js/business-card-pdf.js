/**
 * Business card file upload (own-design local print, business cards only).
 *
 * Reads finished business card artwork in the browser and turns it into the
 * Front / Back artwork:
 *   - a PDF (PDF.js): any page count, page 1 = front, page 2 (if any) = back; ≤ 50 MB
 *   - 1 or 2 images (PNG / JPG / JPEG, checked by their bytes); ≤ 25 MB each.
 *     Two images: names containing "front" / "back" decide the side, otherwise
 *     the order they were chosen in. One image goes on one side only.
 * Every page / image is drawn onto a 1050 × 600 px JPEG (300 DPI at
 * 3.5 × 2 in): scaled to fill the card (never stretched) and the overflow
 * trimmed evenly. Other shapes and low-resolution images get a heads-up note.
 *
 * The result is handed from the size page to the editor through
 * sessionStorage (this browser tab only) - nothing is uploaded until the card
 * is saved, where the server re-checks the 300 DPI side renders as usual.
 *
 * Used by local-print/partials/business-card-upload (size page) and
 * partials/business-card-editor (drop zone, Replace, Remove).
 */
window.BusinessCardPdf = (function () {
    'use strict';

    // Same PDF.js build as the local-print check page.
    var PDFJS_URL = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
    var WORKER_URL = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

    var CARD_W_PT = 252;           // 3.5 in
    var CARD_H_PT = 144;           // 2 in
    var TOLERANCE_PT = 1.5;        // ± 0.02 in counts as an exact 3.5 × 2 page
    var MAX_PDF_BYTES = 50 * 1024 * 1024;
    var MAX_IMAGE_BYTES = 25 * 1024 * 1024;
    var OUT_W = 1050;              // 300 DPI at 3.5 in → 1050 × 600
    var OUT_H = 600;
    var STORE_KEY = 'qrinto_bizcard_v1_upload';

    /** File types offered in the pickers. */
    var ACCEPT = '.pdf,.png,.jpg,.jpeg,application/pdf,image/png,image/jpeg';

    function UploadError(message) {
        this.name = 'UploadError';
        this.message = message;
    }
    UploadError.prototype = Object.create(Error.prototype);

    var libPromise = null;
    function loadLib() {
        if (window.pdfjsLib) return Promise.resolve(window.pdfjsLib);
        if (libPromise) return libPromise;
        libPromise = new Promise(function (resolve, reject) {
            var s = document.createElement('script');
            s.src = PDFJS_URL;
            s.onload = function () {
                if (!window.pdfjsLib) return reject(new Error('PDF.js missing'));
                window.pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_URL;
                resolve(window.pdfjsLib);
            };
            s.onerror = function () {
                libPromise = null;
                reject(new UploadError('The PDF reader could not be loaded. Check your internet connection and try again.'));
            };
            document.head.appendChild(s);
        });
        return libPromise;
    }

    function inches(pt) {
        return (Math.round(pt / 72 * 100) / 100).toString();
    }

    /** What a file really is, from its first bytes (not its name). */
    async function sniff(file) {
        var b = new Uint8Array(await file.slice(0, 8).arrayBuffer());
        if (b[0] === 0x25 && b[1] === 0x50 && b[2] === 0x44 && b[3] === 0x46 && b[4] === 0x2D) return 'pdf'; // %PDF-
        if (b[0] === 0x89 && b[1] === 0x50 && b[2] === 0x4E && b[3] === 0x47) return 'png';
        if (b[0] === 0xFF && b[1] === 0xD8 && b[2] === 0xFF) return 'jpg';
        return null;
    }

    function blankCard() {
        var c = document.createElement('canvas');
        c.width = OUT_W;
        c.height = OUT_H;
        var ctx = c.getContext('2d');
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, OUT_W, OUT_H);
        return { canvas: c, ctx: ctx };
    }

    // Scale that makes a w × h source cover the card (fill, never stretch).
    function coverScale(w, h) {
        return Math.max(OUT_W / w, OUT_H / h);
    }

    async function readPdf(file) {
        if (file.size > MAX_PDF_BYTES) throw new UploadError('This PDF is too large. Upload a file under 50 MB.');
        var lib = await loadLib();
        var doc;
        try {
            doc = await lib.getDocument({ data: await file.arrayBuffer(), isEvalSupported: false }).promise;
        } catch (e) {
            throw new UploadError('We couldn’t read this PDF. Try exporting it again.');
        }

        try {
            // Any page count: page 1 = front, page 2 (if any) = back, the rest are ignored.
            var count = Math.min(doc.numPages, 2);
            var out = [];
            var resized = [];
            var sizes = [];
            for (var i = 1; i <= count; i++) {
                var page = await doc.getPage(i);
                var vp = page.getViewport({ scale: 1 }); // honours the page's /Rotate
                sizes.push(inches(vp.width) + ' × ' + inches(vp.height) + ' in');
                if (Math.abs(vp.width - CARD_W_PT) > TOLERANCE_PT || Math.abs(vp.height - CARD_H_PT) > TOLERANCE_PT) {
                    resized.push(i);
                }

                // Cover the card, centred; an exact 3.5 × 2 page simply fits.
                var scale = coverScale(vp.width, vp.height);
                var view = page.getViewport({
                    scale: scale,
                    offsetX: (OUT_W - vp.width * scale) / 2,
                    offsetY: (OUT_H - vp.height * scale) / 2,
                });
                var card = blankCard();
                // Print intent: print-visibility layers, and no requestAnimationFrame
                // pacing, so it also finishes in a background tab.
                await page.render({ canvasContext: card.ctx, viewport: view, intent: 'print' }).promise;
                out.push(card.canvas.toDataURL('image/jpeg', 0.95));
            }

            return {
                kind: 'pdf',
                names: [file.name || 'business-card.pdf'],
                pages: out,
                note: [
                    !resized.length ? '' : 'Heads up: ' + (resized.length === 2 && sizes[0] === sizes[1]
                        ? 'the pages are ' + sizes[0]
                        : resized.map(function (n) { return 'page ' + n + ' is ' + sizes[n - 1]; }).join(' and ')) +
                        ', not 3.5 × 2 in, so the design was scaled to fill the card and its edges were trimmed.',
                    doc.numPages > 2 ? 'This PDF has ' + doc.numPages + ' pages; only pages 1 and 2 are used.' : '',
                ].filter(Boolean).join(' '),
            };
        } finally {
            doc.destroy();
        }
    }

    async function decodeImage(file) {
        try {
            if (window.createImageBitmap) return await createImageBitmap(file);
        } catch (e) { /* fall back to <img> */ }
        return await new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('decode')); };
            img.src = url;
        });
    }

    async function readImage(file) {
        if (file.size > MAX_IMAGE_BYTES) throw new UploadError(file.name + ' is too large. Upload images under 25 MB.');
        var img;
        try {
            img = await decodeImage(file);
        } catch (e) {
            throw new UploadError('We couldn’t read ' + file.name + '. Try saving it again as PNG or JPG.');
        }
        var w = img.width, h = img.height;
        if (!w || !h) throw new UploadError('We couldn’t read ' + file.name + '. Try saving it again as PNG or JPG.');

        var scale = coverScale(w, h);
        var card = blankCard();
        card.ctx.imageSmoothingQuality = 'high';
        card.ctx.drawImage(img, (OUT_W - w * scale) / 2, (OUT_H - h * scale) / 2, w * scale, h * scale);
        if (img.close) img.close();

        var notes = [];
        if (Math.abs((w / h) - (OUT_W / OUT_H)) / (OUT_W / OUT_H) > 0.01) notes.push('isn’t the shape of a 3.5 × 2 in card, so it was scaled to fill the card and its edges were trimmed');
        if (scale > 1.05) notes.push('is only ' + w + ' × ' + h + ' px, so it may print soft (1050 × 600 px or more is best)');

        return { url: card.canvas.toDataURL('image/jpeg', 0.95), note: notes.length ? file.name + ' ' + notes.join(', and ') + '.' : '' };
    }

    /**
     * Validate and render business card artwork from a file picker / drop.
     * @param {FileList|File[]|File} input
     * @returns {Promise<{kind: 'pdf'|'image', names: string[], pages: string[], note: string}>}
     *          pages: JPEG data URLs - [front, back] for a PDF or two images,
     *          [one side] for a single image
     * @throws {UploadError} with a message ready to show to the user
     */
    async function read(input) {
        var files = input instanceof File ? [input] : Array.prototype.slice.call(input || []);
        if (!files.length) throw new UploadError('Choose a PDF, PNG or JPG file.');

        var kinds = [];
        for (var i = 0; i < files.length; i++) {
            var kind = await sniff(files[i]);
            if (!kind) throw new UploadError(files[i].name + ' is not a PDF, PNG or JPG file. Upload a PDF, PNG or JPG.');
            kinds.push(kind);
        }

        if (kinds.indexOf('pdf') !== -1) {
            if (files.length > 1) throw new UploadError('Upload one PDF on its own, or one or two images (front and back) - not both together.');
            return readPdf(files[0]);
        }
        if (files.length > 2) throw new UploadError('Choose one or two images: the front, and optionally the back.');

        // Two images: "front" / "back" in the names decide the side, else keep the order.
        if (files.length === 2 && /back/i.test(files[0].name) && /front/i.test(files[1].name)) files.reverse();

        var pages = [], notes = [];
        for (var j = 0; j < files.length; j++) {
            var res = await readImage(files[j]);
            pages.push(res.url);
            if (res.note) notes.push(res.note);
        }
        return {
            kind: 'image',
            names: files.map(function (f) { return f.name; }),
            pages: pages,
            note: notes.length ? 'Heads up: ' + notes.join(' ') : '',
        };
    }

    /** "Using …" line for a result; `side` names where a single image went. */
    function describe(result, side) {
        if (result.kind === 'pdf') return 'Using ' + result.names[0] + (result.pages.length === 2
            ? ': page 1 is on the front and page 2 on the back.' : ' on the front.');
        if (result.pages.length === 2) return 'Using ' + result.names[0] + ' on the front and ' + result.names[1] + ' on the back.';
        return 'Using ' + result.names[0] + ' on the ' + (side || 'front') + '.';
    }

    // ── Hand-off between the size page and the editor (this tab only) ──
    function store(result) {
        try {
            sessionStorage.setItem(STORE_KEY, JSON.stringify({
                kind: result.kind, names: result.names, pages: result.pages, note: result.note || '',
            }));
            return true;
        } catch (e) {
            return false;
        }
    }

    function stored() {
        try {
            var data = JSON.parse(sessionStorage.getItem(STORE_KEY) || 'null');
            return data && Array.isArray(data.pages) && data.pages.length >= 1 && data.pages.length <= 2 &&
                Array.isArray(data.names) ? data : null;
        } catch (e) {
            return null;
        }
    }

    function clear() {
        try { sessionStorage.removeItem(STORE_KEY); } catch (e) {}
    }

    function message(e) {
        return e instanceof UploadError ? e.message : 'We couldn’t read this file. Try exporting it again as a PDF, PNG or JPG.';
    }

    return { ACCEPT: ACCEPT, read: read, describe: describe, store: store, stored: stored, clear: clear, message: message, UploadError: UploadError };
})();
