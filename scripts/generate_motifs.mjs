#!/usr/bin/env node
/**
 * generate_motifs.mjs — topographic contour SVGs + ridgeline divider paths.
 *
 * Dependency-free (inline 2D simplex noise + marching squares). Deterministic
 * (seeded PRNG), so re-running reproduces the committed assets byte-for-byte.
 *
 * Outputs:
 *   assets/img/topo-hero.svg    1440x900, 10 contour thresholds
 *   assets/img/topo-footer.svg  1440x480, 7 contour thresholds
 * Updates (d attributes only):
 *   templates/partials/ridge-a.php  (peaks leaning left)
 *   templates/partials/ridge-b.php  (peaks leaning right)
 *   templates/partials/ridge-c.php  (twin summit, centered)
 *
 * Usage: node scripts/generate_motifs.mjs
 */

import { readFileSync, writeFileSync, statSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = join(dirname(fileURLToPath(import.meta.url)), "..");
const IMG = join(ROOT, "assets", "img");
const PARTIALS = join(ROOT, "templates", "partials");

/* ------------------------------------------------------------------ */
/* Seeded PRNG (mulberry32)                                            */
/* ------------------------------------------------------------------ */
function mulberry32(seed) {
  let a = seed >>> 0;
  return function () {
    a |= 0; a = (a + 0x6d2b79f5) | 0;
    let t = Math.imul(a ^ (a >>> 15), 1 | a);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
  };
}

/* ------------------------------------------------------------------ */
/* 2D simplex noise (Gustavson-style, seeded permutation)              */
/* ------------------------------------------------------------------ */
function makeSimplex2D(seed) {
  const rand = mulberry32(seed);
  const p = Uint8Array.from({ length: 256 }, (_, i) => i);
  for (let i = 255; i > 0; i--) {
    const j = Math.floor(rand() * (i + 1));
    [p[i], p[j]] = [p[j], p[i]];
  }
  const perm = new Uint8Array(512);
  for (let i = 0; i < 512; i++) perm[i] = p[i & 255];

  const grad = [
    [1, 1], [-1, 1], [1, -1], [-1, -1],
    [1, 0], [-1, 0], [0, 1], [0, -1],
  ];
  const F2 = 0.5 * (Math.sqrt(3) - 1);
  const G2 = (3 - Math.sqrt(3)) / 6;

  return function noise2D(xin, yin) {
    const s = (xin + yin) * F2;
    const i = Math.floor(xin + s);
    const j = Math.floor(yin + s);
    const t = (i + j) * G2;
    const x0 = xin - (i - t);
    const y0 = yin - (j - t);
    const [i1, j1] = x0 > y0 ? [1, 0] : [0, 1];
    const x1 = x0 - i1 + G2, y1 = y0 - j1 + G2;
    const x2 = x0 - 1 + 2 * G2, y2 = y0 - 1 + 2 * G2;
    const ii = i & 255, jj = j & 255;
    let n = 0;
    let t0 = 0.5 - x0 * x0 - y0 * y0;
    if (t0 > 0) {
      const g = grad[perm[ii + perm[jj]] & 7];
      t0 *= t0; n += t0 * t0 * (g[0] * x0 + g[1] * y0);
    }
    let t1 = 0.5 - x1 * x1 - y1 * y1;
    if (t1 > 0) {
      const g = grad[perm[ii + i1 + perm[jj + j1]] & 7];
      t1 *= t1; n += t1 * t1 * (g[0] * x1 + g[1] * y1);
    }
    let t2 = 0.5 - x2 * x2 - y2 * y2;
    if (t2 > 0) {
      const g = grad[perm[ii + 1 + perm[jj + 1]] & 7];
      t2 *= t2; n += t2 * t2 * (g[0] * x2 + g[1] * y2);
    }
    return 70 * n; // roughly [-1, 1]
  };
}

/** 3-octave fractal noise; featurePx sets the dominant feature size. */
function makeFbm(seed, featurePx) {
  const noise = makeSimplex2D(seed);
  const f0 = 1 / featurePx;
  return (x, y) =>
    noise(x * f0, y * f0) +
    0.45 * noise(x * f0 * 2 + 31.7, y * f0 * 2 + 11.3) +
    0.18 * noise(x * f0 * 4 + 71.1, y * f0 * 4 + 47.9);
}

/* ------------------------------------------------------------------ */
/* Marching squares — isolines (stroke outlines, not filled bands)      */
/* ------------------------------------------------------------------ */
/**
 * Trace isolines of `values` (row-major, ny rows of nx) at `threshold`.
 * Returns chains: { pts: [[x,y]...] in grid units, closed: bool }.
 * Open chains terminate at the grid boundary (no frame-edge strokes).
 */
function isolines(values, nx, ny, threshold) {
  const at = (i, j) => values[j * nx + i];
  const ptOf = new Map(); // edge key -> [x, y]
  const segs = [];        // { a: edgeKey, b: edgeKey }

  // Interpolated crossing point on a horizontal/vertical grid edge.
  const hPoint = (i, j) => {
    const v1 = at(i, j), v2 = at(i + 1, j);
    return [i + (threshold - v1) / (v2 - v1), j];
  };
  const vPoint = (i, j) => {
    const v1 = at(i, j), v2 = at(i, j + 1);
    return [i, j + (threshold - v1) / (v2 - v1)];
  };

  for (let j = 0; j < ny - 1; j++) {
    for (let i = 0; i < nx - 1; i++) {
      const tl = at(i, j) > threshold ? 8 : 0;
      const tr = at(i + 1, j) > threshold ? 4 : 0;
      const br = at(i + 1, j + 1) > threshold ? 2 : 0;
      const bl = at(i, j + 1) > threshold ? 1 : 0;
      const idx = tl | tr | br | bl;
      if (idx === 0 || idx === 15) continue;

      // Edge descriptors: key + lazy crossing-point computation. Points are
      // only computed for edges actually used by a segment (a real crossing),
      // so the cache never holds extrapolated points from non-crossing edges.
      const T = { key: `h${i},${j}`, pt: () => hPoint(i, j) };
      const B = { key: `h${i},${j + 1}`, pt: () => hPoint(i, j + 1) };
      const L = { key: `v${i},${j}`, pt: () => vPoint(i, j) };
      const R = { key: `v${i + 1},${j}`, pt: () => vPoint(i + 1, j) };

      const add = (e1, e2) => {
        if (!ptOf.has(e1.key)) ptOf.set(e1.key, e1.pt());
        if (!ptOf.has(e2.key)) ptOf.set(e2.key, e2.pt());
        segs.push({ a: e1.key, b: e2.key });
      };
      switch (idx) {
        case 1:  add(L, B); break;
        case 2:  add(B, R); break;
        case 3:  add(L, R); break;
        case 4:  add(T, R); break;
        case 6:  add(T, B); break;
        case 7:  add(L, T); break;
        case 8:  add(L, T); break;
        case 9:  add(T, B); break;
        case 11: add(T, R); break;
        case 12: add(L, R); break;
        case 13: add(B, R); break;
        case 14: add(L, B); break;
        case 5: { // saddle: TR+BL inside
          const c = (at(i, j) + at(i + 1, j) + at(i, j + 1) + at(i + 1, j + 1)) / 4;
          if (c > threshold) { add(L, T); add(B, R); } else { add(T, R); add(L, B); }
          break;
        }
        case 10: { // saddle: TL+BR inside
          const c = (at(i, j) + at(i + 1, j) + at(i, j + 1) + at(i + 1, j + 1)) / 4;
          if (c > threshold) { add(T, R); add(L, B); } else { add(L, T); add(B, R); }
          break;
        }
      }
    }
  }

  // Chain segments into polylines / closed loops.
  const adj = new Map();
  segs.forEach((s, idx) => {
    for (const k of [s.a, s.b]) {
      if (!adj.has(k)) adj.set(k, []);
      adj.get(k).push(idx);
    }
  });
  const used = new Array(segs.length).fill(false);
  const chains = [];
  for (let i = 0; i < segs.length; i++) {
    if (used[i]) continue;
    used[i] = true;
    const keys = [segs[i].a, segs[i].b];
    let closed = false;
    for (;;) { // extend forward
      const end = keys[keys.length - 1];
      const cand = (adj.get(end) || []).find((k) => !used[k]);
      if (cand === undefined) break;
      used[cand] = true;
      const s = segs[cand];
      const next = s.a === end ? s.b : s.a;
      if (next === keys[0]) { closed = true; break; }
      keys.push(next);
    }
    if (!closed) {
      for (;;) { // extend backward
        const start = keys[0];
        const cand = (adj.get(start) || []).find((k) => !used[k]);
        if (cand === undefined) break;
        used[cand] = true;
        const s = segs[cand];
        keys.unshift(s.a === start ? s.b : s.a);
      }
    }
    chains.push({ pts: keys.map((k) => ptOf.get(k)), closed });
  }
  return chains;
}

/* Ramer–Douglas–Peucker simplification (px units). */
function rdp(pts, eps) {
  if (pts.length < 3) return pts;
  const keep = new Array(pts.length).fill(false);
  keep[0] = keep[pts.length - 1] = true;
  const stack = [[0, pts.length - 1]];
  while (stack.length) {
    const [a, b] = stack.pop();
    const [ax, ay] = pts[a], [bx, by] = pts[b];
    const dx = bx - ax, dy = by - ay;
    const len = Math.hypot(dx, dy) || 1e-9;
    let maxD = -1, maxI = -1;
    for (let i = a + 1; i < b; i++) {
      const d = Math.abs(dy * (pts[i][0] - ax) - dx * (pts[i][1] - ay)) / len;
      if (d > maxD) { maxD = d; maxI = i; }
    }
    if (maxD > eps) {
      keep[maxI] = true;
      stack.push([a, maxI], [maxI, b]);
    }
  }
  return pts.filter((_, i) => keep[i]);
}

const r1 = (n) => Math.round(n * 10) / 10;

function topoSvg({ width, height, cellPx, featurePx, thresholds, seed }) {
  const nx = Math.round(width / cellPx) + 1;
  const ny = Math.round(height / cellPx) + 1;
  const fbm = makeFbm(seed, featurePx);
  const values = new Float64Array(nx * ny);
  let min = Infinity, max = -Infinity;
  for (let j = 0; j < ny; j++) {
    for (let i = 0; i < nx; i++) {
      const v = fbm(i * cellPx, j * cellPx);
      values[j * nx + i] = v;
      if (v < min) min = v;
      if (v > max) max = v;
    }
  }
  const sx = width / (nx - 1), sy = height / (ny - 1);
  const paths = [];
  for (let k = 0; k < thresholds; k++) {
    const t = min + ((k + 1) / (thresholds + 1)) * (max - min);
    const chains = isolines(values, nx, ny, t);
    let d = "";
    for (const { pts, closed } of chains) {
      let px = pts.map(([x, y]) => [x * sx, y * sy]);
      px = rdp(px, 0.4);
      if (px.length < (closed ? 3 : 2)) continue;
      d += `M${r1(px[0][0])} ${r1(px[0][1])}`;
      for (let i = 1; i < px.length; i++) d += `L${r1(px[i][0])} ${r1(px[i][1])}`;
      if (closed) d += "Z";
    }
    if (d) paths.push(`<path fill="none" stroke="currentColor" stroke-width="1" d="${d}"/>`);
  }
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}" aria-hidden="true">\n${paths.join("\n")}\n</svg>\n`;
}

/* ------------------------------------------------------------------ */
/* Ridgeline dividers                                                  */
/* ------------------------------------------------------------------ */
/** Asymmetric summit bump: gentle Gaussian flank, width differs per side. */
function bump(u, { c, h, wl, wr }) {
  const w = u < c ? wl : wr;
  const d = (u - c) / w;
  return h * Math.exp(-0.5 * d * d);
}

/** Small smooth 1D value noise for natural texture on the ridgelines. */
function makeNoise1D(seed) {
  const rand = mulberry32(seed);
  const g = Array.from({ length: 64 }, () => rand() * 2 - 1);
  const fade = (t) => t * t * (3 - 2 * t);
  return (x) => {
    const i = Math.floor(x), f = x - i;
    const a = g[((i % 64) + 64) % 64], b = g[(((i + 1) % 64) + 64) % 64];
    return a + (b - a) * fade(f);
  };
}

/**
 * One ridge divider = 3 stacked silhouettes (back/mid/front). Same summit
 * family per variant, with parallax drift and softening toward the front.
 */
function ridgePaths(bumps, seed) {
  const noise = makeNoise1D(seed);
  const layers = [
    { base: 96,  amp: 74, drift: -0.030, widen: 1.00, undAmp: 0.10, nz: 0.05 }, // back
    { base: 108, amp: 52, drift: 0.025,  widen: 1.35, undAmp: 0.08, nz: 0.04 }, // mid
    { base: 116, amp: 32, drift: 0.060,  widen: 1.75, undAmp: 0.07, nz: 0.035 }, // front
  ];
  const N = 40; // 41 points across
  return layers.map((L, li) => {
    const elev = [];
    for (let s = 0; s <= N; s++) {
      const u = s / N;
      let e = 0;
      for (const b of bumps) {
        e += bump(u, { c: b.c + L.drift, h: b.h, wl: b.wl * L.widen, wr: b.wr * L.widen });
      }
      e += L.undAmp * Math.sin(2 * Math.PI * (1.6 * u + 0.13 * (li + 1) + seed * 0.01));
      e += L.nz * noise(u * 5.5 + li * 17.3);
      elev.push(e);
    }
    const maxE = Math.max(...elev);
    let d = "";
    for (let s = 0; s <= N; s++) {
      const x = r1((s / N) * 1440);
      const y = r1(Math.min(119, Math.max(4, L.base - (elev[s] / maxE) * L.amp)));
      d += s === 0 ? `M0 ${y}` : `L${x} ${y}`;
    }
    return d + "V120 H0 Z";
  });
}

const RIDGES = {
  "ridge-a.php": { // dominant summit left of center, steep left faces, long run-outs right
    seed: 101,
    bumps: [
      { c: 0.30, h: 1.00, wl: 0.075, wr: 0.20 },
      { c: 0.62, h: 0.45, wl: 0.065, wr: 0.15 },
      { c: 0.87, h: 0.30, wl: 0.055, wr: 0.12 },
    ],
  },
  "ridge-b.php": { // mirrored: dominant summit right of center, leaning right
    seed: 202,
    bumps: [
      { c: 0.70, h: 1.00, wl: 0.20, wr: 0.075 },
      { c: 0.38, h: 0.45, wl: 0.15, wr: 0.065 },
      { c: 0.13, h: 0.30, wl: 0.12, wr: 0.055 },
    ],
  },
  "ridge-c.php": { // twin summits astride the center, moderate saddle, gentle shoulders
    seed: 303,
    bumps: [
      { c: 0.40, h: 1.10, wl: 0.065, wr: 0.070 },
      { c: 0.60, h: 0.97, wl: 0.070, wr: 0.065 },
      { c: 0.13, h: 0.30, wl: 0.08, wr: 0.12 },
      { c: 0.87, h: 0.28, wl: 0.12, wr: 0.08 },
    ],
  },
};

/* ------------------------------------------------------------------ */
/* Run                                                                 */
/* ------------------------------------------------------------------ */
const outputs = [
  ["topo-hero.svg", topoSvg({ width: 1440, height: 900, cellPx: 10, featurePx: 420, thresholds: 10, seed: 11 })],
  ["topo-footer.svg", topoSvg({ width: 1440, height: 480, cellPx: 10, featurePx: 380, thresholds: 7, seed: 23 })],
];
for (const [name, svg] of outputs) {
  const path = join(IMG, name);
  writeFileSync(path, svg);
  console.log(`${name}: ${statSync(path).size} bytes`);
}

for (const [file, { seed, bumps }] of Object.entries(RIDGES)) {
  const path = join(PARTIALS, file);
  const src = readFileSync(path, "utf8");
  const ds = ridgePaths(bumps, seed);
  let n = 0;
  const out = src.replace(/ d="[^"]*"/g, () => ` d="${ds[n++]}"`);
  if (n !== 3) throw new Error(`${file}: expected 3 d attributes, found ${n}`);
  writeFileSync(path, out);
  console.log(`${file}: updated 3 ridgeline paths`);
}
