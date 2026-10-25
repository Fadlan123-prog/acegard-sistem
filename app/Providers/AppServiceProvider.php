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
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Blade::componentNamespace('App\\View\\Components\\Ui', 'ui');

        // Login throttling keyed on email+IP (not just IP — see audit finding
        // M-3). A plain per-IP limiter lets an attacker spread a brute-force
        // or credential-stuffing attempt across many IPs and never trip any
        // single bucket, since each IP gets its own fresh 5/min budget.
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });
    }
}

