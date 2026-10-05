# Development Plan

Brand : Laundry Wash

Version : 1.0

Project Duration

15 Hari (2–3 Minggu)

Framework

Laravel 12

Database

MySQL

Frontend

Blade + Bootstrap 5

Map

Leaflet.js + OpenStreetMap

---

# Sprint 1

## Foundation

Target

Project dapat dijalankan.

Task

- Setup Laravel 12
- Setup Git Repository
- Setup Environment
- Setup MySQL
- Konfigurasi Bootstrap
- Konfigurasi Authentication
- Konfigurasi Role Middleware
- Konfigurasi Policy
- Membuat Migration
- Membuat Seeder
- Membuat Factory

Deliverable

- Login
- Register
- Dashboard sesuai Role

---

# Sprint 2

## Customer Module

Task

- Dashboard Customer
- Profile
- Change Password
- Address Management
- Create Order
- Order History
- Order Detail

Deliverable

Customer dapat membuat Order.

---

# Sprint 3

## Admin Module

Task

- Dashboard Admin
- Customer Management
- Courier Management
- Order Management
- Confirm Order
- Assign Courier
- Input Berat Aktual
- Hitung Harga Otomatis
- Update Status Laundry
- Monitoring Pembayaran Midtrans
- Pengaturan Harga Laundry

Deliverable

Seluruh operasional Laundry berjalan.

---

# Sprint 4

## Courier Module

Task

- Dashboard Courier
- Daftar Tugas
- Pickup
- Delivery
- Update GPS
- Riwayat Tugas

Deliverable

Courier dapat Pickup dan Delivery.

---

# Sprint 5

## Live GPS

Task

- Leaflet
- OpenStreetMap
- Browser Geolocation API
- AJAX Polling
- Tracking Customer
- Tracking Admin

Deliverable

Customer dapat melihat posisi Courier.

---

# Sprint 6

## Payment

Task

- Waiting Payment
- Payment
- Midtrans Webhook
- Payment History

Deliverable

Pembayaran selesai.

---

# Sprint 7

## Reporting

Task

- Dashboard Statistik
- Grafik Pendapatan
- Laporan Order
- Laporan Revenue

Deliverable

Admin dapat melihat laporan.

---

# Sprint 8

## QA

Task

- Functional Testing
- Role Testing
- Validation Testing
- GPS Testing
- Responsive Testing
- Bug Fixing

Deliverable

Semua fitur berjalan.

---

# Sprint 9

## Deployment

Task

- Environment Production
- Database Migration
- Seeder
- Storage
- Deployment
- Final Demo

Deliverable

Aplikasi siap dipresentasikan.

---

# Development Priority

P0

- Authentication
- Dashboard
- Order
- Courier
- Tracking
- Laundry Process
- Payment

P1

- Reports
- Statistics
- Settings

P2

- UI Improvement

---

# Git Branch Strategy

main

develop

feature/auth

feature/customer

feature/order

feature/admin

feature/courier

feature/tracking

feature/payment

feature/report

hotfix

release

---

# Git Commit Convention

feat:

fix:

refactor:

style:

docs:

test:

chore:

---

# Coding Standard

- PSR-12
- SOLID
- DRY
- Repository Pattern
- Service Layer
- Form Request
- Laravel Policy
- Middleware
- Eloquent ORM

---

# Testing Checklist

Authentication

Customer

Admin

Courier

GPS

Payment

Order

History

Reports

Responsive

---

# Definition of Done

Sprint dianggap selesai apabila:

- Semua fitur pada sprint selesai.
- Tidak ada error kritis.
- Tidak ada query gagal.
- Tidak ada route yang rusak.
- UI responsive.
- Testing berhasil.
- Siap di-merge ke branch develop.

---

# AI Development Rules

AI wajib:

- Mengikuti PRD.md
- Mengikuti Database.md
- Mengikuti DESIGN.md
- Mengikuti API.md
- Mengikuti Role Matrix

AI tidak boleh:

- Menambah fitur baru.
- Mengubah struktur database tanpa izin.
- Mengubah alur bisnis.
- Menghapus fitur yang telah disetujui.

Jika terdapat requirement yang bertentangan, AI harus berhenti dan meminta konfirmasi sebelum melanjutkan.
