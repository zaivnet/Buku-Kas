# PANDUAN BACKUP & PEMELIHARAAN SISTEM (MAINTENANCE)

Dokumen ini berisi panduan rutin untuk melakukan backup data keuangan dan pemeliharaan berkala pada **Buku Kas Digital**.

---

## 1. Prosedur Backup Database

Backup database wajib dilakukan secara berkala (harian / mingguan) untuk melindungi data transaksi dari potensi kegagalan hardware atau bencana.

### Manual Backup via `mysqldump` (Terminal / SSH / Cron):
```bash
# Ganti variabel sesuai database Anda
mysqldump -u [DB_USERNAME] -p'[DB_PASSWORD]' [DB_DATABASE] | gzip > backup_bukukas_$(date +%Y%m%d_%H%M%S).sql.gz
```

*Contoh Perintah:*
```bash
mysqldump -u user_bukukas -p'password123' db_bukukas | gzip > backup_bukukas_20260808_103000.sql.gz
```

### Manual Backup via phpMyAdmin (cPanel):
1. Buka cPanel -> **phpMyAdmin**.
2. Pilih database `db_bukukas`.
3. Klik tab **Export** di bagian atas.
4. Pilih metode **Quick** dan format **SQL**.
5. Klik **Go** / **Kirim** untuk mengunduh file `.sql`.

---

## 2. Prosedur Backup Berkas Bukti Transaksi (Images)

Semua berkas foto/gambar bukti transaksi disimpan di folder:
`storage/app/public/proofs/`

### Cara Backup Manual Folder Gambar:
Kompres folder `proofs` menjadi file ZIP:

```bash
# Terminal / SSH
tar -czvf backup_proofs_$(date +%Y%m%d).tar.gz storage/app/public/proofs
```

Atau unduh folder `storage/app/public/proofs` melalui **cPanel File Manager** / **FTP Client** (FileZilla).

---

## 3. Otomatisasi Backup Harian via Cron Job (cPanel)

Tambahkan perintah berikut pada menu **Cron Jobs** di cPanel untuk backup harian otomatis setiap jam 02:00 malam:

```bash
0 2 * * * mysqldump -u [DB_USERNAME] -p'[DB_PASSWORD]' [DB_DATABASE] | gzip > /home/[USERNAME]/backups/db_$(date +\%Y\%m\%d).sql.gz
```

---

## 4. Prosedur Restore Data (Pemulihan)

Jika terjadi kendala dan data perlu dipulihkan dari backup:

### Restore Database:
```bash
gunzip < backup_bukukas_20260808_103000.sql.gz | mysql -u [DB_USERNAME] -p'[DB_PASSWORD]' [DB_DATABASE]
```

### Restore Berkas Bukti Gambar:
Ekstrak kembali file tar.gz/zip backup ke dalam folder `storage/app/public/proofs/`.

---

## 5. Pemeliharaan Rutin Cache & Log

Untuk menjaga performa aplikasi tetap cepat di shared hosting:

```bash
# 1. Bersihkan log lama yang membengkak jika perlu
rm -f storage/logs/laravel-*.log

# 2. Re-cache sistem jika ada perubahan data outlet/kategori mendasar
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
