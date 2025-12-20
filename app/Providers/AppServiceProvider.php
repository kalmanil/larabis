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
        // Add tenant view paths from consolidated tenants/ directory
        $tenantsPath = base_path('tenants');
        
        if (is_dir($tenantsPath)) {
            $tenantDirs = array_filter(glob($tenantsPath . '/*'), 'is_dir');
            
            foreach ($tenantDirs as $tenantDir) {
                $viewsPath = $tenantDir . '/resources/views';
                
                if (is_dir($viewsPath)) {
                    // Add tenant views to Laravel's view finder
                    View::addLocation($viewsPath);
                }
            }
        }
    }
}
