<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ==========================================
// DASHBOARD
// ==========================================
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Ingest data ESP32 hanya lewat SATU endpoint: POST /api/terima-data
// (lihat routes/api.php) — duplikat web dihapus pada Fase 3 agar tidak ada
// dua pintu dengan aturan validasi & token yang harus dirawat terpisah.

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