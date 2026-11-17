{{-- ═══════════════════════════════════════════════════════════════════════
     Business card add-on for quick-flow-pc.customize (only included when
     BusinessCardController passes $businessCard).
     - Front / Back are the generic editor's two independent canvases (the
       editor UI itself is partials/local-print-editor-ui).
     - "Continue" saves BOTH sides (300 DPI render + editor JSON each) to
       business-card.save, which also builds the 7×10 and A4 print PDFs, then
       opens the dedicated business card preview / print page.
     - Finished artwork can be placed on the card - a 2-page PDF (page 1 front,
       page 2 back) or one or two PNG / JPG images (one image: the current side;
       two: front and back) - from the size page (handed over in sessionStorage)
       or the drop zone / Replace file here. public/js/business-card-pdf.js checks
       and renders it; each page becomes a fixed bottom layer on its side
       (_bcUpload), so text, photos and shapes can still go on top.
     ═══════════════════════════════════════════════════════════════════════ --}}
<script src="{{ asset('js/business-card-pdf.js') }}?v={{ filemtime(public_path('js/business-card-pdf.js')) }}"></script>
<script>
    (function() {
        const BC = @json($businessCard);
        const SIDE_KEYS = { front: 'frame_image', back: 'sample_image' };
        const DESIGN_ID_KEY = BC.storagePrefix + '_design_id';
        const STATE_PROPS = ['_isUserImage', '_isUserText', '_isTemplateText', '_isTemplateImage', '_isTemplateSvg',
            '_uploadId', '_isShape', '_shapeMaskType', '_shapeBorderShow', '_shapeBorderColor', '_shapeBorderWidth',
            '_shapeBorderStyle', '_bcUpload'
        ];

        const store = {
            get() { try { return parseInt(localStorage.getItem(DESIGN_ID_KEY), 10) || null; } catch (e) { return null; } },
            set(id) { try { localStorage.setItem(DESIGN_ID_KEY, String(id)); } catch (e) {} },
            clear() { try { localStorage.removeItem(DESIGN_ID_KEY); } catch (e) {} },
        };

        // ── Uploaded PDF artwork (_bcUpload): a fixed bottom layer per side ──
        const PDF = window.BusinessCardPdf;

        // Save resolution from the toolbar dropdown (#bc-dpi), remembered in this browser.
        const DPI_KEY = BC.storagePrefix + '_dpi';
        const dpi = {
            get() {
                const v = parseInt(document.getElementById('bc-dpi')?.value, 10);
                return (BC.dpis || [300]).includes(v) ? v : BC.dpi || 300;
            },
        };
        document.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('bc-dpi');
            if (!sel) return;
            try {
                const saved = localStorage.getItem(DPI_KEY);
                if (saved && [...sel.options].some(o => o.value === saved)) sel.value = saved;
            } catch (e) {}
            sel.addEventListener('change', () => { try { localStorage.setItem(DPI_KEY, sel.value); } catch (e) {} });
        });
        const SIDE_ORDER = [SIDE_KEYS.front, SIDE_KEYS.back];
        // Objects the editor saves (same filter as customizer-base _saveCanvasState).
        const SAVED = o => o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg || o._isShape;
        const stateKey = key => customizer._config.storagePrefix + '_' + customizer.productId + '_' + key;
        let busy = false;

        function lockArt(o) {
            o.set({ selectable: false, evented: false, hasControls: false, hasBorders: false, lockMovementX: true, lockMovementY: true });
        }

        function uploadedArt(key) {
            const cv = customizer.canvases[key];
            return cv ? cv.fabricCanvas.getObjects().filter(o => o._bcUpload) : [];
        }

        function sideIsEmpty(key) {
            const cv = customizer.canvases[key];
            if (!cv) return false;
            const bg = cv.fabricCanvas.backgroundColor;
            return cv.fabricCanvas.getObjects().filter(SAVED).length === 0 && (!bg || bg === '#ffffff');
        }

        function showMsg(text, kind) {
            const el = document.getElementById('bc-upload-msg');
            if (!el) return;
            el.textContent = text || '';
            el.className = 'bc-upload-msg ' + (kind || 'ok');
            el.hidden = !text;
        }

        // Drop zone on an empty side; Replace / Remove once a PDF is on the card.
        function refreshUploadUi() {
            const key = customizer.activeCanvas;
            const dz = document.getElementById('bc-dropzone');
            const reading = document.getElementById('bc-reading');
            const replace = document.getElementById('bc-replace');
            const remove = document.getElementById('bc-remove');
            if (dz) dz.hidden = busy || !sideIsEmpty(key);
            if (reading) reading.hidden = !busy;
            if (replace) replace.hidden = busy || !SIDE_ORDER.some(k => uploadedArt(k).length);
            if (remove) remove.hidden = busy || !uploadedArt(key).length;
        }

        function addArt(key, url) {
            return new Promise(resolve => {
                const cv = customizer.canvases[key];
                if (!cv) return resolve();
                const fc = cv.fabricCanvas;
                fabric.Image.fromURL(url, img => {
                    if (!img || !img.width) return resolve();
                    img.set({
                        left: 0, top: 0, originX: 'left', originY: 'top',
                        // Pages are exactly 3.5 × 2 in, the same shape as the card: no stretching.
                        scaleX: fc.width / img.width, scaleY: fc.height / img.height,
                        _isUserImage: true, _bcUpload: true,
                    });
                    lockArt(img);
                    fc.add(img);
                    fc.sendToBack(img);
                    fc.renderAll();
                    customizer._saveCanvasState(key);
                    resolve();
                });
            });
        }

        const sideName = key => key === SIDE_KEYS.back ? 'back' : 'front';

        // Two pages (a PDF or two images): page 1 on the Front, page 2 on the Back.
        // One page or image: on both sides if both are empty, else only `singleKey`. Replaces earlier uploaded artwork;
        // from the size page (clearSides) any old design in this browser is
        // cleared from both sides first.
        async function applyPages(pages, clearSides, singleKey) {
            if (clearSides) {
                SIDE_ORDER.forEach(key => {
                    const fc = customizer.canvases[key] && customizer.canvases[key].fabricCanvas;
                    if (!fc) return;
                    fc.getObjects().filter(SAVED).forEach(o => fc.remove(o));
                    fc.setBackgroundColor('#ffffff', () => {});
                    customizer._saveCanvasState(key);
                });
            }
            // 2 pages → Front gets page 1, Back gets page 2. 1 page/image: both sides only when BOTH are empty; otherwise just the side being edited.
            // From the size page (clearSides) both sides were just cleared.
            const bothEmpty = clearSides || SIDE_ORDER.every(k => sideIsEmpty(k));
            const targets = pages.length >= 2 || bothEmpty ? SIDE_ORDER : [singleKey || SIDE_KEYS.front];
            for (let i = 0; i < targets.length; i++) {
                const key = targets[i];
                const fc = customizer.canvases[key] && customizer.canvases[key].fabricCanvas;
                if (!fc) continue;
                uploadedArt(key).forEach(o => fc.remove(o));
                await addArt(key, pages.length >= 2 ? pages[i] : pages[0]);
                if (customizer.pushHistoryState) customizer.pushHistoryState(key);
            }
            customizer.updateUI();
            refreshUploadUi();
            return targets.length === 2;
        }

        function showResult(res, singleKey, both) {
            showMsg(PDF.describe(res, both ? 'both' : sideName(singleKey)) + (res.note ? ' ' + res.note : ''), res.note ? 'warn' : 'ok');
        }

        async function handleFile(files) {
            if (!files || !files.length || busy) return;
            const target = customizer.activeCanvas;
            busy = true;
            showMsg('');
            refreshUploadUi();
            try {
                const res = await PDF.read(files);
                const both = await applyPages(res.pages, false, target);
                showResult(res, target, both);
            } catch (e) {
                showMsg(PDF.message(e), 'err');
            } finally {
                busy = false;
                refreshUploadUi();
            }
        }

        customizer.bcRemoveUpload = function() {
            const key = this.activeCanvas;
            const fc = this.canvases[key] && this.canvases[key].fabricCanvas;
            if (!fc) return;
            uploadedArt(key).forEach(o => fc.remove(o));
            fc.renderAll();
            this._saveCanvasState(key);
            showMsg('');
            this.updateUI();
            refreshUploadUi();
        };

        // Keep the _bcUpload flag in the saved state (the shared saver only keeps
        // its own props), and lock the artwork again whenever it is re-added.
        const saveState = customizer._saveCanvasState;
        customizer._saveCanvasState = function(key) {
            saveState.apply(this, arguments);
            const cv = this.canvases[key];
            if (!cv) return;
            try {
                const data = JSON.parse(localStorage.getItem(stateKey(key)) || 'null');
                if (!data || !Array.isArray(data.objects)) return;
                cv.fabricCanvas.getObjects().filter(SAVED).forEach((o, i) => {
                    if (data.objects[i] && o._bcUpload) data.objects[i]._bcUpload = true;
                });
                localStorage.setItem(stateKey(key), JSON.stringify(data));
            } catch (e) {}
        };

        const initCanvases = customizer._initAllCanvases;
        customizer._initAllCanvases = function() {
            // A PDF handed over from the size page replaces whatever this browser
            // had saved for the card, so the old sides are not loaded first.
            const handoff = PDF ? PDF.stored() : null;
            if (handoff) {
                try { SIDE_ORDER.forEach(k => localStorage.removeItem(stateKey(k))); } catch (e) {}
            }
            initCanvases.apply(this, arguments);
            SIDE_ORDER.forEach(key => {
                const fc = this.canvases[key] && this.canvases[key].fabricCanvas;
                if (!fc) return;
                fc.on('object:added', e => {
                    if (e.target && e.target._bcUpload) lockArt(e.target);
                    refreshUploadUi();
                });
                fc.on('object:removed', refreshUploadUi);
                fc.getObjects().filter(o => o._bcUpload).forEach(lockArt);
            });
            if (handoff) {
                PDF.clear();
                applyPages(handoff.pages, true, SIDE_KEYS.front).then(both => showResult(handoff, SIDE_KEYS.front, both));
            }
            setTimeout(refreshUploadUi, 600);
        };

        const switchCanvas = customizer.switchCanvas;
        customizer.switchCanvas = function() {
            switchCanvas.apply(this, arguments);
            refreshUploadUi();
        };

        const setBackground = customizer.setCanvasBackground;
        customizer.setCanvasBackground = function() {
            setBackground.apply(this, arguments);
            refreshUploadUi();
        };

        // Drop zone + Replace file inputs, and dropping files on the drop zone.
        document.addEventListener('change', e => {
            if (!e.target.matches || !e.target.matches('[data-bc-pdf-input]')) return;
            const files = e.target.files ? Array.from(e.target.files) : [];
            e.target.value = '';
            handleFile(files);
        });
        const dz = document.getElementById('bc-dropzone');
        if (dz) {
            // Stop the stage's own image-drop handler from also taking the file.
            dz.addEventListener('dragover', e => { e.preventDefault(); e.stopPropagation(); dz.classList.add('is-over'); });
            dz.addEventListener('dragleave', () => dz.classList.remove('is-over'));
            dz.addEventListener('drop', e => {
                e.preventDefault();
                e.stopPropagation();
                dz.classList.remove('is-over');
                handleFile(e.dataTransfer && e.dataTransfer.files ? Array.from(e.dataTransfer.files) : []);
            });
        }

        // Render one side at the chosen DPI (toolbar dropdown) and serialise its editable objects. Each side
        // has its own Fabric canvas, so exporting never touches the other side.
        customizer.exportBusinessCardSide = function(key) {
            const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas) throw new Error('Missing canvas ' + key);
            const fc = cv.fabricCanvas;

            const wrapper = document.getElementById('canvas-wrapper-' + key);
            const prev = wrapper ? { display: wrapper.style.display, visibility: wrapper.style.visibility } : null;
            if (wrapper) { wrapper.style.display = 'block'; wrapper.style.visibility = 'visible'; }

            fc.discardActiveObject();
            if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', false));
            fc.renderAll();

            let image = null;
            try {
                // 3.5 in × the DPI picked in the toolbar (1050 px at 300, 4200 px at 1200).
                image = fc.toDataURL({ format: 'jpeg', quality: 0.95, multiplier: Math.round(BC.widthIn * dpi.get()) / fc.width });
            } finally {
                if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', true));
                fc.renderAll();
                if (wrapper && prev && key !== this.activeCanvas) {
                    wrapper.style.display = prev.display || 'none';
                    wrapper.style.visibility = prev.visibility || 'hidden';
                }
            }

            const objects = fc.getObjects()
                .filter(o => o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg || o._isShape)
                .map(o => o.toObject(STATE_PROPS));
            const bg = fc.backgroundColor;

            return {
                image,
                design: {
                    version: fabric.version,
                    width: fc.width,
                    height: fc.height,
                    background: bg && typeof bg.toObject === 'function' ? bg.toObject() : (bg || '#ffffff'),
                    objects,
                },
                isEmpty: objects.length === 0 && (!bg || bg === '#ffffff'),
            };
        };

        customizer.submitAllCanvases = async function() {
            if (this.isSavingComposite) return;

            let sides;
            try {
                sides = {
                    front: this.exportBusinessCardSide(SIDE_KEYS.front),
                    back: this.exportBusinessCardSide(SIDE_KEYS.back),
                };
            } catch (e) {
                console.error('Business card export failed:', e);
                alert('Your design could not be exported. If you added an image from another website, remove it and try again.');
                return;
            }

            const empty = Object.keys(sides).filter(s => sides[s].isEmpty);
            if (empty.length === 2) {
                alert('Both sides are empty. Add your design to the front and back first.');
                return;
            }
            if (empty.length === 1 && !confirm('The ' + empty[0] + ' side is blank. Save the business card anyway?')) {
                this.switchCanvas(SIDE_KEYS[empty[0]]);
                return;
            }

            this.isSavingComposite = true;
            let navigating = false;
            // Continue button busy state (partials/local-print-editor-ui).
            this.lpBusy(true);

            const payload = {
                design_id: store.get(),
                product_id: this.productId,
                width_in: BC.widthIn,
                height_in: BC.heightIn,
                orientation: BC.orientation,
                dpi: dpi.get(),
                sides: {
                    front: { image: sides.front.image, design: sides.front.design },
                    back: { image: sides.back.image, design: sides.back.design },
                },
            };

            try {
                let res = await this._postBusinessCard(payload);
                // A design id from an old session is not ours any more - save as new.
                const staleId = res.status === 403 || (res.status === 422 && res.body?.errors?.design_id);
                if (payload.design_id && staleId) {
                    store.clear();
                    payload.design_id = null;
                    res = await this._postBusinessCard(payload);
                }
                if (!res.ok || !res.body?.success) {
                    const errs = res.body?.errors ? Object.values(res.body.errors).flat().join('\n') : null;
                    throw new Error(errs || res.body?.message || 'Saving failed (' + res.status + ').');
                }
                store.set(res.body.design_id);
                // Keep the button disabled while the preview page loads.
                navigating = true;
                window.location.assign(res.body.preview_url);
            } catch (e) {
                console.error('Business card save failed:', e);
                alert(e.message || 'Your business card could not be saved.');
            } finally {
                if (navigating) return;
                this.isSavingComposite = false;
                this.lpBusy(false);
            }
        };

        // data:image/jpeg;base64,... -> Blob (binary, no base64 overhead).
        const dataUriToBlob = uri => {
            const [head, b64] = uri.split(',');
            const mime = (head.match(/data:([^;]+)/) || [])[1] || 'image/jpeg';
            const bin = atob(b64);
            const bytes = new Uint8Array(bin.length);
            for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
            return new Blob([bytes], { type: mime });
        };

        // Sent as multipart FILES, not a JSON body: shared hosting (ModSecurity)
        // rejects large non-file request bodies with 413. The server rebuilds
        // the JSON shape (BusinessCardController::unpackMultipart).
        customizer._postBusinessCard = async function(payload) {
            const meta = JSON.parse(JSON.stringify(payload));
            const form = new FormData();
            ['front', 'back'].forEach(side => {
                form.append(side + '_image', dataUriToBlob(payload.sides[side].image), side + '.jpg');
                delete meta.sides[side].image;
            });
            form.append('payload', new Blob([JSON.stringify(meta)], { type: 'application/json' }), 'payload.json');

            const res = await fetch(BC.saveUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this._config.csrfToken,
                },
                body: form,
            });
            let body = null;
            try { body = await res.json(); } catch (e) {}
            return { ok: res.ok, status: res.status, body };
        };
    })();
</script>
