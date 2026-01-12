<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil 10 data terbaru
        $logs = SensorData::latest()->take(10)->get();
        
        // Ambil data paling terakhir untuk widget
        $latest = SensorData::latest()->first();

        return view('dashboard', compact('logs', 'latest'));
    }
}
