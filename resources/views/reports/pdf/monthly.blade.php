<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            color: #16a34a;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 12px;
            color: #666;
        }
        
        /* Stats Grid - 3 kolom simetris (table layout untuk kompatibilitas DomPDF) */
        .stats-grid {
            width: 100%;
            margin-bottom: 25px;
            border-spacing: 15px 0;
            border-collapse: separate;
        }
        
        .stat-box {
            width: 33.33%;
            border: 2px solid #e5e7eb;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9fafb;
            text-align: center;
            box-sizing: border-box;
            vertical-align: top;
        }
        
        .stat-box h3 {
            margin: 0 0 12px 0;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #16a34a;
            margin-bottom: 12px;
        }
        
        .stat-details {
            display: flex;
            justify-content: space-around;
            gap: 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        
        .stat-detail-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-detail-item p {
            margin: 2px 0;
            font-size: 10px;
            color: #666;
        }
        
        .stat-detail-item .value {
            font-weight: bold;
            color: #111;
            font-size: 14px;
        }
        
        /* Charts Container */
        .charts-container {
            margin-top: 20px;
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
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #16a34a;
        }
        
        .chart-wrapper {
            background-color: #ffffff;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chart-wrapper svg {
            max-width: 100%;
            height: auto;
        }
        
        /* Section Title */
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #16a34a;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #16a34a;
        }
        
        /* Table Styles */
        .table-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            background-color: #ffffff;
        }
        
        table th {
            background-color: #16a34a;
            color: #ffffff;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #15803d;
        }
        
        table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table tr:hover {
            background-color: #f0fdf4;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        /* Print Optimization */
        @media print {
            body {
                padding: 10px;
            }
            
            .stat-box,
            .chart-wrapper {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            .chart-section {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Bulanan HydroDash</h1>
        <p>Periode: {{ $startDate->format('F Y') }}</p>
        <p>Generated: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Stats Grid - 3 Kolom Simetris -->
    <table class="stats-grid">
        <tr>
            <td class="stat-box">
                <h3>pH Level</h3>
                <div class="stat-value">{{ $stats['avg_ph'] }}</div>
                <div class="stat-details">
                    <div class="stat-detail-item">
                        <p>Min</p>
                        <p class="value">{{ $stats['min_ph'] }}</p>
                    </div>
                    <div class="stat-detail-item">
                        <p>Max</p>
                        <p class="value">{{ $stats['max_ph'] }}</p>
                    </div>
                </div>
            </td>

            <td class="stat-box">
                <h3>Suhu Air</h3>
                <div class="stat-value">{{ $stats['avg_suhu'] }}°C</div>
                <div class="stat-details">
                    <div class="stat-detail-item">
                        <p>Min</p>
                        <p class="value">{{ $stats['min_suhu'] }}°C</p>
                    </div>
                    <div class="stat-detail-item">
                        <p>Max</p>
                        <p class="value">{{ $stats['max_suhu'] }}°C</p>
                    </div>
                </div>
            </td>

            <td class="stat-box">
                <h3>TDS (PPM)</h3>
                <div class="stat-value">{{ $stats['avg_tds'] }}</div>
                <div class="stat-details">
                    <div class="stat-detail-item">
                        <p>Min</p>
                        <p class="value">{{ $stats['min_tds'] }}</p>
                    </div>
                    <div class="stat-detail-item">
                        <p>Max</p>
                        <p class="value">{{ $stats['max_tds'] }}</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Charts Container -->
    <div class="charts-container">
        <div class="chart-section">
            <div class="chart-title">Grafik Suhu Air Per Hari (°C)</div>
            <div class="chart-wrapper">
                {!! svgLineChart($data, 'suhu', 25, 35, 'rgba(239, 68, 68, 0.8)') !!}
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-title">Grafik pH Level Per Hari</div>
            <div class="chart-wrapper">
                {!! svgLineChart($data, 'ph', 5, 8, 'rgba(59, 130, 246, 0.8)') !!}
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-title">Grafik TDS (PPM) Per Hari</div>
            <div class="chart-wrapper">
                {!! svgLineChart($data, 'tds', 200, 800, 'rgba(34, 197, 94, 0.8)') !!}
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="table-section">
        <h2 class="section-title">Data Lengkap Per Hari</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Suhu (Rata-rata)</th>
                    <th>pH (Rata-rata)</th>
                    <th>TDS (Rata-rata)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $row->created_at->timezone('Asia/Jakarta')->format('d/m/Y') }}</td>
                        <td>{{ number_format($row->suhu, 2) }}°C</td>
                        <td>{{ number_format($row->ph, 2) }}</td>
                        <td>{{ number_format($row->tds, 2) }} PPM</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">Tidak ada data untuk bulan ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Report ini dibuat secara otomatis oleh HydroDash Monitoring System</p>
    </div>
</body>
</html>