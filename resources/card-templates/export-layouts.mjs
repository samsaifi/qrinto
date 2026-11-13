// Extracts the built-in LAYOUTS from public/js/card-editor.js into layouts.json
// (portrait 360x504 coordinates). Used once to seed the templates table (kind=layout).
import { readFileSync, writeFileSync } from 'node:fs';
import vm from 'node:vm';
const src = readFileSync(new URL('../../public/js/card-editor.js', import.meta.url), 'utf8');
const lines = src.split('\n');
const pick = re => lines.find(l => re.test(l));
const start = lines.findIndex(l => l.startsWith('const LAYOUTS='));
let end = start; while (!lines[end].startsWith('};')) end++;
const code = [
  "let n=0;const uid=()=>'l'+(n++);",
  pick(/^const T=/), pick(/^const P=/), pick(/^const S=/), pick(/^const Q=/),
  ...lines.filter(l => /^const (LH|LL|LM|LC)=/.test(l)),
  lines.slice(start, end + 1).join('\n'),
  'out=Object.fromEntries(Object.entries(LAYOUTS).map(([v,list])=>[v,list.map(l=>({name:l.name,pages:l.make()}))]));',
].join('\n');
const ctx = { out: null }; vm.runInNewContext(code, ctx);
for (const list of Object.values(ctx.out)) for (const l of list) for (const els of Object.values(l.pages)) els.forEach(e => delete e.id);
writeFileSync(new URL('./layouts.json', import.meta.url), JSON.stringify(ctx.out, null, 1));
console.log(Object.entries(ctx.out).map(([v, l]) => v + ':' + l.length).join(' '));
