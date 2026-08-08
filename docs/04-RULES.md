# CODING RULES — Buku Kas Digital

Dokumen ini menjadi acuan konsisten saat generate/edit kode via Antigravity (AI coding agent), agar hasil kode seragam di semua sprint.

## 1. Konvensi Umum Laravel
- Ikuti **PSR-12** untuk gaya kode PHP.
- Model: nama tunggal, PascalCase → `Transaction`, `Category`, `Outlet`, `User`.
- Tabel: nama jamak, snake_case → `transactions`, `categories`, `outlets`, `users`.
- Controller: PascalCase + suffix `Controller` → `TransactionController`.
- Route name: kebab-case dengan dot notation → `transactions.index`, `admin.categories.store`.
- Semua teks yang tampil ke user (label, pesan error, tombol) dalam **Bahasa Indonesia**.

## 2. Struktur Kode Wajib

### 2.1 Validasi
- **Jangan** validasi langsung di Controller. Selalu gunakan **Form Request** (`php artisan make:request`).
- Pesan error custom dalam Bahasa Indonesia via method `messages()` di Form Request.

### 2.2 Otorisasi
- **Jangan** cek role dengan `if ($user->role == 'admin')` tersebar di Controller/Blade.
- Gunakan **Policy** (`TransactionPolicy`, `CategoryPolicy`, dst) + `$this->authorize()` di Controller, dan `@can` di Blade.
- Middleware role (`role:admin`) dipakai di level route group untuk halaman admin-only.

### 2.3 Business Logic
- Logic yang lebih dari sekadar CRUD sederhana (hitung saldo, filter laporan kompleks, resize gambar) **wajib** dipindah ke Service class (`app/Services/`), bukan menumpuk di Controller.
- Controller idealnya tipis: terima request → panggil Form Request → panggil Policy → panggil Service/Model → return view/redirect.

### 2.4 Query & Performa
- Selalu gunakan **eager loading** (`with()`) saat menampilkan list dengan relasi (kategori, outlet, user pencatat).
- Selalu **paginate** (`paginate(20)`), jangan `get()` semua data di list transaksi.
- Filter scope untuk Staff (hanya outlet miliknya) diterapkan via **Query Scope** di Model (`scopeForCurrentUser()`), dipakai konsisten di semua tempat (list, dashboard, export) — supaya tidak ada celah lupa filter di satu halaman.

### 2.5 Multi-tenancy Ringan (Outlet Scoping)
- **Wajib**: setiap query transaksi untuk role Staff difilter berdasarkan `outlet_id` milik user login, dilakukan di **level Service/Model**, bukan disembunyikan di frontend saja.
- Field `outlet_id` di form untuk Staff harus **disabled/hidden** dan diisi otomatis di server side (jangan percaya nilai dari client meski field terlihat terkunci di UI).

## 3. Upload File (Bukti Transaksi)
- Validasi: `image`, `mimes:jpg,jpeg,png,webp`, `max:2048` (KB).
- Simpan ke disk `public`, path terstruktur: `storage/app/public/proofs/{tahun}/{bulan}/{uuid}.ext`.
- Resize/compress via Intervention Image sebelum simpan (max lebar 1200px, quality 80).
- Simpan **path relatif** di DB (bukan full URL), generate URL saat tampil via accessor Model.

## 4. Format Angka & Tanggal
- Semua nominal disimpan di DB sebagai **integer** (dalam Rupiah, tanpa desimal) — hindari masalah floating point.
- Format tampil: `Rp 1.500.000` (gunakan helper/format Indonesia, `number_format($value, 0, ',', '.')`).
- Tanggal disimpan sebagai `date` (bukan `datetime`, kecuali butuh jam), format tampil `d M Y` (contoh: 08 Agu 2026).

## 5. Soft Delete & Audit
- `Transaction`, `Category`, `Outlet` menggunakan **SoftDeletes** — data tidak pernah hilang permanen dari DB (kecuali admin sengaja force delete via fitur khusus).
- Kolom `created_by` dan `updated_by` wajib diisi otomatis via Model Observer/Event — jangan diisi manual di tiap Controller (rawan lupa).

## 6. Blade & Frontend
- Gunakan **Blade Components** untuk elemen berulang (lihat `03-DESIGN.md` §6) — jangan copy-paste markup form/tabel di banyak file.
- Interaktivitas ringan (toggle, modal, konfirmasi hapus) pakai **Alpine.js** (`x-data`, `x-show`) — jangan tambah library JS besar baru tanpa alasan kuat.
- Tidak boleh ada inline `<script>` besar tercampur logic bisnis di Blade — pisahkan ke `resources/js/` jika lebih dari beberapa baris.

## 7. Environment & Konfigurasi
- Semua kredensial (DB, mail, dsb) HANYA lewat `.env`, tidak pernah hardcode di kode.
- `.env.example` selalu diupdate setiap ada variabel baru.
- Default `.env` produksi: `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync` (kompatibel shared hosting tanpa Redis/worker).

## 8. Testing Minimal (opsional tapi disarankan)
- Setiap fitur inti (transaksi, kategori, outlet, auth) punya minimal 1 Feature Test happy-path.
- Test khusus untuk **outlet scoping**: pastikan Staff outlet A tidak bisa melihat/mengedit transaksi outlet B (baik lewat UI maupun manipulasi request langsung).

## 9. Git & Commit
- Commit kecil per fitur, pesan commit format: `feat: tambah CRUD kategori`, `fix: validasi upload bukti transaksi`, `chore: setup seeder default kategori`.
- Jangan commit `vendor/`, `node_modules/`, `.env`, `storage/app/public/proofs/*` (isi upload user).

## 10. Larangan
- ❌ Jangan tulis query mentah (`DB::raw`) kecuali benar-benar perlu agregasi kompleks yang tidak praktis via Eloquent — dan jika dipakai, harus pakai parameter binding (hindari SQL Injection).
- ❌ Jangan simpan nominal sebagai string/float.
- ❌ Jangan taruh logic otorisasi hanya di Blade (`@if($user->role=='admin')`) tanpa proteksi juga di Controller/Policy — Blade hanya untuk sembunyikan UI, bukan satu-satunya lapisan keamanan.
- ❌ Jangan gunakan package berat (Livewire, Inertia+Vue/React penuh) yang menambah kompleksitas build & runtime — sudah diputuskan pakai Blade + Alpine.js untuk menjaga aplikasi ringan.
