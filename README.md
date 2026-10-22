# JelajahKu — Backend Booking Tiket Destinasi

Backend REST API untuk aplikasi booking tiket destinasi wisata lokal, dibangun dengan **Laravel 12 + MySQL**.
Implementasi mengikuti dokumen *Architecture Planning Aplikasi Booking Tiket Destinasi* dan
*MVP Core Features Development (JelajahKu)*.

Pendekatan arsitektur: **modular monolith** — satu aplikasi backend dengan batas modul yang jelas
(auth, katalog, tiket & kuota, booking, pembayaran, e-tiket/QR, review & voucher, laporan admin) plus worker
untuk pekerjaan latar belakang.

## Fitur

| Kode | Fitur | Status |
|------|-------|--------|
| F1 | Autentikasi (register, login, logout, profil) | ✅ |
| F2–F3 | Daftar, pencarian, filter & detail destinasi | ✅ |
| F4 | Pilih tanggal kunjungan & jumlah tiket + cek kuota | ✅ |
| F5 | Checkout & reservasi (kode unik, kuota ditahan sementara) | ✅ |
| F6 | Pembayaran + webhook idempotent & verifikasi signature | ✅ |
| F7 | E-tiket QR (SVG) sekali pakai | ✅ |
| F8 | Riwayat & detail pesanan | ✅ |
| F9–F10 | Admin: CRUD destinasi/tiket/kategori + kuota per tanggal | ✅ |
| F11 | Dashboard admin, daftar pesanan, ekspor CSV, laporan penjualan | ✅ |
| F12 | Validasi/scan tiket di pintu masuk | ✅ |
| F13 | Profil pengguna | ✅ |
| F14 | Notifikasi email e-tiket (queued job) | ✅ |
| F15 | Filter kategori, lokasi, harga | ✅ |
| — | Voucher / kode promo | ✅ |
| — | Halaman katalog publik (Blade, konsumsi data dinamis) | ✅ |

## Kebutuhan

- PHP 8.2+ (ekstensi `fileinfo`)
- Composer 2
- MySQL / MariaDB
- Node.js (opsional, hanya untuk aset Blade/Vite)

## Instalasi

```bash
composer install
copy .env.example .env        # Windows
php artisan key:generate

# Buat database lalu sesuaikan kredensial di .env
# DB_DATABASE=jelajahku

php artisan migrate --seed
php artisan storage:link      # agar bukti transfer bisa diakses
```

Akun hasil seeder:

| Peran | Email | Password |
|-------|-------|----------|
| Admin | `admin@jelajahku.test` | `password` |
| Pengunjung | `wisatawan@jelajahku.test` | `password` |

## Menjalankan

```bash
php artisan serve                                   # API di http://127.0.0.1:8000
php artisan queue:work                               # worker untuk email e-tiket
php artisan schedule:work                            # worker kedaluwarsa booking (bookings:expire)
```

Atau sekaligus: `composer run dev`.

> Halaman katalog publik (Blade) tersedia di `http://127.0.0.1:8000/` — destinasi, harga, dan detail
> dibaca dinamis dari database.

> Worker `bookings:expire` dijalankan setiap menit (lihat `routes/console.php`) untuk
> mengembalikan kuota booking yang melewati batas pembayaran.

## Konfigurasi Pembayaran

Diatur melalui `config/payment.php` / `.env`:

- `PAYMENT_DRIVER=manual` — pengunjung mengunggah bukti transfer, admin memverifikasi.
- `PAYMENT_DRIVER=sandbox` — gateway dengan callback bertanda tangan (mock Midtrans/Xendit),
  signature HMAC-SHA256 (`X-Signature`) dengan `PAYMENT_WEBHOOK_SECRET`.
- `BOOKING_PAYMENT_TTL_MINUTES` — batas waktu pembayaran (default 15 menit).

## Alur Utama

1. Pengunjung memilih destinasi, tanggal, jenis & jumlah tiket.
2. Sistem mengecek `ticket_availability` untuk tanggal tersebut.
3. Booking dibuat berstatus `pending`, kuota ditahan **atomik**, `expires_at` ditetapkan.
4. Pengunjung membayar (transfer manual atau gateway).
5. Webhook/verifikasi admin menandai `paid`, e-tiket + QR diterbitkan, email dikirim.
6. Jika lewat batas waktu → `expired` dan kuota dikembalikan oleh worker.
7. Petugas memindai QR → tiket menjadi `used`.

Status booking: `pending → paid → completed`, cabang `expired` / `cancelled`.

## Endpoint API (`/api/v1`)

### Publik
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/auth/register` | Registrasi pengunjung (rate limited) |
| POST | `/auth/login` | Login, mengembalikan token Sanctum |
| GET | `/categories` | Daftar kategori |
| GET | `/destinations` | Daftar/pencarian destinasi (`q`, `category`, `location`, `min_price`, `max_price`, `sort`, `per_page`) |
| GET | `/destinations/{id}` | Detail destinasi |
| GET | `/destinations/{id}/tickets?date=` | Jenis tiket & sisa kuota per tanggal |

### Pengunjung (Bearer token)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/me` | Profil |
| PUT | `/me` | Ubah profil/password |
| POST | `/auth/logout` | Logout |
| POST | `/bookings` | Buat booking |
| GET | `/bookings` | Riwayat pesanan |
| GET | `/bookings/{code}` | Detail & status booking |
| POST | `/bookings/{code}/cancel` | Batalkan booking pending |
| POST | `/bookings/{code}/payments` | Inisiasi pembayaran (`method`: qris/va/ewallet/transfer) |
| GET | `/payments/{transaction_ref}` | Status pembayaran |
| POST | `/payments/{transaction_ref}/proof` | Unggah bukti transfer |
| GET | `/me/tickets` | E-tiket milik pengguna |
| GET | `/tickets/{code}` | Detail e-tiket |
| GET | `/tickets/{code}/qr` | QR code (SVG) |
| POST | `/destinations/{id}/reviews` | Tambah ulasan (setelah bayar) |
| POST | `/vouchers/check` | Cek validitas voucher |

### Gateway
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/payments/webhook` | Callback gateway (verifikasi signature, idempotent) |

### Admin (Bearer token, role `admin`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/admin/dashboard` | Ringkasan pesanan/pendapatan |
| GET | `/admin/reports/sales` | Laporan penjualan (`from`, `to`, `destination_id`) |
| GET/POST/PUT/DELETE | `/admin/categories` | Kelola kategori |
| GET/POST/PUT/DELETE | `/admin/destinations` | Kelola destinasi |
| GET/POST/PUT/DELETE | `/admin/tickets` | Kelola jenis tiket |
| POST | `/admin/tickets/validate` | Validasi tiket via QR |
| GET/PUT | `/admin/availability` | Lihat / atur kuota per tanggal |
| GET | `/admin/bookings`, `/admin/bookings/export` | Daftar & ekspor CSV pesanan |
| GET | `/admin/payments` | Daftar pembayaran |
| POST | `/admin/payments/{transaction_ref}/verify` | Verifikasi bukti transfer |
| GET/POST/PUT/DELETE | `/admin/vouchers` | Kelola voucher |

## Jaminan Konsistensi

- **Kuota atomik** — `QuotaService::reserve()` memakai
  `UPDATE ticket_availability SET sold = sold + qty WHERE quota - sold >= qty`
  di dalam transaksi; booking dibatalkan bila tidak ada baris yang berubah.
- **Reservasi sementara** — booking `pending` menahan kuota hingga `expires_at`.
- **Webhook idempotent** — diproses berdasarkan `transaction_ref` unik; pengiriman ulang tidak
  menerbitkan tiket ganda.
- **Snapshot harga** — `booking_items.unit_price` menyimpan harga saat transaksi sehingga perubahan
  harga di `ticket_types` tidak mempengaruhi pesanan lama.
- **Tiket sekali pakai** — validasi mengunci baris tiket dan menolak pemakaian berulang atau di luar
  tanggal kunjungan.

## Skema Database (11 tabel)

- **Master**: `categories`, `destinations`, `ticket_types`, `ticket_availability`
- **Transaksi**: `bookings`, `booking_items`, `payments`, `tickets`
- **Pendukung**: `users`, `reviews`, `vouchers`

## Pengujian

```bash
php artisan test
php artisan pint --test
```

Skenario kritis yang diuji:

1. Dua pengguna memesan tiket terakhir bersamaan → hanya satu berhasil.
2. Pembayaran melewati batas waktu → `expired`, kuota kembali.
3. Webhook pembayaran dikirim dua kali → hanya satu set tiket terbit.
4. QR dipindai dua kali → ditolak (`already_used`).
5. QR dipindai pada tanggal berbeda → ditolak (`wrong_date`).
6. Pengguna membuka pesanan milik orang lain → `403`.
7. Admin mengubah harga setelah ada pesanan → pesanan lama tetap harga lama.
8. Signature webhook tidak valid → `401`.

## Struktur Kode

```
app/
├── Console/Commands/ExpireBookings.php   # worker kedaluwarsa booking
├── Http/
│   ├── Controllers/CatalogController.php # halaman katalog publik (Blade)
│   ├── Controllers/Api/V1/               # controller publik & pengunjung
│   │   └── Admin/                        # controller admin
│   ├── Middleware/EnsureUserHasRole.php  # RBAC
│   └── Requests/Api/                     # validasi server-side (FormRequest)
├── Jobs/SendEticketJob.php               # kirim e-tiket (queued)
├── Mail/EticketMail.php
├── Models/                               # 11 model domain
└── Services/
    ├── BookingService.php                # checkout, expiry, pembatalan
    ├── PaymentService.php                # inisiasi, webhook, verifikasi
    ├── QuotaService.php                  # reservasi kuota atomik
    ├── ReportService.php                 # dashboard & laporan
    ├── TicketService.php                 # terbit & validasi tiket, QR
    └── Payments/                         # abstraksi payment gateway
```

## Dokumentasi Sprint 2

Laporan tugas Sprint 2 (S2-03 s/d S2-06) dalam format `.docx` tersedia di folder `dokumentasi/`.
