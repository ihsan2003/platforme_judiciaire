<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use App\Observers\JugementObserver;
use App\Observers\ExecutionObserver;

use App\Models\Jugement;
use App\Models\Execution;

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
        Paginator::useBootstrapFive();
        Jugement::observe(JugementObserver::class);
        Execution::observe(ExecutionObserver::class);
    }
}
