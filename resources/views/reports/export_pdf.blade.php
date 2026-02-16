<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan HydroDash - {{ ucfirst($type) }}</title>
    <style>
        @page { margin: 1cm; }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #333;
            line-height: 1.4;
            margin: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #059669;
            margin: 0;
            text-transform: uppercase;
            font-size: 22px;
        }

        /* --- GUNAKAN TABEL UNTUK KARTU AGAR TIDAK TUMPANG TINDIH --- */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 20px;
        }
        .card {
            width: 33%;
            padding: 15px;
            border-radius: 10px;
            color: white;
            vertical-align: top;
        }
        .card-blue { background-color: #3b82f6; }
        .card-green { background-color: #10b981; }
        .card-amber { background-color: #f59e0b; }
        .card-title { font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .card-value { font-size: 22px; font-weight: bold; margin: 5px 0; }
        .card-footer { font-size: 9px; opacity: 0.9; }

        /* --- CHART CONTAINER --- */
        .chart-box {
            width: 100%;
            margin-top: 10px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            text-align: center;
            background-color: #ffffff;
        }
        .chart-title {
            font-size: 12px;
            font-weight: bold;
            color: #4b5563;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .chart-img {
            width: 100%; /* Sesuai lebar kontainer */
            max-width: 650px;
            height: auto;
        }

        /* --- TABEL DATA --- */
        .table-container { margin-top: 25px; }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            color: #374151;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }
        table.data-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .status-on { color: #059669; font-weight: bold; }
        .status-off { color: #ef4444; }
        
        .footer {
            position: fixed;
            bottom: -20px;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>LAPORAN MONITORING HYDRODASH</h1>
        <p>Tipe Laporan: {{ ucfirst($type) }} | Dicetak: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
    </div>

    <table class="stats-table">
        <tr>
            <td class="card card-blue">
                <div class="card-title">RATA-RATA SUHU</div>
                <div class="card-value">{{ $stats['avg_suhu'] }}°C</div>
                <div class="card-footer">MIN: {{ $stats['min_suhu'] }}° | MAX: {{ $stats['max_suhu'] }}°</div>
            </td>
            <td class="card card-green">
                <div class="card-title">RATA-RATA PH</div>
                <div class="card-value">{{ $stats['avg_ph'] }}</div>
                <div class="card-footer">MIN: {{ $stats['min_ph'] }} | MAX: {{ $stats['max_ph'] }}</div>
            </td>
            <td class="card card-amber">
                <div class="card-title">RATA-RATA TDS</div>
                <div class="card-value">{{ number_format((float)$stats['avg_tds'], 0) }} PPM</div>
                <div class="card-footer">MIN: {{ $stats['min_tds'] }} | MAX: {{ $stats['max_tds'] }}</div>
            </td>
        </tr>
    </table>

    <div class="chart-box">
        <div class="chart-title">Visualisasi Tren Sensor (Suhu, pH, TDS)</div>
        @php
            $config = [
                'type' => 'line',
                'data' => [
                    'labels' => $chartConfig['labels'],
                    'datasets' => [
                        [
                            'label' => 'Suhu',
                            'borderColor' => '#3b82f6',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'data' => $chartConfig['suhu'],
                            'fill' => true,
                            'pointRadius' => 2
                        ],
                        [
                            'label' => 'pH',
                            'borderColor' => '#10b981',
                            'data' => $chartConfig['ph'],
                            'fill' => false,
                            'pointRadius' => 2
                        ],
                        [
                            'label' => 'TDS',
                            'borderColor' => '#f59e0b',
                            'data' => $chartConfig['tds'],
                            'yAxisID' => 'y1',
                            'fill' => false,
                            'pointRadius' => 2
                        ]
                    ]
                ],
                'options' => [
                    'scales' => [
                        'yAxes' => [
                            ['id' => 'y', 'position' => 'left'],
                            ['id' => 'y1', 'position' => 'right', 'gridLines' => ['display' => false]]
                        ]
                    ]
                ]
            ];
            $chartUrl = 'https://quickchart.io/chart?width=600&height=300&c=' . urlencode(json_encode($config));
        @endphp
        <img src="{{ $chartUrl }}" class="chart-img">
    </div>

    <div class="table-container">
        <h3 style="font-size: 14px; color: #374151; margin-bottom: 10px;">Rincian Log Data Sensor</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu / Periode</th>
                    <th>Suhu</th>
                    <th>pH</th>
                    <th>TDS</th>
                    @if ($type === 'daily')
                        <th>Pompa pH</th>
                        <th>Pompa TDS</th>
                        <th>Cooling</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr>
                        <td style="font-weight: bold;">
                            @if ($type === 'daily')
                                {{ is_string($row->created_at) ? $row->created_at : $row->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}
                            @else
                                {{ is_string($row->created_at) ? $row->created_at : $row->created_at->format('d/m/Y') }}
                            @endif
                        </td>
                        <td style="color: #3b82f6;">{{ $row->suhu }}°C</td>
                        <td style="color: #10b981;">{{ $row->ph }}</td>
                        <td style="color: #f59e0b;">{{ $row->tds }}</td>
                        @if ($type === 'daily')
                            <td class="{{ $row->status_pompa_ph == 'ON' ? 'status-on' : 'status-off' }}">{{ $row->status_pompa_ph }}</td>
                            <td class="{{ $row->status_pompa_tds == 'ON' ? 'status-on' : 'status-off' }}">{{ $row->status_pompa_tds }}</td>
                            <td class="{{ $row->status_pendingin == 'ON' ? 'status-on' : 'status-off' }}">{{ $row->status_pendingin }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        HydroDash Monolith System Report - Dicetak secara otomatis oleh sistem.
    </div>
</body>
</html>