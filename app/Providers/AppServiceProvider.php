<?php

namespace App\Providers;

use App\Models\Tour;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrapFive();

        View::composer('frontend.partials.navbar', function ($view) {
            $destinations = Tour::where('status', 'active')
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->select('location')
                ->distinct()
                ->orderBy('location')
                ->pluck('location')
                ->map(fn ($dest) => trim($dest))
                ->unique(fn ($dest) => strtolower($dest))
                ->values();
            $view->with('navbarDestinations', $destinations);
        });
    }
}
