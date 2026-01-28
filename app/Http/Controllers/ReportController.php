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

                $data = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);

                $displayData = [
                    'title' => 'Laporan Bulanan',
                    'subtitle' => $startDateObj->format('F Y'),
                    'month' => $month,
                ];

                // Chart data untuk monthly - 1 per hari (last record of the day)
                for ($i = 1; $i <= $endDateObj->day; $i++) {
                    $dayDate = $startDateObj->copy()->addDays($i - 1);
                    $dayData = SensorData::whereDate('created_at', $dayDate)->orderBy('created_at', 'desc')->first();
                    if ($dayData) {
                        $chartData['labels'][] = $dayData->created_at->setTimezone('Asia/Jakarta')->format('d M');
                        $chartData['suhu'][] = $dayData->suhu;
                        $chartData['ph'][] = $dayData->ph;
                        $chartData['tds'][] = $dayData->tds;
                    }
                }

                // Prepare $logs for data table
                $logs = collect([]);
                for ($i = 1; $i <= $endDateObj->day; $i++) {
                    $dayDate = $startDateObj->copy()->addDays($i - 1);
                    $dayData = SensorData::whereDate('created_at', $dayDate)->orderBy('created_at', 'desc')->first();
                    if ($dayData) {
                        $logs->push($dayData);
                    }
                }
                break;

            case 'yearly':
                $startDateObj = Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDateObj = Carbon::createFromDate($year, 12, 31)->endOfYear();

                $data = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);

                $displayData = [
                    'title' => 'Laporan Tahunan',
                    'subtitle' => "Tahun {$year}",
                    'year' => $year,
                ];

                // Chart data untuk yearly - 1 per bulan (last record of the month)
                for ($m = 1; $m <= 12; $m++) {
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                    $monthData = SensorData::whereBetween('created_at', [$monthStart, $monthEnd])->orderBy('created_at', 'desc')->first();
                    if ($monthData) {
                        $chartData['labels'][] = $monthData->created_at->setTimezone('Asia/Jakarta')->format('M');
                        $chartData['suhu'][] = $monthData->suhu;
                        $chartData['ph'][] = $monthData->ph;
                        $chartData['tds'][] = $monthData->tds;
                    }
                }

                // Prepare $logs for data table
                $logs = collect([]);
                for ($m = 1; $m <= 12; $m++) {
                    $monthStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
                    $monthEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();
                    $monthData = SensorData::whereBetween('created_at', [$monthStart, $monthEnd])->orderBy('created_at', 'desc')->first();
                    if ($monthData) {
                        $logs->push($monthData);
                    }
                }
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

                // Chart data untuk period - 1 per hari (last record of the day)
                $currentDate = $startDateObj->copy()->startOfDay();
                while ($currentDate <= $endDateObj) {
                    $dayData = SensorData::whereDate('created_at', $currentDate)->orderBy('created_at', 'desc')->first();
                    if ($dayData) {
                        $chartData['labels'][] = $dayData->created_at->setTimezone('Asia/Jakarta')->format('d M');
                        $chartData['suhu'][] = $dayData->suhu;
                        $chartData['ph'][] = $dayData->ph;
                        $chartData['tds'][] = $dayData->tds;
                    }
                    $currentDate->addDay();
                }

                // Prepare $logs for data table
                $logs = collect([]);
                $currentDate = $startDateObj->copy()->startOfDay();
                while ($currentDate <= $endDateObj) {
                    $dayData = SensorData::whereDate('created_at', $currentDate)->orderBy('created_at', 'desc')->first();
                    if ($dayData) {
                        $logs->push($dayData);
                    }
                    $currentDate->addDay();
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
                $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $filename = "laporan_bulanan_{$month}.csv";
                break;

            case 'yearly':
                $year = $request->input('year', Carbon::now()->year);
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
                $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $filename = "laporan_tahunan_{$year}.csv";
                break;

            case 'period':
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
                $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();
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
                $pdf = Pdf::loadView('reports.pdf.daily', compact('data', 'stats', 'dateObj'));
                return $pdf->download("laporan_harian_{$date}.pdf");
                break;

            case 'monthly':
                $month = $request->input('month', Carbon::now()->format('Y-m'));
                list($year, $monthVal) = explode('-', $month);
                $startDate = Carbon::createFromDate($year, $monthVal, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $monthVal, 1)->endOfMonth();
                $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);
                $pdf = Pdf::loadView('reports.pdf.monthly', compact('data', 'stats', 'startDate', 'endDate'));
                return $pdf->download("laporan_bulanan_{$month}.pdf");
                break;

            case 'yearly':
                $year = $request->input('year', Carbon::now()->year);
                $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
                $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
                $data = SensorData::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);
                $pdf = Pdf::loadView('reports.pdf.yearly', compact('data', 'stats', 'year'));
                return $pdf->download("laporan_tahunan_{$year}.pdf");
                break;

            case 'period':
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                $startDateObj = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $endDateObj = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
                $data = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])
                    ->orderBy('created_at', 'asc')
                    ->get();
                $stats = $this->calculateStats($data);
                $pdf = Pdf::loadView('reports.pdf.period', compact('data', 'stats', 'startDateObj', 'endDateObj'));
                return $pdf->download("laporan_periode_{$startDate}_{$endDate}.pdf");
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

