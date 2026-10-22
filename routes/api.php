<?php

use App\Http\Controllers\Api\V1\Admin\AvailabilityController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\TicketTypeController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DestinationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\VoucherController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Publik
    |--------------------------------------------------------------------------
    */
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:login');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('destinations', [DestinationController::class, 'index']);
    Route::get('destinations/{destination}', [DestinationController::class, 'show']);
    Route::get('destinations/{destination}/tickets', [DestinationController::class, 'tickets']);
    Route::get('payments/webhook', fn () => response()->json(['message' => 'Webhook endpoint aktif. Gunakan POST.']));

    /*
    |--------------------------------------------------------------------------
    | Gateway
    |--------------------------------------------------------------------------
    */
    Route::post('payments/webhook', [PaymentController::class, 'webhook']);

    /*
    |--------------------------------------------------------------------------
    | Pengunjung (butuh login)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'updateProfile']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me/tickets', [TicketController::class, 'index']);

        Route::post('bookings', [BookingController::class, 'store'])->middleware('throttle:booking');
        Route::get('bookings', [BookingController::class, 'index']);
        Route::get('bookings/{booking:code}', [BookingController::class, 'show']);
        Route::post('bookings/{booking:code}/cancel', [BookingController::class, 'cancel']);

        Route::post('bookings/{booking:code}/payments', [PaymentController::class, 'store']);
        Route::get('payments/{payment:transaction_ref}', [PaymentController::class, 'show']);
        Route::post('payments/{payment:transaction_ref}/proof', [PaymentController::class, 'proof']);

        Route::get('tickets/{ticket:code}', [TicketController::class, 'show']);
        Route::get('tickets/{ticket:code}/qr', [TicketController::class, 'qr']);

        Route::post('destinations/{destination}/reviews', [ReviewController::class, 'store']);
        Route::post('vouchers/check', [VoucherController::class, 'check']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('dashboard', [ReportController::class, 'dashboard']);
        Route::get('reports/sales', [ReportController::class, 'sales']);

        Route::apiResource('categories', App\Http\Controllers\Api\V1\Admin\CategoryController::class)
            ->except(['show']);

        Route::apiResource('destinations', App\Http\Controllers\Api\V1\Admin\DestinationController::class);

        Route::apiResource('tickets', TicketTypeController::class)
            ->except(['show']);
        Route::post('tickets/validate', App\Http\Controllers\Api\V1\Admin\TicketController::class.'@validate');

        Route::get('availability', [AvailabilityController::class, 'index']);
        Route::put('availability', [AvailabilityController::class, 'upsert']);

        Route::get('bookings', [App\Http\Controllers\Api\V1\Admin\BookingController::class, 'index']);
        Route::get('bookings/export', [App\Http\Controllers\Api\V1\Admin\BookingController::class, 'export']);
        Route::get('bookings/{booking:code}', [App\Http\Controllers\Api\V1\Admin\BookingController::class, 'show']);

        Route::get('payments', [App\Http\Controllers\Api\V1\Admin\PaymentController::class, 'index']);
        Route::post('payments/{payment:transaction_ref}/verify', [App\Http\Controllers\Api\V1\Admin\PaymentController::class, 'verify']);

        Route::apiResource('vouchers', App\Http\Controllers\Api\V1\Admin\VoucherController::class)
            ->except(['show']);
    });
});
