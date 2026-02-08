<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rute untuk menerima data dari ESP32 (Sudah ada)
Route::post('/terima-data', [DashboardController::class, 'store']);

// TAMBAHKAN INI: Rute untuk memberikan data terbaru ke Dashboard (AJAX)
Route::get('/get-latest-hydro', [DashboardController::class, 'getLatest']);