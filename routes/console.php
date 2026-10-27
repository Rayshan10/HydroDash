<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pemeliharaan database: hapus data sensor lebih tua dari batas retensi
// (config hydro.prune.retention_days, standarnya 365 hari).
Schedule::command('sensors:prune')->daily()->at('03:00');
