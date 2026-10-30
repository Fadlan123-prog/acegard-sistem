<?php

namespace App\Providers;

use App\Services\BranchContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BranchContext::class);

        $this->app->bind(
            \App\Domain\Commission\CommissionCalculator::class,
            fn () => new \App\Domain\Commission\CommissionCalculator(config('commission'))
        );

        $this->app->bind(
            \App\Domain\Sales\SalesCommissionCalculator::class,
            fn () => new \App\Domain\Sales\SalesCommissionCalculator(config('sales_commission'))
        );
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Throttle publik butuh IP pengunjung asli bila ada reverse proxy (config app.trusted_proxies).
        if ($proxies = array_values(array_filter(array_map('trim', explode(',', (string) config('app.trusted_proxies')))))) {
            \Illuminate\Http\Middleware\TrustProxies::at($proxies === ['*'] ? '*' : $proxies);
        }

        Blade::componentNamespace('App\\View\\Components\\Ui', 'ui');

        // Login throttling keyed on email+IP (not just IP — see audit finding
        // M-3). A plain per-IP limiter lets an attacker spread a brute-force
        // or credential-stuffing attempt across many IPs and never trip any
        // single bucket, since each IP gets its own fresh 5/min budget.
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        // Lupa-password & penukaran token: dikunci per email+IP (bukan IP saja,
        // karena trustProxies sengaja tidak diaktifkan — lihat bootstrap/app.php).
        // Pengiriman tautan juga dibatasi 1x/60 detik per akun oleh broker.
        RateLimiter::for('password-link', function (Request $request) {
            return Limit::perMinute(3)->by('pwlink|' . Str::lower((string) $request->input('email')) . '|' . $request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by('pwreset|' . Str::lower((string) $request->input('email')) . '|' . $request->ip());
        });
    }
}

