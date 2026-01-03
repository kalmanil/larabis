<?php

namespace App\Contracts;

use App\Models\Tenant;

/**
 * Contract for accessing the current tenant in the request context.
 * 
 * This contract provides type-safe access to the current tenant instance
 * resolved by TenantResolver and bound to the service container.
 */
interface CurrentTenant
{
    /**
     * Get the current tenant instance.
     * 
     * @return Tenant|null The current tenant or null if not in tenant context
     */
    public function getTenant(): ?Tenant;
}

