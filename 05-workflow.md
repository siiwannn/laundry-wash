# Workflow System

**Brand:** Laundry Wash
**Versi:** 1.1

---

# 1. Main Business Workflow

```text
Customer Login
      ↓
Customer Create Order
      ↓
Admin Confirm Order
      ↓
Assign Pickup Courier
      ↓
Courier Pickup + GPS Tracking
      ↓
Received At Laundry
      ↓
Admin Input Actual Weight
      ↓
Washing → Drying → Ironing → Ready
      ↓
Customer Membuka Midtrans Snap
      ↓
QRIS / Virtual Account
      ↓
Webhook Midtrans Terverifikasi
      ↓
Order Paid
      ↓
Assign Delivery Courier
      ↓
Courier Delivery + GPS Tracking
      ↓
Delivered → Completed
```

Sistem tidak mendukung Drop Off atau Self Pickup.

---

# 2. Courier Pickup Workflow

```text
Admin Assign Pickup Courier
      ↓
Courier Mulai Pickup
      ↓
Browser Geolocation Aktif
      ↓
Lokasi Dikirim Setiap 10 Detik
      ↓
Laundry Dijemput
      ↓
Pickup Assignment Selesai
      ↓
Laundry Diterima di Outlet
```

Tracking berhenti ketika assignment pickup selesai.

---

# 3. Laundry Workflow

```text
Received At Laundry
      ↓
Input Actual Weight
      ↓
Washing
      ↓
Drying
      ↓
Ironing
      ↓
Ready
```

```text
subtotal = actual_weight × price_per_kg
total = subtotal + pickup_fee + delivery_fee + additional_fee
```

---

# 4. Payment Workflow

```text
Ready
      ↓
Customer Membuat Transaksi Midtrans
      ↓
Payment Pending + Order Waiting Payment
      ↓
Customer Membayar via QRIS / Virtual Account
      ↓
Midtrans Mengirim Webhook
      ↓
Validasi Signature + Nominal
      ↓
Payment Paid + Order Paid
```

Jika transaksi gagal atau kedaluwarsa, Payment menjadi Failed dan Order kembali ke Ready agar customer dapat mencoba lagi. Admin hanya melihat status pembayaran dan tidak melakukan verifikasi manual.

Activity Log mencatat Payment Created, Payment Pending, Payment Paid, Payment Failed, dan Payment Expired.

---

# 5. Courier Delivery Workflow

```text
Order Paid + Payment Paid
      ↓
Admin Assign Delivery Courier
      ↓
Courier Mulai Delivery
      ↓
Browser Geolocation Aktif
      ↓
Lokasi Dikirim Setiap 10 Detik
      ↓
Laundry Diterima Customer
      ↓
Delivered → Completed
```

Assignment delivery wajib ditolak jika Order atau Payment belum Paid. Tracking berhenti ketika assignment delivery selesai.

---

# 6. Tracking Workflow

- Courier mengirim lokasi dengan `navigator.geolocation.watchPosition()`.
- Lokasi dikirim melalui AJAX setiap 10 detik.
- Customer mengambil lokasi terbaru melalui polling setiap 10 detik.
- Peta menggunakan Leaflet dan OpenStreetMap.
- Tracking hanya aktif pada perjalanan pickup atau delivery.

---

# 7. Business Rules

- Customer harus login sebelum membuat order.
- Semua order menggunakan pickup dan delivery oleh Courier.
- Berat aktual hanya dapat diinput oleh Admin.
- Pembayaran hanya dapat dibuat ketika status Order Ready.
- Metode pembayaran hanya QRIS dan Virtual Account melalui Midtrans Snap.
- Status pembayaran hanya berubah melalui webhook Midtrans yang valid.
- Delivery hanya dapat ditugaskan setelah Order dan Payment Paid.
- Customer hanya dapat melihat order dan tracking miliknya.
- Courier hanya dapat mengakses assignment miliknya.

---

# 8. Error Flow

- Izin GPS ditolak: tampilkan pesan untuk mengaktifkan lokasi browser.
- Koneksi GPS terputus: pertahankan lokasi terakhir yang tersimpan.
- Signature atau nominal webhook tidak valid: tolak request tanpa mengubah status.
- Payment gagal atau kedaluwarsa: kembalikan Order ke Ready.
- Pickup/delivery gagal: Admin dapat melakukan penugasan ulang sesuai status order.

---

# 9. Constraints

- Tidak menggunakan Firebase, Redis, WebSocket, Socket.io, atau Pusher.
- Tidak menerima pembayaran Cash atau Transfer Manual.
- Tidak menyediakan aksi Verify Payment untuk Admin.
