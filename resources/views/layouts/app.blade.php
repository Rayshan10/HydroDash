<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    {{-- Tailwind & CSS aplikasi via build Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Chart.js dipin agar versi stabil di semua halaman --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    @stack('head')
</head>

<body class="bg-gray-100 p-4 sm:p-8">
    @yield('content')
</body>

</html>
