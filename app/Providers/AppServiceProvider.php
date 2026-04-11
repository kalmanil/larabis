<?php

namespace App\Providers;

use App\Features\Pages\Contracts\PageDataServiceInterface;
use App\Features\Pages\Services\PageDataServiceFactory;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantViewPathConfigurator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Stancl\Tenancy\Commands\Migrate as TenantsMigrate;
use Stancl\Tenancy\Events\TenancyInitialized;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tenant class autoloading is handled by bootstrap/tenant-autoload.php
        // which is loaded via composer.json's "files" autoload array.

        $this->app->bind(PageDataServiceInterface::class, function ($app) {
            $factory = $app->make(PageDataServiceFactory::class);
            $context = $app->make(TenantContext::class);
            return $factory->resolveFromContext($context);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // After stancl registers tenants:migrate, use a version that also runs
        // tenants/{id}/database/migrations (see App\Console\Commands\TenantsMigrateCommand).
        $this->app->singleton(TenantsMigrate::class, function ($app) {
            return new \App\Console\Commands\TenantsMigrateCommand(
                $app['migrator'],
                $app['events']
            );
        });

        // Register tenant view namespaces AFTER tenancy is initialized
        // This ensures tenant context is available when views are resolved
        $this->app['events']->listen(TenancyInitialized::class, function ($event) {
            $this->registerTenantViews();
            $this->configureTenantAuth();
        });
        
        // Also register on boot for non-tenant requests (backward compatibility)
        // This ensures views are available even if tenancy isn't initialized
        $this->registerTenantViews();

        // Tenant error pages (errors::*) use config('view.paths'); prepend early when domain sets tenant id
        TenantViewPathConfigurator::prependTenantResourcesViews(
            $_ENV['DOMAIN_TENANT_ID'] ?? config('domain.tenant_id')
        );
        
        // Configure tenant auth if DOMAIN_TENANT_ID is set (early tenant context)
        $this->configureTenantAuth();

        $this->registerFlashcardsCommands();
    }

    /**
     * Register flashcards tenant Artisan commands when that tenant is active.
     */
    protected function registerFlashcardsCommands(): void
    {
        $tenantId = $_ENV['DOMAIN_TENANT_ID'] ?? null;
        if ($tenantId !== 'flashcards') {
            return;
        }

        $path = base_path('tenants/flashcards/app/Features/Flashcards/Console/Commands/EnsureSuperAdminCommand.php');
        if (file_exists($path)) {
            $this->commands([\App\Features\Flashcards\Console\Commands\EnsureSuperAdminCommand::class]);
        }
    }
    
    /**
     * Configure Auth to use tenant-specific User model if available.
     */
    protected function configureTenantAuth(): void
    {
        $tenantId = $_ENV['DOMAIN_TENANT_ID'] ?? null;
        
        if (!$tenantId) {
            return;
        }
        
        // Check if tenant has a custom User model
        $tenantUserClass = "App\\Features\\Auth\\Models\\User";
        $tenantUserPath = base_path("tenants/{$tenantId}/app/Features/Auth/Models/User.php");
        
        if (file_exists($tenantUserPath) && class_exists($tenantUserClass)) {
            Config::set('auth.providers.users.model', $tenantUserClass);
        }
    }
    
    /**
     * Register tenant view namespaces from consolidated tenants/ directory.
     * 
     * This method is called both during boot (for backward compatibility)
     * and after tenancy initialization (for proper tenant context).
     */
    protected function registerTenantViews(): void
    {
        $tenantsPath = base_path('tenants');
        
        if (!is_dir($tenantsPath)) {
            return;
        }
        
        $tenantDirs = array_filter(glob($tenantsPath . '/*'), 'is_dir');
        
        foreach ($tenantDirs as $tenantDir) {
            $tenantId = basename($tenantDir);
            $viewsPath = $tenantDir . '/resources/views';
            
            if (is_dir($viewsPath)) {
                // Register namespace for this tenant: tenants.{tenant_id}
                // This allows views like: tenants.lapp.default.home
                // to resolve to: tenants/lapp/resources/views/default/home.blade.php
                View::addNamespace("tenants.{$tenantId}", $viewsPath);
                
                // Also add as location for fallback
                View::addLocation($viewsPath);
            }
        }
    }
}
