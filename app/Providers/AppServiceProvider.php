<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        | Batas rate untuk seluruh endpoint grup `api` (ingest ESP32 + AJAX dashboard).
        |
        | Default 240 permintaan/menit per IP — polling dashboard tiap 1 detik
        | (±60/menit) ditambah ESP32 tiap ±5 detik (±12/menit) masih sangat
        | longgar di bawahnya, sementara spam/lalu lintas liar terkendali.
        | Nilai dapat ditimpa via `config/hydro.php` -> `api_rate_limit`.
        */
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute((int) config('hydro.api_rate_limit', 240))->by($request->ip());
        });
    }
}
