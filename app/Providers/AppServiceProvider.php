<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
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
        // Fechas en español: now()->translatedFormat('l j \d\e F') => "miércoles 28 de octubre"
        Carbon::setLocale(config('app.locale'));

        // Paginación con estilos de Bootstrap 5
        Paginator::useBootstrapFive();
    }
}
