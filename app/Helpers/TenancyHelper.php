<?php

namespace App\Helpers;

use App\Models\Tenant;
use App\Models\TenantView;

class TenancyHelper
{
    /**
     * Get the current tenant
     */
    public static function currentTenant(): ?Tenant
    {
        return app('currentTenant');
    }

    /**
     * Get the current tenant view
     */
    public static function currentView(): ?TenantView
    {
        return app('currentTenantView');
    }

    /**
     * Check if we're in a tenant context
     */
    public static function isTenantContext(): bool
    {
        return tenancy()->initialized;
    }

    /**
     * Check if we're in a specific view code
     */
    public static function isViewCode(string $code): bool
    {
        $view = self::currentView();
        return $view && $view->code === $code;
    }

    /**
     * Check if we're in admin view
     */
    public static function isAdminView(): bool
    {
        return self::isViewCode('admin');
    }

    /**
     * Get the view path for current tenant and view code
     * Format: tenants.{tenant_id}::{code}.{view_name} (namespace format)
     */
    public static function getViewPath(string $viewName): string
    {
        $tenant = self::currentTenant();
        $view = self::currentView();
        
        if (!$tenant || !$view) {
            // Fallback to default structure
            return $viewName;
        }
        
        $tenantId = $tenant->id;
        $code = $view->code;
        
        // Use namespace format: tenants.{tenant_id}::{code}.{view_name}
        return "tenants.{$tenantId}::{$code}.{$viewName}";
    }

    /**
     * Get tenant-specific view
     * Format: tenants.{tenant_id}.{code}.{view_name}
     * 
     * Supports both consolidated (tenants/{id}/resources/views) and legacy locations
     */
    public static function view(string $viewName, array $data = [])
    {
        $tenant = self::currentTenant();
        $view = self::currentView();
        
        if (!$tenant || !$view) {
            throw new \Exception('Tenant or view context not available');
        }
        
        $tenantId = $tenant->id;
        $code = $view->code;
        
        // Use namespace format: tenants.{tenant_id}::{code}.{view_name}
        // This works with View::addNamespace() registration in AppServiceProvider
        $namespaceViewPath = "tenants.{$tenantId}::{$code}.{$viewName}";
        
        // Check if view exists using namespace
        if (view()->exists($namespaceViewPath)) {
            return view($namespaceViewPath, $data);
        }
        
        // Fallback: Try simplified consolidated location (tenants/{id}/resources/views/{code}/{view})
        $simplifiedPath = base_path("tenants/{$tenantId}/resources/views/{$code}/{$viewName}.blade.php");
        if (file_exists($simplifiedPath)) {
            return view($namespaceViewPath, $data);
        }
        
        // Fallback: Try old consolidated location (tenants/{id}/resources/views/tenants/{id}/{code}/{view})
        $oldConsolidatedPath = base_path("tenants/{$tenantId}/resources/views/tenants/{$tenantId}/{$code}/{$viewName}.blade.php");
        if (file_exists($oldConsolidatedPath)) {
            return view($namespaceViewPath, $data);
        }
        
        // Fallback: Try legacy location (resources/views/tenants/{id}/{code}/{view})
        $legacyPath = resource_path("views/tenants/{$tenantId}/{$code}/{$viewName}.blade.php");
        if (file_exists($legacyPath)) {
            return view($namespaceViewPath, $data);
        }
        
        // Try original format as last resort
        $tenantViewPath = "tenants.{$tenantId}.{$code}.{$viewName}";
        if (view()->exists($tenantViewPath)) {
            return view($tenantViewPath, $data);
        }
        
        throw new \Exception("View not found: {$viewName} for tenant {$tenantId}, view {$code} (checked namespace, simplified, old consolidated, legacy, and original format)");
    }
}

