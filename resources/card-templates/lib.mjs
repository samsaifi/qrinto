/* Shared drawing helpers for the Qrinto greeting-card asset library.
   Every illustration is original vector art generated here, so the assets are
   licence-free, resolution independent (crisp at print DPI) and transparent. */

let gid = 0;
export const uid = p => `${p}${(++gid).toString(36)}`;
export const r2 = n => Math.round(n * 100) / 100;

export function rng(seed) {
  let a = seed >>> 0;
  return () => {
    a |= 0; a = (a + 0x6D2B79F5) | 0;
    let t = Math.imul(a ^ (a >>> 15), 1 | a);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}

const hex = c => { c = c.replace('#', ''); if (c.length === 3) c = c.split('').map(x => x + x).join(''); return [0, 2, 4].map(i => parseInt(c.slice(i, i + 2), 16)); };
const toHex = a => '#' + a.map(v => Math.max(0, Math.min(255, Math.round(v))).toString(16).padStart(2, '0')).join('');
export const mix = (a, b, t) => { const x = hex(a), y = hex(b); return toHex(x.map((v, i) => v + (y[i] - v) * t)); };
/* t>0 lightens toward white, t<0 darkens toward black */
export const shade = (c, t) => t >= 0 ? mix(c, '#ffffff', t) : mix(c, '#000000', -t);

export const svg = (w, h, body) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w} ${h}" width="${w}" height="${h}">${body}</svg>\n`;

export const lin = (id, stops, x1 = 0, y1 = 0, x2 = 0, y2 = 1) =>
  `<linearGradient id="${id}" x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}">${stops.map(([o, c, op = 1]) => `<stop offset="${o}" stop-color="${c}" stop-opacity="${op}"/>`).join('')}</linearGradient>`;
export const rad = (id, stops, cx = .5, cy = .5, r = .5, fx = cx, fy = cy) =>
  `<radialGradient id="${id}" cx="${cx}" cy="${cy}" r="${r}" fx="${fx}" fy="${fy}">${stops.map(([o, c, op = 1]) => `<stop offset="${o}" stop-color="${c}" stop-opacity="${op}"/>`).join('')}</radialGradient>`;
export const defs = s => `<defs>${s}</defs>`;

/* ---------- Botanical ---------- */
export function leaf(x, y, len, ang, fill, o = {}) {
  const w = o.w ?? .3, vein = o.vein ?? shade(fill, -.28);
  const body = `<path d="M0 0C${r2(len * .25)} ${r2(-len * w)} ${r2(len * .7)} ${r2(-len * w * .8)} ${r2(len)} 0C${r2(len * .7)} ${r2(len * w * .8)} ${r2(len * .25)} ${r2(len * w)} 0 0Z" fill="${fill}"/>`;
  const v = o.novein ? '' : `<path d="M${r2(len * .06)} 0Q${r2(len * .5)} ${r2(-len * .04)} ${r2(len * .9)} 0" stroke="${vein}" stroke-width="${r2(Math.max(.6, len * .022))}" fill="none" opacity=".55" stroke-linecap="round"/>`;
  return `<g transform="translate(${r2(x)} ${r2(y)}) rotate(${r2(ang)})">${body}${v}</g>`;
}

export function sprig(x, y, len, ang, col, o = {}) {
  const n = o.n ?? 6, ll = o.leaf ?? len * .34, bend = o.bend ?? len * .07, stem = o.stem ?? shade(col, -.25);
  let s = `<g transform="translate(${r2(x)} ${r2(y)}) rotate(${r2(ang)})"><path d="M0 0Q${r2(len / 2)} ${r2(-bend * 2)} ${r2(len)} 0" stroke="${stem}" stroke-width="${r2(o.sw ?? Math.max(1, len * .014))}" fill="none" stroke-linecap="round"/>`;
  for (let i = 0; i < n; i++) {
    const t = (i + 1) / (n + 1), px = len * t, py = -4 * bend * t * (1 - t);
    const side = i % 2 ? -1 : 1, a = side * (o.spread ?? 48) - 4;
    const c = mix(col, shade(col, i % 2 ? .18 : -.08), .6);
    s += leaf(px, py, ll * (1 - .38 * t), a, c, { w: o.w ?? .3, novein: o.novein });
  }
  s += leaf(len, 0, ll * .6, -2, col, { w: o.w ?? .3, novein: o.novein });
  return s + '</g>';
}

/* Eucalyptus: round leaves in pairs along a stem */
export function euca(x, y, len, ang, col, o = {}) {
  const n = o.n ?? 6, rr = o.r ?? len * .11;
  let s = `<g transform="translate(${r2(x)} ${r2(y)}) rotate(${r2(ang)})"><path d="M0 0Q${r2(len / 2)} ${r2(-len * .08)} ${r2(len)} 0" stroke="${shade(col, -.3)}" stroke-width="${r2(Math.max(1, len * .012))}" fill="none"/>`;
  for (let i = 0; i < n; i++) {
    const t = (i + .7) / (n + .4), px = len * t, py = -len * .32 * t * (1 - t), k = 1 - .45 * t;
    const c1 = mix(col, '#ffffff', .12 + (i % 3) * .06), c2 = mix(col, '#000000', .06);
    s += `<ellipse cx="${r2(px)}" cy="${r2(py - rr * k * .95)}" rx="${r2(rr * k)}" ry="${r2(rr * k * .88)}" fill="${c1}" opacity=".95"/>`;
    s += `<ellipse cx="${r2(px + rr * .3)}" cy="${r2(py + rr * k * .95)}" rx="${r2(rr * k)}" ry="${r2(rr * k * .88)}" fill="${c2}" opacity=".9"/>`;
  }
  return s + '</g>';
}

export function rose(cx, cy, r, c, o = {}) {
  const g = uid('rg');
  let s = defs(rad(g, [[0, shade(c, -.38)], [.55, c], [1, shade(c, .3)]], .5, .5, .55)) + `<g transform="translate(${r2(cx)} ${r2(cy)}) rotate(${o.rot ?? 0})"><circle r="${r2(r)}" fill="url(#${g})"/>`;
  const rings = [[1, 6, 0], [.8, 5, .4], [.6, 5, 1.1], [.42, 4, .3], [.27, 3, 1.4]];
  for (const [k, n, off] of rings) {
    for (let i = 0; i < n; i++) {
      const a = (i / n) * Math.PI * 2 + off, span = Math.PI / n * 1.25, pr = r * k;
      const a1 = a - span, a2 = a + span, ir = pr * .5;
      const P = (rad_, an) => `${r2(rad_ * Math.cos(an))} ${r2(rad_ * Math.sin(an))}`;
      const fill = shade(c, .28 - (1 - k) * .75);
      s += `<path d="M${P(ir, a1)}Q${P(pr * 1.08, a1 - .08)} ${P(pr, a)}Q${P(pr * 1.08, a2 + .08)} ${P(ir, a2)}Q${P(ir * .7, a)} ${P(ir, a1)}Z" fill="${fill}" stroke="${shade(c, .45)}" stroke-width="${r2(r * .025)}" stroke-opacity=".7"/>`;
    }
  }
  s += `<path d="M0 0m${r2(-r * .14)} 0a${r2(r * .14)} ${r2(r * .12)} 0 1 1 ${r2(r * .2)} ${r2(r * .08)}" fill="none" stroke="${shade(c, -.4)}" stroke-width="${r2(r * .04)}" stroke-linecap="round"/>`;
  return s + '</g>';
}

export function blossom(cx, cy, r, c, o = {}) {
  const n = o.n ?? 5, g = uid('bg'), center = o.center ?? '#f4c542';
  let s = defs(rad(g, [[0, shade(c, -.2)], [.6, c], [1, shade(c, .35)]], 0, .5, 1)) + `<g transform="translate(${r2(cx)} ${r2(cy)}) rotate(${o.rot ?? 0})">`;
  for (let i = 0; i < n; i++) s += `<path transform="rotate(${r2(i * 360 / n)})" d="M0 0C${r2(r * .55)} ${r2(-r * .6)} ${r2(r * 1.05)} ${r2(-r * .38)} ${r2(r)} 0C${r2(r * 1.05)} ${r2(r * .38)} ${r2(r * .55)} ${r2(r * .6)} 0 0Z" fill="url(#${g})" stroke="${shade(c, -.12)}" stroke-width="${r2(r * .02)}" stroke-opacity=".5"/>`;
  s += `<circle r="${r2(r * .24)}" fill="${center}"/>`;
  for (let i = 0; i < 7; i++) { const a = i / 7 * Math.PI * 2; s += `<circle cx="${r2(Math.cos(a) * r * .13)}" cy="${r2(Math.sin(a) * r * .13)}" r="${r2(r * .035)}" fill="${shade(center, -.35)}"/>`; }
  return s + '</g>';
}

export function daisy(cx, cy, r, petal = '#ffffff', center = '#f5b72a', o = {}) {
  const n = o.n ?? 16;
  let s = `<g transform="translate(${r2(cx)} ${r2(cy)}) rotate(${o.rot ?? 0})">`;
  for (let i = 0; i < n; i++) s += `<ellipse transform="rotate(${r2(i * 360 / n)})" cx="${r2(r * .52)}" cy="0" rx="${r2(r * .48)}" ry="${r2(r * .13)}" fill="${petal}" stroke="${shade(petal, -.12)}" stroke-width="${r2(r * .02)}"/>`;
  const g = uid('dc');
  s += defs(rad(g, [[0, shade(center, .3)], [1, shade(center, -.2)]], .4, .4, .6)) + `<circle r="${r2(r * .27)}" fill="url(#${g})"/>`;
  return s + '</g>';
}

export function tulip(x, y, h, c, o = {}) {
  const w = h * .3, g = uid('tg');
  return defs(lin(g, [[0, shade(c, .25)], [1, shade(c, -.15)]])) + `<g transform="translate(${r2(x)} ${r2(y)}) rotate(${o.rot ?? 0})">
<path d="M0 0Q${r2(-w * .1)} ${r2(h * .5)} 0 ${r2(h)}" stroke="#4f8a4b" stroke-width="${r2(h * .025)}" fill="none"/>
${leaf(0, h * .8, h * .42, -118, '#5f9c58', { w: .22 })}${leaf(0, h * .7, h * .38, -62, '#6aa862', { w: .22 })}
<path d="M${r2(-w * .5)} ${r2(-w * .1)}C${r2(-w * .55)} ${r2(-w * 1.1)} ${r2(-w * .2)} ${r2(-w * 1.25)} ${r2(-w * .25)} ${r2(-w * 1.3)}L0 ${r2(-w * .85)}L${r2(w * .25)} ${r2(-w * 1.3)}C${r2(w * .2)} ${r2(-w * 1.25)} ${r2(w * .55)} ${r2(-w * 1.1)} ${r2(w * .5)} ${r2(-w * .1)}C${r2(w * .4)} ${r2(w * .4)} ${r2(-w * .4)} ${r2(w * .4)} ${r2(-w * .5)} ${r2(-w * .1)}Z" fill="url(#${g})"/>
<path d="M0 ${r2(-w * .85)}C${r2(-w * .2)} ${r2(-w * .3)} ${r2(-w * .15)} ${r2(w * .1)} 0 ${r2(w * .2)}" stroke="${shade(c, -.25)}" stroke-width="${r2(h * .008)}" fill="none" opacity=".6"/></g>`;
}

/* ---------- Shapes ---------- */
export const heartPath = (cx, cy, s) => `M${r2(cx)} ${r2(cy + s * .42)}C${r2(cx - s * .95)} ${r2(cy - s * .1)} ${r2(cx - s * .55)} ${r2(cy - s * .8)} ${r2(cx)} ${r2(cy - s * .3)}C${r2(cx + s * .55)} ${r2(cy - s * .8)} ${r2(cx + s * .95)} ${r2(cy - s * .1)} ${r2(cx)} ${r2(cy + s * .42)}Z`;
export function heart(cx, cy, s, c, o = {}) {
  const g = uid('hg');
  return defs(rad(g, [[0, shade(c, .35)], [.6, c], [1, shade(c, -.2)]], .35, .3, .8)) + `<path transform="rotate(${o.rot ?? 0} ${r2(cx)} ${r2(cy)})" d="${heartPath(cx, cy, s)}" fill="${o.flat ? c : `url(#${g})`}" ${o.stroke ? `stroke="${o.stroke}" stroke-width="${o.sw ?? 2}"` : ''}/>`;
}
export function starPts(cx, cy, R, r, n = 5, rot = -90) {
  const p = [];
  for (let i = 0; i < n * 2; i++) { const a = (rot + i * 180 / n) * Math.PI / 180, rr = i % 2 ? r : R; p.push(`${r2(cx + rr * Math.cos(a))},${r2(cy + rr * Math.sin(a))}`); }
  return p.join(' ');
}
export const star = (cx, cy, R, c, o = {}) => `<polygon points="${starPts(cx, cy, R, R * (o.inner ?? .45), o.n ?? 5, o.rot ?? -90)}" fill="${c}" ${o.stroke ? `stroke="${o.stroke}" stroke-width="${o.sw ?? 1}" stroke-linejoin="round"` : ''}/>`;
export const sparkle = (cx, cy, s, c) => `<path d="M${r2(cx)} ${r2(cy - s)}Q${r2(cx + s * .12)} ${r2(cy - s * .12)} ${r2(cx + s)} ${r2(cy)}Q${r2(cx + s * .12)} ${r2(cy + s * .12)} ${r2(cx)} ${r2(cy + s)}Q${r2(cx - s * .12)} ${r2(cy + s * .12)} ${r2(cx - s)} ${r2(cy)}Q${r2(cx - s * .12)} ${r2(cy - s * .12)} ${r2(cx)} ${r2(cy - s)}Z" fill="${c}"/>`;

export function balloon(cx, cy, rx, ry, c, sx, sy) {
  const g = uid('bl');
  return defs(rad(g, [[0, shade(c, .55)], [.35, shade(c, .1)], [1, shade(c, -.25)]], .35, .3, .75)) +
    `<path d="M${r2(cx)} ${r2(cy + ry)}C${r2(cx + 6)} ${r2(cy + ry + (sy - cy - ry) * .3)} ${r2(sx - 10)} ${r2(cy + ry + (sy - cy - ry) * .6)} ${r2(sx)} ${r2(sy)}" stroke="#8a8a8a" stroke-width="1.2" fill="none"/>` +
    `<path d="M${r2(cx)} ${r2(cy - ry)}C${r2(cx + rx * 1.3)} ${r2(cy - ry)} ${r2(cx + rx * 1.1)} ${r2(cy + ry * .75)} ${r2(cx)} ${r2(cy + ry)}C${r2(cx - rx * 1.1)} ${r2(cy + ry * .75)} ${r2(cx - rx * 1.3)} ${r2(cy - ry)} ${r2(cx)} ${r2(cy - ry)}Z" fill="url(#${g})"/>` +
    `<path d="M${r2(cx - 5)} ${r2(cy + ry + 6)}L${r2(cx)} ${r2(cy + ry - 1)}L${r2(cx + 5)} ${r2(cy + ry + 6)}Z" fill="${shade(c, -.2)}"/>` +
    `<ellipse cx="${r2(cx - rx * .38)}" cy="${r2(cy - ry * .45)}" rx="${r2(rx * .14)}" ry="${r2(ry * .22)}" fill="#fff" opacity=".45" transform="rotate(-25 ${r2(cx - rx * .38)} ${r2(cy - ry * .45)})"/>`;
}

export function gift(x, y, w, h, box, rib, o = {}) {
  const g = uid('gb'), lidH = h * .2, by = y + lidH * .9;
  return defs(lin(g, [[0, shade(box, .12)], [.7, box], [1, shade(box, -.18)]], 0, 0, 1, 0)) +
    `<rect x="${r2(x + w * .05)}" y="${r2(by)}" width="${r2(w * .9)}" height="${r2(y + h - by)}" rx="${r2(w * .02)}" fill="url(#${g})"/>` +
    `<rect x="${r2(x + w * .05)}" y="${r2(by)}" width="${r2(w * .9)}" height="${r2(h * .06)}" fill="#000" opacity=".1"/>` +
    `<rect x="${r2(x)}" y="${r2(y)}" width="${r2(w)}" height="${r2(lidH)}" rx="${r2(w * .025)}" fill="${shade(box, .08)}"/>` +
    `<rect x="${r2(x + w * .44)}" y="${r2(y)}" width="${r2(w * .12)}" height="${r2(h)}" fill="${rib}"/>` +
    (o.pattern ? Array.from({ length: 6 }, (_, i) => `<circle cx="${r2(x + w * (.16 + (i % 3) * .3))}" cy="${r2(by + (h - lidH) * (.3 + Math.floor(i / 3) * .4))}" r="${r2(w * .035)}" fill="${o.pattern}" opacity=".8"/>`).join('') : '') +
    `<path d="M${r2(x + w * .5)} ${r2(y)}C${r2(x + w * .2)} ${r2(y - h * .35)} ${r2(x + w * .05)} ${r2(y - h * .05)} ${r2(x + w * .5)} ${r2(y)}Z" fill="${rib}" stroke="${shade(rib, -.2)}" stroke-width="1.5"/>` +
    `<path d="M${r2(x + w * .5)} ${r2(y)}C${r2(x + w * .8)} ${r2(y - h * .35)} ${r2(x + w * .95)} ${r2(y - h * .05)} ${r2(x + w * .5)} ${r2(y)}Z" fill="${rib}" stroke="${shade(rib, -.2)}" stroke-width="1.5"/>` +
    `<ellipse cx="${r2(x + w * .5)}" cy="${r2(y)}" rx="${r2(w * .07)}" ry="${r2(h * .05)}" fill="${shade(rib, -.15)}"/>`;
}

export function confetti(w, h, n, colors, seed, o = {}) {
  const R = rng(seed); let s = '';
  for (let i = 0; i < n; i++) {
    const x = R() * w, y = o.band ? (R() ** (o.band) * h) : R() * h, c = colors[Math.floor(R() * colors.length)], rot = R() * 360, k = R();
    const sz = (o.size ?? 7) * (.6 + R() * .8);
    if (k < .4) s += `<rect x="${r2(x)}" y="${r2(y)}" width="${r2(sz)}" height="${r2(sz * .45)}" rx="1" fill="${c}" transform="rotate(${r2(rot)} ${r2(x)} ${r2(y)})"/>`;
    else if (k < .65) s += `<circle cx="${r2(x)}" cy="${r2(y)}" r="${r2(sz * .38)}" fill="${c}"/>`;
    else if (k < .85) s += `<path d="M${r2(x)} ${r2(y)}q${r2(sz * .4)} ${r2(-sz * .5)} ${r2(sz * .8)} 0t${r2(sz * .8)} 0" stroke="${c}" stroke-width="${r2(sz * .22)}" fill="none" stroke-linecap="round" transform="rotate(${r2(rot)} ${r2(x)} ${r2(y)})"/>`;
    else s += sparkle(x, y, sz * .55, c);
  }
  return s;
}

/* Watercolour wash: soft blobs with turbulent, bleeding edges */
export function wash(w, h, blobs, seed, o = {}) {
  const f = uid('wc');
  return defs(`<filter id="${f}" x="-30%" y="-30%" width="160%" height="160%"><feTurbulence type="fractalNoise" baseFrequency="${o.freq ?? .018}" numOctaves="3" seed="${seed}"/><feDisplacementMap in="SourceGraphic" scale="${o.disp ?? 46}" xChannelSelector="R" yChannelSelector="G"/><feGaussianBlur stdDeviation="${o.blur ?? 5}"/></filter>`) +
    `<g filter="url(#${f})">${blobs.map(([cx, cy, rx, ry, c, op = .5]) => `<ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="${c}" opacity="${op}"/>`).join('')}</g>`;
}
