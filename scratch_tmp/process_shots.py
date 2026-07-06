import os
from PIL import Image

SRC = r"C:\shots"
DEST = r"C:\Users\ausi4.AUSILAPTOP\austin-schuetz-portfolio\media\uploads\projects"

# (src_filename, project_slug, out_name, max_width)
JOBS = [
    ("ausi-trader-dashboard.png", "ausi-trader", "dashboard", 1600),
    ("equity-curve.png", "ausi-trader", "equity-curve", 1600),
    ("koda-desktop.png", "koda-ironview", "dashboard", 1600),
    ("koda-mobile.png", "koda-ironview", "mobile-home", 800),
    ("grocery-desktop.png", "we-have-food-at-home", "desktop-home", 1600),
    ("grocery-mobile.png", "we-have-food-at-home", "mobile-home", 800),
    ("billing-desktop2.png", "tally", "desktop-login", 1600),
    ("billing-mobile.png", "tally", "mobile-login", 800),
    ("dwr-desktop.png", "draft-war-room", "draft-board", 1600),
    ("bush-char-green.png", "bush-league-baseball", "character-green", 1600),
    ("bush-char-red.png", "bush-league-baseball", "character-red", 1600),
    ("cms-editor.png", "this-site", "editor", 1600),
]

# thumb source per project: (project_slug, src_filename)
THUMBS = [
    ("ausi-trader", "ausi-trader-dashboard.png"),
    ("koda-ironview", "koda-mobile.png"),
    ("we-have-food-at-home", "grocery-desktop.png"),
    ("tally", "billing-desktop2.png"),
    ("draft-war-room", "dwr-desktop.png"),
    ("bush-league-baseball", "bush-char-green.png"),
    ("this-site", "cms-editor.png"),
]

def save_webp(im, path, quality=82):
    if im.mode in ("RGBA", "P"):
        im = im.convert("RGBA")
    else:
        im = im.convert("RGB")
    im.save(path, "WEBP", quality=quality, method=6)

def resize_max_width(im, max_w):
    if im.width <= max_w:
        return im
    ratio = max_w / im.width
    new_h = round(im.height * ratio)
    return im.resize((max_w, new_h), Image.LANCZOS)

os.makedirs(DEST, exist_ok=True)

for src_name, slug, out_name, max_w in JOBS:
    src_path = os.path.join(SRC, src_name)
    if not os.path.exists(src_path):
        print("MISSING", src_path)
        continue
    im = Image.open(src_path)
    im = resize_max_width(im, max_w)
    out_dir = os.path.join(DEST, slug)
    os.makedirs(out_dir, exist_ok=True)
    out_path = os.path.join(out_dir, out_name + ".webp")
    save_webp(im, out_path)
    print("wrote", out_path, im.size)

for slug, src_name in THUMBS:
    src_path = os.path.join(SRC, src_name)
    if not os.path.exists(src_path):
        print("MISSING thumb src", src_path)
        continue
    im = Image.open(src_path)
    im = resize_max_width(im, 480)
    out_dir = os.path.join(DEST, slug)
    os.makedirs(out_dir, exist_ok=True)
    out_path = os.path.join(out_dir, "thumb.webp")
    save_webp(im, out_path)
    print("wrote", out_path, im.size)
