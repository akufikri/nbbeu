# NBBEU — Feature Breakdown

---

## 1. Landing Page (Public)

- Hero section: nama organisasi, tagline, CTA "Daftar Jadi Ahli"
- Tentang: visi-misi, sejarah singkat, "Est. 2024"
- Org chart preview (ambil dari tabel `org_chart`)
- Statistik: jumlah ahli aktif (opsional, bisa ditampilkan sebagai counter)
- Blog terbaru (3-4 postingan terakhir)
- Footer: kontak, sosial media

*Referensi struktur: consilium.europa.eu — formal, institutional, informasi terstruktur per section.*

---

## 2. Blog / Postingan (Public + Admin)

**Public**:
- List postingan (paginated), filter by kategori (opsional)
- Detail postingan

**Admin (Filament)**:
- CRUD post: title, slug (auto-generate dari title), excerpt, content (rich text), cover image
- Status: draft / published
- Preview sebelum publish

---

## 3. Membership — Pendaftaran (Public)

**User Flow**:
```
1. Isi form: nama, email, no. HP, perusahaan
2. Validasi otomatis (format email, no. HP, duplikat email)
3. Redirect ke Toyyibpay untuk bayar
4. Setelah bayar sukses → status: pending, payment: paid
5. Tampilkan halaman "Menunggu review admin"
6. User bisa cek status pendaftaran via email/link unik
```

**Yang perlu di-handle**:
- Kalau user close browser sebelum bayar → payment status `pending`, bisa retry dari link email
- Kalau bayar gagal → payment status `failed`, user bisa coba lagi
- Duplikat email yang sudah `approved` → tolak, tampilkan pesan "sudah terdaftar"
- Duplikat email yang `rejected` → *(lihat Open Questions di PLAN.md — perlu keputusan)*

---

## 4. Membership — Admin Review

- List semua pendaftar dengan filter status (`pending`/`approved`/`rejected`)
- Detail per pendaftar: data diri + status payment
- Aksi: **Approve** / **Reject** (+ catatan alasan opsional untuk reject)
- Saat approve → trigger otomatis: generate `member_no`, kad ahli, sertifikat, kirim email

---

## 5. Admin: Organisation Chart

- CRUD: nama, jawatan, foto, urutan tampil
- Drag & drop reorder (Filament punya built-in sortable)
- Toggle aktif/nonaktif (tanpa hapus data historis)

---

## 6. Admin: Kad Ahli (Member Card)

- Setting template kad (upload background/design sekali, field auto-isi: nama, no. ahli, foto, QR)
- Auto-generate saat status approved
- Regenerate manual (kalau ada typo/perlu update)
- Preview & download PDF/image
- QR code → link ke halaman verifikasi publik (`/verify/{qr_token}`)

---

## 7. Admin: Certificate

- Setting template sertifikat (mirip kad ahli)
- Auto-generate saat approved
- Regenerate manual
- Download PDF

---

## 8. Admin: Import by Excel

- Upload file `.xlsx`/`.csv`
- Mapping kolom → field (`name`, `email`, `phone`, `company`)
- Preview sebelum commit (tampilkan baris yang akan error, mis. email invalid/duplikat)
- Proses: baris valid masuk dengan status `pending` (tetap perlu approval, konsisten dengan alur normal — kecuali admin secara eksplisit pilih opsi "langsung approved" untuk data migrasi ahli lama)
- Laporan hasil: X berhasil, Y gagal + alasan per baris

---

## 9. Admin: Export to Excel

- Filter: status, rentang tanggal daftar, status renewal
- Export kolom: nama, email, no. HP, perusahaan, no. ahli, status, tanggal approve, tanggal expired
- Format `.xlsx`

---

## 10. Renewal (perlu durasi keanggotaan dikonfirmasi dulu)

- Scheduled job harian: cek `renewal_expires_at`
- H-30 & H-7: kirim email reminder
- H+0 (expired): status tetap `approved` tapi flag terpisah `is_expired` (jangan ubah `status` ke selain approved, supaya histori tidak hilang) — atau tambah status `expired` di enum, tergantung kebutuhan laporan
- User renewal: bayar lagi via Toyyibpay → `renewal_expires_at` diperpanjang otomatis, **tidak perlu approval ulang** (sudah disepakati)

---

## 11. Member Area (Users/Ahli, setelah login)

- Lihat status pendaftaran (kalau masih pending)
- Download kad ahli & sertifikat (kalau approved)
- Update data profil (nama, no. HP, perusahaan)
- Lihat tanggal expired & tombol renewal
