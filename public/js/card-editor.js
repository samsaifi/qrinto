(function(){
'use strict';
const W=360,H=504,KEY='qrinto-card-editor-v1';
const $=s=>document.querySelector(s);
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const clamp=(v,a,b)=>Math.min(b,Math.max(a,v));

const ICONS={
  undo:'<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>',
  redo:'<path d="m15 14 5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/>',
  grid:'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
  layout:'<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M12 4v16M15 10h3M15 13h3"/>',
  text:'<path d="M4 7V5h16v2M9 19h6M12 5v14"/>',
  image:'<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
  smile:'<circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/>',
  qr:'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4v-3"/>',
  drop:'<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/>',
  trash:'<path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/>',
  copy:'<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/>',
  up:'<path d="M12 19V5M5 12l7-7 7 7"/>',
  down:'<path d="M12 5v14M5 12l7 7 7-7"/>',
  chevL:'<path d="m15 18-6-6 6-6"/>',chevR:'<path d="m9 18 6-6-6-6"/>',
  plus:'<path d="M12 5v14M5 12h14"/>',minus:'<path d="M5 12h14"/>',
  check:'<path d="M20 6 9 17l-5-5"/>',x:'<path d="M18 6 6 18M6 6l12 12"/>',
  pencil:'<path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/>',
  alignL:'<path d="M4 6h16M4 12h10M4 18h14"/>',alignC:'<path d="M4 6h16M7 12h10M5 18h14"/>',alignR:'<path d="M4 6h16M10 12h10M6 18h14"/>',
  margins:'<rect x="3" y="3" width="18" height="18" rx="2"/><rect x="7" y="7" width="10" height="10" stroke-dasharray="2 2"/>',
  upload:'<path d="M12 15V3M7 8l5-5 5 5M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>',
  refresh:'<path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 3v5h-5M21 12a9 9 0 0 1-15 6.7L3 16M3 21v-5h5"/>',
  alert:'<circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/>'
};
const ic=(n,s=20)=>`<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[n]}</svg>`;

const FONTS=[['Onest',"'Onest',system-ui,sans-serif"],['Playfair Display',"'Playfair Display',Georgia,serif"],['Cormorant Garamond',"'Cormorant Garamond',Georgia,serif"],['DM Serif Display',"'DM Serif Display',Georgia,serif"],['Great Vibes',"'Great Vibes',cursive"],['Pinyon Script',"'Pinyon Script',cursive"],['Caveat',"'Caveat',cursive"],['Bebas Neue',"'Bebas Neue',Impact,sans-serif"],['Josefin Sans',"'Josefin Sans',system-ui,sans-serif"]];
const fontCss=n=>(FONTS.find(f=>f[0]===n)||FONTS[0])[1];
const VIEWS={front:['front'],inside:['inL','inR'],back:['back']};
const ORDER=['front','inside','back'];
const VIEW_LABEL={front:'Front',inside:'Inside',back:'Back'};
const PAGE_LABEL={front:'Front',inL:'Inside left',inR:'Inside right',back:'Back'};

let uidN=0;const uid=()=>'e'+Date.now().toString(36)+(uidN++).toString(36);
const T=o=>({id:uid(),type:'text',x:20,y:200,w:320,text:'Your text',font:'Onest',size:20,color:'#2a2a2a',align:'center',bold:false,italic:false,lh:1.35,ls:0,...o});
const P=o=>({id:uid(),type:'photo',x:30,y:30,w:300,h:300,src:null,fit:'cover',shape:'rounded',...o});
const S=o=>({id:uid(),type:'sticker',x:140,y:200,w:80,h:80,char:'💖',...o});
const Q=o=>({id:uid(),type:'qr',x:120,y:150,w:120,h:120,url:'',color:'#14201a',src:null,...o});

const TEMPLATES=[
 {name:'Anniversary',make:()=>({
  front:{bg:'#fbe9ec',els:[P({x:30,y:30,w:300,h:290}),T({y:336,text:'Happy anniversary',font:'Great Vibes',size:46,color:'#9b2242'}),T({x:40,y:412,w:280,text:'To many more years together',font:'Cormorant Garamond',size:18,italic:true,color:'#6b3a47'})]},
  inL:{bg:'#ffffff',els:[]},
  inR:{bg:'#ffffff',els:[T({x:40,y:150,w:280,text:'Every year with you is my favourite year so far. Here is to the next one, and all the ones after that.',font:'Cormorant Garamond',size:20,color:'#4a2a33',lh:1.5}),T({x:40,y:330,w:280,text:'All my love, Sam',font:'Great Vibes',size:32,color:'#9b2242'})]},
  back:{bg:'#fbe9ec',els:[]}})},
 {name:'Birthday',make:()=>({
  front:{bg:'#fff3d1',els:[S({x:36,y:42,w:70,h:70,char:'🎈'}),S({x:262,y:58,w:60,h:60,char:'🎉'}),S({x:125,y:108,w:110,h:110,char:'🎂'}),T({y:232,text:'HAPPY',font:'Bebas Neue',size:84,color:'#e4572e',ls:4,lh:1}),T({y:322,text:'birthday!',font:'Caveat',size:64,color:'#2b59c3',bold:true,lh:1.1}),T({x:40,y:420,w:280,text:'Make it a big one',font:'Josefin Sans',size:15,color:'#7a5b12',ls:2})]},
  inL:{bg:'#ffffff',els:[]},
  inR:{bg:'#ffffff',els:[T({x:40,y:160,w:280,text:'Another trip around the sun, and you still make it look easy. Hope today is loud, sweet and full of cake.',font:'Caveat',size:26,color:'#2b2b2b',lh:1.3}),T({x:40,y:350,w:280,text:'Love, Jo',font:'Caveat',size:32,color:'#e4572e',bold:true})]},
  back:{bg:'#fff3d1',els:[]}})},
 {name:'Wedding',make:()=>({
  front:{bg:'#ffffff',els:[S({x:150,y:40,w:60,h:60,char:'🌿'}),T({y:116,text:'ON YOUR',font:'Cormorant Garamond',size:16,color:'#7a5c2e',ls:6}),T({y:144,text:'Wedding',font:'Pinyon Script',size:76,color:'#7a5c2e',lh:1.2}),T({y:246,text:'DAY',font:'Cormorant Garamond',size:16,color:'#7a5c2e',ls:6}),T({y:300,text:'Ana & Theo',font:'Pinyon Script',size:40,color:'#7a5c2e',lh:1.3}),T({y:372,text:'August 28, 2026',font:'Cormorant Garamond',size:16,color:'#5a4520',ls:2}),T({y:398,text:'Fort Point',font:'Cormorant Garamond',size:16,italic:true,color:'#5a4520'})]},
  inL:{bg:'#ffffff',els:[]},
  inR:{bg:'#ffffff',els:[T({x:40,y:170,w:280,text:'Wishing you a lifetime of love that grows and overflows with happiness. Congratulations!',font:'Cormorant Garamond',size:20,color:'#5a4520',lh:1.6,ls:1})]},
  back:{bg:'#ffffff',els:[]}})},
 {name:'Thank you',make:()=>({
  front:{bg:'#e3efe8',els:[S({x:145,y:70,w:70,h:70,char:'🌿'}),T({y:158,text:'Thank you',font:'Playfair Display',size:58,color:'#0b5d3b',italic:true,lh:1.2}),T({y:240,text:'for everything',font:'Caveat',size:32,color:'#2e7d57',lh:1.2}),P({x:105,y:310,w:150,h:150,shape:'circle'})]},
  inL:{bg:'#ffffff',els:[]},
  inR:{bg:'#ffffff',els:[T({x:40,y:190,w:280,text:'Your kindness meant more than you know. Thank you for showing up when it mattered.',font:'Onest',size:17,color:'#1e3b2e',lh:1.55})]},
  back:{bg:'#e3efe8',els:[]}})}
];

const LAYOUTS={
 front:[
  {name:'Photo and caption',make:()=>({front:[P({x:30,y:30,w:300,h:290}),T({y:340,text:'Add a heading',font:'Great Vibes',size:42}),T({x:40,y:412,w:280,text:'Add a short line',size:16})]})},
  {name:'Full photo',make:()=>({front:[P({x:0,y:0,w:360,h:504,shape:'square'}),T({y:404,text:'Add a heading',font:'Playfair Display',size:42,color:'#ffffff'})]})},
  {name:'Circle photo',make:()=>({front:[P({x:80,y:56,w:200,h:200,shape:'circle'}),T({y:286,text:'Add a heading',font:'Playfair Display',size:42}),T({x:40,y:372,w:280,text:'Add a short line',size:16})]})},
  {name:'Words only',make:()=>({front:[T({y:166,text:'Add a heading',font:'Playfair Display',size:54,lh:1.15}),T({x:40,y:300,w:280,text:'Add a short line',size:18})]})}
 ],
 inside:[
  {name:'Message on the right',make:()=>({inL:[],inR:[T({x:40,y:180,w:280,text:'Write your message here',font:'Cormorant Garamond',size:20,lh:1.55})]})},
  {name:'Message on both sides',make:()=>({inL:[T({x:40,y:180,w:280,text:'Write your message here',font:'Cormorant Garamond',size:20,lh:1.55})],inR:[T({x:40,y:180,w:280,text:'And a little more here',font:'Cormorant Garamond',size:20,lh:1.55})]})},
  {name:'Photo and message',make:()=>({inL:[P({x:30,y:30,w:300,h:444})],inR:[T({x:40,y:180,w:280,text:'Write your message here',font:'Cormorant Garamond',size:20,lh:1.55})]})},
  {name:'Blank inside',make:()=>({inL:[],inR:[]})}
 ],
 back:[
  {name:'QR video message',make:()=>({back:[Q({x:120,y:150,w:120,h:120}),T({x:40,y:286,w:280,text:'Scan for a video message',size:14,color:'#444444'})]})},
  {name:'Short note',make:()=>({back:[T({x:40,y:200,w:280,text:'Made with love',font:'Cormorant Garamond',size:18,italic:true})]})},
  {name:'Blank back',make:()=>({back:[]})}
 ]
};

const STICKERS={Love:['💖','💕','💞','❤️','💌','🌹','💐','💍'],Celebrate:['🎉','🎈','🎂','🎁','🥂','✨','🎊','🍾'],Nature:['🌿','🌸','🌼','🌻','🍃','🌙','☀️','🌈'],Fun:['😊','🥳','😍','🤗','👏','🙌','⭐','🦋']};
const BG_COLORS=['#ffffff','#f6f1e7','#fbe9ec','#fde2d2','#fff3d1','#e3efe8','#ddebf7','#e9e3f5','#1f2a44','#14201a'];
const QR_COLORS=['#14201a','#0b7a49','#9b2242','#1f3a93','#7a5c2e'];
const PAIRS=[
 [{text:'Happy anniversary',font:'Great Vibes',size:44,color:'#9b2242'},{text:'with all my love',font:'Cormorant Garamond',size:18,italic:true,color:'#6b3a47'}],
 [{text:'CONGRATS',font:'Bebas Neue',size:64,color:'#e4572e',ls:3,lh:1},{text:'you did it!',font:'Caveat',size:34,color:'#2b59c3',bold:true}],
 [{text:'Thank you',font:'Playfair Display',size:48,italic:true,color:'#0b5d3b'},{text:'FOR BEING YOU',font:'Josefin Sans',size:14,color:'#2e5d47',ls:3}],
 [{text:'Merry & bright',font:'DM Serif Display',size:44,color:'#8e1b1b'},{text:'Warm wishes from all of us',font:'Onest',size:14,color:'#3b3b3b'}]
];

/* ---------- State ---------- */
let state={title:'Anniversary card',pages:TEMPLATES[0].make()};
try{const raw=localStorage.getItem(KEY);if(raw){const d=JSON.parse(raw);if(d&&d.pages&&d.pages.front&&d.pages.inL&&d.pages.inR&&d.pages.back)state={title:d.title||'Untitled card',pages:d.pages};}}catch(e){}

let view='front',sel=null,activePage='front',editingId=null,zoom=1,zoomMode='fit',panel='templates',showSafe=false,library=[],pendingPhoto=null,addCount=0,pvView='front';
let qrDraft={url:'',color:'#14201a'};
let past=[],future=[],present=JSON.stringify(state.pages);

const stage=$('#stage'),wrap=$('#wrap'),sheet=$('#sheet'),caps=$('#caps'),ctx=$('#ctx'),rail=$('#rail'),panelEl=$('#panel'),strip=$('#strip'),fileInput=$('#filePick'),toastEl=$('#toast'),modal=$('#modal');

function findEl(id){for(const pid in state.pages){const arr=state.pages[pid].els;const idx=arr.findIndex(e=>e.id===id);if(idx>-1)return{pid,el:arr[idx],idx};}return null;}
const nodeOf=id=>sheet.querySelector(`.el[data-id="${id}"]`);
function targetPage(){const pids=VIEWS[view];if(sel&&pids.includes(sel.pid))return sel.pid;if(pids.includes(activePage))return activePage;return view==='inside'?'inR':pids[0];}

/* ---------- History & saving ---------- */
function commit(){const snap=JSON.stringify(state.pages);if(snap===present)return;past.push(present);if(past.length>80)past.shift();present=snap;future=[];afterChange();}
function afterChange(){updateUndo();scheduleSave();scheduleThumbs();}
function undo(){finishEditing();if(!past.length)return;future.push(present);present=past.pop();state.pages=JSON.parse(present);restore();}
function redo(){finishEditing();if(!future.length)return;past.push(present);present=future.pop();state.pages=JSON.parse(present);restore();}
function restore(){if(sel&&!findEl(sel.id))sel=null;renderStage();renderStrip();renderCtx();updateUndo();scheduleSave();if(['layouts','background','qr'].includes(panel))renderPanel();}
function updateUndo(){$('#undoBtn').disabled=!past.length;$('#redoBtn').disabled=!future.length;}
let saveT;
function scheduleSave(){setStatus('saving');clearTimeout(saveT);saveT=setTimeout(()=>{try{localStorage.setItem(KEY,JSON.stringify({title:state.title,pages:state.pages}));setStatus('saved');}catch(e){setStatus('error');}},500);}
function setStatus(s){const el=$('#status');el.className='status '+s;el.innerHTML=s==='error'?`${ic('alert',16)}<span class="hide-sm">Too large to save in this browser</span>`:`${ic('check',16)}<span class="hide-sm">${s==='saving'?'Saving…':'Saved'}</span>`;}
let thumbR;function scheduleThumbs(){cancelAnimationFrame(thumbR);thumbR=requestAnimationFrame(renderStrip);}

/* ---------- Rendering ---------- */
function applyTextStyle(t,el){const s=t.style;s.fontFamily=fontCss(el.font);s.fontSize=el.size+'px';s.color=el.color;s.textAlign=el.align;s.fontWeight=el.bold?'700':'400';s.fontStyle=el.italic?'italic':'normal';s.lineHeight=el.lh;s.letterSpacing=(el.ls||0)+'px';}
function buildEl(el){
  const d=document.createElement('div');d.className='el el-'+el.type;d.dataset.id=el.id;
  d.style.left=el.x+'px';d.style.top=el.y+'px';d.style.width=el.w+'px';if(el.type!=='text')d.style.height=el.h+'px';
  if(el.type==='text'){const t=document.createElement('div');t.className='txt';t.textContent=el.text;applyTextStyle(t,el);d.appendChild(t);}
  else if(el.type==='photo'){d.classList.add('shape-'+(el.shape||'rounded'));
    if(el.src){const img=document.createElement('img');img.src=el.src;img.alt='';img.draggable=false;img.style.objectFit=el.fit||'cover';d.appendChild(img);}
    else{d.classList.add('empty');d.innerHTML=`<div class="ph">${ic('image',22)}<span>Add photo</span><small>optional</small></div>`;}}
  else if(el.type==='sticker'){const s=document.createElement('span');s.textContent=el.char;s.style.fontSize=Math.round(el.h*.84)+'px';d.appendChild(s);}
  else if(el.type==='qr'){if(el.src){const img=document.createElement('img');img.src=el.src;img.alt='QR code';img.draggable=false;d.appendChild(img);}
    else{d.classList.add('empty');d.innerHTML=`<div class="ph">${ic('qr',22)}<span>Add QR code</span></div>`;}}
  return d;
}
function buildPage(pg,pid,mode){
  const p=document.createElement('div');p.className='page';p.dataset.page=pid;
  const c=document.createElement('div');c.className='content';c.style.background=pg.bg;
  pg.els.forEach(el=>{
    if(mode==='preview'){if((el.type==='photo'||el.type==='qr')&&!el.src)return;if(el.type==='text'&&!el.text.trim())return;}
    c.appendChild(buildEl(el));
  });
  if(pid==='back')c.insertAdjacentHTML('beforeend','<div class="backmark">made with <b>qrinto</b></div>');
  p.appendChild(c);
  if(mode==='edit')p.insertAdjacentHTML('beforeend','<div class="safe"></div><div class="guide-v"></div><div class="guide-h"></div>');
  return p;
}
function mini(arr,s,mode='thumb'){
  const m=document.createElement('div');m.className='mini';m.style.width=W*arr.length*s+'px';m.style.height=H*s+'px';
  const inner=document.createElement('div');inner.className='mini-inner'+(arr.length>1?' spread':'');inner.style.transform=`scale(${s})`;
  arr.forEach(([pg,pid])=>inner.appendChild(buildPage(pg,pid,mode)));m.appendChild(inner);return m;
}
function renderStage(){
  finishEditing();
  const pids=VIEWS[view];sheet.innerHTML='';
  sheet.classList.toggle('spread',pids.length>1);sheet.classList.toggle('show-safe',showSafe);
  pids.forEach(pid=>sheet.appendChild(buildPage(state.pages[pid],pid,'edit')));
  caps.innerHTML=pids.map(p=>`<span>${PAGE_LABEL[p]}</span>`).join('');
  $('#prevBtn').hidden=view==='front';$('#nextPageBtn').hidden=view==='back';
  applyZoom();drawSelection();
}
function renderStrip(){
  if(!strip.querySelector('#stripRow'))strip.innerHTML='<div class="strip-ind" id="stripInd" aria-hidden="true"></div><div class="strip-row" id="stripRow"></div>';
  const row=strip.querySelector('#stripRow');row.innerHTML='';
  ORDER.forEach(v=>{
    const b=document.createElement('button');b.className='thumb'+(v===view?' active':'');b.setAttribute('aria-label',VIEW_LABEL[v]+' of card');
    if(v===view)b.setAttribute('aria-current','page');
    const fold=document.createElement('div');fold.className='fold fold-'+v;
    if(v!=='inside')fold.insertAdjacentHTML('beforeend','<div class="sheet-behind"></div>');
    fold.appendChild(mini(VIEWS[v].map(pid=>[state.pages[pid],pid]),.14));
    b.appendChild(fold);
    b.insertAdjacentHTML('beforeend',`<span>${VIEW_LABEL[v]}</span>`);
    b.onclick=()=>setView(v);row.appendChild(b);
  });
  placeIndicator();
}
function placeIndicator(){const ind=document.getElementById('stripInd');const a=strip.querySelector('.thumb.active');if(!ind||!a)return;ind.style.width=a.offsetWidth+'px';ind.style.transform=`translateX(${a.offsetLeft}px)`;if(!ind.classList.contains('ready'))requestAnimationFrame(()=>requestAnimationFrame(()=>ind.classList.add('ready')));}
window.addEventListener('resize',placeIndicator);
function setView(v){if(v===view)return;const from=view;view=v;sel=null;zoomMode='fit';renderStage();renderStrip();renderCtx();if(['layouts','background','text','qr'].includes(panel))renderPanel();animateFlip(from,v);}
/* Card open / close animation: a ghost of the outgoing page turns on the spine while the incoming page lands */
function animateFlip(from,to){
  if(!sheet.animate||window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;
  const dur=760,easing='cubic-bezier(.45,.05,.25,1)',PS='perspective(2200px) ';
  const pg=pid=>sheet.querySelector(`.page[data-page="${pid}"]`);
  const anims=[];let g=null;
  const ghost=(pid,left)=>{g=buildPage(state.pages[pid],pid,'thumb');g.classList.add('ghost');g.style.left=left+'px';sheet.appendChild(g);return g;};
  const turn=(node,origin,a,b)=>{if(!node)return;node.style.transformOrigin=origin;node.style.backfaceVisibility='hidden';anims.push(node.animate([{transform:PS+`rotateY(${a}deg)`},{transform:PS+`rotateY(${b}deg)`}],{duration:dur,easing}));};
  const pair=from+'>'+to;
  if(pair==='front>inside'){turn(ghost('front',W),'left center',0,-180);turn(pg('inL'),'right center',180,0);}
  else if(pair==='inside>front'){turn(ghost('inL',-W),'right center',0,180);turn(pg('front'),'left center',-180,0);}
  else if(pair==='inside>back'){turn(ghost('inR',W),'left center',0,-180);turn(pg('back'),'right center',180,0);}
  else if(pair==='back>inside'){turn(ghost('back',0),'right center',0,180);turn(pg('inR'),'left center',-180,0);}
  else{const n=pg(VIEWS[to][0]);if(n){n.style.transformOrigin='center';anims.push(n.animate([{transform:PS+'rotateY(-90deg)',opacity:0},{transform:PS+'rotateY(0deg)',opacity:1}],{duration:dur*.7,easing:'cubic-bezier(.2,.8,.3,1)'}));}}
  sheet.classList.add('flipping');
  Promise.all(anims.map(a=>a.finished.catch(()=>{}))).then(()=>{if(g)g.remove();sheet.classList.remove('flipping');sheet.querySelectorAll('.page').forEach(n=>{n.style.transformOrigin='';n.style.backfaceVisibility='';});});
}

/* ---------- Zoom ---------- */
function contentW(){return W*VIEWS[view].length;}
function fitZoom(){const cs=getComputedStyle(stage);const pw=stage.clientWidth-parseFloat(cs.paddingLeft)-parseFloat(cs.paddingRight);const ph=stage.clientHeight-parseFloat(cs.paddingTop)-parseFloat(cs.paddingBottom)-30;return clamp(Math.min(pw/contentW(),ph/H),.2,1.6);}
function applyZoom(){if(zoomMode==='fit')zoom=fitZoom();wrap.style.width=contentW()*zoom+'px';wrap.style.height=H*zoom+'px';sheet.style.transform=`scale(${zoom})`;sheet.style.setProperty('--inv',(1/zoom).toFixed(4));caps.style.width=contentW()*zoom+'px';$('#zoomPct').textContent=Math.round(zoom*100)+'%';}
function zoomBy(f){zoomMode='manual';zoom=clamp(zoom*f,.25,3);applyZoom();}

/* ---------- Selection ---------- */
function drawSelection(){
  sheet.querySelectorAll('.selbox').forEach(n=>n.remove());
  if(!sel)return;const node=nodeOf(sel.id);const f=findEl(sel.id);if(!node||!f){sel=null;return;}
  const box=document.createElement('div');box.className='selbox';
  const dirs=f.el.type==='text'?['w','e']:['nw','ne','sw','se'];
  box.innerHTML=dirs.map(d=>`<div class="handle h-${d}" data-dir="${d}"></div>`).join('');
  node.closest('.page').appendChild(box);positionSelbox();if(editingId)box.hidden=true;
}
function positionSelbox(){const box=sheet.querySelector('.selbox');if(!box||!sel)return;const node=nodeOf(sel.id);if(!node)return;box.style.left=node.offsetLeft+'px';box.style.top=node.offsetTop+'px';box.style.width=node.offsetWidth+'px';box.style.height=node.offsetHeight+'px';}
function select(pid,id){
  if(editingId&&id!==editingId)finishEditing();
  sel=id?{pid,id}:null;if(pid)activePage=pid;
  drawSelection();renderCtx();
  if(['qr','background','photos','stickers'].includes(panel))renderPanel();
}

/* ---------- Pointer interaction ---------- */
sheet.addEventListener('pointerdown',e=>{
  if(e.button!==0)return;
  const h=e.target.closest('.handle');if(h){startResize(e,h);return;}
  const node=e.target.closest('.el');const pageNode=e.target.closest('.page');
  if(node&&pageNode){const id=node.dataset.id,pid=pageNode.dataset.page;if(editingId===id)return;
    const wasSel=!!(sel&&sel.id===id);if(!wasSel)select(pid,id);e.preventDefault();startDrag(e,pid,id,wasSel);return;}
  if(pageNode){activePage=pageNode.dataset.page;select(null);if(panel==='background')renderPanel();}
});
sheet.addEventListener('dblclick',e=>{const node=e.target.closest('.el-text');if(node)startEdit(node.dataset.id);});
stage.addEventListener('pointerdown',e=>{if(e.target===stage||e.target.classList.contains('canvas-col')||e.target===caps)select(null);});

function startDrag(e,pid,id,wasSel){
  const f=findEl(id);if(!f)return;const el=f.el;const node=nodeOf(id);const pageNode=node.closest('.page');
  const sx=e.clientX,sy=e.clientY,ox=el.x,oy=el.y;let moved=false;
  const gv=pageNode.querySelector('.guide-v'),gh=pageNode.querySelector('.guide-h');
  try{node.setPointerCapture(e.pointerId);}catch(_){}
  const move=ev=>{
    const dx=(ev.clientX-sx)/zoom,dy=(ev.clientY-sy)/zoom;
    if(!moved&&Math.hypot(ev.clientX-sx,ev.clientY-sy)<4)return;moved=true;
    let nx=ox+dx,ny=oy+dy;const w=el.w,h=node.offsetHeight;let sv=false,sh=false;
    if(Math.abs(nx+w/2-W/2)<6){nx=W/2-w/2;sv=true;}
    if(Math.abs(ny+h/2-H/2)<6){ny=H/2-h/2;sh=true;}
    nx=clamp(nx,20-w,W-20);ny=clamp(ny,20-h,H-20);
    el.x=Math.round(nx);el.y=Math.round(ny);node.style.left=el.x+'px';node.style.top=el.y+'px';
    gv.classList.toggle('on',sv);gh.classList.toggle('on',sh);positionSelbox();
  };
  const up=()=>{
    node.removeEventListener('pointermove',move);node.removeEventListener('pointerup',up);node.removeEventListener('pointercancel',up);
    gv.classList.remove('on');gh.classList.remove('on');
    if(moved){commit();return;}
    if(el.type==='photo'&&!el.src)pickPhotoFor(pid,id);
    else if(el.type==='qr'&&!el.src)openPanel('qr');
    else if(wasSel&&el.type==='text')startEdit(id);
  };
  node.addEventListener('pointermove',move);node.addEventListener('pointerup',up);node.addEventListener('pointercancel',up);
}
function startResize(e,h){
  e.preventDefault();e.stopPropagation();
  const f=sel&&findEl(sel.id);if(!f)return;const el=f.el;const node=nodeOf(el.id);
  const dir=h.dataset.dir,sx=e.clientX,sy=e.clientY;
  const o={x:el.x,y:el.y,w:el.w,h:el.type==='text'?node.offsetHeight:el.h};const ratio=o.w/o.h;const keep=el.type==='sticker'||el.type==='qr';
  try{h.setPointerCapture(e.pointerId);}catch(_){}
  const move=ev=>{
    const dx=(ev.clientX-sx)/zoom,dy=(ev.clientY-sy)/zoom;let w=o.w,hh=o.h;
    if(dir.includes('e'))w=o.w+dx;if(dir.includes('w'))w=o.w-dx;
    if(el.type!=='text'){if(dir.includes('s'))hh=o.h+dy;if(dir.includes('n'))hh=o.h-dy;}
    w=Math.max(30,w);hh=Math.max(30,hh);
    if(keep){const sc=Math.max(w/o.w,hh/o.h);w=Math.max(30,o.w*sc);hh=w/ratio;}
    let x=o.x,y=o.y;if(dir.includes('w'))x=o.x+o.w-w;if(dir.includes('n'))y=o.y+o.h-hh;
    el.x=Math.round(x);el.w=Math.round(w);node.style.left=el.x+'px';node.style.width=el.w+'px';
    if(el.type!=='text'){el.y=Math.round(y);el.h=Math.round(hh);node.style.top=el.y+'px';node.style.height=el.h+'px';if(el.type==='sticker')node.firstChild.style.fontSize=Math.round(el.h*.84)+'px';}
    positionSelbox();
  };
  const up=()=>{h.removeEventListener('pointermove',move);h.removeEventListener('pointerup',up);h.removeEventListener('pointercancel',up);commit();};
  h.addEventListener('pointermove',move);h.addEventListener('pointerup',up);h.addEventListener('pointercancel',up);
}

/* ---------- Text editing ---------- */
function startEdit(id){
  const f=findEl(id);if(!f||f.el.type!=='text')return;const node=nodeOf(id);if(!node)return;
  if(!sel||sel.id!==id)select(f.pid,id);
  const t=node.querySelector('.txt');editingId=id;node.classList.add('editing');
  const box=sheet.querySelector('.selbox');if(box)box.hidden=true;
  t.setAttribute('contenteditable','true');t.focus();
  const r=document.createRange();r.selectNodeContents(t);const s=window.getSelection();s.removeAllRanges();s.addRange(r);
  const onPaste=ev=>{ev.preventDefault();const txt=(ev.clipboardData||window.clipboardData).getData('text/plain');document.execCommand('insertText',false,txt);};
  const onKey=ev=>{if(ev.key==='Escape'){ev.preventDefault();t.blur();}ev.stopPropagation();};
  const onInput=()=>positionSelbox();
  t.addEventListener('paste',onPaste);t.addEventListener('keydown',onKey);t.addEventListener('input',onInput);
  t.addEventListener('blur',()=>{
    t.removeEventListener('paste',onPaste);t.removeEventListener('keydown',onKey);t.removeEventListener('input',onInput);
    t.removeAttribute('contenteditable');node.classList.remove('editing');editingId=null;
    const f2=findEl(id);if(f2)f2.el.text=t.innerText.replace(/\n+$/,'');
    commit();positionSelbox();const b=sheet.querySelector('.selbox');if(b)b.hidden=false;
  },{once:true});
}
function finishEditing(){if(!editingId)return;const node=nodeOf(editingId);const t=node&&node.querySelector('.txt');if(t)t.blur();else editingId=null;}

/* ---------- Element operations ---------- */
function refreshNode(id){const f=findEl(id);const node=nodeOf(id);if(!f||!node)return;
  if(editingId===id)applyTextStyle(node.querySelector('.txt'),f.el);else node.replaceWith(buildEl(f.el));positionSelbox();}
function patch(id,obj,doCommit=true){const f=findEl(id);if(!f)return;Object.assign(f.el,obj);refreshNode(id);if(doCommit)commit();}
function addElement(el,pid){pid=pid||targetPage();state.pages[pid].els.push(el);renderStage();select(pid,el.id);commit();return el;}
function stagger(){return (addCount++%5)*14;}
function duplicate(){const f=sel&&findEl(sel.id);if(!f)return;const c=JSON.parse(JSON.stringify(f.el));c.id=uid();c.x+=14;c.y+=14;state.pages[f.pid].els.splice(f.idx+1,0,c);renderStage();select(f.pid,c.id);commit();}
function removeSel(){const f=sel&&findEl(sel.id);if(!f)return;state.pages[f.pid].els.splice(f.idx,1);sel=null;renderStage();renderCtx();commit();toast('Deleted','Undo',undo);}

/* ---------- Context bar ---------- */
function renderCtx(){
  const f=sel&&findEl(sel.id);
  const common=`<span class="sep"></span><button class="tb" data-act="forward" title="Bring forward" aria-label="Bring forward">${ic('up',18)}</button><button class="tb" data-act="backward" title="Send backward" aria-label="Send backward">${ic('down',18)}</button><button class="tb" data-act="dup" title="Duplicate (Ctrl+D)" aria-label="Duplicate">${ic('copy',18)}</button><button class="tb danger" data-act="del" title="Delete" aria-label="Delete">${ic('trash',18)}</button>`;
  if(!f){ctx.innerHTML=`<span class="label">${VIEW_LABEL[view]}</span><span class="sep"></span><span class="muted">Click anything on the card to edit it. Double-click text to type.</span>`;return;}
  const el=f.el;
  if(el.type==='text'){
    ctx.innerHTML=`<select data-k="font" aria-label="Font">${FONTS.map(([n,c])=>`<option value="${n}" ${n===el.font?'selected':''} style="font-family:${c}">${n}</option>`).join('')}</select>
    <div class="size"><button data-act="size-" aria-label="Smaller">${ic('minus',16)}</button><input data-k="size" type="number" min="6" max="200" value="${el.size}" aria-label="Font size"><button data-act="size+" aria-label="Bigger">${ic('plus',16)}</button></div>
    <label class="colorpick" title="Text colour"><i style="background:${el.color}"></i><input type="color" data-k="color" value="${el.color}" aria-label="Text colour"></label>
    <span class="sep"></span>
    <button class="tb" data-act="bold" aria-pressed="${el.bold}" title="Bold" aria-label="Bold"><b>B</b></button>
    <button class="tb" data-act="italic" aria-pressed="${el.italic}" title="Italic" aria-label="Italic"><i style="font-family:Georgia,serif;font-size:15px">I</i></button>
    <div class="seg" role="group" aria-label="Alignment">${['left','center','right'].map(a=>`<button class="tb" data-act="align" data-v="${a}" aria-pressed="${el.align===a}" title="Align ${a}" aria-label="Align ${a}">${ic('align'+a[0].toUpperCase(),18)}</button>`).join('')}</div>
    <select data-k="lh" aria-label="Line spacing" title="Line spacing">${[1,1.2,1.35,1.5,1.75,2].map(v=>`<option value="${v}" ${Number(el.lh)===v?'selected':''}>Line ${v}</option>`).join('')}</select>${common}`;
  }else if(el.type==='photo'){
    ctx.innerHTML=`<button class="tb" data-act="replace">${ic(el.src?'refresh':'upload',18)}${el.src?'Replace':'Add photo'}</button>
    ${el.src?`<span class="sep"></span><div class="seg" role="group" aria-label="Photo fit"><button class="tb" data-act="fit" data-v="cover" aria-pressed="${el.fit!=='contain'}">Fill</button><button class="tb" data-act="fit" data-v="contain" aria-pressed="${el.fit==='contain'}">Fit</button></div>`:''}
    <span class="sep"></span><div class="seg" role="group" aria-label="Shape">${['square','rounded','circle'].map(s=>`<button class="tb" data-act="shape" data-v="${s}" aria-pressed="${(el.shape||'rounded')===s}">${s[0].toUpperCase()+s.slice(1)}</button>`).join('')}</div>
    ${el.src?`<button class="tb" data-act="clear">Remove photo</button>`:''}${common}`;
  }else if(el.type==='sticker'){
    ctx.innerHTML=`<button class="tb" data-act="swap">${ic('smile',18)}Change sticker</button>${common}`;
  }else if(el.type==='qr'){
    ctx.innerHTML=`<button class="tb" data-act="editqr">${ic('qr',18)}${el.src?'Edit link':'Add link'}</button>${el.url?`<span class="muted">${esc(el.url.replace(/^https?:\/\//,'').slice(0,32))}</span>`:''}${common}`;
  }
}
ctx.addEventListener('click',e=>{const b=e.target.closest('[data-act]');if(!b||!sel)return;act(b.dataset.act,b.dataset.v);});
ctx.addEventListener('input',e=>{const k=e.target.dataset.k;if(!k||!sel)return;let v=e.target.value;
  if(k==='size'){const n=parseInt(v,10);if(!(n>=6&&n<=200))return;v=n;}
  if(k==='lh')v=parseFloat(v);
  patch(sel.id,{[k]:v},false);
  if(k==='color'){const i=e.target.parentElement.querySelector('i');if(i)i.style.background=v;}});
ctx.addEventListener('change',e=>{const k=e.target.dataset.k;if(!k||!sel)return;
  if(k==='size'){const f=findEl(sel.id);const n=clamp(parseInt(e.target.value,10)||f.el.size,6,200);patch(sel.id,{size:n},false);e.target.value=n;}
  commit();});
function act(a,v){
  const f=findEl(sel.id);if(!f)return;const el=f.el;const arr=state.pages[f.pid].els;
  switch(a){
    case 'size-':patch(el.id,{size:Math.max(6,el.size-2)});renderCtx();break;
    case 'size+':patch(el.id,{size:Math.min(200,el.size+2)});renderCtx();break;
    case 'bold':patch(el.id,{bold:!el.bold});renderCtx();break;
    case 'italic':patch(el.id,{italic:!el.italic});renderCtx();break;
    case 'align':patch(el.id,{align:v});renderCtx();break;
    case 'fit':patch(el.id,{fit:v});renderCtx();break;
    case 'shape':patch(el.id,{shape:v});renderCtx();break;
    case 'replace':pickPhotoFor(f.pid,el.id);break;
    case 'clear':patch(el.id,{src:null});renderCtx();break;
    case 'swap':openPanel('stickers');break;
    case 'editqr':openPanel('qr');break;
    case 'forward':if(f.idx<arr.length-1){arr.splice(f.idx,1);arr.splice(f.idx+1,0,el);renderStage();commit();}else toast('Already at the front');break;
    case 'backward':if(f.idx>0){arr.splice(f.idx,1);arr.splice(f.idx-1,0,el);renderStage();commit();}else toast('Already at the back');break;
    case 'dup':duplicate();break;
    case 'del':removeSel();break;
  }
}

/* ---------- Rail & panels ---------- */
const TOOLS=[['templates','Templates','grid'],['layouts','Layouts','layout'],['text','Text','text'],['photos','Photos','image'],['stickers','Stickers','smile'],['qr','QR code','qr'],['background','Background','drop']];
function renderRail(){rail.innerHTML=TOOLS.map(([id,label,icon])=>`<button data-tool="${id}" aria-pressed="${panel===id&&!panelEl.hidden}">${ic(icon,22)}<span>${label}</span></button>`).join('');}
rail.addEventListener('click',e=>{const b=e.target.closest('[data-tool]');if(b)togglePanel(b.dataset.tool);});
function openPanel(name){panel=name;panelEl.hidden=false;renderRail();renderPanel();}
function togglePanel(name){if(panel===name&&!panelEl.hidden){panelEl.hidden=true;panel=null;}else{panel=name;panelEl.hidden=false;}renderRail();renderPanel();}
const head=t=>`<div class="phead"><h2>${t}</h2><button class="iconbtn" data-closepanel aria-label="Close panel">${ic('x',18)}</button></div>`;
panelEl.addEventListener('click',e=>{if(e.target.closest('[data-closepanel]')){panelEl.hidden=true;panel=null;renderRail();}});

function renderPanel(){
  if(panelEl.hidden||!panel){panelEl.innerHTML='';return;}
  const inner=document.createElement('div');inner.className='panel-inner';
  const f=sel&&findEl(sel.id);
  if(panel==='templates'){
    inner.innerHTML=head('Templates')+`<p class="hint">Start from a finished design. It replaces your whole card, and you can undo it.</p><div class="tiles"></div>`;
    const g=inner.querySelector('.tiles');
    TEMPLATES.forEach(t=>{const pages=t.make();const b=document.createElement('button');b.className='tile';b.appendChild(mini([[pages.front,'front']],.29));b.insertAdjacentHTML('beforeend',`<span>${t.name}</span>`);b.onclick=()=>applyTemplate(t);g.appendChild(b);});
  }
  else if(panel==='layouts'){
    inner.innerHTML=head('Layouts')+`<p class="hint">Arrange the ${VIEW_LABEL[view].toLowerCase()} of your card. Your words and photos carry over.</p><div class="tiles ${view==='inside'?'one':''}"></div>`;
    const g=inner.querySelector('.tiles');
    LAYOUTS[view].forEach(ly=>{const made=ly.make();const arr=VIEWS[view].map(pid=>[{bg:state.pages[pid].bg,els:made[pid]||[]},pid]);
      const b=document.createElement('button');b.className='tile';b.appendChild(mini(arr,view==='inside'?.33:.29));b.insertAdjacentHTML('beforeend',`<span>${ly.name}</span>`);b.onclick=()=>applyLayout(ly);g.appendChild(b);});
  }
  else if(panel==='text'){
    inner.innerHTML=head('Text')+`<p class="hint">Adds to the ${PAGE_LABEL[targetPage()].toLowerCase()}. Start typing right away.</p>
      <button class="addtext" data-add="heading"><span style="font:700 26px 'Playfair Display',Georgia,serif">Add a heading</span></button>
      <button class="addtext" data-add="sub"><span style="font:italic 400 21px 'Cormorant Garamond',Georgia,serif">Add a subheading</span></button>
      <button class="addtext" data-add="body"><span style="font:400 15px 'Onest',system-ui,sans-serif">Add a short message</span></button>
      <div class="sec">Font pairs</div><div class="tiles"></div>`;
    const g=inner.querySelector('.tiles');
    PAIRS.forEach(pair=>{const b=document.createElement('button');b.className='tile';
      b.innerHTML=`<div class="pair">${pair.map(p=>`<span style="font-family:${fontCss(p.font)};font-size:${Math.round(p.size*.42)}px;color:${p.color};font-style:${p.italic?'italic':'normal'};font-weight:${p.bold?700:400};letter-spacing:${(p.ls||0)*.4}px;line-height:1.1">${esc(p.text)}</span>`).join('')}</div>`;
      b.onclick=()=>addPair(pair);g.appendChild(b);});
    inner.querySelectorAll('[data-add]').forEach(b=>b.onclick=()=>addText(b.dataset.add));
  }
  else if(panel==='photos'){
    const fill=f&&f.el.type==='photo';
    inner.innerHTML=head('Photos')+`<p class="hint">${fill?'Pick a photo to put in the selected frame.':'Upload photos, then click one to add it. You can also drop photos straight onto the card.'}</p>
      <button class="drop" id="dropZone">${ic('upload',24)}<b>Upload photos</b><span>JPG, PNG or WebP. Drag and drop works too.</span></button>
      ${library.length?`<div class="sec">Your uploads</div><div class="lib">${library.map((p,i)=>`<button data-lib="${i}" aria-label="Use photo ${i+1}"><img src="${p.src}" alt=""></button>`).join('')}</div>`:''}
      <div class="sec">Frames</div><button class="addtext" data-add="frame">${ic('image',18)}<span>Add an empty photo frame</span></button>`;
    const dz=inner.querySelector('#dropZone');
    dz.onclick=()=>{pendingPhoto=null;fileInput.value='';fileInput.click();};
    dz.ondragover=e=>{e.preventDefault();dz.classList.add('over');};dz.ondragleave=()=>dz.classList.remove('over');
    dz.ondrop=e=>{e.preventDefault();e.stopPropagation();dz.classList.remove('over');handleUploads([...e.dataTransfer.files]);};
    inner.querySelectorAll('[data-lib]').forEach(b=>b.onclick=()=>useLibrary(library[+b.dataset.lib]));
    inner.querySelector('[data-add="frame"]').onclick=()=>{const o=stagger();addElement(P({x:70+o,y:120+o,w:220,h:220}));};
  }
  else if(panel==='stickers'){
    const swap=f&&f.el.type==='sticker';
    inner.innerHTML=head('Stickers')+`<p class="hint">${swap?'Pick a sticker to swap the selected one.':'Click a sticker to add it. Drag a corner to resize.'}</p>`+
      Object.entries(STICKERS).map(([cat,list])=>`<div class="sec">${cat}</div><div class="stickers">${list.map(c=>`<button data-st="${c}" aria-label="Sticker ${c}">${c}</button>`).join('')}</div>`).join('');
    inner.querySelectorAll('[data-st]').forEach(b=>b.onclick=()=>{const c=b.dataset.st;const cur=sel&&findEl(sel.id);
      if(cur&&cur.el.type==='sticker'){patch(cur.el.id,{char:c});}else{const o=stagger();addElement(S({char:c,x:140+o,y:212+o}));}});
  }
  else if(panel==='qr'){
    const editing=f&&f.el.type==='qr';const url=editing?(f.el.url||''):qrDraft.url;const color=editing?(f.el.color||'#14201a'):qrDraft.color;
    inner.innerHTML=head('QR code')+`<p class="hint">Link the printed card to a video message, playlist, photo album or gift registry. Anyone can scan it with a phone camera.</p>
      <label class="field-label" for="qrUrl">Link</label><input id="qrUrl" class="input" type="url" inputmode="url" autocomplete="off" placeholder="https://" value="${esc(url)}">
      <div class="err" id="qrErr" hidden></div>
      <span class="field-label">Colour</span><div class="swatches" id="qrCols">${QR_COLORS.map(c=>`<button class="sw" data-c="${c}" style="background:${c}" aria-label="Colour ${c}" aria-pressed="${c===color}"></button>`).join('')}</div>
      <div class="qr-prev" id="qrPrev">Your QR code shows up here</div>
      <button class="btn primary wide" id="qrGo">${editing?'Update QR code':'Add QR code to card'}</button>
      ${editing?'':`<p class="hint small">It goes on the ${PAGE_LABEL[targetPage()].toLowerCase()}. The back is the usual spot, so people find it after reading.</p>`}`;
    let cur={url,color};const input=inner.querySelector('#qrUrl'),prev=inner.querySelector('#qrPrev'),err=inner.querySelector('#qrErr');
    const preview=()=>{err.hidden=true;if(!cur.url.trim()){prev.textContent='Your QR code shows up here';return;}const src=makeQR(normUrl(cur.url),cur.color);if(src)prev.innerHTML=`<img src="${src}" alt="QR code preview">`;else prev.textContent='Preview unavailable';};
    let qt;input.oninput=()=>{cur.url=input.value;if(!editing)qrDraft.url=input.value;clearTimeout(qt);qt=setTimeout(preview,250);};
    inner.querySelectorAll('[data-c]').forEach(b=>b.onclick=()=>{cur.color=b.dataset.c;if(!editing)qrDraft.color=cur.color;inner.querySelectorAll('[data-c]').forEach(x=>x.setAttribute('aria-pressed',x===b));preview();});
    inner.querySelector('#qrGo').onclick=()=>{
      const showErr=m=>{err.textContent=m;err.hidden=false;input.focus();};
      if(typeof QRCode==='undefined'){showErr('The QR generator did not load. Check your connection and reload the page.');return;}
      if(!cur.url.trim()){showErr('Paste a link to create a QR code.');return;}
      const u=normUrl(cur.url);const src=makeQR(u,cur.color);if(!src){showErr('That link is too long for a QR code. Try a shorter link.');return;}
      if(editing){patch(f.el.id,{url:u,color:cur.color,src});renderCtx();toast('QR code updated');}
      else{const ph=state.pages[targetPage()].els.find(e=>e.type==='qr'&&!e.src);
        if(ph){patch(ph.id,{url:u,color:cur.color,src});select(targetPage(),ph.id);}
        else addElement(Q({url:u,color:cur.color,src,y:150+stagger()}));
        qrDraft.url='';toast('QR code added');}
    };
    setTimeout(preview,0);
  }
  else if(panel==='background'){
    const pid=targetPage();const bg=state.pages[pid].bg;
    inner.innerHTML=head('Background')+`<p class="hint">Paper colour for the ${PAGE_LABEL[pid].toLowerCase()}.</p>
      <div class="swatches">${BG_COLORS.map(c=>`<button class="sw" data-bg="${c}" style="background:${c}" aria-label="Colour ${c}" aria-pressed="${c===bg}"></button>`).join('')}</div>
      <label class="custom"><input type="color" id="bgCustom" value="${bg}"> Custom colour</label>
      <button class="btn wide" id="bgAll">Use on every page</button>`;
    const setBg=(c,doCommit)=>{state.pages[pid].bg=c;const n=sheet.querySelector(`.page[data-page="${pid}"] .content`);if(n)n.style.background=c;if(doCommit){commit();}};
    inner.querySelectorAll('[data-bg]').forEach(b=>b.onclick=()=>{setBg(b.dataset.bg,true);inner.querySelectorAll('[data-bg]').forEach(x=>x.setAttribute('aria-pressed',x===b));inner.querySelector('#bgCustom').value=b.dataset.bg;});
    const cc=inner.querySelector('#bgCustom');cc.oninput=()=>setBg(cc.value,false);cc.onchange=()=>commit();
    inner.querySelector('#bgAll').onclick=()=>{const c=state.pages[pid].bg;Object.values(state.pages).forEach(p=>p.bg=c);renderStage();commit();toast('Background used on every page','Undo',undo);};
  }
  panelEl.innerHTML='';panelEl.appendChild(inner);
}

function applyTemplate(t){finishEditing();state.pages=t.make();sel=null;view='front';zoomMode='fit';renderStage();renderStrip();renderCtx();commit();toast(`${t.name} template applied`,'Undo',undo);}
function applyLayout(ly){
  finishEditing();const pids=VIEWS[view];const texts=[],photos=[],qrs=[];
  pids.forEach(pid=>[...state.pages[pid].els].sort((a,b)=>a.y-b.y).forEach(el=>{if(el.type==='text'&&el.text.trim())texts.push(el);if(el.type==='photo'&&el.src)photos.push(el);if(el.type==='qr'&&el.src)qrs.push(el);}));
  const made=ly.make();
  pids.forEach(pid=>{const els=made[pid]||[];els.forEach(el=>{
    if(el.type==='text'&&texts.length){const o=texts.shift();Object.assign(el,{text:o.text,font:o.font,color:o.color,bold:o.bold,italic:o.italic,ls:o.ls});}
    if(el.type==='photo'&&photos.length){const o=photos.shift();el.src=o.src;el.fit=o.fit;}
    if(el.type==='qr'&&qrs.length){const o=qrs.shift();Object.assign(el,{src:o.src,url:o.url,color:o.color});}
  });state.pages[pid].els=els;});
  sel=null;renderStage();renderCtx();commit();toast('Layout applied','Undo',undo);
}
function addText(kind){
  const o=stagger();let el;
  if(kind==='heading')el=T({text:'Your heading',font:'Playfair Display',size:34,bold:true,y:190+o});
  else if(kind==='sub')el=T({x:40,w:280,text:'Add a subheading',font:'Cormorant Garamond',size:22,italic:true,y:200+o});
  else el=T({x:40,w:280,text:'Write your message here',font:'Onest',size:16,lh:1.5,y:210+o});
  addElement(el);startEdit(el.id);
}
function addPair(pair){
  const pid=targetPage();let y=170+stagger();const ids=[];
  pair.forEach(p=>{const el=T({...p,y});state.pages[pid].els.push(el);ids.push(el.id);y+=Math.round(p.size*(p.lh||1.35))+14;});
  renderStage();select(pid,ids[0]);commit();
}

/* ---------- Photos ---------- */
function loadImage(file){return new Promise((res,rej)=>{const fr=new FileReader();fr.onload=()=>{const img=new Image();img.onload=()=>{const max=1400;const s=Math.min(1,max/Math.max(img.width,img.height));const c=document.createElement('canvas');c.width=Math.round(img.width*s);c.height=Math.round(img.height*s);const g=c.getContext('2d');const png=file.type==='image/png';if(!png){g.fillStyle='#fff';g.fillRect(0,0,c.width,c.height);}g.drawImage(img,0,0,c.width,c.height);res({src:c.toDataURL(png?'image/png':'image/jpeg',.86),w:c.width,h:c.height});};img.onerror=rej;img.src=fr.result;};fr.onerror=rej;fr.readAsDataURL(file);});}
function pickPhotoFor(pid,id){pendingPhoto={pid,id};fileInput.value='';fileInput.click();}
fileInput.addEventListener('change',()=>{const files=[...fileInput.files];if(files.length)handleUploads(files);});
async function handleUploads(files,dropAt){
  files=files.filter(f=>f.type.startsWith('image/'));if(!files.length){toast('Choose an image file: JPG, PNG or WebP');return;}
  let imgs=[];try{imgs=await Promise.all(files.map(loadImage));}catch(e){toast('That photo could not be opened. Try a different file.');return;}
  library.unshift(...imgs);library=library.slice(0,24);
  const first=imgs[0];
  if(pendingPhoto&&findEl(pendingPhoto.id)){const {pid,id}=pendingPhoto;patch(id,{src:first.src});select(pid,id);}
  else if(dropAt&&dropAt.id){patch(dropAt.id,{src:first.src});select(dropAt.pid,dropAt.id);}
  else{const cur=sel&&findEl(sel.id);if(cur&&cur.el.type==='photo'&&!dropAt){patch(cur.el.id,{src:first.src});renderCtx();}else addPhoto(first,dropAt&&dropAt.pid,dropAt&&dropAt.x,dropAt&&dropAt.y);}
  pendingPhoto=null;
  if(imgs.length>1)toast(`${imgs.length} photos uploaded. Find the rest under Photos.`);
  if(panel==='photos')renderPanel();
}
function addPhoto(img,pid,cx,cy){const m=240;const s=Math.min(m/img.w,m/img.h);const w=Math.round(img.w*s),h=Math.round(img.h*s);const o=cx==null?stagger():0;
  cx=cx==null?W/2+o:cx;cy=cy==null?H/2+o:cy;addElement(P({x:Math.round(cx-w/2),y:Math.round(cy-h/2),w,h,src:img.src,shape:'rounded'}),pid);}
function useLibrary(img){const cur=sel&&findEl(sel.id);if(cur&&cur.el.type==='photo'){patch(cur.el.id,{src:img.src});renderCtx();renderPanel();}else addPhoto(img);}
stage.addEventListener('dragover',e=>{if(e.dataTransfer&&[...e.dataTransfer.types].includes('Files')){e.preventDefault();stage.classList.add('dropping');}});
stage.addEventListener('dragleave',e=>{if(!stage.contains(e.relatedTarget))stage.classList.remove('dropping');});
stage.addEventListener('drop',e=>{e.preventDefault();stage.classList.remove('dropping');const files=[...e.dataTransfer.files];if(!files.length)return;
  const pageNode=e.target.closest('.page');const photoNode=e.target.closest('.el-photo');let at=null;
  if(pageNode){const r=pageNode.getBoundingClientRect();at={pid:pageNode.dataset.page,x:(e.clientX-r.left)/zoom,y:(e.clientY-r.top)/zoom};if(photoNode)at.id=photoNode.dataset.id;}
  else at={pid:targetPage()};
  handleUploads(files,at);});

/* ---------- QR ---------- */
function normUrl(u){u=u.trim();return /^[a-z][a-z0-9+.-]*:/i.test(u)?u:'https://'+u;}
function makeQR(text,color){if(typeof QRCode==='undefined')return null;try{const box=document.createElement('div');new QRCode(box,{text,width:480,height:480,colorDark:color,colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});const c=box.querySelector('canvas');return c?c.toDataURL('image/png'):null;}catch(e){return null;}}

/* ---------- Review ---------- */
function measureH(el){if(el.type!=='text')return el.h;const m=$('#measure');m.innerHTML='';const n=buildEl(el);n.style.position='relative';n.style.left='0';n.style.top='0';m.appendChild(n);const h=n.offsetHeight;m.innerHTML='';return h;}
function runChecks(){
  const out=[];
  ORDER.forEach(v=>VIEWS[v].forEach(pid=>state.pages[pid].els.forEach(el=>{
    const where=PAGE_LABEL[pid].toLowerCase();const item=msg=>out.push({v,pid,id:el.id,msg});
    if(el.type==='photo'&&!el.src)return item(`A photo frame on the ${where} is empty. It will print blank.`);
    if(el.type==='qr'&&!el.src)return item(`The QR code spot on the ${where} has no link yet.`);
    if(el.type==='text'&&!el.text.trim())return item(`There's an empty text box on the ${where}.`);
    if(el.type==='photo')return;
    const h=measureH(el),m=12;
    if(el.x<m||el.y<m||el.x+el.w>W-m||el.y+h>H-m)item(`${el.type==='text'?'Text':el.type==='qr'?'The QR code':'A sticker'} on the ${where} sits close to the edge and could be trimmed.`);
  })));
  return out;
}
function openReview(){finishEditing();modal.hidden=false;pvView=view;renderReview();$('#dlgClose').focus();}
function closeReview(){modal.hidden=true;}
function renderReview(){
  $('#pvTabs').querySelectorAll('[data-pv]').forEach(b=>b.setAttribute('aria-pressed',b.dataset.pv===pvView));
  const area=$('#pvStage');area.innerHTML='';const pids=VIEWS[pvView];
  const aw=Math.max(220,area.clientWidth||520)-8,ah=Math.min(440,window.innerHeight*.5);
  const s=Math.min(ah/H,aw/(W*pids.length),1);
  area.appendChild(mini(pids.map(pid=>[state.pages[pid],pid]),s,'preview'));
  const issues=runChecks();const list=$('#checkList');
  list.innerHTML=issues.length?issues.map((it,i)=>`<div class="check"><span class="ic warn">${ic('alert',18)}</span><div><p>${esc(it.msg)}</p><button class="link" data-fix="${i}">Show me</button></div></div>`).join('')
    :`<div class="check"><span class="ic ok">${ic('check',18)}</span><p>Everything looks ready to print.</p></div>`;
  list.querySelectorAll('[data-fix]').forEach(b=>b.onclick=()=>{const it=issues[+b.dataset.fix];closeReview();setView(it.v);if(findEl(it.id))select(it.pid,it.id);});
}
$('#pvTabs').addEventListener('click',e=>{const b=e.target.closest('[data-pv]');if(b){pvView=b.dataset.pv;renderReview();}});
modal.addEventListener('click',e=>{if(e.target===modal||e.target.closest('[data-close]'))closeReview();});
$('#confirmBtn').onclick=()=>{closeReview();toast('Design saved. Next up: paper and delivery options.');};
$('#nextBtn').onclick=openReview;

/* ---------- Toast ---------- */
let toastT;
function toast(msg,label,fn){toastEl.innerHTML=`<span>${esc(msg)}</span>${label?`<button>${label}</button>`:''}`;if(label)toastEl.querySelector('button').onclick=()=>{fn();hideToast();};toastEl.classList.add('show');clearTimeout(toastT);toastT=setTimeout(hideToast,4200);}
function hideToast(){toastEl.classList.remove('show');}

/* ---------- Keyboard ---------- */
let nudgeT;
document.addEventListener('keydown',e=>{
  const t=e.target;const typing=t.tagName==='INPUT'||t.tagName==='TEXTAREA'||t.tagName==='SELECT'||t.isContentEditable;
  const mod=e.metaKey||e.ctrlKey;const k=e.key.toLowerCase();
  if(!modal.hidden){if(e.key==='Escape')closeReview();return;}
  if(mod&&k==='z'&&!typing){e.preventDefault();e.shiftKey?redo():undo();return;}
  if(mod&&k==='y'&&!typing){e.preventDefault();redo();return;}
  if(typing)return;
  if(sel){
    if(e.key==='Delete'||e.key==='Backspace'){e.preventDefault();removeSel();return;}
    if(mod&&k==='d'){e.preventDefault();duplicate();return;}
    if(e.key==='Escape'){select(null);return;}
    if(e.key==='Enter'){const f=findEl(sel.id);if(f&&f.el.type==='text'){e.preventDefault();startEdit(sel.id);}return;}
    if(e.key.startsWith('Arrow')){e.preventDefault();const d=e.shiftKey?10:1;const f=findEl(sel.id);if(!f)return;
      if(e.key==='ArrowLeft')f.el.x-=d;if(e.key==='ArrowRight')f.el.x+=d;if(e.key==='ArrowUp')f.el.y-=d;if(e.key==='ArrowDown')f.el.y+=d;
      const n=nodeOf(sel.id);n.style.left=f.el.x+'px';n.style.top=f.el.y+'px';positionSelbox();clearTimeout(nudgeT);nudgeT=setTimeout(commit,350);}
  }else{
    if(e.key==='ArrowRight'){const i=ORDER.indexOf(view);if(i<2)setView(ORDER[i+1]);}
    if(e.key==='ArrowLeft'){const i=ORDER.indexOf(view);if(i>0)setView(ORDER[i-1]);}
  }
});

/* ---------- Wire up chrome ---------- */
$('#undoBtn').innerHTML=ic('undo');$('#redoBtn').innerHTML=ic('redo');$('#pencil').innerHTML=ic('pencil',15);
$('#prevBtn').innerHTML=ic('chevL');$('#nextPageBtn').innerHTML=ic('chevR');$('#dlgClose').innerHTML=ic('x');
$('#zoomIn').innerHTML=ic('plus',18);$('#zoomOut').innerHTML=ic('minus',18);$('#safeBtn').innerHTML=ic('margins',18);
$('#undoBtn').onclick=undo;$('#redoBtn').onclick=redo;
$('#prevBtn').onclick=()=>{const i=ORDER.indexOf(view);if(i>0)setView(ORDER[i-1]);};
$('#nextPageBtn').onclick=()=>{const i=ORDER.indexOf(view);if(i<2)setView(ORDER[i+1]);};
$('#zoomIn').onclick=()=>zoomBy(1.15);$('#zoomOut').onclick=()=>zoomBy(1/1.15);
$('#zoomPct').onclick=()=>{zoomMode='fit';applyZoom();};
$('#safeBtn').onclick=()=>{showSafe=!showSafe;$('#safeBtn').setAttribute('aria-pressed',showSafe);sheet.classList.toggle('show-safe',showSafe);if(showSafe)toast('Keep important things inside the dashed line');};
const titleIn=$('#title');titleIn.value=state.title;document.title=state.title+' | qrinto';
titleIn.oninput=()=>{state.title=titleIn.value||'Untitled card';document.title=state.title+' | qrinto';scheduleSave();};
titleIn.onkeydown=e=>{if(e.key==='Enter')titleIn.blur();};
if('ResizeObserver' in window)new ResizeObserver(()=>{if(zoomMode==='fit')applyZoom();}).observe(stage);
else window.addEventListener('resize',()=>{if(zoomMode==='fit')applyZoom();});
if(window.matchMedia('(max-width:760px)').matches){panelEl.hidden=true;panel=null;}

renderRail();renderPanel();renderStage();renderStrip();renderCtx();updateUndo();setStatus('saved');
if(document.fonts&&document.fonts.ready)document.fonts.ready.then(()=>{positionSelbox();renderStrip();});
})();
