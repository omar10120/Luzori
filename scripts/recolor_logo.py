from PIL import Image, ImageFilter
from pathlib import Path

src = Path("public/assets/img/branding/luzori-logo-source.png")
out = Path("public/assets/img/branding/luzori-logo-beige.png")

img = Image.open(src).convert("RGBA")
# Upscale first for cleaner sidebar rendering
img = img.resize((img.width * 6, img.height * 6), Image.Resampling.LANCZOS)

pixels = img.load()
w, h = img.size

beige = (236, 220, 190)  # soft beige
beige_deep = (210, 180, 140)

for y in range(h):
    for x in range(w):
        r, g, b, a = pixels[x, y]
        # Near-black / very dark -> transparent
        if r + g + b < 90:
            pixels[x, y] = (0, 0, 0, 0)
            continue

        lum = (0.299 * r + 0.587 * g + 0.114 * b)
        t = max(0.0, min(1.0, (lum - 45) / 160.0))
        nr = int(beige_deep[0] * (1 - t) + beige[0] * t)
        ng = int(beige_deep[1] * (1 - t) + beige[1] * t)
        nb = int(beige_deep[2] * (1 - t) + beige[2] * t)

        # Alpha from how "present" the pixel is vs background
        strength = min(1.0, max(0.0, (r + g + b - 60) / 220.0))
        na = int(255 * (0.35 + 0.65 * strength))
        if lum > 90:
            na = max(na, 230)
        pixels[x, y] = (nr, ng, nb, na)

# Slight soften for upscaled edges
img = img.filter(ImageFilter.SMOOTH_MORE)
img.save(out, "PNG")
print(f"saved {out} {img.size}")
