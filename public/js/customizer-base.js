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
        // PAGE / CANVAS SWITCHING
        // ═══════════════════════════════════════════════
        switchCanvas(key) {
            if (!key || !this.canvases[key]) return;
            this.activeCanvas = key;

            const keys = Object.keys(this.imageTypes || this.canvases);
            keys.forEach(k => {
                const wrapper = document.getElementById('canvas-wrapper-' + k);
                const btnIcon = document.getElementById('page-btn-' + k);
                const btnNav = document.querySelector('.thumb-nav-btn[data-key="' + k + '"]');

                if (k === key) {
                    if (wrapper) {
                        wrapper.classList.remove('canvas-hidden', 'hidden');
                        wrapper.style.display = 'block';
                        wrapper.style.visibility = 'visible';
                        wrapper.style.pointerEvents = 'auto';
                        wrapper.style.zIndex = '10';
                        wrapper.style.opacity = '1';
                    }
                    if (btnIcon) {
                        btnIcon.classList.remove('text-slate-500', 'shadow-2xs', 'border', 'border-slate-200/90');
                        btnIcon.classList.add('text-brand-600', 'shadow-md', 'border-2', 'border-brand-500', 'scale-105');
                    }
                    if (btnNav) {
                        const span = btnNav.querySelector('span');
                        if (span) {
                            span.classList.remove('text-slate-500');
                            span.classList.add('font-black', 'text-brand-600');
                        }
                    }
                } else {
                    if (wrapper) {
                        wrapper.classList.add('canvas-hidden');
                        wrapper.style.display = 'none';
                        wrapper.style.visibility = 'hidden';
                        wrapper.style.pointerEvents = 'none';
                        wrapper.style.zIndex = '-1';
                        wrapper.style.opacity = '0';
                    }
                    if (btnIcon) {
                        btnIcon.classList.remove('text-brand-600', 'shadow-md', 'border-2', 'border-brand-500', 'scale-105');
                        btnIcon.classList.add('text-slate-500', 'shadow-2xs', 'border', 'border-slate-200/90');
                    }
                    if (btnNav) {
                        const span = btnNav.querySelector('span');
                        if (span) {
                            span.classList.remove('font-black', 'text-brand-600');
                            span.classList.add('font-bold', 'text-slate-500');
                        }
                    }
                }
            });

            const cv = this.canvases[key];
            if (cv && cv.fabricCanvas) {
                const active = cv.fabricCanvas.getActiveObject();
                this.selectedObject = active || null;
                cv.fabricCanvas.calcOffset();
                cv.fabricCanvas.requestRenderAll();
            }

            this.renderLayersPanel();
            this.updateUI();
        },

        // ═══════════════════════════════════════════════
        // TOOLBAR SYNC
        // ═══════════════════════════════════════════════
        _syncToolbarToSelection(obj) {
            this._syncShapeMaskLibrary();
            this._syncShapeLibraryBorderControls();
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
                if (this._config.hasMasks && this._maybeClip) this._maybeClip(t, null, cv.scaleFactor || 1, key);
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
                const corsOpts = (!url || url.startsWith('data:') || url.startsWith('blob:')) ? {} : { crossOrigin: 'anonymous' };
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
                    this._maybeClip(img, null, cv.scaleFactor || 1, key);
                    fc.add(img);
                    if (this._enforceZOrder) this._enforceZOrder(key);
                    fc.setActiveObject(img);
                    fc.renderAll();
                    img.setCoords();
                    cv.imgObj = img;
                    this.imgScales[key] = s;
                    this.updateUI();
                    resolve(img);
                }, corsOpts);
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
                    fd.append('image', file);
                    fd.append('photo', file);
                    fd.append('canvas_key', key);
                    fd.append('_token', this._config.csrfToken);
                    const res = await fetch(this._config.uploadRoute, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this._config.csrfToken },
                        body: fd
                    });
                    const dat = await res.json();
                    if (dat && dat.success) {
                        this.uploadIds[key] = dat.upload_id;
                        if (imgObj) imgObj._uploadId = dat.upload_id;
                        this._saveCanvasState(key);
                    }
                }
            } catch (e) { console.error('Upload error:', e); }
            finally { this.isUploading = false; input.value = ''; this.updateUI(); }
        },

        async handleFileDrop(e) {
            const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            if (!files.length) return;
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas || (this._config.multiCanvas && !this.canvasEnabled[key])) return;

            const fc = cv.fabricCanvas;
            const rect = fc.upperCanvasEl.getBoundingClientRect();
            const dropX = (e.clientX - rect.left) / cv.scaleFactor;
            const dropY = (e.clientY - rect.top) / cv.scaleFactor;

            this.isUploading = true;
            this.updateUI();

            try {
                for (const file of files) {
                    const optimized = await this._processImage(file);
                    this.canvasImages[key] = optimized.dataUrl;

                    const targetObj = fc.getObjects().find(o => {
                        if (!o.visible || o.name?.startsWith('mask_guide_')) return false;
                        return o.containsPoint(new fabric.Point(dropX * cv.scaleFactor, dropY * cv.scaleFactor));
                    });

                    const dropCors = (!optimized.dataUrl || optimized.dataUrl.startsWith('data:') || optimized.dataUrl.startsWith('blob:')) ? {} : { crossOrigin: 'anonymous' };
                    fabric.Image.fromURL(optimized.dataUrl, img => {
                        const s = Math.min(fc.width / img.width, fc.height / img.height) * 0.7;
                        img.set({
                            left: dropX,
                            top: dropY,
                            originX: 'center',
                            originY: 'center',
                            scaleX: s,
                            scaleY: s,
                            cornerStyle: 'circle', cornerSize: 12,
                            transparentCorners: false, borderColor: '#378ADD', cornerColor: '#378ADD',
                            hasControls: true, hasBorders: true, selectable: true, _isUserImage: true,
                            objectCaching: true, lockScalingFlip: true, uniformScaling: true
                        });
                        if (targetObj && targetObj._isShape) {
                            targetObj.clone((shapeClip) => {
                                shapeClip.set({ absolutePositioned: true, evented: false, selectable: false });
                                img.set({ clipPath: shapeClip });
                                targetObj.set({ fill: 'transparent', stroke: (targetObj.stroke && targetObj.stroke !== 'transparent') ? targetObj.stroke : '#6FB63A', strokeWidth: 3 });
                                fc.add(img);
                                const shapeIdx = fc.getObjects().indexOf(targetObj);
                                if (shapeIdx > 0) fc.moveTo(img, shapeIdx);
                                fc.setActiveObject(img);
                                fc.renderAll();
                                img.setCoords();
                                this._saveCanvasState(key);
                                if (this.pushHistoryState) this.pushHistoryState(key);
                                this.updateUI();
                            });
                            return;
                        }
                        if (this._config.hasMasks && this._maybeClip) this._maybeClip(img, null, cv.scaleFactor || 1, key);
                        fc.add(img);
                        if (this._enforceZOrder) this._enforceZOrder(key);
                        fc.setActiveObject(img);
                        fc.renderAll();
                        img.setCoords();
                        this._saveCanvasState(key);
                        if (this.pushHistoryState) this.pushHistoryState(key);
                        this.updateUI();
                    }, dropCors);

                    const fd = new FormData();
                    fd.append('image', file);
                    fd.append('photo', file);
                    fd.append('canvas_key', key);
                    fd.append('_token', this._config.csrfToken);
                    const res = await fetch(this._config.uploadRoute, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this._config.csrfToken },
                        body: fd
                    });
                    const dat = await res.json();
                    if (dat && dat.success) {
                        this.uploadIds[key] = dat.upload_id;
                    }
                }
            } catch (err) {
                console.error('File drop upload error:', err);
            } finally {
                this.isUploading = false;
                this.updateUI();
            }
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
                    if (o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg || o._isShape)
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
            this.isSavingComposite = true;
            const btn = document.getElementById('submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="animate-spin" data-lucide="loader-2"></i> Saving...';
            }
            if (window.lucide) lucide.createIcons();
            const ids = {};
            const keys = Object.keys(this.canvases);

            const uploadPromises = keys.map(async key => {
                const cv = this.canvases[key];
                if (!cv || !cv.fabricCanvas) return;

                const wrapper = document.getElementById('canvas-wrapper-' + key);
                const prevDisplay = wrapper ? wrapper.style.display : null;
                const prevVisibility = wrapper ? wrapper.style.visibility : null;

                // Temporarily unhide canvas container to ensure offscreen rendering & toDataURL work properly
                if (wrapper) {
                    wrapper.style.display = 'block';
                    wrapper.style.visibility = 'visible';
                }

                cv.fabricCanvas.discardActiveObject();
                if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', false));
                
                cv.fabricCanvas.calcOffset();
                cv.fabricCanvas.renderAll();

                let b64 = null;
                try {
                    b64 = cv.fabricCanvas.toDataURL({ format: 'jpeg', quality: 0.9, multiplier: 2 });
                } catch (e) {
                    console.error('DataURL capture error for ' + key + ':', e);
                    // Tainted canvas fallback: temporarily clear background image to export user objects
                    const bg = cv.fabricCanvas.backgroundImage;
                    cv.fabricCanvas.backgroundImage = null;
                    cv.fabricCanvas.renderAll();
                    try {
                        b64 = cv.fabricCanvas.toDataURL({ format: 'jpeg', quality: 0.9, multiplier: 2 });
                    } catch (err2) {
                        console.error('Fallback export failed for ' + key + ':', err2);
                    }
                    cv.fabricCanvas.backgroundImage = bg;
                    cv.fabricCanvas.renderAll();
                }

                if (cv.maskGuides) cv.maskGuides.forEach(g => g.set('visible', true));
                cv.fabricCanvas.renderAll();

                // Restore original wrapper display state if it wasn't the active canvas
                if (wrapper && key !== this.activeCanvas) {
                    wrapper.style.display = prevDisplay || 'none';
                    wrapper.style.visibility = prevVisibility || 'hidden';
                }

                if (!b64 || b64 === 'data:,') return;

                try {
                    const res = await fetch(this._config.uploadCompositeRoute, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this._config.csrfToken },
                        body: JSON.stringify({ image_data: b64, canvas_key: key }),
                    });
                    const dat = await res.json();
                    if (dat && dat.success) {
                        ids[key] = dat.upload_id;
                    }
                } catch (err) {
                    console.error('Failed to upload composite for ' + key + ':', err);
                }
            });

            Promise.all(uploadPromises).then(() => {
                const uploadIdsInput = document.getElementById('upload_ids_field');
                if (uploadIdsInput) uploadIdsInput.value = JSON.stringify(ids);
                const form = document.getElementById('checkout-form');
                if (form) form.submit();
            }).catch(err => {
                console.error('submitAllCanvases error:', err);
                this.isSavingComposite = false;
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i data-lucide="shopping-bag"></i> Add to cart';
                }
                if (window.lucide) lucide.createIcons();
            });
        },

        // ═══════════════════════════════════════════════
        // STATE PERSISTENCE
        // ═══════════════════════════════════════════════
        _saveCanvasState(key) {
            const cv = this.canvases[key]; if (!cv) return;
            const objects = cv.fabricCanvas.getObjects().filter(o => o._isUserImage || o._isUserText || o._isTemplateText || o._isTemplateImage || o._isTemplateSvg || o._isShape);
            const data = {
                objects: objects.map(o => o.toObject(['_isUserImage', '_isUserText', '_isTemplateText', '_isTemplateImage', '_isTemplateSvg', '_uploadId', '_isShape', '_shapeMaskType', '_shapeBorderShow', '_shapeBorderColor', '_shapeBorderWidth', '_shapeBorderStyle'])),
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
                            if (obj._isUserImage) {
                                if (!obj._shapeMaskType && !obj._parentShape) {
                                    this._maybeClip(obj, null, cv.scaleFactor || 1, key);
                                }
                                cv.imgObj = obj;
                                this.canvasImages[key] = true;
                            }
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
        // GROUP & MULTI-SELECTION ENGINE (CANVA/FIGMA STYLE)
        // ═══════════════════════════════════════════════
        groupSelected() {
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const activeObj = fc.getActiveObject();
            if (!activeObj || activeObj.type !== 'activeSelection') return;

            const existingGroups = fc.getObjects().filter(o => o.type === 'group' && o._isUserGroup);
            const groupNum = existingGroups.length + 1;
            const groupLabel = 'Group ' + groupNum;

            const group = activeObj.toGroup();
            group.set({
                _isUserGroup: true,
                label: groupLabel,
                id: 'group_' + Date.now(),
                cornerStyle: 'circle',
                cornerSize: 12,
                borderColor: '#6FB63A',
                cornerColor: '#6FB63A',
                transparentCorners: false
            });

            fc.setActiveObject(group);
            fc.requestRenderAll();
            this._saveCanvasState(key);
            if (this.pushHistoryState) this.pushHistoryState(key);
            this.updateUI();
        },

        ungroupSelected() {
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const activeObj = fc.getActiveObject() || this.selectedObject;
            if (!activeObj || activeObj.type !== 'group') return;

            const activeSelection = activeObj.toActiveSelection();
            fc.setActiveObject(activeSelection);
            fc.requestRenderAll();
            this._saveCanvasState(key);
            if (this.pushHistoryState) this.pushHistoryState(key);
            this.updateUI();
        },

        isEditingGroup: false,
        editingGroupObj: null,

        enterGroupEditMode(groupObj) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const group = groupObj || fc.getActiveObject() || this.selectedObject;
            if (!group || group.type !== 'group') return;

            this.isEditingGroup = true;
            this.editingGroupObj = group;

            fc.getObjects().forEach(o => {
                if (o !== group && (!group.contains || !group.contains(o))) {
                    o.set({ opacity: 0.35, selectable: false, evented: false });
                }
            });

            group.set({ subTargetCheck: true, interactive: true });
            fc.renderAll();

            const banner = document.getElementById('group-edit-banner');
            if (banner) banner.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
            this.updateUI();
        },

        exitGroupEditMode() {
            if (!this.isEditingGroup || !this.editingGroupObj) return;
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;

            fc.getObjects().forEach(o => {
                o.set({ opacity: 1, selectable: true, evented: true });
            });

            if (this.editingGroupObj) {
                this.editingGroupObj.set({ subTargetCheck: false, interactive: false });
                fc.setActiveObject(this.editingGroupObj);
            }

            this.isEditingGroup = false;
            this.editingGroupObj = null;

            const banner = document.getElementById('group-edit-banner');
            if (banner) banner.classList.add('hidden');

            fc.renderAll();
            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
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
                (!o.name || !o.name.startsWith('mask_guide_')) && o !== cv.fabricCanvas.backgroundImage
            );
            if (objects.length === 0) {
                listEl.innerHTML = '<div class="flex flex-col items-center justify-center py-8 text-slate-400 space-y-2"><i data-lucide="layers" class="w-8 h-8 opacity-40"></i><p class="text-xs font-semibold">No layers added yet</p><p class="text-[10px] text-slate-400">Add photos, text, shapes or templates to manage layers</p></div>';
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
                if (obj.type === 'group') {
                    iconHtml = '<div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="folder" class="w-4 h-4"></i></div>';
                    labelText = obj.label || 'Group Layer';
                    badgeText = 'Group (' + (obj._objects ? obj._objects.length : 0) + ')';
                    badgeColorClass = 'text-indigo-600';
                } else if (obj._isUserImage || obj._isTemplateImage) {
                    var src = obj._element ? obj._element.src : (obj.src || '');
                    iconHtml = src ? '<img src="' + src + '" class="w-9 h-9 object-cover rounded-xl border border-slate-200/80 shrink-0 shadow-2xs">' : '<div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="image" class="w-4 h-4"></i></div>';
                    labelText = obj.label || (obj._isTemplateImage ? 'Template Photo' : 'Photo Layer');
                    badgeText = 'Image'; badgeColorClass = 'text-pink-500';
                } else if (obj._isUserText || obj._isTemplateText) {
                    iconHtml = '<div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="type" class="w-4 h-4"></i></div>';
                    labelText = obj.text ? (obj.text.length > 16 ? obj.text.substring(0, 16) + '...' : obj.text) : 'Text Layer';
                    badgeText = 'Text'; badgeColorClass = 'text-purple-500';
                } else {
                    iconHtml = '<div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0"><i data-lucide="shapes" class="w-4 h-4"></i></div>';
                    labelText = obj.label || (obj.type ? (obj.type.charAt(0).toUpperCase() + obj.type.slice(1)) : 'Shape Layer');
                    badgeText = 'Vector'; badgeColorClass = 'text-brand-600';
                }

                var ungroupBtnHtml = obj.type === 'group' ? '<button type="button" class="ungroup-layer-btn p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-100/60 transition-colors" title="Ungroup"><i data-lucide="folder-minus" class="w-4 h-4"></i></button>' : '';

                layerItem.innerHTML = '<div class="flex items-center gap-2 min-w-0 flex-1"><div class="cursor-grab active:cursor-grabbing text-slate-300 group-hover:text-slate-500 shrink-0 px-0.5" title="Drag to reorder"><i data-lucide="grip-vertical" class="w-4 h-4"></i></div>' + iconHtml + '<div class="flex-1 min-w-0"><p class="text-xs font-bold ' + (isSelected ? 'text-purple-900' : 'text-slate-700') + ' truncate">' + labelText + '</p><span class="text-[10px] font-extrabold ' + badgeColorClass + ' uppercase tracking-wider">' + badgeText + '</span></div></div><div class="flex items-center gap-1 shrink-0">' + ungroupBtnHtml + '<button type="button" class="move-up-btn p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-100/60 transition-colors" title="Bring Forward"><i data-lucide="chevron-up" class="w-4 h-4"></i></button><button type="button" class="move-down-btn p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-100/60 transition-colors" title="Send Backward"><i data-lucide="chevron-down" class="w-4 h-4"></i></button><button type="button" class="delete-layer-btn p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-100/60 transition-colors" title="Delete Layer"><i data-lucide="trash-2" class="w-4 h-4"></i></button></div>';
                layerItem.addEventListener('click', (e) => {
                    if (e.target.closest('button')) return;
                    cv.fabricCanvas.setActiveObject(obj); cv.fabricCanvas.renderAll();
                    this.selectedObject = obj; this.updateUI();
                });
                var ungroupBtn = layerItem.querySelector('.ungroup-layer-btn');
                var upBtn = layerItem.querySelector('.move-up-btn');
                var downBtn = layerItem.querySelector('.move-down-btn');
                var deleteBtn = layerItem.querySelector('.delete-layer-btn');
                if (ungroupBtn) ungroupBtn.onclick = (e) => { e.stopPropagation(); cv.fabricCanvas.setActiveObject(obj); this.ungroupSelected(); };
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
            const manageableObjects = allObjects.filter(o => (!o.name || !o.name.startsWith('mask_guide_')) && o !== cv.fabricCanvas.backgroundImage);
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

        setShapeColor(colorVal) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject;
            if (!obj) return;
            obj.set('fill', colorVal);
            cv.fabricCanvas.renderAll();
            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
        },

        removeShapeFill() {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject;
            if (!obj) return;
            obj.set({
                fill: 'transparent',
                stroke: (obj.stroke && obj.stroke !== 'transparent') ? obj.stroke : '#6FB63A',
                strokeWidth: obj.strokeWidth || 3
            });
            cv.fabricCanvas.renderAll();
            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
        },

        fillShapeWithImage(input) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas || !input.files || !input.files[0]) return;
            const fc = cv.fabricCanvas;
            const shapeObj = fc.getActiveObject() || this.selectedObject;
            if (!shapeObj) return;

            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = (e) => {
                fabric.Image.fromURL(e.target.result, (img) => {
                    shapeObj.clone((shapeClip) => {
                        const sOriginX = shapeObj.originX || 'left';
                        const sOriginY = shapeObj.originY || 'top';
                        const center = shapeObj.getCenterPoint ? shapeObj.getCenterPoint() : { x: shapeObj.left, y: shapeObj.top };
                        shapeClip.set({
                            absolutePositioned: true,
                            left: (sOriginX === 'center') ? center.x : shapeObj.left,
                            top: (sOriginY === 'center') ? center.y : shapeObj.top,
                            scaleX: shapeObj.scaleX || 1,
                            scaleY: shapeObj.scaleY || 1,
                            angle: shapeObj.angle || 0,
                            originX: sOriginX,
                            originY: sOriginY,
                            evented: false,
                            selectable: false
                        });

                        const shapeW = shapeObj.width * (shapeObj.scaleX || 1);
                        const shapeH = shapeObj.height * (shapeObj.scaleY || 1);
                        const scale = Math.max(shapeW / img.width, shapeH / img.height);

                        img.set({
                            left: shapeObj.left,
                            top: shapeObj.top,
                            scaleX: scale,
                            scaleY: scale,
                            originX: shapeObj.originX || 'left',
                            originY: shapeObj.originY || 'top',
                            angle: shapeObj.angle || 0,
                            clipPath: shapeClip,
                            _isUserImage: true,
                            label: 'Image in ' + (shapeObj.label || 'Shape')
                        });

                        shapeObj.set({
                            fill: 'transparent',
                            stroke: (shapeObj.stroke && shapeObj.stroke !== 'transparent') ? shapeObj.stroke : '#6FB63A',
                            strokeWidth: shapeObj.strokeWidth || 3
                        });

                        // Link shape & image
                        const imgId = 'img_' + Date.now();
                        const shapeId = 'shape_' + Date.now();
                        img.id = imgId;
                        shapeObj.id = shapeId;
                        img._filledImageId = imgId;
                        shapeObj._filledImageId = imgId;
                        img._parentShapeId = shapeId;
                        shapeObj._parentShapeId = shapeId;

                        shapeObj._filledImage = img;
                        img._parentShape = shapeObj;
                        img._offsetX = img.left - shapeObj.left;
                        img._offsetY = img.top - shapeObj.top;

                        img._normScaleX = scale / (shapeObj.scaleX || 1);
                        img._normScaleY = scale / (shapeObj.scaleY || 1);
                        img._normOffsetX = 0;
                        img._normOffsetY = 0;

                        // Lock image for normal mode
                        img.set({
                            selectable: false,
                            evented: false
                        });

                        fc.add(img);
                        const shapeIdx = fc.getObjects().indexOf(shapeObj);
                        if (shapeIdx > 0) fc.moveTo(img, shapeIdx);
                        fc.setActiveObject(shapeObj);
                        fc.renderAll();
                        this._saveCanvasState(this.activeCanvas);
                        if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
                        input.value = '';
                        this.updateUI();
                    });
                });
            };
            reader.readAsDataURL(file);
        },

        // ═══════════════════════════════════════════════
        // IMAGE REPOSITION MODE (CANVA / FIGMA FRAME STYLE)
        // ═══════════════════════════════════════════════
        isRepositioningImage: false,
        repositioningShape: null,
        repositioningImage: null,

        enterImageRepositionMode(targetShape) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const activeObj = fc.getActiveObject() || this.selectedObject;
            const shapeObj = targetShape || (activeObj && activeObj._filledImage ? activeObj : (activeObj && activeObj._parentShape ? activeObj._parentShape : activeObj));
            if (!shapeObj) return;

            let img = shapeObj._filledImage || (shapeObj.clipPath || shapeObj._shapeMaskType ? shapeObj : null);
            if (!img) return;

            this.isRepositioningImage = true;
            this.repositioningShape = shapeObj;
            this.repositioningImage = img;

            if (shapeObj !== img) {
                // Shape frame mode
                shapeObj.set({
                    selectable: false,
                    evented: false,
                    lockMovementX: true,
                    lockMovementY: true,
                    lockScalingX: true,
                    lockScalingY: true,
                    lockRotation: true
                });

                if (img.clipPath) {
                    const sOriginX = shapeObj.originX || 'left';
                    const sOriginY = shapeObj.originY || 'top';
                    const center = shapeObj.getCenterPoint ? shapeObj.getCenterPoint() : { x: shapeObj.left, y: shapeObj.top };
                    img.clipPath.set({
                        absolutePositioned: true,
                        left: (sOriginX === 'center') ? center.x : shapeObj.left,
                        top: (sOriginY === 'center') ? center.y : shapeObj.top,
                        scaleX: shapeObj.scaleX || 1,
                        scaleY: shapeObj.scaleY || 1,
                        angle: shapeObj.angle || 0,
                        originX: sOriginX,
                        originY: sOriginY
                    });
                    img.clipPath.setCoords();
                }
            } else {
                // Direct clipPath image mode
                const currentMaskLeft = img._maskCanvasLeft !== undefined ? img._maskCanvasLeft : img.left;
                const currentMaskTop = img._maskCanvasTop !== undefined ? img._maskCanvasTop : img.top;
                const currentMaskScaleX = img._maskCanvasScaleX !== undefined ? img._maskCanvasScaleX : (img.scaleX || 1);
                const currentMaskScaleY = img._maskCanvasScaleY !== undefined ? img._maskCanvasScaleY : (img.scaleY || 1);
                const currentMaskAngle = img._maskCanvasAngle !== undefined ? img._maskCanvasAngle : (img.angle || 0);

                img._maskCanvasLeft = currentMaskLeft;
                img._maskCanvasTop = currentMaskTop;
                img._maskCanvasScaleX = currentMaskScaleX;
                img._maskCanvasScaleY = currentMaskScaleY;
                img._maskCanvasAngle = currentMaskAngle;

                if (img.clipPath) {
                    const cOriginX = (img.clipPath && img.clipPath.originX) ? img.clipPath.originX : 'center';
                    const cOriginY = (img.clipPath && img.clipPath.originY) ? img.clipPath.originY : 'center';
                    img.clipPath.set({
                        absolutePositioned: true,
                        left: currentMaskLeft,
                        top: currentMaskTop,
                        scaleX: currentMaskScaleX,
                        scaleY: currentMaskScaleY,
                        angle: currentMaskAngle,
                        originX: cOriginX,
                        originY: cOriginY
                    });
                    img.clipPath.setCoords();
                }
            }

            img.set({
                selectable: true,
                evented: true,
                lockMovementX: false,
                lockMovementY: false,
                lockScalingX: false,
                lockScalingY: false,
                lockRotation: true,
                cornerStyle: 'circle',
                cornerSize: 10,
                borderColor: '#A855F7',
                cornerColor: '#A855F7'
            });
            img.setCoords();

            fc.setActiveObject(img);
            fc.renderAll();

            const banner = document.getElementById('reposition-mode-banner');
            if (banner) banner.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
            this.updateUI();
        },

        exitImageRepositionMode() {
            if (!this.isRepositioningImage || !this.repositioningShape || !this.repositioningImage) return;
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;

            const shapeObj = this.repositioningShape;
            const img = this.repositioningImage;

            if (shapeObj !== img) {
                const sScaleX = shapeObj.scaleX || 1;
                const sScaleY = shapeObj.scaleY || 1;

                img._normOffsetX = (img.left - shapeObj.left) / sScaleX;
                img._normOffsetY = (img.top - shapeObj.top) / sScaleY;
                img._normScaleX = (img.scaleX || 1) / sScaleX;
                img._normScaleY = (img.scaleY || 1) / sScaleY;
                img._offsetX = img.left - shapeObj.left;
                img._offsetY = img.top - shapeObj.top;

                img.set({
                    selectable: false,
                    evented: false
                });

                shapeObj.set({
                    selectable: true,
                    evented: true,
                    lockMovementX: false,
                    lockMovementY: false,
                    lockScalingX: false,
                    lockScalingY: false,
                    lockRotation: false
                });
                fc.setActiveObject(shapeObj);
            } else {
                const maskLeft = img._maskCanvasLeft !== undefined ? img._maskCanvasLeft : img.left;
                const maskTop = img._maskCanvasTop !== undefined ? img._maskCanvasTop : img.top;
                const maskScaleX = img._maskCanvasScaleX !== undefined ? img._maskCanvasScaleX : (img.scaleX || 1);
                const maskScaleY = img._maskCanvasScaleY !== undefined ? img._maskCanvasScaleY : (img.scaleY || 1);

                img._normOffsetX = (img.left - maskLeft) / maskScaleX;
                img._normOffsetY = (img.top - maskTop) / maskScaleY;

                img.set({
                    selectable: true,
                    evented: true,
                    lockMovementX: false,
                    lockMovementY: false,
                    lockScalingX: false,
                    lockScalingY: false,
                    lockRotation: false,
                    borderColor: '#378ADD',
                    cornerColor: '#378ADD'
                });
                fc.setActiveObject(img);
            }

            this.isRepositioningImage = false;
            this.repositioningShape = null;
            this.repositioningImage = null;

            this._syncShapeAndImage(shapeObj);
            fc.renderAll();

            const banner = document.getElementById('reposition-mode-banner');
            if (banner) banner.classList.add('hidden');

            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
        },

        _syncShapeAndImage(shapeObj) {
            if (!shapeObj) return;

            const sScaleX = shapeObj.scaleX || 1;
            const sScaleY = shapeObj.scaleY || 1;

            if (shapeObj._filledImage) {
                const img = shapeObj._filledImage;

                if (img.clipPath) {
                    const sOriginX = shapeObj.originX || 'left';
                    const sOriginY = shapeObj.originY || 'top';
                    const center = shapeObj.getCenterPoint ? shapeObj.getCenterPoint() : { x: shapeObj.left, y: shapeObj.top };
                    img.clipPath.set({
                        absolutePositioned: true,
                        left: (sOriginX === 'center') ? center.x : shapeObj.left,
                        top: (sOriginY === 'center') ? center.y : shapeObj.top,
                        scaleX: sScaleX,
                        scaleY: sScaleY,
                        angle: shapeObj.angle || 0,
                        originX: sOriginX,
                        originY: sOriginY
                    });
                    img.clipPath.setCoords();
                }

                if (!this.isRepositioningImage) {
                    const rad = (shapeObj.angle || 0) * (Math.PI / 180);
                    const cos = Math.cos(rad);
                    const sin = Math.sin(rad);
                    const offX = (img._normOffsetX !== undefined ? img._normOffsetX * sScaleX : (img._offsetX || 0));
                    const offY = (img._normOffsetY !== undefined ? img._normOffsetY * sScaleY : (img._offsetY || 0));

                    const normScaleX = (img._normScaleX !== undefined) ? img._normScaleX : ((img.scaleX || 1) / sScaleX);
                    const normScaleY = (img._normScaleY !== undefined) ? img._normScaleY : ((img.scaleY || 1) / sScaleY);
                    img._normScaleX = normScaleX;
                    img._normScaleY = normScaleY;

                    img.set({
                        left: shapeObj.left + (offX * cos - offY * sin),
                        top: shapeObj.top + (offX * sin + offY * cos),
                        scaleX: normScaleX * sScaleX,
                        scaleY: normScaleY * sScaleY,
                        angle: shapeObj.angle || 0
                    });
                    img.setCoords();
                }
            } else if (shapeObj._shapeMaskType) {
                const img = shapeObj;
                const center = img.getCenterPoint ? img.getCenterPoint() : { x: img.left, y: img.top };
                const maskLeft = img._maskCanvasLeft !== undefined ? img._maskCanvasLeft : center.x;
                const maskTop = img._maskCanvasTop !== undefined ? img._maskCanvasTop : center.y;
                const maskScaleX = img._maskCanvasScaleX !== undefined ? img._maskCanvasScaleX : (img.scaleX || 1);
                const maskScaleY = img._maskCanvasScaleY !== undefined ? img._maskCanvasScaleY : (img.scaleY || 1);
                const cOriginX = (img.clipPath && img.clipPath.originX) ? img.clipPath.originX : 'center';
                const cOriginY = (img.clipPath && img.clipPath.originY) ? img.clipPath.originY : 'center';

                if (this.isRepositioningImage) {
                    if (img.clipPath) {
                        img.clipPath.set({
                            absolutePositioned: true,
                            left: maskLeft,
                            top: maskTop,
                            scaleX: maskScaleX,
                            scaleY: maskScaleY,
                            angle: img._maskCanvasAngle !== undefined ? img._maskCanvasAngle : (img.angle || 0),
                            originX: cOriginX,
                            originY: cOriginY
                        });
                        img.clipPath.setCoords();
                    }
                } else if (img.clipPath) {
                    const normOffX = img._normOffsetX || 0;
                    const normOffY = img._normOffsetY || 0;
                    img.clipPath.set({
                        absolutePositioned: true,
                        left: center.x - (normOffX * (img.scaleX || 1)),
                        top: center.y - (normOffY * (img.scaleY || 1)),
                        scaleX: img.scaleX || 1,
                        scaleY: img.scaleY || 1,
                        angle: img.angle || 0,
                        originX: cOriginX,
                        originY: cOriginY
                    });
                    img.clipPath.setCoords();
                }
            }

            if (shapeObj._borderOutlineObj) {
                const b = shapeObj._borderOutlineObj;
                const center = shapeObj.getCenterPoint ? shapeObj.getCenterPoint() : { x: shapeObj.left, y: shapeObj.top };
                b.set({
                    left: center.x,
                    top: center.y,
                    scaleX: sScaleX,
                    scaleY: sScaleY,
                    angle: shapeObj.angle || 0,
                    originX: 'center',
                    originY: 'center'
                });
                b.setCoords();
            }

            if (shapeObj.canvas) shapeObj.canvas.requestRenderAll();
        },

        // ═══════════════════════════════════════════════
        // SHAPE BORDER CONTROL ENGINE
        // ═══════════════════════════════════════════════
        _getActiveMaskedOrShapeObject() {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return null;
            const fc = cv.fabricCanvas;
            const activeObj = fc.getActiveObject() || this.selectedObject;
            if (!activeObj) return null;

            if (activeObj._filledImage) return activeObj;
            if (activeObj._parentShape) return activeObj._parentShape;
            if (activeObj.clipPath || activeObj._shapeMaskType) return activeObj;
            return activeObj._isShape ? activeObj : null;
        },

        _applyShapeBorder(targetObj) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const obj = targetObj || this._getActiveMaskedOrShapeObject();
            if (!obj) return;

            const show = obj._shapeBorderShow !== false && (obj._shapeBorderShow || (obj._shapeBorderWidth && obj._shapeBorderWidth > 0));
            const color = obj._shapeBorderColor || '#378ADD';
            const width = parseFloat(obj._shapeBorderWidth) || (show ? 3 : 0);
            const style = obj._shapeBorderStyle || 'solid';

            obj._shapeBorderShow = show;
            obj._shapeBorderColor = color;
            obj._shapeBorderWidth = width;
            obj._shapeBorderStyle = style;

            let dashArray = null;
            if (style === 'dashed') dashArray = [6, 6];
            else if (style === 'dotted') dashArray = [2, 4];

            if (obj._isShape || obj._filledImage || (!obj.clipPath && obj.stroke !== undefined)) {
                obj.set({
                    stroke: show && width > 0 ? color : 'transparent',
                    strokeWidth: show ? width : 0,
                    strokeDashArray: show ? dashArray : null
                });
                obj.setCoords();
            } else if (obj.clipPath || obj._shapeMaskType) {
                const center = obj.getCenterPoint ? obj.getCenterPoint() : { x: obj.left, y: obj.top };
                if (show && width > 0) {
                    if (!obj._borderOutlineObj || !fc.contains(obj._borderOutlineObj)) {
                        const maskType = obj._shapeMaskType || 'rect';
                        const borderShape = this._createShapeMaskGeometry(maskType, obj.width, obj.height);
                        if (borderShape) {
                            borderShape.set({
                                fill: 'transparent',
                                stroke: color,
                                strokeWidth: width,
                                strokeDashArray: dashArray,
                                left: center.x,
                                top: center.y,
                                scaleX: obj.scaleX || 1,
                                scaleY: obj.scaleY || 1,
                                angle: obj.angle || 0,
                                originX: 'center',
                                originY: 'center',
                                selectable: false,
                                evented: false,
                                _isShapeBorderObj: true
                            });
                            obj._borderOutlineObj = borderShape;
                            borderShape._parentImageObj = obj;
                            fc.add(borderShape);
                            const imgIdx = fc.getObjects().indexOf(obj);
                            if (imgIdx >= 0) fc.moveTo(borderShape, imgIdx + 1);
                        }
                    } else {
                        obj._borderOutlineObj.set({
                            stroke: color,
                            strokeWidth: width,
                            strokeDashArray: dashArray,
                            visible: true,
                            left: center.x,
                            top: center.y,
                            scaleX: obj.scaleX || 1,
                            scaleY: obj.scaleY || 1,
                            angle: obj.angle || 0,
                            originX: 'center',
                            originY: 'center'
                        });
                        obj._borderOutlineObj.setCoords();
                    }
                } else if (obj._borderOutlineObj) {
                    obj._borderOutlineObj.set({ visible: false });
                }
            }

            fc.renderAll();
            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
        },

        setShapeBorderShow(show) {
            const obj = this._getActiveMaskedOrShapeObject();
            if (!obj) return;
            obj._shapeBorderShow = !!show;
            this._applyShapeBorder(obj);
        },

        setShapeBorderColor(color) {
            const obj = this._getActiveMaskedOrShapeObject();
            if (!obj) return;
            obj._shapeBorderColor = color;
            if (obj._shapeBorderShow === undefined) obj._shapeBorderShow = true;
            this._applyShapeBorder(obj);
        },

        setShapeBorderWidth(width) {
            const obj = this._getActiveMaskedOrShapeObject();
            if (!obj) return;
            const w = parseFloat(width) || 0;
            obj._shapeBorderWidth = w;
            if (w > 0 && obj._shapeBorderShow === undefined) obj._shapeBorderShow = true;
            this._applyShapeBorder(obj);
        },

        setShapeBorderStyle(style) {
            const obj = this._getActiveMaskedOrShapeObject();
            if (!obj) return;
            obj._shapeBorderStyle = style || 'solid';
            this._applyShapeBorder(obj);
        },

        // ═══════════════════════════════════════════════
        // NON-DESTRUCTIVE IMAGE CROP ENGINE (CANVA / FIGMA STYLE)
        // ═══════════════════════════════════════════════
        isCroppingImage: false,
        croppingImageObj: null,
        cropRectObj: null,
        cropOriginalProps: null,
        cropAspectRatio: 'free',

        enterCropMode(targetImg) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const img = targetImg || fc.getActiveObject() || this.selectedObject;
            if (!img || !(img._isUserImage || img._isTemplateImage || img.type === 'image')) return;

            this.isCroppingImage = true;
            this.croppingImageObj = img;
            this.cropOriginalProps = {
                cropX: img.cropX || 0,
                cropY: img.cropY || 0,
                width: img.width,
                height: img.height,
                scaleX: img.scaleX,
                scaleY: img.scaleY,
                left: img.left,
                top: img.top,
                angle: img.angle || 0
            };

            const naturalW = img._element ? (img._element.naturalWidth || img.width) : img.width;
            const naturalH = img._element ? (img._element.naturalHeight || img.height) : img.height;

            const scaleX = img.scaleX || 1;
            const scaleY = img.scaleY || 1;

            const currentCropX = img.cropX || 0;
            const currentCropY = img.cropY || 0;
            const currentW = img.width || naturalW;
            const currentH = img.height || naturalH;

            img.set({
                selectable: false,
                evented: false
            });

            const cropRect = new fabric.Rect({
                left: img.left,
                top: img.top,
                width: currentW * scaleX,
                height: currentH * scaleY,
                fill: 'rgba(236, 72, 153, 0.1)',
                stroke: '#EC4899',
                strokeWidth: 2,
                strokeDashArray: [6, 6],
                cornerStyle: 'circle',
                cornerSize: 12,
                borderColor: '#EC4899',
                cornerColor: '#EC4899',
                transparentCorners: false,
                lockRotation: true,
                hasRotatingPoint: false,
                _isCropBox: true
            });

            this.cropRectObj = cropRect;
            fc.add(cropRect);
            fc.setActiveObject(cropRect);

            const updateImageFromCropRect = () => {
                if (!this.croppingImageObj || !this.cropRectObj) return;
                const mImg = this.croppingImageObj;
                const cRect = this.cropRectObj;

                const mScaleX = mImg.scaleX || 1;
                const mScaleY = mImg.scaleY || 1;

                const fullLeft = mImg.left - ((mImg.cropX || 0) * mScaleX);
                const fullTop = mImg.top - ((mImg.cropY || 0) * mScaleY);
                const fullRight = fullLeft + (naturalW * mScaleX);
                const fullBottom = fullTop + (naturalH * mScaleY);

                let boxW = cRect.width * (cRect.scaleX || 1);
                let boxH = cRect.height * (cRect.scaleY || 1);

                let newLeft = Math.max(fullLeft, Math.min(cRect.left, fullRight - 20));
                let newTop = Math.max(fullTop, Math.min(cRect.top, fullBottom - 20));
                boxW = Math.min(boxW, fullRight - newLeft);
                boxH = Math.min(boxH, fullBottom - newTop);

                cRect.set({
                    left: newLeft,
                    top: newTop,
                    width: boxW,
                    height: boxH,
                    scaleX: 1,
                    scaleY: 1
                });
                cRect.setCoords();

                const newCropX = Math.round((newLeft - fullLeft) / mScaleX);
                const newCropY = Math.round((newTop - fullTop) / mScaleY);
                const newW = Math.round(boxW / mScaleX);
                const newH = Math.round(boxH / mScaleY);

                mImg.set({
                    cropX: newCropX,
                    cropY: newCropY,
                    width: newW,
                    height: newH,
                    left: newLeft,
                    top: newTop
                });
                mImg.setCoords();
                fc.renderAll();
            };

            cropRect.on('moving', updateImageFromCropRect);
            cropRect.on('scaling', updateImageFromCropRect);

            fc.renderAll();

            const banner = document.getElementById('crop-mode-banner');
            if (banner) banner.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
            this.updateUI();
        },

        setCropAspectRatio(ratio) {
            this.cropAspectRatio = ratio;
            const img = this.croppingImageObj;
            const cRect = this.cropRectObj;
            if (!img || !cRect) return;

            const naturalW = img._element ? (img._element.naturalWidth || img.width) : img.width;
            const naturalH = img._element ? (img._element.naturalHeight || img.height) : img.height;
            const mScaleX = img.scaleX || 1;
            const mScaleY = img.scaleY || 1;

            let targetW = naturalW;
            let targetH = naturalH;

            switch (ratio) {
                case '1:1':
                    targetW = Math.min(naturalW, naturalH);
                    targetH = targetW;
                    break;
                case '4:3':
                    targetW = naturalW;
                    targetH = Math.round(naturalW * (3 / 4));
                    if (targetH > naturalH) { targetH = naturalH; targetW = Math.round(naturalH * (4 / 3)); }
                    break;
                case '16:9':
                    targetW = naturalW;
                    targetH = Math.round(naturalW * (9 / 16));
                    if (targetH > naturalH) { targetH = naturalH; targetW = Math.round(naturalH * (16 / 9)); }
                    break;
                case 'original':
                    targetW = naturalW;
                    targetH = naturalH;
                    break;
                case 'free':
                default:
                    return;
            }

            const cropX = Math.max(0, Math.round((naturalW - targetW) / 2));
            const cropY = Math.max(0, Math.round((naturalH - targetH) / 2));

            const fullLeft = img.left - ((img.cropX || 0) * mScaleX);
            const fullTop = img.top - ((img.cropY || 0) * mScaleY);

            img.set({
                cropX: cropX,
                cropY: cropY,
                width: targetW,
                height: targetH,
                left: fullLeft + (cropX * mScaleX),
                top: fullTop + (cropY * mScaleY)
            });
            img.setCoords();

            cRect.set({
                left: img.left,
                top: img.top,
                width: targetW * mScaleX,
                height: targetH * mScaleY,
                scaleX: 1,
                scaleY: 1
            });
            cRect.setCoords();

            const cv = this.canvases[this.activeCanvas];
            if (cv) cv.fabricCanvas.renderAll();
        },

        applyCrop() {
            if (!this.isCroppingImage || !this.croppingImageObj) return;
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;

            const img = this.croppingImageObj;
            img.set({
                selectable: true,
                evented: true,
                borderColor: '#378ADD',
                cornerColor: '#378ADD',
                lockRotation: false
            });
            img.setCoords();

            if (this.cropRectObj) {
                fc.remove(this.cropRectObj);
                this.cropRectObj = null;
            }

            this.isCroppingImage = false;
            this.croppingImageObj = null;
            this.cropOriginalProps = null;

            const banner = document.getElementById('crop-mode-banner');
            if (banner) banner.classList.add('hidden');

            fc.setActiveObject(img);
            fc.renderAll();
            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
        },

        cancelCrop() {
            if (!this.isCroppingImage || !this.croppingImageObj || !this.cropOriginalProps) return;
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;

            const img = this.croppingImageObj;
            img.set(this.cropOriginalProps);
            img.set({
                selectable: true,
                evented: true,
                borderColor: '#378ADD',
                cornerColor: '#378ADD',
                lockRotation: false
            });
            img.setCoords();

            if (this.cropRectObj) {
                fc.remove(this.cropRectObj);
                this.cropRectObj = null;
            }

            this.isCroppingImage = false;
            this.croppingImageObj = null;
            this.cropOriginalProps = null;

            const banner = document.getElementById('crop-mode-banner');
            if (banner) banner.classList.add('hidden');

            fc.setActiveObject(img);
            fc.renderAll();
            this.updateUI();
        },

        resetCrop() {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const img = this.croppingImageObj || fc.getActiveObject() || this.selectedObject;
            if (!img || !(img._isUserImage || img._isTemplateImage || img.type === 'image')) return;

            const origW = img._element ? (img._element.naturalWidth || img.width) : img.width;
            const origH = img._element ? (img._element.naturalHeight || img.height) : img.height;

            img.set({
                cropX: 0,
                cropY: 0,
                width: origW,
                height: origH,
                selectable: true,
                evented: true
            });
            img.setCoords();

            if (this.cropRectObj) {
                fc.remove(this.cropRectObj);
                this.cropRectObj = null;
            }

            this.isCroppingImage = false;
            this.croppingImageObj = null;
            this.cropOriginalProps = null;

            const banner = document.getElementById('crop-mode-banner');
            if (banner) banner.classList.add('hidden');

            fc.setActiveObject(img);
            fc.renderAll();
            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
        },

        // ═══════════════════════════════════════════════
        // IMAGE SHAPE MASKING ENGINE (CANVA / FIGMA STYLE)
        // ═══════════════════════════════════════════════
        _createShapeMaskGeometry(shapeType, width, height) {
            const w = width || 200;
            const h = height || 200;
            const halfW = w / 2;
            const halfH = h / 2;

            switch (shapeType) {
                case 'circle':
                    return new fabric.Circle({ radius: Math.min(w, h) / 2, originX: 'center', originY: 'center' });
                case 'oval':
                    return new fabric.Ellipse({ rx: w / 2, ry: h / 2, originX: 'center', originY: 'center' });
                case 'rounded-rect':
                    return new fabric.Rect({ width: w, height: h, rx: 24, ry: 24, originX: 'center', originY: 'center' });
                case 'triangle':
                    return new fabric.Triangle({ width: w, height: h, originX: 'center', originY: 'center' });
                case 'diamond':
                    return new fabric.Polygon([
                        { x: 0, y: -halfH }, { x: halfW, y: 0 }, { x: 0, y: halfH }, { x: -halfW, y: 0 }
                    ], { originX: 'center', originY: 'center' });
                case 'pentagon':
                    return new fabric.Polygon(Array.from({length:5}, (_,i) => {
                        const a = (Math.PI * 2 * i / 5) - Math.PI / 2;
                        return { x: halfW * Math.cos(a), y: halfH * Math.sin(a) };
                    }), { originX: 'center', originY: 'center' });
                case 'hexagon':
                    return new fabric.Polygon(Array.from({length:6}, (_,i) => {
                        const a = (Math.PI * 2 * i / 6);
                        return { x: halfW * Math.cos(a), y: halfH * Math.sin(a) };
                    }), { originX: 'center', originY: 'center' });
                case 'star':
                    const pts = [];
                    for (let i = 0; i < 10; i++) {
                        const r = (i % 2 === 0) ? halfW : (halfW * 0.45);
                        const a = (Math.PI * 2 * i / 10) - Math.PI / 2;
                        pts.push({ x: r * Math.cos(a), y: r * Math.sin(a) });
                    }
                    return new fabric.Polygon(pts, { originX: 'center', originY: 'center' });
                case 'heart':
                    const pathData = 'M 50 90 C 25 70 0 50 0 30 C 0 12 12 0 25 0 C 35 0 45 7 50 18 C 55 7 65 0 75 0 C 88 0 100 12 100 30 C 100 50 75 70 50 90 Z';
                    return new fabric.Path(pathData, { left: 0, top: 0, originX: 'center', originY: 'center', scaleX: w / 100, scaleY: h / 90 });
                case 'rect':
                default:
                    return new fabric.Rect({ width: w, height: h, originX: 'center', originY: 'center' });
            }
        },

        applyShapeMaskToSelectedImage(shapeType) {
            const cv = this.canvases[this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const img = fc.getActiveObject() || this.selectedObject;
            if (!img || !(img._isUserImage || img._isTemplateImage || img.type === 'image')) return;

            // Switching the mask shape must not change position, scale, rotation or crop.
            // Only the clipPath geometry (and its matching border outline) is replaced.
            if (shapeType === 'none' || shapeType === 'rect') {
                img.set({ clipPath: null, _shapeMaskType: null });
                img._maskCanvasLeft = undefined;
                img._maskCanvasTop = undefined;
                img._maskCanvasScaleX = undefined;
                img._maskCanvasScaleY = undefined;
                img._maskCanvasAngle = undefined;
                img._normOffsetX = undefined;
                img._normOffsetY = undefined;
            } else {
                const maskObj = this._createShapeMaskGeometry(shapeType, img.width, img.height);
                if (maskObj) {
                    const center = img.getCenterPoint ? img.getCenterPoint() : { x: img.left, y: img.top };
                    maskObj.set({
                        absolutePositioned: true,
                        left: center.x,
                        top: center.y,
                        scaleX: img.scaleX || 1,
                        scaleY: img.scaleY || 1,
                        angle: img.angle || 0,
                        originX: 'center',
                        originY: 'center'
                    });
                    img.set({
                        clipPath: maskObj,
                        _shapeMaskType: shapeType
                    });
                    img._maskCanvasLeft = center.x;
                    img._maskCanvasTop = center.y;
                    img._maskCanvasScaleX = img.scaleX || 1;
                    img._maskCanvasScaleY = img.scaleY || 1;
                    img._maskCanvasAngle = img.angle || 0;
                    img._normOffsetX = 0;
                    img._normOffsetY = 0;
                }
            }

            // Border outline is geometry-specific: drop the stale one and rebuild it from
            // the preserved border settings so the outline follows the new shape.
            if (img._borderOutlineObj) {
                fc.remove(img._borderOutlineObj);
                img._borderOutlineObj = null;
            }
            if (img._shapeBorderShow && img._shapeBorderWidth > 0) {
                this._applyShapeBorder(img);
            }

            fc.renderAll();
            this._saveCanvasState(this.activeCanvas);
            if (this.pushHistoryState) this.pushHistoryState(this.activeCanvas);
            this.updateUI();
            this._syncShapeMaskLibrary();
        },

        // ═══════════════════════════════════════════════
        // SHAPE MASK LIBRARY ACTIVE-STATE INDICATOR
        // Highlights which shape mask is applied to the selected image.
        // Purely a visual indicator inside the drawer — never touches the image itself.
        // ═══════════════════════════════════════════════
        _syncShapeMaskLibrary() {
            const buttons = document.querySelectorAll('.shape-mask-btn');
            if (!buttons.length) return;

            const cv = this.canvases[this.activeCanvas];
            const activeObj = (cv && cv.fabricCanvas)
                ? (cv.fabricCanvas.getActiveObject() || this.selectedObject)
                : this.selectedObject;
            const isImage = !!(activeObj && (activeObj._isUserImage || activeObj._isTemplateImage || activeObj.type === 'image'));

            // Only images get an active shape; an unmasked image reads as the default 'rect'.
            const activeShape = isImage ? (activeObj._shapeMaskType || 'rect') : null;

            let changed = false;
            buttons.forEach(btn => {
                const isActive = activeShape !== null && btn.dataset.shape === activeShape;
                if (btn.classList.contains('shape-mask-active') !== isActive) changed = true;
                btn.classList.toggle('shape-mask-active', isActive);

                let check = btn.querySelector('.shape-mask-check');
                if (isActive && !check) {
                    check = document.createElement('span');
                    check.className = 'shape-mask-check absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-md border border-white z-10';
                    check.innerHTML = '<i data-lucide="check" class="w-2.5 h-2.5"></i>';
                    btn.appendChild(check);
                } else if (!isActive && check) {
                    check.remove();
                }
            });
            if (changed && window.lucide) window.lucide.createIcons();
        },

        // Reflects the selected shape's border state into the Shapes Library border controls.
        _syncShapeLibraryBorderControls() {
            const showCb = document.getElementById('shape-lib-border-show');
            if (!showCb) return; // Shapes Library drawer not on this page
            const colorInput = document.getElementById('shape-lib-border-color');
            const colorVal = document.getElementById('shape-lib-border-color-val');
            const widthSlider = document.getElementById('shape-lib-border-width');
            const widthVal = document.getElementById('shape-lib-border-width-val');

            const obj = this._getActiveMaskedOrShapeObject ? this._getActiveMaskedOrShapeObject() : null;
            if (!obj) {
                showCb.checked = false;
                return;
            }

            const show = obj._shapeBorderShow !== false &&
                (obj._shapeBorderShow || (obj._shapeBorderWidth && obj._shapeBorderWidth > 0));
            const color = obj._shapeBorderColor ||
                (obj.stroke && obj.stroke !== 'transparent' ? obj.stroke : '#6FB63A');
            const width = obj._shapeBorderWidth !== undefined
                ? obj._shapeBorderWidth
                : (obj.strokeWidth || 3);

            showCb.checked = !!show;
            if (colorInput) colorInput.value = color;
            if (colorVal) colorVal.innerText = color;
            if (widthSlider) widthSlider.value = width;
            if (widthVal) widthVal.innerText = width + 'px';
        },

        removeShapeMaskFromImage() {
            this.applyShapeMaskToSelectedImage('none');
        },

        // ═══════════════════════════════════════════════
        // HISTORY (UNDO / REDO)
        // ═══════════════════════════════════════════════
        undoStack: {},
        redoStack: {},
        _isHistoryNav: false,

        pushHistoryState(key) {
            if (this._isHistoryNav) return;
            const cv = this.canvases[key || this.activeCanvas];
            if (!cv || !cv.fabricCanvas) return;
            if (!this.undoStack[key || this.activeCanvas]) this.undoStack[key || this.activeCanvas] = [];
            if (!this.redoStack[key || this.activeCanvas]) this.redoStack[key || this.activeCanvas] = [];

            const stateJson = JSON.stringify(cv.fabricCanvas.toJSON(['_isUserText', '_isUserImage', '_isTemplateText', '_isTemplateImage', '_isTemplateSvg', '_isShape', '_isShapeBorderObj', 'label', 'id', 'cropX', 'cropY', '_shapeMaskType', '_shapeBorderShow', '_shapeBorderColor', '_shapeBorderWidth', '_shapeBorderStyle', '_normOffsetX', '_normOffsetY', '_normScaleX', '_normScaleY', '_filledImageId', '_parentShapeId', 'clipPath']));
            const stack = this.undoStack[key || this.activeCanvas];
            if (stack.length === 0 || stack[stack.length - 1] !== stateJson) {
                stack.push(stateJson);
                if (stack.length > 30) stack.shift();
                this.redoStack[key || this.activeCanvas] = [];
            }
            this.updateUI();
        },

        undo() {
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas) return;
            const stack = this.undoStack[key] || [];
            if (stack.length <= 1) return;

            if (!this.redoStack[key]) this.redoStack[key] = [];
            const currentState = stack.pop();
            this.redoStack[key].push(currentState);

            const prevState = stack[stack.length - 1];
            this._isHistoryNav = true;
            cv.fabricCanvas.loadFromJSON(prevState, () => {
                cv.fabricCanvas.renderAll();
                this._isHistoryNav = false;
                this._saveCanvasState(key);
                this.updateUI();
            });
        },

        redo() {
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas) return;
            const stack = this.redoStack[key] || [];
            if (stack.length === 0) return;

            const nextState = stack.pop();
            if (!this.undoStack[key]) this.undoStack[key] = [];
            this.undoStack[key].push(nextState);

            this._isHistoryNav = true;
            cv.fabricCanvas.loadFromJSON(nextState, () => {
                cv.fabricCanvas.renderAll();
                this._isHistoryNav = false;
                this._saveCanvasState(key);
                this.updateUI();
            });
        },

        // ═══════════════════════════════════════════════
        // OBJECT ALIGNMENT & DISTRIBUTION
        // ═══════════════════════════════════════════════
        alignSelected(dir) {
            const key = this.activeCanvas;
            const cv = this.canvases[key];
            if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const obj = fc.getActiveObject() || this.selectedObject;
            if (!obj) return;

            const W = fc.width;
            const H = fc.height;

            switch (dir) {
                case 'left': obj.set({ left: 0, originX: 'left' }); break;
                case 'center': obj.set({ left: W / 2, originX: 'center' }); break;
                case 'right': obj.set({ left: W, originX: 'right' }); break;
                case 'top': obj.set({ top: 0, originY: 'top' }); break;
                case 'middle': obj.set({ top: H / 2, originY: 'center' }); break;
                case 'bottom': obj.set({ top: H, originY: 'bottom' }); break;
            }
            obj.setCoords();
            fc.renderAll();
            this._saveCanvasState(key);
            this.pushHistoryState(key);
        },

        // ═══════════════════════════════════════════════
        // LAYER ARRANGE & OBJECT ACTIONS
        // ═══════════════════════════════════════════════
        bringToFrontSelected() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            cv.fabricCanvas.bringToFront(obj);
            if (this._enforceZOrder) this._enforceZOrder(key);
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.renderLayersPanel();
        },

        sendToBackSelected() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            cv.fabricCanvas.sendToBack(obj);
            if (this._enforceZOrder) this._enforceZOrder(key);
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.renderLayersPanel();
        },

        duplicateSelected() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const activeObj = fc.getActiveObject() || this.selectedObject;
            if (!activeObj) return;

            activeObj.clone((cloned) => {
                fc.discardActiveObject();
                cloned.set({
                    left: activeObj.left + 20,
                    top: activeObj.top + 20,
                    evented: true,
                    selectable: true
                });
                if (cloned.type === 'activeSelection') {
                    cloned.canvas = fc;
                    cloned.forEachObject((o) => {
                        if (this._config.hasMasks && this._maybeClip) this._maybeClip(o, null, cv.scaleFactor || 1, key);
                        fc.add(o);
                    });
                    cloned.setCoords();
                } else {
                    if (this._config.hasMasks && this._maybeClip) this._maybeClip(cloned, null, cv.scaleFactor || 1, key);
                    fc.add(cloned);
                }
                fc.setActiveObject(cloned);
                fc.requestRenderAll();
                this._saveCanvasState(key);
                this.pushHistoryState(key);
                this.updateUI();
            });
        },

        toggleLockSelected() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            const isLocked = !obj.lockMovementX;
            obj.set({
                lockMovementX: isLocked, lockMovementY: isLocked,
                lockRotation: isLocked, lockScalingX: isLocked, lockScalingY: isLocked,
                hasControls: !isLocked
            });
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.updateUI();
        },

        toggleHideSelected() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            obj.set('visible', !obj.visible);
            if (!obj.visible) cv.fabricCanvas.discardActiveObject();
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key); this.updateUI();
        },

        setObjectOpacity(val) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            obj.set('opacity', parseFloat(val));
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key);
        },

        rotateSelected(deg) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            obj.rotate((obj.angle || 0) + deg);
            obj.setCoords();
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key);
        },

        flipHSelected() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            obj.set('flipX', !obj.flipX);
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key);
        },

        flipVSelected() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const obj = cv.fabricCanvas.getActiveObject() || this.selectedObject; if (!obj) return;
            obj.set('flipY', !obj.flipY);
            cv.fabricCanvas.renderAll(); this._saveCanvasState(key);
        },

        // ═══════════════════════════════════════════════
        // SHAPES & STICKERS LIBRARY
        // ═══════════════════════════════════════════════
        addShape(shapeType, fillColor) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const center = fc.getCenter();
            const fill = fillColor || '#6FB63A';
            let shapeObj = null;

            switch (shapeType) {
                case 'rectangle':
                    shapeObj = new fabric.Rect({ left: center.left - 50, top: center.top - 35, width: 100, height: 70, fill });
                    break;
                case 'circle':
                    shapeObj = new fabric.Circle({ left: center.left - 40, top: center.top - 40, radius: 40, fill });
                    break;
                case 'triangle':
                    shapeObj = new fabric.Triangle({ left: center.left - 40, top: center.top - 40, width: 80, height: 80, fill });
                    break;
                case 'star':
                    const points = [
                        {x: 0, y: -50}, {x: 14, y: -20}, {x: 47, y: -15}, {x: 23, y: 7},
                        {x: 29, y: 40}, {x: 0, y: 25}, {x: -29, y: 40}, {x: -23, y: 7},
                        {x: -47, y: -15}, {x: -14, y: -20}
                    ];
                    shapeObj = new fabric.Polygon(points, { left: center.left, top: center.top, fill });
                    break;
                case 'heart':
                    const pathStr = 'M 50 90 C 25 70 0 50 0 30 C 0 12 12 0 25 0 C 35 0 45 7 50 18 C 55 7 65 0 75 0 C 88 0 100 12 100 30 C 100 50 75 70 50 90 Z';
                    shapeObj = new fabric.Path(pathStr, { left: center.left, top: center.top, originX: 'center', originY: 'center', scaleX: 0.8, scaleY: 0.8, fill, _isShape: true });
                    break;
            }

            if (shapeObj) {
                shapeObj.set({
                    cornerStyle: 'circle', cornerSize: 10, transparentCorners: false,
                    borderColor: '#6FB63A', cornerColor: '#6FB63A',
                    label: shapeType + ' Shape', _isShape: true
                });
                if (this._config.hasMasks && this._maybeClip) this._maybeClip(shapeObj, null, cv.scaleFactor || 1, key);
                fc.add(shapeObj);
                if (this._enforceZOrder) this._enforceZOrder(key);
                fc.setActiveObject(shapeObj);
                fc.renderAll();
                this._saveCanvasState(key);
                this.pushHistoryState(key);
                this.updateUI();
            }
        },

        // ═══════════════════════════════════════════════
        // BACKGROUND TOOLS
        // ═══════════════════════════════════════════════
        setCanvasBackground(type, val) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;

            if (type === 'solid') {
                fc.setBackgroundColor(val, fc.renderAll.bind(fc));
            } else if (type === 'gradient') {
                const grad = new fabric.Gradient({
                    type: 'linear',
                    coords: { x1: 0, y1: 0, x2: fc.width, y2: fc.height },
                    colorStops: val || [
                        { offset: 0, color: '#F1F8EA' },
                        { offset: 1, color: '#E0F0D0' }
                    ]
                });
                fc.setBackgroundColor(grad, fc.renderAll.bind(fc));
            }
            this._saveCanvasState(key);
        },

        // ═══════════════════════════════════════════════
        // QR CODE GENERATOR
        // ═══════════════════════════════════════════════
        addQrCode(text, color) {
            if (!text) return;
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(text) + '&color=' + (color || '000000').replace('#','');
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            const center = fc.getCenter();

            fabric.Image.fromURL(qrUrl, (img) => {
                img.set({
                    left: center.left - 50,
                    top: center.top - 50,
                    scaleX: 0.4,
                    scaleY: 0.4,
                    label: 'QR Code (' + text + ')',
                    _isUserImage: true,
                    cornerStyle: 'circle', cornerSize: 10, transparentCorners: false,
                    borderColor: '#6FB63A', cornerColor: '#6FB63A'
                });
                if (this._config.hasMasks && this._maybeClip) this._maybeClip(img, null, cv.scaleFactor || 1, key);
                fc.add(img);
                if (this._enforceZOrder) this._enforceZOrder(key);
                fc.setActiveObject(img);
                fc.renderAll();
                this._saveCanvasState(key);
                this.pushHistoryState(key);
                this.updateUI();
            }, { crossOrigin: 'anonymous' });
        },

        // ═══════════════════════════════════════════════
        // PRE-FLIGHT PRINT QUALITY CHECKER
        // ═══════════════════════════════════════════════
        checkPrintQuality() {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return { score: 100, dpi: 300, status: 'Optimal' };
            const userImgs = cv.fabricCanvas.getObjects().filter(o => o._isUserImage);
            if (userImgs.length === 0) return { score: 100, dpi: 300, status: 'Optimal (Vectors/Text)' };

            let lowestDpi = 300;
            userImgs.forEach(img => {
                if (img._element) {
                    const realW = img._element.naturalWidth || img.width;
                    const dispW = (img.width * img.scaleX) / cv.scaleFactor;
                    const calculatedDpi = Math.round((realW / dispW) * 72);
                    if (calculatedDpi < lowestDpi) lowestDpi = calculatedDpi;
                }
            });

            let status = 'Optimal (300+ DPI)';
            let score = 100;
            if (lowestDpi < 150) {
                status = 'Low Quality (<150 DPI)'; score = 50;
            } else if (lowestDpi < 220) {
                status = 'Good Quality (200+ DPI)'; score = 80;
            }
            return { score, dpi: lowestDpi, status };
        },

        // ═══════════════════════════════════════════════
        // CANVAS ZOOM & GUIDES
        // ═══════════════════════════════════════════════
        setZoom(level) {
            const key = this.activeCanvas; const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const containerEl = document.getElementById('canvas-container'); if (!containerEl) return;
            containerEl.style.transform = 'scale(' + level + ')';
            containerEl.style.transformOrigin = 'center center';
        },

        // ═══════════════════════════════════════════════
        // KEYBOARD SHORTCUTS & CONTEXT MENU
        // ═══════════════════════════════════════════════
        initKeyboardShortcuts() {
            window.addEventListener('keydown', (e) => {
                if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
                if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
                    e.preventDefault();
                    if (e.shiftKey) this.redo(); else this.undo();
                } else if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
                    e.preventDefault(); this.redo();
                } else if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                    e.preventDefault(); this.duplicateSelected();
                } else if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key.toLowerCase() === 'g') {
                    e.preventDefault(); this.groupSelected();
                } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'g' && e.shiftKey) {
                    e.preventDefault(); this.ungroupSelected();
                } else if (e.key === 'Escape') {
                    if (this.isEditingGroup) this.exitGroupEditMode();
                    if (this.isRepositioningImage) this.exitImageRepositionMode();
                    this.clearSelection();
                } else if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                    const obj = this.selectedObject;
                    if (obj) {
                        e.preventDefault();
                        const step = e.shiftKey ? 10 : 2;
                        if (e.key === 'ArrowUp') obj.top -= step;
                        if (e.key === 'ArrowDown') obj.top += step;
                        if (e.key === 'ArrowLeft') obj.left -= step;
                        if (e.key === 'ArrowRight') obj.left += step;
                        obj.setCoords();
                        const cv = this.canvases[this.activeCanvas];
                        if (cv && cv.fabricCanvas) cv.fabricCanvas.renderAll();
                    }
                }
            });
        },

        // ═══════════════════════════════════════════════
        // MASK SYSTEM & Z-ORDER
        // ═══════════════════════════════════════════════
        _createMaskObject(m, sf, extraProps) {
            if (!m) return null;
            sf = sf || 1;
            extraProps = extraProps || {};
            const type = m.type || 'rectangle';
            const base = Object.assign({
                left: (m.left || 0) * sf,
                top: (m.top || 0) * sf,
                scaleX: (m.scaleX || 1) * sf,
                scaleY: (m.scaleY || 1) * sf,
                angle: m.angle || 0,
                originX: 'left',
                originY: 'top'
            }, extraProps);

            switch (type) {
                case 'square':
                case 'rectangle':
                case 'diamond':
                    return new fabric.Rect(Object.assign(base, { width: (m.width || 100), height: (m.height || 100) }));
                case 'circle':
                    return new fabric.Circle(Object.assign(base, { radius: (m.radius || 50) }));
                case 'ellipse':
                case 'oval':
                    return new fabric.Ellipse(Object.assign(base, { rx: (m.rx || 50), ry: (m.ry || 50) }));
                case 'triangle':
                    return new fabric.Triangle(Object.assign(base, { width: (m.width || 100), height: (m.height || 100) }));
                case 'pentagon':
                    return new fabric.Polygon(Array.from({length:5},(_,i)=>{const a=(Math.PI*2*i/5)-Math.PI/2;return{x:55*Math.cos(a),y:55*Math.sin(a)};}), base);
                case 'hexagon':
                    return new fabric.Polygon(Array.from({length:6},(_,i)=>{const a=Math.PI*2*i/6;return{x:55*Math.cos(a),y:55*Math.sin(a)};}), base);
                case 'star':
                    return new fabric.Polygon(Array.from({length:10},(_,i)=>{const r=(i%2===0)?55:25;const a=(Math.PI*2*i/10)-Math.PI/2;return{x:r*Math.cos(a),y:r*Math.sin(a)};}), base);
                case 'heart':
                    const heartPathData = 'M 50 90 C 25 70 0 50 0 30 C 0 12 12 0 25 0 C 35 0 45 7 50 18 C 55 7 65 0 75 0 C 88 0 100 12 100 30 C 100 50 75 70 50 90 Z';
                    const hScaleX = ((m.width || 100) / 100) * (m.scaleX || 1) * sf;
                    const hScaleY = ((m.height || 90) / 90) * (m.scaleY || 1) * sf;
                    return new fabric.Path(heartPathData, Object.assign({}, base, { scaleX: hScaleX, scaleY: hScaleY }));
                case 'arch':
                    const archPathData = 'M 10 120 L 10 50 C 10 15 30 0 60 0 C 90 0 110 15 110 50 L 110 120 Z';
                    const aScaleX = ((m.width || 100) / 100) * (m.scaleX || 1) * sf;
                    const aScaleY = ((m.height || 120) / 120) * (m.scaleY || 1) * sf;
                    return new fabric.Path(archPathData, Object.assign({}, base, { scaleX: aScaleX, scaleY: aScaleY }));
                default:
                    return new fabric.Rect(Object.assign(base, { width: (m.width || 100), height: (m.height || 100) }));
            }
        },

        _createCombinedClipPath(key, sf) {
            sf = sf || 1;
            let masks = null;
            if (this.allMaskData) {
                if (key && this.allMaskData[key] && this.allMaskData[key].masks) {
                    masks = this.allMaskData[key].masks;
                } else if (this.allMaskData.masks) {
                    masks = this.allMaskData.masks;
                } else if (Array.isArray(this.allMaskData)) {
                    masks = this.allMaskData;
                } else if (key && Array.isArray(this.allMaskData[key])) {
                    masks = this.allMaskData[key];
                }
            }
            if (!Array.isArray(masks) || masks.length === 0) return null;
            const clipObjects = masks.map(m => this._createMaskObject(m, sf, { absolutePositioned: true, strokeWidth: 0 })).filter(Boolean);
            if (clipObjects.length === 0) return null;
            if (clipObjects.length === 1) { clipObjects[0].set({ absolutePositioned: true }); return clipObjects[0]; }
            return new fabric.Group(clipObjects, { absolutePositioned: true });
        },

        _maybeClip(obj, spec, sf, key) {
            if (!obj) return;
            if (spec && spec.ignoreMask) return;
            if (!this._config.hasMasks) return;
            key = key || this.activeCanvas;
            const cv = this.canvases[key];
            sf = sf || (cv ? cv.scaleFactor : 1) || 1;
            const clipGroup = this._createCombinedClipPath(key, sf);
            if (clipGroup) {
                obj.set('clipPath', clipGroup);
            }
        },

        _enforceZOrder(key) {
            const cv = this.canvases[key]; if (!cv || !cv.fabricCanvas) return;
            const fc = cv.fabricCanvas;
            if (cv.maskGuides) cv.maskGuides.forEach(g => g.bringToFront());
            fc.renderAll();
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
function closeAllDrawers() {
    ['text-studio-drawer', 'layers-studio-drawer', 'templates-studio-drawer', 'shapes-studio-drawer', 'qr-studio-drawer', 'shape-mask-drawer'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
}

function toggleShapeMaskDrawer() {
    var maskDrawer = document.getElementById('shape-mask-drawer');
    var isHidden = maskDrawer ? maskDrawer.classList.contains('hidden') : true;
    closeAllDrawers();
    if (!maskDrawer || (typeof customizer !== 'undefined' && customizer.canvasEnabled && customizer.canvasEnabled[customizer.activeCanvas] === false)) return;
    if (isHidden) {
        maskDrawer.classList.remove('hidden');
    }
    if (window.lucide) window.lucide.createIcons();
}

function toggleTextDrawer(focusTarget) {
    var textDrawer = document.getElementById('text-studio-drawer');
    var isHidden = textDrawer ? textDrawer.classList.contains('hidden') : true;
    closeAllDrawers();
    if (!textDrawer || (typeof customizer !== 'undefined' && customizer.canvasEnabled && customizer.canvasEnabled[customizer.activeCanvas] === false)) return;
    if (isHidden) {
        textDrawer.classList.remove('hidden');
        if (focusTarget === 'font') document.getElementById('font-family-select')?.focus();
        else document.getElementById('text-input')?.focus();
    }
    if (window.lucide) window.lucide.createIcons();
}

function toggleLayersDrawer() {
    var layersDrawer = document.getElementById('layers-studio-drawer');
    var isHidden = layersDrawer ? layersDrawer.classList.contains('hidden') : true;
    closeAllDrawers();
    if (!layersDrawer || (typeof customizer !== 'undefined' && customizer.canvasEnabled && customizer.canvasEnabled[customizer.activeCanvas] === false)) return;
    if (isHidden) {
        layersDrawer.classList.remove('hidden');
        if (typeof customizer !== 'undefined') customizer.renderLayersPanel();
    }
    if (window.lucide) window.lucide.createIcons();
}

function toggleTemplatesDrawer() {
    var templatesDrawer = document.getElementById('templates-studio-drawer');
    var isHidden = templatesDrawer ? templatesDrawer.classList.contains('hidden') : true;
    closeAllDrawers();
    if (!templatesDrawer || (typeof customizer !== 'undefined' && customizer.canvasEnabled && customizer.canvasEnabled[customizer.activeCanvas] === false)) return;
    if (isHidden) {
        templatesDrawer.classList.remove('hidden');
        if (typeof customizer !== 'undefined') customizer._initTemplates();
    }
    if (window.lucide) window.lucide.createIcons();
}

function toggleShapesDrawer() {
    var shapesDrawer = document.getElementById('shapes-studio-drawer');
    var isHidden = shapesDrawer ? shapesDrawer.classList.contains('hidden') : true;
    closeAllDrawers();
    if (!shapesDrawer || (typeof customizer !== 'undefined' && customizer.canvasEnabled && customizer.canvasEnabled[customizer.activeCanvas] === false)) return;
    if (isHidden) shapesDrawer.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}

function toggleQrDrawer() {
    var qrDrawer = document.getElementById('qr-studio-drawer');
    var isHidden = qrDrawer ? qrDrawer.classList.contains('hidden') : true;
    closeAllDrawers();
    if (!qrDrawer || (typeof customizer !== 'undefined' && customizer.canvasEnabled && customizer.canvasEnabled[customizer.activeCanvas] === false)) return;
    if (isHidden) qrDrawer.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}
