<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantViewMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Check if domain config is set (from domain folder)
        $tenantId = $_ENV['DOMAIN_TENANT_ID'] ?? null;
        $code = $_ENV['DOMAIN_CODE'] ?? 'default';
        
        if ($tenantId) {
            // Find tenant by ID
            $tenant = Tenant::find($tenantId);
            
            if ($tenant) {
                // Find or create view for this domain
                $view = $tenant->views()->where('domain', $host)->first();
                
                if (!$view) {
                    // Create view if it doesn't exist
                    $view = $tenant->views()->create([
                        'name' => $code,
                        'domain' => $host,
                        'code' => $code,
                    ]);
                }
                
                // Set current tenant view in container
                app()->instance('currentTenantView', $view);
                app()->instance('currentTenant', $tenant);
                
                // Initialize tenant context
                tenancy()->initialize($tenant);
            }
        } else {
            // Fallback: Find by domain directly
            $view = TenantView::where('domain', $host)->first();
            
            if ($view) {
                app()->instance('currentTenantView', $view);
                app()->instance('currentTenant', $view->tenant);
                tenancy()->initialize($view->tenant);
            }
        }
        
        return $next($request);
    }
}

