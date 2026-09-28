<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Card Editor — {{ config('app.name', 'Qrinto') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@400;700&family=Montserrat:wght@400;600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --primary:#16c784; --primary-600:#12a869; --primary-50:#e9faf2;
      --workspace:#f1f1f1; --white:#ffffff; --text:#202124; --muted:#9ca3af;
      --border:#e5e7eb; --line-soft:#eeeeee;
      --hdr-h:58px; --ctx-h:56px; --foot-h:104px; --layout-w:184px;
      --shadow-card:0 2px 8px rgba(0,0,0,.08);
      --shadow-pop:0 6px 24px rgba(16,24,40,.10);
    }
    *{box-sizing:border-box}
    html,body{margin:0;height:100%;overflow:hidden;
      font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
      color:var(--text);background:var(--workspace);-webkit-font-smoothing:antialiased}
    button{font-family:inherit;cursor:pointer;color:inherit}
    input,select{font-family:inherit}

    /* ---------------------------------------------------------------- shell */
    #qe{display:flex;flex-direction:column;height:100vh;width:100vw;overflow:hidden}

    /* --------------------------------------------------------------- header */
    .qe-header{height:var(--hdr-h);flex:0 0 var(--hdr-h);position:relative;display:flex;align-items:center;
      background:var(--white);border-bottom:1px solid var(--line-soft);z-index:40}
    .qe-logo{position:absolute;left:24px;top:0;height:100%;display:flex;align-items:center;z-index:2}
    .qe-logo img{height:37px;width:auto;display:block}
    /* tools centered over the canvas area (full width minus the left panel) */
    .qe-htools{flex:1;height:100%;display:flex;align-items:center;justify-content:center;gap:10px;
      padding-left:var(--layout-w);padding-right:24px}
    .qe-hright{position:absolute;right:24px;top:0;height:100%;display:flex;align-items:center;gap:8px;z-index:2}

    /* header tool button (Sticker / Image / Video) */
    .qe-tool{display:inline-flex;align-items:center;gap:7px;height:38px;padding:0 15px;border-radius:20px;
      border:1px solid var(--border);background:var(--white);font-size:14px;font-weight:500;color:var(--text);transition:.15s}
    .qe-tool svg{width:18px;height:18px;stroke:var(--text);fill:none;stroke-width:1.6}
    .qe-tool:hover{border-color:#d0d5dd;background:#fafafa}
    .qe-tool.active{background:var(--primary-50);border-color:var(--primary);color:var(--primary-600);font-weight:600}
    .qe-tool.active svg{stroke:var(--primary-600)}

    .qe-icbtn{width:36px;height:36px;display:grid;place-items:center;border-radius:9px;border:1px solid transparent;background:transparent;transition:.15s}
    .qe-icbtn svg{width:20px;height:20px;stroke:var(--text);fill:none;stroke-width:1.7}
    .qe-icbtn:hover{background:#f3f4f6}
    .qe-icbtn:disabled{cursor:default}
    .qe-icbtn:disabled svg{stroke:#d1d5db}
    .qe-icbtn.on{background:var(--primary-50)}
    .qe-icbtn.on svg{stroke:var(--primary-600)}
    .qe-divider{width:1px;height:24px;background:var(--border);margin:0 4px}
    .qe-link{background:none;border:none;font-size:14px;font-weight:600;color:var(--primary-600);padding:8px 10px;border-radius:8px}
    .qe-link:hover{background:var(--primary-50)}
    .qe-next{height:40px;min-width:92px;padding:0 18px;border:none;border-radius:20px;background:var(--primary);
      color:#fff;font-size:14px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:.15s}
    .qe-next:hover{background:var(--primary-600)}

    /* ------------------------------------------------------- context toolbar */
    /* Always reserve the context-toolbar height so opening/closing it never
       resizes the canvas. Contents are only shown when an element is selected. */
    .qe-ctx{height:var(--ctx-h);flex:0 0 var(--ctx-h);display:flex;align-items:center;gap:8px;
      background:var(--white);border-bottom:1px solid var(--line-soft);padding:0 18px;overflow-x:auto;z-index:30}
    .qe-ctx:not(.show){visibility:hidden}
    .qe-ctx .grp{display:flex;align-items:center;gap:6px}
    .qe-ctx select,.qe-ctx .qe-num{height:36px;border:1px solid var(--border);border-radius:9px;background:#fff;font-size:13px}
    .qe-ctx select{padding:0 28px 0 10px;min-width:120px;appearance:none;
      background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'><path d='M6 9l6 6 6-6'/></svg>");
      background-repeat:no-repeat;background-position:right 9px center}
    .qe-ctx select:focus,.qe-ctx .qe-num:focus-within{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-50)}
    .qe-num{display:inline-flex;align-items:center;overflow:hidden}
    .qe-num button{width:32px;height:100%;border:none;background:#fff;font-size:16px;color:var(--muted)}
    .qe-num button:hover{background:#f3f4f6;color:var(--text)}
    .qe-num input{width:42px;height:100%;border:none;text-align:center;font-size:13px;-moz-appearance:textfield}
    .qe-num input::-webkit-outer-spin-button,.qe-num input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
    .qe-tb{height:36px;min-width:36px;padding:0 9px;border:1px solid var(--border);border-radius:9px;background:#fff;
      display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:13px;font-weight:500}
    .qe-tb svg{width:18px;height:18px;stroke:var(--text);fill:none;stroke-width:1.7}
    .qe-tb:hover{border-color:#d0d5dd;background:#fafafa}
    .qe-tb.on{background:var(--primary-50);border-color:var(--primary);color:var(--primary-600)}
    .qe-tb.on svg{stroke:var(--primary-600)}
    .qe-tb.danger:hover{border-color:#fca5a5;color:#dc2626;background:#fef2f2}
    .qe-tb.danger:hover svg{stroke:#dc2626}
    .qe-color{width:34px;height:34px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px var(--border);cursor:pointer;padding:0;overflow:hidden}
    .qe-color::-webkit-color-swatch-wrapper{padding:0}
    .qe-color::-webkit-color-swatch{border:none;border-radius:50%}
    .qe-ctxsep{width:1px;height:26px;background:var(--border);margin:0 4px;flex:0 0 auto}
    .qe-slider{display:inline-flex;align-items:center;gap:8px}
    .qe-slider svg{width:16px;height:16px;stroke:var(--muted);fill:none;stroke-width:1.7}
    .qe-slider input[type=range]{width:120px;accent-color:var(--primary)}

    /* menu popover (magic styles / filters / case) */
    .qe-menu{position:absolute;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow-pop);
      padding:6px;z-index:60;display:none;min-width:160px}
    .qe-menu.open{display:block}
    .qe-menu button{display:flex;width:100%;align-items:center;gap:8px;padding:9px 12px;border:none;background:none;
      border-radius:8px;font-size:13px;text-align:left}
    .qe-menu button:hover{background:#f3f4f6}
    .qe-menu .swatch{width:28px;height:22px;border-radius:5px;border:1px solid var(--border);flex:0 0 auto}

    /* ------------------------------------------------------------------ body */
    .qe-body{flex:1;display:flex;min-height:0;position:relative}
    /* right column holds the context toolbar (over the canvas only) + workspace,
       so the left side panel can run all the way up to just below the header */
    .qe-main{flex:1;display:flex;flex-direction:column;min-width:0;min-height:0}

    /* left panels (layout / sticker) */
    .qe-side{flex:0 0 auto;background:var(--white);border-right:1px solid var(--line-soft);
      display:none;flex-direction:column;min-height:0;z-index:20}
    .qe-side.open{display:flex}
    #qe-layoutside{width:184px}
    #qe-stickerside{width:340px}
    .qe-side-head{display:flex;align-items:center;justify-content:space-between;padding:16px 16px 10px;flex:0 0 auto}
    .qe-side-head h3{margin:0;font-size:15px;font-weight:600}
    .qe-side-close{width:30px;height:30px;border:none;background:none;border-radius:8px;font-size:20px;color:var(--muted);line-height:1}
    .qe-side-close:hover{background:#f3f4f6;color:var(--text)}
    .qe-side-scroll{flex:1;overflow-y:auto;padding:4px 16px 20px}
    .qe-side-scroll::-webkit-scrollbar{width:8px}
    .qe-side-scroll::-webkit-scrollbar-thumb{background:#e0e3e8;border-radius:8px}

    /* layout previews */
    #qe-layoutlist{display:flex;flex-direction:column;gap:12px}
    .qe-lay{position:relative;border:1.5px solid var(--border);border-radius:10px;background:#fff;padding:6px;cursor:pointer;transition:.15s}
    .qe-lay:hover{border-color:#cbd5e1}
    .qe-lay.sel{border-color:var(--primary);background:var(--primary-50)}
    .qe-lay .prev{position:relative;width:100%;background:#fff;border-radius:6px;overflow:hidden;box-shadow:inset 0 0 0 1px #f0f0f0}
    .qe-lay .crown{position:absolute;top:-8px;right:-6px;font-size:15px;display:none}
    .qe-lay.sel .crown{display:block}
    .qe-mini{position:absolute;box-sizing:border-box}
    .qe-mini.image{background:#eef1f5;border:1px dashed #cfd6df;display:flex;align-items:center;justify-content:center}
    .qe-mini.image svg{width:38%;height:38%;stroke:#b8c0cc;fill:none;stroke-width:1.6;max-width:22px}
    .qe-mini.text{display:flex;flex-direction:column;justify-content:center;gap:3px;overflow:hidden}
    .qe-mini.text i{display:block;height:3px;border-radius:2px;background:#d4d9e0}
    .qe-mini.sticker{display:flex;align-items:center;justify-content:center;font-size:10px}

    /* sticker sidebar */
    .qe-search{position:relative;margin-bottom:12px}
    .qe-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;stroke:var(--muted);fill:none;stroke-width:1.8}
    .qe-search input{width:100%;height:40px;border:1px solid var(--border);border-radius:22px;padding:0 14px 0 36px;font-size:14px}
    .qe-search input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-50)}
    #qe-stkcat{width:100%;height:40px;border:1px solid var(--border);border-radius:10px;padding:0 30px 0 12px;font-size:14px;
      appearance:none;margin-bottom:12px;background:#fff;
      background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'><path d='M6 9l6 6 6-6'/></svg>");
      background-repeat:no-repeat;background-position:right 12px center}
    .qe-magic{width:100%;height:44px;border:none;border-radius:24px;color:#fff;font-size:14px;font-weight:600;margin-bottom:16px;
      display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(90deg,#8b5cf6,#ec4899)}
    .qe-magic:hover{filter:brightness(1.04)}
    #qe-stkgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
    .qe-stk{aspect-ratio:1;border:none;background:none;border-radius:10px;font-size:34px;display:flex;align-items:center;justify-content:center;transition:.15s}
    .qe-stk:hover{background:#f3f4f6;transform:scale(1.08)}

    /* --------------------------------------------------------- workspace */
    .qe-work{flex:1;position:relative;display:flex;align-items:center;justify-content:center;
      background:var(--workspace);min-width:0;padding:28px}
    #qe-stage{position:relative;transition:width .2s,height .2s}
    .qe-page{position:absolute;top:0;background:#fff;box-shadow:var(--shadow-card);overflow:hidden}
    #qe-stage.spread .qe-fold{position:absolute;top:0;bottom:0;left:50%;width:1px;transform:translateX(-.5px);
      background:linear-gradient(180deg,rgba(0,0,0,.05),rgba(0,0,0,.12),rgba(0,0,0,.05));z-index:6;pointer-events:none}

    /* elements */
    .qe-el{position:absolute;cursor:move;user-select:none}
    .qe-el .body{width:100%;height:100%;pointer-events:none}
    .qe-el.text .body{display:flex;flex-direction:column;justify-content:center;white-space:pre-wrap;word-break:break-word;overflow:hidden}
    .qe-el.text.editing{cursor:text}
    .qe-el.text.editing .body{pointer-events:auto;cursor:text;outline:none}
    .qe-el img.body{object-fit:cover;display:block}
    .qe-el.sticker .body{display:flex;align-items:center;justify-content:center;line-height:1}
    .qe-el.placeholder{border:1.5px dashed #cbd5e1;background:#f8fafc;border-radius:6px;display:flex;align-items:center;justify-content:center}
    .qe-el.placeholder .ph{display:flex;flex-direction:column;align-items:center;gap:6px;color:#94a3b8;font-size:13px;pointer-events:none}
    .qe-el.placeholder .ph svg{width:26px;height:26px;stroke:#94a3b8;fill:none;stroke-width:1.6}
    .qe-el:hover{outline:1px solid rgba(22,199,132,.4)}
    .qe-el.sel{outline:2px solid var(--primary);z-index:50}
    .qe-el.sel:hover{outline:2px solid var(--primary)}
    .qe-h{position:absolute;width:12px;height:12px;background:#fff;border:2px solid var(--primary);border-radius:50%;display:none;z-index:5}
    .qe-el.sel .qe-h{display:block}
    .qe-h.tl{left:-7px;top:-7px;cursor:nwse-resize}
    .qe-h.tr{right:-7px;top:-7px;cursor:nesw-resize}
    .qe-h.bl{left:-7px;bottom:-7px;cursor:nesw-resize}
    .qe-h.br{right:-7px;bottom:-7px;cursor:nwse-resize}
    .qe-h.rot{left:50%;top:-30px;transform:translateX(-50%);cursor:grab;background:var(--primary)}
    .qe-el.sel .qe-rotstem{position:absolute;left:50%;top:-18px;width:1px;height:18px;background:var(--primary);display:block}
    .qe-rotstem{display:none}

    /* nav arrows */
    .qe-nav{position:absolute;top:50%;transform:translateY(-50%);width:40px;height:40px;border-radius:50%;background:#fff;
      border:1px solid var(--border);box-shadow:var(--shadow-card);display:grid;place-items:center;z-index:15;transition:.15s}
    .qe-nav svg{width:20px;height:20px;stroke:var(--text);fill:none;stroke-width:2}
    .qe-nav:hover{transform:translateY(-50%) scale(1.06);border-color:#d0d5dd}
    .qe-nav:disabled{opacity:.35;cursor:default}
    #qe-prev{left:20px} #qe-next{right:20px}

    /* --------------------------------------------------------- thumbnails */
    .qe-foot{height:var(--foot-h);flex:0 0 var(--foot-h);background:var(--white);border-top:1px solid var(--line-soft);
      display:flex;align-items:center;justify-content:center;z-index:20}
    #qe-thumbs{display:flex;gap:20px;align-items:center;max-width:100%;overflow-x:auto;padding:0 24px;height:100%}
    .qe-thumb{position:relative;flex:0 0 auto;height:74px;border:1.5px solid var(--border);border-radius:4px;background:#fff;
      cursor:pointer;transition:.15s;overflow:hidden;box-shadow:var(--shadow-card)}
    .qe-thumb:hover{border-color:#cbd5e1;transform:translateY(-2px)}
    .qe-thumb.active{border-color:var(--primary);box-shadow:0 0 0 2px var(--primary-50),var(--shadow-card)}
    .qe-thumb .mini{position:absolute;inset:0}
    .qe-thumb .num{position:absolute;bottom:0;left:0;right:0;text-align:center;font-size:9px;color:var(--muted);
      background:rgba(255,255,255,.9);padding:1px 0}
    .qe-thumb.active .num{background:var(--primary);color:#fff;font-weight:600}
    .qe-thumb.spread .midline{position:absolute;top:0;bottom:0;left:50%;width:1px;background:var(--border)}

    /* toast */
    #qe-toast{position:fixed;bottom:calc(var(--foot-h) + 16px);left:50%;transform:translateX(-50%) translateY(16px);
      background:var(--text);color:#fff;padding:10px 18px;border-radius:22px;opacity:0;transition:.25s;z-index:80;
      pointer-events:none;font-size:14px}
    #qe-toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

    /* ------------------------------------------------------------ responsive */
    @media (max-width:1024px){
      #qe-stickerside{width:280px}
      #qe-layoutside{width:160px}
    }
    @media (max-width:640px){
      :root{--hdr-h:52px}
      .qe-header{padding:0 12px;gap:8px}
      .qe-logo img{height:30px}
      .qe-htools{position:fixed;bottom:0;left:0;right:0;height:60px;background:#fff;border-top:1px solid var(--line-soft);
        justify-content:space-around;gap:0;z-index:45;padding:0 8px}
      .qe-hright .qe-link,.qe-divider{display:none}
      .qe-side{position:fixed;inset:var(--hdr-h) 0 0 0;width:100%!important;z-index:60}
      .qe-foot{--foot-h:88px;margin-bottom:60px}
      .qe-work{padding:12px}
    }
  </style>
</head>
<body>
  <div id="qe">
    <!-- ===================================================== MAIN HEADER -->
    <header class="qe-header">
      <div class="qe-logo"><img src="{{ asset('logo/Qrinto-logo-med.png') }}" alt="Qrinto"></div>

      <div class="qe-htools">
        <button class="qe-tool" id="qe-t-text" data-tool="text">
          <svg viewBox="0 0 24 24"><path d="M5 4h14v3M12 4v16M9 20h6"/></svg>
          Text
        </button>
        <button class="qe-tool" id="qe-t-sticker" data-tool="sticker">
          <svg viewBox="0 0 24 24"><path d="M4 12a8 8 0 1 0 8-8 8 8 0 0 0-8 8Z"/><path d="M12 4v6a2 2 0 0 0 2 2h6"/><path d="M9 10h.01M15 10h.01M9.5 14a3 3 0 0 0 5 0"/></svg>
          Sticker
        </button>
        <button class="qe-tool" id="qe-t-image" data-tool="image">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m4 18 5-5 4 4 3-3 4 4"/></svg>
          Image
        </button>
      </div>

      <div class="qe-hright">
        <button class="qe-icbtn" id="qe-undo" title="Undo" disabled>
          <svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-1"/></svg>
        </button>
        <button class="qe-icbtn" id="qe-redo" title="Redo" disabled>
          <svg viewBox="0 0 24 24"><path d="m15 14 5-5-5-5"/><path d="M20 9H9a5 5 0 0 0 0 10h1"/></svg>
        </button>
        <span class="qe-divider"></span>
        <button class="qe-icbtn qe-orient" id="qe-orient" title="Switch to landscape">
          <svg viewBox="0 0 24 24"><rect x="4" y="6" width="10" height="14" rx="2"/><path d="M14 13h5a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-5"/><path d="m16 6-2-2 2-2" transform="translate(1.5 3.2)"/></svg>
        </button>
        <span class="qe-divider"></span>
        <button class="qe-link" id="qe-save">Save draft</button>
        <button class="qe-next" id="qe-continue">Next
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#fff" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </div>
    </header>

    <!-- ============================================================ BODY -->
    <div class="qe-body">
      <!-- Change layout panel -->
      <aside class="qe-side" id="qe-layoutside">
        <div class="qe-side-head"><h3>Change layout</h3></div>
        <div class="qe-side-scroll"><div id="qe-layoutlist"></div></div>
      </aside>

      <!-- Sticker panel -->
      <aside class="qe-side" id="qe-stickerside">
        <div class="qe-side-head"><h3>Stickers</h3><button class="qe-side-close" id="qe-stk-close">&times;</button></div>
        <div class="qe-side-scroll">
          <div class="qe-search">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="qe-stksearch" placeholder="Search stickers">
          </div>
          <select id="qe-stkcat"></select>
          <button class="qe-magic" id="qe-stkmagic">✧ Create Magic sticker</button>
          <div id="qe-stkgrid"></div>
        </div>
      </aside>

      <!-- Main column: context toolbar (over canvas only) + workspace -->
      <div class="qe-main">
        <div class="qe-ctx" id="qe-ctx"></div>
        <div class="qe-work" id="qe-work">
          <button class="qe-nav" id="qe-prev" title="Previous"><svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></button>
          <div id="qe-stage"></div>
          <button class="qe-nav" id="qe-next" title="Next"><svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>
        </div>
      </div>
    </div>

    <!-- ====================================================== THUMBNAILS -->
    <div class="qe-foot"><div id="qe-thumbs"></div></div>

    <input type="file" id="qe-file" accept="image/*" hidden>
    <div id="qe-toast"></div>
  </div>

  <script>window.QRINTO_LOGO_URL = @json(asset('logo/Qrinto-logo-small.png'));</script>
  <script src="{{ asset('js/card-editor.js') }}?v={{ time() }}"></script>
  <script>
    initCardEditor({
      productId: @json($product->id ?? null),
      width: @json($size['width'] ?? 5),
      height: @json($size['height'] ?? 7),
      unit: @json($size['unit'] ?? 'in'),
    });
  </script>
</body>
</html>
