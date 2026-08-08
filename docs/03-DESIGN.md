# UI/UX DESIGN — Buku Kas Digital

## 1. Prinsip Desain
- **Sederhana & cepat diinput** — target utama pengguna adalah kasir/staff yang input transaksi berkali-kali sehari, form harus minim klik.
- **Mobile-friendly** — banyak input transaksi kemungkinan dilakukan dari HP di lokasi outlet.
- **Jelas secara visual** antara pemasukan (hijau) dan pengeluaran (merah).
- Ringan: tanpa animasi berat, tanpa asset besar, load cepat di koneksi lambat.

## 2. Palet Warna

| Token | Warna | Penggunaan |
|---|---|---|
| Primary | `#1E40AF` (biru tua) | Tombol utama, navbar, link aktif |
| Success/Income | `#16A34A` (hijau) | Badge pemasukan, angka saldo positif |
| Danger/Expense | `#DC2626` (merah) | Badge pengeluaran, tombol hapus |
| Warning | `#D97706` (oranye) | Peringatan, status pending |
| Neutral background | `#F8FAFC` | Background halaman |
| Neutral surface | `#FFFFFF` | Card, tabel |
| Border | `#E2E8F0` | Garis pembatas |
| Text primary | `#0F172A` | Teks utama |
| Text secondary | `#64748B` | Label, keterangan kecil |

## 3. Tipografi
- Font: `Inter` atau `system-ui` (fallback), agar tidak perlu load font eksternal besar — cukup ringan.
- Skala: Heading 24px/20px/16px, body 14px, small/caption 12px.

## 4. Layout Utama

```
┌─────────────────────────────────────────────┐
│ Topbar: Logo | Nama Outlet aktif | User ▾    │
├───────────┬───────────────────────────────────┤
│ Sidebar   │  Konten halaman                    │
│ - Dashboard│                                    │
│ - Pemasukan│                                    │
│ - Pengeluaran│                                 │
│ - Laporan │                                    │
│ - Kategori*│  (*hanya tampil untuk Admin)       │
│ - Outlet* │                                    │
│ - User*   │                                    │
└───────────┴───────────────────────────────────┘
```
- Di mobile: sidebar berubah jadi bottom-nav atau hamburger drawer (Alpine.js toggle, tanpa reload).

## 5. Halaman-Halaman Utama

### 5.1 Login
- Form sederhana: email, password, tombol "Masuk". Tanpa opsi daftar sendiri.

### 5.2 Dashboard
- 3 kartu ringkasan atas: **Total Pemasukan**, **Total Pengeluaran**, **Saldo Berjalan** (periode & outlet bisa difilter via dropdown di atas).
- Grafik garis: tren pemasukan vs pengeluaran per hari/bulan (Chart.js).
- Grafik donat: breakdown per kategori (pemasukan & pengeluaran terpisah, atau tab toggle).
- Tabel 5–10 transaksi terbaru dengan link "Lihat semua".

### 5.3 Daftar Transaksi (Pemasukan / Pengeluaran)
- Filter bar: rentang tanggal, outlet (khusus Admin — Staff otomatis terkunci ke outlet-nya), kategori, kata kunci (atas nama/keterangan).
- Tabel kolom: Tanggal | Kategori | Outlet | Atas Nama | Jumlah | Bukti (ikon thumbnail) | Dicatat oleh | Aksi (edit/hapus, sesuai izin).
- Tombol "+ Tambah Pemasukan/Pengeluaran" mengambang di kanan atas (mobile: floating action button).
- Total baris berjalan (running total) di bagian bawah tabel/footer.

### 5.4 Form Tambah/Edit Transaksi
- Field urut sesuai kebiasaan input cepat:
  1. Tipe (income/expense) — otomatis terkunci sesuai halaman asal
  2. Tanggal (default hari ini)
  3. Kategori (dropdown, hanya kategori aktif sesuai tipe)
  4. Outlet (dropdown; untuk Staff otomatis terisi & terkunci)
  5. Jumlah (input angka dengan format ribuan otomatis, prefix "Rp")
  6. Atas Nama
  7. Keterangan (textarea, opsional)
  8. Upload Bukti (drag & drop / tap untuk kamera di mobile, preview thumbnail, opsional)
- Validasi inline (Alpine.js) sebelum submit agar tidak bolak-balik reload.

### 5.5 Detail Transaksi (modal atau halaman)
- Tampilkan semua field + gambar bukti ukuran penuh (klik untuk zoom/lightbox sederhana) + riwayat edit (siapa & kapan terakhir mengubah).

### 5.6 Manajemen Kategori (Admin)
- Dua tab: **Kategori Pemasukan** | **Kategori Pengeluaran**.
- List sederhana dengan tombol tambah, edit inline, toggle aktif/nonaktif, tanpa hapus permanen jika sudah dipakai transaksi (soft-disable saja).

### 5.7 Manajemen Outlet (Admin)
- List outlet + tombol tambah/edit, toggle aktif/nonaktif.

### 5.8 Manajemen User (Admin)
- List user: nama, email, role, outlet (jika staff), status aktif.
- Form tambah/edit: pilih role via dropdown, outlet muncul kondisional hanya jika role = Staff.

### 5.9 Laporan
- Filter periode custom (dari–sampai), outlet, tipe.
- Tabel hasil + tombol **Export Excel**, **Export PDF**, **Cetak**.
- Ringkasan total di atas tabel.

## 6. Komponen Reusable (Blade Components)
- `<x-badge type="income|expense" />`
- `<x-form.input>`, `<x-form.select>`, `<x-form.textarea>`, `<x-form.currency-input>`
- `<x-table>` dengan slot header/body + pagination bawaan
- `<x-modal>` (Alpine.js based, tanpa library modal eksternal)
- `<x-image-upload>` dengan preview
- `<x-empty-state>` untuk list kosong
- `<x-toast>` notifikasi sukses/error (auto-dismiss)

## 7. State Kosong & Error
- List transaksi kosong → ilustrasi ringan (SVG kecil) + teks "Belum ada transaksi" + tombol tambah.
- Error validasi → tampil di bawah field terkait (bukan alert global) + ringkasan error di atas form jika banyak.
- Error 403 (akses ditolak) → halaman khusus dengan pesan jelas, bukan error generik.

## 8. Responsif
- Breakpoint Tailwind standar (`sm`, `md`, `lg`).
- Tabel di mobile: mode "card list" (setiap baris jadi card ringkas) alih-alih scroll horizontal, supaya tetap nyaman dibaca di layar kecil.
- Form full-width di mobile, 2 kolom di desktop (`grid md:grid-cols-2`).

## 9. Aksesibilitas Ringan
- Kontras warna teks vs background memenuhi minimal AA.
- Semua tombol ikon punya `aria-label`.
- Form field punya `<label>` eksplisit (bukan hanya placeholder).
