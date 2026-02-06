<?php

namespace App\Tenancy;

use App\Contracts\CurrentTenant;
use App\Contracts\CurrentTenantView;
use App\Models\Tenant;
use App\Models\TenantView;

/**
 * Container for current tenant context.
 * 
 * Implements contracts for type-safe access to current tenant and view.
 * Bound to service container by TenantViewMiddleware after resolution.
 */
class TenantContext implements CurrentTenant, CurrentTenantView
{
    protected ?Tenant $tenant = null;
    protected ?TenantView $view = null;
    
    public function __construct(?Tenant $tenant = null, ?TenantView $view = null)
    {
        $this->tenant = $tenant;
        $this->view = $view;
    }
    
    /**
     * Get the current tenant instance.
     */
    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }
    
    /**
     * Get the current tenant view instance.
     */
    public function getView(): ?TenantView
    {
        return $this->view;
    }
    
    /**
     * Set the tenant.
     */
    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }
    
    /**
     * Set the view.
     */
    public function setView(?TenantView $view): void
    {
        $this->view = $view;
    }

    /**
     * Check if the current view has the given code.
     */
    public function isView(string $code): bool
    {
        return $this->view && $this->view->code === $code;
    }

    /**
     * Get the view path for current tenant and view code.
     * Format: tenants.{tenant_id}::{code}.{view_name}
     */
    public function getViewPath(string $viewName): string
    {
        if (!$this->tenant || !$this->view) {
            return $viewName;
        }
        return "tenants.{$this->tenant->id}::{$this->view->code}.{$viewName}";
    }

    /**
     * Render tenant-specific view. Supports namespace and fallback paths.
     */
    public function view(string $viewName, array $data = [])
    {
        if (!$this->tenant || !$this->view) {
            throw new \Exception('Tenant or view context not available');
        }

        $tenantId = $this->tenant->id;
        $code = $this->view->code;
        $namespaceViewPath = "tenants.{$tenantId}::{$code}.{$viewName}";

        if (view()->exists($namespaceViewPath)) {
            return view($namespaceViewPath, $data);
        }

        $simplifiedPath = base_path("tenants/{$tenantId}/resources/views/{$code}/{$viewName}.blade.php");
        if (file_exists($simplifiedPath)) {
            return view($namespaceViewPath, $data);
        }

        $oldConsolidatedPath = base_path("tenants/{$tenantId}/resources/views/tenants/{$tenantId}/{$code}/{$viewName}.blade.php");
        if (file_exists($oldConsolidatedPath)) {
            return view($namespaceViewPath, $data);
        }

        $legacyPath = resource_path("views/tenants/{$tenantId}/{$code}/{$viewName}.blade.php");
        if (file_exists($legacyPath)) {
            return view($namespaceViewPath, $data);
        }

        $tenantViewPath = "tenants.{$tenantId}.{$code}.{$viewName}";
        if (view()->exists($tenantViewPath)) {
            return view($tenantViewPath, $data);
        }

        throw new \Exception("View not found: {$viewName} for tenant {$tenantId}, view {$code} (checked namespace, simplified, old consolidated, legacy, and original format)");
    }
}

