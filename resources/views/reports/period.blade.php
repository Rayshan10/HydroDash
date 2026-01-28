<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Periode - HydroDash</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
                    ← Kembali ke Dashboard
                </a>
                <h1 class="text-3xl font-bold text-green-700">Laporan Periode</h1>
                <p class="text-gray-600">{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('report.pdf-period', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
                    📄 Export PDF
                </a>
                <a href="{{ route('report.export-period', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                    📥 Export CSV
                </a>
            </div>
        </div>

        <!-- Date Range Picker -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-8">
            <form method="GET" class="flex gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="border border-gray-300 rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="border border-gray-300 rounded px-4 py-2">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded self-end">
                    Filter
                </button>
            </form>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- pH Stats -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-gray-500 font-semibold uppercase text-xs mb-4">pH Level</p>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Rata-rata</p>
                        <p class="text-2xl font-bold">{{ $stats['avg_ph'] }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-gray-600">Minimum</p>
                            <p class="font-semibold">{{ $stats['min_ph'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Maximum</p>
                            <p class="font-semibold">{{ $stats['max_ph'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suhu Stats -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                <p class="text-gray-500 font-semibold uppercase text-xs mb-4">Suhu Air</p>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Rata-rata</p>
                        <p class="text-2xl font-bold">{{ $stats['avg_suhu'] }}°C</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-gray-600">Minimum</p>
                            <p class="font-semibold">{{ $stats['min_suhu'] }}°C</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Maximum</p>
                            <p class="font-semibold">{{ $stats['max_suhu'] }}°C</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TDS Stats -->
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                <p class="text-gray-500 font-semibold uppercase text-xs mb-4">TDS (Nutrisi)</p>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Rata-rata</p>
                        <p class="text-2xl font-bold">{{ $stats['avg_tds'] }} PPM</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-gray-600">Minimum</p>
                            <p class="font-semibold">{{ $stats['min_tds'] }} PPM</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Maximum</p>
                            <p class="font-semibold">{{ $stats['max_tds'] }} PPM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow-md">
                <p class="text-gray-600 font-semibold text-sm mb-1">Total Hari</p>
                <p class="text-2xl font-bold text-blue-600">{{ $totalDays }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <p class="text-gray-600 font-semibold text-sm mb-1">Total Record</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['total_records'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <p class="text-gray-600 font-semibold text-sm mb-1">Pompa pH (Aktif)</p>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['pompa_ph_on'] }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <p class="text-gray-600 font-semibold text-sm mb-1">Pendingin (Aktif)</p>
                <p class="text-2xl font-bold text-cyan-600">{{ $stats['pendingin_on'] }}</p>
            </div>
        </div>

        <!-- Charts -->
        @if($logs->isNotEmpty())
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h3 class="font-bold text-gray-700 mb-6 text-center text-xl">Grafik Tren Sensor (1 data per hari)</h3>

            <div class="grid grid-cols-1 gap-12">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-semibold text-blue-600 mb-2 uppercase">Suhu Air</h4>
                    <div class="h-[300px] w-full">
                        <canvas id="suhuChart"></canvas>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-semibold text-green-600 mb-2 uppercase">pH Level</h4>
                    <div class="h-[300px] w-full">
                        <canvas id="phChart"></canvas>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-semibold text-yellow-600 mb-2 uppercase">TDS (Nutrisi)</h4>
                    <div class="h-[300px] w-full">
                        <canvas id="tdsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Data Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="font-bold text-gray-700">Riwayat Data Sensor ({{ $allData->count() }} records)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="p-4">Waktu</th>
                            <th class="p-4">Suhu (°C)</th>
                            <th class="p-4">pH</th>
                            <th class="p-4">TDS (PPM)</th>
                            <th class="p-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allData->take(100) as $item)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="p-4 font-medium text-sm">{{ $item->created_at->timezone('Asia/Jakarta')->format('d M H:i:s') }}</td>
                                <td class="p-4">{{ $item->suhu }}</td>
                                <td class="p-4 text-green-600 font-semibold">{{ $item->ph }}</td>
                                <td class="p-4 text-yellow-600 font-semibold">{{ $item->tds }}</td>
                                <td class="p-4 space-x-1">
                                    <span class="text-[10px] px-2 py-0.5 rounded border {{ $item->status_pompa_ph == 'ON' ? 'border-green-500 text-green-500 font-bold' : 'border-gray-300 text-gray-300' }}">pH</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded border {{ $item->status_pompa_tds == 'ON' ? 'border-green-500 text-green-500 font-bold' : 'border-gray-300 text-gray-300' }}">TDS</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded border {{ $item->status_pendingin == 'ON' ? 'border-blue-500 text-blue-500 font-bold' : 'border-gray-300 text-gray-300' }}">Cool</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400 italic">Tidak ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($allData->count() > 100)
                    <div class="p-4 bg-gray-50 text-center text-sm text-gray-600">
                        Menampilkan 100 dari {{ $allData->count() }} records. Download CSV untuk melihat semua data.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const labels = {!! json_encode($logs->pluck('label')->values()) !!};
            const phData = {!! json_encode($logs->pluck('ph')->values()) !!};
            const suhuData = {!! json_encode($logs->pluck('suhu')->values()) !!};
            const tdsData = {!! json_encode($logs->pluck('tds')->values()) !!};

            // Helper Function untuk menampilkan Label Ambang Batas di Sumbu Y
            const forceTicks = (axis, thresholds) => {
                thresholds.forEach(t => {
                    if (!axis.ticks.find(tick => tick.value === t)) {
                        axis.ticks.push({
                            value: t
                        });
                    }
                });
                axis.ticks.sort((a, b) => a.value - b.value);
            };

            // Chart Suhu
            new Chart(document.getElementById('suhuChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Suhu (°C)',
                        data: suhuData,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#3B82F6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            max: 50,
                            min: 0,
                            ticks: {
                                callback: function(value) {
                                    if (value === 30 || value === 35) return null;
                                    if (value === 32) return '32 (Batas)';
                                    return value;
                                }
                            },
                            afterBuildTicks: (axis) => forceTicks(axis, [32])
                        }
                    },
                    plugins: {
                        annotation: {
                            annotations: {
                                line1: {
                                    type: 'line',
                                    yMin: 32,
                                    yMax: 32,
                                    borderColor: 'red',
                                    borderWidth: 2,
                                    borderDash: [5, 5]
                                }
                            }
                        }
                    }
                }
            });

            // Chart pH
            new Chart(document.getElementById('phChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'pH Level',
                        data: phData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#10B981'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            max: 14,
                            min: 0,
                            ticks: {
                                callback: function(value) {
                                    if (value === 6) return null;
                                    if (value === 5.5) return '5.5 (Min)';
                                    if (value === 6.5) return '6.5 (Max)';
                                    return value;
                                }
                            },
                            afterBuildTicks: (axis) => forceTicks(axis, [5.5, 6.5])
                        }
                    },
                    plugins: {
                        annotation: {
                            annotations: {
                                line1: {
                                    type: 'line',
                                    yMin: 6.5,
                                    yMax: 6.5,
                                    borderColor: 'red',
                                    borderWidth: 2,
                                    borderDash: [5, 5]
                                },
                                line2: {
                                    type: 'line',
                                    yMin: 5.5,
                                    yMax: 5.5,
                                    borderColor: 'red',
                                    borderWidth: 2,
                                    borderDash: [5, 5]
                                }
                            }
                        }
                    }
                }
            });

            // Chart TDS
            new Chart(document.getElementById('tdsChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'TDS (PPM)',
                        data: tdsData,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#F59E0B'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            max: 1500,
                            min: 0,
                            ticks: {
                                callback: function(value) {
                                    if (value === 600 || value === 800) return null;
                                    if (value === 560) return '560 (Min)';
                                    if (value === 840) return '840 (Max)';
                                    return value;
                                }
                            },
                            afterBuildTicks: (axis) => forceTicks(axis, [560, 840])
                        }
                    },
                    plugins: {
                        annotation: {
                            annotations: {
                                line1: {
                                    type: 'line',
                                    yMin: 840,
                                    yMax: 840,
                                    borderColor: '#F59E0B',
                                    borderWidth: 2,
                                    borderDash: [5, 5]
                                },
                                line2: {
                                    type: 'line',
                                    yMin: 560,
                                    yMax: 560,
                                    borderColor: '#F59E0B',
                                    borderWidth: 2,
                                    borderDash: [5, 5]
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>
