/*
 * Shared print-PDF re-lay for every page that prints through the new PrintTrays
 * bridge (customizer preview, store orders). It is the SAME algorithm as
 * buildPrintPdfBase64() on /new-testing/print/pdf/check — the reference page —
 * so a PDF prints identically from every page. Keep the two in sync.
 *
 * window.qrintoRelayPdf(bytes, opts) → Promise<Uint8Array>
 *   opts.paperW / paperH  selected paper dims in inches (live driver dims, else
 *                         the static table; the driver custom size's W×H)
 *   opts.e2e              E2E / driver custom paper: build at the TRIM size
 *                         (bleed − 0.57 / 0.49 in) and always cover-fill
 *   opts.trim             [w, h] trim parsed from the size code / label (non-E2E
 *                         "already at trim" check), or null
 *   opts.mode             'fit' (cover) | 'fit_area' (contain) | 'actual' | 'custom'
 *   opts.scaleFactor      percentage for 'custom'
 * Returns the original bytes when the PDF is already at the trim size in
 * portrait, or when pdf-lib is missing / the re-lay fails.
 */
(function () {
    'use strict';

    // E2E bleed paper dims → portrait TRIM dims: width − 0.57 in, height − 0.49 in
    // (7.57 × 10.49 → 7 × 10).
    function e2eTrimDims(dim) {
        var mn = Math.min(dim.w, dim.h),
            mx = Math.max(dim.w, dim.h);
        return {
            w: +Math.max(0.1, mn - 0.57).toFixed(2),
            h: +Math.max(0.1, mx - 0.49).toFixed(2)
        };
    }

    window.qrintoRelayPdf = async function (bytes, opts) {
        opts = opts || {};
        if (!window.PDFLib) return bytes;
        try {
            var PDFDocument = window.PDFLib.PDFDocument,
                degrees = window.PDFLib.degrees;
            var src = await PDFDocument.load(bytes);
            var srcPages = src.getPages();

            var PT = 72;
            // Output is ALWAYS portrait — the landscape toggle only drives the
            // preview, never the printed/saved PDF.
            var outLandscape = false;
            var dim = (opts.paperW > 0 && opts.paperH > 0) ? {
                w: Math.min(opts.paperW, opts.paperH),
                h: Math.max(opts.paperW, opts.paperH)
            } : {
                w: 8.5,
                h: 11
            };
            // E2E paper: the PDF is built at the TRIM size, not the bleed size.
            // The driver's E2E form adds the bleed.
            var e2e = !!opts.e2e;
            if (e2e) dim = e2eTrimDims(dim);

            // PDF already AT the selected paper's trim size: do NOT rebuild it.
            // Portrait → send the original bytes untouched (same as the new demo);
            // landscape → only turn it portrait, keeping the trim page size.
            var trim = e2e ? [dim.w, dim.h] : opts.trim;
            if (trim) {
                var tMin = Math.min(trim[0], trim[1]),
                    tMax = Math.max(trim[0], trim[1]);
                var TOL = 0.03; // inches
                var allPortrait = true;
                var allMatch = srcPages.length > 0 && srcPages.every(function (sp) {
                    var b = (sp.getCropBox && sp.getCropBox()) || sp.getMediaBox();
                    var w = b.width / 72,
                        h = b.height / 72;
                    var rot = ((sp.getRotation && sp.getRotation().angle) || 0) % 180;
                    if (rot) {
                        var t = w;
                        w = h;
                        h = t;
                    }
                    if (w > h) allPortrait = false;
                    return Math.abs(Math.min(w, h) - tMin) <= TOL && Math.abs(Math.max(w, h) - tMax) <= TOL;
                });
                if (allMatch) {
                    console.log('[print-relay] source already at trim ' + tMin + ' × ' + tMax + ' in — ' +
                        (allPortrait ? 'sending ORIGINAL PDF (no rebuild).' : 'rotating only, keeping trim size.'));
                    if (allPortrait) return bytes;
                    dim = {
                        w: tMin,
                        h: tMax
                    };
                }
            }

            var rawMax = Math.max(dim.w, dim.h),
                rawMin = Math.min(dim.w, dim.h);
            var pageW = (outLandscape ? rawMax : rawMin) * PT;
            var pageH = (outLandscape ? rawMin : rawMax) * PT;

            var out = await PDFDocument.create();
            // E2E: always zoom from the centre until the page covers the trim sheet
            // edge to edge ('fit' = cover), whatever the shape.
            var mode = e2e ? 'fit' : (opts.mode || 'fit');

            for (var i = 0; i < srcPages.length; i++) {
                var sp = srcPages[i];
                // CropBox/MediaBox origin offset. Canva (and some other exporters)
                // emit pages whose box does NOT start at (0,0). pdf-lib's embedPage()
                // forces the bounding box to left/bottom = 0, which clips the top and
                // prints a WHITE LINE at the bottom. Pass the REAL box + an adjusting
                // matrix so the content is translated to a true (0,0) origin.
                var emb = null;
                try {
                    var cb = (sp.getCropBox && sp.getCropBox()) || sp.getMediaBox();
                    var ox = cb.x || 0,
                        oy = cb.y || 0;
                    if (ox || oy) {
                        emb = await out.embedPage(sp, {
                            left: ox,
                            bottom: oy,
                            right: ox + cb.width,
                            top: oy + cb.height
                        }, [1, 0, 0, 1, -ox, -oy]);
                    }
                } catch (_e) {}
                if (!emb) emb = await out.embedPage(sp);
                var sw = emb.width,
                    sh = emb.height;
                var needSwap = outLandscape !== (sw > sh);
                // Footprint on the sheet after any 90° turn.
                var ew = needSwap ? sh : sw,
                    eh = needSwap ? sw : sh;
                var k;
                if (mode === 'actual') k = 1;
                else if (mode === 'custom') k = Math.max(0.01, (opts.scaleFactor || 100) / 100);
                else if (mode === 'fit_area') k = Math.min(pageW / ew, pageH / eh);
                else k = Math.max(pageW / ew, pageH / eh); // 'fit' = crop to cover
                var drawW = sw * k,
                    drawH = sh * k;
                var page = out.addPage([pageW, pageH]);
                if (needSwap) {
                    // -90° (clockwise) turn, centred; the pivot sits on the sheet's
                    // left/top for a clockwise turn.
                    page.drawPage(emb, {
                        x: (pageW - drawH) / 2,
                        y: (pageH + drawW) / 2,
                        xScale: k,
                        yScale: k,
                        rotate: degrees(-90)
                    });
                } else {
                    page.drawPage(emb, {
                        x: (pageW - drawW) / 2,
                        y: (pageH - drawH) / 2,
                        xScale: k,
                        yScale: k
                    });
                }
            }
            console.log('[print-relay] re-laid onto ' + (pageW / 72).toFixed(2) + ' × ' + (pageH / 72).toFixed(2) +
                ' in (' + mode + ').');
            return await out.save();
        } catch (e) {
            // On failure fall back to the ORIGINAL PDF bytes (may print with a white
            // border on a larger sheet) — same as the reference page.
            console.error('[print-relay] PDF re-lay FAILED — sending ORIGINAL:', (e && e.message) || e);
            return bytes;
        }
    };
})();
