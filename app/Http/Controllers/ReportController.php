<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Tampilkan laporan utama (Unified)
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'daily');
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $year = $request->input('year', Carbon::now()->year);
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $data = collect();
        $stats = [];
        $logs = collect();
        $displayData = [];
        $chartData = ['labels' => [], 'suhu' => [], 'ph' => [], 'tds' => []];

        switch ($type) {
            case 'daily':
                $dateObj = Carbon::createFromFormat('Y-m-d', $date);

                // 1. Ambil semua data mentah untuk menghitung statistik akurat (Min/Max)
                $rawData = SensorData::whereDate('created_at', $dateObj)->get();
                $stats = $this->calculateStats($rawData);

                // 2. Ambil data rata-rata per jam untuk Tabel dan Grafik
                $logs = $this->getHourlyAveragedLogs($dateObj);
                $data = $logs; // Agar count($data) > 0 di Blade bernilai true

                $displayData = [
                    'title' => 'Laporan Harian',
                    'subtitle' => $dateObj->translatedFormat('d F Y'),
                ];

                // 3. Siapkan data grafik (maksimal 24 titik)
                foreach ($logs as $item) {
                    $chartData['labels'][] = $item->created_at->format('H:i');
                    $chartData['suhu'][] = $item->suhu;
                    $chartData['ph'][] = $item->ph;
                    $chartData['tds'][] = $item->tds;
                }
                break;

            case 'monthly':
                list($yearVal, $monthVal) = explode('-', $month);
                $startDateObj = Carbon::createFromDate($yearVal, $monthVal, 1)->startOfMonth();
                $endDateObj = Carbon::createFromDate($yearVal, $monthVal, 1)->endOfMonth();
                $allData = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])->get();
                $stats = $this->calculateStats($allData);
                $logs = $this->getAveragedLogs($startDateObj, $endDateObj, 1);
                $data = $logs; // Agar count($data) > 0 valid

                foreach ($logs as $log) {
                    $chartData['labels'][] = $log->created_at->format('d M');
                    $chartData['suhu'][] = $log->suhu;
                    $chartData['ph'][] = $log->ph;
                    $chartData['tds'][] = $log->tds;
                }
                $displayData = ['title' => 'Laporan Bulanan', 'subtitle' => $startDateObj->translatedFormat('F Y')];
                break;

            case 'yearly':
                $allData = SensorData::whereYear('created_at', $year)->get();
                $stats = $this->calculateStats($allData);
                $logs = collect([]);
                for ($m = 1; $m <= 12; $m++) {
                    $monthReadings = SensorData::whereYear('created_at', $year)->whereMonth('created_at', $m)->get();
                    if ($monthReadings->count() > 0) {
                        $last = $monthReadings->last();
                        $avg = new \stdClass();
                        $avg->created_at = Carbon::createFromDate($year, $m, 1);
                        $avg->suhu = number_format($monthReadings->avg('suhu'), 2);
                        $avg->ph = number_format($monthReadings->avg('ph'), 2);
                        $avg->tds = round($monthReadings->avg('tds'));
                        $avg->status_pompa_ph = $last->status_pompa_ph;
                        $avg->status_pompa_tds = $last->status_pompa_tds;
                        $avg->status_pendingin = $last->status_pendingin;
                        $logs->push($avg);
                        $chartData['labels'][] = $avg->created_at->format('M');
                        $chartData['suhu'][] = $avg->suhu;
                        $chartData['ph'][] = $avg->ph;
                        $chartData['tds'][] = $avg->tds;
                    }
                }
                $data = $logs;
                $displayData = ['title' => 'Laporan Tahunan', 'subtitle' => "Tahun $year"];
                break;

            case 'period':
                $startDateObj = Carbon::parse($startDate)->startOfDay();
                $endDateObj = Carbon::parse($endDate)->endOfDay();
                $allData = SensorData::whereBetween('created_at', [$startDateObj, $endDateObj])->get();
                $stats = $this->calculateStats($allData);
                $logs = $this->getAveragedLogs($startDateObj, $endDateObj, 1);
                $data = $logs;
                foreach ($logs as $log) {
                    $chartData['labels'][] = $log->created_at->format('d M');
                    $chartData['suhu'][] = $log->suhu;
                    $chartData['ph'][] = $log->ph;
                    $chartData['tds'][] = $log->tds;
                }
                $displayData = ['title' => 'Laporan Periode', 'subtitle' => $startDateObj->format('d/m/Y') . " - " . $endDateObj->format('d/m/Y')];
                break;
        }

        return view('reports.index', compact('type', 'data', 'stats', 'logs', 'displayData', 'chartData', 'date', 'month', 'year', 'startDate', 'endDate'));
    }

    /**
     * Fungsi Helper untuk data rata-rata
     */
    private function getAveragedLogs($start, $end, $interval)
    {
        $logs = collect([]);
        $current = $start->copy();
        while ($current <= $end) {
            $periodEnd = $current->copy()->addDays($interval)->endOfDay();
            $readings = SensorData::whereBetween('created_at', [$current, $periodEnd])->get();
            if ($readings->count() > 0) {
                $last = $readings->last();
                $avg = new \stdClass();
                $avg->created_at = $current->copy();
                $avg->suhu = number_format($readings->avg('suhu'), 2);
                $avg->ph = number_format($readings->avg('ph'), 2);
                $avg->tds = round($readings->avg('tds'));
                $avg->status_pompa_ph = $last->status_pompa_ph;
                $avg->status_pompa_tds = $last->status_pompa_tds;
                $avg->status_pendingin = $last->status_pendingin;
                $logs->push($avg);
            }
            $current->addDays($interval);
        }
        return $logs;
    }

    private function getHourlyAveragedLogs($date)
    {
        $hourlyLogs = collect([]);

        for ($h = 0; $h < 24; $h++) {
            $startTime = $date->copy()->startOfDay()->addHours($h);
            $endTime = $startTime->copy()->endOfHour();

            $readings = SensorData::whereBetween('created_at', [$startTime, $endTime])->get();

            if ($readings->count() > 0) {
                $last = $readings->last();
                $avg = new \stdClass();
                // Simpan waktu awal jam tersebut
                $avg->created_at = $startTime;
                $avg->suhu = number_format($readings->avg('suhu'), 2);
                $avg->ph = number_format($readings->avg('ph'), 2);
                $avg->tds = round($readings->avg('tds'));

                // Ambil status relay terakhir pada jam tersebut
                $avg->status_pompa_ph = $last->status_pompa_ph;
                $avg->status_pompa_tds = $last->status_pompa_tds;
                $avg->status_pendingin = $last->status_pendingin;

                $hourlyLogs->push($avg);
            }
        }
        return $hourlyLogs;
    }

    /**
     * EXPORT CSV (Unified)
     */
    public function exportReport(Request $request)
    {
        $type = $request->input('type', 'daily');
        // Logika pengambilan data sama dengan index
        $data = $this->getDataForExport($request);
        $filename = "laporan_{$type}_" . Carbon::now()->format('Ymd') . ".csv";
        return $this->exportToCsv($data, $filename);
    }

    /**
     * EXPORT PDF (Unified)
     */
    public function pdfReport(Request $request)
    {
        $type = $request->input('type', 'daily');
        $data = $this->getDataForExport($request);
        $stats = $this->calculateStats($data);

        // Ambil maksimal 15 titik data agar grafik tidak terlalu padat di PDF
        $chartLogs = $data->count() > 15 ? $data->nth(round($data->count() / 15)) : $data;

        $labels = [];
        $suhuData = [];
        $phData = [];
        $tdsData = [];

        foreach ($chartLogs as $log) {
            $labels[] = $type === 'daily' ? $log->created_at->format('H:i') : $log->created_at->format('d M');
            $suhuData[] = (float)$log->suhu;
            $phData[] = (float)$log->ph;
            $tdsData[] = (float)$log->tds;
        }

        // Gabungkan data menjadi string untuk URL API
        $chartConfig = [
            'labels' => $labels,
            'suhu' => $suhuData,
            'ph' => $phData,
            'tds' => $tdsData
        ];

        $pdf = Pdf::loadView("reports.export_pdf", compact('data', 'stats', 'type', 'chartConfig'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("hydrodash_report_{$type}.pdf");
    }

    /**
     * Helper untuk mengambil data export tanpa duplikasi logic
     */
    private function getDataForExport(Request $request)
    {
        $type = $request->input('type', 'daily');
        if ($type === 'daily') {
            return SensorData::whereDate('created_at', $request->date)->get();
        }
        // Untuk Bulanan/Tahunan/Periode, ambil data rata-rata agar file tidak terlalu bengkak
        return $this->index($request)->getData()['logs'];
    }

    private function calculateStats($data)
    {
        if ($data->isEmpty()) {
            return [
                'avg_ph' => '0.00',
                'min_ph' => '0.00',
                'max_ph' => '0.00',
                'avg_tds' => 0,
                'min_tds' => 0,
                'max_tds' => 0,
                'avg_suhu' => '0.00',
                'min_suhu' => '0.00',
                'max_suhu' => '0.00',
                'pompa_ph_on' => 0,
                'pompa_tds_on' => 0,
                'pendingin_on' => 0,
                'total_records' => 0
            ];
        }
        return [
            'avg_ph' => number_format($data->avg('ph'), 2),
            'min_ph' => number_format($data->min('ph'), 2),
            'max_ph' => number_format($data->max('ph'), 2),
            'avg_tds' => round($data->avg('tds')),
            'min_tds' => $data->min('tds'),
            'max_tds' => $data->max('tds'),
            'avg_suhu' => number_format($data->avg('suhu'), 2),
            'min_suhu' => number_format($data->min('suhu'), 2),
            'max_suhu' => number_format($data->max('suhu'), 2),
            'pompa_ph_on' => $data->where('status_pompa_ph', 'ON')->count(),
            'pompa_tds_on' => $data->where('status_pompa_tds', 'ON')->count(),
            'pendingin_on' => $data->where('status_pendingin', 'ON')->count(),
            'total_records' => $data->count(),
        ];
    }

    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Waktu', 'Suhu (°C)', 'pH', 'TDS (PPM)', 'Pompa pH', 'Pompa TDS', 'Pendingin']);
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->created_at,
                    $row->suhu,
                    $row->ph,
                    $row->tds,
                    $row->status_pompa_ph,
                    $row->status_pompa_tds,
                    $row->status_pendingin
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // Fungsi legacy tetap ada tapi memanggil fungsi unified agar tidak error
    public function exportDaily(Request $request)
    {
        return $this->exportReport($request);
    }
    public function exportMonthly(Request $request)
    {
        return $this->exportReport($request);
    }
    public function exportYearly(Request $request)
    {
        return $this->exportReport($request);
    }
    public function exportPeriod(Request $request)
    {
        return $this->exportReport($request);
    }
}
