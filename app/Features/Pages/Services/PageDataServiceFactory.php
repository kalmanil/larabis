<?php

namespace App\Features\Pages\Services;

use App\Features\Pages\Contracts\PageDataServiceInterface;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves the PageDataService implementation for the current tenant and view.
 * Priority: tenant-specific (Tenants\{id}\Default or \Admin) then base (Base\Default or \Admin).
 */
class PageDataServiceFactory
{
    public function __construct(
        protected Container $app
    ) {
    }

    public function resolve(?string $tenantId, ?string $viewCode): PageDataServiceInterface
    {
        $viewCode = $viewCode ?? 'default';
        $viewPart = $this->viewCodeToNamespacePart($viewCode);

        if ($tenantId !== null && $tenantId !== '') {
            $tenantFqcn = "App\\Features\\Pages\\Tenants\\{$tenantId}\\{$viewPart}\\PageDataService";
            if (class_exists($tenantFqcn)) {
                return $this->app->make($tenantFqcn);
            }
        }

        $baseFqcn = "App\\Features\\Pages\\Base\\{$viewPart}\\PageDataService";
        return $this->app->make($baseFqcn);
    }

    public function resolveFromContext(TenantContext $context): PageDataServiceInterface
    {
        $tenant = $context->getTenant();
        $view = $context->getView();
        $tenantId = $tenant?->id;
        $viewCode = $view?->code ?? 'default';
        return $this->resolve($tenantId, $viewCode);
    }

    private function viewCodeToNamespacePart(string $viewCode): string
    {
        return match (strtolower($viewCode)) {
            'admin' => 'Admin',
            default => 'Default',
        };
    }
}
