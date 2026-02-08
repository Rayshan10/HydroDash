<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Harian</title>
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

        /* Stats Grid - 3 kolom simetris dengan border kiri berwarna */
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
            box-sizing: border-box;
            vertical-align: top;
        }

        /* Border kiri berwarna untuk setiap stat box */
        .stat-box-ph {
            border-left: 4px solid #16a34a;
        }

        .stat-box-suhu {
            border-left: 4px solid #16a34a;
        }

        .stat-box-tds {
            border-left: 4px solid #16a34a;
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
            text-align: center;
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

        /* Layout Utama Grafik: 3 kolom simetris */
        .charts-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            /* Memastikan setiap kolom lebarnya sama persis */
        }

        .chart-cell {
            padding: 5px;
            vertical-align: top;
        }

        .chart-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: #ffffff;
            padding: 10px;
            /* Menghilangkan kebocoran visual */
            overflow: hidden;
            height: 220px;
            /* Batasi tinggi total kartu agar muat 1 halaman */
        }

        .chart-title-small {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #16a34a;
            white-space: nowrap;
        }

        .chart-inner-wrapper {
            width: 100%;
            height: 160px;
            /* Ruang khusus untuk grafik */
            display: block;
            position: relative;
        }

        /* Memaksa SVG agar tidak keluar jalur */
        .chart-inner-wrapper svg {
            width: 100% !important;
            height: 100% !important;
            max-height: 160px;
            display: block;
        }

        /* Table Section */
        .table-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .table-title {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #16a34a;
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
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Harian HydroDash</h1>
        <p>Tanggal:
            {{ isset($date) ? $date->format('d MMMM Y') : (isset($selectedDate) ? \Carbon\Carbon::parse($selectedDate)->format('d MMMM Y') : now()->format('d MMMM Y')) }}
        </p>
        <p>Generated: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table class="charts-row-table">
        <tr>
            <td class="chart-cell">
                <div class="chart-card">
                    <div class="chart-title-small">Suhu Air (°C)</div>
                    <div class="chart-inner-wrapper">
                        {!! svgLineChart($data, 'suhu', 25, 35, 'rgba(239, 68, 68, 0.8)') !!}
                    </div>
                </div>
            </td>

            <td class="chart-cell">
                <div class="chart-card">
                    <div class="chart-title-small">pH Level</div>
                    <div class="chart-inner-wrapper">
                        {!! svgLineChart($data, 'ph', 5, 8, 'rgba(59, 130, 246, 0.8)') !!}
                    </div>
                </div>
            </td>

            <td class="chart-cell">
                <div class="chart-card">
                    <div class="chart-title-small">TDS (PPM)</div>
                    <div class="chart-inner-wrapper">
                        {!! svgLineChart($data, 'tds', 200, 800, 'rgba(34, 197, 94, 0.8)') !!}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Data Table Section -->
    <div class="table-section">
        <div class="table-title">Data Detail Monitoring</div>

        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Suhu (°C)</th>
                    <th>pH</th>
                    <th>TDS (PPM)</th>
                    <th>Pompa pH</th>
                    <th>Pompa TDS</th>
                    <th>Pendingin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $row->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}</td>
                        <td>{{ $row->suhu }}</td>
                        <td>{{ $row->ph }}</td>
                        <td>{{ $row->tds }}</td>
                        <td>{{ $row->status_pompa_ph }}</td>
                        <td>{{ $row->status_pompa_tds }}</td>
                        <td>{{ $row->status_pendingin }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data untuk tanggal ini
                        </td>
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
