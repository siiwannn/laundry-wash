# System Requirements

Brand : Laundry Wash

Versi : 1.0

---

# 1. Functional Requirements

## Authentication

FR-01
User dapat login menggunakan email dan password.

FR-02
Customer dapat melakukan registrasi akun.

FR-03
User dapat logout.

FR-04
Sistem membatasi akses halaman berdasarkan Role.

Role:

- Customer
- Admin
- Courier

---

## Customer

FR-05
Melihat dashboard.

FR-06
Melihat dan mengubah profil.

FR-07
Menambah, mengubah, dan menghapus alamat.

FR-08
Membuat order laundry.

FR-09
Memilih metode penyerahan:

- Pickup
- Antar Sendiri

FR-10
Menentukan alamat pickup.

FR-11
Menentukan tanggal pickup.

FR-12
Menentukan jam pickup.

FR-13
Menambahkan catatan khusus.

FR-14
Melihat detail order.

FR-15
Melihat status laundry.

FR-16
Melihat tracking GPS Courier saat Pickup atau Delivery berlangsung.

FR-17
Melihat riwayat transaksi.

FR-18
Melakukan pembayaran.

---

## Admin

FR-19
Melihat dashboard.

FR-20
Mengelola Customer.

FR-21
Mengelola Courier.

FR-22
Mengelola Harga Laundry.

FR-23
Mengelola Order.

FR-24
Mengonfirmasi Order.

FR-25
Assign Courier Pickup.

FR-26
Assign Courier Delivery.

FR-27
Input Berat Aktual.

FR-28
Menghitung Total Harga secara otomatis.

FR-29
Mengubah Status Laundry.

FR-30
Memverifikasi Pembayaran.

FR-31
Melihat laporan.

---

## Courier

FR-32
Melihat Dashboard.

FR-33
Melihat daftar tugas.

FR-34
Melihat detail pickup.

FR-35
Melihat detail delivery.

FR-36
Memulai Pickup.

FR-37
Mengirim lokasi GPS.

FR-38
Mengubah status Pickup.

FR-39
Mengantar Laundry.

FR-40
Mengubah status Delivery.

FR-41
Melihat riwayat tugas.

---

# 2. Business Rules

BR-01

Sistem hanya mendukung Laundry Kiloan.

BR-02

Harga dihitung menggunakan:

Berat Aktual × Harga per Kilogram

BR-03

Berat aktual hanya dapat diinput Admin.

BR-04

Customer hanya dapat membayar ketika status READY.

BR-05

Courier hanya dapat Delivery setelah pembayaran dikonfirmasi.

BR-06

Customer hanya dapat melihat order miliknya sendiri.

BR-07

Courier hanya dapat melihat tugas yang diberikan kepadanya.

BR-08

Admin memiliki akses penuh.

---

# 3. Live GPS Requirements

GPS aktif hanya ketika:

- Pickup
- Delivery

Teknologi:

- Browser Geolocation API
- navigator.geolocation.watchPosition()

Lokasi dikirim setiap:

10 detik

Menggunakan:

AJAX Polling

Leaflet.js

OpenStreetMap

Tidak menggunakan:

- Firebase
- Redis
- WebSocket
- Socket.io
- Pusher

---

# 4. Payment Rules

Metode pembayaran:

- Tunai
- Transfer
- QRIS

Pembayaran dilakukan setelah laundry selesai.

Admin melakukan verifikasi pembayaran.

Tidak menggunakan Payment Gateway.

---

# 5. Non Functional Requirements

## Performance

NFR-01

Halaman dimuat kurang dari 3 detik.

NFR-02

Pagination digunakan pada data besar.

NFR-03

Query database menggunakan index.

---

## Security

NFR-04

Password menggunakan Hash.

NFR-05

CSRF Protection aktif.

NFR-06

Server-side Validation.

NFR-07

Role Based Authorization.

NFR-08

Menggunakan Laravel Policy.

NFR-09

Mencegah SQL Injection.

NFR-10

Mencegah XSS.

---

## Reliability

NFR-11

Status Order tidak boleh hilang.

NFR-12

Semua perubahan status dicatat pada Status History.

NFR-13

Tracking GPS disimpan setiap update.

---

## Usability

NFR-14

Responsive.

NFR-15

Mobile Friendly.

NFR-16

Navigasi berbeda berdasarkan Role.

---

## Maintainability

NFR-17

Menggunakan Laravel MVC.

NFR-18

Menggunakan Service Layer.

NFR-19

Menggunakan Form Request.

NFR-20

Menggunakan Enum.

NFR-21

Menggunakan Eloquent ORM.

---

# 6. Software Requirements

PHP 8.3+

Laravel 12

MySQL 8+

Composer

Node.js 20+

Git

Bootstrap 5

Leaflet.js

OpenStreetMap

---

# 7. Browser Support

Google Chrome

Microsoft Edge

Mozilla Firefox

Safari

---

# 8. Constraint

Sistem hanya mendukung:

- Laundry Kiloan
- Satu Outlet
- Tiga Role
- Shared Hosting

---

# 9. Audit Log

Sistem wajib menyimpan:

- Login
- Logout
- Perubahan Status
- Perubahan Pembayaran
- Assign Courier
- Input Berat Aktual
- Update GPS