<?php

namespace App\Providers;

use App\Models\Hall;
use App\Models\SetionNotAdded;
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
        View::composer('session.modal.start-booking', function ($view) {
        $view->with('halls', Hall::all());
        
    });

            View::composer('main.create', function ($view) {
        $view->with('newSessions', SetionNotAdded::all());
        
    });
    }
}
