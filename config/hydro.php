<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Monitoring HydroDash
    |--------------------------------------------------------------------------
    |
    | Pusat konfigurasi sistem monitoring: satu sumber kebenaran agar
    | controller, JavaScript dashboard, dan grafik tidak lagi menuliskan
    | angka ambang batas yang sama di beberapa tempat.
    |
    */

    // Token rahasia untuk autentikasi perangkat IoT (ESP32).
    // Dikirim lewat header `X-Device-Token` atau field `token`; jika kosong,
    // seluruh endpoint penerimaan data terkunci otomatis (fail-safe).
    'device_token' => env('IOT_DEVICE_TOKEN'),

    // Batas wajar input sensor (validasi endpoint /api/terima-data).
    // Nilai di luar rentang ini ditolak (sensor rusak/kabel lepas).
    'limits' => [
        'ph' => ['min' => 0, 'max' => 14],
        'tds' => ['min' => 0, 'max' => 5000],
        'suhu' => ['min' => -10, 'max' => 60],
    ],

    // Ambang batas ideal hidroponik — dipakai di controller (fallback relay),
    // JavaScript dashboard (alert & label aksi grafik), sekaligus satu acuan UI.
    'thresholds' => [
        'ph' => ['min' => 5.5, 'max' => 6.5],
        'tds' => ['min' => 560, 'max' => 840],

        // Fallback status relay bila ESP32 tidak mengirimkan status masing-masing
        'pump' => [
            'ph_on_below' => 5.5,   // pH di bawah nilai ini -> pompa pH dianggap ON
            'tds_on_below' => 560,  // TDS di bawah nilai ini -> pompa nutrisi dianggap ON
        ],
        'cooler' => [
            'on_above' => 32.0,     // suhu di atas nilai ini -> pendingin dianggap ON
        ],
    ],

    // Data dianggap "tertahan/offline" bila usia data terakhir melebihi X detik
    // (ESP32 mengirim tiap ±5 detik, jadi 120 detik cukup aman).
    'stale_after_seconds' => 120,

    // Batas rate endpoint grup `api` (permintaan/menit per IP). Polling dashboard
    // ±60/menit + ESP32 ±12/menit aman di bawah 240; berlindung dari spam.
    'api_rate_limit' => 240,

    // Pemeliharaan database: perintah `sensors:prune` menghapus data lebih tua
    // dari X hari (dijadwalkan harian di routes/console.php).
    'prune' => [
        'retention_days' => 365,
    ],
];
