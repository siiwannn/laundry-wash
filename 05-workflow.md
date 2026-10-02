# Workflow System

**Brand:** Laundry Wash

**Versi:** 1.0

---

# 1. Main Business Workflow

```text
                Customer Login
                      │
                      ▼
              Customer Create Order
                      │
                      ▼
                  Pending
                      │
                      ▼
            Admin Confirm Order
                      │
                      ▼
                 Confirmed
              ┌───────────────┐
              │               │
              ▼               ▼
       Pickup Dipilih     Antar Sendiri
              │               │
              ▼               ▼
   Assign Pickup Courier  Customer Antar Laundry
              │               │
              ▼               ▼
      Courier To Pickup  Received At Laundry
              │               ▲
              ▼               │
          Picked Up ──────────┘
              │
              ▼
     Received At Laundry
              │
              ▼
     Admin Input Weight
              │
              ▼
 Sistem Hitung Total Harga
              │
              ▼
           Washing
              │
              ▼
           Drying
              │
              ▼
          Ironing
              │
              ▼
            Ready
              │
              ▼
      Waiting Payment
              │
              ▼
 Customer Melakukan Pembayaran
              │
              ▼
 Admin Verifikasi Pembayaran
              │
              ▼
             Paid
              │
              ▼
      Pilih Metode Pengambilan
              │
      ┌───────┴────────┐
      ▼                ▼
Delivery          Ambil Sendiri
      │                │
      ▼                ▼
Assign Courier     Customer Datang
      │                │
      ▼                ▼
Courier To Customer    Completed
      │
      ▼
Delivered
      │
      ▼
Completed
```

---

# 2. Courier Pickup Workflow

```text
Courier Login
      │
      ▼
Lihat Daftar Pickup
      │
      ▼
Klik Mulai Pickup
      │
      ▼
GPS Aktif
      │
      ▼
Lokasi dikirim setiap 10 detik
      │
      ▼
Menuju Customer
      │
      ▼
Laundry Dijemput
      │
      ▼
Konfirmasi Pickup
      │
      ▼
Status = Picked Up
      │
      ▼
GPS Berhenti
```

---

# 3. Courier Delivery Workflow

```text
Courier Login
      │
      ▼
Lihat Daftar Delivery
      │
      ▼
Klik Mulai Delivery
      │
      ▼
GPS Aktif
      │
      ▼
Lokasi dikirim setiap 10 detik
      │
      ▼
Menuju Customer
      │
      ▼
Laundry Diterima Customer
      │
      ▼
Konfirmasi Delivery
      │
      ▼
Status = Completed
      │
      ▼
GPS Berhenti
```

---

# 4. Live GPS Workflow

1. Courier menekan tombol **Mulai Pickup** atau **Mulai Delivery**.
2. Browser meminta izin akses lokasi.
3. Browser membaca koordinat menggunakan:

   - navigator.geolocation.watchPosition()

4. Frontend mengirim:

   - Latitude
   - Longitude
   - Accuracy
   - Timestamp

5. Data dikirim ke server setiap **10 detik** menggunakan AJAX.
6. Laravel menyimpan lokasi terbaru.
7. Customer membuka halaman Tracking.
8. Frontend melakukan polling setiap **10 detik**.
9. Marker Leaflet diperbarui.
10. Ketika tugas selesai, GPS berhenti.

---

# 5. Payment Workflow

```text
Laundry Ready
      │
      ▼
Waiting Payment
      │
      ▼
Customer Memilih Metode Pembayaran
      │
      ▼
Tunai / Transfer / QRIS
      │
      ▼
Admin Verifikasi
      │
      ▼
Paid
```

---

# 6. Order Status Workflow

```text
Pending

↓

Confirmed

↓

Pickup Assigned

↓

Courier To Pickup

↓

Picked Up

↓

Received At Laundry

↓

Washing

↓

Drying

↓

Ironing

↓

Ready

↓

Waiting Payment

↓

Paid

↓

Delivery Assigned

↓

Courier To Customer

↓

Delivered

↓

Completed
```

---

# 7. Status History

Setiap perubahan status wajib disimpan.

Data yang dicatat:

- Order ID
- Status Lama
- Status Baru
- User yang Mengubah
- Tanggal & Waktu
- Catatan (Opsional)

---

# 8. Business Rules

- Customer hanya dapat membuat order setelah login.
- Berat aktual hanya dapat diinput oleh Admin.
- Harga dihitung otomatis berdasarkan berat aktual × harga per kilogram.
- Pembayaran hanya dapat dilakukan ketika status **Ready**.
- Delivery hanya dapat dilakukan setelah pembayaran dikonfirmasi.
- GPS hanya aktif saat Pickup atau Delivery.
- Customer hanya dapat melihat tracking miliknya sendiri.
- Courier hanya dapat melihat tugas yang ditugaskan kepadanya.

---

# 9. Error Flow

Jika:

- GPS ditolak browser → tampilkan pesan agar pengguna mengaktifkan izin lokasi.
- Courier kehilangan koneksi internet → simpan lokasi terakhir dan kirim ulang saat koneksi kembali.
- Pembayaran gagal diverifikasi → status tetap **Waiting Payment**.
- Courier gagal menyelesaikan pickup/delivery → Admin dapat melakukan penugasan ulang.

---

# 10. Constraint

- Sistem hanya mendukung Laundry Kiloan.
- Satu outlet.
- Tiga role:
  - Customer
  - Admin
  - Courier
- Live GPS menggunakan Leaflet + OpenStreetMap.
- Tidak menggunakan Firebase, WebSocket, Redis, Socket.io, atau Pusher.