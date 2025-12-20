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
        // Register tenant class autoloading from consolidated tenants/ directory
        $tenantsPath = base_path('tenants');
        
        if (is_dir($tenantsPath)) {
            $tenantDirs = array_filter(glob($tenantsPath . '/*'), 'is_dir');
            
            foreach ($tenantDirs as $tenantDir) {
                $appPath = $tenantDir . '/app';
                
                if (is_dir($appPath)) {
                    // Add tenant app directory to autoloader
                    $loader = require base_path('vendor/autoload.php');
                    
                    // Register tenant Features namespace
                    $featuresPath = $appPath . '/Features';
                    if (is_dir($featuresPath)) {
                        $loader->addPsr4("App\\Features\\", $featuresPath);
                    }
                }
            }
        }
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
