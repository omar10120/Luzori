from PIL import Image
from pathlib import Path

src = Path(
    r"C:\Users\Dell\.cursor\projects\c-Users-Dell-Desktop-luzori-dash-Luzori"
    r"\assets\c__Users_Dell_AppData_Roaming_Cursor_User_workspaceStorage_"
    r"091e9b613fb8ac7a6bf38290127a4c4f_images_background-b8edd8f4-0159-46ae-a134-8b49fd2d31d4.png"
)
out_dir = Path("public/assets/img/branding")
out_dir.mkdir(parents=True, exist_ok=True)
out = out_dir / "corner-leaf-beige.png"

img = Image.open(src).convert("RGBA")
pixels = img.load()
w, h = img.size

for y in range(h):
    for x in range(w):
        r, g, b, a = pixels[x, y]
        # Pure / near black -> transparent
        if r < 40 and g < 40 and b < 40:
            pixels[x, y] = (0, 0, 0, 0)
            continue
        # Soften very dark edges into transparency
        lum = (r + g + b) / 3.0
        if lum < 55:
            pixels[x, y] = (0, 0, 0, 0)
            continue
        # Keep beige tones, boost alpha for visible fronds
        na = min(255, int(a * (0.55 + 0.45 * min(1.0, (lum - 55) / 160.0))))
        if lum > 120:
            na = max(na, 200)
        pixels[x, y] = (r, g, b, na)

img.save(out, "PNG")
print(f"saved {out} {img.size}")
