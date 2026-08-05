/**
 * Shared Customizer Base — common logic for single/double/quad canvas editors.
 *
 * Each page creates a `customizer` object that starts with `customizerBase(config)`
 * and then overrides/extends page-specific methods (init, updateUI, _initAllCanvases, etc.).
 *
 * Config shape:
 *   imageTypes      — {key: {label, url}} map
 *   allMaskData     — mask data from product
 *   productId       — product ID
 *   templates       — active templates object
 *   templateCategories — template categories
 *   csrfToken       — CSRF token string
 *   uploadRoute     — upload URL
 *   uploadCompositeRoute — composite upload URL
 *   isPortrait      — boolean
 *   storagePrefix   — localStorage key prefix (e.g. 'qrinto_design_v1' or 'qrinto_single_v1')
 *   multiCanvas     — boolean (has multiple canvases / page switching)
 *   hasMasks        — boolean (has mask/clip path system)
 */
function customizerBase(config) {
    return {
        activeCanvas: '',
        canvases: {},
        canvasEnabled: {},
        canvasImages: {},
        uploadIds: {},
        imgScales: {},
        isUploading: false,
        isSavingComposite: false,
        selectedObject: null,

        textFontSize: '28',
        textFontFamily: 'Inter',
        textColor: '#000000',
        textAlign: 'center',

        templates: config.templates || {},
        templateCategories: config.templateCategories || [],
        activeTplCategory: null,

        imageTypes: config.imageTypes || {},
        allMaskData: config.allMaskData || {},
        productId: config.productId,
        _config: config,

        // ═══════════════════════════════════════════════
        // TEMPLATES
        // ═══════════════════════════════════════════════
        _initTemplates() {
            this.renderCategoryFilter();
            this.renderTemplateChips();
        },

        renderCategoryFilter() {
            const filter = document.getElementById('template-cat-filter');
            if (!filter) return;
            filter.innerHTML = '';
            const allBtn = document.createElement('button');
            allBtn.type = 'button';
            allBtn.className = 'template-cat-chip' + (this.activeTplCategory === null ? ' active' : '');
            allBtn.dataset.cat = '';
            allBtn.textContent = 'All';
            allBtn.onclick = () => this.filterTemplates(null);
            filter.appendChild(allBtn);
            const rawCategories = Array.isArray(this.templateCategories)
                ? this.templateCategories : Object.values(this.templateCategories || {});
            rawCategories.forEach(cat => {
                const btn = document.createElement('button');
                btn.type = 'button';
                const catId = cat.id !== undefined ? cat.id : cat;
                const catName = cat.name !== undefined ? cat.name : cat;
                btn.className = 'template-cat-chip' + (this.activeTplCategory !== null && String(this.activeTplCategory) == String(catId) ? ' active' : '');
                btn.dataset.cat = String(catId);
                btn.textContent = catName;
                btn.onclick = () => this.filterTemplates(catId);
                filter.appendChild(btn);
            });
        },

        filterTemplates(catId) {
            this.activeTplCategory = catId;
            document.querySelectorAll('.template-cat-chip').forEach(el => {
                el.classList.toggle('active', el.dataset.cat === (catId === null ? '' : String(catId)));
            });
            this.renderTemplateChips();
        },

        renderTemplateChips() {
            const strip = document.getElementById('template-strip');
            if (!strip) return;
            strip.querySelectorAll('.template-chip').forEach(el => el.remove());
            const tplMap = this.templates || {};
            const tplKeys = Object.keys(tplMap);
            if (tplKeys.length === 0) {
                strip.innerHTML = '<div class="flex flex-col items-center justify-center py-6 text-slate-400 space-y-1.5 w-full text-center"><i data-lucide="layout-template" class="w-8 h-8 opacity-40"></i><p class="text-xs font-semibold">No templates available</p></div>';
                if (window.lucide) window.lucide.createIcons();
                return;
            }
            tplKeys.forEach(id => {
                const tpl = tplMap[id];
                if (!tpl) return;
                const tplCatId = tpl.categoryId !== undefined ? tpl.categoryId : tpl.category_id;
                if (this.activeTplCategory !== null && tplCatId != this.activeTplCategory) return;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'template-chip';
                btn.setAttribute('data-template', id);
                btn.onclick = () => this.applyTemplate(id);
                const iconHtml = tpl.iconUrl
                    ? '<img src="' + tpl.iconUrl + '" alt="" style="width:1em;height:1em;object-fit:contain;">'
                    : '<i data-lucide="' + (tpl.icon || 'layout-template') + '"></i>';
                btn.innerHTML = iconHtml + '<span>' + (tpl.label || tpl.name || id) + '</span>';
                strip.appendChild(btn);
            });
            if (window.lucide) window.lucide.createIcons();
        },

        async applyTemplate(id) {
            const tpl = this.templates[id];
            if (!tpl) return;
            const keys = (this._config.multiCanvas && tpl.applyTo === 'all')
                ? Object.keys(this.canvases).filter(k => this.canvasEnabled[k]) : [this.activeCanvas];
            this.showCanvasLoading('Loading Template...');
            try {
                for (const key of keys) {
                    const cv = this.canvases[key];
                    if (!cv || this.canvasEnabled[key] === false) continue;
                    const fc = cv.fabricCanvas;
                    const W = fc.width, H = fc.height, sf = cv.scaleFactor;
                    if (tpl.replace !== false) {
                        fc.getObjects().filter(o => o._isTemplateText || o._isTemplateImage || o._isTemplateSvg)
                            .forEach(o => fc.remove(o));
                    }
                    for (const spec of (tpl.images || [])) {
                        await this._addTemplateImage(fc, { ...spec, ignoreMask: tpl.ignoreMask }, sf, key);
                    }
                    for (const spec of (tpl.svgs || [])) {
                        await this._addTemplateSvg(fc, { ...spec, ignoreMask: tpl.ignoreMask }, sf, key);
                    }
                    (tpl.texts || tpl.layers || []).forEach(spec => {
                        const align = spec.textAlign || 'center';
                        const textStr = spec.text || (spec.type === 'textbox' ? 'Text' : '');
                        const t = new fabric.Textbox(textStr, {
                            left: W * (spec.xFrac ?? spec.left ?? 0.1),
                            top: H * (spec.yFrac ?? spec.top ?? 0.3),
                            originX: align === 'right' ? 'right' : (align === 'left' ? 'left' : 'center'),
                            originY: 'top',
                            width: W * (spec.widthFrac ?? spec.width ?? 0.8),
                            fontSize: (spec.fontSize || 24) * (sf || 1),
                            fontFamily: spec.fontFamily || 'Inter',
                            fill: spec.fill || '#000000',
                            textAlign: align,
                            _isTemplateText: true, objectCaching: false,
                            cornerSize: 12, transparentCorners: false, borderColor: '#378ADD',
                            cornerColor: '#378ADD', cornerStyle: 'circle', lockScalingFlip: true, hasRotatingPoint: true
                        });
                        if (this._config.hasMasks) this._maybeClip(t, { ignoreMask: tpl.ignoreMask }, sf, key);
                        fc.add(t);
                    });
                    if (this._enforceZOrder) this._enforceZOrder(key);
                    fc.renderAll();
                    this._saveCanvasState(key);
                }
                const activeCv = this.canvases[this.activeCanvas];
                if (activeCv && activeCv.fabricCanvas) activeCv.fabricCanvas.discardActiveObject().renderAll();
                this.selectedObject = null;
                this.updateUI();
            } catch (err) { console.error('Template loading error:', err); }
            finally { setTimeout(() => this.hideCanvasLoading(), 300); }
        },

        showCanvasLoading(msg) {
            const overlay = document.getElementById('canvas-loading-overlay');
            if (overlay) {
                const txt = overlay.querySelector('span');
                if (txt) txt.textContent = msg || 'Loading Template...';
                overlay.classList.remove('hidden');
                if (window.lucide) window.lucide.createIcons();
            }
        },

        hideCanvasLoading() {
            const overlay = document.getElementById('canvas-loading-overlay');
            if (overlay) overlay.classList.add('hidden');
        },

        _addTemplateImage(fc, spec, sf, key) {
            return new Promise((resolve) => {
                const imgUrl = spec.url || spec.src || spec.image_url || spec.image;
                if (!imgUrl) return resolve(null);
                const loadImage = (url, useCors) => {
                    const opts = useCors ? { crossOrigin: 'anonymous' } : {};
                    fabric.Image.fromURL(url, img => {
                        if (!img || !img.width) { if (useCors) return loadImage(url, false); return resolve(null); }
                        const W = fc.width, H = fc.height;
                        const scale = (W * (spec.widthFrac || spec.width_frac || 0.3)) / img.width;
                        img.set({
                            originX: 'center', originY: 'center',
                            left: W * (spec.xFrac ?? spec.left ?? 0.5),
                            top: H * (spec.yFrac ?? spec.top ?? 0.5),
                            scaleX: scale, scaleY: scale, angle: spec.angle || 0,
                            label: spec.label || 'Template Image', _isTemplateImage: true,
                            selectable: !spec.locked, evented: !spec.locked, hasControls: !spec.locked,
                            cornerStyle: 'circle', cornerSize: 12, transparentCorners: false,
                            borderColor: '#378ADD', cornerColor: '#378ADD',
                            lockScalingFlip: true, uniformScaling: true, objectCaching: true
                        });
                        if (this._config.hasMasks && this._maybeClip) this._maybeClip(img, spec, sf, key);
                        fc.add(img);
                        resolve(img);
                    }, opts);
                };
                loadImage(imgUrl, true);
            });
        },

        _addTemplateSvg(fc, spec, sf, key) {
            return new Promise((resolve) => {
                const onLoaded = (objects, options) => {
                    if (!objects || !objects.length) return resolve(null);
                    const obj = fabric.util.groupSVGElements(objects, options);
                    const W = fc.width;
                    const scale = (W * (spec.widthFrac || 0.15)) / (obj.width || 100);
                    if (spec.fill) {
                        if (obj._objects) obj._objects.forEach(o => o.set('fill', spec.fill));
                        else obj.set('fill', spec.fill);
                    }
                    obj.set({
                        originX: 'center', originY: 'center',
                        left: W * (spec.xFrac ?? 0.5), top: fc.height * (spec.yFrac ?? 0.5),
                        scaleX: scale, scaleY: scale, angle: spec.angle || 0,
                        _isTemplateSvg: true, selectable: !spec.locked, evented: !spec.locked, hasControls: !spec.locked,
                        cornerStyle: 'circle', cornerSize: 12, transparentCorners: false,
                        borderColor: '#378ADD', cornerColor: '#378ADD', lockScalingFlip: true, uniformScaling: true
                    });
                    if (this._config.hasMasks && this._maybeClip) this._maybeClip(obj, spec, sf, key);
                    fc.add(obj);
                    resolve(obj);
                };
                if (spec.url) fabric.loadSVGFromURL(spec.url, onLoaded);
                else if (spec.svg) fabric.loadSVGFromString(spec.svg, onLoaded);
                else resolve(null);
            });
        },

        // ═══════════════════════════════════════════════
        // TOOLBAR SYNC
        // ═══════════════════════════════════════════════
        _syncToolbarToSelection(obj) {
            const clearBtn = document.getElementById('clear-text-btn');
            const addBtn = document.getElementById('add-text-btn');
            const editBadge = document.getElementById('editing-badge');
            if (!obj || !['i-text', 'text', 'textbox'].includes(obj.type)) {
                clearBtn?.classList.add('hidden');
                addBtn?.classList.remove('hidden');
                editBadge?.classList.add('hidden');
                return;
            }
            const ti = document.getElementById('text-input');
            if (ti && ti.value !== obj.text) ti.value = obj.text;
            const ff = document.getElementById('font-family-select'); if (ff) ff.value = obj.fontFamily;
            const fs = document.getElementById('font-size-select'); if (fs) fs.value = obj.fontSize.toString();
            const tc = document.getElementById('text-color-input'); if (tc) tc.value = obj.fill;
            const ta = document.getElementById('text-align-select'); if (ta) ta.value = obj.textAlign || 'center';
            clearBtn?.classList.remove('hidden');
            addBtn?.classList.add('hidden');
            editBadge?.classList.remove('hidden');
        },

        async _updateSelectedStyle(property, value) {
            if (!this.selectedObject) return;
            if (property === 'fontFamily') {
                try { await document.fonts.load('1em "' + value + '"'); } catch (e) {}
            }
            this.selectedObject.set(property, value);
            const cv = this.canvases[this.activeCanvas];
            if (cv) { cv.fabricCanvas.requestRenderAll(); this._saveCanvasState(this.activeCanvas); }
        },

        onTextInputChange(value) {
            if (this.selectedObject && ['i-text', 'text', 'textbox'].includes(this.selectedObject.type)) {
                this.selectedObject.set('text', value);
                this.canvases[this.activeCanvas]?.fabricCanvas?.requestRenderAll();
                this._saveCanvasState(this.activeCanvas);
                if (!value.trim()) this.handleRemove();
            } else if (value.trim()) {
                this.addText();
            }
        },

        clearSelection() {
            const cv = this.canvases[this.activeCanvas];
            if (cv) {
                cv.fabricCanvas.discardActiveObject().renderAll();
                this.selectedObject = null;
                const ti = document.getElementById('text-input'); if (ti) ti.value = '';
                this.updateUI();
            }
        },

        // ═══════════════════════════════════════════════
        // TEXT
        // ═══════════════════════════════════════════════
        addText() {
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            if (!cv || (this._config.multiCanvas && !this.canvasEnabled[key])) return;
            const ti = document.getElementById('text-input');
            const str = (ti && ti.value.trim()) ? ti.value.trim() : 'Your Text Here';
            const ffSelect = document.getElementById('font-family-select');
            const fsSelect = document.getElementById('font-size-select');
            const tcInput = document.getElementById('text-color-input');
            const taSelect = document.getElementById('text-align-select');
            const fontFamily = ffSelect ? ffSelect.value : this.textFontFamily;
            const fontSize = fsSelect ? parseInt(fsSelect.value) : parseInt(this.textFontSize);
            const fill = tcInput ? tcInput.value : this.textColor;
            const align = taSelect ? taSelect.value : this.textAlign;

            document.fonts.load('1em "' + fontFamily + '"').then(() => {
                const W = cv.fabricCanvas.width, H = cv.fabricCanvas.height;
                const t = new fabric.Textbox(str, {
                    left: W / 2, top: H / 2, originX: 'center', originY: 'center',
                    width: W * 0.7, fontSize: fontSize, fontFamily: fontFamily,
                    fill: fill, textAlign: align, _isUserText: true, objectCaching: false,
                    cornerSize: 12, transparentCorners: false, borderColor: '#378ADD',
                    cornerColor: '#378ADD', cornerStyle: 'circle', lockScalingFlip: true, hasRotatingPoint: true
                });
                if (this._config.hasMasks) {
                    const firstKey = Object.keys(this.imageTypes)[0];
                    if (key === firstKey) {
                        const clipGroup = this._createCombinedClipPath(key, cv.scaleFactor);
                        if (clipGroup) t.set('clipPath', clipGroup);
                    }
                }
                cv.fabricCanvas.add(t);
                if (this._enforceZOrder) this._enforceZOrder(key);
                cv.fabricCanvas.setActiveObject(t);
                cv.fabricCanvas.renderAll();
                this.selectedObject = t;
                this.updateUI();
                if (t.canvas) { t.set('fontFamily', fontFamily); t.setCoords(); t.canvas.requestRenderAll(); }
            }).catch(() => {});
        },

        // ═══════════════════════════════════════════════
        // IMAGE
        // ═══════════════════════════════════════════════
        _addImageToCanvas(key, url) {
            const cv = this.canvases[key];
            if (!cv || (this._config.multiCanvas && !this.canvasEnabled[key])) return Promise.reject(new Error('Canvas disabled'));
            return new Promise((resolve) => {
                fabric.Image.fromURL(url, img => {
                    const fc = cv.fabricCanvas;
                    const canvasW = fc.width, canvasH = fc.height;
                    const s = Math.min(canvasW / img.width, canvasH / img.height) * 0.8;
                    const userImages = fc.getObjects().filter(o => o._isUserImage);
                    const offset = (userImages.length % 8) * 22;
                    img.set({
                        left: (canvasW - img.width * s) / 2 + offset,
                        top: (canvasH - img.height * s) / 2 + offset,
                        scaleX: s, scaleY: s, cornerStyle: 'circle', cornerSize: 12,
                        transparentCorners: false, borderColor: '#378ADD', cornerColor: '#378ADD',
                        hasControls: true, hasBorders: true, selectable: true, _isUserImage: true,
                        objectCaching: true, lockScalingFlip: true, uniformScaling: true
                    });
                    if (this._config.hasMasks) {
                        const firstKey = Object.keys(this.imageTypes)[0];
                        if (key === firstKey) {
                            const clipGroup = this._createCombinedClipPath(key, cv.scaleFactor);
                            if (clipGroup) img.set('clipPath', clipGroup);
                        }
                    }
                    fc.add(img);
                    if (this._enforceZOrder) this._enforceZOrder(key);
                    fc.setActiveObject(img);
                    fc.renderAll();
                    img.setCoords();
                    cv.imgObj = img;
                    this.imgScales[key] = s;
                    this.updateUI();
                    resolve(img);
                }, { crossOrigin: 'anonymous' });
            });
        },

        async handleFileUpload(input) {
            const files = Array.from(input.files);
            if (!files.length) return;
            const key = this.activeCanvas;
            if (this._config.multiCanvas && !this.canvasEnabled[key]) return;
            this.isUploading = true;
            this.updateUI();
            try {
                for (const file of files) {
                    const optimized = await this._processImage(file);
                    this.canvasImages[key] = optimized.dataUrl;
                    const imgObj = await this._addImageToCanvas(key, optimized.dataUrl);
                    const fd = new FormData();
                    fd.append('photo', file);
                    fd.append('canvas_key', key);
                    fd.append('_token', this._config.csrfToken);
                    const res = await fetch(this._config.uploadRoute, { method: 'POST', body: fd });
                    const dat = await res.json();
                    if (dat.success) {
                        this.uploadIds[key] = dat.upload_id;
                        if (imgObj) imgObj._uploadId = dat.upload_id;
                        this._saveCanvasState(key);
                    }
                }
            } catch (e) { console.error('Upload error:', e); }
            finally { this.isUploading = false; input.value = ''; this.updateUI(); }
        },

        _processImage(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const MAX = 1600;
                        let w = img.width, h = img.height;
                        if (w > MAX || h > MAX) {
                            if (w > h) { h = Math.round((h * MAX) / w); w = MAX; }
                            else { w = Math.round((w * MAX) / h); h = MAX; }
                        }
                        const c = document.createElement('canvas');
                        c.width = w; c.height = h;
                        c.getContext('2d').drawImage(img, 0, 0, w, h);
                        resolve({ dataUrl: c.toDataURL('image/jpeg', 0.88), width: w, height: h });
                    };
                    img.onerror = reject;
                    img.src = e.target.result;
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        },

        updateImageScale(val) {
            const cv = this.canvases[this.activeCanvas]; if (!cv?.imgObj) return;
            this.imgScales[this.activeCanvas] = val;
            cv.imgObj.set({ scaleX: parseFloat(val), scaleY: parseFloat(val) });
            cv.imgObj.setCoords(); cv.fabricCanvas.requestRenderAll();
        },

        // ═══════════════════════════════════════════════
        // REMOVE / CLEAR
        // ═══════════════════════════════════════════════
        handleRemove() {
            if (this._config.multiCanvas && this.canvasEnabled[this.activeCanvas] === false) return;
            const cv = this.canvases[this.activeCanvas]; if (!cv) return;
            if (this.selectedObject) {
                cv.fabricCanvas.remove(this.selectedObject);
                cv.fabricCanvas.discardActiveObject();
                this.selectedObject = null;
                const ti = document.getElementById('text-input'); if (ti) ti.value = '';
            } else {
                const userImages = cv.fabricCanvas.getObjects().filter(o => o._isUserImage);
                if (userImages.length > 0) cv.fabricCanvas.remove(userImages[userImages.length - 1]);
            }
            const remainingImages = cv.fabricCanvas.getObjects().filter(o => o._isUserImage);
            if (remainingImages.length === 0) { this.canvasImages[this.activeCanvas] = null; this.uploadIds[this.activeCanvas] = null; }
            if (this._enforceZOrder) this._enforceZOrder(this.activeCanvas);
            cv.fabricCanvas.renderAll();
            this.updateUI();
        },

        clearAll() {
            const msg = this._config.multiCanvas
                ? 'Are you sure you want to clear all designs across all pages?'
                : 'Are you sure you want to clear all designs?';
            if (!confirm(msg)) return;
            Object.keys(this.canvases).forEach(key => {
                const cv = this.canvases[key];
                if (!cv?.fabricCanvas) return;
                cv.fabricCanvas.getObjects().forEach(o => {
                    if (o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg)
                        cv.fabricCanvas.remove(o);
                });
                cv.fabricCanvas.renderAll();
                this._saveCanvasState(key);
            });
            this.updateUI();
        },

        // ═══════════════════════════════════════════════
        // SUBMIT
        // ═══════════════════════════════════════════════
        submitAllCanvases() {
            if (this.isSavingComposite) return;
            const hasUpload = Object.values(this.uploadIds).some(id => id !== null) ||
                Object.keys(this.canvases).some(k => this.canvases[k].fabricCanvas.getObjects().some(o => o._isUserText || o._isTemplateText));
            if (!hasUpload) {
                document.getElementById('upload_ids_field').value = JSON.stringify({});
                document.getElementById('checkout-form').submit();
                return;
            }
            this.isSavingComposite = true;
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="animate-spin" data-lucide="loader-2"></i> Saving...';
            if (window.lucide) lucide.createIcons();
            const ids = {};
            const uploadPromises = Object.keys(this.canvases).map(async key => {
                const cv = this.canvases[key];
                if (!cv || (this._config.multiCanvas && !this.canvasEnabled[key])) return;
                const hasEdit = this.canvasImages[key] !== null || cv.fabricCanvas.getObjects().some(o => o._isUserText || o._isTemplateText);
                if (!hasEdit) return;
                cv.fabricCanvas.discardActiveObject();
                if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', false));
                cv.fabricCanvas.renderAll();
                const b64 = cv.fabricCanvas.toDataURL({ format: 'jpeg', quality: 0.9, multiplier: 2 });
                if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', true));
                cv.fabricCanvas.renderAll();
                const res = await fetch(this._config.uploadCompositeRoute, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this._config.csrfToken },
                    body: JSON.stringify({ image_data: b64, canvas_key: key }),
                });
                const dat = await res.json();
                if (dat.success) ids[key] = dat.upload_id;
            });
            Promise.all(uploadPromises).then(() => {
                document.getElementById('upload_ids_field').value = JSON.stringify(ids);
                document.getElementById('checkout-form').submit();
            }).catch(err => {
                console.error(err); this.isSavingComposite = false; btn.disabled = false;
                btn.innerHTML = '<i data-lucide="save"></i> Add to cart';
                if (window.lucide) lucide.createIcons();
            });
        },

        // ═══════════════════════════════════════════════
        // STATE PERSISTENCE
        // ═══════════════════════════════════════════════
        _saveCanvasState(key) {
            const cv = this.canvases[key]; if (!cv) return;
            const objects = cv.fabricCanvas.getObjects().filter(o => o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg);
            const data = {
                objects: objects.map(o => o.toObject(['_isUserImage', '_isUserText', '_isTemplateText', '_isTemplateImage', '_isTemplateSvg', '_uploadId'])),
                imgScale: this.imgScales[key], uploadId: this.uploadIds[key]
            };
            localStorage.setItem(this._config.storagePrefix + '_' + this.productId + '_' + key, JSON.stringify(data));
        },

        _loadCanvasState(key) {
            const saved = localStorage.getItem(this._config.storagePrefix + '_' + this.productId + '_' + key);
            if (!saved) return;
            try {
                const data = JSON.parse(saved);
                const cv = this.canvases[key]; const fc = cv.fabricCanvas;
                this.imgScales[key] = data.imgScale || 1;
                this.uploadIds[key] = data.uploadId;
                if (data.objects && data.objects.length > 0) {
                    fabric.util.enlivenObjects(data.objects, (objs) => {
                        objs.forEach(obj => {
                            obj.set({
                                selectable: true, evented: true, hasControls: true,
                                lockScalingFlip: true, uniformScaling: true, cornerSize: 12,
                                transparentCorners: false, borderColor: '#378ADD', cornerColor: '#378ADD', cornerStyle: 'circle'
                            });
                            if (this._config.hasMasks) {
                                const firstKey = Object.keys(this.imageTypes)[0];
                                if (key === firstKey) {
                                    const clipGroup = this._createCombinedClipPath(key, cv.scaleFactor);
                                    if (clipGroup) obj.set('clipPath', clipGroup);
                                }
                            }
                            if (obj._isUserImage) { cv.imgObj = obj; this.canvasImages[key] = true; }
                            fc.add(obj);
                        });
                        if (this._enforceZOrder) this._enforceZOrder(key);
                        fc.renderAll();
                        this.updateUI();
                    });
                }
            } catch (e) { console.error('Restore error:', e); }
        },

        // ═══════════════════════════════════════════════
        // LAYERS PANEL
        // ═══════════════════════════════════════════════
        renderLayersPanel() {
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            const listEl = document.getElementById('layers-list');
            if (!cv || !cv.fabricCanvas || !listEl) return;
            const objects = cv.fabricCanvas.getObjects().filter(o =>
                o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg
            );
            if (objects.length === 0) {
                listEl.innerHTML = '<div class="flex flex-col items-center justify-center py-8 text-slate-400 space-y-2"><i data-lucide="layers" class="w-8 h-8 opacity-40"></i><p class="text-xs font-semibold">No layers added yet</p><p class="text-[10px] text-slate-400">Add photos, text or templates to manage layers</p></div>';
                if (window.lucide) window.lucide.createIcons();
                return;
            }
            const reversed = [...objects].reverse();
            listEl.innerHTML = '';
            reversed.forEach((obj, displayIndex) => {
                const isSelected = this.selectedObject === obj;
                const layerItem = document.createElement('div');
                layerItem.className = 'group flex items-center justify-between gap-2.5 p-3 rounded-2xl border transition-all duration-200 cursor-pointer ' +
                    (isSelected ? 'bg-purple-50/90 border-purple-300 shadow-sm ring-2 ring-purple-500/20' : 'bg-slate-50/80 border-slate-200/80 hover:bg-slate-100/80');
                layerItem.draggable = true;
                var iconHtml = '', labelText = '', badgeText = 'Layer', badgeColorClass = 'text-purple-500';
                if (obj._isUserImage || obj._isTemplateImage) {
                    var src = obj._element ? obj._element.src : (obj.src || '');
                    iconHtml = src ? '<img src="' + src + '" class="w-9 h-9 object-cover rounded-xl border border-slate-200/80 shrink-0 shadow-2xs">' : '<div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="image" class="w-4 h-4"></i></div>';
                    labelText = obj._isTemplateImage ? (obj.label || 'Template Photo') : 'Photo Layer';
                    badgeText = 'Image'; badgeColorClass = 'text-pink-500';
                } else if (obj._isUserText || obj._isTemplateText) {
                    iconHtml = '<div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="type" class="w-4 h-4"></i></div>';
                    labelText = obj.text ? (obj.text.length > 16 ? obj.text.substring(0, 16) + '...' : obj.text) : 'Text Layer';
                    badgeText = 'Text'; badgeColorClass = 'text-purple-500';
                } else if (obj._isTemplateSvg) {
                    iconHtml = '<div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="sparkles" class="w-4 h-4"></i></div>';
                    labelText = obj.label || 'Design SVG'; badgeText = 'Graphic'; badgeColorClass = 'text-indigo-500';
                }
                layerItem.innerHTML = '<div class="flex items-center gap-2 min-w-0 flex-1"><div class="cursor-grab active:cursor-grabbing text-slate-300 group-hover:text-slate-500 shrink-0 px-0.5" title="Drag to reorder"><i data-lucide="grip-vertical" class="w-4 h-4"></i></div>' + iconHtml + '<div class="flex-1 min-w-0"><p class="text-xs font-bold ' + (isSelected ? 'text-purple-900' : 'text-slate-700') + ' truncate">' + labelText + '</p><span class="text-[10px] font-extrabold ' + badgeColorClass + ' uppercase tracking-wider">' + badgeText + '</span></div></div><div class="flex items-center gap-1 shrink-0"><button type="button" class="move-up-btn p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-100/60 transition-colors" title="Bring Forward"><i data-lucide="chevron-up" class="w-4 h-4"></i></button><button type="button" class="move-down-btn p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-100/60 transition-colors" title="Send Backward"><i data-lucide="chevron-down" class="w-4 h-4"></i></button><button type="button" class="delete-layer-btn p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-100/60 transition-colors" title="Delete Layer"><i data-lucide="trash-2" class="w-4 h-4"></i></button></div>';
                layerItem.addEventListener('click', (e) => {
                    if (e.target.closest('button')) return;
                    cv.fabricCanvas.setActiveObject(obj); cv.fabricCanvas.renderAll();
                    this.selectedObject = obj; this.updateUI();
                });
                var upBtn = layerItem.querySelector('.move-up-btn');
                var downBtn = layerItem.querySelector('.move-down-btn');
                var deleteBtn = layerItem.querySelector('.delete-layer-btn');
                if (upBtn) upBtn.onclick = (e) => { e.stopPropagation(); this.moveLayerUp(obj); };
                if (downBtn) downBtn.onclick = (e) => { e.stopPropagation(); this.moveLayerDown(obj); };
                if (deleteBtn) deleteBtn.onclick = (e) => {
                    e.stopPropagation(); cv.fabricCanvas.remove(obj);
                    if (this.selectedObject === obj) this.selectedObject = null;
                    cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.updateUI();
                };
                layerItem.addEventListener('dragstart', (e) => { e.dataTransfer.setData('text/plain', displayIndex.toString()); layerItem.classList.add('opacity-40'); });
                layerItem.addEventListener('dragend', () => { layerItem.classList.remove('opacity-40'); });
                layerItem.addEventListener('dragover', (e) => { e.preventDefault(); layerItem.classList.add('border-purple-500', 'bg-purple-50/50'); });
                layerItem.addEventListener('dragleave', () => { layerItem.classList.remove('border-purple-500', 'bg-purple-50/50'); });
                layerItem.addEventListener('drop', (e) => {
                    e.preventDefault(); layerItem.classList.remove('border-purple-500', 'bg-purple-50/50');
                    var fromIndex = parseInt(e.dataTransfer.getData('text/plain'));
                    if (!isNaN(fromIndex) && fromIndex !== displayIndex) this.reorderLayers(fromIndex, displayIndex);
                });
                listEl.appendChild(layerItem);
            });
            if (window.lucide) window.lucide.createIcons();
        },

        moveLayerUp(obj) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !obj) return;
            cv.fabricCanvas.bringForward(obj);
            if (this._enforceZOrder) this._enforceZOrder(key);
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.renderLayersPanel();
        },

        moveLayerDown(obj) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !obj) return;
            cv.fabricCanvas.sendBackwards(obj);
            if (this._enforceZOrder) this._enforceZOrder(key);
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.renderLayersPanel();
        },

        reorderLayers(fromDisplayIndex, toDisplayIndex) {
            const key = this.activeCanvas; const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas) return;
            const allObjects = cv.fabricCanvas.getObjects();
            const manageableObjects = allObjects.filter(o => o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg);
            if (manageableObjects.length === 0) return;
            const reversed = [...manageableObjects].reverse();
            if (fromDisplayIndex < 0 || fromDisplayIndex >= reversed.length) return;
            if (toDisplayIndex < 0 || toDisplayIndex >= reversed.length) return;
            const [movedObj] = reversed.splice(fromDisplayIndex, 1);
            reversed.splice(toDisplayIndex, 0, movedObj);
            const newCanvasOrder = [...reversed].reverse();
            const originalIndices = manageableObjects.map(o => allObjects.indexOf(o)).sort((a, b) => a - b);
            newCanvasOrder.forEach((obj, idx) => {
                const targetFabricIndex = originalIndices[idx] !== undefined ? originalIndices[idx] : idx;
                cv.fabricCanvas.moveTo(obj, targetFabricIndex);
            });
            if (this._enforceZOrder) this._enforceZOrder(key);
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.renderLayersPanel();
        },

        // ═══════════════════════════════════════════════
        // MASK SYSTEM (used by multi-canvas pages with masks)
        // ═══════════════════════════════════════════════
        _maybeClip(obj, spec, sf, key) {
            if (spec.ignoreMask) return;
            const clipGroup = this._createCombinedClipPath(key, sf);
            if (clipGroup) obj.set('clipPath', clipGroup);
        },

        _createMaskObject(m, sf, extraProps) {
            if (!m) return null;
            extraProps = extraProps || {};
            const type = m.type || 'rectangle';
            const base = {
                left: m.left * sf, top: m.top * sf,
                scaleX: (m.scaleX || 1) * sf, scaleY: (m.scaleY || 1) * sf,
                angle: m.angle || 0, originX: 'left', originY: 'top'
            };
            Object.assign(base, extraProps);
            switch (type) {
                case 'square': case 'rectangle': case 'diamond': return new fabric.Rect(Object.assign(base, { width: m.width, height: m.height }));
                case 'circle': return new fabric.Circle(Object.assign(base, { radius: m.radius }));
                case 'ellipse': case 'oval': return new fabric.Ellipse(Object.assign(base, { rx: m.rx, ry: m.ry }));
                case 'triangle': return new fabric.Triangle(Object.assign(base, { width: m.width, height: m.height }));
                case 'custom_polygon': return new fabric.Polygon(m.points || [], base);
                default: return new fabric.Rect(Object.assign(base, { width: m.width || 100, height: m.height || 100 }));
            }
        },

        _createCombinedClipPath(key, sf) {
            const mData = this.allMaskData[key] || {};
            const masks = mData.masks || this.allMaskData.masks;
            if (!Array.isArray(masks) || masks.length === 0) return null;
            const clipObjects = masks.map(m => this._createMaskObject(m, sf, { absolutePositioned: true, strokeWidth: 0 })).filter(Boolean);
            if (clipObjects.length === 0) return null;
            if (clipObjects.length === 1) { clipObjects[0].set({ absolutePositioned: true }); return clipObjects[0]; }
            return new fabric.Group(clipObjects, { absolutePositioned: true });
        },

        _enforceZOrder(key) {
            const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            [o => o._isUserImage, o => o._isTemplateImage, o => o._isTemplateSvg, o => o._isTemplateText, o => o._isUserText]
                .forEach(pred => fc.getObjects().filter(pred).forEach(o => o.bringToFront()));
            if (cv.maskGuides) cv.maskGuides.forEach(g => g.bringToFront());
            fc.renderAll();
        },

        // ═══════════════════════════════════════════════
        // MULTI-CANVAS PAGE SWITCHING
        // ═══════════════════════════════════════════════
        switchCanvas(key) {
            const currentCv = this.canvases[this.activeCanvas];
            if (currentCv && currentCv.fabricCanvas) { currentCv.fabricCanvas.discardActiveObject(); currentCv.fabricCanvas.renderAll(); }
            this.selectedObject = null;
            const ti = document.getElementById('text-input'); if (ti) ti.value = '';
            this.activeCanvas = key;
            this.updateUI();
            setTimeout(() => {
                const newCv = this.canvases[key];
                if (newCv && newCv.fabricCanvas) { newCv.fabricCanvas.calcOffset(); newCv.fabricCanvas.discardActiveObject().renderAll(); }
            }, 50);
        },

        // ═══════════════════════════════════════════════
        // UTIL
        // ═══════════════════════════════════════════════
        _debounce(fn, delay) {
            var t;
            return function() { var a = arguments; clearTimeout(t); t = setTimeout(function() { fn.apply(this, a); }.bind(this), delay); }.bind(this);
        }
    };
}

// ═══════════════════════════════════════════════
// SHARED DRAWER TOGGLE FUNCTIONS
// ═══════════════════════════════════════════════
function toggleTextDrawer(focusTarget) {
    var textDrawer = document.getElementById('text-studio-drawer');
    var layersDrawer = document.getElementById('layers-studio-drawer');
    var templatesDrawer = document.getElementById('templates-studio-drawer');
    if (layersDrawer) layersDrawer.classList.add('hidden');
    if (templatesDrawer) templatesDrawer.classList.add('hidden');
    if (!textDrawer) return;
    if (textDrawer.classList.contains('hidden')) {
        textDrawer.classList.remove('hidden');
        if (focusTarget === 'font') document.getElementById('font-family-select')?.focus();
        else document.getElementById('text-input')?.focus();
    } else { textDrawer.classList.add('hidden'); }
}

function toggleLayersDrawer() {
    var layersDrawer = document.getElementById('layers-studio-drawer');
    var textDrawer = document.getElementById('text-studio-drawer');
    var templatesDrawer = document.getElementById('templates-studio-drawer');
    if (textDrawer) textDrawer.classList.add('hidden');
    if (templatesDrawer) templatesDrawer.classList.add('hidden');
    if (!layersDrawer) return;
    if (layersDrawer.classList.contains('hidden')) {
        layersDrawer.classList.remove('hidden');
        if (typeof customizer !== 'undefined') customizer.renderLayersPanel();
    } else { layersDrawer.classList.add('hidden'); }
}

function toggleTemplatesDrawer() {
    var templatesDrawer = document.getElementById('templates-studio-drawer');
    var layersDrawer = document.getElementById('layers-studio-drawer');
    var textDrawer = document.getElementById('text-studio-drawer');
    if (layersDrawer) layersDrawer.classList.add('hidden');
    if (textDrawer) textDrawer.classList.add('hidden');
    if (!templatesDrawer) return;
    if (templatesDrawer.classList.contains('hidden')) {
        templatesDrawer.classList.remove('hidden');
        if (typeof customizer !== 'undefined') customizer._initTemplates();
    } else { templatesDrawer.classList.add('hidden'); }
}
