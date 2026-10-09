# Panduan QA Laundry Wash

Dokumen ini membantu tim memeriksa alur Laundry Wash sebelum perubahan dipakai atau aplikasi didemokan. Ikuti langkahnya, catat hasil sebenarnya, lalu laporkan jika hasilnya berbeda dari yang diharapkan.

## Yang diperiksa

- Login, daftar, dan pembatasan akses berdasarkan peran.
- Pesanan, proses laundry, penugasan kurir, pembayaran, dan tracking.
- Data pelanggan, alamat, harga layanan, serta tampilan dashboard.
- Tampilan desktop dan ponsel.
- Keamanan dasar, seperti akses data milik pengguna lain.

Peran pengguna: Admin, Customer, dan Kurir.

## Cara menjalankan tes otomatis

Jalankan dari folder utama proyek. Perintah pertama membersihkan cache konfigurasi agar tes memakai pengaturan terbaru.

```powershell
php artisan optimize:clear
php artisan test
node --test tests/js/workspace-navigation.test.mjs
```

Tes Laravel memeriksa proses backend dan halaman. Tes JavaScript memeriksa navigasi workspace, termasuk drawer pada ponsel. Jika salah satu perintah gagal, salin pesan error lengkap dan catat perintah yang gagal.

## Hasil cek terakhir

Diperiksa pada 9 Oktober 2026 di lingkungan lokal.

| Bagian | Hasil | Catatan |
|---|---|---|
| Tes Laravel | LULUS — 57 tes, 365 pemeriksaan | Mencakup login, akun, katalog, dashboard, alur pesanan, pembayaran, GPS, pengaturan, dan hak akses. |
| Tes navigasi JavaScript | LULUS — 7 tes | Mencakup sidebar desktop, drawer ponsel, tombol tutup, tombol Escape, dan pencarian halaman. |
| Pembayaran langsung Midtrans Sandbox | BELUM DIPERIKSA | Tes otomatis memakai simulasi; hasilnya tidak membuktikan koneksi atau kredensial Midtrans berfungsi. |
| Tampilan di perangkat dan browser nyata | BELUM DIPERIKSA | Jalankan pemeriksaan manual di bagian berikut sebelum demo. |

Status “lulus” hanya berlaku untuk tes yang benar-benar dijalankan di atas. Jangan tandai pemeriksaan manual sebagai lulus sebelum mencobanya.

## Pemeriksaan manual sebelum demo

Gunakan akun uji untuk setiap peran. Jangan memakai data pelanggan sungguhan. Untuk pembayaran, gunakan Sandbox dan jangan memasukkan kredensial ke laporan atau Git.

### Login dan hak akses

1. Login sebagai Admin, Customer, dan Kurir dengan akun masing-masing.
2. Pastikan setiap akun masuk ke dashboard yang sesuai.
3. Coba buka halaman peran lain dengan mengganti alamat halaman di browser.
4. Coba buka pesanan atau alamat milik akun Customer lain.

Hasil yang diharapkan: pengguna hanya dapat membuka halaman dan data yang menjadi haknya. Login dengan kata sandi salah atau akun nonaktif harus ditolak dengan pesan yang jelas.

### Pesanan sampai selesai

1. Sebagai Customer, buat pesanan memakai alamat yang memiliki titik peta.
2. Sebagai Admin, konfirmasi pesanan dan tugaskan kurir untuk pickup.
3. Sebagai Kurir, mulai perjalanan, kirim lokasi, lalu konfirmasi pickup.
4. Sebagai Admin, tandai pakaian sudah diterima, masukkan berat aktual, lalu jalankan tahap laundry sesuai urutan.
5. Setelah pesanan siap, lakukan pembayaran di Sandbox.
6. Setelah pembayaran terkonfirmasi, tugaskan kurir delivery. Sebagai Kurir, mulai perjalanan dan selesaikan pengantaran.
7. Buka kembali detail pesanan dan riwayatnya.

Hasil yang diharapkan: status berubah sesuai urutan, jumlah tagihan mengikuti berat aktual dan tarif, riwayat perubahan tersimpan, dan pesanan berakhir sebagai selesai setelah pengantaran dikonfirmasi.

### Pembayaran

1. Coba QRIS dan Virtual Account di lingkungan Sandbox.
2. Tutup lalu buka kembali proses pembayaran yang masih menunggu.
3. Jika transaksi lama dibatalkan atau kedaluwarsa, pilih metode lain dan mulai pembayaran baru.
4. Periksa status pembayaran pada halaman Customer dan Admin setelah notifikasi Sandbox diterima.

Hasil yang diharapkan: metode yang dipilih tampil dengan benar, transaksi lama tidak mengunci pilihan selamanya, dan status pesanan hanya berubah setelah pembayaran terkonfirmasi. Jika Sandbox tidak dapat dijangkau, catat pesan koneksi dan waktu kejadian; jangan menyimpulkan tes otomatis membuktikan gateway normal.

### Peta, rute, dan GPS

1. Mulai tugas pickup atau delivery sebagai Kurir dan izinkan akses lokasi di browser.
2. Pastikan posisi kurir muncul di peta Customer dan bergerak setelah lokasi baru dikirim.
3. Muat ulang halaman tracking Customer.
4. Pastikan garis rute, perkiraan waktu tiba (ETA), dan jarak tersisa tampil jika layanan rute dapat dijangkau.
5. Coba akses halaman tracking setelah tugas selesai dan coba kirim lokasi lagi sebagai Kurir.
6. Ulangi dengan izin lokasi ditolak atau koneksi internet dimatikan sementara.

Hasil yang diharapkan: tracking hanya aktif saat pickup atau delivery berlangsung; setelah tugas selesai, lokasi baru ditolak. Jika jaringan peta atau layanan rute bermasalah, halaman tetap dapat dipakai dan menjelaskan bahwa data rute belum tersedia—bukan menampilkan garis atau ETA yang menyesatkan.

### Tampilan ponsel dan desktop

Periksa minimal lebar 360 px dan 390 px untuk ponsel, serta 1366 px untuk desktop. Gunakan browser yang tersedia: Chrome, Edge, Firefox, atau Safari.

- Buka dashboard, daftar pesanan, detail pesanan, halaman pembayaran, dan tugas kurir.
- Pastikan teks, tombol, kartu, tabel, dan peta tidak terpotong atau menimpa elemen lain.
- Buka dan tutup sidebar ponsel; coba tombol tutup, backdrop, dan tombol Escape.
- Pastikan tombol penting dapat ditekan dan status masih terbaca saat halaman digulir.
- Periksa cuaca pada dashboard Customer: ikon, suhu, keterangan, dan tanggal tidak bertumpuk atau keluar layar.

Catat ukuran layar dan browser. Tampilan yang hanya benar di satu ukuran belum cukup untuk dinyatakan lulus.

## Ringkasan cakupan otomatis

| Area | Yang sudah diperiksa otomatis |
|---|---|
| Login dan akun | Login/logout, akun nonaktif, pendaftaran, reset kata sandi, login Google. |
| Customer | Profil, alamat, katalog layanan, ringkasan dashboard, akses data milik sendiri, cuaca. |
| Admin | Pengelolaan pelanggan dan pengaturan harga. |
| Pesanan | Penolakan alamat milik orang lain, penolakan alur yang tidak didukung, urutan tahap laundry, syarat pembayaran sebelum delivery. |
| Pembayaran | Pembuatan dan pembukaan ulang transaksi, pergantian metode, simulasi sukses, signature webhook, status lunas/gagal/kedaluwarsa. |
| GPS | Pengiriman dan pembacaan lokasi, kepemilikan tugas, tracking pickup/delivery, penghentian tracking setelah tugas selesai. |
| Tampilan dan navigasi | Dashboard per peran, ringkasan pesanan Customer, menu workspace, sidebar desktop dan drawer ponsel. |
| Rute | Pengolahan respons layanan rute dan penanganan saat layanan rute gagal. |

Daftar ini menggambarkan tes yang tersedia saat dokumen diperbarui. Tes otomatis tidak menggantikan pemeriksaan langsung di browser, perangkat, atau layanan pihak ketiga.

## Cara mencatat bug

Gunakan format singkat ini saat menemukan masalah:

```text
Judul:
Halaman dan peran:
Perangkat/browser:
Langkah untuk mengulang:
Hasil yang diharapkan:
Hasil yang terjadi:
Seberapa mengganggu: Kritis / Tinggi / Sedang / Rendah
Bukti: tangkapan layar atau pesan error (hapus data pribadi dan rahasia)
```

Tingkat gangguan:

- **Kritis:** aplikasi atau alur utama tidak dapat dipakai, atau data pengguna berisiko.
- **Tinggi:** fitur utama gagal tanpa jalan lain yang aman.
- **Sedang:** sebagian fungsi terganggu, tetapi pekerjaan masih bisa dilanjutkan.
- **Rendah:** masalah tampilan atau kemudahan yang tidak menghalangi tugas.

## Syarat siap demo

- Semua tes otomatis di atas lulus.
- Tidak ada masalah Kritis atau Tinggi yang belum diselesaikan.
- Alur satu pesanan dari pembuatan sampai selesai sudah diperiksa manual.
- Pembayaran Sandbox, peta/rute, dan GPS sudah dicoba dengan layanan yang diperlukan aktif.
- Halaman utama sudah diperiksa di ponsel dan desktop.
- Bukti dan hasil pemeriksaan disimpan tanpa kata sandi, token, atau data pelanggan sungguhan.
