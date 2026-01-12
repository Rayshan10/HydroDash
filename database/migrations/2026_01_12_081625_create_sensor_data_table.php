<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            // Kolom Parameter Sensor
            $table->float('ph');           // Untuk menyimpan nilai pH (desimal)
            $table->integer('tds');        // Untuk menyimpan nilai TDS (PPM)
            $table->float('suhu');         // Untuk menyimpan nilai suhu

            // Kolom Status Relay/Pompa (Sebagai tanda di dashboard)
            $table->string('status_pompa_ph')->default('OFF');  // Status pompa pH Up
            $table->string('status_pompa_tds')->default('OFF'); // Status pompa Nutrisi
            $table->string('status_pendingin')->default('OFF'); // Status pompa Pendingin

            $table->timestamps(); // Mencatat created_at dan updated_at secara otomatis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
