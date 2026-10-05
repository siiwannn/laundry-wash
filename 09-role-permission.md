# Role & Permission Matrix

Brand : Laundry Wash

Version : 1.0

---

# Roles

Sistem hanya memiliki tiga role:

- Customer
- Admin
- Courier

---

| Feature | Customer | Admin | Courier |
|---------|:--------:|:-----:|:-------:|
| Login | ✅ | ✅ | ✅ |
| Logout | ✅ | ✅ | ✅ |
| Register | ✅ | ❌ | ❌ |
| Dashboard | ✅ | ✅ | ✅ |
| View Profile | ✅ | ✅ | ✅ |
| Edit Profile | ✅ | ✅ | ✅ |
| Change Password | ✅ | ✅ | ✅ |
| Manage Address | ✅ | ❌ | ❌ |
| Create Order | ✅ | ❌ | ❌ |
| View Own Orders | ✅ | ❌ | ❌ |
| View Order Detail | ✅ | ✅ | ✅* |
| View Order Timeline | ✅ | ✅ | ✅* |
| Track Courier | ✅ | ✅ | ✅* |
| Make Payment | ✅ | ❌ | ❌ |
| View Payment Status | ✅ | ✅ | ❌ |
| Manage Orders | ❌ | ✅ | ❌ |
| Confirm Order | ❌ | ✅ | ❌ |
| Assign Courier | ❌ | ✅ | ❌ |
| Input Actual Weight | ❌ | ✅ | ❌ |
| Update Laundry Status | ❌ | ✅ | ❌ |
| Verify Payment | ❌ | ❌ | ❌ |
| Manage Laundry Price | ❌ | ✅ | ❌ |
| View Reports | ❌ | ✅ | ❌ |
| View Courier List | ❌ | ✅ | ❌ |
| View Customer List | ❌ | ✅ | ❌ |
| View Assigned Tasks | ❌ | ❌ | ✅ |
| Start Pickup | ❌ | ❌ | ✅ |
| Complete Pickup | ❌ | ❌ | ✅ |
| Start Delivery | ❌ | ❌ | ✅ |
| Complete Delivery | ❌ | ❌ | ✅ |
| Update GPS Location | ❌ | ❌ | ✅ |
| View Task History | ❌ | ❌ | ✅ |

*Khusus tugas yang diberikan kepada Courier tersebut.

---

# Authorization Rules

## Customer

Customer hanya dapat:

- Mengakses data miliknya sendiri.
- Mengelola profil sendiri.
- Mengelola alamat sendiri.
- Membuat order.
- Melihat status order sendiri.
- Melihat tracking courier untuk order miliknya.
- Melakukan pembayaran untuk order miliknya.
- Melihat riwayat order miliknya.

Customer tidak dapat:

- Mengakses dashboard Admin.
- Mengubah status laundry.
- Mengakses data customer lain.
- Mengakses data courier.
- Mengakses laporan.

---

## Admin

Admin memiliki akses penuh terhadap seluruh operasional.

Admin dapat:

- Mengelola Customer.
- Mengelola Courier.
- Mengelola Order.
- Mengelola Harga Laundry.
- Assign Courier.
- Input Berat Aktual.
- Mengubah Status Laundry.
- Melihat status pembayaran Midtrans tanpa mengubahnya.
- Melihat Dashboard.
- Melihat Laporan.

Admin tidak dapat:

- Mengubah lokasi GPS Courier secara manual.

---

## Courier

Courier hanya dapat:

- Melihat tugas yang ditugaskan kepadanya.
- Memulai Pickup.
- Menyelesaikan Pickup.
- Memulai Delivery.
- Menyelesaikan Delivery.
- Mengirim lokasi GPS.
- Melihat riwayat tugas.

Courier tidak dapat:

- Mengubah data Order.
- Mengubah harga Laundry.
- Mengubah pembayaran.
- Mengakses laporan.
- Mengakses order milik courier lain.

---

# Middleware

Guest

- Login
- Register

Customer

- auth
- role:customer

Admin

- auth
- role:admin

Courier

- auth
- role:courier

---

# Route Protection

Customer

/customer/*

Admin

/admin/*

Courier

/courier/*

---

# Data Access Rules

Customer

- Hanya dapat membaca data miliknya sendiri.

Admin

- Dapat membaca dan mengubah seluruh data.

Courier

- Hanya dapat membaca Assignment yang diberikan.
- Hanya dapat mengirim lokasi GPS untuk Assignment aktif.

---

# Security Rules

- Semua endpoint wajib Authentication.
- Semua request divalidasi menggunakan Form Request.
- Semua authorization menggunakan Middleware dan Policy Laravel.
- Password disimpan menggunakan Hash.
- CSRF Protection aktif.
- Semua perubahan status dicatat pada Order Status History.
- Aktivitas penting dicatat pada Activity Log.

---

# Permission Summary

Customer

- Self Service

Admin

- Full Operational Access

Courier

- Task Based Access
