# Product Requirement Document (PRD)

# Laundry Wash

Versi : 1.0

Dokumen ini menjadi acuan utama (Source of Truth) dalam pengembangan sistem Laundry Wash. Seluruh implementasi harus mengikuti dokumen ini.

---

# 1. Informasi Proyek

## Nama Produk

Laundry Wash

## Jenis Produk

Aplikasi Web Manajemen Laundry

## Platform

Web Browser

## Framework

Laravel 12

## Bahasa

PHP 8.3

## Database

MySQL

## Frontend

Blade

Bootstrap 5

Javascript

## Peta

Leaflet.js

OpenStreetMap

---

# 2. Latar Belakang

Sebagian besar usaha laundry masih menggunakan pencatatan manual sehingga sering terjadi berbagai kendala seperti:

- Data pelanggan tidak terorganisir.
- Status laundry sulit dipantau pelanggan.
- Admin kesulitan mengelola banyak pesanan.
- Kurir tidak memiliki sistem penugasan yang jelas.
- Pelanggan tidak mengetahui posisi kurir saat pickup maupun delivery.
- Perhitungan biaya sering dilakukan secara manual sehingga rentan kesalahan.

Laundry Wash dibuat untuk mendigitalisasi seluruh proses bisnis laundry mulai dari pelanggan membuat pesanan hingga laundry selesai diterima kembali oleh pelanggan.

---

# 3. Tujuan Produk

Laundry Wash dibuat untuk:

- Mempermudah pelanggan membuat pesanan laundry.
- Mempermudah admin mengelola seluruh proses laundry.
- Mempermudah kurir melakukan pickup dan delivery.
- Menampilkan status laundry secara real-time.
- Menampilkan posisi kurir ketika pickup maupun delivery.
- Mengurangi kesalahan pencatatan transaksi.
- Mempercepat proses operasional laundry.

---

# 4. Target Pengguna

Sistem hanya memiliki tiga role.

## Customer

Pelanggan yang menggunakan jasa laundry.

Hak akses:

- Registrasi
- Login
- Membuat pesanan
- Melihat status laundry
- Melihat tracking kurir
- Mengelola profil
- Melihat riwayat transaksi
- Melakukan pembayaran

---

## Admin

Petugas operasional laundry.

Hak akses:

- Login
- Mengelola pelanggan
- Mengelola pesanan
- Mengelola harga laundry
- Menentukan berat aktual
- Menghitung total biaya
- Menugaskan kurir
- Mengubah status laundry
- Memverifikasi pembayaran
- Melihat laporan

---

## Courier

Petugas pickup dan delivery.

Hak akses:

- Login
- Melihat daftar tugas
- Melakukan pickup
- Melakukan delivery
- Mengirim lokasi GPS
- Mengubah status tugas
- Melihat riwayat tugas

---

# 5. Permasalahan

Permasalahan utama yang ingin diselesaikan:

- Pencatatan masih manual.
- Tidak ada tracking status laundry.
- Tidak ada tracking lokasi kurir.
- Penugasan kurir belum terstruktur.
- Perhitungan biaya masih manual.
- Riwayat transaksi sulit dicari.

---

# 6. Solusi

Laundry Wash menyediakan sistem terintegrasi yang mencakup:

- Authentication
- Dashboard
- Order Management
- Pickup Management
- Laundry Processing
- Delivery Management
- Payment Management
- GPS Tracking
- Reporting

---

# 7. Ruang Lingkup MVP

## Fitur yang termasuk

- Login
- Register Customer
- Dashboard Customer
- Dashboard Admin
- Dashboard Courier
- CRUD Customer
- CRUD Courier
- Pengaturan Harga Laundry
- Membuat Order
- Assign Kurir
- Pickup
- Delivery
- Tracking Status Laundry
- Live GPS Kurir
- Input Berat Aktual
- Hitung Harga Otomatis
- Pembayaran
- Riwayat Pesanan
- Laporan Sederhana

---

## Tidak termasuk pada MVP

- Mobile App Android
- Mobile App iOS
- Payment Gateway
- WhatsApp Notification
- Email Notification
- Loyalty Point
- Promo
- Voucher
- Multi Cabang
- AI Recommendation
- Optimasi Rute Kurir
- GPS setingkat aplikasi ride-hailing

---

# 8. Teknologi

Backend

- Laravel 12

Frontend

- Blade
- Bootstrap 5

Database

- MySQL

Maps

- Leaflet.js
- OpenStreetMap

GPS

- Browser Geolocation API

Deployment

- Shared Hosting

---

# 9. Business Flow

## Alur Utama Sistem

Customer Registrasi

↓

Login

↓

Membuat Order Laundry

↓

Memilih Metode Penyerahan

- Pickup
- Antar Sendiri

↓

Jika Pickup

Customer memilih:

- Alamat
- Tanggal Pickup
- Jam Pickup

↓

Admin menerima order

↓

Admin melakukan verifikasi order

↓

Admin menugaskan Courier

↓

Courier menuju lokasi Customer

↓

Courier melakukan Pickup

↓

Courier mengantar laundry ke outlet

↓

Admin menerima laundry

↓

Admin melakukan penimbangan

↓

Sistem menghitung total biaya otomatis

↓

Laundry diproses

- Washing
- Drying
- Ironing

↓

Laundry selesai

↓

Customer melakukan pembayaran

↓

Admin melakukan verifikasi pembayaran

↓

Admin menugaskan Courier Delivery

↓

Courier mengantar laundry

↓

Customer menerima laundry

↓

Order selesai

---

# 10. Business Rules

## BR-01

Sistem hanya mendukung layanan Laundry Kiloan.

---

## BR-02

Harga laundry dihitung berdasarkan:

Berat Aktual × Harga per Kilogram

---

## BR-03

Berat aktual hanya dapat diinput oleh Admin.

---

## BR-04

Customer hanya dapat melakukan pembayaran apabila status laundry sudah READY.

---

## BR-05

Courier hanya dapat melakukan Delivery apabila pembayaran sudah dikonfirmasi.

---

## BR-06

Customer hanya dapat melihat data miliknya sendiri.

---

## BR-07

Courier hanya dapat melihat tugas yang diberikan kepadanya.

---

## BR-08

Admin memiliki akses penuh terhadap seluruh data operasional.

---

## BR-09

Order yang dibatalkan tidak dapat diproses kembali.

---

## BR-10

Semua perubahan status harus tercatat pada Riwayat Status Order.

---

# 11. Status Order

Status order harus mengikuti urutan berikut.

pending

↓

confirmed

↓

pickup_assigned

↓

courier_to_pickup

↓

picked_up

↓

received_at_laundry

↓

washing

↓

drying

↓

ironing

↓

ready

↓

waiting_payment

↓

paid

↓

delivery_assigned

↓

courier_to_customer

↓

delivered

↓

completed

---

Status cancelled dapat dilakukan sebelum laundry mulai diproses.

---

# 12. Pembayaran

Pembayaran dilakukan setelah laundry selesai.

Metode pembayaran:

- Tunai
- Transfer Bank
- QRIS

Tidak menggunakan Payment Gateway.

Admin melakukan verifikasi pembayaran secara manual.

Status pembayaran:

pending

paid

failed

---

# 13. Live GPS Tracking

Live GPS hanya aktif ketika:

Pickup sedang berlangsung.

atau

Delivery sedang berlangsung.

Teknologi:

Leaflet.js

OpenStreetMap

Browser Geolocation API

navigator.geolocation.watchPosition()

Lokasi dikirim setiap:

10 detik

Menggunakan:

AJAX Polling

Tidak menggunakan:

Firebase

Redis

Socket.io

Pusher

WebSocket

Data GPS yang disimpan:

- Latitude
- Longitude
- Accuracy
- Updated_at

Customer dapat melihat posisi Courier selama proses Pickup maupun Delivery.

---

# 14. Functional Requirements

## Authentication

FR-01

Customer dapat melakukan registrasi.

FR-02

Semua pengguna dapat login.

FR-03

Semua pengguna dapat logout.

---

## Customer

FR-04

Customer dapat membuat order.

FR-05

Customer dapat memilih Pickup atau Antar Sendiri.

FR-06

Customer dapat melihat status laundry.

FR-07

Customer dapat melihat posisi Courier.

FR-08

Customer dapat melihat riwayat transaksi.

FR-09

Customer dapat melakukan pembayaran.

---

## Admin

FR-10

Admin dapat mengelola order.

FR-11

Admin dapat menginput berat aktual.

FR-12

Admin dapat menghitung total harga otomatis.

FR-13

Admin dapat mengubah status laundry.

FR-14

Admin dapat menugaskan Courier.

FR-15

Admin dapat memverifikasi pembayaran.

FR-16

Admin dapat melihat laporan.

---

## Courier

FR-17

Courier dapat melihat daftar tugas.

FR-18

Courier dapat melakukan Pickup.

FR-19

Courier dapat melakukan Delivery.

FR-20

Courier dapat mengirim lokasi GPS selama perjalanan.

---

# 15. Non Functional Requirements

Sistem harus:

- Responsive.
- Aman.
- Mudah digunakan.
- Mobile Friendly.
- Menggunakan Bootstrap 5.
- Menggunakan Laravel 12.
- Menggunakan MySQL.
- Menggunakan Blade.
- Menggunakan Leaflet.
- Menggunakan OpenStreetMap.

---

# 16. Acceptance Criteria

Sistem dianggap selesai apabila:

✅ Customer dapat membuat order.

✅ Admin dapat menerima order.

✅ Courier dapat melakukan pickup.

✅ Laundry dapat diproses.

✅ Berat aktual dapat diinput.

✅ Total harga dihitung otomatis.

✅ Customer dapat melakukan pembayaran.

✅ Courier dapat melakukan delivery.

✅ Customer dapat melihat tracking GPS.

✅ Order dapat selesai.

✅ Riwayat transaksi tersimpan.

✅ Dashboard menampilkan data sesuai role.

---

# 17. User Journey

## 17.1 Customer Journey

1. Customer membuka website Laundry Wash.
2. Customer melakukan registrasi atau login.
3. Customer membuat pesanan baru.
4. Customer memilih metode penyerahan:
   - Pickup
   - Antar sendiri
5. Jika Pickup, customer memilih alamat, tanggal, dan slot waktu.
6. Customer mengirim pesanan.
7. Customer menunggu konfirmasi admin.
8. Customer memantau status laundry.
9. Customer melihat posisi kurir saat pickup dan delivery berlangsung.
10. Customer menerima notifikasi bahwa laundry selesai.
11. Customer melakukan pembayaran.
12. Customer menerima laundry.
13. Customer melihat riwayat transaksi.

---

## 17.2 Admin Journey

1. Login.
2. Melihat dashboard.
3. Memeriksa order baru.
4. Memverifikasi order.
5. Menugaskan courier pickup.
6. Menerima laundry.
7. Menginput berat aktual.
8. Sistem menghitung harga otomatis.
9. Mengubah status laundry.
10. Memverifikasi pembayaran.
11. Menugaskan courier delivery.
12. Menutup order.
13. Melihat laporan.

---

## 17.3 Courier Journey

1. Login.
2. Melihat daftar tugas.
3. Membuka detail tugas.
4. Menekan tombol "Mulai Pickup".
5. GPS aktif.
6. Sistem mengirim lokasi setiap 10 detik.
7. Pickup selesai.
8. Laundry diantar ke outlet.
9. Menunggu tugas delivery.
10. Menekan tombol "Mulai Delivery".
11. GPS aktif kembali.
12. Laundry diterima customer.
13. Menyelesaikan tugas.

---

# 18. Hak Akses

## Customer

- Register
- Login
- Membuat Order
- Melihat Status
- Tracking Kurir
- Riwayat
- Pembayaran
- Profil

---

## Admin

- Login
- Dashboard
- Kelola Order
- Kelola Customer
- Kelola Courier
- Kelola Harga
- Assign Courier
- Input Berat
- Update Status
- Verifikasi Pembayaran
- Laporan

---

## Courier

- Login
- Dashboard
- Daftar Pickup
- Daftar Delivery
- Tracking GPS
- Update Status
- Riwayat

---

# 19. Validasi Sistem

## Order

- Customer wajib login.
- Alamat pickup wajib diisi jika memilih pickup.
- Berat aktual tidak boleh kosong setelah laundry diterima.
- Total harga dihitung otomatis.
- Order Number harus unik.

---

## Pembayaran

- Pembayaran hanya dapat dilakukan ketika status READY.
- Admin wajib melakukan konfirmasi pembayaran.
- Courier tidak dapat delivery sebelum status PAID.

---

## Tracking

- GPS hanya aktif ketika pickup atau delivery.
- GPS berhenti ketika tugas selesai.
- Customer hanya dapat melihat posisi courier miliknya.

---

# 20. Dashboard

## Dashboard Customer

Menampilkan:

- Order Aktif
- Status Laundry
- Tracking Kurir
- Riwayat
- Total Order

---

## Dashboard Admin

Menampilkan:

- Total Customer
- Total Courier
- Order Aktif
- Laundry Diproses
- Pendapatan Hari Ini
- Pendapatan Bulan Ini
- Order Selesai
- Grafik Pendapatan

---

## Dashboard Courier

Menampilkan:

- Pickup Hari Ini
- Delivery Hari Ini
- Tugas Berjalan
- Riwayat
- Status Online

---

# 21. Keamanan

Sistem wajib:

- Menggunakan Middleware.
- Menggunakan CSRF Protection.
- Menggunakan Form Request Validation.
- Menggunakan Password Hashing.
- Menggunakan Authorization Policy.
- Menggunakan Eloquent ORM.
- Mencegah SQL Injection.
- Mencegah XSS.

---

# 22. Batasan Sistem

Sistem ini hanya mendukung:

- Laundry Kiloan
- Satu Outlet
- Satu Admin
- Multi Courier
- Customer Tidak Terbatas

Belum mendukung:

- Multi Cabang
- Multi Outlet
- Membership
- Promo
- Voucher
- Payment Gateway
- Mobile Apps

---

# 23. Future Development

Versi berikutnya dapat menambahkan:

- Multi Outlet
- Multi Cabang
- Membership
- Promo
- Voucher
- WhatsApp Notification
- Email Notification
- Push Notification
- Payment Gateway
- Optimasi Rute Kurir
- Scan QR Order
- Barcode Laundry
- Rating Customer
- Review Customer
- Dashboard Owner
- Mobile Android
- Mobile iOS

---

# 24. Definition of Done (DoD)

Proyek dianggap selesai apabila:

- Semua Functional Requirement telah selesai.
- Semua halaman dapat diakses sesuai role.
- Seluruh CRUD berjalan dengan baik.
- Tracking GPS berjalan.
- Perhitungan harga berjalan otomatis.
- Status laundry berjalan sesuai alur.
- Pembayaran dapat diverifikasi.
- Tidak terdapat error kritis.
- Seluruh pengujian P0 berhasil.
- Siap dipresentasikan.

---

# 25. Lampiran

Dokumen pendukung:

- BA Document
- UML
- ERD
- Use Case Diagram
- Activity Diagram
- Sequence Diagram
- UI/UX Design
- Database Design
- API Documentation
- AGENTS.md
- TASKS.md