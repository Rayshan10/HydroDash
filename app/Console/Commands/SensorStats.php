<?php

namespace App\Console\Commands;

use App\Models\SensorData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SensorStats extends Command
{
    protected $signature = 'sensors:stats';

    protected $description = 'Ringkasan kesehatan data sensor: jumlah baris, rentang waktu, usia data terkini, ukuran tabel, dan sisa data yang akan terprune';

    public function handle(): int
    {
        $retention = (int) config('hydro.prune.retention_days', 365);
        $staleAfter = (int) config('hydro.stale_after_seconds', 120);

        $info = [
            'Total baris data'   => number_format(SensorData::count()),
            'Data terlama'       => SensorData::min('created_at') ?? '-',
            'Data terkini'       => SensorData::max('created_at') ?? '-',
        ];

        $latest = SensorData::latest('id')->first();
        if ($latest) {
            $age = (int) abs(now()->diffInSeconds($latest->created_at));
            // Format Respons: di bawah sehari → HH:ii:ss, setelah itu belum hari
            $ageText = $age >= 86400
                ? intdiv($age, 86400) . ' hari ' . gmdate('H:i:s', $age % 86400)
                : gmdate('H:i:s', $age);
            $status = $age <= $staleAfter
                ? 'MASIH SEGAR (perangkat aktif)'
                : "TERTAHAN > {$ageText} (periksa ESP32)";
            $info['Usia data terkini'] = $age . ' detik — ' . $status;
        }

        $info['Data yang akan terprune'] = number_format(
            SensorData::where('created_at', '<', now()->subDays($retention))->count()
        ) . " baris (retensi {$retention} hari)";

        // Ukuran tabel hanya terbaca di MySQL (information_schema tidak ada di sqlite)
        try {
            $row = DB::selectOne(
                "SELECT ROUND((data_length + index_length) / 1048576, 1) AS size_mb
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'sensor_data'"
            );
            $info['Ukuran tabel (MySQL)'] = $row ? $row->size_mb . ' MB' : 'tidak diketahui';
        } catch (\Throwable) {
            $info['Ukuran tabel (MySQL)'] = 'tidak tersedia di driver non-MySQL';
        }

        $this->table(['Metrik', 'Nilai'], collect($info)->map(fn ($v, $k) => [$k, $v])->values());

        return self::SUCCESS;
    }
}
