# NBBEU — Project Plan

**North Borneo Banking Executive Union** — Sistem keanggotaan & website organisasi
**Est. 2024** | Dokumen ini: v1 Draft

---

## 1. Ringkasan

Sistem untuk mengelola keanggotaan organisasi NBBEU, terdiri dari:
- **Public site**: landing page (info organisasi) + blog/postingan
- **Membership system**: pendaftaran → pembayaran → approval admin
- **Admin panel**: kelola ahli, org chart, kad ahli, sertifikat, import/export data

Target skala awal: **100–1.000 ahli**.

---

## 2. Keputusan Teknis (Confirmed)

| Aspek | Keputusan | Rasional Singkat |
|---|---|---|
| Tech stack | **Laravel + MySQL + Filament** | SDK Toyyibpay tersedia, admin panel CRUD-heavy cepat dibangun, hosting murah, cocok skala 100-1000 |
| Payment gateway | **Toyyibpay** | Target user Malaysia |
| Alur payment | **Bayar dulu → admin approve** | Filter applicant serius, cash flow lebih cepat |
| Role | **Admin (2, akses setara)** + **Users/Ahli** | Skala kecil, tidak perlu hierarki admin |
| Status ahli | `pending → approved / rejected` | State machine sederhana |
| Formulir daftar | Minimal: nama, email, no. HP, perusahaan | Kurangi friksi pendaftaran |
| Org chart | Statis, admin kelola manual (drag & drop) | Fleksibel untuk pengurus non-ahli |
| Akun Gmail/Google | Institutional account untuk daftar third-party service (Toyyibpay dll) | Operasional saja, bukan fitur sistem |

---

## 3. Modul Sistem

1. **Landing Page** — info organisasi, visi-misi, statistik keanggotaan
2. **Blog/Postingan** — berita & pengumuman, dikelola admin via Filament
3. **Membership** — form daftar → payment → status pending → admin review
4. **Admin: Organisation Chart** — kelola struktur kepengurusan (statis)
5. **Admin: Kad Ahli** — generate otomatis saat approved (PDF + QR verifikasi)
6. **Admin: Certificate** — generate otomatis saat approved (PDF)
7. **Admin: Import Excel** — bulk input data ahli, dengan laporan error per baris
8. **Admin: Export Excel** — laporan data ahli sesuai filter

Detail tiap modul: lihat `FEATURES.md`
Struktur database lengkap: lihat `DATABASE.md`
Timeline & milestone: lihat `ROADMAP.md`

---

## 4. Risiko Utama

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Webhook Toyyibpay gagal/telat | Status payment nyangkut di `pending` | Cek status manual via API sebagai fallback + admin bisa override manual |
| User bayar tapi ditolak admin | Dispute/komplain | Kebijakan jelas di T&C saat daftar (non-refund/application fee) — lihat §Open Questions |
| Import Excel data kotor | Data ahli salah/duplikat | Validasi ketat per baris, laporan error, tidak silent-fail |
| QR kad ahli bisa ditebak | Privasi/pemalsuan | `qr_token` random (bukan `user_id` mentah), endpoint verifikasi expose data minimal |
| Renewal tidak ke-track | Ahli expired tanpa notifikasi | Scheduled job cek `renewal_expires_at`, kirim reminder H-30/H-7 |

---

## 5. Open Questions (belum final)

- [ ] Kebijakan refund/non-refund kalau ditolak setelah bayar
- [ ] Durasi keanggotaan: 1 tahun dari approval, atau tahun kalender tetap?
- [ ] Format `member_no`: reset per tahun atau running number permanen?
- [ ] Desain visual kad ahli & sertifikat — sudah ada referensi atau perlu didesain dari branding logo?
- [ ] Konten landing page — sudah ada copy (visi-misi, sejarah) atau perlu dibuatkan draft?
- [ ] Apakah user yang `rejected` boleh daftar ulang dengan email sama?

---

## 6. Dokumen Terkait

- `DATABASE.md` — schema lengkap + SQL + rasional desain
- `FEATURES.md` — breakdown fitur per modul + user flow
- `ROADMAP.md` — timeline pengerjaan per fase
