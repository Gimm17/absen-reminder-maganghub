"""
Generate PWA icons untuk Reminder Absen MagangHub.

Output:
  public/icons/icon-192.png    app icon (large)
  public/icons/icon-512.png    app icon (splash / store)
  public/icons/icon-96.png     badge (status bar Android)
  public/icons/icon-72.png     badge fallback (legacy)

Catatan badge: Android men-auto-mask badge jadi SILUET MONOKROM.
Badge harus putih di atas transparan, tanpa background solid — kalau
dikasih background opaque, hasilnya jadi kotak putih penuh di status bar.
Badge juga TIDAK boleh dipakai untuk `icon` (ikon utama), beda perlakuan.

Brand: bg=#0f172a (slate-900), accent=#1d4ed8 (brand-700, sesuai design system).
"""
import math
import os
from PIL import Image, ImageDraw, ImageFont

OUT_DIR = os.path.join(os.path.dirname(__file__), "public", "icons")
os.makedirs(OUT_DIR, exist_ok=True)

BG = (15, 23, 42, 255)          # slate-900
ACCENT = (29, 78, 216, 255)     # brand-700 (#1d4ed8)
WHITE = (255, 255, 255, 255)
SOFT = (219, 234, 254, 255)     # blue-100


def _load_font(size: int):
    for name in ("arialbd.ttf", "Arial Bold.ttf", "DejaVuSans-Bold.ttf"):
        try:
            return ImageFont.truetype(name, size)
        except OSError:
            continue
    return ImageFont.load_default()


def draw_clock(img: Image.Image, size: int, ring: bool = True, label: bool = True) -> None:
    """Gambar jam analog sederhana. Dipakai ulang oleh icon & badge."""
    draw = ImageDraw.Draw(img)
    cx = cy = size // 2

    if ring:
        pad = int(size * 0.08)
        draw.ellipse([pad, pad, size - pad, size - pad], fill=ACCENT)

    radius = size * 0.30
    hand_w = max(2, size // 32)

    # Jarum jam ke arah 10, jarum menit ke arah 2.
    draw.line([(cx, cy), (cx - radius * 0.6, cy - radius * 0.4)], fill=WHITE, width=hand_w)
    draw.line([(cx, cy), (cx + radius * 0.8, cy - radius * 0.5)], fill=WHITE, width=max(2, size // 40))

    dot_r = max(2, size // 32)
    draw.ellipse([cx - dot_r, cy - dot_r, cx + dot_r, cy + dot_r], fill=SOFT if ring else WHITE)

    # Tanda jam di 12/3/6/9
    tick_len = radius * 0.18
    for angle in (0, 90, 180, 270):
        a = math.radians(angle - 90)
        draw.line(
            [
                (cx + math.cos(a) * (radius - tick_len), cy + math.sin(a) * (radius - tick_len)),
                (cx + math.cos(a) * radius, cy + math.sin(a) * radius),
            ],
            fill=WHITE,
            width=max(1, size // 48),
        )

    if not label:
        return

    font = _load_font(int(size * 0.14))
    text = "ABSEN"
    bbox = draw.textbbox((0, 0), text, font=font)
    tx = (size - (bbox[2] - bbox[0])) // 2
    ty = int(size * 0.78)

    # Outline tipis supaya terbaca di atas accent color.
    for dx, dy in ((-1, 0), (1, 0), (0, -1), (0, 1)):
        draw.text((tx + dx, ty + dy), text, font=font, fill=(0, 0, 0, 200))
    draw.text((tx, ty), text, font=font, fill=WHITE)


def make_app_icon(size: int) -> Image.Image:
    """Icon biasa ('purpose: any') — konten boleh mendekati tepi."""
    img = Image.new("RGBA", (size, size), BG)
    draw_clock(img, size, ring=True, label=True)
    return img


def make_maskable_icon(size: int) -> Image.Image:
    """
    Icon maskable — Android memotongnya sesuai bentuk launcher
    (lingkaran/squircle). Safe zone = lingkaran 80% diameter di tengah,
    jadi SEMUA konten harus masuk ke dalam 80% itu. Background harus
    full-bleed supaya tidak ada sudut transparan yang terlihat.
    """
    img = Image.new("RGBA", (size, size), BG)
    draw = ImageDraw.Draw(img)

    # Gambar jam pada kanvas kecil, lalu tempel di tengah (dengan margin aman).
    inner = int(size * 0.78)          # 78% < 80% safe zone
    tile = Image.new("RGBA", (inner, inner), (0, 0, 0, 0))
    draw_clock(tile, inner, ring=True, label=True)

    off = (size - inner) // 2
    img.paste(tile, (off, off), tile)
    return img


def make_badge(size: int) -> Image.Image:
    """
    Badge monokrom: siluet SOLID putih di atas transparan.

    Badge di status bar Android ditampilkan ~24dp. Versi garis tipis
    (2% piksel) praktis tak terlihat, jadi pakai bentuk padat yang
    terbaca jelas: cakram penuh + jarum jam di-KNOCKOUT (transparan).
    Tanpa teks — teks hilang saat di-mask.
    """
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)

    cx = cy = size // 2
    r = size * 0.46

    # Cakram putih solid
    draw.ellipse([cx - r, cy - r, cx + r, cy + r], fill=WHITE)

    # Jarum jam transparan (knockout) supaya siluetnya terbaca sebagai jam
    hr = r * 0.62
    hand_w = max(2, int(size * 0.10))
    draw.line([(cx, cy), (cx - hr * 0.55, cy - hr * 0.45)], fill=(0, 0, 0, 0), width=hand_w)
    draw.line([(cx, cy), (cx + hr * 0.80, cy - hr * 0.45)], fill=(0, 0, 0, 0), width=hand_w)

    # Titik tengah transparan
    dr = max(1, int(size * 0.07))
    draw.ellipse([cx - dr, cy - dr, cx + dr, cy + dr], fill=(0, 0, 0, 0))

    return img


# --- App icons ('any') ---
for sz in (192, 512):
    out = os.path.join(OUT_DIR, f"icon-{sz}.png")
    make_app_icon(sz).save(out, "PNG", optimize=True)
    print(f"Wrote {out} ({os.path.getsize(out)} bytes)")

# --- Maskable icon (adaptive launcher) ---
out = os.path.join(OUT_DIR, "icon-maskable-512.png")
make_maskable_icon(512).save(out, "PNG", optimize=True)
print(f"Wrote {out} ({os.path.getsize(out)} bytes)  [maskable]")

# --- Badge icons (status bar Android) ---
for sz in (72, 96):
    out = os.path.join(OUT_DIR, f"icon-{sz}.png")
    make_badge(sz).save(out, "PNG", optimize=True)
    print(f"Wrote {out} ({os.path.getsize(out)} bytes)  [badge, monokrom]")
