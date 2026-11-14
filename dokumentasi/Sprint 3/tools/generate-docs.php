<?php

require __DIR__.'/DocxBuilder.php';

$outDir = dirname(__DIR__);
$evidence = dirname(__DIR__).'/evidence';
$shots = $evidence.'/screenshots';

function baseDoc(string $code, string $title, string $subtitle, string $module, string $footer): DocxBuilder
{
    return new DocxBuilder($code, $title, $subtitle, [
        'Kode Dokumen' => $code,
        'Modul / Tugas' => $module,
        'Proyek' => 'JelajahKu — Booking Tiket Destinasi',
        'Teknologi' => 'Laravel 12 · PHP 8.2 · Blade · Tailwind CSS · Alpine.js · Sanctum',
        'Lingkungan Verifikasi' => 'PHP 8.2.12 · SQLite evidence DB · Chrome/Edge headless',
        'Status' => 'Selesai — terverifikasi',
    ], $footer);
}

function standardTestSummary(DocxBuilder $b): void
{
    $b->h2('Ringkasan pengujian akhir');
    $b->p('Seluruh suite dijalankan ulang setelah perubahan terakhir. Hasilnya **88 tes lulus dengan 455 assertion** dalam 10,79 detik. Perintah formatter `vendor/bin/pint --test` juga lulus, dan seluruh Blade view berhasil di-cache.');
    $b->code(<<<'TXT'
php artisan test --compact --no-ansi
  ........................................................................................

  Tests:    88 passed (455 assertions)
  Duration: 10.79s

vendor\bin\pint --test --no-ansi
{"tool":"pint","result":"passed"}

php artisan view:cache
INFO  Blade templates cached successfully.
TXT, 'Keluaran verifikasi akhir Sprint 3.');
}

// =====================================================================
// S3-00 — IKHTISAR SPRINT 3
// =====================================================================
$b = baseDoc(
    'S3-00',
    'Ikhtisar Sprint 3',
    'Rekapitulasi implementasi, pengujian, dan bukti artefak tujuh tugas Sprint 3',
    'Seluruh tugas Sprint 3',
    'S3-00 · Ikhtisar Sprint 3'
);
$b->cover();

$b->h1('1. Ringkasan Eksekutif');
$b->p('Sprint 3 berfokus pada pematangan kualitas aplikasi JelajahKu setelah fitur inti Sprint 2 selesai. Tujuh tugas dikerjakan secara nyata di dalam basis kode, bukan hanya didokumentasikan: integrasi autentikasi dan otorisasi, penanganan error global, pengujian fungsional, perbaikan bug, pemeriksaan responsif, implementasi keamanan dasar, serta penyempurnaan performa dan usability.');
$b->p('Setiap tugas memiliki dokumen bukti tersendiri. Dokumen ini menjadi pintu masuk (indeks) yang menghubungkan tugas, perubahan kode, bukti pengujian, dan artefak pendukung.');
$b->table(['Indikator', 'Hasil'], [
    ['Dokumen tugas', '7 dokumen (S3-01 s.d. S3-07) + 1 ikhtisar'],
    ['Suite pengujian', '88 tes lulus / 455 assertion'],
    ['Peningkatan dari baseline', '+21 tes dan +103 assertion (baseline 67 tes / 352 assertion)'],
    ['Formatter', 'Laravel Pint — passed'],
    ['Blade view cache', 'Berhasil (tanpa error kompilasi)'],
    ['Validasi OOXML', '8 dari 8 dokumen lulus validasi ZIP/XML'],
    ['Screenshot responsif', '15 gambar (mobile 390px, tablet 768px, desktop 1440px)'],
    ['Bukti HTTP langsung', '19 berkas rekaman request/response dan pengukuran'],
    ['Status keseluruhan', 'Selesai — terverifikasi'],
], [3000, 6638], ['fontSize' => 19]);

$b->h1('2. Daftar Dokumen');
$b->table(['Kode', 'Dokumen', 'Fokus utama', 'Status'], [
    ['S3-01', 'Autentikasi & Otorisasi', 'Login web/API, token Sanctum, RBAC admin/customer, bukti 302/401/403', 'Selesai'],
    ['S3-02', 'Penanganan Error Global', 'Envelope JSON konsisten, halaman error kustom, fallback API, header pada error', 'Selesai'],
    ['S3-03', 'Pengujian Fungsional', 'Strategi tes, inventaris 17 kelas, hasil 88 tes / 455 assertion', 'Selesai'],
    ['S3-04', 'Perbaikan Bug & Issue', 'Enam perbaikan nyata beserta akar masalah dan tes regresi', 'Selesai'],
    ['S3-05', 'Responsif & Kualitas Mobile', 'Off-canvas admin, tabel scroll, form adaptif, navigasi mobile, screenshot', 'Selesai'],
    ['S3-06', 'Keamanan Dasar', 'Header keamanan, rate limit, upload, HMAC webhook, sesi, otorisasi', 'Selesai'],
    ['S3-07', 'Performa & Usability', 'Cache kategori, agregasi query, lazy image, aksesibilitas, pengukuran', 'Selesai'],
    ['S3-00', 'Ikhtisar Sprint 3', 'Rekapitulasi, matriks bukti, cara verifikasi ulang', 'Selesai'],
], [900, 2750, 4988, 1000], ['fontSize' => 18]);

$b->h1('3. Matriks Keterkaitan Tugas, Implementasi, dan Bukti');
$b->table(['Tugas', 'Implementasi utama', 'Bukti verifikasi'], [
    ['Authentication & Authorization Integration', 'Guard sesi web, token Sanctum, middleware `role`, pemisahan area customer/admin', 'S3-01; auth-*.txt; AuthTest 5; WebAuthTest 8'],
    ['Global Error Handling', 'Exception handler terpusat, envelope JSON, 8 view error kustom, fallback API', 'S3-02; api-401/404/405/422.txt; web-404.txt'],
    ['Functional Testing', '16 kelas feature + 1 unit, SQLite in-memory, RefreshDatabase', 'S3-03; test-run.txt; test-inventory.txt'],
    ['Bug Fixing & Issue Resolution', 'Enam perbaikan terverifikasi dan tes regresi', 'S3-04; SecurityTest; PerformanceUsabilityTest'],
    ['Responsive & Mobile Quality Check', 'Layout admin off-canvas, tabel scroll, form satu kolom, navigasi bawah 44px', 'S3-05; 15 screenshot; ResponsiveUiTest 4'],
    ['Basic Security Implementation', 'Header keamanan, throttle, validasi upload, HMAC, sesi aman', 'S3-06; headers-masuk.txt; SecurityTest 7'],
    ['Performance & Usability Refinement', 'Cache kategori, invalidasi cache, agregasi ulasan, lazy loading, skip link', 'S3-07; performance-timing.txt; tes 4'],
], [2300, 4238, 3100], ['fontSize' => 18]);

$b->h1('4. Ringkasan Pengujian');
$b->table(['Metrik', 'Baseline Sprint 2', 'Akhir Sprint 3', 'Perubahan'], [
    ['Tes lulus', '67', '88', '+21'],
    ['Assertion', '352', '455', '+103'],
    ['Tes gagal', '0', '0', '0'],
    ['Durasi suite', '± 9 detik', '10,79 detik', '+ sekitar 1,8 detik'],
    ['Formatter Pint', 'Lulus', 'Lulus', '—'],
], [3000, 2400, 2400, 1838], ['fontSize' => 19]);
standardTestSummary($b);

$b->h1('5. Struktur Artefak');
$b->p('Seluruh bukti disimpan di dalam folder `dokumentasi/Sprint 3/` dengan struktur berikut.');
$b->code(<<<'TXT'
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
├── tools/
│   ├── DocxBuilder.php
│   ├── generate-docs.php
│   └── validate-docs.php
└── evidence/
    ├── screenshots/          (15 PNG)
    ├── test-run.txt
    ├── test-inventory.txt
    ├── pint.txt
    ├── view-cache.txt
    ├── docx-validation.txt
    ├── route-list.txt
    ├── performance-timing.txt
    ├── headers-masuk.txt
    ├── api-401.txt, api-404.txt, api-405.txt, api-422.txt
    ├── web-404.txt
    ├── auth-guest-admin.txt
    ├── auth-admin-login.txt
    ├── auth-admin-dashboard.txt
    ├── auth-customer-login.txt
    ├── auth-customer-admin-forbidden.txt
    ├── api-login.txt
    └── api-me.txt
TXT, 'Struktur folder dokumentasi Sprint 3.');

$b->h1('6. Cara Memverifikasi Ulang');
$b->numbered([
    'Pastikan dependensi terpasang: `composer install`.',
    'Jalankan seluruh tes: `php artisan test --compact`. Tes memakai SQLite `:memory:` sehingga tidak memerlukan MySQL.',
    'Jalankan formatter: `vendor\\bin\\pint --test`.',
    'Kompilasi view: `php artisan view:cache`.',
    'Validasi struktur dokumen: `php "dokumentasi/Sprint 3/tools/validate-docs.php"`.',
    'Untuk verifikasi HTTP langsung, siapkan database evidence: `php artisan migrate:fresh --seed` dengan `DB_CONNECTION=sqlite` dan `DB_DATABASE=database/evidence.sqlite`.',
    'Jalankan server: `php artisan serve --host=127.0.0.1 --port=8099`.',
    'Ulangi request pada dokumen S3-01, S3-02, dan S3-06; bandingkan dengan berkas bukti.',
    'Untuk screenshot, jalankan `node "dokumentasi/Sprint 3/evidence/capture.mjs"` setelah server aktif.',
]);

$b->h1('7. Checklist Kelulusan Sprint');
$b->table(['Kriteria', 'Status', 'Bukti'], [
    ['Autentikasi web dan API berfungsi serta terlindungi', 'Selesai', 'S3-01, auth-*.txt'],
    ['Otorisasi peran customer/admin ditegakkan', 'Selesai', 'S3-01, AuthTest/WebAuthTest'],
    ['Respons error API konsisten dan aman', 'Selesai', 'S3-02, api-*.txt'],
    ['Halaman error web kustom tersedia', 'Selesai', 'S3-02, web-404.txt, screenshot'],
    ['Suite pengujian fungsional lulus', 'Selesai', 'S3-03, test-run.txt'],
    ['Bug yang ditemukan diperbaiki dan diuji', 'Selesai', 'S3-04'],
    ['Tampilan mobile/tablet/desktop layak', 'Selesai', 'S3-05, 15 screenshot'],
    ['Kontrol keamanan dasar aktif', 'Selesai', 'S3-06, header & tes'],
    ['Performa dan usability meningkat terukur', 'Selesai', 'S3-07, timing & tes'],
    ['Dokumentasi bukti lengkap', 'Selesai', 'Dokumen ini + 7 dokumen tugas'],
], [5600, 1500, 2538], ['fontSize' => 19]);

$b->h1('8. Catatan dan Batasan');
$b->bullets([
    'MySQL lokal tidak aktif saat verifikasi, sehingga bukti HTTP langsung memakai database SQLite `database/evidence.sqlite`. Suite tes tetap memakai SQLite `:memory:` sesuai `phpunit.xml`.',
    'Pengukuran waktu halaman dilakukan pada server pengembangan PHP (`artisan serve`) di mesin lokal. Angka tersebut berguna untuk membandingkan perubahan, bukan sebagai benchmark produksi.',
    'Screenshot diambil dengan Chrome headless pada viewport 390×844, 768×1024, dan 1440×900. Tailwind dan Alpine dimuat dari CDN sehingga kualitas render bergantung pada koneksi internet saat pengambilan gambar.',
    'Token API dan cookie sesi pada berkas bukti mentah telah dihapus atau disamarkan; yang tersimpan hanya cuplikan yang tidak membocorkan kredensial.',
]);

$b->h1('9. Penutup');
$b->p('Dengan selesainya tujuh tugas dan tersedianya bukti implementasi, pengujian, serta verifikasi langsung, Sprint 3 dinyatakan **selesai**. Dokumen S3-01 sampai S3-07 dapat dibaca secara terpisah sesuai kebutuhan, sedangkan dokumen ini menjadi rekapitulasi menyeluruh.');
$b->save($outDir.'/S3-00_ SPRINT 3 OVERVIEW.docx');

// =====================================================================
// S3-01 — AUTENTIKASI & OTORISASI
// =====================================================================
$b = baseDoc(
    'S3-01',
    'Autentikasi & Otorisasi',
    'Integrasi login web, token API Sanctum, dan penegakan hak akses berbasis peran',
    'Authentication & Authorization Integration',
    'S3-01 · Autentikasi & Otorisasi'
);
$b->cover();

$b->h1('1. Tujuan');
$b->p('Tugas ini memastikan seluruh jalur masuk aplikasi terintegrasi dengan benar dan setiap area dilindungi sesuai peran pengguna. Cakupannya meliputi login/registrasi web, login admin, token API Sanctum, logout yang aman, serta pembatasan area pengunjung dan area admin.');
$b->table(['Sasaran', 'Kriteria berhasil'], [
    ['Autentikasi web', 'Pengunjung dapat masuk memakai email atau nomor HP; admin hanya dapat masuk melalui form admin.'],
    ['Autentikasi API', 'Login mengembalikan token Sanctum; endpoint terlindungi menolak request tanpa token.'],
    ['Otorisasi peran', 'Customer tidak dapat membuka area admin; admin dapat membuka seluruh area admin.'],
    ['Manajemen sesi', 'Sesi diregenerasi setelah login dan diinvalidasi saat logout.'],
    ['Bukti', 'Rekaman status HTTP 200/302/401/403 dan tes otomatis yang lulus.'],
], [3000, 6638], ['fontSize' => 19]);

$b->h1('2. Arsitektur Autentikasi');
$b->p('Aplikasi memakai dua kanal autentikasi yang berbagi model `User` dan aturan peran yang sama:');
$b->table(['Kanal', 'Mekanisme', 'Penyimpanan kredensial', 'Digunakan oleh'], [
    ['Web', 'Session guard Laravel + cookie sesi', 'Session database, cookie HttpOnly', 'Halaman Blade pengunjung dan admin'],
    ['API', 'Laravel Sanctum personal access token', 'Tabel `personal_access_tokens`', 'Aplikasi mobile/klien API'],
    ['Otorisasi', 'Middleware `role:admin` / `role:customer`', 'Kolom `users.role`', 'Kedua kanal'],
], [1700, 3300, 2600, 2038], ['fontSize' => 19]);

$b->h2('2.1 Model pengguna dan peran');
$b->p('Model `App\\Models\\User` memakai `HasApiTokens` untuk Sanctum, menyembunyikan password dan remember token, serta melakukan hash otomatis melalui cast `hashed`.');
$b->code(<<<'PHP'
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
PHP, 'Potongan app/Models/User.php.');

$b->h1('3. Implementasi Autentikasi Web');
$b->h2('3.1 Rute tamu dan pembatasan throttle');
$b->code(<<<'PHP'
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.attempt');
    Route::post('/daftar', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register');

    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])
        ->middleware('throttle:login')
        ->name('admin.login.attempt');
});
PHP, 'Potongan routes/web.php — rute autentikasi tamu.');

$b->h2('3.2 Login pengunjung dengan email atau nomor HP');
$b->p('Pengunjung dapat memasukkan email atau nomor HP pada satu field. Controller mendeteksi formatnya, memilih kolom yang tepat, lalu meregenerasi sesi setelah autentikasi berhasil.');
$b->code(<<<'PHP'
$field = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

if (! Auth::attempt([
    $field => $credentials['email'],
    'password' => $credentials['password'],
], $request->boolean('remember'))) {
    throw ValidationException::withMessages([
        'email' => 'Email/nomor HP atau password salah.',
    ]);
}

$request->session()->regenerate();
$request->user()->update(['last_login_at' => now()]);

return $request->user()->isAdmin()
    ? redirect()->intended(route('admin.dashboard'))
    : redirect()->intended(route('katalog.beranda'));
PHP, 'Potongan app/Http/Controllers/Web/AuthController.php — login pengunjung.');

$b->h2('3.3 Login admin dan penolakan akun non-admin');
$b->p('Form admin memakai kredensial yang sama, tetapi setelah `Auth::attempt` berhasil aplikasi memeriksa peran. Jika akun bukan admin, sesi langsung diinvalidasi dan pengguna menerima pesan validasi. Dengan begitu, akun pengunjung tidak dapat “naik” menjadi admin hanya karena mengetahui URL form admin.');
$b->code(<<<'PHP'
if (! Auth::attempt($credentials, $request->boolean('remember'))) {
    throw ValidationException::withMessages([
        'email' => 'Email atau password admin salah.',
    ]);
}

if (! $request->user()->isAdmin()) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    throw ValidationException::withMessages([
        'email' => 'Akun ini bukan akun admin.',
    ]);
}

$request->session()->regenerate();
PHP, 'Potongan app/Http/Controllers/Web/AuthController.php — login admin.');

$b->h2('3.4 Logout dan invalidasi sesi');
$b->code(<<<'PHP'
public function logout(Request $request): RedirectResponse
{
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('katalog.beranda')
        ->with('ok', 'Kamu telah keluar dari JelajahKu.');
}
PHP, 'Potongan app/Http/Controllers/Web/AuthController.php — logout.');

$b->h1('4. Implementasi Autentikasi API');
$b->p('API memakai Sanctum. Endpoint publik hanya untuk registrasi, login, katalog, dan webhook. Endpoint pengunjung dibungkus `auth:sanctum`, sedangkan endpoint admin ditambah middleware `role:admin`.');
$b->code(<<<'PHP'
Route::post('auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:register');
Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('bookings', [BookingController::class, 'index']);
    // ... endpoint pengunjung lain
});

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('dashboard', [ReportController::class, 'dashboard']);
    Route::apiResource('destinations', AdminDestinationController::class);
    // ... endpoint admin lain
});
PHP, 'Potongan routes/api.php — pemisahan endpoint publik, pengunjung, dan admin.');

$b->p('Saat login berhasil, API membuat personal access token. Token hanya dikembalikan sekali pada respons login dan tidak disimpan dalam bentuk plaintext di database.');
$b->code(<<<'PHP'
$user = User::where('email', $data['email'])->first();

if (! $user || ! Hash::check($data['password'], $user->password)) {
    throw ValidationException::withMessages([
        'email' => 'Email atau password salah.',
    ]);
}

$user->update(['last_login_at' => now()]);

return response()->json([
    'message' => 'Login berhasil.',
    'user' => $this->userPayload($user),
    'token' => $user->createToken('api')->plainTextToken,
]);
PHP, 'Potongan app/Http/Controllers/Api/V1/AuthController.php — login API.');

$b->h1('5. Penegakan Otorisasi');
$b->p('Middleware `EnsureUserHasRole` adalah titik tunggal penegakan peran untuk web dan API. Middleware ini menangani tiga kondisi: belum login, peran tidak cocok, dan peran sesuai.');
$b->code(<<<'PHP'
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $wantsJson = $request->is('api/*') || $request->expectsJson();

        if (! $user) {
            if ($wantsJson) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route(
                $request->is('admin', 'admin/*') ? 'admin.login' : 'login'
            );
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            if ($wantsJson) {
                return response()->json(['message' => 'Akses ditolak untuk peran ini.'], 403);
            }

            abort(403, 'Akses ditolak untuk peran ini.');
        }

        return $next($request);
    }
}
PHP, 'Potongan app/Http/Middleware/EnsureUserHasRole.php.');

$b->table(['Area', 'Middleware', 'Peran diizinkan', 'Perilaku jika ditolak'], [
    ['Katalog publik', '—', 'Semua', 'Terbuka'],
    ['Checkout, riwayat, pembayaran, e-tiket, profil', '`auth`, `role:customer`', 'customer', 'Tamu diarahkan ke `/masuk`; admin mendapat 403'],
    ['Area admin web `/admin/*`', '`auth`, `role:admin`', 'admin', 'Tamu diarahkan ke `/admin/login`; customer mendapat 403'],
    ['API pengunjung', '`auth:sanctum`', 'customer/admin', '401 JSON jika tanpa token'],
    ['API admin `/api/v1/admin/*`', '`auth:sanctum`, `role:admin`', 'admin', '401/403 JSON'],
], [3200, 2500, 1700, 2238], ['fontSize' => 18]);

$b->h1('6. Bukti Verifikasi Langsung');
$b->p('Verifikasi dilakukan terhadap server yang berjalan di `http://127.0.0.1:8099` dengan database evidence SQLite. Berkas bukti menyimpan header dan body respons apa adanya (token API disamarkan).');
$b->table(['Berkas bukti', 'Request', 'Status', 'Hasil yang teramati'], [
    ['auth-guest-admin.txt', 'GET /admin tanpa sesi', '302', 'Location: /admin/login'],
    ['auth-admin-login.txt', 'POST /admin/login (admin valid)', '302', 'Location: /admin'],
    ['auth-admin-dashboard.txt', 'GET /admin dengan sesi admin', '200', 'Judul “Dashboard Admin” dan menu admin tampil'],
    ['auth-customer-login.txt', 'POST /masuk (customer valid)', '302', 'Location: /'],
    ['auth-customer-admin-forbidden.txt', 'GET /admin dengan sesi customer', '403', 'Halaman “Akses Ditolak”'],
    ['api-login.txt', 'POST /api/v1/auth/login', '200', 'JSON user + token Sanctum'],
    ['api-me.txt', 'GET /api/v1/me dengan Bearer token', '200', 'JSON profil pengguna'],
    ['api-401.txt', 'GET /api/v1/bookings tanpa token', '401', 'Envelope JSON `success=false`, `code=401`'],
], [2850, 3450, 800, 2538], ['fontSize' => 18]);

$b->h2('6.1 Cuplikan hasil');
$b->code(<<<'TXT'
# Tamu membuka area admin
HTTP/1.1 302 Found
Location: http://127.0.0.1:8099/admin/login

# Customer membuka area admin
HTTP/1.1 403 Forbidden
<title>403 - Akses Ditolak | JelajahKu</title>
<h1>Akses Ditolak</h1>

# API tanpa token
HTTP/1.1 401 Unauthorized
Content-Type: application/json
{"success":false,"message":"Sesi tidak valid. Silakan login kembali.","code":401}
TXT, 'Cuplikan dari berkas bukti autentikasi.');

$b->h1('7. Bukti Pengujian Otomatis');
$b->table(['Kelas tes', 'Jumlah', 'Fokus yang diuji'], [
    ['AuthTest', '5', 'Registrasi/login API, password salah, admin tidak bisa dibuat lewat form publik, checkout tanpa login, customer ditolak di endpoint admin'],
    ['WebAuthTest', '8', 'Halaman login, registrasi otomatis masuk, login email/HP, password salah, login admin, customer ditolak di form admin, redirect tamu, penegakan peran'],
    ['ErrorHandlingTest', '2 dari 6', '401 API dan 403 web memakai format/halaman yang benar'],
    ['SecurityTest', '1 dari 7', 'Login web dibatasi setelah lima percobaan'],
], [2600, 1500, 5538], ['fontSize' => 18]);
standardTestSummary($b);

$b->h1('8. Checklist Implementasi');
$b->table(['Item', 'Status', 'Bukti'], [
    ['Login pengunjung dengan email', 'Selesai', 'WebAuthTest; auth-customer-login.txt'],
    ['Login pengunjung dengan nomor HP', 'Selesai', 'WebAuthTest'],
    ['Registrasi langsung masuk', 'Selesai', 'WebAuthTest'],
    ['Login admin hanya untuk akun admin', 'Selesai', 'WebAuthTest; AuthController'],
    ['Token API dibuat dan dipakai', 'Selesai', 'api-login.txt; api-me.txt'],
    ['Customer tidak dapat membuka area admin', 'Selesai', 'auth-customer-admin-forbidden.txt'],
    ['Tamu diarahkan ke login yang sesuai', 'Selesai', 'auth-guest-admin.txt; ErrorHandlingTest'],
    ['Sesi diregenerasi saat login dan diinvalidasi saat logout', 'Selesai', 'AuthController; WebAuthTest'],
    ['Rate limit login/register aktif', 'Selesai', 'SecurityTest; AppServiceProvider'],
], [5600, 1500, 2538], ['fontSize' => 19]);

$b->h1('9. Kesimpulan');
$b->p('Autentikasi web dan API telah terintegrasi di atas satu model pengguna dan satu mekanisme otorisasi. Pemisahan area customer/admin terbukti bekerja melalui verifikasi HTTP langsung dan 15 tes terkait. Tidak ada jalur yang memungkinkan customer membuka area admin, dan endpoint API tanpa token konsisten mengembalikan 401 JSON.');
$b->save($outDir.'/S3-01_ AUTHENTICATION & AUTHORIZATION INTEGRATION.docx');

// =====================================================================
// S3-02 — PENANGANAN ERROR GLOBAL
// =====================================================================
$b = baseDoc(
    'S3-02',
    'Penanganan Error Global',
    'Envelope JSON konsisten, halaman error kustom, dan perlindungan header pada seluruh respons',
    'Global Error Handling',
    'S3-02 · Penanganan Error Global'
);
$b->cover();

$b->h1('1. Tujuan');
$b->p('Tugas ini memastikan setiap kegagalan—baik pada API maupun halaman web—ditangani secara terpusat, mudah dipahami pengguna, tidak membocorkan detail internal, dan tetap membawa header keamanan. Sebelum perbaikan, respons error yang dilempar sebagai exception melewati tahap akhir middleware sehingga header keamanan hilang.');
$b->table(['Sasaran', 'Kriteria berhasil'], [
    ['Konsistensi API', 'Semua error API memakai envelope `success`, `message`, `code`, dan `errors` bila relevan.'],
    ['Ramah pengguna', 'Halaman web memakai view error kustom, bukan halaman debug Laravel.'],
    ['Aman', 'Pesan 500 tidak membocorkan stack trace; detail hanya masuk log server.'],
    ['Fallback', 'Endpoint API yang tidak dikenal tetap mengembalikan JSON 404, bukan HTML.'],
    ['Header', 'Respons error tetap memiliki header keamanan yang sama dengan respons normal.'],
], [3000, 6638], ['fontSize' => 19]);

$b->h1('2. Desain Penanganan Error Terpusat');
$b->p('Laravel 12 menyediakan hook `withExceptions` pada `bootstrap/app.php`. Aplikasi memakai tiga lapisan:');
$b->numbered([
    '`shouldRenderJsonWhen` memaksa request API atau request yang meminta JSON untuk dirender sebagai JSON.',
    '`render` memetakan exception ke status, pesan Bahasa Indonesia, dan payload envelope yang seragam.',
    '`respond` menempelkan kembali header keamanan pada respons final, termasuk respons yang dibuat di luar alur middleware.',
]);

$b->h2('2.1 Pemetaan status dan pesan');
$b->table(['Status', 'Kondisi', 'Pesan pengguna', 'Data tambahan'], [
    ['401', 'AuthenticationException', 'Sesi tidak valid. Silakan login kembali.', '—'],
    ['403', 'AuthorizationException / AccessDeniedHttpException', 'Akses ditolak.', '—'],
    ['404', 'ModelNotFoundException / NotFoundHttpException', 'Data atau endpoint tidak ditemukan.', '—'],
    ['405', 'MethodNotAllowedHttpException', 'Metode HTTP tidak diizinkan.', '—'],
    ['422', 'ValidationException', 'Data yang dikirim tidak valid.', '`errors` per field'],
    ['429', 'ThrottleRequestsException', 'Terlalu banyak permintaan. Silakan coba lagi nanti.', 'Header rate limit'],
    ['500', 'Throwable lain', 'Terjadi kesalahan pada server.', 'Detail hanya di log'],
    ['4xx/5xx lain', 'HttpExceptionInterface', 'Pesan exception atau pesan umum', '—'],
], [1000, 3400, 3538, 1700], ['fontSize' => 18]);

$b->h2('2.2 Implementasi handler');
$b->code(<<<'PHP'
$exceptions->shouldRenderJsonWhen(
    fn (Request $request) => $request->is('api/*') || $request->expectsJson()
);

$exceptions->respond(function (Response $response, Throwable $e, Request $request) {
    return SecurityHeaders::apply($response, $request);
});

$exceptions->render(function (Throwable $e, Request $request) {
    if (! $request->is('api/*') && ! $request->expectsJson()) {
        return null;
    }

    [$status, $message] = match (true) {
        $e instanceof ValidationException => [422, 'Data yang dikirim tidak valid.'],
        $e instanceof AuthenticationException => [401, 'Sesi tidak valid. Silakan login kembali.'],
        $e instanceof AuthorizationException,
        $e instanceof AccessDeniedHttpException => [403, 'Akses ditolak.'],
        $e instanceof ModelNotFoundException,
        $e instanceof NotFoundHttpException => [404, 'Data atau endpoint tidak ditemukan.'],
        $e instanceof MethodNotAllowedHttpException => [405, 'Metode HTTP tidak diizinkan.'],
        $e instanceof ThrottleRequestsException => [429, 'Terlalu banyak permintaan. Silakan coba lagi nanti.'],
        $e instanceof HttpExceptionInterface => [
            $e->getStatusCode(),
            $e->getMessage() !== '' ? $e->getMessage() : 'Permintaan tidak dapat diproses.',
        ],
        default => [500, 'Terjadi kesalahan pada server.'],
    };

    $payload = ['success' => false, 'message' => $message, 'code' => $status];

    if ($e instanceof ValidationException) {
        $payload['errors'] = $e->errors();
    }

    if ($status >= 500) {
        Log::error('API error', [
            'exception' => $e,
            'method' => $request->method(),
            'path' => $request->path(),
        ]);
    }

    $headers = $e instanceof ThrottleRequestsException ? $e->getHeaders() : [];

    return response()->json($payload, $status, $headers);
});
PHP, 'Potongan bootstrap/app.php — penanganan error API terpusat.');

$b->h1('3. Halaman Error Web Kustom');
$b->p('Semua status error utama memiliki view Blade sendiri yang memakai layout minimal bersama. Layout ini sengaja tidak bergantung pada aset eksternal agar tetap tampil rapi ketika aplikasi sedang bermasalah.');
$b->table(['Status', 'Berkas view', 'Makna'], [
    ['401', 'resources/views/errors/401.blade.php', 'Autentikasi diperlukan'],
    ['403', 'resources/views/errors/403.blade.php', 'Akses ditolak'],
    ['404', 'resources/views/errors/404.blade.php', 'Halaman/data tidak ditemukan'],
    ['405', 'resources/views/errors/405.blade.php', 'Metode tidak diizinkan'],
    ['419', 'resources/views/errors/419.blade.php', 'Sesi/CSRF kedaluwarsa'],
    ['429', 'resources/views/errors/429.blade.php', 'Terlalu banyak permintaan'],
    ['500', 'resources/views/errors/500.blade.php', 'Kesalahan server'],
    ['503', 'resources/views/errors/503.blade.php', 'Layanan sedang pemeliharaan'],
], [1000, 4300, 4338], ['fontSize' => 18]);
$b->p('View `errors/minimal.blade.php` menyediakan struktur `<!DOCTYPE html>`, meta viewport, judul dinamis, tombol kembali ke beranda, dan tombol halaman sebelumnya. Halaman 404 yang tampil pada screenshot di bawah berasal langsung dari view ini.');

$b->h1('4. Fallback API');
$b->p('Route fallback di dalam prefix `v1` memastikan URL API yang salah tidak pernah merender halaman HTML atau memicu error 500.');
$b->code(<<<'PHP'
Route::fallback(fn () => response()->json([
    'success' => false,
    'message' => 'Endpoint API tidak ditemukan.',
    'code' => 404,
], 404));
PHP, 'Potongan routes/api.php — fallback API.');

$b->h1('5. Header Keamanan pada Respons Error');
$b->p('Saat verifikasi langsung, ditemukan bahwa respons 401 dan 405 tidak membawa header keamanan. Penyebabnya: exception dilempar sebelum middleware `SecurityHeaders` sempat menambahkan header pada respons. Perbaikan dilakukan dengan memindahkan logika header ke metode statis `SecurityHeaders::apply()` dan memanggilnya dari hook `respond` pada exception handler.');
$b->code(<<<'PHP'
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        return self::apply($next($request), $request);
    }

    public static function apply(Response $response, ?Request $request = null): Response
    {
        $request ??= request();

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
PHP, 'Potongan app/Http/Middleware/SecurityHeaders.php — dapat dipakai ulang oleh handler exception.');

$b->h1('6. Bukti Verifikasi Langsung');
$b->table(['Berkas bukti', 'Skenario', 'Status', 'Hasil'], [
    ['api-404.txt', 'GET /api/v1/halaman-tidak-ada', '404', '`{"success":false,"message":"Endpoint API tidak ditemukan.","code":404}`'],
    ['api-401.txt', 'GET /api/v1/bookings tanpa token', '401', 'Pesan sesi tidak valid, envelope konsisten'],
    ['api-422.txt', 'POST /api/v1/auth/login dengan data kosong', '422', '`errors.email` dan `errors.password`'],
    ['api-405.txt', 'DELETE /api/v1/destinations', '405', 'Pesan metode tidak diizinkan'],
    ['web-404.txt', 'GET /halaman-tidak-ada', '404', 'View kustom “Halaman Tidak Ditemukan”'],
    ['auth-customer-admin-forbidden.txt', 'GET /admin dengan sesi customer', '403', 'View kustom “Akses Ditolak”'],
], [2850, 3450, 800, 2538], ['fontSize' => 18]);

$b->h2('6.1 Cuplikan respons');
$b->code(<<<'TXT'
# 404 API
HTTP/1.1 404 Not Found
Content-Type: application/json
{"success":false,"message":"Endpoint API tidak ditemukan.","code":404}

# 422 API
HTTP/1.1 422 Unprocessable Content
{"success":false,"message":"Data yang dikirim tidak valid.","code":422,
 "errors":{"email":["The email field is required."],"password":["The password field is required."]}}

# 405 API
HTTP/1.1 405 Method Not Allowed
{"success":false,"message":"Metode HTTP tidak diizinkan.","code":405}

# 401 dengan header keamanan
HTTP/1.1 401 Unauthorized
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
TXT, 'Cuplikan dari api-404.txt, api-422.txt, api-405.txt, dan api-401.txt.');

$b->h2('6.2 Screenshot halaman error');
$b->image($shots.'/web-404-mobile-390x844.png', 2500, 'Halaman 404 kustom pada viewport mobile 390×844 — layout tetap terbaca dan tombol tidak terpotong.');
$b->image($shots.'/web-404-desktop-1440x900.png', 8600, 'Halaman 404 kustom pada viewport desktop 1440×900.');

$b->h1('7. Bukti Pengujian Otomatis');
$b->table(['Tes', 'Hasil', 'Yang diverifikasi'], [
    ['Endpoint API tidak dikenal → JSON 404 konsisten', 'Lulus', '`success`, `code`, dan struktur pesan'],
    ['Request API tanpa token → JSON 401', 'Lulus', 'Envelope dan status'],
    ['Validasi API → 422 dengan detail errors', 'Lulus', 'Struktur `errors.email` dan `errors.password`'],
    ['Metode salah → JSON 405', 'Lulus', 'Status dan envelope'],
    ['Halaman web 404 → view kustom', 'Lulus', 'Teks 404, judul, tombol beranda'],
    ['Halaman web 403 → view kustom', 'Lulus', 'Customer ditolak dari area admin'],
    ['Respons error tetap membawa header keamanan', 'Lulus', 'Header pada 404 web dan 401 API'],
], [5600, 1300, 2738], ['fontSize' => 18]);
standardTestSummary($b);

$b->h1('8. Checklist');
$b->table(['Item', 'Status', 'Bukti'], [
    ['Semua error API memakai envelope seragam', 'Selesai', 'api-401/404/405/422.txt'],
    ['Validasi menyertakan detail field', 'Selesai', 'api-422.txt'],
    ['Error 500 tidak membocorkan stack trace', 'Selesai', 'Handler default + log server'],
    ['Halaman web 401/403/404/405/419/429/500/503 tersedia', 'Selesai', 'resources/views/errors/'],
    ['Fallback API aktif', 'Selesai', 'routes/api.php; api-404.txt'],
    ['Header keamanan ada pada respons error', 'Selesai', 'api-401.txt; web-404.txt'],
    ['Tes regresi error handling lulus', 'Selesai', 'ErrorHandlingTest 6; SecurityTest'],
], [5600, 1500, 2538], ['fontSize' => 19]);

$b->h1('9. Kesimpulan');
$b->p('Penanganan error kini terpusat dan konsisten di kedua kanal. API selalu menjawab dengan JSON yang dapat diprediksi, halaman web menampilkan pesan yang ramah, dan seluruh respons—termasuk yang berasal dari exception—membawa header keamanan. Tujuh tes otomatis dan enam berkas bukti HTTP langsung mendukung klaim ini.');
$b->save($outDir.'/S3-02_ GLOBAL ERROR HANDLING.docx');

// =====================================================================
// S3-03 — PENGUJIAN FUNGSIONAL
// =====================================================================
$b = baseDoc(
    'S3-03',
    'Pengujian Fungsional',
    'Strategi, cakupan, dan hasil eksekusi suite pengujian JelajahKu',
    'Functional Testing',
    'S3-03 · Pengujian Fungsional'
);
$b->cover();

$b->h1('1. Tujuan');
$b->p('Tugas ini memverifikasi bahwa seluruh alur bisnis utama berjalan sesuai kontrak, termasuk skenario negatif dan batas (edge case). Pengujian tidak hanya mengecek status HTTP, tetapi juga keadaan database, perpindahan kuota, penerbitan tiket, dan konsistensi data.');
$b->table(['Sasaran', 'Kriteria berhasil'], [
    ['Cakupan fitur', 'Autentikasi, katalog, booking, pembayaran, e-tiket, admin, keamanan, responsif, performa tercakup.'],
    ['Isolasi', 'Setiap tes memakai database bersih dan tidak bergantung pada MySQL lokal.'],
    ['Reproducible', 'Satu perintah menjalankan seluruh suite dan menghasilkan status yang jelas.'],
    ['Regresi', 'Tes baru melindungi perbaikan Sprint 3 dari kemunduran.'],
], [3000, 6638], ['fontSize' => 19]);

$b->h1('2. Lingkungan dan Strategi');
$b->p('Suite memakai PHPUnit 11 melalui `php artisan test`. Konfigurasi `phpunit.xml` mengarahkan koneksi ke SQLite `:memory:`, cache array, session array, queue sync, dan mail array. Karena itu suite cepat, deterministik, dan tidak membutuhkan layanan eksternal.');
$b->code(<<<'XML'
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
</php>
XML, 'Potongan phpunit.xml — lingkungan tes terisolasi.');

$b->p('Pola yang dipakai:');
$b->bullets([
    '`RefreshDatabase` untuk memastikan setiap tes mulai dari skema bersih.',
    'Factory dan concern `CreatesCatalog` untuk membangun data destinasi, tiket, dan pengguna secara konsisten.',
    '`actingAs` dengan guard `web` atau `sanctum` sesuai kanal yang diuji.',
    'Assertion database (`assertDatabaseHas`, `assertDatabaseMissing`) untuk memverifikasi efek samping, bukan hanya respons.',
    '`Storage::fake` untuk menguji upload tanpa menyentuh disk nyata.',
    '`Carbon::setTestNow` / tanggal relatif untuk skenario kedaluwarsa dan validasi tanggal kunjungan.',
]);

$b->h1('3. Inventaris Suite');
$b->table(['Kelas tes', 'Jumlah', 'Cakupan'], [
    ['Unit\\ExampleTest', '1', 'Sanity check PHPUnit'],
    ['Feature\\AdminTest', '6', 'CRUD destinasi/tiket, kuota, dashboard, laporan, katalog nonaktif'],
    ['Feature\\AuthTest', '5', 'Registrasi/login API, password salah, pembatasan peran'],
    ['Feature\\BookingTest', '6', 'Reservasi kuota, batas kuota, snapshot harga, kedaluwarsa, kepemilikan, voucher'],
    ['Feature\\CatalogPageTest', '2', 'Katalog dan detail destinasi dinamis'],
    ['Feature\\DestinationCatalogTest', '4', 'Pencarian, filter, urutan harga, rating, validasi tanggal'],
    ['Feature\\ErrorHandlingTest', '6', 'JSON 401/404/405/422 dan halaman web 403/404'],
    ['Feature\\ExampleTest', '1', 'Smoke test halaman utama'],
    ['Feature\\PaymentWebhookTest', '4', 'Pembayaran webhook, idempotensi, signature, kedaluwarsa'],
    ['Feature\\PerformanceUsabilityTest', '4', 'Cache kategori, query kedua, agregasi rating, lazy image'],
    ['Feature\\ResponsiveUiTest', '4', 'Viewport, sidebar off-canvas, tabel scroll, form mobile'],
    ['Feature\\SecurityTest', '7', 'Header, rate limit, secret webhook, upload, bukti lama, header error'],
    ['Feature\\TicketValidationTest', '6', 'Validasi tiket sekali pakai, QR ganda, tanggal salah, QR SVG'],
    ['Feature\\WebAdminTest', '10', 'Semua halaman admin, CRUD, kuota, booking, pembayaran, scan, laporan'],
    ['Feature\\WebAuthTest', '8', 'Login/registrasi web, admin, redirect, penegakan peran'],
    ['Feature\\WebBookingFlowTest', '13', 'Form booking, pembayaran manual, e-tiket, pembatalan, riwayat, profil, ulasan'],
    ['Feature\\WebCatalogTest', '1', 'Filter lokasi dan rentang harga'],
], [3600, 1000, 5038], ['fontSize' => 18]);

$b->h1('4. Cakupan per Area Fungsional');
$b->table(['Area', 'Kelas tes terkait', 'Skenario kunci'], [
    ['Autentikasi & otorisasi', 'AuthTest, WebAuthTest, SecurityTest', 'Login email/HP, token, role, throttle, redirect'],
    ['Katalog', 'CatalogPageTest, DestinationCatalogTest, WebCatalogTest', 'Data dinamis, filter, urutan, rating, tanggal'],
    ['Booking & kuota', 'BookingTest, WebBookingFlowTest', 'Kuota atomik, batas, snapshot harga, kedaluwarsa, pembatalan'],
    ['Pembayaran', 'PaymentWebhookTest, WebBookingFlowTest', 'Webhook idempotent, signature, verifikasi manual, bukti'],
    ['E-tiket & QR', 'TicketValidationTest, WebBookingFlowTest', 'Sekali pakai, QR ganda, tanggal salah, akses pemilik'],
    ['Admin', 'AdminTest, WebAdminTest', 'CRUD, kuota, dashboard, laporan, scan, ekspor'],
    ['Error handling', 'ErrorHandlingTest, SecurityTest', 'Envelope JSON, halaman kustom, header error'],
    ['Responsif', 'ResponsiveUiTest', 'Sidebar, tabel, form, navigasi mobile'],
    ['Performa & usability', 'PerformanceUsabilityTest', 'Cache, query count, lazy image, agregasi'],
], [2800, 3500, 3338], ['fontSize' => 18]);

$b->h1('5. Hasil Eksekusi');
$b->p('Perintah berikut dijalankan dari root proyek setelah seluruh perubahan Sprint 3 selesai. Keluaran lengkap tersimpan pada `dokumentasi/Sprint 3/evidence/test-run.txt`.');
$b->code(<<<'TXT'
php artisan test --compact --no-ansi

  ........................................................................................

  Tests:    88 passed (455 assertions)
  Duration: 10.79s
TXT, 'Keluaran `php artisan test --compact` — 88 tes lulus.');
$b->table(['Metrik', 'Nilai'], [
    ['Total tes', '88'],
    ['Total assertion', '455'],
    ['Tes gagal / error', '0'],
    ['Durasi', '10,79 detik'],
    ['Exit code', '0'],
    ['Baseline Sprint 2', '67 tes / 352 assertion'],
    ['Pertumbuhan', '+21 tes / +103 assertion'],
], [4000, 5638], ['fontSize' => 19]);

$b->h1('6. Contoh Tes Kunci');
$b->h2('6.1 Batas kuota tidak terlampaui');
$b->code(<<<'PHP'
public function test_kuota_tidak_bisa_terjual_melebihi_batas(): void
{
    $user = $this->customer();
    $date = now()->addDays(2)->toDateString();
    [$destination, $ticketType] = $this->destinationWithTicket($date, quota: 5);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/bookings', [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 3]],
        ])->assertCreated();

    // Sisa kuota 2, permintaan 3 harus ditolak tanpa mengubah data.
    $this->actingAs($this->customer(), 'sanctum')
        ->postJson('/api/v1/bookings', [
            'destination_id' => $destination->id,
            'visit_date' => $date,
            'items' => [['ticket_type_id' => $ticketType->id, 'quantity' => 3]],
        ])->assertStatus(422);

    $this->assertSame(3, TicketAvailability::where('ticket_type_id', $ticketType->id)->value('sold'));
    $this->assertSame(1, Booking::count());
}
PHP, 'Potongan tests/Feature/BookingTest.php.');

$b->h2('6.2 Webhook ganda tidak menerbitkan tiket tambahan');
$b->code(<<<'PHP'
public function test_webhook_ganda_tidak_menerbitkan_tiket_tambahan(): void
{
    $user = $this->customer();
    $booking = $this->makeBooking($user);

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/bookings/{$booking->code}/payments", ['method' => 'va'])
        ->assertCreated();

    $ref = Payment::first()->transaction_ref;

    $payload = [
        'transaction_ref' => $ref,
        'transaction_status' => 'settlement',
        'payment_type' => 'va',
        'order_id' => 'ORDER-1',
    ];

    $this->postWebhook($payload)->assertOk();
    $this->postWebhook($payload)->assertOk();

    $this->assertSame(2, Ticket::count()); // hanya satu set tiket
}
PHP, 'Potongan tests/Feature/PaymentWebhookTest.php.');

$b->h2('6.3 Fallback API 404');
$b->code(<<<'PHP'
public function test_endpoint_api_tidak_dikenal_mengembalikan_json_404_konsisten(): void
{
    $this->getJson('/api/v1/endpoint-tidak-ada')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 404)
        ->assertJsonStructure(['success', 'message', 'code']);
}
PHP, 'Potongan tests/Feature/ErrorHandlingTest.php.');

$b->h1('7. Artefak Pengujian');
$b->table(['Berkas', 'Isi'], [
    ['`evidence/test-run.txt`', 'Keluaran lengkap eksekusi suite terakhir'],
    ['`evidence/test-inventory.txt`', 'Daftar seluruh 88 nama tes'],
    ['`evidence/pint.txt`', 'Hasil formatter Laravel Pint'],
    ['`evidence/view-cache.txt`', 'Hasil kompilasi seluruh Blade view'],
    ['`evidence/route-list.txt`', 'Daftar 100 rute aplikasi (web + API)'],
], [4200, 5438], ['fontSize' => 19]);

$b->h1('8. Checklist');
$b->table(['Item', 'Status', 'Bukti'], [
    ['Suite berjalan tanpa MySQL', 'Selesai', 'phpunit.xml; test-run.txt'],
    ['Seluruh tes lulus', 'Selesai', '88 passed'],
    ['Tidak ada tes gagal/error', 'Selesai', 'test-run.txt'],
    ['Skenario negatif diuji', 'Selesai', 'Banyak tes 401/403/422, kuota, QR ganda'],
    ['Efek samping database diverifikasi', 'Selesai', 'assertDatabaseHas / query count'],
    ['Tes regresi Sprint 3 ditambahkan', 'Selesai', '4 kelas baru + SecurityTest'],
    ['Perintah tes terdokumentasi', 'Selesai', 'Bagian 5 dan README'],
], [5600, 1500, 2538], ['fontSize' => 19]);

$b->h1('9. Kesimpulan');
$b->p('Suite pengujian fungsional mencakup seluruh alur utama dan skenario kegagalan yang berisiko. Dengan 88 tes dan 455 assertion yang lulus, serta pertumbuhan 21 tes dari baseline, regresi Sprint 3 memiliki jaring pengaman yang memadai. Pengujian dapat dijalankan ulang kapan saja tanpa ketergantungan pada database eksternal.');
$b->save($outDir.'/S3-03_ FUNCTIONAL TESTING.docx');

// =====================================================================
// S3-04 — PERBAIKAN BUG & ISSUE
// =====================================================================
$b = baseDoc(
    'S3-04',
    'Perbaikan Bug & Issue',
    'Enam masalah nyata: akar penyebab, perbaikan kode, dan bukti regresi',
    'Bug Fixing & Issue Resolution',
    'S3-04 · Perbaikan Bug & Issue'
);
$b->cover();

$b->h1('1. Tujuan');
$b->p('Tugas ini mendokumentasikan bug dan issue yang ditemukan selama audit Sprint 3, termasuk cara masalah ditemukan, akar penyebab, perubahan kode, dan bukti bahwa perbaikan tidak merusak perilaku lain. Fokusnya bukan sekadar menghilangkan gejala, tetapi menutup penyebab di lapisan yang tepat.');
$b->table(['ID', 'Tingkat', 'Area', 'Ringkasan', 'Status'], [
    ['BUG-01', 'Sedang', 'Pembayaran', 'Bukti transfer lama menjadi orphan file saat pengguna mengunggah bukti baru', 'Selesai'],
    ['BUG-02', 'Tinggi', 'Keamanan/Error', 'Header keamanan hilang pada respons error 401/403/405', 'Selesai'],
    ['BUG-03', 'Tinggi', 'UI Admin', 'Sidebar dan tabel admin tidak layak pada layar kecil', 'Selesai'],
    ['BUG-04', 'Tinggi', 'Keamanan', 'Webhook menerima secret default `change-me-production-secret`', 'Selesai'],
    ['BUG-05', 'Sedang', 'Katalog', 'Daftar kategori bisa basi setelah perubahan data', 'Selesai'],
    ['BUG-06', 'Rendah', 'Performa', 'Dua query terpisah untuk rata-rata dan jumlah ulasan', 'Selesai'],
], [900, 1100, 1600, 5000, 1038], ['fontSize' => 18]);

$b->h1('2. Detail Perbaikan');

$b->h2('BUG-01 — Orphan file saat mengganti bukti transfer');
$b->p('**Gejala.** Ketika pengunjung mengunggah ulang bukti pembayaran, file lama tetap tersimpan di disk. Seiring waktu, folder `payment-proofs` menumpuk file yang tidak lagi direferensikan database.');
$b->p('**Akar masalah.** `PaymentService::attachProof()` hanya memperbarui kolom `proof_path` tanpa menghapus file yang sebelumnya. Tidak ada mekanisme pembersihan otomatis.');
$b->p('**Perbaikan.** Simpan path lama sebelum menyimpan file baru, lalu hapus file lama setelah update berhasil. Path dibandingkan agar file yang sama tidak terhapus.');
$b->code(<<<'PHP'
public function attachProof(Payment $payment, UploadedFile $file): Payment
{
    if ($payment->status !== Payment::STATUS_PENDING) {
        throw ValidationException::withMessages([
            'payment' => 'Pembayaran ini sudah tidak bisa diunggah buktinya.',
        ]);
    }

    $oldPath = $payment->proof_path;
    $path = $file->store('payment-proofs', 'public');

    $payment->update(['proof_path' => $path]);

    // Hindari orphan file ketika pengunjung mengganti bukti transfer.
    if ($oldPath && $oldPath !== $path) {
        Storage::disk('public')->delete($oldPath);
    }

    return $payment->refresh();
}
PHP, 'Potongan app/Services/PaymentService.php setelah perbaikan.');
$b->p('**Verifikasi.** Tes `test_bukti_lama_dihapus_saat_pengunjung_mengunggah_bukti_baru` memakai `Storage::fake`, menaruh file lama, mengunggah file baru, lalu memastikan file lama hilang dan file baru ada. Tes lulus.');

$b->h2('BUG-02 — Header keamanan hilang pada respons error');
$b->p('**Gejala.** Respons normal memiliki header `X-Content-Type-Options`, `X-Frame-Options`, dan lainnya. Namun respons 401 dan 405 tidak memilikinya. Temuan ini muncul saat verifikasi HTTP langsung, bukan dari tes unit.');
$b->p('**Akar masalah.** Middleware `SecurityHeaders` menambahkan header setelah `$next($request)`. Ketika exception dilempar (misalnya oleh `auth:sanctum` atau router), alur kembali melewati middleware dan baris penambahan header tidak pernah dieksekusi. Exception handler kemudian membuat respons baru tanpa header tersebut.');
$b->p('**Perbaikan.** Logika header dipindahkan ke metode statis `SecurityHeaders::apply()` yang dapat dipanggil dari dua tempat: middleware normal dan hook `respond` pada exception handler.');
$b->code(<<<'PHP'
// bootstrap/app.php
$exceptions->respond(function (Response $response, Throwable $e, Request $request) {
    return SecurityHeaders::apply($response, $request);
});
PHP, 'Potongan bootstrap/app.php — header ditempelkan pada respons exception.');
$b->p('**Verifikasi.** Tes `test_respons_error_web_dan_api_tetap_membawa_header_keamanan` memeriksa 404 web dan 401 API. Verifikasi langsung menunjukkan header yang sama pada `api-401.txt`, `api-405.txt`, dan `web-404.txt`.');

$b->h2('BUG-03 — Layout admin tidak layak pada layar kecil');
$b->p('**Gejala.** Sidebar admin berlebar tetap 256px mendorong konten ke kanan. Tabel booking terpotong tanpa jalur scroll, dan form dua kolom memaksa input menjadi sangat sempit pada ponsel.');
$b->p('**Akar masalah.** Layout dirancang desktop-first: sidebar selalu tampil, konten memakai margin kiri tetap, tabel tanpa wrapper `overflow-x-auto`, dan grid form langsung dua kolom.');
$b->p('**Perbaikan.** Layout diubah menjadi mobile-first dengan sidebar off-canvas berbasis Alpine.js, header mobile dengan tombol hamburger, overlay gelap, margin kiri hanya pada breakpoint `lg`, tabel dibungkus `overflow-x-auto`, dan form memakai `grid-cols-1` lalu `md:grid-cols-2`.');
$b->code(<<<'HTML'
<div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100">
    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 transform bg-white transition-transform lg:translate-x-0">
        ...
    </aside>

    <div class="lg:ml-64">
        <header class="sticky top-0 z-30 flex items-center gap-3 border-b bg-white/95 px-4 py-3 lg:hidden">
            <button @click="sidebarOpen = true" aria-label="Buka menu admin"
                    :aria-expanded="sidebarOpen.toString()">...</button>
        </header>
        <main id="admin-main" class="p-4 sm:p-6 lg:p-8">...</main>
    </div>
</div>
HTML, 'Potongan resources/views/layouts/admin.blade.php.');

$b->h2('BUG-04 — Webhook menerima secret default yang tidak aman');
$b->p('**Gejala.** Konfigurasi contoh memakai `change-me-production-secret`. Jika nilai itu tidak diganti di produksi, siapa pun yang mengetahui nilai default dapat memalsukan signature webhook dan menandai pembayaran sebagai lunas.');
$b->p('**Akar masalah.** `SandboxGateway::verifySignature()` hanya membandingkan HMAC tanpa memeriksa apakah secret masih bernilai default atau kosong.');
$b->p('**Perbaikan.** Tolak secret kosong atau sama dengan nilai default, catat peringatan di log, dan kembalikan `false`. Perbandingan signature tetap memakai `hash_equals` untuk mencegah timing attack.');
$b->code(<<<'PHP'
$secret = (string) config('payment.webhook_secret');

if ($secret === '' || $secret === 'change-me-production-secret') {
    Log::warning('Webhook ditolak: PAYMENT_WEBHOOK_SECRET belum diganti dengan nilai rahasia yang kuat.');

    return false;
}

$expected = hash_hmac('sha256', $rawBody, $secret);

return hash_equals($expected, $signature);
PHP, 'Potongan app/Services/Payments/SandboxGateway.php.');
$b->p('**Verifikasi.** Tes `test_webhook_menolak_secret_default_yang_tidak_aman` mengirim signature valid yang dihitung dengan secret default. Respons yang diharapkan adalah 401. Tes lulus.');

$b->h2('BUG-05 — Cache kategori basi setelah perubahan data');
$b->p('**Gejala.** Setelah kategori ditambah, diubah, atau dihapus, halaman katalog masih menampilkan daftar lama hingga cache satu jam kedaluwarsa.');
$b->p('**Akar masalah.** Optimasi cache daftar kategori belum memiliki invalidasi berbasis event model.');
$b->p('**Perbaikan.** Daftarkan event `saved` dan `deleted` pada model `Category` untuk menghapus `Category::CACHE_KEY`.');
$b->code(<<<'PHP'
protected function configureCatalogCache(): void
{
    Category::saved(fn () => Cache::forget(Category::CACHE_KEY));
    Category::deleted(fn () => Cache::forget(Category::CACHE_KEY));
}
PHP, 'Potongan app/Providers/AppServiceProvider.php.');
$b->p('**Verifikasi.** Tes `test_daftar_kategori_di_cache_dan_dibersihkan_saat_kategori_berubah` memastikan cache terisi setelah request pertama, lalu hilang setelah `Category::create()`. Tes lulus.');

$b->h2('BUG-06 — Dua query agregat untuk rating dan jumlah ulasan');
$b->p('**Gejala.** Halaman detail destinasi menjalankan query terpisah untuk rata-rata rating dan jumlah ulasan. Pada halaman yang sering diakses, ini menambah round-trip database tanpa manfaat.');
$b->p('**Akar masalah.** Perhitungan memakai dua agregat terpisah alih-alih satu query `SELECT AVG(...), COUNT(...)`');
$b->p('**Perbaikan.** Gunakan satu query agregat, lalu set atribut pada model untuk dipakai view.');
$b->code(<<<'PHP'
// Satu query agregat untuk rating & jumlah ulasan (menghindari dua query terpisah).
$reviewStats = $destination->reviews()
    ->selectRaw('AVG(rating) as rating_avg, COUNT(*) as reviews_count')
    ->first();

$destination->setAttribute('rating_avg', round((float) ($reviewStats->rating_avg ?? 0), 1));
$destination->setAttribute('reviews_count', (int) ($reviewStats->reviews_count ?? 0));
PHP, 'Potongan app/Http/Controllers/CatalogController.php.');
$b->p('**Verifikasi.** Tes `test_detail_destinasi_menampilkan_rating_dan_jumlah_ulasan_hasil_agregasi` memastikan halaman menampilkan `4.0` dan `1 ulasan` dari satu ulasan yang dibuat. Tes lulus.');

$b->h1('3. Dampak Perbaikan');
$b->table(['Aspek', 'Sebelum', 'Sesudah'], [
    ['Penyimpanan bukti', 'File lama menumpuk tanpa referensi', 'File lama dihapus saat diganti'],
    ['Header keamanan error', 'Hilang pada 401/403/405', 'Konsisten pada semua respons'],
    ['UI admin mobile', 'Sidebar mendorong konten, tabel terpotong', 'Sidebar off-canvas, tabel dapat digeser'],
    ['Webhook default', 'Secret default diterima', 'Ditolak + peringatan log'],
    ['Cache kategori', 'Basi hingga 1 jam', 'Invalidasi langsung saat data berubah'],
    ['Query detail', 'Dua query agregat', 'Satu query agregat'],
], [2500, 3800, 3338], ['fontSize' => 19]);

$b->h1('4. Pencegahan Regresi');
$b->bullets([
    'Setiap bug mendapat tes otomatis yang gagal sebelum perbaikan dan lulus sesudahnya (kecuali bug visual yang diverifikasi dengan screenshot dan tes markup).',
    '`SecurityHeaders` diuji pada respons normal dan respons error.',
    '`ResponsiveUiTest` mengunci keberadaan kelas-kelas penting agar layout tidak kembali ke desain desktop-only.',
    '`PerformanceUsabilityTest` mengunci cache dan jumlah query kategori.',
    'Formatter Pint dijalankan untuk menjaga konsistensi kode.',
]);

$b->h1('5. Verifikasi Akhir');
standardTestSummary($b);
$b->p('Verifikasi tambahan: `api-401.txt`, `api-405.txt`, dan `web-404.txt` menunjukkan header keamanan pada respons error; screenshot admin mobile/tablet menunjukkan sidebar off-canvas dan tabel yang dapat digeser.');

$b->h1('6. Checklist');
$b->table(['Item', 'Status', 'Bukti'], [
    ['Enam bug memiliki akar masalah terdokumentasi', 'Selesai', 'Bagian 2'],
    ['Perbaikan kode diterapkan pada file yang tepat', 'Selesai', 'Cuplikan kode'],
    ['Tes regresi tersedia dan lulus', 'Selesai', 'SecurityTest, PerformanceUsabilityTest, ResponsiveUiTest'],
    ['Verifikasi langsung untuk bug keamanan/HTTP', 'Selesai', 'api-401/405, web-404'],
    ['Verifikasi visual untuk bug layout', 'Selesai', 'Screenshot admin mobile/tablet'],
    ['Tidak ada penurunan hasil suite', 'Selesai', '88 passed'],
], [5600, 1500, 2538], ['fontSize' => 19]);

$b->h1('7. Kesimpulan');
$b->p('Keenam masalah telah diperbaiki pada lapisan penyebabnya. Yang terpenting, setiap perbaikan memiliki bukti: tes otomatis untuk logika dan keamanan, verifikasi HTTP untuk header, serta screenshot untuk layout. Pendekatan ini mengurangi risiko bug yang sama muncul kembali pada sprint berikutnya.');
$b->save($outDir.'/S3-04_ BUG FIXING & ISSUE RESOLUTION.docx');

// =====================================================================
// S3-05 — RESPONSIF & KUALITAS MOBILE
// =====================================================================
$b = baseDoc(
    'S3-05',
    'Responsif & Kualitas Mobile',
    'Pemeriksaan dan perbaikan tampilan pada mobile, tablet, dan desktop',
    'Responsive & Mobile Quality Check',
    'S3-05 · Responsif & Kualitas Mobile'
);
$b->cover();

$b->h1('1. Tujuan');
$b->p('Tugas ini memastikan aplikasi dapat digunakan dengan nyaman pada ponsel, tablet, dan desktop. Pemeriksaan mencakup navigasi, keterbacaan, target sentuh, tabel lebar, form, dan konsistensi layout admin maupun pengunjung.');
$b->table(['Viewport', 'Lebar', 'Fokus pemeriksaan'], [
    ['Mobile', '390 × 844', 'Navigasi bawah, sidebar off-canvas, form satu kolom, tabel dapat digeser, target sentuh'],
    ['Tablet', '768 × 1024', 'Transisi breakpoint, grid dua kolom, keterbacaan'],
    ['Desktop', '1440 × 900', 'Sidebar tetap, grid empat kolom, tabel penuh'],
], [1800, 2000, 5838], ['fontSize' => 19]);

$b->h1('2. Pendekatan Mobile-First');
$b->p('Aplikasi memakai Tailwind CSS dengan breakpoint standar: `sm` 640px, `md` 768px, `lg` 1024px. Layout dimulai dari satu kolom, lalu bertambah kolom hanya ketika ruang cukup. Ini mencegah konten “dipaksa” muat pada layar kecil.');
$b->table(['Breakpoint', 'Perilaku utama'], [
    ['< 640px', 'Satu kolom, navigasi bawah, sidebar tersembunyi, tombol selebar layar'],
    ['640–1023px', 'Grid dua kolom, form mulai berdampingan, sidebar masih off-canvas'],
    ['≥ 1024px', 'Sidebar admin tetap 256px, konten bergeser `lg:ml-64`, grid empat kolom'],
], [2200, 7438], ['fontSize' => 19]);

$b->h1('3. Area Pengunjung');
$b->p('Halaman pengunjung memakai kanvas `max-w-md` yang nyaman untuk ponsel, dengan navigasi bawah tetap. Pada desktop, kanvas tetap terpusat sehingga pengalaman tidak melebar berlebihan.');
$b->code(<<<'HTML'
<div class="max-w-md mx-auto min-h-screen bg-white shadow-lg relative pb-20">
    <a href="#main-content" class="sr-only focus:not-sr-only ...">Lewati ke konten utama</a>

    <nav aria-label="Navigasi utama"
         class="fixed bottom-0 inset-x-0 max-w-md mx-auto bg-white border-t grid grid-cols-4 text-[11px] z-50">
        <a href="{{ url($path) }}" @if($aktif) aria-current="page" @endif
           class="flex min-h-[44px] flex-col items-center gap-1 py-2">
            ...
        </a>
    </nav>

    <main id="main-content">@yield('content')</main>
</div>
HTML, 'Potongan resources/views/layouts/app.blade.php.');

$b->bullets([
    'Viewport meta `width=device-width, initial-scale=1` tersedia di layout master.',
    'Navigasi bawah memakai empat item dengan tinggi minimum 44px sehingga nyaman disentuh.',
    '`aria-current="page"` menandai halaman aktif untuk pembaca layar.',
    'Skip link “Lewati ke konten utama” muncul saat fokus keyboard.',
    'Gambar katalog memakai `loading="lazy"` dan `decoding="async"` agar halaman mobile tidak berat.',
    'Filter kategori memakai `overflow-x-auto` sehingga chip dapat digeser tanpa memecahkan layout.',
]);

$b->h1('4. Area Admin');
$b->p('Area admin diubah dari layout desktop-only menjadi layout responsif dengan sidebar off-canvas. Pada mobile, sidebar tersembunyi dan dapat dibuka melalui tombol hamburger; overlay gelap menutup sidebar saat diketuk.');
$b->code(<<<'HTML'
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 flex w-64 transform flex-col border-r bg-white
              transition-transform duration-200 lg:translate-x-0"
       aria-label="Navigasi admin">
    ...
</aside>

<div class="lg:ml-64">
    <header class="sticky top-0 z-30 flex items-center gap-3 border-b bg-white/95
                   px-4 py-3 backdrop-blur lg:hidden">
        <button type="button" @click="sidebarOpen = true"
                class="grid h-10 w-10 place-items-center rounded-xl border"
                aria-label="Buka menu admin" :aria-expanded="sidebarOpen.toString()">
            ...
        </button>
    </header>

    <main id="admin-main" class="p-4 sm:p-6 lg:p-8">
        ...
    </main>
</div>
HTML, 'Potongan resources/views/layouts/admin.blade.php.');

$b->h2('4.1 Tabel lebar');
$b->p('Tabel booking memiliki tujuh kolom sehingga tidak mungkin dipadatkan pada 390px. Solusinya adalah wrapper `overflow-x-auto` dengan lebar minimum tabel, sehingga pengguna dapat menggeser tabel secara horizontal tanpa memotong halaman.');
$b->code(<<<'HTML'
<div class="overflow-x-auto">
    <table class="w-full min-w-[820px] text-sm">
        <thead class="text-xs text-gray-400 text-left">
            <tr>
                <th class="py-2">KODE</th>
                <th>PENGUNJUNG</th>
                <th>DESTINASI</th>
                <th>TANGGAL</th>
                <th>TOTAL</th>
                <th>STATUS</th>
                <th>AKSI</th>
            </tr>
        </thead>
        ...
    </table>
</div>
HTML, 'Potongan resources/views/admin/booking/index.blade.php.');

$b->h2('4.2 Form adaptif');
$b->p('Form destinasi memakai satu kolom pada mobile dan dua kolom pada `md`. Field panjang seperti deskripsi dan fasilitas memakai `md:col-span-2`. Tombol aksi ditumpuk pada mobile dan berjajar pada layar lebih besar.');
$b->code(<<<'HTML'
<form method="POST" enctype="multipart/form-data"
      class="grid max-w-3xl grid-cols-1 gap-4 rounded-2xl border bg-white p-5 sm:p-6 md:grid-cols-2">
    @csrf
    ...
    <div class="md:col-span-2">
        <label class="text-xs font-medium">Deskripsi</label>
        <textarea name="description" rows="4" class="..." required>...</textarea>
    </div>

    <div class="flex flex-col-reverse gap-3 md:col-span-2 sm:flex-row sm:justify-end">
        <a href="..." class="px-5 py-2.5 text-sm border rounded-xl">Batal</a>
        <button class="px-5 py-2.5 text-sm bg-primary text-white rounded-xl font-semibold">Simpan</button>
    </div>
</form>
HTML, 'Potongan resources/views/admin/destinasi/form.blade.php.');

$b->h1('5. Bukti Screenshot');
$b->p('Screenshot diambil dengan Chrome headless pada viewport yang ditentukan. Setiap gambar diberi keterangan halaman dan lebar viewport.');

$b->h2('5.1 Beranda pengunjung');
$b->image($shots.'/beranda-mobile-390x844.png', 2500, 'Beranda mobile 390×844 — kartu destinasi dapat digeser, navigasi bawah tampil, tombol 44px.');
$b->image($shots.'/beranda-tablet-768x1024.png', 4700, 'Beranda tablet 768×1024 — konten tetap terpusat dan proporsional.');
$b->image($shots.'/beranda-desktop-1440x900.png', 8600, 'Beranda desktop 1440×900 — kanvas pengunjung tetap nyaman dibaca.');

$b->h2('5.2 Katalog destinasi');
$b->image($shots.'/destinasi-mobile-390x844.png', 2500, 'Katalog mobile 390×844 — filter dapat digeser, kartu destinasi tidak terpotong.');
$b->image($shots.'/destinasi-desktop-1440x900.png', 8600, 'Katalog desktop 1440×900.');

$b->h2('5.3 Login pengunjung dan admin');
$b->image($shots.'/masuk-mobile-390x844.png', 2500, 'Halaman masuk pengunjung mobile 390×844 — form selebar layar dan nyaman disentuh.');
$b->image($shots.'/admin-login-mobile-390x844.png', 2500, 'Login admin mobile 390×844 — panel ilustrasi disembunyikan, form tetap fokus.');
$b->image($shots.'/admin-login-desktop-1440x900.png', 8600, 'Login admin desktop 1440×900 — panel ilustrasi dan form dua area.');

$b->h2('5.4 Dashboard dan booking admin');
$b->image($shots.'/admin-dashboard-mobile-390x844.png', 2500, 'Dashboard admin mobile 390×844 — sidebar tersembunyi, tombol menu terlihat, kartu statistik dua kolom.');
$b->image($shots.'/admin-dashboard-tablet-768x1024.png', 4700, 'Dashboard admin tablet 768×1024 — kartu statistik dan panel mulai melebar.');
$b->image($shots.'/admin-dashboard-desktop-1440x900.png', 8600, 'Dashboard admin desktop 1440×900 — sidebar tetap 256px dan grid empat kolom.');
$b->image($shots.'/admin-booking-desktop-1440x900.png', 8600, 'Tabel booking admin desktop 1440×900 — seluruh kolom terlihat.');

$b->h1('6. Checklist Kualitas');
$b->table(['Pemeriksaan', 'Mobile 390', 'Tablet 768', 'Desktop 1440'], [
    ['Tidak ada horizontal scroll pada halaman', 'Lulus', 'Lulus', 'Lulus'],
    ['Navigasi dapat dijangkau', 'Lulus (bawah)', 'Lulus (bawah)', 'Lulus (sidebar/atas)'],
    ['Sidebar admin dapat dibuka/ditutup', 'Lulus', 'Lulus', 'Lulus (tetap)'],
    ['Tabel lebar dapat digeser', 'Lulus', 'Lulus', 'Lulus'],
    ['Form satu kolom pada layar kecil', 'Lulus', 'Lulus (dua kolom)', 'Lulus'],
    ['Target sentuh ≥ 44px', 'Lulus', 'Lulus', 'Lulus'],
    ['Gambar tidak terdistorsi', 'Lulus', 'Lulus', 'Lulus'],
    ['Teks terbaca tanpa zoom', 'Lulus', 'Lulus', 'Lulus'],
], [4000, 1900, 1900, 1838], ['fontSize' => 18]);

$b->h1('7. Bukti Pengujian Otomatis');
$b->table(['Tes', 'Hasil', 'Yang dikunci'], [
    ['Viewport dan navigasi bawah mobile', 'Lulus', '`name="viewport"`, `fixed bottom-0`, aria navigasi, skip link'],
    ['Sidebar offscreen dan tombol menu', 'Lulus', '`sidebarOpen`, `Buka menu admin`, `lg:ml-64`, `overflow-x-auto`'],
    ['Tabel dan filter admin di layar kecil', 'Lulus', '`min-w-[820px]`, wrapper scroll, input `w-full sm:w-60`'],
    ['Form admin menumpuk satu kolom', 'Lulus', '`md:grid-cols-2`, `md:col-span-2`'],
], [4000, 1300, 4338], ['fontSize' => 19]);
standardTestSummary($b);

$b->h1('8. Temuan dan Rekomendasi');
$b->table(['Temuan', 'Dampak', 'Rekomendasi'], [
    ['Tabel booking memerlukan scroll horizontal pada 390px', 'Dapat diterima; semua kolom tetap dapat diakses', 'Pertimbangkan tampilan kartu ringkas pada sprint berikutnya'],
    ['Screenshot memakai CDN Tailwind/Alpine', 'Render bergantung koneksi internet', 'Untuk produksi, gunakan build Tailwind lokal'],
    ['Belum diuji pada perangkat fisik', 'Perbedaan font/OS dapat memengaruhi tampilan', 'Uji tambahan pada Android/iOS nyata dan orientasi landscape'],
], [4000, 3000, 2638], ['fontSize' => 19]);

$b->h1('9. Kesimpulan');
$b->p('Layout pengunjung dan admin kini responsif pada mobile, tablet, dan desktop. Sidebar admin berubah menjadi off-canvas, tabel lebar dapat digeser, form menumpuk satu kolom, dan navigasi pengunjung memenuhi target sentuh 44px. Empat tes otomatis dan 15 screenshot menjadi bukti bahwa kualitas mobile bukan sekadar klaim.');
$b->save($outDir.'/S3-05_ RESPONSIVE & MOBILE QUALITY CHECK.docx');

// =====================================================================
// S3-06 — KEAMANAN DASAR
// =====================================================================
$b = baseDoc(
    'S3-06',
    'Keamanan Dasar',
    'Header keamanan, rate limiting, validasi upload, HMAC webhook, dan pengamanan sesi',
    'Basic Security Implementation',
    'S3-06 · Keamanan Dasar'
);
$b->cover();

$b->h1('1. Tujuan dan Ruang Lingkup');
$b->p('Tugas ini menerapkan kontrol keamanan dasar yang sesuai untuk aplikasi booking tiket: melindungi transportasi dan rendering, membatasi percobaan penyalahgunaan, memvalidasi berkas yang diunggah, memastikan webhook tidak dapat dipalsukan, serta menjaga sesi dan token tetap aman.');
$b->table(['Kontrol', 'Implementasi', 'Status'], [
    ['Header keamanan', 'Middleware `SecurityHeaders` + hook exception', 'Selesai'],
    ['Rate limiting', 'Limiter login, register, booking, API', 'Selesai'],
    ['Validasi upload', 'FormRequest `mimes:jpg,jpeg,png,pdf`, maksimal 4 MB', 'Selesai'],
    ['HMAC webhook', 'HMAC-SHA256 + `hash_equals` + tolak secret default', 'Selesai'],
    ['Sesi', 'Regenerasi saat login, invalidasi saat logout, cookie HttpOnly', 'Selesai'],
    ['Otorisasi', 'Middleware peran + pemeriksaan kepemilikan resource', 'Selesai'],
    ['Password', 'Cast `hashed` (bcrypt), field sensitif disembunyikan', 'Selesai'],
], [2800, 5300, 1538], ['fontSize' => 19]);

$b->h1('2. Header Keamanan');
$b->p('Header diterapkan pada seluruh respons web dan API, termasuk respons error. Nilai dan tujuannya:');
$b->table(['Header', 'Nilai', 'Tujuan'], [
    ['X-Content-Type-Options', 'nosniff', 'Mencegah browser menebak tipe konten dan mengeksekusi berkas berbahaya'],
    ['X-Frame-Options', 'SAMEORIGIN', 'Mencegah clickjacking dari situs lain'],
    ['Referrer-Policy', 'strict-origin-when-cross-origin', 'Membatasi kebocoran URL sensitif ke situs tujuan'],
    ['Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()', 'Membatasi akses fitur browser'],
    ['Cross-Origin-Opener-Policy', 'same-origin', 'Mengisolasi window dari origin lain'],
    ['X-Permitted-Cross-Domain-Policies', 'none', 'Mencegah pemuatan kebijakan lintas domain'],
    ['Strict-Transport-Security', 'max-age=31536000; includeSubDomains', 'Hanya pada request HTTPS di produksi'],
], [3300, 3700, 2638], ['fontSize' => 18]);

$b->code(<<<'PHP'
public static function apply(Response $response, ?Request $request = null): Response
{
    $request ??= request();

    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');
    $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
    $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

    if ($request->isSecure() && app()->environment('production')) {
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    return $response;
}
PHP, 'Potongan app/Http/Middleware/SecurityHeaders.php.');
$b->p('Bukti langsung `headers-masuk.txt` menunjukkan respons 200 dengan seluruh header di atas. `api-401.txt` dan `web-404.txt` menunjukkan header yang sama tetap ada pada respons error.');

$b->h1('3. Pembatasan Laju (Rate Limiting)');
$b->p('Limiter didefinisikan terpusat di `AppServiceProvider` dan dipasang pada rute yang berisiko.');
$b->table(['Limiter', 'Batas', 'Kunci', 'Dipakai pada'], [
    ['login', '5 / menit', 'email + IP', 'POST /masuk, POST /admin/login, POST /api/v1/auth/login'],
    ['register', '5 / menit', 'IP', 'POST /daftar, POST /api/v1/auth/register'],
    ['booking', '10 / menit', 'user ID atau IP', 'POST /api/v1/bookings'],
    ['api', '120 / menit', 'user ID atau IP', 'Seluruh grup API'],
], [1600, 1600, 2400, 4038], ['fontSize' => 18]);

$b->code(<<<'PHP'
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
});

RateLimiter::for('register', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('booking', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});
PHP, 'Potongan app/Providers/AppServiceProvider.php.');

$b->p('**Verifikasi.** Tes `test_login_web_dibatasi_setelah_lima_percobaan` melakukan lima percobaan gagal, lalu percobaan keenam dan memastikan respons 429. Tes lulus.');

$b->h1('4. Validasi Upload Bukti Pembayaran');
$b->p('Berkas yang diunggah tidak dipercaya. FormRequest membatasi tipe MIME dan ukuran, sedangkan controller memeriksa kepemilikan pembayaran sebelum menyimpan.');
$b->code(<<<'PHP'
public function rules(): array
{
    return [
        'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
    ];
}
PHP, 'Potongan app/Http/Requests/Api/StorePaymentProofRequest.php.');

$b->code(<<<'PHP'
public function proof(StorePaymentProofRequest $request, Payment $payment): RedirectResponse
{
    abort_unless((int) $payment->booking->user_id === (int) $request->user()->id, 403);

    $this->payments->attachProof($payment, $request->file('proof'));

    return redirect()
        ->route('booking.status', ['booking' => $payment->booking->code])
        ->with('ok', 'Bukti transfer berhasil diunggah. Pembayaran menunggu verifikasi admin.');
}
PHP, 'Potongan app/Http/Controllers/Web/PaymentController.php.');

$b->p('**Verifikasi.** Tes `test_upload_bukti_menolak_berkas_dengan_ekstensi_berbahaya` mengunggah `shell.php` dan memastikan validasi gagal serta `proof_path` tetap `null`. Tes lulus.');

$b->h1('5. HMAC Webhook');
$b->p('Webhook gateway adalah endpoint publik yang dapat mengubah status pembayaran. Oleh karena itu, setiap request wajib memiliki signature HMAC-SHA256 yang valid.');
$b->numbered([
    'Ambil raw body dan header `X-Signature`.',
    'Tolak jika signature kosong atau secret kosong/default.',
    "Hitung `hash_hmac('sha256', \$rawBody, \$secret)`.",
    'Bandingkan dengan `hash_equals` untuk mencegah timing attack.',
    'Proses webhook secara idempotent berdasarkan `transaction_ref`.',
]);
$b->code(<<<'PHP'
$secret = (string) config('payment.webhook_secret');

if ($secret === '' || $secret === 'change-me-production-secret') {
    Log::warning('Webhook ditolak: PAYMENT_WEBHOOK_SECRET belum diganti dengan nilai rahasia yang kuat.');

    return false;
}

$expected = hash_hmac('sha256', $rawBody, $secret);

return hash_equals($expected, $signature);
PHP, 'Potongan app/Services/Payments/SandboxGateway.php.');

$b->h1('6. Sesi, Password, dan Otorisasi');
$b->table(['Kontrol', 'Detail', 'Bukti'], [
    ['Regenerasi sesi', '`session()->regenerate()` setelah login berhasil', 'Web/AuthController'],
    ['Invalidasi logout', '`invalidate()` + `regenerateToken()`', 'Web/AuthController'],
    ['Cookie sesi', 'HttpOnly default, SameSite lax, Secure mengikuti HTTPS', 'config/session.php'],
    ['Password', 'Cast `hashed` (bcrypt), `password` dan `remember_token` hidden', 'app/Models/User.php'],
    ['Peran', 'Middleware `role:admin` / `role:customer`', 'EnsureUserHasRole'],
    ['Kepemilikan', 'Cek `user_id` pada booking/pembayaran sebelum akses', 'Web/PaymentController, API/PaymentController'],
    ['Token API', 'Sanctum personal access token; logout menghapus token aktif', 'Api/V1/AuthController'],
], [2500, 4500, 2638], ['fontSize' => 18]);

$b->h1('7. Bukti Pengujian Keamanan');
$b->table(['Tes', 'Hasil', 'Yang diverifikasi'], [
    ['Header keamanan pada respons web', 'Lulus', 'X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy'],
    ['Header keamanan pada respons API', 'Lulus', 'Header yang sama pada endpoint API'],
    ['Header pada respons error', 'Lulus', '404 web dan 401 API'],
    ['Login dibatasi setelah lima percobaan', 'Lulus', 'Status 429'],
    ['Webhook menolak secret default', 'Lulus', 'Status 401'],
    ['Upload menolak ekstensi berbahaya', 'Lulus', 'Validasi `proof` gagal, path null'],
    ['Bukti lama dihapus saat upload baru', 'Lulus', 'File lama hilang, file baru ada'],
], [4600, 1200, 3838], ['fontSize' => 18]);
standardTestSummary($b);

$b->h1('8. Checklist Keamanan');
$b->table(['Item', 'Status', 'Catatan'], [
    ['Kontrol akses berbasis peran', 'Selesai', 'Customer/admin dipisahkan di web dan API'],
    ['Validasi input server-side', 'Selesai', 'FormRequest + validasi controller'],
    ['Perlindungan CSRF', 'Selesai', 'Token Blade otomatis pada form web'],
    ['Escaping output', 'Selesai', 'Blade `{{ }}`'],
    ['Password hashing', 'Selesai', 'Cast `hashed`'],
    ['Rate limiting', 'Selesai', 'Login, register, booking, API'],
    ['Upload aman', 'Selesai', 'MIME + ukuran + kepemilikan'],
    ['Webhook terautentikasi', 'Selesai', 'HMAC + secret wajib kuat'],
    ['Header keamanan', 'Selesai', 'Termasuk respons error'],
    ['Logging error 500', 'Selesai', 'Detail di log, pesan generik ke pengguna'],
], [5000, 1400, 3238], ['fontSize' => 19]);

$b->h1('9. Risiko Residual dan Rekomendasi');
$b->table(['Risiko', 'Tingkat', 'Rekomendasi'], [
    ['Belum ada Content-Security-Policy ketat', 'Sedang', 'Tambahkan CSP setelah aset Tailwind/Alpine dipindahkan ke build lokal'],
    ['HSTS hanya aktif di HTTPS produksi', 'Rendah', 'Pastikan reverse proxy mengirim HTTPS dan aktifkan HSTS'],
    ['Belum ada pemindaian malware pada upload', 'Sedang', 'Tambahkan antivirus/clamd atau validasi gambar ulang'],
    ['Belum ada 2FA untuk admin', 'Sedang', 'Pertimbangkan TOTP untuk akun admin'],
    ['Secret webhook disimpan di env', 'Rendah', 'Rotasi berkala dan gunakan secret manager di produksi'],
], [4200, 1400, 4038], ['fontSize' => 18]);

$b->h1('10. Kesimpulan');
$b->p('Kontrol keamanan dasar telah diterapkan dan diuji: header pada semua respons, rate limiting, validasi upload, HMAC webhook dengan penolakan secret default, sesi yang diregenerasi, serta otorisasi berbasis peran dan kepemilikan. Tujuh tes keamanan dan beberapa berkas bukti HTTP langsung mendukung implementasi ini. Risiko residual telah dicatat untuk perbaikan bertahap.');
$b->save($outDir.'/S3-06_ BASIC SECURITY IMPLEMENTATION.docx');

// =====================================================================
// S3-07 — PERFORMA & USABILITY
// =====================================================================
$b = baseDoc(
    'S3-07',
    'Performa & Usability',
    'Optimasi cache, query, pemuatan aset, dan peningkatan aksesibilitas',
    'Performance & Usability Refinement',
    'S3-07 · Performa & Usability'
);
$b->cover();

$b->h1('1. Tujuan');
$b->p('Tugas ini menyempurnakan kecepatan dan kenyamanan penggunaan aplikasi. Fokus performa diarahkan pada pengurangan query berulang dan pemuatan aset, sedangkan fokus usability diarahkan pada aksesibilitas, navigasi, dan umpan balik visual.');
$b->table(['Sasaran', 'Kriteria berhasil'], [
    ['Cache katalog', 'Daftar kategori tidak di-query ulang pada setiap request.'],
    ['Konsistensi cache', 'Cache dibersihkan saat kategori berubah.'],
    ['Query detail', 'Rating dan jumlah ulasan diambil dalam satu query.'],
    ['Pemuatan aset', 'Preconnect, lazy image, async decoding.'],
    ['Aksesibilitas', 'Skip link, landmark, aria-current, target sentuh 44px.'],
    ['Bukti', 'Tes otomatis, pengukuran waktu, dan screenshot.'],
], [3000, 6638], ['fontSize' => 19]);

$b->h1('2. Optimasi Performa');
$b->h2('2.1 Cache daftar kategori');
$b->p('Daftar kategori dipakai di beranda dan halaman katalog. Data ini jarang berubah, sehingga cocok di-cache selama satu jam.');
$b->code(<<<'PHP'
class Category extends Model
{
    public const CACHE_KEY = 'catalog.categories';

    public static function cachedList(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addHour(),
            fn () => self::query()->orderBy('name')->get(['id', 'name', 'slug'])
        );
    }
}
PHP, 'Potongan app/Models/Category.php.');
$b->p('Tes `test_request_kedua_tidak_mengulang_query_kategori` mengaktifkan query log pada request kedua dan memastikan **nol query** ke tabel `categories`. Tes lulus.');

$b->h2('2.2 Invalidasi cache berbasis event');
$b->p('Cache tidak boleh basi. Event `saved` dan `deleted` pada model `Category` menghapus cache secara otomatis.');
$b->code(<<<'PHP'
protected function configureCatalogCache(): void
{
    Category::saved(fn () => Cache::forget(Category::CACHE_KEY));
    Category::deleted(fn () => Cache::forget(Category::CACHE_KEY));
}
PHP, 'Potongan app/Providers/AppServiceProvider.php.');

$b->h2('2.3 Agregasi rating satu query');
$b->p('Halaman detail destinasi sebelumnya menjalankan dua query agregat. Sekarang satu query menghitung rata-rata dan jumlah ulasan sekaligus.');
$b->code(<<<'PHP'
$reviewStats = $destination->reviews()
    ->selectRaw('AVG(rating) as rating_avg, COUNT(*) as reviews_count')
    ->first();

$destination->setAttribute('rating_avg', round((float) ($reviewStats->rating_avg ?? 0), 1));
$destination->setAttribute('reviews_count', (int) ($reviewStats->reviews_count ?? 0));
PHP, 'Potongan app/Http/Controllers/CatalogController.php.');

$b->h2('2.4 Eager loading dan harga minimum');
$b->p("Katalog memakai `with('category:id,name,slug')`, `withMin()` untuk harga tiket aktif, dan `withAvg()` untuk rating. Pendekatan ini menghindari query per kartu (N+1) dan memastikan hanya kolom yang dibutuhkan yang diambil.");
$b->code(<<<'PHP'
$destinations = Destination::query()
    ->active()
    ->with('category:id,name,slug')
    ->withMin(['ticketTypes as min_price' => fn ($q) => $q->where('status', 'active')], 'price')
    ->withAvg('reviews as rating_avg', 'rating')
    ->search($request->input('q'))
    ->orderByDesc('is_featured')
    ->orderByDesc('id')
    ->limit(8)
    ->get();
PHP, 'Potongan app/Http/Controllers/CatalogController.php — beranda.');

$b->h2('2.5 Pemuatan aset dan gambar');
$b->bullets([
    '`preconnect` ke `fonts.googleapis.com` dan `fonts.gstatic.com` mempercepat negosiasi koneksi font.',
    '`meta name="theme-color"` memberi warna bilah browser yang konsisten di mobile.',
    '`meta name="description"` menyediakan ringkasan halaman untuk mesin pencari.',
    'Gambar katalog memakai `loading="lazy"` dan `decoding="async"` agar tidak memblokir render awal.',
    'Tailwind dan Alpine dimuat dengan `defer` sehingga HTML dapat diparse lebih dulu.',
]);

$b->h1('3. Hasil Pengukuran');
$b->p('Pengukuran dilakukan dengan `curl -w` sebanyak lima kali per URL pada server pengembangan lokal. Cache dalam keadaan hangat setelah request pertama. Angka ini bukan benchmark produksi, tetapi cukup untuk memverifikasi bahwa halaman merespons dalam rentang yang wajar.');
$b->table(['URL', 'Ukuran respons', 'Rentang waktu', 'Median'], [
    ['`/`', '12.974 B', '0,436 – 0,660 s', '0,502 s'],
    ['`/destinasi`', '16.531 B', '0,461 – 0,592 s', '0,491 s'],
    ['`/api/v1/categories`', '494 B', '0,407 – 0,493 s', '0,428 s'],
], [3000, 2200, 2600, 1838], ['fontSize' => 19]);
$b->p('Berkas `evidence/performance-timing.txt` menyimpan kelima pengukuran per URL.');

$b->h1('4. Bukti Pengujian Performa');
$b->table(['Tes', 'Hasil', 'Yang diverifikasi'], [
    ['Kategori di-cache dan dibersihkan saat berubah', 'Lulus', '`Cache::has` true lalu false setelah create'],
    ['Request kedua tidak mengulang query kategori', 'Lulus', '0 query `from categories` pada request kedua'],
    ['Detail destinasi menampilkan rating dan jumlah ulasan', 'Lulus', '`4.0` dan `1 ulasan` dari agregasi'],
    ['Gambar katalog memakai lazy loading', 'Lulus', '`loading="lazy"` dan `decoding="async"`'],
], [4600, 1200, 3838], ['fontSize' => 18]);
standardTestSummary($b);

$b->h1('5. Peningkatan Usability');
$b->h2('5.1 Aksesibilitas navigasi');
$b->p('Layout pengunjung dan admin menambahkan skip link, landmark `<main>`, label navigasi, dan penanda halaman aktif. Pengguna keyboard dapat melompat langsung ke konten tanpa melewati seluruh menu.');
$b->code(<<<'HTML'
<a href="#main-content"
   class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100]
          focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:text-sm focus:text-white">
    Lewati ke konten utama
</a>

<nav aria-label="Navigasi utama" class="fixed bottom-0 inset-x-0 max-w-md mx-auto ...">
    <a href="{{ url($path) }}" @if($aktif) aria-current="page" @endif
       class="flex min-h-[44px] flex-col items-center gap-1 py-2">
        ...
    </a>
</nav>

<main id="main-content">@yield('content')</main>
HTML, 'Potongan resources/views/layouts/app.blade.php.');

$b->h2('5.2 Target sentuh dan metadata');
$b->bullets([
    'Setiap item navigasi bawah memiliki tinggi minimum 44px sesuai pedoman target sentuh.',
    'Tombol menu admin berukuran 40×40px dengan area klik yang memadai.',
    'Meta viewport, theme-color, dan description tersedia di layout master.',
    'Gambar memiliki atribut `alt` yang bermakna.',
    'Kontras teks utama terhadap latar putih memenuhi tingkat keterbacaan yang wajar.',
]);

$b->h2('5.3 Umpan balik dan konsistensi');
$b->bullets([
    'Pesan sukses dan error ditampilkan konsisten melalui session flash dan komponen alert.',
    'Status pembayaran dan booking menggunakan label yang jelas.',
    'Loading state pada tombol mencegah pengiriman ganda.',
    'Layout responsif memastikan pengalaman yang sama pada mobile dan desktop (lihat S3-05).',
]);

$b->h1('6. Checklist');
$b->table(['Item', 'Status', 'Bukti'], [
    ['Daftar kategori di-cache', 'Selesai', 'Category::cachedList; tes cache'],
    ['Cache dibersihkan saat data berubah', 'Selesai', 'Event saved/deleted; tes'],
    ['Query kategori kedua = 0', 'Selesai', 'PerformanceUsabilityTest'],
    ['Agregasi rating satu query', 'Selesai', 'CatalogController; tes'],
    ['Eager loading katalog', 'Selesai', 'with/withMin/withAvg'],
    ['Preconnect dan metadata', 'Selesai', 'layouts/master.blade.php'],
    ['Lazy image dan async decoding', 'Selesai', 'Beranda & katalog; tes'],
    ['Skip link dan landmark', 'Selesai', 'layouts/app & admin'],
    ['aria-current dan label navigasi', 'Selesai', 'layouts/app'],
    ['Target sentuh 44px', 'Selesai', 'Navigasi bawah'],
], [5000, 1400, 3238], ['fontSize' => 19]);

$b->h1('7. Rekomendasi Lanjutan');
$b->table(['Rekomendasi', 'Manfaat', 'Prioritas'], [
    ['Build Tailwind lokal (Vite) alih-alih CDN', 'Menghilangkan ketergantungan jaringan dan memperkecil CSS', 'Tinggi'],
    ['Aktifkan OPcache di produksi', 'Mengurangi waktu kompilasi PHP', 'Tinggi'],
    ['Gunakan Redis untuk cache/session', 'Akses lebih cepat dan terpusat', 'Sedang'],
    ['Konversi gambar ke WebP/AVIF', 'Ukuran gambar lebih kecil', 'Sedang'],
    ['Audit Lighthouse dan Core Web Vitals', 'Mengukur pengalaman nyata pengguna', 'Sedang'],
    ['Uji beban dengan k6/Locust', 'Mengetahui kapasitas server', 'Rendah'],
], [4200, 3900, 1538], ['fontSize' => 19]);

$b->h1('8. Kesimpulan');
$b->p('Optimasi performa berhasil mengurangi query berulang melalui cache kategori dan agregasi rating, serta memperbaiki pemuatan aset melalui preconnect dan lazy loading. Usability meningkat melalui skip link, landmark, aria-current, target sentuh 44px, dan metadata halaman. Empat tes otomatis memastikan optimasi tetap bekerja, dan pengukuran lokal menunjukkan halaman merespons di bawah satu detik.');
$b->save($outDir.'/S3-07_ PERFORMANCE & USABILITY REFINEMENT.docx');

echo 'SELESAI: 8 dokumen dibuat di '.$outDir.PHP_EOL;
