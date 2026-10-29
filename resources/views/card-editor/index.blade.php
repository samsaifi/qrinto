<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Card editor — {{ config('app.name', 'Qrinto') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Great+Vibes&family=Pinyon+Script&family=Caveat:wght@400;700&family=Bebas+Neue&family=Josefin+Sans:wght@300;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<style>
:root{
  --bg:#F2F4F3;--surface:#FFFFFF;--surface-2:#F4F7F5;--ink:#14201A;--muted:#5E6B65;--line:#DDE4E0;
  --brand:#11A463;--brand-strong:#0B7A49;--brand-soft:#E2F5EB;--stage:#E6EBE8;--dot:rgba(20,32,26,.10);
  --danger:#C2412D;--warn:#B7791F;
  --shadow:0 1px 2px rgba(20,32,26,.06),0 8px 24px rgba(20,32,26,.08);
  --page-shadow:0 2px 4px rgba(0,0,0,.06),0 18px 40px rgba(20,32,26,.18);
  --inv:1;
  color-scheme:light;
  box-sizing:border-box;
  padding-top:env(safe-area-inset-top,0px);
  padding-bottom:env(safe-area-inset-bottom,0px);
}
@media (prefers-color-scheme:dark){
  :root:not([data-theme="light"]){
    --bg:#111613;--surface:#19201C;--surface-2:#202823;--ink:#E7EEEA;--muted:#9AA8A1;--line:#2C3631;
    --brand:#2BC77F;--brand-strong:#5FDCA0;--brand-soft:#173527;--stage:#0E1310;--dot:rgba(255,255,255,.07);
    --shadow:0 1px 2px rgba(0,0,0,.3),0 8px 24px rgba(0,0,0,.35);color-scheme:dark;
  }
}
:root[data-theme="dark"]{
  --bg:#111613;--surface:#19201C;--surface-2:#202823;--ink:#E7EEEA;--muted:#9AA8A1;--line:#2C3631;
  --brand:#2BC77F;--brand-strong:#5FDCA0;--brand-soft:#173527;--stage:#0E1310;--dot:rgba(255,255,255,.07);
  --shadow:0 1px 2px rgba(0,0,0,.3),0 8px 24px rgba(0,0,0,.35);color-scheme:dark;
}
html{scroll-padding-top:env(safe-area-inset-top,0px)}
html,body{height:100%;margin:0}
*,*::before,*::after{box-sizing:inherit}
body{background:var(--bg);color:var(--ink);font-family:'Onest',ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;font-size:14px;overflow:hidden;-webkit-font-smoothing:antialiased}
button{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--brand);outline-offset:2px}
[hidden]{display:none!important}

.app{height:100%;display:grid;grid-template-rows:60px minmax(0,1fr) auto}

/* Header */
.top{display:flex;align-items:center;gap:10px;padding:0 14px;background:var(--surface);border-bottom:1px solid var(--line);min-width:0}
.brand{display:flex;align-items:center;flex:none}
.brand img{height:40px;width:auto;display:block}
.vsep{width:1px;height:26px;background:var(--line);margin:0 4px;flex:none}
.title-field{display:flex;align-items:center;gap:6px;border:1px solid transparent;border-radius:8px;padding:5px 8px;color:var(--muted);min-width:0}
.title-field:hover,.title-field:focus-within{border-color:var(--line)}
.title-field input{border:0;background:transparent;font:inherit;font-weight:600;font-size:15px;color:var(--ink);width:clamp(110px,22vw,260px);outline:none;min-width:0}
.spacer{flex:1}
.iconbtn{width:36px;height:36px;display:grid;place-items:center;border-radius:8px;border:0;background:transparent;color:var(--ink);cursor:pointer;flex:none}
.iconbtn:hover{background:var(--surface-2)}
.iconbtn:disabled{opacity:.35;cursor:default;background:transparent}
.status{font-size:13px;color:var(--muted);display:flex;gap:6px;align-items:center;white-space:nowrap;padding:0 6px}
.status.saving svg{opacity:.4}
.status.error{color:var(--danger)}
.btn{height:38px;padding:0 16px;border-radius:999px;border:1px solid var(--line);background:var(--surface);font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;white-space:nowrap;flex:none}
.btn:hover{background:var(--surface-2)}
.btn.primary{background:var(--brand);border-color:var(--brand);color:#fff}
.btn.primary:hover{background:var(--brand-strong);border-color:var(--brand-strong)}
.btn.wide{width:100%;margin-top:14px}

/* Main */
.main{position:relative;display:grid;grid-template-columns:76px minmax(0,1fr);min-height:0}
.rail{grid-column:1;background:var(--surface);border-right:1px solid var(--line);display:flex;flex-direction:column;gap:2px;padding:10px 7px;overflow-y:auto;z-index:21}
.rail button{display:flex;flex-direction:column;align-items:center;gap:5px;padding:10px 2px;border-radius:10px;border:0;background:transparent;color:var(--muted);font-size:11.5px;font-weight:500;cursor:pointer}
.rail button:hover{background:var(--surface-2);color:var(--ink)}
.rail button[aria-pressed="true"]{background:var(--brand-soft);color:var(--brand-strong)}

.panel{position:absolute;left:76px;top:0;bottom:0;z-index:20;width:300px;background:var(--surface);border-right:1px solid var(--line);overflow-y:auto;overflow-x:hidden;box-shadow:6px 0 24px rgba(20,32,26,.08)}
.panel-inner{padding:16px 18px 28px}
.phead{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px}
.phead h2{font-size:16px;margin:0;font-weight:650}
.hint{color:var(--muted);font-size:13px;line-height:1.45;margin:0 0 14px}
.hint.small{font-size:12.5px;margin-top:10px}
.sec{font-size:13px;font-weight:600;margin:20px 0 8px}
.tiles{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.tiles.one{grid-template-columns:1fr}
.tile{border:1px solid var(--line);border-radius:10px;padding:10px;background:var(--surface-2);cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:8px;font-size:12.5px;font-weight:500;text-align:center}
.tile:hover{border-color:var(--brand)}
.addtext{width:100%;text-align:left;border:1px solid var(--line);border-radius:10px;background:var(--surface);padding:12px 14px;margin-bottom:8px;cursor:pointer;display:flex;align-items:center;gap:10px;line-height:1.15}
.addtext:hover{border-color:var(--brand);background:var(--surface-2)}
.pair{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;min-height:82px;width:100%;background:#fff;border-radius:6px;color:#222;overflow:hidden;padding:6px}
.drop{border:1.5px dashed var(--line);border-radius:12px;padding:22px 14px;text-align:center;color:var(--muted);cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:6px;background:transparent;width:100%;font-size:12.5px}
.drop b{color:var(--ink);font-size:14px}
.drop:hover,.drop.over{border-color:var(--brand);background:var(--brand-soft)}
.lib{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.lib button{padding:0;border:1px solid var(--line);border-radius:8px;overflow:hidden;aspect-ratio:1;cursor:pointer;background:var(--surface-2)}
.lib button:hover{border-color:var(--brand)}
.lib img{width:100%;height:100%;object-fit:cover;display:block}
.stickers{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}
.stickers button{aspect-ratio:1;border:0;border-radius:10px;background:var(--surface-2);font-size:28px;cursor:pointer;line-height:1}
.stickers button:hover{background:var(--brand-soft)}
.swatches{display:flex;flex-wrap:wrap;gap:10px}
.sw{width:32px;height:32px;border-radius:50%;border:1px solid rgba(0,0,0,.14);cursor:pointer;padding:0}
.sw[aria-pressed="true"]{box-shadow:0 0 0 2px var(--surface),0 0 0 4px var(--brand)}
.custom{display:flex;align-items:center;gap:10px;margin-top:14px;font-size:13px;font-weight:500;cursor:pointer}
.custom input{width:36px;height:36px;border:1px solid var(--line);border-radius:8px;padding:2px;background:var(--surface);cursor:pointer}
.field-label{display:block;font-size:13px;font-weight:600;margin:14px 0 6px}
.input{width:100%;height:40px;border:1px solid var(--line);border-radius:8px;padding:0 12px;font:inherit;background:var(--surface);color:var(--ink)}
.input:focus{outline:2px solid var(--brand);outline-offset:-1px}
.err{color:var(--danger);font-size:12.5px;margin-top:6px}
.qr-prev{margin-top:14px;border-radius:12px;background:var(--surface-2);display:grid;place-items:center;height:170px;color:var(--muted);font-size:12.5px;text-align:center;padding:10px}
.qr-prev img{width:130px;height:130px;background:#fff;padding:8px;border-radius:8px}

/* Stage */
.stagewrap{grid-column:2;position:relative;min-width:0;min-height:0;display:flex;flex-direction:column;background:var(--stage)}
.stage{flex:1;overflow:auto;display:flex;padding:86px 76px 30px;background-image:radial-gradient(var(--dot) 1px,transparent 1.3px);background-size:18px 18px}
.stage.dropping{box-shadow:inset 0 0 0 3px var(--brand)}
.canvas-col{margin:auto;display:flex;flex-direction:column;align-items:center;gap:12px}
.sheet-wrap{position:relative;flex:none}
.sheet{position:absolute;left:0;top:0;transform-origin:0 0;display:flex;box-shadow:var(--page-shadow)}
.page{position:relative;width:360px;height:504px;flex:none}
.content{position:absolute;inset:0;overflow:hidden;background:#fff}
.spread>.page+.page .content{box-shadow:inset 14px 0 18px -16px rgba(0,0,0,.35)}
.spread>.page:first-child .content{box-shadow:inset -10px 0 14px -14px rgba(0,0,0,.25)}
.caps{display:flex;color:var(--muted);font-size:12.5px;font-weight:500}
.caps span{flex:1;text-align:center}
.sheet.flipping{box-shadow:none;pointer-events:none}
.sheet.flipping>.page{box-shadow:var(--page-shadow)}
.ghost{position:absolute;top:0;z-index:5;pointer-events:none}

.el{position:absolute;touch-action:none;cursor:move;user-select:none;-webkit-user-select:none}
.txt{padding:4px 6px;white-space:pre-wrap;overflow-wrap:break-word;outline:none;min-height:1.2em}
.txt:empty::before{content:'Type something';opacity:.35}
.sheet .el-text:hover{outline:calc(1px * var(--inv)) dashed rgba(17,164,99,.8)}
.sheet .el-text.editing{outline:calc(1.5px * var(--inv)) dashed var(--brand);cursor:text;user-select:text;-webkit-user-select:text}
.el-text.editing .txt{user-select:text;-webkit-user-select:text;cursor:text}
.el-photo{overflow:hidden}
.shape-rounded{border-radius:14px}
.shape-circle{border-radius:50%}
.shape-square{border-radius:0}
.el-photo img,.el-qr img{width:100%;height:100%;display:block;pointer-events:none}
.el.empty{background:rgba(20,32,26,.05);border:1.5px dashed rgba(20,32,26,.28);display:grid;place-items:center;cursor:pointer}
.ph{display:flex;flex-direction:column;align-items:center;gap:4px;color:#46514C;font-family:'Onest',system-ui,sans-serif;font-size:13px;font-weight:500;text-align:center;padding:4px}
.ph small{color:#7A8580;font-size:11px;font-weight:400}
.el-sticker{display:grid;place-items:center}
.el-sticker span{line-height:1}
.el-qr{background:#fff;padding:6%;border-radius:6px}
.el-qr.empty{padding:0}
.backmark{position:absolute;bottom:18px;left:0;right:0;text-align:center;font:500 10px 'Onest',system-ui,sans-serif;color:rgba(0,0,0,.45);pointer-events:none}
.backmark b{color:#11A463;font-weight:700}
.safe{position:absolute;inset:12px;border:calc(1px * var(--inv)) dashed rgba(194,65,45,.6);pointer-events:none;display:none}
.show-safe .safe{display:block}
.guide-v,.guide-h{position:absolute;pointer-events:none;display:none}
.guide-v{top:0;bottom:0;left:50%;border-left:calc(1px * var(--inv)) solid #E2457A}
.guide-h{left:0;right:0;top:50%;border-top:calc(1px * var(--inv)) solid #E2457A}
.guide-v.on,.guide-h.on{display:block}
.selbox{position:absolute;outline:calc(1.5px * var(--inv)) solid var(--brand);pointer-events:none;z-index:3}
.handle{position:absolute;width:calc(12px * var(--inv));height:calc(12px * var(--inv));background:#fff;border:calc(1.5px * var(--inv)) solid var(--brand);border-radius:50%;transform:translate(-50%,-50%);pointer-events:auto;touch-action:none}
.h-nw{left:0;top:0;cursor:nwse-resize}.h-se{left:100%;top:100%;cursor:nwse-resize}
.h-ne{left:100%;top:0;cursor:nesw-resize}.h-sw{left:0;top:100%;cursor:nesw-resize}
.h-e,.h-w{top:50%;width:calc(7px * var(--inv));height:calc(20px * var(--inv));border-radius:calc(4px * var(--inv));cursor:ew-resize}
.h-e{left:100%}.h-w{left:0}

/* Context bar */
.ctx{position:absolute;top:14px;left:50%;transform:translateX(-50%);z-index:6;max-width:calc(100% - 24px);display:flex;align-items:center;gap:4px;background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:6px;box-shadow:var(--shadow);overflow-x:auto;white-space:nowrap;scrollbar-width:none}
.ctx::-webkit-scrollbar{display:none}
.ctx .sep{width:1px;height:22px;background:var(--line);margin:0 4px;flex:none}
.ctx select{height:34px;border:1px solid var(--line);border-radius:8px;padding:0 8px;font:inherit;background:var(--surface);color:var(--ink);max-width:170px;flex:none;cursor:pointer}
.ctx .label{font-weight:600;padding:0 8px}
.ctx .muted{color:var(--muted);font-size:13px;padding:0 8px 0 2px}
.size{display:flex;align-items:center;border:1px solid var(--line);border-radius:8px;height:34px;flex:none}
.size input{width:38px;text-align:center;border:0;background:transparent;font:inherit;color:var(--ink);-moz-appearance:textfield}
.size input::-webkit-inner-spin-button{-webkit-appearance:none}
.size button{width:28px;height:32px;border:0;background:transparent;cursor:pointer;display:grid;place-items:center;border-radius:6px}
.size button:hover{background:var(--surface-2)}
.colorpick{position:relative;width:34px;height:34px;border-radius:8px;border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;flex:none}
.colorpick input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.colorpick i{width:18px;height:18px;border-radius:50%;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15)}
.tb{height:34px;min-width:34px;padding:0 8px;border-radius:8px;border:0;background:transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-size:13px;font-weight:500;flex:none}
.tb:hover{background:var(--surface-2)}
.tb[aria-pressed="true"]{background:var(--brand-soft);color:var(--brand-strong)}
.tb.danger:hover{color:var(--danger)}
.seg{display:inline-flex;background:var(--surface-2);border-radius:9px;padding:2px;flex:none}
.seg .tb{height:30px}

.nav{position:absolute;top:50%;margin-top:-22px;width:44px;height:44px;border-radius:50%;background:var(--surface);border:1px solid var(--line);box-shadow:var(--shadow);display:grid;place-items:center;cursor:pointer;z-index:4}
.nav:hover{border-color:var(--brand)}
.nav.prev{left:16px}.nav.next{right:16px}
.zoom{position:absolute;right:14px;bottom:14px;display:flex;align-items:center;gap:2px;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:4px;box-shadow:var(--shadow);z-index:4}
.zoom .iconbtn{width:32px;height:32px}
.zoom .pct{min-width:52px;height:32px;border:0;background:transparent;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer}
.zoom .pct:hover{background:var(--surface-2)}
.zoom .iconbtn[aria-pressed="true"]{background:var(--brand-soft);color:var(--brand-strong)}

/* Page strip: folded-card thumbnails with a sliding tab */
.strip{position:relative;display:flex;justify-content:center;padding:0 16px 10px;background:var(--surface);border-top:1px solid var(--line)}
.strip-row{position:relative;z-index:1;display:flex;gap:6px}
.strip-ind{position:absolute;left:0;top:-1px;height:calc(100% - 9px);background:var(--stage);border-radius:0 0 16px 16px;pointer-events:none}
.strip-ind.ready{transition:transform .45s cubic-bezier(.3,1.25,.4,1),width .45s cubic-bezier(.3,1.25,.4,1)}
.thumb{display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:8px;padding:16px 22px 8px;border:0;border-radius:0 0 16px 16px;background:transparent;cursor:pointer;color:var(--muted);font-size:12.5px;font-weight:500;transition:color .2s}
.thumb:hover{color:var(--ink)}
.thumb.active{color:var(--brand-strong);font-weight:600}
.fold{position:relative;perspective:420px;transition:transform .3s cubic-bezier(.3,.7,.3,1)}
.thumb:hover .fold{transform:translateY(-3px)}
.thumb:active .fold{transform:translateY(-1px) scale(.96)}
.fold .mini{transition:transform .5s cubic-bezier(.3,.7,.3,1)}
.fold .sheet-behind{position:absolute;inset:0;background:#c6cdc9;border-radius:2px;transition:transform .5s cubic-bezier(.3,.7,.3,1)}
.fold-front .sheet-behind{transform-origin:left bottom;transform:translate(3px,-2px) skewY(-6deg)}
.fold-back .sheet-behind{transform-origin:right bottom;transform:translate(-3px,-2px) skewY(6deg)}
.fold-front .mini{transform-origin:left center}
.fold-back .mini{transform-origin:right center}
.thumb:hover .fold-front .mini{transform:rotateY(-30deg)}
.thumb:hover .fold-back .mini{transform:rotateY(30deg)}
.thumb:hover .fold-front .sheet-behind{transform:translate(5px,-3px) skewY(-9deg)}
.thumb:hover .fold-back .sheet-behind{transform:translate(-5px,-3px) skewY(9deg)}
.fold-inside .mini{overflow:visible;background:transparent;box-shadow:none}
.fold-inside .mini-inner{perspective:1400px}
.fold-inside .page{background:#fff;box-shadow:0 6px 18px rgba(0,0,0,.22);transition:transform .5s cubic-bezier(.3,.7,.3,1)}
.fold-inside .page:first-child{transform-origin:right center}
.fold-inside .page+.page{transform-origin:left center}
.thumb:hover .fold-inside .page:first-child{transform:rotateY(20deg)}
.thumb:hover .fold-inside .page+.page{transform:rotateY(-20deg)}
.mini{position:relative;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.2);border-radius:2px;background:#fff;flex:none}
.mini-inner{position:absolute;left:0;top:0;transform-origin:0 0;display:flex;pointer-events:none}
.mini .el.empty{border-width:3px}

/* Toast */
.toast{position:fixed;left:50%;bottom:120px;transform:translate(-50%,8px);background:var(--ink);color:var(--surface);padding:10px 14px;border-radius:10px;font-size:13.5px;display:flex;gap:14px;align-items:center;opacity:0;pointer-events:none;transition:opacity .18s,transform .18s;z-index:60;box-shadow:var(--shadow);max-width:calc(100% - 32px)}
.toast.show{opacity:1;transform:translate(-50%,0);pointer-events:auto}
.toast button{background:none;border:0;color:var(--brand);font-weight:700;cursor:pointer;padding:0}

/* Modal */
.modal{position:fixed;inset:0;background:rgba(10,18,14,.55);display:grid;place-items:center;z-index:50;padding:16px}
.dialog{background:var(--surface);border-radius:16px;width:min(980px,100%);max-height:100%;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.35)}
.dlg-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px 14px 22px;border-bottom:1px solid var(--line)}
.dlg-head h2{margin:0;font-size:18px}
.dlg-body{display:grid;grid-template-columns:minmax(0,1fr) 300px;min-height:0;overflow:auto}
.pv{background:var(--stage);padding:20px;display:flex;flex-direction:column;align-items:center;gap:18px;min-height:440px}
.pv-stage{flex:1;width:100%;display:flex;align-items:center;justify-content:center}
.pv .mini{box-shadow:var(--page-shadow)}
.checks{padding:18px 20px;border-left:1px solid var(--line)}
.checks h3{margin:0 0 4px;font-size:15px}
.check{display:flex;gap:10px;padding:12px 0;border-bottom:1px solid var(--line);font-size:13.5px;line-height:1.45}
.check p{margin:0}
.check .ic{flex:none;margin-top:1px}
.check .ic.warn{color:var(--warn)}
.check .ic.ok{color:var(--brand)}
.link{background:none;border:0;padding:0;margin-top:4px;color:var(--brand-strong);font-weight:600;cursor:pointer;font-size:13px}
.dlg-foot{display:flex;justify-content:flex-end;gap:10px;padding:14px 18px;border-top:1px solid var(--line)}
.measure{position:absolute;left:-9999px;top:0;visibility:hidden;width:360px}

@media (max-width:760px){
  .app{grid-template-rows:56px minmax(0,1fr) auto}
  body{padding-bottom:64px}
  .hide-sm{display:none!important}
  .top{gap:4px;padding:0 8px}
  .brand img{height:32px}
  .title-field input{width:110px}
  .btn{padding:0 14px}
  .main{grid-template-columns:minmax(0,1fr)}
  .rail{position:fixed;left:0;right:0;bottom:0;flex-direction:row;justify-content:space-around;border-right:0;border-top:1px solid var(--line);padding:6px 4px calc(6px + env(safe-area-inset-bottom,0px));z-index:30;overflow-x:auto}
  .rail button{flex:1;min-width:52px;padding:6px 2px;font-size:10.5px}
  .panel{position:fixed;left:0;right:0;bottom:calc(64px + env(safe-area-inset-bottom,0px));height:52vh;width:auto;z-index:25;border-right:0;border-top:1px solid var(--line);border-radius:16px 16px 0 0;box-shadow:0 -10px 30px rgba(0,0,0,.15)}
  .stage{padding:70px 14px 20px}
  .nav{display:none}
  .ctx{top:10px;justify-content:flex-start}
  .zoom{right:10px;bottom:10px}
  .dlg-body{grid-template-columns:1fr}
  .checks{border-left:0;border-top:1px solid var(--line)}
  .pv{min-height:320px}
  .toast{bottom:150px}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important}}
</style>
</head>
<body>
<div class="app">
  <header class="top">
    <a class="brand" href="{{ url('/') }}" aria-label="Qrinto home">
      <img src="{{ asset('images/svg-logo/Qrinto-logo-primary-lockup-no-tagline.svg') }}" alt="Qrinto">
    </a>
    <div class="vsep hide-sm"></div>
    <label class="title-field" title="Rename card">
      <input id="title" aria-label="Card name" maxlength="60">
      <span id="pencil"></span>
    </label>
    <div class="spacer"></div>
    <button class="iconbtn" id="undoBtn" title="Undo (Ctrl+Z)" aria-label="Undo"></button>
    <button class="iconbtn" id="redoBtn" title="Redo (Ctrl+Shift+Z)" aria-label="Redo"></button>
    <div class="vsep hide-sm"></div>
    <span class="status" id="status" role="status"></span>
    <button class="btn primary" id="nextBtn">Review<span class="hide-sm">&nbsp;and print</span></button>
  </header>

  <div class="main">
    <nav class="rail" id="rail" aria-label="Tools"></nav>
    <aside class="panel" id="panel" aria-label="Tool options"></aside>
    <section class="stagewrap">
      <div class="ctx" id="ctx" role="toolbar" aria-label="Edit selection"></div>
      <button class="nav prev" id="prevBtn" aria-label="Previous page"></button>
      <button class="nav next" id="nextPageBtn" aria-label="Next page"></button>
      <div class="stage" id="stage">
        <div class="canvas-col">
          <div class="sheet-wrap" id="wrap"><div class="sheet" id="sheet"></div></div>
          <div class="caps" id="caps"></div>
        </div>
      </div>
      <div class="zoom">
        <button class="iconbtn" id="safeBtn" title="Show print margins" aria-label="Show print margins" aria-pressed="false"></button>
        <button class="iconbtn" id="zoomOut" aria-label="Zoom out"></button>
        <button class="pct" id="zoomPct" title="Fit to screen">100%</button>
        <button class="iconbtn" id="zoomIn" aria-label="Zoom in"></button>
      </div>
    </section>
  </div>

  <footer class="strip" id="strip" aria-label="Pages"></footer>
</div>

<input type="file" id="filePick" accept="image/*" multiple hidden>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<div class="measure" id="measure"></div>

<div class="modal" id="modal" hidden>
  <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="dlgTitle">
    <div class="dlg-head"><h2 id="dlgTitle">Review your card</h2><button class="iconbtn" data-close aria-label="Close" id="dlgClose"></button></div>
    <div class="dlg-body">
      <div class="pv">
        <div class="seg" id="pvTabs" role="group" aria-label="Preview side">
          <button class="tb" data-pv="front">Front</button><button class="tb" data-pv="inside">Inside</button><button class="tb" data-pv="back">Back</button>
        </div>
        <div class="pv-stage" id="pvStage"></div>
      </div>
      <aside class="checks">
        <h3>Before printing</h3>
        <p class="hint">5 × 7 in folded card. Anything outside the dashed margin may be trimmed.</p>
        <div id="checkList"></div>
      </aside>
    </div>
    <div class="dlg-foot"><button class="btn" data-close>Keep editing</button><button class="btn primary" id="confirmBtn">Looks good, continue</button></div>
  </div>
</div>

<script src="{{ asset('js/card-editor.js') }}?v={{ time() }}"></script>
</body>
</html>
