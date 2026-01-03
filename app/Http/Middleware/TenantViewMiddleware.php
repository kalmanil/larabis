<?php

namespace App\Http\Middleware;

use App\Contracts\CurrentTenant;
use App\Contracts\CurrentTenantView;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for tenant and view resolution and tenancy initialization.
 * 
 * Responsibilities:
 * - Resolve tenant and view using TenantResolver
 * - Bind resolved instances to service container via contracts
 * - Initialize stancl tenancy context
 * 
 * This middleware does NOT:
 * - Perform database writes
 * - Create tenant views
 * - Check schema existence
 * - Contain business logic
 */
class TenantViewMiddleware
{
    public function __construct(
        protected TenantResolver $resolver
    ) {
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ⚠️ DO NOT REORDER THESE STEPS.
        // 1. Resolve tenant/view FIRST (before any tenancy initialization)
        // 2. Initialize stancl tenancy (establishes database connection)
        // 3. Bind context AFTER initialization (ensures stancl lifecycle is respected)
        // Changes to this order will break upgrade safety - see docs/UPGRADES.md
        
        // Resolve tenant and view using resolver service
        $resolved = $this->resolver->resolve($request);
        $tenant = $resolved->tenant();
        $view = $resolved->view();
        
        // If tenant is resolved, initialize tenancy and bind context
        if ($tenant) {
            try {
                // Initialize stancl tenancy FIRST - this establishes the tenant database
                // connection, fires TenancyInitialized event, and sets up stancl's internal
                // state. Our custom bindings must come AFTER to ensure stancl's lifecycle
                // is respected and to avoid interfering with initialization.
                tenancy()->initialize($tenant);
                
                // Bind context AFTER tenancy initialization - this ensures:
                // 1. Tenant database connection is established
                // 2. TenancyInitialized event has fired (allows listeners to run)
                // 3. stancl's internal state is ready
                // 4. If initialization fails, our bindings won't be set (fail-safe)
                $context = new TenantContext($tenant, $view);
                
                // Bind via contracts for type-safe access
                app()->instance(CurrentTenant::class, $context);
                app()->instance(CurrentTenantView::class, $context);
                
                // Also bind context itself for direct access
                app()->instance(TenantContext::class, $context);
            } catch (\Exception $e) {
                // Log the error for debugging and monitoring
                Log::error('Tenancy initialization failed', [
                    'tenant_id' => $tenant->id,
                    'domain' => $request->getHost(),
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
                
                // Re-throw the exception so stancl can handle it appropriately
                // This allows stancl's error handling to work (e.g., showing proper error pages)
                throw $e;
            }
        }
        
        return $next($request);
    }
}

