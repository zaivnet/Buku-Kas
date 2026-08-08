# ARSITEKTUR — Buku Kas Digital

## 1. Prinsip Desain
- **Monolith klasik Laravel** (bukan SPA/API terpisah) → paling ringan & paling kompatibel shared hosting.
- **Server-rendered Blade** + **Alpine.js** untuk interaktivitas kecil (modal, toggle, dsb) — hindari SPA framework berat (Vue/React full) supaya build asset minimal dan tidak butuh Node runtime di server produksi.
- **Tailwind CSS** untuk styling, di-compile sekali via Vite saat development, hasil build (`public/build`) diupload ke hosting — **server produksi tidak perlu Node.js sama sekali**.
- Tanpa queue worker wajib, tanpa Redis wajib — semua fitur inti berjalan dengan driver default (`sync` queue, `file`/`database` cache & session) agar kompatibel shared hosting yang tidak bisa jalankan proses background.

## 2. Tech Stack

| Layer | Pilihan | Alasan |
|---|---|---|
| Bahasa/Framework | PHP 8.2+, Laravel 11 | Familiar, ekosistem besar, ringan di shared hosting |
| Frontend | Blade + Tailwind CSS + Alpine.js | Tanpa build step di server, interaktif secukupnya |
| Database | MySQL 5.7+/8 | Tersedia default di hampir semua shared hosting cPanel |
| Auth | Laravel Breeze (Blade stack) | Scaffolding auth ringan, tanpa Livewire/Inertia yang lebih berat |
| Authorization | Role enum + Laravel Gate/Policy | Cukup untuk 3 role, tanpa overhead package permission kompleks |
| Upload gambar | Laravel Filesystem (`public` disk) + Intervention Image (resize/compress) | Kontrol ukuran file, hemat storage shared hosting |
| Chart | Chart.js (via CDN, tanpa build) | Ringan, tanpa dependensi npm di server |
| Export Excel | maatwebsite/excel | Standar de-facto Laravel |
| Export PDF | barryvdh/laravel-dompdf | Ringan, tanpa headless browser (beda dengan Puppeteer yang berat) |
| Session/Cache | Database driver (fallback file) | Tidak butuh Redis di shared hosting |
| Queue | `sync` (default) | Tidak butuh worker process; job kecil (misal resize gambar) dijalankan langsung |

## 3. Struktur Folder (Laravel standar + konvensi tambahan)

```
app/
  Http/
    Controllers/
      Admin/
        UserController.php
        OutletController.php
        CategoryController.php
      TransactionController.php      # income & expense (dibedakan via query param/type)
      DashboardController.php
      ReportController.php
    Requests/
      StoreTransactionRequest.php
      UpdateTransactionRequest.php
      StoreCategoryRequest.php
      StoreOutletRequest.php
      StoreUserRequest.php
    Middleware/
      EnsureUserHasRole.php
  Models/
    User.php
    Outlet.php
    Category.php
    Transaction.php
  Policies/
    TransactionPolicy.php
    CategoryPolicy.php
    OutletPolicy.php
    UserPolicy.php
  Services/
    TransactionService.php   # business logic: hitung saldo, filter laporan
    ReportService.php        # agregasi data untuk dashboard/export
    ImageUploadService.php   # resize & simpan bukti transaksi
  Enums/
    RoleEnum.php
    TransactionTypeEnum.php

resources/
  views/
    layouts/
      app.blade.php
      guest.blade.php
    components/
      form/
      table/
      modal.blade.php
    transactions/
      index.blade.php
      create.blade.php
      edit.blade.php
    dashboard/
      index.blade.php
    reports/
      index.blade.php
    admin/
      users/
      outlets/
      categories/
  css/app.css
  js/app.js

database/
  migrations/
  seeders/
    RoleSeeder.php (jika pakai tabel roles) / cukup enum
    DefaultCategorySeeder.php
    AdminUserSeeder.php

routes/
  web.php
storage/
  app/public/proofs/            # bukti transaksi (image)
```

## 4. Alur Data Utama

```
User login → Middleware auth + role check
   │
   ▼
Route (web.php) → Controller → FormRequest (validasi) → Policy (otorisasi)
   │
   ▼
Controller → Service (business logic: hitung saldo, simpan gambar)
   │
   ▼
Model (Eloquent) → Database (MySQL)
   │
   ▼
Blade View (server render) ← data sudah difilter sesuai role/outlet user
```

**Aturan penting**: Staff yang login otomatis hanya melihat data `outlet_id` miliknya — difilter di level Service/Query, **bukan** di JavaScript, supaya aman meski request dimanipulasi.

## 5. Modul & Tanggung Jawab

| Modul | Tanggung Jawab |
|---|---|
| Auth | Login/logout, reset password (opsional email) |
| User Management | CRUD user, assign role & outlet (Admin only) |
| Outlet Management | CRUD outlet (Admin only) |
| Category Management | CRUD kategori income/expense (Admin only) |
| Transaction (Income/Expense) | CRUD transaksi, upload bukti, filter sesuai role |
| Dashboard | Ringkasan saldo, grafik tren, breakdown kategori |
| Report/Export | Filter laporan, export Excel/PDF, print view |

## 6. Deployment

### 6.1 Server Lokal (development)
- Laragon / XAMPP / Herd, PHP 8.2+, MySQL.
- `php artisan serve` atau virtual host lokal.
- `npm run dev` hanya dipakai saat development untuk hot-reload Tailwind.

### 6.2 Shared Hosting (cPanel) — Produksi
1. `npm run build` **di lokal** (bukan di server) → hasilkan `public/build/`.
2. Upload seluruh project via Git/FTP, **kecuali** `node_modules` (tidak perlu di server).
3. Set document root ke `public/` (atau gunakan trik `index.php` + `.htaccess` redirect jika tidak bisa ubah document root — umum di shared hosting).
4. `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, kredensial DB dari cPanel.
5. `composer install --optimize-autoloader --no-dev` (jalankan via SSH jika tersedia, atau upload `vendor/` hasil build lokal jika hosting tidak punya akses composer/SSH).
6. `php artisan migrate --force`, `php artisan storage:link`, `php artisan config:cache`.
7. Folder `storage/` dan `bootstrap/cache/` harus writable (chmod 755/775 sesuai kebijakan hosting).

### 6.3 VPS / Server Lokal Kantor (opsional)
- Bisa pakai Docker Compose (PHP-FPM + Nginx + MySQL) jika diinginkan, tapi **tidak wajib** — cukup LAMP/LEMP manual sudah jalan.

## 7. Keamanan
- Semua route transaksi & admin dilindungi middleware `auth` + `role`.
- Validasi file upload: cek MIME type asli (bukan hanya ekstensi), max 2MB, resize otomatis ke max 1200px lebar.
- Mass assignment protection via `$fillable` di setiap Model.
- Rate limiting login (Laravel default throttle).
- CSRF token di semua form (default Blade `@csrf`).

## 8. Pertimbangan Performa (agar tetap ringan)
- Index database di kolom yang sering difilter: `date`, `outlet_id`, `category_id`, `type`.
- Eager loading relasi (`with('category','outlet','creator')`) untuk hindari N+1.
- Pagination default 20–25 baris per halaman di semua list.
- Gambar bukti di-compress ke kualitas 75–80% saat upload (Intervention Image), simpan max lebar 1200px.
- Cache ringan (Laravel `cache()`) untuk data master yang jarang berubah (daftar kategori, outlet) dengan TTL pendek + invalidate saat ada perubahan.
