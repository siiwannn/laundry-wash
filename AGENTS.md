# AGENTS.md

# Laundry Wash

Versi : 1.0

Dokumen ini adalah aturan utama bagi seluruh AI Coding Agent.

Seluruh implementasi WAJIB mengikuti dokumen ini.

Jika terdapat konflik antara AGENTS.md dengan file lain,

prioritas dokumen:

PRD.md

↓

DATABASE.md

↓

DESIGN.md

↓

SYSTEM_REQUIREMENTS.md

↓

AGENTS.md

---

# Project Context

Laundry Wash adalah aplikasi Web Management Laundry.

Role

- Admin
- Customer
- Courier

Target

MVP selesai maksimal

2–3 minggu.

AI tidak boleh menambahkan fitur di luar MVP.

---

# Tech Stack

Backend

Laravel 12

PHP 8.3

Frontend

Blade

Bootstrap 5

Javascript

Database

MySQL 8

Maps

Leaflet.js

OpenStreetMap

GPS

Browser Geolocation API

---

# Architecture

Gunakan Laravel Standard Structure.

Gunakan:

- MVC
- Service Layer
- Form Request
- Policy
- Middleware
- Eloquent ORM

Jangan menggunakan Raw SQL kecuali sangat diperlukan.

Controller harus tipis.

Business Logic berada pada Service.

---

# Naming Convention

Model

PascalCase

Contoh

Order

CourierAssignment

Payment

Setting

Controller

OrderController

AdminOrderController

CourierTaskController

Migration

snake_case

Table

snake_case plural

orders

payments

courier_locations

Route

dot notation

admin.orders.index

customer.orders.show

courier.tasks.index

---

# Roles

Gunakan Role berikut

admin

customer

courier

Authorization wajib dilakukan pada Backend.

Jangan hanya menyembunyikan tombol.

---

# Order Status

Gunakan PHP Enum.

Status

pending

confirmed

pickup_assigned

courier_to_pickup

picked_up

received_at_laundry

washing

drying

ironing

ready

waiting_payment

paid

delivery_assigned

courier_to_customer

delivered

completed

cancelled

Jangan membuat Status baru.

---

# Payment Status

Gunakan Enum

pending

paid

failed

Payment Gateway

- Midtrans Snap Sandbox
- QRIS
- Virtual Account
- Webhook: `/api/midtrans/notification`
- Admin hanya melihat status pembayaran
- Tidak menggunakan Cash, Transfer Manual, atau verifikasi Admin

Activity Log wajib mencatat Payment Created, Payment Pending, Payment Paid, Payment Failed, dan Payment Expired.

---

# Courier Assignment

pickup

delivery

---

# Database Rules

Selalu menggunakan Migration.

Selalu menggunakan Foreign Key.

Tambahkan Index pada kolom pencarian.

Gunakan Transaction untuk operasi penting.

Tidak menggunakan Hard Delete.

---

# Calculation Rules

subtotal

=

actual_weight

×

price_per_kg

total

=

subtotal

+

pickup_fee

+

delivery_fee

+

additional_fee

AI tidak boleh mengubah Formula.

---

# Tracking Rules

Gunakan

Leaflet.js

OpenStreetMap

Browser Geolocation API

navigator.geolocation.watchPosition()

Lokasi dikirim

setiap

10 detik

Menggunakan

AJAX Polling

Tidak menggunakan

Firebase

Redis

WebSocket

Socket.io

Pusher

Tracking hanya aktif ketika

Pickup

atau

Delivery

Tracking berhenti ketika Assignment selesai.

---

# Frontend Rules

Gunakan

Bootstrap 5

Responsive

Mobile First

Reusable Component

Status Badge konsisten.

Confirmation Dialog untuk Delete.

Business Logic tidak boleh berada di Blade.

---

# Coding Rules

Gunakan:

Form Request

Policy

Middleware

Service Layer

Eloquent ORM

PSR-12

SOLID

DRY

---

# Security Rules

Password Hash

CSRF

Validation

Policy

Middleware

Escape Blade

No Secret di Repository

.env tidak boleh di Commit

---

# Logging

Gunakan

Activity Log

untuk

- Login
- Logout
- Assign Courier
- Update Status
- Payment
- GPS Update

---

# Testing

Minimal Feature Test

- Login
- Register
- Authorization
- Create Order
- Confirm Order
- Assign Courier
- Update GPS
- Pickup
- Delivery
- Payment

---

# AI Workflow

Sebelum Coding

1.

Baca

README.md

2.

Baca

PRD.md

3.

Baca

DATABASE.md

4.

Baca

DESIGN.md

5.

Baca

SYSTEM_REQUIREMENTS.md

6.

Baca

AGENTS.md

---

Ketika membuat fitur

1.

Analisis Requirement.

2.

Analisis Database.

3.

Analisis Route.

4.

Analisis Existing Code.

5.

Jangan membuat Migration baru jika tabel sudah ada.

6.

Jangan Rename Table.

7.

Jangan Rename Column.

8.

Jangan mengubah Business Flow.

9.

Jelaskan seluruh file yang dibuat.

---

# AI Coding Rules

Sebelum membuat file baru

cek apakah file sudah abaca a.

Jangan Duplicate Code.

Gunakan Reusable Component.

Jangan Refactor area yang tidak diminta.

Jangan mengubah struktur folder Laravel.

---

# Priority

P0

Authentication

Dashboard

Address

Order

Courier

Tracking

Laundry Process

Payment

P1

Reports

Settings

Statistics

P2

UI Improvement

---

# Do Not

Jangan mengganti Framework.

Jangan mengganti Database.

Jangan menggunakan Firebase.

Jangan menggunakan Redis.

Jangan menggunakan WebSocket.

Jangan membuat Microservice.

Jangan menggunakan Docker.

Jangan menambah Package tanpa alasan.

Jangan menambah fitur baru.

---

# Definition of Done

Migration selesai.

Model selesai.

Controller selesai.

Service selesai.

Validation selesai.

Policy selesai.

Route selesai.

UI selesai.

Testing selesai.

Tidak ada Bug Critical.

Siap Demo.
