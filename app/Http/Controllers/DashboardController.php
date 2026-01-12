<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil 20 data terbaru untuk grafik
        $logs = SensorData::orderBy('created_at', 'desc')->take(20)->get()->reverse();
        $latest = SensorData::latest()->first();

        return view('dashboard', compact('logs', 'latest'));
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

        // Logika Status untuk Web
        $data->status_pompa_ph = ($request->ph < 5.5) ? 'ON' : 'OFF';
        $data->status_pompa_tds = ($request->tds < 1050) ? 'ON' : 'OFF';
        $data->status_pendingin = ($request->suhu > 30.0) ? 'ON' : 'OFF';

        $data->save();

        return response()->json(['status' => 'success', 'message' => 'Data Saved'], 201);
    }
}
