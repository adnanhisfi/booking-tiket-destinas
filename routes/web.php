<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Web\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\DestinationController as AdminDestinationController;
use App\Http\Controllers\Web\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Web\Admin\QuotaController as AdminQuotaController;
use App\Http\Controllers\Web\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Web\Admin\ScanController as AdminScanController;
use App\Http\Controllers\Web\Admin\TicketTypeController as AdminTicketTypeController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Katalog publik
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'beranda'])->name('katalog.beranda');
Route::get('/destinasi', [CatalogController::class, 'index'])->name('katalog.index');
Route::get('/destinasi/{destination:slug}', [CatalogController::class, 'detail'])->name('katalog.detail');

/*
|--------------------------------------------------------------------------
| Autentikasi (tamu)
|--------------------------------------------------------------------------
*/
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

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Area pengunjung
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/destinasi/{destination:slug}/pesan', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/destinasi/{destination:slug}/pesan', [BookingController::class, 'store'])->name('booking.store');

    Route::get('/riwayat', [BookingController::class, 'history'])->name('booking.history');
    Route::get('/booking/{booking:code}', [BookingController::class, 'show'])->name('booking.show');
    Route::get('/booking/{booking:code}/status', [BookingController::class, 'status'])->name('booking.status');
    Route::post('/booking/{booking:code}/batal', [BookingController::class, 'cancel'])->name('booking.cancel');

    Route::post('/booking/{booking:code}/bayar', [PaymentController::class, 'store'])->name('payment.store');
    Route::get('/pembayaran/{payment:transaction_ref}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/pembayaran/{payment:transaction_ref}/bukti', [PaymentController::class, 'proof'])->name('payment.proof');

    Route::get('/booking/{booking:code}/e-ticket', [TicketController::class, 'show'])->name('ticket.show');
    Route::get('/booking/{booking:code}/e-ticket/{ticket}/qr', [TicketController::class, 'qr'])->name('ticket.qr');

    Route::post('/destinasi/{destination:slug}/ulasan', [ReviewController::class, 'store'])->name('review.store');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Area admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/destinasi', [AdminDestinationController::class, 'index'])->name('destinations.index');
    Route::get('/destinasi/create', [AdminDestinationController::class, 'create'])->name('destinations.create');
    Route::post('/destinasi', [AdminDestinationController::class, 'store'])->name('destinations.store');
    Route::get('/destinasi/{destination:slug}/edit', [AdminDestinationController::class, 'edit'])->name('destinations.edit');
    Route::put('/destinasi/{destination:slug}', [AdminDestinationController::class, 'update'])->name('destinations.update');
    Route::delete('/destinasi/{destination:slug}', [AdminDestinationController::class, 'destroy'])->name('destinations.destroy');

    Route::get('/tiket', [AdminTicketTypeController::class, 'index'])->name('tickets.index');
    Route::get('/tiket/create', [AdminTicketTypeController::class, 'create'])->name('tickets.create');
    Route::post('/tiket', [AdminTicketTypeController::class, 'store'])->name('tickets.store');
    Route::get('/tiket/{ticketType}/edit', [AdminTicketTypeController::class, 'edit'])->name('tickets.edit');
    Route::put('/tiket/{ticketType}', [AdminTicketTypeController::class, 'update'])->name('tickets.update');
    Route::delete('/tiket/{ticketType}', [AdminTicketTypeController::class, 'destroy'])->name('tickets.destroy');

    Route::get('/kuota', [AdminQuotaController::class, 'index'])->name('quotas.index');
    Route::post('/kuota', [AdminQuotaController::class, 'store'])->name('quotas.store');

    Route::get('/booking', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/booking/export', [AdminBookingController::class, 'export'])->name('bookings.export');
    Route::get('/booking/{booking:code}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/booking/{booking:code}/selesai', [AdminBookingController::class, 'complete'])->name('bookings.complete');
    Route::post('/booking/{booking:code}/batal', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/pembayaran/{payment:transaction_ref}/verifikasi', [AdminPaymentController::class, 'verify'])->name('payments.verify');

    Route::get('/laporan', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/unduh', [AdminReportController::class, 'export'])->name('reports.export');

    Route::get('/scan', [AdminScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [AdminScanController::class, 'scan'])->name('scan.validate');
});
