# Sprint 3 — Bukti Implementasi & Dokumentasi

Folder ini berisi dokumentasi Sprint 3 aplikasi **JelajahKu — Booking Tiket Destinasi**.
Seluruh tugas dikerjakan langsung di dalam basis kode, diuji ulang, dan dilampirkan bukti
artefaknya. Dokumen utama dalam format `.docx` dapat dibuka dengan Microsoft Word,
LibreOffice Writer, atau Google Docs.

## Ringkasan hasil

| Indikator | Hasil |
|-----------|-------|
| Dokumen | 7 dokumen tugas (S3-01 s.d. S3-07) + 1 ikhtisar (S3-00) |
| Suite pengujian | **88 tes lulus / 455 assertion** dalam 10,79 detik |
| Peningkatan dari baseline Sprint 2 | +21 tes dan +103 assertion (67/352 → 88/455) |
| Formatter Laravel Pint | Lulus |
| Kompilasi Blade (`view:cache`) | Berhasil tanpa error |
| Validasi OOXML | 8 dari 8 dokumen lulus (ZIP dan XML valid) |
| Screenshot responsif | 15 PNG (mobile 390×844, tablet 768×1024, desktop 1440×900) |
| Bukti HTTP langsung | Rekaman header/body untuk auth, error API/web, dan header keamanan |

## Daftar dokumen

| Kode | Dokumen | Fokus utama |
|------|---------|-------------|
| S3-00 | [Sprint 3 Overview](S3-00_%20SPRINT%203%20OVERVIEW.docx) | Rekapitulasi, matriks bukti, dan cara verifikasi ulang |
| S3-01 | [Authentication & Authorization Integration](S3-01_%20AUTHENTICATION%20%26%20AUTHORIZATION%20INTEGRATION.docx) | Login web/API, token Sanctum, RBAC admin/customer, bukti 302/401/403 |
| S3-02 | [Global Error Handling](S3-02_%20GLOBAL%20ERROR%20HANDLING.docx) | Envelope JSON konsisten, 8 halaman error kustom, fallback API |
| S3-03 | [Functional Testing](S3-03_%20FUNCTIONAL%20TESTING.docx) | Strategi tes, inventaris 17 kelas, hasil 88 tes / 455 assertion |
| S3-04 | [Bug Fixing & Issue Resolution](S3-04_%20BUG%20FIXING%20%26%20ISSUE%20RESOLUTION.docx) | Enam perbaikan nyata, akar masalah, dan tes regresi |
| S3-05 | [Responsive & Mobile Quality Check](S3-05_%20RESPONSIVE%20%26%20MOBILE%20QUALITY%20CHECK.docx) | Sidebar off-canvas, tabel scroll, form adaptif, navigasi mobile |
| S3-06 | [Basic Security Implementation](S3-06_%20BASIC%20SECURITY%20IMPLEMENTATION.docx) | Header keamanan, rate limit, validasi upload, HMAC webhook, sesi |
| S3-07 | [Performance & Usability Refinement](S3-07_%20PERFORMANCE%20%26%20USABILITY%20REFINEMENT.docx) | Cache kategori, agregasi query, lazy image, aksesibilitas |

Versi PDF dari kedelapan dokumen tersedia satu per satu di folder [`pdf/`](pdf/), ditambah
versi gabungan [`S3_LENGKAP_SPRINT_3.pdf`](pdf/S3_LENGKAP_SPRINT_3.pdf) (48 halaman).

## Bukti implementasi di basis kode

| Area | Berkas utama |
|------|--------------|
| Autentikasi & otorisasi | `app/Http/Controllers/Web/AuthController.php`, `app/Http/Controllers/Api/V1/AuthController.php`, `app/Http/Middleware/EnsureUserHasRole.php`, `routes/web.php`, `routes/api.php` |
| Penanganan error global | `bootstrap/app.php`, `app/Http/Middleware/SecurityHeaders.php`, `resources/views/errors/` |
| Rate limiting | `app/Providers/AppServiceProvider.php`, `routes/web.php` |
| Keamanan webhook | `app/Services/Payments/SandboxGateway.php`, `app/Services/PaymentService.php` |
| Responsif admin | `resources/views/layouts/admin.blade.php`, halaman `resources/views/admin/` |
| Usability pengunjung | `resources/views/layouts/app.blade.php`, `resources/views/catalog/` |
| Performa katalog | `app/Models/Category.php`, `app/Http/Controllers/CatalogController.php` |
| Tes Sprint 3 | `tests/Feature/AuthTest.php`, `ErrorHandlingTest.php`, `SecurityTest.php`, `ResponsiveUiTest.php`, `PerformanceUsabilityTest.php` |

## Struktur folder

```text
dokumentasi/Sprint 3/
├── S3-00_ SPRINT 3 OVERVIEW.docx
├── S3-01_ AUTHENTICATION & AUTHORIZATION INTEGRATION.docx
├── S3-02_ GLOBAL ERROR HANDLING.docx
├── S3-03_ FUNCTIONAL TESTING.docx
├── S3-04_ BUG FIXING & ISSUE RESOLUTION.docx
├── S3-05_ RESPONSIVE & MOBILE QUALITY CHECK.docx
├── S3-06_ BASIC SECURITY IMPLEMENTATION.docx
├── S3-07_ PERFORMANCE & USABILITY REFINEMENT.docx
├── README.md
├── pdf/
│   ├── S3-00_ SPRINT 3 OVERVIEW.pdf
│   ├── S3-01_ AUTHENTICATION & AUTHORIZATION INTEGRATION.pdf
│   ├── S3-02_ GLOBAL ERROR HANDLING.pdf
│   ├── S3-03_ FUNCTIONAL TESTING.pdf
│   ├── S3-04_ BUG FIXING & ISSUE RESOLUTION.pdf
│   ├── S3-05_ RESPONSIVE & MOBILE QUALITY CHECK.pdf
│   ├── S3-06_ BASIC SECURITY IMPLEMENTATION.pdf
│   ├── S3-07_ PERFORMANCE & USABILITY REFINEMENT.pdf
│   └── S3_LENGKAP_SPRINT_3.pdf
├── tools/
│   ├── DocxBuilder.php          # pembangun OOXML (.docx)
│   ├── generate-docs.php        # generator seluruh dokumen
│   └── validate-docs.php        # validator struktur ZIP/XML dokumen
└── evidence/
    ├── screenshots/             # 15 screenshot responsif
    ├── test-run.txt             # hasil suite akhir
    ├── test-inventory.txt       # daftar 88 nama tes
    ├── pint.txt                 # hasil formatter
    ├── view-cache.txt           # hasil kompilasi Blade
    ├── docx-validation.txt      # hasil validasi 8 dokumen
    ├── route-list.txt           # 100 rute web + API
    ├── performance-timing.txt   # pengukuran 5x per URL
    ├── headers-masuk.txt        # header keamanan respons 200
    ├── api-*.txt                # respons API 401/404/405/422
    ├── web-404.txt              # halaman error web
    ├── auth-*.txt               # skenario autentikasi/otorisasi
    ├── api-login.txt, api-me.txt
    └── capture.mjs              # skrip screenshot Chrome headless
```

## Bukti HTTP langsung

Berkas `*-headers.txt` dan `*-body.txt` menyimpan respons mentah, sedangkan berkas
`*-txt` gabungan memuat ringkasan skenario beserta header dan body. Ringkasan yang
terverifikasi:

| Skenario | Hasil |
|----------|-------|
| Tamu membuka `/admin` | `302 Found` ke `/admin/login` |
| Login admin berhasil | `302 Found` ke `/admin`, sesi aktif |
| Customer membuka `/admin` | `403 Forbidden` |
| API tanpa token | `401 Unauthorized` dengan envelope JSON konsisten |
| API endpoint tidak dikenal | `404 Not Found` JSON |
| API metode salah | `405 Method Not Allowed` JSON |
| API validasi gagal | `422 Unprocessable Content` + detail `errors` |
| Halaman web tidak ditemukan | `404` memakai view error kustom |
| Respons sukses/error | Membawa `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy` |

Token API dan cookie sesi pada rekaman telah dihapus atau disamarkan.

## Screenshot

| Halaman | Mobile 390×844 | Tablet 768×1024 | Desktop 1440×900 |
|---------|:---:|:---:|:---:|
| Beranda | ✓ | ✓ | ✓ |
| Destinasi | ✓ | — | ✓ |
| Masuk | ✓ | — | ✓ |
| Login admin | ✓ | — | ✓ |
| Dashboard admin | ✓ | ✓ | ✓ |
| Booking admin | — | — | ✓ |
| Halaman 404 | ✓ | — | ✓ |

Nama berkas mengikuti pola `<halaman>-<perangkat>-<lebar>x<tinggi>.png` di
`evidence/screenshots/`.

## Cara memverifikasi ulang

Jalankan dari root proyek:

```bash
# 1. Seluruh tes (SQLite :memory:, tidak memerlukan MySQL)
php artisan test --compact

# 2. Formatter
vendor\bin\pint --test

# 3. Kompilasi view
php artisan view:cache

# 4. Server untuk verifikasi HTTP manual
php artisan serve --host=127.0.0.1 --port=8099
```

Untuk database bukti HTTP, gunakan SQLite:

```bash
set DB_CONNECTION=sqlite
set DB_DATABASE=database/evidence.sqlite
php artisan migrate:fresh --seed
```

Setelah server aktif, ulangi request pada dokumen S3-01, S3-02, dan S3-06 lalu
bandingkan dengan berkas di `evidence/`. Screenshot dapat diambil ulang dengan:

```bash
node "dokumentasi/Sprint 3/evidence/capture.mjs"
```

## Regenerasi dokumen

Dokumen dihasilkan ulang dari sumber PHP tanpa dependensi eksternal:

```bash
php "dokumentasi/Sprint 3/tools/generate-docs.php"
php "dokumentasi/Sprint 3/tools/validate-docs.php"
```

Generator memakai `ZipArchive` untuk menulis OOXML, sehingga tidak memerlukan
Microsoft Word, Pandoc, atau LibreOffice. Validator memeriksa struktur ZIP, bagian
OOXML wajib, dan seluruh XML/RELS; hasilnya ditulis ke `evidence/docx-validation.txt`.
Setelah regenerasi, buka dokumen untuk memastikan tata letak dan gambar tetap sesuai.

## Catatan

- MySQL lokal tidak aktif saat verifikasi, sehingga bukti HTTP langsung memakai
  `database/evidence.sqlite`; suite tes tetap memakai SQLite `:memory:`.
- Pengukuran waktu halaman dilakukan pada server pengembangan `artisan serve`.
  Angka tersebut berguna untuk membandingkan perubahan, bukan benchmark produksi.
- Screenshot diambil dengan Chrome headless; Tailwind dan Alpine dimuat dari CDN
  sehingga hasil render bergantung pada koneksi internet saat pengambilan gambar.
- Akun seeder: admin `admin@jelajahku.test` / `password`, customer
  `wisatawan@jelajahku.test` / `password`. Jangan gunakan kredensial ini di produksi.
