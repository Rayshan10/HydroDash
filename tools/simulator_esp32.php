#!/usr/bin/env php
<?php

/**
 * ==========================================================================
 *  HydroDash — Simulator Perangkat ESP32 (tanpa hardware / rangkaian)
 * ==========================================================================
 *
 * Script CLI ini meniru perilaku sketch ESP32 (`program_esp32.ino`):
 * mengirim pembacaan sensor TIAP ±5 DETIK ke endpoint `POST /api/terima-data`
 * dengan device token yang sama, sehingga dashboard terlihat hidup & data
 * demo masuk secara berkala. Kontrak data 100% identik — saat nanti ada
 * rangkaian asli, sketch langsung menggantikan simulator tanpa ubah server.
 *
 * Pemakaian:
 *
 *   # Mode LIVE — kirim tiap 5 detik sampai Ctrl+C
 *   php tools\simulator_esp32.php
 *
 *   # Mode BACKFILL — tanam riwayat N jam terakhir (default 24) sekali jalan
 *   # agar grafik & laporan harian langsung terisi
 *   php tools\simulator_esp32.php --backfill=24
 *
 *   # Mode LIVE dengan batas siklus (praktis untuk pengujian)
 *   php tools\simulator_esp32.php --cycles=5
 *
 *   # Opsi lain:
 *   #   --url=http://127.0.0.1:8000   alamat server (default 127.0.0.1:8000)
 *   #   --interval=5                  jeda kirim dalam detik (mode live)
 *
 * Nilai data dibuat realistis: pH berayun di area ideal (±6.0), TDS
 * menurun seiring "konsumsi nutrisi" lalu naik (refill), suhu mengikuti
 * siklus harian, plus episode acak kecil (pH drop / TDS drop / panas)
 * agar status relay (pompa pH, pompa nutrisi, pendingin) sesekali ON
 * seperti keadaan nyata.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// --------------------------------------------------------------------------
// Parse argumen
// --------------------------------------------------------------------------
$url = 'http://127.0.0.1:8000';
$interval = 5;
$backfill = null; // jam
$cycles = null;   // batas siklus live (null = tanpa batas)

foreach (array_slice($argv, 1) as $arg) {
    [$key, $value] = array_pad(explode('=', $arg, 2), 2, null);
    switch ($key) {
        case '--backfill':
            $backfill = max(1, (int) $value);
            break;
        case '--url':
            $url = rtrim((string) $value, '/');
            break;
        case '--interval':
            $interval = max(1, (int) $value);
            break;
        case '--cycles':
            $cycles = max(1, (int) $value);
            break;
    }
}

$token = config('hydro.device_token');
if (!$token) {
    fwrite(STDERR, "Token perangkat kosong. Set IOT_DEVICE_TOKEN di .env terlebih dahulu.\n");
    exit(1);
}

// Batas aman dari satu sumber kebenaran (sama dipakai controller)
$limits = config('hydro.limits');
$th = config('hydro.thresholds');

function clamp($value, $min, $max)
{
    return min($max, max($min, $value));
}

/**
 * Hitung status relay persis seperti fallback controller/sketch:
 * pompa pH     ON bila pH < batas "pH_on_below"
 * pompa nutrisi ON bila TDS < batas "tds_on_below"
 * pendingin     ON bila suhu > batas "on_above"
 */
function relayStatuses(float $ph, int $tds, float $suhu, array $th): array
{
    return [
        'status_pompa_ph'   => $ph < $th['pump']['ph_on_below'] ? 'ON' : 'OFF',
        'status_pompa_tds'  => $tds < $th['pump']['tds_on_below'] ? 'ON' : 'OFF',
        'status_pendingin'  => $suhu > $th['cooler']['on_above'] ? 'ON' : 'OFF',
    ];
}

/**
 * Noise desimal acak dalam rentang ±range
 */
function noise(float $range): float
{
    return mt_rand(-1000, 1000) / 1000 * $range;
}

/**
 * Value sebenarnya backTujuan: satu titik data sintetis berbasis waktu.
 * Dipakai mode backfill — deterministik terhadap $t sehingga urutan historis
 * terasa alami: suhu mengikuti siklus siang-malam, TDS menurun lalu refill.
 */
function sampleAt(Carbon $t, array $th, array $limits): array
{
    $minutes = ($t->timestamp % 86400) / 60.0; // menit-dalam-sehari

    // Suhu: puncak sekitar tengah hari (amplitude 2.3°C, baseline 25.5)
    $suhu = 25.5 + 2.3 * sin(2 * M_PI * ($minutes / 1440.0) - M_PI / 2) + noise(0.35);

    // TDS: gelombang segitiga ~12 jam (dummy konsumsi & refill 560–840 PPM)
    $phase = fmod($minutes / 720.0, 2.0);
    $wave = $phase < 1.0 ? 1.0 - $phase : $phase - 1.0; // 1 -> 0 -> 1
    $tds = 560.0 + (840.0 - 560.0) * $wave + noise(9.0);

    // pH: gelombang pelan + sesekali dip lebih dalam (seperti asam berbaur)
    $ph = 6.05 + 0.3 * sin(2 * M_PI * ($minutes / 510.0)) + noise(0.12);
    if (mt_rand(0, 99) < 8) {
        $ph -= 0.5; // episode asam sesekali -> pompa pH sempat ON
    }

    $ph = clamp($ph, $limits['ph']['min'] + 0.1, $limits['ph']['max'] - 0.1);
    $tds = clamp($tds, $limits['tds']['min'] + 10, $limits['tds']['max'] - 10);
    $suhu = clamp($suhu, $limits['suhu']['min'] + 1, $limits['suhu']['max'] - 1);

    return [
        'ph' => round($ph, 2),
        'tds' => (int) round($tds),
        'suhu' => round($suhu, 2),
        'created_at' => $t->format('Y-m-d H:i:s'),
    ] + relayStatuses($ph, (int) round($tds), $suhu, $th);
}

// --------------------------------------------------------------------------
// MODE BACKFILL — seed riwayat N jam terakhir
// --------------------------------------------------------------------------
if ($backfill !== null) {
    echo "Menanam riwayat {$backfill} jam terakhir (interval 60 detik)...\n";

    $rows = [];
    $t = Carbon::now()->subHours($backfill);
    $end = Carbon::now();

    while ($t < $end) {
        $d = sampleAt($t, $th, $limits);
        $rows[] = [
            'ph' => $d['ph'], 'tds' => $d['tds'], 'suhu' => $d['suhu'],
            'status_pompa_ph' => $d['status_pompa_ph'],
            'status_pompa_tds' => $d['status_pompa_tds'],
            'status_pendingin' => $d['status_pendingin'],
            'created_at' => $d['created_at'], 'updated_at' => $d['created_at'],
        ];
        $t->addMinute();
    }

    // Insert per 200 baris agar hemat memori
    foreach (array_chunk($rows, 200) as $chunk) {
        DB::table('sensor_data')->insert($chunk);
    }

    $no = count($rows);
    echo "Selesai: {$no} baris demo ditambah.\n";
    echo 'Total baris: ' . SensorData::count() . "\n";
    exit(0);
}

// --------------------------------------------------------------------------
// MODE LIVE — kirim sampai berhenti (Ctrl+C)
// --------------------------------------------------------------------------
echo "Simulator ESP32 LIVE -> {$url}/api/terima-data (interval {$interval} detik, token aktif).\n";
echo "Tekan Ctrl+C untuk berhenti.\n\n";

// state acak dimulai dekat nilai baseline
$state = ['ph' => 6.05, 'tds' => 760.0, 'suhu' => 26.0];
$episode = null; // ['kind' => 'ph|tds|heat', 'sisa' => n]
$targets = ['ph' => 6.05, 'tds' => 760.0, 'suhu' => 26.0];
$sent = 0;
$failed = 0;
$cycle = 0;

while (true) {
    $cycle++;
    if ($cycle > 1) {
        sleep($interval);
    }
    if ($cycles !== null && $cycle > $cycles) {
        break;
    }

    // --- Roll episode acak (hanya bila tidak ada episode aktif)
    if ($episode === null && mt_rand(0, 999) < 14) { // ~1.4% per tick
        $pilihan = ['ph', 'tds', 'heat'][array_rand(['ph', 'tds', 'heat'])];
        $episode = ['kind' => $pilihan, 'sisa' => ['ph' => 22, 'tds' => 18, 'heat' => 70][$pilihan]];
    }

    // --- Atur target sesuai episode
    if ($episode !== null) {
        $episode['sisa']--;
        if ($episode['sisa'] <= 0) {
            $targets = ['ph' => 6.05, 'tds' => 760.0, 'suhu' => 26.0];
            $episode = null;
        } else {
            switch ($episode['kind']) {
                case 'ph':
                    $targets['ph'] = mt_rand(530, 543) / 100; // 5.30–5.43
                    break;
                case 'tds':
                    $targets['tds'] = (float) mt_rand(525, 552); // di bawah batas pompa
                    break;
                case 'heat':
                    $targets['suhu'] = mt_rand(3260, 3330) / 100; // 32.6–33.3
                    break;
            }
        }
    }

    // --- Gerakkan nilai menuju target + noise (random walk)
    $state['ph'] = clamp($state['ph'] + ($targets['ph'] - $state['ph']) * 0.12 + noise(0.045), 5.2, 6.8);
    $state['tds'] = clamp($state['tds'] + ($targets['tds'] - $state['tds']) * 0.10 + noise(7.0), 500.0, 850.0);
    $state['suhu'] = clamp($state['suhu'] + ($targets['suhu'] - $state['suhu']) * 0.08 + noise(0.16), 23.0, 33.6);

    $ph = round($state['ph'], 2);
    $tds = (int) round($state['tds']);
    $suhu = round($state['suhu'], 2);

    $payload = array_merge([
        'ph' => $ph,
        'tds' => $tds,
        'suhu' => $suhu,
    ], relayStatuses($state['ph'], (int) $state['tds'], $state['suhu'], $th));

    // --- Kirim persis seperti ESP32 (token via header)
    try {
        $response = Http::timeout(5)
            ->withHeaders(['X-Device-Token' => $token])
            ->asForm()
            ->post($url . '/api/terima-data', $payload);
    } catch (\Throwable $e) {
        $failed++;
        $waktu = now()->format('H:i:s');
        echo "[{$waktu}] GAGAL (percobaan #{$failed}): server tak terjangkau — {$e->getMessage()}\n";
        continue;
    }

    if ($response->successful()) {
        $sent++;
        $st = $payload;
        printf(
            "[%s] 201 ph=%.2f tds=%d suhu=%.2f  [pH:%s TDS:%s PND:%s]  (terkirim %d)\n",
            now()->format('H:i:s'),
            $ph,
            $tds,
            $suhu,
            $st['status_pompa_ph'],
            $st['status_pompa_tds'],
            $st['status_pendingin'],
            $sent
        );
    } else {
        $failed++;
        printf(
            "[%s] HTTP %d — %s\n",
            now()->format('H:i:s'),
            $response->status(),
            $response->body()
        );
    }
}

echo "\nSelesai. Terkirim: {$sent} baris, gagal: {$failed}.\n";
exit(0);
