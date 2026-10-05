# UI Flow

Brand : Laundry Wash

Versi : 1.0

---

# Public

Landing Page

Login

Register

---

# Customer

## Dashboard

Menampilkan:

- Active Orders
- Status Laundry Terbaru
- Riwayat Singkat
- Quick Action
- Total Order

Tombol:

- Buat Order
- Tracking

---

## Profile

Menampilkan:

- Nama
- Email
- Nomor HP

Fitur:

- Edit Profil
- Ganti Password

---

## Address

Menampilkan:

- Daftar Alamat

Fitur:

- Tambah Alamat
- Edit Alamat
- Hapus Alamat
- Jadikan Default

---

## Create Order

Flow

1. Pilih Metode Penyerahan

- Pickup
- Pickup oleh Courier

↓

2. Pilih Alamat Pickup

↓

3. Pilih Tanggal Pickup

↓

4. Pilih Jam Pickup

↓

5. Tambahkan Catatan

↓

6. Konfirmasi Order

---

## Order Detail

Menampilkan:

- Nomor Order
- Status
- Timeline
- Pickup Method
- Delivery Method
- Berat Aktual
- Harga per Kg
- Subtotal
- Pickup Fee
- Delivery Fee
- Total
- Catatan
- Payment Status

Tombol:

- Tracking
- Bayar

---

## Tracking

Menampilkan:

- Map Leaflet
- Posisi Courier
- Status Perjalanan
- Timeline
- Last Update

---

## Payment

Menampilkan:

- Total Tagihan
- Metode Pembayaran

Midtrans Snap menampilkan pilihan:

- QRIS
- Virtual Account

Status:

- Pending
- Paid

---

## History

Menampilkan:

- Seluruh Order
- Filter Status
- Detail Order

---

# Admin

## Dashboard

Menampilkan:

- Total Order Hari Ini
- Pending Order
- Laundry Diproses
- Courier Aktif
- Revenue Hari Ini
- Revenue Bulan Ini
- Grafik Pendapatan

---

## Orders

Fitur:

- Table
- Search
- Filter
- Detail
- Confirm
- Assign Courier
- Input Berat
- Update Status
- Lihat Status Pembayaran

---

## Customers

Fitur:

- List Customer
- Detail Customer
- Riwayat Order

---

## Couriers

Fitur:

- List Courier
- Status Courier
- Pickup Aktif
- Delivery Aktif
- Posisi Terakhir

---

## Settings

Fitur:

- Harga per Kilogram
- Pickup Fee
- Delivery Fee

---

## Reports

Fitur:

- Filter Tanggal
- Total Order
- Total Revenue
- Export PDF (Opsional)

---

# Courier

## Dashboard

Menampilkan:

- Pickup Hari Ini
- Delivery Hari Ini
- Tugas Aktif
- Riwayat

---

## Task Detail

Menampilkan:

- Customer
- Nomor HP
- Alamat
- Catatan
- Map
- Status

Tombol:

- Mulai Pickup
- Mulai Delivery
- Pickup Selesai
- Delivery Selesai

---

## GPS Tracking

Saat Pickup atau Delivery aktif:

- Map
- Posisi Saat Ini
- Status GPS
- Last Update

GPS otomatis berhenti ketika tugas selesai.

---

# Navigation

## Customer

- Dashboard
- Order
- Tracking
- History
- Profile

---

## Admin

- Dashboard
- Orders
- Customers
- Couriers
- Settings
- Reports

---

## Courier

- Dashboard
- Tasks
- History
- Profile

---

# Responsive Rules

Desktop

- Sidebar
- Navbar
- Content

Tablet

- Sidebar Collapse

Mobile

- Bottom Navigation
- Floating Action Button (Order Baru)

---

# Empty State

Jika tidak ada data:

- Tampilkan ilustrasi
- Pesan informatif
- Tombol aksi

---

# Loading State

Gunakan:

- Spinner
- Skeleton Loading

---

# Error State

Gunakan:

- Bootstrap Alert
- Toast Notification

---

# AI Rules

Jangan mengubah user flow.

Jangan menambah halaman baru.

Jangan menghapus halaman yang ada.

Ikuti DESIGN.md sebagai acuan utama.

Gunakan Bootstrap 5.

Pastikan semua halaman responsive.
