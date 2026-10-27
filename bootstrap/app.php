<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php', // <--- PASTIKAN BARIS INI ADA
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Endpoint ESP32 (`/api/terima-data`) hidup di grup `api` tanpa CSRF,
        // sehingga tidak perlu pengecualian di sini. Route web ingest sudah
        // dihapus pada Fase 3 (single endpoint dedupe).
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
