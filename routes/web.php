<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebGISController;

// --- Rute Halaman Utama (Views) ---
Route::get('/', [WebGISController::class, 'beranda'])->name('beranda');
Route::get('/peta', [WebGISController::class, 'peta'])->name('peta');
Route::get('/statistik', [WebGISController::class, 'statistik'])->name('statistik');
Route::get('/laporan', [WebGISController::class, 'laporan'])->name('laporan');

// --- Rute untuk Menangani Form Submit Laporan ---
Route::post('/laporan', [WebGISController::class, 'simpanLaporan']);

// --- Rute API untuk Peta Leaflet & Chart.js ---
Route::get('/api/peta', [WebGISController::class, 'apiPeta']);
Route::get('/api/statistik', [WebGISController::class, 'apiStatistik']);