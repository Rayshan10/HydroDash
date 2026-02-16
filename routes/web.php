<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ==========================================
// DASHBOARD & DATA RECEIVER (ESP32)
// ==========================================
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/terima-data', [DashboardController::class, 'store']);

// ==========================================
// UNIFIED REPORT SYSTEM
// ==========================================
Route::prefix('report')->name('report.')->group(function () {
    // Tampilan Halaman Laporan (Daily, Monthly, Yearly, Period)
    Route::get('/', [ReportController::class, 'index'])->name('index');
    
    // Fitur Export (Satu route untuk semua tipe)
    Route::get('/export', [ReportController::class, 'exportReport'])->name('export');
    Route::get('/pdf', [ReportController::class, 'pdfReport'])->name('pdf');
});

// ==========================================
// BACKWARD COMPATIBILITY (Opsional)
// ==========================================
// Jika Anda masih memiliki link lama di tempat lain, 
// ini akan otomatis mengarahkan ke sistem unified yang baru.
Route::redirect('/report/daily', '/report?type=daily');
Route::redirect('/report/monthly', '/report?type=monthly');
Route::redirect('/report/yearly', '/report?type=yearly');
Route::redirect('/report/period', '/report?type=period');