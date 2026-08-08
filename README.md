# Buku Kas Digital

Aplikasi manajemen keuangan (buku kas) multi-outlet & multi-user, dibangun dengan **Laravel + Blade + Tailwind CSS + Alpine.js**.

---

## ℹ️ Tentang Aplikasi & Pengembang

**Buku Kas Digital** adalah sistem pencatatan keuangan kas usaha berbasis web & mobile (PWA) yang mempermudah usaha retail maupun bisnis cabang dalam mencatat arus uang masuk (pemasukan) dan uang keluar (pengeluaran) secara transparan dan terstruktur.

- **Developer / Pembuat**: **Ade Zaiv**
- **Kontak & Support Email**: [admin@pehawe.me](mailto:admin@pehawe.me)
- **Versi Rilis**: v1.0.0 (Produksi)

---

## 🐳 Deployment Cepat via Docker / Proxmox VE

Aplikasi ini dilengkapi konfigurasi Docker multi-stage & Docker Compose untuk kemudahan instalasi di **Proxmox VE (LXC / VM)** atau server VPS lainnya:

```bash
git clone https://github.com/zaivnet/Buku-Kas.git
cd Buku-Kas
docker compose up -d
```

*Aplikasi langsung aktif di `http://localhost:1990` (atau IP Proxmox Anda: `http://IP-PROXMOX:1990`). Lihat [DOCKER.md](file:///E:/laragon/www/buku/DOCKER.md) untuk panduan lengkap Proxmox.*

---

## Fitur Utama

- 📊 **Dashboard** — ringkasan saldo, grafik tren pemasukan vs pengeluaran, breakdown per kategori
- 💰 **Transaksi Pemasukan & Pengeluaran** — dengan kategori custom, outlet, atas nama, keterangan & upload bukti
- 🏪 **Multi-outlet** — Admin akses semua outlet, Staff terkunci ke outlet masing-masing
- 🏷️ **Kategori Custom** — buat, edit, nonaktifkan kategori income/expense sesuai kebutuhan
- 👥 **Manajemen User** — Admin buat user, assign role & outlet
- 📄 **Laporan** — filter periode, export Excel/PDF, cetak
- 🔒 **RBAC** — Admin, Staff, Viewer dengan hak akses berbeda

---

## Tech Stack

| Layer | Pilihan |
|---|---|
| Backend | PHP 8.2+ / Laravel 13 |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Database | MySQL 5.7+/8 |
| Auth | Laravel Breeze (Blade) |
| Upload Gambar | Intervention Image v4 (resize/compress) |
| Export Excel | Maatwebsite Excel v3 |
| Export PDF | barryvdh/laravel-dompdf |
| Charts | Chart.js (via CDN) |

---

## Instalasi Lokal (Laragon / XAMPP / Herd)

### Prasyarat
- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL

### Langkah

```bash
# 1. Clone repository
git clone https://github.com/zaivnet/Buku-Kas.git
cd Buku-Kas

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies & build assets
npm install
npm run build

# 4. Salin file .env
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Buat database MySQL bernama 'buku_kas' (atau sesuaikan di .env)
#    Kemudian jalankan migrasi + seeder
php artisan migrate --seed

# 7. Buat symlink storage
php artisan storage:link

# 8. Jalankan server (development)
php artisan serve
```

Akses di: `http://localhost:8000`

> **Akun Default Seeder**:
> - **Admin**: `admin@bukukas.local` / `password123` (Akses penuh seluruh outlet)
> - **Staff**: `staff1@bukukas.local` / `password123` (Akses terkunci ke Outlet 1)


---

## Pengembangan (Hot-reload)

```bash
npm run dev
```

---

## Deployment Shared Hosting (cPanel)

1. **Build asset di lokal** (bukan di server):
   ```bash
   npm run build
   ```

2. **Upload project** via Git/FTP ke folder hosting (misal `/home/user/buku`), **kecuali** `node_modules/`

3. **Set document root** ke folder `public/` di cPanel

4. **Konfigurasi `.env` produksi**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-anda.com
   DB_HOST=localhost
   DB_DATABASE=nama_db
   DB_USERNAME=user_db
   DB_PASSWORD=password_db
   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=sync
   ```

5. **Jalankan via SSH** (jika tersedia):
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

   > Jika tidak ada akses SSH: upload folder `vendor/` hasil build lokal.

6. Pastikan folder `storage/` dan `bootstrap/cache/` **writable** (chmod 755/775).

---

## Struktur Folder Penting

```
app/
  Enums/          # RoleEnum, TransactionTypeEnum
  Http/
    Controllers/
      Admin/      # UserController, OutletController, CategoryController
    Requests/     # Form Request validation
    Middleware/   # EnsureUserHasRole
  Models/         # User, Outlet, Category, Transaction
  Policies/       # Authorization policies
  Services/       # Business logic (TransactionService, ReportService, ImageUploadService)
resources/
  views/
    layouts/      # app.blade.php, guest.blade.php
    components/   # Reusable Blade components
storage/
  app/public/proofs/  # Bukti transaksi (gambar upload)
```

---

## Lisensi

Aplikasi internal — hak cipta sepenuhnya milik pemilik proyek.
