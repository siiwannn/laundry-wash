# API Specification

Brand : Laundry Wash

Version : 1.0

Base URL

/api

Authentication

Laravel Sanctum

Content-Type

application/json

---

# Standard Response

## Success

HTTP 200

```json
{
    "success": true,
    "message": "Success",
    "data": {}
}
```

---

## Validation Error

HTTP 422

```json
{
    "success": false,
    "message": "Validation Error",
    "errors": {}
}
```

---

## Unauthorized

HTTP 401

```json
{
    "success": false,
    "message": "Unauthorized"
}
```

---

## Not Found

HTTP 404

```json
{
    "success": false,
    "message": "Data Not Found"
}
```

---

# Authentication

## POST /login

Request

```json
{
    "email":"customer@email.com",
    "password":"password123"
}
```

Response

```json
{
    "token":"xxxxxxxx",
    "role":"customer"
}
```

---

## POST /logout

Authentication Required

---

# Customer

## GET /customer/dashboard

Dashboard Customer

---

## GET /customer/profile

Melihat profil.

---

## PUT /customer/profile

Mengubah profil.

---

# Address

## GET /customer/addresses

Daftar alamat.

---

## POST /customer/addresses

Tambah alamat.

---

## PUT /customer/addresses/{id}

Update alamat.

---

## DELETE /customer/addresses/{id}

Hapus alamat.

---

# Orders

## GET /orders

Daftar order Customer.

Filter

- status

---

## POST /orders

```json
{
    "pickup_address_id":1,
    "service_id":1,
    "pickup_date":"2026-10-10",
    "pickup_time":"09:00",
    "notes":"Jangan gunakan parfum."
}
```

---

## GET /orders/{id}

Detail order.

---

## GET /orders/{id}/tracking

Mengembalikan posisi Courier, bearing, status perjalanan, tujuan, route GeoJSON, remaining distance dalam meter, dan ETA dalam detik. Endpoint hanya dapat dilihat oleh pemilik order, Admin, atau Courier yang ditugaskan.

---

# Payment

## POST /orders/{id}/payment

Tidak menerima metode atau nominal dari client. Sistem membuat transaksi Midtrans berdasarkan total order dan mengembalikan `snap_token` serta `client_key`.

---

## POST /api/midtrans/notification

Webhook publik Midtrans. Sistem memvalidasi signature dan nominal sebelum memperbarui Payment dan Order. Status settlement/capture menjadi Paid; expire, deny, cancel, atau failure menjadi Failed.

---

# Admin

## GET /admin/dashboard

Dashboard Admin.

---

## GET /admin/orders

Filter

- status

- customer

- date

---

## GET /admin/orders/{id}

Detail Order.

---

## PATCH /admin/orders/{id}/confirm

Konfirmasi Order.

---

## PATCH /admin/orders/{id}/weight

```json
{
    "actual_weight":4.75
}
```

---

## PATCH /admin/orders/{id}/status

```json
{
    "status":"washing"
}
```

---

## POST /admin/orders/{id}/assign

```json
{
    "courier_id":5,
    "assignment_type":"pickup"
}
```

assignment_type

pickup

delivery

---

Admin hanya dapat melihat status pembayaran. Tidak tersedia endpoint verifikasi pembayaran manual.

# Laundry Price

## GET /admin/settings

Lihat harga laundry.

---

## PUT /admin/settings

```json
{
    "laundry_price_per_kg":8000,
    "pickup_fee":5000,
    "delivery_fee":5000
}
```

---

# Courier

## GET /courier/dashboard

Dashboard Courier.

---

## GET /courier/tasks

Daftar tugas.

---

## GET /courier/tasks/{id}

Detail tugas.

---

## PATCH /courier/tasks/{id}/start

Memulai Pickup atau Delivery.

---

## POST /courier/tasks/{id}/location

```json
{
    "latitude":-6.200000,
    "longitude":106.816666,
    "accuracy":10,
    "speed":8.4,
    "heading":135
}
```

Dipanggil setiap

10 detik.

---

## PATCH /courier/tasks/{id}/pickup-complete

Pickup selesai.

---

## PATCH /courier/tasks/{id}/delivery-complete

Delivery selesai.

---

# Reports

## GET /admin/reports

Query

start_date

end_date

status

---

# Middleware

Guest

- Login
- Register

Customer

- Dashboard
- Orders
- Payment
- Tracking
- Profile

Admin

- Dashboard
- Orders
- Courier
- Reports
- Settings

Courier

- Dashboard
- Tasks
- Tracking

---

# Business Rules

Semua endpoint selain Login wajib Authentication.

Customer hanya dapat melihat order miliknya.

Courier hanya dapat melihat tugas miliknya.

Admin memiliki akses penuh.

GPS hanya aktif ketika Pickup atau Delivery.

Payment hanya dapat dilakukan ketika status Ready.

Delivery hanya dapat dilakukan setelah status Paid.

---

# API Convention

Method

GET

POST

PUT

PATCH

DELETE

Response

JSON

Authentication

Laravel Sanctum

Timezone

Asia/Jakarta

Date Format

Y-m-d

DateTime Format

Y-m-d H:i:s
