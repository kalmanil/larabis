<?php

namespace App\Features\Pages\Tenants\lapp\Admin;

use App\Features\Pages\Base\Admin\PageDataService as BaseAdminPageDataService;
use App\Tenancy\TenantContext;

/**
 * Lapp admin view page data.
 */
class PageDataService extends BaseAdminPageDataService
{
    protected array $lappAdminConfig;

    public function __construct(TenantContext $tenantContext)
    {
        parent::__construct($tenantContext);
        $this->lappAdminConfig = [
            'custom_theme' => 'lapp-blue',
            'show_custom_widgets' => true,
            'enable_advanced_features' => true,
            'custom_branding' => true,
        ];
        $this->adminConfig = array_merge($this->adminConfig, [
            'theme' => 'lapp-blue',
            'meta_title' => 'Lapp Admin CMS',
            'custom_features' => $this->lappAdminConfig,
        ]);
    }

    public function getPageData(): array
    {
        $base = parent::getPageData();
        return array_merge($base, [
            'lapp_admin_config' => $this->lappAdminConfig,
        ]);
    }

    public function getAdminDashboardData(): array
    {
        $base = parent::getAdminDashboardData();
        return array_merge($base, [
            'stats' => [
                'lapp_specific_metric' => 100,
                'custom_widgets' => $this->getLappWidgets(),
                'advanced_features_enabled' => true,
            ],
            'recent_activity' => $this->getLappRecentActivity(),
            'custom_branding' => $this->getLappAdminBranding(),
        ]);
    }

    public function getAdminTheme(): array
    {
        return [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#8b5cf6',
            'accent_color' => '#10b981',
            'background_color' => '#f8fafc',
        ];
    }

    protected function getLappWidgets(): array
    {
        return [
            'sales_overview',
            'customer_metrics',
            'revenue_chart',
        ];
    }

    protected function getLappRecentActivity(): array
    {
        return [
            'User logged in',
            'Product updated',
            'Order created',
        ];
    }

    protected function getLappAdminBranding(): array
    {
        return [
            'logo' => '/images/lapp-admin-logo.png',
            'favicon' => '/images/lapp-favicon.ico',
            'company_name' => 'Lapp',
        ];
    }
}
