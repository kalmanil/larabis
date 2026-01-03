<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Stancl\Tenancy\Events\TenancyInitialized;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tenant class autoloading is handled by bootstrap/tenant-autoload.php
        // which is loaded via composer.json's "files" autoload array.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register tenant view namespaces AFTER tenancy is initialized
        // This ensures tenant context is available when views are resolved
        $this->app['events']->listen(TenancyInitialized::class, function ($event) {
            $this->registerTenantViews();
        });
        
        // Also register on boot for non-tenant requests (backward compatibility)
        // This ensures views are available even if tenancy isn't initialized
        $this->registerTenantViews();
    }
    
    /**
     * Register tenant view namespaces from consolidated tenants/ directory.
     * 
     * This method is called both during boot (for backward compatibility)
     * and after tenancy initialization (for proper tenant context).
     */
    protected function registerTenantViews(): void
    {
        $tenantsPath = base_path('tenants');
        
        if (!is_dir($tenantsPath)) {
            return;
        }
        
        $tenantDirs = array_filter(glob($tenantsPath . '/*'), 'is_dir');
        
        foreach ($tenantDirs as $tenantDir) {
            $tenantId = basename($tenantDir);
            $viewsPath = $tenantDir . '/resources/views';
            
            if (is_dir($viewsPath)) {
                // Register namespace for this tenant: tenants.{tenant_id}
                // This allows views like: tenants.lapp.default.home
                // to resolve to: tenants/lapp/resources/views/default/home.blade.php
                View::addNamespace("tenants.{$tenantId}", $viewsPath);
                
                // Also add as location for fallback
                View::addLocation($viewsPath);
            }
        }
    }
}
