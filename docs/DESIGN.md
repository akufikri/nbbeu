# NBBEU — Design Tokens (Landing Page)

Palet & tipografi diturunkan dari logo resmi (navy + teal handshake/globe), bukan dibuat dari nol — supaya landing page konsisten dengan identitas visual yang sudah ada.

---

## 1. Warna

| Nama | Hex | Peran |
|---|---|---|
| NBBEU Navy | `#16305C` | Primary — heading, tombol utama, header/nav |
| Deep Navy | `#0B1D3A` | Footer, section gelap, hover state navy |
| Teal (handshake) | `#18AFBF` | Secondary/aksen — link, ikon, highlight interaktif |
| Executive Gold | `#B08D3D` | Aksen prestise — badge "Est. 2024", no. ahli, elemen sertifikasi/kad, hover CTA. **Pakai terbatas**, jangan jadi warna dominan |
| Background | `#F6F7F9` | Latar halaman — abu-biru dingin, bukan cream, menjaga kesan formal |
| Ink (teks) | `#232A33` | Warna teks body — charcoal ber-tint navy, bukan hitam pekat |

**Aturan pakai**:
- Navy = otoritas/struktur (heading, nav, footer)
- Teal = interaksi/koneksi (sesuai makna handshake di logo — link, tombol sekunder, ikon)
- Gold = eksklusivitas/pencapaian (badge keanggotaan, sertifikat, elemen yang menandakan status "sah/terverifikasi") — kalau dipakai berlebihan, kesannya jadi murahan, jadi batasi ke elemen kecil saja
- Kontras teks: teks di atas Navy/Deep Navy pakai putih; teks di atas Teal/Gold pakai Deep Navy (bukan putih, karena kontrasnya lebih baik dan lebih tenang secara visual)

---

## 2. Tipografi

| Peran | Font | Sumber | Catatan |
|---|---|---|---|
| Display (headline) | **Spectral** | Google Fonts | Serif institusional, dipakai TERBATAS di headline/hero saja, weight 600 |
| Body | **IBM Plex Sans** | Google Fonts | Teks utama, paragraf, navigasi, form |
| Utility/data | **IBM Plex Mono** | Google Fonts | No. ahli, tanggal, statistik, elemen bernuansa "data resmi" |

**Google Fonts import**:
```html
<link href="https://fonts.googleapis.com/css2?family=Spectral:wght@400;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
```

**Type scale (rekomendasi)**:
| Elemen | Font | Size | Weight |
|---|---|---|---|
| Hero headline | Spectral | 48-56px (mobile: 32px) | 600 |
| Section heading | Spectral | 28-32px | 600 |
| Body text | IBM Plex Sans | 16px | 400 |
| Small/caption | IBM Plex Sans | 13-14px | 400 |
| Data/nomor (mis. no. ahli) | IBM Plex Mono | 14-16px | 500 |

---

## 3. Layout Signature

Motif **garis lengkung (arc)** dari logo (bentuk globe/handshake) dipakai sebagai elemen pembatas antar-section — bukan garis lurus/divider standar. Contoh implementasi:
- SVG arc tipis (1-2px, warna teal atau navy dengan opacity rendah) sebagai transisi antar-section
- Bisa juga jadi elemen dekoratif di belakang hero section (subtle, jangan ramai)
- Konsisten dipakai di beberapa titik supaya jadi "signature" — bukan cuma sekali pakai

---

## 4. Implementasi (Tailwind CSS — cocok dengan stack Laravel)

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        'nbbeu-navy': '#16305C',
        'nbbeu-navy-deep': '#0B1D3A',
        'nbbeu-teal': '#18AFBF',
        'nbbeu-gold': '#B08D3D',
        'nbbeu-bg': '#F6F7F9',
        'nbbeu-ink': '#232A33',
      },
      fontFamily: {
        display: ['Spectral', 'serif'],
        sans: ['"IBM Plex Sans"', 'sans-serif'],
        mono: ['"IBM Plex Mono"', 'monospace'],
      },
    },
  },
}
```

---

## 5. Contoh Pemakaian per Elemen

| Elemen UI | Warna | Font |
|---|---|---|
| Navbar background | Navy | Plex Sans (menu items) |
| Hero headline | Ink di atas Background | Spectral 600 |
| Tombol "Daftar Jadi Ahli" | Background Navy, teks putih; hover → Gold | Plex Sans 500 |
| Badge "Est. 2024" | Background Gold-tint, teks Deep Navy | Plex Mono |
| Link/nav aktif | Teal | Plex Sans |
| Footer | Background Deep Navy, teks putih/abu muda | Plex Sans |
| No. ahli di kad/sertifikat | Teal atau Ink | Plex Mono 500 |
