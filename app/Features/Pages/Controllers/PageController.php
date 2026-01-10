<?php

namespace App\Features\Pages\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Services\TenantTraitRegistry;
use App\Features\Pages\Views\default\Traits\PageLogic as DefaultPageLogic;
use App\Features\Pages\Views\admin\Traits\PageLogic as BaseAdminPageLogic;
use App\Helpers\TenancyHelper;

/**
 * Page Controller with Dynamic Trait Resolution
 * 
 * Uses TenantTraitRegistry to dynamically discover and call tenant-specific
 * trait methods without requiring code changes when adding new tenants.
 * 
 * This approach is upgrade-safe because:
 * - Uses only TenancyHelper (contract-based, not stancl directly)
 * - Gracefully falls back when tenant-specific code doesn't exist
 * - Supports both static methods (new tenants) and instance methods (legacy tenants)
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
    
    public function __construct()
    {
        // Initialize default view logic (called for all requests)
        // This sets up basic page data via DefaultPageLogic
        $this->traitConstructPageLogic();
    }
    
    public function home()
    {
        $view = TenancyHelper::currentView();
        
        // Determine which view to use based on view code
        if ($view && $view->code === 'admin') {
            return $this->adminLogin();
        }
        
        // Default landing page - use dynamic trait resolution
        // Priority: Tenant-View → Tenant → View → Base
        // The registry handles both static methods (new tenants) and instance methods (legacy)
        $tenantData = TenantTraitRegistry::getData('getPageData', $this) ?? [];
        
        // Merge with default view data (fallback)
        $defaultData = $this->getPageData();
        
        $pageData = array_merge($defaultData, $tenantData);
        
        return TenancyHelper::view('home', $pageData);
    }
    
    public function adminLogin()
    {
        // Initialize admin view logic (merge with default data)
        // This sets up admin-specific page data via BaseAdminPageLogic
        $this->traitConstructAdminPageLogic();
        
        // Admin view - use dynamic trait resolution
        // Priority: Tenant-View (admin) → Tenant → View (admin) → Base
        $tenantData = TenantTraitRegistry::getData('getPageData', $this) ?? [];
        
        // Get admin-specific data
        $adminData = TenantTraitRegistry::getData('getAdminDashboardData', $this) ?? [];
        $themeData = TenantTraitRegistry::getData('getAdminTheme', $this) ?? [];
        
        // Fallback to instance methods if registry didn't find static methods
        // (for backward compatibility with lapp and other legacy tenants)
        if (empty($adminData) && method_exists($this, 'getAdminDashboardData')) {
            $adminData = $this->getAdminDashboardData();
        }
        if (empty($themeData) && method_exists($this, 'getAdminTheme')) {
            $themeData = $this->getAdminTheme();
        }
        
        // Merge all data (default + admin + tenant-specific)
        $defaultData = $this->getPageData(); // Gets merged default + admin data
        $pageData = array_merge(
            $defaultData,
            $tenantData,
            ['dashboard' => $adminData],
            ['theme' => $themeData]
        );
        
        // View path is constructed as: tenants.{tenant_id}.{view_code}.{view_name}
        // So for admin view, just use 'login' (not 'admin.login')
        return TenancyHelper::view('login', $pageData);
    }
}


