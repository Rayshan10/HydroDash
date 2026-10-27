<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PruneCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_nilai_days_kurang_dari_1_ditolak(): void
    {
        $this->artisan('sensors:prune', ['--days' => 0])
            ->expectsOutputToContain('Nilai --days minimal 1 hari')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_menghapus_hanya_data_lebih_tua_dari_retensi_dan_idempoten(): void
    {
        $base = ['ph' => 6.5, 'tds' => 600, 'suhu' => 25.0,
            'status_pompa_ph' => 'OFF', 'status_pompa_tds' => 'OFF', 'status_pendingin' => 'OFF'];

        // 1 baris umur 366 hari (layak hapus) + 1 baris segar (harus selamat)
        DB::table('sensor_data')->insert(array_merge($base, [
            'created_at' => now()->subYear()->subDays(1),
            'updated_at' => now()->subYear()->subDays(1),
        ]));
        DB::table('sensor_data')->insert(array_merge($base, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $this->assertDatabaseCount('sensor_data', 2);

        $this->artisan('sensors:prune') // default: config retention 365 hari
            ->expectsOutputToContain('1 baris')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount('sensor_data', 1);

        // Jalan kedua harus bersih & tidak berubah apa pun (idempoten)
        $this->artisan('sensors:prune')
            ->expectsOutputToContain('Bersih')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount('sensor_data', 1);
    }
}
