<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed ringan: beberapa baris hari ini & kemarin
        $today = now();
        $rows = [];
        foreach ([0, 1] as $dayOffset) {
            for ($h = 1; $h <= 3; $h++) {
                $t = $today->copy()->subDays($dayOffset)->startOfDay()->addHours($h);
                $rows[] = [
                    'ph' => 6.0 + $h, 'tds' => 600 + $h, 'suhu' => 25.0 + $h,
                    'status_pompa_ph' => 'OFF', 'status_pompa_tds' => 'OFF', 'status_pendingin' => 'OFF',
                    'created_at' => $t, 'updated_at' => $t,
                ];
            }
        }
        DB::table('sensor_data')->insert($rows);
    }

    public function test_keempat_jenis_laporan_tampil(): void
    {
        foreach (['daily', 'monthly', 'yearly', 'period'] as $type) {
            $response = $this->get('/report?type=' . $type);
            $response->assertOk()->assertSee('HydroDash Laporan');
        }
    }

    public function test_laporan_kosong_tetap_ditangani_baik(): void
    {
        DB::table('sensor_data')->delete();

        $this->get('/report?type=daily')
            ->assertOk()
            ->assertSee('Tidak ada data');
    }

    public function test_export_csv_menghasilkan_header_kolom(): void
    {
        $response = $this->get('/report/export?type=daily');
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');

        // CSV dikirim via response()->stream() — isi baris baru bisa diambil
        // dengan memancarkan konten stream secara langsung
        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('Waktu,', $csv);
        $this->assertStringContainsString('Pompa pH', $csv);
        $this->assertStringContainsString('Suhu (°C)', $csv);
    }

    public function test_pdf_diharapakan_dihasilkan(): void
    {
        $response = $this->get('/report/pdf?type=daily');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
