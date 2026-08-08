# BLUEPRINT ARSITEKTUR & DESAIN SISTEM - BUKU KAS DIGITAL

Dokumen ini menjelaskan arsitektur teknis, struktur data, aliran data, dan aturan keamanan aplikasi **Buku Kas Digital**.

---

## 1. Ringkasan Arsitektur

- **Framework**: Laravel 13.x (PHP 8.3+)
- **Frontend Stack**: Blade Templates + Tailwind CSS v4 (Custom Palette) + Alpine.js
- **Database**: MySQL 8.0+ / MariaDB (Produksi), SQLite (Testing)
- **Image Engine**: Intervention Image v4 (GD Driver)
- **Export Engine**: PhpSpreadsheet v5.9 (Excel) & DomPDF v3.1 (PDF)

---

## 2. Struktur Modul Utama & Service Layer

```
app/
├── Enums/
│   ├── RoleEnum.php                 (ADMIN, STAFF, VIEWER)
│   └── TransactionTypeEnum.php      (INCOME, EXPENSE)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── CategoryController.php
│   │   │   ├── OutletController.php
│   │   │   └── UserController.php
│   │   ├── DashboardController.php
│   │   ├── ReportController.php
│   │   └── TransactionController.php
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php
│   └── Requests/
│       ├── StoreTransactionRequest.php
│       └── UpdateTransactionRequest.php
├── Models/
│   ├── Category.php
│   ├── Outlet.php
│   ├── Transaction.php
│   └── User.php
├── Policies/
│   ├── CategoryPolicy.php
│   ├── OutletPolicy.php
│   ├── TransactionPolicy.php
│   └── UserPolicy.php
└── Services/
    ├── ExcelExportService.php
    ├── ImageUploadService.php
    ├── ReportService.php
    └── TransactionService.php
```

---

## 3. Matriks Hak Akses & Multi-Tenancy Scoping

| Fitur / Halaman | Admin | Staff | Viewer |
| :--- | :---: | :---: | :---: |
| **Dashboard** | Semua Outlet | Terkunci ke Outlet-nya | Read-only Semua Outlet |
| **Pemasukan (Income)** | CRUD Semua Outlet | CRUD Outlet-nya ($\le 7$ hari) | Read-only |
| **Pengeluaran (Expense)** | CRUD Semua Outlet | CRUD Outlet-nya ($\le 7$ hari) | Read-only |
| **Laporan & Export** | Semua Outlet | Terkunci ke Outlet-nya | Read-only |
| **Manajemen Outlet** | Full CRUD | ❌ Forbidden (403) | ❌ Forbidden (403) |
| **Manajemen User** | Full CRUD | ❌ Forbidden (403) | ❌ Forbidden (403) |
| **Manajemen Kategori** | Full CRUD | ❌ Forbidden (403) | ❌ Forbidden (403) |

---

## 4. Keamanan & Proteksi Integrity Data

1. **Scoping Level Service**: Staff pengguna dipaksa secara otomatis ke `outlet_id` miliknya di layer Service (`TransactionService::create()`), sehingga manipulasi request HTTP diredam sepenuhnya.
2. **Penguncian Transaksi Staff (7 Hari)**: `TransactionPolicy::isLockedForStaff()` mencegah Staff mengedit/menghapus transaksi yang telah berlalu $> 7$ hari.
3. **Optimasi Kompresi Bukti Transaksi**: `ImageUploadService` melakukan resize (max 1200px) dan kompresi JPEG 80% untuk menghemat ruang disk storage.
4. **Memory Guard Export**: Limitasi 2.000 record pada PDF Export untuk menjamin kestabilan memori di server shared hosting.
