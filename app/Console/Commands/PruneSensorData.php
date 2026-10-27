<?php

namespace App\Console\Commands;

use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneSensorData extends Command
{
    protected $signature = 'sensors:prune {--days= : Batas retensi data dalam hari (default dari config hydro.prune.retention_days)}';

    protected $description = 'Menghapus data sensor yang lebih tua dari batas retensi (pemeliharaan database)';

    public function handle(): int
    {
        // Penting: cek null terlebih dahulu — pola `0 ?: default` melewatkan --days=0 secara diam-diam
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('hydro.prune.retention_days', 365);

        // Pengaman: tolak retensi < 1 hari agar tidak terjadi kehilangan semua data
        if ($days < 1) {
            $this->error('Nilai --days minimal 1 hari. Penghapusan dibatalkan.');
            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days)->format('d-m-Y H:i');
        $deleted = SensorData::where('created_at', '<', Carbon::now()->subDays($days))->delete();

        if ($deleted === 0) {
            $this->info("Bersih. Tidak ada data yang lebih tua dari {$cutoff}.");
        } else {
            $this->info("{$deleted} baris data sensor yang lebih tua dari {$cutoff} telah dihapus.");
        }

        return self::SUCCESS;
    }
}
