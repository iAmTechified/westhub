<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        config(['media-library.media_model' => \App\Models\Media::class]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Site settings and contact info are accessed directly in blade files
        // via SiteSettings::* calls where needed, rather than injecting into
        // every view via a wildcard composer (which would fire on every render).
    }
}
