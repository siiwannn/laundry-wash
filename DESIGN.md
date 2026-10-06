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

#F4512A

Secondary

#FFF7F2

Success

#22C55E

Danger

#EF4444

Warning

#F59E0B

Info

#0EA5E9

Dark

#202020

Border

#E5E7EB

Background

#F5F3EF

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

# Implementasi Visual Terkini

## Tema

- Gunakan palet oranye Laundry Wash sebagai aksen utama pada tombol primer, ikon aktif, CTA, dan halaman katalog.
- Pertahankan teks gelap dengan kontras tinggi serta latar netral hangat agar informasi operasional tetap mudah dibaca.
- Jangan memakai warna status sebagai warna primer tombol. Warna hijau, kuning, dan biru hanya untuk status atau informasi yang relevan.

## Katalog Publik

- Navbar desktop: logo di kiri, tautan navigasi di tengah, serta Masuk dan Daftar Pelanggan di kanan.
- Hero katalog memakai CTA pemesanan yang jelas, ilustrasi bertema laundry, dan latar oranye.
- Footer ringkas: identitas merek di kiri, copyright di tengah, dan tautan navigasi di kanan.

## Workspace Mobile

- Tombol sidebar harus membuka drawer dari sisi kiri dengan backdrop dan tombol tutup yang selalu dapat ditekan.
- Drawer memakai tinggi viewport penuh dan lebar yang konsisten pada seluruh role.
- Kartu dashboard menggunakan grid dua kolom bila ruang cukup; konten pendapatan dan grafik tidak boleh meluber atau terpotong.
- Banner pembayaran menata deskripsi dan seluruh aksi secara sejajar di desktop, lalu bertumpuk rapi di mobile.

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

Gunakan MapLibre GL JS dengan style OpenFreeMap/OpenMapTiles.

Marker Courier menggunakan asset SVG motor flat/isometric, tajam pada Retina Display, berputar mengikuti bearing, dan bergerak halus sepanjang route jalan.

Marker tujuan menggunakan asset SVG non-pin.

Polyline route mengikuti jalan dan wajib menampilkan ETA serta jarak tersisa.

Map mendukung zoom, rotate, pitch sekitar 45 derajat, serta 3D Buildings jika style menyediakan source bangunan.

Auto Follow dapat dimatikan tanpa menonaktifkan tracking.

Map minimal tinggi

350px; pada halaman Live Tracking gunakan minimal 480px di desktop dan tinggi adaptif di mobile.

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
