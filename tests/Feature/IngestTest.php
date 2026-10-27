<?php

namespace Tests\Feature;

use App\Models\SensorData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngestTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'hd_test_token_unit';

    protected function setUp(): void
    {
        parent::setUp();
        // Token config ditimpa agar uji tidak menyentuh .env
        config(['hydro.device_token' => self::TOKEN]);
    }

    public function test_menolak_permintaan_tanpa_token(): void
    {
        $this->post('/api/terima-data', ['ph' => 6, 'tds' => 600, 'suhu' => 25])
            ->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    public function test_menolak_token_salah(): void
    {
        $this->post('/api/terima-data', ['ph' => 6, 'tds' => 600, 'suhu' => 25], [
            'X-Device-Token' => 'hd_token_salah',
        ])->assertStatus(401);
    }

    public function test_gagal_tutup_bila_config_token_kosong(): void
    {
        config(['hydro.device_token' => null]);

        // Meski client tetap mengirim token, server menolak semua
        $this->post('/api/terima-data', ['ph' => 6, 'tds' => 600, 'suhu' => 25], [
            'X-Device-Token' => self::TOKEN,
        ])->assertStatus(401);
    }

    public function test_menerima_data_via_header_token(): void
    {
        $this->post('/api/terima-data', ['ph' => 6.02, 'tds' => 442, 'suhu' => 24.4], [
            'X-Device-Token' => self::TOKEN,
        ])->assertStatus(201);

        $this->assertDatabaseCount('sensor_data', 1);
    }

    public function test_menerima_data_via_field_body_token(): void
    {
        $this->post('/api/terima-data', [
            'ph' => 6.02, 'tds' => 442, 'suhu' => 24.4, 'token' => self::TOKEN,
        ])->assertStatus(201);
    }

    public function test_pesan_validasi_tetap_dalam_bahasa_indonesia(): void
    {
        // Kontrak untuk ESP32: 400 + {status: error, errors: {...}}
        $this->post('/api/terima-data', ['ph' => 99, 'tds' => 442, 'suhu' => 24.4], [
            'X-Device-Token' => self::TOKEN,
        ])->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('errors.ph.0', 'Kolom pH harus di antara 0 dan 14.');
    }

    public function test_status_relay_fallback_dihitung_dari_threshold_config(): void
    {
        // ph 5.0 < 5.5 -> pompa pH ON; tds 500 < 560 -> pompa nutrisi ON; suhu 33 > 32 -> pendingin ON
        $this->post('/api/terima-data', ['ph' => 5.0, 'tds' => 500, 'suhu' => 33], [
            'X-Device-Token' => self::TOKEN,
        ])->assertStatus(201);

        $row = SensorData::latest('id')->first();

        $this->assertSame('ON', $row->status_pompa_ph);
        $this->assertSame('ON', $row->status_pompa_tds);
        $this->assertSame('ON', $row->status_pendingin);
    }

    public function test_status_relay_dari_esp32_mendapat_prioritas(): void
    {
        $this->post('/api/terima-data', [
            'ph' => 5.0, 'tds' => 500, 'suhu' => 33,
            'status_pompa_ph' => 'OFF', 'status_pompa_tds' => 'OFF', 'status_pendingin' => 'OFF',
        ], ['X-Device-Token' => self::TOKEN])->assertStatus(201);

        $row = SensorData::latest('id')->first();

        $this->assertSame('OFF', $row->status_pompa_ph);
        $this->assertSame('OFF', $row->status_pompa_tds);
        $this->assertSame('OFF', $row->status_pendingin);
    }
}
