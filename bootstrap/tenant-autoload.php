<?php

/**
 * Tenant Autoloader
 * 
 * ⚠️ CRITICAL FILE - DO NOT REMOVE OR MODIFY WITHOUT REVIEWING docs/UPGRADES.md
 * 
 * This file registers autoloading for tenant-specific classes
 * located in tenants/{tenant_id}/app/ directories.
 * 
 * It's included early in the bootstrap process (via composer.json "files" autoload)
 * to ensure tenant classes are available before Laravel fully boots.
 * 
 * Upgrade Notes:
 * - May need adjustment if Laravel class caching changes
 * - Registered via composer.json autoload "files" array (line 32-34)
 * - Uses spl_autoload_register with prepend=true (runs before Composer)
 * - Must run before Laravel service providers boot
 * 
 * See docs/UPGRADES.md for upgrade safety information.
 */

if (!function_exists('registerTenantAutoloader')) {
    // Track registered autoloaders to prevent duplicates
    static $registeredAutoloaders = [];
    
    function registerTenantAutoloader()
    {
        global $registeredAutoloaders;
        
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
            
            // Extract tenant ID from directory name
            $tenantId = basename($tenantDir);
            
            // Prevent duplicate registration for same tenant
            $autoloaderKey = "tenant_{$tenantId}";
            if (isset($registeredAutoloaders[$autoloaderKey])) {
                continue;
            }
            
            // Register autoloader for this tenant's app directory
            // Prepend so it runs before Composer, but only handle tenant-specific classes
            spl_autoload_register(function ($class) use ($appPath, $tenantId) {
                // Only handle App namespace classes that contain this tenant's namespace
                if (strpos($class, 'App\\') !== 0) {
                    return false;
                }
                
                // Only handle classes that contain this tenant's namespace part
                // This prevents the tenant autoloader from interfering with base classes
                $tenantNamespacePart = "Tenants\\{$tenantId}\\";
                if (strpos($class, $tenantNamespacePart) === false) {
                    return false; // Let Composer's autoloader handle non-tenant classes
                }
                
                // Convert namespace to file path
                // Remove 'App\' prefix
                $relativePath = str_replace('App\\', '', $class);
                
                // Remove 'Tenants\{tenant_id}\' from path
                // Example: App\Features\Pages\Tenants\lapp\Traits\PageLogic
                // → Features\Pages\Traits\PageLogic
                $relativePath = str_replace($tenantNamespacePart, '', $relativePath);
                
                // Convert namespace separators to directory separators
                $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);
                $filePath = $appPath . DIRECTORY_SEPARATOR . $relativePath . '.php';
                
                // Normalize path separators for Windows compatibility
                $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
                
                // Check if file exists (case-sensitive on Linux, case-insensitive on Windows)
                if (file_exists($filePath)) {
                    require $filePath;
                    return true;
                }
                
                return false;
            }, true, false); // Prepend to autoload stack, don't throw (let Composer try if we fail)
            
            $registeredAutoloaders[$autoloaderKey] = true;
        }
    }
    
    // Register immediately
    registerTenantAutoloader();
}

// Load tenant-specific autoloader when DOMAIN_TENANT_ID is set.
// Each tenant defines its own namespaces in tenants/{tenant_id}/bootstrap/autoload.php
// This keeps Larabis tenant-agnostic; tenants own their autoload config.
$tenantId = $_ENV['DOMAIN_TENANT_ID'] ?? null;
if ($tenantId) {
    $tenantBootstrap = __DIR__ . '/../tenants/' . $tenantId . '/bootstrap/autoload.php';
    if (file_exists($tenantBootstrap)) {
        require $tenantBootstrap;
    }
}
