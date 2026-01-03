<?php

namespace App\Contracts;

use App\Models\TenantView;

/**
 * Contract for accessing the current tenant view in the request context.
 * 
 * This contract provides type-safe access to the current tenant view instance
 * resolved by TenantResolver and bound to the service container.
 */
interface CurrentTenantView
{
    /**
     * Get the current tenant view instance.
     * 
     * @return TenantView|null The current tenant view or null if not in tenant context
     */
    public function getView(): ?TenantView;
}

