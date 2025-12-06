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
     */
    public static function view(string $viewName, array $data = [])
    {
        $tenant = self::currentTenant();
        $view = self::currentView();
        
        if (!$tenant || !$view) {
            throw new \Exception('Tenant or view context not available');
        }
        
        $tenantViewPath = "tenants.{$tenant->id}.{$view->code}.{$viewName}";
        
        if (!view()->exists($tenantViewPath)) {
            throw new \Exception("View not found: {$tenantViewPath}");
        }
        
        return view($tenantViewPath, $data);
    }
}

