<?php

namespace App\Features\Pages\Tenants\lapp\Default;

use App\Features\Pages\Base\Default\PageDataService as BasePageDataService;
use App\Tenancy\TenantContext;

/**
 * Lapp default (landing) view page data.
 */
class PageDataService extends BasePageDataService
{
    protected array $lappConfig;

    protected array $lappBranding;

    public function __construct(TenantContext $tenantContext)
    {
        parent::__construct($tenantContext);
        $this->lappConfig = [
            'name' => 'Lapp',
            'tagline' => 'Your Business Solution',
            'theme_color' => '#3b82f6',
            'logo' => '/images/lapp-logo.png',
        ];
        $this->lappBranding = [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#8b5cf6',
            'accent_color' => '#10b981',
        ];
    }

    public function getPageData(): array
    {
        $base = parent::getPageData();
        return array_merge($base, [
            'tenant' => $this->tenantContext->getTenant(),
            'config' => $this->lappConfig,
            'branding' => $this->lappBranding,
        ]);
    }
}
