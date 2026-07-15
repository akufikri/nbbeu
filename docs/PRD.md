# NBBEU — Product Requirements Document (PRD)

| | |
|---|---|
| **Product** | Sistem Website & Keanggotaan NBBEU |
| **Versi Dokumen** | v1.0 |
| **Status** | Draft |
| **Terkait** | `TRD.md`, `DATABASE.md`, `FEATURES.md`, `ROADMAP.md`, `DESIGN.md` |

---

## 1. Latar Belakang & Tujuan

**North Borneo Banking Executive Union (NBBEU)**, berdiri 2024, membutuhkan platform digital untuk:
1. Mempresentasikan organisasi secara publik (kredibilitas, visi-misi, struktur kepengurusan)
2. Mengelola proses keanggotaan end-to-end — dari pendaftaran, pembayaran, approval, hingga penerbitan kad ahli & sertifikat
3. Memberi tim admin alat operasional (kelola konten, data ahli, laporan) tanpa bergantung pada developer untuk operasional harian

**Masalah yang diselesaikan**: saat ini tidak ada sistem terpusat — proses keanggotaan manual (kemungkinan via WhatsApp/Excel/tatap muka), rawan human error, sulit di-scale ke 100-1.000 ahli, dan tidak ada validasi/dokumentasi resmi yang konsisten (kad ahli, sertifikat).

---

## 2. Target Pengguna

| Persona | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Calon Ahli** | Eksekutif bank yang ingin bergabung | Proses daftar cepat, jelas statusnya, bukti keanggotaan sah |
| **Ahli Aktif** | Sudah approved | Akses kad/sertifikat, info organisasi, renewal mudah |
| **Admin (2 orang, akses setara)** | Pengurus/staf operasional NBBEU | Kelola pendaftar, konten, laporan tanpa perlu bantuan teknis |
| **Pengunjung Publik** | Calon partner, media, publik umum | Info kredibel tentang organisasi |

---

## 3. Lingkup (Scope)

### 3.1 Termasuk (In Scope — v1)
- Landing page publik
- Blog/postingan
- Pendaftaran keanggotaan dengan pembayaran (Toyyibpay)
- Alur approval admin (pending/approved/rejected)
- Generate otomatis kad ahli (PDF + QR verifikasi) & sertifikat saat approved
- Admin panel: org chart, import/export Excel, kelola ahli
- Member area sederhana (lihat status, download dokumen, update profil)
- Renewal keanggotaan tahunan

### 3.2 Tidak Termasuk (Out of Scope — v1)
- Aplikasi mobile native
- Payment gateway selain Toyyibpay
- Sistem forum/diskusi antar-ahli
- Event management / ticketing
- Multi-bahasa (asumsi: 1 bahasa utama dulu, kecuali dikonfirmasi lain)
- Notifikasi WhatsApp (hanya email di v1)

---

## 4. User Stories & Acceptance Criteria

### 4.1 Pendaftaran Ahli

**US-01**: Sebagai calon ahli, saya ingin mendaftar dengan mengisi data minimal (nama, email, no. HP, perusahaan) supaya prosesnya cepat.
- AC: Form menolak submit kalau field wajib kosong atau email tidak valid
- AC: Email yang sudah `approved` tidak bisa daftar ulang (pesan error jelas)

**US-02**: Sebagai calon ahli, saya ingin membayar sebelum aplikasi saya diproses, sesuai kebijakan organisasi.
- AC: Setelah submit form, user diarahkan ke Toyyibpay
- AC: Setelah bayar sukses, status berubah ke `pending` dan user melihat halaman konfirmasi
- AC: Kalau bayar gagal/batal, user bisa retry tanpa isi form ulang

**US-03**: Sebagai calon ahli, saya ingin tahu status aplikasi saya tanpa harus menghubungi admin.
- AC: Ada halaman/link cek status via email

### 4.2 Admin Review

**US-04**: Sebagai admin, saya ingin melihat daftar semua pendaftar dan status pembayarannya, supaya saya bisa memutuskan approve/reject dengan informasi lengkap.
- AC: List bisa difilter by status
- AC: Detail pendaftar menampilkan data diri + status payment (bukan cuma status aplikasi)

**US-05**: Sebagai admin, saya ingin approve/reject pendaftar, dan sistem otomatis menangani sisanya (nomor ahli, kad, sertifikat, email).
- AC: Approve → `member_no` di-generate, kad & sertifikat otomatis dibuat, email terkirim ke ahli
- AC: Reject → status berubah, opsional catatan alasan, tidak ada dokumen yang di-generate

### 4.3 Kad Ahli & Sertifikat

**US-06**: Sebagai ahli yang approved, saya ingin punya kad digital yang bisa diverifikasi pihak lain (mis. saat menghadiri acara bank).
- AC: Kad memiliki QR code yang mengarah ke halaman verifikasi publik
- AC: Halaman verifikasi menampilkan status sah/tidak, tanpa membocorkan data pribadi lengkap

### 4.4 Admin — Konten & Data

**US-07**: Sebagai admin, saya ingin publish artikel/pengumuman tanpa bantuan developer.
- AC: CRUD post dengan status draft/published

**US-08**: Sebagai admin, saya ingin import data ahli lama secara massal dari Excel.
- AC: Sistem menampilkan preview sebelum commit
- AC: Baris gagal ditampilkan dengan alasan spesifik, tidak silent-fail

**US-09**: Sebagai admin, saya ingin export data ahli untuk laporan ke pihak internal/eksternal.
- AC: Export bisa difilter (status, tanggal, dll), hasil file `.xlsx`

### 4.5 Renewal

**US-10**: Sebagai ahli, saya ingin diingatkan sebelum keanggotaan saya expired.
- AC: Email reminder terkirim otomatis H-30 dan H-7
- AC: Renewal cukup bayar ulang, tidak perlu approval ulang admin

---

## 5. Non-Functional Requirements (ringkas — detail teknis di `TRD.md`)

| Kategori | Requirement |
|---|---|
| Performa | Landing page & blog load < 3 detik pada koneksi standar |
| Skalabilitas | Sanggup menangani 100-1.000 ahli tanpa perubahan arsitektur |
| Keamanan | Data pribadi ahli tidak boleh exposed di endpoint publik (termasuk QR verifikasi) |
| Reliabilitas | Kegagalan webhook payment tidak boleh membuat status "hilang" — harus ada fallback cek manual |
| Auditability | Setiap aksi approve/reject admin tercatat (siapa, kapan) |
| Aksesibilitas budget | Hosting & infrastruktur harus terjangkau (bukan enterprise-scale) |

---

## 6. Metrik Sukses (Success Metrics)

| Metrik | Target Awal |
|---|---|
| Waktu proses admin per aplikasi (review sampai approve) | < 5 menit (berkat automasi generate dokumen) |
| Tingkat kegagalan payment yang tidak ter-track | 0% (semua tercermin di sistem, baik lewat webhook maupun fallback) |
| Waktu load landing page | < 3 detik |
| Ahli yang renewal tepat waktu (tidak lewat H+30 dari expired) | > 80% (berkat reminder otomatis) |

---

## 7. Risiko Produk

| Risiko | Mitigasi |
|---|---|
| User bayar tapi ditolak → komplain | Kebijakan T&C jelas saat pendaftaran (lihat Open Question) |
| Admin salah approve/reject (human error) | Audit log + kemampuan admin lain untuk review histori |
| Data ahli lama (import Excel) tidak lengkap/kotor | Validasi ketat, laporan error granular, bukan auto-reject total |

---

## 8. Open Questions (Bisnis — perlu keputusan stakeholder)

- [ ] Kebijakan refund/non-refund kalau ditolak setelah bayar
- [ ] Durasi keanggotaan: 1 tahun dari approval, atau tahun kalender tetap (semua expired 31 Des)?
- [ ] Apakah email yang `rejected` boleh daftar ulang?
- [ ] Konten landing page: sudah ada copy resmi (visi-misi, sejarah) atau perlu dibuatkan draft?

---

## 9. Fase Rilis

| Fase | Fokus | Referensi |
|---|---|---|
| MVP (v1) | Semua US-01 s/d US-10 di atas | `ROADMAP.md` Fase 1-6 |
| v1.1 (potensial) | Notifikasi WhatsApp, multi-bahasa | Belum direncanakan detail |
