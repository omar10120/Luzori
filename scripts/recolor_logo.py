from PIL import Image, ImageFilter
from pathlib import Path

src = Path("public/assets/img/branding/luzori-logo-source.png")
out = Path("public/assets/img/branding/luzori-logo-beige.png")

if not src.exists():
    # recreate from original upload if source missing
    alt = Path(
        r"C:\Users\Dell\.cursor\projects\c-Users-Dell-Desktop-luzori-dash-Luzori"
        r"\assets\c__Users_Dell_AppData_Roaming_Cursor_User_workspaceStorage_"
        r"091e9b613fb8ac7a6bf38290127a4c4f_images_logo-4b289108-be90-4520-a845-9d2c752f2503.png"
    )
    if alt.exists():
        src.parent.mkdir(parents=True, exist_ok=True)
        Image.open(alt).save(src)

img = Image.open(src).convert("RGBA")
img = img.resize((img.width * 8, img.height * 8), Image.Resampling.LANCZOS)
pixels = img.load()
w, h = img.size

beige = (236, 220, 190)
beige_deep = (210, 180, 140)

for y in range(h):
    for x in range(w):
        r, g, b, a = pixels[x, y]
        # Aggressive black/near-black removal
        if max(r, g, b) < 55 or (r + g + b) < 120:
            pixels[x, y] = (0, 0, 0, 0)
            continue
        lum = 0.299 * r + 0.587 * g + 0.114 * b
        t = max(0.0, min(1.0, (lum - 50) / 150.0))
        nr = int(beige_deep[0] * (1 - t) + beige[0] * t)
        ng = int(beige_deep[1] * (1 - t) + beige[1] * t)
        nb = int(beige_deep[2] * (1 - t) + beige[2] * t)
        na = 255 if lum > 80 else int(max(0, min(255, (lum - 50) * 6)))
        pixels[x, y] = (nr, ng, nb, na)

img = img.filter(ImageFilter.SMOOTH)
img.save(out, "PNG")
print(f"saved {out} {img.size}")
