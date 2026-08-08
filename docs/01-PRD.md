# PRD — Buku Kas Digital (Aplikasi Manajemen Keuangan / Cash Book)

## 1. Latar Belakang & Tujuan

Saat ini pencatatan keluar-masuk uang (pemasukan & pengeluaran) dari beberapa outlet/toko dilakukan secara manual. Dibutuhkan aplikasi web internal yang:

- Mencatat **pemasukan** dan **pengeluaran** dengan kategori yang bisa dibuat sendiri (custom, tidak hardcode).
- Mendukung **multi-outlet** dan **multi-user** dengan hak akses berbeda (role-based).
- Melampirkan **bukti transaksi** berupa gambar (opsional).
- Ringan, mudah di-deploy di **server lokal (Laragon/XAMPP/VPS)** maupun **shared hosting (cPanel)**.

## 2. Target Pengguna

| Role | Deskripsi |
|---|---|
| **Admin/Owner** | Akses penuh: kelola user, outlet, kategori, semua transaksi semua outlet, laporan keseluruhan. |
| **Staff/Kasir** | Input & lihat transaksi hanya untuk outlet tempat ia ditugaskan. Tidak bisa kelola user/kategori/outlet. |
| **Viewer** (opsional, bisa ditambah belakangan) | Hanya bisa melihat laporan, tanpa hak input/edit/hapus. |

## 3. Ruang Lingkup Fitur (MVP)

### 3.1 Autentikasi & Otorisasi
- Login (email + password). Tidak ada registrasi publik — user dibuat oleh Admin.
- Role-based access control (Admin, Staff, Viewer).
- Staff hanya terikat ke 1 outlet (atau lebih, opsional multi-outlet per staff di fase 2).

### 3.2 Manajemen Outlet
- CRUD outlet (nama, alamat, status aktif/nonaktif). Admin only.

### 3.3 Manajemen Kategori (Custom)
- Kategori **Pemasukan** (contoh: Setoran Penjualan Outlet 1, Setoran Penjualan Outlet 2, Dipinjam, Pemasukan Lainnya) — dibuat/diedit/dihapus bebas oleh Admin.
- Kategori **Pengeluaran** (contoh: Belanja Stok, Gaji Karyawan, Pinjaman Karyawan, Pengeluaran Lainnya) — sama, custom oleh Admin.
- Kategori punya tipe tetap: `income` atau `expense` (tidak bisa dicampur).
- Kategori bisa dinonaktifkan (soft-disable) tanpa menghapus riwayat transaksi lama.

### 3.4 Transaksi Pemasukan (Income)
Field:
- Tanggal transaksi
- Kategori (pilih dari master kategori tipe income)
- Outlet terkait
- Jumlah (nominal, Rupiah)
- Atas nama (nama penyetor/pihak terkait)
- Keterangan (opsional, teks bebas)
- Bukti (upload gambar, opsional — jpg/png/webp, max 2MB)
- Dicatat oleh (otomatis dari user login)

### 3.5 Transaksi Pengeluaran (Expense)
Field sama seperti Income, dengan kategori tipe `expense`.

### 3.6 Dashboard & Laporan
- Ringkasan saldo kas (total masuk, total keluar, saldo berjalan) — bisa difilter per periode & per outlet.
- Grafik tren pemasukan vs pengeluaran (harian/bulanan).
- Breakdown per kategori (pie/bar chart).
- Tabel transaksi dengan filter (tanggal, outlet, kategori, tipe, atas nama) + pencarian + pagination.
- Export laporan ke Excel & PDF.
- Cetak (print-friendly view) untuk laporan harian/bulanan.

### 3.7 Riwayat & Audit (ringan)
- Log siapa membuat/mengubah/menghapus transaksi (created_by, updated_by, deleted_at — soft delete).
- Staff tidak bisa menghapus transaksi yang sudah lewat X hari (aturan bisa dikonfigurasi Admin) — mencegah manipulasi data lama.

### 3.8 Manajemen User
- Admin membuat/mengedit/menonaktifkan user, assign role & outlet.

## 4. Di Luar Ruang Lingkup MVP (Fase Berikutnya)
- Approval workflow (transaksi butuh approval sebelum final).
- Multi-currency.
- Rekonsiliasi bank otomatis.
- Notifikasi WhatsApp/Telegram untuk transaksi besar.
- Aplikasi mobile native (cukup responsive web dulu).

## 5. Kebutuhan Non-Fungsional

| Aspek | Kebutuhan |
|---|---|
| **Ringan** | Harus jalan lancar di shared hosting spek rendah (RAM 256–512MB, PHP-FPM), tanpa proses Node.js aktif di server. |
| **Kompatibilitas hosting** | PHP 8.2+, MySQL 5.7+/8, tanpa dependensi service tambahan (Redis/Queue worker opsional, bukan wajib). |
| **Keamanan** | CSRF protection, validasi upload file ketat, password hashing bcrypt, role middleware di setiap route. |
| **Performa** | Query pakai index & eager loading, pagination di semua list, gambar bukti di-resize/compress saat upload. |
| **Skalabilitas ringan** | Desain schema mendukung penambahan outlet/kategori/user tanpa migrasi ulang. |
| **Portabilitas** | Backup/restore mudah (dump SQL + folder storage). |

## 6. Metrik Sukses
- Semua transaksi tercatat dengan kategori & outlet yang benar (data integrity).
- Waktu input 1 transaksi < 30 detik.
- Laporan bulanan bisa digenerate < 3 detik untuk ±5.000 transaksi.
- Aplikasi berjalan normal di shared hosting standar tanpa error 500 akibat memory/timeout.

## 7. Asumsi
- Satu instalasi aplikasi = satu entitas bisnis (bisa banyak outlet di dalamnya), bukan multi-tenant SaaS.
- Mata uang tunggal (Rupiah), tidak perlu multi-currency di MVP.
