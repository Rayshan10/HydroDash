<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HydroDash - Laporan Kualitas Air</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
</head>

<body class="bg-gray-100 p-4 sm:p-8">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-green-700">HydroDash Laporan</h1>
                <p class="text-gray-500 text-sm font-medium">Analisis Riwayat Kualitas Air & Performa Sistem</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg shadow-sm transition flex items-center">
                <span class="mr-2">←</span> Kembali ke Dashboard
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-100">
            <form method="GET" action="{{ route('report.index') }}" class="space-y-6" id="reportForm">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Pilih Jenis Laporan</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(['daily' => 'Harian', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan', 'period' => 'Periode'] as $val => $label)
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="type" value="{{ $val }}" {{ $type === $val ? 'checked' : '' }} 
                                        onchange="document.getElementById('reportForm').submit()"
                                        class="hidden peer">
                                    <span class="px-5 py-2 rounded-full border border-gray-200 text-sm font-bold transition
                                        peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600
                                        group-hover:border-green-400 text-gray-600">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-end gap-4">
                        <div class="flex-grow">
                            @if ($type === 'daily')
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilih Tanggal</label>
                                <input type="date" name="date" value="{{ $date }}" required
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-medium">
                            @elseif ($type === 'monthly')
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilih Bulan</label>
                                <input type="month" name="month" value="{{ $month }}" required
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-medium">
                            @elseif ($type === 'yearly')
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilih Tahun</label>
                                <input type="number" name="year" value="{{ $year }}" min="2020" max="{{ date('Y') }}" required
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-medium">
                            @elseif ($type === 'period')
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Dari</label>
                                        <input type="date" name="start_date" value="{{ $startDate }}" required
                                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Sampai</label>
                                        <input type="date" name="end_date" value="{{ $endDate }}" required
                                            class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none font-medium">
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition whitespace-nowrap">
                            Tampilkan
                        </button>
                    </div>
                </div>

                <div class="pt-4 border-t flex flex-wrap gap-4">
                    @php
                        $exportParams = ['type' => $type];
                        if ($type === 'daily') $exportParams['date'] = $date;
                        elseif ($type === 'monthly') $exportParams['month'] = $month;
                        elseif ($type === 'yearly') $exportParams['year'] = $year;
                        else { $exportParams['start_date'] = $startDate; $exportParams['end_date'] = $endDate; }
                    @endphp
                    <a href="{{ route('report.export', $exportParams) }}"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition flex items-center">
                        📥 Export CSV
                    </a>
                    <a href="{{ route('report.pdf', $exportParams) }}"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition flex items-center">
                        📄 Export PDF
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500 hover:shadow-lg transition">
                <p class="text-gray-500 font-bold uppercase text-[10px] tracking-widest mb-1">Rata-rata Suhu</p>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ number_format($stats['avg_suhu'], 2) }}°C</h2>
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-blue-500">MIN: {{ number_format($stats['min_suhu'], 2) }}°</span>
                    <span class="text-red-500">MAX: {{ number_format($stats['max_suhu'], 2) }}°</span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-emerald-500 hover:shadow-lg transition">
                <p class="text-gray-500 font-bold uppercase text-[10px] tracking-widest mb-1">Rata-rata pH Level</p>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ number_format($stats['avg_ph'], 2) }}</h2>
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-blue-500">MIN: {{ number_format($stats['min_ph'], 2) }}</span>
                    <span class="text-red-500">MAX: {{ number_format($stats['max_ph'], 2) }}</span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-amber-500 hover:shadow-lg transition">
                <p class="text-gray-500 font-bold uppercase text-[10px] tracking-widest mb-1">Rata-rata Nutrisi</p>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ number_format($stats['avg_tds'], 0) }} <span class="text-xs">PPM</span></h2>
                <div class="flex justify-between text-[10px] font-bold">
                    <span class="text-blue-500">MIN: {{ $stats['min_tds'] }}</span>
                    <span class="text-red-500">MAX: {{ $stats['max_tds'] }}</span>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-indigo-500 hover:shadow-lg transition">
                <p class="text-gray-500 font-bold uppercase text-[10px] tracking-widest mb-1">Frekuensi Aktivitas</p>
                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-gray-600">Pompa pH:</span>
                        <span class="text-xs font-bold text-indigo-600">{{ $stats['pompa_ph_on'] }}x</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-gray-600">Pompa TDS:</span>
                        <span class="text-xs font-bold text-indigo-600">{{ $stats['pompa_tds_on'] }}x</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-gray-600">Cooling:</span>
                        <span class="text-xs font-bold text-indigo-600">{{ $stats['pendingin_on'] }}x</span>
                    </div>
                </div>
            </div>
        </div>

        @if (count($data) > 0)
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <h3 class="font-bold text-gray-700 mb-6 text-center text-xl">Analisis Grafik Data {{ ucfirst($type) }}</h3>
                <div class="grid grid-cols-1 gap-12">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h4 class="text-xs font-bold text-blue-600 mb-4 uppercase tracking-wider">Tren Suhu Air</h4>
                        <div class="h-[300px] w-full"><canvas id="suhuChart"></canvas></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h4 class="text-xs font-bold text-emerald-600 mb-4 uppercase tracking-wider">Tren pH Level</h4>
                        <div class="h-[300px] w-full"><canvas id="phChart"></canvas></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h4 class="text-xs font-bold text-amber-600 mb-4 uppercase tracking-wider">Tren Nutrisi (TDS)</h4>
                        <div class="h-[300px] w-full"><canvas id="tdsChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mb-12">
                <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700 uppercase text-xs tracking-wider">Detail Log Data Sensor</h3>
                    <span class="text-[10px] text-gray-400 font-medium">Menampilkan {{ count($logs) }} entri</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-[10px] tracking-widest font-bold">
                                <th class="p-4">Waktu / Periode</th>
                                <th class="p-4">Suhu</th>
                                <th class="p-4">pH</th>
                                <th class="p-4">TDS</th>
                                @if($type === 'daily') <th class="p-4">Aksi Relay</th> @endif
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach ($logs as $item)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-4 font-bold text-gray-600">
                                        @if ($type === 'daily')
                                            {{ $item->created_at->setTimezone('Asia/Jakarta')->format('H:i:s | d-m-Y') }}
                                        @elseif ($type === 'monthly')
                                            {{ $item->created_at->format('d/m/Y') }}
                                        @elseif ($type === 'yearly')
                                            {{ $item->created_at->format('F Y') }}
                                        @else
                                            {{ $item->created_at->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td class="p-4 text-blue-600 font-bold">{{ number_format($item->suhu, 2) }}°C</td>
                                    <td class="p-4 text-emerald-600 font-bold">{{ number_format($item->ph, 2) }}</td>
                                    <td class="p-4 text-amber-600 font-bold">{{ number_format($item->tds, 0) }} <span class="text-[10px]">PPM</span></td>
                                    @if($type === 'daily')
                                    <td class="p-4 flex gap-1">
                                        <span class="text-[9px] px-2 py-0.5 rounded border {{ $item->status_pompa_ph == 'ON' ? 'border-green-500 text-green-500 font-bold bg-green-50' : 'border-gray-200 text-gray-300' }}">pH</span>
                                        <span class="text-[9px] px-2 py-0.5 rounded border {{ $item->status_pompa_tds == 'ON' ? 'border-green-500 text-green-500 font-bold bg-green-50' : 'border-gray-200 text-gray-300' }}">TDS</span>
                                        <span class="text-[9px] px-2 py-0.5 rounded border {{ $item->status_pendingin == 'ON' ? 'border-green-500 text-green-500 font-bold bg-green-50' : 'border-gray-200 text-gray-300' }}">Cool</span>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-md p-20 text-center border-2 border-dashed border-gray-200">
                <div class="text-gray-300 text-6xl mb-4">📭</div>
                <p class="text-gray-500 font-bold uppercase tracking-widest text-sm">Tidak ada data untuk periode ini</p>
            </div>
        @endif
    </div>

    <script>
        const chartOptions = (color) => ({
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, weight: 'bold' } } }
            }
        });

        const createGradient = (ctx, color) => {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, color.replace('1)', '0.3)'));
            gradient.addColorStop(1, color.replace('1)', '0)'));
            return gradient;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const labels = {!! json_encode($chartData['labels']) !!};

            // Suhu Chart
            new Chart(document.getElementById('suhuChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: @json($chartData['suhu']),
                        borderColor: '#3B82F6',
                        backgroundColor: (c) => createGradient(c.chart.ctx, 'rgba(59, 130, 246, 1)'),
                        fill: true, tension: 0.4, borderWidth: 3, pointRadius: 2
                    }]
                },
                options: chartOptions('#3B82F6')
            });

            // pH Chart
            new Chart(document.getElementById('phChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: @json($chartData['ph']),
                        borderColor: '#10B981',
                        backgroundColor: (c) => createGradient(c.chart.ctx, 'rgba(16, 185, 129, 1)'),
                        fill: true, tension: 0.4, borderWidth: 3, pointRadius: 2
                    }]
                },
                options: chartOptions('#10B981')
            });

            // TDS Chart
            new Chart(document.getElementById('tdsChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: @json($chartData['tds']),
                        borderColor: '#F59E0B',
                        backgroundColor: (c) => createGradient(c.chart.ctx, 'rgba(245, 158, 11, 1)'),
                        fill: true, tension: 0.4, borderWidth: 3, pointRadius: 2
                    }]
                },
                options: chartOptions('#F59E0B')
            });
        });
    </script>
</body>
</html>