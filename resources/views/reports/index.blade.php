<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kualitas Air - HydroDash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
</head>

<body class="bg-gray-50">
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Laporan Kualitas Air</h1>
                <p class="text-gray-600">Monitoring dan analisis data sensor kualitas air</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                ← Kembali ke Dashboard
            </a>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="{{ route('report.index') }}" class="space-y-6" id="reportForm">
                <!-- Report Type Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-4">Jenis Laporan</label>
                    <div class="flex gap-4 flex-wrap">
                        <label class="flex items-center">
                            <input type="radio" name="type" value="daily" {{ $type === 'daily' ? 'checked' : '' }} 
                                onchange="document.getElementById('reportForm').submit()"
                                class="w-4 h-4 text-blue-600 cursor-pointer">
                            <span class="ml-2 text-gray-700 cursor-pointer">Harian</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="monthly" {{ $type === 'monthly' ? 'checked' : '' }} 
                                onchange="document.getElementById('reportForm').submit()"
                                class="w-4 h-4 text-blue-600 cursor-pointer">
                            <span class="ml-2 text-gray-700 cursor-pointer">Bulanan</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="yearly" {{ $type === 'yearly' ? 'checked' : '' }} 
                                onchange="document.getElementById('reportForm').submit()"
                                class="w-4 h-4 text-blue-600 cursor-pointer">
                            <span class="ml-2 text-gray-700 cursor-pointer">Tahunan</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="period" {{ $type === 'period' ? 'checked' : '' }} 
                                onchange="document.getElementById('reportForm').submit()"
                                class="w-4 h-4 text-blue-600 cursor-pointer">
                            <span class="ml-2 text-gray-700 cursor-pointer">Periode Custom</span>
                        </label>
                    </div>
                </div>

                <!-- Dynamic Filter Container -->
                <div id="filterContainer">
                    @if ($type === 'daily')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                            <input type="date" name="date" value="{{ $date }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    @elseif ($type === 'monthly')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                            <input type="month" name="month" value="{{ $month }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    @elseif ($type === 'yearly')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                            <input type="number" name="year" value="{{ $year }}" min="2020" max="{{ date('Y') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    @elseif ($type === 'period')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Awal</label>
                                <input type="date" name="start_date" value="{{ $startDate }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                                <input type="date" name="end_date" value="{{ $endDate }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 flex-wrap">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                        Tampilkan Laporan
                    </button>
                    @php
                        $exportParams = ['type' => $type];
                        if ($type === 'daily') $exportParams['date'] = $date;
                        elseif ($type === 'monthly') $exportParams['month'] = $month;
                        elseif ($type === 'yearly') $exportParams['year'] = $year;
                        else {
                            $exportParams['start_date'] = $startDate;
                            $exportParams['end_date'] = $endDate;
                        }
                    @endphp
                    <a href="{{ route('report.export', $exportParams) }}"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                        📥 Export CSV
                    </a>
                    <a href="{{ route('report.pdf', $exportParams) }}"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                        📄 Export PDF
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-600 mb-2">Suhu (°C)</h3>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_suhu'], 2) }}</p>
                        <p class="text-xs text-gray-500">Rata-rata</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">Min: {{ number_format($stats['min_suhu'], 2) }}</p>
                        <p class="text-sm text-red-600">Max: {{ number_format($stats['max_suhu'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-600 mb-2">pH</h3>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_ph'], 2) }}</p>
                        <p class="text-xs text-gray-500">Rata-rata</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">Min: {{ number_format($stats['min_ph'], 2) }}</p>
                        <p class="text-sm text-red-600">Max: {{ number_format($stats['max_ph'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-600 mb-2">TDS (PPM)</h3>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_tds'], 2) }}</p>
                        <p class="text-xs text-gray-500">Rata-rata</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-green-600">Min: {{ number_format($stats['min_tds'], 2) }}</p>
                        <p class="text-sm text-red-600">Max: {{ number_format($stats['max_tds'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-sm font-medium text-gray-600 mb-2">Status Peralatan</h3>
                <div class="space-y-2">
                    <p class="text-sm">
                        <span class="font-medium">Pompa pH:</span>
                        <span class="text-blue-600">{{ $stats['pompa_ph_on'] ?? 0 }} / {{ $stats['total_records'] }}</span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium">Pompa TDS:</span>
                        <span class="text-blue-600">{{ $stats['pompa_tds_on'] ?? 0 }} / {{ $stats['total_records'] }}</span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium">Pendingin:</span>
                        <span class="text-blue-600">{{ $stats['pendingin_on'] ?? 0 }} / {{ $stats['total_records'] }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        @if (count($data) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Suhu Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Suhu (°C)</h3>
                    <canvas id="suhuChart"></canvas>
                </div>

                <!-- pH Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik pH</h3>
                    <canvas id="phChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 mb-8">
                <!-- TDS Chart -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik TDS (PPM)</h3>
                    <canvas id="tdsChart"></canvas>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Data Sensor</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Waktu</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Suhu (°C)</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">pH</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">TDS (PPM)</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pompa pH</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pompa TDS</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Pendingin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($logs as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($item->suhu, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($item->ph, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($item->tds, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 rounded {{ $item->status_pompa_ph ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $item->status_pompa_ph ? 'Aktif' : 'Mati' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 rounded {{ $item->status_pompa_tds ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $item->status_pompa_tds ? 'Aktif' : 'Mati' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-1 rounded {{ $item->status_pendingin ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $item->status_pendingin ? 'Aktif' : 'Mati' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-600 text-lg">Tidak ada data untuk periode yang dipilih</p>
            </div>
        @endif
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    // Helper function untuk memastikan threshold lines muncul di axis
    function forceTicks(min, max, threshold) {
        if (Array.isArray(threshold)) {
            return [...threshold].sort((a, b) => a - b);
        } else {
            return [threshold];
        }
    }

    // Suhu Chart
    const suhuCtx = document.getElementById('suhuChart');
    if (suhuCtx) {
        const suhuData = @json($chartData['suhu']);
        const suhuLabels = @json($chartData['labels']);
        
        new Chart(suhuCtx, {
            type: 'line',
            data: {
                labels: suhuLabels,
                datasets: [{
                    label: 'Suhu (°C)',
                    data: suhuData,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#f97316',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    annotation: {
                        annotations: {
                            threshold: {
                                type: 'line',
                                xMin: 0,
                                xMax: suhuLabels.length,
                                yValue: 32,
                                borderColor: '#ef4444',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                label: {
                                    display: true,
                                    content: ['Threshold: 32°C']
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 50,
                        ticks: {
                            callback: function(value) {
                                if (value === 32 || value === 0 || value === 50) return value;
                                // Sembunyikan beberapa label untuk menghindari overlap
                                if (suhuLabels.length > 20 && value % 10 !== 0) return '';
                                return value;
                            }
                        }
                    }
                }
            }
        });

        // Draw threshold line manually
        const plugin = {
            id: 'thresholdLine',
            afterDatasetsDraw(chart) {
                const ctx = chart.ctx;
                const yScale = chart.scales.y;
                const yPos = yScale.getPixelForValue(32);
                
                ctx.save();
                ctx.strokeStyle = '#ef4444';
                ctx.lineWidth = 2;
                ctx.setLineDash([5, 5]);
                ctx.beginPath();
                ctx.moveTo(chart.chartArea.left, yPos);
                ctx.lineTo(chart.chartArea.right, yPos);
                ctx.stroke();
                ctx.restore();
            }
        };
        Chart.register(plugin);
    }

    // pH Chart
    const phCtx = document.getElementById('phChart');
    if (phCtx) {
        const phData = @json($chartData['ph']);
        const phLabels = @json($chartData['labels']);

        new Chart(phCtx, {
            type: 'line',
            data: {
                labels: phLabels,
                datasets: [{
                    label: 'pH',
                    data: phData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 14,
                        ticks: {
                            callback: function(value) {
                                if (value === 5.5 || value === 6.5 || value === 0 || value === 14) return value;
                                if (phLabels.length > 20 && value % 2 !== 0) return '';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

    // TDS Chart
    const tdsCtx = document.getElementById('tdsChart');
    if (tdsCtx) {
        const tdsData = @json($chartData['tds']);
        const tdsLabels = @json($chartData['labels']);

        new Chart(tdsCtx, {
            type: 'line',
            data: {
                labels: tdsLabels,
                datasets: [{
                    label: 'TDS (PPM)',
                    data: tdsData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 1500,
                        ticks: {
                            callback: function(value) {
                                if (value === 560 || value === 840 || value === 0 || value === 1500) return value;
                                if (tdsLabels.length > 20 && value % 250 !== 0) return '';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
</body>

</html>
