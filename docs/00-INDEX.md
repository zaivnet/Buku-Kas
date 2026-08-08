# Buku Kas Digital — Dokumentasi Proyek

Aplikasi manajemen keuangan (buku kas) multi-outlet & multi-user, dibangun dengan **Laravel + PHP** agar ringan dan kompatibel di server lokal maupun shared hosting (cPanel).

## Daftar Dokumen

| File | Isi |
|---|---|
| `01-PRD.md` | Product Requirement Document — fitur, role, ruang lingkup |
| `02-ARCHITECTURE.md` | Arsitektur teknis, stack, struktur folder, alur deploy |
| `03-DESIGN.md` | UI/UX — layout, palet warna, halaman, komponen |
| `04-RULES.md` | Aturan coding & konvensi untuk konsistensi kode |
| `05-SCHEMA.md` | Skema database lengkap (tabel, relasi, index) |
| `sprints/sprint-00-setup.md` | Setup project Laravel + Tailwind + Alpine |
| `sprints/sprint-01-database.md` | Migration, Model, relasi, seeder |
| `sprints/sprint-02-auth-rbac.md` | Login, middleware role, policy dasar |
| `sprints/sprint-03-outlet-user.md` | CRUD Outlet & CRUD User |
| `sprints/sprint-04-category.md` | CRUD Kategori custom (income/expense) |
| `sprints/sprint-05-income-transaction.md` | CRUD transaksi pemasukan + upload bukti |
| `sprints/sprint-06-expense-transaction.md` | CRUD transaksi pengeluaran (reuse dari income) |
| `sprints/sprint-07-dashboard-report.md` | Dashboard, grafik, halaman laporan |
| `sprints/sprint-08-export.md` | Export Excel/PDF & cetak |
| `sprints/sprint-09-polish-deploy.md` | Polish UX, hardening, deploy shared hosting |

## Cara Pakai dengan Antigravity

1. Letakkan semua file `01`–`05` di root project sebagai referensi konteks.
2. Jalankan sprint satu per satu **berurutan** (00 → 09) — setiap sprint dibangun di atas sprint sebelumnya.
3. Sebelum eksekusi tiap sprint, tempel isi file sprint terkait sebagai prompt ke Antigravity, sertakan juga link/isi dokumen `04-RULES.md` agar konsisten.
4. Setelah tiap sprint selesai, cek ulang **Acceptance Criteria** di file sprint sebelum lanjut ke sprint berikutnya.

## Ringkasan Keputusan Teknis

- **Stack**: Laravel 11 (PHP 8.2+) + Blade + Tailwind CSS + Alpine.js — tanpa SPA framework berat, tanpa Node.js di server produksi.
- **Database**: MySQL — kompatibel default di hampir semua shared hosting cPanel.
- **Role**: Admin (akses penuh), Staff (terbatas ke 1 outlet), Viewer (read-only, opsional).
- **Kategori**: sepenuhnya custom, dibuat oleh Admin, terpisah untuk income/expense.
- **File bukti**: opsional, disimpan lokal dengan resize/compress otomatis.
- **Deploy**: build asset dilakukan di lokal, hasil build diupload — server produksi tidak butuh menjalankan `npm`/Node sama sekali.
