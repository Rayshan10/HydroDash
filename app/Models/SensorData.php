<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    // Menentukan nama tabel (opsional jika nama tabel sudah sesuai jamak/plural)
    protected $table = 'sensor_data';

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignment).
     * Ini harus sesuai dengan kolom yang kita buat di Migration.
     */
    protected $fillable = [
        'ph',
        'tds',
        'suhu',
        'status_pompa_ph',
        'status_pompa_tds',
        'status_pendingin',
    ];

    /**
     * Opsional: Casting tipe data agar Laravel otomatis mengonversi 
     * angka dari database menjadi tipe float/integer di kode PHP.
     */
    protected $casts = [
        'ph' => 'float',
        'tds' => 'integer',
        'suhu' => 'float',
    ];
}