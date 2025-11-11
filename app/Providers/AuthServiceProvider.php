<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Define Gates untuk ABAC
        Gate::define('manage-users', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-partners', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-products', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('create-batch', function ($user) {
            return $user->isAdmin() || $user->isSuperAdmin();
        });

        Gate::define('correct-batch', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('create-child-batch', function ($user) {
            return $user->isMitraMiddlestream();
        });

        Gate::define('view-audit-logs', function ($user) {
            return $user->isAuditor() || $user->isSuperAdmin();
        });
    }
}