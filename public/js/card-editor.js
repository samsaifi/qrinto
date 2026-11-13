(function(){
'use strict';
const CARD_TYPE=['single','double','folded'].includes(window.CARD_TYPE)?window.CARD_TYPE:'folded';
const LANDSCAPE=window.CARD_ORIENTATION==='landscape';
/* Page size in editor px. Designs below are drawn for portrait (360x504) and fitted to landscape at load */
/* ?size=WxH (inches) sets the page ratio: long side stays 504px, the other follows the ratio (4x4 = square) */
const CARD_SIZE=(()=>{const z=window.CARD_SIZE;return z&&z.w>0&&z.h>0?z:null;})();
const W=CARD_SIZE?Math.round(CARD_SIZE.w>=CARD_SIZE.h?504:504*CARD_SIZE.w/CARD_SIZE.h):(LANDSCAPE?504:360),
      H=CARD_SIZE?Math.round(CARD_SIZE.h>=CARD_SIZE.w?504:504*CARD_SIZE.h/CARD_SIZE.w):(LANDSCAPE?360:504),
      KEY='qrinto-card-editor-v1'+(CARD_TYPE==='folded'?'':'-'+CARD_TYPE)+(LANDSCAPE?'-landscape':'')+(CARD_SIZE?'-'+CARD_SIZE.w+'x'+CARD_SIZE.h:'');
document.documentElement.style.setProperty('--pw',W+'px');document.documentElement.style.setProperty('--ph',H+'px');
const $=s=>document.querySelector(s);
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const clamp=(v,a,b)=>Math.min(b,Math.max(a,v));

const ICONS={
  pen:'<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
  printer:'<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>',
  store:'<path d="M3 9l1.5-5h15L21 9"/><path d="M3 9h18v2a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0Z"/><path d="M5 13v8h14v-8"/><path d="M10 21v-5h4v5"/>',
  download:'<path d="M12 4v12M7 11l5 5 5-5M5 20h14"/>',
  tag:'<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
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

const FONTS=[['Onest',"'Onest',system-ui,sans-serif"],['Playfair Display',"'Playfair Display',Georgia,serif"],['Cormorant Garamond',"'Cormorant Garamond',Georgia,serif"],['DM Serif Display',"'DM Serif Display',Georgia,serif"],['Great Vibes',"'Great Vibes',cursive"],['Pinyon Script',"'Pinyon Script',cursive"],['Caveat',"'Caveat',cursive"],['Bebas Neue',"'Bebas Neue',Impact,sans-serif"],['Josefin Sans',"'Josefin Sans',system-ui,sans-serif"],['Montserrat',"'Montserrat',system-ui,sans-serif"],['Lora',"'Lora',Georgia,serif"],['Cinzel',"'Cinzel',Georgia,serif"],['Abril Fatface',"'Abril Fatface',Georgia,serif"],['Dancing Script',"'Dancing Script',cursive"],['Sacramento',"'Sacramento',cursive"],['Alex Brush',"'Alex Brush',cursive"],['Pacifico',"'Pacifico',cursive"],['Lobster',"'Lobster',cursive"],['Permanent Marker',"'Permanent Marker',cursive"],['Amatic SC',"'Amatic SC',cursive"],['Righteous',"'Righteous',system-ui,sans-serif"],['Fredoka',"'Fredoka',system-ui,sans-serif"]];
const fontCss=n=>(FONTS.find(f=>f[0]===n)||FONTS[0])[1];
const VIEWS={front:['front'],inside:['inL','inR'],back:['back']};
/* Sides shown for each card type: single = front only, double = front + back, folded = all */
const ORDER={single:['front'],double:['front','back'],folded:['front','inside','back']}[CARD_TYPE];
const LAST=ORDER[ORDER.length-1];
const VIEW_LABEL={front:'Front',inside:'Inside',back:'Back'};
/* Landscape folded cards fold on the long (horizontal) edge: inside pages stack top/bottom and pages turn vertically */
const STACK=LANDSCAPE&&CARD_TYPE==='folded';
const PAGE_LABEL=STACK?{front:'Front',inL:'Inside top',inR:'Inside bottom',back:'Back'}:{front:'Front',inL:'Inside left',inR:'Inside right',back:'Back'};
if(STACK)document.documentElement.classList.add('qe-stack');

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

/* Single-side designs: Front and Back replace one page, Inside replaces both inside pages */
const pg=(bg,els)=>({bg,els});
const MSG=o=>T({x:40,w:280,font:'Cormorant Garamond',size:20,color:'#3b3b3b',lh:1.55,...o});
const PAGE_TEMPLATES={
 front:[
  {name:'Anniversary',make:()=>({front:tplRaw(0).front})},
  {name:'Birthday',make:()=>({front:tplRaw(1).front})},
  {name:'Wedding',make:()=>({front:tplRaw(2).front})},
  {name:'Thank you',make:()=>({front:tplRaw(3).front})},
  {name:'Congrats',make:()=>({front:pg('#1f2a44',[S({x:60,y:60,w:60,h:60,char:'🎉'}),S({x:240,y:90,w:56,h:56,char:'✨'}),T({y:170,text:'CONGRATS',font:'Bebas Neue',size:92,color:'#ffffff',ls:4,lh:1}),T({y:276,text:'you did it!',font:'Caveat',size:52,color:'#ffd166',bold:true,lh:1.1}),T({x:40,y:400,w:280,text:'SO PROUD OF YOU',font:'Josefin Sans',size:14,color:'#c9d3ec',ls:4})])})},
  {name:'Christmas',make:()=>({front:pg('#14201a',[S({x:135,y:60,w:90,h:90,char:'🎄'}),T({y:180,text:'Merry Christmas',font:'Great Vibes',size:56,color:'#f6f1e7',lh:1.2}),T({y:262,text:'& A HAPPY NEW YEAR',font:'Cormorant Garamond',size:16,color:'#e8c547',ls:5}),T({x:40,y:400,w:280,text:'Warm wishes from our home to yours',font:'Cormorant Garamond',size:17,italic:true,color:'#cfdccf'})])})},
  {name:'Get well soon',make:()=>({front:pg('#ddebf7',[S({x:140,y:70,w:80,h:80,char:'🌼'}),T({y:176,text:'Get well',font:'Caveat',size:76,color:'#1f3a93',bold:true,lh:1}),T({y:256,text:'soon',font:'Caveat',size:76,color:'#1f3a93',bold:true,lh:1}),T({x:40,y:372,w:280,text:'Sending sunshine and a big hug your way',font:'Onest',size:16,color:'#3d5a8c',lh:1.45})])})},
  {name:'New baby',make:()=>({front:pg('#e9e3f5',[P({x:90,y:50,w:180,h:180,shape:'circle'}),T({y:256,text:'Welcome,',font:'Pinyon Script',size:46,color:'#5b3f8c',lh:1.2}),T({y:312,text:'little one',font:'Pinyon Script',size:46,color:'#5b3f8c',lh:1.2}),S({x:155,y:392,w:50,h:50,char:'🍼'})])})},
  {name:'Mother\'s Day',make:()=>({front:pg('#fde2d2',[S({x:130,y:56,w:100,h:100,char:'💐'}),T({y:184,text:'Happy',font:'DM Serif Display',size:44,color:'#a23b2a',lh:1.1}),T({y:236,text:'Mother\'s Day',font:'DM Serif Display',size:44,color:'#a23b2a',lh:1.1}),T({x:40,y:330,w:280,text:'To the woman who taught me everything',font:'Cormorant Garamond',size:19,italic:true,color:'#6b3a2e',lh:1.4})])})},
  {name:'Father\'s Day',make:()=>({front:pg('#e6edf5',[T({y:90,text:'BEST',font:'Bebas Neue',size:96,color:'#1f3a5f',ls:6,lh:1}),T({y:186,text:'DAD',font:'Bebas Neue',size:132,color:'#1f3a5f',ls:8,lh:1}),T({y:322,text:'ever',font:'Caveat',size:48,color:'#d9822b',bold:true,lh:1}),T({x:40,y:420,w:280,text:'HAPPY FATHER\'S DAY',font:'Josefin Sans',size:13,color:'#4a5d75',ls:4})])})},
  {name:'Graduation',make:()=>({front:pg('#ffffff',[S({x:140,y:56,w:80,h:80,char:'🎓'}),T({y:160,text:'CLASS OF',font:'Josefin Sans',size:16,color:'#7a5c2e',ls:6}),T({y:186,text:'2026',font:'Bebas Neue',size:110,color:'#1f2a44',ls:4,lh:1}),T({y:310,text:'Congratulations, Graduate',font:'Playfair Display',size:24,italic:true,color:'#1f2a44'}),T({x:40,y:390,w:280,text:'The future is yours',font:'Cormorant Garamond',size:17,color:'#7a5c2e'})])})},
  {name:'Sympathy',make:()=>({front:pg('#f6f1e7',[S({x:145,y:110,w:70,h:70,char:'🕊️'}),T({y:210,text:'With deepest',font:'Cormorant Garamond',size:34,italic:true,color:'#4f4a3e',lh:1.2}),T({y:254,text:'sympathy',font:'Cormorant Garamond',size:34,italic:true,color:'#4f4a3e',lh:1.2}),T({x:60,y:340,w:240,text:'Thinking of you and holding you close',font:'Cormorant Garamond',size:16,color:'#7a7466',lh:1.5})])})},
  {name:'Valentine',make:()=>({front:pg('#fbe9ec',[S({x:50,y:50,w:50,h:50,char:'💕'}),S({x:260,y:70,w:44,h:44,char:'💖'}),S({x:130,y:120,w:100,h:100,char:'❤️'}),T({y:250,text:'Be mine',font:'Great Vibes',size:72,color:'#c2185b',lh:1.2}),T({x:40,y:356,w:280,text:'HAPPY VALENTINE\'S DAY',font:'Josefin Sans',size:13,color:'#9b2242',ls:4})])})},
  {name:'Full photo',make:()=>({front:pg('#1f2a44',[P({x:0,y:0,w:360,h:504,shape:'square'}),T({y:388,text:'Hello there',font:'Playfair Display',size:46,color:'#ffffff',italic:true}),T({x:40,y:448,w:280,text:'A LITTLE NOTE FOR YOU',font:'Josefin Sans',size:12,color:'#ffffff',ls:4})])})},
  {name:'Thinking of you',make:()=>({front:pg('#e3efe8',[S({x:145,y:80,w:70,h:70,char:'🌙'}),T({y:180,text:'Thinking',font:'Caveat',size:66,color:'#2e5d47',bold:true,lh:1}),T({y:250,text:'of you',font:'Caveat',size:66,color:'#2e5d47',bold:true,lh:1}),T({x:40,y:360,w:280,text:'Wherever you are, you are in my heart',font:'Onest',size:15,color:'#4a6b5a',lh:1.5})])})},
  {name:'Love you',make:()=>({front:pg('#ffffff',[P({x:40,y:40,w:280,h:280,shape:'rounded'}),T({y:340,text:'I love you',font:'Pinyon Script',size:58,color:'#9b2242',lh:1.2}),T({x:40,y:420,w:280,text:'more than words can say',font:'Cormorant Garamond',size:17,italic:true,color:'#6b3a47'})])})},
  {name:'New Year',make:()=>({front:pg('#1f2a44',[S({x:70,y:60,w:56,h:56,char:'✨'}),S({x:232,y:60,w:56,h:56,char:'🥂'}),T({y:150,text:'HAPPY',font:'Bebas Neue',size:72,color:'#e8c547',ls:6,lh:1}),T({y:220,text:'NEW YEAR',font:'Bebas Neue',size:72,color:'#e8c547',ls:6,lh:1}),T({y:310,text:'2027',font:'Playfair Display',size:52,italic:true,color:'#ffffff'}),T({x:40,y:410,w:280,text:'Cheers to new beginnings',font:'Cormorant Garamond',size:17,color:'#c9d3ec'})])})},
  {name:'Engagement',make:()=>({front:pg('#f6f1e7',[S({x:150,y:40,w:60,h:60,char:'💍'}),P({x:70,y:116,w:220,h:220,shape:'circle'}),T({y:350,text:'She said yes!',font:'Great Vibes',size:52,color:'#7a5c2e',lh:1.2}),T({x:40,y:426,w:280,text:'CONGRATULATIONS ON YOUR ENGAGEMENT',font:'Josefin Sans',size:11,color:'#7a5c2e',ls:2})])})},
  {name:'Housewarming',make:()=>({front:pg('#fff3d1',[S({x:130,y:70,w:100,h:100,char:'🏡'}),T({y:200,text:'Home',font:'DM Serif Display',size:64,color:'#7a4b12',lh:1}),T({y:268,text:'sweet home',font:'DM Serif Display',size:40,color:'#7a4b12',lh:1.1}),T({x:40,y:370,w:280,text:'Happy housewarming! May every room be full of laughter.',font:'Onest',size:15,color:'#7a5b12',lh:1.5})])})},
  {name:'Retirement',make:()=>({front:pg('#fde2d2',[S({x:140,y:60,w:80,h:80,char:'☀️'}),T({y:166,text:'Happy',font:'Playfair Display',size:46,italic:true,color:'#a23b2a',lh:1.1}),T({y:222,text:'Retirement',font:'Playfair Display',size:52,color:'#a23b2a',lh:1.1}),T({x:40,y:330,w:280,text:'No alarms, no meetings. Just sunny days ahead.',font:'Cormorant Garamond',size:19,italic:true,color:'#6b3a2e',lh:1.45})])})},
  {name:'Photo pair',make:()=>({front:pg('#ffffff',[P({x:24,y:24,w:150,h:220}),P({x:186,y:24,w:150,h:220}),T({y:276,text:'Best friends',font:'Caveat',size:58,color:'#2b59c3',bold:true,lh:1.1}),T({x:40,y:356,w:280,text:'Through every chapter, side by side',font:'Onest',size:15,color:'#555555',lh:1.5})])})}
 ],
 inside:[
  {name:'Anniversary',make:()=>{const p=tplRaw(0);return{inL:p.inL,inR:p.inR};}},
  {name:'Birthday',make:()=>{const p=tplRaw(1);return{inL:p.inL,inR:p.inR};}},
  {name:'Wedding',make:()=>{const p=tplRaw(2);return{inL:p.inL,inR:p.inR};}},
  {name:'Thank you',make:()=>{const p=tplRaw(3);return{inL:p.inL,inR:p.inR};}},
  {name:'Photo and message',make:()=>({inL:pg('#ffffff',[P({x:30,y:30,w:300,h:444})]),inR:pg('#ffffff',[MSG({y:170,text:'Write your message here. Tell them what this moment means to you.'}),T({x:40,y:330,w:280,text:'With love',font:'Great Vibes',size:32,color:'#9b2242'})])})},
  {name:'Big quote',make:()=>({inL:pg('#fbe9ec',[T({y:120,text:'“',font:'Playfair Display',size:120,color:'#9b2242',lh:1}),T({x:40,y:230,w:280,text:'Love is composed of a single soul inhabiting two bodies.',font:'Playfair Display',size:22,italic:true,color:'#6b3a47',lh:1.4}),T({x:40,y:370,w:280,text:'ARISTOTLE',font:'Josefin Sans',size:12,color:'#9b2242',ls:4})]),inR:pg('#ffffff',[MSG({y:180,text:'Write your message here'})])})},
  {name:'Two photos',make:()=>({inL:pg('#ffffff',[P({x:30,y:30,w:300,h:212}),P({x:30,y:262,w:300,h:212})]),inR:pg('#ffffff',[MSG({y:180,text:'A few of my favourite moments with you. Here is to making many more.'})])})},
  {name:'Photo grid',make:()=>({inL:pg('#ffffff',[P({x:24,y:24,w:150,h:150}),P({x:186,y:24,w:150,h:150}),P({x:24,y:186,w:150,h:150}),P({x:186,y:186,w:150,h:150}),T({y:370,text:'Our favourite moments',font:'Caveat',size:30,color:'#2b2b2b',bold:true})]),inR:pg('#ffffff',[MSG({y:180,text:'Write your message here'})])})},
  {name:'Colour block',make:()=>({inL:pg('#e3efe8',[S({x:140,y:200,w:80,h:80,char:'🌿'})]),inR:pg('#ffffff',[MSG({y:170,text:'Write your message here. Keep it short and from the heart.',color:'#1e3b2e'})])})},
  {name:'Handwritten note',make:()=>({inL:pg('#fffdf5',[T({x:40,y:120,w:280,text:'Hey you,',font:'Caveat',size:36,color:'#2b2b2b',align:'left',bold:true}),T({x:40,y:180,w:280,text:'I just wanted to write you a little note to say how much you mean to me.',font:'Caveat',size:26,color:'#2b2b2b',align:'left',lh:1.35})]),inR:pg('#fffdf5',[T({x:40,y:120,w:280,text:'Thank you for every laugh, every late-night chat and every time you just got it.',font:'Caveat',size:26,color:'#2b2b2b',align:'left',lh:1.35}),T({x:40,y:330,w:280,text:'Always, me',font:'Caveat',size:32,color:'#e4572e',align:'right',bold:true})])})},
  {name:'Letter',make:()=>({inL:pg('#ffffff',[]),inR:pg('#ffffff',[MSG({y:90,text:'Dear Alex,',align:'left',size:22,italic:true}),MSG({y:140,text:'Write your letter here. There is plenty of room to say everything you want to say.',align:'left'}),MSG({y:380,text:'With love,',align:'left',italic:true}),T({x:40,y:412,w:280,text:'Sam',font:'Great Vibes',size:34,color:'#3b3b3b',align:'left'})])})},
  {name:'Congratulations',make:()=>({inL:pg('#1f2a44',[S({x:130,y:170,w:100,h:100,char:'🎉'}),T({y:290,text:'WELL DONE',font:'Bebas Neue',size:48,color:'#ffd166',ls:4,lh:1})]),inR:pg('#ffffff',[T({x:40,y:170,w:280,text:'You worked so hard for this, and it shows. Enjoy every bit of it. You earned it!',font:'Onest',size:17,color:'#1f2a44',lh:1.55})])})},
  {name:'Holiday greeting',make:()=>({inL:pg('#14201a',[S({x:140,y:200,w:80,h:80,char:'❄️'})]),inR:pg('#ffffff',[MSG({y:160,text:'May your home be filled with warmth, your table with good food and your heart with joy this season.',color:'#14201a'}),T({x:40,y:340,w:280,text:'Season\'s greetings',font:'Great Vibes',size:32,color:'#8e1b1b'})])})},
  {name:'Sympathy',make:()=>({inL:pg('#f6f1e7',[]),inR:pg('#f6f1e7',[MSG({y:170,text:'There are no words to ease the pain of losing someone so dear. Please know we are here for you, today and always.',color:'#4f4a3e',italic:true}),S({x:155,y:360,w:50,h:50,char:'🕊️'})])})},
  {name:'Get well',make:()=>({inL:pg('#ddebf7',[S({x:130,y:190,w:100,h:100,char:'🌻'})]),inR:pg('#ffffff',[T({x:40,y:180,w:280,text:'Rest up, take it slow and know that lots of people are rooting for you. See you back on your feet soon!',font:'Caveat',size:26,color:'#1f3a93',lh:1.3})])})},
  {name:'QR video message',make:()=>({inL:pg('#ffffff',[Q({x:110,y:150,w:140,h:140}),T({x:40,y:310,w:280,text:'Scan to watch my video message',font:'Onest',size:15,color:'#444444'})]),inR:pg('#ffffff',[MSG({y:180,text:'Write your message here'})])})},
  {name:'Reasons I love you',make:()=>({inL:pg('#fbe9ec',[T({y:180,text:'Reasons',font:'Great Vibes',size:52,color:'#9b2242',lh:1.2}),T({y:246,text:'I love you',font:'Great Vibes',size:52,color:'#9b2242',lh:1.2})]),inR:pg('#ffffff',[MSG({y:110,text:'1. Your laugh\n2. The way you make coffee\n3. Your terrible jokes\n4. How you hold my hand\n5. Everything else',align:'left',lh:1.8,color:'#4a2a33'})])})},
  {name:'From all of us',make:()=>({inL:pg('#ffffff',[T({y:200,text:'From all of us',font:'Playfair Display',size:36,italic:true,color:'#1f2a44',lh:1.2}),T({x:40,y:260,w:280,text:'SIGN BELOW',font:'Josefin Sans',size:12,color:'#7a7a7a',ls:4})]),inR:pg('#ffffff',[])})},
  {name:'New baby',make:()=>({inL:pg('#e9e3f5',[P({x:60,y:80,w:240,h:240,shape:'circle'}),T({y:350,text:'Born June 3, 2026',font:'Josefin Sans',size:14,color:'#5b3f8c',ls:2})]),inR:pg('#ffffff',[MSG({y:180,text:'Welcome to the world, little one. You are already so loved.',color:'#5b3f8c'})])})},
  {name:'Minimal',make:()=>({inL:pg('#ffffff',[]),inR:pg('#ffffff',[S({x:160,y:210,w:40,h:40,char:'✨'}),MSG({y:270,text:'Write a short line here',size:18,italic:true})])})}
 ],
 back:[
  {name:'Made with love',make:()=>({back:pg('#ffffff',[T({x:40,y:200,w:280,text:'Made with love',font:'Cormorant Garamond',size:18,italic:true})])})},
  {name:'QR video message',make:()=>({back:pg('#ffffff',[Q({x:120,y:150,w:120,h:120}),T({x:40,y:286,w:280,text:'Scan for a video message',size:14,color:'#444444'})])})},
  {name:'QR playlist',make:()=>({back:pg('#1f2a44',[S({x:155,y:90,w:50,h:50,char:'🎵'}),Q({x:120,y:160,w:120,h:120}),T({x:40,y:296,w:280,text:'Scan for our playlist',font:'Onest',size:14,color:'#ffffff'})])})},
  {name:'QR photo album',make:()=>({back:pg('#f6f1e7',[Q({x:120,y:140,w:120,h:120}),T({x:40,y:276,w:280,text:'See all the photos',font:'Caveat',size:28,color:'#7a5c2e',bold:true})])})},
  {name:'QR gift registry',make:()=>({back:pg('#ffffff',[S({x:155,y:80,w:50,h:50,char:'🎁'}),Q({x:120,y:150,w:120,h:120}),T({x:40,y:286,w:280,text:'OUR GIFT REGISTRY',font:'Josefin Sans',size:12,color:'#7a5c2e',ls:4})])})},
  {name:'Little heart',make:()=>({back:pg('#fbe9ec',[S({x:150,y:200,w:60,h:60,char:'💖'})])})},
  {name:'XOXO',make:()=>({back:pg('#ffffff',[T({y:210,text:'xoxo',font:'Great Vibes',size:56,color:'#9b2242',lh:1.2})])})},
  {name:'Signature',make:()=>({back:pg('#ffffff',[T({x:40,y:190,w:280,text:'With love, from',font:'Cormorant Garamond',size:17,italic:true,color:'#5a4520'}),T({y:226,text:'Ana & Theo',font:'Pinyon Script',size:40,color:'#7a5c2e',lh:1.3})])})},
  {name:'Est. date',make:()=>({back:pg('#f6f1e7',[T({y:190,text:'EST.',font:'Josefin Sans',size:14,color:'#7a5c2e',ls:6}),T({y:214,text:'2026',font:'Playfair Display',size:56,color:'#7a5c2e',lh:1.1})])})},
  {name:'Small photo',make:()=>({back:pg('#ffffff',[P({x:105,y:130,w:150,h:150,shape:'circle'}),T({x:40,y:300,w:280,text:'Add a caption',font:'Cormorant Garamond',size:17,italic:true,color:'#555555'})])})},
  {name:'Full photo',make:()=>({back:pg('#ffffff',[P({x:30,y:30,w:300,h:390,shape:'rounded'})])})},
  {name:'Botanical',make:()=>({back:pg('#e3efe8',[S({x:100,y:210,w:48,h:48,char:'🌿'}),S({x:156,y:210,w:48,h:48,char:'🌸'}),S({x:212,y:210,w:48,h:48,char:'🌿'})])})},
  {name:'Make a wish',make:()=>({back:pg('#1f2a44',[S({x:150,y:170,w:60,h:60,char:'✨'}),T({y:250,text:'Make a wish',font:'Caveat',size:40,color:'#ffd166',bold:true,lh:1.1})])})},
  {name:'Handmade',make:()=>({back:pg('#fffdf5',[T({x:40,y:210,w:280,text:'handmade with love, just for you',font:'Caveat',size:28,color:'#e4572e',lh:1.3})])})},
  {name:'Hashtag',make:()=>({back:pg('#ffffff',[T({x:40,y:200,w:280,text:'SHARE YOUR PHOTOS',font:'Josefin Sans',size:12,color:'#7a7a7a',ls:4}),T({y:228,text:'#AnaAndTheo',font:'Playfair Display',size:32,italic:true,color:'#1f2a44'})])})},
  {name:'Short quote',make:()=>({back:pg('#ffffff',[T({x:50,y:190,w:260,text:'“The best things in life are the people we love.”',font:'Playfair Display',size:19,italic:true,color:'#3b3b3b',lh:1.5})])})},
  {name:'From address',make:()=>({back:pg('#ffffff',[T({x:40,y:180,w:280,text:'FROM',font:'Josefin Sans',size:12,color:'#7a7a7a',ls:4,align:'left'}),T({x:40,y:204,w:280,text:'The Smith family\n12 Garden Lane\nLondon',font:'Onest',size:15,color:'#2a2a2a',align:'left',lh:1.6})])})},
  {name:'Celebrate',make:()=>({back:pg('#fff3d1',[S({x:110,y:200,w:56,h:56,char:'🎈'}),S({x:194,y:200,w:56,h:56,char:'🎉'})])})},
  {name:'Navy plain',make:()=>({back:pg('#1f2a44',[])})},
  {name:'Blank',make:()=>({back:pg('#ffffff',[])})}
 ]
};

/* Layout helpers: placeholder heading / short line / message / caption */
const LH=o=>T({text:'Add a heading',font:'Playfair Display',size:42,...o});
const LL=o=>T({x:40,w:280,text:'Add a short line',size:16,...o});
const LM=o=>T({x:40,w:280,text:'Write your message here',font:'Cormorant Garamond',size:20,lh:1.55,...o});
const LC=o=>T({x:40,w:280,text:'Add a caption',size:13,color:'#555555',...o});
const LAYOUTS={
 front:[
  {name:'Photo and caption',make:()=>({front:[P({x:30,y:30,w:300,h:290}),T({y:340,text:'Add a heading',font:'Great Vibes',size:42}),LL({y:412})]})},
  {name:'Full photo',make:()=>({front:[P({x:0,y:0,w:360,h:504,shape:'square'}),LH({y:404,color:'#ffffff'})]})},
  {name:'Circle photo',make:()=>({front:[P({x:80,y:56,w:200,h:200,shape:'circle'}),LH({y:286}),LL({y:372})]})},
  {name:'Words only',make:()=>({front:[LH({y:166,size:54,lh:1.15}),LL({y:300,size:18})]})},
  {name:'Photo on top',make:()=>({front:[P({x:0,y:0,w:360,h:280,shape:'square'}),LH({y:304,size:38}),LL({y:378})]})},
  {name:'Photo on bottom',make:()=>({front:[LH({y:56,size:38}),LL({y:126}),P({x:0,y:224,w:360,h:280,shape:'square'})]})},
  {name:'Framed photo',make:()=>({front:[P({x:40,y:40,w:280,h:360,shape:'square'}),LH({y:420,size:34})]})},
  {name:'Tall photo',make:()=>({front:[P({x:60,y:40,w:240,h:300}),LH({y:360,size:36}),LL({y:424})]})},
  {name:'Two photos',make:()=>({front:[P({x:24,y:24,w:150,h:220}),P({x:186,y:24,w:150,h:220}),LH({y:270,size:38}),LL({y:346})]})},
  {name:'Two photos stacked',make:()=>({front:[P({x:30,y:30,w:300,h:170}),P({x:30,y:212,w:300,h:170}),LH({y:402,size:34})]})},
  {name:'Three photo strip',make:()=>({front:[P({x:24,y:24,w:100,h:300}),P({x:130,y:24,w:100,h:300}),P({x:236,y:24,w:100,h:300}),LH({y:346,size:36}),LL({y:418})]})},
  {name:'Grid of four',make:()=>({front:[P({x:24,y:24,w:150,h:150}),P({x:186,y:24,w:150,h:150}),P({x:24,y:186,w:150,h:150}),P({x:186,y:186,w:150,h:150}),LH({y:356,size:34}),LL({y:420})]})},
  {name:'One big, two small',make:()=>({front:[P({x:24,y:24,w:312,h:260}),P({x:24,y:296,w:150,h:110}),P({x:186,y:296,w:150,h:110}),LH({y:424,size:32})]})},
  {name:'Small circle',make:()=>({front:[P({x:130,y:60,w:100,h:100,shape:'circle'}),LH({y:190,size:54,lh:1.15}),LL({y:306})]})},
  {name:'Heading, photo, line',make:()=>({front:[LH({y:36,size:38}),P({x:40,y:108,w:280,h:280}),LL({y:416})]})},
  {name:'Photo on the left',make:()=>({front:[P({x:0,y:0,w:180,h:504,shape:'square'}),LH({x:192,w:156,y:180,size:30,align:'left'}),LL({x:192,w:156,y:262,size:14,align:'left'})]})},
  {name:'Photo on the right',make:()=>({front:[P({x:180,y:0,w:180,h:504,shape:'square'}),LH({x:12,w:156,y:180,size:30,align:'right'}),LL({x:12,w:156,y:262,size:14,align:'right'})]})},
  {name:'Snapshot',make:()=>({front:[P({x:50,y:50,w:260,h:260,shape:'square'}),LH({y:330,font:'Caveat',size:40}),LL({y:400})]})},
  {name:'Full photo, title on top',make:()=>({front:[P({x:0,y:0,w:360,h:504,shape:'square'}),LH({y:40,color:'#ffffff'}),LL({y:104,color:'#ffffff'})]})},
  {name:'Small square photo',make:()=>({front:[P({x:105,y:120,w:150,h:150,shape:'square'}),LH({y:300,size:34}),LL({y:370})]})},
  {name:'Big circle photo',make:()=>({front:[P({x:30,y:36,w:300,h:300,shape:'circle'}),LH({y:356,size:38}),LL({y:426})]})},
  {name:'Bold title',make:()=>({front:[LH({y:150,font:'Bebas Neue',size:84,lh:1,ls:3}),LL({y:330,size:18})]})},
  {name:'Photo with band',make:()=>({front:[P({x:0,y:0,w:360,h:384,shape:'square'}),LH({y:398,size:32}),LL({y:452,size:14})]})},
  {name:'Corner title',make:()=>({front:[LH({x:30,w:300,y:380,size:36,align:'left'}),LL({x:30,w:300,y:440,align:'left'})]})},
  {name:'Banner photo',make:()=>({front:[LH({y:56,size:38}),P({x:24,y:140,w:312,h:210}),LL({y:378})]})}
 ],
 inside:[
  {name:'Message on the right',make:()=>({inL:[],inR:[LM({y:180})]})},
  {name:'Message on both sides',make:()=>({inL:[LM({y:180})],inR:[LM({y:180,text:'And a little more here'})]})},
  {name:'Photo and message',make:()=>({inL:[P({x:30,y:30,w:300,h:444})],inR:[LM({y:180})]})},
  {name:'Blank inside',make:()=>({inL:[],inR:[]})},
  {name:'Message on the left',make:()=>({inL:[LM({y:180})],inR:[]})},
  {name:'Message and photo',make:()=>({inL:[LM({y:180})],inR:[P({x:30,y:30,w:300,h:444})]})},
  {name:'Two photos and message',make:()=>({inL:[P({x:30,y:30,w:300,h:212}),P({x:30,y:262,w:300,h:212})],inR:[LM({y:180})]})},
  {name:'Photo grid and message',make:()=>({inL:[P({x:24,y:24,w:150,h:150}),P({x:186,y:24,w:150,h:150}),P({x:24,y:186,w:150,h:150}),P({x:186,y:186,w:150,h:150}),LC({y:360})],inR:[LM({y:180})]})},
  {name:'Full-bleed photo',make:()=>({inL:[P({x:0,y:0,w:360,h:504,shape:'square'})],inR:[LM({y:180})]})},
  {name:'Circle photo and caption',make:()=>({inL:[P({x:60,y:90,w:240,h:240,shape:'circle'}),LC({y:356})],inR:[LM({y:180})]})},
  {name:'Heading and message',make:()=>({inL:[],inR:[LH({y:120,size:34}),LM({y:200})]})},
  {name:'Heading left, message right',make:()=>({inL:[LH({y:210,size:40})],inR:[LM({y:180})]})},
  {name:'Photos on both sides',make:()=>({inL:[P({x:30,y:30,w:300,h:444})],inR:[P({x:30,y:30,w:300,h:444})]})},
  {name:'Photo and words, twice',make:()=>({inL:[P({x:30,y:30,w:300,h:240}),LM({y:296})],inR:[P({x:30,y:30,w:300,h:240}),LM({y:296,text:'And a little more here'})]})},
  {name:'QR and message',make:()=>({inL:[Q({x:110,y:160,w:140,h:140}),LC({y:318,text:'Scan to watch my video'})],inR:[LM({y:180})]})},
  {name:'Message and QR',make:()=>({inL:[LM({y:180})],inR:[Q({x:110,y:160,w:140,h:140}),LC({y:318,text:'Scan to watch my video'})]})},
  {name:'Message and signature',make:()=>({inL:[],inR:[LM({y:150}),T({x:40,y:350,w:280,text:'Your name',font:'Great Vibes',size:34})]})},
  {name:'Letter',make:()=>({inL:[],inR:[LM({y:80,text:'Dear friend,',align:'left',italic:true}),LM({y:130,text:'Write your letter here',align:'left'}),LM({y:380,text:'With love,',align:'left',italic:true})]})},
  {name:'Photo strip and message',make:()=>({inL:[P({x:30,y:24,w:300,h:144}),P({x:30,y:180,w:300,h:144}),P({x:30,y:336,w:300,h:144})],inR:[LM({y:180})]})},
  {name:'Small photo above message',make:()=>({inL:[],inR:[P({x:105,y:60,w:150,h:150,shape:'circle'}),LM({y:240})]})},
  {name:'Long letter',make:()=>({inL:[LM({y:80,align:'left'})],inR:[LM({y:80,text:'And a little more here',align:'left'})]})},
  {name:'Message above photo',make:()=>({inL:[],inR:[LM({y:70}),P({x:30,y:250,w:300,h:224})]})},
  {name:'Captioned photo',make:()=>({inL:[P({x:30,y:30,w:300,h:380}),LC({y:428})],inR:[LM({y:180})]})},
  {name:'Two headings',make:()=>({inL:[LH({y:220,size:36})],inR:[LH({y:220,size:36,text:'And another'})]})},
  {name:'Photo, QR and message',make:()=>({inL:[LM({y:180})],inR:[P({x:30,y:30,w:300,h:260}),Q({x:130,y:320,w:100,h:100}),LC({y:432,text:'Scan me'})]})}
 ],
 back:[
  {name:'QR video message',make:()=>({back:[Q({x:120,y:150,w:120,h:120}),LC({y:286,text:'Scan for a video message',size:14,color:'#444444'})]})},
  {name:'Short note',make:()=>({back:[T({x:40,y:200,w:280,text:'Made with love',font:'Cormorant Garamond',size:18,italic:true})]})},
  {name:'Blank back',make:()=>({back:[]})},
  {name:'Small QR at bottom',make:()=>({back:[Q({x:140,y:330,w:80,h:80}),LC({y:420,text:'Scan me'})]})},
  {name:'Large QR',make:()=>({back:[Q({x:90,y:110,w:180,h:180}),LC({y:310,text:'Scan for a surprise',size:14})]})},
  {name:'Heading and QR',make:()=>({back:[LH({y:80,size:30}),Q({x:120,y:160,w:120,h:120}),LC({y:300,text:'Scan for a video message'})]})},
  {name:'QR left, text right',make:()=>({back:[Q({x:40,y:190,w:120,h:120}),LC({x:176,w:150,y:226,text:'Scan for a video message',align:'left',size:14})]})},
  {name:'Text left, QR right',make:()=>({back:[LC({x:34,w:150,y:226,text:'Scan for a video message',align:'right',size:14}),Q({x:200,y:190,w:120,h:120})]})},
  {name:'Circle photo',make:()=>({back:[P({x:105,y:130,w:150,h:150,shape:'circle'}),LC({y:300})]})},
  {name:'Full photo',make:()=>({back:[P({x:30,y:30,w:300,h:400})]})},
  {name:'Photo and note',make:()=>({back:[P({x:30,y:30,w:300,h:240}),LL({y:300})]})},
  {name:'Two small photos',make:()=>({back:[P({x:30,y:150,w:145,h:145}),P({x:185,y:150,w:145,h:145}),LC({y:316})]})},
  {name:'Photo and QR',make:()=>({back:[P({x:30,y:40,w:300,h:220}),Q({x:130,y:280,w:100,h:100}),LC({y:394,text:'Scan me'})]})},
  {name:'Heading only',make:()=>({back:[LH({y:210,size:36})]})},
  {name:'Heading and note',make:()=>({back:[LH({y:170,size:34}),LL({y:240})]})},
  {name:'Quote',make:()=>({back:[T({x:50,y:190,w:260,text:'Add a favourite quote',font:'Playfair Display',size:20,italic:true,lh:1.5})]})},
  {name:'Signature',make:()=>({back:[T({y:200,text:'Your name',font:'Great Vibes',size:40,lh:1.3})]})},
  {name:'Note at the top',make:()=>({back:[LL({y:60})]})},
  {name:'Note at the bottom',make:()=>({back:[LL({y:410})]})},
  {name:'Address block',make:()=>({back:[LC({y:180,text:'FROM',align:'left',ls:4,size:12}),T({x:40,y:204,w:280,text:'Your name\nStreet address\nTown',size:15,align:'left',lh:1.6})]})},
  {name:'Two QR codes',make:()=>({back:[Q({x:50,y:170,w:110,h:110}),Q({x:200,y:170,w:110,h:110}),LC({x:30,w:150,y:294,text:'Video'}),LC({x:180,w:150,y:294,text:'Photos'})]})},
  {name:'Three photo strip',make:()=>({back:[P({x:24,y:160,w:100,h:100}),P({x:130,y:160,w:100,h:100}),P({x:236,y:160,w:100,h:100}),LC({y:282})]})},
  {name:'Small emblem',make:()=>({back:[P({x:150,y:180,w:60,h:60,shape:'circle'}),LC({y:256})]})},
  {name:'Grid of four',make:()=>({back:[P({x:50,y:80,w:125,h:125}),P({x:185,y:80,w:125,h:125}),P({x:50,y:215,w:125,h:125}),P({x:185,y:215,w:125,h:125}),LC({y:366})]})},
  {name:'Note and QR',make:()=>({back:[LM({y:110}),Q({x:130,y:270,w:100,h:100}),LC({y:384,text:'Scan me'})]})}
 ]
};

const STICKERS={Love:['💖','💕','💞','❤️','💌','🌹','💐','💍'],Celebrate:['🎉','🎈','🎂','🎁','🥂','✨','🎊','🍾'],Nature:['🌿','🌸','🌼','🌻','🍃','🌙','☀️','🌈'],Fun:['😊','🥳','😍','🤗','👏','🙌','⭐','🦋'],
 Hearts:['🧡','💛','💚','💙','💜','🤍','🖤','💘','💝','💗'],
 Faces:['😂','🥰','😘','😎','🤩','😇','🤓','😜','🤭','😴'],
 Animals:['🐶','🐱','🐼','🦊','🐻','🐰','🦄','🐝','🐢','🐧'],
 Food:['🍕','🍩','🍪','🧁','🍓','🍉','🍦','☕','🍫','🍔'],
 Party:['🎀','🎆','🎇','🎏','🎶','🎵','🎤','🕺','💃','🎺'],
 Holidays:['🎄','🎅','⛄','❄️','🎃','👻','🪔','🐣','🕎','🧧'],
 Garden:['🌷','🌺','🌵','🌴','🍀','🍁','🌊','🌳','🪴','🌾'],
 Travel:['✈️','🚗','🏖️','🗺️','🧳','🏝️','🚀','🗽','🎡','⛺'],
 Hobbies:['⚽','🏀','🎾','🏆','🥇','🎨','📚','🎮','🎸','📷'],
 Symbols:['💯','🔥','🌟','💫','👑','💎','🎯','☮️','♾️','✅']};
const BG_COLORS={
 'Classic':['#ffffff','#f6f1e7','#fbe9ec','#fde2d2','#fff3d1','#e3efe8','#ddebf7','#e9e3f5','#1f2a44','#14201a'],
 'Light & neutral':['#fffdf8','#fbf8f1','#f3ede0','#efe8dc','#f5f5f4','#eeeeec','#fdf6ec','#f5ede3','#efe6da','#e8e2d6'],
 'Pastel':['#f9d2d2','#f9ded2','#f9e9d2','#f9f5d2','#f1f9d2','#e6f9d2','#daf9d2','#d2f9d6','#d2f9e2','#d2f9ed','#d2f9f9','#d2edf9','#d2e2f9','#d2d6f9','#dad2f9','#e6d2f9','#f1d2f9','#f9d2f5','#f9d2e9','#f9d2de'],
 'Soft':['#e3bfbf','#e3cabf','#e3d5bf','#e3e0bf','#dce3bf','#d1e3bf','#c6e3bf','#bfe3c2','#bfe3cd','#bfe3d8','#bfe3e3','#bfd8e3','#bfcde3','#bfc2e3','#c6bfe3','#d1bfe3','#dcbfe3','#e3bfe0','#e3bfd5','#e3bfca'],
 'Warm':['#f0a8a8','#f0afa8','#f0b7a8','#f0bea8','#f0c5a8','#f0cca8','#f0d3a8','#f0daa8','#f0e1a8','#de837c','#de8d7c','#de977c','#dea07c','#deaa7c','#deb47c','#debe7c','#c9625e','#c9775e','#c98d5e','#c9a25e'],
 'Cool':['#bae8d9','#bae8dd','#bae8e0','#bae8e4','#bae8e8','#bae4e8','#bae0e8','#badde8','#bad9e8','#bad5e8','#86a8cb','#86a3cb','#869dcb','#8697cb','#8691cb','#868bcb','#8686cb','#8b86cb','#9186cb','#9786cb'],
 'Earthy':['#bb9081','#bb9881','#bba181','#bbaa81','#bbb281','#bbbb81','#b2bb81','#aabb81','#a1bb81','#98bb81','#896458','#896b58','#897358','#897a58','#898158','#898958','#818958','#7a8958','#738958','#6b8958'],
 'Bright':['#ef3939','#ef6f39','#efa639','#efdd39','#cbef39','#94ef39','#5def39','#39ef4b','#39ef82','#39efb8','#39efef','#39b8ef','#3982ef','#394bef','#5d39ef','#9439ef','#cb39ef','#ef39dd','#ef39a6','#ef396f'],
 'Jewel':['#832121','#833e21','#835b21','#837921','#6f8321','#528321','#348321','#21832a','#218348','#218365','#218383','#216583','#214883','#212a83','#342183','#522183','#6f2183','#832179','#83215b','#83213e'],
 'Dark':['#2f1818','#2f1f18','#2f2618','#2f2d18','#2b2f18','#242f18','#1d2f18','#182f1b','#182f21','#182f28','#182f2f','#18282f','#18212f','#181b2f','#1d182f','#24182f','#2b182f','#2f182d','#2f1826','#2f181f'],
 'Greys':['#f7f7f8','#e9eaec','#dcdde0','#ced0d4','#c1c3c8','#b6b9be','#a9acb2','#9b9fa6','#8d929a','#80858e','#737882','#676b74','#5b5f67','#4f5259','#43464c','#3a3c41','#2e2f33','#222326','#161618','#0a0a0b']
};
const QR_COLORS=['#14201a','#0b7a49','#9b2242','#1f3a93','#7a5c2e'];
/* Text styles: a heading + supporting line, grouped for the Text panel */
const PAIRS={
 Love:[
  [{text:'Happy anniversary',font:'Great Vibes',size:44,color:'#9b2242'},{text:'with all my love',font:'Cormorant Garamond',size:18,italic:true,color:'#6b3a47'}],
  [{text:'I love you',font:'Pinyon Script',size:50,color:'#c2185b'},{text:'MORE EVERY DAY',font:'Josefin Sans',size:13,color:'#9b2242',ls:3}],
  [{text:'Be mine',font:'Lobster',size:52,color:'#d81b60'},{text:'Happy Valentine\'s Day',font:'Onest',size:14,color:'#7a2a44'}],
  [{text:'Forever & always',font:'Sacramento',size:50,color:'#8e2442'},{text:'Ana + Theo',font:'Cormorant Garamond',size:20,italic:true,color:'#6b3a47'}],
  [{text:'Just married',font:'Alex Brush',size:52,color:'#7a5c2e'},{text:'AUGUST 28, 2026',font:'Cinzel',size:14,color:'#5a4520',ls:3}],
  [{text:'You & me',font:'Abril Fatface',size:48,color:'#b23a48'},{text:'since 2019',font:'Caveat',size:26,color:'#6b3a47'}],
  [{text:'Mr & Mrs',font:'Cinzel',size:40,color:'#7a5c2e',ls:4},{text:'Happily ever after',font:'Great Vibes',size:30,color:'#7a5c2e'}],
  [{text:'XOXO',font:'Bebas Neue',size:64,color:'#e91e63',ls:8,lh:1},{text:'hugs and kisses',font:'Dancing Script',size:26,color:'#ad1457'}],
  [{text:'Love you lots',font:'Pacifico',size:40,color:'#e4572e'},{text:'like, a lot',font:'Amatic SC',size:28,color:'#333333',bold:true}],
  [{text:'My favourite person',font:'Playfair Display',size:34,italic:true,color:'#6b3a47'},{text:'IN THE WHOLE WORLD',font:'Josefin Sans',size:13,color:'#9b2242',ls:3}]
 ],
 Birthday:[
  [{text:'HAPPY',font:'Bebas Neue',size:64,color:'#e4572e',ls:4,lh:1},{text:'birthday!',font:'Caveat',size:40,color:'#2b59c3',bold:true}],
  [{text:'Happy Birthday',font:'Pacifico',size:40,color:'#ff6f61'},{text:'Make a wish',font:'Fredoka',size:18,color:'#6a4c93'}],
  [{text:'Party time!',font:'Righteous',size:42,color:'#7b2ff7'},{text:'LET\'S CELEBRATE',font:'Montserrat',size:13,color:'#333333',ls:2}],
  [{text:'Another year of awesome',font:'Permanent Marker',size:32,color:'#1f3a93'},{text:'Cheers to you',font:'Caveat',size:26,color:'#e4572e'}],
  [{text:'Hello 30',font:'Abril Fatface',size:56,color:'#c2185b'},{text:'and fabulous',font:'Dancing Script',size:28,color:'#333333'}],
  [{text:'Birthday wishes',font:'Great Vibes',size:44,color:'#b8860b'},{text:'TO YOU',font:'Cinzel',size:16,color:'#8b6914',ls:6}],
  [{text:'Hip hip hooray!',font:'Fredoka',size:40,color:'#ff9800',bold:true},{text:'It\'s your day',font:'Onest',size:15,color:'#555555'}],
  [{text:'Cake time',font:'Lobster',size:48,color:'#8d5524'},{text:'Eat, laugh, repeat',font:'Amatic SC',size:28,color:'#333333',bold:true}],
  [{text:'LEVEL UP!',font:'Bebas Neue',size:60,color:'#00a6a6',ls:3,lh:1},{text:'One more year of awesome',font:'Montserrat',size:13,color:'#333333'}],
  [{text:'Sweet sixteen',font:'Sacramento',size:52,color:'#ec407a'},{text:'SIXTEEN',font:'Josefin Sans',size:14,color:'#ad1457',ls:6}]
 ],
 Celebrate:[
  [{text:'CONGRATS',font:'Bebas Neue',size:64,color:'#e4572e',ls:3,lh:1},{text:'you did it!',font:'Caveat',size:34,color:'#2b59c3',bold:true}],
  [{text:'Well done!',font:'Pacifico',size:42,color:'#2e7d32'},{text:'We knew you could',font:'Onest',size:14,color:'#3b3b3b'}],
  [{text:'Class of 2026',font:'Cinzel',size:32,color:'#1f2a44',bold:true},{text:'Congratulations, Graduate',font:'Playfair Display',size:20,italic:true,color:'#7a5c2e'}],
  [{text:'Cheers!',font:'Abril Fatface',size:56,color:'#b8860b'},{text:'to new beginnings',font:'Dancing Script',size:26,color:'#333333'}],
  [{text:'You did it',font:'Permanent Marker',size:44,color:'#e4572e'},{text:'SO PROUD',font:'Bebas Neue',size:30,color:'#1f2a44',ls:4}],
  [{text:'Welcome baby',font:'Dancing Script',size:46,color:'#5b3f8c',bold:true},{text:'LITTLE ONE',font:'Josefin Sans',size:14,color:'#7e57c2',ls:4}],
  [{text:'Happy retirement',font:'Playfair Display',size:36,color:'#a23b2a'},{text:'Enjoy the sunny days',font:'Caveat',size:26,color:'#6b3a2e'}],
  [{text:'New home',font:'Righteous',size:44,color:'#00796b'},{text:'sweet new home',font:'Lora',size:20,italic:true,color:'#4e342e'}],
  [{text:'Bravo!',font:'Lobster',size:56,color:'#c62828'},{text:'Encore, encore',font:'Cormorant Garamond',size:20,italic:true,color:'#555555'}],
  [{text:'Engaged!',font:'Alex Brush',size:54,color:'#7a5c2e'},{text:'SHE SAID YES',font:'Montserrat',size:13,color:'#5a4520',ls:4}]
 ],
 Holidays:[
  [{text:'Merry & bright',font:'DM Serif Display',size:44,color:'#8e1b1b'},{text:'Warm wishes from all of us',font:'Onest',size:14,color:'#3b3b3b'}],
  [{text:'Merry Christmas',font:'Great Vibes',size:46,color:'#8e1b1b'},{text:'AND A HAPPY NEW YEAR',font:'Cinzel',size:13,color:'#14532d',ls:3}],
  [{text:'Happy New Year',font:'Abril Fatface',size:40,color:'#b8860b'},{text:'2027',font:'Bebas Neue',size:56,color:'#1f2a44',ls:6,lh:1}],
  [{text:'Happy Diwali',font:'Cinzel',size:38,color:'#d35400',bold:true},{text:'Light, love and laughter',font:'Dancing Script',size:26,color:'#8e44ad'}],
  [{text:'Eid Mubarak',font:'Alex Brush',size:50,color:'#0b6e4f'},{text:'peace and blessings',font:'Lora',size:18,italic:true,color:'#3e5c4a'}],
  [{text:'Happy Holi',font:'Fredoka',size:48,color:'#e91e63',bold:true},{text:'Play with colours',font:'Caveat',size:28,color:'#ff9800',bold:true}],
  [{text:'Season\'s greetings',font:'Playfair Display',size:36,italic:true,color:'#14532d'},{text:'WARM WISHES',font:'Josefin Sans',size:13,color:'#8e1b1b',ls:4}],
  [{text:'Happy Easter',font:'Pacifico',size:40,color:'#7cb342'},{text:'Hop into spring',font:'Amatic SC',size:28,color:'#6a1b9a',bold:true}],
  [{text:'Spooky season',font:'Permanent Marker',size:40,color:'#e65100'},{text:'HAPPY HALLOWEEN',font:'Bebas Neue',size:30,color:'#212121',ls:4}],
  [{text:'Happy Mother\'s Day',font:'Sacramento',size:46,color:'#c2185b'},{text:'with love',font:'Lora',size:18,italic:true,color:'#6b3a47'}]
 ],
 'Thanks & more':[
  [{text:'Thank you',font:'Playfair Display',size:48,italic:true,color:'#0b5d3b'},{text:'FOR BEING YOU',font:'Josefin Sans',size:14,color:'#2e5d47',ls:3}],
  [{text:'Thanks a bunch',font:'Pacifico',size:40,color:'#00897b'},{text:'You\'re the best',font:'Amatic SC',size:28,color:'#333333',bold:true}],
  [{text:'With gratitude',font:'Cormorant Garamond',size:38,italic:true,color:'#4e342e'},{text:'THANK YOU',font:'Cinzel',size:14,color:'#8d6e63',ls:6}],
  [{text:'Get well soon',font:'Caveat',size:48,color:'#1f3a93',bold:true},{text:'Sending hugs',font:'Onest',size:14,color:'#3d5a8c'}],
  [{text:'Thinking of you',font:'Dancing Script',size:44,color:'#2e5d47'},{text:'ALWAYS',font:'Josefin Sans',size:14,color:'#4a6b5a',ls:6}],
  [{text:'With sympathy',font:'Cormorant Garamond',size:36,italic:true,color:'#4f4a3e'},{text:'In loving memory',font:'Lora',size:16,color:'#7a7466'}],
  [{text:'Miss you',font:'Lobster',size:50,color:'#3949ab'},{text:'come back soon',font:'Caveat',size:26,color:'#555555'}],
  [{text:'Hello there',font:'Righteous',size:44,color:'#ff7043'},{text:'Just saying hi',font:'Montserrat',size:14,color:'#555555'}],
  [{text:'Good luck!',font:'Fredoka',size:44,color:'#43a047',bold:true},{text:'You\'ve got this',font:'Permanent Marker',size:22,color:'#1f3a93'}],
  [{text:'BEST FRIENDS',font:'Amatic SC',size:52,color:'#6a1b9a',bold:true,lh:1.1},{text:'forever and ever',font:'Sacramento',size:36,color:'#ec407a'}]
 ]
};

/* ---------- Landscape fitting ---------- */
/* Portrait designs are scaled into the wide page: positions stretch, text shrinks to fit the height,
   round photos, stickers and QR codes stay square around their new centre. */
const tplRaw=i=>(TEMPLATES[i].raw||TEMPLATES[i].make)();
const PX=360,PY=504;
const FIT=W!==PX||H!==PY;
function fitEl(el){
  if(!FIT)return el;
  const sx=W/PX,sy=H/PY,o={...el,x:Math.round(el.x*sx),y:Math.round(el.y*sy),w:Math.round(el.w*sx)};
  if(el.type==='text'){o.size=Math.max(8,Math.round(el.size*sy));return o;}
  o.h=Math.round(el.h*sy);
  if(el.type==='art'&&el.fit!=='fill'){
    const k=Math.min(sx,sy),cx=(el.x+el.w/2)*sx,cy=(el.y+el.h/2)*sy,w=Math.round(el.w*k),h=Math.round(el.h*k);
    return Object.assign(o,{w,h,x:Math.round(cx-w/2),y:Math.round(cy-h/2)});
  }
  if(el.type==='sticker'||el.type==='qr'||(el.type==='photo'&&el.shape==='circle')){
    const side=Math.round(Math.min(el.w*sx,el.h*sy)),cx=(el.x+el.w/2)*sx,cy=(el.y+el.h/2)*sy;
    Object.assign(o,{w:side,h:side,x:Math.round(cx-side/2),y:Math.round(cy-side/2)});
  }
  return o;
}
const fitPages=out=>{const r={};for(const k in out){const v=out[k];r[k]=Array.isArray(v)?v.map(fitEl):{...v,els:v.els.map(fitEl)};}return r;};
/* Text styles, stickers and background colours managed in admin replace the built-in lists */
if(window.QE_LIBRARY)[[PAIRS,'pairs'],[STICKERS,'stickers'],[BG_COLORS,'bgColors']].forEach(([obj,key])=>{const d=window.QE_LIBRARY[key];
  if(!d||Array.isArray(d)||!Object.keys(d).length)return;Object.keys(obj).forEach(k=>delete obj[k]);Object.assign(obj,d);});
/* SVG artwork managed in admin (kind=svg_art), shown under the emojis in the Stickers panel: {group: [{name, src, x, y, w, h}]} */
const ART=(window.QE_LIBRARY&&window.QE_LIBRARY.art&&!Array.isArray(window.QE_LIBRARY.art))?window.QE_LIBRARY.art:{};
/* Layouts managed in admin (templates table, kind=layout) replace the built-in list above */
if(window.QE_LAYOUTS)Object.keys(LAYOUTS).forEach(v=>{const list=window.QE_LAYOUTS[v];if(!Array.isArray(list)||!list.length)return;
  LAYOUTS[v]=list.map(l=>({name:l.name,bg:l.bg||null,make:()=>{const o={};for(const pid in l.pages)o[pid]=(l.pages[pid]||[]).map(e=>({...JSON.parse(JSON.stringify(e)),id:uid()}));return o;}}));});
if(FIT)[TEMPLATES,...Object.values(PAGE_TEMPLATES),...Object.values(LAYOUTS)].forEach(list=>list.forEach(t=>{const m=t.make;t.raw=m;t.make=()=>fitPages(m());}));
/* New elements are placed with portrait coordinates; centre them on a wide page */
const centreX=el=>{if(W!==PX)el.x+=Math.round((W-PX)/2);return el;};

/* ---------- State ---------- */
/* First open (nothing saved yet) starts with a blank card */
const blankPage=()=>({bg:'#ffffff',els:[]});
let state={title:'Untitled card',pages:{front:blankPage(),inL:blankPage(),inR:blankPage(),back:blankPage()}};
try{const raw=localStorage.getItem(KEY);if(raw){const d=JSON.parse(raw);if(d&&d.pages&&d.pages.front&&d.pages.inL&&d.pages.inR&&d.pages.back)state={title:d.title||'Untitled card',pages:d.pages};}}catch(e){}

let view='front',tplTab='card',stTab='emoji',sel=null,activePage='front',editingId=null,zoom=1,zoomMode='fit',panel='templates',showSafe=false,library=[],pendingPhoto=null,addCount=0,pvView='front';
let qrDraft={url:'',color:'#14201a'};
/* Admin mode (/admin/templates/{id}/design): edit a library template and save it back */
const ADMIN_TPL=window.QE_ADMIN_TEMPLATE||null;
if(ADMIN_TPL)state={title:ADMIN_TPL.name,pages:JSON.parse(JSON.stringify(ADMIN_TPL.pages))};
let past=[],future=[],present=JSON.stringify(state.pages);
let titleTouched=false;

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
function scheduleSave(){setStatus('saving');clearTimeout(saveT);saveT=setTimeout(()=>{try{localStorage.setItem(KEY,JSON.stringify({title:state.title,pages:state.pages,clean:baseline!==null&&present===baseline}));setStatus('saved');}catch(e){setStatus('error');}},500);}
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
  else if(el.type==='art'){const img=document.createElement('img');img.src=el.src;img.alt='';img.draggable=false;img.style.objectFit=el.fit==='fill'?'fill':'contain';
    const fl=[el.flipX?'scaleX(-1)':'',el.flipY?'scaleY(-1)':''].join(' ').trim();if(fl)img.style.transform=fl;d.appendChild(img);}
  else if(el.type==='qr'){if(el.src){const img=document.createElement('img');img.src=el.src;img.alt='QR code';img.draggable=false;d.appendChild(img);}
    else{d.classList.add('empty');d.innerHTML=`<div class="ph">${ic('qr',22)}<span>Add QR code</span></div>`;}}
  if(el.rot)d.style.transform=`rotate(${el.rot}deg)`;
  if(el.opacity!=null&&el.opacity<1)d.style.opacity=el.opacity;
  return d;
}
function buildPage(pg,pid,mode){
  const p=document.createElement('div');p.className='page';p.dataset.page=pid;
  const c=document.createElement('div');c.className='content';c.style.background=pg.bg;
  pg.els.forEach(el=>{
    if(mode==='preview'){if((el.type==='photo'||el.type==='qr')&&!el.src)return;if(el.type==='text'&&!el.text.trim())return;}
    c.appendChild(buildEl(el));
  });
  p.appendChild(c);
  if(mode==='edit')p.insertAdjacentHTML('beforeend','<div class="safe"></div><div class="guide-v"></div><div class="guide-h"></div>');
  return p;
}
function mini(arr,s,mode='thumb'){
  const m=document.createElement('div');m.className='mini';m.style.width=W*(STACK?1:arr.length)*s+'px';m.style.height=H*(STACK?arr.length:1)*s+'px';
  const inner=document.createElement('div');inner.className='mini-inner'+(arr.length>1?' spread':'');inner.style.transform=`scale(${s})`;
  arr.forEach(([pg,pid])=>inner.appendChild(buildPage(pg,pid,mode)));m.appendChild(inner);return m;
}
function renderStage(){
  finishEditing();
  const pids=VIEWS[view];sheet.innerHTML='';
  sheet.classList.toggle('spread',pids.length>1);sheet.classList.toggle('show-safe',showSafe);
  pids.forEach(pid=>sheet.appendChild(buildPage(state.pages[pid],pid,'edit')));
  caps.innerHTML=STACK?`<span>${pids.map(p=>PAGE_LABEL[p]).join(' / ')}</span>`:pids.map(p=>`<span>${PAGE_LABEL[p]}</span>`).join('');
  $('#prevBtn').hidden=view===ORDER[0];$('#nextPageBtn').hidden=view===LAST;
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
function placeIndicator(){const ind=document.getElementById('stripInd');const a=strip.querySelector('.thumb.active');if(!ind||!a)return;ind.style.width=a.offsetWidth+'px';ind.style.transform=`translateX(${a.offsetParent===strip?a.offsetLeft:a.offsetParent.offsetLeft+a.offsetLeft}px)`;if(!ind.classList.contains('ready'))requestAnimationFrame(()=>requestAnimationFrame(()=>ind.classList.add('ready')));}
window.addEventListener('resize',placeIndicator);
function setView(v){if(v===view)return;const from=view;view=v;sel=null;zoomMode='fit';if(tplTab!=='card')tplTab=v;renderStage();renderStrip();renderCtx();if(['templates','layouts','background','text','qr'].includes(panel))renderPanel();animateFlip(from,v);}
/* Card open / close animation: a ghost of the outgoing page turns on the spine while the incoming page lands */
function animateFlip(from,to){
  if(!sheet.animate||window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;
  const dur=760,easing='cubic-bezier(.45,.05,.25,1)',PS='perspective(2200px) ';
  const pg=pid=>sheet.querySelector(`.page[data-page="${pid}"]`);
  const anims=[];let g=null;
  const ghost=(pid,left,top=0)=>{g=buildPage(state.pages[pid],pid,'thumb');g.classList.add('ghost');g.style.left=left+'px';g.style.top=top+'px';sheet.appendChild(g);return g;};
  const turn=(node,origin,a,b)=>{if(!node)return;node.style.transformOrigin=origin;node.style.backfaceVisibility='hidden';anims.push(node.animate([{transform:PS+`rotateY(${a}deg)`},{transform:PS+`rotateY(${b}deg)`}],{duration:dur,easing}));};
  const pair=from+'>'+to;
  const tilt=(node,origin,a,b)=>{if(!node)return;node.style.transformOrigin=origin;node.style.backfaceVisibility='hidden';anims.push(node.animate([{transform:PS+`rotateX(${a}deg)`},{transform:PS+`rotateX(${b}deg)`}],{duration:dur,easing}));};
  if(STACK&&pair==='front>inside'){tilt(ghost('front',0,0),'center bottom',0,-180);tilt(pg('inL'),'center bottom',180,0);}
  else if(STACK&&pair==='inside>front'){tilt(ghost('inL',0,0),'center bottom',0,180);tilt(pg('front'),'center bottom',-180,0);}
  else if(STACK&&pair==='inside>back'){tilt(ghost('inR',0,H),'center top',0,180);tilt(pg('back'),'center bottom',-180,0);}
  else if(STACK&&pair==='back>inside'){tilt(ghost('back',0,0),'center bottom',0,-180);tilt(pg('inR'),'center top',180,0);}
  else if(pair==='front>inside'){turn(ghost('front',W),'left center',0,-180);turn(pg('inL'),'right center',180,0);}
  else if(pair==='inside>front'){turn(ghost('inL',-W),'right center',0,180);turn(pg('front'),'left center',-180,0);}
  else if(pair==='inside>back'){turn(ghost('inR',W),'left center',0,-180);turn(pg('back'),'right center',180,0);}
  else if(pair==='back>inside'){turn(ghost('back',0),'right center',0,180);turn(pg('inR'),'left center',-180,0);}
  else{const n=pg(VIEWS[to][0]);if(n){n.style.transformOrigin='center';anims.push(n.animate([{transform:PS+'rotateY(-90deg)',opacity:0},{transform:PS+'rotateY(0deg)',opacity:1}],{duration:dur*.7,easing:'cubic-bezier(.2,.8,.3,1)'}));}}
  sheet.classList.add('flipping');
  Promise.all(anims.map(a=>a.finished.catch(()=>{}))).then(()=>{if(g)g.remove();sheet.classList.remove('flipping');sheet.querySelectorAll('.page').forEach(n=>{n.style.transformOrigin='';n.style.backfaceVisibility='';});});
}

/* ---------- Zoom ---------- */
function contentW(){return W*(STACK?1:VIEWS[view].length);}
function contentH(){return H*(STACK?VIEWS[view].length:1);}
function fitZoom(){const cs=getComputedStyle(stage);const pw=stage.clientWidth-parseFloat(cs.paddingLeft)-parseFloat(cs.paddingRight);const ph=stage.clientHeight-parseFloat(cs.paddingTop)-parseFloat(cs.paddingBottom)-30;return clamp(Math.min(pw/contentW(),ph/H),.2,1.6);}
function applyZoom(){if(zoomMode==='fit')zoom=fitZoom();wrap.style.width=contentW()*zoom+'px';wrap.style.height=contentH()*zoom+'px';sheet.style.transform=`scale(${zoom})`;sheet.style.setProperty('--inv',(1/zoom).toFixed(4));caps.style.width=contentW()*zoom+'px';$('#zoomPct').textContent=Math.round(zoom*100)+'%';}
function zoomBy(f){zoomMode='manual';zoom=clamp(zoom*f,.25,3);applyZoom();}

/* ---------- Selection ---------- */
function drawSelection(){
  sheet.querySelectorAll('.selbox').forEach(n=>n.remove());
  if(!sel)return;const node=nodeOf(sel.id);const f=findEl(sel.id);if(!node||!f){sel=null;return;}
  const box=document.createElement('div');box.className='selbox';
  const dirs=f.el.type==='text'?['w','e','rot']:['nw','ne','sw','se','rot'];
  box.innerHTML=dirs.map(d=>`<div class="handle h-${d}" data-dir="${d}" ${d==='rot'?'title="Drag to rotate (Shift snaps to 15°)"':''}></div>`).join('');
  node.closest('.page').appendChild(box);positionSelbox();if(editingId)box.hidden=true;
}
function positionSelbox(){const box=sheet.querySelector('.selbox');if(!box||!sel)return;const node=nodeOf(sel.id);if(!node)return;box.style.left=node.offsetLeft+'px';box.style.top=node.offsetTop+'px';box.style.width=node.offsetWidth+'px';box.style.height=node.offsetHeight+'px';box.style.transform=node.style.transform||'';}
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
  if(dir==='rot'){startRotate(e,h,el,node);return;}
  const o={x:el.x,y:el.y,w:el.w,h:el.type==='text'?node.offsetHeight:el.h};const ratio=o.w/o.h;const keep=el.type==='sticker'||el.type==='qr'||(el.type==='art'&&el.fit!=='fill');
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

function startRotate(e,h,el,node){
  const r=node.getBoundingClientRect(),cx=r.left+r.width/2,cy=r.top+r.height/2;
  try{h.setPointerCapture(e.pointerId);}catch(_){}
  const move=ev=>{
    let a=Math.atan2(ev.clientY-cy,ev.clientX-cx)*180/Math.PI+90;
    if(ev.shiftKey)a=Math.round(a/15)*15;else{const n=Math.round(a/90)*90;if(Math.abs(a-n)<4)a=n;}
    a=((Math.round(a)%360)+540)%360-180;el.rot=a||0;
    node.style.transform=el.rot?`rotate(${el.rot}deg)`:'';positionSelbox();
  };
  const up=()=>{h.removeEventListener('pointermove',move);h.removeEventListener('pointerup',up);h.removeEventListener('pointercancel',up);if(!el.rot)delete el.rot;commit();renderCtx();};
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
  const rotBtn=(f&&f.el.rot&&f.el.type!=='art')?`<button class="tb" data-act="unrotate" title="Reset rotation">${f.el.rot}° ✕</button>`:'';
  const common=`${rotBtn}<span class="sep"></span><button class="tb" data-act="forward" title="Bring forward" aria-label="Bring forward">${ic('up',18)}</button><button class="tb" data-act="backward" title="Send backward" aria-label="Send backward">${ic('down',18)}</button><button class="tb" data-act="dup" title="Duplicate (Ctrl+D)" aria-label="Duplicate">${ic('copy',18)}</button><button class="tb danger" data-act="del" title="Delete" aria-label="Delete">${ic('trash',18)}</button>`;
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
  }else if(el.type==='art'){
    ctx.innerHTML=`<button class="tb" data-act="replace">${ic('refresh',18)}Replace image</button><span class="sep"></span>
    <button class="tb" data-act="flipx" aria-pressed="${!!el.flipX}" title="Flip horizontally">Flip</button>
    <label class="opac" title="Transparency">Opacity <input type="range" min="15" max="100" step="5" data-k="opacity" value="${Math.round((el.opacity??1)*100)}" aria-label="Opacity"></label>
    ${el.rot?`<button class="tb" data-act="unrotate" title="Reset rotation">${el.rot}° ✕</button>`:''}${common}`;
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
  if(k==='opacity'){v=clamp(parseInt(v,10)/100,.15,1);const f=findEl(sel.id);if(f){f.el.opacity=v;const n=nodeOf(sel.id);if(n)n.style.opacity=v;}return;}
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
    case 'flipx':patch(el.id,{flipX:!el.flipX});renderCtx();break;
    case 'unrotate':patch(el.id,{rot:0});renderCtx();break;
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
    const tabs=[['card','Full card'],...ORDER.map(v=>[v,VIEW_LABEL[v]])];
    const hint=tplTab==='card'?'Start from a finished, fully editable design. Every image and line of text can be changed.':`Designs for the ${VIEW_LABEL[tplTab].toLowerCase()}. Only the ${tplTab==='inside'?'inside pages change':VIEW_LABEL[tplTab].toLowerCase()+' changes'}, and you can undo it.`;
    inner.innerHTML=head('Templates')+`<div class="seg tpl-tabs" role="group" aria-label="Template type">${tabs.map(([id,l])=>`<button class="tb" data-tpl="${id}" aria-pressed="${id===tplTab}">${l}</button>`).join('')}</div><p class="hint">${hint}</p><div class="tiles ${tplTab==='inside'?'one':''}"></div>`;
    inner.querySelectorAll('[data-tpl]').forEach(b=>b.onclick=()=>{tplTab=b.dataset.tpl;renderPanel();});
    const g=inner.querySelector('.tiles');
    if(tplTab==='card')renderLibrary(inner,g);
    else PAGE_TEMPLATES[tplTab].forEach(t=>{const made=t.make();const b=document.createElement('button');b.className='tile';b.appendChild(mini(VIEWS[tplTab].map(pid=>[made[pid],pid]),tplTab==='inside'?.33:.29));b.insertAdjacentHTML('beforeend',`<span>${esc(t.name)}</span>`);b.onclick=()=>applyPageTemplate(t,tplTab);g.appendChild(b);});
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
      ${Object.keys(PAIRS).map((cat,i)=>`<div class="sec">${esc(cat)}</div><div class="tiles" data-pairs="${i}"></div>`).join('')}`;
    Object.values(PAIRS).forEach((list,i)=>{const g=inner.querySelector(`[data-pairs="${i}"]`);
      list.forEach(pair=>{const b=document.createElement('button');b.className='tile';b.setAttribute('aria-label','Add text style: '+pair.map(p=>p.text).join(', '));
        b.innerHTML=`<div class="pair">${pair.map(p=>`<span style="font-family:${fontCss(p.font)};font-size:${Math.round(p.size*.42)}px;color:${p.color};font-style:${p.italic?'italic':'normal'};font-weight:${p.bold?700:400};letter-spacing:${(p.ls||0)*.4}px;line-height:1.1">${esc(p.text)}</span>`).join('')}</div>`;
        b.onclick=()=>addPair(pair);g.appendChild(b);});});
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
    inner.querySelector('[data-add="frame"]').onclick=()=>{const o=stagger();addElement(centreX(P({x:70+o,y:120+o,w:220,h:220})));};
  }
  else if(panel==='stickers'){
    const swap=f&&f.el.type==='sticker';const hasArt=Object.keys(ART).length>0;if(!hasArt||swap)stTab='emoji';
    /* Two tabs: Emojis (admin "Emojis") and SVGs (admin "SVGs") */
    const tabs=hasArt?`<div class="seg tpl-tabs" role="group" aria-label="Sticker type">${[['emoji','Emojis'],['svg','SVGs']].map(([id,l])=>`<button class="tb" data-sttab="${id}" aria-pressed="${id===stTab}">${l}</button>`).join('')}</div>`:'';
    const hint=stTab==='svg'?'Click an illustration to add it. Drag a corner to resize.':(swap?'Pick a sticker to swap the selected one.':'Click a sticker to add it. Drag a corner to resize.');
    inner.innerHTML=head('Stickers')+tabs+`<p class="hint">${hint}</p>`+(stTab==='svg'
      ?Object.entries(ART).map(([g,list])=>`<div class="sec">${esc(g)}</div><div class="arts">${list.map((a,i)=>`<button data-art="${esc(g)}" data-ai="${i}" aria-label="${esc(a.name)}" title="${esc(a.name)}"><img src="${esc(a.src)}" alt="" loading="lazy"></button>`).join('')}</div>`).join('')
      :Object.entries(STICKERS).map(([cat,list])=>`<div class="sec">${cat}</div><div class="stickers">${list.map(c=>`<button data-st="${c}" aria-label="Sticker ${c}">${c}</button>`).join('')}</div>`).join(''));
    inner.querySelectorAll('[data-sttab]').forEach(b=>b.onclick=()=>{stTab=b.dataset.sttab;renderPanel();});
    inner.querySelectorAll('[data-art]').forEach(b=>b.onclick=()=>{const a=ART[b.dataset.art][b.dataset.ai];const o=stagger();
      addElement(fitEl({id:uid(),type:'art',src:a.src,fit:'contain',x:a.x+o,y:a.y+o,w:a.w,h:a.h}));});
    inner.querySelectorAll('[data-st]').forEach(b=>b.onclick=()=>{const c=b.dataset.st;const cur=sel&&findEl(sel.id);
      if(cur&&cur.el.type==='sticker'){patch(cur.el.id,{char:c});}else{const o=stagger();addElement(centreX(S({char:c,x:140+o,y:212+o})));}});
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
        else addElement(centreX(Q({url:u,color:cur.color,src,y:150+stagger()})));
        qrDraft.url='';toast('QR code added');}
    };
    setTimeout(preview,0);
  }
  else if(panel==='background'){
    const pid=targetPage();const bg=state.pages[pid].bg;
    inner.innerHTML=head('Background')+`<p class="hint">Paper colour for the ${PAGE_LABEL[pid].toLowerCase()}.</p>
      <label class="custom"><input type="color" id="bgCustom" value="${bg}"> Custom colour</label>
      <button class="btn wide" id="bgAll">Use on every page</button>
      ${Object.entries(BG_COLORS).map(([theme,list])=>`<div class="sec">${esc(theme)}</div><div class="swatches">${list.map(c=>`<button class="sw" data-bg="${c}" style="background:${c}" aria-label="${esc(theme)} colour ${c}" title="${c}" aria-pressed="${c===bg}"></button>`).join('')}</div>`).join('')}`;
    const setBg=(c,doCommit)=>{state.pages[pid].bg=c;const n=sheet.querySelector(`.page[data-page="${pid}"] .content`);if(n)n.style.background=c;if(doCommit){commit();}};
    inner.querySelectorAll('[data-bg]').forEach(b=>b.onclick=()=>{setBg(b.dataset.bg,true);inner.querySelectorAll('[data-bg]').forEach(x=>x.setAttribute('aria-pressed',x===b));inner.querySelector('#bgCustom').value=b.dataset.bg;});
    const cc=inner.querySelector('#bgCustom');cc.oninput=()=>setBg(cc.value,false);cc.onchange=()=>commit();
    inner.querySelector('#bgAll').onclick=()=>{const c=state.pages[pid].bg;Object.values(state.pages).forEach(p=>p.bg=c);renderStage();commit();toast('Background used on every page','Undo',undo);};
  }
  panelEl.innerHTML='';panelEl.appendChild(inner);
}

/* ---------- Template library (public/assets/templates/templates.json) ---------- */
const LIB_URL=window.QE_TEMPLATES_URL||'assets/templates/templates.json';
const RECENT_KEY='qrinto-card-recent-templates';
let LIB=null,libState='idle',libCat='all',libQuery='',libScroll=0;
let baseline=null; /* snapshot of the last template/empty state; anything else counts as the user's own changes */
const absUrl=(base,src)=>/^(https?:|data:|\/)/.test(src)?src:base+src;
function loadLibrary(){
  if(libState!=='idle')return;libState='loading';
  fetch(LIB_URL,{cache:'no-cache'}).then(r=>{if(!r.ok)throw new Error(r.status);return r.json();}).then(d=>{
    const base=LIB_URL.replace(/[^/]*$/,'');
    d.templates.forEach(t=>Object.values(t.pages).forEach(p=>p.els.forEach(e=>{if(e.src)e.src=absUrl(base,e.src);})));
    d.catName=Object.fromEntries(d.categories.map(c=>[c.id,c.name]));
    LIB=d;libState='ready';if(panel==='templates')renderPanel();
  }).catch(()=>{libState='error';if(panel==='templates')renderPanel();});
}
const libPages=t=>{const c=JSON.parse(JSON.stringify(t.pages));Object.values(c).forEach(p=>p.els.forEach(e=>e.id=uid()));return FIT?fitPages(c):c;};
const libPreview=t=>t._pv||(t._pv=libPages(t).front);
function getRecent(){try{return JSON.parse(localStorage.getItem(RECENT_KEY))||[];}catch(e){return[];}}
function pushRecent(id){try{const r=[id,...getRecent().filter(x=>x!==id)].slice(0,8);localStorage.setItem(RECENT_KEY,JSON.stringify(r));}catch(e){}}
function libMatches(t,q){if(!q)return true;const hay=[t.name,LIB.catName[t.category],t.style,...(t.tags||[])].join(' ').toLowerCase();return q.split(/\s+/).every(w=>hay.includes(w));}
function renderLibrary(inner,grid){
  const hintEl=inner.querySelector('.hint');
  if(libState!=='ready'){loadLibrary();grid.outerHTML=libState==='error'?'<p class="hint">Templates could not be loaded. <button class="link" data-libretry>Try again</button></p>':'<div class="lib-loading" aria-busy="true">Loading templates…</div>';
    const rt=inner.querySelector('[data-libretry]');if(rt)rt.onclick=()=>{libState='idle';renderPanel();};return;}
  const recent=getRecent().map(id=>LIB.templates.find(t=>t.id===id)).filter(Boolean);
  const chips=[['all','All'],['featured','Featured'],['popular','Popular'],...(recent.length?[['recent','Recently used']]:[]),...LIB.categories.map(c=>[c.id,c.name])];
  if(libCat==='recent'&&!recent.length)libCat='all';
  const bar=document.createElement('div');bar.className='lib-bar';
  bar.innerHTML=`<label class="lib-search"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><input type="search" placeholder="Search ${LIB.templates.length} templates" value="${esc(libQuery)}" aria-label="Search templates"></label>
    <div class="lib-chips" role="group" aria-label="Template category">${chips.map(([id,l])=>`<button class="chip" data-cat="${id}" aria-pressed="${id===libCat}">${esc(l)}</button>`).join('')}</div>`;
  hintEl.after(bar);
  const q=libQuery.trim().toLowerCase();
  let list=LIB.templates.filter(t=>libMatches(t,q));
  const tile=t=>{const b=document.createElement('button');b.className='tile tpl-tile';b.title=`${t.name} (${LIB.catName[t.category]}, ${t.style})`;
    const m=mini([[libPreview(t),'front']],LANDSCAPE?.205:.29);m.querySelectorAll('img').forEach(i=>i.loading='lazy');b.appendChild(m);
    b.insertAdjacentHTML('beforeend',`<span class="tpl-name">${esc(t.name)}</span><small class="tpl-meta">${esc(LIB.catName[t.category])} · ${esc(t.style)}</small>${t.featured?'<i class="tpl-badge">Featured</i>':''}`);
    b.onclick=()=>applyLibraryTemplate(t);return b;};
  const section=(title,items)=>{if(!items.length)return;const h=document.createElement('div');h.className='sec';h.textContent=title;const g=document.createElement('div');g.className='tiles';items.forEach(t=>g.appendChild(tile(t)));grid.before(h);grid.before(g);};
  if(libCat==='all'&&!q){
    if(recent.length)section('Recently used',recent.slice(0,4));
    section('Featured',LIB.templates.filter(t=>t.featured).slice(0,6));
    LIB.categories.forEach(c=>section(c.name,list.filter(t=>t.category===c.id)));
  }else{
    if(libCat==='featured')list=list.filter(t=>t.featured);
    else if(libCat==='popular')list=[...list].sort((a,b)=>(b.popular||0)-(a.popular||0)).slice(0,20);
    else if(libCat==='recent')list=recent.filter(t=>libMatches(t,q));
    else if(libCat!=='all')list=list.filter(t=>t.category===libCat);
    const c=document.createElement('p');c.className='hint small';c.textContent=list.length?`${list.length} template${list.length>1?'s':''}`:'No templates match. Try another word or category.';grid.before(c);
    list.forEach(t=>grid.appendChild(tile(t)));
  }
  if(!grid.children.length)grid.remove();
  bar.querySelectorAll('[data-cat]').forEach(b=>b.onclick=()=>{libCat=b.dataset.cat;libScroll=0;renderPanel();});
  const si=bar.querySelector('input');let st;
  si.oninput=()=>{clearTimeout(st);st=setTimeout(()=>{libQuery=si.value;libScroll=0;renderPanel();const n=panelEl.querySelector('.lib-search input');if(n){n.focus();n.setSelectionRange(n.value.length,n.value.length);}},180);};
  requestAnimationFrame(()=>{const pi=panelEl.querySelector('.panel-inner');if(pi){pi.scrollTop=libScroll;pi.onscroll=()=>{libScroll=pi.scrollTop;};}});
}
function hasUserChanges(){finishEditing();return baseline===null||present!==baseline;}
function confirmReplace(name){
  return new Promise(res=>{
    const m=$('#confirmModal');if(!m)return res(window.confirm(`Replace your current design with "${name}"?`));
    m.querySelector('#cfMsg').textContent=`Your current design will be replaced with “${name}”. You can undo this afterwards.`;m.hidden=false;
    const ok=m.querySelector('#cfOk'),done=v=>{m.hidden=true;ok.onclick=null;m.onclick=null;document.removeEventListener('keydown',key,true);res(v);};
    const key=e=>{if(e.key==='Escape'){e.stopPropagation();e.preventDefault();done(false);}};
    ok.onclick=()=>done(true);m.onclick=e=>{if(e.target===m||e.target.closest('[data-cfcancel]'))done(false);};
    document.addEventListener('keydown',key,true);ok.focus();
  });
}
async function applyLibraryTemplate(t){
  if(hasUserChanges()&&!(await confirmReplace(t.name)))return;
  finishEditing();const from=view;state.pages=libPages(t);sel=null;view=ORDER[0];zoomMode='fit';
  if(!titleTouched){state.title=t.name;titleIn.value=state.title;document.title=state.title+' | qrinto';}
  renderStage();renderStrip();renderCtx();commit();baseline=present;pushRecent(t.id);
  if(panel==='templates')renderPanel();if(from!==view)animateFlip(from,view);
  toast(`${t.name} applied. Click any text or image to edit it.`,'Undo',undo);
}

function applyTemplate(t){finishEditing();state.pages=t.make();sel=null;view='front';zoomMode='fit';renderStage();renderStrip();renderCtx();commit();toast(`${t.name} template applied`,'Undo',undo);}
function applyPageTemplate(t,v){
  finishEditing();const made=t.make();VIEWS[v].forEach(pid=>{state.pages[pid]=made[pid];});
  const from=view;view=v;sel=null;zoomMode='fit';renderStage();renderStrip();renderCtx();commit();
  if(from!==v)animateFlip(from,v);
  toast(`${t.name} used on the ${VIEW_LABEL[v].toLowerCase()}`,'Undo',undo);
}
/* Generic placeholder texts take the customer's font and colour; text an admin styled keeps its own style */
const LAYOUT_PLACEHOLDERS=new Set(['Add a heading','Add a short line','Write your message here','Add a caption','And a little more here','And another','Your name','Scan me']);
function applyLayout(ly){
  finishEditing();const pids=VIEWS[view];const texts=[],photos=[],qrs=[];
  pids.forEach(pid=>[...state.pages[pid].els].sort((a,b)=>a.y-b.y).forEach(el=>{if(el.type==='text'&&el.text.trim())texts.push(el);if(el.type==='photo'&&el.src)photos.push(el);if(el.type==='qr'&&el.src)qrs.push(el);}));
  const made=ly.make();
  pids.forEach(pid=>{const els=made[pid]||[];els.forEach(el=>{
    if(el.type==='text'&&texts.length){const o=texts.shift();if(!LAYOUT_PLACEHOLDERS.has(el.text))el.text=o.text;else Object.assign(el,{text:o.text,font:o.font,color:o.color,bold:o.bold,italic:o.italic,ls:o.ls});}
    if(el.type==='photo'&&photos.length){const o=photos.shift();el.src=o.src;el.fit=o.fit;}
    if(el.type==='qr'&&qrs.length){const o=qrs.shift();Object.assign(el,{src:o.src,url:o.url,color:o.color});}
  });state.pages[pid].els=els;if(ly.bg&&ly.bg[pid])state.pages[pid].bg=ly.bg[pid];});
  sel=null;renderStage();renderCtx();commit();toast('Layout applied','Undo',undo);
}
function addText(kind){
  const o=stagger();let el;
  if(kind==='heading')el=T({text:'Your heading',font:'Playfair Display',size:34,bold:true,y:190+o});
  else if(kind==='sub')el=T({x:40,w:280,text:'Add a subheading',font:'Cormorant Garamond',size:22,italic:true,y:200+o});
  else el=T({x:40,w:280,text:'Write your message here',font:'Onest',size:16,lh:1.5,y:210+o});
  addElement(centreX(el));startEdit(el.id);
}
function addPair(pair){
  const pid=targetPage();let y=170+stagger();const ids=[];
  const base=y,placed=pair.every(p=>typeof p.y==='number');
  pair.forEach(p=>{const el=centreX(T({...p,y:placed?base+p.y:y}));state.pages[pid].els.push(el);ids.push(el.id);y+=Math.round(p.size*(p.lh||1.35))+14;});
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
    if(el.type==='photo'||el.type==='art')return;
    const h=measureH(el),m=12;
    if(el.x<m||el.y<m||el.x+el.w>W-m||el.y+h>H-m)item(`${el.type==='text'?'Text':el.type==='qr'?'The QR code':'A sticker'} on the ${where} sits close to the edge and could be trimmed.`);
  })));
  return out;
}
function openReview(){finishEditing();modal.hidden=false;rvStatus('');pvView=view;renderReview();$('#dlgClose').focus();}
function closeReview(){modal.hidden=true;}
function renderReview(){
  $('#pvTabs').hidden=ORDER.length<2;$('#pvTabs').querySelectorAll('[data-pv]').forEach(b=>{b.hidden=!ORDER.includes(b.dataset.pv);b.setAttribute('aria-pressed',b.dataset.pv===pvView);});
  const area=$('#pvStage');area.innerHTML='';const pids=VIEWS[pvView];
  const aw=Math.max(220,area.clientWidth||520)-8,ah=Math.min(440,window.innerHeight*.5);
  const s=Math.min(ah/(H*(STACK?pids.length:1)),aw/(W*(STACK?1:pids.length)),1);
  area.appendChild(mini(pids.map(pid=>[state.pages[pid],pid]),s,'preview'));
  const issues=runChecks();const list=$('#checkList');
  list.innerHTML=issues.length?issues.map((it,i)=>`<div class="check"><span class="ic warn">${ic('alert',18)}</span><div><p>${esc(it.msg)}</p><button class="link" data-fix="${i}">Show me</button></div></div>`).join('')
    :`<div class="check"><span class="ic ok">${ic('check',18)}</span><p>Everything looks ready to print.</p></div>`;
  list.querySelectorAll('[data-fix]').forEach(b=>b.onclick=()=>{const it=issues[+b.dataset.fix];closeReview();setView(it.v);if(findEl(it.id))select(it.pid,it.id);});
}
$('#pvTabs').addEventListener('click',e=>{const b=e.target.closest('[data-pv]');if(b){pvView=b.dataset.pv;renderReview();}});
modal.addEventListener('click',e=>{if(e.target===modal||e.target.closest('[data-close]'))closeReview();});
/* ---------- Review actions: Print on 931BL / Pickup from store / Export as PDF / Save as product template ---------- */
document.querySelectorAll('#rvActions [data-ic]').forEach(e=>e.innerHTML=ic(e.dataset.ic,18));
document.querySelectorAll('#rvActions [data-act]').forEach(b=>b.onclick=()=>reviewAction(b.dataset.act));
const CHECKOUT=window.QE_CHECKOUT||{};
/* Card size in inches and the page ids in print order */
const cardInches=()=>CARD_SIZE?{w:CARD_SIZE.w,h:CARD_SIZE.h}:(LANDSCAPE?{w:7,h:5}:{w:5,h:7});
const printPids=()=>({single:['front'],double:['front','back'],folded:['front','inL','inR','back']}[CARD_TYPE]);
function rvStatus(msg,err){const el=$('#rvStatus');el.textContent=msg||'';el.classList.toggle('err',!!err);}
/* Render one page to a JPEG data URL at ~300 dpi (html2canvas on an off-screen copy of the page) */
async function renderPageImage(pid){
  const host=document.createElement('div');host.style.cssText='position:fixed;left:-20000px;top:0;pointer-events:none';
  const pg=buildPage(state.pages[pid],pid,'preview');pg.style.width=W+'px';pg.style.height=H+'px';host.appendChild(pg);document.body.appendChild(host);
  try{
    await Promise.all([...host.querySelectorAll('img')].map(i=>i.complete?0:new Promise(r=>{i.onload=i.onerror=r;})));
    if(document.fonts&&document.fonts.ready)await document.fonts.ready;
    const scale=Math.min(5,Math.max(2,cardInches().w*300/W));
    const c=await html2canvas(pg,{scale,useCORS:true,backgroundColor:state.pages[pid].bg||'#ffffff',logging:false});
    return c.toDataURL('image/jpeg',0.92);
  }finally{host.remove();}
}
async function postJson(url,body){
  const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':window.QE_CSRF},body:JSON.stringify(body)});
  const d=await r.json().catch(()=>({}));if(!r.ok){const e=new Error(d.message||('Request failed ('+r.status+')'));e.status=r.status;throw e;}return d;
}
function submitForm(url,fields){
  const f=document.createElement('form');f.method='POST';f.action=url;f.hidden=true;
  Object.entries({_token:window.QE_CSRF,...fields}).forEach(([k,v])=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;f.appendChild(i);});
  document.body.appendChild(f);f.submit();
}
let rvBusy=false;
async function reviewAction(act){
  if(rvBusy)return;finishEditing();
  if(typeof html2canvas!=='function'){rvStatus('The page renderer did not load. Check your connection and try again.',true);return;}
  rvBusy=true;const btns=[...document.querySelectorAll('#rvActions button')];btns.forEach(b=>b.disabled=true);
  const size=cardInches(),orientation=size.w>size.h?'landscape':'portrait',pids=printPids();
  try{
    rvStatus('Preparing your card pages…');
    const images={};for(const pid of pids)images[pid]=await renderPageImage(pid);
    if(act==='product'){
      /* Admin only: images become the product images on admin/products/create */
      const slots={single:{front:'frame_image'},double:{front:'frame_image',back:'sample_image'},folded:{front:'frame_image',inL:'sample_image',inR:'background_image',back:'overlay_image'}}[CARD_TYPE];
      const bySlot={};Object.entries(slots).forEach(([pid,slot])=>bySlot[slot]=images[pid]);
      rvStatus('Saving the design as a product…');
      try{const d=await postJson(CHECKOUT.productDraft,{type:CARD_TYPE,orientation,name:state.title,images:bySlot});location.href=d.redirect;}
      catch(e){if(e.status===401||e.status===403||e.status===419){rvStatus('Please sign in with an admin account to save products.',true);setTimeout(()=>{location.href=CHECKOUT.login;},1400);return;}throw e;}
      return;
    }
    /* Print / PDF / Pickup: same hand-off as the local-print customizer (composite uploads + product form) */
    const prep=await postJson(CHECKOUT.prepare,{type:CARD_TYPE,width:size.w,height:size.h,orientation});
    rvStatus('Uploading pages…');const ids={};
    for(const [pid,slot] of Object.entries(prep.slots)){const d=await postJson(CHECKOUT.upload,{image_data:images[pid],canvas_key:slot});if(!d.success)throw new Error(d.error||'Upload failed');ids[slot]=d.upload_id;}
    const common={product_id:prep.product_id,upload_ids:JSON.stringify(ids),size_width:size.w,size_height:size.h,size_unit:'in',orientation};
    rvStatus(act==='pickup'?'Opening checkout…':'Building the print PDF…');
    if(act==='pickup')submitForm(CHECKOUT.cart,{...common,quantity:1});
    else submitForm(CHECKOUT.preview,act==='pdf'?{...common,auto_download:1}:common);
  }catch(e){rvStatus(e.message||'Something went wrong. Please try again.',true);rvBusy=false;btns.forEach(b=>b.disabled=false);}
}
$('#nextBtn').onclick=ADMIN_TPL?saveAdminTemplate:openReview;
function saveAdminTemplate(){finishEditing();const btn=$('#nextBtn');btn.disabled=true;setStatus('saving');
  fetch(ADMIN_TPL.saveUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':window.QE_CSRF},body:JSON.stringify({name:state.title,pages:state.pages})})
    .then(r=>{if(!r.ok)throw new Error(r.status);return r.json();})
    .then(()=>{setStatus('saved');toast('Template saved');})
    .catch(e=>{setStatus('error');toast('Could not save template ('+e.message+')');})
    .finally(()=>{btn.disabled=false;});}

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
    if(e.key==='ArrowRight'){const i=ORDER.indexOf(view);if(i<ORDER.length-1)setView(ORDER[i+1]);}
    if(e.key==='ArrowLeft'){const i=ORDER.indexOf(view);if(i>0)setView(ORDER[i-1]);}
  }
});

/* ---------- Wire up chrome ---------- */
$('#undoBtn').innerHTML=ic('undo');$('#redoBtn').innerHTML=ic('redo');$('#pencil').innerHTML=ic('pencil',15);
$('#prevBtn').innerHTML=ic('chevL');$('#nextPageBtn').innerHTML=ic('chevR');$('#dlgClose').innerHTML=ic('x');
$('#zoomIn').innerHTML=ic('plus',18);$('#zoomOut').innerHTML=ic('minus',18);$('#safeBtn').innerHTML=ic('margins',18);
$('#undoBtn').onclick=undo;$('#redoBtn').onclick=redo;
$('#prevBtn').onclick=()=>{const i=ORDER.indexOf(view);if(i>0)setView(ORDER[i-1]);};
$('#nextPageBtn').onclick=()=>{const i=ORDER.indexOf(view);if(i<ORDER.length-1)setView(ORDER[i+1]);};
$('#zoomIn').onclick=()=>zoomBy(1.15);$('#zoomOut').onclick=()=>zoomBy(1/1.15);
$('#zoomPct').onclick=()=>{zoomMode='fit';applyZoom();};
$('#safeBtn').onclick=()=>{showSafe=!showSafe;$('#safeBtn').setAttribute('aria-pressed',showSafe);sheet.classList.toggle('show-safe',showSafe);if(showSafe)toast('Keep important things inside the dashed line');};
const titleIn=$('#title');titleIn.value=state.title;document.title=state.title+' | qrinto';
titleIn.oninput=()=>{titleTouched=true;state.title=titleIn.value||'Untitled card';document.title=state.title+' | qrinto';scheduleSave();};
titleIn.onkeydown=e=>{if(e.key==='Enter')titleIn.blur();};
if('ResizeObserver' in window)new ResizeObserver(()=>{if(zoomMode==='fit')applyZoom();}).observe(stage);
else window.addEventListener('resize',()=>{if(zoomMode==='fit')applyZoom();});
if(window.matchMedia('(max-width:760px)').matches){panelEl.hidden=true;panel=null;}

baseline=(()=>{try{const raw=localStorage.getItem(KEY);return !raw||JSON.parse(raw).clean?present:null;}catch(e){return present;}})();
renderRail();renderPanel();renderStage();renderStrip();renderCtx();updateUndo();setStatus('saved');
if(document.fonts&&document.fonts.ready)document.fonts.ready.then(()=>{positionSelbox();renderStrip();});
})();
