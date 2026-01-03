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
}

