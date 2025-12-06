<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasDatabase, HasDomains;

    /**
     * Get all views for this tenant
     */
    public function views(): HasMany
    {
        return $this->hasMany(TenantView::class);
    }

    /**
     * Get a view by its domain
     */
    public function viewByDomain(string $domain)
    {
        return $this->views()->where('domain', $domain)->first();
    }

    /**
     * Get a view by its name/type
     */
    public function viewByName(string $name)
    {
        return $this->views()->where('name', $name)->first();
    }
}

