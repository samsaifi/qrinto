/* =============================================================================
 * card-editor.js  —  Standalone HTML/CSS overlay card editor (v2)
 * -----------------------------------------------------------------------------
 * Inspired by the Greetings Island customizer. No canvas, no external deps.
 * Elements are percentage-positioned HTML nodes over a scaled artboard; text
 * is edited in place with contenteditable + a floating toolbar. Fully isolated
 * from the existing Fabric.js customizer.
 * ========================================================================== */
(function () {
  'use strict';

  const uid = () => 'e' + Math.random().toString(36).slice(2, 9);
  const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  const FONTS = [
    'Poppins', 'Montserrat', 'Playfair Display', 'Dancing Script',
    'Georgia', 'Arial', 'Courier New', 'Times New Roman'
  ];
  const STICKERS = ['❤️','⭐','🎉','🎂','🌸','🎈','✨','🌟','💐','🥳','🎁','🍀','☀️','🌙','💌','🕊️'];

  class CardEditor {
    constructor(root, opts) {
      this.root = root;
      this.opts = opts || {};
      this.pageW = this.opts.width || 5;
      this.pageH = this.opts.height || 7;
      this.singleAspect = this.pageW / this.pageH;
      this.zoom = 1;
      this.selected = null;
      // Multi-page model. Each page: { name, bg, elements:[], spread }
      // A "spread" page is a double-panel landscape view (two portrait panels
      // side by side with a center fold) — used for the inside of a card.
      this.pages = [
        { name: 'Front',  bg: '#ffffff', elements: [], spread: false },
        { name: 'Inside', bg: '#ffffff', elements: [], spread: true },
        { name: 'Back',   bg: '#ffffff', elements: [], spread: false },
      ];
      this.current = 0;
      this._cacheDom();
      this._bindToolbar();
      this._bindStage();
      this._load();
      this.renderPage();
      this.renderThumbs();
      window.addEventListener('resize', () => this._fit());
    }

    /* ---------------------------------------------------------------- DOM */
    _cacheDom() {
      this.stageWrap = $('#ce-stage-wrap', this.root);
      this.stage     = $('#ce-stage', this.root);
      this.thumbs    = $('#ce-thumbs', this.root);
      this.propPanel = $('#ce-props', this.root);
      this.textBar   = $('#ce-textbar', this.root);
      this.pageLabel = $('#ce-page-label', this.root);
    }

    _aspectFor(page) {
      // Spread pages are twice as wide (two portrait panels side by side).
      return page && page.spread ? (this.singleAspect * 2) : this.singleAspect;
    }

    _fit() {
      const aspect = this._aspectFor(this.pages[this.current]);
      const wrap = this.stageWrap.getBoundingClientRect();
      const maxH = wrap.height - 48;
      const maxW = wrap.width - 48;
      let h = maxH, w = h * aspect;
      if (w > maxW) { w = maxW; h = w / this.aspect; }
      w *= this.zoom; h *= this.zoom;
      this.stage.style.width = w + 'px';
      this.stage.style.height = h + 'px';
      this.stage.style.setProperty('--canvas-size-scale', (w / 1000).toFixed(5));
    }

    /* ------------------------------------------------------------ toolbar */
    _bindToolbar() {
      $('#ce-add-text', this.root).onclick   = () => this.addText();
      $('#ce-add-image', this.root).onclick  = () => $('#ce-file', this.root).click();
      $('#ce-file', this.root).onchange      = (e) => this._onFile(e);
      $('#ce-add-sticker', this.root).onclick= () => this.toggleStickers();
      $('#ce-save', this.root).onclick       = () => this.save(true);
      $('#ce-export', this.root).onclick     = () => this.exportJSON();
      $('#ce-zoom-in', this.root).onclick    = () => { this.zoom = clamp(this.zoom + .1, .4, 2.5); this._fit(); };
      $('#ce-zoom-out', this.root).onclick   = () => { this.zoom = clamp(this.zoom - .1, .4, 2.5); this._fit(); };
      $('#ce-prev', this.root).onclick       = () => this.goPage(this.current - 1);
      $('#ce-next', this.root).onclick       = () => this.goPage(this.current + 1);

      // Sticker picker grid
      const grid = $('#ce-sticker-grid', this.root);
      STICKERS.forEach(s => {
        const b = document.createElement('button');
        b.className = 'ce-sticker-cell';
        b.textContent = s;
        b.onclick = () => { this.addSticker(s); this.toggleStickers(false); };
        grid.appendChild(b);
      });

      // Text formatting bar
      const fontSel = $('#ce-font', this.textBar);
      FONTS.forEach(f => { const o = document.createElement('option'); o.value = f; o.textContent = f; o.style.fontFamily = f; fontSel.appendChild(o); });
      fontSel.onchange = () => this._applyText('fontFamily', fontSel.value);
      $('#ce-fontsize', this.textBar).onchange = (e) => this._applyText('fontSize', e.target.value + 'px');
      $('#ce-color', this.textBar).oninput     = (e) => this._applyText('color', e.target.value);
      $('#ce-bold', this.textBar).onclick      = () => this._exec('bold');
      $('#ce-italic', this.textBar).onclick    = () => this._exec('italic');
      $('#ce-underline', this.textBar).onclick = () => this._exec('underline');
      ['left','center','right'].forEach(a =>
        $('#ce-align-' + a, this.textBar).onclick = () => this._applyText('textAlign', a));

      // Property panel (z-order, delete, duplicate)
      $('#ce-front', this.propPanel).onclick = () => this._z(1);
      $('#ce-back', this.propPanel).onclick  = () => this._z(-1);
      $('#ce-dup', this.propPanel).onclick   = () => this.duplicate();
      $('#ce-del', this.propPanel).onclick   = () => this.remove();
    }

    _bindStage() {
      // Click empty stage → deselect
      this.stage.addEventListener('mousedown', (e) => {
        if (e.target === this.stage) this.select(null);
      });
      document.addEventListener('keydown', (e) => {
        if (!this.selected) return;
        const editing = document.activeElement && document.activeElement.isContentEditable;
        if (editing) return;
        if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); this.remove(); }
      });
    }

    /* -------------------------------------------------------- page render */
    goPage(i) {
      i = clamp(i, 0, this.pages.length - 1);
      if (i === this.current) return;
      this.current = i;
      this.select(null);
      this.renderPage();
      this.renderThumbs();
    }

    renderPage() {
      const page = this.pages[this.current];
      this.stage.innerHTML = '';
      this.stage.style.background = page.bg;
      this.stage.classList.toggle('is-spread', !!page.spread);
      if (page.spread) {
        const fold = document.createElement('div');
        fold.className = 'ce-fold';
        this.stage.appendChild(fold);
      }
      page.elements.forEach(el => this.stage.appendChild(this._nodeFor(el)));
      this.pageLabel.textContent = `${page.name} (${this.current + 1}/${this.pages.length})`;
      this._fit();
    }

    _nodeFor(el) {
      const wrap = document.createElement('div');
      wrap.className = 'ce-elm';
      wrap.dataset.id = el.id;
      wrap.style.left = el.x + '%';
      wrap.style.top = el.y + '%';
      wrap.style.width = el.w + '%';
      wrap.style.height = el.h + '%';
      wrap.style.zIndex = el.z || 1;
      wrap.style.transform = `rotate(${el.rot || 0}deg)`;

      let inner;
      if (el.type === 'text') {
        inner = document.createElement('div');
        inner.className = 'ce-text';
        inner.contentEditable = 'false';
        inner.innerHTML = el.html || 'Text';
        Object.assign(inner.style, {
          fontFamily: el.fontFamily || 'Poppins',
          fontSize: el.fontSize || '32px',
          color: el.color || '#222',
          textAlign: el.textAlign || 'center',
        });
        inner.addEventListener('dblclick', () => this._editText(wrap, inner, el));
      } else if (el.type === 'image') {
        inner = document.createElement('img');
        inner.className = 'ce-img';
        inner.src = el.src;
        inner.draggable = false;
      } else { // sticker
        inner = document.createElement('div');
        inner.className = 'ce-stk';
        inner.textContent = el.char;
      }
      wrap.appendChild(inner);
      this._addHandles(wrap);
      wrap.addEventListener('mousedown', (e) => this._startDrag(e, wrap, el));
      return wrap;
    }

    _addHandles(wrap) {
      ['tl','tr','bl','br'].forEach(pos => {
        const h = document.createElement('span');
        h.className = 'ce-handle ce-' + pos;
        h.dataset.pos = pos;
        wrap.appendChild(h);
      });
      const rot = document.createElement('span');
      rot.className = 'ce-handle ce-rot';
      wrap.appendChild(rot);
    }

    /* ------------------------------------------------------ add elements */
    _push(el) {
      el.id = uid();
      el.z = (this.pages[this.current].elements.reduce((m, e) => Math.max(m, e.z || 1), 0)) + 1;
      this.pages[this.current].elements.push(el);
      this.renderPage();
      this.select(el.id);
      this.save();
    }

    addText() {
      this._push({ type: 'text', x: 25, y: 40, w: 50, h: 12, rot: 0,
        html: 'Your text', fontFamily: 'Poppins', fontSize: '32px', color: '#222', textAlign: 'center' });
    }
    addSticker(char) {
      this._push({ type: 'sticker', x: 42, y: 42, w: 16, h: 12, rot: 0, char });
    }
    _onFile(e) {
      const f = e.target.files[0];
      if (!f) return;
      const r = new FileReader();
      r.onload = () => this._push({ type: 'image', x: 25, y: 25, w: 50, h: 40, rot: 0, src: r.result });
      r.readAsDataURL(f);
      e.target.value = '';
    }
    toggleStickers(force) {
      const p = $('#ce-sticker-panel', this.root);
      p.classList.toggle('open', force === undefined ? undefined : force);
    }

    /* -------------------------------------------------------- selection */
    select(id) {
      this.selected = id;
      $$('.ce-elm', this.stage).forEach(n => n.classList.toggle('selected', n.dataset.id === id));
      const el = this._get(id);
      this.propPanel.classList.toggle('open', !!el);
      this.textBar.classList.toggle('open', !!el && el.type === 'text');
      if (el && el.type === 'text') {
        $('#ce-font', this.textBar).value = el.fontFamily || 'Poppins';
        $('#ce-fontsize', this.textBar).value = parseInt(el.fontSize) || 32;
        $('#ce-color', this.textBar).value = this._toHex(el.color || '#222222');
      }
    }
    _get(id) { return this.pages[this.current].elements.find(e => e.id === id); }

    /* -------------------------------------------------------- drag/resize */
    _startDrag(e, wrap, el) {
      if (e.target.classList.contains('ce-handle')) return this._startResize(e, wrap, el);
      this.select(el.id);
      if (wrap.querySelector('.ce-text')?.isContentEditable) return; // don't drag while editing
      e.preventDefault();
      const rect = this.stage.getBoundingClientRect();
      const sx = e.clientX, sy = e.clientY, ox = el.x, oy = el.y;
      const move = (ev) => {
        const dx = (ev.clientX - sx) / rect.width * 100;
        const dy = (ev.clientY - sy) / rect.height * 100;
        el.x = clamp(ox + dx, -20, 100);
        el.y = clamp(oy + dy, -20, 100);
        wrap.style.left = el.x + '%';
        wrap.style.top = el.y + '%';
      };
      const up = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); this.save(); };
      document.addEventListener('mousemove', move);
      document.addEventListener('mouseup', up);
    }

    _startResize(e, wrap, el) {
      e.preventDefault(); e.stopPropagation();
      const pos = e.target.dataset.pos;
      const rect = this.stage.getBoundingClientRect();
      const sx = e.clientX, sy = e.clientY;
      const o = { x: el.x, y: el.y, w: el.w, h: el.h, rot: el.rot || 0 };
      const isRot = e.target.classList.contains('ce-rot');
      const cx = rect.left + (el.x + el.w / 2) / 100 * rect.width;
      const cy = rect.top + (el.y + el.h / 2) / 100 * rect.height;
      const move = (ev) => {
        if (isRot) {
          const ang = Math.atan2(ev.clientY - cy, ev.clientX - cx) * 180 / Math.PI + 90;
          el.rot = Math.round(ang);
          wrap.style.transform = `rotate(${el.rot}deg)`;
          return;
        }
        const dx = (ev.clientX - sx) / rect.width * 100;
        const dy = (ev.clientY - sy) / rect.height * 100;
        if (pos.includes('r')) el.w = clamp(o.w + dx, 5, 120);
        if (pos.includes('b')) el.h = clamp(o.h + dy, 4, 120);
        if (pos.includes('l')) { el.w = clamp(o.w - dx, 5, 120); el.x = o.x + dx; }
        if (pos.includes('t')) { el.h = clamp(o.h - dy, 4, 120); el.y = o.y + dy; }
        Object.assign(wrap.style, { left: el.x + '%', top: el.y + '%', width: el.w + '%', height: el.h + '%' });
      };
      const up = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); this.save(); };
      document.addEventListener('mousemove', move);
      document.addEventListener('mouseup', up);
    }

    /* ---------------------------------------------------------- text edit */
    _editText(wrap, inner, el) {
      inner.contentEditable = 'true';
      inner.focus();
      document.execCommand('selectAll', false, null);
      const finish = () => {
        inner.contentEditable = 'false';
        el.html = inner.innerHTML;
        inner.removeEventListener('blur', finish);
        this.save();
      };
      inner.addEventListener('blur', finish);
    }
    _applyText(prop, val) {
      const el = this._get(this.selected); if (!el || el.type !== 'text') return;
      el[prop] = val;
      const inner = $(`.ce-elm[data-id="${el.id}"] .ce-text`, this.stage);
      if (inner) inner.style[prop] = val;
      this.save();
    }
    _exec(cmd) {
      const el = this._get(this.selected); if (!el || el.type !== 'text') return;
      const inner = $(`.ce-elm[data-id="${el.id}"] .ce-text`, this.stage);
      if (inner) { inner.focus(); document.execCommand(cmd, false, null); el.html = inner.innerHTML; this.save(); }
    }

    /* --------------------------------------------------------- element ops */
    _z(dir) {
      const el = this._get(this.selected); if (!el) return;
      el.z = (el.z || 1) + dir * 1000;
      // normalise z after change
      this.pages[this.current].elements.sort((a, b) => (a.z || 1) - (b.z || 1))
        .forEach((e, i) => e.z = i + 1);
      this.renderPage(); this.select(el.id); this.save();
    }
    duplicate() {
      const el = this._get(this.selected); if (!el) return;
      const copy = JSON.parse(JSON.stringify(el));
      copy.x += 4; copy.y += 4;
      this._push(copy);
    }
    remove() {
      const el = this._get(this.selected); if (!el) return;
      const arr = this.pages[this.current].elements;
      arr.splice(arr.indexOf(el), 1);
      this.select(null); this.renderPage(); this.save();
    }

    /* --------------------------------------------------------- thumbnails */
    renderThumbs() {
      this.thumbs.innerHTML = '';
      this.pages.forEach((p, i) => {
        const t = document.createElement('button');
        t.className = 'ce-thumb' + (i === this.current ? ' active' : '') + (p.spread ? ' spread' : '');
        t.style.aspectRatio = this._aspectFor(p);
        t.style.background = p.bg;
        if (p.spread) {
          const f = document.createElement('div'); f.className = 'ce-thumb-fold'; t.appendChild(f);
        }
        t.title = p.name;
        const lbl = document.createElement('span'); lbl.textContent = p.name; t.appendChild(lbl);
        t.onclick = () => this.goPage(i);
        this.thumbs.appendChild(t);
      });
    }

    /* -------------------------------------------------------- persistence */
    _key() { return 'ce-draft-' + (this.opts.productId || 'default'); }
    save(notify) {
      try { localStorage.setItem(this._key(), JSON.stringify({ pages: this.pages })); } catch (e) {}
      if (notify) this._toast('Draft saved');
    }
    _load() {
      // Merge saved content (elements + bg) onto the current page STRUCTURE so
      // page metadata like `spread` is never lost when an older draft — saved
      // before that field existed — is restored.
      try {
        const raw = localStorage.getItem(this._key());
        if (!raw) return;
        const d = JSON.parse(raw);
        if (!d.pages || !Array.isArray(d.pages)) return;
        this.pages.forEach((page, i) => {
          const saved = d.pages[i];
          if (!saved) return;
          if (Array.isArray(saved.elements)) page.elements = saved.elements;
          if (saved.bg) page.bg = saved.bg;
          // `spread` is intentionally kept from the default template.
        });
      } catch (e) {}
    }
    exportJSON() {
      const data = JSON.stringify({ size: this.opts, pages: this.pages }, null, 2);
      const blob = new Blob([data], { type: 'application/json' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'card-design.json';
      a.click();
      this._toast('Exported design JSON');
    }

    _toHex(c) {
      if (c.startsWith('#')) return c.length === 4 ? '#' + [...c.slice(1)].map(x => x + x).join('') : c;
      const m = c.match(/\d+/g); if (!m) return '#222222';
      return '#' + m.slice(0, 3).map(n => (+n).toString(16).padStart(2, '0')).join('');
    }
    _toast(msg) {
      let t = $('#ce-toast', this.root);
      t.textContent = msg; t.classList.add('show');
      clearTimeout(this._tt); this._tt = setTimeout(() => t.classList.remove('show'), 1800);
    }
  }

  window.initCardEditor = function (opts) {
    const root = document.getElementById('card-editor');
    if (root) new CardEditor(root, opts || {});
  };
})();
