# Laundry Wash

Dokumentasi utama untuk pengembangan Sistem Laundry berbasis web.

## Ringkasan
Sistem ini mengelola laundry end-to-end: pickup dan delivery oleh kurir, proses laundry, pembayaran otomatis Midtrans, serta tracking GPS.

## Tech Stack
- Laravel 13 (versi project berjalan)
- PHP 8.3+
- MySQL 8
- Blade Template
- Bootstrap 5
- JavaScript
- Leaflet + OpenStreetMap untuk peta
- Midtrans Snap Sandbox untuk QRIS dan Virtual Account
- Browser Geolocation API untuk posisi kurir
- Git & GitHub

## Role
- Admin
- Customer
- Kurir

## Fitur Utama
- Authentication
- Manajemen user
- Manajemen customer
- Manajemen kurir
- Manajemen layanan laundry
- Pembuatan order
- Assign kurir
- Pickup laundry
- Tracking status order
- Tracking posisi kurir
- Proses laundry
- Pembayaran otomatis melalui webhook Midtrans
- Pengantaran
- Riwayat transaksi
- Dashboard
- Laporan
- Katalog layanan publik dengan CTA pendaftaran dan pemesanan
- Antarmuka responsif untuk desktop dan mobile, termasuk sidebar yang dapat dibuka/tutup pada workspace

## Pembaruan Antarmuka

- Halaman katalog publik menggunakan identitas visual Laundry Wash bernuansa oranye.
- Dashboard Admin, Customer, dan Kurir memakai komponen tombol, badge status, kartu, dan sidebar yang konsisten.
- Tampilan mobile memprioritaskan navigasi yang dapat diakses, grid kartu adaptif, serta area aksi pembayaran yang tidak terpotong.

## Dokumen
1. `01-prd.md`
2. `02-requirements.md`
3. `03-user-stories.md`
4. `04-usecase.md`
5. `05-workflow.md`
6. `06-database-design.md`
7. `07-api-spec.md`
8. `08-ui-flow.md`
9. `09-role-permission.md`
10. `10-development-plan.md`
11. `11-testing-plan.md`
12. `12-deployment.md`
13. `AGENTS.md`
14. `DESIGN.md`

## Status MVP
Target implementasi: 2–3 minggu.

Prioritas utama adalah menyelesaikan alur order end-to-end terlebih dahulu sebelum menambah fitur tambahan.
