<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tahunan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #16a34a;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        /* Stats Grid - 3 kolom simetris (inline-block untuk kompatibilitas DomPDF) */
        .stats-grid {
            width: 100%;
            margin-bottom: 30px;
            text-align: center;
        }
        .stat-box {
            display: inline-block;
            width: 31%;
            margin-right: 2%;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9fafb;
            vertical-align: top;
            box-sizing: border-box;
        }
        .stat-box:last-child { margin-right: 0; }
            background-color: #f9fafb;
        }
        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #111;
            margin-bottom: 10px;
        }
        .stat-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 11px;
        }
        .stat-detail-item p {
            margin: 3px 0;
            color: #666;
        }
        .stat-detail-item .value {
            font-weight: bold;
            color: #111;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }
        table th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .charts-container {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .chart-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .chart-title {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .chart-wrapper {
            background-color: #f9fafb;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Tahunan HydroDash</h1>
        <p>Tahun: {{ $year }}</p>
        <p>Generated: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <h3>pH Level</h3>
            <div class="stat-value">{{ $stats['avg_ph'] }}</div>
            <div class="stat-details">
                <div class="stat-detail-item">
                    <p>Minimum</p>
                    <p class="value">{{ $stats['min_ph'] }}</p>
                </div>
                <div class="stat-detail-item">
                    <p>Maximum</p>
                    <p class="value">{{ $stats['max_ph'] }}</p>
                </div>
            </div>
        </div>

        <div class="stat-box">
            <h3>Suhu Air</h3>
            <div class="stat-value">{{ $stats['avg_suhu'] }}°C</div>
            <div class="stat-details">
                <div class="stat-detail-item">
                    <p>Minimum</p>
                    <p class="value">{{ $stats['min_suhu'] }}°C</p>
                </div>
                <div class="stat-detail-item">
                    <p>Maximum</p>
                    <p class="value">{{ $stats['max_suhu'] }}°C</p>
                </div>
            </div>
        </div>

        <div class="stat-box">
            <h3>TDS (PPM)</h3>
            <div class="stat-value">{{ $stats['avg_tds'] }}</div>
            <div class="stat-details">
                <div class="stat-detail-item">
                    <p>Minimum</p>
                    <p class="value">{{ $stats['min_tds'] }}</p>
                </div>
                <div class="stat-detail-item">
                    <p>Maximum</p>
                    <p class="value">{{ $stats['max_tds'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-section">
            <div class="chart-title">Grafik Suhu Air Per Bulan (°C)</div>
            <div class="chart-wrapper">
                {!! svgLineChart($data, 'suhu', 25, 35, 'rgba(239, 68, 68, 0.8)') !!}
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-title">Grafik pH Level Per Bulan</div>
            <div class="chart-wrapper">
                {!! svgLineChart($data, 'ph', 5, 8, 'rgba(59, 130, 246, 0.8)') !!}
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-title">Grafik TDS (PPM) Per Bulan</div>
            <div class="chart-wrapper">
                {!! svgLineChart($data, 'tds', 200, 800, 'rgba(34, 197, 94, 0.8)') !!}
            </div>
        </div>
    </div>

    <h2 style="font-size: 16px; margin-top: 30px; margin-bottom: 15px;">Data Lengkap (Sampling)</h2>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Suhu (Rata-rata)</th>
                <th>pH (Rata-rata)</th>
                <th>TDS (Rata-rata)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->created_at->timezone('Asia/Jakarta')->format('F Y') }}</td>
                    <td>{{ number_format($row->suhu, 2) }}</td>
                    <td>{{ number_format($row->ph, 2) }}</td>
                    <td>{{ number_format($row->tds, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">Tidak ada data untuk tahun ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Report ini dibuat secara otomatis oleh HydroDash Monitoring System</p>
    </div>
</body>
</html>
