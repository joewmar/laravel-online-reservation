<?php

namespace App\Providers;

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
        $arrNavItems =[
            "Home" => "/", 
            "About Us" => "/aboutus", 
            "Accommodation" => "/accommodation",
            "Contact Us" => "/contact",
            "Login" => "/login",
          ];
        View::share('landingNavbar', $arrNavItems);
    }
}
