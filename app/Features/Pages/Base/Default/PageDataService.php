<?php

namespace App\Features\Pages\Base\Default;

use App\Features\Pages\Contracts\PageDataServiceInterface;
use App\Tenancy\TenantContext;

/**
 * Shared default (landing) view page data. Used when no tenant-specific service exists.
 */
class PageDataService implements PageDataServiceInterface
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {
    }

    public function getPageData(): array
    {
        return [
            'view_config' => [
                'type' => 'landing',
                'show_navigation' => true,
                'show_footer' => true,
                'meta_title' => 'Welcome',
            ],
            'is_landing' => true,
        ];
    }

    public function getAdminDashboardData(): array
    {
        return [];
    }

    public function getAdminTheme(): array
    {
        return [];
    }
}
