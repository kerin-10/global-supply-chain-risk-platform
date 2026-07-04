<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;

// ==========================================
//  RUTE AUTENTIKASI
// ==========================================
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');
Route::post('/logout',  [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
//  RUTE DASHBOARD (Login Wajib)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',               [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/cuaca',         [DashboardController::class, 'weather'])->name('dashboard.weather');
    Route::get('/dashboard/pelabuhan',     [DashboardController::class, 'ports'])->name('dashboard.ports');
    Route::get('/dashboard/mata-uang',     [DashboardController::class, 'currency'])->name('dashboard.currency');
    Route::get('/dashboard/berita',        [DashboardController::class, 'news'])->name('dashboard.news');
    Route::get('/dashboard/perbandingan',  [DashboardController::class, 'compare'])->name('dashboard.compare');
    Route::get('/dashboard/visualisasi',   [DashboardController::class, 'visualization'])->name('dashboard.visualization');
    Route::get('/dashboard/daftar-pantau', [DashboardController::class, 'watchlist'])->name('dashboard.watchlist');
    Route::post('/dashboard/daftar-pantau/toggle', [DashboardController::class, 'toggleWatchlist'])->name('dashboard.watchlist.toggle');

    // Artikel Analisis
    Route::get('/artikel',      [DashboardController::class, 'articles'])->name('articles.index');
    Route::get('/artikel/{id}', [DashboardController::class, 'articleDetail'])->name('articles.show');
});

// ==========================================
//  RUTE ADMIN (Login + Peran Admin)
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              [AdminController::class, 'index'])->name('index');

    // Manajemen Pengguna
    Route::get('/pengguna',                        [AdminController::class, 'penggunaDaftar'])->name('pengguna.daftar');
    Route::delete('/pengguna/{id}',                [AdminController::class, 'penggunaHapus'])->name('pengguna.hapus');
    Route::patch('/pengguna/{id}/ubah-peran',      [AdminController::class, 'penggunaUbahPeran'])->name('pengguna.ubah-peran');

    // Manajemen Pelabuhan
    Route::get('/pelabuhan',                       [AdminController::class, 'pelabuhanDaftar'])->name('pelabuhan.daftar');
    Route::post('/pelabuhan',                      [AdminController::class, 'pelabuhanSimpan'])->name('pelabuhan.simpan');
    Route::delete('/pelabuhan/{id}',               [AdminController::class, 'pelabuhanHapus'])->name('pelabuhan.hapus');

    // Manajemen Artikel
    Route::get('/artikel',                         [AdminController::class, 'artikelDaftar'])->name('artikel.daftar');
    Route::get('/artikel/buat',                    [AdminController::class, 'artikelBuat'])->name('artikel.buat');
    Route::post('/artikel',                        [AdminController::class, 'artikelSimpan'])->name('artikel.simpan');
    Route::delete('/artikel/{id}',                 [AdminController::class, 'artikelHapus'])->name('artikel.hapus');

    // Manajemen Leksikon Sentimen
    Route::get('/leksikon',                        [AdminController::class, 'leksikonDaftar'])->name('leksikon.daftar');
    Route::post('/leksikon/positif',               [AdminController::class, 'leksikonTambahPositif'])->name('leksikon.positif.tambah');
    Route::post('/leksikon/negatif',               [AdminController::class, 'leksikonTambahNegatif'])->name('leksikon.negatif.tambah');
    Route::delete('/leksikon/positif/{id}',        [AdminController::class, 'leksikonHapusPositif'])->name('leksikon.positif.hapus');
    Route::delete('/leksikon/negatif/{id}',        [AdminController::class, 'leksikonHapusNegatif'])->name('leksikon.negatif.hapus');

    // Pengaturan Sistem
    Route::post('/pengaturan',                     [AdminController::class, 'pengaturanSimpan'])->name('pengaturan.simpan');
});
