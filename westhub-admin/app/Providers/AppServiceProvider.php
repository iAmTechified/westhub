<?php

namespace App\Providers;

use App\Support\AdminPermissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        // super_admin holds no permission rows; it is granted everything here.
        // Returning null rather than false lets normal checks run for everyone else.
        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole(AdminPermissions::SUPER_ADMIN) ? true : null;
        });
    }
}
