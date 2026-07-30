<?php

namespace App\Providers;

use App\Services\BranchContext;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

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
    }
}

