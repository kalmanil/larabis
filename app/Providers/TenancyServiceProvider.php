<?php

namespace App\Providers;

use App\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Events\TenancyInitialized;

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

        // Create tenant database on tenant creation
        \Illuminate\Support\Facades\Event::listen(
            \Stancl\Tenancy\Events\TenantCreated::class,
            function (\Stancl\Tenancy\Events\TenantCreated $event) {
                $event->tenant->database()->manager()->createDatabase($event->tenant);
            }
        );
        
        // Configure additional tenant connections when tenancy initializes
        // This allows multiple MySQL connections per tenant (e.g., analytics, read replica)
        // Only configures connections that exist in config/database.php
        // 
        // ⚠️ UPGRADE SAFETY: Uses public TenancyInitialized event (same pattern as AppServiceProvider)
        // This event is part of stancl's public API and should remain stable across upgrades
        // See: docs/UPGRADES.md section "View Namespace Registration" for similar usage
        \Illuminate\Support\Facades\Event::listen(
            TenancyInitialized::class,
            function (TenancyInitialized $event) {
                // Get tenant via tenancy helper (preferred) or from event->tenancy->tenant
                // The event has $tenancy property (Tenancy instance), not $tenant directly
                // Access via: tenancy()->tenant (helper) or $event->tenancy->tenant
                $tenant = tenancy()->tenant ?? $event->tenancy->tenant ?? null;
                
                if (!$tenant) {
                    return;
                }
                
                $tenantId = $tenant->id;
                
                // Configure analytics connection (if defined in config)
                if (config('database.connections.tenant_analytics')) {
                    config([
                        'database.connections.tenant_analytics.database' => "tenant_{$tenantId}_analytics",
                    ]);
                    \Illuminate\Support\Facades\DB::purge('tenant_analytics');
                }
                
                // Configure read replica (if defined in config)
                if (config('database.connections.tenant_read')) {
                    config([
                        'database.connections.tenant_read.database' => "tenant_{$tenantId}",
                    ]);
                    \Illuminate\Support\Facades\DB::purge('tenant_read');
                }
            }
        );
    }
}

