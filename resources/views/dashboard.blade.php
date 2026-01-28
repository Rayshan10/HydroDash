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

<div id="alert-container" class="max-w-6xl mx-auto mb-4 hidden">
    <div
        class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md flex items-center justify-between animate-bounce">
        <div class="flex items-center">
            <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <p class="font-bold">Peringatan Sistem!</p>
                <p id="alert-message" class="text-sm">Kondisi air sedang tidak optimal.</p>
            </div>
        </div>
        <button onclick="document.getElementById('alert-container').remove()" class="text-red-500 hover:text-red-800">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
            </svg>
        </button>
    </div>
</div>


<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-green-700">HydroDash Monitoring</h1>
            <span
                class="bg-white px-4 py-2 rounded-full shadow-sm text-sm text-gray-500 font-medium border border-gray-200">
                Update Terakhir:
                {{ $latest ? \Carbon\Carbon::now('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB' : '--:--' }}
            </span>
        </div>

        <!-- Report Navigation -->
        <div class="mb-8">
            <a href="{{ route('report.index') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition">
                📊 Lihat Laporan
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-between border border-gray-200">
                <span class="text-sm font-bold text-gray-600">Sistem Pendingin</span>
                <span
                    class="px-3 py-1 rounded-full text-xs font-bold {{ ($latest->status_pendingin ?? 'OFF') == 'ON' ? 'bg-green-100 text-green-700 animate-pulse' : 'bg-red-100 text-red-700' }}">
                    {{ $latest->status_pendingin ?? 'OFF' }}
                </span>
            </div>
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
            <h3 class="font-bold text-gray-700 mb-6 text-center text-xl">Grafik Tren Sensor Real-Time</h3>

            <div class="grid grid-cols-1 gap-12">

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-semibold text-blue-600 mb-2 uppercase tracking-wider">Monitoring Suhu Air
                    </h4>
                    <div class="h-[350px] w-full">
                        <canvas id="suhuChart"></canvas>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-semibold text-emerald-600 mb-2 uppercase tracking-wider">Monitoring pH Level
                    </h4>
                    <div class="h-[350px] w-full">
                        <canvas id="phChart"></canvas>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-semibold text-amber-600 mb-2 uppercase tracking-wider">Monitoring Nutrisi
                        (TDS)</h4>
                    <div class="h-[350px] w-full">
                        <canvas id="tdsChart"></canvas>
                    </div>
                </div>

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
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Ambil Data dari Laravel
            const labels = {!! json_encode(
                $logs->pluck('created_at')->map(fn($d) => $d->timezone('Asia/Jakarta')->format('H:i:s'))->reverse()->values(),
            ) !!};
            const phData = {!! json_encode($logs->pluck('ph')->reverse()->values()) !!};
            const suhuData = {!! json_encode($logs->pluck('suhu')->reverse()->values()) !!};
            const tdsData = {!! json_encode($logs->pluck('tds')->reverse()->values()) !!};

            const phLatest = {{ $latest->ph ?? 7 }};
            const tempLatest = {{ $latest->suhu ?? 25 }};
            const tdsLatest = {{ $latest->tds ?? 500 }};

            // 2. Logika Alert
            const alertBox = document.getElementById('alert-container');
            const alertMsg = document.getElementById('alert-message');
            let issues = [];

            if (phLatest < 5.5) issues.push("pH terlalu asam (" + phLatest + ")");
            if (phLatest > 6.5) issues.push("pH terlalu basa (" + phLatest + ")"); // Disesuaikan ke 6.5
            if (tempLatest > 32) issues.push("Suhu panas (" + tempLatest + "°C)");
            if (tdsLatest < 560 || tdsLatest > 840) issues.push("TDS tidak ideal (" + tdsLatest + " PPM)");

            if (issues.length > 0 && alertBox) {
                alertBox.classList.remove('hidden');
                alertMsg.innerText = issues.join(" | ");
            }

            // 3. Helper Function untuk menampilkan Label Ambang Batas di Sumbu Y
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

            // --- KONFIGURASI CHART SUHU ---
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
                        tension: 0.4
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
                                    // Menghilangkan angka 30 dan 35 agar tidak bertumpuk dengan 32
                                    if (value === 30 || value === 35) return null;

                                    // Menampilkan label khusus untuk ambang batas
                                    if (value === 32) return '32 (Batas)';
                                    return value;
                                }
                            },
                            // Memasukkan angka 32 ke dalam susunan skala Y
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

            // --- KONFIGURASI CHART PH (Sama seperti TDS) ---
            new Chart(document.getElementById('phChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'pH Level',
                        data: phData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)', // Ditambahkan fill
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    // Bagian Options pada phChart
                    scales: {
                        y: {
                            max: 14,
                            min: 0,
                            ticks: {
                                callback: function(value) {
                                    // Hapus angka 6 agar tidak menjepit label 5.5 dan 6.5
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

            // --- KONFIGURASI CHART TDS ---
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
                        tension: 0.4
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
                                    // Hapus tick otomatis yang terlalu dekat dengan ambang batas
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
