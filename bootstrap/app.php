<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load tenant-specific routes if they exist
            // This allows tenants to define their own routes in tenants/{tenant_id}/routes/web.php
            $tenantId = $_ENV['DOMAIN_TENANT_ID'] ?? null;
            if ($tenantId) {
                $tenantRoutes = base_path("tenants/{$tenantId}/routes/web.php");
                if (file_exists($tenantRoutes)) {
                    \Illuminate\Support\Facades\Route::middleware('web')->group($tenantRoutes);
                }
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ⚠️ DO NOT MOVE OR REORDER.
        // TenantViewMiddleware MUST be prepended to web middleware stack.
        // Tenancy must be initialized before any other middleware that might need tenant context.
        // Changes here affect upgrade safety - see docs/UPGRADES.md
        $middleware->web(prepend: [
            \App\Http\Middleware\TenantViewMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
