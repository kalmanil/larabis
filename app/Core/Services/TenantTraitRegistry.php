<?php

namespace App\Core\Services;

use App\Features\Pages\Contracts\PageDataServiceInterface;
use App\Features\Pages\Services\PageDataServiceFactory;
use App\Tenancy\TenantContext;

/**
 * @deprecated Use PageDataServiceInterface and PageDataServiceFactory instead.
 * This class remains only for backward compatibility; it delegates to the new service.
 */
class TenantTraitRegistry
{
    /**
     * @deprecated Use PageDataServiceInterface::getPageData(), getAdminDashboardData(), getAdminTheme() instead.
     */
    public static function getData(string $methodName, object $caller, array $args = [], ?TenantContext $context = null): ?array
    {
        $context = $context ?? (app()->bound(TenantContext::class) ? app(TenantContext::class) : null);
        if (!$context) {
            return null;
        }
        $factory = app(PageDataServiceFactory::class);
        $service = $factory->resolveFromContext($context);
        $methodMap = [
            'getPageData' => 'getPageData',
            'getAdminDashboardData' => 'getAdminDashboardData',
            'getAdminTheme' => 'getAdminTheme',
        ];
        if (!isset($methodMap[$methodName]) || !method_exists($service, $methodMap[$methodName])) {
            return null;
        }
        $result = $service->{$methodMap[$methodName]}();
        return is_array($result) ? $result : null;
    }

    /**
     * @deprecated Service-based resolution does not use traits. Check PageDataServiceInterface resolution instead.
     */
    public static function hasTrait(?string $methodName = null, ?TenantContext $context = null): bool
    {
        $context = $context ?? (app()->bound(TenantContext::class) ? app(TenantContext::class) : null);
        if (!$context) {
            return false;
        }
        $factory = app(PageDataServiceFactory::class);
        $service = $factory->resolveFromContext($context);
        if ($methodName === null) {
            return $service instanceof PageDataServiceInterface;
        }
        return method_exists($service, $methodName);
    }
}
