<?php

use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'beranda'])->name('katalog.beranda');
Route::get('/destinasi/{destination:slug}', [CatalogController::class, 'detail'])->name('katalog.detail');
