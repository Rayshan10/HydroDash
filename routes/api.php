<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Satu-satunya pintu ingest data sensor ESP32 — token via header
// X-Device-Token atau field token, validasi & fallback dari config/hydro.php.
Route::post('/terima-data', [DashboardController::class, 'store']);

// AJAX dashboard: ambil data terbaru (dipanggil tiap 1 detik)
Route::get('/get-latest-hydro', [DashboardController::class, 'getLatest']);
