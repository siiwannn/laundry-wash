# Database Design

Brand : Laundry Wash

Version : 1.0

Database : MySQL 8

---

# Database Overview

Database dirancang menggunakan Relational Database (MySQL).

Semua relasi menggunakan Foreign Key.

Semua tabel menggunakan InnoDB.

Semua Primary Key menggunakan BIGINT UNSIGNED AUTO_INCREMENT.

Semua tabel menggunakan timestamps Laravel.

---

# users

Menyimpan seluruh akun sistem.

| Field | Type | Constraint |
|--------|------|------------|
| id | BIGINT | PK |
| name | VARCHAR(100) | NOT NULL |
| email | VARCHAR(100) | UNIQUE |
| phone | VARCHAR(20) | NULL |
| password | VARCHAR(255) | NOT NULL |
| role | ENUM('admin','customer','courier') | NOT NULL |
| is_active | BOOLEAN | DEFAULT TRUE |
| email_verified_at | TIMESTAMP | NULL |
| remember_token | VARCHAR(100) | NULL |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

---

# customer_addresses

Alamat customer.

| Field | Type |
|--------|------|
| id | BIGINT |
| user_id | FK users |
| label | VARCHAR(50) |
| recipient_name | VARCHAR(100) |
| recipient_phone | VARCHAR(20) |
| address | TEXT |
| latitude | DECIMAL(10,7) |
| longitude | DECIMAL(10,7) |
| is_default | BOOLEAN |
| created_at | TIMESTAMP |
| updated_at | TIMESTAMP |

---

# courier_profiles

Informasi tambahan Courier.

| Field | Type |
|--------|------|
| id | BIGINT |
| user_id | FK users |
| vehicle_type | VARCHAR(50) |
| vehicle_plate | VARCHAR(30) |
| status | ENUM('available','busy','offline') |
| current_latitude | DECIMAL(10,7) NULL |
| current_longitude | DECIMAL(10,7) NULL |
| created_at | TIMESTAMP |
| updated_at | TIMESTAMP |

---

# settings

Konfigurasi sistem.

| Field | Type |
|--------|------|
| id | BIGINT |
| laundry_price_per_kg | DECIMAL(12,2) |
| pickup_fee | DECIMAL(12,2) |
| delivery_fee | DECIMAL(12,2) |
| updated_by | FK users |
| created_at | TIMESTAMP |
| updated_at | TIMESTAMP |

---

# orders

Tabel utama transaksi laundry.

| Field | Type |
|--------|------|
| id | BIGINT |
| order_number | VARCHAR(50) UNIQUE |
| customer_id | FK users |
| pickup_address_id | FK customer_addresses |
| pickup_date | DATE |
| pickup_time | TIME |
| status | ENUM('pending','confirmed','pickup_assigned','courier_to_pickup','picked_up','received_at_laundry','washing','drying','ironing','ready','waiting_payment','paid','delivery_assigned','courier_to_customer','delivered','completed','cancelled') |
| payment_status | ENUM('pending','paid','failed') |
| estimated_weight | DECIMAL(8,2) NULL |
| actual_weight | DECIMAL(8,2) NULL |
| price_per_kg | DECIMAL(12,2) |
| subtotal | DECIMAL(12,2) |
| pickup_fee | DECIMAL(12,2) |
| delivery_fee | DECIMAL(12,2) |
| additional_fee | DECIMAL(12,2) |
| total | DECIMAL(12,2) |
| notes | TEXT NULL |
| created_at | TIMESTAMP |
| updated_at | TIMESTAMP |

---

# courier_assignments

Riwayat penugasan courier.

| Field | Type |
|--------|------|
| id | BIGINT |
| order_id | FK orders |
| courier_id | FK users |
| assignment_type | ENUM('pickup','delivery') |
| status | ENUM('assigned','on_the_way','completed','cancelled') |
| assigned_at | DATETIME |
| started_at | DATETIME NULL |
| completed_at | DATETIME NULL |
| created_at | TIMESTAMP |
| updated_at | TIMESTAMP |

---

# courier_locations

Tracking GPS Courier.

| Field | Type |
|--------|------|
| id | BIGINT |
| assignment_id | FK courier_assignments |
| courier_id | FK users |
| latitude | DECIMAL(10,7) |
| longitude | DECIMAL(10,7) |
| accuracy | DECIMAL(8,2) NULL |
| speed | DECIMAL(8,2) NULL |
| heading | DECIMAL(8,2) NULL |
| recorded_at | DATETIME |

---

# order_status_histories

Riwayat perubahan status order.

| Field | Type |
|--------|------|
| id | BIGINT |
| order_id | FK orders |
| old_status | VARCHAR(50) |
| new_status | VARCHAR(50) |
| note | TEXT NULL |
| changed_by | FK users |
| created_at | TIMESTAMP |

---

# payments

Pembayaran.

| Field | Type |
|--------|------|
| id | BIGINT |
| order_id | FK orders |
| gateway_order_id | VARCHAR(100) UNIQUE |
| gateway_transaction_id | VARCHAR(100) NULL |
| snap_token | TEXT NULL |
| gateway_status | VARCHAR(50) NULL |
| method | ENUM('qris','virtual_account') NULL |
| amount | DECIMAL(12,2) |
| status | ENUM('pending','paid','failed') |
| paid_at | DATETIME NULL |
| expires_at | DATETIME NULL |
| created_at | TIMESTAMP |
| updated_at | TIMESTAMP |

---

# activity_logs

Audit Log.

| Field | Type |
|--------|------|
| id | BIGINT |
| user_id | FK users |
| activity | VARCHAR(255) |
| ip_address | VARCHAR(45) |
| user_agent | TEXT |
| created_at | TIMESTAMP |

---

# Relationship

users

1 : N

customer_addresses

---

users

1 : N

orders

---

users

1 : 1

courier_profiles

---

orders

1 : N

courier_assignments

---

users

1 : N

courier_assignments

---

courier_assignments

1 : N

courier_locations

---

orders

1 : N

payments

---

orders

1 : N

order_status_histories

---

users

1 : N

activity_logs

---

# Database Index

users.email

users.role

orders.order_number

orders.customer_id

orders.status

orders.payment_status

orders.created_at

courier_assignments.courier_id

courier_assignments.assignment_type

courier_locations.assignment_id

courier_locations.recorded_at

payments.order_id

payments.status

order_status_histories.order_id

activity_logs.user_id

---

# Business Rules

- Sistem hanya mendukung Laundry Kiloan.
- Harga dihitung berdasarkan Berat Aktual × Harga per Kilogram.
- Berat aktual hanya dapat diinput Admin.
- Pembayaran dilakukan setelah laundry selesai melalui Midtrans Snap.
- Hanya QRIS dan Virtual Account yang didukung.
- Webhook Midtrans memperbarui status pembayaran dan order secara otomatis.
- Order selalu menggunakan pickup dan delivery oleh Courier.
- GPS hanya aktif saat Pickup atau Delivery.
- Semua perubahan status disimpan pada order_status_histories.
- Semua aktivitas penting disimpan pada activity_logs.

---

# Database Convention

Primary Key

id

Foreign Key

nama_tabel_id

Timestamp

created_at

updated_at

Soft Delete

Tidak digunakan pada MVP.

Engine

InnoDB

Charset

utf8mb4

Collation

utf8mb4_unicode_ci
