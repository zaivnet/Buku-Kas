# PANDUAN DEPLOYMENT DOCKER & PROXMOX VE

Dokumen ini berisi panduan lengkap menginstall **Buku Kas Digital** di **Proxmox VE** (baik menggunakan **LXC Container** maupun **Virtual Machine / VM**) dengan Docker & Docker Compose.

---

## 🚀 Cara Cepat (1-Command Deployment)

Apabila Docker dan Docker Compose sudah terpasang di Proxmox LXC / VM Anda:

```bash
# 1. Clone repositori dari GitHub
git clone https://github.com/zaivnet/Buku-Kas.git
cd Buku-Kas

# 2. Buka dan jalankan stack container (App + MariaDB)
docker compose up -d --build
```

Setelah beberapa detik, aplikasi **Buku Kas Digital** akan langsung aktif dan siap diakses melalui browser:
`http://IP_PROXMOX_ANDA:1990`

---

## 🔑 Akun Login Default Setelah Install:
- **Admin**: `admin@bukukas.local` / `password123`
- **Staff Outlet 1**: `staff1@bukukas.local` / `password123`

---

## 🛠️ Panduan Persiapan di Proxmox VE

### Opsi A: Menggunakan Proxmox LXC Container (Ringan & Hemat RAM)

1. **Buat CT (Container) Baru di Proxmox**:
   - Template: **Ubuntu 22.04 LTS** atau **Debian 12**.
   - RAM: Minimum `1 GB` (Rekomendasi `2 GB`).
   - Disk: `10 GB` - `20 GB`.
   - CPU: `1` atau `2` Core.
   - **PENTING (Centang Fitur Nesting)**: Pada tab *Options* -> *Features* -> Centang **Nesting** (`keyctl=1,nesting=1`). Ini wajib agar Docker dapat berjalan di dalam LXC Proxmox.

2. **Install Docker di Dalam LXC Container**:
   ```bash
   curl -fsSL https://get.docker.com -o get-docker.sh
   sh get-docker.sh
   ```

3. **Jalankan Aplikasi**:
   ```bash
   git clone https://github.com/zaivnet/Buku-Kas.git
   cd Buku-Kas
   docker compose up -d
   ```

---

### Opsi B: Menggunakan Proxmox Virtual Machine (VM)

1. Buat VM baru (Ubuntu Server 22.04 LTS / 24.04 LTS).
2. Install Docker & Docker Compose.
3. Clone repositori & jalankan `docker compose up -d`.

---

## ⚙️ Mengubah Konfigurasi & Password di `docker-compose.yml`

Sebelum meluncurkan ke lingkungan produksi publik, Anda disarankan mengubah kunci enkripsi dan password database pada berkas `docker-compose.yml`:

```yaml
environment:
  APP_KEY: "base64:PASTE_APP_KEY_BARU_DI_SINI"
  DB_PASSWORD: "password_db_rahasia_anda"
```

Untuk menggenerate `APP_KEY` baru:
```bash
docker compose exec app php artisan key:generate --show
```

---

## 📦 Mengelola Container

- **Melihat Status Container**:
  ```bash
  docker compose ps
  ```

- **Melihat Log Aplikasi**:
  ```bash
  docker compose logs -f app
  ```

- **Menjalankan Perintah Artisan**:
  ```bash
  docker compose exec app php artisan migrate
  ```

- **Menghentikan Container**:
  ```bash
  docker compose down
  ```
