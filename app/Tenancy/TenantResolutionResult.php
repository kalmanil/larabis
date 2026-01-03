<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\TenantView;

/**
 * Value object representing the result of tenant resolution.
 * 
 * Provides type-safe access to resolved tenant and view instances.
 * This improves upgrade safety by avoiding array keys and providing
 * a stable interface even if internal resolution logic changes.
 */
class TenantResolutionResult
{
    public function __construct(
        protected ?Tenant $tenant = null,
        protected ?TenantView $view = null
    ) {
    }
    
    /**
     * Get the resolved tenant instance.
     */
    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }
    
    /**
     * Get the resolved tenant view instance.
     */
    public function view(): ?TenantView
    {
        return $this->view;
    }
    
    /**
     * Check if a tenant was successfully resolved.
     */
    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }
}

