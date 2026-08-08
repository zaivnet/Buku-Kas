# DATABASE SCHEMA — Buku Kas Digital

Database: **MySQL 5.7+/8**. Semua tabel pakai `id` BIGINT UNSIGNED AUTO_INCREMENT sebagai primary key, `created_at`/`updated_at` standar Laravel, kecuali disebutkan lain.

## 1. ERD (ringkasan relasi)

```
users ───────────┐
  │ outlet_id (FK, nullable)     │ created_by / updated_by (FK)
  ▼                              ▼
outlets ◄──────────────── transactions ────────► categories
                                │
                                ▼
                        (proof_image nullable)
```

- 1 `outlet` punya banyak `users` (staff) dan banyak `transactions`.
- 1 `category` punya banyak `transactions`.
- 1 `user` mencatat (`created_by`) banyak `transactions`.

## 2. Tabel: `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | |
| email | VARCHAR(150) UNIQUE | |
| password | VARCHAR(255) | hashed (bcrypt) |
| role | ENUM('admin','staff','viewer') | default `staff` |
| outlet_id | BIGINT UNSIGNED NULL | FK → `outlets.id`, wajib diisi jika role=staff, NULL jika admin/viewer (akses semua outlet) |
| is_active | BOOLEAN | default `true`, untuk nonaktifkan user tanpa hapus |
| email_verified_at | TIMESTAMP NULL | |
| remember_token | VARCHAR(100) NULL | |
| created_at, updated_at | TIMESTAMP | |

**Index**: `outlet_id`, `role`, `email` (unique).

## 3. Tabel: `outlets`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | contoh: "Outlet 1 - Kalasan" |
| address | VARCHAR(255) NULL | |
| is_active | BOOLEAN | default `true` |
| created_at, updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULL | soft delete |

## 4. Tabel: `categories`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| name | VARCHAR(150) | contoh: "Setoran Penjualan", "Gaji Karyawan" |
| type | ENUM('income','expense') | tipe tetap, tidak bisa campur |
| is_active | BOOLEAN | default `true`, soft-disable |
| created_by | BIGINT UNSIGNED NULL | FK → `users.id` |
| created_at, updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULL | soft delete |

**Index**: `type`, `is_active`.
**Constraint**: unique gabungan `(name, type)` agar tidak duplikat nama kategori dalam tipe yang sama.

## 5. Tabel: `transactions`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| type | ENUM('income','expense') | wajib sama dengan tipe `category` terkait (divalidasi di aplikasi) |
| category_id | BIGINT UNSIGNED | FK → `categories.id` |
| outlet_id | BIGINT UNSIGNED | FK → `outlets.id` |
| date | DATE | tanggal transaksi (bukan waktu input sistem) |
| amount | BIGINT UNSIGNED | nominal dalam Rupiah, tanpa desimal |
| payer_name | VARCHAR(150) | "Atas Nama" |
| description | TEXT NULL | keterangan opsional |
| proof_image_path | VARCHAR(255) NULL | path relatif file bukti, NULL jika tidak upload |
| created_by | BIGINT UNSIGNED | FK → `users.id`, otomatis dari user login |
| updated_by | BIGINT UNSIGNED NULL | FK → `users.id`, terisi saat ada edit |
| created_at, updated_at | TIMESTAMP | |
| deleted_at | TIMESTAMP NULL | soft delete |

**Index**: `date`, `outlet_id`, `category_id`, `type`, gabungan `(outlet_id, date)` untuk query laporan per outlet+periode yang sering dipakai.

## 6. Tabel Pendukung (opsional, bisa ditambah di fase 2)

### 6.1 `activity_logs` (opsional, audit lebih detail)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| user_id | BIGINT UNSIGNED | siapa yang melakukan aksi |
| action | VARCHAR(50) | `created`, `updated`, `deleted` |
| model_type | VARCHAR(100) | contoh: `Transaction` |
| model_id | BIGINT UNSIGNED | |
| changes | JSON NULL | snapshot perubahan (before/after) |
| created_at | TIMESTAMP | |

### 6.2 `settings` (opsional, untuk konfigurasi aplikasi)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| key | VARCHAR(100) UNIQUE | contoh: `edit_lock_days` (berapa hari transaksi lama tidak boleh diedit staff) |
| value | VARCHAR(255) | |

## 7. Aturan Integritas Tambahan (level aplikasi, bukan hanya DB)
- `transactions.type` harus selalu sinkron dengan `categories.type` yang dipilih — divalidasi di Form Request, idealnya juga dijaga via trigger/observer agar konsisten jika ada perubahan data langsung.
- `transactions.outlet_id` untuk user role `staff` **harus** sama dengan `users.outlet_id` milik pencatat — divalidasi di Service layer, bukan hanya di form.
- Kategori/outlet yang sudah pernah dipakai transaksi **tidak boleh dihapus permanen**, hanya bisa dinonaktifkan (`is_active = false`) — mencegah data transaksi lama kehilangan referensi.

## 8. Contoh Data Seed Awal (Default)

**Kategori Pemasukan default:**
- Setoran Penjualan Outlet
- Dipinjam
- Pemasukan Lainnya

**Kategori Pengeluaran default:**
- Belanja Stok
- Gaji Karyawan
- Pinjaman Karyawan
- Pengeluaran Lainnya

**User default (seeder):**
- 1 akun Admin awal (email & password dari `.env` saat seeding, wajib diganti setelah instalasi pertama).
