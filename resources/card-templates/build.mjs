/* Builds the greeting-card template library.
     node resources/card-templates/build.mjs
   Writes public/assets/templates/<category>/*.svg and public/assets/templates/templates.json */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { svg } from './lib.mjs';
import { A } from './assets.mjs';
import { CATEGORIES, TEMPLATES } from './templates.mjs';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const OUT = path.join(root, 'public/assets/templates');

let bytes = 0;
for (const [name, [w, h, body]] of Object.entries(A)) {
  const file = path.join(OUT, name + '.svg');
  fs.mkdirSync(path.dirname(file), { recursive: true });
  const s = svg(w, h, body);
  fs.writeFileSync(file, s);
  bytes += s.length;
}

const used = new Set();
TEMPLATES.forEach(t => Object.values(t.pages).forEach(p => p.els.forEach((e, i) => {
  e.id = `${t.id}-${p === t.pages.front ? 'f' : 'p'}${i + 1}`;
  if (e.src) { used.add(e.src.replace(/\.svg$/, '')); }
})));
const missing = [...used].filter(n => !A[n]);
if (missing.length) throw new Error('Missing assets: ' + missing.join(', '));

const counts = {};
TEMPLATES.forEach(t => counts[t.category] = (counts[t.category] || 0) + 1);
const lib = {
  version: 1,
  page: { width: 360, height: 504 },
  assetBase: 'assets/templates/',
  categories: CATEGORIES.map(([id, name]) => ({ id, name, count: counts[id] || 0 })),
  templates: TEMPLATES
};
fs.writeFileSync(path.join(OUT, 'templates.json'), JSON.stringify(lib));
console.log(`${Object.keys(A).length} assets (${Math.round(bytes / 1024)} KB), ${TEMPLATES.length} templates`);
console.log(CATEGORIES.map(([id]) => `${id}:${counts[id] || 0}`).join('  '));
