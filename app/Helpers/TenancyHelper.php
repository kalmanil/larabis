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
     * Format: tenants.{tenant_id}.{code}.{view_name}
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
        
        return "tenants.{$tenantId}.{$code}.{$viewName}";
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
        $tenantViewPath = "tenants.{$tenantId}.{$code}.{$viewName}";
        
        // Try consolidated location first (tenants/{id}/resources/views/tenants/{id}/{code}/{view})
        $consolidatedPath = base_path("tenants/{$tenantId}/resources/views/tenants/{$tenantId}/{$code}/{$viewName}.blade.php");
        
        // Try legacy location (resources/views/tenants/{id}/{code}/{view})
        $legacyPath = resource_path("views/tenants/{$tenantId}/{$code}/{$viewName}.blade.php");
        
        // Check if view exists in either location
        if (!file_exists($consolidatedPath) && !file_exists($legacyPath) && !view()->exists($tenantViewPath)) {
            throw new \Exception("View not found: {$tenantViewPath} (checked consolidated and legacy locations)");
        }
        
        return view($tenantViewPath, $data);
    }
}

