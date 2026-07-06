#!/usr/bin/env python3
"""Generate subtle paper-grain noise tiles for the portfolio site.

Outputs (to assets/img/):
  grain-light.png  256x256 gray+alpha, black speckle, mean alpha ~6/255  (light surfaces)
  grain-dark.png   256x256 gray+alpha, white speckle, mean alpha ~10/255 (dark green panels)

Usage:  python scripts/make_grain.py
Deps:   pip install numpy pillow
"""

import os

import numpy as np
from PIL import Image

SIZE = 256
OUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "assets", "img")


def make_tile(speckle_gray, mean_alpha, speckle_alpha, seed):
    """Sparse per-pixel speckle: each pixel independently gets alpha
    `speckle_alpha` with probability mean_alpha/speckle_alpha, else 0,
    so the tile's average alpha is ~mean_alpha.

    Independent per-pixel draws mean no visible pattern when tiled, and the
    two-level alpha keeps the PNG down to a few KB (continuous random alpha
    is incompressible and produced ~50KB files).
    """
    rng = np.random.default_rng(seed)
    on = rng.random((SIZE, SIZE)) < (mean_alpha / speckle_alpha)
    la = np.zeros((SIZE, SIZE, 2), dtype=np.uint8)
    la[..., 0] = speckle_gray
    la[..., 1] = np.where(on, speckle_alpha, 0).astype(np.uint8)
    return Image.fromarray(la, mode="LA")


def main():
    os.makedirs(OUT_DIR, exist_ok=True)
    jobs = [
        # name, speckle gray, target mean alpha, per-speckle alpha, seed
        ("grain-light.png", 0, 6, 26, 42),     # black speckle on light paper
        ("grain-dark.png", 255, 10, 30, 7),    # white speckle on dark panels
    ]
    for name, gray, mean_a, spk_a, seed in jobs:
        img = make_tile(gray, mean_a, spk_a, seed)
        path = os.path.join(OUT_DIR, name)
        img.save(path, "PNG", optimize=True)
        arr = np.asarray(img)
        print(f"{name}: {os.path.getsize(path)} bytes, mean alpha {arr[..., 1].mean():.2f}/255")


if __name__ == "__main__":
    main()
