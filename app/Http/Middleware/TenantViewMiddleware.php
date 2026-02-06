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

        // Always bind TenantContext (with null tenant/view when none resolved) so controllers can inject it
        $context = new TenantContext($tenant, $view);
        app()->instance(CurrentTenant::class, $context);
        app()->instance(CurrentTenantView::class, $context);
        app()->instance(TenantContext::class, $context);

        if ($tenant) {
            try {
                tenancy()->initialize($tenant);
            } catch (\Exception $e) {
                Log::error('Tenancy initialization failed', [
                    'tenant_id' => $tenant->id,
                    'domain' => $request->getHost(),
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
                throw $e;
            }
        }

        return $next($request);
    }
}

