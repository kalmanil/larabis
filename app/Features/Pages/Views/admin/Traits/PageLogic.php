<?php

namespace App\Features\Pages\Views\admin\Traits;

use App\Shared\Traits\Base\PageLogic as BasePageLogic;

trait PageLogic
{
    use BasePageLogic {
        BasePageLogic::getPageData as getBasePageData;
    }
    
    protected $adminConfig;
    protected $adminPermissions;
    
    public function traitConstructPageLogic()
    {
        // Base admin configuration (shared across all tenants)
        $this->adminConfig = [
            'type' => 'cms',
            'show_navigation' => false,
            'show_footer' => false,
            'meta_title' => 'Admin CMS',
            'requires_auth' => true,
            'theme' => 'default',
            'show_sidebar' => true,
        ];
        
        $this->adminPermissions = [
            'view_all',
            'edit_all',
            'delete_all',
        ];
        
        // Allow tenant-specific overrides
        $this->initializeAdminConfig();
        
        $this->setPageData([
            'view_config' => $this->adminConfig,
            'permissions' => $this->adminPermissions,
            'is_admin' => true,
        ]);
    }
    
    /**
     * Override this method in tenant-specific admin traits to customize config
     */
    protected function initializeAdminConfig(): void
    {
        // Base implementation - can be overridden by tenant-specific traits
    }
    
    public function getAdminConfig(): array
    {
        return $this->adminConfig;
    }
    
    public function hasAdminPermission(string $permission): bool
    {
        return in_array($permission, $this->adminPermissions);
    }
    
    /**
     * Get admin dashboard data - can be overridden by tenant-specific traits
     */
    public function getAdminDashboardData(): array
    {
        return [
            'stats' => [],
            'recent_activity' => [],
            'notifications' => [],
        ];
    }
    
    /**
     * Get admin theme configuration - can be overridden by tenant-specific traits
     */
    public function getAdminTheme(): array
    {
        return [
            'primary_color' => '#6366f1',
            'secondary_color' => '#8b5cf6',
            'accent_color' => '#10b981',
        ];
    }
}

