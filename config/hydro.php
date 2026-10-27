<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Monitoring HydroDash
    |--------------------------------------------------------------------------
    |
    | Pusat konfigurasi sistem monitoring. Nilai batas ambang (threshold)
    | akan dipindahkan ke sini pada fase berikutnya agar tidak tersebar
    | di beberapa tempat dalam kode.
    |
    */

    // Token rahasia untuk autentikasi perangkat IoT (ESP32).
    // Dikirim lewat header `X-Device-Token` atau field `token`; jika kosong,
    // seluruh endpoint penerimaan data terkunci otomatis (fail-safe).
    'device_token' => env('IOT_DEVICE_TOKEN'),

];
