<?php

/**
 * Tenant Autoloader
 * 
 * This file registers autoloading for tenant-specific classes
 * located in tenants/{tenant_id}/app/ directories.
 * 
 * It's included early in the bootstrap process to ensure
 * tenant classes are available before Laravel fully boots.
 */

if (!function_exists('registerTenantAutoloader')) {
    function registerTenantAutoloader()
    {
        $tenantsPath = __DIR__ . '/../tenants';
        
        if (!is_dir($tenantsPath)) {
            return;
        }
        
        $tenantDirs = array_filter(glob($tenantsPath . '/*'), 'is_dir');
        
        foreach ($tenantDirs as $tenantDir) {
            $appPath = $tenantDir . '/app';
            
            if (!is_dir($appPath)) {
                continue;
            }
            
            // Register autoloader for this tenant's app directory
            spl_autoload_register(function ($class) use ($appPath) {
                // Only handle App namespace classes
                if (strpos($class, 'App\\') !== 0) {
                    return false;
                }
                
                // Convert namespace to file path
                // Remove 'App\' prefix and convert namespace separators to directory separators
                $relativePath = str_replace('App\\', '', $class);
                $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);
                $filePath = $appPath . DIRECTORY_SEPARATOR . $relativePath . '.php';
                
                // Check if file exists (case-sensitive on Linux, case-insensitive on Windows)
                if (file_exists($filePath)) {
                    require $filePath;
                    return true;
                }
                
                return false;
            }, true, true); // Prepend to autoload stack, throw on error
        }
    }
    
    // Register immediately
    registerTenantAutoloader();
}

