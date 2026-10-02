# User Stories

Brand : Laundry Wash

Versi : 1.0

---

# CUSTOMER

## US-C01 Registrasi

Sebagai Customer,
saya ingin membuat akun agar dapat menggunakan layanan Laundry Wash.

### Acceptance Criteria

- Nama wajib diisi.
- Email harus unik.
- Nomor HP wajib.
- Password minimal 8 karakter.
- Password disimpan dalam bentuk hash.

---

## US-C02 Login

Sebagai Customer,
saya ingin login agar dapat mengakses dashboard.

### Acceptance Criteria

- Login menggunakan email dan password.
- Jika berhasil diarahkan ke Dashboard Customer.
- Jika gagal menampilkan pesan kesalahan.

---

## US-C03 Kelola Profil

Sebagai Customer,
saya ingin melihat dan mengubah profil saya.

### Acceptance Criteria

- Nama dapat diubah.
- Nomor HP dapat diubah.
- Password dapat diubah.

---

## US-C04 Kelola Alamat

Sebagai Customer,
saya ingin menyimpan alamat pickup agar tidak perlu mengisi ulang.

### Acceptance Criteria

- Customer dapat menambah alamat.
- Customer dapat mengubah alamat.
- Customer dapat menghapus alamat.
- Customer dapat menentukan alamat utama.

---

## US-C05 Membuat Order

Sebagai Customer,
saya ingin membuat order laundry agar pakaian dapat diproses.

### Acceptance Criteria

- Memilih metode penyerahan:
  - Pickup
  - Antar Sendiri
- Memilih alamat pickup.
- Memilih tanggal pickup.
- Memilih jam pickup.
- Menambahkan catatan (opsional).
- Sistem membuat nomor order unik.
- Status awal = Pending.

---

## US-C06 Tracking Laundry

Sebagai Customer,
saya ingin melihat status laundry agar mengetahui progres pengerjaan.

### Acceptance Criteria

- Status diperbarui secara realtime berdasarkan database.
- Timeline mudah dipahami.

---

## US-C07 Tracking Courier

Sebagai Customer,
saya ingin melihat posisi Courier saat Pickup maupun Delivery.

### Acceptance Criteria

- Peta menggunakan Leaflet.
- Posisi diperbarui setiap 10 detik.
- GPS hanya aktif saat Pickup atau Delivery.

---

## US-C08 Pembayaran

Sebagai Customer,
saya ingin melakukan pembayaran setelah laundry selesai.

### Acceptance Criteria

- Pembayaran hanya dapat dilakukan ketika status READY.
- Metode:
  - Tunai
  - Transfer
  - QRIS

---

## US-C09 Riwayat Order

Sebagai Customer,
saya ingin melihat riwayat transaksi saya.

### Acceptance Criteria

- Menampilkan seluruh order.
- Dapat melihat detail setiap order.

---

# ADMIN

## US-A01 Dashboard

Sebagai Admin,
saya ingin melihat dashboard operasional.

### Acceptance Criteria

Menampilkan:

- Order Aktif
- Laundry Diproses
- Pendapatan Hari Ini
- Pendapatan Bulan Ini
- Courier Aktif

---

## US-A02 Kelola Harga Laundry

Sebagai Admin,
saya ingin mengubah harga per kilogram.

### Acceptance Criteria

- Harga dapat diubah.
- Harga baru digunakan untuk order berikutnya.

---

## US-A03 Konfirmasi Order

Sebagai Admin,
saya ingin memverifikasi order baru.

---

## US-A04 Assign Courier

Sebagai Admin,
saya ingin memilih Courier untuk Pickup maupun Delivery.

---

## US-A05 Input Berat Aktual

Sebagai Admin,
saya ingin memasukkan berat aktual laundry.

### Acceptance Criteria

- Berat harus lebih dari 0.
- Sistem menghitung harga otomatis.

---

## US-A06 Update Status Laundry

Sebagai Admin,
saya ingin memperbarui status laundry.

### Acceptance Criteria

Status mengikuti urutan:

Pending

Confirmed

Pickup Assigned

Picked Up

Received

Washing

Drying

Ironing

Ready

Waiting Payment

Paid

Delivery Assigned

Completed

---

## US-A07 Verifikasi Pembayaran

Sebagai Admin,
saya ingin memverifikasi pembayaran customer.

---

## US-A08 Laporan

Sebagai Admin,
saya ingin melihat laporan transaksi.

---

# COURIER

## US-K01 Dashboard

Sebagai Courier,
saya ingin melihat tugas saya hari ini.

---

## US-K02 Daftar Tugas

Sebagai Courier,
saya ingin melihat Pickup dan Delivery yang ditugaskan.

---

## US-K03 Mulai Pickup

Sebagai Courier,
saya ingin memulai perjalanan Pickup.

### Acceptance Criteria

- GPS aktif.
- Status berubah menjadi Courier To Pickup.

---

## US-K04 Share GPS

Sebagai Courier,
saya ingin mengirim lokasi saya.

### Acceptance Criteria

- Menggunakan Browser Geolocation API.
- Lokasi dikirim setiap 10 detik.
- Latitude dan Longitude tersimpan.

---

## US-K05 Konfirmasi Pickup

Sebagai Courier,
saya ingin mengonfirmasi bahwa laundry sudah dijemput.

---

## US-K06 Mulai Delivery

Sebagai Courier,
saya ingin memulai pengantaran.

---

## US-K07 Konfirmasi Delivery

Sebagai Courier,
saya ingin mengonfirmasi bahwa laundry telah diterima Customer.

### Acceptance Criteria

- GPS berhenti.
- Status menjadi Completed.

---

# PRIORITAS USER STORY

## P0 (Wajib)

- Login
- Register
- Dashboard
- Order
- Pickup
- Delivery
- Tracking Laundry
- Tracking GPS
- Pembayaran
- Riwayat

---

## P1

- Laporan
- Pengaturan Harga
- Statistik Dashboard

---

## P2

- Promo
- Voucher
- Rating
- Notifikasi