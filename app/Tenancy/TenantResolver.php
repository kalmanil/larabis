<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\TenantView;
use Illuminate\Http\Request;

/**
 * Resolves tenant and tenant view from the current request.
 * 
 * This service encapsulates all tenant resolution logic, including:
 * - Reading domain configuration
 * - Finding tenant by ID or domain
 * - Finding tenant view by domain
 * 
 * Note: This service does NOT create tenant views. Views must exist
 * and be created via artisan commands (tenant:create, tenant:view).
 */
class TenantResolver
{
    /**
     * Resolve tenant from request.
     * 
     * Reads domain configuration and finds the corresponding tenant.
     * Returns null if tenant cannot be resolved.
     * 
     * @param Request $request
     * @return Tenant|null
     */
    public function resolveTenant(Request $request): ?Tenant
    {
        $host = $request->getHost();
        
        // Read domain configuration (from domain folder via config/env)
        $tenantId = config('domain.tenant_id') ?? $_ENV['DOMAIN_TENANT_ID'] ?? null;
        
        if ($tenantId) {
            // Find tenant by ID
            return Tenant::find($tenantId);
        }
        
        // Fallback: Find tenant by domain via tenant view
        $view = TenantView::where('domain', $host)->first();
        
        return $view ? $view->tenant : null;
    }
    
    /**
     * Resolve tenant view from request.
     * 
     * Reads domain configuration and finds the corresponding tenant view.
     * Returns null if view cannot be resolved.
     * 
     * @param Request $request
     * @param Tenant|null $tenant Optional tenant (if already resolved)
     * @return TenantView|null
     */
    public function resolveView(Request $request, ?Tenant $tenant = null): ?TenantView
    {
        $host = $request->getHost();
        
        // Use provided tenant or resolve it
        if (!$tenant) {
            $tenant = $this->resolveTenant($request);
        }
        
        if (!$tenant) {
            return null;
        }
        
        // Find view by domain for this tenant
        return $tenant->views()->where('domain', $host)->first();
    }
    
    /**
     * Resolve both tenant and view from request.
     * 
     * @param Request $request
     * @return TenantResolutionResult
     */
    public function resolve(Request $request): TenantResolutionResult
    {
        $tenant = $this->resolveTenant($request);
        $view = $tenant ? $this->resolveView($request, $tenant) : null;
        
        return new TenantResolutionResult($tenant, $view);
    }
}

