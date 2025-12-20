<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tenant class autoloading is handled by bootstrap/tenant-autoload.php
        // which is loaded via composer.json's "files" autoload array.
        // No need to register autoloaders here as they're already registered early.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register tenant view namespaces from consolidated tenants/ directory
        $tenantsPath = base_path('tenants');
        
        if (is_dir($tenantsPath)) {
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
}
