<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\TenantView;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * Syncs domain folder config (source of truth) into the database on each request.
 * - If domain is not present: creates tenant (if needed), stancl domain, and tenant_view.
 * - If domain is present but tenant_id or view/code differ from config: updates DB to match config.
 */
class DomainConfigSync
{
    /**
     * Ensure DB reflects config for the given host. Call early in request (e.g. in TenantResolver::resolve).
     */
    public function sync(string $host): void
    {
        $tenantId = config('domain.tenant_id') ?? null;
        $code = config('domain.view') ?? config('domain.code') ?? 'default';

        if (!$tenantId) {
            return;
        }

        $tenant = Tenant::firstOrCreate(['id' => $tenantId]);

        // Ensure stancl domains table has this domain → this tenant (update if domain moved to another tenant)
        $domainModel = Domain::where('domain', $host)->first();
        if ($domainModel && $domainModel->tenant_id !== $tenantId) {
            $domainModel->update(['tenant_id' => $tenantId]);
        }
        $tenant->domains()->firstOrCreate(['domain' => $host]);

        $view = TenantView::where('domain', $host)->first();

        if (!$view) {
            TenantView::create([
                'tenant_id' => $tenant->id,
                'name' => $code,
                'domain' => $host,
                'code' => $code,
            ]);
            Log::debug('DomainConfigSync: created tenant view for domain', [
                'domain' => $host,
                'tenant_id' => $tenantId,
                'code' => $code,
            ]);
            return;
        }

        $changed = false;
        if ($view->tenant_id !== $tenantId) {
            $view->tenant_id = $tenantId;
            $changed = true;
        }
        if ($view->code !== $code) {
            $view->code = $code;
            $view->name = $code;
            $changed = true;
        }
        if ($changed) {
            $view->save();
            Log::debug('DomainConfigSync: updated tenant view to match config', [
                'domain' => $host,
                'tenant_id' => $tenantId,
                'code' => $code,
            ]);
        }
    }
}
