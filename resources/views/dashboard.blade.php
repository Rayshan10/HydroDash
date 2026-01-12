<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HydroDash - Monitoring ESP32</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-green-700 mb-8">HydroDash Monitoring</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                <p class="text-gray-500 font-semibold uppercase">Suhu Air</p>
                <h2 class="text-4xl font-bold">{{ $latest->temperature ?? '--' }}°C</h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
                <p class="text-gray-500 font-semibold uppercase">pH Level</p>
                <h2 class="text-4xl font-bold">{{ $latest->ph_level ?? '--' }}</h2>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
                <p class="text-gray-500 font-semibold uppercase">TDS (Nutrisi)</p>
                <h2 class="text-4xl font-bold">{{ $latest->tds_value ?? '--' }} PPM</h2>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="font-bold text-gray-700">Riwayat Sensor Terbaru</h3>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                        <th class="p-4">Waktu</th>
                        <th class="p-4">Suhu</th>
                        <th class="p-4">pH</th>
                        <th class="p-4">TDS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4">{{ $log->created_at->format('H:i:s d-m-Y') }}</td>
                        <td class="p-4">{{ $log->temperature }}°C</td>
                        <td class="p-4">{{ $log->ph_level }}</td>
                        <td class="p-4">{{ $log->tds_value }} PPM</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>