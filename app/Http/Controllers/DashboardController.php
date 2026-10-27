<?php

namespace App\Http\Controllers;

use App\Models\SensorData; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil 20 data terbaru untuk grafik
        $logs = SensorData::orderBy('created_at', 'desc')->take(20)->get()->reverse();
        $latest = SensorData::latest()->first();

        return view('dashboard', compact('logs', 'latest'));
    }

    // --- FUNGSI UNTUK AJAX REAL-TIME ---
    public function getLatest()
    {
        $latest = SensorData::latest()->first();

        if (!$latest) {
            return response()->json(['status' => 'empty'], 404);
        }

        return response()->json([
            'ph'    => number_format($latest->ph, 2),
            'tds'   => $latest->tds,
            'suhu'  => number_format($latest->suhu, 2),
            'status_pompa_ph'  => $latest->status_pompa_ph,
            'status_pompa_tds' => $latest->status_pompa_tds,
            'status_pendingin' => $latest->status_pendingin,
            'time'  => Carbon::parse($latest->created_at)->timezone('Asia/Jakarta')->format('H:i:s')
        ]);
    }

    public function store(Request $request)
    {
        // 0. Autentikasi perangkat: hanya IoT (ESP32) dengan token sah yang boleh kirim data.
        //    Token bisa dikirim via header `X-Device-Token` atau field `token` (memudahkan sketch ESP32).
        $token = $request->header('X-Device-Token') ?? $request->input('token');
        $expectedToken = config('hydro.device_token');

        if (!$expectedToken || !is_string($token) || !hash_equals($expectedToken, $token)) {
            return response()->json(['status' => 'error', 'message' => 'Perangkat tidak sah: token tidak valid'], 401);
        }

        // 1. Validasi Input (lengkap dengan batas wajar agar data sensor rusak tertolak)
        $validator = Validator::make($request->all(), [
            'ph'   => 'required|numeric|min:0|max:14',
            'tds'  => 'required|numeric|min:0|max:5000',
            'suhu' => 'required|numeric|min:-10|max:60',
            'status_pompa_ph'   => 'nullable|in:ON,OFF',
            'status_pompa_tds'  => 'nullable|in:ON,OFF',
            'status_pendingin'  => 'nullable|in:ON,OFF',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
        }

        $data = new SensorData();
        $data->ph = $request->ph;
        $data->tds = $request->tds;
        $data->suhu = $request->suhu;

        // 2. Logika Sinkronisasi Status (Prioritas Kiriman ESP32)
        // Jika ESP32 mengirim status, gunakan itu. Jika tidak, hitung otomatis berdasarkan ambang batas baru.
        $data->status_pompa_ph = $request->status_pompa_ph ?? (($request->ph < 5.5) ? 'ON' : 'OFF');
        $data->status_pompa_tds = $request->status_pompa_tds ?? (($request->tds < 560) ? 'ON' : 'OFF');
        $data->status_pendingin = $request->status_pendingin ?? (($request->suhu > 32.0) ? 'ON' : 'OFF');

        $data->save();

        return response()->json(['status' => 'success', 'message' => 'Data Terkirim & Sinkron'], 201);
    }
}