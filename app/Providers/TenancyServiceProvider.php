<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * 
     * Note: We do NOT register stancl's standard middleware here because
     * we use custom TenantViewMiddleware that handles both tenancy initialization
     * AND tenant view context setup. See docs/UPGRADES.md for details.
     * 
     * We only use contracts and traits - no direct references to internal classes.
     */
    public function boot(): void
    {
        // Bind tenant model contract
        // Using contract interface ensures upgrade safety
        $this->app->bind(
            \Stancl\Tenancy\Contracts\Tenant::class,
            Tenant::class
        );
    }
}

