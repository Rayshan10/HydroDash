<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semua query laporan & prune memfilter/mengelompokkan pada `created_at`.
     * Tanpa index, tabel besar (±6 juta baris/tahun) akan lambat.
     */
    public function up(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
