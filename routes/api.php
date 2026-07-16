<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountryApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/countries', [CountryApiController::class, 'countries']);
    Route::get('/risk', [CountryApiController::class, 'risk']);
    Route::get('/ports', [CountryApiController::class, 'ports']);
    Route::post('/ports/sync-global', [CountryApiController::class, 'syncGlobalPorts']);
    Route::get('/news', [CountryApiController::class, 'news']);
    Route::get('/currency', [CountryApiController::class, 'currency']);
    Route::post('/countries/sync', [CountryApiController::class, 'syncCountryData']);
    Route::get('/articles', [CountryApiController::class, 'articles']);
    Route::get('/articles/{id}', [CountryApiController::class, 'articleDetail']);
});
