<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\System;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Access\Response;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
        $this->registerPolicies();
        // Should return TRUE or FALSE
        Gate::define('admin', function(System $system_user) {
            return $system_user->type === 0 
            ? Response::allow()
            : Response::denyWithStatus(404);
        });
        Gate::define('manager', function(System $system_user) {
            return $system_user->type === 1 
            ? Response::allow()
            : Response::denyWithStatus(404);
        });
        Gate::define('front-desk', function(System $system_user) {
            return $system_user->type === 2 
            ? Response::allow()
            : Response::denyWithStatus(404);
        });
    }
}
