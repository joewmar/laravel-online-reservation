<?php

namespace App\Providers;

use App\Models\System;
use Illuminate\Support\Facades\Gate;
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
            "Home" => '/', 
            "About Us" => '/aboutus', 
            "Services" => '/services',
            "Contact Us" => 'contact',
        ];
        View::share('landingNavbar', $arrNavItems);

        $arrNationality = [
            "American",
            "Australian",
            "British",
            "Canadian",
            "Chinese",
            "Filipino",
            "French",
            "German",
            "Indian",
            "Indonesian",
            "Italian",
            "Japanese",
            "Korean",
            "Malaysian",
            "Mexican",
            "Russian",
            "Singaporean",
            "Spanish",
            "Thai",
            "Vietnamese",
        ];
        View::share('nationality', $arrNationality);

        $arrCountries = [
            "Australia",
            "Canada",
            "China",
            "France",
            "Germany",
            "India",
            "Indonesia",
            "Italy",
            "Japan",
            "Malaysia",
            "Philippines",
            "Singapore",
            "South Korea",
            "Spain",
            "Thailand",
            "United Kingdom",
            "United States",
            "Vietnam",
        ];
        View::share('countries', $arrCountries);
        $arrCur = [
            'USD' => '$',
            'EUR' => '€',
            'JPY' => '¥',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'INR' => '₹',
            'PHP' => '₱',
        ];

        View::share('currencies', $arrCur);
        View::share('currencyKey', array_keys($arrCur));

        // Should return TRUE or FALSE
        Gate::define('admin', function(System $system_user) {
            return $system_user->type === 0;
        });
        Gate::define('manager', function(System $system_user) {
            return $system_user->type === 1;
        });
    }
}
