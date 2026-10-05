# DESIGN.md

# Laundry Wash UI/UX Design Specification

Versi : 1.0

---

# Tujuan Desain

Sistem harus mudah digunakan oleh pelanggan, admin, dan petugas.

Prioritas utama adalah:

- Mudah dipahami
- Cepat digunakan
- Responsive
- Modern
- Bersih
- Tidak membingungkan

---

# Source of Truth

DESAIN FIGMA ADALAH ACUAN UTAMA.

AI WAJIB mengikuti layout Figma.

Jangan membuat layout baru.

Jangan mengubah user flow.

Jika terdapat konflik antara implementasi dan desain,

ikuti desain Figma.

---

# Design Style

Modern SaaS Dashboard

Minimalis

Rounded

Soft Shadow

Flat Color

Professional

Clean

---

# Color Palette

Primary

#3B82F6

Secondary

#F8FAFC

Success

#22C55E

Danger

#EF4444

Warning

#F59E0B

Info

#0EA5E9

Dark

#1E293B

Border

#E5E7EB

Background

#F8FAFC

White

#FFFFFF

---

# Typography

Font

Poppins

Fallback

sans-serif

Title

Bold

Subtitle

Medium

Body

Regular

---

# Border Radius

Card

16px

Button

12px

Input

12px

Modal

16px

---

# Shadow

Gunakan shadow lembut.

Jangan menggunakan shadow berlebihan.

---

# Spacing

Gunakan sistem spacing Bootstrap.

Jangan menggunakan margin sembarangan.

---

# Layout

Desktop

Sidebar kiri

Top Navbar

Main Content

Footer

Mobile

Top App Bar

Bottom Navigation

Floating Action Button hanya bila diperlukan.

---

# Sidebar

Logo Laundry Wash

Dashboard

Order

Tracking

History

Settings

Collapse Support

---

# Navbar

Search

Notification

Profile

Avatar

---

# Customer Navigation

Home

Order

Tracking

History

Profile

---

# Admin Navigation

Dashboard

Orders

Services

Customers

Couriers

Payments

Reports

---

# Courier Navigation

Dashboard

Pickup

Delivery

History

Profile

---

# Components

Stat Card

Order Card

Timeline

Status Badge

Progress Step

Table

Modal

Drawer

Toast

Alert

Map Card

Button

Input

Textarea

Dropdown

Pagination

Search Box

Filter

---

# Button

Primary

Secondary

Danger

Outline

Loading State

Disabled State

---

# Form

Gunakan Bootstrap Validation.

Field wajib memiliki label.

Field error harus memiliki pesan.

---

# Status Badge

Pending

Confirmed

Pickup

Washing

Drying

Ironing

Ready

Paid

Delivery

Completed

Cancelled

Semua status memiliki warna berbeda.

---

# Order Timeline

Order Dibuat

↓

Dikonfirmasi

↓

Kurir Menuju Pickup

↓

Laundry Diambil

↓

Laundry Diterima

↓

Sedang Dicuci

↓

Sedang Dikeringkan

↓

Sedang Disetrika

↓

Siap Dibayar

↓

Webhook Midtrans Mengonfirmasi Pembayaran

↓

Kurir Mengantar

↓

Selesai

---

# Map

Gunakan

Leaflet

OpenStreetMap

Marker Customer

Marker Courier

Polyline Route (opsional)

Map minimal tinggi

350px

---

# Dashboard Customer

Statistik

Order Aktif

Tracking

Riwayat

Map

Quick Action

---

# Dashboard Admin

Revenue

Order Hari Ini

Laundry Aktif

Kurir Aktif

Chart

Recent Orders

---

# Dashboard Courier

Pickup Hari Ini

Delivery Hari Ini

Status Tugas

Map

Riwayat

---

# Table

Gunakan Bootstrap Table.

Desktop

Normal Table

Mobile

Horizontal Scroll

---

# Responsive Rules

Desktop

Tablet

Mobile

Sidebar otomatis collapse.

---

# Loading State

Skeleton Loading

Spinner

---

# Empty State

Gunakan ilustrasi.

Tambahkan tombol aksi.

---

# Error State

Gunakan Bootstrap Alert.

---

# Animation

Gunakan Bootstrap Animation ringan.

Tidak berlebihan.

---

# AI Rules

JANGAN membuat desain sendiri.

JANGAN mengubah layout.

JANGAN mengubah user flow.

JANGAN mengubah warna utama.

WAJIB mengikuti Figma.

Komponen boleh dioptimalkan.

Business Logic tidak boleh diubah.

Route tidak boleh diubah.

Controller tidak boleh diubah.

Model tidak boleh diubah.

Database tidak boleh diubah.

Hanya ubah tampilan.

---

# Suggested Pages

## Customer

/customer/dashboard

/customer/orders

/customer/orders/create

/customer/orders/{id}

/customer/orders/{id}/tracking

/customer/history

/customer/profile

---

## Admin

/admin/dashboard

/admin/orders

/admin/orders/{id}

/admin/services

/admin/customers

/admin/couriers

/admin/payments

/admin/reports

---

## Courier

/courier/dashboard

/courier/tasks

/courier/tasks/{id}

/courier/history

/profile
