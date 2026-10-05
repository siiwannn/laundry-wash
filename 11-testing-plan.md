# Testing Plan

Brand : Laundry Wash

Version : 1.0

---

# Testing Objective

Memastikan seluruh fitur Laundry Wash berjalan sesuai PRD, User Story, Use Case, dan Business Rules.

---

# Testing Scope

- Authentication
- Customer Module
- Admin Module
- Courier Module
- Order
- Tracking
- GPS
- Payment
- Reports
- Authorization
- Responsive UI
- Security

---

# Testing Types

✅ Unit Testing

✅ Feature Testing

✅ Integration Testing

✅ Manual Testing

✅ Authorization Testing

✅ Responsive Testing

✅ Security Testing

✅ User Acceptance Testing (UAT)

---

# AUTHENTICATION

## TC-AUTH-001

### Login Valid

Precondition

User sudah terdaftar.

Steps

1. Buka Login.
2. Masukkan Email.
3. Masukkan Password.
4. Klik Login.

Expected

- Login berhasil.
- Redirect ke Dashboard sesuai Role.
- Session dibuat.

Priority

P0

---

## TC-AUTH-002

Login Invalid

Expected

- Pesan Error.
- Tetap di halaman Login.

Priority

P0

---

## TC-AUTH-003

Unauthorized Access

Customer membuka

/admin/dashboard

Expected

403

atau Redirect.

Priority

P0

---

# CUSTOMER

## TC-CUS-001

Create Order

Expected

- Order tersimpan.
- Nomor Order dibuat.
- Status = Pending.
- Status History dibuat.

Priority

P0

---

## TC-CUS-002

Create Order tanpa alamat

Expected

Validation Error.

---

## TC-CUS-003

Melihat Order Orang Lain

Expected

403 Forbidden.

---

## TC-CUS-004

Melihat Tracking

Expected

- Map tampil.
- Marker tampil.
- Status tampil.

---

## TC-CUS-005

Melakukan Pembayaran

Expected

Payment dibuat.

Status Pending.

---

# ADMIN

## TC-ADM-001

Konfirmasi Order

Expected

Status menjadi

Confirmed.

---

## TC-ADM-002

Assign Courier

Expected

Assignment dibuat.

Status berubah.

---

## TC-ADM-003

Input Berat

Expected

Berat tersimpan.

Harga dihitung otomatis.

---

## TC-ADM-004

Update Status Laundry

Expected

Status berubah.

History dibuat.

---

## TC-ADM-005

Webhook Pembayaran Paid

Expected

Payment menjadi Paid.

Order siap Delivery.

---

# COURIER

## TC-COU-001

Melihat Tugas

Expected

Hanya Assignment miliknya.

---

## TC-COU-002

Start Pickup

Expected

Status

On The Way.

GPS aktif.

---

## TC-COU-003

Update GPS

Expected

Latitude tersimpan.

Longitude tersimpan.

Timestamp berubah.

---

## TC-COU-004

Complete Pickup

Expected

Status Picked Up.

GPS berhenti.

---

## TC-COU-005

Start Delivery

Expected

GPS aktif.

---

## TC-COU-006

Complete Delivery

Expected

Status Completed.

GPS berhenti.

---

# GPS

## TC-GPS-001

GPS Permission Allowed

Expected

Lokasi berhasil dikirim.

---

## TC-GPS-002

GPS Permission Denied

Expected

Pesan Error.

---

## TC-GPS-003

Internet Putus

Expected

Lokasi terakhir tetap tampil.

---

## TC-GPS-004

Pickup dan Delivery berstatus On The Way

Expected

Tracking aktif, payload berisi status perjalanan, route jalan, ETA, remaining distance, dan bearing.

---

## TC-GPS-005

Assignment selesai

Expected

Tracking tidak aktif dan Courier tidak dapat mengirim lokasi baru.

---

## TC-GPS-006

Customer atau Courier mengakses assignment milik pengguna lain

Expected

Request ditolak oleh Policy atau validasi ownership.

---

# PAYMENT

## TC-PAY-001

Midtrans QRIS

Expected

Payment dibuat.

---

## TC-PAY-002

Midtrans Virtual Account

Expected

Payment dibuat.

---

## TC-PAY-003

Webhook settlement/capture dengan signature dan nominal valid

Expected

Payment dan Order menjadi Paid secara idempotent.

---

## TC-PAY-004

Webhook gagal atau kedaluwarsa

Expected

Payment menjadi Failed, Order kembali Ready, dan Activity Log mencatat Payment Failed atau Payment Expired.

---

## TC-PAY-005

Webhook dengan signature atau nominal tidak valid

Expected

Request ditolak dan status tidak berubah.

---

# REPORT

## TC-REP-001

Filter Tanggal

Expected

Data sesuai tanggal.

---

## TC-REP-002

Revenue

Expected

Total benar.

---

# AUTHORIZATION

Customer

Tidak dapat membuka

/admin/*

Courier

Tidak dapat membuka

/customer/*

Admin

Dapat membuka seluruh halaman Admin.

---

# RESPONSIVE

Desktop

Sidebar tampil.

Tablet

Sidebar collapse.

Mobile

Bottom Navigation tampil.

Semua halaman responsive.

---

# PERFORMANCE

Target

Halaman

< 3 detik.

GPS

10 detik/update.

Pagination

Aktif.

---

# SECURITY CHECKLIST

- Password Hash
- CSRF Protection
- XSS Escape
- SQL Injection Prevention
- Form Request Validation
- Middleware
- Policy
- Authentication
- Authorization
- .env tidak masuk Git

---

# DATABASE VALIDATION

Pastikan:

Order dibuat.

Payment dibuat.

Assignment dibuat.

Status History dibuat.

GPS tersimpan.

Activity Log dibuat.

---

# BUG SEVERITY

Critical

Aplikasi tidak dapat digunakan.

High

Fitur utama gagal.

Medium

Fitur berjalan sebagian.

Low

UI.

---

# ACCEPTANCE CRITERIA

Semua Test Case P0

PASS

Semua Business Rule

PASS

Semua Role

PASS

Semua Dashboard

PASS

Tracking GPS

PASS

Payment

PASS

Order

PASS

Deployment

PASS

---

# Definition of Testing Done

Testing dianggap selesai apabila:

- Tidak ada Bug Critical.
- Tidak ada Bug High.
- Seluruh Test Case P0 PASS.
- Minimal 95% Test Case berhasil.
- Sistem siap Demo.
