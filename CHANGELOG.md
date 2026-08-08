# CHANGELOG - Buku Kas Digital

Semua perubahan dan riwayat rilis proyek **Buku Kas Digital** didokumentasikan di berkas ini.

---

## [v1.0.0] - 2026-08-08 (Produksi / Ready for Launch)

### 🚀 Sprint 00: Project Setup & Design System
- Inisialisasi framework Laravel 13, Blade stack, Tailwind CSS dengan warna custom (`primary`, `success`, `danger`, `warning`, `neutral`), font Inter, dan Alpine.js.
- Instalasi dependensi core: `intervention/image-laravel`, `phpoffice/phpspreadsheet`, `barryvdh/laravel-dompdf`.
- Konfigurasi antarmuka bahasa Indonesia pada halaman Login & Layout.

### 🗄️ Sprint 01: Database Schema & Seeders
- Pembuatan tabel migrasi: `outlets`, `users` (dengan Role Enum: `admin`, `staff`, `viewer`), `categories` (dengan Type Enum: `income`, `expense`), `transactions`, `activity_logs`, `settings`.
- Implementasi Eloquent Models (`User`, `Outlet`, `Category`, `Transaction`) dilengkapi SoftDeletes dan query scope multi-tenancy `Transaction::forUser()`.
- Seeder data default: Admin (`admin@bukukas.local`), Staff (`staff1@bukukas.local`), Outlet 1, Outlet 2, dan Kategori Default.

### 🔐 Sprint 02: Auth & Role-Based Access Control (RBAC)
- Middleware `EnsureUserHasRole` & pendaftaran alias `'role'`.
- Pencegahan login untuk user status nonaktif (`is_active = false`).
- Penutupan route registrasi publik (404 Not Found).
- Implementasi Policies (`UserPolicy`, `OutletPolicy`, `CategoryPolicy`, `TransactionPolicy`) dan halaman error `403.blade.php`.

### 🏢 Sprint 03: Manajemen Outlet & User
- Form komponen reusable: `<x-form.input>`, `<x-form.select>`, `<x-form.textarea>`, `<x-badge>`, `<x-modal>`.
- CRUD Outlet dengan proteksi hapus jika outlet memiliki riwayat transaksi.
- CRUD User dengan enkripsi password dan visibility field outlet otomatis (Alpine.js) untuk role Staff.

### 🏷️ Sprint 04: Manajemen Kategori Custom
- Tab switcher interaktif untuk Pemasukan vs Pengeluaran.
- Modal inline Alpine.js untuk Tambah & Edit Kategori.
- Validasi unik `(name, type)` per tipe kategori.
- Proteksi hapus kategori yang terikat pada transaksi.

### 💰 Sprint 05: Transaksi Pemasukan (Income)
- `ImageUploadService`: Kompresi otomatis gambar (maksimal lebar 1200px, kualitas 80%) menggunakan Intervention Image v4.
- `TransactionService`: Enforcement scoping outlet Staff secara otomatis (mencegah manipulasi `outlet_id`).
- Form input nominal dengan format ribuan otomatis (Alpine.js `Rp 1.500.000`).
- Komponent upload gambar `<x-image-upload>` dengan drag-and-drop & live preview.
- Batas penguncian transaksi > 7 hari bagi Staff (`TransactionPolicy`).
- Halaman detail `show.blade.php` dengan Lightbox Modal zoom foto bukti.

### 💸 Sprint 06: Transaksi Pengeluaran (Expense)
- Penggunaan ulang arsitektur generik `TransactionController` & `TransactionService` (Zero Code Duplication).
- Highlight menu Pengeluaran dan badge warna merah (*danger*) secara konsisten.
- Validasi ketat penolakan kategori bertipe `income` saat menyimpan transaksi pengeluaran.

### 📊 Sprint 07: Dashboard Analytics & Laporan
- `ReportService`: Agregasi SQL murni (`SUM`, `GROUP BY`) langsung di level database.
- Grafik tren harian/bulanan adaptif (Line Chart Chart.js) dan grafik proporsi kategori (Doughnut Chart).
- 3 Kartu Ringkasan Saldo: Total Pemasukan, Total Pengeluaran, dan Saldo Net Kas.
- Halaman Laporan Keuangan (`/reports`) dengan filter tanggal custom, tipe, kategori, dan outlet.

### 📄 Sprint 08: Export Excel, PDF & Cetak
- `ExcelExportService`: Export spreadsheet `.xlsx` bergaya profesional dengan PhpSpreadsheet, warna header, dan baris total.
- Export PDF A4 Landscape menggunakan DomPDF dengan memory guard limit 2.000 record.
- Pratinjau Cetak Browser (`print.blade.php`) dengan aturan `@media print` dan kolom tanda tangan basah.

### 🛡️ Sprint 09: Polish, Hardening & Deployment Prep
- Komponen notifikasi Toast `<x-toast>` dengan auto-dismiss 4 detik.
- Komponen Empty State `<x-empty-state>` saat tabel data kosong.
- Pencegahan Double-Submit form transaksi dengan status loading spinner tombol.
- Halaman error custom Bahasa Indonesia (`404.blade.php`, `500.blade.php`).
- Dokumen panduan deploy shared hosting (`DEPLOY.md`) dan pemeliharaan/backup (`MAINTENANCE.md`).
- Build asset produksi (`npm run build`).
- **67 Automated Unit & Feature Tests 100% Passed**.
