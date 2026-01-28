<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
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
        <h1>Laporan Harian HydroDash</h1>
        <p>Tanggal: {{ $date->format('d MMMM Y') }}</p>
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
                    <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data untuk tanggal ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Report ini dibuat secara otomatis oleh HydroDash Monitoring System</p>
    </div>
</body>
</html>
