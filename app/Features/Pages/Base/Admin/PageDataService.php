<?php

namespace App\Features\Pages\Base\Admin;

use App\Features\Pages\Contracts\PageDataServiceInterface;
use App\Tenancy\TenantContext;

/**
 * Shared admin view page data. Used when no tenant-specific admin service exists.
 */
class PageDataService implements PageDataServiceInterface
{
    protected array $adminConfig;

    protected array $adminPermissions;

    public function __construct(
        protected TenantContext $tenantContext
    ) {
        $this->adminConfig = [
            'type' => 'cms',
            'show_navigation' => false,
            'show_footer' => false,
            'meta_title' => 'Admin CMS',
            'requires_auth' => true,
            'theme' => 'default',
            'show_sidebar' => true,
        ];
        $this->adminPermissions = ['view_all', 'edit_all', 'delete_all'];
    }

    public function getPageData(): array
    {
        return [
            'view_config' => $this->adminConfig,
            'permissions' => $this->adminPermissions,
            'is_admin' => true,
        ];
    }

    public function getAdminDashboardData(): array
    {
        return [
            'stats' => [],
            'recent_activity' => [],
            'notifications' => [],
        ];
    }

    public function getAdminTheme(): array
    {
        return [
            'primary_color' => '#6366f1',
            'secondary_color' => '#8b5cf6',
            'accent_color' => '#10b981',
        ];
    }
}
