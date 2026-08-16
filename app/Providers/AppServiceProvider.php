<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Tour;

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
                ->select('location')
                ->distinct()
                ->orderBy('location')
                ->pluck('location');
            $view->with('navbarDestinations', $destinations);
        });
    }
}
