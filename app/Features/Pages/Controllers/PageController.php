<?php

namespace App\Features\Pages\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Services\TenantTraitRegistry;
use App\Features\Pages\Views\default\Traits\PageLogic as DefaultPageLogic;
use App\Features\Pages\Views\admin\Traits\PageLogic as BaseAdminPageLogic;
use App\Tenancy\TenantContext;

/**
 * Page Controller with Dynamic Trait Resolution
 *
 * Uses TenantTraitRegistry to dynamically discover and call tenant-specific
 * trait methods without requiring code changes when adding new tenants.
 *
 * Tenant context is injected (no static helper) for testability and explicit dependencies.
 *
 * @see TenantTraitRegistry for trait discovery priority
 * @see docs/UPGRADES.md for upgrade safety information
 */
class PageController extends Controller
{
    use DefaultPageLogic, BaseAdminPageLogic {
        // Resolve traitConstructPageLogic conflict - use DefaultPageLogic as primary
        // BaseAdminPageLogic constructor will be called explicitly when needed
        DefaultPageLogic::traitConstructPageLogic insteadof BaseAdminPageLogic;
        // Alias BaseAdminPageLogic constructor so it can still be called if needed
        BaseAdminPageLogic::traitConstructPageLogic as traitConstructAdminPageLogic;

        // Resolve getPageData conflict - use DefaultPageLogic as primary
        // BaseAdminPageLogic will be used via TenantTraitRegistry or explicit calls
        DefaultPageLogic::getPageData insteadof BaseAdminPageLogic;
    }

    public function __construct(
        protected TenantContext $tenantContext
    ) {
        // Initialize default view logic (called for all requests)
        $this->traitConstructPageLogic();
    }

    public function home()
    {
        if ($this->tenantContext->isView('admin')) {
            return $this->adminLogin();
        }

        // Default landing page - use dynamic trait resolution
        $tenantData = TenantTraitRegistry::getData('getPageData', $this, [], $this->tenantContext) ?? [];
        $defaultData = $this->getPageData();
        $pageData = array_merge($defaultData, $tenantData);

        return $this->tenantContext->view('home', $pageData);
    }

    public function adminLogin()
    {
        $this->traitConstructAdminPageLogic();

        $tenantData = TenantTraitRegistry::getData('getPageData', $this, [], $this->tenantContext) ?? [];
        $adminData = TenantTraitRegistry::getData('getAdminDashboardData', $this, [], $this->tenantContext) ?? [];
        $themeData = TenantTraitRegistry::getData('getAdminTheme', $this, [], $this->tenantContext) ?? [];

        if (empty($adminData) && method_exists($this, 'getAdminDashboardData')) {
            $adminData = $this->getAdminDashboardData();
        }
        if (empty($themeData) && method_exists($this, 'getAdminTheme')) {
            $themeData = $this->getAdminTheme();
        }

        $defaultData = $this->getPageData();
        $pageData = array_merge(
            $defaultData,
            $tenantData,
            ['dashboard' => $adminData],
            ['theme' => $themeData]
        );

        return $this->tenantContext->view('login', $pageData);
    }
}


