<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HydroDash - Monitoring ESP32 Monolith</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta http-equiv="refresh" content="10">
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-green-700">HydroDash Monitoring</h1>
            <span
                class="bg-white px-4 py-2 rounded-full shadow-sm text-sm text-gray-500 font-medium border border-gray-200">
                Update Terakhir:
                {{ $latest ? $latest->created_at->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '--:--' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
                <p class="text-gray-500 font-semibold uppercase text-xs">Suhu Air</p>
                <h2 class="text-4xl font-bold text-gray-800">{{ $latest->suhu ?? '--' }}°C</h2>
            </div>
            <div
                class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 hover:shadow-lg transition-shadow">
                <p class="text-gray-500 font-semibold uppercase text-xs">pH Level</p>
                <h2 class="text-4xl font-bold text-gray-800">{{ $latest->ph ?? '--' }}</h2>
            </div>
            <div
                class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500 hover:shadow-lg transition-shadow">
                <p class="text-gray-500 font-semibold uppercase text-xs">TDS (Nutrisi)</p>
                <h2 class="text-4xl font-bold text-gray-800">{{ $latest->tds ?? '--' }} <span
                        class="text-lg font-normal">PPM</span></h2>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h3 class="font-bold text-gray-700 mb-4 text-center md:text-left">Grafik Tren Sensor (pH, Suhu, & TDS)</h3>
            <div class="h-[400px]">
                <canvas id="hydroChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between border border-gray-200">
                <span class="text-sm font-bold text-gray-600">Pompa pH Up</span>
                <span
                    class="px-3 py-1 rounded-full text-xs font-bold {{ ($latest->status_pompa_ph ?? 'OFF') == 'ON' ? 'bg-green-100 text-green-700 animate-pulse' : 'bg-red-100 text-red-700' }}">
                    {{ $latest->status_pompa_ph ?? 'OFF' }}
                </span>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between border border-gray-200">
                <span class="text-sm font-bold text-gray-600">Pompa Nutrisi</span>
                <span
                    class="px-3 py-1 rounded-full text-xs font-bold {{ ($latest->status_pompa_tds ?? 'OFF') == 'ON' ? 'bg-green-100 text-green-700 animate-pulse' : 'bg-red-100 text-red-700' }}">
                    {{ $latest->status_pompa_tds ?? 'OFF' }}
                </span>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between border border-gray-200">
                <span class="text-sm font-bold text-gray-600">Sistem Pendingin</span>
                <span
                    class="px-3 py-1 rounded-full text-xs font-bold {{ ($latest->status_pendingin ?? 'OFF') == 'ON' ? 'bg-green-100 text-green-700 animate-pulse' : 'bg-red-100 text-red-700' }}">
                    {{ $latest->status_pendingin ?? 'OFF' }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-700">Riwayat Sensor Terbaru</h3>
                <span class="text-xs text-gray-400 font-medium">*Log 10 Data Terakhir</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="p-4">Waktu</th>
                            <th class="p-4">Suhu</th>
                            <th class="p-4">pH</th>
                            <th class="p-4">TDS</th>
                            <th class="p-4">Status Pompa</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($logs as $log)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="p-4 font-medium text-gray-600">
                                    {{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s | d-m-Y') }}
                                <td class="p-4">{{ $log->suhu }}°C</td>
                                <td class="p-4 text-green-600 font-semibold">{{ $log->ph }}</td>
                                <td class="p-4 text-yellow-600 font-semibold">{{ $log->tds }} PPM</td>
                                <td class="p-4 space-x-1">
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded border {{ $log->status_pompa_ph == 'ON' ? 'border-green-500 text-green-500 font-bold' : 'border-gray-300 text-gray-300' }}">pH</span>
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded border {{ $log->status_pompa_tds == 'ON' ? 'border-green-500 text-green-500 font-bold' : 'border-gray-300 text-gray-300' }}">TDS</span>
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded border {{ $log->status_pendingin == 'ON' ? 'border-green-500 text-green-500 font-bold' : 'border-gray-300 text-gray-300' }}">Cool</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400 italic">Belum ada data masuk
                                    dari ESP32...</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('hydroChart').getContext('2d');

        const labels = {!! json_encode(
            $logs->pluck('created_at')->map(fn($d) => $d->timezone('Asia/Jakarta')->format('H:i:s'))->reverse()->values(),
        ) !!};
        const phData = {!! json_encode($logs->pluck('ph')->reverse()->values()) !!};
        const suhuData = {!! json_encode($logs->pluck('suhu')->reverse()->values()) !!};
        const tdsData = {!! json_encode($logs->pluck('tds')->reverse()->values()) !!};

        const hydroChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'pH Level',
                        data: phData,
                        borderColor: '#10B981',
                        yAxisID: 'y', // Menggunakan sumbu kiri
                        tension: 0.4
                    },
                    {
                        label: 'Suhu (°C)',
                        data: suhuData,
                        borderColor: '#3B82F6',
                        yAxisID: 'y', // Menggunakan sumbu kiri
                        tension: 0.4
                    },
                    {
                        label: 'TDS (PPM)',
                        data: tdsData,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        yAxisID: 'y1', // Menggunakan sumbu kanan
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'pH & Suhu'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'TDS (PPM)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }, // Agar grid tidak tumpuk
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
