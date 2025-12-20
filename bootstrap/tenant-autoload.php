<?php

/**
 * Autoload tenant-specific classes from consolidated tenants/ directory
 * 
 * This file is loaded by Composer to register tenant namespaces dynamically
 * Note: This runs during composer dump-autoload, so Laravel helpers aren't available
 */

// Calculate base path (this file is in bootstrap/, so go up one level)
$basePath = dirname(__DIR__);
$tenantsPath = $basePath . '/tenants';

if (!is_dir($tenantsPath)) {
    return;
}

$tenantDirs = array_filter(glob($tenantsPath . '/*'), 'is_dir');

foreach ($tenantDirs as $tenantDir) {
    $tenantId = basename($tenantDir);
    $appPath = $tenantDir . '/app';
    
    if (is_dir($appPath)) {
        // Get the Composer autoloader
        $autoloadFile = $basePath . '/vendor/autoload.php';
        if (file_exists($autoloadFile)) {
            $loader = require $autoloadFile;
            
            // Autoload tenant Features
            $featuresPath = $appPath . '/Features';
            if (is_dir($featuresPath)) {
                $loader->addPsr4("App\\Features\\", $featuresPath);
            }
        }
    }
}

