<?php

namespace App\Features\Pages\Tenants\lapp\Views\admin\Traits;

use App\Features\Pages\Views\admin\Traits\PageLogic as BaseAdminPageLogic;

trait PageLogic
{
    use BaseAdminPageLogic {
        BaseAdminPageLogic::getPageData as getBaseAdminPageData;
        BaseAdminPageLogic::getAdminConfig as getBaseAdminConfig;
        BaseAdminPageLogic::getAdminDashboardData as getBaseAdminDashboardData;
    }
    
    protected $lappAdminConfig;
    
    public function traitConstructPageLogic()
    {
        // Note: Base admin trait's constructor is called separately by ConstructableTrait
        // We initialize Lapp-specific config here
        
        // Lapp-specific admin configuration
        $this->lappAdminConfig = [
            'custom_theme' => 'lapp-blue',
            'show_custom_widgets' => true,
            'enable_advanced_features' => true,
            'custom_branding' => true,
        ];
        
        // Ensure adminConfig exists (from base trait)
        if (!isset($this->adminConfig) || empty($this->adminConfig)) {
            $this->adminConfig = [];
        }
        
        // Override base admin config with Lapp-specific values
        $this->adminConfig = array_merge($this->adminConfig, [
            'theme' => 'lapp-blue',
            'meta_title' => 'Lapp Admin CMS',
            'custom_features' => $this->lappAdminConfig,
        ]);
        
        // Add Lapp-specific admin data
        $this->setPageData([
            'lapp_admin_config' => $this->lappAdminConfig,
        ]);
    }
    
    /**
     * Override base admin dashboard with Lapp-specific data
     */
    public function getAdminDashboardData(): array
    {
        // Get base admin dashboard data via alias
        $baseData = $this->getBaseAdminDashboardData();
        
        // Add Lapp-specific dashboard data
        return array_merge($baseData, [
            'stats' => [
                'lapp_specific_metric' => 100,
                'custom_widgets' => $this->getLappWidgets(),
                'advanced_features_enabled' => true,
            ],
            'recent_activity' => $this->getLappRecentActivity(),
            'custom_branding' => $this->getLappAdminBranding(),
        ]);
    }
    
    /**
     * Override admin theme with Lapp-specific colors
     */
    public function getAdminTheme(): array
    {
        return [
            'primary_color' => '#3b82f6',    // Lapp blue
            'secondary_color' => '#8b5cf6',  // Lapp purple
            'accent_color' => '#10b981',     // Lapp green
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
    
    protected function getLappBranding(): array
    {
        return [
            'logo' => '/images/lapp-admin-logo.png',
            'favicon' => '/images/lapp-favicon.ico',
            'company_name' => 'Lapp',
        ];
    }
    
    public function getLappAdminConfig(): array
    {
        return $this->lappAdminConfig;
    }
}

