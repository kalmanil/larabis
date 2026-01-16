<?php

namespace App\Tenancy;

use App\Models\Tenant;
use App\Models\TenantView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Resolves tenant and tenant view from the current request.
 * 
 * ⚠️ CRITICAL: This is the SINGLE ENTRY POINT for tenant resolution.
 * DO NOT create alternative resolution paths - all tenant resolution
 * must go through this service to maintain upgrade safety.
 * 
 * This service encapsulates all tenant resolution logic, including:
 * - Reading domain configuration
 * - Finding tenant by ID or domain
 * - Finding tenant view by code (from config) or domain
 * 
 * Note: This service does NOT create tenant views. Views must exist
 * and be created via artisan commands (tenant:create, tenant:view).
 * 
 * See docs/UPGRADES.md for upgrade safety information.
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
            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                Log::debug('Tenant resolution failed: tenant not found', [
                    'host' => $host,
                    'tenant_id_from_config' => $tenantId,
                ]);
            }
            
            return $tenant;
        }
        
        // Fallback: Find tenant by domain via tenant view
        $view = TenantView::where('domain', $host)->first();
        
        if (!$view) {
            Log::debug('Tenant resolution failed: no view found for domain', [
                'host' => $host,
            ]);
        }
        
        return $view ? $view->tenant : null;
    }
    
    /**
     * Resolve tenant view from request.
     * 
     * Reads domain configuration and finds the corresponding tenant view.
     * Priority: 1) Code from config, 2) Domain-based lookup
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
        
        // Priority 1: Check if code is explicitly set in domain config
        $code = config('domain.code') ?? $_ENV['DOMAIN_CODE'] ?? null;
        if ($code) {
            $view = $tenant->views()->where('code', $code)->first();
            if ($view) {
                return $view;
            }
            // If code is set but view not found, log it
            Log::debug('Tenant view resolution failed: view not found for code', [
                'host' => $host,
                'tenant_id' => $tenant->id,
                'code' => $code,
            ]);
        }
        
        // Priority 2: Fallback to domain-based lookup
        $view = $tenant->views()->where('domain', $host)->first();
        
        if (!$view) {
            Log::debug('Tenant view resolution failed: no view found for domain', [
                'host' => $host,
                'tenant_id' => $tenant->id,
            ]);
        }
        
        return $view;
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

