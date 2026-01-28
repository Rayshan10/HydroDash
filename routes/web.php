<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Tampilan Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Endpoint untuk ESP32 Wokwi
Route::post('/terima-data', [DashboardController::class, 'store']);

// Routes untuk Report
Route::prefix('report')->name('report.')->group(function () {
    // Unified Report Page
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/pdf', [ReportController::class, 'pdfReport'])->name('pdf');
    Route::get('/export', [ReportController::class, 'exportReport'])->name('export');
});

// Legacy routes untuk backward compatibility
Route::redirect('/report/daily', '/report?type=daily');
Route::redirect('/report/monthly', '/report?type=monthly');
Route::redirect('/report/yearly', '/report?type=yearly');
Route::redirect('/report/period', '/report?type=period');