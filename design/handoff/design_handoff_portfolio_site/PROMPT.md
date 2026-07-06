# Prompt for Claude Code

Paste this (or point Claude Code at this file) after adding the `design_handoff_portfolio_site/` folder to your repo:

---

I'm building my portfolio site, austinschuetz.com. The folder `design_handoff_portfolio_site/` contains a complete design handoff:

- `README.md` — the spec. Read it fully first. It documents every screen, exact colors/type/spacing, the brand motif rules (paper grain, topo lines, ridgeline dividers, duotone treatment), interactions, and open items.
- `Austin Schuetz Portfolio.dc.html` — an HTML **design reference** with three artifacts: 1a Home, 1b Meridian case-study page, 1c style guide. It uses a proprietary preview runtime, so don't run or ship it — read it for markup structure, exact copy, inline style values, and the ridgeline SVG path data.
- `tokens.json` — canonical design tokens (source of truth for color/spacing/type/radius/shadow/motion).
- `assets/` — grain and topographic SVGs to ship as-is.

Task: implement the site in this codebase following the README pixel-perfectly at desktop width (1440px reference). Use this repo's existing framework and conventions; if this is a fresh repo, pick a simple static-friendly stack (e.g. Astro or Next.js) and set it up. Specifics:

1. Wire `tokens.json` into the styling layer (CSS custom properties or the framework's token system) rather than hard-coding hex values.
2. Build the Home page and the Meridian case-study page exactly as specified. The case-study page should be a reusable template/route (`/work/[slug]`).
3. Follow the motif rules strictly: grain on all surfaces, topo only behind hero + inside the dark contact band, max 3 ridgeline dividers per page, duotone (CSS recipe in the README) only on the portrait and archive thumbnails, amber only for links/CTAs.
4. The browser/iPhone frames in the reference are mock presentation chrome — in production, project imagery is plain screenshots. Use placeholder images for now; I'll supply the portrait, Meridian/Ascent screenshots, and 6 archive thumbnails.
5. Fonts: Fraunces (must keep the SOFT variable axis, set to 100), Public Sans, JetBrains Mono — self-hosted or Google Fonts.
6. Contact CTAs are `mailto:hello@austinschuetz.com`. Leave nav/social hrefs as TODO constants I can fill in.
7. Add a sensible responsive pass (the design is desktop-only; keep the same hierarchy, stack the grids, use the clamp() type scale from `tokens.json`) — flag anything that needs a design decision rather than inventing new visuals.

Don't add sections, copy, or decorative elements that aren't in the spec.
