/* =============================================================================
 * card-editor.js — Qrinto Card Editor
 * -----------------------------------------------------------------------------
 * One reusable, data-driven editor system. A single canvas renderer draws
 * portrait cards, open two-page spreads, image/text layouts and stickers from
 * plain data. The same renderer powers the live canvas, the bottom page
 * thumbnails and the "Change layout" previews, so everything stays consistent.
 *
 * Shared editor state drives selection, context toolbars, side panels and an
 * undo/redo history. Elements use a logical coordinate system (600x900 for a
 * portrait page, 1200x900 for a spread) and scale proportionally to the
 * available workspace — the logical size never changes.
 * ========================================================================== */
(function () {
  'use strict';

  /* ------------------------------------------------------------------ utils */
  const uid   = (p) => (p || 'e') + Math.random().toString(36).slice(2, 9);
  const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
  const $     = (s, r = document) => r.querySelector(s);
  const $$    = (s, r = document) => [...r.querySelectorAll(s)];
  const clone = (o) => JSON.parse(JSON.stringify(o));
  const el    = (tag, cls, html) => { const n = document.createElement(tag); if (cls) n.className = cls; if (html != null) n.innerHTML = html; return n; };
  const QRINTO_LOGO = (window.QRINTO_LOGO_URL) || '/qrinto/public/logo/Qrinto-logo-small.png';

  const LOGICAL = { portrait: { w: 600, h: 900 }, spread: { w: 1200, h: 900 } };

  const FONTS = ['Inter', 'Poppins', 'Montserrat', 'Playfair Display', 'Dancing Script', 'Georgia', 'Arial', 'Times New Roman', 'Courier New'];

  const STICKERS = {
    Love:            ['❤️','💕','💖','💘','💝','😍','🥰','💋','🌹','💐','💌','😘'],
    Birthday:        ['🎂','🎈','🎉','🥳','🎁','🍰','🎊','🕯️','🎇','🧁','🎀','🪅'],
    'Thank You':     ['🙏','💐','🌷','😊','🌻','💚','✨','🤗','🌸','🍀'],
    Congratulations: ['🎓','🏆','🥇','🎉','👏','🌟','🎊','💫','🙌','🎯'],
    Celebration:     ['🥂','🍾','🎆','🎇','🎉','✨','🪩','🎊','🕺','💃'],
    Flowers:         ['🌸','🌷','🌹','🌻','🌺','💐','🌼','🏵️','🌿','🍀'],
    Fun:             ['😎','🤩','😜','🤪','🥸','🙃','😁','🤗','👻','🦄'],
    Animals:         ['🐶','🐱','🐰','🐻','🦊','🐼','🐨','🦁','🐯','🐷','🐸','🐥'],
    Travel:          ['✈️','🌍','🏝️','🗺️','🧳','🚗','⛰️','🏖️','🚂','🎒'],
  };

  const FILTERS = {
    Original:   'none',
    Grayscale:  'grayscale(1)',
    Warm:       'saturate(1.3) sepia(.25) hue-rotate(-8deg)',
    Cool:       'saturate(1.1) hue-rotate(15deg) brightness(1.03)',
    Vintage:    'sepia(.45) contrast(.95) saturate(1.2)',
    Brightness: 'brightness(1.2)',
    Contrast:   'contrast(1.35)',
  };
  const MAGIC = {
    Original:    'none',
    Illustrated: 'saturate(1.5) contrast(1.2)',
    Watercolor:  'saturate(1.3) brightness(1.1) blur(.4px)',
    Vintage:     'sepia(.5) contrast(1.05)',
    Sketch:      'grayscale(1) contrast(1.6) brightness(1.1)',
    Cartoon:     'saturate(2) contrast(1.4)',
  };

  const ICON = {
    image:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m4 18 5-5 4 4 3-3 4 4"/></svg>',
    plus:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 5v14M5 12h14"/></svg>',
  };

  /* ============================================================= templates */
  function starter(size) {
    return [
      { id: uid('p'), name: 'Page 1', kind: 'portrait', bg: '#ffffff', layoutId: 'p-photo', elements: [
        { id: uid(), type: 'image', x: 60,  y: 70,  w: 480, h: 380, rot: 0, src: null, filter: 'none' },
        { id: uid(), type: 'text',  x: 60,  y: 500, w: 480, h: 90,  rot: 0, text: 'Enter text...',
          style: { fontFamily: 'Dancing Script', fontSize: 44, color: '#202124', fontWeight: 400, fontStyle: 'normal', textAlign: 'center', lineHeight: 1.2, letterSpacing: 0, textTransform: 'none' } },
        brand(),
      ]},
      { id: uid('p'), name: 'Page 2', kind: 'spread', bg: '#ffffff', layoutId: 's-blank', elements: [
        { id: uid(), type: 'text', x: 720, y: 380, w: 400, h: 160, rot: 0, text: 'HAPPY\nALL OUR DAYS\nTOGETHER DAY!',
          style: { fontFamily: 'Montserrat', fontSize: 30, color: '#e11d48', fontWeight: 700, fontStyle: 'normal', textAlign: 'center', lineHeight: 1.3, letterSpacing: 1, textTransform: 'uppercase' } },
      ]},
      { id: uid('p'), name: 'Page 3', kind: 'portrait', bg: '#ffffff', layoutId: 'p-photo', elements: [
        { id: uid(), type: 'image', x: 90,  y: 120, w: 420, h: 320, rot: 0, src: null, filter: 'none' },
        { id: uid(), type: 'text',  x: 60,  y: 480, w: 480, h: 80,  rot: 0, text: 'Enter text...',
          style: { fontFamily: 'Playfair Display', fontSize: 34, color: '#202124', fontWeight: 400, fontStyle: 'italic', textAlign: 'center', lineHeight: 1.2, letterSpacing: 0, textTransform: 'none' } },
        brand(),
      ]},
    ];
    function brand() { return { id: uid(), type: 'brand', x: 230, y: 830, w: 140, h: 34, rot: 0 }; }
  }

  /* -------- layout library (previews rendered from the same element data) -- */
  const LAYOUTS = [
    { id: 'p-photo',  name: 'Photo + text', kind: 'portrait', elements: [
      { type: 'image', x: 60, y: 70, w: 480, h: 380 },
      { type: 'text',  x: 60, y: 500, w: 480, h: 90, text: 'Enter text...' }, brandDef() ] },
    { id: 'p-rows',   name: 'Three rows', kind: 'portrait', elements: [
      { type: 'image', x: 60, y: 70,  w: 230, h: 210 }, { type: 'text', x: 320, y: 120, w: 230, h: 110, text: 'Enter text' },
      { type: 'image', x: 60, y: 320, w: 230, h: 210 }, { type: 'text', x: 320, y: 370, w: 230, h: 110, text: 'Enter text' },
      { type: 'image', x: 60, y: 570, w: 230, h: 210 }, { type: 'text', x: 320, y: 620, w: 230, h: 110, text: 'Enter text' } ] },
    { id: 'p-stack',  name: 'Stacked blocks', kind: 'portrait', elements: [
      { type: 'image', x: 60, y: 60,  w: 480, h: 250 }, { type: 'text', x: 60, y: 330, w: 480, h: 70, text: 'Enter text' },
      { type: 'image', x: 60, y: 430, w: 480, h: 250 }, { type: 'text', x: 60, y: 700, w: 480, h: 70, text: 'Enter text' } ] },
    { id: 'p-hero',   name: 'Single image', kind: 'portrait', elements: [
      { type: 'image', x: 40, y: 60, w: 520, h: 560 }, { type: 'text', x: 60, y: 680, w: 480, h: 120, text: 'Enter text...' } ] },
    { id: 'p-grid',   name: 'Image grid', kind: 'portrait', elements: [
      { type: 'image', x: 60, y: 70,  w: 230, h: 200 }, { type: 'text',  x: 320, y: 70, w: 230, h: 420, text: 'Enter text' },
      { type: 'image', x: 60, y: 300, w: 230, h: 200 } ] },
    { id: 'p-cols',   name: 'Photos & text', kind: 'portrait', elements: [
      { type: 'image', x: 60, y: 70,  w: 210, h: 180 }, { type: 'text', x: 300, y: 90,  w: 240, h: 120, text: 'Enter text' },
      { type: 'image', x: 60, y: 290, w: 210, h: 180 }, { type: 'text', x: 300, y: 310, w: 240, h: 120, text: 'Enter text' },
      { type: 'image', x: 60, y: 510, w: 210, h: 180 }, { type: 'text', x: 300, y: 530, w: 240, h: 120, text: 'Enter text' } ] },
    { id: 's-blank',  name: 'Open — greeting', kind: 'spread', elements: [
      { type: 'text', x: 720, y: 380, w: 400, h: 160, text: 'Enter greeting...' } ] },
    { id: 's-photo',  name: 'Open — photo + note', kind: 'spread', elements: [
      { type: 'image', x: 90, y: 200, w: 420, h: 500 }, { type: 'text', x: 700, y: 350, w: 440, h: 200, text: 'Enter text...' } ] },
    { id: 's-two',    name: 'Open — two photos', kind: 'spread', elements: [
      { type: 'image', x: 90, y: 180, w: 420, h: 540 }, { type: 'image', x: 690, y: 180, w: 420, h: 540 } ] },
  ];
  function brandDef() { return { type: 'brand', x: 230, y: 830, w: 140, h: 34 }; }

  /* ============================================================== EDITOR */
  class QrintoEditor {
    constructor(root, opts) {
      this.root = root;
      this.opts = opts || {};
      this.state = {
        pages: starter(opts),
        current: 0,
        selectedElementId: null,
        mode: 'normal',              // normal | text | image | sticker
        history: [], histIndex: -1,
      };
      this._load();
      this._cache();
      this._buildStatic();
      this._bind();
      this._snapshot(true);
      this.render();
      this._syncLayoutPanel();
      window.addEventListener('resize', () => this._fit());
    }

    /* ------------------------------------------------------------- caching */
    _cache() {
      this.stage   = $('#qe-stage', this.root);
      this.work    = $('#qe-work', this.root);
      this.ctx     = $('#qe-ctx', this.root);
      this.thumbs  = $('#qe-thumbs', this.root);
      this.layoutSide  = $('#qe-layoutside', this.root);
      this.stickerSide = $('#qe-stickerside', this.root);
      this.file    = $('#qe-file', this.root);
    }
    get page() { return this.state.pages[this.state.current]; }
    // logical dims depend on kind (portrait/spread) AND orientation
    _logicalOf(page) {
      const land = page.orient === 'landscape';
      const w0 = land ? 900 : 600, h0 = land ? 600 : 900;
      return page.kind === 'spread' ? { w: w0 * 2, h: h0 } : { w: w0, h: h0 };
    }
    get logical() { return this._logicalOf(this.page); }
    // coordinate space that LAYOUT defs are authored in (portrait orientation)
    _kindBase(kind) { return kind === 'spread' ? { w: 1200, h: 900 } : { w: 600, h: 900 }; }
    _mapRect(r, from, to) {
      const rx = to.w / from.w, ry = to.h / from.h;
      return { x: r.x * rx, y: r.y * ry, w: r.w * rx, h: r.h * ry };
    }
    get sel() { return this.page.elements.find(e => e.id === this.state.selectedElementId); }

    /* ------------------------------------------------------- static build */
    _buildStatic() {
      // sticker categories + grid
      const cat = $('#qe-stkcat', this.root);
      Object.keys(STICKERS).forEach(c => { const o = el('option'); o.value = c; o.textContent = c; cat.appendChild(o); });
      this._stkCat = 'Love';
      this._renderStickerGrid();
    }

    _bind() {
      // header tools
      $$('.qe-tool', this.root).forEach(b => b.onclick = () => this._toolClick(b.dataset.tool));
      $('#qe-undo', this.root).onclick = () => this.undo();
      $('#qe-redo', this.root).onclick = () => this.redo();
      $('#qe-orient', this.root).onclick = () => this.toggleOrient();
      $('#qe-save', this.root).onclick = () => this.save(true);
      $('#qe-continue', this.root).onclick = () => this.continueNext();
      $('#qe-prev', this.root).onclick = () => this.goPage(this.state.current - 1);
      $('#qe-next', this.root).onclick = () => this.goPage(this.state.current + 1);

      // sticker panel
      $('#qe-stk-close', this.root).onclick = () => this._setMode('normal');
      $('#qe-stkcat', this.root).onchange = (e) => { this._stkCat = e.target.value; this._renderStickerGrid(); };
      $('#qe-stksearch', this.root).oninput = (e) => this._renderStickerGrid(e.target.value);
      $('#qe-stkmagic', this.root).onclick = () => this._toast('Magic sticker generation is not enabled on this environment');

      // image upload
      this.file.onchange = (e) => this._onFile(e);

      // stage empty click → deselect
      this.stage.addEventListener('mousedown', (e) => { if (e.target === this.stage || e.target.classList.contains('qe-page') || e.target.classList.contains('qe-layer')) this.select(null); });

      // keyboard
      document.addEventListener('keydown', (e) => this._onKey(e));
      // click outside menus
      document.addEventListener('mousedown', (e) => { if (!e.target.closest('.qe-menu') && !e.target.closest('[data-menu]')) this._closeMenus(); });
    }

    /* ============================================================ RENDER */
    render() { this._renderStage(); this._renderThumbs(); this._renderLayouts(); this._syncHeader(); this._syncOrientBtn(); }

    _fit() {
      const L = this.logical;
      const pad = 8;
      const availW = this.work.clientWidth - 56;
      const availH = this.work.clientHeight - 56;
      const s = Math.min(availW / L.w, availH / L.h);
      const w = Math.round(L.w * s), h = Math.round(L.h * s);
      this.stage.style.width = w + 'px';
      this.stage.style.height = h + 'px';
      this.stage.style.setProperty('--s', (w / L.w).toFixed(5));
    }

    _renderStage() {
      const page = this.page, L = this.logical;
      this.stage.className = page.kind === 'spread' ? 'spread' : '';
      this.stage.innerHTML = '';

      if (page.kind === 'spread') {
        const gap = 0;
        const left = el('div', 'qe-page'); Object.assign(left.style, { left: '0%', width: '50%', height: '100%', background: page.bg });
        const right = el('div', 'qe-page'); Object.assign(right.style, { left: '50%', width: '50%', height: '100%', background: page.bg });
        this.stage.append(left, right, el('div', 'qe-fold'));
      } else {
        const sheet = el('div', 'qe-page'); Object.assign(sheet.style, { left: '0', width: '100%', height: '100%', background: page.bg });
        this.stage.append(sheet);
      }
      const layer = el('div', 'qe-layer');
      Object.assign(layer.style, { position: 'absolute', inset: '0', zIndex: 3 });
      this.stage.append(layer);
      this._layer = layer;

      page.elements.forEach(elm => { this._clampEl(elm); layer.appendChild(this._node(elm)); });
      this._fit();
      // reselect visual
      if (this.state.selectedElementId) {
        const n = $(`.qe-el[data-id="${this.state.selectedElementId}"]`, layer);
        if (n) n.classList.add('sel');
      }
    }

    _node(elm) {
      const L = this.logical;
      const n = el('div', 'qe-el ' + elm.type);
      n.dataset.id = elm.id;
      n.style.left = (elm.x / L.w * 100) + '%';
      n.style.top = (elm.y / L.h * 100) + '%';
      n.style.width = (elm.w / L.w * 100) + '%';
      n.style.height = (elm.h / L.h * 100) + '%';
      n.style.transform = `rotate(${elm.rot || 0}deg)`;

      if (elm.type === 'text') {
        const b = el('div', 'body'); b.textContent = elm.text || '';
        this._applyTextStyle(b, elm.style);
        n.appendChild(b);
        n.addEventListener('dblclick', () => this._editText(n, b, elm));
      } else if (elm.type === 'image') {
        if (elm.src) {
          const img = el('img', 'body'); img.src = elm.src; img.draggable = false;
          img.style.filter = this._imgFilter(elm);
          img.style.transform = `scale(${elm.scale || 1}) scaleX(${elm.flipH ? -1 : 1}) scaleY(${elm.flipV ? -1 : 1})`;
          n.appendChild(img);
        } else {
          n.classList.add('placeholder');
          n.appendChild(el('div', 'ph', ICON.plus + '<span>Add photo</span>'));
          n.addEventListener('dblclick', () => this._uploadTo(elm.id));
          n.addEventListener('click', (e) => { if (!this._dragged) this._uploadTo(elm.id); });
        }
      } else if (elm.type === 'sticker') {
        const b = el('div', 'body'); b.textContent = elm.char;
        b.style.fontSize = `calc(var(--s) * ${Math.min(elm.w, elm.h) * 0.86}px)`;
        n.appendChild(b);
      } else if (elm.type === 'video') {
        const b = el('div', 'body'); b.style.cssText = 'display:flex;align-items:center;justify-content:center;background:#111;color:#fff;border-radius:6px';
        b.innerHTML = '<svg viewBox="0 0 24 24" width="30%" fill="#fff"><path d="M8 5v14l11-7z"/></svg>';
        n.appendChild(b);
      } else if (elm.type === 'brand') {
        const b = el('div', 'body'); b.style.cssText = 'display:flex;align-items:center;justify-content:center;opacity:.75';
        const img = el('img'); img.src = QRINTO_LOGO; img.alt = 'Qrinto'; img.style.cssText = 'max-width:100%;max-height:100%;object-fit:contain';
        b.appendChild(img); n.appendChild(b);
      }

      ['tl','tr','bl','br','rot'].forEach(p => { const h = el('span', 'qe-h ' + p); h.dataset.pos = p; n.appendChild(h); });
      n.appendChild(el('span', 'qe-rotstem'));
      n.addEventListener('mousedown', (e) => this._down(e, n, elm));
      return n;
    }

    _applyTextStyle(b, s) {
      s = s || {};
      b.style.fontFamily = s.fontFamily || 'Inter';
      b.style.fontSize = `calc(var(--s) * ${s.fontSize || 28}px)`;
      b.style.color = s.color || '#202124';
      b.style.fontWeight = s.fontWeight || 400;
      b.style.fontStyle = s.fontStyle || 'normal';
      b.style.textAlign = s.textAlign || 'center';
      b.style.lineHeight = s.lineHeight || 1.2;
      b.style.letterSpacing = `calc(var(--s) * ${s.letterSpacing || 0}px)`;
      b.style.textTransform = s.textTransform || 'none';
    }
    _imgFilter(elm) {
      const f = FILTERS[elm.filterName] || elm.filter || 'none';
      const m = MAGIC[elm.magicName] || '';
      return [f === 'none' ? '' : f, m === 'none' ? '' : m].filter(Boolean).join(' ') || 'none';
    }

    /* ------------------------------------------------------------ toolClick */
    _toolClick(tool) {
      if (tool === 'text') { this.addText(); }
      else if (tool === 'sticker') { this._setMode(this.state.mode === 'sticker' ? 'normal' : 'sticker'); }
      else if (tool === 'image') { this._addImage(); }
    }
    _setMode(mode) {
      this.state.mode = mode;
      this.stickerSide.classList.toggle('open', mode === 'sticker');
      $('#qe-t-sticker', this.root).classList.toggle('active', mode === 'sticker');
      // layout panel: visible whenever a page has selectable layouts and not in sticker mode
      this._syncLayoutPanel();
    }
    _syncLayoutPanel() {
      const show = this.state.mode !== 'sticker';
      this.layoutSide.classList.toggle('open', show);
    }

    /* ============================================================ SELECT */
    select(id) {
      this.state.selectedElementId = id;
      $$('.qe-el', this.stage).forEach(n => n.classList.toggle('sel', n.dataset.id === id));
      const e = this.sel;
      $('#qe-t-image', this.root).classList.toggle('active', !!e && e.type === 'image' && !!e.src);
      this._renderContext();
    }

    _renderContext() {
      const e = this.sel;
      this.ctx.classList.remove('show');
      this.ctx.innerHTML = '';
      if (!e) return;
      if (e.type === 'text')      this._textToolbar(e);
      else if (e.type === 'image' && e.src) this._imageToolbar(e);
      else if (e.type === 'sticker') this._stickerToolbar(e);
      else return;
      this.ctx.classList.add('show');
    }

    /* ---------------------------------------------------------- text toolbar */
    _textToolbar(e) {
      const s = e.style;
      const fontOpts = FONTS.map(f => `<option value="${f}" style="font-family:${f}" ${f===s.fontFamily?'selected':''}>${f}</option>`).join('');
      this.ctx.innerHTML = `
        <div class="grp"><select id="tb-font">${fontOpts}</select></div>
        <div class="grp"><span class="qe-num"><button data-fs="-1">−</button><input id="tb-size" type="number" min="6" max="200" value="${s.fontSize}"><button data-fs="1">+</button></span></div>
        <input class="qe-color" id="tb-color" type="color" value="${this._hex(s.color)}" title="Color">
        <span class="qe-ctxsep"></span>
        <button class="qe-tb ${s.fontWeight>=600?'on':''}" id="tb-bold"><b>B</b></button>
        <button class="qe-tb ${s.fontStyle==='italic'?'on':''}" id="tb-italic"><i>I</i></button>
        <span class="qe-ctxsep"></span>
        <button class="qe-tb ${s.textAlign==='left'?'on':''}" data-al="left" title="Align left"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h10M4 18h13"/></svg></button>
        <button class="qe-tb ${s.textAlign==='center'?'on':''}" data-al="center" title="Center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M7 12h10M6 18h12"/></svg></button>
        <button class="qe-tb ${s.textAlign==='right'?'on':''}" data-al="right" title="Align right"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M10 12h10M7 18h13"/></svg></button>
        <span class="qe-ctxsep"></span>
        <span class="qe-slider" title="Line spacing"><svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg><input id="tb-line" type="range" min="1" max="2.4" step="0.1" value="${s.lineHeight}"></span>
        <span class="qe-slider" title="Letter spacing"><b style="font-size:11px;color:#9ca3af">AV</b><input id="tb-letter" type="range" min="-2" max="12" step="0.5" value="${s.letterSpacing}"></span>
        <span class="qe-ctxsep"></span>
        <button class="qe-tb" id="tb-case" data-menu>Aa</button>
        <span class="qe-ctxsep"></span>
        <button class="qe-tb danger" id="tb-del" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg></button>`;
      const set = (patch) => { Object.assign(e.style, patch); this._rerenderEl(e); this.commit(); this._renderContext(); };
      $('#tb-font', this.ctx).onchange = (ev) => set({ fontFamily: ev.target.value });
      $('#tb-size', this.ctx).onchange = (ev) => set({ fontSize: clamp(+ev.target.value, 6, 200) });
      $$('[data-fs]', this.ctx).forEach(b => b.onclick = () => set({ fontSize: clamp(e.style.fontSize + (+b.dataset.fs), 6, 200) }));
      $('#tb-color', this.ctx).oninput = (ev) => { e.style.color = ev.target.value; this._rerenderEl(e); };
      $('#tb-color', this.ctx).onchange = () => this.commit();
      $('#tb-bold', this.ctx).onclick = () => set({ fontWeight: e.style.fontWeight >= 600 ? 400 : 700 });
      $('#tb-italic', this.ctx).onclick = () => set({ fontStyle: e.style.fontStyle === 'italic' ? 'normal' : 'italic' });
      $$('[data-al]', this.ctx).forEach(b => b.onclick = () => set({ textAlign: b.dataset.al }));
      $('#tb-line', this.ctx).oninput = (ev) => { e.style.lineHeight = +ev.target.value; this._rerenderEl(e); };
      $('#tb-line', this.ctx).onchange = () => this.commit();
      $('#tb-letter', this.ctx).oninput = (ev) => { e.style.letterSpacing = +ev.target.value; this._rerenderEl(e); };
      $('#tb-letter', this.ctx).onchange = () => this.commit();
      $('#tb-del', this.ctx).onclick = () => this.remove();
      $('#tb-case', this.ctx).onclick = (ev) => this._menu(ev.currentTarget, [
        ['Original','none'],['UPPERCASE','uppercase'],['lowercase','lowercase'],['Capitalize','capitalize']
      ].map(([label,val]) => ({ label, onClick: () => set({ textTransform: val }) })));
    }

    /* -------------------------------------------------------- image toolbar */
    _imageToolbar(e) {
      this.ctx.innerHTML = `
        <span class="qe-slider" title="Image size"><svg viewBox="0 0 24 24"><rect x="4" y="6" width="12" height="10" rx="1"/></svg>
          <input id="ib-size" type="range" min="0.5" max="2.5" step="0.05" value="${e.scale||1}">
          <svg viewBox="0 0 24 24"><rect x="2" y="4" width="18" height="14" rx="1"/></svg></span>
        <span class="qe-ctxsep"></span>
        <button class="qe-tb" id="ib-magic" data-menu>✨ Magic styles</button>
        <button class="qe-tb" id="ib-filter" data-menu>Filters</button>
        <button class="qe-tb" id="ib-rmbg">Remove BG</button>
        <span class="qe-ctxsep"></span>
        <button class="qe-tb" id="ib-fliph" title="Flip horizontal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v18M7 8l-4 4 4 4M17 8l4 4-4 4"/></svg></button>
        <button class="qe-tb" id="ib-flipv" title="Flip vertical"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 12h18M8 7 12 3l4 4M8 17l4 4 4-4"/></svg></button>
        <button class="qe-tb" id="ib-rot" title="Rotate 90°"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 12a9 9 0 1 1-3-6.7M21 3v5h-5"/></svg></button>
        <span class="qe-ctxsep"></span>
        <button class="qe-tb" id="ib-replace">Replace</button>
        <button class="qe-tb danger" id="ib-del"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg></button>`;
      $('#ib-size', this.ctx).oninput = (ev) => { e.scale = +ev.target.value; this._rerenderEl(e); };
      $('#ib-size', this.ctx).onchange = () => this.commit();
      $('#ib-fliph', this.ctx).onclick = () => { e.flipH = !e.flipH; this._rerenderEl(e); this.commit(); };
      $('#ib-flipv', this.ctx).onclick = () => { e.flipV = !e.flipV; this._rerenderEl(e); this.commit(); };
      $('#ib-rot', this.ctx).onclick = () => { e.rot = ((e.rot || 0) + 90) % 360; this._rerenderEl(e); this.commit(); };
      $('#ib-replace', this.ctx).onclick = () => this._uploadTo(e.id);
      $('#ib-del', this.ctx).onclick = () => this.remove();
      $('#ib-rmbg', this.ctx).onclick = () => this._toast('Background removal is not enabled on this environment');
      $('#ib-filter', this.ctx).onclick = (ev) => this._menu(ev.currentTarget, Object.keys(FILTERS).map(name => ({
        label: name, swatch: FILTERS[name] !== 'none', onClick: () => { e.filterName = name; delete e.filter; this._rerenderEl(e); this.commit(); } })));
      $('#ib-magic', this.ctx).onclick = (ev) => this._menu(ev.currentTarget, Object.keys(MAGIC).map(name => ({
        label: name, onClick: () => { e.magicName = name; this._rerenderEl(e); this.commit(); } })));
    }

    _stickerToolbar(e) {
      this.ctx.innerHTML = `
        <span class="qe-slider" title="Size"><b style="font-size:11px;color:#9ca3af">◦</b>
          <input id="sk-size" type="range" min="60" max="360" step="4" value="${Math.round((e.w+e.h)/2)}">
          <b style="font-size:13px;color:#9ca3af">◯</b></span>
        <button class="qe-tb" id="sk-rotl" title="Rotate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 12a9 9 0 1 1-3-6.7M21 3v5h-5"/></svg></button>
        <button class="qe-tb" id="sk-dup" title="Duplicate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg></button>
        <button class="qe-tb danger" id="sk-del"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg></button>`;
      $('#sk-size', this.ctx).oninput = (ev) => { const v = +ev.target.value; e.w = v; e.h = v; this._rerenderEl(e); };
      $('#sk-size', this.ctx).onchange = () => this.commit();
      $('#sk-rotl', this.ctx).onclick = () => { e.rot = ((e.rot || 0) + 45) % 360; this._rerenderEl(e); this.commit(); };
      $('#sk-dup', this.ctx).onclick = () => this.duplicate();
      $('#sk-del', this.ctx).onclick = () => this.remove();
    }

    _menu(anchor, items) {
      this._closeMenus();
      const m = el('div', 'qe-menu open');
      items.forEach(it => {
        const b = el('button');
        if (it.swatch) { const sw = el('span', 'swatch'); b.appendChild(sw); }
        b.appendChild(document.createTextNode(it.label));
        b.onclick = () => { it.onClick(); this._closeMenus(); };
        m.appendChild(b);
      });
      document.body.appendChild(m);
      const r = anchor.getBoundingClientRect();
      m.style.left = Math.min(r.left, window.innerWidth - m.offsetWidth - 8) + 'px';
      m.style.top = (r.bottom + 6) + 'px';
      this._openMenu = m;
    }
    _closeMenus() { if (this._openMenu) { this._openMenu.remove(); this._openMenu = null; } }

    /* ------------------------------------------------------ rerender single */
    _rerenderEl(e) {
      const old = $(`.qe-el[data-id="${e.id}"]`, this._layer);
      if (!old) return;
      const fresh = this._node(e);
      if (e.id === this.state.selectedElementId) fresh.classList.add('sel');
      old.replaceWith(fresh);
    }

    /* ============================================================ ELEMENTS */
    _add(elm) {
      elm.id = uid();
      this.page.elements.push(elm);
      this.render(); this.select(elm.id); this.commit();
    }
    _addImage() {
      this._pendingImageForId = null; // new element
      this.file.click();
    }
    _addVideo() {
      const L = this.logical;
      this._add({ type: 'video', x: L.w/2-160, y: L.h/2-110, w: 320, h: 220, rot: 0 });
      this._toast('Video placeholder added');
    }
    _uploadTo(id) { this._pendingImageForId = id; this.file.click(); }
    _onFile(e) {
      const f = e.target.files && e.target.files[0];
      e.target.value = '';
      if (!f) return;
      const r = new FileReader();
      r.onload = () => {
        if (this._pendingImageForId) {
          const t = this.page.elements.find(x => x.id === this._pendingImageForId);
          if (t) { t.type = 'image'; t.src = r.result; t.filter = 'none'; delete t.filterName; delete t.magicName; }
          this._pendingImageForId = null;
          this.render(); if (t) this.select(t.id); this.commit();
        } else {
          const L = this.logical;
          this._add({ type: 'image', x: L.w*0.2, y: L.h*0.2, w: L.w*0.55, h: L.h*0.4, rot: 0, src: r.result, filter: 'none' });
        }
      };
      r.readAsDataURL(f);
    }
    addText() {
      const L = this.logical;
      this._add({ type: 'text', x: L.w/2-220, y: L.h/2-45, w: 440, h: 90, rot: 0, text: 'Your text',
        style: { fontFamily: 'Inter', fontSize: 32, color: '#202124', fontWeight: 400, fontStyle: 'normal', textAlign: 'center', lineHeight: 1.2, letterSpacing: 0, textTransform: 'none' } });
    }
    addSticker(char) {
      const L = this.logical;
      this._add({ type: 'sticker', x: L.w/2-70, y: L.h/2-70, w: 140, h: 140, rot: 0, char });
    }
    duplicate() {
      const e = this.sel; if (!e) return;
      const c = clone(e); c.x += 24; c.y += 24;
      this._add(c);
    }
    remove() {
      const e = this.sel; if (!e) return;
      this.page.elements = this.page.elements.filter(x => x.id !== e.id);
      this.select(null); this.render(); this.commit();
    }

    // keep an element fully inside the page canvas (ignores rotation)
    _clampEl(e) {
      const L = this.logical;
      e.w = Math.min(e.w, L.w); e.h = Math.min(e.h, L.h);
      e.x = clamp(e.x, 0, L.w - e.w);
      e.y = clamp(e.y, 0, L.h - e.h);
    }

    /* -------------------------------------------------------- interactions */
    _down(e, node, elm) {
      if (node.classList.contains('editing')) return;
      if (e.target.classList.contains('qe-h')) return this._resize(e, node, elm, e.target.dataset.pos);
      this.select(elm.id);
      if (elm.type === 'image' && !elm.src) return; // placeholder handles click
      e.preventDefault();
      const rect = this._layer.getBoundingClientRect(), L = this.logical;
      const sx = e.clientX, sy = e.clientY, ox = elm.x, oy = elm.y;
      this._dragged = false;
      const move = (ev) => {
        const dx = (ev.clientX - sx) / rect.width * L.w;
        const dy = (ev.clientY - sy) / rect.height * L.h;
        if (Math.abs(ev.clientX - sx) + Math.abs(ev.clientY - sy) > 3) this._dragged = true;
        elm.x = ox + dx; elm.y = oy + dy;
        this._clampEl(elm);
        node.style.left = (elm.x / L.w * 100) + '%';
        node.style.top = (elm.y / L.h * 100) + '%';
      };
      const up = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up);
        if (this._dragged) this.commit(); setTimeout(() => this._dragged = false, 0); };
      document.addEventListener('mousemove', move); document.addEventListener('mouseup', up);
    }

    _resize(e, node, elm, pos) {
      e.preventDefault(); e.stopPropagation();
      const rect = this._layer.getBoundingClientRect(), L = this.logical;
      const o = { x: elm.x, y: elm.y, w: elm.w, h: elm.h };
      const cx = rect.left + (elm.x + elm.w / 2) / L.w * rect.width;
      const cy = rect.top + (elm.y + elm.h / 2) / L.h * rect.height;
      const sx = e.clientX, sy = e.clientY;
      const isRot = pos === 'rot';
      const move = (ev) => {
        if (isRot) { elm.rot = Math.round(Math.atan2(ev.clientY - cy, ev.clientX - cx) * 180 / Math.PI + 90); node.style.transform = `rotate(${elm.rot}deg)`; return; }
        const dx = (ev.clientX - sx) / rect.width * L.w;
        const dy = (ev.clientY - sy) / rect.height * L.h;
        if (pos.includes('r')) elm.w = clamp(o.w + dx, 30, L.w);
        if (pos.includes('b')) elm.h = clamp(o.h + dy, 24, L.h);
        if (pos.includes('l')) { elm.w = clamp(o.w - dx, 30, L.w); elm.x = o.x + dx; }
        if (pos.includes('t')) { elm.h = clamp(o.h - dy, 24, L.h); elm.y = o.y + dy; }
        // never let a handle push the element past the page edges
        if (elm.x < 0) { elm.w += elm.x; elm.x = 0; }
        if (elm.y < 0) { elm.h += elm.y; elm.y = 0; }
        elm.w = Math.min(elm.w, L.w - elm.x);
        elm.h = Math.min(elm.h, L.h - elm.y);
        Object.assign(node.style, { left: elm.x/L.w*100+'%', top: elm.y/L.h*100+'%', width: elm.w/L.w*100+'%', height: elm.h/L.h*100+'%' });
        if (elm.type === 'sticker') { const b = node.querySelector('.body'); if (b) b.style.fontSize = `calc(var(--s) * ${Math.min(elm.w, elm.h)*0.86}px)`; }
      };
      const up = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); this.commit(); };
      document.addEventListener('mousemove', move); document.addEventListener('mouseup', up);
    }

    /* --------------------------------------------------------- text editing */
    _editText(node, body, elm) {
      node.classList.add('editing');
      body.contentEditable = 'true'; body.focus();
      document.execCommand && document.getSelection().selectAllChildren(body);
      const finish = () => {
        node.classList.remove('editing');
        body.contentEditable = 'false';
        elm.text = body.innerText;
        body.removeEventListener('blur', finish);
        this.commit();
      };
      body.addEventListener('blur', finish);
    }

    /* ========================================================= ORIENTATION */
    toggleOrient() {
      const from = this.logical;
      this.page.orient = this.page.orient === 'landscape' ? 'portrait' : 'landscape';
      const to = this.logical;
      // remap every element proportionally so the design keeps its relative layout
      this.page.elements.forEach(el => {
        const m = this._mapRect(el, from, to);
        el.x = m.x; el.y = m.y; el.w = m.w; el.h = m.h;
        if (el.type === 'text' && el.style) {
          const ry = to.h / from.h;
          el.style.fontSize = Math.max(6, Math.round(el.style.fontSize * ry));
          el.style.letterSpacing = (el.style.letterSpacing || 0) * ry;
        }
      });
      this.select(null); this.render(); this.commit();
      this._syncOrientBtn();
    }
    _syncOrientBtn() {
      const b = $('#qe-orient', this.root); if (!b) return;
      const land = this.page.orient === 'landscape';
      b.classList.toggle('on', land);
      b.title = land ? 'Switch to portrait' : 'Switch to landscape';
    }

    /* =========================================================== NAVIGATION */
    goPage(i) {
      i = clamp(i, 0, this.state.pages.length - 1);
      if (i === this.state.current) return;
      this.state.current = i;
      this.select(null);
      this.render();
    }

    /* ============================================================ THUMBS */
    _renderThumbs() {
      this.thumbs.innerHTML = '';
      this.state.pages.forEach((p, i) => {
        const L = this._logicalOf(p);
        const H = 74, W = Math.round(H * L.w / L.h);
        const t = el('button', 'qe-thumb' + (i === this.state.current ? ' active' : '') + (p.kind === 'spread' ? ' spread' : ''));
        t.style.width = W + 'px'; t.style.height = H + 'px';
        const mini = el('div', 'mini');
        this._renderMini(mini, p, W, H);
        t.appendChild(mini);
        if (p.kind === 'spread') t.appendChild(el('div', 'midline'));
        t.appendChild(el('div', 'num', p.name));
        t.onclick = () => this.goPage(i);
        this.thumbs.appendChild(t);
      });
    }

    // faithful miniature of a page (used by thumbnails)
    _renderMini(box, page, W, H) {
      const L = this._logicalOf(page);
      box.style.background = page.bg;
      const s = W / L.w;
      page.elements.forEach(e => {
        const n = el('div');
        Object.assign(n.style, { position: 'absolute', left: e.x/L.w*100+'%', top: e.y/L.h*100+'%',
          width: e.w/L.w*100+'%', height: e.h/L.h*100+'%', transform: `rotate(${e.rot||0}deg)`, overflow: 'hidden' });
        if (e.type === 'image' && e.src) { const img = el('img'); img.src = e.src; img.style.cssText = 'width:100%;height:100%;object-fit:cover;filter:'+this._imgFilter(e); n.appendChild(img); }
        else if (e.type === 'image') { n.style.background = '#eef1f5'; n.style.border = '1px dashed #d5dbe3'; }
        else if (e.type === 'text') { n.style.cssText += `display:flex;flex-direction:column;justify-content:center;color:${e.style.color};font-family:${e.style.fontFamily};font-size:${Math.max(3,e.style.fontSize*s)}px;line-height:${e.style.lineHeight};text-align:${e.style.textAlign};text-transform:${e.style.textTransform};font-weight:${e.style.fontWeight};font-style:${e.style.fontStyle};overflow:hidden`; n.textContent = e.text; }
        else if (e.type === 'sticker') { n.style.cssText += `display:flex;align-items:center;justify-content:center;font-size:${Math.min(e.w,e.h)*s*0.8}px`; n.textContent = e.char; }
        else if (e.type === 'brand') { const img = el('img'); img.src = QRINTO_LOGO; img.style.cssText = 'width:100%;height:100%;object-fit:contain;opacity:.7'; n.appendChild(img); }
        else if (e.type === 'video') { n.style.background = '#111'; }
        box.appendChild(n);
      });
    }

    /* ============================================================ LAYOUTS */
    _renderLayouts() {
      const list = $('#qe-layoutlist', this.root);
      list.innerHTML = '';
      const kind = this.page.kind;
      const base = this._kindBase(kind), L = this.logical;
      LAYOUTS.filter(l => l.kind === kind).forEach(lay => {
        const card = el('div', 'qe-lay' + (lay.id === this.page.layoutId ? ' sel' : ''));
        card.appendChild(el('div', 'crown', '👑'));
        const pw = this.layoutSide.clientWidth - 32 - 12; // inner width
        const prevW = pw > 0 ? pw : 140;
        const prevH = Math.round(prevW * L.h / L.w);
        const prev = el('div', 'prev'); prev.style.height = prevH + 'px';
        lay.elements.forEach(def => {
          const e = this._mapRect(def, base, L);
          const m = el('div', 'qe-mini ' + (def.type === 'brand' ? 'sticker' : def.type));
          Object.assign(m.style, { left: e.x/L.w*100+'%', top: e.y/L.h*100+'%', width: e.w/L.w*100+'%', height: e.h/L.h*100+'%' });
          e.type = def.type;
          if (e.type === 'image') m.innerHTML = ICON.image;
          else if (e.type === 'text') m.innerHTML = '<i style="width:90%"></i><i style="width:70%"></i><i style="width:80%"></i>';
          else if (e.type === 'brand') m.textContent = 'Qrinto';
          prev.appendChild(m);
        });
        card.appendChild(prev);
        card.onclick = () => this.selectLayout(lay.id);
        list.appendChild(card);
      });
    }

    // apply a layout to the CURRENT page, preserving user content where possible
    selectLayout(layoutId) {
      const lay = LAYOUTS.find(l => l.id === layoutId); if (!lay) return;
      const old = this.page.elements;
      const pool = { image: old.filter(e => e.type === 'image' && e.src), text: old.filter(e => e.type === 'text'), sticker: old.filter(e => e.type === 'sticker') };
      const keptStickers = pool.sticker.slice(); // stickers always preserved as-is
      const base = this._kindBase(this.page.kind), L = this.logical;
      const next = [];
      lay.elements.forEach(def => {
        const r = this._mapRect(def, base, L); // remap into current orientation
        if (def.type === 'brand') { next.push({ id: uid(), type: 'brand', x: r.x, y: r.y, w: r.w, h: r.h, rot: 0 }); return; }
        const src = pool[def.type] && pool[def.type].shift();
        if (def.type === 'image') {
          next.push({ id: uid(), type: 'image', x: r.x, y: r.y, w: r.w, h: r.h, rot: 0,
            src: src ? src.src : null, filter: src ? (src.filter || 'none') : 'none',
            filterName: src && src.filterName, magicName: src && src.magicName, scale: src && src.scale, flipH: src && src.flipH, flipV: src && src.flipV });
        } else if (def.type === 'text') {
          next.push({ id: uid(), type: 'text', x: r.x, y: r.y, w: r.w, h: r.h, rot: 0,
            text: src ? src.text : def.text,
            style: src ? src.style : { fontFamily: 'Inter', fontSize: 28, color: '#202124', fontWeight: 400, fontStyle: 'normal', textAlign: 'center', lineHeight: 1.2, letterSpacing: 0, textTransform: 'none' } });
        }
      });
      // re-add any leftover stickers so they are never destroyed
      keptStickers.forEach(s => next.push(clone(s)));
      this.page.elements = next;
      this.page.layoutId = layoutId;
      this.select(null);
      this.render(); this.commit();
    }

    /* ============================================================ HISTORY */
    _snapshot(reset) {
      const snap = clone({ pages: this.state.pages, current: this.state.current });
      if (reset) { this.state.history = [snap]; this.state.histIndex = 0; }
      this._syncHeader();
    }
    commit() {
      this.save(false);
      const snap = clone({ pages: this.state.pages, current: this.state.current });
      this.state.history = this.state.history.slice(0, this.state.histIndex + 1);
      this.state.history.push(snap);
      if (this.state.history.length > 60) this.state.history.shift();
      this.state.histIndex = this.state.history.length - 1;
      this._syncHeader();
    }
    _restore(snap) {
      this.state.pages = clone(snap.pages);
      this.state.current = clamp(snap.current, 0, this.state.pages.length - 1);
      this.select(null); this.render();
    }
    undo() { if (this.state.histIndex > 0) { this.state.histIndex--; this._restore(this.state.history[this.state.histIndex]); this._syncHeader(); } }
    redo() { if (this.state.histIndex < this.state.history.length - 1) { this.state.histIndex++; this._restore(this.state.history[this.state.histIndex]); this._syncHeader(); } }
    _syncHeader() {
      const u = $('#qe-undo', this.root), r = $('#qe-redo', this.root);
      if (u) u.disabled = this.state.histIndex <= 0;
      if (r) r.disabled = this.state.histIndex >= this.state.history.length - 1;
    }

    /* ============================================================ KEYBOARD */
    _onKey(e) {
      const editing = document.activeElement && document.activeElement.isContentEditable;
      const meta = e.ctrlKey || e.metaKey;
      if (meta && e.key.toLowerCase() === 'z') { e.preventDefault(); e.shiftKey ? this.redo() : this.undo(); return; }
      if (meta && e.key.toLowerCase() === 'y') { e.preventDefault(); this.redo(); return; }
      if (editing) return;
      if (e.key === 'Escape') { this.select(null); this._setMode('normal'); this._closeMenus(); }
      if ((e.key === 'Delete' || e.key === 'Backspace') && this.sel) { e.preventDefault(); this.remove(); }
    }

    /* ============================================================ STICKERS */
    _renderStickerGrid(query) {
      const grid = $('#qe-stkgrid', this.root); grid.innerHTML = '';
      let list = STICKERS[this._stkCat] || [];
      if (query && query.trim()) { const all = [].concat(...Object.values(STICKERS)); list = [...new Set(all)]; }
      list.forEach(ch => { const b = el('button', 'qe-stk', ch); b.onclick = () => this.addSticker(ch); grid.appendChild(b); });
    }

    /* ============================================================ PERSIST */
    _key() { return 'qe-draft-' + (this.opts.productId || 'default'); }
    save(notify) {
      try { localStorage.setItem(this._key(), JSON.stringify({ pages: this.state.pages })); } catch (e) {}
      if (notify) this._toast('Draft saved');
    }
    _load() {
      try {
        const raw = localStorage.getItem(this._key()); if (!raw) return;
        const d = JSON.parse(raw);
        if (d && Array.isArray(d.pages) && d.pages.length) this.state.pages = d.pages;
      } catch (e) {}
    }
    continueNext() { this.save(false); this._toast('Saved — continuing…'); /* hook: navigate to next step */ }

    /* ------------------------------------------------------------- helpers */
    _hex(c) {
      if (!c) return '#202124';
      if (c[0] === '#') return c.length === 4 ? '#' + [...c.slice(1)].map(x => x + x).join('') : c;
      const m = c.match(/\d+/g); if (!m) return '#202124';
      return '#' + m.slice(0, 3).map(n => (+n).toString(16).padStart(2, '0')).join('');
    }
    _toast(msg) {
      const t = $('#qe-toast', this.root); if (!t) return;
      t.textContent = msg; t.classList.add('show');
      clearTimeout(this._tt); this._tt = setTimeout(() => t.classList.remove('show'), 1900);
    }
  }

  window.initCardEditor = function (opts) {
    const root = document.getElementById('qe');
    if (root) window.qrintoEditor = new QrintoEditor(root, opts || {});
  };
})();
