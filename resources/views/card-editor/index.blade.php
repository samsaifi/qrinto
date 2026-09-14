<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Card Editor — {{ config('app.name', 'Qrinto') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Montserrat:wght@400;600&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --brand:#10b981; --brand-600:#059669; --ink:#1f2937; --line:#e5e7eb;
      --bg:#f3f4f6; --panel:#ffffff; --muted:#6b7280;
    }
    *{box-sizing:border-box}
    html,body{margin:0;height:100%;font-family:Poppins,system-ui,sans-serif;color:var(--ink);background:var(--bg)}
    button{font-family:inherit;cursor:pointer}

    /* ---- shell ---- */
    #card-editor{display:flex;flex-direction:column;height:100vh}
    .ce-topbar{display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--panel);border-bottom:1px solid var(--line)}
    .ce-brand{font-weight:700;color:var(--brand-600);margin-right:12px;font-size:18px}
    .ce-tools{display:flex;gap:6px;flex:1}
    .ce-btn{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--line);background:#fff;border-radius:8px;padding:8px 12px;font-size:14px;color:var(--ink)}
    .ce-btn:hover{border-color:var(--brand);color:var(--brand-600)}
    .ce-btn.primary{background:var(--brand);border-color:var(--brand);color:#fff}
    .ce-btn.primary:hover{background:var(--brand-600)}
    .ce-btn.icon{padding:8px 10px}

    /* ---- body ---- */
    .ce-body{flex:1;position:relative;overflow:hidden}
    #ce-stage-wrap{position:absolute;inset:0;display:flex;align-items:center;justify-content:center}
    #ce-stage{position:relative;background:#fff;box-shadow:0 8px 40px rgba(0,0,0,.14);overflow:hidden}
    /* center book gutter for the inside spread — visually splits the
       left-side page and the right-side page */
    .ce-fold{position:absolute;left:50%;top:0;bottom:0;width:0;transform:translateX(-50%);pointer-events:none;z-index:1;
      box-shadow:-6px 0 10px -6px rgba(0,0,0,.18), 6px 0 10px -6px rgba(0,0,0,.18);
      border-left:1px solid var(--line)}
    /* faint page labels on each half of the spread */
    #ce-stage.is-spread::before,#ce-stage.is-spread::after{position:absolute;top:8px;font-size:10px;letter-spacing:.08em;
      color:#c7ccd3;text-transform:uppercase;z-index:1;pointer-events:none}
    #ce-stage.is-spread::before{content:'Left page';left:10px}
    #ce-stage.is-spread::after{content:'Right page';right:10px}

    /* ---- element ---- */
    .ce-elm{position:absolute;--element-border-inset:-4px}
    .ce-elm>.ce-text,.ce-elm>.ce-img,.ce-elm>.ce-stk{width:100%;height:100%}
    .ce-text{display:flex;align-items:center;justify-content:center;white-space:pre-wrap;line-height:1.2;outline:none;overflow:hidden}
    .ce-text[contenteditable="true"]{cursor:text;box-shadow:0 0 0 2px var(--brand)}
    .ce-img{object-fit:contain;user-select:none}
    .ce-stk{display:flex;align-items:center;justify-content:center;font-size:min(20vw,120px);line-height:1;user-select:none}
    .ce-elm:hover{outline:1px dashed rgba(16,185,129,.5)}
    .ce-elm.selected{outline:1.5px solid var(--brand)}
    .ce-handle{position:absolute;width:12px;height:12px;background:#fff;border:2px solid var(--brand);border-radius:50%;display:none;z-index:5}
    .ce-elm.selected .ce-handle{display:block}
    .ce-tl{left:-7px;top:-7px;cursor:nwse-resize}
    .ce-tr{right:-7px;top:-7px;cursor:nesw-resize}
    .ce-bl{left:-7px;bottom:-7px;cursor:nesw-resize}
    .ce-br{right:-7px;bottom:-7px;cursor:nwse-resize}
    .ce-rot{left:50%;top:-26px;transform:translateX(-50%);cursor:grab;background:var(--brand)}

    /* ---- floating text bar ---- */
    #ce-textbar{position:absolute;top:12px;left:50%;transform:translateX(-50%);display:none;gap:6px;align-items:center;
      background:var(--panel);border:1px solid var(--line);border-radius:10px;padding:6px 8px;box-shadow:0 6px 24px rgba(0,0,0,.12);z-index:20}
    #ce-textbar.open{display:flex}
    #ce-textbar select,#ce-textbar input[type=number]{border:1px solid var(--line);border-radius:6px;padding:5px 6px;font-size:13px}
    #ce-textbar input[type=color]{width:30px;height:30px;border:1px solid var(--line);border-radius:6px;background:#fff;padding:2px}
    .ce-tb-btn{width:32px;height:32px;border:1px solid var(--line);background:#fff;border-radius:6px;font-weight:700}
    .ce-tb-btn:hover{border-color:var(--brand);color:var(--brand-600)}
    .ce-sep{width:1px;height:22px;background:var(--line);margin:0 2px}

    /* ---- property panel ---- */
    #ce-props{position:absolute;right:16px;top:50%;transform:translateY(-50%);display:none;flex-direction:column;gap:6px;
      background:var(--panel);border:1px solid var(--line);border-radius:12px;padding:8px;box-shadow:0 6px 24px rgba(0,0,0,.12);z-index:20}
    #ce-props.open{display:flex}
    #ce-props .ce-btn{justify-content:center}
    #ce-props .danger{color:#dc2626;border-color:#fecaca}

    /* ---- sticker panel ---- */
    #ce-sticker-panel{position:absolute;left:16px;top:64px;width:220px;background:var(--panel);border:1px solid var(--line);
      border-radius:12px;padding:12px;box-shadow:0 6px 24px rgba(0,0,0,.12);display:none;z-index:20}
    #ce-sticker-panel.open{display:block}
    #ce-sticker-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}
    .ce-sticker-cell{font-size:24px;border:1px solid var(--line);background:#fff;border-radius:8px;padding:6px}
    .ce-sticker-cell:hover{border-color:var(--brand)}

    /* ---- zoom + page nav ---- */
    .ce-navbtn{position:absolute;top:50%;transform:translateY(-50%);width:40px;height:40px;border-radius:50%;background:#fff;
      border:1px solid var(--line);box-shadow:0 2px 10px rgba(0,0,0,.12);font-size:18px;z-index:15}
    #ce-prev{left:16px} #ce-next{right:16px}
    .ce-zoom{position:absolute;right:16px;bottom:16px;display:flex;gap:6px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:20px;padding:4px 8px;z-index:15}
    .ce-zoom button{width:26px;height:26px;border:none;background:transparent;font-size:16px}

    /* ---- footer thumbs ---- */
    .ce-footer{background:var(--panel);border-top:1px solid var(--line);padding:10px;display:flex;justify-content:center}
    #ce-thumbs{display:flex;gap:10px}
    .ce-thumb{width:52px;border:2px solid var(--line);border-radius:6px;background:#fff;position:relative;overflow:hidden}
    .ce-thumb.spread{width:96px}
    .ce-thumb-fold{position:absolute;left:50%;top:0;bottom:0;width:1px;background:var(--line)}
    .ce-thumb.active{border-color:var(--brand)}
    .ce-thumb span{position:absolute;bottom:2px;left:0;right:0;font-size:9px;color:var(--muted);text-align:center}

    #ce-page-label{font-size:13px;color:var(--muted);margin-left:auto}
    #ce-toast{position:fixed;bottom:90px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--ink);color:#fff;
      padding:10px 18px;border-radius:20px;opacity:0;transition:.25s;z-index:50;pointer-events:none;font-size:14px}
    #ce-toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
  </style>
</head>
<body>
  <div id="card-editor">
    <!-- Top bar -->
    <div class="ce-topbar">
      <span class="ce-brand">✎ Card Editor</span>
      <div class="ce-tools">
        <button class="ce-btn" id="ce-add-text">➕ Text</button>
        <button class="ce-btn" id="ce-add-image">🖼 Image</button>
        <button class="ce-btn" id="ce-add-sticker">✦ Sticker</button>
        <input type="file" id="ce-file" accept="image/*" hidden>
      </div>
      <button class="ce-btn" id="ce-export">Export JSON</button>
      <button class="ce-btn primary" id="ce-save">Save draft</button>
    </div>

    <!-- Body -->
    <div class="ce-body">
      <div id="ce-stage-wrap">
        <div id="ce-stage"></div>
      </div>

      <!-- Floating text formatting bar -->
      <div id="ce-textbar">
        <select id="ce-font" title="Font"></select>
        <input type="number" id="ce-fontsize" min="8" max="200" value="32" title="Size" style="width:58px">
        <input type="color" id="ce-color" value="#222222" title="Color">
        <span class="ce-sep"></span>
        <button class="ce-tb-btn" id="ce-bold" title="Bold"><b>B</b></button>
        <button class="ce-tb-btn" id="ce-italic" title="Italic"><i>I</i></button>
        <button class="ce-tb-btn" id="ce-underline" title="Underline"><u>U</u></button>
        <span class="ce-sep"></span>
        <button class="ce-tb-btn" id="ce-align-left" title="Align left">⬅</button>
        <button class="ce-tb-btn" id="ce-align-center" title="Align center">☰</button>
        <button class="ce-tb-btn" id="ce-align-right" title="Align right">➡</button>
      </div>

      <!-- Property panel -->
      <div id="ce-props">
        <button class="ce-btn" id="ce-front" title="Bring forward">⬆ Front</button>
        <button class="ce-btn" id="ce-back" title="Send backward">⬇ Back</button>
        <button class="ce-btn" id="ce-dup" title="Duplicate">⧉ Copy</button>
        <button class="ce-btn danger" id="ce-del" title="Delete">🗑 Delete</button>
      </div>

      <!-- Sticker picker -->
      <div id="ce-sticker-panel">
        <div style="font-size:13px;color:var(--muted);margin-bottom:8px">Pick a sticker</div>
        <div id="ce-sticker-grid"></div>
      </div>

      <!-- Page nav -->
      <button class="ce-navbtn" id="ce-prev">‹</button>
      <button class="ce-navbtn" id="ce-next">›</button>

      <!-- Zoom -->
      <div class="ce-zoom">
        <button id="ce-zoom-out">−</button>
        <button id="ce-zoom-in">+</button>
        <span id="ce-page-label"></span>
      </div>
    </div>

    <!-- Footer thumbnails -->
    <div class="ce-footer">
      <div id="ce-thumbs"></div>
    </div>

    <div id="ce-toast"></div>
  </div>

  <script src="{{ asset('js/card-editor.js') }}?v={{ time() }}"></script>
  <script>
    initCardEditor({
      productId: @json($product->id ?? null),
      width: @json($size['width']),
      height: @json($size['height']),
      unit: @json($size['unit']),
    });
  </script>
</body>
</html>
