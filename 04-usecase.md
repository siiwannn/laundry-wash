# Use Case Specification

Brand : Laundry Wash

Versi : 1.0

---

# Actor

Sistem memiliki tiga aktor:

- Customer
- Admin
- Courier

---

# UC-01 Login

Actor

Customer

Admin

Courier

Precondition

- User sudah memiliki akun.

Main Flow

1. User membuka halaman Login.
2. User memasukkan Email.
3. User memasukkan Password.
4. Sistem melakukan validasi.
5. Sistem membuat Session Login.
6. User diarahkan ke Dashboard sesuai Role.

Alternative Flow

- Email atau Password salah.
- Sistem menampilkan pesan kesalahan.

Postcondition

User berhasil Login.

---

# UC-02 Register

Actor

Customer

Main Flow

1. Customer membuka halaman Register.
2. Mengisi Nama.
3. Mengisi Email.
4. Mengisi Nomor HP.
5. Mengisi Password.
6. Sistem membuat akun baru.

Postcondition

Customer dapat Login.

---

# UC-03 Create Order

Actor

Customer

Precondition

Customer Login.

Main Flow

1. Customer memilih metode penyerahan.
   - Pickup
- Pickup oleh Courier

2. Customer memilih alamat.

3. Customer memilih tanggal Pickup.

4. Customer memilih jam Pickup.

5. Customer menambahkan catatan.

6. Customer Submit.

7. Sistem membuat Nomor Order.

8. Status menjadi Pending.

Alternative Flow

Alamat belum dipilih.

Sistem meminta Customer memilih alamat.

Postcondition

Order berhasil dibuat.

---

# UC-04 Tracking Laundry

Actor

Customer

Main Flow

1. Customer membuka Detail Order.

2. Sistem menampilkan Timeline Laundry.

3. Sistem menampilkan Status Terbaru.

Postcondition

Customer mengetahui progres laundry.

---

# UC-05 Tracking Courier

Actor

Customer

Precondition

Status Pickup atau Delivery sedang aktif.

Main Flow

1. Customer membuka halaman Tracking.

2. Sistem mengambil lokasi Courier.

3. Leaflet menampilkan posisi Courier.

4. Posisi diperbarui setiap 10 detik.

Postcondition

Customer mengetahui posisi Courier.

---

# UC-06 Payment

Actor

Customer

Precondition

Status Laundry = Ready

Main Flow

1. Customer membuka Midtrans Snap.

2. Customer memilih QRIS atau Virtual Account dan menyelesaikan pembayaran.

3. Midtrans mengirim webhook dengan signature yang valid.

4. Sistem mengubah Payment dan Order menjadi Paid secara otomatis.

Alternative Flow

Pembayaran gagal atau kedaluwarsa.

Payment menjadi Failed dan Order kembali ke Ready agar Customer dapat mencoba lagi.

---

# UC-07 Dashboard Admin

Actor

Admin

Main Flow

1. Login.

2. Membuka Dashboard.

3. Sistem menampilkan:

- Order Aktif
- Laundry Diproses
- Courier Aktif
- Pendapatan
- Grafik

---

# UC-08 Confirm Order

Actor

Admin

Main Flow

1. Membuka Order Pending.

2. Memverifikasi Order.

3. Status menjadi Confirmed.

---

# UC-09 Assign Courier

Actor

Admin

Main Flow

1. Memilih Order.

2. Memilih Courier.

3. Sistem membuat Assignment.

4. Status menjadi Pickup Assigned.

---

# UC-10 Input Berat Aktual

Actor

Admin

Main Flow

1. Membuka Detail Order.

2. Memasukkan Berat Aktual.

3. Sistem menghitung:

Total = Berat × Harga per Kg

4. Sistem menyimpan Total.

---

# UC-11 Update Laundry Status

Actor

Admin

Main Flow

Admin mengubah status:

Washing

↓

Drying

↓

Ironing

↓

Ready

Semua perubahan disimpan pada Status History.

---

# UC-12 Melihat Status Pembayaran

Actor

Admin

Main Flow

1. Admin membuka daftar pembayaran.

2. Sistem menampilkan status terakhir dari Midtrans.

3. Admin tidak dapat mengubah status secara manual.

4. Courier Delivery hanya dapat ditugaskan jika Order dan Payment berstatus Paid.

---

# UC-13 Dashboard Courier

Actor

Courier

Main Flow

1. Login.

2. Membuka Dashboard.

3. Sistem menampilkan:

Pickup

Delivery

Riwayat

---

# UC-14 Pickup

Actor

Courier

Main Flow

1. Membuka Tugas Pickup.

2. Klik Mulai Pickup.

3. GPS aktif.

4. Lokasi dikirim setiap 10 detik.

5. Courier mengambil Laundry.

6. Klik Pickup Selesai.

7. Status menjadi Picked Up.

Postcondition

GPS berhenti.

---

# UC-15 Delivery

Actor

Courier

Main Flow

1. Membuka Tugas Delivery.

2. Klik Mulai Delivery.

3. GPS aktif.

4. Lokasi dikirim.

5. Laundry diterima Customer.

6. Klik Delivery Selesai.

7. Status menjadi Completed.

Postcondition

GPS berhenti.

---

# Business Rule

- Sistem hanya mendukung Laundry Kiloan.
- Harga dihitung berdasarkan Berat Aktual.
- Pembayaran dilakukan setelah Laundry selesai.
- GPS hanya aktif saat Pickup atau Delivery.
- Customer hanya dapat melihat Order miliknya.
- Courier hanya dapat melihat Assignment miliknya.
- Admin memiliki akses penuh.
