<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Tampilkan laporan unified (harian, bulanan, tahunan, periode)
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'daily'); // daily, monthly, yearly, period
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $year = $request->input('year', Carbon::now()->year);
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = collect();
        $stats = [];
        $logs = collect();
        $displayData = [];
        $chartData = [
            'labels' => [],
            'suhu' => [],
            'ph' => [],
            'tds' => [],
        ];

        switch ($type) {
            case 'daily':
                $dateObj = Carbon::createFromFormat('Y-m-d', $date);
                $data = SensorData::whereDate('created_at', $dateObj)
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);
                $logs = $data->take(50);
                $displayData = [
                    'title' => 'Laporan Harian',
                    'subtitle' => $dateObj->format('d MMMM Y'),
                    'date' => $dateObj,
                ];

                // Chart data untuk daily - gunakan semua data (sampai 50 records)
                foreach ($data->take(50) as $item) {
                    $chartData['labels'][] = $item->created_at->setTimezone('Asia/Jakarta')->format('H:i');
                    $chartData['suhu'][] = $item->suhu;
                    $chartData['ph'][] = $item->ph;
                    $chartData['tds'][] = $item->tds;
                }
                break;

            case 'monthly':
                list($yearVal, $monthVal) = explode('-', $month);
                $startDateObj = Carbon::createFromDate($yearVal, $monthVal, 1)->startOfMonth();
                $endDateObj = Carbon::createFromDate($yearVal, $monthVal, 1)->endOfMonth();

                $allData = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Filtered data - 1 per hari untuk display dan stats
                $data = collect([]);
                $chartData = ['labels' => [], 'suhu' => [], 'ph' => [], 'tds' => []];
                for ($i = 1; $i <= $endDateObj->day; $i++) {
                    $dayDate = $startDateObj->copy()->addDays($i - 1);
                    $dayData = SensorData::whereDate('created_at', $dayDate)->orderBy('created_at', 'desc')->first();
                    if ($dayData) {
                        $data->push($dayData);
                        $chartData['labels'][] = $dayData->created_at->setTimezone('Asia/Jakarta')->format('d M');
                        $chartData['suhu'][] = $dayData->suhu;
                        $chartData['ph'][] = $dayData->ph;
                        $chartData['tds'][] = $dayData->tds;
                    }
                }
                
                $stats = $this->calculateStats($allData); // Stats dari semua data untuk akurasi
                
                // Averaged data per hari untuk data table
                $logs = collect([]);
                for ($i = 1; $i <= $endDateObj->day; $i++) {
                    $dayDate = $startDateObj->copy()->addDays($i - 1);
                    $dayReadings = SensorData::whereDate('created_at', $dayDate)->get();
                    if ($dayReadings->count() > 0) {
                        $lastReading = $dayReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $dayDate->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($dayReadings->avg('suhu'), 2);
                        $avgReading->ph = round($dayReadings->avg('ph'), 2);
                        $avgReading->tds = round($dayReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $logs->push($avgReading);
                    }
                }

                $displayData = [
                    'title' => 'Laporan Bulanan',
                    'subtitle' => $startDateObj->format('F Y'),
                    'month' => $month,
                ];
                break;

            case 'yearly':
                $startDateObj = Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDateObj = Carbon::createFromDate($year, 12, 31)->endOfYear();

                $allData = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                // Filtered data - 1 per bulan untuk display
                $data = collect([]);
                $chartData = ['labels' => [], 'suhu' => [], 'ph' => [], 'tds' => []];
                for ($m = 1; $m <= 12; $m++) {
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                    $monthData = SensorData::whereBetween('created_at', [$monthStart, $monthEnd])->orderBy('created_at', 'desc')->first();
                    if ($monthData) {
                        $data->push($monthData);
                        $chartData['labels'][] = $monthData->created_at->setTimezone('Asia/Jakarta')->format('M');
                        $chartData['suhu'][] = $monthData->suhu;
                        $chartData['ph'][] = $monthData->ph;
                        $chartData['tds'][] = $monthData->tds;
                    }
                }
                
                $stats = $this->calculateStats($allData); // Stats dari semua data untuk akurasi
                
                // Averaged data per bulan untuk data table
                $logs = collect([]);
                for ($m = 1; $m <= 12; $m++) {
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                    $monthReadings = SensorData::whereBetween('created_at', [$monthStart, $monthEnd])->get();
                    if ($monthReadings->count() > 0) {
                        $lastReading = $monthReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $monthStart->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($monthReadings->avg('suhu'), 2);
                        $avgReading->ph = round($monthReadings->avg('ph'), 2);
                        $avgReading->tds = round($monthReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $logs->push($avgReading);
                    }
                }

                $displayData = [
                    'title' => 'Laporan Tahunan',
                    'subtitle' => "Tahun {$year}",
                    'year' => $year,
                ];
                break;

            case 'period':
                $startDateObj = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDateObj = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

                if ($startDateObj > $endDateObj) {
                    return redirect()->back()->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                }

                $data = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);

                $displayData = [
                    'title' => 'Laporan Periode',
                    'subtitle' => $startDateObj->format('d M Y') . ' - ' . $endDateObj->format('d M Y'),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];

                // Determine sampling interval based on date range
                $daysDiff = $startDateObj->diffInDays($endDateObj);
                if ($daysDiff < 30) {
                    $interval = 1; // 1 per hari
                } elseif ($daysDiff <= 90) {
                    $interval = 3; // 1 per 3 hari
                } else {
                    $interval = 7; // 1 per minggu
                }

                // Chart data untuk period - dinamis berdasarkan interval
                $chartData = ['labels' => [], 'suhu' => [], 'ph' => [], 'tds' => []];
                $currentDate = $startDateObj->copy()->startOfDay();
                while ($currentDate <= $endDateObj) {
                    $dayData = SensorData::whereDate('created_at', $currentDate)->orderBy('created_at', 'desc')->first();
                    if ($dayData) {
                        $chartData['labels'][] = $dayData->created_at->setTimezone('Asia/Jakarta')->format('d M');
                        $chartData['suhu'][] = $dayData->suhu;
                        $chartData['ph'][] = $dayData->ph;
                        $chartData['tds'][] = $dayData->tds;
                    }
                    $currentDate->addDays($interval);
                }

                // Prepare $logs for data table - averaged data dengan interval sampling
                $logs = collect([]);
                $currentDate = $startDateObj->copy()->startOfDay();
                while ($currentDate <= $endDateObj) {
                    $periodEnd = $currentDate->copy()->addDays($interval)->endOfDay();
                    $periodReadings = SensorData::whereBetween('created_at', [$currentDate->copy()->startOfDay(), $periodEnd])->get();
                    if ($periodReadings->count() > 0) {
                        $lastReading = $periodReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $currentDate->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($periodReadings->avg('suhu'), 2);
                        $avgReading->ph = round($periodReadings->avg('ph'), 2);
                        $avgReading->tds = round($periodReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $logs->push($avgReading);
                    }
                    $currentDate->addDays($interval);
                }
                break;
        }

        return view('reports.index', compact('type', 'data', 'stats', 'logs', 'displayData', 'chartData', 'date', 'month', 'year', 'startDate', 'endDate'));
    }

    /**
     * Tampilkan laporan bulanan
     */
    public function monthly(Request $request)
    {
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));
        list($year, $month) = explode('-', $yearMonth);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Ambil data untuk bulan tertentu
        $allData = SensorData::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // Hitung statistik
        $stats = $this->calculateStats($allData);

        // Data harian untuk grafik - SAMPLING 1 per hari
        $dailyStats = [];
        $chartData = [];
        for ($i = 1; $i <= $endDate->day; $i++) {
            $dayDate = $startDate->copy()->addDays($i - 1);
            $dayData = SensorData::whereDate('created_at', $dayDate)->orderBy('created_at', 'desc')->get();
            if ($dayData->isNotEmpty()) {
                $dailyStats[$i] = $this->calculateStats($dayData);
                // Ambil 1 data terakhir per hari untuk grafik
                $lastData = $dayData->first();
                $chartData[] = [
                    'label' => $dayDate->format('d M'),
                    'ph' => $lastData->ph,
                    'suhu' => $lastData->suhu,
                    'tds' => $lastData->tds,
                ];
            }
        }

        // Data untuk grafik
        $logs = collect($chartData);

        return view('reports.monthly', compact('allData', 'stats', 'dailyStats', 'logs', 'yearMonth', 'startDate'));
    }

    /**
     * Tampilkan laporan tahunan
     */
    public function yearly(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();

        // Ambil data untuk tahun tertentu
        $allData = SensorData::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // Hitung statistik
        $stats = $this->calculateStats($allData);

        // Data bulanan untuk grafik - SAMPLING 1 per bulan
        $monthlyStats = [];
        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();
            $monthData = SensorData::whereBetween('created_at', [$monthStart, $monthEnd])->orderBy('created_at', 'desc')->get();
            if ($monthData->isNotEmpty()) {
                $monthlyStats[$m] = $this->calculateStats($monthData);
                // Ambil 1 data terakhir per bulan untuk grafik
                $lastData = $monthData->first();
                $chartData[] = [
                    'label' => $monthStart->format('M'),
                    'ph' => $lastData->ph,
                    'suhu' => $lastData->suhu,
                    'tds' => $lastData->tds,
                ];
            }
        }

        // Data untuk grafik
        $logs = collect($chartData);

        return view('reports.yearly', compact('allData', 'stats', 'monthlyStats', 'logs', 'year', 'startDate'));
    }

    /**
     * Tampilkan laporan periode (filter custom date range)
     */
    public function period(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        // Validasi range
        if ($startDate > $endDate) {
            return redirect()->back()->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
        }

        // Ambil data untuk periode tertentu
        $allData = SensorData::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        // Hitung statistik
        $stats = $this->calculateStats($allData);

        // Hitung jumlah hari
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Sampling untuk grafik: 1 data per hari
        $chartData = [];
        $currentDate = $startDate->copy()->startOfDay();
        while ($currentDate <= $endDate) {
            $dayData = SensorData::whereDate('created_at', $currentDate)->orderBy('created_at', 'desc')->first();
            if ($dayData) {
                $chartData[] = [
                    'label' => $currentDate->format('d M'),
                    'ph' => $dayData->ph,
                    'suhu' => $dayData->suhu,
                    'tds' => $dayData->tds,
                ];
            }
            $currentDate->addDay();
        }

        $logs = collect($chartData);

        return view('reports.period', compact('allData', 'stats', 'logs', 'startDate', 'endDate', 'totalDays'));
    }

    /**
     * Export laporan ke CSV berdasarkan type
     */
    public function exportReport(Request $request)
    {
        $type = $request->input('type', 'daily');
        $data = collect();

        switch ($type) {
            case 'daily':
                $date = $request->input('date', Carbon::now()->format('Y-m-d'));
                $data = SensorData::whereDate('created_at', $date)
                    ->orderBy('created_at', 'asc')
                    ->get();
                $filename = "laporan_harian_{$date}.csv";
                break;

            case 'monthly':
                $month = $request->input('month', Carbon::now()->format('Y-m'));
                list($year, $monthVal) = explode('-', $month);
                $startDate = Carbon::createFromDate($year, $monthVal, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $monthVal, 1)->endOfMonth();
                
                // Averaged data per hari
                $data = collect();
                for ($i = 1; $i <= $endDate->day; $i++) {
                    $dayDate = $startDate->copy()->addDays($i - 1);
                    $dayReadings = SensorData::whereDate('created_at', $dayDate)->get();
                    if ($dayReadings->count() > 0) {
                        $lastReading = $dayReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $dayDate->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($dayReadings->avg('suhu'), 2);
                        $avgReading->ph = round($dayReadings->avg('ph'), 2);
                        $avgReading->tds = round($dayReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $data->push($avgReading);
                    }
                }
                $filename = "laporan_bulanan_{$month}.csv";
                break;

            case 'yearly':
                $year = $request->input('year', Carbon::now()->year);
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
                
                // Averaged data per bulan
                $data = collect();
                for ($m = 1; $m <= 12; $m++) {
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                    $monthReadings = SensorData::whereBetween('created_at', [$monthStart, $monthEnd])->get();
                    if ($monthReadings->count() > 0) {
                        $lastReading = $monthReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $monthStart->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($monthReadings->avg('suhu'), 2);
                        $avgReading->ph = round($monthReadings->avg('ph'), 2);
                        $avgReading->tds = round($monthReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $data->push($avgReading);
                    }
                }
                $filename = "laporan_tahunan_{$year}.csv";
                break;

            case 'period':
                $startDateStr = $request->input('start_date');
                $endDateStr = $request->input('end_date');
                $startDate = Carbon::createFromFormat('Y-m-d', $startDateStr)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDateStr)->endOfDay();
                
                // Determine sampling interval
                $daysDiff = $startDate->diffInDays($endDate);
                if ($daysDiff < 30) {
                    $interval = 1; // 1 per hari
                } elseif ($daysDiff <= 90) {
                    $interval = 3; // 1 per 3 hari
                } else {
                    $interval = 7; // 1 per minggu
                }
                
                // Averaged data dengan interval sampling
                $data = collect();
                $currentDate = $startDate->copy()->startOfDay();
                while ($currentDate <= $endDate) {
                    $periodEnd = $currentDate->copy()->addDays($interval)->endOfDay();
                    $periodReadings = SensorData::whereBetween('created_at', [$currentDate->copy()->startOfDay(), $periodEnd])->get();
                    if ($periodReadings->count() > 0) {
                        $lastReading = $periodReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $currentDate->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($periodReadings->avg('suhu'), 2);
                        $avgReading->ph = round($periodReadings->avg('ph'), 2);
                        $avgReading->tds = round($periodReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $data->push($avgReading);
                    }
                    $currentDate->addDays($interval);
                }
                $filename = "laporan_periode_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.csv";
                break;
        }

        return $this->exportToCsv($data, $filename);
    }

    /**
     * Export laporan ke PDF berdasarkan type
     */
    public function pdfReport(Request $request)
    {
        $type = $request->input('type', 'daily');
        $data = collect();
        $stats = [];

        switch ($type) {
            case 'daily':
                $date = $request->input('date', Carbon::now()->format('Y-m-d'));
                $dateObj = Carbon::createFromFormat('Y-m-d', $date);
                $data = SensorData::whereDate('created_at', $dateObj)
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);
                $pdf = Pdf::loadView('reports.pdf.daily', ['data' => $data, 'stats' => $stats, 'date' => $dateObj]);
                return $pdf->download("laporan_harian_{$date}.pdf");
                break;

            case 'monthly':
                $month = $request->input('month', Carbon::now()->format('Y-m'));
                list($year, $monthVal) = explode('-', $month);
                $startDate = Carbon::createFromDate($year, $monthVal, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $monthVal, 1)->endOfMonth();
                $allData = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($allData);
                
                // Averaged data per hari
                $data = collect();
                for ($i = 1; $i <= $endDate->day; $i++) {
                    $dayDate = $startDate->copy()->addDays($i - 1);
                    $dayReadings = SensorData::whereDate('created_at', $dayDate)->get();
                    if ($dayReadings->count() > 0) {
                        $lastReading = $dayReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $dayDate->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($dayReadings->avg('suhu'), 2);
                        $avgReading->ph = round($dayReadings->avg('ph'), 2);
                        $avgReading->tds = round($dayReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $data->push($avgReading);
                    }
                }
                
                $pdf = Pdf::loadView('reports.pdf.monthly', compact('data', 'stats', 'startDate', 'endDate'));
                return $pdf->download("laporan_bulanan_{$month}.pdf");
                break;

            case 'yearly':
                $year = $request->input('year', Carbon::now()->year);
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
                $allData = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($allData);
                
                // Averaged data per bulan
                $data = collect();
                for ($m = 1; $m <= 12; $m++) {
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                    $monthReadings = SensorData::whereBetween('created_at', [$monthStart, $monthEnd])->get();
                    if ($monthReadings->count() > 0) {
                        $lastReading = $monthReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $monthStart->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($monthReadings->avg('suhu'), 2);
                        $avgReading->ph = round($monthReadings->avg('ph'), 2);
                        $avgReading->tds = round($monthReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $data->push($avgReading);
                    }
                }
                
                $pdf = Pdf::loadView('reports.pdf.yearly', compact('data', 'stats', 'year'));
                return $pdf->download("laporan_tahunan_{$year}.pdf");
                break;

            case 'period':
                $startDateStr = $request->input('start_date');
                $endDateStr = $request->input('end_date');
                $startDateObj = Carbon::createFromFormat('Y-m-d', $startDateStr)->startOfDay();
                $endDateObj = Carbon::createFromFormat('Y-m-d', $endDateStr)->endOfDay();
                $allData = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($allData);
                
                // Determine sampling interval based on date range
                $daysDiff = $startDateObj->diffInDays($endDateObj);
                if ($daysDiff < 30) {
                    $interval = 1; // 1 per hari
                } elseif ($daysDiff <= 90) {
                    $interval = 3; // 1 per 3 hari
                } else {
                    $interval = 7; // 1 per minggu
                }
                
                // Averaged data untuk PDF dengan interval sampling
                $data = collect();
                $currentDate = $startDateObj->copy()->startOfDay();
                while ($currentDate <= $endDateObj) {
                    $periodEnd = $currentDate->copy()->addDays($interval)->endOfDay();
                    $periodReadings = SensorData::whereBetween('created_at', [$currentDate->copy()->startOfDay(), $periodEnd])->get();
                    if ($periodReadings->count() > 0) {
                        $lastReading = $periodReadings->last();
                        $avgReading = new \stdClass();
                        $avgReading->created_at = $currentDate->copy()->setTime(12, 0, 0);
                        $avgReading->suhu = round($periodReadings->avg('suhu'), 2);
                        $avgReading->ph = round($periodReadings->avg('ph'), 2);
                        $avgReading->tds = round($periodReadings->avg('tds'), 2);
                        $avgReading->status_pompa_ph = $lastReading->status_pompa_ph ? 1 : 0;
                        $avgReading->status_pompa_tds = $lastReading->status_pompa_tds ? 1 : 0;
                        $avgReading->status_pendingin = $lastReading->status_pendingin ? 1 : 0;
                        $data->push($avgReading);
                    }
                    $currentDate->addDays($interval);
                }
                
                $pdf = Pdf::loadView('reports.pdf.period', ['data' => $data, 'stats' => $stats, 'startDate' => $startDateObj, 'endDate' => $endDateObj]);
                return $pdf->download("laporan_periode_{$startDateStr}_{$endDateStr}.pdf");
                break;
        }
    }

    /**
     * Export laporan harian ke CSV
     */
    public function exportDaily(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $data = SensorData::whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->exportToCsv($data, "laporan_harian_{$date}.csv");
    }

    /**
     * Export laporan bulanan ke CSV
     */
    public function exportMonthly(Request $request)
    {
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));
        list($year, $month) = explode('-', $yearMonth);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->exportToCsv($data, "laporan_bulanan_{$yearMonth}.csv");
    }

    /**
     * Export laporan tahunan ke CSV
     */
    public function exportYearly(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();

        $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->exportToCsv($data, "laporan_tahunan_{$year}.csv");
    }

    /**
     * Export laporan periode ke CSV
     */
    public function exportPeriod(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->exportToCsv($data, "laporan_periode_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.csv");
    }

    /**
     * Helper: Hitung statistik dari dataset
     */
    private function calculateStats($data)
    {
        if ($data->isEmpty()) {
            return [
                'avg_ph' => 0,
                'min_ph' => 0,
                'max_ph' => 0,
                'avg_tds' => 0,
                'min_tds' => 0,
                'max_tds' => 0,
                'avg_suhu' => 0,
                'min_suhu' => 0,
                'max_suhu' => 0,
                'pompa_ph_on' => 0,
                'pompa_tds_on' => 0,
                'pendingin_on' => 0,
                'total_records' => 0,
            ];
        }

        return [
            'avg_ph' => round($data->avg('ph'), 2),
            'min_ph' => round($data->min('ph'), 2),
            'max_ph' => round($data->max('ph'), 2),
            'avg_tds' => round($data->avg('tds'), 2),
            'min_tds' => $data->min('tds'),
            'max_tds' => $data->max('tds'),
            'avg_suhu' => round($data->avg('suhu'), 2),
            'min_suhu' => round($data->min('suhu'), 2),
            'max_suhu' => round($data->max('suhu'), 2),
            'pompa_ph_on' => $data->where('status_pompa_ph', 'ON')->count(),
            'pompa_tds_on' => $data->where('status_pompa_tds', 'ON')->count(),
            'pendingin_on' => $data->where('status_pendingin', 'ON')->count(),
            'total_records' => $data->count(),
        ];
    }

    /**
     * Helper: Export ke CSV
     */
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, ['Waktu', 'Suhu (°C)', 'pH', 'TDS (PPM)', 'Pompa pH', 'Pompa TDS', 'Pendingin']);

            // Data CSV
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    $row->suhu,
                    $row->ph,
                    $row->tds,
                    $row->status_pompa_ph,
                    $row->status_pompa_tds,
                    $row->status_pendingin,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

