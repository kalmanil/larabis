<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\Config;

/**
 * Prepends the active tenant's resources/views to config('view.paths') so HTTP
 * exception rendering (errors::419, errors::404, etc.) resolves tenant-specific
 * templates first; missing files fall through to app and framework defaults.
 *
 * @see \Illuminate\Foundation\Exceptions\RegisterErrorViewPaths
 */
final class TenantViewPathConfigurator
{
    /**
     * @param  string|null  $tenantId  Tenant directory name under tenants/
     */
    public static function prependTenantResourcesViews(?string $tenantId): void
    {
        if ($tenantId === null || $tenantId === '') {
            return;
        }

        $tenantId = basename($tenantId);
        $tenantViews = base_path("tenants/{$tenantId}/resources/views");

        if (! is_dir($tenantViews)) {
            return;
        }

        $paths = config('view.paths', []);
        $paths = array_values(array_filter($paths, static fn (string $p): bool => $p !== $tenantViews));
        array_unshift($paths, $tenantViews);

        Config::set('view.paths', $paths);
    }
}
