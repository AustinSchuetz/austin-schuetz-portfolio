#!/usr/bin/env python3
"""Generate Open Graph share images for the portfolio.

Outputs 1200x630 PNGs (<=300KB each) into media/uploads/og/, one per id in
PAGES below. Re-running is safe/idempotent -- font conversion is cached and
images are deterministic (fixed RNG seed for the grain overlay).

Layout:
  - #FAF9F7 paper background
  - subtle topographic accent (concentric irregular ellipses) top-right,
    stroked #1B3D2F at ~8% alpha -- drawn with pure PIL, no external assets
  - amber (#D97706) blaze rectangle + mono uppercase kicker line
  - Fraunces display title, up to 2 lines, #1B3D2F
  - bottom: 8px full-width #1B3D2F baseline bar + mono footer line above it
  - faint uniform noise overlay for paper-grain feel

Also generates the favicon set (re-runnable, deterministic):
  favicon.ico               (repo root, 16+32+48 multi-size)
  assets/img/icon.svg        (hand-written SVG, same design)
  assets/img/apple-touch-icon.png (180x180, square -- iOS applies its own mask)

Usage:  python scripts/make_og.py
Deps:   pip install pillow fonttools brotli
"""

import math
import os
import random

from fontTools.ttLib import TTFont
from PIL import Image, ImageDraw, ImageFont

# ---------------------------------------------------------------------------
# Paths

ROOT = os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)), ".."))
FONT_DIR = os.path.join(ROOT, "assets", "fonts")
CACHE_DIR = os.path.join(ROOT, "media", "uploads", "og", "_fonts")
OUT_DIR = os.path.join(ROOT, "media", "uploads", "og")

ICON_DIR = os.path.join(ROOT, "assets", "img")
FAVICON_ICO = os.path.join(ROOT, "favicon.ico")
ICON_SVG = os.path.join(ICON_DIR, "icon.svg")
APPLE_TOUCH_ICON = os.path.join(ICON_DIR, "apple-touch-icon.png")

FRAUNCES_WOFF2 = os.path.join(FONT_DIR, "fraunces-var.woff2")
MONO_WOFF2 = os.path.join(FONT_DIR, "jetbrains-mono-400.woff2")

FRAUNCES_TTF = os.path.join(CACHE_DIR, "fraunces-var.ttf")
MONO_TTF = os.path.join(CACHE_DIR, "jetbrains-mono-400.ttf")

# ---------------------------------------------------------------------------
# Brand tokens (design/tokens.json)

PAPER = (250, 249, 247)        # stone-50 / #FAF9F7
EVERGREEN = (27, 61, 47)       # green-800 / #1B3D2F
STONE_500 = (120, 113, 106)    # #78716A
AMBER_500 = (217, 118, 6)      # #D97706

W, H = 1200, 630

# ---------------------------------------------------------------------------
# Pages / OG copy

PAGES = [
    ("og-default", "AUSTIN SCHUETZ — DENVER, CO", "Freelance front-end developer & AI technical partner."),
    ("home", "AUSTIN SCHUETZ — DENVER, CO", "Freelance front-end developer & AI technical partner."),
    ("work", "SELECTED WORK", "Things built and shipped."),
    ("archive", "CLIENT ARCHIVE — 2015–2026", "A decade of client work, preserved."),
    ("about", "BEHIND THE WORK", "Engineering notebook on the desk, trail map on the wall."),
    ("contact", "CONTACT — DENVER, CO", "Have a build in mind?"),
    ("style-guide", "STYLE GUIDE", "The design system, documented in public."),
    ("ausi-trader", "CASE STUDY — TRADING SYSTEM", "Ausi-Trader: engineering rigor with real money on the line."),
    ("koda-ironview", "CASE STUDY — GYM PLATFORM", "Koda IronView: from gym client to gym platform."),
    ("we-have-food-at-home", "CASE STUDY — CONSUMER AI", "We Have Food at Home: point a camera at your fridge, get dinner."),
    ("tally", "CASE STUDY — SAAS", "Tally: the boring plumbing clients pay for."),
    ("bush-league-baseball", "CASE STUDY — GAME DEV", "Bush League Baseball: design-system thinking, ported to game dev."),
    ("this-site", "THE PORTFOLIO IS THE PRODUCT", "A hand-built flat-file CMS, drag-and-drop included."),
    ("draft-war-room", "WEEKEND BUILD", "Dynasty Draft War Room: shipped before draft night."),
]


# ---------------------------------------------------------------------------
# Font conversion (woff2 -> ttf), cached


def ensure_ttf(woff2_path, ttf_path):
    os.makedirs(os.path.dirname(ttf_path), exist_ok=True)
    if os.path.exists(ttf_path) and os.path.getmtime(ttf_path) >= os.path.getmtime(woff2_path):
        return ttf_path
    font = TTFont(woff2_path)
    font.flavor = None
    font.save(ttf_path)
    return ttf_path


def load_fonts():
    fraunces_ttf = ensure_ttf(FRAUNCES_WOFF2, FRAUNCES_TTF)
    mono_ttf = ensure_ttf(MONO_WOFF2, MONO_TTF)
    return fraunces_ttf, mono_ttf


# ---------------------------------------------------------------------------
# Drawing helpers


def with_alpha(rgb, alpha):
    return (rgb[0], rgb[1], rgb[2], alpha)


def draw_topo_accent(base):
    """Concentric irregular ellipse outlines in the top-right corner."""
    layer = Image.new("RGBA", base.size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(layer)
    cx, cy = W - 120, 40
    rng = random.Random(1234)
    n = 6
    for i in range(n):
        rx = 160 + i * 70
        ry = 120 + i * 55
        # irregular ellipse: perturb a base ellipse's bounding box per-segment
        # by walking an approximate polygon with small radius jitter.
        points = []
        steps = 72
        for s in range(steps + 1):
            theta = (s / steps) * 2 * math.pi
            jitter = 1.0 + 0.045 * rng.uniform(-1, 1) * (1 + 0.3 * (i % 3))
            x = cx + rx * jitter * math.cos(theta)
            y = cy + ry * jitter * math.sin(theta)
            points.append((x, y))
        alpha = int(255 * 0.08)
        draw.line(points, fill=with_alpha(EVERGREEN, alpha), width=2, joint="curve")
    base.alpha_composite(layer)


def draw_noise(base, seed, amount=3):
    """Slight uniform noise overlay for paper-grain feel (~3 alpha)."""
    import numpy as np

    rng = np.random.default_rng(seed)
    w, h = base.size
    # Sparse per-pixel speckle keeps file size small (see make_grain.py).
    on = rng.random((h, w)) < 0.5
    speckle_alpha = amount * 2
    alpha = np.where(on, speckle_alpha, 0).astype(np.uint8)
    gray = np.where(rng.random((h, w)) < 0.5, 0, 255).astype(np.uint8)
    la = np.dstack([gray, alpha])
    noise_img = Image.fromarray(la, mode="LA").convert("RGBA")
    base.alpha_composite(noise_img)


def letterspace(text, n=1):
    sep = " " * n  # thin space
    return sep.join(list(text))


def greedy_wrap(draw, text, font, max_width):
    """Greedy word-wrap; returns as many lines as needed (no truncation)."""
    words = text.split()
    lines = []
    cur = ""
    for word in words:
        trial = (cur + " " + word).strip()
        bbox = draw.textbbox((0, 0), trial, font=font)
        if bbox[2] - bbox[0] <= max_width or not cur:
            cur = trial
        else:
            lines.append(cur)
            cur = word
    if cur:
        lines.append(cur)
    return lines


def wrap_title(draw, text, font_path, base_size, max_width, max_lines=2, min_size=40):
    """Wrap text to at most max_lines by shrinking the font size until it
    fits, rather than truncating/ellipsizing. Returns (lines, font)."""
    size = base_size
    while size >= min_size:
        font = ImageFont.truetype(font_path, size)
        lines = greedy_wrap(draw, text, font, max_width)
        if len(lines) <= max_lines:
            return lines, font
        size -= 2
    # fall back to the smallest size, greedy-wrapped to max_lines lines by
    # merging any overflow into the last line (very long titles only).
    font = ImageFont.truetype(font_path, min_size)
    lines = greedy_wrap(draw, text, font, max_width)
    if len(lines) > max_lines:
        keep = lines[:max_lines - 1]
        keep.append(" ".join(lines[max_lines - 1:]))
        lines = keep
    return lines, font


def make_og_image(kicker, title, fraunces_ttf, mono_ttf):
    img = Image.new("RGBA", (W, H), with_alpha(PAPER, 255))

    draw_topo_accent(img)

    margin = 88

    mono_kicker_font = ImageFont.truetype(mono_ttf, 22)
    mono_footer_font = ImageFont.truetype(mono_ttf, 20)

    draw = ImageDraw.Draw(img)

    # --- amber blaze + kicker ---------------------------------------------
    blaze_y = margin + 6
    draw.rectangle([margin, blaze_y, margin + 9, blaze_y + 16], fill=with_alpha(AMBER_500, 255))

    kicker_text = letterspace(kicker, 1)
    kicker_x = margin + 9 + 16
    draw.text((kicker_x, blaze_y - 3), kicker_text, font=mono_kicker_font, fill=with_alpha(STONE_500, 255))

    # --- title, wrapped to <=2 lines (auto-shrinks to fit, never clips) ----
    max_text_width = W - margin * 2
    lines, title_font = wrap_title(draw, title, fraunces_ttf, 68, max_text_width, max_lines=2)

    line_height = int(title_font.size * 1.12)
    title_top = 230
    for i, line in enumerate(lines):
        y = title_top + i * line_height
        draw.text((margin, y), line, font=title_font, fill=with_alpha(EVERGREEN, 255))

    # assert title fits within canvas bounds (pixel-level sanity check)
    for i, line in enumerate(lines):
        y = title_top + i * line_height
        bbox = draw.textbbox((margin, y), line, font=title_font)
        assert bbox[2] <= W - margin + 2, f"title line clipped on right: {line!r} bbox={bbox}"
        assert bbox[3] <= H - 60, f"title line clipped by bottom bar: {line!r} bbox={bbox}"

    # --- bottom bar: 8px baseline + mono footer line -----------------------
    bar_h = 8
    draw.rectangle([0, H - bar_h, W, H], fill=with_alpha(EVERGREEN, 255))

    footer_text = "AUSTINSCHUETZ.COM " + "—" + " DENVER, CO"
    footer_y = H - bar_h - 34
    draw.text((margin, footer_y), footer_text, font=mono_footer_font, fill=with_alpha(STONE_500, 255))

    # --- grain overlay -------------------------------------------------------
    draw_noise(img, seed=42, amount=3)

    return img.convert("RGB")


# ---------------------------------------------------------------------------
# Favicons: evergreen rounded-square, paper "AS" monogram, one thin contour
# arc through the lower third at low alpha.


def rounded_square_mask(size, radius_frac=0.20):
    mask = Image.new("L", (size, size), 0)
    d = ImageDraw.Draw(mask)
    radius = int(size * radius_frac)
    d.rounded_rectangle([0, 0, size - 1, size - 1], radius=radius, fill=255)
    return mask


def draw_contour_arc(draw, size, alpha_frac=0.16):
    """One thin contour arc through the lower third, low alpha paper stroke."""
    cx = size * 0.5
    cy = size * 1.05  # center below the canvas so the arc bows gently
    r = size * 0.62
    bbox = [cx - r, cy - r, cx + r, cy + r]
    alpha = int(255 * alpha_frac)
    width = max(1, round(size * 0.018))
    draw.arc(bbox, start=200, end=340, fill=with_alpha(PAPER, alpha), width=width)


def render_monogram_icon(size, fraunces_ttf, rounded=True):
    """Render the AS monogram icon at `size`px. If rounded, applies the
    rounded-square mask (favicon/svg use); apple-touch-icon stays square
    since iOS applies its own mask."""
    img = Image.new("RGBA", (size, size), with_alpha(EVERGREEN, 255))
    draw = ImageDraw.Draw(img)

    draw_contour_arc(draw, size)

    # Fit "AS" within ~62% of the icon width, vertically centered.
    target_w = size * 0.60
    font_size = int(size * 0.5)
    font = ImageFont.truetype(fraunces_ttf, font_size)
    bbox = draw.textbbox((0, 0), "AS", font=font)
    text_w = bbox[2] - bbox[0]
    while text_w > target_w and font_size > 4:
        font_size -= 1
        font = ImageFont.truetype(fraunces_ttf, font_size)
        bbox = draw.textbbox((0, 0), "AS", font=font)
        text_w = bbox[2] - bbox[0]
    text_h = bbox[3] - bbox[1]
    x = (size - text_w) / 2 - bbox[0]
    y = (size - text_h) / 2 - bbox[1]
    draw.text((x, y), "AS", font=font, fill=with_alpha(PAPER, 255))

    if rounded:
        mask = rounded_square_mask(size, 0.20)
        out = Image.new("RGBA", (size, size), (0, 0, 0, 0))
        out.paste(img, (0, 0), mask)
        return out
    return img


def make_favicon_ico(fraunces_ttf):
    sizes = [16, 32, 48]
    imgs = [render_monogram_icon(s, fraunces_ttf, rounded=True) for s in sizes]
    # Pillow writes a multi-size .ico from the largest image + sizes list.
    imgs[-1].save(FAVICON_ICO, format="ICO", sizes=[(s, s) for s in sizes])
    return os.path.getsize(FAVICON_ICO)


def make_apple_touch_icon(fraunces_ttf):
    img = render_monogram_icon(180, fraunces_ttf, rounded=False).convert("RGB")
    img.save(APPLE_TOUCH_ICON, "PNG", optimize=True)
    return os.path.getsize(APPLE_TOUCH_ICON)


def make_icon_svg():
    """Hand-written SVG: rounded rect + AS text + one contour arc path,
    matching render_monogram_icon's design at vector scale."""
    size = 64
    radius = round(size * 0.20, 1)
    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {size} {size}" role="img" aria-label="Austin Schuetz">
  <rect x="0" y="0" width="{size}" height="{size}" rx="{radius}" ry="{radius}" fill="#1B3D2F"/>
  <path d="M {size * 0.5 - size * 0.62:.1f} {size * 1.05 - size * 0.62 * 0.55:.1f} A {size * 0.62:.1f} {size * 0.62:.1f} 0 0 1 {size * 0.5 + size * 0.62:.1f} {size * 1.05 - size * 0.62 * 0.55:.1f}"
        fill="none" stroke="#FAF9F7" stroke-opacity="0.16" stroke-width="{max(1, round(size * 0.018, 1))}"/>
  <text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle"
        font-family="Fraunces, Georgia, serif" font-size="{size * 0.44:.1f}" fill="#FAF9F7">AS</text>
</svg>
'''
    with open(ICON_SVG, "w", encoding="utf-8") as f:
        f.write(svg)
    return os.path.getsize(ICON_SVG)


def main():
    os.makedirs(OUT_DIR, exist_ok=True)
    fraunces_ttf, mono_ttf = load_fonts()

    results = []
    for slug, kicker, title in PAGES:
        img = make_og_image(kicker, title, fraunces_ttf, mono_ttf)
        out_path = os.path.join(OUT_DIR, f"{slug}.png")
        img.save(out_path, "PNG", optimize=True)
        size = os.path.getsize(out_path)
        results.append((slug, size))
        status = "OK" if size <= 300 * 1024 else "TOO BIG"
        print(f"{slug:26s} {size:7d} bytes  [{status}]")

    over = [r for r in results if r[1] > 300 * 1024]
    if over:
        raise SystemExit(f"{len(over)} image(s) exceeded 300KB budget")

    print(f"\nGenerated {len(results)} OG images into {OUT_DIR}")

    print("\nFavicons:")
    ico_size = make_favicon_ico(fraunces_ttf)
    print(f"{'favicon.ico':26s} {ico_size:7d} bytes")
    apple_size = make_apple_touch_icon(fraunces_ttf)
    print(f"{'apple-touch-icon.png':26s} {apple_size:7d} bytes")
    svg_size = make_icon_svg()
    print(f"{'icon.svg':26s} {svg_size:7d} bytes")


if __name__ == "__main__":
    main()
