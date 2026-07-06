#!/usr/bin/env python
"""
process_archive_images.py

Processes raw screenshots/thumbnails (in media/uploads/archive/_raw/) into the
final archive assets used by the site:

    media/uploads/archive/<slug>.webp        (1200x900, 4:3, top-crop, q=80)
    media/uploads/archive/<slug>_thumb.webp  (480w, same crop, q=80)

Re-runnable: always re-derives outputs from _raw/<slug>.png.

Usage (from repo root):
    python scripts/process_archive_images.py
"""
import os
from PIL import Image

REPO_ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
RAW_DIR = os.path.join(REPO_ROOT, "media", "uploads", "archive", "_raw")
OUT_DIR = os.path.join(REPO_ROOT, "media", "uploads", "archive")

TARGET_W, TARGET_H = 1200, 900  # 4:3
THUMB_W = 480
THUMB_H = int(THUMB_W * TARGET_H / TARGET_W)  # 360
QUALITY = 80

# slug -> raw filename (without extension assumed .png) in _raw/
SLUG_SOURCES = {
    "koda-crossfit-gyms": "koda-crossfit-gyms",
    "account-media": "account-media",
    "newlake-capital": "newlake-capital",
    "lanai": "lanai",
    "geneva-glass-works": "geneva-glass-works",
    # "contelligent": no usable source image (live demo subdomain broken,
    # no surviving wayback thumbnail or page snapshot) -- skipped.
    "crossfit-sdg": "crossfit-sdg-fresh",
    "sphera": "sphera",
    "crossfit-1055": "crossfit-1055",
    "koda-competitor": "koda-competitor",
    "oneplus-systems": "oneplus-systems",
    "taylor-dilk": "taylor-dilk",
    "tamara-edwards": "tamara-edwards",
    "rivo": "rivo",
    "koda-crossfit-tulsa": "koda-crossfit-tulsa",
    "vms-professionals": "vms-professionals",
    "erin-schuetz": "erin-schuetz",
    "bearson": "bearson",
    "todays-jam": "todays-jam",
    "attune-collective": "attune-collective",
    "ground-creations": "ground-creations",
    "integrate": "integrate",
    "conexiom": "conexiom",
    "sequoia": "sequoia",
    "sequoia-one": "sequoia-one",
}


def top_crop_to_ratio(im: Image.Image, target_w: int, target_h: int) -> Image.Image:
    """Resize (cover) then crop from the top so the top of the screenshot
    (the part visitors actually look at) is preserved."""
    src_w, src_h = im.size
    target_ratio = target_w / target_h
    src_ratio = src_w / src_h

    if src_ratio > target_ratio:
        # source is wider than target: scale to match height, crop sides (centered)
        scale = target_h / src_h
        new_w, new_h = round(src_w * scale), target_h
        im = im.resize((new_w, new_h), Image.LANCZOS)
        left = (new_w - target_w) // 2
        im = im.crop((left, 0, left + target_w, target_h))
    else:
        # source is taller than target (typical full-page screenshot):
        # scale to match width, crop from the TOP down.
        scale = target_w / src_w
        new_w, new_h = target_w, round(src_h * scale)
        im = im.resize((new_w, new_h), Image.LANCZOS)
        im = im.crop((0, 0, target_w, target_h))
    return im


def process_one(slug: str, raw_name: str) -> None:
    raw_path = os.path.join(RAW_DIR, raw_name + ".png")
    if not os.path.isfile(raw_path):
        print(f"  MISSING raw file for {slug}: {raw_path}")
        return

    im = Image.open(raw_path).convert("RGB")

    full = top_crop_to_ratio(im, TARGET_W, TARGET_H)
    full_out = os.path.join(OUT_DIR, f"{slug}.webp")
    full.save(full_out, "WEBP", quality=QUALITY)

    thumb = full.resize((THUMB_W, THUMB_H), Image.LANCZOS)
    thumb_out = os.path.join(OUT_DIR, f"{slug}_thumb.webp")
    thumb.save(thumb_out, "WEBP", quality=QUALITY)

    print(f"  {slug}: {full_out} + {thumb_out}")


def main():
    os.makedirs(OUT_DIR, exist_ok=True)
    print(f"Processing {len(SLUG_SOURCES)} archive entries...")
    for slug, raw_name in SLUG_SOURCES.items():
        process_one(slug, raw_name)
    print("Done.")


if __name__ == "__main__":
    main()
