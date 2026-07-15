# NBBEU — Roadmap

Estimasi untuk 1 developer, skala 100-1.000 ahli. Sesuaikan kalau tim lebih dari 1 orang.

---

## Fase 1 — Foundation (1-2 minggu)

- [ ] Setup project Laravel + Filament
- [ ] Setup database sesuai `DATABASE.md`, jalankan migration
- [ ] Setup auth (login admin & member)
- [ ] Setup environment (staging/production), akun Gmail institusional untuk service terkait
- [ ] Deploy dasar (hosting + domain)

## Fase 2 — Membership Core (1-2 minggu)

- [ ] Form pendaftaran publik (nama, email, no. HP, perusahaan)
- [ ] Integrasi Toyyibpay (create bill, callback/webhook, cek status manual sebagai fallback)
- [ ] Halaman status "menunggu review"
- [ ] Admin: list & review pendaftar (approve/reject)
- [ ] Event-driven approval (`UserApproved` listener stub, isi di Fase 3)

## Fase 3 — Auto-generate Kad & Sertifikat (1 minggu)

- [ ] Setting template kad ahli (admin upload desain + mapping field)
- [ ] Generate PDF/image kad ahli + QR token
- [ ] Endpoint publik verifikasi QR (`/verify/{qr_token}`)
- [ ] Setting template sertifikat
- [ ] Generate PDF sertifikat
- [ ] Email otomatis ke ahli setelah approved (attach kad + sertifikat)

## Fase 4 — Public Site (1-2 minggu)

- [ ] Landing page (hero, tentang, org chart preview, blog terbaru)
- [ ] Blog: list & detail page
- [ ] Admin: CRUD blog post (Filament)

## Fase 5 — Admin Tools (1 minggu)

- [ ] Org chart CRUD + drag & drop reorder
- [ ] Import Excel (mapping, validasi, laporan error)
- [ ] Export Excel (filter + generate file)
- [ ] Dashboard ringkasan (jumlah ahli per status, revenue, dll)

## Fase 6 — Renewal & Polish (1 minggu)

- [ ] Scheduled job reminder renewal (H-30, H-7)
- [ ] Flow renewal (bayar ulang → extend `renewal_expires_at`)
- [ ] Member area: profil, download kad/sertifikat, status renewal
- [ ] Testing end-to-end (payment, approval, generate dokumen)
- [ ] QA & bug fixing

---

## Total Estimasi

**~6-9 minggu** (1 developer), tergantung kompleksitas desain kad/sertifikat & seberapa cepat keputusan open questions di `PLAN.md` diselesaikan.

## Rekomendasi Urutan Keputusan (sebelum mulai coding)

Supaya tidak ada rework, sebaiknya jawab dulu urutan ini sebelum/selama Fase 1:
1. Kebijakan refund/non-refund (mempengaruhi UX form + T&C)
2. Durasi keanggotaan & format `member_no` (mempengaruhi migration `users` table)
3. Desain visual kad ahli & sertifikat (mempengaruhi Fase 3, bisa dikerjakan paralel oleh desainer selama Fase 1-2 berjalan)
