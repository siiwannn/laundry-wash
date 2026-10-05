# Deployment Guide

Brand : Laundry Wash

Version : 1.0

Framework : Laravel 12

Database : MySQL 8

Frontend : Blade + Bootstrap 5

---

# Production Stack

Operating System

- Ubuntu 22.04 LTS

Web Server

- Apache 2.4
atau
- Nginx

PHP

- PHP 8.3+

Database

- MySQL 8+

Package Manager

- Composer 2

Frontend

- Node.js 20+
- NPM

SSL

- HTTPS (Let's Encrypt)

---

# Server Requirement

PHP Extensions

- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- ZIP

---

# Environment (.env)

```env
APP_NAME="Laundry Wash"

APP_ENV=production

APP_DEBUG=false

APP_URL=https://your-domain.com

LOG_CHANNEL=stack

LOG_LEVEL=error

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=laundry_wash

DB_USERNAME=root

DB_PASSWORD=password

MIDTRANS_SERVER_KEY=SB-Mid-server-your-key

MIDTRANS_CLIENT_KEY=SB-Mid-client-your-key

MIDTRANS_IS_PRODUCTION=false

MIDTRANS_IS_SANITIZED=true

MIDTRANS_IS_3DS=true
```

Atur Payment Notification URL pada dashboard Midtrans Sandbox ke `https://your-domain.com/api/midtrans/notification`. Jangan commit server key atau client key ke repository.

---

# Deployment Steps

## 1. Clone Repository

```bash
git clone <repository-url>
```

---

## 2. Masuk Folder

```bash
cd laundry-wash
```

---

## 3. Install Dependency PHP

```bash
composer install --no-dev --optimize-autoloader
```

---

## 4. Install Dependency Frontend

```bash
npm install
```

---

## 5. Build Asset

```bash
npm run build
```

---

## 6. Copy Environment

```bash
cp .env.example .env
```

---

## 7. Generate Key

```bash
php artisan key:generate
```

---

## 8. Konfigurasi Database

Edit file

.env

---

## 9. Jalankan Migration

```bash
php artisan migrate --force
```

---

## 10. Jalankan Seeder (Opsional)

```bash
php artisan db:seed --force
```

---

## 11. Storage Link

```bash
php artisan storage:link
```

---

## 12. Cache

```bash
php artisan config:cache

php artisan route:cache

php artisan view:cache
```

---

## 13. Optimize

```bash
php artisan optimize
```

---

# Folder Permission

Pastikan folder berikut dapat ditulis:

storage/

bootstrap/cache/

Linux

```bash
chmod -R 775 storage

chmod -R 775 bootstrap/cache
```

---

# Rollback

Jika Migration gagal

```bash
php artisan migrate:rollback
```

Jika Cache bermasalah

```bash
php artisan optimize:clear
```

---

# Backup Database

Sebelum Deployment

```bash
mysqldump -u root -p laundry_wash > backup.sql
```

Restore

```bash
mysql -u root -p laundry_wash < backup.sql
```

---

# Security Checklist

- APP_DEBUG=false
- HTTPS aktif
- Password Database kuat
- .env tidak dapat diakses publik
- Validasi upload file
- CSRF aktif
- Session aman
- Password menggunakan Hash
- Tidak menyimpan secret di Git

---

# Free Hosting Recommendation

Untuk Demo Kampus

✅ Shared Hosting (PHP + MySQL)

✅ InfinityFree *(jika memenuhi kebutuhan dan batasannya tidak mengganggu)*

✅ Hostinger (Student Plan)

✅ Railway *(jika kuota masih tersedia)*

✅ VPS Trial

Tidak Direkomendasikan

❌ Vercel

Karena Vercel tidak dirancang untuk menjalankan aplikasi Laravel tradisional berbasis PHP secara penuh.

---

# Deployment Checklist

- Repository terbaru
- Dependency terpasang
- Database berhasil dibuat
- Migration berhasil
- Seeder berhasil
- Storage Link aktif
- Login berhasil
- Dashboard berhasil
- Order berhasil dibuat
- Tracking berjalan
- Pembayaran berjalan
- Webhook Midtrans dapat diakses melalui HTTPS
- Signature webhook tidak valid ditolak
- Tidak ada Error 500
- Semua halaman dapat diakses sesuai Role

---

# Post Deployment Testing

Customer

- Login
- Membuat Order
- Tracking
- Pembayaran

Admin

- Dashboard
- Confirm Order
- Assign Courier
- Input Berat
- Lihat Status Pembayaran

Courier

- Login
- Pickup
- GPS
- Delivery

---

# Definition of Done

Deployment dianggap berhasil apabila:

- Website dapat diakses.
- Login berhasil.
- Database terhubung.
- Semua Migration berhasil.
- Seluruh fitur P0 berjalan.
- Tidak terdapat Error Critical.
