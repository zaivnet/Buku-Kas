# PANDUAN DEPLOYMENT KE SHARED HOSTING (cPanel / DirectAdmin)

Dokumen ini berisi panduan langkah demi langkah meluncurkan **Buku Kas Digital** ke lingkungan produksi shared hosting.

---

## 1. Persyaratan Server Shared Hosting
- **PHP Version**: 8.3 atau lebih tinggi
- **Ekstensi PHP**: `pdo_mysql`, `gd`, `mbstring`, `fileinfo`, `xml`, `bcmath`, `ctype`, `cURL`, `openssl`, `tokenizer`, `zip`
- **Database**: MySQL 8.0+ atau MariaDB 10.4+
- **Document Root**: Dukungan pengarahan domain/subdomain ke folder `public/`

---

## 2. Persiapan Sebelum Upload (Lokal)

Jalankan perintah berikut di lingkungan komputer lokal sebelum mengunggah berkas:

```bash
# 1. Build asset CSS & JS produksi
npm run build

# 2. Pastikan file composer.lock terbarui
composer install --no-dev --optimize-autoloader
```

---

## 3. Struktur Berkas di Shared Hosting

Pada shared hosting, sangat disarankan meletakkan kode aplikasi di **luar** folder `public_html` demi alasan keamanan.

### Struktur Rekomendasi:
```
/home/username/
├── buku_app/               <-- Seluruh folder proyek (DI LUAR public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── ...
└── public_html/            <-- Isi dari folder public/ proyek
    ├── build/
    ├── storage -> /home/username/buku_app/storage/app/public
    ├── index.php
    ├── favicon.ico
    └── .htaccess
```

### Penyesuaian `public_html/index.php`:
Jika kode dipisah seperti struktur di atas, edit baris berikut di `public_html/index.php`:
```php
// Ubah path autoloader:
require __DIR__.'/../buku_app/vendor/autoload.php';

// Ubah path bootstrap Laravel:
$app = require_once __DIR__.'/../buku_app/bootstrap/app.php';
```

---

## 4. Konfigurasi Berkas `.env` Produksi

Buat atau edit berkas `.env` di folder server `buku_app/`:

```ini
APP_NAME="Buku Kas Digital"
APP_ENV=production
APP_KEY=base64:PASTE_APP_KEY_ANDA_DI_SINI
APP_DEBUG=false
APP_URL=https://bukukas.domainanda.com

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_usercpanel_dbbuku
DB_USERNAME=nama_usercpanel_userbuku
DB_PASSWORD=password_db_rahasia_anda

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
```

> [!IMPORTANT]
> Pastikan `APP_DEBUG=false` untuk mencegah kebocoran informasi sensitive/stack trace ke pengguna saat terjadi kesalahan server.

---

## 5. Perintah Optimasi Produksi

Jalankan perintah berikut melalui Terminal cPanel / SSH:

```bash
# 1. Jalankan migrasi database produksi
php artisan migrate --force

# 2. Buat symlink storage
php artisan storage:link

# 3. Cache konfigurasi, route, dan view demi kecepatan maksimal
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika tidak ada akses SSH/Terminal di cPanel:
Buat route sementara di `routes/web.php` untuk memicu optimasi:
```php
Route::get('/deploy-optimize-secret-key-123', function () {
    Artisan::call('migrate --force');
    Artisan::call('storage:link');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    return 'Optimasi Deployment Berhasil!';
});
```
*(Hapus route sementara ini segera setelah berhasil dipanggil)*

---

## 6. Hak Akses Folder (Permissions)

Pastikan izin folder berikut pada server:
- `storage/` : `755` atau `775` (dapat ditulis oleh web server)
- `bootstrap/cache/` : `755` atau `775`

---

## 7. Troubleshooting & Kendala Umum Shared Hosting

1. **Gambar Bukti Transaksi 404 (File Not Found)**:
   - Pastikan symlink `public_html/storage` mengarah dengan benar ke `storage/app/public`.
   - Jika hosting tidak mendukung symlink, buat cronjob 1x atau copy folder `storage/app/public` secara manual.

2. **Error 500 Internal Server Error**:
   - Cek log kesalahan di `storage/logs/laravel.log`.
   - Cek versi PHP di cPanel (Select PHP Version -> pilih PHP 8.3).
