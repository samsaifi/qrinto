{{-- ═══════════════════════════════════════════════════════════════════════
     Local-print editor UI (own-design sizes + business card), included by
     quick-flow-pc.customize after the customizer is created. Drives the
     local-print-editor-* partials:
       - left dock: live page thumbnails + selected page
       - canvas frame: caption, "Show next page", trim/safe guides toggle, zoom
       - on-screen page size (stable across window resizes)
       - saved pages keep their layout if the on-screen size changes
       - Continue button busy state (customizer.lpContinue / lpBusy)
     Business-card saving lives in partials/business-card-editor.
     ═══════════════════════════════════════════════════════════════════════ --}}
<script>
    (function() {
        const LP = @json($lpUi);
        const PAGE_KEYS = Object.keys(LP.pages);

        // ── Saved pages keep their layout when the on-screen size changes ──
        // The editor stores object positions in canvas pixels. Record the canvas
        // width with each page, and rescale positions/sizes on load if the page
        // is now drawn at a different width.
        const stateKey = key => customizer._config.storagePrefix + '_' + customizer.productId + '_' + key;

        const saveState = customizer._saveCanvasState;
        customizer._saveCanvasState = function(key) {
            saveState.apply(this, arguments);
            const cv = this.canvases[key];
            if (!cv) return;
            try {
                const data = JSON.parse(localStorage.getItem(stateKey(key)) || 'null');
                if (!data) return;
                data.canvasWidth = cv.fabricCanvas.width;
                localStorage.setItem(stateKey(key), JSON.stringify(data));
            } catch (e) {}
        };

        const loadState = customizer._loadCanvasState;
        customizer._loadCanvasState = function(key) {
            const cv = this.canvases[key];
            try {
                const data = JSON.parse(localStorage.getItem(stateKey(key)) || 'null');
                const width = cv && cv.fabricCanvas.width;
                if (data && data.canvasWidth && width && Math.abs(data.canvasWidth - width) > 0.5) {
                    const r = width / data.canvasWidth;
                    (data.objects || []).forEach(o => {
                        o.left *= r;
                        o.top *= r;
                        o.scaleX = (o.scaleX || 1) * r;
                        o.scaleY = (o.scaleY || 1) * r;
                    });
                    data.canvasWidth = width;
                    localStorage.setItem(stateKey(key), JSON.stringify(data));
                }
            } catch (e) {}
            return loadState.apply(this, arguments);
        };

        // ── Left dock: live page thumbnails ──
        function paintThumb(key) {
            const cv = customizer.canvases[key];
            const img = document.querySelector('[data-lp-thumb="' + key + '"]');
            if (!cv || !cv.fabricCanvas || !img) return;
            try {
                // 2× the thumbnail box, for sharp thumbnails.
                img.src = cv.fabricCanvas.toDataURL({ format: 'png', multiplier: (LP.thumb.w * 2) / cv.fabricCanvas.width });
                img.hidden = false;
            } catch (e) {
                // Tainted by a cross-origin image: keep the last good thumbnail.
            }
        }

        function markActivePage(key) {
            document.querySelectorAll('.lp-page-btn').forEach(btn => {
                const on = btn.dataset.lpPage === key;
                btn.classList.toggle('on', on);
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        }

        function nextPage(key) {
            return PAGE_KEYS[(PAGE_KEYS.indexOf(key) + 1) % PAGE_KEYS.length];
        }

        function updatePageRow(key) {
            const caption = document.getElementById('lp-page-caption');
            const next = document.querySelector('#lp-show-next span');
            if (caption) caption.textContent = LP.pages[key] + ' · ' + LP.sizeLabel;
            if (next) next.textContent = 'Show ' + LP.pages[nextPage(key)].toLowerCase();
        }

        // Repaint a page's thumbnail (debounced) whenever that canvas renders -
        // text, images, shapes, background.
        const initCanvases = customizer._initAllCanvases;
        customizer._initAllCanvases = function() {
            initCanvases.apply(this, arguments);
            Object.keys(this.canvases).forEach(key => {
                let timer = null;
                this.canvases[key].fabricCanvas.on('after:render', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => paintThumb(key), 250);
                });
                setTimeout(() => paintThumb(key), 400);
            });
        };

        const switchCanvas = customizer.switchCanvas;
        customizer.switchCanvas = function(key) {
            switchCanvas.apply(this, arguments);
            if (this.canvases[key]) {
                markActivePage(key);
                updatePageRow(key);
            }
        };

        customizer.lpShowNextPage = function() {
            if (PAGE_KEYS.length > 1) this.switchCanvas(nextPage(this.activeCanvas));
        };

        customizer.lpToggleGuides = function() {
            const frame = document.getElementById('lp-canvas-frame');
            const btn = document.getElementById('lp-guides-toggle');
            if (!frame) return;
            const off = frame.classList.toggle('guides-off');
            if (btn) {
                btn.classList.toggle('on', !off);
                btn.setAttribute('aria-pressed', off ? 'false' : 'true');
            }
        };

        // On-screen page size: up to 630px wide at the page's exact aspect ratio,
        // limited by the space between the docks and the window height. Not
        // derived from the stage's own height (which contains the page), so
        // resizing the window never shrinks the page step by step.
        customizer._calcCanvasDimensions = function(stageEl, customConfig) {
            const adminW = (customConfig && customConfig.canvasWidth) || 560;
            const adminH = (customConfig && customConfig.canvasHeight) || 400;
            const dockSpace = window.innerWidth < 768 ? 16 : 340;
            const stageW = (stageEl && stageEl.offsetWidth > 200) ? stageEl.offsetWidth - dockSpace : 600;
            const maxH = Math.max(180, window.innerHeight - 320);
            const displayWidth = Math.max(160, Math.round(Math.min(630, stageW, maxH * adminW / adminH)));
            const scaleFactor = displayWidth / adminW;
            return { displayWidth, displayHeight: Math.round(adminH * scaleFactor), scaleFactor, adminW, adminH };
        };

        // Zoom the whole frame (page + trim/safe guides) instead of the bare canvas.
        customizer.setZoom = function(level) {
            const frame = document.getElementById('lp-canvas-frame');
            if (!frame) return;
            frame.style.transform = 'scale(' + level + ')';
            frame.style.transformOrigin = 'center center';
        };

        // ── Continue (partials/local-print-editor-right-dock) ──
        // Spin the arrow and relabel the button rather than replacing its markup.
        customizer.lpBusy = function(on) {
            const btn = document.getElementById('lp-continue-btn');
            if (!btn) return;
            const label = btn.querySelector('.lp-rtool-label');
            btn.disabled = on;
            btn.classList.toggle('saving', on);
            if (on) btn.setAttribute('aria-busy', 'true');
            else btn.removeAttribute('aria-busy');
            if (label) label.textContent = on ? 'Saving…' : 'Continue';
        };

        // Own-design sizes: the existing submit (composite upload + checkout-form
        // POST to the print preview). Business cards replace submitAllCanvases.
        customizer.lpContinue = function() {
            if (this.isSavingComposite) return;
            if (!LP.businessCard) this.lpBusy(true);
            return this.submitAllCanvases();
        };
    })();
</script>
