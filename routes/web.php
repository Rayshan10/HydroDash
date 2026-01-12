<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Tampilan Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Endpoint untuk ESP32 Wokwi
Route::post('/terima-data', [DashboardController::class, 'store']);