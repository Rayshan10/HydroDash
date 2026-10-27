<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_batas_rate_api_aktif_dan_bisa_dikendalikan_config(): void
    {
        // Turunkan ambang ke 1 permintaan/menit khusus untuk pengujian
        config(['hydro.api_rate_limit' => 1]);

        // Permintaan pertama lolos throttle (data kosong -> controller balas 404)
        $this->get('/api/get-latest-hydro')->assertStatus(404);

        // Permintaan kedua dalam menit yang sama -> kekunci 429
        $this->post('/api/terima-data', ['ph' => 6, 'tds' => 600, 'suhu' => 25])
            ->assertStatus(429);
    }
}
