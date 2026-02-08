<?php

namespace App\Features\Pages\Controllers;

use App\Http\Controllers\Controller;
use App\Features\Pages\Contracts\PageDataServiceInterface;
use App\Tenancy\TenantContext;

/**
 * Page Controller with service-based page data resolution.
 *
 * Uses PageDataServiceInterface (resolved per tenant+view by PageDataServiceFactory)
 * instead of traits. No TenantTraitRegistry.
 */
class PageController extends Controller
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected PageDataServiceInterface $pageDataService
    ) {
    }

    public function home()
    {
        if ($this->tenantContext->isView('admin')) {
            return $this->adminLogin();
        }

        $pageData = $this->pageDataService->getPageData();
        return $this->tenantContext->view('home', $pageData);
    }

    public function adminLogin()
    {
        $pageData = $this->pageDataService->getPageData();
        $adminData = $this->pageDataService->getAdminDashboardData();
        $themeData = $this->pageDataService->getAdminTheme();

        $pageData = array_merge(
            $pageData,
            ['dashboard' => $adminData],
            ['theme' => $themeData]
        );

        return $this->tenantContext->view('login', $pageData);
    }
}
