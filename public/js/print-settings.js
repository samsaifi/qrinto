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
            printDuplex: 'simplex',
            printColor: true,
            printInputBin: '',
            printQuality: '',
            printMediaType: '',

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

            // Options rendered in the Paper Size <select> — live list if we have
            // one, else the static server list.
            psPaperSizeList: function () {
                if (this.dynamicSizes.length) {
                    return this.dynamicSizes.map(function (s) { return { value: s.value, label: s.label }; });
                }
                return Object.entries(this.psSizeOptionsMap).map(function (e) {
                    return { value: e[0], label: e[1] };
                });
            },

            // Dimensions (inches) of the selected paper size, or null.
            psDim: function () {
                return this.dynamicDims[this.printPaperSize] || this.psStaticDims[this.printPaperSize] || null;
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
                if (dim) {
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
        };
    };
})();
