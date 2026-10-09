# Laundry Wash

Aplikasi manajemen laundry berbasis web yang menghubungkan pelanggan, admin, dan kurir dalam satu alur operasional: pemesanan, penjemputan, pencucian, pembayaran, hingga pengantaran.

Laundry Wash menyediakan katalog layanan publik dan dashboard sesuai peran, dengan antarmuka responsif untuk desktop maupun perangkat mobile.

## Fitur utama

- **Akun dan akses:** registrasi pelanggan, login, pemulihan kata sandi, serta otorisasi berdasarkan peran.
- **Katalog dan pemesanan:** daftar layanan, pengelolaan alamat, pembuatan pesanan, dan riwayat transaksi.
- **Operasional laundry:** konfirmasi pesanan, pencatatan berat aktual, serta pembaruan tahap pencucian, pengeringan, dan penyetrikaan.
- **Manajemen kurir:** penugasan penjemputan dan pengantaran, status ketersediaan, serta riwayat tugas.
- **Pelacakan:** posisi kurir melalui GPS browser, peta interaktif, rute jalan, estimasi waktu tiba, dan jarak tersisa.
- **Pembayaran:** integrasi Midtrans Snap Sandbox untuk QRIS dan Virtual Account, dengan pembaruan status melalui webhook.
- **Administrasi:** pengelolaan pelanggan, kurir, layanan, pengaturan operasional, dashboard, dan laporan.

## Peran pengguna

| Peran | Akses utama |
| --- | --- |
| Admin | Mengelola layanan, pelanggan, kurir, pesanan, proses laundry, dan laporan. |
| Pelanggan | Memesan layanan, mengelola alamat, membayar, serta memantau status pesanan dan posisi kurir. |
| Kurir | Menerima tugas, memperbarui status penjemputan atau pengantaran, dan mengirim lokasi GPS. |

## Teknologi

| Komponen | Teknologi |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+ |
| Antarmuka | Blade, Bootstrap 5, JavaScript |
| Build aset | Vite 8, npm |
| Database | MySQL 8 untuk target aplikasi; SQLite sebagai konfigurasi awal pengembangan dan database pengujian |
| Peta dan rute | MapLibre GL JS, OpenFreeMap, OSRM; Leaflet untuk komponen peta lainnya |
| Lokasi kurir | Browser Geolocation API dan AJAX polling |
| Pembayaran | Midtrans Snap Sandbox |
| Pengujian | PHPUnit 12 dan Node.js test runner |

Versi dependensi yang digunakan tercatat di `composer.lock` dan `package-lock.json`.

## Menjalankan secara lokal

### Prasyarat

- PHP 8.3 atau lebih baru, Composer, dan ekstensi PHP yang diperlukan Laravel serta PHPUnit, termasuk cURL, DOM/XML, Fileinfo, Mbstring, dan PDO.
- Ekstensi `pdo_sqlite` untuk konfigurasi awal atau `pdo_mysql` jika menggunakan MySQL.
- Node.js 20.19+ atau 22.12+ sesuai persyaratan Vite 8, beserta npm.
- Git dan akses internet untuk mengunduh dependensi serta font saat build.

### 1. Unduh proyek dan instal dependensi

```bash
git clone https://github.com/siiwannn/laundry-wash.git
cd laundry-wash
composer install --no-interaction --prefer-dist
npm ci --ignore-scripts
```

### 2. Siapkan konfigurasi aplikasi

Untuk instalasi baru:

```bash
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
```

Sesuaikan `APP_NAME`, `APP_URL`, dan pengaturan database di `.env`. Jangan menghasilkan ulang `APP_KEY` pada instalasi yang sudah digunakan karena dapat memengaruhi data terenkripsi dan sesi.

Konfigurasi awal menggunakan SQLite. Buat file database dan jalankan migrasi:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

Untuk MySQL 8, buat database terlebih dahulu, lalu atur `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sebelum menjalankan migrasi. Driver database ini juga didukung oleh konfigurasi Laravel proyek.

### 3. Build aset dan jalankan aplikasi

```bash
npm run build
php artisan serve
```

Server pengembangan berjalan pada alamat yang ditampilkan oleh Artisan, secara default `127.0.0.1:8000`. Untuk pembaruan aset otomatis selama pengembangan, jalankan `npm run dev` pada terminal terpisah.

Build aset mengunduh font dari `fonts.bunny.net`. Jika menggunakan proxy dengan pembatasan jaringan, izinkan domain tersebut. Node.js yang mendukung proxy melalui environment dapat menggunakan `NODE_USE_ENV_PROXY=1 npm run build`.

### Data demo (opsional)

Seeder menyediakan contoh pengguna, layanan, pesanan, dan tugas kurir:

```bash
php artisan db:seed
```

Akun demo menggunakan email `admin@laundrywash.com`, `customer@laundrywash.com`, dan `courier@laundrywash.com`, dengan kata sandi `password`. Gunakan hanya pada lingkungan pengembangan atau demo yang terisolasi.

## Integrasi eksternal

Konfigurasi integrasi tersedia di `.env.example` dan `config/services.php`.

| Integrasi | Konfigurasi |
| --- | --- |
| Midtrans Sandbox | `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, dan `MIDTRANS_IS_PRODUCTION=false` |
| Login Google | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, dan `GOOGLE_REDIRECT_URI` |
| Style peta | `MAP_STYLE_URL` |
| Routing jalan | `ROUTING_URL` |
| Informasi cuaca | `WEATHER_API_URL` — menggunakan Open-Meteo secara default |

Webhook pembayaran berada pada endpoint `POST /api/midtrans/notification`. Untuk menerima notifikasi dari Midtrans, endpoint harus dapat diakses oleh layanan tersebut. Pengujian integrasi pembayaran dan login Google secara langsung memerlukan kredensial masing-masing layanan.

Pelacakan lokasi memerlukan izin lokasi dari pengguna dan konteks browser yang aman, seperti HTTPS atau localhost. Lokasi dikirim secara berkala selama tugas penjemputan atau pengantaran aktif.

Simpan kredensial di konfigurasi lingkungan. Jangan memasukkan `.env`, token, atau kunci layanan ke dalam commit.

## Pengujian

Jalankan suite PHP dan pengujian navigasi JavaScript dari direktori proyek:

```bash
composer test
node --test tests/js/*.test.mjs
```

Suite PHP mencakup autentikasi, otorisasi, katalog, pengelolaan pelanggan, alur pesanan, GPS, pembayaran, pengaturan, dan tampilan dashboard. Konfigurasi `phpunit.xml` menggunakan SQLite di memori sehingga pengujian tidak memakai database pengembangan.

Untuk memeriksa build frontend:

```bash
npm run build
```

## Struktur proyek

```text
app/
├── Enums/           # Peran pengguna serta status pesanan dan pembayaran
├── Http/            # Controller, middleware, dan validasi request
├── Models/          # Model Eloquent
└── Services/        # Logika bisnis dan integrasi
database/            # Migration, factory, dan seeder
resources/           # Template Blade serta sumber aset
public/              # Aset publik
routes/              # Route web, API, dan console
tests/               # Pengujian unit, fitur, dan JavaScript
```

## Panduan pengembangan

- Baca [AGENTS.md](AGENTS.md) untuk aturan arsitektur, alur bisnis, dan kontribusi oleh coding agent.
- Baca [DESIGN.md](DESIGN.md) untuk acuan desain antarmuka.
- Gunakan migration untuk perubahan database, Form Request untuk validasi, dan Service untuk logika bisnis.
- Pertahankan otorisasi pada backend dan jalankan pengujian yang relevan sebelum mengirim perubahan.

Dokumen perencanaan bernomor `01`–`12` disimpan secara lokal dan dikecualikan dari Git. Dokumen tersebut tidak disertakan dalam hasil clone repository.
