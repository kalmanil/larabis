<?php

namespace App\Features\Pages\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Traits\ConstructableTrait;
use App\Features\Pages\Tenants\lapp\Traits\PageLogic as LappPageLogic;
use App\Features\Pages\Tenants\lapp\Views\admin\Traits\PageLogic as LappAdminPageLogic;
use App\Features\Pages\Views\default\Traits\PageLogic as DefaultPageLogic;
use App\Features\Pages\Views\admin\Traits\PageLogic as BaseAdminPageLogic;
use App\Helpers\TenancyHelper;

class PageController extends Controller
{
    use ConstructableTrait;
    use LappPageLogic, DefaultPageLogic, BaseAdminPageLogic, LappAdminPageLogic {
        // Resolve traitConstructPageLogic conflicts
        // Keep DefaultPageLogic as primary (for default view)
        DefaultPageLogic::traitConstructPageLogic insteadof LappPageLogic, BaseAdminPageLogic, LappAdminPageLogic;
        // Alias others to unique names so ConstructableTrait can call them
        LappPageLogic::traitConstructPageLogic as traitConstructLappPageLogic;
        BaseAdminPageLogic::traitConstructPageLogic as traitConstructBaseAdminPageLogic;
        LappAdminPageLogic::traitConstructPageLogic as traitConstructLappAdminPageLogic;
        
        // Priority: Default view uses DefaultPageLogic, Admin view uses LappAdminPageLogic
        // For default view: DefaultPageLogic takes priority
        DefaultPageLogic::getPageData insteadof LappPageLogic, BaseAdminPageLogic;
        // For admin view: LappAdminPageLogic takes priority (used explicitly in adminLogin)
        LappAdminPageLogic::getPageData as getLappAdminPageData;
        LappAdminPageLogic::getAdminDashboardData insteadof BaseAdminPageLogic;
        LappAdminPageLogic::getAdminTheme insteadof BaseAdminPageLogic;
        // Resolve getLappBranding conflict - tenant version takes priority (public)
        LappPageLogic::getLappBranding insteadof LappAdminPageLogic;
        LappAdminPageLogic::getLappBranding as getLappAdminBranding;
        // Keep others accessible via aliases
        LappPageLogic::getPageData as getLappPageData;
        BaseAdminPageLogic::getPageData as getBaseAdminPageData;
    }
    
    public function __construct()
    {
        // Call parent constructor if it exists (base Controller doesn't have one)
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        
        // Call ConstructableTrait's initialization (it will call DefaultPageLogic::traitConstructPageLogic)
        $this->initializeAllTraits();
        
        // Manually call the aliased constructors for the others
        // (they're aliased so ConstructableTrait won't find them automatically)
        $this->traitConstructLappPageLogic();
        $this->traitConstructBaseAdminPageLogic();
        $this->traitConstructLappAdminPageLogic();
    }
    
    public function home()
    {
        $view = TenancyHelper::currentView();
        $tenant = TenancyHelper::currentTenant();
        
        // Determine which view to use based on view code
        if ($view && $view->code === 'admin') {
            return $this->adminLogin();
        }
        
        // Default landing page - combine tenant and default view data
        // getPageData() now uses DefaultPageLogic as primary
        $pageData = array_merge(
            $this->getLappPageData(),
            $this->getPageData() // Uses DefaultPageLogic
        );
        
        return TenancyHelper::view('home', $pageData);
    }
    
    public function adminLogin()
    {
        // Admin view - explicitly use Lapp admin trait data
        $pageData = $this->getLappAdminPageData();
        
        // Add dashboard data (uses tenant-specific override if available)
        $pageData['dashboard'] = $this->getAdminDashboardData();
        $pageData['theme'] = $this->getAdminTheme();
        
        // View path is constructed as: tenants.{tenant_id}.{view_code}.{view_name}
        // So for admin view, just use 'login' (not 'admin.login')
        return TenancyHelper::view('login', $pageData);
    }
}


