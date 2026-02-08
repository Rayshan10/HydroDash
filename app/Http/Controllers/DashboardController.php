<?php

namespace App\Http\Controllers;

use App\Models\SensorData; // Menggunakan model SensorData sesuai kode Anda
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

    // --- FUNGSI BARU UNTUK AJAX REAL-TIME ---
    public function getLatest()
    {
        $latest = SensorData::latest()->first();

        if (!$latest) {
            return response()->json(['status' => 'empty'], 404);
        }

        // Mengembalikan data dalam format JSON untuk JavaScript
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
        $validator = Validator::make($request->all(), [
            'ph'   => 'required|numeric',
            'tds'  => 'required|numeric',
            'suhu' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error'], 400);
        }

        $data = new SensorData();
        $data->ph = $request->ph;
        $data->tds = $request->tds;
        $data->suhu = $request->suhu;

        // Logika Status Otomatis
        $data->status_pompa_ph = ($request->ph < 5.5) ? 'ON' : 'OFF';
        $data->status_pompa_tds = ($request->tds < 1050) ? 'ON' : 'OFF';
        $data->status_pendingin = ($request->suhu > 30.0) ? 'ON' : 'OFF';

        $data->save();

        return response()->json(['status' => 'success', 'message' => 'Data Saved'], 201);
    }
}