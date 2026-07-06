# Handoff: austinschuetz.com — Portfolio Site

## Overview
Portfolio website for **Austin Schuetz**, a freelance front-end web developer in Denver, CO who also works as a technical partner for startups integrating AI (Claude API expertise). Three artifacts are designed: the **Home page**, one **project case-study page** (template for all case studies), and a **style-guide reference**.

Identity: *"National-park poster meets engineering notebook."* Colorado mountains meet engineering rigor — warm, papery, precise. **Light theme only.** No dark mode, no gradients, no glassmorphism.

## About the Design Files
The files in this bundle are **design references created in HTML** — prototypes showing intended look and behavior, **not production code to copy directly**. The task is to recreate these designs in the target codebase's existing environment using its established patterns and libraries — or, if no environment exists yet, choose the most appropriate framework (a static-friendly stack like Astro/Next.js suits this site well) and implement the designs there.

Notes on the reference file:
- `Austin Schuetz Portfolio.dc.html` renders all three artifacts on one canvas, labeled **1a** (Home), **1b** (Case study), **1c** (Style guide). It depends on a proprietary runtime and will not render standalone — read it for markup structure and exact inline style values.
- The browser window and iPhone frames around project screenshots are **presentation chrome from the mock**, not part of the site. In production, project imagery is plain screenshots styled per the notes below (a simple browser-chrome bar around the dashboard screenshot is optional flavor, not required).
- `<image-slot>` elements are drop targets for real images in the mock. In production these are `<img>` elements.
- All project/client content (Meridian, Ascent, archive clients, stats) is real portfolio content supplied by Austin — copy is final unless he says otherwise.

## Fidelity
**High-fidelity.** Colors, typography, spacing, and copy are final. Recreate pixel-perfectly (desktop 1440px reference width; responsive behavior is not designed yet — see Open items).

## Design Tokens
`tokens.json` in this bundle is the **canonical token file** (supplied by Austin — treat as source of truth). Summary:

- **Green (evergreen)**: 50 `#F0F7F3` · 100 `#E0EEE6` · 200 `#C1DDCE` · 300 `#8FBEA8` · 400 `#5F9E82` · 500 `#3B8266` · 600 `#2F6B52` · 700 `#24523F` · **800 `#1B3D2F` (primary)** · 900 `#132E23` · 950 `#0C1F17`
- **Stone (warm neutrals)**: 50 `#FAF9F7` (paper) · 100 `#F4F1EB` (paper deep) · 200 `#E8E4DD` (hairlines) · 300 `#D6D0C6` · 400 `#A8A196` · **500 `#78716A` (secondary text)** · 600 `#57534B` · **700 `#443F38` (body text)** · 800 `#2E2A25` · 900 `#201D19`
- **Amber (trail blaze)**: 100 `#FBEED7` · 300 `#FCD34D` · 500 `#D97706` · **600 `#B45309` (links/CTAs)** · **700 `#92400E` (hover)**
  - **Rule: amber is wayfinding only** — links, CTAs, index numerals ("01/02/03"), blaze marks. Never backgrounds, never decoration.
- **Radius**: sm 6px (buttons, cards) · md 10px · lg 16px · pill 999px. Mock uses 4px on chips/swatches, 6px on buttons/cards.
- **Shadows** (from tokens.json): xs/sm/md/lift — warm-black based, e.g. md = `0 2px 4px rgb(32 29 25 / 0.05), 0 10px 24px rgb(32 29 25 / 0.08)`
- **Spacing**: 0.25 / 0.5 / 0.75 / 1 / 1.5 / 2 / 3 / 4 / 6 / 8 rem
- **Motion**: ease-soft `cubic-bezier(0.22, 1, 0.36, 1)`; durations 120 / 240 / 480 ms. Hover transitions use 240ms ease-soft.

### Typography
- **Fraunces** (Google Fonts, variable) — h1/h2/h3 and hero lines. Always `font-variation-settings: 'SOFT' 100`. Weights: 480 display / 520 em / 560 headings. Letter-spacing −0.01em on display sizes. Load axes: `ital, opsz 9..144, wght 300..700, SOFT 0..100`.
- **Public Sans** (Google Fonts) — body & UI. Weights 400/500/600. Body 15–19px, line-height 1.65–1.75, color stone-700.
- **JetBrains Mono** (Google Fonts) — UPPERCASE eyebrow labels, stat numerals, tags/chips, footer meta. Weights 400/500. Tracking 0.06–0.16em (wider = smaller text).
- Type scale (tokens.json `type`): body/lg/h4/h3/h2/h1/display as CSS clamp() values — use these for responsive sizing.

## Brand Motifs (strict hierarchy)
1. **Paper grain** — barely visible, on ALL surfaces. `assets/grain.svg` (240×240 feTurbulence tile, stone-tinted, ≤7% alpha), tiled as a full-page overlay: `position:fixed/absolute; inset:0; pointer-events:none; z-index` above content, `background-size:240px 240px`.
2. **Topographic contours** — `assets/topo.svg` (evergreen strokes, 1px @ 7% opacity) **only behind the hero**, and `assets/topo-light.svg` (paper strokes @ 10%) **only inside the deep-green contact/footer band**. `background-size:cover; center`. Nowhere else.
3. **Ridgeline dividers** — full-width inline SVGs (`viewBox 0 0 1440 100`, `preserveAspectRatio="none"`, height 84–92px), 3 stacked jagged paths in green tints + a 4th "ground" path filled with the NEXT section's background so sections merge seamlessly. Light dividers: green-100 → 200 → 300, ground white. Dark divider (into contact band): green-300 → 500 → 700, ground green-800. **Max 3 per page.** Exact path data is in the reference HTML; three approved silhouettes (RIDGE-A alpine / RIDGE-B rolling / RIDGE-C foothills) are in artifact 1c.
4. **Duotone photo treatment** — portrait and client-archive thumbnails only. CSS recipe from the mock (no image pre-processing needed):
   ```css
   .duotone { position:relative; isolation:isolate; background:#F4F1EB; }
   .duotone img { mix-blend-mode:multiply; filter:grayscale(1) contrast(0.95) brightness(1.05); }
   .duotone::after { content:''; position:absolute; inset:0; background:#1B3D2F; mix-blend-mode:color; pointer-events:none; }
   ```
Rules: **photos never sit over topo lines; max two motifs per view** (grain + one other).

## Screens / Views

### 1a — Home (1440px reference)
Top to bottom:

1. **Header** — white, `padding:24px 64px`, bottom hairline stone-200. Left: wordmark "Austin Schuetz" (Fraunces 600/21px, green-800) + "DENVER, CO" (mono 10.5px, 0.12em, stone-500). Right nav: Work / Services / About (Public Sans 500/14px, stone-700, gap 32px) + "Start a project →" (600/14px, amber-600, hover amber-700).
2. **Hero** — bg stone-50, `padding:108px 64px 116px`, max-width 980px, topo.svg behind (cover/center). Eyebrow row: 9×16px amber blaze rectangle (radius 2) + `DENVER, CO — 39.74° N` (mono 12px, 0.16em, stone-500). H1 Fraunces 480/74px/1.05, green-800, "technical partner" in italic 520: *"Freelance front-end developer — and technical partner for startups shipping AI."* Intro paragraph 19px/1.65 stone-700 (max 620px). CTA row (gap 28, margin-top 42): primary button "Start a project" (amber-600 bg, white, 600/15px, padding 14px 26px, radius 6, hover amber-700) + underlined text link "See selected work ↓" (amber-600, underline offset 4px). Availability line (margin-top 48): 8px amber dot + `CURRENTLY BOOKING NEW PROJECTS` (mono 11px).
3. **Ridgeline divider 1** (light, ground = white).
4. **What I do** — white, `padding:88px 64px 96px`. Eyebrow `WHAT I DO`. 3-column grid (hairline left borders between columns, 40px column padding). Each column: amber mono index (01/02/03) → Fraunces 560/24px title → 15px body → tag chips. Titles/tags: "Front-end & WordPress builds" [REACT, TYPESCRIPT, WORDPRESS]; "Full-stack product engineering" [NODE, POSTGRES, FASTAPI]; "AI & Claude integration partnership" [CLAUDE API, RAG, EVALS]. Chip style: mono 10.5px 0.08em stone-700, bg stone-50, hairline border, radius 4, padding 5px 9px.
5. **Selected work** — bg stone-100, top hairline, `padding:92px 64px`. Eyebrow `SELECTED WORK — 2024–26`. Two project rows (grid, 64px gap, 96px between rows):
   - **Meridian** (screenshot left ~648px, text right): chips `148K LOC` / `LIVE IB EXECUTION` / `PYTHON + REACT` (white bg chips), Fraunces 560/31px title "Meridian — automated trading platform", 15.5px body, amber link "Read the case study →" (→ case-study page).
   - **Ascent** (text left, phone screenshot right ~302px): chips `REACT NATIVE` / `OFFLINE-FIRST` / `6-WEEK MVP`, title "Ascent — training log for a Denver gym", body, amber case-study link.
6. **Ridgeline divider 2** (light, ground = white).
7. **Client archive** — white, `padding:84px 64px 92px`. Header row: eyebrow `CLIENT ARCHIVE — 2014–2026` left, amber link "Full archive →" right. 6-column grid (gap 24) of duotone thumbnails (138px tall, hairline border, radius 4) with two-line mono captions (10px, 0.08em, stone-500), e.g. `01 — BOULDER OUTFITTERS / WORDPRESS` … `06 — TRAILHEAD ROASTERS / SHOPIFY`.
8. **About** — bg stone-50, top hairline, `padding:84px 64px`. Duotone portrait 250×315 (radius 6) left; right: eyebrow `BEHIND THE WORK`, Fraunces 560/30px "Engineering notebook on the desk, trail map on the wall.", 15.5px body, mono meta line `AUSTIN SCHUETZ — DENVER, CO · WORKING WITH TEAMS IN EVERY TIMEZONE`.
9. **Ridgeline divider 3** (dark variant, ground = green-800).
10. **Contact band** — bg green-800 with topo-light.svg. `padding:92px 64px 0`, max-width 860px. Amber blaze + eyebrow `CONTACT — DENVER, CO` (green-300 text). H2 Fraunces 480/48px stone-50: "Have a build in mind — or an AI feature that needs to ship for real?" Body 16.5px green-200. CTAs: amber button `hello@austinschuetz.com` (mono 500/14.5px) + underlined paper link "Book a 20-minute intro call". Footer meta bar (margin-top 76, top border `rgba(250,249,247,0.16)`, padding 24px 64px): mono 10.5px — `© 2026 AUSTIN SCHUETZ` · `39.74° N, 104.99° W — DENVER, COLORADO` · GITHUB / LINKEDIN / EMAIL (paper color).

### 1b — Case study: Meridian (template for all case studies)
1. Same header as Home.
2. **Title block** — white, `padding:88px 64px 0`. Breadcrumb: `← ALL WORK` (amber link) `/ CASE STUDY 01 — 2024–26` (mono, stone-500). H1 Fraunces 480/58px/1.08 "Meridian — a live trading system built to be trusted." Intro 18px (max 680px).
3. **Stat row** — 4 equal cells, top+bottom hairlines, hairline separators; numeral JetBrains Mono 500/36px green-800, label mono 10.5px 0.12em stone-500 below: `148K / LINES OF CODE`, `45 MS / ORDER ROUND-TRIP`, `24/7 / LIVE IB EXECUTION`, `18 MO / ENGAGEMENT TO DATE`.
4. **Screenshot band** — bg stone-100, hairlines top/bottom, `padding:72px 64px`, full-width dashboard screenshot (~1312px wide).
5. **Body** — white, grid `1fr 320px`, gap 80:
   - **Main** (max 660px): Fraunces 560/30px section heads "The brief" / "The approach" / "The outcome" with 16px/1.75 paragraphs (copy is in the reference file). Under "The approach", a checklist of 4 mono items (12px, 0.1em) each led by an 8×8 amber square (radius 2): backtests share live code path; fail-closed risk checks; event-sourced order log; Claude reviews overnight runs. Ends with amber link "Request a code walkthrough →".
   - **Facts sidebar**: bg stone-50, hairline border, radius 6, padding 30px 28px. Mono 10px labels (CLIENT / ROLE / TIMELINE / STACK) with 14.5px values: "Private trading principal", "Sole engineer — design to deploy", "2024 → ongoing"; STACK as chips (PYTHON, FASTAPI, REACT, TYPESCRIPT, POSTGRES, IB API, CLAUDE API). Divider, then `NEXT PROJECT` label + amber link "Ascent — gym training app →".
6. **Ridgeline divider** (dark variant) into a **compact contact band**: green-800 + topo-light, Fraunces 480/38px "Building something that has to work every day?" + amber email button; slim footer meta row.

### 1c — Style guide (internal reference, not a public page)
Documents: color scales (with usage annotations), three type specimens with spec labels, the three ridgeline divider variants, and the six motif rules. Implement as needed (e.g. a Storybook page or `/styleguide` route) — it defines the rules, it isn't itself a required page.

## Interactions & Behavior
- **Links/CTAs**: amber-600 → amber-700 on hover, `transition: 240ms cubic-bezier(0.22,1,0.36,1)`. Text links use `text-decoration: underline; text-underline-offset: 3–4px; text-decoration-thickness: 1px`.
- **Buttons**: solid amber-600, white text, radius 6, padding 14px 26px; hover darkens to amber-700 (no lift/scale).
- Nav links, "Full archive", GitHub/LinkedIn, "Book a 20-minute intro call", "Request a code walkthrough" need real destinations (Austin to supply). `hello@austinschuetz.com` buttons are `mailto:` links.
- Case-study cross-links: Home Meridian row → `/work/meridian`; case-study breadcrumb → Home; NEXT PROJECT → next case study.
- No scroll animations designed. If any are added, keep them subtle and use the motion tokens.

## State Management
None — fully static site. No forms in v1 (contact is mailto).

## Assets
- `assets/grain.svg` — paper-grain tile (motif 1). Ship as-is.
- `assets/topo.svg` / `assets/topo-light.svg` — topographic contours for hero / dark band (motif 2). Ship as-is.
- Ridgeline dividers — inline SVG path data in the reference HTML (three variants shown in artifact 1c).
- Fonts — Fraunces, Public Sans, JetBrains Mono via Google Fonts (self-host for production if preferred; Fraunces must keep the SOFT axis).
- **Needed from Austin**: portrait photo, Meridian dashboard + research screenshots, Ascent app screen, 6 client-archive thumbnails. The duotone CSS handles all color treatment — supply ordinary photos/screenshots.

## Files
- `Austin Schuetz Portfolio.dc.html` — all three artifacts (1a Home, 1b case study, 1c style guide); source of truth for exact copy, inline styles, and ridgeline path data
- `tokens.json` — canonical design tokens
- `assets/` — grain + topo SVGs

## Open items (not designed yet)
- Responsive/mobile layouts (reference is 1440px desktop)
- Services & About as standalone pages (nav implies them; only Home sections exist)
- Full client-archive page
- Ascent case study (reuse the 1b template)
