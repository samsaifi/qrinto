/**
 * Shared "Print Settings" modal logic for Qrinto.
 *
 * One source of truth for the live printer-capability modal used on:
 *   - /print/pdf/check                        (checkFlow)
 *   - /print/own-design/…/preview             (previewFlow)
 *   - /store/trays                            (trayForm)
 *
 * Each page merges this mixin into its own Alpine component:
 *
 *   function checkFlow(...) {
 *     return { ...window.printSettingsMixin({ sizeOptions, sizeDims }), ...pageState };
 *   }
 *
 * and includes the shared markup:  @include('partials.print-settings-modal')
 *
 * The mixin owns every field the partial binds to (all prefixed so they never
 * collide with a page's own state):
 *   header:   psLabel, psDriver, psStatusText, psStatusOk, showPaperModal
 *   choices:  printPaperSize, printLandscape, printDuplex, printColor,
 *             printInputBin, printQuality, printMediaType
 *   caps:     sizesLoading, sizesSource, dynamicSizes, dynamicDims, capsSource,
 *             orientationCaps, colorCaps, duplexOptions, inputBins,
 *             resolutions, mediaTypes
 *
 * The live PrintTrays instance is read from window.__ptInstance, which each
 * page assigns once it connects.
 *
 * NOTE: computed values are exposed as METHODS (psPaperSizeList(), psSummary())
 * rather than getters, because the mixin object is spread into the page
 * component and object-spread would evaluate getters into static values.
 */
(function () {
    var DEFAULT_DUPLEX = [
        { value: 'simplex', label: 'Off (single-sided)' },
        { value: 'longEdge', label: 'Long edge (book-style)' },
        { value: 'shortEdge', label: 'Short edge (tablet-style)' },
    ];

    // Sentinel value for the printer-driver's own custom page size (e.g. Noritsu's
    // "PostScript Custom Page Size"). MUST match the string the PrintTrays bridge /
    // official demo use, because when this is the selected size we send the bridge
    // an explicit `pageSize:{width,height,units:'in'}` instead of a `paperSize` name.
    var CUSTOM_SIZE = '__custom_size__';
    // The bridge reports custom-size dims in MICRONS; 25400 microns = 1 inch.
    var MICRON_PER_IN = 25400;

    window.printSettingsMixin = function (cfg) {
        cfg = cfg || {};
        var sizeOptions = cfg.sizeOptions || {}; // { value: label }
        var sizeDims = cfg.sizeDims || {}; // { value: { w, h } inches }

        return {
            // ── modal header ──
            psLabel: '',
            psDriver: '',
            psStatusText: '',
            psStatusOk: true,
            showPaperModal: false,

            // ── user selections ──
            printPaperSize: cfg.defaultSize || Object.keys(sizeOptions)[0] || '',
            printLandscape: false,
            // Orientation constraint for products that only work one way (e.g.
            // 2-page = landscape-only, 4-page = portrait-only). null = both allowed.
            // The host sets this; the modal greys/reddens the disallowed option.
            //   null | 'portrait' (portrait-only) | 'landscape' (landscape-only)
            orientationLock: null,
            printDuplex: 'simplex',
            // Page order for MULTI-PAGE PDFs only (mirrors the PrintTrays demo).
            // Sent to the bridge as cfg.pageOrder:
            //   'frontToBack' → normal (page 1 first)
            //   'backToFront' → reversed (last page first)
            // A one-page document or a photo has no order to reverse, so this is
            // ignored by the bridge for single-page jobs.
            printPageOrder: 'backToFront',
            pageOrderOptions: [
                { value: 'backToFront', label: 'Back to Front' },
                { value: 'frontToBack', label: 'Front to Back' },
            ],
            printColor: true,
            printInputBin: '',
            printQuality: '',
            printMediaType: '',
            // Scale mode sent to the bridge (SumatraPDF scaling):
            //   'fit'    → scale the page to fill the printable area (edge-to-edge)
            //   'actual' → print at true size (no scaling)
            //   'custom' → scale by printScaleFactor percent (sent as scaleFactor)
            // Default 'fit' so prints are edge-to-edge unless the operator changes it.
            printScaleMode: 'fit',
            printScaleFactor: 100,
            // True while "Apply" is regenerating + saving the PDF on the server
            // (preview flow). The shared modal disables Apply and shows a spinner.
            psApplying: false,

            // ── capability state (static defaults until the bridge answers) ──
            sizesLoading: false,
            sizesSource: 'static',
            dynamicSizes: [], // [{ value, label, w, h }]
            dynamicDims: {}, // value -> { w, h } inches
            capsSource: 'static',
            orientationCaps: ['portrait', 'landscape'],
            colorCaps: ['color', 'mono'],
            duplexOptions: DEFAULT_DUPLEX.map(function (d) { return { value: d.value, label: d.label }; }),
            inputBins: [],
            resolutions: [],
            mediaTypes: [],

            psSizeOptionsMap: sizeOptions,
            psStaticDims: sizeDims,

            // Hosts that actually PRINT a single job (the check & preview pages) opt
            // in to the driver custom-size option. Tray-config pages leave it off so
            // the __custom_size__ sentinel is never saved as a tray's media.
            allowCustomSize: !!cfg.allowCustomSize,

            // ── Printer-driver custom page size (Noritsu "PostScript Custom Page
            // Size", etc.) — reported by getPrinterDetails as its OWN fields, NOT
            // inside the sizes array. hasCustomSize gates the __custom_size__ option;
            // customSizeName is the driver's label; printCustomW/H are the inches the
            // operator will print (pre-filled from the driver's last-used size). ──
            hasCustomSize: false,
            customSizeName: '',
            printCustomW: 0,
            printCustomH: 0,

            // Options rendered in the Paper Size <select> — live list if we have
            // one, else the static server list. E2E (borderless) options are sorted
            // to the TOP so the operator sees edge-to-edge forms first; the relative
            // order within each group is preserved (stable sort).
            psPaperSizeList: function () {
                var list;
                if (this.dynamicSizes.length) {
                    list = this.dynamicSizes.map(function (s) { return { value: s.value, label: s.label }; });
                } else {
                    list = Object.entries(this.psSizeOptionsMap).map(function (e) {
                        return { value: e[0], label: e[1] };
                    });
                }
                var isE2E = function (o) {
                    return /e2e/i.test(String(o.value || '')) || /e2e/i.test(String(o.label || ''));
                };
                // Stable sort: E2E first (0), rest after (1), keeping original order
                // within each group via the index tiebreaker.
                return list
                    .map(function (o, i) { return { o: o, i: i, k: isE2E(o) ? 0 : 1 }; })
                    .sort(function (a, b) { return a.k - b.k || a.i - b.i; })
                    .map(function (x) { return x.o; });
            },

            // The sentinel value for the driver custom-size option (exposed so blade
            // templates can bind to it without hard-coding the string).
            psCustomSizeValue: CUSTOM_SIZE,

            // Dimensions (inches) of the selected paper size, or null. For the driver
            // custom size we use the operator-entered printCustomW/H.
            psDim: function () {
                if (this.printPaperSize === CUSTOM_SIZE) {
                    return (this.printCustomW > 0 && this.printCustomH > 0)
                        ? { w: +this.printCustomW, h: +this.printCustomH } : null;
                }
                return this.dynamicDims[this.printPaperSize] || this.psStaticDims[this.printPaperSize] || null;
            },

            // Is the selected paper the printer-driver's own CUSTOM page size (e.g.
            // Noritsu's "PostScript Custom Page Size")? Sent to the bridge as an
            // explicit pageSize, not a paperSize name. The modal shows W×H inputs + a
            // note when this is true.
            psIsCustomMedia: function () {
                return this.printPaperSize === CUSTOM_SIZE;
            },

            // Is the currently selected paper an edge-to-edge / borderless media?
            // Checks the size code AND its human label (either may carry "E2E").
            isE2ESelected: function () {
                if (/e2e/i.test(String(this.printPaperSize || ''))) return true;
                var live = (this.dynamicSizes || []).find(function (s) { return s.value === this.printPaperSize; }, this);
                if (live && /e2e/i.test(String(live.label || ''))) return true;
                var lbl = this.psSizeOptionsMap && this.psSizeOptionsMap[this.printPaperSize];
                return /e2e/i.test(String(lbl || ''));
            },

            // ── Shared preflight checks (size / orientation / borderless) ──
            // Per-check acknowledgements, keyed by check.key. An error must be ticked
            // before printing; a warning (E2E) shows a checkbox but never blocks.
            acks: {},
            // Whether the shared modal should render the checks block + gate Apply.
            psShowChecks: false,
            // The design/order the modal is validating against ({ w, h, pages }).
            // Hosts set this before opening the modal when psShowChecks is on.
            psDesign: { w: 0, h: 0, pages: 0 },

            // Checks for the modal's CURRENT paper selection + orientation toggle.
            psModalChecks: function () {
                var selLabel = this.printPaperSize;
                var live = (this.dynamicSizes || []).find(function (s) { return s.value === this.printPaperSize; }, this);
                if (live && live.label) selLabel = live.label;
                else if (this.psSizeOptionsMap && this.psSizeOptionsMap[this.printPaperSize]) selLabel = this.psSizeOptionsMap[this.printPaperSize];
                return this.psComputeChecks({
                    designW: this.psDesign.w, designH: this.psDesign.h, pages: this.psDesign.pages,
                    paperCode: this.printPaperSize, paperLabel: selLabel,
                    paperDim: this.psDim(), chosenLandscape: this.printLandscape,
                });
            },

            // Parse the leading "W x H" / "W × H" from a code/label (ignores an
            // "-E2E" suffix and the trailing "(bleed × bleed in)" part).
            psParseWH: function (str) {
                var m = String(str || '').match(/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/i);
                return m ? [parseFloat(m[1]), parseFloat(m[2])] : null;
            },

            // Build the preflight checks for a given design + paper selection.
            // cfg: { designW, designH, pages, paperCode, paperLabel, paperDim,
            //        chosenLandscape }.
            // Returns [{ key, ok, severity, title, detail, ackLabel }].
            psComputeChecks: function (cfg) {
                cfg = cfg || {};
                var list = [];
                var dW = parseFloat(cfg.designW), dH = parseFloat(cfg.designH);
                var pages = parseInt(cfg.pages);
                var code = cfg.paperCode, label = cfg.paperLabel || cfg.paperCode || '';

                // Paper TRIM: parse from code, then label, then fall back to dims.
                var trim = this.psParseWH(code) || this.psParseWH(label);
                var paper = trim ? { w: trim[0], h: trim[1] } :
                    (cfg.paperDim && cfg.paperDim.w > 0 ? cfg.paperDim : null);

                if (dW > 0 && dH > 0 && paper) {
                    var dw = Math.max(dW, dH), dh = Math.min(dW, dH);
                    var tw = Math.max(paper.w, paper.h), th = Math.min(paper.w, paper.h);

                    var match = Math.abs(dw - tw) < 0.15 && Math.abs(dh - th) < 0.15;

                    // Order the displayed dims by the intended orientation:
                    //   portrait  → width small, height large ("5 × 7")
                    //   landscape → width large, height small ("7 × 5")
                    var wantLand;
                    if (pages === 2) wantLand = true;
                    else if (pages === 4) wantLand = false;
                    else wantLand = dW > dH;
                    var showDW = wantLand ? dw : dh, showDH = wantLand ? dh : dw;
                    var showPW = wantLand ? tw : th, showPH = wantLand ? th : tw;

                    list.push({
                        key: 'size', ok: match, severity: match ? 'ok' : 'error',
                        title: match ? 'Size matches paper' : 'Paper size doesn\'t match your design',
                        detail: 'Design: ' + showDW + ' × ' + showDH + ' in · Paper: ' + showPW + ' × ' + showPH + ' in.' +
                            (match ? '' : ' Your design will be scaled to fit, leaving white areas or cutting off edges.'),
                        ackLabel: match ? null : 'I understand. Print anyway.',
                    });

                    // Orientation check REMOVED: the final print is ALWAYS portrait (a
                    // landscape design is rotated -90° into a portrait page), so an
                    // "Orientation mismatch" error is meaningless here.
                }

                // Borderless / E2E.
                if (code) {
                    var isE2E = /e2e/i.test(String(code)) || /e2e/i.test(String(label));
                    list.push({
                        key: 'e2e', ok: isE2E, severity: isE2E ? 'ok' : 'warn',
                        title: isE2E ? 'Borderless (edge to edge)' : 'Not edge to edge (E2E)',
                        detail: isE2E ? 'This paper prints to the edge of the paper.' :
                            'Your print won\'t be borderless — there will be a white border on every side. ' +
                            'Choose an E2E paper for a full-bleed print.',
                        ackLabel: isE2E ? null : 'Print with a white border',
                    });
                }
                return list;
            },

            // Helpers over a checks array + the shared `acks` map.
            psChecksHaveError: function (checks) { return (checks || []).some(function (c) { return !c.ok && c.severity === 'error'; }); },
            psChecksErrorsAck: function (checks) { return (checks || []).filter(function (c) { return !c.ok && c.severity === 'error'; }).every(function (c) { return !!this.acks[c.key]; }, this); },
            psChecksHaveWarn: function (checks) { return (checks || []).some(function (c) { return !c.ok && c.severity === 'warn'; }); },
            psChecksAllClear: function (checks) { return !this.psChecksHaveError(checks) && !this.psChecksHaveWarn(checks); },
            // Every failing check that offers an acknowledgement checkbox — errors
            // AND warnings (e.g. the non-E2E "Print with a white border" box) — must
            // be ticked before printing.
            psChecksAcksAll: function (checks) { return (checks || []).filter(function (c) { return !c.ok && c.ackLabel; }).every(function (c) { return !!this.acks[c.key]; }, this); },
            // Printable = every acknowledgement checkbox (error or warning) ticked.
            psChecksPrintable: function (checks) { return this.psChecksAcksAll(checks); },

            // Auto-select the Paper Size option that matches the given TRIM size
            // (inches), ignoring orientation, within a small tolerance. When several
            // options match, the E2E (edge-to-edge / borderless) variant is preferred
            // — e.g. a 7 × 10 design picks "7 x 10in E2E (7.57 × 10.49 in)" over a
            // plain 7 × 10. Matching uses the option's TRIM size parsed from its
            // name/label (e.g. "7 x 10in E2E" → 7×10) so an E2E option isn't rejected
            // by its larger BLEED dims; it falls back to the option's dims otherwise.
            // Sets printPaperSize on the best match and returns its value, or null
            // when nothing is close enough (leaving the current selection).
            psSelectSizeForDims: function (w, h) {
                w = parseFloat(w); h = parseFloat(h);
                if (!(w > 0) || !(h > 0)) return null;
                var targetMin = Math.min(w, h), targetMax = Math.max(w, h);
                var TOL = 0.15; // inches, per side
                // Parse the leading "W x H" (or "W × H") from a code/label, ignoring
                // any "-E2E" suffix and the trailing "(bleed × bleed in)" part.
                var parseTrim = function (str) {
                    var m = String(str || '').match(/(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)/i);
                    return m ? [parseFloat(m[1]), parseFloat(m[2])] : null;
                };
                var isE2E = function (str) { return /e2e/i.test(String(str || '')); };

                // Build the candidate list from the live sizes, else the static map.
                var opts = [];
                if (this.dynamicSizes && this.dynamicSizes.length) {
                    this.dynamicSizes.forEach(function (s) {
                        opts.push({ value: s.value, label: s.label, dim: this.dynamicDims[s.value] || { w: s.w, h: s.h } });
                    }, this);
                } else {
                    Object.keys(this.psStaticDims || {}).forEach(function (k) {
                        var label = (this.psSizeOptionsMap && this.psSizeOptionsMap[k]) || k;
                        opts.push({ value: k, label: label, dim: this.psStaticDims[k] });
                    }, this);
                }

                var matches = [];
                opts.forEach(function (o) {
                    // Prefer the TRIM parsed from the code/label; fall back to dims.
                    var trim = parseTrim(o.value) || parseTrim(o.label);
                    var mn, mx;
                    if (trim) { mn = Math.min(trim[0], trim[1]); mx = Math.max(trim[0], trim[1]); }
                    else if (o.dim && o.dim.w > 0 && o.dim.h > 0) { mn = Math.min(o.dim.w, o.dim.h); mx = Math.max(o.dim.w, o.dim.h); }
                    else return;
                    var delta = Math.abs(mn - targetMin) + Math.abs(mx - targetMax);
                    if (delta <= TOL * 2) {
                        matches.push({ value: o.value, delta: delta, e2e: isE2E(o.value) || isE2E(o.label) });
                    }
                });
                if (!matches.length) return null;
                // E2E variants first, then the closest dimension match.
                matches.sort(function (a, b) {
                    if (a.e2e !== b.e2e) return a.e2e ? -1 : 1;
                    return a.delta - b.delta;
                });
                this.printPaperSize = matches[0].value;
                return matches[0].value;
            },

            // One-line human summary of the current settings.
            psSummary: function () {
                var live = this.dynamicSizes.find(function (s) { return s.value === this.printPaperSize; }, this);
                var parts = [(live && live.label) || this.psSizeOptionsMap[this.printPaperSize] || this.printPaperSize];
                parts.push(this.printLandscape ? 'Landscape' : 'Portrait');
                if (this.printDuplex !== 'simplex') {
                    parts.push(this.printDuplex === 'longEdge' ? 'Duplex' : 'Duplex (short)');
                }
                parts.push(this.printColor ? 'Color' : 'B&W');
                if (this.printInputBin) {
                    var b = this.inputBins.find(function (x) { return x.value === this.printInputBin; }, this);
                    if (b) parts.push(b.label);
                }
                return parts.join(' · ');
            },

            // Open the modal with a header describing the target printer.
            psOpen: function (label, driver, statusObj) {
                this.psLabel = label || '';
                this.psDriver = driver || '';
                this.psStatusText = statusObj ? statusObj.label : '';
                this.psStatusOk = statusObj ? !!statusObj.ok : true;
                this.showPaperModal = true;
            },

            // Reset capability lists to their static defaults (on printer change).
            psResetCaps: function () {
                this.dynamicSizes = [];
                this.dynamicDims = {};
                this.sizesSource = 'static';
                this.hasCustomSize = false;
                this.customSizeName = '';
                this.inputBins = [];
                this.resolutions = [];
                this.mediaTypes = [];
                this.printInputBin = '';
                this.printQuality = '';
                this.printMediaType = '';
                this.capsSource = 'static';
                this.orientationCaps = ['portrait', 'landscape'];
                this.colorCaps = ['color', 'mono'];
                this.duplexOptions = DEFAULT_DUPLEX.map(function (d) { return { value: d.value, label: d.label }; });
            },

            // Windows PRINTER_STATUS bit-mask (0 = Ready) → { label, ok }.
            psPrinterStatus: function (info) {
                var READY = { label: 'Ready to print', ok: true };
                if (!info || info.status === undefined || info.status === null) return READY;
                var s = info.status;
                if (typeof s === 'string' && !/^\d+$/.test(s.trim())) {
                    var word = s.trim().toLowerCase();
                    var okWords = ['idle', 'ready', 'ok', 'online'];
                    return { label: s.charAt(0).toUpperCase() + s.slice(1), ok: okWords.indexOf(word) !== -1 };
                }
                var code = parseInt(s, 10);
                if (!code) return READY;
                var flags = [
                    [0x00000001, 'Paused'], [0x00000002, 'Error'], [0x00000008, 'Paper jam'],
                    [0x00000010, 'Out of paper'], [0x00000040, 'Paper problem'], [0x00000080, 'Offline'],
                    [0x00000200, 'Busy'], [0x00000400, 'Printing'], [0x00000800, 'Output bin full'],
                    [0x00001000, 'Not available'], [0x00002000, 'Waiting'], [0x00004000, 'Processing'],
                    [0x00010000, 'Warming up'], [0x00020000, 'Toner low'], [0x00040000, 'No toner'],
                    [0x00100000, 'Needs attention'], [0x00400000, 'Door open'], [0x01000000, 'Power save'],
                ];
                var reasons = flags.filter(function (f) { return code & f[0]; }).map(function (f) { return f[1]; });
                var benign = code === 0x00000400 || code === 0x01000000;
                return { label: reasons.length ? reasons.join(', ') : ('Status code ' + code), ok: benign };
            },

            // Fetch one printer's live capabilities and populate the modal. Any
            // failure leaves the static lists in place — the modal stays usable.
            psLoad: async function (printer) {
                var pt = window.__ptInstance;
                if (!printer || !pt) return;
                this.sizesLoading = true;
                try {
                    var res = await pt.getPrinterDetails(printer);
                    var raw = (res && (res.sizes || res.paperSizes || res.media)) || [];
                    var list = [], dims = {};
                    for (var i = 0; i < raw.length; i++) {
                        var s = raw[i];
                        var label = s.name || s.label || s.displayName;
                        if (!label) continue;
                        var value = s.id || s.key || label;
                        var w = s.width != null ? s.width : s.w;
                        var h = s.height != null ? s.height : s.h;
                        var units = (s.units || s.unit || '').toLowerCase();
                        if (!units && w != null && h != null) {
                            units = (Math.max(w, h) > 1000) ? 'micron' : (Math.max(w, h) > 100 ? 'mm' : 'in');
                        }
                        if (w != null && h != null) {
                            if (units === 'mm') { w = w / 25.4; h = h / 25.4; }
                            else if (units === 'micron' || units === 'um') { w = w / 25400; h = h / 25400; }
                            dims[value] = { w: +(+w).toFixed(2), h: +(+h).toFixed(2) };
                        }
                        var d = dims[value];
                        list.push({ value: value, label: d ? (label + ' (' + d.w + ' × ' + d.h + ' in)') : label, w: d ? d.w : null, h: d ? d.h : null });
                    }
                    // Driver custom page size (e.g. Noritsu "PostScript Custom Page
                    // Size"). getPrinterDetails reports it as its OWN fields —
                    // customSizeOptionName + defaultCustomSize{name,width,height} in
                    // microns — NOT inside the sizes array. Append it as a single
                    // __custom_size__ option (same as the official PrintTrays demo).
                    this.hasCustomSize = false;
                    this.customSizeName = '';
                    var cName = this.allowCustomSize && res && res.customSizeOptionName;
                    if (cName) {
                        this.hasCustomSize = true;
                        this.customSizeName = String(cName);
                        var def = res.defaultCustomSize;
                        var cLabel = this.customSizeName;
                        if (def && def.width != null && def.height != null) {
                            var cw = +(def.width / MICRON_PER_IN).toFixed(2);
                            var ch = +(def.height / MICRON_PER_IN).toFixed(2);
                            // Pre-fill the operator's entry with the driver's last-used
                            // size (only if they haven't already typed one).
                            if (!(this.printCustomW > 0)) this.printCustomW = cw;
                            if (!(this.printCustomH > 0)) this.printCustomH = ch;
                            dims[CUSTOM_SIZE] = { w: cw, h: ch };
                            cLabel = this.customSizeName + ' (' + cw + ' × ' + ch + ' in)';
                        }
                        list.push({ value: CUSTOM_SIZE, label: cLabel, w: dims[CUSTOM_SIZE] ? dims[CUSTOM_SIZE].w : null, h: dims[CUSTOM_SIZE] ? dims[CUSTOM_SIZE].h : null });
                    }

                    if (list.length) {
                        this.dynamicSizes = list;
                        this.dynamicDims = dims;
                        this.sizesSource = 'printer';
                        if (!list.some(function (o) { return o.value === this.printPaperSize; }, this)) {
                            this.printPaperSize = list[0].value;
                        }
                    }
                    this.psApplyCaps(res);
                } catch (e) {
                    console.info('[print-settings] live capabilities unavailable:', (e && e.message) || e);
                    this.sizesSource = 'static';
                } finally {
                    this.sizesLoading = false;
                }
            },

            // Map the bridge's duplex/orientation/color/bin/resolution/media
            // lists into the modal. Anything omitted keeps its default.
            psApplyCaps: function (res) {
                if (!res) return;
                var touched = false;

                // Orientation is applied as a software rotation in psBuildConfig
                // (cfg.rotation = 90), so both portrait and landscape are always
                // achievable regardless of what the driver reports for this field.
                // Keep both buttons available; just mark caps as printer-sourced
                // when the bridge answered so the LIVE badge shows.
                var ori = res.orientations || res.orientation;
                this.orientationCaps = ['portrait', 'landscape'];
                if (Array.isArray(ori) && ori.length) {
                    touched = true;
                }

                var col = res.colors || res.color || res.outputColor;
                if (Array.isArray(col) && col.length) {
                    this.colorCaps = col.map(function (c) { return String(c).toLowerCase(); })
                        .map(function (c) { return (c.indexOf('mono') !== -1 || c.indexOf('gray') !== -1 || c.indexOf('grey') !== -1) ? 'mono' : 'color'; })
                        .filter(function (v, i, a) { return a.indexOf(v) === i; });
                    if (this.printColor && this.colorCaps.indexOf('color') === -1) this.printColor = false;
                    if (!this.printColor && this.colorCaps.indexOf('mono') === -1) this.printColor = true;
                    touched = true;
                }

                var dup = res.duplex || res.duplexModes;
                if (Array.isArray(dup) && dup.length) {
                    var opts = [{ value: 'simplex', label: 'Off (single-sided)' }];
                    var has = function (k) {
                        return dup.some(function (d) { return String(d).toLowerCase().replace(/[^a-z]/g, '').indexOf(k) !== -1; });
                    };
                    if (has('longedge') || has('twosidedlongedge')) opts.push({ value: 'longEdge', label: 'Long edge (book-style)' });
                    if (has('shortedge') || has('twosidedshortedge')) opts.push({ value: 'shortEdge', label: 'Short edge (tablet-style)' });
                    this.duplexOptions = opts;
                    if (!opts.some(function (o) { return o.value === this.printDuplex; }, this)) this.printDuplex = 'simplex';
                    touched = true;
                }

                var bins = res.inputBins || res.inputBin || res.trays || res.sources;
                if (Array.isArray(bins) && bins.length) {
                    this.inputBins = bins.map(function (b) {
                        var label = (typeof b === 'string') ? b : (b.name || b.label || b.displayName || String(b));
                        var value = (typeof b === 'string') ? b : (b.id || b.key || label);
                        return { value: value, label: label };
                    }).filter(function (o) { return o.label; });
                    if (!this.inputBins.some(function (o) { return o.value === this.printInputBin; }, this)) this.printInputBin = '';
                    touched = true;
                }

                var reso = res.resolutions || res.resolution || res.dpis;
                if (Array.isArray(reso) && reso.length) {
                    this.resolutions = reso.map(function (r) {
                        if (typeof r === 'string') return { value: r, label: r };
                        var label = r.name || r.label || (r.dpiX && r.dpiY ? (r.dpiX + ' × ' + r.dpiY + ' dpi') : (r.dpi ? (r.dpi + ' dpi') : String(r)));
                        var value = r.id || r.key || label;
                        return { value: value, label: label };
                    }).filter(function (o) { return o.label; });
                    if (!this.resolutions.some(function (o) { return o.value === this.printQuality; }, this)) this.printQuality = '';
                    touched = true;
                }

                var media = res.mediaTypes || res.mediaType || res.papers;
                if (Array.isArray(media) && media.length) {
                    this.mediaTypes = media.map(function (m) {
                        var label = (typeof m === 'string') ? m : (m.name || m.label || m.displayName || String(m));
                        var value = (typeof m === 'string') ? m : (m.id || m.key || label);
                        return { value: value, label: label };
                    }).filter(function (o) { return o.label; });
                    if (!this.mediaTypes.some(function (o) { return o.value === this.printMediaType; }, this)) this.printMediaType = '';
                    touched = true;
                }

                if (touched) this.capsSource = 'printer';
            },

            // Translate the current settings into a QZ Tray print-config object.
            // (Trays doesn't print, so it just ignores this.)
            psBuildConfig: function (type, fileIsLandscape) {
                var cfg = {
                    type: type,
                    flavor: 'base64',
                    copies: this.copies || 1,
                    orientation: this.printLandscape ? 'landscape' : 'portrait',
                    colorType: this.printColor ? 'color' : 'grayscale',
                };
                var dim = this.psDim();
                if (this.printPaperSize === CUSTOM_SIZE && dim) {
                    // Driver custom page size → explicit pageSize, no paperSize name.
                    // Normalize to portrait (short edge = width); the output is always
                    // portrait, so raw W > H dims would print on a landscape sheet.
                    cfg.pageSize = { width: Math.min(dim.w, dim.h), height: Math.max(dim.w, dim.h), units: 'in' };
                } else if (dim) {
                    cfg.size = { width: dim.w, height: dim.h };
                    cfg.units = 'in';
                }
                if (this.printInputBin) cfg.printerTray = this.printInputBin;
                if (this.printDuplex === 'longEdge') cfg.duplex = 'two-sided-long-edge';
                else if (this.printDuplex === 'shortEdge') cfg.duplex = 'two-sided-short-edge';
                else cfg.duplex = false;

                var wantLandscape = this.printLandscape;
                var natural = (fileIsLandscape === undefined) ? wantLandscape : fileIsLandscape;
                if (wantLandscape !== natural) cfg.rotation = 90;

                return cfg;
            },

            // ── Live print preview (shared by the check & own-design pages) ──
            // A host opts in by setting psHasPreview = true and feeding psDesign
            // ({ w, h } in inches) + psPreviewSrc (an image URL / dataURL of the
            // artwork). The geometry below mirrors the actual print: paper in the
            // chosen orientation, the ~0.2in white margin on non-E2E media, the
            // Scale dropdown (crop-to-cover / fit / actual / custom) and a 90° turn
            // when the chosen orientation differs from the design's. Pages that
            // already define these methods (e.g. the check page, which drives them
            // off its own live `dims`) override these and keep working unchanged.
            psHasPreview: false,
            psPreviewSrc: '',

            // Multi-page navigation for the modal preview. A host sets psPreviewCount
            // (total pages) and overrides psPreviewSync(index) to swap psPreviewSrc to
            // that page (and, on the check page, render it). The arrows below drive it.
            psPreviewCount: 1,
            psPreviewIndex: 0,
            psPreviewSync: function (i) {},
            psPreviewPrev: function () {
                if (this.psPreviewIndex > 0) { this.psPreviewIndex--; this.psPreviewSync(this.psPreviewIndex); }
            },
            psPreviewNext: function () {
                if (this.psPreviewIndex < this.psPreviewCount - 1) { this.psPreviewIndex++; this.psPreviewSync(this.psPreviewIndex); }
            },

            psFmt: function (n) {
                return (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, '');
            },

            // Paper dims (inches) in the chosen print orientation.
            trayPaperDims: function () {
                var d = this.psDim() || { w: 8.5, h: 11 };
                var land = !!this.printLandscape;
                return {
                    w: land ? Math.max(d.w, d.h) : Math.min(d.w, d.h),
                    h: land ? Math.min(d.w, d.h) : Math.max(d.w, d.h),
                };
            },

            // Printable area (inches): full sheet on E2E, else minus the ~0.2in margin.
            trayPrintableDims: function () {
                var p = this.trayPaperDims();
                if (this.isE2ESelected()) return { w: p.w, h: p.h };
                var m = 0.2;
                return { w: Math.max(0.1, p.w - 2 * m), h: Math.max(0.1, p.h - 2 * m) };
            },

            // Shared px-per-inch: fit the LARGER of design & paper into the box.
            previewScale: function () {
                var f = (this.psDesign && this.psDesign.w) ? this.psDesign : { w: 1, h: 1 };
                var p = this.trayPaperDims();
                var narrow = typeof window !== 'undefined' && window.innerWidth < 768;
                var boxW = narrow ? 230 : 300;
                var boxH = narrow ? 240 : 300;
                return Math.min(boxW / Math.max(f.w, p.w), boxH / Math.max(f.h, p.h));
            },

            trayPaperStyle: function () {
                var d = this.trayPaperDims(), s = this.previewScale();
                return 'width: ' + Math.max(60, Math.round(d.w * s)) + 'px; height: ' +
                    Math.max(60, Math.round(d.h * s)) + 'px;';
            },

            trayContentStyle: function () {
                if (this.isE2ESelected()) return 'inset: 0;';
                var d = this.trayPaperDims(), m = 0.2;
                var mw = Math.min(45, (m / d.w) * 100), mh = Math.min(45, (m / d.h) * 100);
                return 'top: ' + mh.toFixed(2) + '%; bottom: ' + mh.toFixed(2) + '%; left: ' +
                    mw.toFixed(2) + '%; right: ' + mw.toFixed(2) + '%;';
            },

            trayFileStyle: function () {
                var f = (this.psDesign && this.psDesign.w) ? this.psDesign : { w: 1, h: 1 };
                var fileLandscape = f.w > f.h;
                var needSwap = !!this.printLandscape !== fileLandscape;
                var ew = needSwap ? f.h : f.w, eh = needSwap ? f.w : f.h;
                var c = this.trayPrintableDims(), s = this.previewScale();
                var mode = this.printScaleMode || 'fit';
                var k;
                if (mode === 'actual') k = 1;
                else if (mode === 'custom') k = Math.max(0.01, (this.printScaleFactor || 100) / 100);
                else if (mode === 'fit_area') k = Math.min(c.w / ew, c.h / eh);
                else k = Math.max(c.w / ew, c.h / eh); // 'fit' = crop to cover
                var w = Math.max(8, Math.round(f.w * k * s));
                var h = Math.max(8, Math.round(f.h * k * s));
                var rot = needSwap ? ' rotate(90deg)' : '';
                return 'width: ' + w + 'px; height: ' + h + 'px; transform: translate(-50%, -50%)' + rot + ';';
            },

            trayPaperInfo: function () {
                var d = this.psDim();
                var live = (this.dynamicSizes || []).find(function (x) { return x.value === this.printPaperSize; }, this);
                var label = (live && live.label) ||
                    (this.psSizeOptionsMap && this.psSizeOptionsMap[this.printPaperSize]) ||
                    this.printPaperSize || '';
                var sz = d ? (this.psFmt(d.w) + ' × ' + this.psFmt(d.h) + ' in') : '';
                var parts = [label || 'Selected paper'];
                if (sz && String(label).indexOf(sz) === -1) parts.push(sz);
                parts.push(this.isE2ESelected() ? 'Edge-to-edge' : 'With white border');
                return parts.join(' · ');
            },
        };
    };
})();
