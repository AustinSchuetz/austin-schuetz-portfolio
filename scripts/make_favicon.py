"""Layered-peaks favicon set (supersedes the AS-monogram favicon from make_og.py).

Draws the three-ridge mark used in the site header onto an evergreen
rounded square: favicon.ico (16/32/48) at repo root + apple-touch-icon.png.
Run: python scripts/make_favicon.py
"""
from PIL import Image, ImageDraw

GREEN_800 = (27, 61, 47)
BACK = (95, 158, 130)     # green-400
MID = (193, 221, 206)     # green-200
FRONT = (250, 249, 247)   # stone-50


def draw_mark(size: int, rounded: bool) -> Image.Image:
    s = size / 64  # design coordinates are on a 64px grid
    im = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    d = ImageDraw.Draw(im)
    radius = int(size * 0.2) if rounded else 0
    d.rounded_rectangle([0, 0, size - 1, size - 1], radius=radius, fill=GREEN_800)
    d.polygon([(8 * s, 46 * s), (22 * s, 22 * s), (34 * s, 46 * s)], fill=BACK)
    d.polygon([(20 * s, 46 * s), (34 * s, 15 * s), (49 * s, 46 * s)], fill=MID)
    d.polygon([(34 * s, 46 * s), (45 * s, 27 * s), (57 * s, 46 * s)], fill=FRONT)
    return im


def main() -> None:
    # supersample for crisp small sizes
    big = draw_mark(256, rounded=True)
    sizes = [(16, 16), (32, 32), (48, 48)]
    big.save("favicon.ico", sizes=sizes)
    draw_mark(180, rounded=False).convert("RGB").save("assets/img/apple-touch-icon.png")
    print("favicon.ico + apple-touch-icon.png written")


if __name__ == "__main__":
    main()
