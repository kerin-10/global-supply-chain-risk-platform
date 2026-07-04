<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryApiController;

/*
|--------------------------------------------------------------------------
| API Routes - Global Supply Chain Risk Intelligence Platform
|--------------------------------------------------------------------------
| Semua endpoint berikut dapat diakses publik (tanpa autentikasi session)
| karena digunakan oleh AJAX dari halaman dashboard yang sudah dilindungi.
*/

Route::prefix('v1')->group(function () {

    // Daftar semua negara + skor risiko
    Route::get('/countries',      [CountryApiController::class, 'countries']);

    // Detail risiko per negara
    // Contoh: /api/v1/risk?kode_iso2=DE
    Route::get('/risk',           [CountryApiController::class, 'risk']);

    // Daftar pelabuhan + kemacetan
    // Contoh: /api/v1/ports?cari=Rotterdam
    Route::get('/ports',          [CountryApiController::class, 'ports']);

    // Berita & sentimen per negara
    // Contoh: /api/v1/news?kode_iso2=ID&sync=true
    Route::get('/news',           [CountryApiController::class, 'news']);

    // Nilai tukar mata uang
    // Contoh: /api/v1/currency?base=USD&target=IDR
    Route::get('/currency',       [CountryApiController::class, 'currency']);

    // Sinkronisasi data lengkap satu negara dari semua API eksternal
    // Contoh: POST /api/v1/countries/sync  { "kode_iso2": "ID" }
    Route::post('/countries/sync',[CountryApiController::class, 'syncCountryData']);
});
