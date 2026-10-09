# 🧺 Laundry Wash

Aplikasi manajemen laundry berbasis web yang menyatukan pelanggan, admin, dan kurir dalam satu alur operasional — dari pemesanan, penjemputan, pencucian, pembayaran, hingga pengantaran.

Dibangun dengan **Laravel 13** dan **MySQL 8**, featuring katalog layanan publik, dashboard per peran, pelacakan kurir secara real-time, dan pembayaran via Midtrans Snap.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![MapLibre](https://img.shields.io/badge/MapLibre_GL-5-3A3A3A?style=flat-square)

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Peran Pengguna](#-peran-pengguna)
- [Teknologi](#-teknologi)
- [Menjalankan Secara Lokal](#-menjalankan-secara-lokal)
- [Integrasi Eksternal](#-integrasi-eksternal)
- [Pengujian](#-pengujian)
- [Struktur Proyek](#-struktur-proyek)
- [Panduan Pengembangan](#-panduan-pengembangan)

---

## ✨ Fitur Utama

<table>
<tr><td width="50%">

**👤 Akun dan Akses**
- Registrasi pelanggan & login
- Pemulihan kata sandi
- Otorisasi berbasis peran
- Login Google (OAuth)

**🛒 Katalog dan Pemesanan**
- Daftar layanan dengan harga dinamis
- Pengelolaan alamat pengiriman
- Pembuatan pesanan
- Riwayat transaksi

**🧪 Operasional Laundry**
- Konfirmasi pesanan
- Pencatatan berat aktual
- Tahap pencucian, pengeringan, penyetrikaan

</td><td width="50%">

**🚚 Manajemen Kurir**
- Penugasan penjemputan & pengantaran
- Status ketersediaan kurir
- Riwayat tugas

**📍 Pelacakan**
- GPS browser (Geolocation API)
- Peta interaktif (MapLibre GL)
- Rute jalan + estimasi tiba
- Sisa jarak ke tujuan

**💳 Pembayaran**
- Midtrans Snap Sandbox
- QRIS & Virtual Account
- Webhook status real-time

**📊 Administrasi**
- Manajemen pelanggan, kurir, layanan
- Dashboard operasional
- Laporan & audit activity log

</td></tr>
</table>

---

## 👥 Peran Pengguna

| Peran | Akses Utama |
| :--- | :--- |
| **Admin** | Mengelola layanan, pelanggan, kurir, pesanan, proses laundry, dan laporan. |
| **Pelanggan** | Memesan layanan, mengelola alamat, membayar, serta memantau status pesanan dan posisi kurir. |
| **Kurir** | Menerima tugas, memperbarui status penjemputan/pengantaran, dan mengirim lokasi GPS. |

Setiap peran Routes dan Policy terpisah untuk memastikan akses hanya diberikan kepada pihak yang berhak.

---

## 🛠️ Teknologi

| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | Laravel 13, PHP 8.3+ |
| **Antarmuka** | Blade, Bootstrap 5, JavaScript |
| **Build Aset** | Vite 8, npm |
| **Database** | MySQL 8 (produksi) · SQLite (dev & test) |
| **Peta & Rute** | MapLibre GL JS, OpenFreeMap, OSRM; Leaflet untuk peta tambahan |
| **Lokasi Kurir** | Browser Geolocation API + AJAX polling |
| **Pembayaran** | Midtrans Snap Sandbox |
| **Pengujian** | PHPUnit 12, Node.js test runner |

Versi dependensi persis terkunci di `composer.lock` dan `package-lock.json`.

---

## 🚀 Menjalankan Secara Lokal

### Prasyarat

- **PHP 8.3+** — beserta ekstensi cURL, DOM/XML, Fileinfo, Mbstring, dan PDO
- **Database driver** — `pdo_sqlite` (default dev) atau `pdo_mysql` (MySQL 8)
- **Node.js 20.19+** atau **22.12+** (persyaratan Vite 8), beserta npm
- **Git** dan akses internet untuk dependensi

### 1️⃣ Clone & Instal Dependensi

```bash
git clone https://github.com/siiwannn/laundry-wash.git
cd laundry-wash
composer install --no-interaction --prefer-dist
npm ci --ignore-scripts
```

### 2️⃣ Konfigurasi Aplikasi

```bash
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
```

Sesuaikan `APP_NAME`, `APP_URL`, dan pengaturan database di `.env`.

> ⚠️ **Jangan** menjalankan ulang `php artisan key:generate` pada instalasi yang sudah aktif — ini dapat merusak data terenkripsi dan sesi pengguna.

**Opsi A — SQLite (paling cepat):**

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

**Opsi B — MySQL 8:**

Buat database, lalu set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sebelum migrate.

### 3️⃣ Build Aset & Jalankan

```bash
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`. Untuk hot-reload selama pengembangan, jalankan `npm run dev` di terminal terpisah.

> **Catatan jaringan:** Build aset mengunduh font dari `fonts.bunny.net`. Di balik proxy, izinkan domain tersebut atau set `NODE_USE_ENV_PROXY=1`.

### 🌱 Data Demo (opsional)

```bash
php artisan db:seed
```

| Peran | Email | Kata Sandi |
| :--- | :--- | :--- |
| Admin | `admin@laundrywash.com` | `password` |
| Pelanggan | `customer@laundrywash.com` | `password` |
| Kurir | `courier@laundrywash.com` | `password` |

> ⚠️ Akun demo hanya untuk lingkungan pengembangan atau demo yang terisolasi. **Jangan** dipakai di produksi.

---

## 🔌 Integrasi Eksternal

Seluruh konfigurasi tersedia di `.env.example` dan `config/services.php`.

| Integrasi | Variabel |
| :--- | :--- |
| **Midtrans Sandbox** | `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false` |
| **Login Google** | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` |
| **Style Peta** | `MAP_STYLE_URL` |
| **Routing Jalan** | `ROUTING_URL` |
| **Info Cuaca** | `WEATHER_API_URL` (default: Open-Meteo) |

**Webhook Pembayaran:** `POST /api/midtrans/notification`
Endpoint ini wajib dapat diakses langsung oleh layanan Midtrans. Pengujian integrasi pembayaran dan login Google memerlukan kredensial masing-masing layanan.

**Pelacakan Lokasi:** Memerlukan izin lokasi pengguna dan konteks browser aman (HTTPS atau localhost). Koordinat dikirim berkala selama penugasan aktif.

> 🔒 Simpan kredensial di environment. **Jangan pernah** commit `.env`, token, atau kunci layanan.

---

## 🧪 Pengujian

```bash
# PHP test suite
composer test

# JavaScript navigation tests
node --test tests/js/*.test.mjs

# Verifikasi build frontend
npm run build
```

Suite PHP mencakup autentikasi, otorisasi, katalog, manajemen pelanggan, alur pesanan, GPS, pembayaran, pengaturan, dan dashboard. `phpunit.xml` memakai SQLite in-memory sehingga test tidak menyentuh database pengembangan.

---

## 📁 Struktur Proyek

```text
app/
├── Enums/       # Peran pengguna, status pesanan & pembayaran
├── Http/        # Controller, middleware, Form Request
├── Models/      # Model Eloquent
├── Policies/    # Otorisasi tingkat model
└── Services/    # Logika bisnis & integrasi eksternal
database/        # Migration, factory, seeder
resources/       # Template Blade & sumber aset
routes/          # Route web, API, console
tests/           # Unit, Feature, dan JavaScript tests
```

Arsitektur mengikuti **MVC + Service Layer**: controller tetap tipis, seluruh logika bisnis berada di Service, dan validasi\input handled oleh Form Request.

---

## 🧑‍💻 Panduan Pengembangan

Baca dokumen berikut sebelum Contrib:

- **[AGENTS.md](AGENTS.md)** — aturan arsitektur, alur bisnis, dan kontribusi coding agent **(WAJIB)**
- **[DESIGN.md](DESIGN.md)** — acuan desain antarmuka

Konvensi yang harus dijaga:

- ✅ Gunakan **migration** untuk perubahan database
- ✅ Gunakan **Form Request** untuk validasi input
- ✅ Tempatkan logika bisnis di **Service**, bukan Controller
- ✅ Terapkan **Policy** untuk otorisasi
- 🚫 Hindari **raw SQL** kecuali benar-benar diperlukan
- ✅ Jalankan pengujian relevan sebelum mengirim perubahan

Dokumen perencanaan bernomor `01`–`12` disimpan secara lokal dan dikecualikan dari Git.