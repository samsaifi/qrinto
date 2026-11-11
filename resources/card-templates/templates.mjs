/* Greeting-card template library. Coordinates use the editor's portrait page: 360 x 504.
   Element schema matches public/js/card-editor.js:
     text  {type:'text', x,y,w, text,font,size,color,align,bold,italic,lh,ls}
     art   {type:'art',  x,y,w,h, src, fit:'contain'|'fill', rot, flipX}   (decorative transparent graphic)
     photo {type:'photo',x,y,w,h, src, fit, shape}                           (replaceable photo frame)  */
import { A } from './assets.mjs';

export const CATEGORIES = [
  ['birthday', 'Birthday'], ['wedding', 'Wedding'], ['anniversary', 'Anniversary'], ['thank-you', 'Thank You'],
  ['retirement', 'Retirement'], ['congratulations', 'Congratulations'], ['baby-shower', 'Baby Shower'], ['graduation', 'Graduation'],
  ['get-well-soon', 'Get Well Soon'], ['christmas', 'Christmas'], ['new-year', 'New Year'], ['valentines-day', "Valentine's Day"],
  ['invitation', 'Invitation'], ['friendship', 'Friendship'], ['mothers-day', "Mother's Day"], ['fathers-day', "Father's Day"]
];

const FULL = new Set(Object.keys(A).filter(k => /^shared\/(confetti-gold|frame-|dots-|memphis|wash-|stars-scatter)/.test(k) || k === 'christmas/snowfall' || k === 'wedding/floral-arch' || k === 'thank-you/botanical-frame'));

/* art(name, x, y, w, opts): height follows the asset's aspect ratio. x:'c' centres it. */
const art = (name, x, y, w, o = {}) => {
  if (!A[name]) throw new Error('Unknown asset ' + name);
  const [vw, vh] = A[name], h = o.h ?? Math.round(w * vh / vw);
  if (x === 'c') x = Math.round((360 - w) / 2);
  const e = { type: 'art', x, y, w, h, src: name + '.svg', fit: FULL.has(name) ? 'fill' : 'contain' };
  if (o.rot) e.rot = o.rot; if (o.flipX) e.flipX = true; if (o.flipY) e.flipY = true; if (o.opacity) e.opacity = o.opacity;
  return e;
};
const full = name => art(name, 0, 0, 360, { h: 504 });
const tx = (text, y, font, size, color, o = {}) => ({ type: 'text', x: o.x ?? 20, y, w: o.w ?? 320, text, font, size, color, align: o.align ?? 'center', bold: !!o.bold, italic: !!o.italic, lh: o.lh ?? 1.2, ls: o.ls ?? 0 });
const photo = (src, x, y, w, h, shape = 'rounded') => ({ type: 'photo', x, y, w, h, src: src + '.svg', fit: 'cover', shape });

const MSG = {
  birthday: ['Wishing you a day filled with laughter, cake and everyone you love. Here is to your best year yet!', 'Happy birthday!'],
  wedding: ['Wishing you a lifetime of love, laughter and happily ever after. Congratulations to you both!', 'With love'],
  anniversary: ['Every year with you is my favourite year so far. Here is to the next one, and all the ones after that.', 'All my love'],
  'thank-you': ['Your kindness meant more than you know. Thank you for showing up when it mattered most.', 'With gratitude'],
  retirement: ['Congratulations on a wonderful career! Wishing you sunny days, long lunches and endless adventures.', 'Best wishes'],
  congratulations: ['You worked so hard for this, and it shows. Enjoy every bit of it. You earned it!', 'So proud of you'],
  'baby-shower': ['Wishing you all the joy, love and sweet cuddles as you welcome your little one into the world.', 'With love'],
  graduation: ['All those late nights paid off! The future is bright and we can not wait to see what you do next.', 'Congratulations'],
  'get-well-soon': ['Rest up, take it slow and know that lots of people are rooting for you. See you back on your feet soon!', 'Get well soon'],
  christmas: ['May your home be filled with warmth, your table with good food and your heart with joy this season.', "Season's greetings"],
  'new-year': ['Cheers to new adventures, fresh starts and making beautiful memories together in the year ahead.', 'Happy New Year'],
  'valentines-day': ['You make every ordinary day feel like something special. I love you more than words can say.', 'Forever yours'],
  invitation: ['We would love for you to join us. Please let us know if you can make it so we can save you a seat!', 'See you there'],
  friendship: ['Thank you for every laugh, every late-night chat and every time you just got it. Lucky to have you.', 'Your friend'],
  'mothers-day': ['To the woman who taught me everything. Thank you for your endless love, patience and hugs.', 'Love always'],
  'fathers-day': ['Thanks for the advice, the bad jokes and always being in my corner. Have a great day, Dad!', 'Love you, Dad']
};

/* t(id, name, cat, style, front, opts) -> full template with inside + back pages */
const T = [];
let cur = null;
function t(id, name, style, bg, els, o = {}) {
  const [paper = '#ffffff', ink = '#3b3b3b', script = '#9b2242'] = o.inside || [];
  const accent = o.accent ?? els.find(e => e.type === 'art' && e.fit === 'contain')?.src?.replace(/\.svg$/, '');
  const [msg, sign] = MSG[cur];
  const inR = [];
  if (accent) inR.push(art(accent, 'c', 60, 84));
  inR.push(tx(msg, 190, o.bodyFont ?? 'Cormorant Garamond', 19, ink, { x: 40, w: 280, lh: 1.5 }));
  inR.push(tx(sign, 360, o.signFont ?? 'Great Vibes', 32, script, { x: 40, w: 280 }));
  const back = accent ? [art(accent, 'c', 380, 44)] : [];
  T.push({
    id, name, category: cur, style, tags: o.tags ?? [], featured: !!o.featured, popular: o.popular ?? 0,
    pages: { front: { bg, els }, inL: { bg: o.inLbg ?? paper, els: o.inL ?? [] }, inR: { bg: paper, els: inR }, back: { bg: o.backbg ?? bg, els: back } }
  });
}
const cat = c => { cur = c; };

/* ---------------- Birthday ---------------- */
cat('birthday');
t('birthday-001', 'Party Time', 'Colorful celebration', '#fff6e5', [
  art('birthday/bunting', 0, 0, 360), art('birthday/balloons', 8, 92, 96), art('birthday/cake', 'c', 128, 180), art('birthday/gift', 266, 262, 86),
  tx('Happy Birthday', 352, 'Pacifico', 38, '#e4572e'), tx('HAVE A SWEET ONE', 422, 'Josefin Sans', 13, '#7a5b12', { ls: 4 })
], { featured: 1, popular: 98, tags: ['cake', 'balloons', 'party'], inside: ['#ffffff', '#3b3b3b', '#e4572e'] });
t('birthday-002', 'Modern Birthday', 'Minimalist & modern', '#f4efe9', [
  art('shared/sparkles-gold', 252, 54, 84), art('birthday/cupcake', 'c', 80, 104),
  tx('HAPPY', 232, 'Bebas Neue', 70, '#1f2a44', { ls: 8, lh: 1 }), tx('birthday', 296, 'Great Vibes', 56, '#d9822b'),
  tx('WISHING YOU A YEAR OF GOOD THINGS', 410, 'Josefin Sans', 11, '#6b6259', { ls: 3 })
], { popular: 80, tags: ['cupcake', 'minimal'], inside: ['#ffffff', '#1f2a44', '#d9822b'] });
t('birthday-003', 'Golden Birthday', 'Luxury gold & black', '#111318', [
  full('shared/confetti-gold'), full('shared/frame-gold'), art('congratulations/balloons-gold', 'c', 46, 124),
  tx('Happy', 224, 'Great Vibes', 56, '#e3bb57'), tx('BIRTHDAY', 294, 'Cinzel', 34, '#f3e3b5', { ls: 6 }),
  tx('CHEERS TO YOU', 358, 'Josefin Sans', 12, '#c9b88f', { ls: 5 })
], { featured: 1, popular: 92, tags: ['gold', 'black', 'luxury'], accent: 'congratulations/balloons-gold', inside: ['#fbf8f1', '#2b2b2b', '#b8862b'], inLbg: '#111318', inL: [full('shared/confetti-gold')] });
t('birthday-004', 'Party Animal', 'Cute & playful', '#e8f6ff', [
  full('shared/dots-pastel'), art('birthday/party-hat', 36, 52, 92, { rot: -14 }), art('birthday/gift', 232, 64, 100),
  tx("It's your", 200, 'Fredoka', 30, '#2b59c3'), tx('BIRTHDAY!', 240, 'Righteous', 50, '#ff5d73', { lh: 1.1 }), art('birthday/cake', 'c', 324, 130)
], { popular: 76, tags: ['kids', 'party hat'], accent: 'birthday/party-hat', inside: ['#ffffff', '#2b59c3', '#ff5d73'] });
t('birthday-005', 'Birthday Snapshot', 'Photo-based', '#ffffff', [
  photo('shared/photo-bokeh', 30, 30, 300, 250), art('birthday/balloons', 262, 196, 88),
  tx('Happy Birthday', 296, 'Dancing Script', 44, '#c2185b'), tx('to the one and only you', 364, 'Lora', 16, '#5a4a52', { italic: true }),
  art('shared/confetti-top', 0, 404, 360, { h: 100 })
], { popular: 70, tags: ['photo'], accent: 'birthday/balloons', inside: ['#ffffff', '#3b3b3b', '#c2185b'] });

/* ---------------- Wedding ---------------- */
cat('wedding');
t('wedding-001', 'Floral Wedding', 'Floral & botanical', '#fffdf9', [
  full('wedding/floral-arch'), tx('ON YOUR WEDDING DAY', 150, 'Cormorant Garamond', 14, '#7a5c2e', { ls: 5 }),
  tx('Ana & Theo', 176, 'Pinyon Script', 52, '#7a5c2e'), art('wedding/rings', 'c', 262, 86),
  tx('08 · 28 · 2026', 336, 'Cormorant Garamond', 17, '#5a4520', { ls: 4 })
], { featured: 1, popular: 95, tags: ['roses', 'rings'], accent: 'wedding/rings', inside: ['#fffdf9', '#5a4520', '#7a5c2e'] });
t('wedding-002', 'Watercolor Vows', 'Watercolor illustrations', '#ffffff', [
  full('shared/wash-pink'), art('wedding/bouquet', 'c', 34, 160),
  tx('Mr & Mrs', 254, 'Great Vibes', 58, '#8a3b52'), tx('WISHING YOU A LIFETIME OF LOVE', 340, 'Josefin Sans', 11, '#8a5c68', { ls: 3 })
], { popular: 88, tags: ['bouquet', 'watercolor'], inside: ['#ffffff', '#5a3a44', '#8a3b52'] });
t('wedding-003', 'Black Tie Wedding', 'Luxury gold & black', '#0f1115', [
  full('shared/frame-deco'), art('wedding/rings', 'c', 76, 140),
  tx('Wedding', 204, 'Pinyon Script', 64, '#e3bb57'), tx('CONGRATULATIONS', 300, 'Cinzel', 18, '#f3e3b5', { ls: 5 }),
  art('invitation/deco-divider', 'c', 344, 220)
], { popular: 84, tags: ['gold', 'art deco'], inside: ['#fbf8f1', '#2b2b2b', '#b8862b'] });
t('wedding-004', 'Vintage Doves', 'Vintage & classic', '#f6efe2', [
  art('wedding/doves', 'c', 66, 220), tx('Happily', 226, 'Playfair Display', 40, '#5a4520', { italic: true }),
  tx('Ever After', 276, 'DM Serif Display', 48, '#5a4520'), art('wedding/greenery-swag', 'c', 360, 240)
], { popular: 72, tags: ['doves', 'classic'], inside: ['#fbf6ec', '#5a4520', '#7a5c2e'] });

/* ---------------- Anniversary ---------------- */
cat('anniversary');
t('anniversary-001', 'Red Roses', 'Elegant & premium', '#fbf3ef', [
  art('anniversary/rose-corner', 0, 0, 170), art('anniversary/rose-corner', 190, 334, 170, { rot: 180 }),
  tx('Happy', 186, 'Great Vibes', 48, '#9b2242'), tx('Anniversary', 238, 'Playfair Display', 40, '#7a1f35'),
  tx('TO MANY MORE YEARS', 306, 'Josefin Sans', 12, '#9b5a68', { ls: 4 })
], { featured: 1, popular: 90, tags: ['roses', 'romantic'], accent: 'anniversary/hearts-float', inside: ['#ffffff', '#4a2a33', '#9b2242'] });
t('anniversary-002', 'Heart Wreath', 'Floral & botanical', '#ffffff', [
  art('anniversary/heart-wreath', 'c', 40, 280), tx('10', 128, 'Abril Fatface', 60, '#c8243f', { lh: 1 }),
  tx('years', 192, 'Great Vibes', 34, '#7a1f35'), tx('Happy Anniversary', 336, 'Cormorant Garamond', 28, '#7a1f35', { italic: true }),
  tx('my love, my best friend', 386, 'Lora', 15, '#8a6a72')
], { popular: 78, tags: ['wreath', 'milestone'], inside: ['#ffffff', '#4a2a33', '#c8243f'] });
t('anniversary-003', 'Modern Love', 'Modern typography', '#1f2a44', [
  art('anniversary/hearts-float', 236, 34, 100), tx('HAPPY', 148, 'Bebas Neue', 76, '#ffd1da', { ls: 6, lh: 1 }),
  tx('ANNIVERSARY', 222, 'Bebas Neue', 54, '#ffffff', { ls: 3, lh: 1 }), tx('Still my favourite person', 296, 'Caveat', 32, '#f8a5b3'),
  art('anniversary/hearts-float', 26, 384, 70, { rot: -12 })
], { popular: 74, tags: ['hearts', 'bold'], inside: ['#ffffff', '#1f2a44', '#e5384f'] });
t('anniversary-004', 'Our Story', 'Photo-based', '#f7ece8', [
  photo('shared/photo-sunset', 60, 36, 240, 240, 'circle'), art('anniversary/hearts-float', 246, 210, 76),
  tx('Happy Anniversary', 296, 'Alex Brush', 40, '#8a2b40'), tx("Here's to us, and every chapter still to come", 366, 'Lora', 15, '#6b4a52', { italic: true, x: 50, w: 260, lh: 1.4 })
], { popular: 66, tags: ['photo'], inside: ['#ffffff', '#4a2a33', '#8a2b40'] });

/* ---------------- Thank you ---------------- */
cat('thank-you');
t('thank-you-001', 'Botanical Thanks', 'Floral & botanical', '#f7f4ec', [
  full('thank-you/botanical-frame'), tx('Thank', 160, 'Playfair Display', 60, '#2e5d47', { italic: true, lh: 1.05 }),
  tx('you', 226, 'Playfair Display', 60, '#2e5d47', { italic: true, lh: 1.05 }), tx('FOR EVERYTHING', 318, 'Josefin Sans', 13, '#6f9d6a', { ls: 5 })
], { featured: 1, popular: 86, tags: ['leaves', 'greenery'], accent: 'thank-you/eucalyptus-sprig', inside: ['#ffffff', '#1e3b2e', '#2e5d47'] });
t('thank-you-002', 'Watercolor Thank You', 'Watercolor illustrations', '#ffffff', [
  full('shared/wash-peach'), art('thank-you/watercolor-flowers', 'c', 60, 300),
  tx('Thank you', 254, 'Great Vibes', 64, '#b4533c'), tx('so very much', 340, 'Cormorant Garamond', 22, '#8a5a4a', { italic: true })
], { popular: 82, tags: ['watercolor', 'flowers'], inside: ['#ffffff', '#5a3a30', '#b4533c'] });
t('thank-you-003', 'Minimal Thanks', 'Minimalist & modern', '#ffffff', [
  art('thank-you/eucalyptus-sprig', 'c', 40, 100), tx('THANK YOU', 226, 'Montserrat', 30, '#2b2b2b', { bold: true, ls: 8 }),
  art('invitation/deco-divider', 'c', 282, 160), tx('for being wonderful', 318, 'Cormorant Garamond', 21, '#7a7a7a', { italic: true })
], { popular: 68, tags: ['minimal', 'eucalyptus'], inside: ['#ffffff', '#2b2b2b', '#5f8a7a'] });
t('thank-you-004', 'Zesty Thanks', 'Pastel aesthetic', '#fffbe6', [
  art('thank-you/lemon-branch', 'c', 46, 260), tx('Thanks', 266, 'Pacifico', 54, '#2e7d57'), tx('a bunch!', 346, 'Caveat', 42, '#d39a12', { bold: true })
], { popular: 64, tags: ['lemons', 'fresh'], inside: ['#ffffff', '#2e5d47', '#d39a12'] });

/* ---------------- Retirement ---------------- */
cat('retirement');
t('retirement-001', 'Cheers to Retirement', 'Colorful celebration', '#1c2a3a', [
  full('shared/confetti-gold'), art('retirement/champagne', 'c', 40, 176),
  tx('Happy Retirement', 248, 'Great Vibes', 46, '#f3d98b'), tx('THE ADVENTURE BEGINS', 320, 'Josefin Sans', 12, '#dfe8f0', { ls: 4 })
], { featured: 1, popular: 80, tags: ['champagne', 'cheers'], inside: ['#ffffff', '#1c2a3a', '#b8862b'] });
t('retirement-002', 'Sunset Retirement', 'Vintage & classic', '#fff4e4', [
  tx('Happy', 64, 'Playfair Display', 40, '#c2543a', { italic: true }), tx('RETIREMENT', 116, 'Bebas Neue', 58, '#6b2f1f', { ls: 4, lh: 1 }),
  tx('Enjoy every sunny day ahead', 194, 'Lora', 16, '#8a5a40', { italic: true }), art('retirement/sunset', 0, 284, 360)
], { popular: 72, tags: ['sunset', 'palms', 'beach'], inside: ['#fffaf2', '#6b2f1f', '#c2543a'] });
t('retirement-003', 'Officially Retired', 'Geometric & abstract', '#f3efe6', [
  full('shared/memphis'), art('retirement/hammock', 'c', 70, 280), tx('Officially', 244, 'Caveat', 42, '#e4572e', { bold: true }),
  tx('RETIRED', 292, 'Abril Fatface', 58, '#1f2a44'), tx('Now the fun begins', 384, 'Montserrat', 14, '#4a5568', { ls: 2 })
], { popular: 62, tags: ['fun', 'hammock'], inside: ['#ffffff', '#1f2a44', '#e4572e'] });
t('retirement-004', 'Gold Star Career', 'Luxury gold & black', '#0f1115', [
  full('shared/frame-gold'), art('retirement/starburst', 'c', 54, 200),
  tx('Happy Retirement', 280, 'Cinzel', 26, '#f3e3b5', { ls: 2 }), tx('A career to be proud of', 330, 'Cormorant Garamond', 21, '#c9b88f', { italic: true })
], { popular: 58, tags: ['gold', 'star'], inside: ['#fbf8f1', '#2b2b2b', '#b8862b'] });

/* ---------------- Congratulations ---------------- */
cat('congratulations');
t('congratulations-001', 'You Did It', 'Colorful celebration', '#fff7e6', [
  art('shared/confetti-top', 0, 0, 360), art('congratulations/trophy', 'c', 80, 124),
  tx('Congratulations', 260, 'Lobster', 40, '#c43a47'), tx('YOU DID IT!', 320, 'Bebas Neue', 36, '#1f2a44', { ls: 4 })
], { featured: 1, popular: 88, tags: ['trophy', 'confetti'], inside: ['#ffffff', '#1f2a44', '#c43a47'] });
t('congratulations-002', 'Congrats Banner', 'Luxury gold & black', '#1f2a44', [
  art('congratulations/balloons-gold', 24, 30, 120), art('congratulations/balloons-gold', 218, 50, 112, { flipX: 1 }),
  art('congratulations/ribbon-banner', 20, 248, 320), tx('CONGRATS!', 268, 'Bebas Neue', 40, '#ffffff', { ls: 4, lh: 1 }),
  tx('So proud of you', 358, 'Caveat', 34, '#ffd166', { bold: true })
], { popular: 76, tags: ['banner', 'balloons'], inside: ['#ffffff', '#1f2a44', '#c43a47'] });
t('congratulations-003', 'Gold Medal', 'Elegant & premium', '#f5f2ea', [
  art('congratulations/medal', 'c', 40, 120), art('shared/sparkles-gold', 250, 56, 80),
  tx('Well done!', 236, 'DM Serif Display', 50, '#1f2a44'), tx('A WINNING ACHIEVEMENT', 314, 'Josefin Sans', 12, '#a9771f', { ls: 4 })
], { popular: 66, tags: ['medal', 'winner'], inside: ['#ffffff', '#1f2a44', '#a9771f'] });
t('congratulations-004', 'First Place', 'Modern typography', '#e9f0ff', [
  art('congratulations/rosette', 'c', 36, 140), tx('Congrats', 240, 'Pacifico', 48, '#2f5bd3'), tx('on your big win', 316, 'Fredoka', 21, '#1d3f9e')
], { popular: 60, tags: ['rosette', 'ribbon'], inside: ['#ffffff', '#1d3f9e', '#2f5bd3'] });

/* ---------------- Baby shower ---------------- */
cat('baby-shower');
t('baby-shower-001', 'Teddy Bear Shower', 'Cute & playful', '#eaf5fc', [
  art('baby-shower/clouds', 0, 18, 360), art('baby-shower/teddy', 'c', 126, 140),
  tx('Oh Baby!', 296, 'Pacifico', 46, '#5b8fc7'), tx('BABY SHOWER', 372, 'Josefin Sans', 14, '#7a8fa6', { ls: 5 })
], { featured: 1, popular: 84, tags: ['teddy', 'clouds'], inside: ['#ffffff', '#3d5a7a', '#5b8fc7'] });
t('baby-shower-002', 'Little Feet', 'Pastel aesthetic', '#fdf1f4', [
  full('shared/dots-pastel'), art('baby-shower/baby-shoes', 'c', 80, 180),
  tx('Welcome', 222, 'Great Vibes', 54, '#c2577a', { lh: 1.05 }), tx('little one', 282, 'Great Vibes', 54, '#c2577a', { lh: 1.05 }),
  tx('A BABY IS ON THE WAY', 372, 'Josefin Sans', 12, '#8a6a74', { ls: 4 })
], { popular: 74, tags: ['shoes', 'pastel'], inside: ['#ffffff', '#5a3a44', '#c2577a'] });
t('baby-shower-003', 'Twinkle Little Star', 'Minimalist & modern', '#263a5e', [
  full('shared/stars-scatter'), art('baby-shower/moon-stars', 'c', 60, 160),
  tx('Twinkle twinkle', 222, 'Dancing Script', 38, '#fff1b8', { lh: 1.1 }), tx('little star', 266, 'Dancing Script', 38, '#fff1b8', { lh: 1.1 }),
  tx('BABY SHOWER', 344, 'Josefin Sans', 13, '#cfd8ea', { ls: 5 })
], { popular: 70, tags: ['moon', 'stars', 'night'], inside: ['#ffffff', '#263a5e', '#b8891a'] });
t('baby-shower-004', 'Pastel Balloons', 'Pastel aesthetic', '#fffaf0', [
  art('baby-shower/balloons-pastel', 'c', 34, 140), art('baby-shower/onesie', 262, 150, 72, { rot: 12 }),
  tx('Baby', 238, 'Fredoka', 60, '#f29bb4', { bold: true, lh: 1 }), tx('on board', 312, 'Caveat', 38, '#5fa3d0', { bold: true })
], { popular: 62, tags: ['balloons', 'onesie'], inside: ['#ffffff', '#3d5a7a', '#f29bb4'] });

/* ---------------- Graduation ---------------- */
cat('graduation');
t('graduation-001', 'Class of 2026', 'Luxury gold & black', '#121a2c', [
  full('shared/confetti-gold'), art('graduation/cap', 'c', 50, 180), tx('CLASS OF', 232, 'Josefin Sans', 16, '#f3d98b', { ls: 6 }),
  tx('2026', 254, 'Bebas Neue', 104, '#ffffff', { ls: 4, lh: 1 }), tx('Congratulations, Graduate', 378, 'Playfair Display', 20, '#e3bb57', { italic: true })
], { featured: 1, popular: 90, tags: ['cap', 'class of'], inside: ['#ffffff', '#121a2c', '#b8862b'] });
t('graduation-002', 'Proud Graduate', 'Vintage & classic', '#f6efe2', [
  art('graduation/diploma', 'c', 64, 220), tx('Congratulations', 220, 'Pinyon Script', 46, '#5a4520'),
  tx('GRADUATE', 288, 'Cinzel', 26, '#7a1f2a', { ls: 6 }), art('graduation/stars-gold', 'c', 346, 180)
], { popular: 72, tags: ['diploma', 'classic'], inside: ['#fbf6ec', '#5a4520', '#7a1f2a'] });
t('graduation-003', 'Bookworm Grad', 'Cute & playful', '#fff3d6', [
  art('graduation/books', 'c', 74, 150), art('graduation/cap', 196, 30, 110, { rot: 14 }),
  tx('You did it!', 236, 'Permanent Marker', 42, '#2f5bd3'), tx('SO PROUD OF YOU', 306, 'Montserrat', 13, '#c43a47', { bold: true, ls: 3 })
], { popular: 64, tags: ['books', 'school'], accent: 'graduation/books', inside: ['#ffffff', '#1f2a44', '#2f5bd3'] });
t('graduation-004', 'Hats Off', 'Geometric & abstract', '#e9f0ff', [
  full('shared/memphis'), art('graduation/cap', 'c', 76, 140), tx('Hats off', 210, 'Righteous', 46, '#1f2a44'),
  tx('to you, graduate!', 270, 'Caveat', 34, '#e4572e', { bold: true }), art('graduation/stars-gold', 'c', 340, 140)
], { popular: 58, tags: ['geometric', 'cap'], inside: ['#ffffff', '#1f2a44', '#e4572e'] });

/* ---------------- Get well soon ---------------- */
cat('get-well-soon');
t('get-well-soon-001', 'Sunshine', 'Cute & playful', '#e8f4ff', [
  art('get-well-soon/sun', 'c', 44, 150), tx('Get well', 214, 'Caveat', 68, '#1f3a93', { bold: true, lh: 1 }),
  tx('soon', 278, 'Caveat', 68, '#1f3a93', { bold: true, lh: 1 }), tx('Sending sunshine and a big hug your way', 378, 'Onest', 15, '#3d5a8c', { x: 50, w: 260, lh: 1.45 })
], { featured: 1, popular: 80, tags: ['sun', 'cheerful'], inside: ['#ffffff', '#1f3a93', '#f7a81c'] });
t('get-well-soon-002', 'Daisy Wishes', 'Floral & botanical', '#fffbea', [
  art('get-well-soon/butterfly', 248, 50, 84, { rot: 12 }), tx('Feel better', 100, 'Dancing Script', 50, '#3d7a4a', { lh: 1.1 }),
  tx('soon', 156, 'Dancing Script', 50, '#3d7a4a', { lh: 1.1 }), tx('Thinking of you', 236, 'Lora', 17, '#6b6040', { italic: true }),
  art('get-well-soon/daisies', 'c', 278, 340)
], { popular: 70, tags: ['daisies', 'butterfly'], inside: ['#ffffff', '#3d5a40', '#3d7a4a'] });
t('get-well-soon-003', 'Rainbow Hugs', 'Pastel aesthetic', '#ffffff', [
  art('get-well-soon/rainbow', 'c', 64, 280), tx('Get well', 248, 'Fredoka', 50, '#ff7b7b', { bold: true, lh: 1 }),
  tx('big hugs & a speedy recovery', 322, 'Caveat', 30, '#4f9be0', { bold: true }), art('get-well-soon/butterfly', 'c', 396, 70)
], { popular: 66, tags: ['rainbow', 'clouds'], inside: ['#ffffff', '#3d5a8c', '#ff7b7b'] });
t('get-well-soon-004', 'Tulip Bouquet', 'Watercolor illustrations', '#ffffff', [
  full('shared/wash-lilac'), art('get-well-soon/tulips', 'c', 36, 170),
  tx('Get Well Soon', 266, 'Great Vibes', 48, '#7a3d8f'), tx('with love and healing wishes', 340, 'Cormorant Garamond', 19, '#6a5a72', { italic: true })
], { popular: 60, tags: ['tulips', 'watercolor'], inside: ['#ffffff', '#4a3a52', '#7a3d8f'] });

/* ---------------- Christmas ---------------- */
cat('christmas');
t('christmas-001', 'Classic Christmas Tree', 'Vintage & classic', '#14201a', [
  full('christmas/snowfall'), art('christmas/tree', 'c', 36, 160),
  tx('Merry Christmas', 276, 'Great Vibes', 48, '#f6f1e7'), tx('& A HAPPY NEW YEAR', 348, 'Cormorant Garamond', 15, '#e8c547', { ls: 5 })
], { featured: 1, popular: 96, tags: ['tree', 'snow'], inside: ['#ffffff', '#14201a', '#8e1b1b'] });
t('christmas-002', 'Ornament Elegance', 'Elegant & premium', '#7a1424', [
  art('christmas/ornaments', 'c', 0, 300), tx('Merry', 222, 'Great Vibes', 64, '#f5d77a'),
  tx('& BRIGHT', 306, 'Cinzel', 22, '#fff1d6', { ls: 6 }), art('christmas/snowflake', 'c', 372, 44)
], { popular: 86, tags: ['ornaments', 'red', 'gold'], accent: 'christmas/ornaments', inside: ['#fffaf2', '#5a1020', '#7a1424'] });
t('christmas-003', 'Christmas Morning', 'Cute & playful', '#fdf6ec', [
  art('christmas/pine-garland', 0, 0, 360), tx('Ho Ho Ho!', 136, 'Lobster', 54, '#c8243f'),
  tx('MERRY CHRISTMAS', 214, 'Amatic SC', 38, '#1f6b4a', { bold: true, ls: 2 }),
  art('christmas/gift-red', 50, 300, 124), art('fathers-day/gift-blue', 188, 316, 112)
], { popular: 78, tags: ['gifts', 'garland'], accent: 'christmas/gift-red', inside: ['#ffffff', '#1f6b4a', '#c8243f'] });
t('christmas-004', 'Let It Snow', 'Minimalist & modern', '#1f3a5f', [
  full('shared/stars-scatter'), art('christmas/snowflake', 'c', 84, 70), art('christmas/snowflake', 64, 150, 32), art('christmas/snowflake', 262, 136, 40),
  tx('Let it snow', 224, 'Sacramento', 62, '#ffffff'), tx('WARM WINTER WISHES', 318, 'Josefin Sans', 12, '#cfe0f0', { ls: 5 })
], { popular: 72, tags: ['snowflake', 'winter'], accent: 'christmas/holly', inside: ['#ffffff', '#1f3a5f', '#2f6aa8'] });
t('christmas-005', 'Jingle Bells', 'Vintage & classic', '#f6efe2', [
  art('christmas/bells', 'c', 56, 170), tx('Jingle all', 236, 'DM Serif Display', 38, '#8e1b1b', { lh: 1.1 }),
  tx('the way', 280, 'DM Serif Display', 38, '#8e1b1b', { lh: 1.1 }), tx('MERRY CHRISTMAS', 346, 'Josefin Sans', 12, '#2f7d4f', { ls: 5 }),
  art('christmas/holly', 'c', 386, 100)
], { popular: 68, tags: ['bells', 'holly'], inside: ['#fbf6ec', '#5a1a1a', '#8e1b1b'] });

/* ---------------- New Year ---------------- */
cat('new-year');
t('new-year-001', 'Fireworks Night', 'Colorful celebration', '#0e1430', [
  art('new-year/fireworks', 0, 0, 360), tx('HAPPY', 270, 'Bebas Neue', 64, '#f3d98b', { ls: 8, lh: 1 }),
  tx('NEW YEAR', 334, 'Bebas Neue', 64, '#f3d98b', { ls: 6, lh: 1 }), tx('2027', 408, 'Playfair Display', 40, '#ffffff', { italic: true })
], { featured: 1, popular: 88, tags: ['fireworks', 'night'], inside: ['#ffffff', '#0e1430', '#b8862b'] });
t('new-year-002', 'Countdown', 'Luxury gold & black', '#121a2c', [
  art('new-year/streamers', 0, 0, 360), art('new-year/clock', 'c', 116, 128),
  tx('Cheers to', 262, 'Great Vibes', 42, '#f3d98b'), tx('2027', 316, 'Cinzel', 62, '#ffffff', { ls: 6, lh: 1 }),
  tx('MAKE IT SPARKLE', 410, 'Josefin Sans', 11, '#c9b88f', { ls: 5 })
], { popular: 76, tags: ['clock', 'midnight'], inside: ['#fbf8f1', '#121a2c', '#b8862b'] });
t('new-year-003', 'Golden Stars', 'Modern typography', '#f8f3e8', [
  art('new-year/gold-stars', 'c', 52, 240), tx('New Year', 236, 'Abril Fatface', 54, '#1f2a44'),
  tx('new beginnings', 310, 'Caveat', 38, '#b8862b', { bold: true }), art('shared/sparkles-gold', 'c', 390, 90)
], { popular: 66, tags: ['stars', 'gold'], inside: ['#ffffff', '#1f2a44', '#b8862b'] });
t('new-year-004', 'Champagne Toast', 'Pastel aesthetic', '#fdeff0', [
  full('shared/wash-gold'), art('retirement/champagne', 'c', 46, 168),
  tx('Happy New Year', 262, 'Dancing Script', 46, '#a86d1e'), tx('Cheers to 365 new days', 334, 'Lora', 16, '#7a5a40', { italic: true })
], { popular: 62, tags: ['champagne', 'toast'], inside: ['#ffffff', '#5a4020', '#a86d1e'] });

/* ---------------- Valentine's Day ---------------- */
cat('valentines-day');
t('valentines-day-001', 'Big Love', 'Colorful celebration', '#fff0f3', [
  art('valentines-day/heart-big', 'c', 60, 220), tx('Be mine', 284, 'Great Vibes', 62, '#c2185b'),
  tx("HAPPY VALENTINE'S DAY", 378, 'Josefin Sans', 12, '#9b2242', { ls: 4 })
], { featured: 1, popular: 92, tags: ['heart', 'love'], inside: ['#ffffff', '#4a2a33', '#c2185b'] });
t('valentines-day-002', 'Single Rose', 'Elegant & premium', '#1a0f14', [
  full('shared/frame-gold'), art('valentines-day/rose-single', 'c', 40, 84),
  tx('Forever', 236, 'Pinyon Script', 56, '#f5c6d0'), tx('YOURS', 314, 'Cinzel', 24, '#e3bb57', { ls: 8 })
], { popular: 84, tags: ['rose', 'romantic'], inside: ['#fbf6f4', '#3a1a24', '#a8172f'] });
t('valentines-day-003', 'Hearts Garland', 'Cute & playful', '#ffffff', [
  art('valentines-day/hearts-garland', 0, 0, 360), art('valentines-day/love-letter', 'c', 138, 180),
  tx('Love you lots', 300, 'Pacifico', 36, '#e5384f'), tx('XOXO', 362, 'Fredoka', 26, '#f27a8c', { bold: true, ls: 6 })
], { popular: 74, tags: ['letter', 'hearts'], inside: ['#ffffff', '#4a2a33', '#e5384f'] });
t('valentines-day-004', "Cupid's Arrow", 'Modern typography', '#ffe3e8', [
  art('valentines-day/cupid-arrow', 'c', 60, 300), tx('You stole', 234, 'Abril Fatface', 42, '#a8172f'),
  tx('my heart', 290, 'Great Vibes', 56, '#e5384f')
], { popular: 66, tags: ['cupid', 'arrow'], inside: ['#ffffff', '#4a2a33', '#a8172f'] });

/* ---------------- Invitation ---------------- */
cat('invitation');
t('invitation-001', 'Floral Invitation', 'Floral & botanical', '#ffffff', [
  art('invitation/floral-corner', 0, 0, 150), art('invitation/floral-corner', 210, 354, 150, { rot: 180 }),
  tx("YOU'RE INVITED", 150, 'Josefin Sans', 13, '#8a6b4a', { ls: 5 }), tx('Birthday Party', 172, 'Great Vibes', 46, '#a65d6f'),
  tx('in honor of', 240, 'Cormorant Garamond', 16, '#6b5a50', { italic: true }), tx('Andrea Clarkson', 262, 'Playfair Display', 26, '#3b3b3b'),
  tx('SATURDAY, AUGUST 15 · 7 PM', 310, 'Josefin Sans', 11, '#6b5a50', { ls: 2 }), tx('Lake Pavilion, Toronto', 332, 'Cormorant Garamond', 16, '#3b3b3b'),
  tx('RSVP by August 1', 356, 'Cormorant Garamond', 14, '#8a6b4a', { italic: true })
], { featured: 1, popular: 90, tags: ['party', 'roses'], accent: 'invitation/peony-spray', inside: ['#ffffff', '#3b3b3b', '#a65d6f'] });
t('invitation-002', 'Cocktail Night', 'Luxury gold & black', '#0f1115', [
  full('shared/frame-gold'), art('invitation/geo-frame', 'c', 120, 260),
  tx('JOIN US FOR', 88, 'Josefin Sans', 13, '#e3bb57', { ls: 6 }), tx('Cocktails', 196, 'Great Vibes', 50, '#e3bb57'),
  tx('& CONVERSATION', 264, 'Cinzel', 14, '#f3e3b5', { ls: 3 }), tx('FRIDAY · 8 PM', 290, 'Josefin Sans', 12, '#c9b88f', { ls: 4 }),
  art('shared/sparkles-gold', 'c', 410, 90)
], { popular: 80, tags: ['gold', 'geometric', 'party'], accent: 'shared/sparkles-gold', inside: ['#fbf8f1', '#2b2b2b', '#b8862b'] });
t('invitation-003', 'Balloon Arch Party', 'Pastel aesthetic', '#fffaf6', [
  art('invitation/balloon-arch', 0, 16, 360), tx("Let's", 116, 'Caveat', 38, '#b8862b', { bold: true }),
  tx('Celebrate!', 156, 'Playfair Display', 42, '#3b3b3b', { italic: true }), tx('SATURDAY, JUNE 6 · 4 PM', 290, 'Josefin Sans', 12, '#6b5a50', { ls: 3 }),
  tx('12 Garden Lane', 314, 'Cormorant Garamond', 17, '#3b3b3b'), tx('RSVP to Maya', 344, 'Cormorant Garamond', 15, '#8a6b4a', { italic: true }),
  art('invitation/deco-divider', 'c', 392, 160)
], { popular: 72, tags: ['balloons', 'party'], inside: ['#ffffff', '#3b3b3b', '#b8862b'] });
t('invitation-004', 'Save the Date', 'Watercolor illustrations', '#ffffff', [
  full('shared/wash-pink'), art('invitation/peony-spray', 'c', 30, 300),
  tx('Save the Date', 236, 'Great Vibes', 52, '#8a3b52'), tx('06 · 12 · 2027', 310, 'Cormorant Garamond', 22, '#5a3a44', { ls: 4 }),
  art('invitation/deco-divider', 'c', 350, 220), tx('Garden Estate, Napa Valley', 390, 'Cormorant Garamond', 16, '#6b4a52', { italic: true })
], { popular: 76, tags: ['wedding', 'peony'], inside: ['#ffffff', '#5a3a44', '#8a3b52'] });

/* ---------------- Friendship ---------------- */
cat('friendship');
t('friendship-001', 'If Friends Were Flowers', 'Colorful celebration', '#ffd9cf', [
  tx('If friends were', 52, 'Caveat', 42, '#5b3f8c', { bold: true }), tx('flowers', 100, 'Pacifico', 54, '#e4572e'),
  tx("I'd pick you", 184, 'Caveat', 46, '#5b3f8c', { bold: true }), art('friendship/flower-burst', 0, 304, 360)
], { featured: 1, popular: 86, tags: ['flowers', 'colorful'], inside: ['#ffffff', '#3b2f5c', '#e4572e'] });
t('friendship-002', 'Smile Friend', 'Geometric & abstract', '#ffe66d', [
  full('shared/memphis'), art('friendship/smiley', 'c', 66, 150), tx('You make me', 238, 'Fredoka', 32, '#1f2a44'),
  tx('SMILE', 280, 'Righteous', 70, '#ff6b6b', { lh: 1 })
], { popular: 74, tags: ['smiley', 'happy'], inside: ['#ffffff', '#1f2a44', '#ff6b6b'] });
t('friendship-003', 'Best Friends', 'Modern typography', '#ffffff', [
  art('friendship/hearts-pop', 'c', 54, 200), tx('Best', 256, 'Permanent Marker', 54, '#2b2b2b'),
  tx('friends forever', 330, 'Caveat', 36, '#ff6b6b', { bold: true })
], { popular: 70, tags: ['hearts', 'bff'], inside: ['#ffffff', '#2b2b2b', '#ff6b6b'] });
t('friendship-004', 'High Five', 'Cute & playful', '#e8f6ef', [
  art('friendship/friends-hands', 'c', 66, 260), tx('Friends', 254, 'Abril Fatface', 54, '#2e7d57'),
  tx('through thick and thin', 330, 'Caveat', 32, '#e4572e', { bold: true })
], { popular: 60, tags: ['hands', 'together'], inside: ['#ffffff', '#2e5d47', '#e4572e'] });

/* ---------------- Mother's Day ---------------- */
cat('mothers-day');
t('mothers-day-001', "Rose Mother's Day", 'Floral & botanical', '#ffffff', [
  art('invitation/floral-corner', 0, 0, 190), art('invitation/floral-corner', 170, 314, 190, { rot: 180 }),
  tx('HAPPY', 190, 'Josefin Sans', 18, '#d0707e', { ls: 7 }), tx("MOTHER'S", 216, 'Playfair Display', 48, '#d0707e', { ls: 2, lh: 1.05 }),
  tx('DAY', 268, 'Playfair Display', 48, '#d0707e', { ls: 2, lh: 1.05 })
], { featured: 1, popular: 94, tags: ['roses', 'mom'], accent: 'mothers-day/heart-floral', inside: ['#ffffff', '#5a3a44', '#d0707e'] });
t('mothers-day-002', 'Tulips for Mom', 'Watercolor illustrations', '#ffffff', [
  full('shared/wash-pink'), art('mothers-day/tulips', 'c', 36, 170),
  tx("Happy Mother's Day", 252, 'Great Vibes', 38, '#b0436b'), tx('to the best mom in the world', 318, 'Cormorant Garamond', 19, '#6a4a58', { italic: true })
], { popular: 82, tags: ['tulips', 'watercolor'], inside: ['#ffffff', '#5a3a48', '#b0436b'] });
t('mothers-day-003', 'Floral Wreath Mom', 'Elegant & premium', '#fdf6f0', [
  art('mothers-day/floral-wreath', 'c', 56, 280), tx('Mom', 146, 'Pinyon Script', 70, '#b0436b', { lh: 1.1 }),
  tx('you are loved', 236, 'Cormorant Garamond', 18, '#6a4a58', { italic: true }), tx("HAPPY MOTHER'S DAY", 384, 'Josefin Sans', 12, '#a0707e', { ls: 4 })
], { popular: 76, tags: ['wreath', 'roses'], inside: ['#ffffff', '#5a3a48', '#b0436b'] });
t('mothers-day-004', 'Butterfly Garden', 'Pastel aesthetic', '#f3ecff', [
  art('mothers-day/butterflies', 'c', 46, 240), tx('Thank you, Mom', 236, 'Dancing Script', 44, '#7a3d8f'),
  tx('for every little thing', 306, 'Lora', 16, '#6a5a72', { italic: true }), art('mothers-day/heart-floral', 'c', 352, 90)
], { popular: 66, tags: ['butterflies', 'pastel'], inside: ['#ffffff', '#4a3a52', '#7a3d8f'] });

/* ---------------- Father's Day ---------------- */
cat('fathers-day');
t('fathers-day-001', 'Dapper Dad', 'Modern typography', '#e6edf5', [
  art('fathers-day/tie', 'c', 24, 62), tx('BEST', 162, 'Bebas Neue', 84, '#1f3a5f', { ls: 6, lh: 1 }),
  tx('DAD', 238, 'Bebas Neue', 124, '#1f3a5f', { ls: 8, lh: 1 }), tx('ever', 368, 'Caveat', 46, '#d9822b', { bold: true, lh: 1 })
], { featured: 1, popular: 88, tags: ['tie', 'bold'], inside: ['#ffffff', '#1f3a5f', '#d9822b'] });
t('fathers-day-002', "Happy Father's Day", 'Photo-based', '#a8dcec', [
  art('fathers-day/tie', 22, 16, 54, { rot: 28 }), tx('Happy', 92, 'Alex Brush', 64, '#111111', { lh: 1 }),
  tx("Father's", 158, 'Alex Brush', 64, '#111111', { lh: 1 }), tx('Day', 224, 'Alex Brush', 64, '#111111', { lh: 1 }),
  tx('You are the best Dad!', 312, 'Dancing Script', 24, '#111111', { italic: true }),
  art('fathers-day/watch', 22, 352, 82, { rot: -24 }), art('fathers-day/gift-blue', 236, 372, 110)
], { popular: 84, tags: ['script', 'gift', 'watch'], accent: 'fathers-day/watch', inside: ['#ffffff', '#1f3a5f', '#111111'] });
t('fathers-day-003', 'No. 1 Dad', 'Vintage & classic', '#f3ead8', [
  art('fathers-day/badge', 'c', 44, 150), tx('No. 1', 236, 'Abril Fatface', 46, '#1f3a5f'),
  tx('DAD', 296, 'Cinzel', 42, '#7a4b2a', { ls: 10 }), tx("HAPPY FATHER'S DAY", 370, 'Josefin Sans', 12, '#7a6a52', { ls: 4 })
], { popular: 74, tags: ['badge', 'star'], inside: ['#fbf6ec', '#1f3a5f', '#7a4b2a'] });
t('fathers-day-004', 'Coolest Dad', 'Cute & playful', '#ffd166', [
  art('fathers-day/mustache-glasses', 'c', 70, 240), tx("World's coolest", 236, 'Caveat', 40, '#1f2a44', { bold: true }),
  tx('DAD', 288, 'Righteous', 84, '#1f2a44', { lh: 1 })
], { popular: 68, tags: ['mustache', 'fun'], inside: ['#ffffff', '#1f2a44', '#d9822b'] });
t('fathers-day-005', 'Grill Master', 'Geometric & abstract', '#2b2b2b', [
  art('fathers-day/bbq-tools', 'c', 36, 180), tx('Grill', 230, 'Permanent Marker', 58, '#ff9f1c', { lh: 1 }),
  tx('MASTER', 300, 'Bebas Neue', 62, '#ffffff', { ls: 6, lh: 1 }), tx("Happy Father's Day", 386, 'Caveat', 28, '#ffd166', { bold: true })
], { popular: 60, tags: ['bbq', 'grill'], inside: ['#ffffff', '#2b2b2b', '#e4572e'] });

export const TEMPLATES = T;
