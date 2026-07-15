# NBBEU — Technical Requirements Document (TRD)

| | |
|---|---|
| **Produk** | Sistem Website & Keanggotaan NBBEU |
| **Versi Dokumen** | v1.0 |
| **Status** | Draft |
| **Terkait** | `PRD.md`, `DATABASE.md`, `FEATURES.md`, `ROADMAP.md`, `DESIGN.md` |

---

## 1. Ringkasan Arsitektur

```
┌─────────────┐      ┌──────────────────────────────┐      ┌─────────────┐
│   Public    │      │         Laravel App           │      │  Toyyibpay  │
│  (Browser)  │◄────►│  - Web routes (Blade/Livewire) │◄────►│  (Payment)  │
└─────────────┘      │  - Filament Admin Panel        │      └─────────────┘
                      │  - Queue Worker (jobs)         │
┌─────────────┐      │  - Scheduler (renewal reminder)│      ┌─────────────┐
│    Admin    │◄────►│                                 │─────►│    Mail     │
│  (Browser)  │      └──────────────┬─────────────────┘      │  (SMTP)     │
└─────────────┘                     │                          └─────────────┘
                                     ▼
                            ┌─────────────────┐
                            │  MySQL Database  │
                            └─────────────────┘
                                     │
                                     ▼
                            ┌─────────────────┐
                            │  File Storage    │
                            │  (kad/sertifikat)│
                            └─────────────────┘
```

Monolith Laravel — tidak perlu microservices di skala 100-1.000 ahli. Filament berjalan di aplikasi Laravel yang sama (bukan service terpisah), menghemat kompleksitas deployment.

---

## 2. Tech Stack

| Layer | Pilihan | Versi Rekomendasi |
|---|---|---|
| Backend framework | Laravel | 11.x (LTS terbaru saat mulai development — cek versi stabil terkini) |
| Admin panel | Filament | 3.x |
| Database | MySQL | 8.0+ |
| Frontend (public) | Blade + Tailwind CSS | — |
| Interaktivitas ringan | Livewire atau Alpine.js | Livewire untuk form kompleks, Alpine untuk interaksi kecil |
| Queue | Laravel Queue (database driver cukup untuk skala ini; Redis kalau tersedia) | — |
| Scheduler | Laravel Task Scheduler (cron) | — |
| PDF generation | `barryvdh/laravel-dompdf` atau `spatie/browsershot` (kalau butuh render HTML kompleks utk kad/sertifikat) | — |
| Excel import/export | `maatwebsite/excel` | 3.x |
| Payment | Toyyibpay REST API (tidak ada official SDK Laravel resmi — pakai HTTP client Laravel `Http::` langsung, atau package komunitas kalau terpercaya) | — |
| Audit log | `spatie/laravel-activitylog` | — |
| Font (frontend) | Google Fonts: Spectral, IBM Plex Sans, IBM Plex Mono | Lihat `DESIGN.md` |

**Catatan stack**: hindari nambah service terpisah (mis. Node.js backend, microservice search) — semua kebutuhan v1 bisa dipenuhi dalam satu Laravel monolith. Ini keputusan sadar untuk menjaga biaya hosting & maintenance rendah sesuai skala target.

---

## 3. Modul Teknis & Implementasi

### 3.1 Autentikasi
- Laravel built-in auth (`laravel/breeze` atau `laravel/fortify`, minimal setup) untuk member login
- Filament punya auth sendiri untuk admin panel (`/admin` route group, guard terpisah dari member)
- **Dua guard terpisah**: `web` (member) dan `admin` (Filament) — supaya tidak ada kebocoran akses lintas panel

### 3.2 Alur Payment (Toyyibpay)

```
1. User submit form daftar
   → Validasi server-side (unique email, format phone)
   → Simpan users (status: pending, belum ada payment)
   → Simpan payments (status: pending)

2. Redirect ke Toyyibpay
   → createBill() via HTTP POST ke Toyyibpay API
   → Simpan toyyibpay_bill_code ke tabel payments

3. User bayar di Toyyibpay
   → Toyyibpay redirect balik ke return_url (UI feedback ke user)
   → Toyyibpay callback ke callback_url (server-to-server, SUMBER KEBENARAN status)

4. Callback handler (route terpisah, TIDAK butuh auth, tapi WAJIB verifikasi signature/checksum dari Toyyibpay)
   → Update payments.status = paid
   → Trigger event PaymentConfirmed (TIDAK langsung approve user — tetap pending sampai admin review)

5. Fallback (WAJIB, jangan andalkan callback 100%):
   → Scheduled job tiap 15-30 menit: cek payments dengan status=pending yang usianya > 1 jam
   → Query status langsung ke Toyyibpay API (getBillTransactions)
   → Update status kalau ternyata sudah paid tapi callback gagal masuk
```

**Keamanan penting**: endpoint callback HARUS memverifikasi request benar-benar dari Toyyibpay (signature/secret key sesuai dokumentasi Toyyibpay), jangan trust request tanpa validasi — kalau tidak, siapapun bisa hit endpoint itu dan memalsukan status paid.

### 3.3 Event-Driven Approval Flow

```php
// Konsep, bukan kode final
Event: UserApproved (dispatched saat admin klik approve di Filament)

Listeners (queued, urut):
1. GenerateMemberNumber::class
2. GenerateMemberCard::class   // buat qr_token, render PDF via dompdf/browsershot
3. GenerateCertificate::class  // render PDF dari template
4. SendApprovalEmail::class    // kirim email + attachment
```

Semua listener di-queue (`ShouldQueue`), bukan synchronous — supaya klik "approve" di admin panel tidak lambat menunggu PDF generation selesai. Perlu queue worker jalan (`php artisan queue:work`) di server, disupervisi dengan Supervisor/systemd.

### 3.4 QR Verification Endpoint

```
Route: GET /verify/{qr_token}   (public, no auth)

Response:
- qr_token valid & user approved & belum expired → tampilkan: nama, member_no, status "Sah", foto (opsional)
- qr_token valid tapi expired → tampilkan status "Expired"
- qr_token tidak ditemukan → 404 generic (jangan bocorkan info lain)

PENTING: endpoint ini TIDAK boleh return email, no. HP, atau data pribadi lain.
Rate limiting wajib (mis. 60 request/menit per IP) untuk cegah scraping massal.
```

### 3.5 Import Excel

- Package: `maatwebsite/excel`, gunakan `WithValidation` + `WithBatchInserts` + `SkipsOnError`
- Alur: upload → parse ke memory → jalankan validasi per baris → tampilkan preview (baris valid vs invalid) → admin konfirmasi → commit baris valid saja
- Baris invalid disimpan sementara (session/cache) untuk ditampilkan sebagai laporan, bukan langsung dibuang

### 3.6 Renewal Job

```
Scheduled (harian, via Laravel Scheduler):
1. Query users WHERE renewal_expires_at BETWEEN today AND today+30 AND belum dikirim reminder H-30
   → kirim email reminder, catat sudah dikirim (kolom tambahan atau tabel log)
2. Query users WHERE renewal_expires_at = today+7 AND belum dikirim reminder H-7
   → kirim email reminder
3. (Opsional) Query users WHERE renewal_expires_at < today AND status masih approved
   → update flag/status sesuai keputusan bisnis (lihat Open Question di DATABASE.md)
```

---

## 4. Keamanan (Security Requirements)

| Area | Requirement |
|---|---|
| Password | Hash via bcrypt (default Laravel), minimal policy (8+ karakter) |
| Payment callback | Verifikasi signature/secret dari Toyyibpay, reject kalau tidak valid |
| QR verification endpoint | Data minimal exposed, rate limiting |
| File upload (import Excel, foto) | Validasi tipe file & size, scan ekstensi (jangan trust MIME type dari client saja) |
| Admin panel | Guard terpisah, 2FA opsional (Filament mendukung), session timeout wajar |
| CSRF | Default Laravel protection aktif di semua form |
| Rate limiting | Login attempts, form pendaftaran (cegah spam), endpoint verifikasi publik |
| Data pribadi | Tidak ada endpoint publik yang expose email/no.HP/data sensitif tanpa auth |
| Backup | Database backup harian otomatis (mis. `spatie/laravel-backup`), retensi minimal 30 hari |

---

## 5. Infrastruktur & Deployment

| Aspek | Rekomendasi |
|---|---|
| Hosting | Shared/VPS hosting yang support PHP 8.2+, MySQL 8 — tidak perlu cloud enterprise (Laravel Forge + DigitalOcean/Vultr adalah kombinasi umum & terjangkau) |
| Storage file | Local disk cukup di awal (kad/sertifikat/foto); pertimbangkan S3-compatible object storage kalau volume besar/butuh CDN |
| Queue worker | Perlu proses long-running (Supervisor) — pastikan hosting mendukung (bukan shared hosting murni tanpa akses proses) |
| SSL | Wajib (Let's Encrypt gratis) |
| Environment | `.env` terpisah staging/production, jangan commit credential ke repo |
| CI/CD | Minimal: deploy manual via git pull + `artisan migrate` di awal; otomatisasi (GitHub Actions) bisa ditambah belakangan, tidak blocking MVP |
| Domain email | Institutional Gmail/Google Workspace untuk akun terkait service (Toyyibpay, hosting) — operasional, di luar scope kode aplikasi |

---

## 6. Observability

| Aspek | Tools |
|---|---|
| Error tracking | Laravel log (`storage/logs`) minimal; pertimbangkan Sentry kalau budget izin |
| Audit trail | `spatie/laravel-activitylog` — approve/reject, publish post, dll |
| Payment reconciliation | Log semua request/response ke Toyyibpay API (untuk debug dispute) |

---

## 7. Testing Strategy

| Level | Fokus |
|---|---|
| Unit test | Logic generate `member_no`, validasi form |
| Feature test | Alur pendaftaran end-to-end (form → payment mock → status), approval flow |
| Manual QA | Integrasi Toyyibpay real (sandbox mode kalau tersedia), generate PDF kad/sertifikat (cek visual) |

---

## 8. Dependency Checklist (composer packages)

```
laravel/framework ^11.0
filament/filament ^3.0
laravel/breeze (atau fortify)
maatwebsite/excel ^3.1
barryvdh/laravel-dompdf (atau spatie/browsershot)
spatie/laravel-activitylog
spatie/laravel-backup
```

---

## 9. Technical Risks

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Toyyibpay tidak punya SDK Laravel resmi | Effort integrasi lebih manual | Gunakan `Http::` client Laravel langsung ke REST API, dokumentasikan endpoint yang dipakai |
| PDF generation lambat untuk render kompleks (kad dengan foto/QR) | Approval terasa lambat kalau synchronous | Wajib queue, bukan synchronous |
| Webhook payment tidak reliable | Status payment nyangkut | Fallback scheduled job cek status manual (lihat §3.2) |
| Queue worker mati tanpa disadari | Approval "macet" (dokumen tidak ter-generate) | Monitoring sederhana (mis. healthcheck endpoint atau alert kalau job menumpuk) |

---

## 10. Referensi Dokumen Lain

- `DATABASE.md` — schema lengkap, SQL, ERD
- `FEATURES.md` — detail fitur per modul
- `PRD.md` — requirement bisnis & user stories
- `ROADMAP.md` — timeline implementasi
- `DESIGN.md` — token warna & tipografi frontend
