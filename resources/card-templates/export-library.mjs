// Extracts the card editor's built-in Text styles (PAIRS), Stickers (STICKERS) and
// Background colours (BG_COLORS) from public/js/card-editor.js into library.json.
// Used to seed the templates table (kinds text_style, sticker_set, bg_palette).
import { readFileSync, writeFileSync } from 'node:fs';
import vm from 'node:vm';
const lines = readFileSync(new URL('../../public/js/card-editor.js', import.meta.url), 'utf8').split('\n');
const block = name => {
  const s = lines.findIndex(l => l.startsWith(`const ${name}=`));
  let e = s; while (!/^\s*};?\s*$/.test(lines[e]) && !/};\s*$/.test(lines[e])) e++;
  return lines.slice(s, e + 1).join('\n');
};
const code = [block('STICKERS'), block('BG_COLORS'), block('PAIRS'), 'out={pairs:PAIRS,stickers:STICKERS,bgColors:BG_COLORS};'].join('\n');
const ctx = {}; vm.runInNewContext(code, ctx);
writeFileSync(new URL('./library.json', import.meta.url), JSON.stringify(ctx.out, null, 1));
const n = o => Object.values(o).reduce((a, l) => a + l.length, 0);
console.log('pairs', n(ctx.out.pairs), 'stickers', n(ctx.out.stickers), 'colors', n(ctx.out.bgColors),
  'cats', Object.keys(ctx.out.pairs).length, Object.keys(ctx.out.stickers).length, Object.keys(ctx.out.bgColors).length);
